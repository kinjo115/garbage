<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JapanPostController extends Controller
{
    /**
     * 郵便番号・デジタルアドレスAPIを使用して住所を検索
     */
    public function searchAddress(Request $request)
    {
        $request->validate([
            'postal_code' => 'required|string|min:3',
        ]);

        // search_codeは以下が可能: 郵便番号（3桁以上）、事業所個別郵便番号、デジタルアドレス（例: "A7E-2FK2"）
        $searchCode = $request->input('postal_code');
        $page = $request->input('page', 1);
        $limit = min($request->input('limit', 10), 1000); // デフォルト10件、最大1000件
        $choikitype = $request->input('choikitype', 1); // 1: 括弧なし, 2: 括弧あり
        $searchtype = $request->input('searchtype', 1); // 1: すべて検索, 2: 事業所個別郵便番号を除外
        $ecUid = $request->input('ec_uid'); // オプション: プロバイダーのユーザーID

        $apiUrl = config('services.japan_post.api_url');

        // トークンを取得（動的に生成）
        $token = $this->getTokenFromAPI($request);

        // トークンが取得できない場合
        if (!$token) {
            Log::error('日本郵便APIトークン取得失敗', [
                'client_id' => config('services.japan_post.client_id') ? '設定済み' : '未設定',
                'secret_key' => config('services.japan_post.secret_key') ? '設定済み' : '未設定',
            ]);
            return response()->json([
                'error' => '日本郵便APIトークンを取得できませんでした。.envファイルにJAPAN_POST_CLIENT_IDとJAPAN_POST_SECRET_KEYを設定してください。'
            ], 500);
        }

        Log::info('日本郵便APIトークン取得成功', ['token_length' => strlen($token)]);

        try {
            $queryParams = [
                'page' => $page,
                'limit' => $limit,
                'choikitype' => $choikitype,
                'searchtype' => $searchtype,
            ];

            // ec_uidが提供されている場合は追加
            if ($ecUid) {
                $queryParams['ec_uid'] = $ecUid;
            }

            // API URLを構築（api_urlには既に/api/v1が含まれている）
            $endpoint = rtrim($apiUrl, '/') . '/searchcode/' . $searchCode;

            Log::info('日本郵便API検索リクエスト', [
                'endpoint' => $endpoint,
                'search_code' => $searchCode,
                'query_params' => $queryParams
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get($endpoint, $queryParams);

            if ($response->successful()) {
                $responseData = $response->json();

                // レスポンスがJSONでない場合（トークン文字列が返された場合など）
                if (is_null($responseData)) {
                    $body = $response->body();
                    Log::error('日本郵便API: JSONでないレスポンス', ['body' => substr($body, 0, 200)]);
                    return response()->json([
                        'error' => '住所データの取得に失敗しました',
                        'message' => 'APIレスポンスが無効な形式です'
                    ], 500);
                }

                return response()->json($responseData);
            }

            // エラーレスポンスの処理
            $errorBody = $response->body();
            $errorData = null;

            try {
                $errorData = json_decode($errorBody, true);
            } catch (\Exception $e) {
                // JSONでない場合はそのまま使用
            }

            Log::error('日本郵便API検索エラー', [
                'status' => $response->status(),
                'search_code' => $searchCode,
                'error_body' => $errorBody,
                'error_data' => $errorData
            ]);

            // エラーメッセージを構築
            $errorMessage = '住所データの取得に失敗しました';

            // APIから返されたエラーメッセージを優先的に使用
            if ($errorData) {
                if (isset($errorData['message'])) {
                    $errorMessage = $errorData['message'];
                } elseif (isset($errorData['error'])) {
                    $errorMessage = $errorData['error'];
                }
            }

            // ステータスコードに基づくデフォルトメッセージ
            if ($response->status() === 404) {
                if (!$errorData || !isset($errorData['message'])) {
                    $errorMessage = '該当する郵便番号・住所が見つかりませんでした。郵便番号を確認してください。';
                }
            } elseif ($response->status() === 401) {
                $errorMessage = '認証に失敗しました。API認証情報を確認してください。';
            } elseif ($response->status() === 400) {
                $errorMessage = 'リクエストが無効です。入力内容を確認してください。';
            }

            return response()->json([
                'error' => $errorMessage,
                'message' => $errorData['message'] ?? ($errorData['error'] ?? $errorBody),
                'error_code' => $errorData['error_code'] ?? null,
                'request_id' => $errorData['request_id'] ?? null,
                'status' => $response->status()
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('日本郵便APIエラー: ' . $e->getMessage());

            return response()->json([
                'error' => '住所データの取得中にエラーが発生しました',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 日本郵便APIからトークンを取得
     * 必要に応じて動的にトークンを取得するために使用
     */
    public function getToken(Request $request)
    {
        $apiUrl = config('services.japan_post.api_url');
        $clientId = config('services.japan_post.client_id');
        $secretKey = config('services.japan_post.secret_key');

        if (!$clientId || !$secretKey) {
            return response()->json([
                'error' => '日本郵便APIの認証情報が設定されていません'
            ], 500);
        }

        try {
            $response = Http::withHeaders([
                'x-forwarded-for' => $request->ip(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$apiUrl}/j/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'secret_key' => $secretKey,
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'トークンの取得に失敗しました',
                'message' => $response->body()
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('日本郵便トークンAPIエラー: ' . $e->getMessage());

            return response()->json([
                'error' => 'トークン取得中にエラーが発生しました',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * APIからトークンを取得（プライベートヘルパーメソッド）
     */
    private function getTokenFromAPI(Request $request): ?string
    {
        $apiUrl = config('services.japan_post.api_url');
        $clientId = config('services.japan_post.client_id');
        $secretKey = config('services.japan_post.secret_key');

        if (!$clientId || !$secretKey) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'x-forwarded-for' => $request->ip(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$apiUrl}/j/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'secret_key' => $secretKey,
            ]);

            if ($response->successful()) {
                $body = $response->body();

                // レスポンスがJSONかどうかを確認
                $data = json_decode($body, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    // JSONレスポンスの場合
                    return $data['token'] ?? null;
                } else {
                    // レスポンスが直接トークン文字列の場合（JWT形式）
                    // トークンは通常 "eyJ..." で始まる
                    if (preg_match('/^eyJ[A-Za-z0-9_-]+\.eyJ[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/', trim($body))) {
                        return trim($body);
                    }

                    // それ以外の場合はログに記録
                    Log::warning('日本郵便トークンAPI: 予期しないレスポンス形式', ['body' => substr($body, 0, 100)]);
                    return null;
                }
            }

            Log::error('日本郵便トークンAPIエラー: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('日本郵便トークンAPI例外: ' . $e->getMessage());
            return null;
        }
    }
}
