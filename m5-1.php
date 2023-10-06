<!DOCTYPE html>
<html lang= "ja">
<head>
    <meta charset = "UFT-8">
    <title>mission_5-1</title>
</head>
<body>
    <!--とりあえずコメントアウト。
    パスワード、名前、コメントを記入して送信するとコメントを投稿することができます。<br>
    パスワードと投稿番号を入力し、削除ボタンを押すことでコメントを削除することができます。<br>
    パスワードと投稿番号を入力し、編集ボタンを押すことでコメントを編集することができます。<br>
    -->
    <?php
    //データベースに接続
    $dsn = 'mysql:dbname=データベース名;host=localhost';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    //データベース内にテーブル作成
    $sql = "CREATE TABLE IF NOT EXISTS post(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name CHAR(32) NOT NULL,
    comment TEXT NOT NULL,
    day TEXT,
    pass TEXT
    );";
    $stmt = $pdo ->query($sql);

    if(!empty($_POST["del_num"]) && empty($_POST["edit_num"]) && empty($_POST["name"])){
        //削除フォームに入力がされた場合
        //削除番号とパスワード取得
        $del_num = $_POST["del_num"];
        $del_pass = $_POST["del_pass"];

        if(!empty($_POST["del_pass"])){
            //削除番号のパスワード取得
            $sql = "SELECT pass FROM post WHERE id = $del_num";
            $stmt = $pdo -> query($sql);
            $result = $stmt -> fetch(PDO::FETCH_BOTH);//配列としてパスワードが取得されている。
            $pass =  $result["pass"];//配列のパスワードを代入。
            
            //取得したパスワードと一致していた場合削除。
            if($pass == $del_pass){
                $sql = "delete from post where id = :id";
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(":id", $del_num, PDO::PARAM_INT);
                $stmt -> execute();

                //最後の行の投稿番号を取得
                $sql = "SELECT id FROM post ORDER BY id DESC LIMIT 1";
                $stmt = $pdo -> query($sql);
                $result = $stmt -> fetch(PDO::FETCH_BOTH);
                $las_num = $result["id"];
                $del_count_num = $del_num +1;

                //削除した投稿以降の投稿番号-1
                for( ; $las_num >= $del_count_num; $del_count_num++ ){
                    $del_txt_num = $del_count_num -1;
                    $sql = "UPDATE post SET id = :id WHERE id = $del_count_num";
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(":id", $del_txt_num, PDO::PARAM_INT);
                    $stmt -> execute();
                }
                //auto_incrementを振りなおす。（次の入力が前のid+1の状態になる）
                $sql = "ALTER TABLE post auto_increment = 1";
                $stmt = $pdo -> prepare($sql);
                $stmt -> execute();
                
                /*
                この方法もあるが現在の技術では不可。
                $reset_num = "SET @i:=0; UPDATE post SET id = @i:=@i+1";
                $stmt = $pdo -> prepare($reset_num);
                $stmt -> execute();
                */
            }else{
                echo "パスワードが違います。";
            }
        
        }else{
            echo "パスワードを入力してください";
        }

    }elseif(!empty($_POST["edit_num"]) && empty($_POST["del_num"]) && empty($_POST["name"])){
        //編集フォームに入力がされた場合。新規投稿フォームに名前とコメント表示。
        $edit_num = $_POST["edit_num"];
        $edit_pass = $_POST["edit_pass"];

        //編集対象のパスワード取得
        $sql = "SELECT pass FROM post WHERE id = $edit_num";
        $stmt = $pdo -> query($sql);
        $result = $stmt ->fetch(PDO::FETCH_BOTH);
        $txt_pass = $result["pass"];

        if($edit_pass == ""){
            $edit_txt_name = "";
            $edit_txt_comment = "";
            echo "パスワードを入力してください。";
        }elseif($txt_pass == ""){
            $edit_txt_name = "";
            $edit_txt_comment = "";
            echo "この投稿は編集できません。";
        }elseif($txt_pass == $edit_pass){
            //編集対象の名前取得
            $sql = "SELECT name FROM post WHERE id = $edit_num";
            $stmt = $pdo -> query($sql);
            $result = $stmt ->fetch(PDO::FETCH_BOTH);
            $edit_txt_name = $result["name"];
    
            //編集対象のコメント取得
            $sql = "SELECT comment FROM post WHERE id = $edit_num";
            $stmt = $pdo -> query($sql);
            $result = $stmt ->fetch(PDO::FETCH_BOTH);
            $edit_txt_comment = $result["comment"];

        }else{
            $edit_txt_name = "";
            $edit_txt_comment = "";
            echo "パスワードが違います。";
        }

    }elseif(!empty($_POST["edited_num"])){
        //新規フォームに編集番号の入力があった場合（投稿の編集）
        $edited_num = $_POST["edited_num"];
        $edited_name = $_POST["name"];
        $edited_comment = $_POST["comment"];
        $edited_pass = $_POST["pass"];
        
        //投稿のパスワード取得
        $sql = "SELECT pass FROM post WHERE id = $edited_num";
        $stmt = $pdo -> query($sql);
        $result = $stmt ->fetch(PDO::FETCH_BOTH);
        $edited_txt_pass = $result["pass"];

        if($edited_pass == ""){
            echo "パスワードを入力してください。";
        }elseif($edited_pass == $edited_txt_pass){
            //入力内容の変更
            $sql = "UPDATE post SET name = :name, comment = :comment WHERE id = $edited_num";
            $stmt = $pdo -> prepare($sql);
            $stmt -> bindParam(":name", $edited_name, PDO::PARAM_STR);
            $stmt -> bindParam(":comment", $edited_comment, PDO::PARAM_STR);
            $stmt -> execute();

        }else{
            echo "パスワードが違います。";
        }

    }elseif(!empty($_POST["name"]) && !empty($_POST["comment"]) && empty($_POST["del_num"]) && empty($_POST["edit_num"])){
        //新規フォームに入力がされた場合
        //新規登録フォームの情報
        $name = $_POST["name"];
        $comment = $_POST["comment"];
        $pass = $_POST["pass"];
        $day = date("Y/m/d H:i:s");
        
        //データレコードの挿入
        $sql = "INSERT INTO post(name, comment, day ,pass) VALUES(:name, :comment, :day, :pass)";
        $stmt = $pdo -> prepare($sql);
        $stmt -> bindParam(":name", $name, PDO::PARAM_STR);
        $stmt -> bindParam(":comment", $comment, PDO::PARAM_STR);
        $stmt -> bindParam(":day", $day, PDO::PARAM_STR);
        $stmt -> bindParam(":pass", $pass, PDO::PARAM_STR);
        $stmt -> execute();

    }
    if(empty($_POST["edit_num"]) or !empty($_POST["del_num"]) or !empty($_POST["name"])){
        $edit_num = "";
        $edit_txt_name = "";
        $edit_txt_comment = "";
    }

    ?>
    <!--入力フォーム-->
    <form action = "", method = "post">
        <!--新規投稿フォーム-->
        <input type = "hidden" name = "edited_num" value = <?= $edit_num ?>>
        <input type = "txt" name = "pass" placeholder = "パスワード" size = "8px">
        <input type = "txt" name = "name" placeholder = "お名前" size = "8px" value =<?= $edit_txt_name ?> >
        <input type = "txt" name = "comment" placeholder = "コメントを入力してください" size = "25px" value =<?= $edit_txt_comment ?>>
        <input type = "submit" name = "submit"><br>
        <!--削除フォーム-->
        <input type = "txt" name = "del_pass" placeholder = "パスワード" size = "8px">
        <input type = "number" name = "del_num" placeholder = "削除する投稿番号">
        <input type = "submit" name = "del_submit" value = "削除"><br>
        <!--編集フォーム-->
        <input type = "txt" name = "edit_pass" placeholder = "パスワード" size = "8px">
        <input type = "number" name = "edit_num" placeholder = "編集する投稿番号">
        <input type = "submit" name = "edit_submit" value = "編集"><br>
    </form>
    <?php
    echo "<hr><hr>";
    //データレコードをブラウザに表示
    $sql = "SELECT * FROM post";
    $stmt = $pdo -> query($sql);
    $result = $stmt -> fetchAll();
    foreach($result as $row){
        echo $row["id"]. " ";
        echo $row["name"]. " ";
        echo $row["comment"]. " ";
        echo $row["day"]. "<br>";
    }
    echo "<hr><hr>";
    ?>

</body>
</html>

