<?php

namespace namwansoft\ThaiBankAPI\KBank;

use Yii;

class KAPiMerchant extends KAPi
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
        $this->ApiKey = $ar->merchant_api;
        $this->ApiSecret = $ar->merchant_secret;
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

    public function setHeader($aR)
    {
        $this->Header['ProjectID'] = $aR['ProjectID'] ? 'ProjectID:' . $aR['ProjectID'] : $this->Header['ProjectID'];
        $this->Header['ProjectKey'] = $aR['ProjectKey'] ? 'ProjectKey:' . $aR['ProjectKey'] : $this->Header['ProjectKey'];
        $this->Header['PartnerID'] = $aR['PartnerID'] ? 'PartnerID:' . $aR['PartnerID'] : $this->Header['PartnerID'];
    }

    public function setMerchant($aR)
    {
        // $this->partnerId = $aR['partnerId'];
        // $this->partnerSecret = $aR['partnerSecret'];
        // $this->merchantId = $aR['merchantId'];
    }

    public function payWithCard($Head = [], $Body)
    {
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/mpp/payment/v1/card/charge', $Head, $Body);
        return $cUrl;
    }

    public function payWithKPlus($Head = [], $Body)
    {
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/mpp/payment/v1/appswitch/kplus', $Head, $Body);
        return $cUrl;
    }

    public function payWithQR($Head = [], $Body)
    {
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/mpp/payment/v1/qr', $Head, $Body);
        return $cUrl;
    }

    public function inquiryPayment($Head = [], $Body)
    {
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/mpp/payment/v1/inquiry', $Head, $Body);
        return $cUrl;
    }

    public function payout($Head = [], $Body)
    {
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/mpp/payout/v1/payout', $Head, $Body);
        return $cUrl;
    }

    public function payoutMerchant($Head = [], $Body)
    {
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/mpp/payout/v1/payout-merchant', $Head, $Body);
        return $cUrl;
    }

    public function inquiryPayout($Head = [], $Body)
    {
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/mpp/payout/v1/inquiry', $Head, $Body);
        return $cUrl;
    }

    public function inquiryPayoutMerchant($Head = [], $Body)
    {
        $cUrl = $this->cUrl('POST', $this->Url . '/v1/mpp/payout/v1/inquiry-payout-merchant', $Head, $Body);
        return $cUrl;
    }

    public function SSLVerify()
    {
        return $this->cUrl('POST', $this->UrlSSL, null, null);
    }

    public function tryAPI()
    {
        $res[] = $this->oauth();
        $res[] = $this->payWithCard(['env-id:mpp-paycard', 'RequestID:req-paycard001'], [
            'partnerShopID'          => 'shop001',
            'partnerOrderID'         => 'ORDER000000000001',
            'partnerPaymentID'       => 'PAYMENT0000000001',
            'amount'                 => '100.00',
            'currencyCode'           => 'THB',
            'payoutType'             => 'DELAY',
            'mode'                   => "TOKEN",
            'token'                  => 'tokn_prod_12345678',
            // 'customer'               => (object) ['customerID' => '', 'cardID' => ''],
            'saveCard'               => (object) ['name' => 'test', 'email' => 'test@test.com'],
            'saveFlag'               => true,
            'threeDSFlag'            => true,
            'switchBackURL'          => 'https://mpp-kgptest.web.app',
            'sourceOfFundMerchantID' => 'MERCHANT001',
            'sourceOfFundShopID'     => 'SHOP001',
        ]);
        $res[] = $this->payWithKPlus(['env-id:mpp-paykplus', 'RequestID:req-paykplus001'], [
            'partnerShopID'          => 'shop001',
            'partnerOrderID'         => 'ORDER000000000001',
            'partnerPaymentID'       => 'PAYMENT0000000001',
            'amount'                 => '100.00',
            'currencyCode'           => 'THB',
            'payoutType'             => 'DELAY',
            'switchBackURL'          => 'https://mpp-kgptest.web.app',
            'sourceOfFundMerchantID' => 'MERCHANT001',
            'sourceOfFundShopID'     => 'SHOP001',
        ]);
        $res[] = $this->payWithQR(['env-id:mpp-payqr', 'RequestID:req-payqr001'], [
            'partnerShopID'          => 'shop001',
            'partnerOrderID'         => 'ORDERQR0000000001',
            'partnerPaymentID'       => 'PAYMENTQR00000001',
            'amount'                 => '1000.50',
            'currencyCode'           => 'THB',
            'payoutType'             => 'DELAY',
            'sourceOfFundMerchantID' => 'MERCHANT001',
            'sourceOfFundShopID'     => 'SHOP001',
        ]);
        $res[] = $this->inquiryPayment(['env-id:mpp-inquirypayment', 'RequestID:req-inqpayment001'], ['partnerPaymentID' => 'PAYMENTQR00000001']);
        $res[] = $this->payout(['env-id:mpp-payouts', 'RequestID:req-payoutshop001'], [
            'partnerBatchID' => 'BatchS001',
            'partnerShopID'  => 'shop001',
            'payoutLevel'    => 'S',
            'payments'       => [
                [
                    'partnerPaymentID' => 'PAYMENT0000000001',
                    'distribution'     => (object) [
                        'shopAmount' => '50.00',
                        'partners'   => [
                            ['partnerID' => 'Partner0001', 'amount' => '30.00'],
                            ['partnerID' => 'Partner0002', 'amount' => '20.00'],
                        ],
                    ],
                ],
                [
                    'partnerPaymentID' => 'PAYMENT0000000002',
                    'distribution'     => (object) ['shopAmount' => '100.00'],
                ],
            ],
        ]);
        $res[] = $this->payout(['env-id:mpp-payoutm', 'RequestID:req-payoutshop002'], [
            'partnerBatchID' => 'BatchM001',
            'partnerShopID'  => 'shop001',
            'payoutLevel'    => 'M',
            'payments'       => [
                [
                    'partnerPaymentID' => 'PAYMENT0000000001',
                    'distribution'     => (object) [
                        'shopAmount' => '50.00',
                        'partners'   => [
                            ['partnerID' => 'Partner0001', 'amount' => '30.00'],
                            ['partnerID' => 'Partner0002', 'amount' => '20.00'],
                        ],
                    ],
                ],
                [
                    'partnerPaymentID' => 'PAYMENT0000000002',
                    'distribution'     => (object) ['shopAmount' => '100.00'],
                ],
            ],
        ]);
        $res[] = $this->payoutMerchant(['env-id:mpp-payoutmerchant', 'RequestID:req-payoutmerc001'], [
            'partnerPayoutMerchantID' => 'MerchantBatch001',
            'partnerMerchantID'       => 'merchant001',
            'payouts'                 => ["BatchM001"],
        ]);
        $res[] = $this->inquiryPayout(['env-id:mpp-inquirypayout', 'RequestID:req-inqoutshop001'], ['partnerBatchID' => 'BatchS001']);
        $res[] = $this->inquiryPayoutMerchant(['env-id:mpp-inquirypayoutmerchant', 'RequestID:req-inqoutmer001'], ['partnerPayoutMerchantID' => 'MerchantBatch001']);
        $res[] = $this->SSLVerify();
        return $res;
    }
}
