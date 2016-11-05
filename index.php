<?php
    include 'connection.php';

    $result = $mysqli->query("SELECT w.word,w.id FROM words w JOIN connections c ON c.first_word = w.id ORDER BY c.weight DESC LIMIT 1");
    $arr = $result->fetch_array(MYSQLI_ASSOC);
    $str = $arr['word'] . ' ';
    for($i = 0; $i < 50; $i++){
        $result = $mysqli->query("SELECT w.word,w.id FROM words w JOIN connections c ON c.second_word = w.id WHERE c.first_word = " . $arr['id'] . " ORDER BY c.weight DESC LIMIT 1");
        $arr = $result->fetch_array(MYSQLI_ASSOC);
        $str .= $arr['word'] . ' ';
    }
    echo $str;
?>
