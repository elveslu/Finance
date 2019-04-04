<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\finance\controller;

use cmf\lib\Storage;
use think\Validate;
use think\Image;
use cmf\controller\UserBaseController;
use app\user\model\UserModel;
use think\Db;
use app\finance\model\GoodsModel;
use app\finance\model\FinanceBoundModel;

class BoundController extends UserBaseController
{

    public function inBound(){
        $user = cmf_get_current_user();
        $this->assign($user);

        $goodsModel = new GoodsModel();
        $goods = $goodsModel->where(['user_id'=>$user['id'],'status'=>'1'])->select();
        $this->assign('goods',$goods);

        return $this->fetch();
    }

    public function jumpInBound(){
        $user = cmf_get_current_user();
        $this->assign($user);

        if ($this->request->isPost()) {
            $validate = new Validate([
                'goods_id' => 'require',
                'size' => 'integer|gt:-1',
                'float_money' => 'number'
            ]);
            $validate->message([
                'goods_id.require'   => '必须选择一款产品进行入库',
                'size.integer'         =>'箱数必须填写大于等于0的整数',
                'size.gt'         =>'箱数必须填写大于等于0的整数',
                'float_money.number'         =>'浮动金额请填写数字',
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $boundModel = new FinanceBoundModel();

            //生成入库单
            $bound_id = $boundModel->saveInBound($data);

            if($bound_id){
                $this->success("请确认信息无误后提交！", url("bound/jumpInBoundPage",['bound_id'=>$bound_id]));
            }else{
                $this->error('系统出错，请联系管理员！');
            }

        } else {
            $this->error(lang('ERROR'));
        }
    }

    public function jumpInBoundPage()
    {
        $user = cmf_get_current_user();
        $this->assign($user);

        $boundModel = new FinanceBoundModel();
        $bound_id = $this->request->param('bound_id');
        $boundinfo = $boundModel->find($bound_id)->toArray();

        $goodsModel = new GoodsModel();
        $g_info = '';
        $g_size = '';
        foreach ($boundinfo['goods'] as $goods_id => $num){
            $goods_info = $goodsModel->find($goods_id);
            $str = $goods_info['name'].' * '.$num.'（箱）';
            $g_info .= $str;

            $price = number_format($goods_info['buying_price']*$goods_info['size'],2);
            $str_size = $goods_info['buying_price'].'（元） * '.$goods_info['size'].'（个） = '.$price.' /箱';
            $g_size .= $str_size;
        }
        $boundinfo['goods_info'] = $g_info;
        $boundinfo['goods_size'] = $g_size;
        $this->assign('boundinfo', $boundinfo);

        return $this->fetch();
    }

    public function SureInBound(){
        $goodsModel = new GoodsModel();
        $boundModel = new FinanceBoundModel();

        $boundinfo = $boundModel->find($_POST['bound_id'])->toArray();

        foreach ($boundinfo['goods'] as $goods_id => $num){
            $goods_info = $goodsModel->find($goods_id);

            //更新库存
            $inCount = $num*$goods_info['size'];
            $total = $inCount + $goods_info['num'];

            $flg = $goodsModel->where(['id'=>$goods_id])->update(['num'=>$total]);
        }

        if($flg){
            $this->success("入库成功！", url("bound/inBound"));
        }else{
            $boundModel->where(['bound_id'=>$_POST['bound_id']])->delete();
            $this->error('入库失败，请联系管理员！',url("bound/inBound"));
        }


    }

    function CancelInBound(){
        $boundModel = new FinanceBoundModel();
        $boundModel->where(['bound_id'=>$_POST['bound_id']])->delete();
        $this->success("请返回重新操作！", url("bound/inBound"));
    }
}