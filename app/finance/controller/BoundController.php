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
use app\finance\model\GoodsGroupModel;

class BoundController extends UserBaseController
{
    public function boundIndex(){
        return $this->fetch();
    }

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
        Db::startTrans();
        foreach ($boundinfo['goods'] as $goods_id => $num){
            $goods_info = $goodsModel->find($goods_id);

            //更新库存
            $inCount = $num*$goods_info['size'];
            $total = $inCount + $goods_info['num'];

            $flg = $goodsModel->where(['id'=>$goods_id])->update(['num'=>$total]);
        }

        if($flg){
            $flg2 = $boundModel->where(['bound_id'=>$_POST['bound_id']])->update(['status'=>1]);
            if($flg2){
                Db::commit();
                $this->success("入库成功！", url("bound/inBound"));
            }else{
                Db::rollback();
                $this->error('入库失败，请联系管理员！',url("bound/inBound"));
            }

        }else{
            Db::rollback();
            $this->error('入库失败，请联系管理员！',url("bound/inBound"));
        }


    }

    function CancelInBound(){
        $boundModel = new FinanceBoundModel();
        $boundModel->where(['bound_id'=>$_POST['bound_id']])->delete();
        $this->success("请返回重新操作！", url("bound/inBound"));
    }

    public function outBound(){
        $user = cmf_get_current_user();
        $this->assign($user);

        $goodsModel = new GoodsModel();
        $goods = $goodsModel->where(['user_id'=>$user['id'],'status'=>'1'])->select();
        $this->assign('goods',$goods);

        return $this->fetch();
    }

    public function jumpOutBound(){
        $user = cmf_get_current_user();
        $this->assign($user);

        if ($this->request->isPost()) {

            $data = $this->request->post();
            $boundModel = new FinanceBoundModel();
            $goodsModel = new GoodsModel();
            $grade_info = [];
            $out_goods = [];
            $total_money = 0;
            $grade_pmt = 0;

            //组数据
            if($data['out_grade'] == 'retail_price'){
                //记录套装数据
                foreach ($data['goods'] as $goods_id=>$num){
                    if($num) {
                        $goods_info = $goodsModel->find($goods_id);
                        if (array_key_exists($goods_info['group_id'], $grade_info)) {
                            $grade_info[$goods_info['group_id']] += $num;
                        } else {
                            $grade_info[$goods_info['group_id']] = $num;
                        }
                    }
                }
                unset($grade_info[0]);
                if($grade_info){
                    $gradeModel = new GoodsGroupModel();
                    foreach ($grade_info as $grade_id => $value){
                        $grade_price = $gradeModel->find($grade_id)->toArray();
                        //查询是否刚好组成套装
                        $single = floor($value / $grade_price['count']);
                        $real_price = $goodsModel->where(['group_id'=>$grade_id])->find();
                        $coordinates_pmt = $real_price['retail_price']*$grade_price['count']-$grade_price['group_price'];
                        $grade_pmt += $single*$coordinates_pmt;
                    }
                }

            }

            foreach ($data['goods'] as $goods_id=>$num){
                if($num){
                    $goods_info = $goodsModel->find($goods_id);

                    //验证库存
                    if($goods_info['num'] < $num){
                        $msg = $goods_info['name'].'库存不足，请确认后重试！';
                        $this->error($msg);
                    }

                    //计算金额
                    $out_goods[$goods_id] = $num;
                    $total_money += number_format($goods_info[$data['out_grade']] * $num,2,'.','');
                }
            }

            //生成出库单
            $bound_id = $boundModel->saveOutBound($data,$total_money,$out_goods,$grade_pmt);

            if($bound_id){
                $this->success("请确认信息无误后提交！", url("bound/jumpOutBoundPage",['bound_id'=>$bound_id]));
            }else{
                $this->error('系统出错，请联系管理员！');
            }

        } else {
            $this->error(lang('ERROR'));
        }
    }

    public function jumpOutBoundPage()
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
            $num_price = number_format($goods_info[$boundinfo['price_grade']]*$num,2,'.','');
            $str = $goods_info['name'].' * '.$num.'（个）  小计：'.$num_price.'（元）</br>';
            $g_info .= $str;
        }
        $boundinfo['goods_info'] = $g_info;
        $this->assign('boundinfo', $boundinfo);

        return $this->fetch();
    }

    public function SureOutBound(){
        $goodsModel = new GoodsModel();
        $boundModel = new FinanceBoundModel();

        $boundinfo = $boundModel->find($_POST['bound_id'])->toArray();
        Db::startTrans();
        if($boundinfo['goods']){
            foreach ($boundinfo['goods'] as $goods_id => $num){
                $goods_info = $goodsModel->find($goods_id);

                //更新库存
                $total = $goods_info['num'] - $num;

                $flg = $goodsModel->where(['id'=>$goods_id])->update(['num'=>$total]);
            }
        }else{
            $flg = true;
        }

        if($flg){
            $flg2 = $boundModel->where(['bound_id'=>$_POST['bound_id']])->update(['status'=>1]);
            if($flg2){
                Db::commit();
                $this->success("出库成功！", url("bound/outBound"));
            }else{
                Db::rollback();
                $this->error('入库失败，请联系管理员！',url("bound/outBound"));
            }
        }else{
            Db::rollback();
            $this->error('入库失败，请联系管理员！',url("bound/outBound"));
        }


    }

    function CancelOutBound(){
        $boundModel = new FinanceBoundModel();
        $boundModel->where(['bound_id'=>$_POST['bound_id']])->delete();
        $this->success("请返回重新操作！", url("bound/outBound"));
    }
}