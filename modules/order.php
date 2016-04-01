<?php

include 'lib/lib-base.php';
include 'lib/lib-order.php';
include 'lib/lib-local.php';
include 'lib/lib-delivery.php';

class Application extends ApplicationBase{
    
    public $appname = "order";
    
    public $order;
    public $transaction;
    public $delivery;

    public function __construct(){
        parent::__construct();
        $this->order = new Order($this->connection);
        $this->local = new Local($this->connection);
        $this->delivery = new Delivery($this->connection);
    }
    
    public function GetFreeDeliveryList() {
        $conditions = array();
        $conditions[] = " delivery_id != '0' ";
        $conditions[] = " (status = 'pending' OR status = 'going' OR status = 'coming') ";
        $activeOrders = $this->order->OrderGetList($conditions);
        if($activeOrders){
            $BusyDeliveries = array();
            foreach ($activeOrders as $order) {
                $BusyDeliveries[] = $order["delivery_id"];
            }
            if(is_array($BusyDeliveries)){
                $query = " id NOT IN('".implode("','", $BusyDeliveries)."') ";
                $conditions = array();
                $conditions[] = $query;
                $FreeDeliveries = $this->delivery->DeliveryGetList($conditions);
                if(is_array($FreeDeliveries)){
                    return $FreeDeliveries;
                }
            }
            return array();
        }
        return array();
    }
    
    public function DealOutOrders() {
        //Check there are open orders
        $conditions = array();
        $conditions[] = " delivery_id = '0' ";
        $conditions[] = " status = 'open' ";
        $activeOrders = $this->order->OrderGetList($conditions);
        if($activeOrders){
//                //get order id
//                $this->order->OrderUpdate($openOrders[0]["id"], array(
//                    "delivery_id" => $request["data"]["delivery_id"],
//                    "status" => "pending"
//                ));
//                $this->data = $this->order->OrderGetInfo($openOrders[0]["id"]);
        }
                
    }
    
    // -- İSTEK İŞLENİYOR -- //
    public function processrequest($request) {

        if($request["command"]=="OrderGetInfo"){
            /**
            * @api {get} /api/listener.php OrderGetInfo
            * @apiName OrderGetInfo
            * @apiVersion 0.1.0
            * @apiGroup Order
            * @apiDesc Sipariş detaylarını döndürür
            *
            * @apiParam {string} command OrderGetInfo
            * @apiParam {object} data Restaurant ID Değeri
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
                            local_id: "3",
                            price: "70",
                            from: "Cocacola Şişhane ",
                            to: "Taksim Bambi Cafe",
                            status: "pending",
                            createdOn: "2016-03-08 00:00:00",
                            updatedOn: "2016-03-10 00:00:00"
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
                $this->data = $this->order->OrderGetInfo($request["data"]["oid"]);
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="OrderGetList"){
            /**
            * @api {get} /api/listener.php OrderGetList
            * @apiName OrderGetList
            * @apiVersion 0.1.0
            * @apiGroup Order
            * @apiDesc Sipariş listesini döndürür
            *
            * @apiParam {string} command OrderGetList
            * @apiParam {object} data Siparişler için gerekli filtreler uygulanır. Ör: data[status]=pending
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
                $this->data = $this->order->OrderGetList();
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="OrderCancel"){
            /**
            * @api {get} /api/listener.php OrderCancel
            * @apiName OrderCancel
            * @apiVersion 0.1.0
            * @apiGroup Order
            * @apiDesc Siparişi iptal eder
            *
            * @apiParam {string} command OrderGetList
            * @apiParam {object} data data[oid] ile sipariş id'si gönderilir.
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
                            //Güncel sipariş bilgileri
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
                $theorder = $this->order->OrderGetInfo($request["data"]["oid"]);
                if($theorder){
                    if($theorder !== "canceled" || $theorder !== "completed" ){
                        $canceled = $this->order->OrderUpdate($request["data"]["oid"],array("status"=>"canceled"));
                        if($canceled){
                            $this->data = $this->order->OrderGetInfo($request["data"]["oid"]);
                        }else{
                            $this->err = "Sipariş iptali sıraasında bir hata ile karşılaşıldı.";
                        }
                    }else{
                        $this->err = "Tamamlanmış/İptal edilmiş siparişi iptal edemezsiniz.";
                    }
                }else{
                    $this->err = "Böyle bir sipariş bulunmamaktadır!";
                }
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="OrderCreate"){
            /**
            * @api {get} /api/listener.php OrderCreate
            * @apiName OrderCreate
            * @apiVersion 0.1.0
            * @apiGroup Order
            * @apiDesc Yeni sipariş oluşturur.
            *
            * @apiParam {string} command OrderCreate
            * @apiParam {object} data Sipariş bilgilerini barındırır. Zorunlu alanlar. local_id, payment_method[cash,creditcard,foodcard]
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
                            //Oluşturulan sipariş bilgileri
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
                if($request["data"]["local_id"] == ""){
                    $this->err = "Restoran(local_id) bilgisi olmadan sipariş oluşturamazsınız.";
                }elseif($request["data"]["payment_method"] == ""){
                    $this->err = "Ödeme şekli belirtilmedi!";
                }else{
                    $thelocal = $this->local->LocalGetInfo($request["data"]["local_id"]);
                    if($thelocal){
                        $request["data"]["from"] = $thelocal["address"];
                        $theorder = $this->order->OrderCreate($request["data"]);
                        if($theorder){
                            $this->data = "Sipariş başarılı bir şekilde oluşturuldu.".  serialize($theorder);
                        }else{
                            $this->err = "Sipariş oluşturulma sırasında bir hata ile karşılaşıldı!";
                        }
                    }else{
                        $this->err = "Restoran bilgisi bulunamadı!";
                    }
                }

            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="OrderApprove"){
            /**
            * @api {get} /api/listener.php OrderApprove
            * @apiName OrderApprove
            * @apiVersion 0.1.0
            * @apiGroup Order
            * @apiDesc K  siparişi kabul eder
            *
            * @apiParam {string} command OrderApprove
            * @apiParam {object} data data[delivery_id] ve data[order_id] zorunlu alanlardır.
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
                            //Sipariş bilgileri
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
                $order_id = $request["data"]["order_id"];
                //check if order is pending and delivery_id set to this.delivery_id
                $theorder = $this->order->OrderGetInfo($order_id);
                if($theorder){
                    if($theorder["status"] == "pending"){
                        //check if this.delivery_id has other open orders if so cancel them
                        $thedelivery = $this->delivery->DeliveryGetInfo($delivery_id);
                        if($thedelivery["status"]){
                            $this->order->OrderUpdate($order_id, array(
                               "delivery_id" => $delivery_id,
                               "startedOn" => date("Y-m-d H:i:s"),
                               "status" => "coming" 
                            ));
                            $this->data = $this->order->OrderGetInfo($order_id);
                        }else{
                            $this->err = "K  durumu geçersiz. Lütfen yetkili ile görüşünüz.";
                        }
                    }else{
                        $this->err = "Sipariş durumu geçersiz.";
                    }
                }else{
                    $this->err = "Böyle bir sipariş bulunmamaktadır.";
                }                
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="OrderTake"){
            /**
            * @api {get} /api/listener.php OrderTake
            * @apiName OrderTake
            * @apiVersion 0.1.0
            * @apiGroup Order
            * @apiDesc K  siparişi restorandan alır. Sipariş fiyatını günceller.
            *
            * @apiParam {string} command OrderTake
            * @apiParam {object} data data[delivery_id], data[order_id] ve data[price] zorunlu alanlardır.
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
                            //Sipariş bilgileri
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
                $order_id = $request["data"]["order_id"];
                $price = $request["data"]["price"];
                if($price){
                    $theorder = $this->order->OrderGetInfo($order_id);
                    if($theorder){
                        if($theorder["status"] == "coming"){
                            $thedelivery = $this->delivery->DeliveryGetInfo($delivery_id);
                            if($thedelivery["status"]){
                                $this->order->OrderUpdate($order_id, array(
                                   "delivery_id" => $delivery_id,
                                    "price" => $price,
                                   "status" => "going" 
                                ));
                                $this->data = $this->order->OrderGetInfo($order_id);
                            }else{
                                $this->err = "K  durumu geçersiz. Lütfen yetkili ile görüşünüz.";
                            }
                        }else{
                            $this->err = "Sipariş durumu geçersiz.";
                        }
                    }else{
                        $this->err = "Böyle bir sipariş bulunmamaktadır.";
                    }    
                }else{
                    $this->err = "Sipariş fiyatı girilmelidir.";
                }
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="OrderDeliver"){
            /**
            * @api {get} /api/listener.php OrderDeliver
            * @apiName OrderDeliver
            * @apiVersion 0.1.0
            * @apiGroup Order
            * @apiDesc K  siparişi restorandan alır. Sipariş fiyatını günceller.
            *
            * @apiParam {string} command OrderDeliver
            * @apiParam {object} data data[delivery_id] ve data[order_id] zorunlu alanlardır.
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
                            //Sipariş bilgileri
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
                $order_id = $request["data"]["order_id"];
                $theorder = $this->order->OrderGetInfo($order_id);
                if($theorder){
                    if($theorder["status"] == "going"){
                        $thedelivery = $this->delivery->DeliveryGetInfo($delivery_id);
                        if($thedelivery["status"]){
                            $this->order->OrderUpdate($order_id, array(
                                "delivery_id" => $delivery_id,
                                "finishedOn" => date("Y-m-d H:i:s"),
                                "status" => "completed" 
                            ));
                            $this->data = $this->order->OrderGetInfo($order_id);
                        }else{
                            $this->err = "K  durumu geçersiz. Lütfen yetkili ile görüşünüz.";
                        }
                    }else{
                        $this->err = "Sipariş durumu geçersiz.";
                    }
                }else{
                    $this->err = "Böyle bir sipariş bulunmamaktadır.";
                }    
            } catch ( Exception $e ) {
                $this->err = $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL;
            }
//            
        }elseif($request["command"]=="OrderDecline"){
            /**
            * @api {get} /api/listener.php OrderDecline
            * @apiName OrderDecline
            * @apiVersion 0.1.0
            * @apiGroup Order
            * @apiDesc K  siparişi reddeder
            *
            * @apiParam {string} command OrderDecline
            * @apiParam {object} data data[delivery_id] ve data[order_id] zorunlu alanlardır.
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
                            //Sipariş bilgileri
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
                $order_id = $request["data"]["order_id"];
                $theorder = $this->order->OrderGetInfo($order_id);
                if($theorder){
                    if($theorder["status"] == "pending"){
                        $thedelivery = $this->delivery->DeliveryGetInfo($delivery_id);
                        if($thedelivery["status"]){
                            $this->order->OrderUpdate($order_id, array(
                               "delivery_id" => "0",
                               "status" => "open" 
                            ));
                            $this->delivery->DeliveryPushDeclined($delivery_id,$order_id);
                            $this->data = $this->order->OrderGetInfo($order_id);
                        }else{
                            $this->err = "K  durumu geçersiz. Lütfen yetkili ile görüşünüz.";
                        }
                    }else{
                        $this->err = "Sipariş durumu geçersiz.";
                    }
                }else{
                    $this->err = "Böyle bir sipariş bulunmamaktadır.";
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

