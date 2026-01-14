<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Email Verified</title>
    <style>
        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; padding: 20px !important; }
            .button { width: 100% !important; text-align: center !important; }
        }
    </style>
</head>
<body style="font-family: 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #f4f7f9; margin: 0; padding: 0;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="570" class="container" style="background-color: #ffffff; border-radius: 8px; border: 1px solid #e1e8ed; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    
                    <tr>
                        <td align="center" style="padding: 40px 40px 0 40px;">
                            <div style="background-color: #ecfdf5; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                                <span style="color: #10b981; font-size: 32px; line-height: 64px;">✓</span>
                            </div>
                            <h1 style="color: #1a1a1a; font-size: 24px; font-weight: 700; margin: 0;">Verification Complete!</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 24px 40px; text-align: center;">
                            <p style="color: #4b5563; font-size: 16px; line-height: 24px; margin: 0;">
                                Hi {{ $name }}, <br>
                                Your email address has been successfully verified. You now have full access to your account and all of our features.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 10px 40px 40px 40px;">
                            <a href="{{ $url }}" class="button" style="background-color: #2563eb; border-radius: 6px; color: #ffffff; display: inline-block; font-size: 16px; font-weight: 600; line-height: 50px; text-align: center; text-decoration: none; width: 250px; -webkit-text-size-adjust: none;">
                                Go to Dashboard
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 40px 40px 40px; text-align: center;">
                            <hr style="border: none; border-top: 1px solid #f3f4f6; margin-bottom: 30px;">
                            <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                If you have any questions, feel free to reply to this email.
                            </p>
                            <p style="color: #9ca3af; font-size: 12px; margin: 10px 0 0 0;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>