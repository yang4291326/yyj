<?php
namespace Admin\Model;
use Think\Model;

/**
 * 皮肤管理模型
 * @author yuanyulin <QQ: 755687023>
 */
class SkinModel extends Model{

    protected $insertFields = array('name', 'photo_path', 'file_path', 'sort','member_ids', 'disabled');
    protected $updateFields = array('id', 'name', 'photo_path', 'file_path', 'sort', 'member_ids', 'disabled');
    
    protected $_validate = array(    
        array('name', 'require', '皮肤名称必须填写',            self::MUST_VALIDATE, 'regex',  3),
        array('name', '1,24',    '皮肤名称长度在1-24个字符之间', self::MUST_VALIDATE, 'length', 3),
        
        array('photo_path', 'require', '图片路径必须填写', self::MUST_VALIDATE, 'regex', 3),
        
        array('file_path', 'require', '文件路径必须填写', self::MUST_VALIDATE, 'regex', 3),
        
        array('sort', 'number',   '排序必须是数字',          self::VALUE_VALIDATE, 'regex', 3),
        array('sort', '0, 10000', '排序字段不能超过10000！', self::VALUE_VALIDATE, 'length', 3),
    );
    
    protected function _before_insert(&$data,$option) {
        $data['add_time'] = time();
    }

    /**
     * @param sting $type 区分是否为登陆人还是显示全部
     * @param sting $order 排序字段
     * @return array 返回处理好的数据和分页
     */
    public function getSkinByPage($type = 0, $order = 'sort desc, id desc') { 
        $keywords = I('keywords', '', trim);
        if ($keywords) 
            $where['name'] = array('LIKE', "%$keywords%");
        if ($type == 1) {
            $id = UID;
            $map = "`member_ids` like '%,$id,%' or `member_ids` like '$id,%' or `member_ids` ='$id' or `member_ids` like '%,$id'";
        }
        
        $where['status'] = array('EQ', 0);
        $count = $this->where($map)->where($where)->count();
        
        $page = get_page($count);
        $data = $this->where($map)->where($where)->limit($page['limit'])->order($order)->select();
//        echo $this->_sql();

        return array('list' => $data, 'page' => $page);
    }
}
