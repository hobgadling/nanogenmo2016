<?php
    include 'connection.php';

    echo "\n\n";
    $result = $mysqli->query("SELECT w.word,w.id FROM words w JOIN connections c ON c.first_word = w.id ORDER BY c.weight DESC LIMIT 1");
    $arr = $result->fetch_array(MYSQLI_ASSOC);
    $str = [$arr['word']];
    $w = $arr['word'];
    $full_text = '';
    for($i = 0; $i < 20; $i++){
        $full_text .= "Chapter " . ($i + 1) . "\n\n";
        while(count($str) <= 2500){
            $w = pickNextWord($w,$str);
            $word_arr = explode(' ',$w);
            foreach($word_arr as $word){
                $str[] = $word;
            }
        }
        $text = implode(' ',$str);
        $sentences = explode('.',$text);
        foreach($sentences as $id=>$sentence){
            $sentences[$id] = ucfirst($sentence);
        }
        $paragraphs = array_chunk($sentences,3);
        $full_text .= "\t";
        foreach($paragraphs as $paragraph){
            $full_text .= implode('. ',$paragraph) . ".\n\t";
        }

        $full_text .= "\n\n";
    }

    echo $full_text;
    echo "\n\n";

    $fp = fopen('txt/novel.txt','w');
    fwrite($fp,$full_text);
    fclose($fp);

    function pickNextWord($word,$text){
        global $mysqli;
        $l = min(5,count($text));
        $str_array = array_slice($text,count($text) - $l);
        $words = [];
        while(count($str_array) > 0){
            $words[] = implode(' ',$str_array);
            $x = array_shift($str_array);
        }
        $result = $mysqli->query("SELECT w.word,c.weight FROM words w JOIN connections c ON c.second_word = w.id WHERE c.first_word IN (SELECT id FROM words WHERE word IN ('" . implode("','",$words) . "')) ORDER BY c.weight DESC");
        $weights = [];
        while($row = $result->fetch_array(MYSQLI_ASSOC)){
            $weights[$row['word']] = $row['weight'];
        }

        return getRandomWeightedElement($weights);
    }

    function getRandomWeightedElement(array $weightedValues) {
        $rand = mt_rand(1, (int) array_sum($weightedValues));

        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }
    }
?>
