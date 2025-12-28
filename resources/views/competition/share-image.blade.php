<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Tajawal', Arial, sans-serif;
            width: 1200px;
            height: 630px;
            background: linear-gradient(135deg, #f97316 0%, #eab308 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .card {
            background: white;
            border-radius: 24px;
            padding: 48px;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .header {
            text-align: center;
        }
        .badge {
            display: inline-block;
            background: linear-gradient(135deg, #f97316, #eab308);
            color: white;
            padding: 8px 24px;
            border-radius: 100px;
            font-size: 18px;
            font-weight: bold;
        }
        .title {
            font-size: 48px;
            font-weight: 800;
            color: #1f2937;
            margin-top: 24px;
        }
        .content {
            display: flex;
            align-items: center;
            gap: 40px;
            flex: 1;
            padding: 40px 0;
        }
        .photo {
            width: 200px;
            height: 200px;
            border-radius: 24px;
            object-fit: cover;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .photo-placeholder {
            width: 200px;
            height: 200px;
            border-radius: 24px;
            background: #fed7aa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .info h2 {
            font-size: 36px;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .info p {
            font-size: 24px;
            color: #6b7280;
        }
        .stats {
            display: flex;
            gap: 32px;
            margin-top: 24px;
        }
        .stat {
            text-align: center;
        }
        .stat-value {
            font-size: 48px;
            font-weight: 800;
            color: #f97316;
        }
        .stat-label {
            font-size: 16px;
            color: #9ca3af;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 24px;
            border-top: 2px solid #f3f4f6;
        }
        .logo {
            font-size: 24px;
            font-weight: 800;
            color: #1f2937;
        }
        .cta {
            font-size: 20px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <span class="badge">مسابقة أفضل مطعم</span>
            <h1 class="title">رشّحت مطعمي المفضل!</h1>
        </div>

        <div class="content">
            @if($nomination->competitionBranch->photo_url)
                <img src="{{ $nomination->competitionBranch->photo_url }}" class="photo" alt="">
            @else
                <div class="photo-placeholder">🍽️</div>
            @endif

            <div class="info">
                <h2>{{ $nomination->competitionBranch->name }}</h2>
                <p>{{ $nomination->competitionBranch->city }}</p>

                <div class="stats">
                    <div class="stat">
                        <div class="stat-value">{{ number_format($nomination->competitionBranch->google_rating, 1) }}</div>
                        <div class="stat-label">التقييم</div>
                    </div>
                    @if($score && $score->rank_position)
                        <div class="stat">
                            <div class="stat-value">#{{ $score->rank_position }}</div>
                            <div class="stat-label">الترتيب</div>
                        </div>
                    @endif
                    @if($score && $score->competition_score)
                        <div class="stat">
                            <div class="stat-value">{{ number_format($score->competition_score, 0) }}</div>
                            <div class="stat-label">النقاط</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="logo">TABsense</div>
            <div class="cta">شارك أنت أيضاً واربح جوائز نقدية!</div>
        </div>
    </div>
</body>
</html>
