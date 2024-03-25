<?php

namespace namwansoft\ThaiBankAPI\KBank;

use Yii;

class KAPiCorporateFundTransfer extends KAPi
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
        $this->ApiKey = $ar->corporate_fund_transfer_api;
        $this->ApiSecret = $ar->corporate_fund_transfer_secret;
        $this->ApiToken = Yii::$app->cache->get(get_class($this) . '_' . $this->ApiKey);
        $this->isSSL = $SSL;
    }

    public function setMerchant($aR)
    {
        $this->merchantId = $aR['merchantID'];
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

    public function inquiryAccount($Head = [], $Body)
    {
        $Body = array_merge($Body, [
            'merchantID' => $this->merchantId,
            // 'requestDateTime' => true,
        ]);
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/fundtransfer/verifydata', $Head, $Body);
        return $cUrl;
    }

    public function fundTransfer($Head = [], $Body)
    {
        $Body = array_merge($Body, [
            'merchantID' => $this->merchantId,
            // 'requestDateTime' => true,
        ]);
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/fundtransfer/fundtransfer', $Head, $Body);
        return $cUrl;
    }

    public function inquiryTxnStatus($Head = [], $Body)
    {
        $Body = array_merge($Body, [
            'merchantID' => $this->merchantId,
            // 'requestDateTime' => true,
        ]);
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/fundtransfer/inqtxnstatus', $Head, $Body);
        return $cUrl;
    }

    public function SSLVerify()
    {
        return $this->cUrl('POST', $this->UrlSSL, null, null);
    }

    public function tryAPI()
    {
        $res[] = $this->oauth();
        $res[] = $LastEx = $this->inquiryAccount(['env-id:CFT001'], [
            'transType'       => 'K2K',
            'merchantTransID' => '1005_20220101_0000000000000000000000101',
            'requestDateTime' => '2022-01-01T22:28:39.000Z',
            'proxyType'       => '10',
            'proxyValue'      => '0268571833',
            'fromAccountNo'   => '1112333000',
            'senderName'      => 'Sompong',
            'senderTaxID'     => '0001301120098',
            'toBankCode'      => '004',
            'amount'          => '2000.99',
            'typeOfSender'    => 'K',
        ]);
        $res[] = $this->fundTransfer(['env-id:CFT002'], [
            'merchantTransID'  => $LastEx['merchantTransID'],
            'rsTransID'        => $LastEx['rsTransID'],
            'requestDateTime'  => '2022-01-01T22:28:39.000Z',
            'customerMobileNo' => '0991115588',
            'ref1'             => '',
            'ref2'             => '',
        ]);
        $res[] = $LastEx = $this->inquiryAccount(['env-id:CFT003'], [
            'transType'       => 'K2O',
            'merchantTransID' => '1005_20220101_0000000000000000000000102',
            'requestDateTime' => '2022-01-01T22:28:39.000Z',
            'proxyType'       => '10',
            'proxyValue'      => '9991115000',
            'fromAccountNo'   => '1112333000',
            'senderName'      => 'Sompong',
            'senderTaxID'     => '0001301120098',
            'toBankCode'      => '025',
            'amount'          => '2000.99',
            'typeOfSender'    => 'K',
        ]);
        $res[] = $this->fundTransfer(['env-id:CFT004'], [
            'merchantTransID'  => $LastEx['merchantTransID'],
            'rsTransID'        => $LastEx['rsTransID'],
            'requestDateTime'  => '2022-01-01T22:28:39.000Z',
            'customerMobileNo' => '0991115588',
            'ref1'             => '',
            'ref2'             => '',
        ]);
        $res[] = $LastEx = $this->inquiryAccount(['env-id:CFT005'], [
            'transType'       => 'K2O',
            'merchantTransID' => '1005_20220101_0000000000000000000000103',
            'requestDateTime' => '2022-01-01T22:28:39.000Z',
            'proxyType'       => '01',
            'proxyValue'      => '3331200123456',
            'fromAccountNo'   => '1112333000',
            'senderName'      => 'Sompong',
            'senderTaxID'     => '0001301120098',
            'toBankCode'      => '',
            'amount'          => '2000.99',
            'typeOfSender'    => 'K',
        ]);
        $res[] = $this->fundTransfer(['env-id:CFT006'], [
            'merchantTransID'  => $LastEx['merchantTransID'],
            'rsTransID'        => $LastEx['rsTransID'],
            'requestDateTime'  => '2022-01-01T22:28:39.000Z',
            'customerMobileNo' => '0991115588',
            'ref1'             => '',
            'ref2'             => '',
        ]);
        $res[] = $LastEx = $this->inquiryAccount(['env-id:CFT007'], [
            'transType'       => 'K2O',
            'merchantTransID' => '1005_20220101_0000000000000000000000104',
            'requestDateTime' => '2022-01-01T22:28:39.000Z',
            'proxyType'       => '02',
            'proxyValue'      => '0631999999',
            'fromAccountNo'   => '1112333000',
            'senderName'      => 'Sompong',
            'senderTaxID'     => '0001301120098',
            'toBankCode'      => '',
            'amount'          => '2000.99',
            'typeOfSender'    => 'K',
        ]);
        $res[] = $this->fundTransfer(['env-id:CFT008'], [
            'merchantTransID'  => $LastEx['merchantTransID'],
            'rsTransID'        => $LastEx['rsTransID'],
            'requestDateTime'  => '2022-01-01T22:28:39.000Z',
            'customerMobileNo' => '0991115588',
            'ref1'             => '',
            'ref2'             => '',
        ]);
        $res[] = $this->inquiryAccount(['env-id:CFT009'], [
            'transType'       => 'K2K',
            'merchantTransID' => '1005_20220101_0000000000000000000000105',
            'requestDateTime' => '2022-01-01T22:28:39.000Z',
            'proxyType'       => '10',
            'proxyValue'      => '0268571833',
            'fromAccountNo'   => '1112333000',
            'senderName'      => 'Sompong',
            'senderTaxID'     => '0001301120098',
            'toBankCode'      => '004',
            'amount'          => '50000.00',
            'typeOfSender'    => 'K',
        ]);
        $res[] = $LastEx = $this->inquiryAccount(['env-id:CFT010'], [
            'transType'       => 'K2K',
            'merchantTransID' => '1005_20220101_0000000000000000000000106',
            'requestDateTime' => '2022-01-01T22:28:39.000Z',
            'proxyType'       => '10',
            'proxyValue'      => '4063554123',
            'fromAccountNo'   => '1112333000',
            'senderName'      => 'Sompong',
            'senderTaxID'     => '0001301120098',
            'toBankCode'      => '004',
            'amount'          => '25000.00',
            'typeOfSender'    => 'K',
        ]);
        $res[] = $this->fundTransfer(['env-id:CFT011'], [
            'merchantTransID'  => $LastEx['merchantTransID'],
            'rsTransID'        => $LastEx['rsTransID'],
            'requestDateTime'  => '2022-01-01T22:28:39.000Z',
            'customerMobileNo' => '0991115588',
            'ref1'             => 'ref1',
            'ref2'             => 'ref2',
        ]);
        $res[] = $LastEx = $this->inquiryAccount(['env-id:CFT012'], [
            'transType'       => 'K2K',
            'merchantTransID' => '1005_20220101_0000000000000000000000107',
            'requestDateTime' => '2022-01-01T22:28:39.000Z',
            'proxyType'       => '10',
            'proxyValue'      => '4063554123',
            'fromAccountNo'   => '1112333000',
            'senderName'      => 'Sompong',
            'senderTaxID'     => '0001301120098',
            'toBankCode'      => '004',
            'amount'          => '2000.99',
            'typeOfSender'    => 'K',
        ]);
        $res[] = $this->fundTransfer(['env-id:CFT013'], [
            'merchantTransID'  => $LastEx['merchantTransID'],
            'rsTransID'        => $LastEx['rsTransID'],
            'requestDateTime'  => '2022-01-01T22:28:39.000Z',
            'customerMobileNo' => '0991115588',
            'ref1'             => 'ref1',
            'ref2'             => 'ref2',
        ]);
        $res[] = $this->inquiryTxnStatus(['env-id:CFT014'], [
            'merchantTransID' => $LastEx['merchantTransID'],
            'rsTransID'       => $LastEx['rsTransID'],
            'requestDateTime' => '2022-01-01T22:28:39.000Z',
        ]);
        $res[] = $this->SSLVerify();
        return $res;
    }
}
