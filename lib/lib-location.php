<?php

class Location{
    
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
    
    private $table_location = "kc_location";


    public function LocationGetInfo($lid) {
        if($lid == ""){
            return FALSE;
        }
        $f = "id";
        $query = "
        SELECT
            *
        FROM
            ".$this->table_location."
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
    
    public function LocationGetInfoByType($type,$lid) {
        if($lid == ""){
            return FALSE;
        }
        $query = "
        SELECT
            *
        FROM
            ".$this->table_location."
        WHERE
            type_id = '".$lid."'"
                . " AND "
                . " type = '".$type."'"
                . " ORDER BY id DESC ";
        
        $ky = $this->DB->prepare($query);  
        $ky->execute();        
        if($ky->rowCount() < 1){
            return array();
        }else{
            return $ky->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    public function LocationGetList($conditions=array(),$order="id DESC",$limit=1000,$cells="*") {
         
        $q1 = "";
        if(count($conditions) > 0) {
            $q1 .= "WHERE " . implode (' AND ', $conditions);
        }
        
        $query = "
        SELECT
            ".$cells."
        FROM
            `".$this->table_location."`
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
    
    public function LocationPush($data) {
        if (!is_array($data) || !count($data)){
            return false;  
        }
        if($data["order_id"] == ""){
            $data["order_id"] = "0";
        }
        $statement = $this->DB->prepare("INSERT INTO ".$this->table_location."(type,type_id,order_id,latitude,longitude,createdOn) VALUES(:type,:type_id,:order_id,:latitude,:longitude,:createdOn)");
        return $statement->execute(array(
            "type" => $data["type"],
            "type_id" => $data["type_id"],
            "order_id" => $data["order_id"],
            "latitude" => $data["latitude"],
            "longitude" => $data["longitude"],
            "createdOn" => date("Y-m-d H:i:s")
        ));
    }
    
    public function LocationGetDistance($a,$b) {
        $q = "http://maps.googleapis.com/maps/api/distancematrix/json?origins=".$a."&destinations=".$b."&mode=driving&sensor=false";
        $json = file_get_contents($q);
        $mapdata = json_decode($json, TRUE);
        if($mapdata){
            $data = array();
            $data["origin"] = $mapdata["origin_addresses"][0];
            $data["destination"] = $mapdata["destination_addresses"][0];
            $data["distance"] = 0;
            $data["duration"] = 0;
            foreach ($mapdata["rows"] as $row) {
                foreach ($row["elements"] as $element) {
                    if($element["status"] == "OK"){
                        $data["distance"] += $element["distance"]["value"];
                        $data["duration"] += $element["duration"]["value"];
//                        $data["distance"] = "2423423";
//                        $data["duration"] = "ddadad";
                    }
                }
            }
            return $data;
            
        }else{
            return false;
        }
    }
   
}
?>
