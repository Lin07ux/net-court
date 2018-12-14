<?php
/**
 * The identity
 *
 * Author: Lin07ux
 * Created_at: 2018-12-13 23:09:08
 */

namespace NetCourt\Identity;


abstract class Identity
{
    /**
     * 用户类别：个人
     */
    const USER_TYPE_PERSON = 'PERSON';

    /**
     * 用户类别：企业
     */
    const USER_TYPE_ENTERPRISE = 'ENTERPRISE';

    /**
     * 证件类型：身份证(个人)
     */
    const CERT_TYPE_IDENTITY_CARD = 'IDENTITY_CARD';

    /**
     * 证件类型：统一社会信用代码(企业)
     */
    const CERT_TYPE_CREDIT_CODE = 'UNIFIED_SOCIAL_CREDIT_CODE';

    /**
     * 证件类型：企业工商注册号(企业)
     */
    const CERT_TYPE_REGISTERED_NUMBER = 'ENTERPRISE_REGISTERED_NUMBER';

    /**
     * @var array 身份信息
     */
    protected $data = [];

    /**
     * Identity constructor.
     *
     * @param array $data
     */
    public function __construct (array $data = [])
    {
        $this->setData($data);
    }

    /**
     * 设置用户名称
     *
     * @param  string  $userName
     * @return $this
     */
    public function setUserName ($userName)
    {
        $this->data['certName'] = $userName;

        return $this;
    }

    /**
     * 获取用户名称
     *
     * @return string|null
     */
    public function getUserName ()
    {
        return isset($this->data['certName']) ? $this->data['certName'] : null;
    }

    /**
     * 设置用户名称
     *
     * @param  string  $certName
     * @return $this
     */
    public function setCertName ($certName)
    {
        $this->data['certName'] = $certName;

        return $this;
    }

    /**
     * 获取用户名称
     *
     * @return string|null
     */
    public function getCertName ()
    {
        return isset($this->data['certName']) ? $this->data['certName'] : null;
    }

    /**
     * 设置证件号
     *
     * @param  string  $certNo
     * @return $this
     */
    public function setCertNo ($certNo)
    {
        $this->data['certNo'] = $certNo;

        return $this;
    }

    /**
     * 获取证件号
     *
     * @return string|null
     */
    public function getCertNo ()
    {
        return isset($this->data['certNo']) ? $this->data['certNo'] : null;
    }

    /**
     * 设置手机号码
     *
     * @param  string  $mobileNo
     * @return $this
     */
    public function setMobileNo ($mobileNo)
    {
        $this->data['mobileNo'] = $mobileNo;

        return $this;
    }

    /**
     * 获取手机号码
     *
     * @return string|null
     */
    public function getMobileNo ()
    {
        return isset($this->data['mobileNo']) ? $this->data['mobileNo'] : null;
    }

    /**
     * 设置扩展属性
     *
     * @param  string  $properties
     * @return $this
     */
    public function setProperties ($properties)
    {
        $this->data['properties'] = $properties;

        return $this;
    }

    /**
     * 获取扩展属性值
     *
     * @return string|null
     */
    public function getProperties ()
    {
        return isset($this->data['properties']) ? $this->data['properties'] : null;
    }

    /**
     * 设置身份信息
     *
     * @param  array $data
     * @return $this
     */
    abstract public function setData (array $data);

    /**
     * 获取全部身份信息
     *
     * @return array
     */
    abstract public function toArray ();
}