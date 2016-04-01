<?php

error_reporting(E_ERROR);
ini_set('display_errors', 1);


class ApplicationBase{
    
    //Database settings
    private $dbname = "dbname";
    private $dbuser = "dbuser";
    private $dbpass = "dbpass";
    private $dbhost = "localhost";
    private $dbchar = "utf8";

    public $connection = array();


    // -- DÜZENLEMEYİ BIRAKIN -- //
    //Bu çizgiden sonra ayar bulunmamaktadır
    public $APIDB;
    public $data;
    public $err = "";

    public function __construct() {
        $this->connection = array(
            "dbname" => $this->dbname,
            "dbuser" => $this->dbuser,
            "dbpass" => $this->dbpass,
            "dbhost" => $this->dbhost,
            "dbchar" => $this->dbchar
            
        );
        $this->APIDB = new PDO('mysql:host='.$this->dbhost.';dbname='.$this->dbname.';charset=utf8',$this->dbuser ,  $this->dbpass );
        
    }
    
    public function KodUret($l=10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $l; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }    
    
    function ago($time){
       $periods = array("saniye", "dakika", "saat", "gün", "hafta", "ay", "yıl", "decade");
       $lengths = array("60","60","24","7","4.35","12","10");

       $now = time();

           $difference     = $now - $time;
           $tense         = "önce";

       for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
           $difference /= $lengths[$j];
       }

       $difference = round($difference);

       if($difference != 1) {
           $periods[$j].= "";
       }

       return "$difference $periods[$j] önce ";
    }
    
    public function returnerror($errorinfo){
        $this->result["status"]                     = "error";
        $this->result["message"]                    = $errorinfo;
        return $this->result;
    }
    
    public function returnfail($errorinfo){
        $this->result["status"]                     = "fail";
        $this->result["message"]                    = $errorinfo;
        return $this->result;
    }
    
    public function returndata($datainfo){
        $this->result["status"]         = "success";
        $this->result["data"]           = $datainfo;
        return $this->result;
    }
    
    private  function requestcheck($req){
        $k=0;
        foreach ($this->commands as $key=>$val) {
            if($req["command"]==$key){
                $k=1;
                if(array_key_exists($val,"N")){
                    $k=0;
                    foreach ($val["N"] as $v) {
                        if(array_key_exists($req,$v) && $req[$v] != ""){
                            //Bu kısımda value değerinin içeriği de kontrol edilmeli! 
                            //fields kısmında bunun tanımı zaten yapıldı!
                            $k=1;
                        }
                    }
                }                
            }            
        }
        if($k==0){
            return FALSE;
        }else{
            return TRUE;
        }            
    }
    
    public function ArrayToPDO($array) {
        //array key=>value şeklinde olmalıdır
        $pdo = array("a"=>"","b"=>"","ab"=>array());
        foreach ($array as $key => $value) {
            $pdo["a"]   .= $key.",";
            $pdo["b"]   .= ":".$key.",";
            $pdo["ab"][":".$key]  = $value;
            
        }
        $pdo["a"] = rtrim($pdo["a"],",");
        $pdo["b"] = rtrim($pdo["b"],",");
        return $pdo;
    }
    
    public function GetTableCells($table) {
        $q = $this->APIDB->prepare("DESCRIBE ".$table);
        $q->execute();
        $table_fields = $q->fetchAll(PDO::FETCH_COLUMN);
        return $table_fields;
    } 
    
    public function FilterDataByTable($data,$table) {
        $Veri   = array("filtered"=>array(),"others"=>array());
        $cells = $this->GetTableCells($table);
        foreach ($data as $key => $value) {
            if(in_array($key, $cells) && $value !=="") {
                $Veri["filtered"][$key] = $value; 
            }else{
                $Veri["others"][$key] = $value;
            } 
        }
        return $Veri;
    }  
    
    public function ConvertDataForTable($data,$tablekeys) {
        $newdata = array();
        foreach ($data as $key => $value) {
            if(array_key_exists($key, $tablekeys)){
                $newdata[$tablekeys[$key]] = $value;
            }else{
                $newdata[$key] = $value; 
            }
        }
        return $newdata;
    }    
}
?>
