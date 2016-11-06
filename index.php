<?php
    include 'connection.php';

    echo "\n\n";
    $result = $mysqli->query("SELECT w.word,w.id FROM words w JOIN connections c ON c.first_word = w.id ORDER BY c.weight DESC LIMIT 1");
    $arr = $result->fetch_array(MYSQLI_ASSOC);
    $str = [$arr['word']];
    $w = $arr['word'];
    while(count($str) <= 50){
        $w = pickNextWord($w,$str);
        $str[] = $w;

    }
    echo implode(' ',$str);
    echo "\n\n";

    function pickNextWord($word,$text){
        global $mysqli;
        $l = min(9,count($text));
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
