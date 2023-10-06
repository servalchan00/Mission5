<?php
//m4-1の内容：データベース接続
$dsn = 'mysql:データベース名;host=localhost';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

//テーブルの削除
$sql = "DROP TABLE post";
$stmt = $pdo -> query($sql);

//テーブルの表示
$sql = "SHOW TABLES";
$result = $pdo -> query($sql);
foreach($result as $row){
    echo $row[0];
    echo "<br>";
}
echo "<hr>";

//テーブルの詳細情報の表示
$sql = "SHOW CREATE TABLE post";
$result = $pdo -> query($sql);
    foreach($result as $row){
        echo $row[1];
    }
echo "<hr><hr>";


?>
