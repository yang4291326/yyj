<?php
namespace Admin\Model;
use Think\Model;

/**
 * 收藏夹明细管理模型
 * @author yuanyulin <QQ: 755687023>
 */
class FavoriteDetailModel extends Model{
    
    /**
     * 对后台收藏夹列表进行分页显示
     * @param  int  $id     获取到的收藏夹的id
     * @return array $list  分页完成的数据
     */
    public function getFavoriteDetailDataByPage($id){
        
        $where['favoritedetail.favorite_id']           = array('EQ', $id); // 获取到的收藏夹的id
        $where['favoritedetail.status']                = array('EQ', 0);   // （0：正常 1：删除）
        $where['shopgoodsattributevalue.attribute_id'] = array('EQ', 1);   // 1 表示获取商品扩展属性中的商品名称
        
        $keywords = I('keywords', '', trim);
        if ($keywords) 
            $where['shopgoodsattributevalue.attr_value'] = array('LIKE', "%$keywords%");

//        $count = $this->alias('favoritedetail')->join('__SHOP_GOODS_ATTRIBUTE_VALUE__ as shopgoodsattributevalue ON favoritedetail.goods_id = shopgoodsattributevalue.goods_id')
//                ->where($where)->field('favoritedetail.id, favoritedetail.style, favoritedetail.size, favoritedetail.material, favoritedetail.colour, favoritedetail.add_time, favoritedetail.like_level, favoritedetail.cart_sn, shopgoodsattributevalue.attr_value')
//                ->count();
//        $page = get_page($count);
//
//        $list = $this->alias('favoritedetail')->join('__SHOP_GOODS_ATTRIBUTE_VALUE__ as shopgoodsattributevalue ON favoritedetail.goods_id = shopgoodsattributevalue.goods_id')
//                ->where($where)->field('favoritedetail.id, favoritedetail.goods_id, favoritedetail.style, favoritedetail.size, favoritedetail.material, favoritedetail.colour, favoritedetail.add_time, favoritedetail.like_level, favoritedetail.cart_sn, shopgoodsattributevalue.attr_value')
//                ->limit($page['limit'])
//                ->order('favoritedetail.id desc')
//                ->select();

        /*杨yongjie  修改  添加商品总数量字段*/

        $count = $this->alias('favoritedetail')->join('__SHOP_GOODS_ATTRIBUTE_VALUE__ as shopgoodsattributevalue ON favoritedetail.goods_id = shopgoodsattributevalue.goods_id')
            ->where($where)->field('favoritedetail.id, favoritedetail.style, favoritedetail.size, favoritedetail.material, favoritedetail.colour, favoritedetail.add_time, favoritedetail.like_level, favoritedetail.cart_sn, favoritedetail.goods_count,shopgoodsattributevalue.attr_value')
            ->count();
        $page = get_page($count);

        $list = $this->alias('favoritedetail')->join('__SHOP_GOODS_ATTRIBUTE_VALUE__ as shopgoodsattributevalue ON favoritedetail.goods_id = shopgoodsattributevalue.goods_id')
            ->where($where)->field('favoritedetail.id, favoritedetail.goods_id, favoritedetail.style, favoritedetail.size, favoritedetail.material, favoritedetail.colour, favoritedetail.add_time, favoritedetail.like_level, favoritedetail.cart_sn, favoritedetail.goods_count,shopgoodsattributevalue.attr_value')
            ->limit($page['limit'])
            ->order('favoritedetail.id desc')
            ->select();

        /*杨yongjie  修改*/
        $ShopGoodsAttributeValueModel = D('ShopGoodsAttributeValue');//源代码

        /*杨yongjie  添加*/
        $ShopGoodsColor=D('ShopGoodsColor');
        /*杨yongjie  添加*/
        foreach ($list as $key => $value) { // 获取商品的颜色
            //$list[$key]['colour']   = $ShopGoodsAttributeValueModel->getBasicAttrValue($value['goods_id'], $value['colour']);//源代码

            /*杨yongjie  添加*/
            $list[$key]['colour']=$ShopGoodsColor->getColor($value['goods_id'], $value['colour']);

            //获取商品现价
            $list[$key]['price']   = $ShopGoodsAttributeValueModel->getBasicAttrValue($value['goods_id'], 4);
            /*杨yongjie  添加*/
        }
        return $list;
        
    }

}