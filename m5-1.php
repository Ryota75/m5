<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_3</title>
</head>
<body>
    <?php 
        //内部の処理

        //データベース接続
        $dsn = 'mysql:dbname=*****;host=localhost';
        $user = '*****';
        $password = '*****';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

        //テーブル作成
        $sql = "CREATE TABLE IF NOT EXISTS tbtest"
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "name char(32),"
        . "comment TEXT,"
        . "date DATETIME,"
        . "password varchar(32),"
        . "edited char(6)"
        .");";
        $stmt = $pdo->query($sql);

        //投稿フォーム
        if(isset($_POST["submit_post"])){
            
            //変数設定
            $edit_number = $_POST["edit_number"];//編集モード用　投稿番号参照
            
            if($_POST["comment"] != ""){
                $name = $_POST["name"];//名前設定//
                $comment = $_POST["comment"];//コメント設定
                $date = date("Y/m/d H:i:s");//日付設定
                $password = $_POST["password"];//パスワード設定
                
                if($name == ""){
                    $name = "匿名";
                }
                
                if(empty($edit_number)){//新規投稿モード
                    $sql = "INSERT INTO tbtest (name, comment, date, password) VALUES (:name, :comment, :date, :password)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                    $stmt->execute();
                    
                }else{//編集モード
                    $edited = "（編集済み）";
                    $sql = 'UPDATE tbtest SET name=:name,comment=:comment,date=:date,password=:password,edited=:edited WHERE id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $edit_number, PDO::PARAM_INT);
                    $stmt->bindParam(':edited', $edited, PDO::PARAM_STR);
                    $stmt->execute();
                }
                
            }
            
        }elseif(isset($_POST["submit_deletion"])){//削除フォーム  
            if(!empty($_POST["deletion"]) && $_POST["password_deletion"] != ""){
                $deletion = $_POST["deletion"];//削除番号設定
                $password_deletion = $_POST["password_deletion"];//パスワード受け取り　削除用

                $sql = "SELECT password FROM tbtest WHERE id = :deletion";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':deletion', $deletion, PDO::PARAM_INT);
                $stmt->execute();
                $results = $stmt->fetch();

                if($results[0] == $password_deletion){
                    $sql = 'delete from tbtest where id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $deletion, PDO::PARAM_INT);
                    $stmt->execute();
                    $check = 1;
                }
            }
            
        }elseif(isset($_POST["submit_edit"])){//編集フォーム 
            
            if(!empty($_POST["edit"]) && $_POST["password_edit"] != ""){
                $edit = $_POST["edit"];//編集番号設定
                $password_edit = $_POST["password_edit"];//パスワード受け取り　編集用
                
                $sql = 'SELECT name,comment,password FROM tbtest where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $edit, PDO::PARAM_INT);
                $stmt->execute();
                $results = $stmt->fetch();

                if($results['password'] == $password_edit){
                    $edit_name = $results['name'];
                    $edit_comment = $results['comment'];
                }
            
            }
            
        }
        
        //ブラウザ表示　新規投稿モード、編集モード
        if(!isset($edit_name)){
            echo "新規投稿モード<br>";
        }else{
            echo "編集モード<br>";
        }
    ?>
    
    投稿フォーム
    <form method="post">
        <!--名前-->
        <input type="text" name="name" placeholder="名前" value=<?php
        
        if(isset($edit_name)){
            echo  $edit_name;
        }
        
        ?>>
        <br>
        
        <!--コメント-->
        <input type="text" name="comment" placeholder="コメント" value=<?php
        
        if(isset($edit_name)){
            echo $edit_comment; 
        }
        
        ?>>
        <br>
        
        <!--パスワード-->
        <input type="text" name="password" placeholder="パスワード" value=<?php
        
        if(isset($edit_name)){
            echo $password_edit;
        }
        
        ?>>
        
        <!--編集番号　隠し-->
        <input type="hidden" name="edit_number" value=<?php
        
        if(isset($edit_name)){
            echo $edit;
        }
        
        ?>>
        <br>
        ※パスワードのない投稿は削除・編集が行えません
        <br>
        <input type="submit" name="submit_post" value="投稿">
    </form>
    
    削除フォーム
    <form method="post">
        <input type="number" name="deletion" value="削除番号" placeholder="投稿番号を入力">
        <br>
        <input type="text" name="password_deletion" placeholder="パスワード">
        <br>
        <input type="submit" name="submit_deletion" value="削除">
    </form>
    
    編集フォーム
    <form method="post">
        <input type="number" name="edit" value="編集番号" placeholder="投稿番号を入力">
        <br>
        <input type="text" name="password_edit" placeholder="パスワード">
        <br>
        <input type="submit" name="submit_edit" value="編集">
    </form>
    
    <?php 
        //ブラウザ表示
        
        echo "<br>通知：";
        
        //条件分岐　送信結果
        if(isset($_POST["submit_post"])){//投稿通知
            
            if(empty($edit_number)){//新規投稿モード
                
                if($_POST["comment"] != ""){
                    echo "投稿を受け付けました<br>";
                }else{
                    echo "コメントを入力してください（新規投稿）<br>";
                }
                
            }else{//編集モード
                
                if($_POST["comment"] != ""){
                    echo $edit_number."は編集されました<br>";
                }else{
                    echo "コメント未入力での編集はできません　編集フォームからやり直してください　コメントを削除したい場合は削除フォームを使用してください（編集）<br>";
                }
                
            }
            
        }elseif(isset($_POST["submit_deletion"])){//削除フォーム
            
            if(!empty($check)){
                echo $deletion."は削除されました<br>";
            }elseif($_POST["deletion"] != "" && $_POST["password_deletion"] != ""){
                echo "投稿番号、またはパスワードが違います（削除）<br>";
            }else{
                echo "投稿番号、パスワードを入力してください（削除）<br>";
            }
            
        }elseif(isset($_POST["submit_edit"])){//編集フォーム
            
            if(isset($edit_name)){
                echo "投稿フォームで編集してください<br>";
            }elseif($_POST["edit"] != "" && $_POST["password_edit"] != ""){
                echo "投稿番号、またはパスワードが違います（編集）<br>"; 
            }else{
                echo "投稿番号、パスワードを入力してください（編集）<br>";
            }
            
        }else{
            echo "<br>";
        }
        
        echo "<br>";
        
        //データベース表示
        $sql = 'SELECT * FROM tbtest';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
            echo $row['id'].',';
            echo $row['name'].',';
            echo $row['comment'].'<br>';
            echo $row['date'].'<br>';
            echo $row['edited'].'<br>';
        echo "<hr>";
        }
    ?>
</body>
</html>