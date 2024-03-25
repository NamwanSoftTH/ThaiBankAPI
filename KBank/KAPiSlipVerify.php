<?php

namespace namwansoft\ThaiBankAPI\KBank;

use Yii;

class KAPiSlipVerify extends KAPi
{
    private $Url = 'https://openapi.kasikornbank.com';
    private $UrlSSL = 'https://openapi.kasikornbank.com/exercise/ssl';
    public $Header, $isTest, $isV1, $ApiKey, $ApiSecret, $ApiToken, $isSSL, $partnerId, $partnerSecret, $merchantId;

    public function __construct($isV1 = false, $ar = null, $isTest = false, $SSL = false)
    {
        $this->isTest = $isTest;
        $this->isV1 = $isV1;
        if ($this->isTest) {
            $this->Url = 'https://openapi-sandbox.kasikornbank.com';
            $this->UrlSSL = 'https://openapi-test.kasikornbank.com/exercise/ssl';
        }
        $this->ApiKey = $ar->slip_verification_api;
        $this->ApiSecret = $ar->slip_verification_secret;
        $this->ApiToken = Yii::$app->cache->get(get_class($this) . '_' . $this->ApiKey);
        $this->isSSL = $SSL;
    }

    public function oauth()
    {
        if ($this->ApiToken) {
            return ['status' => false, 'msg' => 'use Old Key', 'token' => $this->ApiToken];
        }
        $cUrl = $this->cUrl('POST', $this->Url . '/v2/oauth/token', [
            'Authorization:Basic ' . base64_encode($this->ApiKey . ':' . $this->ApiSecret),
            'Content-Type:application/x-www-form-urlencoded', 'env-id:OAUTH2'],
            http_build_query(['grant_type' => 'client_credentials']), false
        );
        $this->ApiToken = ($cUrl['access_token']) ? $cUrl['access_token'] : null;
        Yii::$app->cache->set(get_class($this) . '_' . $this->ApiKey, $this->ApiToken, 29 * (60 * 1000));
        return $cUrl;
    }

    public function SSLVerify()
    {
        return $this->cUrl('POST', $this->UrlSSL, null, null);
    }

    public function tryAPI()
    {
        $res[] = $this->oauth();
        $res[] = $this->SSLVerify();
        return $res;
    }
}
