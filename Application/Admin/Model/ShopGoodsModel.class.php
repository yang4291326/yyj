<?php
namespace Admin\Model;
use Think\Model;

/**
 * Created by liuniukeji.com
 * 商品模型
 * @author goryua <1661745274@qq.com>
*/

class ShopGoodsModel extends Model {
     
    protected $insertFields = array('goods_category_id','goods_img','goods_remark','status','member_id');
	protected $updateFields = array('id','goods_category_id','goods_img','goods_remark','status');
	protected $selectFields = array('goods.id','goods_category_id','goods_img','goods_remark','goods.status','member_id'); 

    protected $_validate = array(
        array('goods_category_id', 'checkCateLevel', '商品分类只能选择第4级', self::MUST_VALIDATE, 'callback', 3),
        array('goods_img', 'require', '商品主图必须选择', self::MUST_VALIDATE),
        //array('goods_remark', 'require', '文本描述不能为空', self::MUST_VALIDATE),
    );

    /**
     * 获取后台日志分页方法
     * @param array $where 传入where条件
     * @param string $order 排序方式
     * @return array 搜索数据和分页数据
    */
    public function getGoodsList($where, $field = null, $order = 'add_time desc') {
        if ($field == null) {
            $field = $this->selectFields;
        }

        $count = $this->alias('goods')
            ->join('__SHOP_GOODS_ATTRIBUTE_VALUE__ value ON goods.id = value.goods_id', 'left')
            ->where($where)
            ->count('goods.id');
        $page = get_page($count);
        
        $data = $this->alias('goods')
            ->join('__SHOP_GOODS_TYPE__ type ON goods.goods_category_id = type.id', 'left')
            ->join('__SHOP_GOODS_ATTRIBUTE_VALUE__ value ON goods.id = value.goods_id', 'left')
            ->join('__MEMBER__ member ON goods.member_id = member.id', 'left')
            ->field($field)
            ->where($where)
            ->limit($page['limit'])
            ->order($order)
            ->select();

        return array(
            'list' => $data,
            'page' => $page['page']
        );   
    }

    /**
     * 判断商品分类是否选择为第5级
     */
    protected function checkCateLevel($data) {
        if ($data != 0){
            $level = D('ShopGoodsType')->where('id='. $data)->getField('level');
            if ($level == 4){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    protected function _before_insert(&$data, $option){
        $data['member_id'] = UID;
        $data['add_time'] = NOW_TIME;
    }

    protected function _before_update(&$data, $option){
        $data['member_id'] = UID;
        $data['add_time'] = NOW_TIME;
    }
    /**
     * create by yuanyulin
     * 获取分页的商品数据
     * @return array 分页好的数据
     */
    public function getShopGoodsData(){
        $keywords = I('keywords', '', trim);
        if ($keywords) 
            $where['value.attr_value'] = array('LIKE', "%$keywords%");
        $where['value.attribute_id'] = array('EQ', 1); // 1表示为取出该商品的名称
        $where['member_id'] = array('EQ', UID);
        $count = $this->alias('shopGoods')->join('__SHOP_GOODS_ATTRIBUTE_VALUE__ AS value ON shopGoods.id = value.goods_id')
                ->where($where)
                ->count();
        $page = get_page($count);
        
        $data = $this->alias('shopGoods')->join('__SHOP_GOODS_ATTRIBUTE_VALUE__ AS value ON shopGoods.id = value.goods_id')
                ->where('shopGoods.id = value.goods_id')->where($where)
                ->field('shopGoods.id, shopGoods.goods_category_id, shopGoods.goods_img, value.attr_value')
                ->limit($page['limit'])
                ->select();

        return array(
            'list' => $data,
            'page' => $page['page']
        );           
    }
}
?>