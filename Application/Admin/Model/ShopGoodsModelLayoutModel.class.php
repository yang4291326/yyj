<?php
namespace Admin\Model;
use Think\Model;

/**
 * Created by liuniukeji.com
 * 商品模型户型
 * @author goryua <1661745274@qq.com>
*/

class ShopGoodsModelLayoutModel extends Model {
    /*杨yongjie  修改*/
//    protected $insertFields = array('model_id','name','layout_photo_path','layout_path','layout_style','layout_scene','initial_position','initial_direction','is_default','version');
//    protected $selectFields = array('model_id','name','layout_photo_path','layout_path','layout_style','layout_scene','initial_position','initial_direction','is_default','id','version');
    /*杨yongjie  修改*/
    protected $insertFields = array('member_id','name','layout_photo_path','layout_path','layout_style','layout_scene','initial_position','initial_direction','is_default','version');
    protected $selectFields = array('member_id','name','layout_photo_path','layout_path','layout_style','layout_scene','initial_position','initial_direction','is_default','id','version');
    protected $_validate = array(
        // array('name', 'require', '户型名称不能为空', self::MUST_VALIDATE, 'regex', 4),
        // array('layout_photo_path', 'require', '户型图标资源不能为空', self::MUST_VALIDATE, 'regex', 5),
        // array('layout_path', 'require', '户型模型资源不能为空', self::MUST_VALIDATE, 'regex', 6),
        // array('layout_style', 'require', '户型风格不能为空', self::MUST_VALIDATE, 'regex', 7),
        // array('layout_scene', 'require', '户型场景不能为空', self::MUST_VALIDATE, 'regex', 8),
        // array('initial_position', 'require', '初始位置不能为空', self::MUST_VALIDATE, 'regex', 9),
        // array('initial_direction', 'require', '初始方向不能为空', self::MUST_VALIDATE, 'regex', 10),
    );

     public function getLayoutList($where, $field = null, $order = 'id asc') {
        if ($field == null) {
            $field = $this->selectFields;
        }
        
        $list = $this->field($field)->where($where)->order($order)->select();
        return $list;
    }
    
    /**
     * 保存商品户型
     * @param array $vr_data 模型数据
     * @param integer $model_id 模型id
     * @return bool 成功返回true，否则返回错误提示
    */
//    public function saveLayout($vr_data, $model_id) {
//        // if (empty($vr_data['name']) || empty($vr_data['layout_photo_path']) || empty($vr_data['layout_path'])) {
//        //  return '户型不能为空';
//        //  exit;
//        // }
//
//        $list = $this->getLayoutList(array('member_id' => $model_id));
//        if (!empty($list) && is_array($list)) {
//            foreach ($list as $val) { //清空文件图片
//                if (file_exists($val['layout_photo_path']) == true) {
//                    @unlink($val['layout_photo_path']);
//                }
//                if (file_exists($val['layout_path']) == true) {
//                    @unlink($val['layout_path']);
//                }
//            }
//            $this->where('model_id ='.$model_id)->delete();
//        }
//
//        /*杨yjie 添加*/
//        //单独拿出查询到的id和版本号
//        foreach($list as $k =>$v){
//            //unset($v['id']);
//            $id[]=$v['id'];
//            //var_dump($id);
//            $version[]=$v['version'];
//            //var_dump($version);
//            $list[$k]=$v;
//         }
//        foreach($id as $k =>$v){
//           $vr_data['id'][$k+1]=$v;
//        }
//        foreach($version as $k =>$v){
//           $vr_data['version'][$k+1]=$v;
//        }
        /*杨yjie 添加*/
        

        //var_dump($vr_data);die;
//        foreach ($vr_data['name'] as $key => $value) {
//            // if (!$value) {
//            //     $_validate = 4; //户型名称不能为空
//            // }
//            // if (!$vr_data['layout_photo_path'][$key]) {
//            //     $_validate = 5; //户型图标不能为空
//            // }
//            // if (!$vr_data['layout_path'][$key]) {
//            //     $_validate = 6; //户型模型不能为空
//            // }
//            // if (!$vr_data['layout_style'][$key]) {
//            //     $_validate = 7; //户型风格不能为空
//            // }
//            // if (!$vr_data['layout_scene'][$key]) {
//            //     $_validate = 8; //户型场景不能为空
//            // }
//            // if (!$vr_data['initial_position'][$key]) {
//            //     $_validate = 9; //初始位置不能为空
//            // }
//            // if (!$vr_data['initial_direction'][$key]) {
//            //     $_validate = 10; //初始方向不能为空
//            // }
//            $data['model_id'] = $model_id;
//            $data['name'] = $value;
//            $data['layout_photo_path'] = $vr_data['layout_photo_path'][$key];
//            $data['layout_path'] = $vr_data['layout_path'][$key];
//            $data['layout_style'] = $vr_data['layout_style'][$key];
//            $data['layout_scene'] = $vr_data['layout_scene'][$key];
//            $data['initial_position'] = $vr_data['initial_position'][$key];
//            $data['initial_direction'] = $vr_data['initial_direction'][$key];
//            $data['is_default'] = $vr_data['is_default_hx'][$key];
//
//            /*杨yjie 添加*/
//            $data['id']=$vr_data['id'][$key];
//            $data['version']=$vr_data['version'][$key];
//            static $datas=array();
//            $datas[]=$data;
//            /*杨yjie 添加*/
//
//            if ($this->create($data, $_validate) !== false) {
//                unset($_validate);
//                $this->add();
//            } else {
//                unset($_validate);
//                return($this->getError());
//            }
//            unset($data);
//        }
//
//        /*杨yjie 添加*/
//        // var_dump($datas);die;
//        // var_dump($list);die;
//        //设置一个开关
//        $_POST['layout']['flag']=false;
//        //循环对比每组插入的新数据与原数据是否相同
//        foreach($datas as $k => $v){
//               // var_dump($datas[$k]);
//               // var_dump($list[$k])
//               if($datas[$k] != $list[$k]){
//                    //echo 'OK';
//                   $_POST['layout']['id'][]=$v['id'];
//                   $_POST['layout']['flag']=true;
//               }
//        }
//        //var_dump($_POST['layout']['id']);die;
//        /*杨yjie 添加*/
//
//        return true;
//    }
}
?>