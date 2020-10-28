<?php


namespace App\helpers;


class Shingler
{
    protected $length;
    protected $stopSymbols;
    protected $stopWords = [];
    protected $irregularWords;

    public function __construct($length, $removeStopWords = true)
    {
        $this->length = $length;
        if ($removeStopWords) {
            $this->stopSymbols = config('shingler.stop_symbols');
        }
        $this->irregularWords = config('shingler.irregular_verbs');
    }

    /**
     * Search duplicate percent
     *
     * @param $textA
     * @param $textB
     * @return float|int
     */
    public function compare($textA, $textB)
    {
        $shinglesA = $this->shingle($this->canonize($textA));
        $shinglesB = $this->shingle($this->canonize($textB));

        $matches = 0;

        foreach ($shinglesA as $shingle) {
            if (in_array($shingle, $shinglesB))
                $matches++;
        }

        return 2 * 100 * $matches / (count($shinglesA) + count($shinglesB));
    }

    /**
     * Prepare text
     *
     * @param $text
     * @return string
     */
    protected function canonize($text)
    {
        // remove stop symbols
        $text = str_replace($this->stopSymbols, null, $text);
        $text = strtolower(preg_replace('/\+/', " ", $text));

        $words = explode(" ", $text);

        // remove stop words
        foreach ($words as $i => $word) {
            if (in_array(strtolower($word), $this->stopWords))
                $words = array_remove($word, $words);
        }

        // replace irregular words in verb form
        foreach ($words as $i => $word) {
            $irregularWordIndex = array_multi_search(strtolower($word), $this->irregularWords);
            if ($irregularWordIndex != -1) {
                $words[$i] = $this->irregularWords[$irregularWordIndex][0];
            }
        }

        return implode(" ", $words);
    }

    /**
     * Get shingles
     *
     * @param $text
     * @return array
     */
    protected
    function shingle($text)
    {
        $result = [];
        $words = explode(" ", $text);

        for ($i = 0; $i <= count($words) - $this->length; $i++) {
            $currentShingle = [];

            for ($j = 0; $j < $this->length; $j++) {
                array_push($currentShingle, $words[$i + $j]);
            }

            $shingledText = implode(" ", $currentShingle);
            array_push($result, crc32($shingledText));
        }

        return $result;
    }
}

function array_remove($val, &$arr)
{
    $result = $arr;

    for ($x = 0; $x < count($result); $x++) {
        $i = array_search($val, $result);

        if (is_numeric($i)) {
            $left = array_slice($result, 0, $i);
            $right = array_slice($result, $i + 1, count($result) - 1);
            $result = array_merge($left, $right);
        }
    }

    return $result;
}

function array_multi_search($val, $arr)
{
    foreach ($arr as $k => $v) {
        if (in_array($val, $v)) {
            return $k;
        }
    }

    return -1;
}
