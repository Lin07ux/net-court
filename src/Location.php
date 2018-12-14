<?php
/**
 * 存证环境信息
 *
 * Author: Lin07ux
 * Created_at: 2018-12-14 15:44:32
 */

namespace NetCourt;


class Location
{
    /**
     * @var array 存证环境信息
     */
    protected $info = [];

    /**
     * Location constructor.
     *
     * @param  string|array  $ip   ip 或环境信息
     * @param  array         $info 环境信息
     */
    public function __construct ($ip, array $info = [])
    {
        if (is_array($ip)) {
            $info = $ip;
        } else {
            $info['ip'] = $ip;
        }

        $this->setInfo($info);
    }

    /**
     * 设置 IP
     *
     * @param  string  $ip
     * @return $this
     */
    public function setIp ($ip)
    {
        if (empty($ip)) {
            throw new \InvalidArgumentException('IP can not be empty');
        }

        $this->info['ip'] = $ip;

        return $this;
    }

    /**
     * 获取 IP
     *
     * @return string
     */
    public function getIp ()
    {
        return $this->info['ip'];
    }

    /**
     * 设置 Wi-Fi 物理理地址
     *
     * @param  string  $wifiMac
     * @return $this
     */
    public function setWifiMac ($wifiMac)
    {
        $this->info['wifiMac'] = $wifiMac;
        
        return $this;
    }

    /**
     * 获取 Wi-Fi 物理理地址
     *
     * @return string|null
     */
    public function getWifiMac ()
    {
        return isset($this->info['wifiMac']) ? $this->info['wifiMac'] : null;
    }

    /**
     *  设置 IMEI
     *
     * @param  string  $imei
     * @return $this
     */
    public function setIMEI ($imei)
    {
        $this->info['imei'] = $imei;

        return $this;
    }

    /**
     * 获取 IMEI
     *
     * @return string|null
     */
    public function getIMEI ()
    {
        return isset($this->info['imei']) ? $this->info['imei'] : null;
    }

    /**
     *  设置 IMSI
     *
     * @param  string  $imsi
     * @return $this
     */
    public function setIMSI ($imsi)
    {
        $this->info['imsi'] = $imsi;

        return $this;
    }

    /**
     * 获取 IMSI
     *
     * @return string|null
     */
    public function getIMSI ()
    {
        return isset($this->info['imsi']) ? $this->info['imsi'] : null;
    }

    /**
     * 设置纬度
     *
     * @param  string  $latitude
     * @return $this
     */
    public function setLatitude ($latitude)
    {
        $this->info['latitude'] = $latitude;

        return $this;
    }

    /**
     * 获取维度
     *
     * @return string|null
     */
    public function getLatitude ()
    {
        return isset($this->info['latitude']) ? $this->info['latitude'] : null;
    }

    /**
     * 设置经度
     *
     * @param  string  $longitude
     * @return $this
     */
    public function setLongitude ($longitude)
    {
        $this->info['longitude'] = $longitude;

        return $this;
    }

    /**
     * 获取经度
     *
     * @return string|null
     */
    public function getLongitude ()
    {
        return isset($this->info['longitude']) ? $this->info['longitude'] : null;
    }

    /**
     * 设置扩展属性
     *
     * @param  string  $properties
     * @return $this
     */
    public function setProperties ($properties)
    {
        $this->info['properties'] = $properties;

        return $this;
    }

    /**
     * 获取扩展属性
     *
     * @return string|null
     */
    public function getProperties ()
    {
        return isset($this->info['properties']) ? $this->info['properties'] : null;
    }

    /**
     * 设置信息
     *
     * @param  array  $info
     * @return $this
     */
    public function setInfo (array $info)
    {
        if (empty($this->info['ip'])) {
            throw new \InvalidArgumentException('Invalid Location property: IP can not be empty');
        }

        $defaults = [
            'ip' => null, 'wifiMac' => null, 'imei' => null, 'imsi' => null,
            'latitude' => null, 'longitude' => null, 'properties' => null,
        ];

        $this->info = array_intersect_key(array_merge($info, $defaults), $defaults);

        return $this;
    }

    /**
     * 获取全部信息
     *
     * @return array
     */
    public function getInfo ()
    {
        return array_filter($this->info);
    }
}