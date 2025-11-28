<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Streak Reminder</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; background: #f8fafc; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; }
        .content { padding: 40px 30px; }
        .streak-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin: 32px 0; border-left: 4px solid #667eea; }
        .button { display: inline-block; background: #667eea; color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; }
        .footer { text-align: center; padding: 24px; color: #64748b; font-size: 14px; border-top: 1px solid #e2e8f0; background: #f8fafc; }
        .stats { display: flex; justify-content: space-around; text-align: center; margin: 20px 0; }
        .stat-item { flex: 1; padding: 0 15px; }
        .stat-number { font-size: 2.5rem; font-weight: bold; color: #667eea; margin-bottom: 5px; }
        .stat-label { font-size: 0.875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .forgiveness-note { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 16px; margin: 20px 0; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; font-size: 2rem; font-weight: 700;">🔥 Keep Your Streak Alive!</h1>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $name }}</strong>,</p>
            <p>Your consistent learning is impressive! Here's where you stand:</p>
            <div class="streak-card">
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
            </div>
            <p>Just a few minutes of learning today will keep your <strong>{{ $currentStreak }}-day streak</strong> going strong!</p>
            <div style="text-align: center; margin: 40px 0;">
                <a href="{{ url('/') }}" class="button" style="color: white; text-decoration: none;">Continue Learning Now</a>
            </div>
        </div>
        <div class="footer">
            <p>You're receiving this because you're an active MyAcademy learner.</p>
        </div>
    </div>
</body>
</html>