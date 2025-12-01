<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Don't Break Your Streak!</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; background: #f8fafc; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 40px 30px; text-align: center; }
        .content { padding: 40px 30px; }
        .warning-card { background: #fffbeb; border: 2px solid #f59e0b; border-radius: 12px; padding: 24px; margin: 32px 0; text-align: center; }
        .button { display: inline-block; background: #f59e0b; color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; }
        .footer { text-align: center; padding: 24px; color: #64748b; font-size: 14px; border-top: 1px solid #e2e8f0; background: #f8fafc; }
        .stats { display: flex; justify-content: space-around; text-align: center; margin: 20px 0; }
        .stat-item { flex: 1; padding: 0 15px; }
        .stat-number { font-size: 2.5rem; font-weight: bold; color: #f59e0b; margin-bottom: 5px; }
        .stat-label { font-size: 0.875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; font-size: 2rem; font-weight: 700;">🛡️ Don't Break Your Streak!</h1>
            <p style="margin: 8px 0 0 0; opacity: 0.9;">You used your forgiveness day</p>
        </div>
        
        <div class="content">
            <p style="font-size: 16px; margin-bottom: 20px;">Hello <strong style="color: #1e293b;">{{ $name }}</strong>,</p>
            
            <p style="font-size: 16px; color: #475569;">We noticed you missed a day of learning yesterday. Don't worry - we've applied your forgiveness day! 🛡️</p>
            
            <div class="warning-card">
                <h3 style="margin-top: 0; color: #92400e;">⚠️ Forgiveness Day Used</h3>
                <p style="color: #92400e; margin-bottom: 0;">
                    Your <strong>{{ $currentStreak }}-day streak</strong> is still alive, but this is your only free pass!
                </p>
            </div>

            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number">{{ $currentStreak }}</div>
                    <div class="stat-label">Current Streak</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $longestStreak }}</div>
                    <div class="stat-label">Longest Streak</div>
                </div>
            </div>

            <p style="font-size: 16px; color: #475569; margin-bottom: 30px;">
                <strong>Important:</strong> If you miss another day, your streak will reset to 1. Continue learning today to keep your progress safe!
            </p>

            <div style="text-align: center; margin: 40px 0;">
                <a href="{{ url('/') }}" class="button" style="color: white; text-decoration: none;">Continue Learning Now</a>
            </div>

            <p style="color: #475569; font-size: 16px; text-align: center;">
                You've worked hard for this streak - don't let it slip away!<br>
                <strong style="color: #334155;">The MyAcademy Team</strong>
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0 0 16px 0;">You're receiving this because you missed a learning day.</p>
            <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                © {{ date('Y') }} MyAcademy. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>