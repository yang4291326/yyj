<?php
namespace Admin\Controller;

/**
 *  后台用户控制器
 * @author yuanyulin <QQ:755687023>
 */
class MemberController extends AdminCommonController {

    // 管理员（用户）列表
    public function index(){
        $map = I('type', 0, 'intval'); // 获取用户的类型 （0： 表示商户 1： 表示管理员）
        
        $memberAttributeData = M('MemberAttribute')->field('id, attr_name')->where('attr_status=0')->order('attr_sort desc')->limit(3)->select();
        $memberData = D('Member')->getMemberByPage($map, $memberAttributeData); // 根据查询分页显示管理员(用户)列表 
       
        $this->assign('getMemberAttributeData', $memberAttributeData); // 将附加信息的头属性显示到页面中
        
       	$this->assign('type', $map); // 用来判断是管理员还是用户
       	$this->assign('list', $memberData['list']);
       	$this->assign('page', $memberData['page']);
        $prompt_tips = $this->_getPromptLanguage(); //获取提示语
        $this->assign('prompt_tips', $prompt_tips);

        $this->display();
    }
    
    // 修改管理员（用户）信息
    public  function edit(){    
        $id   = I('id', 0, 'intval');
        $type = I('type', 2, 'intval'); // 如果type值传递为2则代表该用户为修改操作 （1：表示为添加管理员，2表示为添加商户）
        if(IS_POST){
            $memberData             = I('post.member','');                // 需要修改或添加到用户表的信息
            $userAttributeValueData = I('post.userAttributeValue','');    // 需要插入用户附加属性信息表中的信息
            $userAttributeNameData = I('post.userAttributeName','');      // 需要插入用户附加属性信息表中的信息
//            $memberMachineCodeData  = I('post.memberMachineCodeData',''); // 需要插入用户机器码表的信息
            
            $memberModel = D('Member');
            M()->startTrans();  // 开启事务
            if($memberModel->create($memberData) !== false){
                if ($id > 0) {
                    $log_type = array('type' => 1, 'info' => '编辑');                   
                    $memberResult = $memberModel->where('id='. $id)->save();
                } else {
                    $memberResult = $id = $memberModel->add(); // 此处的$id表示该用户是新加的那么就将该用户id保存下来，用于后续的两张表中。
                    $log_type = array('type' => 0, 'info' => '添加');
                    //默认插入商户一级分类
                    $this->insertShopType($id);
                }
            } else {
                $this->ajaxReturn(V(0, $memberModel->getError()));
            }
            
            // 插入用户附加属性信息表
            $memberAttributeValueResult = D('MemberAttributeValue')->editMemberAttributeValue($userAttributeValueData, $id, $userAttributeNameData);
            if ($memberAttributeValueResult !== true) {
                M()->rollback(); // 事务回滚
                $this->ajaxReturn(V(0, $memberAttributeValueResult));
            }
          
//            // 插入用户机器码表的信息
//            $memberMachineCodeResult = D('MemberMachineCode')->editMemberMachineCode($memberMachineCodeData, $id);
//            if ($memberMachineCodeResult != 1) {
//                $this->ajaxReturn(V(0, $memberMachineCodeResult));
//            }
            
            if ( ($memberResult && $memberAttributeValueResult != 1) ) {
                M()->rollback(); // 事务回滚
                $this->_addAdminLog($log_type['type'], ''.$log_type['info'].'id为'.$memberData['id'].'的记录失败', '', 1);
            } else {

                M()->commit(); // 事务提交
                $this->_addAdminLog($log_type['type'], ''.$log_type['info'].'id为'.$memberData['id'].'的记录成功', '', 0);
                $this->ajaxReturn(V(1, '保存成功'));                
            } 
            
        } else {
       
            if ($type == 1) { // type值为1表示添加管理员
                $where['attr_status'] = array('EQ', 0);
                $where['attr_mode']   = array('IN', array(1, 2)); // (对应ln_member_attribute表中的attr_mode属性，获取管理员可以拥有的附加属性)
                $adminAttributeData = M('MemberAttribute')->field(true)->where($where)->order('attr_sort desc')->select(); // 获取所有的添加的附加属性
                $adminAttributeData = $this->mosaicUserAttributeDataAndMemberAttributeData($adminAttributeData); // 获取处理好的附加属性

                $this->assign('type', $type);
                $this->assign('memberMachineCodeCount', 0);           
                $this->assign('mergeUserAttribute', $adminAttributeData); 
            } elseif($type == 0) { // type值为0表示添加商户
                $where['attr_status'] = array('EQ', 0);
                $where['attr_mode']   = array('IN', array(0, 2)); // (对应ln_member_attribute表中的attr_mode属性，获取商户可以拥有的附加属性)
                $shopAttributeData = M('MemberAttribute')->field(true)->where($where)->order('attr_sort desc')->select(); // 获取所有的添加的附加属性
                $shopAttributeData = $this->mosaicUserAttributeDataAndMemberAttributeData($shopAttributeData); // 获取处理好的附加属性
                
                $this->assign('type', $type);
                $this->assign('memberMachineCodeCount', 0);           
                $this->assign('mergeUserAttribute', $shopAttributeData);           
                
            } elseif($type == 2) { // type值为2表示用户是修改操作
                $info = M('Member')->find($id); // 获取该用户在member表（主用户表中)的信息
                $memberCustomAttributeData = M('MemberAttributeValue')->field('attribute_id, attr_value')->where('member_id='. $id)->select(); // 获取该用户的附加属性值
                $where['attr_status'] = array('EQ', 0);
                $where['attr_mode']   = array('IN', array((int)$info['type'], 2)); // (对应ln_member_attribute表中的attr_mode属性，根据该用户的类型来过滤此用户可以拥有的附加属性)
                $memberAttributeData = M('MemberAttribute')->field(true)->where($where)->order('attr_sort desc')->select(); // 获取所有的添加的附加属性
                $mergeUserAttribute = $this->mosaicUserAttributeDataAndMemberAttributeData($memberAttributeData, $memberCustomAttributeData); // 获取合并好的用户属性值
                
//                $memberMachineCodeData = M('MemberMachineCode')->where('member_id='. $id)->field(true)->select(); // 获取该用户的机器码信息
//                $memberMachineCodeCount = M('MemberMachineCode')->where('member_id='. $id)->count(); // 获取用户机器码的总数(用于js)
                
                $this->assign('info', $info);
                $this->assign('mergeUserAttribute', $mergeUserAttribute);          
                
//                $this->assign('memberMachineCodeData', $memberMachineCodeData);
//                $this->assign('memberMachineCodeCount', $memberMachineCodeCount);
            }
            $this->display();
        }
    } 
    
    // 访问用户授权页面
    public function auth(){
        $id = I('id', 0 , 'intval');

        if (IS_POST) {
            $id    = I('post.id', 0, 'intval'); 
            $rules = I('post.rules', '');
            
            $stringRules = implode(",", $rules);
            
            M('Member')->where('id='.$id)->setField('menu_ids', ''); // 提交时先滞空再写入
            $result = M('Member')->where('id='.$id)->setField('menu_ids', $stringRules); // 将数据更新到数据库
            if ($result)
                $this->ajaxReturn(V(1, '保存成功'));   
            
        } else {
            $memberAuths = M('Member')->where('id='. $id)->getfield('menu_ids'); // 获取到的用户的权限
            $nodeTreeList = $this->returnTreeNodes(); // 获取树状权限列表
            
            $this->assign('id', $id);           // 将id传递到页面中
            $this->assign('nodeTreeList',  $nodeTreeList);
            $this->assign('this_group', $memberAuths); 
            $this->display();
        }
    }
    
    // 访问用户修改个人信息页面
    public function adminSetting(){
        $machineCodeNum = $this->_getDataAccess('machine_code_num'); // 用户限制的机器码的数量

        if (IS_POST) {
            $memberData             = I('post.member','');                // 需要修改或添加到用户表的信息
            $userAttributeValueData = I('post.userAttributeValue','');    // 需要插入用户附加属性信息表中的信息
            $memberMachineCodeData  = I('post.memberMachineCodeData',''); // 需要插入用户机器码表的信息
            if ( $memberMachineCodeData != '') { // 如果用户提交了机器码才验证
                $memberMachineCodeDataNum = count($memberMachineCodeData); // 获取用户插入机器码的个数（防止用户提交假的数据）
                if ( $memberMachineCodeDataNum > $machineCodeNum ) {
                    $this->ajaxReturn(V(0, '可以添加的机器码的总量超过了限制！'));
                }                
            }            

            $memberData['id'] = UID; // 添加用户的id          
            
            $memberModel = D('Member');
            
            M()->startTrans();  // 开启事务
            if ($memberModel->create($memberData) !== false) {
                $memberResult = $memberModel->where('id='. UID)->save();
            } else {
                $this->ajaxReturn(V(0, $memberModel->getError()));
            }

            /*杨yongjie  添加*/
            foreach($userAttributeValueData as $k => $v){
                $attr_nm=M('MemberAttribute')->where('id='.$k)->field('attr_name')->find();//获取属性名称
                $attr_name[$k]=$attr_nm['attr_name'];//重组
            }
            /*杨yongjie  添加*/

            // 插入用户附加属性信息表
            $memberAttributeValueResult = D('MemberAttributeValue')->editMemberAttributeValue($userAttributeValueData, UID,$attr_name);
            if ($memberAttributeValueResult != 1) {
                $this->ajaxReturn(V(0, $memberAttributeValueResult));
            }
            
            // 插入用户机器码表的信息
            $memberMachineCodeResult = D('MemberMachineCode')->editMemberMachineCode($memberMachineCodeData, UID);
            if ($memberMachineCodeResult != 1) {
                $this->ajaxReturn(V(0, $memberMachineCodeResult));
            }
            
            if ( ($memberResult && $memberAttributeValueResult != 1) ) {
                M()->rollback(); // 事务回滚
            } else {
                M()->commit(); // 事务提交
                $this->ajaxReturn(V(1, '保存成功'));
                //$this->success('保存成功',"/Admin/Member/adminsetting");
            }
        } else {
            $prompt_tips = $this->_getPromptLanguage(); //获取提示语
            $info = M('Member')->field('id, type, user_name')->where('id='.UID)->find();

            $memberCustomAttributeData = M('MemberAttributeValue')->field('attribute_id, attr_value')->where('member_id='. UID)->select(); // 获取该用户的附加属性值

            $where['attr_status'] = array('EQ', 0); // 属性状态，0可用 1禁用
            $where['is_edit']     = array('EQ', 1); // 是否允许填写（0 否 1 是）
            $where['attr_mode']   = array('IN', array((int)$info['type'], 2)); // (对应ln_member_attribute表中的attr_mode属性，根据该用户的类型来过滤此用户可以拥有的附加属性)
            $memberAttributeData = M('MemberAttribute')->field(true)->where($where)->order('attr_sort desc')->select(); // 获取所有的添加的附加属性
            unset($where);

            $mergeUserAttribute = $this->mosaicUserAttributeDataAndMemberAttributeData($memberAttributeData, $memberCustomAttributeData); // 获取合并好的用户属性值
            
            $memberMachineCodeData = M('MemberMachineCode')->where('member_id='. UID)->field(true)->limit($machineCodeNum)->select();
            $promptTips = $this->_getPromptLanguage(); //获取提示语
            $this->assign('promptTips', $promptTips);
            $this->assign('info', $info);
            $this->assign('mergeUserAttribute', $mergeUserAttribute);

            $this->assign('machineCodeNum', $machineCodeNum);

            $this->assign('memberMachineCodeData', $memberMachineCodeData);
//            $this->assign('memberMachineCodeCount', $memberMachineCodeCount);
            $this->assign('memberMachineCodeCount', $machineCodeNum);
            $this->assign('prompt_tips', $prompt_tips);

            $this->display('adminSetting');            
        }        
    }
    
    // 访问用户数据权限
    public function dataAuth(){
        $id = I('id', 0, 'intval'); // 注意：get过来的id是member表中对应的用户id，而post过来的id对应的是data_access表中的主键id
        
        if (IS_POST) {
            $dataAccessModel = D('DataAccess');
            if ($dataAccessModel->create() !== false) {
                if ($id > 0) {                   
                    $dataAccessModel->where('id='. $id)->save();
                } else {
                    $dataAccessModel->add();
                }
                $this->ajaxReturn(V(1, '保存成功'));
            } else {
                $this->ajaxReturn(V(0, $dataAccessModel->getError()));
            }
        } else {
            $memberInfo = M('DataAccess')->field(true)->where('member_id='.$id)->find();
            $this->assign('id', $id);
            $this->assign('info', $memberInfo);
            $this->display('dataAuth');
        }     
    }
    
    /**
     * 返回后台节点数据
     * @param boolean $tree    是否返回多维数组结构(生成菜单时用到),为false返回一维数组(生成权限节点时用到)
     * @retrun array
     * 注意,返回的主菜单节点数组中有'controller'元素,以供区分子节点和主节点
     */
    protected function returnTreeNodes($tree = true){
        static $tree_nodes = array();
        if ( $tree && !empty($tree_nodes[(int)$tree]) ) {
            return $tree_nodes[$tree];
        }
        if ((int)$tree) {
            $list = M('Menu')->field('id,pid,title,url,tip,display')->order('sort asc')->where('display=1')->select();
            foreach ($list as $key => $value) {
                if( stripos($value['url'],MODULE_NAME)!==0 ){
                    $list[$key]['url'] = MODULE_NAME.'/'.$value['url'];
                }
            }
            $nodes = list_to_tree($list,$pk='id',$pid='pid',$child='operator',$root=0);
            foreach ($nodes as $key => $value) {
                if(!empty($value['operator'])){
                    $nodes[$key]['child'] = $value['operator'];
                    unset($nodes[$key]['operator']);
                }
            }
        } else {
            $nodes = M('Menu')->field('title,url,tip,pid')->order('sort asc')->where('display=1')->select();
            foreach ($nodes as $key => $value) {
                if( stripos($value['url'],MODULE_NAME)!==0 ){
                    $nodes[$key]['url'] = MODULE_NAME.'/'.$value['url'];
                }
            }
        }
        $tree_nodes[(int)$tree]   = $nodes;
        return $nodes;
    }    
    
    /**
     * 拼接用户的附加属性值和所有的附加属性值，并且如果所有的附加属性值的类型为枚举，则将该枚举的属性变换成数组
     * @param type $memberAttributeData   所有的自定义属性
     * @param type $userAttributeData     用户的附加属性
     * @param return $memberAttributeData   处理好的合并属性值
     */
    private function mosaicUserAttributeDataAndMemberAttributeData($memberAttributeData, $userAttributeData=0){

        foreach ($memberAttributeData as $key => $value) {
            if ($value['attr_type'] == 2) // 2代表该属性的类型为枚举型（1111, 2222）,将其变为数组类型
                $memberAttributeData[$key]['attr_value'] = explode(',', $value['attr_value']);
            
            if ($userAttributeData == 0) {
                continue;
            }

            foreach ($userAttributeData as $k => $v) {
                if ($value['id'] == $v['attribute_id']) {
                    $memberAttributeData[$key]['user_attr_value'] = $v['attr_value'];
                }
            }
        }
        return $memberAttributeData;
    }
    
    // 逻辑删除
    public function recycle(){
        $this->_recycle('Member');  //调用父类的方法
    }

    protected function insertShopType($mid) {
        $data['member_id'] = $mid;
        $data['name'] = trim($_POST['userAttributeValue'][15]);
        $data['level'] = 1;
        M('shop_goods_type')->add($data);
    }
}
