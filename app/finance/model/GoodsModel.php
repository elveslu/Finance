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

class GoodsModel extends Model
{
    protected $type = [
        'more' => 'array',
    ];

    //绑定组合
    public function group()
    {
        return $this->hasOne('GoodsGroupModel', 'id', 'group_id');
    }


    //添加商品操作
    public function adminAddGoods($data,$user_id)
    {
        $data['user_id'] = $user_id;

        $this->allowField(true)->data($data, true)->isUpdate(false)->save();


        return $this;

    }

    //添加商品操作
    public function adminEditGoods($data, $user_id)
    {
        $data['user_id'] = $user_id;
        $this->allowField(true)->data($data, true)->isUpdate(true)->save();


        return $this;

    }
}