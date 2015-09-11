<?php
/**检测是否到订餐时间
 **/
class CheckTime extends CComponent
{
    public function init()
    {

    }

    public function isOnTime()
    {
        $timeConfig = Config::model()->find('name=:name',array(':name' => 'dinner_time'));
        $timeConfig = CJSON::decode(CJSON::encode($timeConfig));
        if($timeConfig)
        {
            $dataStr = date('Y-m-d',time());
            if($timeConfig['is_open'])
            {
                $startTime 	= strtotime($dataStr . ' ' . $timeConfig['start_time']);
                $endTime 	= strtotime($dataStr . ' ' . $timeConfig['end_time']);
                if($startTime <= time() && $endTime >= time())
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return true;
            }
        }
        return true;
    }

    public function isShopOnTime($shop_id)
    {
        $shopData = Shops::model()->findByPk($shop_id);
        if(!$shopData)
        {
            Error::output(Error::ERR_NO_SHOPID);
        }
        $shopData = CJSON::decode(CJSON::encode($shopData));


        $dataStr = date('Y-m-d',time());
        $startTime 	= strtotime($dataStr . ' ' . $shopData['starttime']);
        $endTime 	= strtotime($dataStr . ' ' . $shopData['endtime']);
        if($startTime <= time() && $endTime >= time())
        {
            return true;
        }
        else
        {
            return false;
        }
        return false;

    }
}