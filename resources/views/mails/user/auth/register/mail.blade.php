@extends('mails.app')

@section('content')
    <div
        style="font-family: 'Noto Sans JP', sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1
            style="font-size: 24px; font-weight: 600; color: #333; margin-bottom: 20px; border-bottom: 2px solid #ED4141; padding-bottom: 10px;">
            新規申込みの受付が完了しました
        </h1>

        <p style="font-size: 16px; margin-bottom: 20px;">
            この度は、名古屋市ゴミ収集サイトにご登録いただき、誠にありがとうございます。
        </p>

        <p style="font-size: 16px; margin-bottom: 20px;">
            新規申込みの受付が完了いたしました。以下の情報でログインできます。
        </p>

        <div style="background-color: #f5f5f5; border-left: 4px solid #ED4141; padding: 20px; margin: 20px 0;">
            <h2 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">
                ログイン情報
            </h2>

            @if (isset($mailInfo['email']))
                <p style="font-size: 16px; margin-bottom: 10px;">
                    <strong>メールアドレス:</strong> {{ $mailInfo['email'] }}
                </p>
            @endif

            <p style="font-size: 16px; margin-bottom: 10px;">
                <strong>パスワード:</strong> <span
                    style="font-family: monospace; background-color: #fff; padding: 5px 10px; border: 1px solid #ddd; display: inline-block; font-weight: 600; color: #ED4141;">{{ $mailInfo['password'] ?? '' }}</span>
            </p>
        </div>

        <div
            style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 15px; margin: 20px 0;">
            <p style="font-size: 14px; color: #856404; margin: 0;">
                <strong>⚠️ 重要:</strong> セキュリティのため、初回ログイン後はパスワードの変更をお願いいたします。
            </p>
        </div>

        <div style="margin: 30px 0;">
            <p style="font-size: 16px; margin-bottom: 15px;">
                以下のリンクからログインページにアクセスできます：
            </p>
            <a href="{{ route('user.login') }}"
                style="display: inline-block; background-color: #ED4141; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 16px;">
                ログインページへ
            </a>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
            <p style="font-size: 14px; color: #666; margin: 0;">
                ご不明な点がございましたら、お気軽にお問い合わせください。
            </p>
        </div>
    </div>
@endsection
