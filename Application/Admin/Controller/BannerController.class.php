<?php
namespace Admin\Controller;

/**
 * 后台轮播图控制器
 * @author wangzhiliang QQ:1337841872 liniukeji.com
 */
class BannerController extends AdminCommonController {

    // 手机启动页管理
    public function mobileStartPicture(){
        $where['list.type'] = 0; // type轮播图类型  0表示手机APP启动页图片列表  1表示手机APP轮播图图片列表  2手机登录页背景图片
        $result = $this->_bannerList($where);

        $this->assign('type', 0);
        $this->display('index');
    }

    // 手机轮播图管理
    public function mobileBannerPicture(){
        $where['list.type'] = 1; // type轮播图类型  0表示手机APP启动页图片列表  1表示手机APP轮播图图片列表  2手机登录页背景图片
        $where['list.member_id'] = UID;
        $result = $this->_bannerList($where);

        $this->assign('type', 1);
        $this->display('index');
    }

    // 手机登录背景图管理
    public function mobileLoginPicture(){
        $where['list.type'] = 2; // type轮播图类型  0表示手机APP启动页图片列表  1表示手机APP轮播图图片列表  2手机登录页背景图片
        $result = $this->_bannerList($where);

        $this->assign('type', 2);
        $this->display('index');
    }

    // 根据条件查询banner图列表
    private function _bannerList($where){
        $where['list.status'] = 0;
        $Banner = D('Banner');
        $field = 'list.*, member.user_name';
        $list = $Banner->bannerList($where,$field);

        $prompt_tips = $this->_getPromptLanguage(); //获取提示语
        $this->assign('prompt_tips', $prompt_tips);
        $this->assign('list', $list);
    }

    // 轮播图添加、修改
    public function edit(){
        $type = I('type');
        $id = I('id', 0, 'intval');     // banner表的主键id
        $Banner = D('Banner');
        if (IS_POST) {
            $click_type = I('click_type', 0, 'intval');
            if ($click_type == 1) {
                $_POST['open_type'] = 1;
                unset($_POST['url']);
            }
            if ($click_type == 2) {
                unset($_POST['goods_id']);
            }

            if ($id == 0) {

                /*杨yongjie  添加*/
                $count=D('Banner')->usableCount();//获取用户已经添加的可用的轮播图个数
                $accesscount=$this->accessCount();//获取用户可以添加轮播的总个数
                if($count >= $accesscount){
                    $this->ajaxReturn(V(0, '对不起, 您可以添加的轮播图个数已达上限!'));
                }
                /*杨yongjie  添加*/

                $data = $Banner->create(I('post.'), 1);
                if($data){
                    $Banner->member_id = UID;
                    $Banner->add();
                    $this->ajaxReturn(V(1, '保存成功'));
                } else {
                    $this->ajaxReturn(V(0, $Banner->getError()));
                }
            } else{
                $data = $Banner->create(I('post.'), 2);
                if($data){
                    $Banner->save();
                    $this->ajaxReturn(V(1, '修改成功'));
                } else {
                    $this->ajaxReturn(V(0, $Banner->getError()));
                }
            }
        }
        $info = $Banner->detailInfo($id);
        if ($info['open_type'] == 1) {
            $goodsName = D('ShopGoodsAttributeValue')->getBasicAttrValue($info['goods_id'], 1);
            $info['goodsName'] = $goodsName;
        }
        
        $this->assign('type', $type);
        $this->assign('info', $info);       
        $this->display();
    }
    /**
     * 图片排序
     * @author wangwujiang <1358140190@qq.com>
     */

    public function sort(){
        if(IS_GET){

            $type = I('get.type','', 'intval');
            $num = array(0,1,2);
            if (in_array($type, $num)) {

                //获取排序的数据
                $map['status'] = 0;
                $map['type'] = $type;
                /*杨yongjie  添加*/
                $map['member_id']=UID;
                /*杨yongjie  添加*/
                $list = M('Banner')->where($map)->field('id,title')->order('sort asc,id desc')->select();
                $this->assign('type', $type);//返回type值
                $this->assign('list', $list);
            }
            $this->display();
        }elseif (IS_POST) {
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key=>$value) {
                $res = M('Banner')->where(array('id'=>$value))->setField('sort', $key+1);
            }
            if ($res !== false) {
                $this->ajaxReturn(v(1, '排序成功'));
            } else {
                $this->ajaxReturn(v(0, '排序失败'));
            }
        } else {
            $this->ajaxReturn(v(0, '非法请求'));
        }
    }

    public function recycle(){
        $this->_recycle('Banner');
    }

    // 删除图片
    public function delFile(){
        $this->_delFile();  //调用父类的方法
    }

    // 上传图片
    public function uploadImg(){
        $this->_uploadImg();  //调用父类的方法
    }

    // 改变可用状态
    public function changeDisabled(){
        $this->_changeDisabled('Banner');  //调用父类的方法
    }

    // ajax设置为默认启动页
    public function ajaxChangeIsDefault(){
        if (IS_AJAX) {
            $id = I('id', 0, 'intval');
            $type = I('type', -1, 'intval');
            if ($type == -1) {
                $this->ajaxReturn(V(0, '修改失败, 需要type'));
            }
            $where['type'] = $type;
            $map['id'] = $id;
            M('Banner')->where($where)->setField('is_default', 1);
            M('Banner')->where($map)->setField('is_default', 0);
            $this->ajaxReturn(V(1, '修改成功'));
        }
    }

    /*杨yongjie  添加*/
    //获取用户可以添加轮播的总个数
    public function accessCount(){
        $where['member_id']=UID;
        $accesscount=M('DataAccess')->where($where)->field('carousel_num')->find();
        return $accesscount['carousel_num'];
    }
    /*杨yongjie  添加*/
}
