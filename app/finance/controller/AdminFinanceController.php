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
use app\finance\model\AdminFinanceBoundModel;
use app\user\model\UserModel;
use app\finance\model\PesModel;

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
        $where1 = [];
        $boundModel = new AdminFinanceBoundModel();

        $memo = $this->request->param('memo');
        $where['memo'] = ['like',"%{$memo}%"];
        $this->assign('memo', $memo);

        $param = $this->request->param();
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');

        $startTime = empty($param['start_time']) ? 0 : strtotime($param['start_time']);
        $endTime   = empty($param['end_time']) ? 0 : strtotime($param['end_time']);
        if (!empty($startTime)) {
            $where['createtime'] = ['>=',$startTime];
        }
        if (!empty($endTime)) {
            $where1['createtime'] = ['<=',$endTime];
        }

        $userModel = new UserModel();
        $u_id = isset($param['user_id']) ? $param['user_id'] : 0;
        $categoryTree = $userModel->adminUserTableTree($u_id);
        $this->assign('categoryTree',$categoryTree);


        $select_user_id = '';
        if($this->user_id == 1){
            if($u_id != 0){
                $select_user_id = $u_id;
                //$where['user_id'] = $u_id;
            }
        }else{
            $select_user_id = $this->user_id;
            //$where['user_id'] = $this->user_id;
        }
        $where['user_id'] = $select_user_id;
        $this->assign('login_user_id', $this->user_id);
        $this->assign('select_user_id', $u_id);
        $where['status'] = '1';
        $total_finance = $boundModel->where($where)->where($where1)->select();
        $total_amount = 0;
        $in_amount = 0;
        $out_amount = 0;
        $profit = 0;
        foreach($total_finance as $key=>$val){
            if($val['type'] == 'inBound'){
                $in_amount += $val['amount'];
                $total_amount -= $val['amount'];
            }elseif($val['type'] == 'outBound'){
                $out_amount += $val['amount'];
                $total_amount += $val['amount'];
                $profit += $val['profit'];
            }
        }
        $this->assign('in_amount', $in_amount);
        $this->assign('out_amount', $out_amount);
        $this->assign('total_amount', $total_amount);
        $this->assign('profit', $profit);

        $type = $this->request->param('type');
        $where['type'] = $type;
        $this->assign('type', $type);
        $obj = $boundModel->where($where)->where($where1)->order('createtime desc');
        $list =$obj->paginate(50);
        //echo '<pre>';print_r(array_merge($where,$where1));exit;
        $appends = [];
        if($memo){
            $appends['memo'] = ['like',"%{$memo}%"];
        }
        $appends['user_id'] = $select_user_id;
        $appends['createtime'] = ['>=',$startTime];
        $appends['createtime'] = ['<=',$endTime];
        $list->appends($appends);
        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function editProfit(){

        $bound_id = $this->request->param('bound_id');

        $boundModel = new AdminFinanceBoundModel();
        $bound_info = $boundModel->find($bound_id);

        $this->assign('bound_info',$bound_info);

        return $this->fetch();
    }

    public function editPostProfit(){
        $params = $this->request->param();

        $boundModel = new AdminFinanceBoundModel();
        $boundModel->where(['bound_id'=>$params['bound_id']])->update(['profit'=>$params['profit'],'memo'=>$params['memo']]);

        $this->success('编辑成功!', url('AdminFinance/index',array('type'=>'outBound')));
    }

    public function pe(){
        $pesModel = new PesModel();
        $shangzheng1500 = $pesModel->where(['code'=>'000001.XSHG'])->order('date ASC')->select()->toArray();

        $shangzheng1500pe = [];
        $shangzheng1500date = [];
        foreach ($shangzheng1500 as $key=>$val){
            $shangzheng1500pe[] = $val['pe'];
            $shangzheng1500date[] = $val['date'];
        }

        $shengzheng1000 = $pesModel->where(['code'=>'399011.XSHE'])->order('date ASC')->select()->toArray();

        $shengzheng1000pe = [];
        $shengzheng1000date = [];
        foreach ($shengzheng1000 as $key=>$val){
            $shengzheng1000pe[] = $val['pe'];
            $shengzheng1000date[] = $val['date'];
        }

        $chuangye800 = $pesModel->where(['code'=>'399102.XSHE'])->order('date ASC')->select()->toArray();

        $chuangye800pe = [];
        $chuangye800date = [];
        foreach ($chuangye800 as $key=>$val){
            $chuangye800pe[] = $val['pe'];
            $chuangye800date[] = $val['date'];
        }

        $husheng300 = $pesModel->where(['code'=>'399300.XSHE'])->order('date ASC')->select()->toArray();

        $husheng300pe = [];
        $husheng300date = [];
        foreach ($husheng300 as $key=>$val){
            $husheng300pe[] = $val['pe'];
            $husheng300date[] = $val['date'];
        }

        $this->assign('shangzheng1500pe',json_encode($shangzheng1500pe));
        $this->assign('shangzheng1500date',json_encode($shangzheng1500date));
        $this->assign('shengzheng1000pe',json_encode($shengzheng1000pe));
        $this->assign('shengzheng1000date',json_encode($shengzheng1000date));
        $this->assign('chuangye800pe',json_encode($chuangye800pe));
        $this->assign('chuangye800date',json_encode($chuangye800date));
        $this->assign('husheng300pe',json_encode($husheng300pe));
        $this->assign('husheng300date',json_encode($husheng300date));

        $this->assign('jinqishangzheng1500pe',json_encode(array_slice($shangzheng1500pe,-120)));
        $this->assign('jinqishangzheng1500date',json_encode(array_slice($shangzheng1500date,-120)));
        $this->assign('jinqishengzheng1000pe',json_encode(array_slice($shengzheng1000pe,-120)));
        $this->assign('jinqishengzheng1000date',json_encode(array_slice($shengzheng1000date,-120)));
        $this->assign('jinqichuangye800pe',json_encode(array_slice($chuangye800pe,-120)));
        $this->assign('jinqichuangye800date',json_encode(array_slice($chuangye800date,-120)));
        $this->assign('jinqihusheng300pe',json_encode(array_slice($husheng300pe,-120)));
        $this->assign('jinqihusheng300date',json_encode(array_slice($husheng300date,-120)));

        return $this->fetch();
    }

    public function peSelect(){
        $data = $this->request->param();

        $start = explode(' ',$data['start_time']);
        $end = explode(' ',$data['end_time']);
        $pesModel = new PesModel();
        $shangzheng1500 = $pesModel->where(['code'=>$data['code']])->where('date','>',$start['0'])->where('date','<',$end['0'])->order('date ASC')->select()->toArray();

        $shangzheng1500pe = [];
        $shangzheng1500date = [];
        foreach ($shangzheng1500 as $key=>$val){
            $shangzheng1500pe[] = $val['pe'];
            $shangzheng1500date[] = $val['date'];
        }

        $this->assign('shangzheng1500pe',json_encode($shangzheng1500pe));
        $this->assign('shangzheng1500date',json_encode($shangzheng1500date));

        //获取最近的相同长度的数据
        $shangzhengzuijin1500 = $pesModel->where(['code'=>$data['code']])->order('date DESC')->limit(count($shangzheng1500date))->select()->toArray();
        $shangzhengzuijin1500 = array_reverse($shangzhengzuijin1500);

        $shangzhengzuijin1500pe = [];
        foreach ($shangzhengzuijin1500 as $key=>$val){
            $shangzhengzuijin1500pe[] = $val['pe'];
        }

        $this->assign('shangzhengzuijin1500pe',json_encode($shangzhengzuijin1500pe));


        return $this->fetch();
    }

    public function notUse(){
        $data = $this->request->param();
        $bound_id = $data['a'];
        //查询memo
        $boundModel = new AdminFinanceBoundModel();
        $memo = $boundModel->where(['bound_id'=>$bound_id])->field('memo')->find();
        $memo_new = $memo['memo'].'  可省';
        $boundModel->where(['bound_id'=>$bound_id])->update(['memo'=>$memo_new]);
    }

}
