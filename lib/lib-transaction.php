<?php

class Transaction{
    
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
    
    private $table_transaction = "kc_transaction";

    
    public function TransactionGetList($conditions=array(),$order="id DESC",$limit=1000,$cells="*") {
        $q1 = "";
        if(count($conditions) > 0) {
            $q1 .= "WHERE " . implode (' AND ', $conditions);
        }
        
        $query = "
        SELECT
            ".$cells."
        FROM
            `".$this->table_transaction."`
            ".$q1."
        ORDER BY 
            ".$order."
        LIMIT 
            ".$limit;
        
        $ky = $this->DB->prepare($query);  
        $ky->execute();        
        if($ky->rowCount() < 1){
            return;
        }else{
            return $ky->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    public function TransactionGetInfo($conditions=array(),$order="id DESC",$limit=1000,$cells="*") {
        $q1 = "";
        if(count($conditions) > 0) {
            $q1 .= "WHERE " . implode (' AND ', $conditions);
        }
        $query = "
        SELECT
            ".$cells."
        FROM
            `".$this->table_transaction."`
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
            return $ky->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    public function TransactionCreate($data) {
//        if($data == ""){
//            return FALSE;
//        }

        $query = "INSERT INTO `{$this->table_transaction}` (`local_id`, `order_id`, `delivery_id`, `status`, `createdOn`) VALUES (:local_id, :order_id, :delivery_id, :status, :createdOn)";
        $stmt = $this->DB->prepare($query);
        return $stmt->execute(array(
            ':local_id' => $data["local_id"],
            ':order_id' => $data["order_id"],
            ':delivery_id' => $data["delivery_id"],
            ':status' => $data["status"],
            ':createdOn' => date("Y-m-d H:i:s")
        ));
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
