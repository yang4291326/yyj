<?php
namespace Goods\Controller;
header("Content-type:text/html;charset=utf-8");
/**
 * Created by liuniukeji.com
 * 商品确认接口
 * @author goryua <1661745274@qq.com>
*/
class GoodsCartApiController extends \Common\Controller\CommonApiController {
    
    /**
     * 获取商品确认列表
     */
    public function shoppingCartList(){
        
        $name = I('name', '', 'trim'); //app传参确认列表名称
        if ($name) {
            $this->homeLogApi(UID,20,'商品确认页面搜索--'.$name,0,time(),'','');
            $where['collection_sn'] = array('like', '%'.$name.'%');
        }
        $where['member_id'] = array('eq', UID);
        $where['status'] = array('eq', 0);

        $cart_list = D('ShoppingCart')->getShoppingCartList($where);

        /*杨yongjie  添加*/
        foreach($cart_list as $k => $v) {
                $cart_lists[$k]=D('Goods/Favorite')->query("select id,customer_name,customer_phone,customer_address,customer_deposit,customer_code,price_total FROM ln_favorite where id=(select collection_id FROM ln_shopping_cart where id=" . $cart_list[$k]['id'] . ') and status=0');
        }
        foreach($cart_list as $k =>$v){
            $new_cart[$k]['id']=$v['id'];
            $new_cart[$k]['collection_sn']=$v['collection_sn'];
            $new_cart[$k]['add_time']=$v['add_time'];
            $new_cart[$k]['customer_name']=$cart_lists[$k][0]['customer_name'];
            $new_cart[$k]['customer_phone']=$cart_lists[$k][0]['customer_phone'];
            $new_cart[$k]['customer_address']=$cart_lists[$k][0]['customer_address'];
            $new_cart[$k]['customer_deposit']=$cart_lists[$k][0]['customer_deposit'];
            $new_cart[$k]['customer_code']=$cart_lists[$k][0]['customer_code'];
            $new_cart[$k]['price_total']=$cart_lists[$k][0]['price_total'];
            $new_cart[$k]['balanceDue']=$cart_lists[$k][0]['price_total']-$cart_lists[$k][0]['customer_deposit'];
        }
//        echo '<pre>';
//        var_dump($new_cart);die;

        /*杨yongjie  添加*/

        $this->apiReturn(1, '商品确认列表', $new_cart);
    }
    /*杨yongjie  添加*/
    /*
     * 商品确认通过客户名搜索
     */
     public function searchshoppingCart(){
          $customer_name=I('post.customer_name','','trim');
          if($customer_name){
              $this->homeLogApi(UID,20,'商品确认页面搜索--'.$customer_name,0,time(),'','');
          }
          $code['customer_name']=array('like','%'.$customer_name.'%');
          $code['member_id']=UID;
          //$code['status']=0;
         //获取所有的客户相关信息
          $list=M("Favorite")->field('id,customer_name,customer_phone,customer_address,customer_deposit,customer_code,price_total')->where($code)->select();
         //var_dump($list);die;
         //通过收藏夹id找到对应的确认表id
          foreach($list as $k => $v){
              $where['status']=array('eq',0);
              $where['collection_id']=array('eq',$v['id']);
              $result=M('ShoppingCart')->field('id,collection_sn,add_time,price_total')->where($where)->select();
              foreach($result as $x => $y){
                  $data[$k][$x]['id']=$y['id'];
                  $data[$k][$x]['collection_sn']=$y['collection_sn'];
                  $data[$k][$x]['add_time']=$y['add_time'];
                  $data[$k][$x]['customer_name']=$v['customer_name'];
                  $data[$k][$x]['customer_phone']=$v['customer_phone'];
                  $data[$k][$x]['customer_address']=$v['customer_address'];
                  $data[$k][$x]['customer_deposit']=$v['customer_deposit'];
                  $data[$k][$x]['customer_code']=$v['customer_code'];
                  $data[$k][$x]['price_total']=$y['price_total'];//总价为确认表的总价
                  $data[$k][$x]['balanceDue']=$y['price_total']-$v['customer_deposit'];//确认表里的总价-收藏表的押金
                  //$data[$k][$x]['price_total']=$v['price_total'];
                  //$data[$k][$x]['balanceDue']=$v['price_total']-$v['customer_deposit'];
              }
          }
          //重新组装数组
          foreach($data as $k=>$v){
              foreach($v as $x => $y){
                  $datas[]=$y;
              }
          }
          //var_dump($datas);die;
          $this->apiReturn(1, '商品搜索确认列表', $datas);
     }
     /*杨yongjie  添加*/
     /*
      * 获取商品确认明细列表
      */
    public function shoppingCartDetailList(){
        $cart_id = I('cart_id', 0, 'intval'); //app传参 确认列表id
        $where['cart_id'] = array('eq', $cart_id);
        $where['status'] = array('eq', 0);
        $detail_list = D('ShoppingCartDetail')->getShoppingCartDetailList($where);
        
        if (empty($detail_list['list']) && !is_array($detail_list['list'])) {
            $this->apiReturn(0, '商品确认明细列表为空');
        }
        foreach ($detail_list['list'] as $list) {
            $goods_id[] = $list['goods_id'];
            $color_id[] = $list['color'];
        }

        //根据颜色id取出符合条件颜色列表
        unset($where);
        $where['id'] = array('in', implode(',', $color_id));
        $color_list = D('ShopGoods')->getColorList($where);

        //根据商品id取出符合条件商品属性值
        unset($where);
        $where['value.goods_id'] = array('in', $goods_id);
        $where['attr.id'] = array('in', '1,2');
        $attr_list = D('ShopGoodsAttribute')->getGoodsAttrList($where);
        //获取商品主图
        $goods_list = M('shop_goods')->where(array('id'=>array('in', $goods_id)))->getField('id,goods_img');

        foreach ($detail_list['list'] as $key => $v) {
            $data[$key]['id'] = $v['id'];
            $data[$key]['goods_id'] = $v['goods_id'];
            $data[$key]['goods_name'] = $attr_list[$v['goods_id']][1]['attr_value'];
            $data[$key]['goods_code'] = $attr_list[$v['goods_id']][2]['attr_value'];
            //$data[$key]['present_price'] = D('Goods/ShopGoods')->getBasicAttrValue($v['goods_id'], 4);
            /*杨yongjie  添加*/
            $data[$key]['present_price'] = $v['price'];//单价
            $data[$key]['goods_count'] = $v['goods_count'];//数量
            /*杨yongjie  添加*/
            $data[$key]['goods_img'] = $goods_list[$v['goods_id']];
            $data[$key]['style'] = $v['style'];
            $data[$key]['size'] = $v['size'];
            $data[$key]['material'] = $v['material'];
            $data[$key]['color_id'] = $color_list[$v['color']]['id'];
            $data[$key]['color_name'] = $color_list[$v['color']]['color'];
            $data[$key]['color_path'] = $color_list[$v['color']]['default_color_pic'];
            $data[$key]['size'] = $v['size'];
            $data[$key]['like_level'] = $v['like_level'];
            $data[$key]['add_time'] = date("Y m d H:i:s",$v['add_time']);

            /*杨yongjie  添加*/
            //获取goods_count数量
//            $goods_count=M("FavoriteDetail")->query("select goods_count from ln_favorite_detail WHERE favorite_id=(select collection_id from ln_shopping_cart WHERE id=(select cart_id FROM ln_shopping_cart_detail WHERE id=".$v['id']."))AND goods_id=".$v['goods_id']);
//            $data[$key]['goods_count']=$goods_count[0]['goods_count'];
            /*杨yongjie  添加*/
        }
        //var_dump($data);die;
        $this->apiReturn(1, '商品确认明细列表', $data);
    }

    /**
     * 商品确认拍照
     */
    public function shoppingCartPhoto() {
        $cart_id = I('cart_id', 0, 'intval');
        $img = app_upload_img('photo', '', 'cartPhoto');
        if ($img === 0) {
            $this->apiReturn(0, '拍照上传失败');
        } else if ($img === -1){
            $this->apiReturn(0, '拍照上传失败');
        } else {
            //保存拍照
            $result = D('ShoppingCart')->saveCartPhoto($cart_id, $img);
        }

        if ($result['status'] == 1) { //拍照保存成功，发送照片到邮箱
            //获取确认列表编号
            $cart_sn = M('shopping_cart')->where('id='.$cart_id)->getField('collection_sn');
            //获取商户邮箱
            $member_email = M('member_attribute_value')->where('member_id='.UID.' and attribute_id=18')->getField('attr_value');
            if ($member_email) {
                $img_array = explode('.', $img);
                $attach = array(
                    "0" => array('file_path' => substr($img, 1), 'file_name' => $cart_sn.'.'.$img_array[1])
                );
                $send_result = sendEmail($member_email, ''.$cart_sn.'商品确认信息', '此为商品确认信息邮件。', $attach);
                if ($send_result['status'] == 0) {
                    $this->homeLogApi(UID, 22, '商品确认发送照片到指定邮箱失败', 0, time());
                }
            } else {
                $this->apiReturn(0, '邮件发送失败');
            }
        }
        $this->apiReturn($result['status'], $result['info']);
    } 

    /**
     * 写入商品确认列表
     */
    public function saveShoppingCart() {
        $fav_id = I('fav_id', 0, 'intval'); //app传参 收藏列表id【int格式】
        $fav_goods_id = I('fav_goods_id', ''); //app传参 选中商品id【string字符串格式】
        $price_total=D('Favorite')->field('price_total')->where('id='.$fav_id)->find();
        //取出收藏列表信息
        $fav_info = D('Favorite')->getFavoriteInfo(array('id' => $fav_id));
        if (empty($fav_info)) {
             $this->apiReturn(0, '该收藏夹信息不存在');
        }
        $cart_sn = $this->makeCartSn($fav_id, $fav_info['name']);
        M()->startTrans();
        $data['collection_sn'] = $cart_sn;
        $data['collection_id'] = $fav_id;
        $data['price_total'] = floatval($price_total['price_total']);
        if (D('ShoppingCart')->create($data) !== false) {
            $cart_id = D('ShoppingCart')->add();
        } else {
            M()->rollback();
            $this->apiReturn(0, D('ShoppingCart')->getError());
        }

        //取出收藏详情列表
        $where['goods_id'] = array('in', $fav_goods_id);
        $where['favorite_id'] = array('eq', $fav_id);
        $where['status']    = array('EQ', 0);
        $fav_detail_list = D('FavoriteDetail')->getFavoriteDetailList($where);
        $insert_detail = D('ShoppingCartDetail')->addShoppingCartDetail($cart_id, $cart_sn, $fav_detail_list);
        if ($cart_id && $insert_detail == true) {
            M()->commit();
            $logInfo = '收藏夹推荐商品';
            $recordId = explode(',',$fav_goods_id);
            foreach ($recordId as $value){
                $this->homeLogApi(UID, 41, $logInfo, '', '',  '', $value);
            }
            /*杨yongjie  添加*/
            //如果插入成功 返回当前插入数据的收藏夹id
            $ShoppingCart_id=M('ShoppingCart')->field('id')->order('id desc')->limit(1)->find();
            /*杨yongjie  添加*/
            $this->apiReturn(1, '添加确认列表成功',$ShoppingCart_id);
        } else {
            M()->rollback();
            $this->apiReturn(0, '添加确认列表失败');
        }
    } 

    /**
     * 生成确认列表编号
     * @param int $fav_id 收藏夹id
     * @param string $fav_sn 收藏夹编号
     * @return string 确认列表编号
     */
    protected function makeCartSn($fav_id, $fav_sn) {
        $count = D('ShoppingCart')->where('collection_id='.$fav_id)->count('id');
        //根据查询个数生成三位编号
        $num = str_pad(($count+1), 2, '0', STR_PAD_LEFT);
        //规则 月-日 时:分/三位编号
        $sn = $fav_sn.'_'.$num;
        return $sn;
    }

}
