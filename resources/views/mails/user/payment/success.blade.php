@extends('mails.app')

@section('content')
    <div style="font-family: 'Noto Sans JP', sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="font-size: 24px; font-weight: 600; color: #333; margin-bottom: 20px; border-bottom: 2px solid #416FED; padding-bottom: 10px;">
            決済が完了しました
        </h1>

        <p style="font-size: 16px; margin-bottom: 20px;">
            {{ $mailInfo['name'] ?? '' }} 様
        </p>

        <p style="font-size: 16px; margin-bottom: 20px;">
            この度は、名古屋市ゴミ収集サイトをご利用いただき、誠にありがとうございます。<br>
            決済が正常に完了いたしました。
        </p>

        <div style="background-color: #f5f5f5; border-left: 4px solid #416FED; padding: 20px; margin: 20px 0;">
            <h2 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">
                決済情報
            </h2>

            @if (isset($mailInfo['order_id']))
                <p style="font-size: 16px; margin-bottom: 10px;">
                    <strong>注文番号:</strong> {{ $mailInfo['order_id'] }}
                </p>
            @endif

            @if (isset($mailInfo['payment_date']))
                <p style="font-size: 16px; margin-bottom: 10px;">
                    <strong>決済日時:</strong> {{ $mailInfo['payment_date'] }}
                </p>
            @endif

            @if (isset($mailInfo['payment_amount']))
                <p style="font-size: 16px; margin-bottom: 10px;">
                    <strong>決済金額:</strong> <span style="font-size: 20px; font-weight: 600; color: #416FED;">{{ number_format($mailInfo['payment_amount']) }}円</span>
                </p>
            @endif

            @if (isset($mailInfo['payment_method']))
                <p style="font-size: 16px; margin-bottom: 10px;">
                    <strong>支払い方法:</strong> {{ $mailInfo['payment_method'] }}
                </p>
            @endif
        </div>

        @if (isset($mailInfo['collection_date']))
            <div style="background-color: #e7f3ff; border-left: 4px solid #416FED; padding: 20px; margin: 20px 0;">
                <h2 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">
                    収集日
                </h2>
                <p style="font-size: 16px; margin-bottom: 10px;">
                    <strong>収集予定日:</strong> {{ $mailInfo['collection_date'] }}
                </p>
            </div>
        @endif

        @if (isset($mailInfo['selected_items']) && is_array($mailInfo['selected_items']) && count($mailInfo['selected_items']) > 0)
            <div style="background-color: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 4px;">
                <h2 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">
                    選択品目
                </h2>
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #ddd;">
                            <th style="padding: 10px; text-align: left; font-size: 14px;">品目名</th>
                            <th style="padding: 10px; text-align: right; font-size: 14px;">数量</th>
                            <th style="padding: 10px; text-align: right; font-size: 14px;">金額</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mailInfo['selected_items'] as $item)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px; font-size: 14px;">{{ $item['name'] ?? '' }}</td>
                                <td style="padding: 10px; text-align: right; font-size: 14px;">{{ $item['quantity'] ?? 0 }}個</td>
                                <td style="padding: 10px; text-align: right; font-size: 14px;">{{ number_format($item['line_amount'] ?? 0) }}円</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f8f9fa; border-top: 2px solid #ddd; font-weight: 600;">
                            <td style="padding: 10px; font-size: 16px;" colspan="2">合計</td>
                            <td style="padding: 10px; text-align: right; font-size: 16px; color: #416FED;">
                                {{ number_format($mailInfo['total_amount'] ?? 0) }}円
                                @if (isset($mailInfo['total_quantity']))
                                    <span style="font-size: 14px; font-weight: normal; color: #666;">({{ $mailInfo['total_quantity'] }}個)</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <div style="background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; padding: 15px; margin: 20px 0;">
            <p style="font-size: 14px; color: #155724; margin: 0;">
                <strong>✓ 決済完了:</strong> お支払いが正常に完了しました。受付番号は別途メールでお送りいたします。
            </p>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
            <p style="font-size: 14px; color: #666; margin: 0;">
                ご不明な点がございましたら、お気軽にお問い合わせください。<br>
                今後とも名古屋市ゴミ収集サイトをよろしくお願いいたします。
            </p>
        </div>
    </div>
@endsection

