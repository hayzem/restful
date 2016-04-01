<?php

include 'lib/lib-base.php';
include 'lib/lib-local.php';
include 'lib/lib-order.php';
include 'lib/lib-delivery.php';
include 'lib/lib-transaction.php';
include 'lib/lib-push.php';

class Application extends ApplicationBase{
    
    public $appname = "delivery";
    
    public $delivery;
    public $transaction;
    public $order;
    public $push;

    public function __construct(){
        parent::__construct();
        $this->delivery = new Delivery($this->connection);
        $this->transaction = new Transaction($this->connection);
        $this->order = new Order($this->connection);
        $this->push = new pushmessage();
    }
    
    // -- İSTEK İŞLENİYOR -- //
    public function processrequest($request) {

        if($request["command"]=="DeliveryGetInfo"){
            /**
            * @api {get} /api/listener.php DeliveryGetInfo
            * @apiName DeliveryGetInfo
            * @apiVersion 0.1.0
            * @apiGroup Delivery
            * @apiDesc K  bilgilerini döndürür
            *
            * @apiParam {string} command DeliveryGetInfo
            * @apiParam {object} data K  ID Değeri
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
                            id: "2",
                            name: "Onur",
                            surname: "K ",
                            phone: "+905544208011",
                            address: "Şahkulu Mahallesi Ora Sokak No:45/1",
                            status: "1",
                            createdOn: "2016-03-21 00:00:00",
                            updatedOn: "2016-03-22 00:00:00"
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
                $this->data = $this->delivery->DeliveryGetInfo($request["data"]["did"]);
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="DeliveryGetStatus"){
            /**
            * @api {get} /api/listener.php DeliveryGetStatus
            * @apiName DeliveryGetStatus
            * @apiVersion 0.1.0
            * @apiGroup Delivery
            * @apiDesc K  aktif siparişi döndürür
            *
            * @apiParam {string} command DeliveryGetStatus
            * @apiParam {object} data[delivery_id]
            *
            * @apiSuccess {string="succes","error","fail"} Basarili
            * @apiSuccess {string} [data] Servisten dönen değerleri içerir
            * @apiSuccess {string} [message] Hata mesajlarını içerir
            *
            * @apiSuccessExample Success-Response:
            *      HTTP/1.1 200 OK
            *      {
                        status: "success",
                        data: {
                            id: "19",
                            local_id: "2",
                            delivery_id: "3",
                            price: "",
                            payment_method: "cash",
                            from: "",
                            to: "",
                            status: "pending",
                            createdOn: "0000-00-00 00:00:00",
                            updatedOn: "2016-03-22 05:17:38"
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
                $delivery_id = $request["data"]["delivery_id"];
                $delivery = $this->delivery->DeliveryGetInfo($delivery_id);
                $declinedFor = json_decode($delivery["declinedFor"], TRUE);
                $declinedForQ = " id NOT IN  ('".implode("' ,'", $declinedFor)."' )";
                //Check if K  has ongoing delivery
                $conditions = array();
                $conditions[] = " delivery_id = '".$delivery_id."' ";
                $conditions[] =  $declinedForQ;
                $conditions[] = " ( status = 'open' OR status = 'pending' OR status = 'coming' OR status = 'going' ) ";
                $activeOrders = $this->order->OrderGetList($conditions);
                if($activeOrders){
                    $this->data = $activeOrders[0];
                }else{
                    //Check if there is an open order
                    $conditions = array();
                    $conditions[] = " delivery_id = '' ";
                    $conditions[] = " status = 'open' ";
                    $openOrders = $this->order->OrderGetList($conditions);
                    if($openOrders[0]){
                        //get order id
                        $this->order->OrderUpdate($openOrders[0]["id"], array(
                            "delivery_id" => $request["data"]["delivery_id"],
                            "status" => "pending"
                        ));
                        $delivery = $this->delivery->DeliveryGetInfo($request["data"]["delivery_id"]);
                        $mobiles = array(
                            array("pushtype"=>"android","idphone"=>$delivery["mobile_id"])
                        );
                        $params = array(
                            "msg" => "Sizi bekleyen yeni bir sipariş var. Görüntülemek için tıklayın!"
                        );
                        $this->push->send($mobiles,$params);
                        $this->data = $this->order->OrderGetInfo($openOrders[0]["id"]);
                    }else{
                        $this->data = array();
                    }
                }
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="DeliveryGetList"){
            /**
            * @api {get} /api/listener.php DeliveryGetList
            * @apiName DeliveryGetList
            * @apiVersion 0.1.0
            * @apiGroup Delivery
            * @apiDesc K  listesini döndürür
            *
            * @apiParam {string} command DeliveryGetList
            * @apiParam {object} data K  liste filtreleri uygulanır. Mevcut durumda aktif filtreler: yok
            *
            * @apiSuccess {string="succes","error","fail"} Basarılı
            * @apiSuccess {string} [data] Servisten dönen değerleri içerir
            * @apiSuccess {string} [message] Hata mesajlarını içerir
            *
            * @apiSuccessExample Success-Response:
            *      HTTP/1.1 200 OK
            *      {
                        status: "success",
                        data: [
                            {...},
                            {...},
                            {...},
                            ...
                        ]
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
                $this->data = $this->delivery->DeliveryGetList();
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="DeliveryLogin"){
            /**
            * @api {get} /api/listener.php DeliveryLogin
            * @apiName DeliveryLogin
            * @apiVersion 0.1.0
            * @apiGroup Delivery
            * @apiDesc K  eposta ve şifresini kontrol eder.
            *
            * @apiParam {string} command DeliveryLogin
            * @apiParam {object} data data[email] ve data[password] zorunlu alanlardır.  data[mobile_id] seçmeli alandır.
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
                $check = $this->delivery->DeliveryLogin($request["data"]["email"],$request["data"]["password"]);
                if($check){
                    $check["passwd"] = "******";
                    $this->data = $check;
                    if($request["data"]["mobile_id"]){
                        $this->delivery->DeliverUpdate($check["id"], array(
                            "mobile_id" => $request["data"]["mobile_id"]
                        ));
                    }

                }else{
                    $this->err = "Eposta/Şifre hatalı.";
                }
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="DeliveryPushTest"){
            /**
            * @api {get} /api/listener.php DeliveryPushTest
            * @apiName DeliveryPushTest
            * @apiVersion 0.1.0
            * @apiGroup Delivery
            * @apiDesc Push test
            *
            * @apiParam {string} command DeliveryPushTest
            * @apiParam {object} data data[delivery_id] ve data[msg] zorunlu alanlardır. 
            *
            * @apiSuccess {string="succes","error","fail"} status 
            * @apiSuccess {string} [data] Servisten dönen değerleri içerir
            * @apiSuccess {string} [message] Hata mesajlarını içerir
            *
            * @apiSuccessExample Success-Response:
            *      HTTP/1.1 200 OK
            *      {
                        status: "success",
                        data: []
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
            $params = array(
                "msg" => $request["data"]["msg"]
            );
            $delivery = $this->delivery->DeliveryGetInfo($request["data"]["delivery_id"]);
            if($delivery){
                $mobiles = array(
                    array("pushtype"=>"android","idphone"=>$delivery["mobile_id"])
                );
                $this->push->send($mobiles,$params);
                $this->data = array($mobiles,$params);
            }else{
                $this->err = "Böyle bir K  bulunamadı!";
            }

            
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

