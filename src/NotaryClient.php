<?php
/**
 * Notary client
 *
 * Author: Lin07ux
 * Created_at: 2018-12-12 22:48:51
 */

namespace NetCourt;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use NetCourt\Exception\BadResponseException;
use NetCourt\Identity\Identity;
use Psr\Http\Message\ResponseInterface;

class NotaryClient
{
    /**
     * 获取存证事务 ID 接口
     */
    const API_NOTARY_TOKEN = '/api/blockChain/notaryToken';

    /**
     * 存证接口
     */
    const API_NOTARY_CERT = '/api/blockChain/notaryCert';

    /**
     * @var string 存证域名
     */
    protected $host = 'https://check.netcourt.gov.cn';

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
     * @var Client http client
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
     * 生成事务 ID
     *
     * @param  Identity     $customer   客户信息
     * @param  Business     $biz        业务信息
     * @param  string|null  $properties 附加属性
     * @return string
     */
    public function createNotaryToken (Identity $customer, Business $biz, $properties = null)
    {
        $params = array_filter([
            'accountId' => $this->accountId,
            'entity' => $this->entity ? $this->entity->toArray() : null,
            'bizId' => $biz->getBiz(),
            'subBizId' => $biz->getSubBiz(),
            'customer' => $customer->toArray(),
            'timestamp' => $this->getMillisecond(),
            'properties' => $properties,
        ]);
        $params['signedData'] = $this->sign($this->accountId, $params['bizId'], $params['timestamp']);

        list($result) = $this->post(self::API_NOTARY_TOKEN, $params);

        return $result;
    }

    /**
     * 存证并得到存证证书
     *
     * @param  string         $token      事务 ID
     * @param  string         $phase      存证阶段
     * @param  string|null    $content    存证内容
     * @param  Location|null  $location   存证环境
     * @param  string|null    $properties 附加属性
     * @return array
     */
    public function createNotaryCert ($token, $phase, $content = null, Location $location = null, $properties = null)
    {
        $meta = array_filter([
            'accountId' => $this->accountId,
            'token' => $token,
            'phase' => $phase,
            'timestamp' => $this->getMillisecond(),
            'entity' => $this->entity,
            'location' => $location,
            'properties' => $properties,
        ]);

        $params = array_filter(['meta' => $meta, 'notaryContent' => $content, 'timestamp' => $meta['timestamp']]);
        $params['signedData'] = $this->sign($this->accountId, $meta['phase'], $params['timestamp']);

        return $this->post(self::API_NOTARY_CERT, $params);
    }

    /**
     * 发送请求
     *
     * @param  string|\Psr\Http\Message\UriInterface  $uri
     * @param  array   $params
     * @return array
     */
    protected function post ($uri, array $params)
    {
        try {
            $response = $this->getHttpClient()->post($uri, ['json' => $params]);
        } catch (RequestException $e) {
            throw new BadResponseException('http post failed, please check your host or network', 500);
        }

        list($result, $body) = $this->getResponseResult($response);

        if ($response->getStatusCode() !== 200 || ! $result) {
            throw new BadResponseException('http post failed, please check your host or network', 500);
        } elseif (empty($result['success'])) {
            throw new BadResponseException(isset($result['errMessage']) ? $result['errMessage'] : 'Unknow response');
        }

        return [$result['responseData'], $body];
    }

    /**
     * 获取 http 客户端
     *
     * @return Client
     */
    private function getHttpClient ()
    {
        if (! $this->httpClient) {
            $this->httpClient = new Client([
                'timeout' => 60,
                'base_uri' => $this->host,
                'http_errors' => false,
            ]);
        }

        return $this->httpClient;
    }

    /**
     * 获取响应结果
     *
     * @param  ResponseInterface  $response
     * @return array
     */
    private function getResponseResult (ResponseInterface $response)
    {
        $body = $response->getBody();
        $result = $response->getHeader('Blockchainresponse') ?: [$body->getContents()];
        $result = $result ? json_decode($result[0], true) : [];

        return [$result, $body];
    }

    /**
     * 签名
     *
     * @param  string        $accountId
     * @param  string        $content
     * @param  float|string  $timestamp
     * @return string
     */
    private function sign ($accountId, $content, $timestamp)
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
    private function getMillisecond ()
    {
        list($uSec, $sec) = explode(" ", microtime());

        return $sec * 1000 + substr($uSec, 2, 3) * 1;
    }
}