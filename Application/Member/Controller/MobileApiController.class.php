<?php
namespace Member\Controller;
use \Think\Controller;
/**
 * Created by PhpStorm.
 * User: YangYongJIE
 * Date: 2017/5/15
 * Time: 10:59
 * name:获取端口号
 */
/*杨yongjie  添加*/
class MobileApiController extends Controller{
    public function getMobileCode(){
        $code=$_POST['code'];
        //var_dump($code);
        if($code==''){
            $this->apiReturn(0,'非法访问');
        }
        $params=json_decode($code,true);
        $_POST=null;
        foreach($params as $k => $v){
            $_POST[$k] = $v;
        }
        if ($_POST['type']!=1 && $_POST['type']!=2){
            $this->apiReturn(0,'type 参数错误');
        }
        if($_POST['type']==1){//1为平板端  //返回端口号
            $where['machine_code']=$_POST['code'];
            $port=M('MemberMachineCode')->field('port')->where($where)->find();
            if($port){
                $this->apiReturn(1,'请求成功',$port);
            }else{
                $this->apiReturn(0,'机器码有误');
            }
        }elseif($_POST['type']==2){//2为手机端  //返回端口号
            $res=M('MemberMachineCode')->field('mobile_code,id')->select();//获取所有手机及其编码和对应id
            foreach($res as $k => $v){
                //传入的编码是否在每条记录的编码组内
                if(in_array($_POST['code'],explode(',',$v['mobile_code']))){
                    $where['id']=$v['id'];//如果在数组内获取其id
                    $port=M('MemberMachineCode')->field('port')->where($where)->find();//通过id获取端口号
                }
            }
            if($port){
                $this->apiReturn(1,'请求成功',$port);
            }else{
                $this->apiReturn(0,'机器码有误');
            }
        }
    }
    protected function apiReturn($status, $message='', $data=''){
        if ($status != 0 && $status != 1) {
            exit('参数调用错误 status');
        }

        if ($data != '' && C('APP_DATA_ENCODE') == true) {
            $data = json_encode($data); // 数组转为json字符串
            $aes = new \Common\Tools\Aes();
            $data = $aes->aes128cbcEncrypt($data); // 加密
        }

        if (is_null($data) || empty($data)) $data = array();
        $this->ajaxReturn(V($status, $message, $data));
    }
}
/*杨yongjie  添加*/