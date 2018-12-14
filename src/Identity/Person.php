<?php
/**
 * The identity for Person
 *
 * Author: Lin07ux
 * Created_at: 2018-12-13 23:38:39
 */

namespace NetCourt\Identity;

use NetCourt\Exception\InvalidIdentityPropertyValueException;

class Person extends Identity
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
            $defaults = ['certName' => null, 'certNo' => null, 'mobileNo' => null, 'properties' => null];

            $this->data = array_intersect_key(array_merge($defaults, $data), $defaults);
        }

        $this->data['userType'] = self::USER_TYPE_PERSON;
        $this->data['certType'] = self::CERT_TYPE_IDENTITY_CARD;

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
            throw new InvalidIdentityPropertyValueException('Person certName can not be empty');
        }

        if (empty($this->data['certNo'])) {
            throw new InvalidIdentityPropertyValueException('Person certNo can not be empty');
        }
        
        return array_filter($this->data);
    }
}