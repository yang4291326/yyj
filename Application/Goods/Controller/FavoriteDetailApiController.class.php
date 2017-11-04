<?php
namespace Goods\Controller;

/**
 * Created by liuniukeji.com
 * 收藏夹详细商品接口
 * @author yuanyulin <7556870234@qq.com>
*/
class FavoriteDetailApiController extends \Common\Controller\CommonApiController {
    
    // 获取收藏夹详细商品列表接口
    public function getFavoriteDetailList(){
        $favoriteList = D('Goods/FavoriteDetail')->getFavoriteDetail();
        $shopGoodsModel = D('Goods/ShopGoods');
        foreach ($favoriteList as $key => $value) {
            $where['id'] = array('EQ', $value['colour']);
            $colorData = $shopGoodsModel->getColorList($where);
            unset($where);
            $favoriteList[$key]['add_time'] = date("Y m d H:i:s",$favoriteList[$key]['add_time']);
            $favoriteList[$key]['colour']         = $colorData[$value['colour']]['color'];                                      // 获取商品颜色
            if ($favoriteList[$key]['colour'] == null) {
                $favoriteList[$key]['colour'] = [];
            }
            $favoriteList[$key]['colour_path']    = $colorData[$value['colour']]['default_color_pic'];                                 // 获取商品颜色图片
            if ($favoriteList[$key]['colour_path'] == null) {
                $favoriteList[$key]['colour_path'] =[];
            }
            $favoriteList[$key]['goods_img']      = M('ShopGoods')->where('id='. $value['goods_id'])->getfield('goods_img');  // 获取商品的主图图片
            $favoriteList[$key]['good_name']      = $shopGoodsModel->getBasicAttrValue($value['goods_id'], 1);                // 获取商品名称
            $favoriteList[$key]['goods_code']     = $shopGoodsModel->getBasicAttrValue($value['goods_id'], 2);                // 获取商品编码
            $favoriteList[$key]['original_price'] = $shopGoodsModel->getBasicAttrValue($value['goods_id'], 3);                // 获取商品原价
            $favoriteList[$key]['present_price']  = $shopGoodsModel->getBasicAttrValue($value['goods_id'], 4);                // 获取商品现价
            $count= M('ShopGoodsModel')->query("select count(*) from ln_shop_goods_model where id in (select resource.model_id from ln_shop_goods_model_resource as resource
            where photo_resource_path is not null and photo_resource_path != ''
            and group_no is not null and group_no != '' and
            map_path is not null and map_path != '' and
            material_ball_name is not null and material_ball_name != '' ) and goods_id = ".$value['goods_id']);   // 获取该商品是否可以vr
            $favoriteList[$key]['is_vr']          = $count[0]['count(*)'] > 0 ? 1 : 0;//1 VR可用 0 VR不可用
            $articleId = M('Article')->where('goods_id='. $value['goods_id'])->getfield('id'); // 根据主商品的id获取模板表的id，再根据模板表的id获取附加信息表的信息
            $favoriteList[$key]['recommend'] = M('ArticleDetail')->field('photo_path, content, record_id')->where('article_id='. $articleId)->select();
            if($favoriteList[$key]['recommend']===false){
                $favoriteList[$key]['recommend'] = array(
                    array(
                        "photo_path"=> "", // 推荐商品的图片路径
                        "content"=> "", // 推荐商品的内容
                        "record_id"=> "" // 推荐商品的id
                    )
                );
            }
        }
        //var_dump($favoriteList);die;
        foreach($favoriteList as $k => $v){ //对数据进行分组
            $result[$v['like_level']][] = $v;
        }
        rsort($result);
        //var_dump($result);die();
        $this->apiReturn(1, '收藏夹详细商品列表', $result);
    }

    /*杨yongjie  添加*/
    //获取收藏夹详细商品 VR 列表接口
    public function getFavoriteDetailVRList(){
        $favoriteList = D('Goods/FavoriteDetail')->getFavoriteDetail();
        $shopGoodsModel = D('Goods/ShopGoods');
        foreach ($favoriteList as $key => $value) {
            $where['id'] = array('EQ', $value['colour']);
            $colorData = $shopGoodsModel->getColorList($where);
            unset($where);
            $favoriteList[$key]['add_time'] = date("Y m d H:i:s", $favoriteList[$key]['add_time']);
            $favoriteList[$key]['colour'] = $colorData[$value['colour']]['color'];                                      // 获取商品颜色
            if ($favoriteList[$key]['colour'] == null) {
                $favoriteList[$key]['colour'] = [];
            }
            $favoriteList[$key]['colour_path'] = $colorData[$value['colour']]['default_color_pic'];                                 // 获取商品颜色图片
            if ($favoriteList[$key]['colour_path'] == null) {
                $favoriteList[$key]['colour_path'] = [];
            }
            $favoriteList[$key]['goods_img'] = M('ShopGoods')->where('id=' . $value['goods_id'])->getfield('goods_img');  // 获取商品的主图图片
            $favoriteList[$key]['good_name'] = $shopGoodsModel->getBasicAttrValue($value['goods_id'], 1);                // 获取商品名称
            $favoriteList[$key]['original_price'] = $shopGoodsModel->getBasicAttrValue($value['goods_id'], 3);                // 获取商品原价
            $favoriteList[$key]['present_price'] = $shopGoodsModel->getBasicAttrValue($value['goods_id'], 4);// 获取商品现价
            //通过模型资源的四个值判断VR是否可用
            $count= M('ShopGoodsModel')->query("select count(*) from ln_shop_goods_model where id in (select resource.model_id from ln_shop_goods_model_resource as resource
            where photo_resource_path is not null and photo_resource_path != ''
            and group_no is not null and group_no != '' and
            map_path is not null and map_path != '' and
            material_ball_name is not null and material_ball_name != '' ) and goods_id = ".$value['goods_id']);
            $favoriteList[$key]['is_vr']=$count[0]['count(*)'] > 0 ? 1 : 0;//1 VR可用 0 VR不可用
        }

        foreach($favoriteList as $k => $v){
            if($v['is_vr']==1){
                $favoriteLists[$k]['id']=$v['id'];
                $favoriteLists[$k]['goods_id']=$v['goods_id'];
                $favoriteLists[$k]['style']=$v['style'];
                $favoriteLists[$k]['size']=$v['size'];
                $favoriteLists[$k]['material']=$v['material'];
                $favoriteLists[$k]['colour']=$v['colour'];
                if ($favoriteLists[$k]['colour'] == null) {
                    $favoriteLists[$k]['colour'] = [];
                }
                $favoriteLists[$k]['like_level']=$v['like_level'];
                $favoriteLists[$k]['add_time']=$v['add_time'];
                $favoriteLists[$k]['goods_count']=$v['goods_count'];
                $favoriteLists[$k]['colour_path']=$v['colour_path'];
                if ($favoriteLists[$k]['colour_path'] == null) {
                    $favoriteLists[$k]['colour_path'] = [];
                }
                $favoriteLists[$k]['goods_img']=$v['goods_img'];
                $favoriteLists[$k]['good_name']=$v['good_name'];
                $favoriteLists[$k]['original_price']=$v['original_price'];
                $favoriteLists[$k]['present_price']=$v['present_price'];
                $favoriteLists[$k]['is_vr']=$v['is_vr'];
            }
        }
        foreach($favoriteLists as $k => $v){ //对数据进行分组
            $result[0][] = $v;
        }
        //var_dump($result);die;
        $this->apiReturn(1, '收藏夹详细商品列表', $result);
    }
    /*杨yongjie  添加*/

    // 增加收藏夹详细商品接口
    public function addFavoriteDetail(){
        if(IS_POST){
            $favoriteDetailModel = D('FavoriteDetail');
            $fav_id = intval($_POST['favorite_id']);
            $where['favorite_id'] = array('eq', $fav_id);
            $where['status'] = array('eq', 0);
            $where['goods_id'] = array('eq', intval($_POST['goods_id']));
            $fav_count = M('favorite_detail')->where($where)->count();
            if ($fav_count > 0) {
                $this->apiReturn(0, '该收藏夹已包含该商品');
            }
            if($favoriteDetailModel->create() !== false){
                $this->homeLogApi(UID,30,'商品加入收藏夹',0,time(),'',$favoriteDetailModel->goods_id);
                $favoriteDetailModel->add();
                //插入喜好收藏日志
                switch ($_POST['like_level']){
                    case '0': // 比较满意
                        $logInfo = '比较满意';
                        $logType = 37;
                        break;
                    case '1': // 感觉还好
                        $logInfo = '感觉还好';
                        $logType = 38;
                        break;
                    case '2': // 再想一想
                        $logInfo = '再想一想';
                        $logType = 39;
                        break;
                }
                $this->homeLogApi(UID, $logType, $logInfo, '', '','', $_POST['goods_id']);
                $this->apiReturn(1, '收藏夹增加商品成功！');
            } else {
                $this->apiReturn(0, $favoriteDetailModel->getError());
            }
        }
    }

    
    // 删除收藏夹详细商品接口
    public function delFavoriteDetail(){
        $id = I('post.id', '', 'trim');
        $where['id'] = array('IN', $id);
        $goods_id = M('FavoriteDetail')->where($where)->getfield('goods_id',true);
        $result = M('FavoriteDetail')->where($where)->setField('status', 1);

        if ($result) {
            foreach ($goods_id as $key => $value) {
                $this->homeLogApi(UID,31,'加入收藏夹后被删除的商品',0,time(),'',$value);
            }            
            $this->apiReturn(1, '删除成功！');            
        } else {
            $this->apiReturn(0, '删除失败！');            
        }
    }
}
