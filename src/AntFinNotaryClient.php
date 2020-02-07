<?php
/**
 * 蚂蚁金服存证客户端
 *
 * Author: Lin07ux
 * Created_at: 2019-06-16 17:22:53
 */

namespace NetCourt;

use GuzzleHttp\Exception\RequestException;
use NetCourt\Exception\BadResponseException;
use NetCourt\Identity\Identity;

class AntFinNotaryClient extends Client
{
    /**
     * 获取存证事务 ID 接口
     */
    const API_TOKEN = '/api/notaryToken';

    /**
     * 存证接口
     */
    const API_CRAWLER = '/api/crawlerNotary';

    /**
     * 查询存证结果
     */
    const API_QUERY = '/api/crawlerNotaryGet';

    /**
     * @var string 存证域名
     */
    protected $host = 'https://cz.tech.antfin.com';

    /**
     * 获取存证操作的 token
     *
     * @param  Identity    $customer
     * @param  Business    $biz
     * @param  string|null $properties
     *
     * @return string
     */
    public function token (Identity $customer, Business $biz, $properties = null)
    {
        $body = array_filter([
            'accountId' => $this->accountId,
            'entity' => $this->entity ? $this->entity->toArray() : null,
            'bizId' => $biz->getBiz(),
            'subBizId' => $biz->getSubBiz(),
            'customer' => $customer->toArray(),
            'timestamp' => $this->getMillisecond(),
            'properties' => $properties,
        ]);
        $body['signedData'] = $this->sign($this->accountId, $body['bizId'], $body['timestamp']);

        return $this->post(self::API_TOKEN, $body);
    }

    /**
     * crawl
     *
     * @param  string $token 存证 token
     * @param  string $url   存证链接
     * @param  string $phase 存证阶段
     *
     * @return string
     */
    public function crawl ($token, $url, $phase = 'step_2_crawl')
    {
        $body = [
            'url' => $url,
            'accountId' => $this->accountId,
            'timestamp' => $this->getMillisecond(),
            'meta' => ['token' => $token, 'phase' => $phase, 'accountId' => $this->accountId]
        ];
        $body['signedData'] = $this->sign($this->accountId, $url, $body['timestamp']);

        $response = json_decode($this->post(self::API_CRAWLER, $body), true);

        return $response['nonce'];
    }

    /**
     * 查询取证结果
     *
     * @param  string $nonce
     *
     * @return array|bool
     */
    public function query ($nonce)
    {
        $body = [
            'accountId' => $this->accountId,
            'timestamp' => $this->getMillisecond(),
            'nonce' => $nonce,
        ];
        $body['signedData'] = $this->sign($this->accountId, $nonce, $body['timestamp']);

        $response = json_decode($this->post(self::API_QUERY, $body), true);

        if (isset($response['status']) && $response['status'] === 'finish') {
            return [
                'file_uri' => $response['screenshotZip'],
                'file_hash' => $response['zipHash'],
                'block_hash' => $response['txHash'],
            ];
        }

        return false;
    }

    /**
     * 发送请求
     *
     * @param  string|\Psr\Http\Message\UriInterface  $uri
     * @param  array   $params
     * @return string
     */
    protected function post ($uri, array $params)
    {
        try {
            $response = $this->getHttpClient()->post($uri, ['json' => $params]);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }

        $data = json_decode($response->getBody()->getContents(), true);

        if (empty($data['success'])) {
            $message = empty($data['errMessage']) ?
                (empty($data['message']) ? 'Request Failed' : $data['message']) :
                $data['errMessage'];

            throw new BadResponseException($message, $response->getStatusCode());
        }

        return $data['responseData'];
    }

    /**
     * 获取毫秒级时间戳
     *
     * @return float
     */
    protected function getMillisecond ()
    {
        list($s1, $s2) = explode(' ', microtime());

        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
}