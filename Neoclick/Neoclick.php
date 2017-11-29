<?php
namespace IIA\Neoclick;

/**
 *
 * @author pawelszczepaniak
 *
 */
class Neoclick
{
    private $merchantID;
    private $accessKey;
    private $appId;
    private $appSecret;
    private $signingKey;
    private $apiurl;
    /**
     * @return the $merchantID
     */
    public function getMerchantID()
    {
        return $this->merchantID;
    }

    /**
     * @return the $accessKey
     */
    public function getAccessKey()
    {
        return $this->accessKey;
    }

    /**
     * @return the $appId
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @return the $appSecret
     */
    public function getAppSecret()
    {
        return $this->appSecret;
    }

    /**
     * @return the $signingKey
     */
    public function getSigningKey()
    {
        return $this->signingKey;
    }

    /**
     * @return the $apiurl
     */
    public function getApiurl()
    {
        return $this->apiurl;
    }

    /**
     * @param field_type $merchantID
     */
    public function setMerchantID($merchantID)
    {
        $this->merchantID = $merchantID;
    }

    /**
     * @param field_type $accessKey
     */
    public function setAccessKey($accessKey)
    {
        $this->accessKey = $accessKey;
    }


    /**
     * @param field_type $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @param field_type $appSecret
     */
    public function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;
    }

    /**
     * @param field_type $signingKey
     */
    public function setSigningKey($signingKey)
    {
        $this->signingKey = $signingKey;
    }

    /**
     * @param field_type $apiurl
     */
    public function setApiurl($apiurl)
    {
        $this->apiurl = $apiurl;
    }



    /**
     */
    public function __construct()
    {

        // TODO - Insert your code here
    }

    /**
     */
    function __destruct()
    {

        // TODO - Insert your code here
    }

}

