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
use app\finance\model\FinanceBoundModel;

class AdminFinanceController extends AdminBaseController
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

    //财务报表
    public function index()
    {
        $where = [];
        $boundModel = new FinanceBoundModel();

        $param = $this->request->param();
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');

        $startTime = empty($param['start_time']) ? 0 : strtotime($param['start_time']);
        $endTime   = empty($param['end_time']) ? 0 : strtotime($param['end_time']);
        if (!empty($startTime)) {
            $where['createtime'] = ['>=',$startTime];
        }
        if (!empty($endTime)) {
            $where['createtime'] = ['<=',$endTime];
        }

        $where['user_id'] = $this->user_id;
        $where['status'] = '1';

        $total_finance = $boundModel->where($where)->select();
        $total_amount = 0;
        $in_amount = 0;
        $out_amount = 0;
        foreach($total_finance as $key=>$val){
            if($val['type'] == 'inBound'){
                $in_amount += $val['amount'];
                $total_amount -= $val['amount'];
            }elseif($val['type'] == 'outBound'){
                $out_amount += $val['amount'];
                $total_amount += $val['amount'];
            }
        }
        $this->assign('in_amount', $in_amount);
        $this->assign('out_amount', $out_amount);
        $this->assign('total_amount', $total_amount);

        $type = $this->request->param('type');
        $where['type'] = $type;
        $this->assign('type', $type);
        $obj = $boundModel->where($where)->order('createtime desc');
        $list =$obj->paginate(15);
        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

}
