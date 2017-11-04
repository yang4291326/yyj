<?php 
namespace Timing\Controller;
use Think\Controller;
header("Content-type:text/html;charset=utf-8");
class TimingsController extends Controller{
	public function ClearData(){
        //获取所有用户id
        $con['status']=0;//0表示正常
        $con['disabled']=0;//0表示可用
        $member_ids=M('Member')->where($con)->field('id')->select();
        //获取收藏属性id
        $favorite_id=M('MemberAttribute')->field('id')->where(['attr_name'=>'用户收藏单清理时间'])->find();
        //获取确认属性id
        $cate_id=M('MemberAttribute')->field('id')->where(['attr_name'=>'用户商品确认单清理时间'])->find();
        //开启事物
        //M()->startTrans();
        //收藏过期清理  //获取用户收藏单过期清理时间
        foreach($member_ids as $k => $v){
            $where['member_id']=$v['id'];
            $where['attribute_id']=$favorite_id['id'];
            //获取收藏单过期清理天数
            $attr_value=M('MemberAttributeValue')->field('attr_value')->where($where)->find();
            //echo M('MemberAttributeValue')->getLastSql();
            if(intval($attr_value['attr_value'])>0){
                unset($where);
                $where['add_time']=array('lt',time()-$attr_value['attr_value']*24*3600);
                $where['status']=0;
                //获取过期的收藏id
                $id=M('Favorite')->field('id')->where($where)->select();
                unset($where);
                foreach($id as $key => $value){
                    $where['id']=$value['id'];
                    $data['status']=1;
                    //var_dump($where);
                    $res=M('Favorite')->where($where)->save($data);//修改状态
//                    if(!$res){
//                        M()->rollback();
//                    }
                }
            }
        }

        //商品确认过期清理  //获取商品确认单过期清理时间
        foreach($member_ids as $k => $v){
            $condition['member_id']=$v['id'];
            $condition['attribute_id']=$cate_id['id'];
            //获取商品确认单过期清理天数
            $cart_value=M('MemberAttributeValue')->field('attr_value')->where($condition)->find();
            if(intval($cart_value['attr_value'])>0){
                unset($condition);
                $condition['add_time']=array('lt',time()-$cart_value['attr_value']*24*3600);
                $condition['status']=0;
                //获取过期确认id
                $cart_id=M('ShoppingCart')->field('id')->where($condition)->select();
                unset($condition);
                foreach($cart_id as $key => $value){
                    $condition['id']=$value['id'];
                    $cart_data['status']=1;
                    //var_dump($condition);
                    $result=M('ShoppingCart')->where($condition)->save($cart_data);//修改状态
//                    if(!$result){
//                        M()->rollback();
//                    }
                }
            }
        }

        if($res && $result){
            //M()->commit();
            echo 'OK';
        }else{
            //M()->rollback();
            echo 'NO';
        }
	}
	public function postdata(){
        $data=$_POST;
        foreach ($data as $key => $value) {
            $k=$key;
            $v=$value;
        }
        echo "key:".$k."value:".$v;
    }
}