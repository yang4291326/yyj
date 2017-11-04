<?php
namespace Admin\Model;
use Think\Model;

/**
 * 用户机器码模型
 * @author yuanyulin <QQ:755687023>
 */
class MemberMachineCodeModel extends Model{
    
    protected $_validate = array(
        array('member_id', 'number', '非法数据, 必须是数字!', self::MUST_VALIDATE, 'function', 3),
        
        array('machine_code', '4,20', '机器码不正确, 请输入4到20位字符！', self::MUST_VALIDATE, 'length', 3),

        array('port', 'number', '端口号必须是数字!', self::MUST_VALIDATE, 'is_numeric', 3),

        array('mobile_code', 'checkCount', '手机码请输入4到20位字符！, 最多可输入八个!', self::VALUE_VALIDATE, 'callback', 3),
    );

    
    /**
     * 判断用户的新的机器二维码是否保存成功
     * @param array    $data  需要修改的二维码信息
     * @param int      $id    对应用户的member表中的id
     */
    public function editMemberMachineCode($data, $id=0) {
        foreach ($data as $key => $value) {
            $where['member_id'] = array('EQ', $value['member_id']);
            $where['machine_code'] = array('EQ', $value['machine_code']);
            $data[$key]['token'] = $this->where($where)->getfield('token');
        }
        $this->where('member_id='. $id)->delete(); // 删除原来的信息
        
        foreach ($data as $key => $value) { // 将新的信息写入表中
            $data['member_id'] = $id;
            $data['machine_code'] = $value['machine_code'];
            $data['mobile_code']=$value['mobile_code'];
            $data['port']=$value['port'];
            if ( $value['token'] ) { $data['token'] = $value['token']; }
            
            if($this->create($data) !== false){
                $this->add();
            } else {
                return($this->getError());
            }   
        }
        return 1;
    }
    public function checkCount($data){
        $datas=explode(',',$data);
        if(count($datas)>8){//手机码不能超过八个
            return false;
        }
        if(count($datas)==1){//当手机码仅有一个时
            if(strlen($datas[0])<4 || strlen($datas[0])>20){
                return false;
            }
        }elseif(count($datas)>1 && count($datas)<=8){
            foreach($datas as $k => $v){
                if(strlen($v)<4 || strlen($v)>20){
                    return false;
                }
            }

        }
    }
}
