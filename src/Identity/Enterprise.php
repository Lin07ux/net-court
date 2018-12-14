<?php
/**
 * The identity for Enterprise
 *
 * Author: Lin07ux
 * Created_at: 2018-12-13 23:49:41
 */

namespace NetCourt\Identity;

use NetCourt\Exception\InvalidIdentityPropertyValueException;

class Enterprise extends Identity
{
    /**
     * 设置身份信息
     *
     * @param  array $data
     * @return $this
     */
    public function setData (array $data)
    {
        if (! empty($data)) {
            $defaults = [
                'certName' => null, 'certNo' => null, 'mobileNo' => null, 'properties' => null,
                'legalPerson' => null, 'legalPersonId' => null, 'agent' => null, 'agentId' => null,
            ];

            $this->data = array_intersect_key(array_merge($defaults, $data), $defaults);

            if (isset($data['certType'])) {
                $this->setCertType($data['certType']);
            }
        }

        $this->data['userType'] = self::USER_TYPE_ENTERPRISE;

        return $this;
    }

    /**
     * 获取全部身份信息
     *
     * @return array
     */
    public function toArray ()
    {
        if (empty($this->data['certName'])) {
            throw new InvalidIdentityPropertyValueException('Enterprise certName can not be empty');
        }

        if (empty($this->data['certType'])) {
            throw new InvalidIdentityPropertyValueException('Enterprise certType can not be empty');
        }

        if (empty($this->data['certNo'])) {
            throw new InvalidIdentityPropertyValueException('Enterprise certNo can not be empty');
        }

        if (empty($this->data['legalPerson'])) {
            throw new InvalidIdentityPropertyValueException('Enterprise legalPerson can not be empty');
        }

        if (empty($this->data['legalPersonId'])) {
            throw new InvalidIdentityPropertyValueException('Enterprise legalPersonId can not be empty');
        }

        if (empty($this->data['agent'])) {
            throw new InvalidIdentityPropertyValueException('Enterprise agent can not be empty');
        }

        if (empty($this->data['agentId'])) {
            throw new InvalidIdentityPropertyValueException('Enterprise agentId can not be empty');
        }

        return array_filter($this->data);
    }

    /**
     * 设置证件类型
     *
     * @param  string  $certType
     * @return $this
     */
    public function setCertType ($certType)
    {
        if ($certType !== self::CERT_TYPE_CREDIT_CODE || $certType !== self::CERT_TYPE_REGISTERED_NUMBER) {
            throw new \InvalidArgumentException('Enterprise cert type can only be "UNIFIED_SOCIAL_CREDIT_CODE" or "ENTERPRISE_REGISTERED_NUMBER"');
        }

        $this->data['certType'] = $certType;

        return $this;
    }

    /**
     * 获取证件类型
     *
     * @return string|null
     */
    public function getCertType ()
    {
        return isset($this->data['certType']) ? $this->data['certType'] : null;
    }

    /**
     * 设置企业法人姓名
     *
     * @param  string  $legalPerson
     * @return $this
     */
    public function setLegalPerson ($legalPerson)
    {
        $this->data['legalPerson'] = $legalPerson;

        return $this;
    }

    /**
     * 获取企业法人姓名
     *
     * @return string|null
     */
    public function getLegalPerson ()
    {
        return isset($this->data['legalPerson']) ? $this->data['legalPerson'] : null;
    }

    /**
     * 设置企业法人身份证号码
     *
     * @param  string  $legalPersonId
     * @return $this
     */
    public function setLegalPersonId ($legalPersonId)
    {
        $this->data['legalPersonId'] = $legalPersonId;

        return $this;
    }

    /**
     * 获取企业法人身份证号码
     *
     * @return string|null
     */
    public function getLegalPersonId ()
    {
        return isset($this->data['legalPersonId']) ? $this->data['legalPersonId'] : null;
    }

    /**
     * 获取经办人姓名
     *
     * @param  string  $agent
     * @return $this
     */
    public function setAgent ($agent)
    {
        $this->data['agent'] = $agent;

        return $this;
    }

    /**
     * 获取经办人姓名
     *
     * @return string|null
     */
    public function getAgent ()
    {
        return isset($this->data['agent']) ? $this->data['agent'] : null;
    }

    /**
     * 设置经办人身份证号码
     *
     * @param  string  $agentId
     * @return $this
     */
    public function setAgentId ($agentId)
    {
        $this->data['agentId'] = $agentId;

        return $this;
    }

    /**
     * 获取经办人身份证号码
     *
     * @return string|null
     */
    public function getAgentId ()
    {
        return isset($this->data['agentId']) ? $this->data['agentId'] : null;
    }
}