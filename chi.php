<?php 

    class chi
    {
        private $dataLearning = [];
        private $data = [];
        private $stressLevel = [];
        private $chi = [];
        private $totalData = 0;

        function __construct() {
            $this->dataLearning = [
                    "ringan" => [
                        "tuhan tolong bantu adil sih",
                        "allah kuat hadap coba"
                    ],
                    "sedang" => [
                        "salah pergi sanggup tahan beban hidup kuat pergi",
                        "percaya pada bunuh biar kau percaya kau bunuh",
                        "satu jalan sisa bunuh aku"
                    ],
                    "berat" => [
                        "lupa kecuali mati",
                        "mati sial susah orang",
                        "tuhan hidup guna beban derita dosa engkau mati aku",
                        "ahhh mati saja"
                    ]
                ];
            $this->stressLevel = [
                "ringan",
                "sedang",
                "berat"
            ];
        }

        function getDataAndLevel(){
            return ["data" => $this->dataLearning, "level" => $this->stressLevel];
        }
        
        function getDataLearning(){
            $data = '';
            foreach(array_values($this->dataLearning) as $key => $value){
                $data .= " ".implode(" ",$value);
                $this->totalData += count($value);
            }
            $data = explode(" ",$data);
            $this->data = array_unique($data);
        }

        function countABCD($token, $level){
            $x = 0;
            $y = 0;
            foreach($level as $keyLevel){
                $count = array_filter($this->dataLearning[$keyLevel], function ($var) use ($token) {
                    return (strpos($var, $token) !== false);
                });
                $x += count($count);
                $y += (count($this->dataLearning[$keyLevel])) - count($count);
            }
            return ["x" => $x, "y" => $y];
        }

        function calculateChi(){
            $stresslvl = [];
            foreach(array_filter($this->data) as $key => $token){
                $chi = 0;
                foreach($this->stressLevel as $keylvl => $level){
                    $stresslvl = $this->stressLevel;
                    $AC = $this->countABCD($token, [$level]);
                    unset($stresslvl[$keylvl]);
                    $BD = $this->countABCD($token, $stresslvl);
                    $chi += ($this->totalData * pow(($AC["x"]*$BD["y"] - $AC["y"]*$BD["x"]), 2))/(array_sum($AC)*array_sum($BD)*($AC["x"]+$BD["x"])*($AC["y"]+$BD["y"]));
                }
                $this->chi[$token] = $chi;
            }
        }
        
        function main(){
            // get tokenization of data learning
            $this->getDataLearning();
            // calculate chi per term
            $this->calculateChi();
            return $this->chi;
        }

    }
    
?>
