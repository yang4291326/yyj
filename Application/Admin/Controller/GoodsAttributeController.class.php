<?php
namespace Admin\Controller;

/**
 * Created by liuniukeji.com
 * 商品属性控制器
 * @author goryua <1661745274@qq.com>
*/

class GoodsAttributeController extends AdminCommonController {
    
    /**
     * 基础属性列表
     */
    public function index() {
        $cate_id = I('get.cate_id', 0, 'intval');
        $goodsAttrModel = D('ShopGoodsAttribute');
        //定义属性页面标题
        if ($cate_id) {
            $goods_cate_info = D('ShopGoodsType')->getCateInfo('name', array('id' => $cate_id));
            if (empty($goods_cate_info)) {
                $this->ajaxReturn(v(0, '该商品分类不存在'));
            }
            $attr_mode = 1;
            $list_title = $goods_cate_info['name'].' - 自定义属性列表';
        } else {
            $attr_mode = 0;
            $list_title = '基础属性列表';
        }
        $this->assign('cate_id', $cate_id);
        $this->assign('list_title', $list_title);

        $map['attr_mode'] = array('eq', $attr_mode);
        if ($attr_mode == 1){
            $map['member_id'] = array('eq', UID);
        }
        $map['cate_id'] = array('eq', $cate_id);
        $data = $goodsAttrModel->getAttrByPage($map);

        //设置分页变量
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);

        //自定义提示语
        $prompt_tips = $this->_getPromptLanguage();
        $this->assign('prompt_tips', $prompt_tips);

     	$this->display();
    }


    /**
     * 新增或编辑商品基础属性
     */
    public function edit() {
        $id = I('id', 0, 'intval');
        $cate_id = I('cate_id', 0, 'intval');
        $goodsAttrModel = D('ShopGoodsAttribute');
        if (IS_POST) {
            $attr_type = I('post.attr_type', 0, 'intval');
            //获取该商户允许自定义的属性个数
            $attr_num_access = $this->_getDataAccess('goods_attribute_num');
            //获取该商户已添加自定义属性个数
            //$has_add_num = $goodsAttrModel->getMemberAttrCount();//原代码
            /*杨yongjie  添加*/
            $has_add_num = $goodsAttrModel->getMemberAttrCounts();
            /*杨yongjie  添加*/
//            if ($has_add_num >= $attr_num_access) {
//                $this->ajaxReturn(V(0, '您可以添加的自定义属性已达到上限'));
//            }

            $data = $goodsAttrModel->create();
            if ($data !== false) {
                $goodsAttrModel->attr_value = ($attr_type == 2 || $attr_type == 3) ? I('post.attr_value') : '';
                if ($id > 0) {
                    $goodsAttrModel->where('id='. $id)->save();
                    $log_type = array('type' => 1, 'info' => '编辑');
                } else {
                    if ($has_add_num >= $attr_num_access) {
                        $this->ajaxReturn(V(0, '您可以添加的自定义属性已达到上限'));
                    }
                    $lastid = $goodsAttrModel->add();
                    $log_type = array('type' => 0, 'info' => '添加');
                }
                $this->_addAdminLog($log_type['type'], ''.$log_type['info'].'商品属性id为'.$lastid.'的记录成功', '', 0);
                $this->ajaxReturn(V(1, '保存成功'));
            } else {
                $this->_addAdminLog($log_type['type'], ''.$log_type['info'].'商品属性id为'.$lastid.'的记录失败', '', 1);
                $this->ajaxReturn(V(0, $goodsAttrModel->getError()));
            }
        } else {
            $info = $goodsAttrModel->find($id);
            $this->assign("info", $info);
            $this->assign("cate_id", $cate_id);
            $this->display();
        }
    }


    // 放入回收站
    public function del() {
        $this->_del('ShopGoodsAttribute');  //调用父类的方法
    }
    
}
?>