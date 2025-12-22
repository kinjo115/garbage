@extends('mails.app')

@section('content')
    <div style="font-family: 'Noto Sans JP', sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="font-size: 24px; font-weight: 600; color: #333; margin-bottom: 20px; border-bottom: 2px solid #416FED; padding-bottom: 10px;">
            パスワードリセット
        </h1>

        <p style="font-size: 16px; margin-bottom: 20px;">
            {{ $mailInfo['user']->email ?? '' }} 様
        </p>

        <p style="font-size: 16px; margin-bottom: 20px;">
            パスワードリセットのリクエストを受け付けました。<br>
            以下のリンクをクリックして、新しいパスワードを設定してください。
        </p>

        <div style="background-color: #f5f5f5; border-left: 4px solid #416FED; padding: 20px; margin: 20px 0;">
            <p style="font-size: 14px; color: #666; margin: 0 0 15px 0;">
                <strong>有効期限:</strong> {{ isset($mailInfo['expiresAt']) ? $mailInfo['expiresAt']->format('Y年m月d日 H:i') : '1時間' }}
            </p>
            <p style="margin: 0;">
                <a href="{{ $mailInfo['resetUrl'] ?? '#' }}"
                    style="display: inline-block; background-color: #416FED; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px;">パスワードをリセット</a>
            </p>
        </div>

        <div style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 15px; margin: 20px 0;">
            <p style="font-size: 14px; color: #856404; margin: 0;">
                <strong>【重要】</strong> このリンクは1時間のみ有効です。期限が過ぎた場合は、再度パスワードリセットをリクエストしてください。
            </p>
        </div>

        <div style="background-color: #e7f3ff; border-left: 4px solid #416FED; padding: 20px; margin: 20px 0;">
            <h2 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">
                セキュリティに関する注意事項
            </h2>
            <ul style="margin: 0; padding-left: 20px; font-size: 14px; line-height: 1.8;">
                <li style="margin-bottom: 10px;">このメールに心当たりがない場合は、無視してください。</li>
                <li style="margin-bottom: 10px;">このリンクは一度しか使用できません。</li>
                <li style="margin-bottom: 10px;">パスワードは8文字以上の半角英数字で設定してください。</li>
            </ul>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
            <p style="font-size: 14px; color: #666; margin: 0 0 15px 0;">
                ご不明な点がございましたら、お気軽にお問い合わせください。<br>
                今後とも名古屋市ゴミ収集サイトをよろしくお願いいたします。
            </p>
        </div>
    </div>
@endsection

