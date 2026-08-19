<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="margin:0;padding:0;background:#F3F4F6;font-family:'Inter',Arial,sans-serif;">
    <div style="max-width:480px;margin:0 auto;padding:40px 20px;">
        <div style="text-align:center;margin-bottom:24px;">
            <span style="font-size:26px;font-weight:900;letter-spacing:-0.5px;text-transform:uppercase;color:#1B3A6B;">Record</span>
        </div>

        <div style="background:#ffffff;border:1px solid #E5E7EB;border-radius:4px;padding:36px 24px;text-align:center;">
            <p style="font-size:14px;color:#6B7280;margin:0 0 4px;">Kode verifikasi reset password Anda:</p>

            <div style="font-size:38px;font-weight:800;letter-spacing:14px;color:#1B3A6B;margin:20px 0;font-family:'Courier New',monospace;">
                {{ $code }}
            </div>

            <p style="font-size:12px;color:#9CA3AF;margin:16px 0 0;">
                Kode ini berlaku selama 10 menit. Jangan berikan kode ini kepada siapa pun, termasuk pihak yang mengaku dari Record.
            </p>
        </div>

        <p style="text-align:center;font-size:12px;color:#9CA3AF;margin-top:24px;">
            Jika Anda tidak meminta reset password, abaikan email ini.
        </p>
    </div>
</body>
</html>
