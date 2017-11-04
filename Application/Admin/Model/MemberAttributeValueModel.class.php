<?php
namespace Admin\Model;
use Think\Model;

/**
 * 用户自定义属性添加模型
 * @author yuanyulin <QQ:755687023> 
 */
class MemberAttributeValueModel extends Model{

    protected $_validate = array(
        array('member_id', 'number', '非法数据, 必须是数字！', self::MUST_VALIDATE, 'regex', 3),
        
        array('attribute_id', 'number', '非法数据, 必须是数字！', self::MUST_VALIDATE, 'regex', 3),
        
        array('attr_value', '0, 20',   '用户的自定义属性值不正确, 请输入0到20位字符！', self::MUST_VALIDATE,  'length', 3),
        array('attr_value', 'require', '用户的自定义属性值不能为空, 请输入字符！',      self::MUST_VALIDATE,  'regex',  4),
        array('attr_value', 'email',   '格式必须是电子邮件格式！',                     self::VALUE_VALIDATE, 'regex',  5),
        array('attr_value', 'email',   '格式必须是电子邮件格式！',                     self::MUST_VALIDATE,  'regex',  6),
        array('attr_value', 'number',  '非法数据, 必须是数字！',                       self::MUST_VALIDATE,  'regex', 7),
        array('attr_value', 'number',  '非法数据, 必须是数字！',                       self::VALUE_VALIDATE, 'regex', 8),
    );
    
    /**
     * 判断用户自定义属性是否保存成功
     * @param array    $data  需重新写入的用户的的自定义属性
     * @param int      $id    对应的member表中的id
     * @param array    $attr_name  需重新写入的用户的的自定义属性名称
     */
    public function editMemberAttributeValue($data, $id=0, $attr_name) {
        
        $arrayNoOperationalId = M('MemberAttribute')->where('is_edit=0')->getfield('id', true); // 查出自定义属性表中的后台数据为不可写的自定义属性的id
        
        $where['attribute_id'] = array('NOT IN', $arrayNoOperationalId); // 要删除的原来的保存的用户自定义属性不能有不可写的自定义属性
        $where['member_id']    = array('EQ', $id);
        $this->where($where)->delete(); // 删除原来的保存的用户自定义属性
        
        $arrayMustWriteId = M('MemberAttribute')->where('attr_require=1')->getfield('id', true); // 查出用户自定义属性表中必须填写的字段
        $arrayEmailId = M('MemberAttribute')->where('arrt_control=2')->getfield('id', true); // 查出用户自定义属性表中属性值是email的字段
        $arrayNumId = M('MemberAttribute')->where('attr_type=1')->getfield('id', true); // 查出用户自定义属性表中属性值是必须是数字的字段

        foreach ($data as $key => $value) { // 将新的信息写入表中
            
            $data['member_id']    = $id;    // 用户的id
            $data['attr_value']   = $value; // 用户的扩展属性值
            
            if (!(in_array($key, $arrayNoOperationalId))) // 如果用户提交的扩展属性id在不可写的自定义属性中则不放入要操作的数组中
                $data['attribute_id'] = $key;   // 用户的扩展属性id

            if ((in_array($key, $arrayMustWriteId))) { // 如果用户提交的扩展属性id是必须填写的那么该条记录就要走验证
                if ($value == '') {
                    return $attr_name[$key].'--不能为空';
                }
            }
            
            if ((in_array($key, $arrayEmailId))) { // 如果用户提交的扩展属性id是email格式的那么该条记录就要走验证
                $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
                if (!preg_match($pattern, $value) && $value != ''){
                    return $attr_name[$key].'--格式不正确';
                }
            }
            
            if ((in_array($key, $arrayNumId))) { // 如果用户提交的扩展属性id是数字格式的那么该条记录就要走验证
                if (is_numeric($value) == false && $value != '') {
                    return $attr_name[$key].'--必须是数字！';
                }
            }

            if ($this->create($data, $validate) !== false) {  // 如果验证都没有走那么validate默认为3
                unset($validate);
                unset($type);
                $this->add();
            } else {
                unset($validate);
                unset($type);
                return($this->getError());
            }         
        }
        return true; // 1表示保存用户的自定义属性成功
    }   
    
    /**
     * 根据用户传入的用户id和需要获取的用户的属性id来获取该用户的实际属性值
     * @param int $memberid
     * @param int $memberpropertyid
     * @return $memberPropertyValue
     */
    public function getMemberAttributeValueByMemberIdAndMemeberProperty($memberid, $memberpropertyid){

        $where['member_id']    = array('EQ', $memberid);         // 用户的id
        $where['attribute_id'] = array('EQ', $memberpropertyid); // 用户的扩展属性的id
        $memberPropertyValue = $this->where($where)->getfield('attr_value');
        
        return $memberPropertyValue;
    }
}
