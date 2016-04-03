<?php

/* 
 * API GENEL AYARLAININ VE UYGULAMALARIN TANIMLANDIĞI AYAR DOSYASI
 */

class Services{
    
    public $apidomain = "domain";
    public $apiurl = "http://domain/apilocation";

    public $ssl = "OFF"; //ON
    public $jsonmod = "ON"; //OFF
    public $keeplogs = "ON"; //OFF
    
    public $request = array("key"=>"","command"=>"","data"=>"","page"=>"");
    public $result  = array("status"=>"","data"=>array(),"message"=>"");
    
    # MODULES
    public $apps = array(   
        "local"=>array(  "appclass"   =>  "modules/local.php"),
        "delivery"=>array(  "appclass"   =>  "modules/delivery.php"),
        "location"=>array(  "appclass"   =>  "modules/location.php"),
        "order"=>array(  "appclass"   =>  "modules/order.php")
    );
    
    # KEYS
    private $keys = array(
        array("key"=>"TESTKEY","token"=>"TESTTOKEN","apps"=>array(
            "local","order","delivery","location"
        )),
        array("key"=>"TESTKEY2","token"=>"TESTTOKEN2","apps"=>array(
            "users"
        ))
    );
    
    public $commands = array(
        //Delivery
        "DeliveryGetInfo" => "delivery",
        "DeliveryGetStatus" => "delivery",
        "DeliveryGetList" => "delivery",
        "DeliveryLogin" => "delivery",
        "DeliveryPushTest" => "delivery",
        
        //Locations
        "LocationGetInfo" => "location",
        "LocationPush" => "location",
        
        //Orders
        "OrderGetInfo" => "order",
        "OrderGetList" => "order",
        "OrderCancel" => "order",
        "OrderCreate" => "order",
        "OrderApprove" => "order",
        "OrderDecline" => "order",
        "OrderDeliver" => "order",
        "OrderTake" => "order",
        
        //Restautans
        "LocalLogin"  =>  "local",
        "LocalGetInfo"  =>  "local",
        "LocalGetStatus"  =>  "local"
        
    );
    
    public $activetedapps = array();
    public $currentapp = array();
    
    public function sslcheck(){
        if($this->ssl == "ON"){
            if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])){
               return TRUE;
            }else{
                $this->result["status"] = "fail";
                $this->result["message"] = "Baglanti reddedildi. SSL, guvenli baglanti yok.";// SSL HATASI //Requested representation not available for the resource.
                $this->sendresult();
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }
    
    public function requestcheck(){
        if(!isset($this->request["key"]) || !isset($this->request["command"])){
            $this->result["status"]             = "fail"; // PARAMETRE EKSIK //Malformed syntax or a bad query.
            $this->result["message"]            = "command, key parametresi eksik!";       
            $this->sendresult();     
        }
    }

    public function securitycheck() {
        foreach ($this->keys as $key) {
            if($key["key"] == $this->request["key"]){
                return $this->activetedapps = $key["apps"]; 
            }
        }
        return $this->returnerror("fail","isteginiz yetkilendirilemedi");
    }
    
    public function callapps(){
        //check if requested command's app in the actived modulsapps
        $this->currentapp = $this->commands[$this->request["command"]];
        if(in_array($this->currentapp, $this->activetedapps)){
            return $this->currentapp;
        }else{
            return $this->returnerror("fail","ilgili modulu kullanma yetkiniz yok");;
        }
    }

    public function returnerror($errorcode,$errorinfo){
        $this->result["status"]                     = $errorcode; //success,fail,error
        $this->result["data"]                       = array();
        $this->result["message"]                    = $errorinfo;
        $this->log(serialize($this->result), "ERROR");
        return $this->sendresult();
    }
    
    public function sendresult(){
        if($this->jsonmod == "ON"){
            echo json_encode($this->result);
        }else{
            print_r($this->result);
        }
        die();
    }
    
    public function log($data,$type="NULL") {
        if($this->keeplogs == "OFF" || $this->keeplogs == FALSE ){
            return TRUE;
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $filename = "logs.log";
        $fh = fopen($filename, "a") or die("Could not open log file.");
        fwrite($fh, "[#]".date("d-m-Y, H:i")." [#]".$ip." [#]".session_id()." [#]".$type." [#]".$data."\n") or die("Could not write file!");
        fclose($fh);
        return TRUE;
    }
}