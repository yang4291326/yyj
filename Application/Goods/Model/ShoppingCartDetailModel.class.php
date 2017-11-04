<?php
namespace Goods\Model;
use Think\Model;

/**
 * Created by liuniukeji.com
 * 商品确认详情
 * @author goryua <1661745274@qq.com>
*/
class ShoppingCartDetailModel extends Model{
	
	protected $selectFields = array('id','goods_id','style','size','material','color','like_level','add_time','price','goods_count');
	
	/**
     * 获取确认详情列表
     */
	public function getShoppingCartDetailList($where, $field = null, $order = 'id desc') {
		
		if ($field == null) {
			$field = $this->selectFields;
		}
		$count = M('shopping_cart_detail')->where($where)->count('id');
        $page = get_page($count);
		$data = M('shopping_cart_detail')->field($field)->where($where)->limit($page['limit'])->order($order)->select();
        return array(
            'list' => $data,
            'page' => $page['page']
        );   
	}
    
	/**
     * 添加确认列表详情
     * @param  int $id 确认列表id
     * @param  string $cartSn 确认列表编号
     * @param  array $data 收藏详情数据
     * @return  bool
     */
	public function addShoppingCartDetail($id, $cartSn, $data) {
		if (empty($data) && !is_array($data)) {
			return false;
		}
		foreach ($data as $key => $value) {
			$insert[$key]['cart_id'] = $id;
			$insert[$key]['goods_id'] = $value['goods_id'];
			$insert[$key]['style'] = $value['style'];
			$insert[$key]['size'] = $value['size'];
			$insert[$key]['material'] = $value['material'];
			$insert[$key]['color'] = $value['colour'];
			$insert[$key]['like_level'] = $value['like_level'];
            $insert[$key]['price']=D('Goods/ShopGoods')->getBasicAttrValue($value['goods_id'], 4);
            $insert[$key]['goods_count']=$value['goods_count'];
			$insert[$key]['add_time'] = time();

			//更新收藏列表详情cart_sn追加确认列表编号
			$up_data['cart_sn'] = $cartSn.','.$value['cart_sn'];
			M('favorite_detail')->where('id='.$value['id'])->save($up_data);

		}
		$this->addAll($insert);
		return true;
	}
    
}
