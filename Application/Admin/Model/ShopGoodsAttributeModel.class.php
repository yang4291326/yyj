<?php
namespace Admin\Model;
use Think\Model;

/**
 * Created by liuniukeji.com
 * 商品属性模型
 * @author goryua <1661745274@qq.com>
*/

class ShopGoodsAttributeModel extends Model {
     
    protected $insertFields = array('attr_name','attr_mode','member_id','attr_type','attr_value','attr_sort','attr_status','cate_id');
	protected $updateFields = array('id','attr_name','attr_mode','attr_type','attr_value','attr_sort','attr_status','cate_id');
	protected $selectFields = array('id','attr_name','member_id','attr_type','attr_value','attr_sort','attr_status','cate_id'); 

    protected $_validate = array(
        array('attr_name', 'require', '属性名称不能为空', self::MUST_VALIDATE),
        array('attr_value', 'checkAttrValue', '当属性类型为枚举时，属性值不能为空', self::MUST_VALIDATE, 'callback', 3),
        array('attr_type', array(0, 1, 2, 3), '非法数据！', self::MUST_VALIDATE, 'in', 3),
        array('attr_sort', '0,1000', '属性排序范围在0--1000', 0, 'between', 3),
        array('status', array(0, 1), '非法数据，该属性值是否启用！', self::MUST_VALIDATE, 'in', 3),
    );

    /**
     * 获取属性分页方法
     * @param array $where 传入where条件
     * @param string $order 排序方式
     * @return array 搜索数据和分页数据
    */
    public function getAttrByPage($where, $field=null, $order='attr_sort asc, id asc') {
        if ($field == null) {
            $field = $this->selectFields;
        }
        $count = $this->where($where)->count('id');
        $page = get_page($count);
        
        $data = $this->field($field)->where($where)->limit($page['limit'])->order($order)->select();
        return array(
            'list' => $data,
            'page' => $page['page']
        );   
    }
    
    /**
     * 系统自定义属性个数
     */
    public function getSysAttrCount() {
        $where['attr_mode'] = array('eq', 0);
        return $this->where($where)->count();
    }

    /**
     * 商家自定义定义属性个数
     */
    public function getMemberAttrCount() {
        $where['member_id'] = array('eq', UID);
        $where['attr_mode'] = array('eq', 0);
        return $this->where($where)->count();
    }
    /*杨yongjie  添加*/
    /**
     * 商家自定义定义属性个数
     */
    public function getMemberAttrCounts() {
        $where['member_id'] = array('eq', UID);
        $where['attr_mode'] = array('eq', 1);//商家添加的自定义属性attr_mode值为1
        return $this->where($where)->count();
    }

    /*杨yongjie  添加*/

    /**
     * 判断属性类型为枚举时，属性值是否为空
     */
    protected function checkAttrValue($data) {
        $attr_type = I('post.attr_type', 0, 'intval');
        
        if ($attr_type == 2 && $data == "") {
            return false;
        }
        return true;
    }

    protected function _before_insert(&$data, $option){
        $data['member_id'] = UID;
    }


    

}
?>