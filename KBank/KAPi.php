<?php
namespace namwansoft\ThaiBankAPI\KBank;

use Yii;

class KAPi
{

    public function cUrl($Method, $Url, $Header = [], $Body = [], $isJson = true)
    {
        $reqDTNow = date('Y-m-d\TH:i:s' . substr((string) microtime(), 1, 4) . '\Z');
        if ($isJson) {
            $Header = array_merge(['Accept:application/json', 'Content-Type:application/json'], $Header ?? []);
            $Header = ($this->ApiToken) ? array_merge(['Authorization:Bearer ' . $this->ApiToken], $Header ?? []) : $Header;
            if (is_array($Body)) {
                if ($Body['requestDt']) {
                    $Body['requestDt'] = $reqDTNow;
                }
                // if ($Body['requestDateTime']) {
                //     $Body['requestDateTime'] = $reqDTNow;
                // }
            }
            $Body = json_encode($Body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $Header = ($this->Header) ? array_merge($Header, $this->Header) : $Header;
        $Header = ($this->isTest) ? array_merge($Header, ['x-test-mode:true']) : $Header;
        // return $Header;
        // return $Body;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $Method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $Header);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        if ($this->isSSL) {
            curl_setopt($ch, CURLOPT_SSLCERT, $this->isSSL . '.crt');
            curl_setopt($ch, CURLOPT_SSLKEY, $this->isSSL . '.key');
            // curl_setopt($ch, CURLOPT_CAINFO, $this->isSSL . '-ca.crt');
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        }
        $res = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($res, true);
        switch ($res['message']) {
            case 'Access Token expired':
            case 'Invalid Access Token':
                $this->ApiToken = false;
                Yii::$app->cache->delete(get_class($this) . '_' . $this->ApiKey);
                $this->oauth();
                break;
            default:
        }
        $res['status'] = $res['status'] == 'success' ? true : false;
        $res['message'] = $res['error']['description'] ? $res['error']['description'] : $res['message'];
        // $res['req']['header'] = $Header;
        // $res['req']['body'] = json_decode($Body);
        return $res;
    }
}
