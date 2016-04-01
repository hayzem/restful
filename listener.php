<?php
    ob_start();
    session_start();    
    error_reporting(E_ERROR);
    ini_set('display_errors', 0);

    // -- ISTEKLER -- //
    $ky     = $_REQUEST['key'];        //zorunlu
    $cmd    = $_REQUEST['command'];    //zorunlu
    $dtls   = $_REQUEST['data'];    //isteğe bağlı
    $pg     = $_REQUEST['page'];    //isteğe bağlı

    // -- ISTEKLERI TEMIZLE -- //
    
    // -- GENEL API VE APP AYARLARI -- //
    include 'services.php';
    $api = new Services();
    
    // -- ISTEKLER APININ ANLAYACAGI SEKILDE AKTARILIYOR -- //
    $api->request = array("key"=>$ky,"command"=>$cmd,"data"=>$dtls,"page"=>$pg);
    $api->log(serialize($api->request),"REQUEST");
    
    // -- KONTROLLER -- //
    $api->sslcheck();
    $api->requestcheck();
    $api->securitycheck();
  
    // -- ISTEK ILE GELEN KEY HANGI UYGULAMANIN KONTROL EDILIYOR
    $api->callapps();
    
    // -- ILGILI UYGULAMANIN GEREKLI DOSYALARI EKLENIP ISTEK AKTARILIYOR -- //
    include getcwd()."/".$api->apps[$api->currentapp]["appclass"];
    $app = new Application();
    $api->result = $app->processrequest($api->request);
    
    // -- UYGULAMANIN GERI DONDURDUGU SONUCLAR GONDERILIYOR -- //
    header("Access-Control-Allow-Origin: *"); //Test için eklendi. Production'da silinmeli.
    header('Content-Type: application/json; charset=utf-8');
    ob_end_clean();
    $api->sendresult();

?>
