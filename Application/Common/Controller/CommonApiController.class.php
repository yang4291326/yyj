<?php
namespace Common\Controller;
/**
 * 用户登录后, 需要继承的基类
 * create by zhaojiping <QQ: 17620286>
 */
class CommonApiController extends CommonController {

    protected function _initialize(){
        $code = $_POST['code'];
        if ($code == '') {
            //$this->ajaxReturn(V('0', '非法访问'));
        }
        /* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config = api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置
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
            if ($key == 'p') {
                $_GET['p'] = $value;
            }
        }
        $token = I('post.token', '');
        // 判断token值是否正确并返回用户信息
        $uid = $this->checkTokenAndGetUid($token);
        if ($uid > 0) {
        	define('UID', $uid);
        } else {
        	$this->ajaxReturn(V('0', '用户不合法, 无权访问'));
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

    private function checkTokenAndGetUid($token){
    	$where['token'] = $token;
    	$id = M('MemberMachineCode')->where($where)->getField('member_id');
    	return $id;
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
