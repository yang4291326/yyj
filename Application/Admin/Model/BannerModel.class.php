<?php
namespace Admin\Model;
use Think\Model;
/**
 * 后台轮播图、登录背景图
 * @author wangzhiliang QQ:1337841872 liniukeji.com
 *
 */
class BannerModel extends Model{
	protected $insertFields = array('title','type','img','add_time','sort','open_type','url','goods_id','is_default','member_id');
	protected $updateFields = array('id','title','type','img','add_time','status','sort','open_type','url','goods_id','is_default');
	protected $selectFields = array('id','title','type','img','add_time','status','sort','open_type','url','goods_id','is_default','member_id');

	protected $_validate = array(
		array('title', 'require', '标题名称不能为空', 1, 'regex', 3),
		array('title', '1,60', '标题名称长度有误,请输入1到60个字符', 1, 'length', 3),
		array('img', 'require', '图片不能为空', 1, 'regex', 3),
		array('img', '1,255', '图片长度有误', 0, 'length', 3),
		array('url','/^http:\/\//','跳转url有误！必须以http://开头', 2, 'regex', 3),
		array('url','1,255','跳转url有误！', 2, 'length', 3),
		array('goods_id', '1,1000000000', '选择商品有误', 0, 'between', 3),
		array('sort','1,1000','排序必须填写', 0,'between',3),
	);

	//添加时间
	protected function _before_insert(&$data, $option){
		$data['add_time'] = NOW_TIME;
		$data['member_id'] = UID;
	}
	protected function _before_update(&$data, $option){
		$data['add_time'] = NOW_TIME;
		$data['member_id'] = UID;
	}

	//轮播图列表
	public function bannerList($where, $field=null, $order='list.sort asc, list.id desc'){
		if ($field == null) $field = $this->selectFields;

		$list = $this->alias('list')
			->join('__MEMBER__ member ON list.member_id = member.id','left')
			->where($where)
			->field($field)
			->order($order)
			->select();
		return $list;
	}
	//修改轮播图的详情页
	public function detailInfo($id){
		$info = $this->find($id);
		return $info;
	}

	/*杨yongjie  添加*/
	//获取用户已经添加的可用的轮播图个数
	public function usableCount(){
	    $where['status']=0;
        $where['member_id']=UID;
        $count=$this->where($where)->count('id');
        return $count;
    }
    /*杨yongjie  添加*/
}