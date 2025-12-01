<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Streak Reset</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; background: #f8fafc; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 40px 30px; text-align: center; }
        .content { padding: 40px 30px; }
        .reset-card { background: #fef2f2; border: 2px solid #ef4444; border-radius: 12px; padding: 24px; margin: 32px 0; text-align: center; }
        .button { display: inline-block; background: #ef4444; color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; }
        .footer { text-align: center; padding: 24px; color: #64748b; font-size: 14px; border-top: 1px solid #e2e8f0; background: #f8fafc; }
        .achievement { background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; font-size: 2rem; font-weight: 700;">😔 Streak Reset</h1>
            <p style="margin: 8px 0 0 0; opacity: 0.9;">Time for a fresh start!</p>
        </div>
        
        <div class="content">
            <p style="font-size: 16px; margin-bottom: 20px;">Hello <strong style="color: #1e293b;">{{ $name }}</strong>,</p>
            
            <div class="reset-card">
                <h3 style="margin-top: 0; color: #dc2626;">🔥 Streak Ended: {{ $previousStreak }} Days</h3>
                <p style="color: #dc2626; margin-bottom: 0; font-size: 18px;">
                    You missed two consecutive days, so your streak has been reset.
                </p>
            </div>

            @if($previousStreak >= 7)
            <div class="achievement">
                <h4 style="margin: 0 0 10px 0; color: #0ea5e9;">🎉 Amazing Achievement!</h4>
                <p style="margin: 0; color: #0369a1;">
                    You maintained a <strong>{{ $previousStreak }}-day streak</strong> - that's incredible! 
                    Your longest streak remains at <strong>{{ $longestStreak }} days</strong>.
                </p>
            </div>
            @endif

            <p style="font-size: 16px; color: #475569; margin-bottom: 30px;">
                Every great journey has setbacks. What matters is getting back on track! 
                Start your new streak today and build an even longer one. 💪
            </p>

            <div style="text-align: center; margin: 40px 0;">
                <a href="{{ url('/') }}" class="button" style="color: white; text-decoration: none;">Start New Streak</a>
            </div>

            <p style="color: #475569; font-size: 16px; text-align: center;">
                Remember: Consistency is key. You've got this!<br>
                <strong style="color: #334155;">The MyAcademy Team</strong>
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0 0 16px 0;">Every ending is a new beginning.</p>
            <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                © {{ date('Y') }} MyAcademy. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>