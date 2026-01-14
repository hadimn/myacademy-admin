<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Verify Your Email</title>
    <style>
        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; padding: 20px !important; }
            .otp-code { font-size: 24px !important; letter-spacing: 4px !important; }
        }
    </style>
</head>
<body style="font-family: 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #f4f7f9; margin: 0; padding: 0;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="570" class="container" style="background-color: #ffffff; border-radius: 8px; border: 1px solid #e1e8ed; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <tr>
                        <td style="padding: 40px 40px 20px 40px; text-align: left;">
                            <h1 style="color: #1a1a1a; font-size: 24px; font-weight: 700; margin: 0;">Confirm your email address</h1>
                            <p style="color: #4b5563; font-size: 16px; line-height: 24px; margin-top: 16px;">
                                Hello {{ $name }}, <br>
                                Use the verification code below to complete your registration. This code helps us keep your account secure.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 20px 40px;">
                            <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 24px; text-align: center;">
                                <span style="display: block; color: #64748b; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Verification Code</span>
                                <strong class="otp-code" style="color: #2563eb; font-size: 36px; font-family: monospace; letter-spacing: 8px;">{{ $otp }}</strong>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 20px 40px 40px 40px;">
                            <p style="color: #6b7280; font-size: 14px; line-height: 20px; margin: 0;">
                                This code will expire in <strong>{{ $expires }} minutes</strong>. If you did not request this email, you can safely ignore it.
                            </p>
                            <hr style="border: none; border-top: 1px solid #f3f4f6; margin: 30px 0;">
                            <p style="color: #9ca3af; font-size: 12px; text-align: center; margin: 0;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>