<?php
namespace Goods\Model;
use Think\Model;

/**
 * Created by liuniukeji.com
 * 收藏夹模型
 * @author yuanyulin <755687023@qq.com>
*/
class FavoriteModel extends Model{

    protected $insertFields = array('remark','customer_name','customer_phone','customer_address','customer_deposit','customer_code');
  
    protected $_validate = array(
//        array('remark', 'require', '收藏夹内容不能为空！',        self::MUST_VALIDATE,  'regex', 3),
          array('remark', '1,200',   '收藏夹内容不能超过200个字符', self::VALUE_VALIDATE, 'length', 3),
//        array('customer_name','require','姓名必须填写'),
//        array('customer_phone','number','电话必须为数字'),
//        array('customer_address','require','地址必须填写'),
//        array('customer_deposit','number','押金'),
//        array('customer_code','require','随机编码'),
    );
    
    protected function _before_insert(&$data, $option) {
        $data['member_id'] = UID;
//        $data['member_id'] = 5;
        $data['add_time']  = time();
        $data['name']      = $this->makeFavSn();
    }

    /**
     * 根据传入的分页显示收藏夹列表
     * @param string  $order     排序的字段
     * @return array  $list      返回的收藏夹列表
     */
    public function getFavoriteByPage($where, $order='add_time desc, id desc'){
        $where['member_id'] = array('EQ', UID);
        $where['status']    = array('EQ', 0); // 0表示正常的收藏夹 
        //$list = $this->field('id, name, remark, add_time')->where($where)->order($order)->select();

        /*杨yongjie  添加*/
        $list = $this->field('id, name, remark, add_time,customer_name,customer_phone,customer_address,customer_deposit,customer_code,price_total')->where($where)->order($order)->select();
        //echo $this->getLastSql();die;
        /*杨yongjie  添加*/
        return $list;   
    }

    public function getFavoriteInfo($where, $field = null) {
        if ($field == null) {
            $field = $this->selectFields;
        }
        $info = $this->field($field)->where($where)->find();
        //echo $this->getLastSql();exit;
        return $info;
    }

    /**
     * 取出商户所有收藏商品id
     * @author goryua
    */
    public function getFavGoodsId($goods_id_array) {
        $where['fav.member_id'] = array('eq', UID);
        $where['fav.status'] = array('eq', 0);
        if (!empty($goods_id_array)) {
            $where['detail.goods_id'] = array('in', implode(',', $goods_id_array));
        }
        $list = $this->alias('fav')
            ->join('__FAVORITE_DETAIL__ detail ON fav.id = detail.favorite_id', 'left')
            ->field('detail.goods_id')
            ->where($where)
            ->select();
        foreach ($list as $v) {
            $array[] = $v['goods_id'];
        }
        return $array;
    }
	
    
    //生成收藏夹单号
    protected function makeFavSn() {
        $where['member_id'] = array('eq', UID);
        $where['status'] = array('eq', 0);
        $count = D('Favorite')->where($where)->count('id');
        //根据查询个数生成三位编号
        $num = str_pad(($count+1), 3, '0', STR_PAD_LEFT);
        //规则 月-日 时:分/三位编号
        $sn = date('m-d H:i').'/'.$num;
        return $sn;
    }

}
