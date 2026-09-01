<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Segoe UI', Helvetica, Arial, sans-serif; color: #374151;">
    <div style="max-width: 500px; margin: 30px auto; padding: 0 16px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <span style="font-size: 24px; font-weight: 900; letter-spacing: 2px; text-transform: uppercase; color: #1B3A6B;">RECORD</span>
        </div>

        <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 32px 24px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
            <div style="display: inline-block; background-color: #fee2e2; color: #991b1b; font-size: 11px; font-weight: bold; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; margin-bottom: 16px;">
                Permintaan Hapus Akun
            </div>

            <h2 style="font-size: 18px; font-weight: 800; color: #111827; margin: 0 0 8px;">Halo, {{ $userName }}</h2>
            <p style="font-size: 13px; color: #4b5563; margin: 0 0 20px; line-height: 1.5;">
                Kami menerima permintaan untuk menghapus akun Anda di RECORD. Gunakan kode verifikasi di bawah ini untuk mengonfirmasi:
            </p>

            <div style="background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; padding: 18px; margin: 20px 0;">
                <span style="font-size: 36px; font-weight: 900; letter-spacing: 12px; color: #1B3A6B; font-family: monospace; display: inline-block; padding-left: 12px;">
                    {{ $code }}
                </span>
            </div>

            <p style="font-size: 12px; color: #dc2626; font-weight: 600; margin: 16px 0 6px;">
                &#9888; Kode ini hanya berlaku selama 10 menit.
            </p>
            <p style="font-size: 11px; color: #6b7280; margin: 0; line-height: 1.4;">
                Jika Anda tidak merasa melakukan permintaan penghapusan akun ini, segera abaikan email ini dan amankan kata sandi akun Anda.
            </p>
        </div>

        <div style="text-align: center; font-size: 11px; color: #9ca3af; margin-top: 24px;">
            &copy; {{ date('Y') }} RECORD Shoes. All rights reserved.
        </div>
    </div>
</body>
</html>