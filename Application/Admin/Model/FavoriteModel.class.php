<?php
namespace Admin\Model;
use Think\Model;
/**
 * 收藏夹管理模型
 * @author yuanyulin <QQ: 755687023>
 */
class FavoriteModel extends Model{

    /**
     * 对后台收藏夹列表进行分页显示
     * @param  type $order  要排序的字段
     * @return array $list  分页完成的数据
     */
    public function getFavoriteDataByPage( $order = 'id desc' ){
        $where['status'] = array('EQ', 0); // （0：正常 1：删除）
        
        $userType = M('Member')->where('id='.UID)->getfield('type'); // 根据登陆用户的id获取该用户的类型（0：表示商户 1：表示管理员）
        if ($userType == 0) // 如果登陆的用户是商户就只显示它自己的
            $where['member_id'] = array('EQ', UID);
        
        $keywords = I('keywords', '', trim);
        if ($keywords) 
            $where['name'] = array('LIKE', "%$keywords%");
        
        $count = $this->where($where)->count();
        $page = get_page($count);
        
        $list = $this->field(true)->where($where)->limit($page['limit'])->order($order)->select();
        
        $MemberModel = D('Member');// getMemberNameByIdgetFavoriteDataByPage
        foreach ($list as $key => $value) {
            $list[$key]['member_name'] = $MemberModel->getMemberNameById($value['member_id']);
        }
        return array('list' => $list, 'page' => $page, 'count' => $count);        
    }
    protected function _before_insert(&$data,$option) {
        $data['add_time'] = time();
    }
    protected function _before_update(&$data,$option) {
        $data['add_time'] = time();
    }
}