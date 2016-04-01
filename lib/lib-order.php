<?php

class Order{
    
    //Database settings
    private $dbname = "";
    private $dbuser = "";
    private $dbpass = "";
    private $dbhost = "";
    private $dbchar = "";


    private $DB;
    
    public function __construct($connection) {
        try{
            $this->DB = new PDO('mysql:host='.$connection["dbhost"].';dbname='.$connection["dbname"].';charset=utf8',$connection["dbuser"] ,  $connection["dbpass"] );
        }catch(PDOException $ex){
            die(json_encode(array('outcome' => false, 'message' => 'Unable to connect')));
        }    
    }
    
    private $table_order = "kc_order";

    public function OrderGetInfo($lid) {
        if($lid == ""){
            return FALSE;
        }
        $f = "id";
        
        $query = "SELECT
            *
        FROM
            `".$this->table_order."`
        WHERE
            `".$f."` = '".$lid."'";
        
        $ky = $this->DB->prepare($query);
        $ky->execute();        
        if($ky->rowCount() < 1){
            return array($query);
        }else{
            return $ky->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    public function OrderGetList($conditions=array(),$order="id DESC",$limit=1000,$cells="*") {
         
        $q1 = "";
        if(count($conditions) > 0) {
            $q1 .= "WHERE " . implode (' AND ', $conditions);
        }
        
        $query = "
        SELECT
            ".$cells."
        FROM
            `".$this->table_order."`
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
    
    public function OrderUpdate($uid,$newinfo) {
        if($uid == ""){
            return FALSE;
        }
        
        $f = "id";
        $value = $uid;
        
        $newinfo["updatedOn"] = date("Y-m-d H:i:s");
        $newinfo = $this->FilterDataByTable($newinfo, $this->table_order);
        $cleaninfo = $newinfo["filtered"];
        foreach($cleaninfo as $anahtar=>$bilgi) { 
            if(is_array($bilgi)) { 
                $cleaninfo[$anahtar] = implode(";",$bilgi);
            }
        }
        $query = 'UPDATE '.$this->table_order.' SET';
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
    
    public function OrderCreate($data) {
        if (!is_array($data) || !count($data)){
            return false;  
        }
        $statement = $this->DB->prepare("INSERT INTO ".$this->table_order."(local_id, payment_method, status,createdOn)
            VALUES(:local_id, :payment_method, :status, :createdOn)");
        return $statement->execute(array(
            "local_id" => $data["local_id"],
            "payment_method" => $data["payment_method"],
            "status" => "open",
            "createdOn" => date("Y-m-d H:i:s")
        ));
        
//        $info = $this->FilterDataByTable($data, $this->table_order);
//        $cleaninfo = $info["filtered"];
//        foreach($cleaninfo as $anahtar=>$bilgi) { 
//            if(is_array($bilgi)) { 
//                $cleaninfo[$anahtar] = implode(";",$bilgi);
//            }
//        }
//        $data = $cleaninfo;
//        $bind = ':'.implode(',:', array_keys($data));
//        $sql  = 'INSERT INTO '.$this->table_order.'('.implode(',', array_keys($data)).') '.
//                'values ('.$bind.')';
//        $this->DB->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
//        $stmt = $this->DB->prepare($sql);
//        $stmt->execute(array_combine(explode(',',$bind), array_values($data)));
//        if ($stmt->rowCount() > 0){
//           return true;
//        }
////        return false;
//        return $this->DB->errorInfo();
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
