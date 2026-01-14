<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Streak Reminder</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 0;
            color: #334155;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
        }
        .header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #ffffff;
            padding: 36px 24px;
            text-align: center;
        }
        .content {
            padding: 32px 24px;
        }
        .streak-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin: 32px 0;
            padding: 24px;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 8px;
        }
        .stat-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .button {
            background: #667eea;
            color: #ffffff !important;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            display: inline-block;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 14px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }
    </style>
</head>
<body>

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table class="container" width="600" cellpadding="0" cellspacing="0">
                
                <!-- Header -->
                <tr>
                    <td class="header">
                        <h1 style="margin:0; font-size:24px;">🔥 Keep Your Streak Alive!</h1>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td class="content">
                        <p>Hello <strong>{{ $name }}</strong>,</p>

                        <p>Your consistent learning is impressive! Here's where you stand:</p>

                        <!-- Streak Card -->
                        <table width="100%" cellpadding="0" cellspacing="0" class="streak-card">
                            <tr>
                                <td align="center" width="50%">
                                    <div class="stat-number">{{ $currentStreak }}</div>
                                    <div class="stat-label">Current Streak</div>
                                </td>
                                <td align="center" width="50%">
                                    <div class="stat-number">{{ $longestStreak }}</div>
                                    <div class="stat-label">Longest Streak</div>
                                </td>
                            </tr>
                        </table>

                        <p>
                            Just a few minutes of learning today will keep your
                            <strong>{{ $currentStreak }}-day streak</strong> going strong!
                        </p>

                        <div style="text-align:center; margin: 32px 0;">
                            <a href="{{ config('app.frontend_url').'/learning' }}" class="button">
                                Continue Learning Now
                            </a>
                        </div>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td class="footer">
                        <p>You're receiving this because you're an active MyAcademy learner.</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
