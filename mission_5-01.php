<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>billboard</title>
</head>
<body>
    <form action="" method="post" id="submit">
    </form>
    <form action="" method="post" id="del">
    </form>
    <form action="" method="post" id="edit">
    </form>
    入力フォーム
    <br>
    <input type="text" name="name" placeholder="名前" form="submit">
    <input type="text" name="comment" placeholder="コメント" form="submit">
    <input type="password" name="password" placeholder="パスワード" form="submit">
    <!- 送信ボタンの作成 ->
    <input type="submit" name="submit" form="submit">
    <br>
    削除番号指定用フォーム
    <br>
    <input type="number" name="num_del" placeholder="削除番号" form="del">
    <input type="password" name="password_del" placeholder="パスワード" form="del">
    <!- 削除ボタンの作成 ->
    <input type="submit" name="del" value="削除" form="del">
    <br>
    編集番号指定用フォーム
    <br>
    <input type="number" name="num_edit" placeholder="編集対象番号" form="edit">
    <input type="password" name="password_edit" placeholder="パスワード" form="edit">
    <!- 編集ボタンの作成 ->
    <input type="submit" name="edit" value="編集" form="edit">
    <br>
    <?php
        // DB接続設定
        $dsn = 'データ';
        $user = 'ユーザー名';
        $password = 'パスワード';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        // データベース内にテーブルを作成
        $sql = "CREATE TABLE IF NOT EXISTS tb_billboard"
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "name char(32),"
        . "comment TEXT,"
        . "password TEXT,"
        . "registry_datetime DATETIME"
        .");";
        $stmt = $pdo->query($sql);

        #既存投稿を表示
        if(empty($_POST["num_del"]) && empty($_POST["num_edit"]) && empty($_POST["editnum"])){ #削除や編集フォームに入力がない場合に実行
            // 入力したデータレコードを抽出し、表示する
            $sql = 'SELECT * FROM tb_billboard';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach ($results as $row){
                //$rowの中にはテーブルのカラム名が入る
                echo $row['id'].',';
                echo $row['name'].',';
                echo $row['comment'].',';
                echo $row['registry_datetime'].'<br>';
                echo "<hr>";
            }
        }

        #新規投稿処理(編集対象番号に入力がない場合は入力フォームの入力内容をDBに追加)
        if(empty($_POST["num_edit"])){
            #入力フォームに入力があり編集状態でないことを確認
            if((!empty($_POST["name"])) && !(empty($_POST["comment"])) && !(empty($_POST["password"])) && empty($_POST["editnum"])){
                // データを入力（データレコードの挿入）
                $sql = $pdo -> prepare("INSERT INTO tb_billboard (name, comment, password, registry_datetime) VALUES (:name, :comment, :password, :registry_datetime)");
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $sql -> bindParam(':password', $password, PDO::PARAM_STR);
                $sql -> bindParam(':registry_datetime', $date, PDO::PARAM_STR);
                $name = $_POST["name"];
                $comment = $_POST["comment"];
                $password = $_POST["password"];
                $date = date("Y/m/d H:i:s");
                $sql -> execute();
                // 入力したデータレコードを抽出し、表示する(追加した行のみ表示)
                $sql = 'SELECT * FROM tb_billboard WHERE id=(SELECT MAX(id) FROM tb_billboard)'; //idに最終行(追加した行)のidを代入
                $stmt = $pdo->query($sql);
                $results = $stmt->fetchAll();
                foreach ($results as $row){
                    //$rowの中にはテーブルのカラム名が入る
                    echo $row['id'].',';
                    echo $row['name'].',';
                    echo $row['comment'].',';
                    echo $row['registry_datetime'].'<br>';
                    echo "<hr>";
                }
            }
        }

        #編集
        #編集対象番号に入力がある場合は対象行を表示
        else{
            $num_edit = $_POST["num_edit"];
            $password_edit = $_POST["password_edit"];
            // 入力したデータレコードを抽出し、表示する
            $id = $num_edit; // idが編集対象番号のデータだけを抽出したい、とする
            $sql = 'SELECT * FROM tb_billboard WHERE id=:id ';
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchAll(); 
            foreach ($results as $row){
                //$rowの中にはテーブルのカラム名が入る
                if($row['password'] == $password_edit){
                    echo "<strong>編集状態<br></strong>";
                    echo "名前,コメント,パスワード欄に編集内容を入力して送信してください。投稿番号は変更されません。<br><br>";
                    echo "<strong>編集対象投稿</strong><br>";
                    echo "投稿番号： ".$row['id']."<br>";
                    echo "名前： ".$row['name']."<br>";
                    echo "コメント： ".$row['comment']."<br>";
                }else{
                    echo "パスワードが違います<br><br>";
                    // 入力したデータレコードを抽出し、表示する
                    $sql = 'SELECT * FROM tb_billboard';
                    $stmt = $pdo->query($sql);
                    $results = $stmt->fetchAll();
                    foreach ($results as $row){
                        //$rowの中にはテーブルのカラム名が入る
                        echo $row['id'].',';
                        echo $row['name'].',';
                        echo $row['comment'].',';
                        echo $row['registry_datetime'].'<br>';
                        echo "<hr>";
                    }
                }
            }
        }

        #編集後内容表示
        #編集内容が入力フォームに入力されていて編集状態であることを確認
        if(!(empty($_POST["name"])) && !(empty($_POST["comment"])) && !(empty($_POST["password"])) && !(empty($_POST["editnum"]))){
            $name = $_POST["name"];
            $comment =$_POST["comment"];
            $password = $_POST["password"];
            $editnum = $_POST["editnum"];
            $date = date("Y/m/d H:i:s");
            // 入力されているデータレコードの内容を編集
            $id = $editnum; //変更する投稿番号
            $sql = 'UPDATE tb_billboard SET name=:name,comment=:comment,password=:password,registry_datetime=:registry_datetime WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->bindParam(':registry_datetime', $date, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            // 入力したデータレコードを抽出し、表示する
            $sql = 'SELECT * FROM tb_billboard';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach ($results as $row){
                //$rowの中にはテーブルのカラム名が入る
                echo $row['id'].',';
                echo $row['name'].',';
                echo $row['comment'].',';
                echo $row['registry_datetime'].'<br>';
                echo "<hr>";
            }
        }

        #削除
        #削除番号に入力がある場合は対象行を削除する
        if(!(empty($_POST["num_del"]))){
            $num_del = $_POST["num_del"];
            $password_del = $_POST["password_del"];
            // 入力したデータレコードを削除
            $id = $num_del;
            $sql = 'SELECT * FROM tb_billboard WHERE id=:id ';
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchAll(); 
            foreach ($results as $row){
                //$rowの中にはテーブルのカラム名が入る
                if($row['password'] == $password_del){
                    $sql = 'delete from tb_billboard where id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();
                }else{echo "パスワードが違います<br><br>";}
            }
            // 入力したデータレコードを抽出し、表示する
            $sql = 'SELECT * FROM tb_billboard';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach ($results as $row){
                //$rowの中にはテーブルのカラム名が入る
                echo $row['id'].',';
                echo $row['name'].',';
                echo $row['comment'].',';
                echo $row['registry_datetime'].'<br>';
                echo "<hr>";
            }
        }
    ?>
    <!- 最終的に入力フォームの送信と共に送信する ->
    <input type="hidden" name="editnum"
        value = "<?php if(!(empty($_POST["num_edit"])) && !(empty($_POST["password_edit"]))){
            $id = $_POST["num_edit"]; // idが編集対象番号のデータだけを抽出したい、とする
            $sql = 'SELECT * FROM tb_billboard WHERE id=:id ';
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchAll(); 
            foreach ($results as $row){
                //$rowの中にはテーブルのカラム名が入る
                if($row['password'] == $_POST["password_edit"]){
                    echo $_POST["num_edit"];
                }
            }
        }?>"
        form="submit">
</body>
</html>
