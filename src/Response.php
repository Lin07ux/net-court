<?php
/**
 * 司法链存证响应
 *
 * Author: Lin07ux
 * Created_at: 2019-03-01 19:02:37
 */

namespace NetCourt;

use Psr\Http\Message\ResponseInterface;

class Response
{
    /**
     * @var bool 请求是否成功
     */
    private $isSuccess = false;

    /**
     * @var string 响应数据
     */
    private $data;

    /**
     * @var string 证书下载网址
     */
    private $certUrl;

    /**
     * @var string 响应代码
     */
    private $code;

    /**
     * @var string 响应信息
     */
    private $message;

    /**
     * Response constructor.
     *
     * @param ResponseInterface|null $nativeResponse
     */
    public function __construct (ResponseInterface $nativeResponse = null)
    {
        $this->setResponse($nativeResponse);
    }

    /**
     * 设置响应数据
     *
     * @param ResponseInterface $nativeResponse
     *
     * @return $this
     */
    public function setResponse (ResponseInterface $nativeResponse)
    {
        if ($nativeResponse && $nativeResponse->getStatusCode() === 200) {
            $result = $nativeResponse->getBody()->getContents();
            $result = $result ? json_decode($result, true) : [];

            // 解析响应数据
            $this->isSuccess = isset($result['success']) ? $result['success'] : false;
            $this->data = isset($result['responseData']) ? $result['responseData'] : null;
            $this->code = isset($result['code']) ? $result['code'] : null;
            $this->message = isset($result['errMessage']) ? $result['errMessage'] : null;

            // 证书下载链接
            if ($this->isSuccess && $nativeResponse->hasHeader('Certurl')) {
                $this->certUrl = $nativeResponse->getHeader('Certurl')[0];
            }
        }

        return $this;
    }

    /**
     * 是否是成功响应
     *
     * @return bool
     */
    public function isSuccess ()
    {
        return $this->isSuccess;
    }

    /**
     * 获取响应数据
     *
     * @return string
     */
    public function getResponseData ()
    {
        return $this->data;
    }

    /**
     * 获取响应代码
     *
     * @return string
     */
    public function getCode ()
    {
        return $this->code ?: 'NETWORK_ERROR';
    }

    /**
     * 获取响应信息
     *
     * @return string
     */
    public function getMessage ()
    {
        return $this->message ?: 'http post failed, please check your host or network';
    }

    /**
     * 获取存证证书下载链接
     *
     * @return string
     */
    public function getCertUrl ()
    {
        return $this->certUrl;
    }

    /**
     * 下载证书
     *
     * @param string      $path 存储路径
     * @param string|null $name 保存文件的名称(不含扩展名)
     *
     * @return bool|null|string
     */
    public function downloadCert ($path, $name = null)
    {
        if ($this->isSuccess() && $this->certUrl) {
            $path = rtrim($path, DIRECTORY_SEPARATOR);
            $ext = pathinfo(parse_url($this->certUrl)['path'], PATHINFO_EXTENSION) ?: 'pdf';
            $name = $path.DIRECTORY_SEPARATOR.($name ?: $this->getResponseData()).'.'.$ext;

            file_put_contents($name, fopen($this->certUrl, 'r'));

            return $name;
        }

        return false;
    }
}