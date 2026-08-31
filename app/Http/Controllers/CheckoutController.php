<?php

namespace App\Http\Controllers;

use App\Mail\OrderInvoiceMail;
use Illuminate\Support\Facades\Mail;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\CartService;
use App\Services\MidtransService;
use App\Services\ShippingCostService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\SaldoTidakCukup;
use App\Services\ReferralService;
use App\Services\RpayService;
use App\Services\TransactionFeeService;
use App\Support\CatatAktivitas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;

class CheckoutController extends Controller
{
    protected $cartService;
    protected $shippingService;
    protected $midtransService;

    public function __construct(
        CartService $cartService,
        ShippingCostService $shippingService,
        MidtransService $midtransService
    ) {
        $this->cartService     = $cartService;
        $this->shippingService = $shippingService;
        $this->midtransService = $midtransService;
    }

    /**
 * Apakah data kontak pembeli sudah lengkap?
 */
    private function kontakLengkap(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        $hp = trim((string) $user->phone);

        return filled(trim((string) $user->name))
            && $hp !== ''
            && $hp !== 'Belum diatur'
            && strlen(preg_replace('/\D/', '', $hp)) >= 9;
    }

    /**
     * Tampilkan halaman checkout.
     */
    public function index()
    {
        $cart = $this->cartService->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja Anda kosong.');
        }

        // Hanya barang yang dicentang yang dibawa ke checkout.
        $cartItems = $this->cartService->itemTerpilih();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Pilih dulu barang yang mau dibayar dengan mencentangnya.');
        }

        // Tamu belum punya alamat tersimpan
        $addresses = Auth::check()
            ? Auth::user()->addresses()->orderBy('is_default', 'desc')->get()
            : collect();

        // Hitung estimasi ongkir default untuk alamat utama/pertama (jika ada lat/lng)
        $defaultAddress = $addresses->first();
        $defaultCouriers = [];

        if ($defaultAddress && $defaultAddress->latitude && $defaultAddress->longitude) {
            $defaultCouriers = $this->shippingService->calculate(
                (float) $defaultAddress->latitude,
                (float) $defaultAddress->longitude,
                $cart->jumlah_terpilih,
                $defaultAddress->city,
                $defaultAddress->postal_code
            );
        } else {
            // Belum ada koordinat tujuan. Titik toko dipakai sekadar untuk
            // menampilkan perkiraan, tetapi pengiriman instan sengaja
            // dibuang — kalau ikut tampil, pembeli luar kota akan melihat
            // tawaran yang langsung hilang begitu alamatnya diisi.
            $defaultCouriers = collect($this->shippingService->calculate(
                (float) config('pengiriman.toko.lintang'),
                (float) config('pengiriman.toko.bujur'),
                $cart->jumlah_terpilih
            ))->reject(fn ($kurir) => ($kurir['jenis'] ?? '') === 'instan')->values()->all();
        }

        $kontakLengkap = $this->kontakLengkap();
        $sudahLogin    = Auth::check();

        return view('checkout.index', compact(
            'cart', 'cartItems', 'addresses', 'defaultCouriers', 'kontakLengkap', 'sudahLogin'
        ));
    }

    /**
 * Langkah "Kontak" pada halaman checkout.
 */
    public function simpanKontak(Request $request)
    {
        $aturan = [
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
        ];

        $pesan = [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'phone.required'     => 'Nomor HP wajib diisi.',
            'phone.min'          => 'Nomor HP terlalu pendek.',
            'phone.regex'        => 'Nomor HP hanya boleh berisi angka.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email ini sudah terdaftar. Silakan masuk memakai akun tersebut.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Ulangi kata sandi belum sama.',
        ];

        // Tamu perlu email & kata sandi untuk membuat akun
        if (! Auth::check()) {
            $aturan['email']    = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class];
            $aturan['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        $data = $request->validate($aturan, $pesan);

        if (Auth::check()) {
            Auth::user()->update([
                'name'  => trim($data['name']),
                'phone' => trim($data['phone']),
            ]);

            return redirect()->route('checkout.index')
                ->with('success', 'Data kontak diperbarui. Silakan lanjutkan ke alamat pengiriman.');
        }

        // ── Tamu: buat akun, masuk, lalu pindahkan isi keranjangnya ──
        $sesiTamu = Session::getId();

        $user = User::create([
            'name'     => trim($data['name']),
            'phone'    => trim($data['phone']),
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'customer',
        ]);

        // Beri peran customer bila paket izin dipakai di aplikasi ini
        if (method_exists($user, 'assignRole')) {
            try {
                $user->assignRole('customer');
            } catch (\Throwable $e) {
                // Peran belum ada di database — bukan penghalang untuk berbelanja
            }
        }

        event(new Registered($user));

        Auth::login($user);

        // ID sesi berubah setelah login demi keamanan, karena itu keranjang
        // tamu dipindahkan memakai ID sesi yang lama.
        $request->session()->regenerate();
        $this->cartService->mergeGuestCart($sesiTamu, $user->id);

        return redirect()->route('checkout.index')
            ->with('success', 'Akun berhasil dibuat dan kamu sudah masuk. Silakan lanjutkan ke alamat pengiriman.');
    }

    /**
 * Masuk ke akun langsung dari halaman checkout.
 */
    public function masuk(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email belum benar.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // Percobaan masuk dibatasi.
        $kunci = 'checkout-masuk|' . $request->ip() . '|' . strtolower($data['email']);

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            $detik = RateLimiter::availableIn($kunci);

            return back()->withInput($request->only('email'))->withErrors([
                'checkout_email' => 'Terlalu banyak percobaan. Coba lagi dalam ' . $detik . ' detik.',
            ], 'masuk');
        }

        $sesiTamu = Session::getId();

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            RateLimiter::hit($kunci, 60);

            return back()->withInput($request->only('email'))->withErrors([
                'checkout_email' => 'Email atau kata sandi salah.',
            ], 'masuk');
        }

        RateLimiter::clear($kunci);

        // ID sesi berubah setelah masuk demi keamanan, jadi keranjang tamu
        // dipindahkan memakai ID sesi yang lama.
        $request->session()->regenerate();
        $this->cartService->mergeGuestCart($sesiTamu, Auth::id());

        return redirect()->route('checkout.index')
            ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '. Lanjutkan pesananmu.');
    }

    /**
     * API Response AJAX: Hitung biaya ongkir dinamis berdasarkan koordinat GPS customer.
     */
    public function calculateShippingCost(Request $request)
    {
        $request->validate([
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'city'        => 'nullable|string',
            'postal_code' => 'nullable|string',
        ]);

        $cart = $this->cartService->getCart();
        $totalQty = $cart ? max(1, $cart->jumlah_terpilih) : 1;

        $couriers = $this->shippingService->calculate(
            (float) $request->latitude,
            (float) $request->longitude,
            $totalQty,
            $request->input('city'),
            $request->input('postal_code')
        );

        return response()->json([
            'status'   => 'success',
            'couriers' => $couriers,
        ]);
    }

    /**
     * Simpan pesanan baru ke database dan alihkan ke halaman pembayaran.
     */
    public function store(Request $request)
    {
        $request->validate([
            'address_id'                 => 'nullable|exists:addresses,id',
            'new_address.label'          => 'required_if:address_id,null|nullable|string|max:255',
            'new_address.recipient_name'  => 'required_if:address_id,null|nullable|string|max:255',
            'new_address.phone'           => 'required_if:address_id,null|nullable|string|max:20',
            'new_address.address_line'    => 'required_if:address_id,null|nullable|string',
            'new_address.city'            => 'required_if:address_id,null|nullable|string|max:255',
            'new_address.province'        => 'required_if:address_id,null|nullable|string|max:255',
            'new_address.postal_code'     => 'required_if:address_id,null|nullable|string|max:10',
            'new_address.latitude'        => 'nullable|numeric',
            'new_address.longitude'       => 'nullable|numeric',
            'courier_code'               => 'required|string',
            'courier_cost'               => 'nullable|numeric|min:0',
            'payment_method'             => 'required|string|in:R_Pay,QRIS,BCA,BNI,BRI,Mandiri,Indomaret,Alfamart',
            'referral_code'              => 'nullable|string|max:60',
            'notes'                      => 'nullable|string',
        ]);

        // Urutan pengisian ditegakkan juga di server. Penguncian di halaman
        // hanya membantu tampilan dan bisa dilewati lewat permintaan langsung.
        if (! $this->kontakLengkap()) {
            return redirect()->route('checkout.index')
                ->with('error', 'Lengkapi data kontak terlebih dahulu sebelum melanjutkan.');
        }

        $cart = $this->cartService->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja Anda kosong.');
        }

        $cart->load(['items.product.activeDiscount', 'items.productVariant.activeDiscount']);

        // Yang dibayar hanya yang dicentang. Diperiksa lagi di sini, bukan
        // hanya di index(), karena permintaan bisa datang langsung tanpa
        // melewati halamannya.
        $itemDibayar = $cart->items->where('dipilih', true);

        if ($itemDibayar->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Pilih dulu barang yang mau dibayar dengan mencentangnya.');
        }

        // Tentukan alamat pengiriman yang dipakai
        $address = null;
        if ($request->filled('address_id')) {
            $address = Address::where('user_id', Auth::id())->findOrFail($request->address_id);
        } else {
            $address = Address::create([
                'user_id'        => Auth::id(),
                'label'          => $request->input('new_address.label', 'Alamat Utama'),
                'recipient_name'  => $request->input('new_address.recipient_name'),
                'phone'          => $request->input('new_address.phone'),
                'address_line'    => $request->input('new_address.address_line'),
                'city'           => $request->input('new_address.city'),
                'province'       => $request->input('new_address.province'),
                'postal_code'    => $request->input('new_address.postal_code'),
                'latitude'       => $request->input('new_address.latitude', -7.2575),
                'longitude'      => $request->input('new_address.longitude', 112.7521),
                'is_default'     => Auth::user()->addresses->isEmpty(),
            ]);

            // Jika user belum punya HP di profile, update otomatis
            if (empty(Auth::user()->phone) || Auth::user()->phone === 'Belum diatur') {
                Auth::user()->update(['phone' => $request->input('new_address.phone')]);
            }
        }

        // Hitung ulang ongkir berdasarkan koordinat & kota alamat
        $destLat  = (float) ($address->latitude ?? -7.2575);
        $destLng  = (float) ($address->longitude ?? 112.7521);
        $destCity = $address->city;
        $destZip  = $address->postal_code;

        $availableCouriers = $this->shippingService->calculate($destLat, $destLng, $cart->jumlah_terpilih, $destCity, $destZip);

        $selectedCourier = collect($availableCouriers)->firstWhere('code', $request->courier_code);

        // Jika pembeli mencoba memilih kurir instan padahal tidak tersedia (misal ke Mojokerto)
        if (!$selectedCourier) {
            if (str_contains(strtolower($request->courier_code), 'instant') || str_contains(strtolower($request->courier_code), 'gojek') || str_contains(strtolower($request->courier_code), 'grab')) {
                return back()->withInput()->with('error', 'Pengiriman instan hanya melayani area Kota Surabaya (maksimal 15 KM). Alamat pengiriman Anda (' . ($destCity ?: 'Luar Surabaya') . ') berada di luar area instan, silakan pilih pengiriman reguler (JNE, J&T, SiCepat, dll).');
            }
        }
        /*
         * Kurir yang dipilih HARUS ada di daftar yang baru saja dihitung peladen
         * untuk alamat ini.
         *
         * Sebelumnya, kode kurir yang tidak dikenali membuat ongkirnya diambil
         * dari `courier_cost` kiriman pembeli. Nilai itu berasal dari halaman,
         * dan apa pun yang berasal dari halaman bisa diubah orang: cukup kirim
         * kode kurir asing bersama courier_cost=0, dan ongkirnya menjadi nol
         * sementara tarif kurir yang sesungguhnya tetap ditanggung toko.
         *
         * Sekarang permintaannya ditolak. Lebih baik pembeli memilih ulang
         * kurirnya daripada toko menanggung ongkir yang tidak pernah dibayar.
         */
        if (! $selectedCourier) {
            Log::warning('Kurir di luar daftar ditolak saat checkout', [
                'pengguna'    => Auth::id(),
                'kode_kurir'  => $request->courier_code,
                'kota_tujuan' => $destCity,
            ]);

            return back()->withInput()->with('error',
                'Pilihan kurirnya sudah tidak berlaku untuk alamat ini. '
                . 'Silakan pilih ulang jasa kirimnya.');
        }

        $courierCost = (int) $selectedCourier['cost'];
        $courierName = $selectedCourier['name'];

        // Kode referal diperiksa ULANG di sini.
        $referral = app(ReferralService::class);
        $cekKode  = $referral->periksa($request->input('referral_code'), Auth::user());

        $kodeReferal = null;
        $pemilikKode = null;
        $diskon      = 0.0;
        $komisi      = 0.0;

        if ($cekKode['sah']) {
            $kodeReferal = $referral->rapikan($request->input('referral_code'));
            $pemilikKode = $cekKode['pemilik']->id;
            $diskon      = $referral->diskon((float) $cart->total_terpilih);
            $komisi      = $referral->komisi((float) $cart->total_terpilih);
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id'          => Auth::id(),
                'order_number'     => Order::generateOrderNumber(),
                'total_price'      => $cart->total_terpilih,
                'shipping_cost'    => $courierCost,
                'shipping_actual_cost'    => $selectedCourier ? (int)($selectedCourier['cost_actual'] ?? $courierCost) : (int)$courierCost,
                'shipping_markup_profit'  => $selectedCourier ? max(0, $courierCost - (int)($selectedCourier['cost_actual'] ?? $courierCost)) : 0,
                'grand_total'      => $cart->total_terpilih - $diskon + $courierCost,
                'referral_code_used'  => $kodeReferal,
                'referrer_id'         => $pemilikKode,
                'referral_discount'   => $diskon,
                'referral_commission' => $komisi,
                'status'           => 'pending',
                'shipping_address' => $address->toSnapshot(),
                'courier'          => $courierName,

                // Kode resmi dari Biteship disimpan apa adanya, misalnya "jne:reg" atau "gojek:instant".
                'courier_code'     => $selectedCourier['code'] ?? $request->courier_code,
                'payment_method'   => $request->payment_method,
                'payment_status'   => $request->payment_method === 'COD' ? 'unpaid' : 'unpaid',
                'notes'            => $request->notes,
            ]);

            foreach ($itemDibayar as $item) {
                $variantInfo = $item->productVariant ? $item->productVariant->variant_info : null;

                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name'       => $item->product->name,
                    'variant_info'       => $variantInfo,
                    'quantity'           => $item->quantity,
                    'price'              => $item->unit_price,
                ]);

                // Kurangi stok
                if ($item->productVariant) {
                    $item->productVariant->decrement('stock', $item->quantity);
                }
                $item->product->decrement('stock', $item->quantity);
            }

            // Pembayaran memakai saldo R_Pay.
            if ($order->payment_method === 'R_Pay') {
                app(RpayService::class)->debit(
                    Auth::id(),
                    (float) $order->grand_total,
                    'checkout',
                    'Pembayaran pesanan ' . $order->order_number,
                    $order,
                    Auth::id()
                );

                // Nomor invoice terbit sendiri lewat event saving() di model Order.
                $order->update([
                    'payment_status' => 'paid',
                    'status'         => 'processing',
                ]);

                CatatAktivitas::tulis(
                    'rpay',
                    'membayar pesanan dengan R_Pay: ' . $order->order_number,
                    $order,
                    ['nominal' => (float) $order->grand_total],
                    'updated'
                );
            }

            // Yang dibuang dari keranjang hanya barang yang benar-benar dibayar.
            $cart->items()->whereIn('id', $itemDibayar->pluck('id'))->delete();

            DB::commit();

            // Redirect ke halaman pembayaran (payment instruction / Midtrans)
            
        // Kirim email invoice lunas hanya jika pesanan langsung lunas (mis. pembayaran R_Pay)
        if ($order->payment_status === 'paid') {
            try {
                $recipientEmail = $order->shipping_address['email'] ?? $order->user?->email;
                if (!empty($recipientEmail)) {
                    Mail::to($recipientEmail)->send(new OrderInvoiceMail($order));
                }
            } catch (\Throwable $mailErr) {
                Log::error('Gagal mengirim email invoice lunas: ' . $mailErr->getMessage());
            }
        }

        return redirect()->route('checkout.payment', $order->order_number);

        } catch (SaldoTidakCukup $e) {
            // Keadaan wajar, bukan kegagalan sistem: beri tahu apa adanya.
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pesanan Anda: ' . $e->getMessage());
        }
    }

    /**
     * Halaman instruksi & eksekusi pembayaran (Midtrans Snap & COD).
     */
    public function payment($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->with(['items.product', 'items.productVariant'])
            ->firstOrFail();

        // Pesanan yang dibayar R_Pay sudah lunas sejak dibuat, jadi tidak ada
        // yang perlu dibayar di halaman ini — pembeli langsung diantar ke
        // pesanannya, lengkap dengan invoice yang sudah terbit.
        if ($order->payment_method === 'R_Pay' && $order->payment_status === 'paid') {
            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Pembayaran dengan R_Pay berhasil. Pesananmu langsung diproses.');
        }

        $snapToken = null;
        $snapError = null;

        // Jika bukan COD dan status masih unpaid, minta Snap Token Midtrans
        if ($order->payment_method !== 'COD' && $order->payment_status === 'unpaid') {
            $midtransRes = $this->midtransService->createSnapToken($order);

            if ($midtransRes['success']) {
                $snapToken = $midtransRes['token'];
            } else {
                $snapError = $midtransRes['message'] ?? 'Gagal memuat sistem pembayaran.';
            }
        }

        $clientKey = $this->midtransService->getClientKey();
        $isProduction = $this->midtransService->isProduction();

        return view('checkout.payment', compact('order', 'snapToken', 'snapError', 'clientKey', 'isProduction'));
    }

    /**
     * Halaman penyelesaian pesanan: customer klik "Konfirmasi Saya Sudah Bayar".
     * Set payment_status = pending_verification agar admin bisa verifikasi transfer.
     */
    public function paymentFinish($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Hanya update jika masih unpaid (hindari double update)
        if ($order->payment_status === 'unpaid') {
            $order->update(['payment_status' => 'pending_verification']);
        }

        return redirect()->route('orders.show', $order->order_number)
            ->with('success', 'Terima kasih! Pembayaran Anda sedang kami verifikasi. Pesanan akan segera diproses setelah pembayaran dikonfirmasi oleh admin.');
    }

    /**
     * API Response AJAX: Polling cek status pembayaran pesanan secara real-time.
     */
    public function paymentStatus($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        // Jika masih unpaid, tanyakan langsung ke API Midtrans
        if ($order->payment_status === 'unpaid') {
            $midtransRes = $this->midtransService->checkStatus($order->order_number);
            $trxStatus   = $midtransRes['transaction_status'] ?? '';
            $fraudStatus = $midtransRes['fraud_status'] ?? 'accept';

            if ($trxStatus === 'settlement' || ($trxStatus === 'capture' && $fraudStatus === 'accept')) {
                $order->payment_status = 'paid';
                if (in_array($order->status, ['pending', 'unpaid'])) {
                    $order->status = 'processing';
                }
                $order->save();
            } elseif (in_array($trxStatus, ['cancel', 'deny', 'expire'])) {
                $order->payment_status = 'failed';
                $order->save();
            }
        }

        return response()->json([
            'status'         => 'success',
            'order_number'   => $order->order_number,
            'payment_status' => $order->payment_status,
            'order_status'   => $order->status,
        ]);
    }

    /**
     * Webhook/Callback dari Midtrans (dipanggil server-to-server oleh Midtrans).
     */
    public function midtransCallback(Request $request)
    {
        /*
         * Yang dicatat hanya penanda pesanannya, bukan seluruh muatan.
         * Muatan penuh berisi nomor Virtual Account dan keterangan pembayaran
         * pembeli — data yang tidak perlu tersimpan selamanya di berkas catatan
         * yang biasanya dibaca lebih banyak orang daripada basis data.
         */
        Log::info('Notifikasi Midtrans diterima', [
            'pesanan' => $request->input('order_id'),
            'status'  => $request->input('transaction_status'),
        ]);

        $verified = $this->midtransService->verifyCallback($request);

        if (!$verified['valid']) {
            return response()->json(['status' => 'error', 'message' => $verified['message']], 400);
        }

        $order = Order::where('order_number', $verified['order_number'])->first();
        if (!$order) {
            // Jika order_id memiliki akhiran timestamp unik dari retry Snap (mis. ORD-XXXX-1725100000)
            $baseOrderNumber = preg_replace('/-\d{9,}$/', '', (string) $verified['order_number']);
            $order = Order::where('order_number', $baseOrderNumber)->first();
        }

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        /*
         * Nominalnya harus sama dengan tagihan pesanan.
         *
         * Nilai ini ikut ditandatangani Midtrans sehingga tidak bisa dipalsukan,
         * tetapi mencocokkannya tetap perlu: kalau suatu saat tagihan pesanan
         * berubah setelah tautan pembayaran terbit, notifikasi lama tidak boleh
         * melunasi tagihan yang baru.
         */
        $tagihan = round((float) $order->grand_total, 2);
        $dibayar = round((float) $verified['gross_amount'], 2);

        if ($verified['payment_status'] === 'paid' && abs($tagihan - $dibayar) > 0.01) {
            Log::warning('Nominal notifikasi Midtrans tidak sama dengan tagihan', [
                'pesanan' => $order->order_number,
                'tagihan' => $tagihan,
                'dibayar' => $dibayar,
            ]);

            return response()->json(['status' => 'error', 'message' => 'Amount mismatch'], 409);
        }

        /*
         * Pesanan yang sudah lunas tidak boleh turun status.
         *
         * Notifikasi Midtrans tidak bernomor urut dan bisa datang terlambat
         * atau dikirim ulang. Notifikasi "expire" lama yang tiba setelah
         * pembayaran berhasil akan menandai pesanan yang sudah dibayar menjadi
         * gagal. Hanya pengembalian dana dan pembatalan resmi yang boleh
         * mengubah pesanan lunas.
         */
        $turunStatus = $order->payment_status === 'paid'
            && in_array($verified['payment_status'], ['unpaid', 'failed'], true);

        if ($turunStatus) {
            Log::warning('Notifikasi Midtrans yang menurunkan status pesanan lunas diabaikan', [
                'pesanan'  => $order->order_number,
                'diminta'  => $verified['payment_status'],
                'transaksi' => $verified['transaction_status'],
            ]);

            return response()->json(['status' => 'OK', 'message' => 'Ignored: stale notification']);
        }

        // Update payment_status & order_status
        $order->payment_status = $verified['payment_status'];

        if ($verified['order_status']) {
            $order->status = $verified['order_status'];
        }

        // Selaraskan metode pembayaran dengan kenyataan.
        if (! empty($verified['metode']) && $verified['metode'] !== $order->payment_method) {
            Log::info('Metode pembayaran diselaraskan dengan notifikasi Midtrans', [
                'pesanan'   => $order->order_number,
                'sebelumnya' => $order->payment_method,
                'menjadi'   => $verified['metode'],
            ]);

            $order->payment_method = $verified['metode'];
        }

        $order->save();

                // Kirim email invoice resmi lunas ke pembeli saat pembayaran sukses
        if ($order->payment_status === 'paid') {
            $this->kirimEmailInvoice($order);
        }

        return response()->json(['status' => 'OK']);
    }
    /**
     * Kirim email invoice resmi lunas dengan lampiran PDF ke pembeli.
     */
    protected function kirimEmailInvoice(Order $order): void
    {
        try {
            $order->refresh()->loadMissing(['items.product', 'items.productVariant', 'user']);

            $recipientEmail = $order->shipping_address['email']
                ?? ($order->user?->email ?: null);

            if (empty($recipientEmail)) {
                Log::warning('Gagal kirim email invoice: email penerima kosong', ['order' => $order->order_number]);
                return;
            }

            // Cegah pengiriman ganda via Cache lock
            $cacheKey = 'invoice_email_sent_' . $order->id;
            if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(24));
                Mail::to($recipientEmail)->send(new OrderInvoiceMail($order));
                Log::info('Email invoice resmi lunas berhasil dikirim ke pembeli', [
                    'pesanan' => $order->order_number,
                    'tujuan'  => $recipientEmail,
                ]);
            }
        } catch (\Throwable $mailErr) {
            Log::error('Gagal mengirim email invoice lunas: ' . $mailErr->getMessage(), [
                'order' => $order->order_number,
            ]);
        }
    }
}
