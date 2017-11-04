<?php
namespace Admin\Controller;
/**
 * Created by liuniukeji.com
 * 商品分类控制器
 * @author goryua <1661745274@qq.com>
*/

class GoodsCategoryController extends AdminCommonController {

    /**
     * 商品分类列表
     */
    public function index() {
        $goodsTypeModel = D('ShopGoodsType');
        
        //获取分类列表
        $goods_cate_list = $goodsTypeModel->getCateList('', 'type.id,type.name,type.sort,type.parent_id,type.status,type.level as cate_level,type.add_time,member.user_name');
        $goods_cate_tree = D('Common/Tree')->toFormatTree($goods_cate_list);
        //p($goods_cate_list);exit;
        $this->assign('goods_cate_list', $goods_cate_tree);
        //序号
        $order = 0;
        //自定义提示语
        $prompt_tips = $this->_getPromptLanguage();
        $this->assign('prompt_tips', $prompt_tips);
        $this->assign('order',$order);
     	$this->display();
    }
    
    /**
     * 添加或编辑商品分类
     */
    public function edit() {
    	$id = I('id', 0, 'intval');
        $goodsTypeModel = D('ShopGoodsType');
    	if (IS_POST) {
            $pid = I('post.parent_id', 0, 'intval');
            if ($pid) {
                $level = $goodsTypeModel->where('id = '.$pid.'')->getField('level');
                $level+=1;
            } else {
                $level = 1;
            }
            if ($level > 4) {
                $this->ajaxReturn(V(0, '层级最多只能添加到4级'));
            }
            
            if ($id == 0) { //添加时验证权限
                //获取该商户该等级下分类允许添加个数
                $save_access = $this->_getDataAccess('goods_type_num_'.$level.'');
                //获取该商户该等级下已经添加的个数
                $level_count = $this->hasAddCount($level);
                if ($level_count >= $save_access) {
                    $this->ajaxReturn(V(0, ''.$level.'级分类最多只能添加'.$save_access.'个，您已达到上限！'));
                }
            }

            switch ($level) { //根据不同分类级别，赋予不同验证时间
                case 2:
                    $_validate = 4;
                    break;
                case 3:
                    $_validate = 5;
                    break;
                case 4:
                    $_validate = 6;
                    break;
                default:
                    $_validate = 3;
                    break;
            }
            $data = $goodsTypeModel->create(I('post.'), $_validate);
            if ($data !== false) {
                $goodsTypeModel->level = $level;
                if ($id > 0) {
                    $goodsTypeModel->where('id='. $id)->save();
                    $log_type = array('type' => 1, 'info' => '编辑');
                } else {
                    $id = $goodsTypeModel->add();
                    $log_type = array('type' => 0, 'info' => '添加');
                }

                $this->_addAdminLog($log_type['type'], ''.$log_type['info'].'分类id为'.$id.'的记录成功', '', 0);
                $this->ajaxReturn(V(1, '保存成功'));
   
            } else {
                $this->_addAdminLog($log_type['type'], ''.$log_type['info'].'分类id为'.$id.'的记录失败', '', 1);
                $this->ajaxReturn(V(0, $goodsTypeModel->getError()));
            }
     	} else {
            $info = $goodsTypeModel->find($id);
            $goodsCategory = $goodsTypeModel->getCateList('', 'type.id,type.name,type.sort,type.parent_id,type.level as cate_level');
            $goodsCategory = D('Common/Tree')->toFormatTree($goodsCategory);
            $this->assign('goodsCategory', $goodsCategory);
            $this->assign('info', $info);
            
            $this->display();
     	}
    }
    /*杨yongjie  添加*/
    public function add(){
        if(IS_POST){
            if($_FILES['layout_photo_path']['name']=='' && $_FILES['layout_path']['name']==''){
                $data['member_id']=UID;
                $data['name']=$_POST['name'];
                $data['layout_style']=$_POST['layout_style'];
                $data['layout_scene']=$_POST['layout_scene'];
                $data['initial_position']=$_POST['initial_position'];
                $data['initial_direction']=$_POST['initial_direction'];
                $data['is_default']=$_POST['is_default'];
                $data['layout_photo_path']='';
                $data['layout_path']='';
                $result=M('ShopGoodsModelLayout')->add($data);
                if($result){
                    $this->success('新增成功','/Admin/GoodsCategory/add');
                }else{
                    $this->error('新增失败');
                }
            }else{
                $config = array(
                    'mimes'         =>  array(), //允许上传的文件MiMe类型
                    'maxSize'       =>  8*1024*1024, //上传的文件大小限制 (0-不做限制)
                    'exts'          =>  array('jpg','png','gif','jpeg'), //允许上传的文件后缀
                    'autoSub'       =>  true, //自动子目录保存文件
                    'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
                    'rootPath'      =>  './Uploads/', //保存根路径
                    'savePath'      =>  'Picture/'.CONTROLLER_NAME.'/', //保存路径
                    'saveName'      =>  array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
                    'saveExt'       =>  '', //文件保存后缀，空则使用原后缀
                    'replace'       =>  false, //存在同名是否覆盖
                    'hash'          =>  true, //是否生成hash编码
                    'callback'      =>  false, //检测文件是否存在回调，如果存在返回文件信息数组
                );
                $upload=new \Think\Upload($config);
                $info=$upload->upload($_FILES);
                if(!$info){
                    $this->error($upload->getError(),'/Admin/GoodsCategory/add',2);//图片上传失败跳转到添加页面重新上传图片
                }else{
                    $data['member_id']=UID;
                    $data['name']=$_POST['name'];
                    $data['layout_style']=$_POST['layout_style'];
                    $data['layout_scene']=$_POST['layout_scene'];
                    $data['initial_position']=$_POST['initial_position'];
                    $data['initial_direction']=$_POST['initial_direction'];
                    $data['is_default']=$_POST['is_default'];
                    $data['layout_photo_path']='/Uploads/'.$info['layout_photo_path']['savepath'].$info['layout_photo_path']['savename'];
                    $data['layout_path']='/Uploads/'.$info['layout_path']['savepath'].$info['layout_path']['savename'];//图片保存路径拼接上图片的名称
                    $result=M('ShopGoodsModelLayout')->add($data);
                    if($result){
                        $this->success('新增成功','/Admin/GoodsCategory/add');
                    }else{
                        $this->error('新增失败');
                    }
                }
            }
        }else{
            $this->display();
        }
    }
    public function update(){
        if(IS_POST){
            //大版本更新时用
            $_POST['layout']['BOSS']=false;
            $posts=$_POST;
            $files=$_FILES;
            //dump($files);die();
            $layout=M('ShopGoodsModelLayout');
            foreach($files as $k => $v){
                //小版本更新时用
                $_POST['layout']['switch']=flase;
                //都为空证明没有图片上传修改
                if($v['name']['layout_photo_path']=='' && $v['name']['layout_path']==''){
                    //表单提交的数据(即将修改的数据)
                    $where['id']=$k;
                    $data['name']=$posts[$k]['name'];
                    $data['layout_photo_path']=$posts[$k]['old_layout_photo_path'];
                    $data['layout_path']=$posts[$k]['old_layout_path'];
                    $data['layout_style']=$posts[$k]['layout_style'];
                    $data['layout_scene']=$posts[$k]['layout_scene'];
                    $data['initial_position']=$posts[$k]['initial_position'];
                    $data['initial_direction']=$posts[$k]['initial_direction'];
                    $data['is_default']=$posts[$k]['is_default'];
                    //通过$where['id']=$k去数据库查询相关信息
                    $select_data=$layout->where($where)->field('name,layout_photo_path,layout_path,layout_style,layout_scene,initial_position,initial_direction,is_default')->select();
                    //dump($data);
                    foreach($select_data as $k => $v){
                        //数据库查询后重新组装的数据
                        $new_data['name']=$v['name'];
                        $new_data['layout_photo_path']=$v['layout_photo_path'];
                        $new_data['layout_path']=$v['layout_path'];
                        $new_data['layout_style']=$v['layout_style'];
                        $new_data['layout_scene']=$v['layout_scene'];
                        $new_data['initial_position']=$v['initial_position'];
                        $new_data['initial_direction']=$v['initial_direction'];
                        $new_data['is_default']=$v['is_default'];
                    }
                    //比较:表单提交的数据和通过表单提交的id去数据库查询得出的数据是否一致,如果一致证明没有数据修改,不用去数据库修改了
                    //如果不相等,去数据库修改数据,并且将开关($_POST['layout']['switch'])设置为true
                    if($data !== $new_data){
                        $res=$layout->where($where)->save($data);
                        if(!$res){
                            $this->error('修改失败');
                        }
                        $_POST['layout']['switch']=true;
                        $_POST['layout']['BOSS']=true;
                        //如果为true,version字段值加0.1
                        if($_POST['layout']['switch']=true){
                            $layout->where($where)->setInc('version',0.1);
                        }
                        continue;
                    }
                }elseif($v['name']['layout_photo_path'] && $v['name']['layout_path']==''){
                    //dump($v);die;
                    /*重新组装图片上传数组格式(upload类文件不识别,
                    组装成识别的格式[layout_photo_path表示输入框的
                    name属性值,name,type,tmp_name,error,size分别是图片的属性])*/
                    $new_file['layout_photo_path']=array(
                        'name'=>$v['name']['layout_photo_path'],
                        'type'=>$v['type']['layout_photo_path'],
                        'tmp_name'=>$v['tmp_name']['layout_photo_path'],
                        'error'=>$v['error']['layout_photo_path'],
                        'size'=>$v['size']['layout_photo_path'],
                    );
                    //dump($new_file);die;
                    $upload=new \Think\Upload($this->uploadconfig());
                    $info=$upload->upload($new_file);
                    if(!$info){
                        $this->error($upload->getError());
                    }
                    //dump($info);
                    $src=C('UPLOAD_ROOTPATH').$info['layout_photo_path']['savepath'].$info['layout_photo_path']['savename'];
                    $where['id']=$k;
                    $data['name']=$posts[$k]['name'];
                    $data['layout_photo_path']=$src;
                    $data['layout_path']=$posts[$k]['old_layout_path'];
                    $data['layout_style']=$posts[$k]['layout_style'];
                    $data['layout_scene']=$posts[$k]['layout_scene'];
                    $data['initial_position']=$posts[$k]['initial_position'];
                    $data['initial_direction']=$posts[$k]['initial_direction'];
                    $data['is_default']=$posts[$k]['is_default'];
                    //修改
                    $res=$layout->where($where)->save($data);
                    if(!$res){
                        $this->error('修改失败');
                    }
                    $_POST['layout']['switch']=true;
                    $_POST['layout']['BOSS']=true;
                    //如果为true,version字段值加0.1
                    if($_POST['layout']['switch']=true){
                        $layout->where($where)->setInc('version',0.1);
                    }
                    continue;
                }elseif($v['name']['layout_path'] && $v['name']['layout_photo_path']==''){
                    $new_file['layout_path']=array(
                        'name'=>$v['name']['layout_path'],
                        'type'=>$v['type']['layout_path'],
                        'tmp_name'=>$v['tmp_name']['layout_path'],
                        'error'=>$v['error']['layout_path'],
                        'size'=>$v['size']['layout_path'],
                    );
                    //dump($new_file);die;
                    $upload=new \Think\Upload($this->uploadconfig());
                    $info=$upload->upload($new_file);
                    if(!$info){
                        $this->error($upload->getError());
                    }
                    $src=C('UPLOAD_ROOTPATH').$info['layout_path']['savepath'].$info['layout_path']['savename'];
                    $where['id']=$k;
                    $data['name']=$posts[$k]['name'];
                    $data['layout_photo_path']=$posts[$k]['old_layout_photo_path'];
                    $data['layout_path']=$src;
                    $data['layout_style']=$posts[$k]['layout_style'];
                    $data['layout_scene']=$posts[$k]['layout_scene'];
                    $data['initial_position']=$posts[$k]['initial_position'];
                    $data['initial_direction']=$posts[$k]['initial_direction'];
                    $data['is_default']=$posts[$k]['is_default'];
                    $res=$layout->where($where)->save($data);
                    if(!$res){
                        $this->error('修改失败');
                    }
                    $_POST['layout']['switch']=true;
                    $_POST['layout']['BOSS']=true;
                    //如果为true,version字段值加0.1
                    if($_POST['layout']['switch']=true){
                        $layout->where($where)->setInc('version',0.1);
                    }
                    continue;
                }elseif($v['name']['layout_photo_path'] && $v['name']['layout_path']){
                    $new_file['layout_photo_path']=array(
                        'name'=>$v['name']['layout_photo_path'],
                        'type'=>$v['type']['layout_photo_path'],
                        'tmp_name'=>$v['tmp_name']['layout_photo_path'],
                        'error'=>$v['error']['layout_photo_path'],
                        'size'=>$v['size']['layout_photo_path'],
                    );
                    $new_file['layout_path']=array(
                        'name'=>$v['name']['layout_path'],
                        'type'=>$v['type']['layout_path'],
                        'tmp_name'=>$v['tmp_name']['layout_path'],
                        'error'=>$v['error']['layout_path'],
                        'size'=>$v['size']['layout_path'],
                    );
                    //dump($new_file);die;
                    $upload=new \Think\Upload($this->uploadconfig());
                    $info=$upload->upload($new_file);
                    if(!$info){
                        $this->error($upload->getError());
                    }
//                    dump($info);die;
                    $layout_photo_path=C('UPLOAD_ROOTPATH').$info['layout_photo_path']['savepath'].$info['layout_photo_path']['savename'];
                    $layout_path=C('UPLOAD_ROOTPATH').$info['layout_path']['savepath'].$info['layout_path']['savename'];
                    $where['id']=$k;
                    $data['name']=$posts[$k]['name'];
                    $data['layout_photo_path']=$layout_photo_path;
                    $data['layout_path']=$layout_path;
                    $data['layout_style']=$posts[$k]['layout_style'];
                    $data['layout_scene']=$posts[$k]['layout_scene'];
                    $data['initial_position']=$posts[$k]['initial_position'];
                    $data['initial_direction']=$posts[$k]['initial_direction'];
                    $data['is_default']=$posts[$k]['is_default'];
                    $res=$layout->where($where)->save($data);
                    if(!$res){
                        $this->error('修改失败');
                    }
                    $_POST['layout']['switch']=true;
                    $_POST['layout']['BOSS']=true;
                    //如果为true,version字段值加0.1
                    if($_POST['layout']['switch']=true){
                        $layout->where($where)->setInc('version',0.1);
                    }
                    continue;
                }
            }
            //如果为true member_attribute_value表的attr_value字段值加1
            if($_POST['layout']['BOSS']==true){
                $con['attribute_id']=36;
                $con['member_id']=UID;
                $attr_value=M('MemberAttributeValue')->where($con)->field('attr_value')->find();
                $datas['attr_value']=floatval($attr_value['attr_value'])+0.1;
                M('MemberAttributeValue')->where($con)->save($datas);
            }
            $this->success('修改成功','/Admin/GoodsCategory/index');
        }else{
            $where['member_id']=UID;
            $res=M('ShopGoodsModelLayout')->where($where)->field('id,name,layout_photo_path,layout_path,layout_style,layout_scene,initial_position,initial_direction,is_default')->select();
            //dump($res);die;
            $this->assign('res',$res);
            $this->display();
        }
    }
    /*杨yongjie  添加*/
    public function selectGoods() {

        $goods_name = I('get.goods_name', '', 'trim');
        $cate_id = I('get.cate_id', 0, 'intval');
        if ($goods_name) {
            $where['value.attr_value'] = array('like', '%'.$goods_name.'%');
        }
        if ($cate_id) {
            $where['goods.goods_category_id'] = array('eq', $cate_id);
        }
        $where['value.attribute_id'] = array('eq', 1);
        $where['goods.status'] = array('eq', 0);
        $where['goods.member_id'] = array('eq', UID);
        $goods_list = D('ShopGoods')->getGoodsList($where, 'goods.id, goods.add_time, value.attr_value');
        
        $this->assign('goods_name', $goods_name);
        $this->assign('cate_id', $cate_id);
        //设置分页变量
        $this->assign('goods_list', $goods_list['list']);
        $this->assign('page', $goods_list['page']);

        //商品分类
        $goodsCategory = D('ShopGoodsType')->getCateList();
        $goodsCategory = D('Common/Tree')->toFormatTree($goodsCategory);
        $goodsCategory = array_merge(array(0=>array('id'=>0, 'title_show'=>'选择商品分类')), $goodsCategory);
        $this->assign('goodsCategory', $goodsCategory);


        $this->display('Banner/goods');
    }
    
    /**
     * 商户在该等级下已添加分类个数
     */
    protected function hasAddCount($level = 0) {
        $where['member_id'] = array('eq', UID);
        $where['level'] = array('eq', $level);
        $where['status'] = array('eq', 0);
        $count = D('ShopGoodsType')->where($where)->count('id');
        return $count;
    }

    protected function checkShopCategory() {
        //判断商户一级分类是否存在，不存在插入
        $where['member_id'] = array('eq', UID);
        $where['level'] = array('eq', 1);
        $shop_category_info = D('ShopGoodsType')->getCateInfo('id', $where);
        if (empty($shop_category_info)) {
            $data['member_id'] = UID;
            $data['name'] = D('MemberAttributeValue')->getMemberAttributeValueByMemberIdAndMemeberProperty(UID, 15);
            $data['level'] = 1;
            M('shop_goods_type')->add($data);
        }
    }

    /**
     * 商品分类放入回收站
     */
    public function recycle() {
        
        $this->_recycle('ShopGoodsType');  //调用父类的方法
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
    /*杨yongjie  添加*/
    public function uploadconfig(){
        return $config = array(
            'mimes'         =>  array(), //允许上传的文件MiMe类型
            'maxSize'       =>  8*1024*1024, //上传的文件大小限制 (0-不做限制)
            'exts'          =>  array('jpg','png','gif','jpeg'), //允许上传的文件后缀
            'autoSub'       =>  true, //自动子目录保存文件
            'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath'      =>  './Uploads/', //保存根路径
            'savePath'      =>  'Picture/'.CONTROLLER_NAME.'/', //保存路径
            'saveName'      =>  array('uniqid', $_SERVER['REQUEST_TIME']), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt'       =>  '', //文件保存后缀，空则使用原后缀
            'replace'       =>  false, //存在同名是否覆盖
            'hash'          =>  true, //是否生成hash编码
            'callback'      =>  false, //检测文件是否存在回调，如果存在返回文件信息数组
        );
    }
    public function delete(){
        $where['id']=$_POST['id'];
        $res=M('ShopGoodsModelLayout')->where($where)->delete();
        if($res){
            $this->ajaxReturn(V(1, '删除成功'));
        }else{
            $this->ajaxReturn(V(-1, '删除失败'));
        }
    }
    /*杨yongjie  添加*/
}
?>