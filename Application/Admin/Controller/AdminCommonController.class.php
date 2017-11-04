<?php

namespace Admin\Controller;
use Think\Controller;
/**
 * 后台基类
 */
class AdminCommonController extends Controller {

    /**
     * 后台控制器初始化
     */
    protected function _initialize(){
        // //记录当前用户id
        define('UID', is_login()['uid']);
        // define('UID', 1);//暂时定义用户id为admin的id
        if( !UID ){// 还没登录 跳转到登录页面
            $this->redirect('Public/login');
        }
        
        $where['id'] = array('eq', UID);
        $disabled = M('Member')->where($where)->getfield('disabled');
        unset($where);
        if ($disabled == 1) {
            session(null);
            $this->redirect('Public/login');
        }

        /* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config = api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置
        //验证权限,防止用户跨界访问
    	$admin_id=C('USER_ADMINISTRATOR');
    	//非超级管理员情况下需要根据关联的用户组的权限判断权限
    	//下面代码暂时注释掉,当需要开放权限控制时释放即可
        $url = MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
        $noCheck = array('index','welcome','uploadimg','uploadfield','delfile','getgoodspics','ajaxchangeskin','ajaxchangeisdefault','selectgoods','getcustomattr','changedisabled', 'detailsort', 'cartdetail', 'alliancemerchantmemberedit');
        if (UID!=$admin_id && 'cropper' != ACTION_NAME && !in_array(ACTION_NAME , $noCheck)) {
            $res = $this->_checkPrivilege(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
        } else {
            $res = array('status'=>1,'info'=>'允许访问!');
        }

		// if (UID!=$admin_id && $url != 'Admin/Index/index' && $url != 'Admin/Index/welcome' && 'uploadimg' != ACTION_NAME && ACTION_NAME !='delfile'  &&  $url != 'Admin/Article/getgoodspics' && 'cropper' != ACTION_NAME && $url !='Admin/Skin/ajaxchangeskin'&& $url !='Admin/Banner/ajaxChangeIsDefault') {
		// 	$res = $this->_checkPrivilege(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
		// } else {
		// 	$res = array('status'=>1,'info'=>'允许访问!');
		// }
    	//$res = array('status'=>1,'info'=>'允许访问!');

        if(!$res['status']){//没有权限直接返回  status=0
        	if(IS_AJAX){
        		$this->ajaxReturn(v(0, $res['info']));
        		exit;
        	}else{
        		$this->error($res['info']);
        		exit;
        	}
        }
    }

	private function _checkPrivilege($url){
        $_lst = D('Member')->where('id='.UID)->getfield('menu_ids');
	    $where['id'] = array('in',$_lst);
	    $where['display'] = 1;
	    $where['url']=$url;
        $has =  M('Menu')->where($where)->count();
        if($has < 1){
            return array('status'=>0,'info'=>'您当前帐号没有此操作权限');
        }
        return array('status'=>1,'info'=>'允许访问!');
    }

    /**
     * 检测是否是需要动态判断的权限
     * @return boolean|null
     *      返回true则表示当前访问有权限
     *      返回false则表示当前访问无权限
     *      返回null，则会进入checkRule根据节点授权判断权限
     */
    protected function checkDynamic(){
        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }
        return null;//不明,需checkRule
    }


    /**
     * action访问控制,在 **登陆成功** 后执行的第一项权限检测任务
     *
     * @return boolean|null  返回值必须使用 `===` 进行判断
     *
     *   返回 **false**, 不允许任何人访问(超管除外)
     *   返回 **true**, 允许任何管理员访问,无需执行节点权限检测
     *   返回 **null**, 需要继续执行节点权限检测决定是否允许访问
     */
    final protected function accessControl(){
        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }
		$allow = C('ALLOW_VISIT');
		$deny  = C('DENY_VISIT');
		$check = strtolower(CONTROLLER_NAME.'/'.ACTION_NAME);
        if ( !empty($deny)  && in_array_case($check,$deny) ) {
            return false;//非超管禁止访问deny中的方法
        }
        if ( !empty($allow) && in_array_case($check,$allow) ) {
            return true;
        }
        return null;//需要检测节点权限
    }

    /**物理删除
     * ajax 删除指定数据库的记录
     * @param string $table: 操作的数据库
     * @return json: 直接返回客户端json
     */
    protected function _del($table){
        $id = I('id', 0);
        $result = V(0, '删除失败, 未知错误');
        if($table != '' && $id != 0){
            if( M($table)->delete($id) !== false ){
                $result = V(1, '删除成功');
                unset($where);
                $where['url'] = array('like', '%'.MODULE_NAME.'/'.CONTROLLER_NAME.'%');
                $menu_id =  M('Menu')->where($where)->getfield('id');
                $this->_addAdminLog(2, '删除了id为'.$id.'的记录。', $menu_id, 0);
            } else {
                //记录删除日志
                unset($where);
                $where['url'] = array('like', '%'.MODULE_NAME.'/'.CONTROLLER_NAME.'%');
                $menu_id =  M('Menu')->where($where)->getfield('id');
                $this->_addAdminLog(2, '删除了id为'.$id.'的记录。', $menu_id, 1);
            }
        }
        $this->ajaxReturn($result);
    }

    /**
     * ajax 数据更新到回收站
     * @param string $table: 操作的数据库
     * @return json: 直接返回客户端json
     */
    protected function _recycle($table){
        $id = I('id', 0);
        $result = V(0, '删除失败, 未知错误');
        if($table != '' && $id != 0){
            $where['id'] = array('in', $id);
            $data['status'] = 1;
            if( M($table)->data($data)->where($where)->save() !== false ) {
                $result = V(1, '删除成功');
                //记录删除日志
                unset($where);
                $where['url'] = array('like', '%'.MODULE_NAME.'/'.CONTROLLER_NAME.'%');
                $where['group'] = array('neq', '');
                $menu_id =  M('Menu')->where($where)->getfield('id');
                $this->_addAdminLog(2, '删除了id为'.$id.'的记录。', $menu_id, 0);
            } else {
                //记录删除日志
                unset($where);
                $where['url'] = array('like', '%'.MODULE_NAME.'/'.CONTROLLER_NAME.'%');
                $menu_id =  M('Menu')->where($where)->getfield('id');
                $this->_addAdminLog(2, '删除了id为'.$id.'的记录。', $menu_id, 1);
            }
        }
        $this->ajaxReturn($result);
    }

    /**
     * ajax 还原回收站的数据
     * @param string $table: 操作的数据库
     * @return json: 直接返回客户端json
     */
    protected function _restore($table){
        $id = I('id', 0);
        $result = V(0, '还回失败, 未知错误');
        if($table != '' && $id != 0){
            $where['id'] = array('in', $id);
            $data['status'] = 0;
            if( M($table)->data($data)->where($where)->save() !== false){
                $result = V(1, '还原成功');
            }
        }
        $this->ajaxReturn($result);
    }

    /**disabled在数据库中代表启用和禁用
     * ajax 修改数据的启用性
     * @param string $table: 操作的数据库
     * @return json: 直接返回客户端json
     */
    protected function _changeDisabled($table){
        $id = I('id', 0, 'intval');
        $disabled = I('disabled', 0, 'intval');
        $result = V(0, '修改状态失败, 未知错误'. $table . $id);
        if ($disabled != 0 && $disabled != 1) {
            $this->ajaxReturn(V(0, '修改状态失败, 状态值不正常'));
        }
        if($table != '' && $id != 0){
            $where['id'] = array('in', array($id));
            if ($disabled == 0) {
                $data['disabled'] = 1;
            } else if ($disabled == 1) {
                $data['disabled'] = 0;
            }
            $result = V(1, '还原成功');
            if( M($table)->data($data)->where($where)->save() !== false){
                $result = V(1, '修改状态成功');
            }
        }
        $this->ajaxReturn($result);
    }

    /**
     * 覆盖上传封面, 缩略图
     */
    protected function _uploadImg(){
        //处理手机端因为url未变时框架会读取本地缓存的图片数据，导致修改的图片未更新的问题(导致原因：修改图片时只是修改了资源，)
        //$oldImg = I('oldImg', '', 'htmlspecialchars');
        $oldImg = '';
        $savePath = I('savePath', '', 'htmlspecialchars');
        if($savePath != '') $savePath = $savePath . '/';

        $result = array( 'status' => 1, 'msg' => '上传完成');
        //判断有没有上传图片
        //p(trim($_FILES['photo2']['name']));
        if(trim($_FILES['photo']['name']) != ''){
            $upload = new \Think\Upload(C('PICTURE_UPLOAD')); // 实例化上传类
            $upload->replace  = true; //覆盖
            $upload->savePath = $savePath; //定义上传目录
            //如果有上传名, 用原来的名字
            if($oldImg != '') $upload->saveName = $oldImg;
            // 上传文件
            $info = $upload->uploadOne($_FILES['photo']);
            if(!$info) {
                $result = array( 'status' => 0, 'msg' => $upload->getError() );
            }else{
                if ($oldImg != '') {
                    //删除缩略图
                    $dir = '.'.C('UPLOAD_PICTURE_ROOT') . '/' . $info['savepath'];
                    $filesnames = dir($dir);
                    while($file = $filesnames->read()){
                        if ((!is_dir("$dir/$file")) AND ($file != ".") AND ($file != "..")) {
                            $count = strpos($file, $oldImg.'_');
                            if ($count !== false) {
                                if (file_exists("$dir/$file") == true) {
                                    @unlink("$dir/$file");
                                }
                            }
                        }   
                    }
                    $filesnames->close();
                }
                $result['src'] = C('UPLOAD_PICTURE_ROOT') . '/' . $info['savepath'] . $info['savename'];
            }
            $this->_addAdminLog(4, '上传了图片名称为'.$info['savename'].'的图片。', '', 0);
            $this->ajaxReturn($result);
        }
    }

    /**
     * 覆盖上传附件
     */
    protected function _uploadField(){
        $oldImg = I('oldImg', '', 'htmlspecialchars');
        $savePath = I('savePath', '', 'htmlspecialchars');

        if($savePath != '') $savePath = $savePath . '/';

        $result = array( 'status' => 1, 'msg' => '上传完成');
        //判断有没有上传图片
        //p(trim($_FILES['photo2']['name']));
        if(trim($_FILES['photo']['name']) != ''){
//            $upload = new \Think\Upload(C('FIELD_UPLOAD')); // 实例化上传类
            $upload = new \Think\Upload(C('PICTURE_UPLOAD')); // 实例化上传类
            $upload->replace  = true; //覆盖
            $upload->savePath = $savePath; //定义上传目录
            $upload->exts = array('jpg','png','gif','jpeg','doc','docx','ppt','pptx','pps','xls','xlsx','pot','vsd','rtf','wps','et','dps','pdf','txt','mp3','3gp','wmv','avi','mp4','apk','jn','unity3d', 'zip'); //定义上传格式
            //如果有上传名, 用原来的名字
            //if($oldImg != '') $upload->saveName = $oldImg;
            // 上传文件
            $info = $upload->uploadOne($_FILES['photo']);
            if(!$info) {
                $result = array( 'status' => 0, 'msg' => $upload->getError() );
            }else{
//                $result['src'] = C('UPLOAD_FIELD_ROOT') . '/' . $info['savepath'] . $info['savename'];
                $result['src'] = C('UPLOAD_PICTURE_ROOT') . '/' . $info['savepath'] . $info['savename'];
                $result['file_name'] = $info['savename'];
//                var_dump($result);
            }
            $this->_addAdminLog(4, '上传了文件名称为'.$info['savename'].'的文件。', '', 0);
            $this->ajaxReturn($result);
        }
    }

    /**
     * 删除图片
     */
    protected function _delFile(){
        $file = I('file', '', 'htmlspecialchars');

        $result = array( 'status' => 1, 'msg' => '删除完成');

        if($file != ''){
            $file = './' . __ROOT__ . $file;

            if (file_exists($file) == true) {
                @unlink($file);
            }
        }
        $this->ajaxReturn($result);
    }

	/**
     * 通用分页列表数据集获取方法
     *
     *  可以通过url参数传递where条件,例如:  index.html?name=asdfasdfasdfddds
     *  可以通过url空值排序字段和方式,例如: index.html?_field=id&_order=asc
     *  可以通过url参数r指定每页数据条数,例如: index.html?r=5
     *
     * @param sting|Model  $model   模型名或模型实例
     * @param array        $where   where查询条件(优先级: $where>$_REQUEST>模型设定)
     * @param array|string $order   排序条件,传入null时使用sql默认排序或模型属性(优先级最高);
     *                              请求参数中如果指定了_order和_field则据此排序(优先级第二);
     *                              否则使用$order参数(如果$order参数,且模型也没有设定过order,则取主键降序);
     *
     * @param array        $base    基本的查询条件
     * @param boolean      $field   单表模型用不到该参数,要用在多表join时为field()方法指定参数
     *
     * @return array|false
     * 返回数据集
     */
    protected function lists ($model,$where=array(),$order='',$base = array('status'=>array('egt',0)),$field=true){
        $options    =   array();
        $REQUEST    =   (array)I('request.');
        if(is_string($model)){
            $model  =   M($model);
        }

        $OPT        =   new \ReflectionProperty($model,'options');
        $OPT->setAccessible(true);

        $pk         =   $model->getPk();
        if($order===null){
            //order置空
        }else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
            $options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
        }elseif( $order==='' && empty($options['order']) && !empty($pk) ){
            $options['order'] = $pk.' desc';
        }elseif($order){
            $options['order'] = $order;
        }
        unset($REQUEST['_order'],$REQUEST['_field']);

        $options['where'] = array_filter(array_merge( (array)$base, /*$REQUEST,*/ (array)$where ),function($val){
            if($val===''||$val===null){
                return false;
            }else{
                return true;
            }
        });
        if( empty($options['where'])){
            unset($options['where']);
        }
        $options      =   array_merge( (array)$OPT->getValue($model), $options );
        $total        =   $model->where($options['where'])->count();

        if( isset($REQUEST['r']) ){
            $listRows = (int)$REQUEST['r'];
        }else{
            $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        }
        $page = new \Think\Page($total, $listRows, $REQUEST);
        if($total>$listRows){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $p =$page->show();
        $this->assign('_page', $p? $p: '');
        $this->assign('_total',$total);
        $options['limit'] = $page->firstRow.','.$page->listRows;

        $model->setProperty('options',$options);

        return $model->field($field)->select();
    }
    
    /**
     * 根据传入的用户数据权限类型获取该权限的值
     * @param string $accessType
     * @return int   $intAccessTypeValue
     */
//    protected function _getDataAccess($accessType){
//        if (empty($accessType)) {
//          return "false";
//        }
//
//        $where['member_id'] = UID; // 根据配置的常量获取登录的用户的id
//        $intAccessTypeValue = M('DataAccess')->where($where)->getfield($accessType); // 根据用户id和需要获取的用户数据权限的类型获取该用户数据权限的类型的值
//
//        return $intAccessTypeValue;
//    }
    /*杨yongjie  添加*/
    protected function _getDataAccess($accessType,$member){
        if (empty($accessType)) {
            return "false";
        }

        $where['member_id'] = $member ? $member : UID; // 根据配置的常量获取登录的用户的id
        $intAccessTypeValue = M('DataAccess')->where($where)->getfield($accessType); // 根据用户id和需要获取的用户数据权限的类型获取该用户数据权限的类型的值

        return $intAccessTypeValue;
    }
    /*杨yongjie  添加*/
    /**
     * 添加后台日志方法
     * @param int $log_type: 日志类型  0 新增  1 修改 2 删除   3 登录  4 上传
     * @param string $log_info: 日志内容
     * @param string $log_url: 操作模块名称
     * @param int $log_status: 日志状态  0 成功 1 失败
     */
    protected function _addAdminLog($log_type, $log_info, $log_url, $log_status){
        $adminLogModel = D('Admin/AdminLog');
        $adminLogData['log_type'] = $log_type;
        $adminLogData['log_info'] = $log_info;
        //获取操作模块
        if (!empty($log_url) && $log_url >= 0) {
            $menu_id =  $log_url;
        } else {
            $where['group'] = array('neq', '');
            $where['url'] = array('like', '%'.MODULE_NAME.'/'.CONTROLLER_NAME.'%');
            $menu_id =  M('Menu')->where($where)->getfield('id');
        }
        $adminLogData['log_url'] = $menu_id;
        $adminLogData['log_status'] = $log_status;
        $adminLogData['member_id'] = UID;
        $adminLogData['log_time'] = time();
        if($adminLogModel->create($adminLogData) !== false){
            $adminLogResult = $adminLogModel->add();
            if ($adminLogResult === false) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 根据用户的访问方法返回设置好的后台模块提示语
     * @return string 置好的后台模块提示语
     */
    protected function _getPromptLanguage() {
        $type = I('get.'); // 获取自定义的配置的属性
//        var_dump($type);
        if ( empty($type) ) {
            $path = MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
        } else {
            $basePath = MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
            foreach ($type as $key => $value) {
                if ($key != p) {
                    $addAttribute .= "/$key/$value";                    
                }
            }
            $path = $basePath.$addAttribute;            
        }
//        var_dump($path);
        $where['menu.url'] = array('EQ', $path);
        $promptLanguageContent = M('PromptLanguage')->alias('promptlanguage')->join('__MENU__ as menu ON promptlanguage.menu_id = menu.id')
            ->where($where)->getfield('promptlanguage.content');
        return nl2br($promptLanguageContent);        
    }

    // 获取登录用户的类型（0 商户 1 管理员）
    protected function _getMemberTypeByUID() {
        $type = M('Member')->where('id='.UID)->getfield('type');
        return $type;
    }
}
