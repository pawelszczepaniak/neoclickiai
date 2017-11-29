<?php
ini_set('display_errors', 1);
include 'Neoclick/Neoclick.php';
include 'Neoclick/NeoclickMerchantAPI.php';
include 'Neoclick/NeoClickApiConnectionException.php';
use IIA\Neoclick\Neoclick;
use IIA\Neoclick\NeoclickMerchantAPI;
use IIA\Neoclick\NeoClickApiConnectionException;

$neoclickData = file_get_contents('php://input');
$neoclickDataTable = json_decode($neoclickData);

ob_start();
var_dump($neoclickDataTable);
$output = ob_get_clean();

$outputFile = "/tmp/output.txt";
$fileHandle = fopen($outputFile, "a") or die('File creation error.');
fwrite($fileHandle, $output);
fclose($fileHandle);

$neoclickAppId = '6269318634238120323';
$neoclickMerchantId = '1';
$neoclickAccessKey = '145b5687148c51d213014a6f9e5ae4d4';
$neoclickAppSecret = '';
$neoclickSigningKey = 'd925857054cf93a480deccedb80248df';
$neoclickApiUrl = 'https://merchant.api.neoclick.io/v1';
$neoclick = new Neoclick();
$neoclick->setMerchantID($neoclickMerchantId);
$neoclick->setAccessKey($neoclickAccessKey);
$neoclick->setAppId($neoclickAppId);
$neoclick->setAppSecret($neoclickAppSecret);
$neoclick->setSigningKey($neoclickSigningKey);
$neoclick->setApiurl($neoclickApiUrl);

$neoclickMerchantAPI = new NeoclickMerchantAPI($neoclick);
$neoclickOrderData = $neoclickMerchantAPI->getOrderById($neoclickDataTable->orderId);
$neoclickBasketData = $neoclickMerchantAPI->getBasketById($neoclickOrderData->basketId);


$fp = fopen("/tmp/neoclicklock.txt", "rw+");

if (flock($fp, LOCK_EX)) {
    ftruncate($fp, 0);
    fwrite($fp, $neoclickOrderData->id);


$query = "SELECT COUNT(id) as ile FROM neoclick_status WHERE iaiOrdered=1 AND orderId =" . $neoclickOrderData->id;

try {
    $conn = new PDO('mysql:host=localhost;dbname=neoclick', 'xxx', 'xxx');

    $stmt = $conn->query($query);
    $row = $stmt->fetchAll();
    $orderCount = $row[0]['ile'];

    if ($orderCount < 1) {
        $orderExists = 0;
    } else {
        $orderExists = 1;
        echo 'OK';
        exit();
    }
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

if ($neoclickOrderData->status != 'readyToProcess') {
    echo 'OK';
    exit();
}

$data = array(
    "currency_id" => "PLN",
    "email" => $neoclickOrderData->configuration->contactEmail,
    "shipping_cost" => $neoclickOrderData->shipmentTotalPrice,
    "neoclick_order_id" => $neoclickOrderData->id,
    'total_price' => $neoclickOrderData->totalPrice,
    "paymentMethodID" => $neoclickOrderData->configuration->paymentMethodId,
    "shipping_address" => array(
        "firstname" => $neoclickOrderData->configuration->shipmentParams->deliveryAddress->firstName,
        "lastname" => $neoclickOrderData->configuration->shipmentParams->deliveryAddress->lastName,
        "street" => $neoclickOrderData->configuration->shipmentParams->deliveryAddress->street,
        "houseNumber" => $neoclickOrderData->configuration->shipmentParams->deliveryAddress->houseNumber,
        "flatNumber" => $neoclickOrderData->configuration->shipmentParams->deliveryAddress->flatNumber,
        "city" => $neoclickOrderData->configuration->shipmentParams->deliveryAddress->city,
        "country_id" => "PL",
        "region" => "xx",
        "postcode" => $neoclickOrderData->configuration->shipmentParams->deliveryAddress->postalCode,
        "telephone" => $neoclickOrderData->configuration->shipmentParams->phoneNumber,
        "fax>" => $neoclickOrderData->configuration->shipmentParams->phoneNumber,
        "save_in_address_book" => 1,
        "vatNumber" => $neoclickOrderData->configuration->invoice->vatNumber,
        "invoiceName" => $neoclickOrderData->configuration->invoice->name,
        "invoiceStreet" => $neoclickOrderData->configuration->invoice->street,
        "invoiceHouseNumber" => $neoclickOrderData->configuration->invoice->houseNumber,
        "invoiceFlatNumber" => $neoclickOrderData->configuration->invoice->flatNumber,
        "invoiceCity" => $neoclickOrderData->configuration->invoice->city,
        "invoicePostalCode" => $neoclickOrderData->configuration->invoice->postalCode
    )
);

$address = 'http://neotest.iai-shop.com/api/?gate=addorders/addOrders/39/json';

$systemKey = sha1(date('Ymd') . sha1('xxx'));

$request = array();
$request['authenticate'] = array();
$request['authenticate']['system_key'] = $systemKey;
$request['authenticate']['system_login'] = "neotest";
$request['params'] = array();
$request['params']['orders'] = array();
$request['params']['orders'][0] = array();
$request['params']['orders'][0]['order_type'] = "retail";
$request['params']['orders'][0]['shop_id'] = 1;
$request['params']['orders'][0]['stock_id'] = 1;

if ($neoclickOrderData->configuration->shipmentServices[0] == 'cod') {
    $request['params']['orders'][0]['payment_type'] = 'cash_on_delivery';
} else {
    $request['params']['orders'][0]['payment_type'] = 'prepaid';
}

$request['params']['orders'][0]['currency'] = "PLN";
$request['params']['orders'][0]['client_once'] = "y";
$request['params']['orders'][0]['client_once_data'] = array();

if ($data["shipping_address"]["vatNumber"] != '') {

    $request['params']['orders'][0]['client_once_data']['firm'] = $data["shipping_address"]["invoiceName"];
    $request['params']['orders'][0]['client_once_data']['nip'] = $data["shipping_address"]["vatNumber"];
    $request['params']['orders'][0]['client_once_data']['street'] = $data["shipping_address"]["invoiceStreet"] . ' ' . $data["shipping_address"]["invoiceHouseNumber"];
    if ($data["shipping_address"]["invoiceFlatNumber"] != '') {
        $request['params']['orders'][0]['client_once_data']['street'] .= '/' . $data["shipping_address"]["invoiceFlatNumber"];
    }

    $request['params']['orders'][0]['client_once_data']['zip_code'] = $data["shipping_address"]["invoicePostalCode"];
    $request['params']['orders'][0]['client_once_data']['city'] = $data["shipping_address"]["invoiceCity"];
    $request['params']['orders'][0]['client_once_data']['country'] = "Polska";
} else {

    $request['params']['orders'][0]['client_once_data']['firstname'] = $data["shipping_address"]["firstname"];
    $request['params']['orders'][0]['client_once_data']['lastname'] = $data["shipping_address"]["lastname"];
    $request['params']['orders'][0]['client_once_data']['street'] = $data["shipping_address"]["street"] . ' ' . $data["shipping_address"]["houseNumber"];

    if ($data["shipping_address"]["flatNumber"] != '') {
        $request['params']['orders'][0]['client_once_data']['street'] .= '/' . $data["shipping_address"]["flatNumber"];
    }

    $request['params']['orders'][0]['client_once_data']['zip_code'] = $data["shipping_address"]["postcode"];
    $request['params']['orders'][0]['client_once_data']['city'] = $data["shipping_address"]["city"];
    $request['params']['orders'][0]['client_once_data']['country'] = "Polska";
}

$request['params']['orders'][0]['client_once_data']['email'] = $data["email"];
$request['params']['orders'][0]['client_once_data']['phone1'] = $data["shipping_address"]["telephone"];
$request['params']['orders'][0]['client_once_data']['phone2'] = "";
$request['params']['orders'][0]['client_once_data']['language_id'] = "pl";
$request['params']['orders'][0]['client_login'] = "";
$request['params']['orders'][0]['client_note'] = "";

$deliveryMethod = $neoclickOrderData->configuration->shipmentMethodId;

switch ($deliveryMethod) {
    case 'Paczkomaty':
        $request['params']['orders'][0]['deliverer_id'] = 77;
        break;
    case 'inpostCourier':
        $request['params']['orders'][0]['deliverer_id'] = 100045;
        break;
    case 'inpostLocker':
        $request['params']['orders'][0]['deliverer_id'] = 100045;
        break;

    case 'glsCourier':
        $request['params']['orders'][0]['deliverer_id'] = 85;
        break;
    case 'paczka48':
        $request['params']['orders'][0]['deliverer_id'] = 85;
        break;
    case 'personalCollection':
        $request['params']['orders'][0]['deliverer_id'] = 85;
        break;
    case 'virtualCollection':
        $request['params']['orders'][0]['deliverer_id'] = 85;
        break;
    case 'selfDeliveryToAddress':
        $request['params']['orders'][0]['deliverer_id'] = 85;
        break;
}





$request['params']['orders'][0]['delivery_cost'] = ($neoclickOrderData->shipmentTotalPrice - $neoclickOrderData->discountTotalPrice) / 100;

$request['params']['orders'][0]['delivery_address'] = array();
$request['params']['orders'][0]['delivery_address']['firstname'] = $data["shipping_address"]["firstname"];
$request['params']['orders'][0]['delivery_address']['lastname'] = $data["shipping_address"]["lastname"];
$request['params']['orders'][0]['delivery_address']['additional'] = "";
$request['params']['orders'][0]['delivery_address']['street'] = $data["shipping_address"]["street"] . ' ' . $data["shipping_address"]["houseNumber"];

if ($data["shipping_address"]["flatNumber"] != '') {
    $request['params']['orders'][0]['delivery_address']['street'] .= '/' . $data["shipping_address"]["flatNumber"];
}

$request['params']['orders'][0]['delivery_address']['zip_code'] = $data["shipping_address"]["postcode"];
$request['params']['orders'][0]['delivery_address']['city'] = $data["shipping_address"]["city"];
$request['params']['orders'][0]['delivery_address']['country'] = "Polska";
$request['params']['orders'][0]['delivery_address']['phone'] = $data["shipping_address"]["telephone"];



if ($neoclickOrderData->configuration->shipmentMethodId == 'inpostLocker' || $neoclickOrderData->configuration->shipmentMethodId == 'Paczkomaty') {


    $request['params']['orders'][0]['delivery_address']['street'] = $neoclickOrderData->configuration->shipmentParams->additionalId;
    $request['params']['orders'][0]['delivery_address']['city'] = $neoclickOrderData->configuration->shipmentParams->additionalId;
    //$request['params']['orders'][0]['delivery_address']['zip_code'] = $neoclickOrderData->configuration->shipmentParams->additionalId;


}


$request['params']['orders'][0]['products'] = array();

$request['params']['orders'][0]['products'][0] = array();

$i = 0;

foreach ($neoclickBasketData->articles as $article) {

    $productData = explode("_", $article->id);

    $request['params']['orders'][0]['products'][$i]['id'] = $productData[0];

    switch ($productData[1]) {
        case 'xxs':
            $sizeId = 1;
            break;
        case 'xs':
            $sizeId = 2;
            break;
        case 's':
            $sizeId = 3;
            break;
        case 'm':
            $sizeId = 4;
            break;
        case 'l':
            $sizeId = 5;
            break;
        case 'xl':
            $sizeId = 6;
            break;
        default:
            $sizeId = $productData[1];
    }

    $request['params']['orders'][0]['products'][$i]['size_id'] = $sizeId;

    // $request['params']['orders'][0]['products'][$i]['size_id'] = $productData[1];
    $request['params']['orders'][0]['products'][$i]['product_sizecode'] = '';
    $request['params']['orders'][0]['products'][$i]['stock_id'] = 1;
    $request['params']['orders'][0]['products'][$i]['quantity'] = $article->quantity;
    $request['params']['orders'][0]['products'][$i]['price'] = $article->price / 100;
    $i ++;
}

$request['params']['orders'][0]['rebate'] = 0.0;
$request['params']['orders'][0]['order_operator'] = "NeoClickAPI: " . $neoclickOrderData->id;
$request['params']['orders'][0]['ignore_bridge'] = true;
$request['params']['orders'][0]['settings'] = array();
$request['params']['orders'][0]['settings']['send_mail'] = true;
$request['params']['orders'][0]['settings']['send_sms'] = false;
if ($data["shipping_address"]["vatNumber"] != '') {
    $request['params']['orders'][0]['invoice_requested'] = "y";
} else {
    $request['params']['orders'][0]['invoice_requested'] = "n";
}

$request_json = json_encode($request);
$headers = array(
    'Accept: application/json',
    'Content-Type: application/json;charset=UTF-8'
);

$curl = curl_init($address);
curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($curl);
$jsonResponse = json_decode($response);

$orderId = $jsonResponse->result->orders[0]->order_sn;

$status = curl_getinfo($curl);
curl_close($curl);

if ($neoclickOrderData->configuration->shipmentServices[0] != 'cod') {

    $address = 'http://neotest.iai-shop.com/api/?gate=payments/addPayment/21/json';

    $systemKey = sha1(date('Ymd') . sha1('Frania2014@#'));

    $request = array();
    $request['authenticate'] = array();
    $request['authenticate']['system_key'] = $systemKey;
    $request['authenticate']['system_login'] = "neotest";
    $request['params']['order_number'] = $orderId;

    $request['params']['value'] = $data['total_price'] / 100;
    $request['params']['type'] = 'payment';

    switch ($data['paymentMethodID']) {
        case 'ECARD_CARD':
            $request['params']['payment_form_id'] = 20;
            break;
        case 'ECARD_BLIK':
            $request['params']['payment_form_id'] = 177;
            break;
        case 'ECARD_MASTERPASS':
            $request['params']['payment_form_id'] = 173;
            break;
        case 'DOTPAY_MTRANSFER':
            $request['params']['payment_form_id'] = 126;
            break;
        case 'DOTPAY_MILLENIUM':
            $request['params']['payment_form_id'] = 9;
            break;
        case 'DOTPAY_IPKONET':
            $request['params']['payment_form_id'] = 37;
            break;
        case 'DOTPAY_WBK_PRZELEW24':
            $request['params']['payment_form_id'] = 14;
            break;
        case 'DOTPAY_CITI_HANDLOWY':
            $request['params']['payment_form_id'] = 17;
            break;
        case 'DOTPAY_BPH':
            $request['params']['payment_form_id'] = 137;
            break;
        case 'DOTPAY_IPKO':
            $request['params']['payment_form_id'] = 37;
            break;
        case 'DOTPAY_PEKAO24':
            $request['params']['payment_form_id'] = 36;
            break;
        case 'DOTPAY_PEOPAY':
            $request['params']['payment_form_id'] = 165;
            break;
        case 'DOTPAY_BOS':
            $request['params']['payment_form_id'] = 10;
            break;
        case 'DOTPAY_ALIOR':
            $request['params']['payment_form_id'] = 4;
            break;
        case 'DOTPAY_TMOBILE':
            $request['params']['payment_form_id'] = 135;
            break;
        case 'DOTPAY_TOYOTA':
            $request['params']['payment_form_id'] = 41;
            break;
        case 'DOTPAY_AGRICOLE':
            $request['params']['payment_form_id'] = 28;
            break;
        case 'DOTPAY_EUROBANK':
            $request['params']['payment_form_id'] = 20;
            break;
        case 'DOTPAY_ING':
            $request['params']['payment_form_id'] = 24;
            break;
        case 'DOTPAY_DBTRANSFER':
            $request['params']['payment_form_id'] = 18;
            break;
        case 'DOTPAY_PLUSBANK':
            $request['params']['payment_form_id'] = 26;
            break;
        case 'DOTPAY_IDEABANK':
            $request['params']['payment_form_id'] = 156;
            break;
        case 'DOTPAY_POCZTOWY24':
            $request['params']['payment_form_id'] = 179;
            break;
        case 'DOTPAY_ORANGE':
            $request['params']['payment_form_id'] = 178;
            break;
        case 'DOTPAY_VOLKSWAGEN':
            $request['params']['payment_form_id'] = 42;
            break;
        case 'DOTPAY_PRZELEW':
            $request['params']['payment_form_id'] = 110;
            break;
        case 'DOTPAY_INTELIGO':
            $request['params']['payment_form_id'] = 25;
            break;
        case 'DOTPAY_GETIN':
            $request['params']['payment_form_id'] = 22;
            break;
        case 'DOTPAY_NOBLE':
            $request['params']['payment_form_id'] = 18;
            break;
        case 'DOTPAY_PBS':
            $request['params']['payment_form_id'] = 12;
            break;
        case 'DOTPAY_IDEA_CLOUD':
            $request['params']['payment_form_id'] = 156;
            break;

        default:
            $request['params']['payment_form_id'] = 9;
            break;
    }

    $request['params']['accounting_date'] = date("Y-m-d H:i:s");
    $request['params']['external_payment_id'] = $data['neoclick_order_id'];

    $request_json = json_encode($request);
    $headers = array(
        'Accept: application/json',
        'Content-Type: application/json;charset=UTF-8'
    );

    $curl = curl_init($address);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);

    $jsonResponse = json_decode($response);

    $paymentId = $jsonResponse->result->payment_id;
    $status = curl_getinfo($curl);
    curl_close($curl);

    $address = 'http://neotest.iai-shop.com/api/?gate=payments/confirm/0/json';

    $systemKey = sha1(date('Ymd') . sha1('Frania2014@#'));

    $request = array();
    $request['authenticate'] = array();
    $request['authenticate']['system_key'] = $systemKey;
    $request['authenticate']['system_login'] = "neotest";
    $request['params'] = array();
    $request['params']['payment_number'] = $paymentId;
    $request['params']['source_type'] = 'order';

    $request_json = json_encode($request);
    $headers = array(
        'Accept: application/json',
        'Content-Type: application/json;charset=UTF-8'
    );

    $curl = curl_init($address);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($curl);
    $status = curl_getinfo($curl);
    curl_close($curl);
}

$query = "INSERT INTO neoclick_status(`orderId`,`status`, `data`, `iaiOrdered`, `iaiOrderId`) VALUES(
'$neoclickDataTable->orderId',
'$neoclickOrderData->status',
'$neoclickData',
'1',
'$orderId'

)";

try {

    $result = $conn->query($query);
}

catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

echo 'OK';
exit();


fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);
}

else {
    echo "Couldn't get the lock!";
}







