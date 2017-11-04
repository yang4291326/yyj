<?php
namespace Admin\Controller;

/**
 * Created by liuniukeji.com
 * 商品信息管理控制器
 * @author goryua <1661745274@qq.com>
*/

class GoodsController extends AdminCommonController {

    public function index() {
        //$goods_name = I('post.goods_name', '', 'trim');
        /*杨yongjie  修改*/
        $goods_name = I('get.goods_name', '', 'trim');//分页搜索用get请求
        /*杨yongjie  修改*/
        if ($goods_name) {
            $where['value.attr_value'] = array('like', '%'.$goods_name.'%');
        }
        $where['value.attribute_id'] = array('eq', 1);
        $where['goods.status'] = array('eq', 0);
        $where['goods.member_id'] = array('eq', UID);
        $goods_list = D('ShopGoods')->getGoodsList($where, 'goods.id, goods.add_time, goods.status, type.name, value.attr_value, member.user_name');
        //序号
        $order = 0;
        //自定义提示语
        // echo '<pre>';
        $prompt_tips = $this->_getPromptLanguage();
        //设置分页变量
//        echo '<pre>';
        //var_dump($goods_list);die();
        $this->assign('list', $goods_list['list']);
        $this->assign('page', $goods_list['page']);
        $this->assign('order',$order);
        $this->assign('prompt_tips', $prompt_tips);
        $this->display();
    }
    
    /**
     * 商品信息添加编辑
     */
    public function edit() {
        $goods_id = I('id', 0, 'intval');
        if (IS_POST) {
           $goods_attr_value = I('post.goods_attr_value', '');
           $goodsModel = D('ShopGoods');
           M()->startTrans();
           $create = $goodsModel->create();
           if ($create !== false){
               if ($goods_id) {
                    $goods_save = $goodsModel->where('id='. $goods_id)->save();
                    $log_type = array('type' => 1, 'info' => '编辑');
               } else {
                    $goods_save = $goods_id = $goodsModel->add();
                    $log_type = array('type' => 0, 'info' => '添加');
               }
               
           } else {
               $this->ajaxReturn(V(0, $goodsModel->getError()));
           }
           
           //保存商品属性值
           // var_dump($goods_attr_value);die;
           $attr_value_save = D('ShopGoodsAttributeValue')->saveAttrValue($goods_attr_value, $goods_id);
           if ($attr_value_save !== true){
                M()->rollback();
                $this->ajaxReturn(V(0, $attr_value_save));
           }

           //保存商品颜色
           $color_name = I('post.color_name', '');
           $color_pic = I('post.color_pic', '');
           $color_save = D('ShopGoodsColor')->saveColor($color_name, $color_pic, $goods_id);
           if ($color_save !== true){
                M()->rollback();
                $this->ajaxReturn(V(0, $color_save));
           }
           
           if ($goods_save !== false && $attr_value_save === true && $color_save === true) {
               M()->commit();
               $this->_addAdminLog($log_type['type'], ''.$log_type['info'].'商品id为'.$goods_id.'的记录成功', '', 0);
               $this->ajaxReturn(V(1, '保存成功'));
           } else {
               M()->rollback();
               $this->_addAdminLog($log_type['type'], ''.$log_type['info'].'商品id为'.$goods_id.'的记录失败', '', 1);
               $this->ajaxReturn(V(0, '保存失败'));
           }
 
        } else {

            //调取商品分类信息
            $goods_cate_list = D('ShopGoodsType')->getCateList('', 'type.id,type.name,type.parent_id,type.level as cate_level');
            $goods_cate_tree = D('Common/Tree')->toFormatTree($goods_cate_list);
            $this->assign('goods_cate_list', $goods_cate_tree);

            //读取系统设置基础属性
            $map['attr_mode'] = array('eq', 0);
            $map['attr_status'] = array('eq', 0);
            $basic_attr = D('ShopGoodsAttribute')->getAttrByPage($map, 'id,attr_name,attr_type,attr_value'); 
            $basic_attr_list = $this->customAttrToArray($basic_attr['list']);
            $this->assign('attr_list', $basic_attr_list);

            if ($goods_id) {
                $info = D('ShopGoods')->field(true)->find($goods_id);
                $this->assign('info', $info);
                //取出该分类下的商家自定义属性
                if ($info['goods_category_id']) {
                    $cate_attr_list = $this->getCateAttr($info['goods_category_id']);
                    $this->assign('cate_attr_list', $cate_attr_list);
                }

                //取出商品系统基础属性值及商家自定义属性值
                $where['goods_id'] = array('eq', $goods_id);
                $attr_data = D('ShopGoodsAttributeValue')->getAttrValue($where, 'attribute_id, attr_value');
                foreach ($attr_data as $v) {
                    $attr_value[$v['attribute_id']] = $v;
                }
                $this->assign('attr_value', $attr_value);

                //取出商品颜色值
                $color_data = D('ShopGoodsColor')->getColorList($where, 'id, color, photo_path, default_color_pic');
                foreach ($color_data as $v) {
                    $color_array[$v['color']]['name'] = $v['color']; 
                    $color_array[$v['color']]['default_pic'] = $v['default_color_pic'];   
                    $color_array[$v['color']]['list'][] = $v;        
                }
                $m = 1;
                foreach ($color_array as $k => $v) { //给取出的颜色数组，赋num值
                    $color_list[$k] = $v;
                    $color_list[$k]['num'] = $m++;
                }
                $this->assign('color_list', $color_list);

                $this->assign('color_num', count($color_list));

                $this->display('edit');
            } else {
                $this->display('add');
            }
        }
    }
    
    /**
     * 商品详情管理
     */
    public function details() {
        $goods_id = I('id', 0, 'intval');

        if (IS_POST) {

            $detail = I('post.detail', '');

            /*杨yongjie  添加*/
            //从前台获取添加的总图片个数
            $fontcount=count($detail['path']);
            //从数据库获取此商品已经添加的商品详情图片总数
            $count=D('ShopGoodsDetail')->usableCount($goods_id);
            //获取此用户下所有商品可添加的商品详情图片总个数
            $accessCount=$this->accessCount();
//            var_dump($count);
//            var_dump($accessCount);
//            die;
            //前台判断添加的图片总个数是否超过权限
            if($fontcount > $accessCount){
                $this->ajaxReturn(V(0, '对不起!您可以添加的图片个数已达上限!'));
            }
            //后台从数据库判断图片总个数是否超过权限
            if($count >= $accessCount){
                $this->ajaxReturn(V(0, '对不起!您可以添加的图片个数已达上限!'));
            }
            /*杨yongjie  添加*/

            //保存详情
            $detail_save = D('ShopGoodsDetail')->saveDetailValue($detail, $goods_id);
            if ($detail_save !== true){
                $this->ajaxReturn(V(0, $detail_save));
            } else {
                $this->ajaxReturn(V(1, '保存成功'));
            }

        } else {
            $where['goods_id'] = array('eq', $goods_id);
            //取出表中存在的详情
            $detail_list = D('ShopGoodsDetail')->getDetailList($where);
            $this->assign('detail_list', $detail_list);
            $this->assign('detail_num', count($detail_list));

            //取出商品属性名称
            $where['attribute_id'] = array('eq', 1);
            $goods_info = D('ShopGoodsAttributeValue')->getAttrInfo($where, 'attr_value');
            $this->assign('goods_info', $goods_info);   
            $this->assign('id', $goods_id);

            if (!empty($detail_list) && is_array($detail_list)) {
                $this->display('details_edit');
            } else {
                $this->display('details_add');
            }
        }
    }

    /**
     * 商品详情排序
     */
    public function detailSort() {
        if (IS_GET) {
            $id = I('get.id');
            $where['goods_id'] = $id;
            $detail_list = D('ShopGoodsDetail')->getDetailList($where, 'id, name, sort');
            $this->assign('detail_list', $detail_list);
            $this->assign('id', $id);
            $this->display('detail_sort');

        } elseif (IS_POST) {
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key=>$value) {
                $res = D('ShopGoodsDetail')->where(array('id'=>$value))->setField('sort', $key+1);
            }
            if ($res !== false) {
                $this->ajaxReturn(V(1, '排序成功'));
            } else {
                $this->ajaxReturn(V(0, '排序失败'));
            }
        } else {
            $this->ajaxReturn(V(0, '非法请求'));
        }
    }

    /**
     * 根据select选择的商品分类id，获取对应自定义属性
     */
    public function getCustomAttr() {
        $cate_id = I('post.id', 0, 'intval');
        $cate_level = D('ShopGoodsType')->where('id='. $cate_id)->getField('level');
        if ($cate_level != 4) {
            $this->ajaxReturn(V(0, '请选择第4级分类'));
        }
        
        $where['attr_mode'] = array('eq', 1);
        $where['member_id'] = array('eq', UID);
        $where['attr_status'] = array('eq', 0);
        $where['cate_id'] = array('eq', $cate_id);
        $attr_list = D('ShopGoodsAttribute')->getAttrByPage($where, 'id,attr_name,attr_type,attr_value');
        $custom_attr_list = $this->customAttrToArray($attr_list['list']);
        $this->assign('attr_list', $custom_attr_list);
        $return_html = $this->fetch('get_custom_attr');
        $this->ajaxReturn(V(1, '', $return_html));
    }

    /**
     * 根据商品分类id，取出该分类下自定义的属性
     */
    protected function getCateAttr($cid) {

        $where['attr_mode'] = array('eq', 1);
        $where['member_id'] = array('eq', UID);
        $where['attr_status'] = array('eq', 0);
        $where['cate_id'] = array('eq', $cid);
        $attr_list = D('ShopGoodsAttribute')->getAttrByPage($where, 'id,attr_name,attr_type,attr_value');
        $custom_attr_list = $this->customAttrToArray($attr_list['list']);
        return $custom_attr_list;
    }
    
    /**
     * 把单选和多选项转化为数组
     */
    protected function customAttrToArray($attr_list) {
        foreach ($attr_list as $key => $val) {
            $customArray[$key] = $val;
            if ($val['attr_type'] == 2 || $val['attr_type'] == 3) {
                $customArray[$key]['attr_value'] = explode(',', $val['attr_value']);
            }
        }

        return $customArray;
    }

    /**
     * 商品VR模型
     */
    public function vrModel() {
        
        $model_id = I('model_id', 0, 'intval');
        if (IS_POST) {
            $goods_id = I('post.goods_id', 0, 'intval');
            $vr_data = I('post.vr', '');
            $vrModel = D('ShopGoodsModel');
            $vr_edit=$vrModel->field('model_path,ico,material_tiling,description,goods_id,name')
                            ->where('id='. $model_id)
                            ->select();
            //var_dump($vrModel->create());die;
            M()->startTrans();
            $create = $vrModel->create();
            if ($create !== false){
                if ($model_id) {
                    $vr_save = $vrModel->where('id='. $model_id)->save();
                    /*yyjie 添加*/
                    if($vr_save){
                        $_POST['flag1']=false;
                        if($create != $vr_edit){
                            $_POST['flag1']=true;
                            /*如果插入时数据时VR模型数据改变,它的version字段值加0.1*/   //setInc自动增长
                            $vr_save = $vrModel->where('id='. $model_id)->setInc('version',0.1);
                        }
                    }
                    /*yyjie 添加*/

                } else {
                    $vr_save = $model_id = $vrModel->add();
                }
                
            } else {
                $this->ajaxReturn(V(0, $vrModel->getError()));
            }

            //保存模型户型
//            $layout_save = D('ShopGoodsModelLayout')->saveLayout($vr_data, $model_id);
//
//            /*yyjie 添加*/
//            /*如果插入时数据时模型户型数据改变,它的version字段值加0.1*/
//            if($_POST['layout']['flag']==true){
//                  //echo 'ok';die;
//                /*多组数据发生改变,每组的版本号都要加0.1*/
//               foreach($_POST['layout']['id'] as $k =>$v){
//                 D('ShopGoodsModelLayout')->where('id ='.$v)->setInc('version',0.1);
//               }
//            }
//            /*yyjie 添加*/
//
//            if ($layout_save !== true){
//                M()->rollback();
//                $this->ajaxReturn(V(0, $layout_save));
//            }
            

            // 保存模型属性
            $resource_save = D('ShopGoodsModelResource')->saveResource($vr_data, $model_id);
            if ($resource_save !== true){
                M()->rollback();
                $this->ajaxReturn(V(0, $resource_save));
            }
            //if ($vr_save !== false && $layout_save === true && $resource_save === true) {//yangyongjie 修改
            if ($vr_save !== false && $resource_save === true) {
                /*yyjie 添加*/
                /*如果插入时数据时模型数据改变,它的version字段值加0.1*/
                //var_dump($_POST['resource']['flag']);die;
                if($_POST['resource']['flag'] == true){
                    foreach($_POST['resource']['id'] as $k =>$v ){
                       D('ShopGoodsModelResource')->where('id ='.$v)->setInc('version',0.1); 
                    }
                }
                /*三张子表中的任何数据发生变化,总表的version 字段值加 0.1*/
                //if($_POST['flag1']==true || $_POST['layout']['flag']==true || $_POST['resource']['flag']==true){//yangyongjie 修改
                if($_POST['flag1']==true || $_POST['resource']['flag']==true){
                    $where['attribute_id']=36;
                    $where['member_id']=UID;
                    //D('member_attribute_value')->where($where)->setInc('attr_value',0.1);
                    $attr_value=D('MemberAttributeValue')->where($where)->field('attr_value')->find();
                    $datas['attr_value']=floatval($attr_value['attr_value'])+0.1;
                    D('MemberAttributeValue')->where($where)->save($datas);
                }
                /*yyjie 添加*/
                
                M()->commit();
                $this->ajaxReturn(V(1, '保存成功'));
               
            } else {
                M()->rollback();
                $this->ajaxReturn(V(0, '保存失败'));
            }

        } else {
            $goods_id = I('get.id', 0, 'intval');
            $where['goods_id'] = array('eq', $goods_id);
            //通过ln_Shop_Goods_Model的id=goods_id 查询所有信息
            $model_info = D('ShopGoodsModel')->getModelInfo($where);
            $this->assign('model_info', $model_info);
            $this->assign('goods_id', $goods_id);
            if (empty($model_info)) {
                $this->display('vr_model_add');
            } else {
            /*杨yongjie  修改*/
                //商品户型列表
                //$layout_list = D('ShopGoodsModelLayout')->getLayoutList(array('model_id' => $model_info['id']));
                //$this->assign('layout_list', $layout_list);
                //$this->assign('layout_num', count($layout_list));
            /*杨yongjie  修改*/


                //商品户型属性
                $resource_list= D('ShopGoodsModelResource')->getResourceList(array('model_id' => $model_info['id']));
                $this->assign('resource_list', $resource_list);
                $this->assign('resource_num', count($resource_list));

                $this->display('vr_model_edit');
            }
        }
    }


    /**
     * 删除图片
     */
    public function delFile() {
        $this->_delFile();  //调用父类的方法
    }

    /**
     * 上传图片
     */
    public function uploadImg() {
        $this->_uploadImg();  //调用父类的方法
    }

    public function uploadField(){
        $this->_uploadField();  //调用父类的方法
    }

    /**
     * 放入回收站
     */
    public function recycle() {
        $this->_recycle('ShopGoods');  //调用父类的方法
    }
    //物理删除
    public function del() {
        $this->_del('ShopGoodsDetail');  //调用父类的方法
        //$this->_del('ShopGoodsModelLayout');  //调用父类的方法
        //$this->_del('ShopGoodsModelResource');  //调用父类的方法
    }
    public function delete() {
        $type = I('type');
        $id = I('id');
        if ($type == 1) {
            M('ShopGoodsModelLayout')->where('id =' . $id)->delete();
        } else {
            M('ShopGoodsModelResource')->where('id =' . $id)->delete();
        }
        $this->ajaxReturn(V(1, '删除成功'));

    }

    /*杨yongjie  添加*/
    //获取商品详情图片权限个数
    public function accessCount(){
        $where['member_id']=UID;
        $accessCount=D('DataAccess')->where($where)->field('goods_detail_photo_num')->find();
        return $accessCount['goods_detail_photo_num'];
    }
    /*杨yongjie  添加*/
}
?>
