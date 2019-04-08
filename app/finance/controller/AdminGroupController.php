<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\finance\controller;

use think\Db;
use cmf\controller\AdminBaseController;
use app\finance\model\GoodsModel;
use app\finance\model\GoodsGroupModel;

class AdminGroupController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        //当前角色
        $user_id = cmf_get_current_admin_id();
        $data = Db::name('role_user')->where("user_id", $user_id)->find();
        $this->role_id = $data['role_id'];
        $this->user_id = $user_id;
    }

    //组合列表
    public function index()
    {
        $where = [];

        $keyword = $this->request->param('keyword');
        $where['name'] = ['like',"%{$keyword}%"];
        $this->assign('keyword', $keyword);

        $goodsGroupModel = new GoodsGroupModel();

        $where['user_id'] = $this->user_id;

        $obj = $goodsGroupModel->where($where);
        $list =$obj->paginate(20);
        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    //添加商品
    public function add()
    {
        return $this->fetch();
    }

    //添加商品操作
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();

            $data['post']['update_time']    = time();
            $data['post']['published_time'] = time();
            $post = $data['post'];

            $result = $this->validate($post, 'Group');
            if ($result !== true) {
                $this->error($result);
            }

            $goodsGroupModel = new GoodsGroupModel();

            $goodsGroupModel->adminAddGroup($data['post'],$this->user_id);
            $this->success('添加成功!', url('AdminGroup/index'));
        }
    }

    //编辑商品
    public function edit()
    {
        $id = $this->request->param("id", 0, 'intval');
        $goodsGroupModel = new GoodsGroupModel();
        $row = $goodsGroupModel->where('id',$id)->find();


        $this->assign('row',$row);
        return $this->fetch();
    }

    //编辑商品保存
    public function editPost()
    {

        if ($this->request->isPost()) {
            $data = $this->request->param();
            $post   = $data['post'];

            $result = $this->validate($post, 'Group');
            if ($result !== true) {
                $this->error($result);
            }

            $goodsGroupModel = new GoodsGroupModel();

            $goodsGroupModel->adminEditGroup($data['post'], $this->user_id);
            $this->success('保存成功!', url('AdminGroup/index'));

        }
    }

    //上下架商品
    public function down(){
        $goodsGroupModel = new GoodsGroupModel();

        $id = $this->request->param("id", 0, 'intval');
        $type = $this->request->param('type');
        $updated = [];
        $updated['update_time'] = time();
        if($type == 'down'){
            $updated['delete_time'] = time();//下架时间
            $updated['status'] = 0;
        } else{
            $updated['delete_time'] = 0;
            $updated['status'] = 1;
        }
        $res = $goodsGroupModel->where('id',$id)->update($updated);
        if($res){
            $this->success('修改成功');
        }else{
            $this->error('修改失败');
        }
    }
}
