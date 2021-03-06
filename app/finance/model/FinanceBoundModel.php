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

class FinanceBoundModel extends Model
{
    protected $type = [
        'goods' => 'json',
    ];

    public function saveInBound($data){
        $goodsModel = new GoodsModel();
        $goodinfo = $goodsModel->find($data['goods_id']);
        if($data['unit'] == '1'){
            $totalAmount = number_format($goodinfo['size']*$goodinfo['buying_price']*$data['size'],2,'.','');
        }else{
            $totalAmount = number_format($goodinfo['buying_price']*$data['size'],2,'.','');
        }


        $amount = $totalAmount + $data['float_money'];

        $user = cmf_get_current_user();
        $save_data['bound_id'] = $this->createBoundId();
        $save_data['money'] = $totalAmount;
        $save_data['float_money'] = $data['float_money'];
        $save_data['amount'] = $amount;
        $save_data['user_id'] = $user['id'];
        $save_data['type'] = 'inBound';
        $save_data['memo'] = $data['memo'];
        $save_data['goods'] = [$data['goods_id']=>$data['size']];
        $save_data['createtime'] = time();
        $save_data['unit'] = $data['unit'];
        if($this->save($save_data)){
            return $save_data['bound_id'];
        }else{
            return false;
        }

    }

    public function saveOutBound($data,$total_money,$out_goods,$grade_pmt,$profit){

        $amount = $total_money + $data['float_money'] - $grade_pmt;

        $user = cmf_get_current_user();
        $save_data['bound_id'] = $this->createBoundId();
        $save_data['money'] = $total_money;
        $save_data['float_money'] = $data['float_money'];
        $save_data['amount'] = $amount;
        $save_data['user_id'] = $user['id'];
        $save_data['type'] = 'outBound';
        $save_data['memo'] = $data['memo'];
        $save_data['goods'] = $out_goods;
        $save_data['createtime'] = time();
        $save_data['price_grade'] = $data['out_grade'];
        $save_data['grade_pmt'] = $grade_pmt;
        $save_data['unit'] = '2';
        $save_data['profit'] = $profit + $data['float_money'];
        if($this->save($save_data)){
            return $save_data['bound_id'];
        }else{
            return false;
        }

    }

    public function createBoundId(){

        $bound_id = $this->generatorId();

        if($bound_id){
            return $bound_id;
        }else{
            return $this->createBoundId();
        }
    }

    public function generatorId(){
        //16位支付单号 时间戳+6位随机数
        $bound_id = time();
        $bound_id .= rand(111111,999999);

        $res = $this->where('bound_id',$bound_id)->find();

        if(empty($res)){
            return $bound_id;
        }else{
            return false;
        }
    }



}