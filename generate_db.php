<?php
    include 'connection.php';

    $mysqli->query("DROP TABLE IF EXISTS words");
    $mysqli->query("DROP TABLE IF EXISTS connections");
    $mysqli->query("CREATE TABLE words(id INT NOT NULL AUTO_INCREMENT, word VARCHAR(255), PRIMARY KEY (id))");
    $mysqli->query("CREATE TABLE connections(id INT NOT NULL AUTO_INCREMENT, first_word INT, second_word INT, weight INT, length INT, PRIMARY KEY (id))");
    $mysqli->query("CREATE UNIQUE INDEX word_link ON connections (first_word, second_word)");
    $mysqli->query("CREATE INDEX weight ON connections (weight)");
    $mysqli->query("CREATE INDEX length ON connections (length)");

    $files = glob('txt/*.txt', GLOB_BRACE);
    foreach($files as $file){
        $handle = fopen($file,'r');
        $text = fread($handle, filesize($file));
        $text_array = explode(' ',$text);
        $word_list = [];
        foreach($text_array as $word){
            $word = preg_replace("/[^A-Za-z0-9., ]/", '', $word);
            if($word != ''){
                $word_list[1][] = $word;
            }
        }

        for($i = 2; $i <= 5; $i++){
            foreach($word_list[1] as $id => $word){
                $arr = [$word];
                for($j = 1; $j < $i; $j++){
                    $arr[] = $word_list[1][$id + $j];
                }
                $word_list[$i][] = implode(' ',$arr);
            }
        }

        foreach($word_list as $length => $list){
            foreach($list as $id=>$word){
                $result = $mysqli->query("SELECT id FROM words WHERE word = '$word'");
                if($result->num_rows == 0){
                    $res = $mysqli->query("INSERT INTO words (word) VALUES('$word')");
                    $word_id = $mysqli->insert_id;
                } else {
                    $row = $result->fetch_array(MYSQLI_ASSOC);
                    $word_id = $row['id'];
                }

                if($id - $length >= 0){
                    $result = $mysqli->query("SELECT id FROM words WHERE word = '" . $word_list[$length][$id - $length] . "'");
                    $row = $result->fetch_array(MYSQLI_ASSOC);
                    $first_id = $row['id'];
                    $result = $mysqli->query("SELECT id,weight FROM connections WHERE first_word = $first_id AND second_word = $word_id");
                    if($result->num_rows == 0){
                        $res = $mysqli->query("INSERT INTO connections (first_word,second_word,weight,length) VALUES($first_id,$word_id,1,$length)");
                    } else {
                        $row = $result->fetch_array(MYSQLI_ASSOC);
                        $res = $mysqli->query("UPDATE connections SET weight = " . ($row['weight'] + 1) . " WHERE id = " . $row['id']);
                    }
                }
            }
        }
    }
?>
