<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\finance\model;

use think\Model;


class AdminFinanceBoundModel extends FinanceBoundModel
{
    protected $table='cmf_finance_bound';

    public function getGoodsAttr($value)
    {
        $return_value = '';
        $goodsModel = new GoodsModel();
        $val = json_decode($value,true);
        foreach ($val as $key=>$item) {
            $goods = $goodsModel->find($key);
            $str = $goods['name'].'*'.$item.'</br>';
            $return_value .= $str;
        }
        return $return_value;
    }


}