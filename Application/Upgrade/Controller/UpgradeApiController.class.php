<?php
namespace Upgrade\Controller;
/**
 * 升级信息前端控制器.
 * @author wangwujiang <1358140190@qq.com>
 */
class UpgradeApiController extends \Common\Controller\CommonApiController{
    // 升级信息列表
    public function upgradeList(){
        //判断用户是否有升级权限
        $where['member_id'] = array('eq', UID );
        $upgradeInfo = D('Upgrade/UpgradeDetail')->where($where)->order('id desc')->find();

        if (empty($upgradeInfo)) {
            $data['upgrade'] = 0;
            $data['0'] = array("mode" => "", "file_path" => "", "version" => "");
        }else{
            unset($where);
            $where['id'] = array('eq', $upgradeInfo['upgrade_id']);
            $field = 'mode, file_path, version';
            $data = M('Upgrade')->where($where)->field($field)->select();
            $data['upgrade'] = 1;

        }
        $this->apiReturn(1, '升级信息列表', $data = array('data'=>$data['0'], 'upgrade'=>$data['upgrade'] ));
    }
    //回传用户升级时间、升级状态
    public function upgradeUpdate(){
        $upgradeStatus = I('upgradeStatus', 0, 'intval');
        $mode = D('UpgradeDetail');
        $data['status'] = $upgradeStatus;
        $data['upgrade_time'] = time();
        if ($mode->create($data) !== false){
            $info = $mode->where('member_id =' . UID )->save();
            if ($info !== false && $upgradeStatus != 0){
                if ($upgradeStatus == 1){
                    $this->apiReturn(1, '升级失败');
                }else{
                    $this->apiReturn(1, '升级成功');
                }
            }else{
                $this->apiReturn(0, '未升级');
            }
        } else {
            $this->ajaxReturn(V(0, $mode->getError()));
        }
    }
}