<?php

include 'lib/lib-base.php';
include 'lib/lib-location.php';
include 'lib/lib-delivery.php';
include 'lib/lib-local.php';

class Application extends ApplicationBase{
    
    public $appname = "location";
    
    public $location;
    public $deliver;
    public $local;

    public function __construct(){
        parent::__construct();
        $this->location = new Location($this->connection);
    }
    
    // -- İSTEK İŞLENİYOR -- //
    public function processrequest($request) {

        if($request["command"]=="LocationGetInfo"){
            /**
            * @api {get} /api/listener.php LocationGetInfo
            * @apiName LocationGetInfo
            * @apiVersion 0.1.0
            * @apiGroup Location
            * @apiDesc Sipariş, K  ve restoran son konumlarını konumlarını döndürür.
            *
            * @apiParam {string} command LocationGetInfo
            * @apiParam {object} data data[loc_id]
            *
            * @apiSuccess {string="succes","error","fail"} status 
            * @apiSuccess {string} [data] Servisten dönen değerleri içerir
            * @apiSuccess {string} [message] Hata mesajlarını içerir
            *
            * @apiSuccessExample Success-Response:
            *      HTTP/1.1 200 OK
            *      {
                        status: "success",
                        data: {
                            //location info
                        }
                    }
            *
            *
            * @apiErrorExample Error-Response:
            *     HTTP/1.1 404 Not Found
            *     {
            *       "status": "error",
            *       "message": "hata mesajı"
            *     }
            */
            try {
                $this->data = $this->location->LocationGetInfo($request["data"]["loc_id"]);
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="LocationGetInfoByType"){
            /**
            * @api {get} /api/listener.php LocationGetInfoByType
            * @apiName LocationGetInfoByType
            * @apiVersion 0.1.0
            * @apiGroup Location
            * @apiDesc Sipariş, K  ve restoran son konumlarını konumlarını döndürür.
            *
            * @apiParam {string} command LocationGetInfoByType
            * @apiParam {object} data data[type]={delivery,order,local} data[type_id] seçili type'a göre ID değeri
            *
            * @apiSuccess {string="succes","error","fail"} Basarılı
            * @apiSuccess {string} [data] Servisten dönen değerleri içerir
            * @apiSuccess {string} [message] Hata mesajlarını içerir
            *
            * @apiSuccessExample Success-Response:
            *      HTTP/1.1 200 OK
            *      {
                        status: "success",
                        data: {
                            //location info
                        }
                    }
            *
            *
            * @apiErrorExample Error-Response:
            *     HTTP/1.1 404 Not Found
            *     {
            *       "status": "error",
            *       "message": "hata mesajı"
            *     }
            */
            try {
                $this->data = $this->location->LocationGetInfoByType($request["data"]["type"],$request["data"]["type_id"]);
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="LocationPush"){
            /**
            * @api {post} /api/listener.php LocationPush
            * @apiName LocationPush
            * @apiDesc K , sipariş ve restoran lokasyonlarını kaydeder.
            * @apiVersion 0.1.0
            * @apiGroup Location
            *
            * @apiParam {string} command LocationPush
            * @apiParam {object} data data[type] data[type_id], data[latitude] ve data[	longitude] zorunlu alanlardır. data[order] değeri eğer ilgili bir sipariş varsa gönderilmesi zorunludur.
            *
            * @apiSuccess {string="succes","error","fail"} Basarılı
            * @apiSuccess {string} [data] Servisten dönen değerleri içerir
            * @apiSuccess {string} [message] Hata mesajlarını içerir
            *
            * @apiSuccessExample Success-Response:
            *      HTTP/1.1 200 OK
            *      {
                        status: "success",
                        data: {
                            //location info
                        }
                    }
            *
            *
            * @apiErrorExample Error-Response:
            *     HTTP/1.1 404 Not Found
            *     {
            *       "status": "error",
            *       "message": "hata mesajı"
            *     }
            */
            try {
                $thelocation = $this->location->LocationPush($request["data"]);
                if($thelocation){
                    $type = $request["data"]["type"];
                    $type_id = $request["data"]["type_id"];
                    $longitude = $request["data"]["longitude"];
                    $latitude = $request["data"]["latitude"];
                    $newlocation = $this->location->LocationGetInfoByType($type, $type_id);
                    $this->data = $newlocation;
                    if($type == "delivery"){
                        $this->delivery = new Delivery($this->connection);
                        $this->delivery->DeliverUpdate($type_id,array(
                            "latitude" => $latitude,
                            "longitude" => $longitude
                        ));
                    }elseif ($type == "local") {
                        $this->local = new Local($this->connection);
                        $this->local->LocalUpdate($type_id,array(
                            "latitude" => $latitude,
                            "longitude" => $longitude
                        ));
                    }
                }else{
                    $this->err = "Konum eklenirken bir hata ile karşışlaşıldı.";
                }
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }else{
            $this->err = "Geçersiz command!";
        }
        
        if($this->err !== ""){
            return $this->returnerror($this->err);
        }else{
            return $this->returndata($this->data);
        }
    }    
}
?>

