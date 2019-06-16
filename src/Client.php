<?php
/**
 * 基础存证客户端
 *
 * Author: Lin07ux
 * Created_at: 2019-06-16 17:41:26
 */

namespace NetCourt;

use GuzzleHttp\Client as HttpClient;
use NetCourt\Identity\Identity;

class Client
{
    /**
     * @var string 存证 API 域名
     */
    protected $host;

    /**
     * @var string 签名私钥
     */
    protected $privateKey;

    /**
     * @var string 账号标识
     */
    protected $accountId;

    /**
     * @var Identity 存证实体信息
     */
    protected $entity;

    /**
     * @var HttpClient http client
     */
    protected $httpClient;

    /**
     * NotaryClient constructor.
     *
     * @param  string         $accountId   用户账号
     * @param  string|null    $privateKey  签名私钥值
     * @param  Identity|null  $entity      存证实体
     */
    public function __construct ($accountId, $privateKey = null, Identity $entity = null)
    {
        $this->accountId = $accountId;
        $this->entity = $entity ?: null;

        if (! empty($privateKey)) {
            $this->setPrivateKey($privateKey);
        }
    }

    /**
     * 获取存证服务器域名
     *
     * @return string
     */
    public function getHost ()
    {
        return $this->host;
    }

    /**
     * 设置存证服务器域名
     *
     * @param  string $host
     * @return $this
     */
    public function setHost ($host)
    {
        $this->host = $host;
        $this->httpClient = null;

        return $this;
    }

    /**
     * 获取签名私钥内容
     *
     * @return string
     */
    public function getPrivateKey ()
    {
        return $this->privateKey;
    }

    /**
     * 设置签名私钥
     *
     * @param  string  $privateKey
     * @param  bool    $isFile
     * @return $this
     */
    public function setPrivateKey ($privateKey, $isFile = false)
    {
        if ($isFile) {
            $privateKey = @file_get_contents($privateKey);
        }

        if (empty($privateKey)) {
            throw new \InvalidArgumentException('Set private key failed, content can\'t be empty!');
        }

        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * 获取 accountId
     *
     * @return string
     */
    public function getAccountId ()
    {
        return $this->accountId;
    }

    /**
     * 设置 accountId
     *
     * @param  string  $accountId
     * @return $this
     */
    public function setAccountId ($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * 获取存证实体信息
     *
     * @return Identity|null
     */
    public function getEntity ()
    {
        return $this->entity;
    }

    /**
     * 设置存证实体信息
     *
     * @param  Identity  $entity
     * @return $this
     */
    public function setEntity (Identity $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * 获取 http 客户端
     *
     * @return HttpClient
     */
    protected function getHttpClient ()
    {
        if (! $this->httpClient) {
            $this->httpClient = new HttpClient([
                'timeout' => 60,
                'base_uri' => $this->host,
                'http_errors' => false,
            ]);
        }

        return $this->httpClient;
    }

    /**
     * 签名
     *
     * @param  string        $accountId
     * @param  string        $content
     * @param  float|string  $timestamp
     * @return string
     */
    protected function sign ($accountId, $content, $timestamp)
    {
        $string = "{$accountId}{$content}{$timestamp}";

        if (empty($string)) {
            throw new \InvalidArgumentException('Sign data must be not empty!');
        }

        openssl_sign($string, $binarySignature, $this->getPrivateKey(), OPENSSL_ALGO_SHA256);

        return bin2hex($binarySignature);
    }

    /**
     * 获取毫秒级时间戳
     *
     * @return float|int
     */
    protected function getMillisecond ()
    {
        list($uSec, $sec) = explode(" ", microtime());

        return $sec * 1000 + substr($uSec, 2, 3) * 1;
    }
}