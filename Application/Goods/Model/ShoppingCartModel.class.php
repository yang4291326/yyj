<?php
namespace Goods\Model;
use Think\Model;

/**
 * Created by liuniukeji.com
 * 商品确认
 * @author goryua <1661745274@qq.com>
*/
class ShoppingCartModel extends Model{
	
	protected $selectFields = array('id','collection_sn','add_time');
	protected $insertFields = array('collection_sn','remark','add_time','collection_id','member_id','status','price_total');

	protected $_validate = array(
        array('collection_sn', 'require', '确认列表编码错误', self::MUST_VALIDATE, 'regex', 3),
    );
    
	public function getShoppingCartList($where, $field=null, $order='id desc') {

		if ($field == null) {
			$field = $this->selectFields;
		}

		$list = $this->field($field)->where($where)->order($order)->select();

        return $list;	
	} 

	protected function _before_insert(&$data, $option){
        $data['member_id'] = UID;
        $data['add_time'] = NOW_TIME;
    }
    
    /**
	 * 保存拍照
	 * @param int $cart_id 拍照的商品确认列表id
	 * @param string $photo_path 拍照图片路径
	 * @return 
	 */
    public function saveCartPhoto($cart_id, $photo_path){
    	$where['id'] = array('eq', $cart_id);
    	$where['member_id'] = array('eq', UID);
		$data['cart_photo'] = $photo_path;
		$result = $this->where($where)->save($data);
		if ($result === false) {
			return V(0, '拍照上传失败, 未知原因');
		}
		return V(1, '拍照上传成功');
	}



}
