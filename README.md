# インスタンス絵文字カテゴライズ支援ツール

## いるもの

* PHP 7.2
* composer
* MySQL

## インストール方法

1. クローンします。
1. composerします。
1. `config.sample.php`を`config.php`にして、設定を埋めます。(後述)
1. `config.sample.js`を`config.js`にして、設定を埋めます。(後述)
1. Nginxの場合はconfをいじってください。https://gist.github.com/jamesvl/910325  
Apacheの場合は`.htaccess`があるのでOKです。
1. `tool/add.php`を実行します。全てのカスタム絵文字がデータベースに登録されます。  
なんかエラー出てきてデータベースにテーブルを作れないときは、add.phpの7行目のSQL文をコピペして実行して、7-11行目を削除してもう一回実行してください。

### レンサバに置きたいです

ローカル環境でクローンして、ローカル環境でcomposeしてからFTPでも使ってあげてください。

## config.php

```
define('DB_HOST', 'database.xxx.xxx');
define('DB_NAME', 'name');
define('DB_USER', 'user');
define('DB_PASSWORD', 'password');
define('DB_CHARSET', 'utf-8');
```
ここら辺はわかると思います。`DB_CHARSET`は多分そのままでいいです。  
`define('INSTANCE', 'myinstance.xxx');`自分のインスタンス名をを入れます。  
`define('DBNAME', 'emojis');`定数名に似合わないけどテーブル名です。被らなければなんでもいい気がする。  
`DB2NAME`は専用のトークンとインスタンスユーザーの対応テーブルです。  

## config.js

```
const category = [
    "cat1",
    "cat2"
]
```
この場合はカテゴリ「cat1」「cat2」が選択肢に出てきます。
```
const domain = "myinstance.xxx"
const siteName = "site title"
const appName ="app title"
```
`siteName`はタイトルバーに出たり、一番上に表示されます。  
`appName`はインスタンスと認証するときに、向こうに教える名前です。  