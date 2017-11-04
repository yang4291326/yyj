<?php
namespace Admin\Controller;
/**
 * 升级信息控制器.
 * @author wangwujiang <1358140190@qq.com>
 */
class UpgradeController extends AdminCommonController{
    //升级信息详情管理列表
    public function index() {
        $UpgradeData = D('Upgrade')->search();
        //将添加信息的用户名显示到页面中
        $memberModel = D('Member');
        foreach ($UpgradeData['data'] as $key => $value){
            $UpgradeData['data'][$key]['user_name'] = $memberModel->getMemberNameById($value['update_id']);
        }
        if ($UpgradeData) {
            $this->assign('data',$UpgradeData['data']);
            $this->assign('page',$UpgradeData['page']);
        }
        $promptTips = $this->_getPromptLanguage(); //获取提示语
        $this->assign('prompt_tips', $promptTips);
        $this->display();
    }
    //升级信息管理的添加与编辑
    public function edit(){
        $id = I('get.id', 0, 'intval');
        if (IS_POST){
            $upgrade = D('Upgrade');
            M()->startTrans();  // 开启事务
            if ($upgrade->create() !== false) {
                if ($id > 0) {
                    $upgradeResult = $upgrade->where('id=' . $id)->save();
                } else {
                    $upgradeResult = $id = $upgrade->add();// 此处的$id表示该用户是新加的那么就将该用户id保存下来，用于后续的表中。
                }

            } else {
                $this->ajaxReturn(V(0, $upgrade->getError()));
            }
            //插入升级信息详情
            $userName = I('post.memberRole', '');
            $upgradeDetail = D('UpgradeDetail');
            $detailResult = $upgradeDetail->saveDetail($userName, $id);
            if ($detailResult !== true) {
                $this->ajaxReturn(V(0,  $detailResult));
            }
            if ($detailResult !== true && $upgradeResult !== true){
                M()->rollback(); // 事务回滚
                $this->ajaxReturn(V(0, '保存失败'));
            }else {
                M()->commit(); // 事务提交
                $this->ajaxReturn(V(1, '保存成功'));
            }

        } else {
            /* 获取数据 */
            $memberData = M('Member')->where('type = 0 and status = 0')->field('id, user_name')->select();
            $info = M('Upgrade')->field(true)->find($id);
            //设置升级用户选中
            $memberId =  M('UpgradeDetail')->where(array('upgrade_id' => $id))->getField('member_id',true);
            $memberId = json_encode($memberId);
            $this->assign('userName', $memberData);
            $this->assign('info', $info);
            $this->assign('member_ids', $memberId);
            $this->display();
        }
    }
    //显示升级用户升级状态
    public function member(){
        $upgradeId = I('get.upgrade_id', 0, 'intval');
        $name = I('get.user_name', '', 'trim');
        if ($name) {
            $where['member.user_name'] = array('like', '%'.$name.'%');
        }
        $where['detail.upgrade_id'] = array('eq', $upgradeId);

        $detailList = D('UpgradeDetail')->getUpgradeDetailList($where,'detail.*,member.user_name');
        //设置分页变量
        $this->assign('list', $detailList['list']);
        $this->assign('page', $detailList['page']);
        $this->assign('upgrade_id', $upgradeId);
        $this->display();
    }
    // 物理删除
    public function del(){
        $this->_del('Upgrade');  //调用父类的方法
    }

    // 删除附件
    public function delFile(){
        $this->_delFile();  //调用父类的方法
    }

    // 上传附件
    public function uploadField(){
        $this->_uploadField();  //调用父类的方法
    }
}