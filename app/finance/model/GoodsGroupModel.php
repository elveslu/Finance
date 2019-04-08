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
use tree\Tree;

class GoodsGroupModel extends Model
{

    //添加商品操作
    public function adminAddGroup($data,$user_id)
    {
        $data['user_id'] = $user_id;

        $this->allowField(true)->data($data, true)->isUpdate(false)->save();


        return $this;

    }

    //添加商品操作
    public function adminEditGroup($data, $user_id)
    {
        $data['user_id'] = $user_id;
        $this->allowField(true)->data($data, true)->isUpdate(true)->save();


        return $this;

    }

    public function adminGroupTableTree($currentIds = 0,$user_id = 0,$flg = false)
    {
        $where['status'] = 1;
        $where['user_id']     = $user_id;
        $categories = $this->where($where)->select()->toArray();
        return $this->getTree($currentIds,$categories);
    }

    public function getTree($currentIds = 0, $categories){
        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';
        if (!is_array($currentIds)) {
            $currentIds = [$currentIds];
        }

        foreach ($categories as &$item) {
            $item['checked'] = in_array($item['id'], $currentIds) ? "checked" : "";
            $item['selected'] = in_array($item['id'], $currentIds) ? "selected" : "";
        }
        return $categories;
    }

}