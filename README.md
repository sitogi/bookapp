# メモ
## Laravel を使ってアプリケーションを作る
```bash
$ cd ~/dev/php_study
$ composer create-project laravel/laravel bookapp
```

動かしてみる
```bash
$ php artisan serve
Laravel development server started: <http://127.0.0.1:8000>
```

以降登場する設定ファイルのパスなどは Laravel プロジェクトのルートを `PROJECT_HOME` として表現する。

### データベースの接続設定を変更する
`PROJECT_HOME/.env` に記載してある。 このファイル中にコメントを書くには先頭に `#` をつければ良い。
今は何に使うかわからないけどアプリケーションキーもここに書いてあった。

`DB_CONNECTION=mysql` をコメントアウトして、`DB_DATABASE` に以下を設定。ここは OS のルートからの絶対パスを入れるらしい。
`DB_DATABASE=/home/masayuki/dev/php_study/bookapp/database/database.sqlite`

### データベースを生成するマイグレーションファイルを作成する
```bash
php artisan make:migration create_books_table --create=books
```

`PROJECT_HOME/database/migration` にファイルが生成されている。ユーザやパスのファイルはデフォルトで用意されているもの。

これで、自動的に DB を操作するためのコードが自動生成されている。私達は追加したいフィールドや DB の定義のみを編集していけばよい。これがフレームワークの力。

### データベースが使えるように設定する
`PROJECT_HOME/config/database.php` に接続情報が書いてあるのでいじる。
`DB_CONNECTION` に指定した文字列ごとの、デフォルトの接続情報が記載されている。

```php
    'sqlite' => [
        'driver' => 'sqlite',
        'url' => env('DATABASE_URL'),
        'database' => env('DB_DATABASE', database_path('database.sqlite')),
        'prefix' => '',
        'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
    ],
```

### マイグレートを実行する
```bash
$ cd ~/dev/php_study/bookapp
$ php artisan migrate
```

このコマンドによって、先程作成したマイグレーションファイルをもとにデータベースを作成してくれる。

#### ただしドライバが見つからないエラーになってしまった
とりあえず `/etc/php/7.2/cli/php.ini` で sqlite3 拡張のコメントアウトを消してみた。
これでもダメだったので `sudo apt install php-sqlite3` をしてみる。

```bash
$ php artisan migrate
PHP Warning:  Module 'sqlite3' already loaded in Unknown on line 0
Migration table created successfully.
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table (0.04 seconds)
Migrating: 2014_10_12_100000_create_password_resets_table
Migrated:  2014_10_12_100000_create_password_resets_table (0.05 seconds)
Migrating: 2019_08_24_122924_create_books_table
Migrated:  2019_08_24_122924_create_books_table (0.02 seconds)
```

できた！！けどすでにモジュールが読み込まれてるよ的なエラーになったので、さっきのコメントアウト消した部分を戻してみる。

```bash
$ rm database/database.sqlite
$ php artisan migrate
Migration table created successfully.
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table (0.05 seconds)
Migrating: 2014_10_12_100000_create_password_resets_table
Migrated:  2014_10_12_100000_create_password_resets_table (0.05 seconds)
Migrating: 2019_08_24_122924_create_books_table
Migrated:  2019_08_24_122924_create_books_table (0.02 seconds)
```

できたー。 Warning も消えてスッキリ。

## モデルを追加する
```bash
masayuki@masa-sb [~/dev/php_study/bookapp]  (master)
$ php artisan make:model Book
Model created successfully.
```

テーブル名に対応した、単数形の先頭大文字のモデル名を指定すると、自動的に関連付けてくれる！
これで、 `PROJECT_HOME/app/Book.php` が作成される。

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    //
}
```
最初は中身がからなので、ここに CRUD 処理を追記していく。

## レイアウトファイルを作成する
今回は予め用意してあります。
`app.blade.php` を `PROJECT_HOME/resources/views/layouts` に保存する。
`PROJECT_HOME/resources/views/layouts` で `php -S 127.0.0.1:8000` をやればいつものように確認できる。 `php artisan serve` をすると Laravel のルーティング設定をしてあるものしか見れないのでダメ。

## メインコンテンツを表示するファイルを作成する
これも予め用意してある。
`books.blade.php` を `/resources/views` に保存する。

## Laravel によるページ表示の流れ
1. ブラウザからアクセスする
2. Laravel の中で `routes.php` というファイルが実行され、アドレスごとに処理が分岐される
3. 今回はまず一覧を表示する
4. Laravel の DB ファサードというところで Books テーブルのデータを全取得 (Book::all())
5. `routes.php` で、取得したデータを books 変数にセットして books というビューに渡す (view('books')
6. `books.blade.php` が呼ばれ、blade の機能でさらにその中で `commons/errors.blade.php` や共通のレイアウト (`layouts/apps.blade.php`) を読み込む。
7. そこで Bootstrap (JS, CSS, jQuery) も読み込む。

## ここまで配置したファイルのポイントおさらい。
### `PROJECT_HOME/resource/views/books.blade.php`
- L1 の `@extends('layouts.app')` で、共通レイアウトを読み込んでいる
- L3 の `@section('content')` で、これから本文のデータを表示するという宣言をしている
- L4 以降の class は Bootstrap の class なので頑張って覚えよう。
- L13 の `@include('common.errors')` で外部のレイアウトを読み込んでいる
    - エラーがあった場合は、この場所 (本文の先頭) にエラーメッセージが表示される
- L41 の `@if (count($books) > 0)` は books 変数にデータがあれば本の一覧を表示する

## ルーティングファイルのベースを作る
どの URL を呼び出した際にどの View を表示するかを定義するもの。
`PROJECT_HOME/routes/web.php` に記述されている。


## `PROJECT_HOME/route/web.php` を修正する
- `Book::all();` で全レコードを持ってこれる
- `view()` の引数は連想配列で渡す。
- リクエスト受けたあとの処理は、コントローラという別ファイルに切り出すことも可能だが、今回はこのファイルに書く

## ORM のおさらい
### モデル名とテーブル名の関係
|モデル名|テーブル名|
|--|--|
|Book|books|
|Dog|dogs|
|Cat|cats|
|Flower|flowers|

Eloquant ORM によって、定められた命名規則に従えば自動処理される。
定義は変更可能。デフォルトだと `単数形:複数形` の形っぽい。

## 認証機能を追加する
`PROJECT_HOME/app/Http/Controllers/Auth` に認証に使う Controller が予め用意されている。

あとは `PROJECT_HOME/resources/views` に View を追加するのと、 `PROJECT_HOME/route/web.php` にルーティングの処理を書いてあげれば良い。

### まずは artisan でテンプレートを作成
```bash
php artisan make:auth --views
```

### web.php を編集する
`Route::auth();` を `PROJECT_HOME/route/web.php` に追加。

### 各ページでも認証を通るようにする

```
-    Route::get('/', function() {
+    Route::get('/', ['middleware' => 'auth', function() {
         $books = Book::all();
-    });
+    }]);
```

### 認証されていないときの処理について
以下のファイルに認証されていないときのリダイレクト先が記載してある。
昔のバージョンと比較して名前が変わっているが、多分これ。

`PROJECT_HOME/app/Http/Controllers/Auth/VerificationController.php`

変更。
```
-    protected $redirectTo = '/home';
+    protected $redirectTo = '/';
```

