<?php

namespace namwansoft\ThaiBankAPI\KBank;

use Yii;

class KAPiQR extends KAPi
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
        $this->ApiKey = $ar->api;
        $this->ApiSecret = $ar->secret;
        $this->ApiToken = Yii::$app->cache->get(get_class($this) . '_' . $this->ApiKey);
        $this->isSSL = $SSL;
    }

    public function oauth()
    {
        if ($this->ApiToken) {
            return ['status' => false, 'msg' => 'use Old Key', 'token' => $this->ApiToken];
        }
        $cUrl = $this->cUrl('POST', $this->Url . (($this->isV1) ? '/oauth/token' : '/v2/oauth/token'), [
            'Authorization:Basic ' . base64_encode($this->ApiKey . ':' . $this->ApiSecret),
            'Content-Type:application/x-www-form-urlencoded', 'env-id:OAUTH2'],
            http_build_query(['grant_type' => 'client_credentials']), false
        );
        $this->ApiToken = ($cUrl['access_token']) ? $cUrl['access_token'] : null;
        Yii::$app->cache->set(get_class($this) . '_' . $this->ApiKey, $this->ApiToken, 29 * (60 * 1000));
        return $cUrl;
    }

    public function setMerchant($aR)
    {
        $this->partnerId = $aR['partnerId'];
        $this->partnerSecret = $aR['partnerSecret'];
        $this->merchantId = $aR['merchantId'];
    }

    public function genQR($Head = [], $Body)
    {
        $Body = array_merge($Body, [
            'partnerId'       => $this->partnerId,
            'partnerSecret'   => $this->partnerSecret,
            'merchantId'      => $this->merchantId,
            'requestDt'       => true,
            'qrType'          => '3',
            'txnCurrencyCode' => 'THB',
        ]);
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/qrpayment/request', $Head, $Body);
        return $cUrl;
    }

    public function genQRCredit($Head = [], $Body)
    {
        $Body = array_merge($Body, [
            'partnerId'       => $this->partnerId,
            'partnerSecret'   => $this->partnerSecret,
            'merchantId'      => $this->merchantId,
            'requestDt'       => true,
            'qrType'          => '4',
            'txnCurrencyCode' => 'THB',
        ]);
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/qrpayment/request', $Head, $Body);
        return $cUrl;
    }

    public function inquiryQR($Head = [], $Body)
    {
        $Body = array_merge($Body, [
            'partnerId'     => $this->partnerId,
            'partnerSecret' => $this->partnerSecret,
            'merchantId'    => $this->merchantId,
            'requestDt'     => true,
        ]);
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/qrpayment/v4/inquiry', $Head, $Body);
        return $cUrl;
    }

    public function cancelQR($Head = [], $Body)
    {
        $Body = array_merge($Body, [
            'partnerId'     => $this->partnerId,
            'partnerSecret' => $this->partnerSecret,
            'merchantId'    => $this->merchantId,
            'requestDt'     => true,
        ]);
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/qrpayment/cancel', $Head, $Body);
        return $cUrl;
    }

    public function voidPayment($Head = [], $Body)
    {
        $Body = array_merge($Body, [
            'partnerId'     => $this->partnerId,
            'partnerSecret' => $this->partnerSecret,
            'merchantId'    => $this->merchantId,
            'requestDt'     => true,
        ]);
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/qrpayment/void', $Head, $Body);
        return $cUrl;
    }

    public function SSLVerify()
    {
        return $this->cUrl('POST', $this->UrlSSL, null, null);
    }

    public function tryAPI()
    {
        $res[] = $this->oauth();
        $res[] = $this->genQR(['env-id:QR002'], ['partnerTxnUid' => 'PARTNERTEST0001', 'txnAmount' => '100.5', 'reference1' => 'INV001', 'reference2' => 'HELLOWORLD', 'reference3' => 'INV001', 'reference4' => 'INV001']);
        $res[] = $this->genQRCredit(['env-id:QR003'], ['partnerTxnUid' => 'PARTNERTEST0001-2', 'txnAmount' => '100.5', 'reference1' => 'INV001', 'reference2' => 'HELLOWORLD', 'reference3' => 'INV001', 'reference4' => 'INV001']);
        $res[] = $this->inquiryQR(['env-id:QR004'], ['partnerTxnUid' => 'PARTNERTEST0002', 'origPartnerTxnUid' => 'PARTNERTEST0001']);
        $res[] = $this->inquiryQR(['env-id:QR005'], ['partnerTxnUid' => 'PARTNERTEST0003', 'origPartnerTxnUid' => 'TESTCANCELQR001']);
        $res[] = $this->inquiryQR(['env-id:QR006'], ['partnerTxnUid' => 'PARTNERTEST0004', 'origPartnerTxnUid' => 'PARTNERTEST0007']);
        $res[] = $this->inquiryQR(['env-id:QR007'], ['partnerTxnUid' => 'PARTNERTEST0005', 'origPartnerTxnUid' => 'PARTNERTEST0011']);
        $res[] = $this->cancelQR(['env-id:QR008'], ['partnerTxnUid' => 'PARTNERTEST0006', 'origPartnerTxnUid' => 'PARTNERTEST0001']);
        $res[] = $this->cancelQR(['env-id:QR010'], ['partnerTxnUid' => 'PARTNERTEST0007', 'origPartnerTxnUid' => 'PARTNERTEST0007']);
        $res[] = $this->cancelQR(['env-id:QR011'], ['partnerTxnUid' => 'PARTNERTEST0008', 'origPartnerTxnUid' => 'PARTNERTEST0011']);
        $res[] = $this->voidPayment(['env-id:QR012'], ['partnerTxnUid' => 'PARTNERTEST0009', 'origPartnerTxnUid' => 'PARTNERTEST0011']);
        $res[] = $this->voidPayment(['env-id:QR013'], ['partnerTxnUid' => 'PARTNERTEST0009-2', 'origPartnerTxnUid' => 'PARTNERTEST0001-2']);
        $res[] = $this->voidPayment(['env-id:QR014'], ['partnerTxnUid' => 'PARTNERTEST0010', 'origPartnerTxnUid' => 'PARTNERTEST0017']);
        $res[] = $this->voidPayment(['env-id:QR015'], ['partnerTxnUid' => 'PARTNERTEST0011', 'origPartnerTxnUid' => 'PARTNERTEST0016']);
        $res[] = $this->voidPayment(['env-id:QR016'], ['partnerTxnUid' => 'PARTNERTEST0012', 'origPartnerTxnUid' => 'PARTNERTEST0007']);
        $res[] = $this->SSLVerify();
        return $res;
    }
}
