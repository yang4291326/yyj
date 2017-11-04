<?php
namespace Goods\Controller;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: yyj
 * Date: 2017/4/21
 * Time: 14:00
 * 商品搜索接口
 */
header('Content-Type:text/html;charset=utf-8');
class GoodssearchApiController extends \Common\Controller\CommonApiController{
    public function index(){
        $UserToken=I('post.token','','trim');
        $CommodityName=I('post.CommodityName','','trim');
        $CommodityStyle=I('post.CommodityStyle','','trim');
        $CommodityType=I('post.CommodityType','','trim');
        $CommodityPriceB=I('post.CommodityPriceB',-1,'intval');
        $CommodityPriceS=I('post.CommodityPriceS',-1,'intval');
        $M=new Model();
        $sql="CALL SearchData('$UserToken','$CommodityName','$CommodityStyle', '$CommodityType', $CommodityPriceB,$CommodityPriceS)";
        $res=$M->query($sql);
        $this->apiReturn(1, '搜索商品', $res);
    }
}
