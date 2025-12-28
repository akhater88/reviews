<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مبروك! فزت في مسابقة أفضل مطعم</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #f97316, #eab308); padding: 40px 30px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 28px; }
        .header .emoji { font-size: 60px; margin-bottom: 20px; }
        .content { padding: 30px; }
        .prize-box { background: #fef3c7; border: 2px solid #f59e0b; border-radius: 12px; padding: 20px; text-align: center; margin: 20px 0; }
        .prize-amount { font-size: 36px; font-weight: bold; color: #d97706; }
        .claim-code { background: #1f2937; color: white; padding: 15px 25px; border-radius: 8px; font-family: monospace; font-size: 20px; display: inline-block; margin: 15px 0; letter-spacing: 2px; }
        .btn { display: inline-block; background: #f97316; color: white; padding: 15px 40px; border-radius: 8px; text-decoration: none; font-weight: bold; margin: 20px 0; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 14px; }
        .steps { text-align: right; margin: 20px 0; }
        .steps li { margin: 10px 0; padding: 10px; background: #f3f4f6; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="emoji">🎉</div>
            <h1>مبروك {{ $participant->name ?? 'المشارك' }}!</h1>
            @if($data['winner_type'] === 'lottery')
                <p>لقد فزت في سحب مسابقة أفضل مطعم!</p>
            @else
                <p>المطعم الذي رشحته فاز بالمركز {{ $data['rank'] == 1 ? 'الأول 🥇' : ($data['rank'] == 2 ? 'الثاني 🥈' : 'الثالث 🥉') }}!</p>
            @endif
        </div>

        <div class="content">
            @if($data['winner_type'] === 'lottery')
                <div class="prize-box">
                    <p style="margin: 0; color: #92400e;">جائزتك</p>
                    <div class="prize-amount">{{ $data['prize_amount'] }} ر.س</div>
                </div>

                <h3>🍽️ المطعم الذي رشحته:</h3>
                <p style="font-size: 18px; font-weight: bold;">{{ $data['branch_name'] }}</p>

                @if($data['claim_code'])
                    <h3>📋 كود الاستلام:</h3>
                    <div style="text-align: center;">
                        <div class="claim-code">{{ $data['claim_code'] }}</div>
                    </div>

                    <h3>لاستلام جائزتك:</h3>
                    <ol class="steps">
                        <li>احتفظ بكود الاستلام أعلاه</li>
                        <li>اضغط على الزر أدناه</li>
                        <li>أدخل بياناتك البنكية لتحويل المبلغ</li>
                    </ol>

                    <div style="text-align: center;">
                        <a href="{{ $data['claim_url'] }}" class="btn">استلم جائزتك الآن 🎁</a>
                    </div>

                    <p style="color: #dc2626; text-align: center;">
                        ⚠️ يجب استلام الجائزة خلال 30 يوماً من تاريخ الإعلان
                    </p>
                @endif
            @else
                <h3>🍽️ المطعم الفائز:</h3>
                <p style="font-size: 20px; font-weight: bold;">{{ $data['branch_name'] }}</p>

                <div class="prize-box">
                    <p style="margin: 0; color: #92400e;">جائزة المطعم</p>
                    <div class="prize-amount">{{ $data['prize_amount'] }} ر.س</div>
                </div>

                <p style="font-size: 16px; text-align: center;">
                    أنت جزء من نجاح هذا المطعم! شكراً لاختيارك الموفق 👏
                </p>
            @endif
        </div>

        <div class="footer">
            <p>شكراً لمشاركتك في مسابقة TABsense!</p>
            <p>تابعنا للمزيد من المسابقات القادمة 🚀</p>
            <p style="margin-top: 15px;">© {{ date('Y') }} TABsense. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</body>
</html>
