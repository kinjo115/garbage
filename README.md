# 名古屋市ゴミ収集サイト

名古屋市の粗大ごみ収集申込みシステムです。Laravel 12 と Livewire を使用して構築されています。

## 📋 目次

-   [要件](#要件)
-   [インストール](#インストール)
-   [環境設定](#環境設定)
-   [機能](#機能)
-   [開発](#開発)
-   [テスト](#テスト)
-   [デプロイ](#デプロイ)

## 🔧 要件

-   PHP 8.2 以上
-   Composer
-   Node.js 22 以上
-   npm
-   SQLite（開発環境）または MySQL/PostgreSQL（本番環境）

## 📦 インストール

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd garbage
```

### 2. 依存関係のインストール

```bash
# PHP依存関係のインストール
composer install

# Node.js依存関係のインストール
npm install
```

### 3. 環境設定ファイルの作成

```bash
cp .env.example .env
php artisan key:generate
```

### 4. データベースのセットアップ

```bash
# SQLiteを使用する場合
touch database/database.sqlite

# マイグレーションの実行
php artisan migrate

# シーダーの実行（オプション）
php artisan db:seed
```

### 5. アセットのビルド

```bash
# 開発環境
npm run dev

# 本番環境
npm run build
```

### 6. アプリケーションの起動

```bash
php artisan serve
```

ブラウザで `http://localhost:8000` にアクセスしてください。

## ⚙️ 環境設定

`.env`ファイルに以下の設定を追加してください：

### 基本設定

```env
APP_NAME="名古屋市ゴミ収集サイト"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=ja
APP_FALLBACK_LOCALE=ja
APP_FAKER_LOCALE=ja_JP
```

### データベース設定

```env
DB_CONNECTION=sqlite
# または
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=garbage
DB_USERNAME=root
DB_PASSWORD=
```

### メール設定

```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 日本郵便 API 設定

```env
# テスト環境用（デフォルトで設定済み）
JAPAN_POST_API_URL=https://stub-qz73x.da.pf.japanpost.jp/api/v1
JAPAN_POST_CLIENT_ID=Biz_DaPfJapanpost_MockAPI_j3QKS
JAPAN_POST_SECRET_KEY=uXuN0ejHG7nAn89AfAwa

# 本番環境では上記を上書きしてください
```

### キュー設定

```env
QUEUE_CONNECTION=database
```

## 🚀 機能

### 認証機能

-   ユーザー登録（メール認証付き）
-   ログイン/ログアウト
-   パスワードリセット
-   ゲストミドルウェア（認証済みユーザーのリダイレクト）

### 住所検索機能

-   日本郵便 API を使用した郵便番号検索
-   デジタルアドレス検索対応
-   自動住所入力
-   事業所個別郵便番号検索対応

### メール機能

-   新規登録確認メール
-   キューを使用した非同期メール送信
-   日本語メールテンプレート

### UI/UX

-   Tailwind CSS v4
-   SCSS によるカスタムスタイル
-   レスポンシブデザイン
-   モーダルダイアログ
-   Toast 通知

## 💻 開発

### 開発サーバーの起動

```bash
# すべてのサービスを同時に起動（サーバー、キュー、Vite）
composer run dev

# または個別に起動
php artisan serve
php artisan queue:work
npm run dev
```

### コードスタイル

```bash
# PHPコードスタイルのチェックと修正
./vendor/bin/pint
```

### アセットの開発

```bash
# 開発モード（ホットリロード）
npm run dev

# 本番ビルド
npm run build
```

## 🧪 テスト

```bash
# すべてのテストを実行
composer run test

# または
php artisan test
```

## 📧 キュー処理

### 開発環境

```bash
php artisan queue:work
```

### 本番環境

Supervisor を使用してキューワーカーを常駐させます。

詳細は `QUEUE_SETUP.md` を参照してください。

## 📁 プロジェクト構造

```
garbage/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── JapanPostController.php  # 日本郵便API統合
│   │   │   └── User/
│   │   │       └── AuthenticationController.php
│   │   └── Middleware/
│   │       └── RedirectIfAuthenticated.php
│   ├── Jobs/
│   │   └── SendMessageJob.php  # メール送信ジョブ
│   ├── Mail/
│   │   └── JobsMasseage.php
│   └── Models/
│       ├── User.php
│       ├── TempUser.php
│       ├── Prefecture.php
│       └── HousingType.php
├── config/
│   └── services.php  # 日本郵便API設定
├── database/
│   ├── migrations/
│   └── seeders/
├── lang/
│   └── ja/  # 日本語翻訳ファイル
├── resources/
│   ├── css/
│   │   └── app.css  # Tailwind CSS
│   ├── scss/
│   │   └── app.scss  # カスタムSCSS
│   ├── js/
│   │   └── app.js  # JavaScript
│   └── views/
│       ├── user/
│       │   └── auth/
│       │       └── register_confirmed.blade.php
│       └── mails/
└── routes/
    └── web.php
```

## 🌐 API エンドポイント

### 日本郵便 API 検索

```
GET /api/japan-post/search
```

**パラメータ:**

-   `postal_code` (必須): 郵便番号、事業所個別郵便番号、またはデジタルアドレス
-   `page` (オプション): ページ番号（デフォルト: 1）
-   `limit` (オプション): 取得件数（デフォルト: 10、最大: 1000）
-   `choikitype` (オプション): 町域フィールド形式（1: 括弧なし、2: 括弧あり）
-   `searchtype` (オプション): 検索タイプ（1: すべて、2: 事業所個別郵便番号を除外）

**レスポンス例:**

```json
{
    "addresses": [
        {
            "pref_name": "東京都",
            "city_name": "千代田区",
            "town_name": "丸の内",
            "block_name": "２丁目７-２",
            "zip_code": 1000001
        }
    ],
    "count": 1,
    "page": 1,
    "limit": 10,
    "searchtype": "zipcode"
}
```

## 🔐 セキュリティ

-   CSRF 保護
-   XSS 対策
-   SQL インジェクション対策（Eloquent ORM 使用）
-   パスワードハッシュ化
-   メール認証トークン（24 時間有効）

## 📝 ライセンス

MIT License

## 👥 コントリビューション

プルリクエストを歓迎します。大きな変更の場合は、まずイシューを開いて変更内容を議論してください。

## 📞 サポート

問題が発生した場合は、イシューを作成してください。

## 🗺️ ロードマップ

-   [ ] ダッシュボード機能
-   [ ] 申込み履歴管理
-   [ ] 管理者機能
-   [ ] 通知機能

---

**注意:** 本プロジェクトは開発中です。本番環境で使用する前に、適切なセキュリティ設定とテストを実施してください。
