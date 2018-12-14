<?php
/**
 * Business id and code
 *
 * Author: Lin07ux
 * Created_at: 2018-12-14 10:59:00
 */

namespace NetCourt;

class Business
{
    /**
     * 版权业务号
     */
    const COPYRIGHT = 1;

    /**
     * 合同业务号
     */
    const CONTRACT = 2;

    /**
     * 视频版权子业务
     */
    const COPYRIGHT_VIDEO = 'VIDEO';

    /**
     * 音频版权子业务
     */
    const COPYRIGHT_AUDIO = 'AUDIO';

    /**
     * 图片版权子业务
     */
    const COPYRIGHT_IMAGE = 'IMAGE';

    /**
     * 文本版权子业务
     */
    const COPYRIGHT_TEXT = 'TEXT';

    /**
     * HR 合同子业务
     */
    const CONTRACT_HR = 'HR';

    /**
     * 房屋租赁合同子业务
     */
    const CONTRACT_HOUSERENTING = 'HOUSERENTING';

    /**
     * 租赁合同子业务
     */
    const CONTRACT_LEASING = 'LEASING';

    /**
     * 供应链合同子业务
     */
    const CONTRACT_SUPPLY_CHAIN = 'SUPPLY_CHAIN';

    /**
     * 旅游合同子业务
     */
    const CONTRACT_TRAVEL = 'TRAVEL';

    /**
     * 教育合同子业务
     */
    const CONTRACT_EDUCATION = 'EDUCATION';

    /**
     * 保险合同子业务
     */
    const CONTRACT_INSURANCE = 'INSURANCE';

    /**
     * @var integer 业务 ID
     */
    protected $biz;

    /**
     * @var string 子业务 ID
     */
    protected $subBiz;

    /**
     * Biz constructor.
     *
     * @param  integer      $biz
     * @param  string|null  $subBiz
     */
    public function __construct ($biz, $subBiz = null)
    {
        $this->setBiz($biz)->setSubBiz($subBiz);
    }

    /**
     * 获取业务 ID
     *
     * @return int|null
     */
    public function getBiz ()
    {
        return $this->biz;
    }

    /**
     * 设置业务 ID
     *
     * @param  integer  $biz
     * @return $this
     */
    public function setBiz ($biz)
    {
        if (empty($biz)) {
            throw new \InvalidArgumentException('Biz id can not be empty');
        }

        $biz = (int)$biz;

        if (! in_array($biz, [self::COPYRIGHT, self::CONTRACT])) {
            throw new \InvalidArgumentException('Biz id can only be '.self::COPYRIGHT.' or '.self::CONTRACT);
        }

        $this->biz = $biz;

        return $this;
    }

    /**
     * 获取子业务 ID
     *
     * @return string|null
     */
    public function getSubBiz ()
    {
        return $this->subBiz;
    }

    /**
     * 设置子业务 ID
     *
     * @param  string|null  $subBiz
     * @return $this
     */
    public function setSubBiz ($subBiz = null)
    {
        if ($subBiz) {
            if ($this->biz === self::COPYRIGHT) {
                $subIds = [self::COPYRIGHT_AUDIO, self::COPYRIGHT_VIDEO, self::COPYRIGHT_IMAGE, self::COPYRIGHT_TEXT];

                if (! in_array($subBiz, $subIds)) {
                    throw new \InvalidArgumentException('Sub biz id of copyright can only be '.implode(', ', $subIds));
                }
            } else {
                $subIds = [
                    self::CONTRACT_HR, self::CONTRACT_HOUSERENTING, self::CONTRACT_LEASING, self::CONTRACT_SUPPLY_CHAIN,
                    self::CONTRACT_TRAVEL, self::CONTRACT_EDUCATION, self::CONTRACT_INSURANCE,
                ];

                if (! in_array($subBiz, $subIds)) {
                    throw new \InvalidArgumentException('Sub biz id of contract can only be '.implode(', ', $subIds));
                }
            }
        }

        $this->subBiz = $subBiz ?: null;

        return $this;
    }
}