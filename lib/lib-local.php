<?php

class Local{
    
    //Database settings
    private $dbname = "";
    private $dbuser = "";
    private $dbpass = "";
    private $dbhost = "";
    private $dbchar = "";


    private $DB;
    
    public function __construct($connection) {
        $this->DB = new PDO('mysql:host='.$connection["dbhost"].';dbname='.$connection["dbname"].';charset=utf8',$connection["dbuser"] ,  $connection["dbpass"] );
    }
    
    private $table_local = "kc_local";


    public function LocalGetInfo($lid) {
        if($lid == ""){
            return FALSE;
        }
        $f = "id";
        $query = "
        SELECT
            *
        FROM
            ".$this->table_local."
        WHERE
            ".$f." = '".$lid."'";
        
        $ky = $this->DB->prepare($query);  
        $ky->execute();        
        if($ky->rowCount() < 1){
            return array();
        }else{
            return $ky->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    public function LocalGetInfoByEmail($email) {
        if($email == ""){
            return FALSE;
        }
        $f = "email";
        $query = "
        SELECT
            *
        FROM
            ".$this->table_local."
        WHERE
            ".$f." = '".$email."'";
        $ky = $this->DB->prepare($query);  
        $ky->execute();        
        if($ky->rowCount() < 1){
            return array();
        }else{
            return $ky->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    public function LocalLogin($email,$pass) {
        if($email == "" || $pass == ""){
            return FALSE;
        }
        $f = "id";
        $query = "
        SELECT
            *
        FROM
            ".$this->table_local."
        WHERE
            email = '".$email."'"
                . " AND "
                . " passwd = '".$pass."' ";        
        $ky = $this->DB->prepare($query);  
        $ky->execute();        
        if($ky->rowCount() < 1){
            return array();
        }else{
            return $ky->fetch(PDO::FETCH_ASSOC);
        }
        
    }
    
    public function LocalGetList($conditions=array(),$order="id DESC",$limit=1000,$cells="*") {
         
        $q1 = "";
        if(count($conditions) > 0) {
            $q1 .= "WHERE " . implode (' AND ', $conditions);
        }
        
        $query = "
        SELECT
            ".$cells."
        FROM
            `".$this->table_local."`
            ".$q1."
        ORDER BY 
            ".$order."
        LIMIT 
            ".$limit;
        
        $ky = $this->DB->prepare($query);  
        $ky->execute();        
        if($ky->rowCount() < 1){
            return array();
        }else{
            return $ky->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    public function LocalUpdate($uid,$newinfo) {
        if($uid == ""){
            return FALSE;
        }
        
        $f = "id";
        $value = $uid;
        
        $newinfo["updatedOn"] = date("Y-m-d H:i:s");
        $newinfo = $this->FilterDataByTable($newinfo, $this->table_local);
        $cleaninfo = $newinfo["filtered"];
        foreach($cleaninfo as $anahtar=>$bilgi) { 
            if(is_array($bilgi)) { 
                $cleaninfo[$anahtar] = implode(";",$bilgi);
            }
        }
        $query = 'UPDATE '.$this->table_local.' SET';
        $values = array();
        foreach ($cleaninfo as $name => $val) {
            $query .= ' '.$name.' = :'.$name.','; //  :$name 
            $values[':'.$name] = $val; // tutucu
        }

        $query = substr($query, 0, -1); // sonuncuyu , sil             
        $query .= ' WHERE '.$f.'=:'.$f.' ';
        $query .= ';'; //  ; ekle
        $values[':'.$f] = $value;

        $sth = $this->DB->prepare($query);
        $sth->execute($values);
        return $sth->rowCount();        
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
    
    public function GetTableCells($table) {
        $q = $this->DB->prepare("DESCRIBE `".$table."`");
        $q->execute();
        $table_fields = $q->fetchAll(PDO::FETCH_COLUMN);
        return $table_fields;
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
   
}
?>
