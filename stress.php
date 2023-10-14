<?php

require_once('chi.php');

    class stress{

        private $dataTesting = [];

        function __construct() {
            $this->dataTesting = [
                    "hidup mati lahir dunia",
                    "biar org tua anak andai pergi"
                ];
        }

        function wtd($doc, $chi, $isTraining = true){
            $wtdValue = [];
            foreach($chi as $term => $value){
                if($isTraining){
                    $dataIDF = array_merge($doc["ringan"], $doc["sedang"], $doc["berat"]);
                }else{
                    $dataIDF = $doc;
                }
                // calculate IDF term
                $countIDF = count(array_filter($dataIDF, function ($var) use ($term) {
                    return (strpos($var, $term) !== false);
                }));
                $IDF = $countIDF == 0? 0 : log10((count($dataIDF)/$countIDF));
                // calculate TF and WTD (TF*IDF)
                foreach($dataIDF as $key => $document){
                    $TF = 0;
                    $countTF = array_count_values(explode(" ", $document));
                    // calculate TF
                    if(array_key_exists($term, $countTF)){
                        $TF = 1 + log10($countTF[$term]);
                    }
                    $keyDocument = $isTraining? "":"Q";
                    // calculate TFIDF
                    $wtdValue[$keyDocument.($key+1)][$term] = $TF*$IDF;
                }
            }
            return $wtdValue;
        }

        function cosine($wtdTraining, $wtdTesting, $chi){
            $final = [];
            foreach($wtdTesting as $docTest => $wtdTest){
                $cosine = [];
                foreach($wtdTraining as $docTraining => $wtdTr){
                    $AB = 0;
                    $BC = 0;
                    $CD = 0;
                    foreach($chi as $term => $value){
                        $AB += $wtdTest[$term]*$wtdTr[$term];
                        $BC += pow($wtdTest[$term], 2);
                        $CD += pow($wtdTr[$term], 2);
                    }
                    // calculate cosine similarity between data training and data testing
                    $cosine[$docTraining]= $AB / sqrt($BC*$CD);
                }
                $final[$docTest] = $cosine;
            }
            return $final;
        }

        function probability($cosine, $k, $level, $dataTraining){
            $number = 1;
            $documentLevel = [];
            $prob = [];
            foreach($level as $keyLevel){
                $documentLevel[$keyLevel] = range($number, $number-1+count($dataTraining[$keyLevel]));
                $number += count($dataTraining[$keyLevel]);
            }
            foreach($cosine as $docKey => $cos){
                arsort($cos);
                foreach($level as $keyLevel){
                    $x = 0;
                    $y = 0;
                    $threshold = 1;
                    foreach($cos as $docCos => $cosineValue){
                        // if docCos (document training) is in document level of $keylevel, then $dc = 1, else $dc = 0
                        $dc = in_array($docCos, $documentLevel[$keyLevel])? 1:0;
                        $x += $cosineValue * $dc;
                        $y += $cosineValue;
                        if($threshold == $k){
                            break;
                        }else{
                            $threshold++;
                        }
                    }
                    $xy = $x/$y;
                    $prob[$docKey][$keyLevel] = $xy;
                }
                arsort($prob[$docKey]);
                $lvl = array_keys($prob[$docKey]);
                $prob[$docKey] = $lvl[0];
            }
            return $prob;
        }

        function main(){
            $chi = new chi();
            // get data and level
            $dataAndLevel = $chi->getDataAndLevel();
            // calculate chi for every term
            $chiResult = $chi->main();
            arsort($chiResult);
            /*
            - only use top 25 of term. for training purpose
            - calculate tf-idf for data training
            */
            $wtd_training = $this->wtd($dataAndLevel["data"], array_slice($chiResult, 0, 25));
            // calculate tf-idf for data testing
            $wtd_testing = $this->wtd($this->dataTesting, array_slice($chiResult, 0, 25), false);
            // calculate cosine similarity between data training and data testing
            $cosine = $this->cosine($wtd_training, $wtd_testing, array_slice($chiResult, 0, 25));
            // i'm using k-value = 4 to calculate probability
            $final_calculation = $this->probability($cosine, 4, $dataAndLevel["level"], $dataAndLevel["data"]);
            echo json_encode($final_calculation);
        }
    }

    $run = new stress();
    $run->main();
?>
