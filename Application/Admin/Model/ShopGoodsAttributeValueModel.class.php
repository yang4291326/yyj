<?php
namespace Admin\Model;
use Think\Model;

/**
 * Created by liuniukeji.com
 * 商品属性值模型
 * @author goryua <1661745274@qq.com>
*/

class ShopGoodsAttributeValueModel extends Model {
    
    protected $insertFields = array('goods_id','attribute_id','attr_value');
	protected $selectFields = array('id','goods_id','attribute_id','attr_value'); 

    protected $_validate = array(
        array('goods_id', 'number', '非法数据, 必须是数字', self::MUST_VALIDATE, 'regex', 3),
        array('attr_value', 'require', '基本信息全部不能为空', self::MUST_VALIDATE, 'regex', 4),
        array('attr_value', '1,11', '商品名称最多不能超过11个字', self::VALUE_VALIDATE, 'length', 5),
        array('attr_value', 'number', '商品编码只能为数字', self::VALUE_VALIDATE, 'regex', 6),
        //array('attr_value', '1,18', '商品编码最多不能超过18个数字', self::VALUE_VALIDATE, 'length', 6),
        array('attr_value', '1,18', '商品编码最多不能超过18个数字', self::VALUE_VALIDATE, 'length', 11),/*杨yongjie  修改*/
        array('attr_value', '1,11', '商品价格最多只能输入11个字', self::VALUE_VALIDATE, 'length', 7),
        array('attr_value', 'number', '商品排序只能为数字', self::VALUE_VALIDATE, 'regex', 8),
        array('attr_value', '1,5', '商品风格最多不能超过5个字', self::VALUE_VALIDATE, 'length', 9),
        array('attr_value', '1,8', '商品材质最多不能超过8个字', self::VALUE_VALIDATE, 'length', 10),
    );

    /**
     * 获取属性值列表
    */
    public function getAttrValue($where, $field = null, $order = 'id desc') {
        if ($field == null) {
            $field = $this->selectFields;
        }

        $list = $this->field($field)->where($where)->order($order)->select();
        return $list;
    }

    /**
     * 获取属性值详情
    */
    public function getAttrInfo($where, $field = null) {
        if ($field == null) {
            $field = $this->selectFields;
        }

        return $this->field($field)->where($where)->find();
    }
    
    /**
     * 根据商品id 获取基础属性信息
     * @param integer $goods_id 商品id
     * @param integer $typeid 基础属性类型id 1名称 2编号 3原价 4现价 5优惠 6排序
     * @return string
    */
    public function getBasicAttrValue($goods_id, $typeid) {
        if ($typeid > 6) 
            return false;
        
        $where['goods_id'] = array('eq', $goods_id);
        $where['attribute_id'] = array('eq', $typeid);
        $info = $this->where($where)->getField('attr_value');
        return $info;
    }

    /**
     * 保存商品属性值
     * @param array $attr_data 属性数组值
     * @param integer $goods_id 商品id
     * @return bool 成功返回true，否则返回错误提示
    */
    public function saveAttrValue($attr_data, $goods_id) {
        $attr_list = $this->getAttrValue(array('goods_id' => $goods_id));
        if (!empty($attr_list) && is_array($attr_list)) {
        	$this->where('goods_id='.$goods_id)->delete();
        }

    	//获取系统定义属性
    	$sys_attr = D('ShopGoodsAttribute')->where('attr_mode = 0 and attr_status = 0')->getField('id', true);

        /*杨yongjie  添加  用于判断是文本还是数字*/
        $attr_type = D('ShopGoodsAttribute')->where('attr_mode = 0 and attr_status = 0 and id=2')->getField('attr_type', true);
        /*杨yongjie  添加*/

		foreach ($attr_data as $key => $value) {
			if ($value == '') {
				//continue;
			}
			if (is_array($value)) { //如果值为数组，转化成字符串
				$value = implode(',', $value);
			}
			$attr_data['goods_id'] = $goods_id;
			$attr_data['attribute_id'] = $key;
			$attr_data['attr_value'] = $value;
            
            //判断是否为系统定义属性，需要验证
            if (in_array($key, $sys_attr)) {
            	$_validate = 4;
            	if ($key == 1 && $value != '') { //验证商品名称长度
            		$_validate = 5;
            	}
//                if ($key == 2 && $value != '') { //验证商品编码格式
//                    $_validate = 6;
//                }

                /*杨yongjie  添加*/
                if ($key == 2 && $value != '') { //验证商品编码格式(数字和文本)
                    if($attr_type[0]==0){
                        $_validate = 11;
                    }elseif($attr_type[0]==1){
                        $_validate = 6;
                    }
                }
                /*杨yongjie  添加*/

                if ($key == 4 && $value != '') { //验证商品价格长度
                    $_validate = 7;
                }
            	if ($key == 6 && $value != '') { //验证商品排序格式
            		$_validate = 8;
            	}
                if ($key == 8 && $value != '') { //验证商品风格长度
                    $_validate = 9;
                }
                if ($key == 9 && $value != '') { //验证商品材料长度
                    $_validate = 10;
                }
            }
            
            if ($this->create($attr_data, $_validate) !== false) {
            	unset($_validate);
                if ($value != ''){
                    $this->add();
                }
            } else {
            	unset($_validate);
            	return($this->getError());
            }
		}

		return true;
    }

}
?>