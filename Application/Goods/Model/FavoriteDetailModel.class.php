<?php
namespace Goods\Model;
use Think\Model;

/**
 * Created by liuniukeji.com
 * 收藏夹模型
 * @author yuanyulin <755687023@qq.com>
*/
class FavoriteDetailModel extends Model{
    
    protected $insertFields = array('goods_id', 'style', 'size', 'material', 'colour', 'like_level', 'favorite_id');
    protected $selectFields = array('id', 'goods_id', 'style', 'size', 'material', 'colour', 'like_level', 'favorite_id', 'cart_sn','goods_count');
    
    protected $_validate = array(
        array('goods_id', 'require', '商品ID必须填写', self::MUST_VALIDATE, 'regex', 3),
        array('style', 'require', '商品样式必须填写', self::MUST_VALIDATE, 'regex', 3),
        array('size', 'require', '商品尺寸必须填写', self::MUST_VALIDATE, 'regex', 3),
        array('material', 'require', '商品材料必须填写', self::MUST_VALIDATE, 'regex', 3),
        array('colour', 'require', '商品颜色必须填写', self::MUST_VALIDATE, 'regex', 3),
        array('like_level', 'require', '商品收藏类型必须填写', self::MUST_VALIDATE, 'regex', 3),
        array('like_level', array(0,1,2), '收藏类型不符', self::MUST_VALIDATE, 'in', 3),
        array('favorite_id', 'require', '商品所属收藏夹id必须填写', self::MUST_VALIDATE, 'regex', 3),
    );
    
    protected function _before_insert(&$data, $option) {
        $data['add_time'] = time();
    }

    /**
     * 根据传入的分页显示收藏夹列表
     * @param string  $order     排序的字段
     * @return array  $list      返回的收藏夹列表
     */
    public function getFavoriteDetail($order='add_time desc, id desc'){
        $favoriteId = I('post.favorite_id', '0', 'intval');
        
        $where['favorite_id'] = array('EQ', $favoriteId);
        $where['status']      = array('EQ', 0); // 0表示正常的收藏夹商品

        $list = $this->field('id, goods_id, style, size, material, colour, like_level, add_time, goods_count')->where($where)->order($order)->select();
        return $list;   
    }
    
    //返回符合条件的记录
    public function getFavoriteDetailList($where, $field = null, $order = 'id desc') {
        if ($field == null) {
            $field = $this->selectFields;
        }

        $list = $this->field($field)->where($where)->order($order)->select();
        return $list;
    }
	
}
