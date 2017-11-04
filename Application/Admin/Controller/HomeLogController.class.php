<?php
namespace Admin\Controller;

/**
 * 前台日志管理控制器
 * @author liuyang <594353482@qq.com>
 */
class HomeLogController extends AdminCommonController {

    //前台日志列表
    public function homelog() {
        $log_type = I('log_type', 0, 'intval');
        if ($log_type > 0) {
            $where['homelog.log_type'] = array('eq', $log_type);
        }
        $terminal_type = I('terminal_type', '');
        if ($terminal_type != null) {
            $where['homelog.terminal_type'] = array('eq', $terminal_type);
        }
        $member_name = I('member_name', '', 'trim');
        if (!empty($member_name)) {
            $where['member.user_name'] = array('like', '%'.$member_name.'%');
        }
        /*杨yongjie  添加*/
        $where['member_id']=UID;//根据登录的用户展示对应的操作日志
        //如果是超级管理员,可以查看所有操作日志
        if($where['member_id']==1){
            unset($where['member_id']);
        }
        /*杨yongjie  添加*/
        $data = D('HomeLog')->getHomeLogByPage($where);
        //获取关联的模块
        $homeLogModularModel = D('HomeLogModular');
        foreach ($data['list'] as $key => $value) {
            $data['list'][$key]['log_type_name']          = $homeLogModularModel->getHomeLogModularName($value['log_type']);
            $data['list'][$key]['data_table_name']        = $homeLogModularModel->getHomeLogModularDataTable($value['log_type']);
            $data['list'][$key]['data_table_record_name'] = $homeLogModularModel->getHomeLogModularDataTableName($value['log_type'], $value['record_id']);
        }
        // 生成树型列表
        $homeLogModularTree = $homeLogModularModel->getHomeLogModularTree($id);

        $this->assign('homeLogModularTree', $homeLogModularTree);
        //设置分页变量
       	$this->assign('list', $data['list']);
       	$this->assign('page', $data['page']);
        $prompt_tips = $this->_getPromptLanguage(); //获取提示语
        $this->assign('prompt_tips', $prompt_tips);
        $this->display('homelog');
    }

    public function exportExcel() {
        $log_type = I('log_type', 0, 'intval');
        if ($log_type > 0) {
            $where['homelog.log_type'] = array('eq', $log_type);
        }
        $terminal_type = I('terminal_type', '');
        if ($terminal_type != null) {
            $where['homelog.terminal_type'] = array('eq', $terminal_type);
        }
        $member_name = I('member_name', '', 'trim');
        if (!empty($member_name)) {
            $where['member.user_name'] = array('like', '%'.$member_name.'%');
        }
        $data = D('HomeLog')->getHomeLogNoPage($where);
        if (empty($data)) {
           $data = [];
        }
        foreach ($data as $key => $value) {
            if (!empty($value['start_time']) && !empty($value['end_time'])) {
                $data[$key]['last_time'] = getTimeContent($value['end_time'] - $value['start_time']);
            }
            $data[$key]['start_time'] = time_format($value['start_time']);
            $data[$key]['end_time'] = time_format($value['end_time']);
            $data[$key]['terminal_type'] = show_homelog_terminal_type($value['terminal_type']);
        }
        $title_array = array('日志类型', '日志内容', '操作时间', '操作时间', '持续时间', '操作人', '终端类型');
        array_unshift($data, $title_array);
        $count = count($data);
        create_xls($data, '家居建材VR数字化营销系统日志表', '家居建材VR数字化营销系统日志表', '家居建材VR数字化营销系统日志表', array('A','B','C','D','E','F', 'G'), $count, 0);
    }

    // 放入回收站
    public function del(){
        $this->_del('HomeLog');  //调用父类的方法
    }
    
}