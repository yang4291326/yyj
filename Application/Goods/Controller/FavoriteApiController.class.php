<?php
namespace Goods\Controller;

/**
 * Created by liuniukeji.com
 * 收藏夹接口
 * @author yuanyulin <7556870234@qq.com>
*/
class FavoriteApiController extends \Common\Controller\CommonApiController {
    
    // 获取收藏夹列表接口
    public function getFavoriteList(){
        $customer_name = I('customer_name', '', 'trim');
        $where=array(); 
        if ($customer_name) {
            $this->homeLogApi(UID,33,'喜好收藏页面搜索 --'.$name,0,time(),'','');
            $where['customer_name'] = array('like', '%'.$customer_name.'%');
        }
        $favoriteList = D('Goods/Favorite')->getFavoriteByPage($where);
        //var_dump($favoriteList);die;
        $this->apiReturn(1, '收藏夹列表', $favoriteList);
    }
    
    // 增加收藏夹接口
    public function addFavorite(){
        if(IS_POST){
            $favoriteModel = D('Favorite');
            if($favoriteModel->create() !== false ){
            //p($favoriteModel->remark);die();
                if ( empty($favoriteModel->remark)) { // 该处用于处理没有传入内容概述
                    $favoriteModel->remark = '';
                }

                $id = $favoriteModel->add();
                $info = M('Favorite')->where('id='. $id)->find();
                $this->apiReturn(1, '保存成功！', $info);
            } else {
                $this->apiReturn(0, $favoriteModel->getError());
            }
        }
    }  
    /*杨yongjie  添加*/
    //修改收藏夹接口
    public function editFavorite(){
        $id=I('post.id',0,'intval');
        $where['id'] = array('EQ', $id);
        $data['customer_name']=I('post.customer_name','','trim');
        $data['customer_phone']=I('post.customer_phone','','trim');
        $data['customer_address']=I('post.customer_address','','trim');
        $data['customer_deposit']=I('post.customer_deposit','','trim');
        $result=M('Favorite')->where($where)->save($data);
        if($result){
            $infos = M('Favorite')->where('id='. $id)->find();
            $this->apiReturn(1, '修改成功！',$infos);
        }else{
            $this->apiReturn(0, '修改失败！');
        }
    }
    /*杨yongjie  添加*/
    // 删除收藏夹列表
    public function delFavorite(){

        $id = I('post.id', 0, 'intval');
        $where['id'] = array('EQ', $id);
        $result = M('Favorite')->where($where)->setField('status', 1);
        if ($result) {
            $this->apiReturn(1, '删除成功！');            
        } else {
            $this->apiReturn(0, '删除失败！');            
        }
    }
    /*杨yongjie  添加*/
    //总价修改接口
    public function editPriceTotal(){
        $id=I('post.id','','intval');
        $price_total=I('post.price_total','');
        if(!is_numeric($price_total)){
            $this->apiReturn(0,'金额必须为数字!');
        }
        $where['id']=$id;
        $data['price_total']=$price_total;
        $res=M('Favorite')->where($where)->save($data);
        if($res){
            $this->apiReturn(1, '修改成功！');
        }else{
            $this->apiReturn(0, '修改失败！');
        }
    }
    //生成客户随机编码并返回
    public function customer_Code(){
        $code=microtime();
        $customer_code=time().substr($code,2,2);
        $this->apiReturn(1,'客户随机编码',strval($customer_code));
    }
    /*杨yongjie  添加*/
}
