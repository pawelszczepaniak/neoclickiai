<?php
namespace IIA\Neoclick;

/**
 *
 * @author pawelszczepaniak
 *
 */
class NeoclickMerchantAPI
{

    /**
     */
    public function __construct(Neoclick $neoclick)
    {
        $this->neoclick = $neoclick;
    }

    /**
     */
    function __destruct()
    {}

    private function authorizeNeoclick()
    {
       unset($this->neoclick);
    }


    /**
     * @param string $neoclickApiServerOutput
     * @throws NeoClickApiConnectionException
     */
    private static function checkNeoclickApiServerOutput($neoclickApiServerOutput='')
    {
        if ($neoclickApiServerOutput == NULL) {
            throw new NeoClickApiConnectionException();
        }
    }

    public function neoclickCurl($neoclickCallUrl)
    {

    }

    /**
     * @param string $method
     * @param array $apiParameters
     * @return mixed
     */
    private function callNeoclickApi($method='', $apiParameters=array())
    {
        $apiQueryParameters = http_build_query($apiParameters);
        $authCode = base64_encode($this->neoclick->getMerchantID() . ':' . $this->neoclick->getAccessKey());
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->neoclick->getApiurl() . "/" . $method . "?" . $apiQueryParameters);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = array();
        $headers[] = 'Authorization:Basic' . $authCode;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        try {
            $neoclickApiServerOutput = curl_exec($ch);
        } catch (NeoClickApiConnectionException $e) {
            echo $e->errorMessage();
            exit();
        }
        curl_close($ch);
        try {
            self::checkNeoclickApiServerOutput($neoclickApiServerOutput);
        } catch (NeoClickApiConnectionException $e) {
            echo $e->errorMessage();
            exit();
        }
        $data = json_decode($neoclickApiServerOutput);
        return $data;
    }

    /**
     * @param string $method
     * @param string $id
     * @param string $additionalMethod
     * @throws NeoClickApiConnectionException
     * @return mixed
     */
    private function callNeoclickSingle($method='', $id = '', $additionalMethod = '')
    {
        $authCode = base64_encode($this->neoclick->getMerchantID() . ':' . $this->neoclick->getAccessKey());
        $ch = curl_init();
        $neoclickApiUrl = $this->neoclick->getApiurl() . "/" . $method . "/" . $id;
        if (! $additionalMethod == '') {
            $neoclickApiUrl .= "/" . $additionalMethod;
        }
        curl_setopt($ch, CURLOPT_URL, $neoclickApiUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = array();
        $headers[] = 'Authorization:Basic' . $authCode;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


        try {
            $neoclickApiServerOutput = curl_exec($ch);
            if (curl_getinfo($ch)['http_code'] != 200) {
                throw new NeoClickApiConnectionException();
            }
        } catch (NeoClickApiConnectionException $e) {
            echo $e->errorMessage();
            exit();
        }
        curl_close($ch);
        try {
            self::checkNeoclickApiServerOutput($neoclickApiServerOutput);
        } catch (NeoClickApiConnectionException $e) {
            echo $e->errorMessage();
            exit();
        }
        $data = json_decode($neoclickApiServerOutput);
        return $data;
    }

    /**
     * @param string $apps
     * @param string $basketId
     * @param string $corelationId
     * @param string $page
     * @param string $userId
     * @return mixed
     * https://docs.neoclick.io/doc/api/orders
     * method GET
     */
    public function getOrders($apps = '', $basketId = '', $corelationId = '', $page = '', $userId = '')
    {
        $apiParameters = array();

        if (isset($apps) && $apps != '') {
            $apiParameters['apps'] = $apps;
        }

        if (isset($basketId) && $basketId != '') {
            $apiParameters['basketId'] = $basketId;
        }

        if (isset($corelationId) && $corelationId != '') {
            $apiParameters['corelationId'] = $corelationId;
        }

        if (isset($page) && $page != '') {
            $apiParameters['page'] = $page;
        }

        if (isset($userId) && $userId != '') {
            $apiParameters['userId'] = $userId;
        }

        return $this->callNeoclickApi('orders', $apiParameters);
    }

    /**
     * @param string $orderId
     * @return mixed|boolean
     * https://docs.neoclick.io/doc/api/orders/{orderId}
     * method GET
     */
    public function getOrderById($orderId='')
    {
        if (isset($orderId) && $orderId != '') {
            return $this->callNeoclickSingle('orders', $orderId);
        } else {
            return FALSE;
        }
    }

    /**
     * @param string $orderId
     * @return mixed|boolean
     * https://docs.neoclick.io/doc/api/orders/{orderId}/shipments
     */
    public function getOrderShipments($orderId='')
    {
        if (isset($orderId) && $orderId != '') {
            return $this->callNeoclickSingle('orders', $orderId, 'shipments');
        } else {
            return FALSE;
        }
    }

    public function createOrderShipment($orderId='')
    {
        //TODO

    }

    public function changeShipmentConfiguration($orderId='')
    {
        //TODO
    }

    /**
     * @param string $apps
     * @param string $page
     * @return mixed
     * https://docs.neoclick.io/doc/api/payments
     */
    public function getPayments($apps = '', $page = '')
    {
        $apiParameters = array();

        if (isset($apps) && $apps != '') {
            $apiParameters['apps'] = $apps;
        }

        if (isset($page) && $page != '') {
            $apiParameters['page'] = $page;
        }

        return $this->callNeoclickApi('payments', $apiParameters);
    }

    /**
     * @param string $paymentId
     * @return mixed|boolean
     * https://docs.neoclick.io/doc/api/payments/{paymentId}
     */
    public function getPaymentById($paymentId='')
    {
        if (isset($paymentId) && $paymentId != '') {
            return $this->callNeoclickSingle('payments', $paymentId);
        } else {
            return FALSE;
        }
    }

    /**
     * @param string $apps
     * @param string $page
     * @return mixed
     * https://docs.neoclick.io/doc/api/baskets
     */
    public function getBaskets($apps = '', $page = '')
    {
        $apiParameters = array();

        if (isset($apps) && $apps != '') {
            $apiParameters['apps'] = $apps;
        }

        if (isset($page) && $page != '') {
            $apiParameters['page'] = $page;
        }

        return $this->callNeoclickApi('baskets', $apiParameters);
    }

    /**
     * @param string $basketId
     * @return mixed|boolean
     * https://docs.neoclick.io/doc/api/baskets{basketId}
     */
    public function getBasketById($basketId='')
    {
        if (isset($basketId) && $basketId != '') {
            return $this->callNeoclickSingle('baskets', $basketId);
        } else {
            return FALSE;
        }
    }

    /**
     * @return mixed
     * https://docs.neoclick.io/doc/api/merchants
     */
    public function getMerchants()
    {
        return $this->callNeoclickApi('merchants');

    }
}

