<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録が完了しました</title>
</head>

<body
    style="font-family: 'Hiragino Sans', 'Hiragino Kaku Gothic ProN', 'Meiryo', sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 8px;">
        <h1 style="color: #416FED; margin-bottom: 20px; font-size: 24px;">会員登録が完了しました</h1>

        <p style="margin-bottom: 20px;">{{ $mailInfo['name'] ?? '' }} 様</p>

        <p style="margin-bottom: 20px;">
            この度は、名古屋市ゴミ収集サイトにご登録いただき、誠にありがとうございます。<br>
            決済が完了し、会員登録が完了いたしました。
        </p>

        <div style="background-color: #fff; padding: 20px; border-radius: 4px; border: 1px solid #ddd; margin: 20px 0;">
            <h2 style="color: #E20000; font-size: 18px; margin-bottom: 15px;">ログイン情報</h2>
            <p style="margin-bottom: 10px;"><strong>メールアドレス:</strong> {{ $mailInfo['email'] ?? '' }}</p>
            <p style="margin-bottom: 10px;"><strong>電話番号:</strong> {{ $mailInfo['phone'] ?? '' }}</p>
            <p style="margin-bottom: 10px;"><strong>仮パスワード:</strong> <span
                    style="font-family: monospace; font-size: 16px; background-color: #f0f0f0; padding: 5px 10px; border-radius: 4px;">{{ $mailInfo['password'] ?? '' }}</span>
            </p>
        </div>

        <div
            style="background-color: #fff3cd; padding: 15px; border-radius: 4px; border-left: 4px solid #ffc107; margin: 20px 0;">
            <p style="margin: 0; color: #856404;"><strong>【重要】</strong> セキュリティのため、初回ログイン後は必ずパスワードを変更してください。</p>
        </div>

        <p style="margin-top: 30px; margin-bottom: 10px;">
            <a href="{{ route('user.login', ['email' => $mailInfo['email'], 'phone' => $mailInfo['phone']]) }}"
                style="display: inline-block; background-color: #416FED; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold;">ログインページへ</a>
        </p>

        <p style="margin-top: 30px; font-size: 14px; color: #666;">
            このメールは自動送信されています。心当たりがない場合は、このメールを無視してください。<br>
            ご不明な点がございましたら、お問い合わせください。
        </p>
    </div>
</body>

</html>
