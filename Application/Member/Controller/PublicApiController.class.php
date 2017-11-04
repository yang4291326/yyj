<?php
namespace Member\Controller;

/**
 * @author 刘阳 <QQ: 17620286>
 */
class PublicApiController extends \Think\Controller {

    protected function _initialize(){

        /* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config = api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置

    }

    protected function apiReturn($status, $message='', $data=''){
        if ($status != 0 && $status != 1) {
            exit('参数调用错误 status');
        }

        if ($data != '' && C('APP_DATA_ENCODE') === true) {
            $data = json_encode($data); // 数组转为json字符串
            $aes = new \Common\Tools\Aes();
            $data = $aes->aes128cbcEncrypt($data); // 加密
        }

        if (is_null($data)) $data='';

        $this->ajaxReturn(V($status, $message, $data));
    }

    private function _decode(){
        $code = $_POST['code'];

        if ($code == '') {
            $this->ajaxReturn(V('0', '非法访问'));
        }

        if (C('APP_DATA_ENCODE') === true) {
            // 解密
            $aes = new \Common\Tools\Aes();
            $code = $aes->aes128cbcHexDecrypt($code);

            if ($code == '') {
                $this->ajaxReturn(V('0', '非法访问!'));
            }
        }

        $params = json_decode($code, true);

        // 重新赋值
        $_POST = null;
        foreach ($params as $key => $value) {
            // $_GET[$key] = $value;
            $_POST[$key] = $value;
        }
    }


    /**
     * 用户登录
     */
    public function doLogin(){ 
//        var_dump($_POST);die;
        $this->_decode();
        $machine_code = I('post.machine_code', '');
        
        if ($machine_code == '') {
            $this->apiReturn(0, '参数错误, 请传递机器码!');
        }
        
        $where['machine_code'] = array('EQ', $machine_code);
        $memberId = M('MemberMachineCode')->where($where)->getfield('member_id');
        unset($where);
        if (!$memberId){
            $this->apiReturn(0, '登陆失败!');            
        }
        
        $memberMachineCodeNum = M('DataAccess')->where('member_id='. $memberId)->getfield('machine_code_num'); // 获取该用户可以用的平板的个数
        $memberMachineCodeList = M('MemberMachineCode')->where('member_id='. $memberId)->limit($memberMachineCodeNum)->getfield('machine_code',true);
        if (!in_array($machine_code, $memberMachineCodeList)) {
            $this->apiReturn(0, '请联系管理员!');
        }
        
        $loginInfo =  M('Member')->field('id, type, user_name')->where('id='.$memberId)->find(); // 获取用户的类型和用户名

        $loginInfo['machine_code'] = $machine_code;                             // 获取传入的机器码
        $loginInfo['token']        = $this->_createTokenAndSave($loginInfo); // 重新生成token
        $loginInfo['bannerList']   = $this->_bannerList($memberId); // 返回Banner
        if ($loginInfo['bannerList'] == null) {
            $loginInfo['bannerList'] = [];
        }
        // 返回nav
        $loginInfo['navList'] = $this->_navList();
        if ($loginInfo['navList'] == null) {
            $loginInfo['navList'] = [];                  
        }                    
        //插入登录日志
        $memberId = $loginInfo['id'];
        //$logType = 3;//3对应的类型是首页
        /*杨yongjie  修改*/
        $logType = 2; // 2对应的类型是用户登录
        /*杨yongjie  修改*/
        $logInfo = $loginInfo['user_name'].'登录';
        $this->homeLogApi($memberId, $logType, $logInfo);
        $this->apiReturn(1, '登陆成功', $loginInfo, false);
    }

    /**
     * 生成token值, 并保存到数据库
     * @param array userInfo 用户信息
     * @return string token值
     */
    private function _createTokenAndSave($userInfo){
        $token = randNumber(18); // 18位纯数字
        $where['member_id'] = $userInfo['id'];
        $where['machine_code'] = $userInfo['machine_code'];
        $data['token'] = $token;
        M('MemberMachineCode')->where($where)->data($data)->save();
        return $token;
    }

    // 返回app首页轮播图数据
    private function _bannerList($memberId){
        $count = M('DataAccess')->where(array('member_id'=>$memberId))->getfield('carousel_num');
        $list = array();
        $list = M('Banner')
                    ->where(array('type' => 1, 'status' => 0,'member_id'=>$memberId ))
                    ->field('id, img, url, open_type, goods_id')
                    ->limit($count)
                    ->order('sort asc')
                    ->select();
        foreach ($list as $key => $value) {
            //$list[$key]['img'] = thumb($value['img'], 1200, 600);
        }
        return $list;
    }
    // 返回app导航栏数据
    private function _navList(){
        $list = array();
        $list = M('Nav')
                    ->where(array('status' => 0, 'disabled' => 0))
                    ->field('id, title, img')
                    ->order('sort asc')
                    ->select();
        return $list;
    }

    // 返回app启动页和登录背景图片
    public function startPicture(){
        $where['status'] = 0;
        $where['is_default'] = 0;
        $banner = M('Banner');
        // 启动页
        $startPicture = $banner->where($where)->where(array('type' => 0))->getField('img');

        // 登录背景图
        $loginBgPicture = $banner->where($where)->where(array('type' => 2))->getField('img');
        $data = array(
                'startPicture' => $startPicture,
                'loginBgPicture' => $loginBgPicture
            );
        if ($data !=true){
            $data = array(
                'startPicture' => '',
                'loginBgPicture' =>''
            );
        }
        $this->apiReturn(1, '返回信息', $data);
    }
    
    
        /**
     * 前台日志分类调用接口
     * @param type $memberId     人员id
     * @param type $logType      日志类型
     * @param type $logInfo      日志内容
     * @param type $terminalType 终端类型  0 平板  1 手机
     * @param type $startTime    开始时间
     * @param type $endTime      结束时间
     * @param type $recordId     对应的表数据id（该字段只用于商品）
     */
    public function homeLogApi($memberId, $logType, $logInfo, $terminalType = 0, $startTime, $endTime = '', $recordId = 0) {
        $startTime == '' && $startTime = time(); // 如果没有传入开始时间默认是自动取出
        $data['member_id']     = $memberId;      // 人员id
        $data['log_type']      = $logType;       // 日志类型
        $data['log_info']      = $logInfo;       // 日志内容
        $data['terminal_type'] = $terminalType;  // 终端类型  0 平板  1 手机
        $data['start_time']    = $startTime;     // 开始时间
        $data['end_time']      = $endTime;       // 结束时间
        $data['record_id']     = $recordId;      // 对应的表数据id（该字段用于商品）
        M('HomeLog')->add($data);
    }
}
