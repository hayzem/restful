<?php

include 'lib/lib-base.php';
include 'lib/lib-local.php';
include 'lib/lib-order.php';

class Application extends ApplicationBase{
    
    public $appname = "local";
    
    public $local;

    public function __construct(){
        parent::__construct();
        $this->local = new Local($this->connection);
        $this->order = new Order($this->connection);
    }
    
    // -- İSTEK İŞLENİYOR -- //
    public function processrequest($request) {

        if($request["command"]=="LocalGetInfo"){
            /**
            * @api {get} /api/listener.php LocalGetInfo
            * @apiName LocalGetInfo
            * @apiVersion 0.1.0
            * @apiGroup Local
            * @apiDesc dadadadwawda
            *
            * @apiParam {string} command LocalGetInfo
            * @apiParam {object} data data[lid] değeri geçerli restoran ID'si içermelidir
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
                            id: "4",
                            name: "Falafel House 4",
                            email: "",
                            phone: "",
                            address: "",
                            passwd: "",
                            contact_name: "",
                            contact_surname: "",
                            contanct_phone: "",
                            status: "",
                            createdOn: "0000-00-00 00:00:00",
                            updatedOn: "0000-00-00 00:00:00"
                        }
            *       }
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
                $thelocal = $this->local->LocalGetInfo($request["data"]["lid"]);
                $thelocal["passwd"] = "******";
                $this->data = $thelocal;
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="LocalGetStatus"){
            /**
            * @api {get} /api/listener.php LocalGetStatus
            * @apiName LocalGetStatus
            * @apiVersion 0.1.0
            * @apiGroup Local
            * @apiDesc Restoran durumunu kontrol eder. Restoran durumunu döndürür.
            *
            * @apiParam {string} command LocalGetStatus
            * @apiParam {object} data data[lid] değeri geçerli restoran ID'si içermelidir.
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
                            id: "3",
                            local_id: "2",
                            price: "70",
                            from: "Manisa Kebap",
                            to: "Rızaamba Kokreçmisi",
                            status: "coming",
                            createdOn: "2016-03-03 00:00:00",
                            updatedOn: "2016-03-04 00:00:00"
                        }
            *       }
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
                $thelocal = $this->local->LocalGetInfo($request["data"]["lid"]);
                if($thelocal){
                    $con = array();
                    $con[] = "local_id = '".$request["data"]["lid"]."'";
                    $orders = $this->order->OrderGetList($con,"id DESC",1);
                    if($orders){
                        $this->data =  $orders[0];
                    }else{
                        $this->data = array();
                    }
                }else{
                    $this->err = "Böyle bir restoran bulunmamaktadır.";
                }
                
//                $this->data = $this->local->LocalGetInfo($request["data"]["lid"]);
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="LocalLogin"){
            /**
            * @api {get} /api/listener.php LocalLogin
            * @apiName LocalLogin
            * @apiVersion 0.1.0
            * @apiGroup Local
            * @apiDesc Restoran eposta ve şifresini kontrol eder.
            *
            * @apiParam {string} command LocalLogin
            * @apiParam {object} data data[email] ve data[password] zorunlu alanlardır
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
                            id: "1",
                            name: "Falafel House 1",
                            email: "restoran01@test.com",
                            phone: "+905554440011",
                            address: "",
                            passwd: "******",
                            contact_name: "ContantNane",
                            contact_surname: "Surname",
                            contanct_phone: "",
                            status: "",
                            createdOn: "0000-00-00 00:00:00",
                            updatedOn: "0000-00-00 00:00:00"
                        }
            *       }
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
                $check = $this->local->LocalLogin($request["data"]["email"],$request["data"]["password"]);
                if($check){
                    $check["passwd"] = "******";
                    $this->data = $check;
                }else{
                    $this->err = "Eposta/Şifre hatalı.".  serialize($check);
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

