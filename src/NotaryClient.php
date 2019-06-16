<?php
/**
 * 互联网法院(共道)存证客户端
 *
 * Author: Lin07ux
 * Created_at: 2018-12-12 22:48:51
 */

namespace NetCourt;

use GuzzleHttp\Exception\RequestException;
use NetCourt\Exception\BadResponseException;
use NetCourt\Identity\Identity;

class NotaryClient extends Client
{
    /**
     * 获取存证事务 ID 接口
     */
    const API_TOKEN = '/api/blockChain/notaryToken';

    /**
     * 存证接口
     */
    const API_CERT = '/api/blockChain/notaryCertUrl';

    /**
     * @var string 存证域名
     */
    protected $host = 'https://check.netcourt.gov.cn';

    /**
     * 生成事务 ID
     *
     * @param  Identity     $customer   客户信息
     * @param  Business     $biz        业务信息
     * @param  string|null  $properties 附加属性
     * @return Response
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

        return $this->post(self::API_TOKEN, $params);
    }

    /**
     * 存证并得到存证证书
     *
     * @param  string         $token      事务 ID
     * @param  string         $phase      存证阶段
     * @param  string|null    $content    存证内容
     * @param  Location|null  $location   存证环境
     * @param  string|null    $properties 附加属性
     * @return Response
     */
    public function createNotaryCert ($token, $phase, $content = null, Location $location = null, $properties = null)
    {
        $meta = array_filter([
            'accountId' => $this->accountId,
            'token' => $token,
            'phase' => $phase,
            'entity' => $this->entity,
            'location' => $location,
            'properties' => $properties,
        ]);

        $params = array_filter(['meta' => $meta, 'notaryContent' => $content, 'timestamp' => $this->getMillisecond()]);
        $params['signedData'] = $this->sign($this->accountId, $meta['phase'], $params['timestamp']);

        return $this->post(self::API_CERT, $params);
    }

    /**
     * 发送请求
     *
     * @param  string|\Psr\Http\Message\UriInterface  $uri
     * @param  array   $params
     * @return Response
     */
    private function post ($uri, array $params)
    {
        try {
            $response = $this->getHttpClient()->post($uri, ['json' => $params]);
        } catch (RequestException $e) {
            throw new BadResponseException($e->getMessage(), 500);
        }

        $response = new Response($response);

        if (! $response->isSuccess()) {
            throw new BadResponseException($response->getCode().': '.$response->getMessage(), 500);
        }

        return $response;
    }
}