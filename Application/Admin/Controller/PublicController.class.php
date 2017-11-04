<?php
namespace Admin\Controller;
/**
 *  后台首页控制器
 */
class PublicController extends \Think\Controller {

    /**
     * 后台用户登录显示
     */
    public function login(){
        // 123456的加密758e001c4fd2b221540ef0a36a133f02d93a5def7511da3d0f2d171d9c344e91
        if(is_login()){
            $this->redirect('Index/index');
        }else{
            /* 读取数据库中的配置 */
            $config	=	S('DB_CONFIG_DATA');
            if(!$config){
                $config = api('Config/lists');
                S('DB_CONFIG_DATA',$config);
            }
            C($config); //添加配置

            // 随机加密字符串
            $security_code = randNumber();
            session('security_code', $security_code);
            $this->security_code = $security_code;
            
            $this->display();
        }
        
    }

    /**
     * 后台用户登录
     */
    public function doLogin(){
        //$this->ajaxReturn(V(1, '登录成功'));
        $verify = I('verify', '');
        /* 检测验证码 TODO: */
       if(!check_verify($verify)){
            $this->ajaxReturn(V(0, '验证码输入错误'));
        }  

        $username = I('username', '');
        $where['user_name'] = array('eq', $username);
        $disabled = M('Member')->where($where)->getfield('disabled');
        unset($where);
        if ($disabled == 1) {
            $this->ajaxReturn(V(0, '您的账号已被禁用！'));
        }
        $password = I('password', '');
        // 获取security_code, 重新组织密码
        $pre_code = substr($password, 0, 2);
        $end_code = substr($password, -2);
        $password = substr($password, 2, -2);
        $security_code = $pre_code . $end_code;
        if ($security_code != session('security_code')) {
            $this->ajaxReturn(V(0, '非法操作, 您的IP已记录'));
        }

        $member = D('Common/Member');
        $data = $member->create(I('post.'), 4);
        if (!$data) {
            $this->ajaxReturn(V(0, $member->getError()));
        }
        $loginInfo = $member->login($username, $password, 'admin', session('security_code'));
        if( $loginInfo['status'] == 1 ){ //登录成功
            /* 存入session */
            $this->autoSession($loginInfo['data']);
            $this->_addAdminLog(3, '用户登录后台。', 0 , 0, $loginInfo['data']['id']);
            $this->ajaxReturn(V(1, '登录成功'));
        } else {
            $this->_addAdminLog(3, '用户登录后台失败，'.$loginInfo['info'], 0, 1, $loginInfo['data']['id']);
            $this->ajaxReturn(V(0, $loginInfo['info']));
        }
    }

    /**
     * 添加后台日志方法
     * @param int $log_type: 日志类型  0 新增  1 修改 2 删除   3 登录  4 上传
     * @param string $log_info: 日志内容
     * @param string $log_url: 操作模块名称
     * @param int $log_status: 日志状态  0 成功 1 失败
     */
    protected function _addAdminLog($log_type, $log_info, $log_url, $log_status, $member_id){
        $adminLogModel = D('Admin/AdminLog');
        $adminLogData['log_type'] = $log_type;
        $adminLogData['log_info'] = $log_info;
        //获取操作模块
        if ($log_url >= 0) {
            $menu_id =  $log_url;
        } else {
            $where['group'] = array('neq', '');
            $where['url'] = array('like', '%'.MODULE_NAME.'/'.CONTROLLER_NAME.'%');
            $menu_id =  M('Menu')->where($where)->getfield('id');
        }
        $adminLogData['log_url'] = $menu_id;
        $adminLogData['log_status'] = $log_status;
        $adminLogData['member_id'] = $member_id;
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
     * 图片剪切
     */
    public function uploadImgCut() {

        $src = I('get.src'); //上传的商品图片路径
        $item = I('get.item'); //商品多个图片时的标识
        $save_path = I('get.savePath'); //剪切后保存图片的路径

        $this->assign('src', $src);
        $this->assign('item', $item);
        $this->assign('save_path', $save_path);
        $this->display('img_cut');
    }

    /**
     * 地图图片剪切
     */
    public function mapuploadImgCut() {
        $src = I('get.src'); //上传的商品图片路径
        $item = I('get.item'); //商品多个图片时的标识
        $save_path = I('get.savePath'); //剪切后保存图片的路径

        $this->assign('src', $src);
        $this->assign('item', $item);
        $this->assign('save_path', $save_path);
        $this->display('mapimg_cut');
    }

    /* 退出登录 */
    public function logout(){
        session(null);
        $this->redirect('login');
    }

    public function verify(){
        $Verify = new \Think\Verify(array(
            'length' => 4,
            'useNoise' => FALSE,
            'imageH' =>40,
            'imageW' => 100,
            'fontSize'=>14
        ));
        $Verify->entry(1);
    }
    
    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoSession($user){
    	/* 记录登录SESSION和COOKIES */
    	$auth = array(
			'id'              => $user['id'],
			'uid'             => $user['id'],
            'type'            => $user['type'],
			'username'        => $user['user_name'],
    	   );
    	session('admin_auth', $auth);
    }


}
