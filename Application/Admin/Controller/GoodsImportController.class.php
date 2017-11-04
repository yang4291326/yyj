<?php
namespace Admin\Controller;
/**
 * Created by PhpStorm.
 * User: yangyongjie
 * Date: 2017/6/8
 * Time: 9:18
 */
class GoodsImportController extends AdminCommonController{
    public function importexecl(){
        if (!IS_POST) {
            $this->display();
        }
        if (!empty($_FILES)) {
            $config = array(
                'mimes' => '', //允许上传的文件MiMe类型
                'maxSize' => 0, //上传的文件大小限制 (0-不做限制)
                'exts' => 'xls,xlsx', //允许上传的文件后缀
                'autoSub' => true, //自动子目录保存文件
                'subName' => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
                'rootPath' => './Uploads/', //保存根路径
                'savePath' => 'UploadsField/' . CONTROLLER_NAME . '/', //保存路径
                'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
                'saveExt' => '', //文件保存后缀，空则使用原后缀
                'replace' => false, //存在同名是否覆盖
                'hash' => true, //是否生成hash编码
                'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
            );
            $upload = new \Think\Upload($config);
            $info = $upload->upload($_FILES);
            //上传失败
            if (!$info) {
                $this->error($upload->getError());
            //上传成功
            } else {
//                die();
                $ext = $info['importtable']['ext'];//获取上传文件后缀
                $filename = 'Uploads/' . $info['importtable']['savepath'] . $info['importtable']['savename'];//获取上传文件的根路径
                //p($info);die;
                vendor("PHPExcel.PHPExcel");//引入PHPExcel类库
                //判断是xlsx还是xls
                if ($ext == 'xlsx') {
                    $objReader = \PHPExcel_IOFactory::createReader("Excel2007");
                    $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
                } elseif ($ext == 'xls') {
                    $objReader = \PHPExcel_IOFactory::createReader("Excel5");
                    $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
                }
                $sheetcount = $objPHPExcel->getSheetCount();//获取excel文件里有多少个sheet
                M()->startTrans();

                //调用存储过程
                $M=new \Think\Model();
                $sql="CALL DataImport_CreateTmpTable()";//这个存储过程主要为了创建下面六张临时表
                $M->query($sql);
                $tmp_shop_goods_type=M('TmpShopGoodsType');
                $TmpShopGoods=M('TmpShopGoods');
                $TmpShopGoodsColor=M('TmpShopGoodsColor');
                $TmpShopGoodsDetail=M('TmpShopGoodsDetail');
                $TmpShopGoodsRecommend=M('TmpShopGoodsRecommend');
                $TmpShopGoodsArticle=M('TmpShopGoodsArticle');

                //循环取出所有sheet里的数据
                for ($i = 0; $i < $sheetcount; $i++) {
                    //当获取到sheet1数据时
                    if ($i == 0) {
                        $arr0 = $this->changeArr($objPHPExcel, $i);
                        foreach($arr0 as $k => $v){
                            $j=$i+1;
                            if($v['A']==null){
                                $this->error("sheet".$j."---".$k."行A列单元格值不能为空值");
                            }
                            if($v['B']==null){
                                $this->error("sheet".$j."---".$k."行B列单元格值不能为空值");
                            }
                            $data0['id']=$v['A'];
                            $data0['categoryName']=$v['B'];
                            $data0['style']=$v['C'] ? $v['C'] : "";
                            $data0['brand']=$v['D'] ? $v['D'] : "";
                            $data0['commodityName']=$v['E'] ? $v['E'] : "";
                            $data0['pic']=$v['F'] ? $v['F'] : "";
                            $data0['describes']=$v['G'] ? $v['G'] : "";
                            //dump($data0);
                            //插入数据库
                            $res0=$tmp_shop_goods_type->add($data0);
                            //如果插入失败.则回调
                            if(!$res0){
                                M()->rollback();
                                $this->error("sheet1数据插入失败");
                            }
                        }
                        //die;
                    //当获取到sheet2数据时
                    }elseif($i == 1){
                        $arr1=$this->changeArr($objPHPExcel,$i);
                        foreach($arr1 as $k =>$v){
                            $j=$i+1;
                            if($v['A']==null){
                                $this->error("sheet".$j."---".$k."行A列单元格值不能为空值");
                            }
                            if($v['B']==null){
                                $this->error("sheet".$j."---".$k."行B列单元格值不能为空值");
                            }
                            $data1['id']=$v['A'];
                            $data1['commodityName']=$v['B'];
                            $data1['commodityCode']=$v['C'] ? $v['C'] : "";
                            $data1['price']=$v['D'] ? $v['D'] : "";
                            $data1['sale']=$v['E'] ? $v['E'] : "";
                            $data1['discount']=$v['F'] ? $v['F'] : "";
                            $data1['sort']=$v['G'] ? $v['G'] : "";
                            $data1['size']=$v['H'] ? $v['H'] : "";
                            $data1['style']=$v['I'] ? $v['I'] : "";
                            $data1['mat']=$v['J'] ? $v['J'] : "";
                            //dump($data1);
                            //插入数据库
                            $res1=$TmpShopGoods->add($data1);
                            //如果插入失败.则回调
                            if(!$res1){
                               M()->rollback();
                                $this->error("sheet2数据插入失败");
                            }

                        }
                    //当获取到sheet3数据时
                    }elseif($i == 2){
                        $arr2=$this->changeArr($objPHPExcel,$i);
                        foreach($arr2 as $k => $v){
                            $j=$i+1;
                            if($v['A']==null){
                                $this->error("sheet".$j."---".$k."行A列单元格值不能为空值");
                            }
                            if($v['B']==null){
                                $this->error("sheet".$j."---".$k."行B列单元格值不能为空值");
                            }
                            $data2['id']=$v['A'];
                            $data2['categoryName']=$v['B'];
                            $data2['color']=$v['C'] ? $v['C'] : "";
                            $data2['icon']=$v['D'] ? $v['D'] : "";
                            $data2['commodityPic']=$v['E'] ? $v['E'] : "";
                            //dump($data2);
                            //插入数据库
                            $res2=$TmpShopGoodsColor->add($data2);
                            //如果插入失败.则回调
                            if(!$res2){
                                M()->rollback();
                                $this->error("sheet3数据插入失败");
                            }
                        }
                        //die;
                    //当获取到sheet4数据时
                    }elseif($i == 3){
                        $arr3=$this->changeArr($objPHPExcel,$i);
                        foreach($arr3 as $k => $v){
                            $j=$i+1;
                            if($v['A']==null){
                                $this->error("sheet".$j."---".$k."行A列单元格值不能为空值");
                            }
                            if($v['B']==null){
                                $this->error("sheet".$j."---".$k."行B列单元格值不能为空值");
                            }
                            $data3['id']=$v['A'];
                            $data3['categoryName']=$v['B'];
                            $data3['commodityPic']=$v['C'] ? $v['C'] : "";
                            $data3['describes']=$v['D'] ? $v['D'] : "";
                            $data3['sort']=$v['E'] ? $v['E'] : "";
                            //dump($data3);
                            //插入数据库
                            $res3=$TmpShopGoodsDetail->add($data3);
                            //如果插入失败.则回调
                            if(!$res3){
                                M()->rollback();
                                $this->error("sheet4数据插入失败");
                            }
                        }
                        //die;
                    //当获取到sheet5数据时
                    }elseif($i == 4){
                        $arr4=$this->changeArr($objPHPExcel,$i);
                        foreach($arr4 as $k => $v){
                            $j=$i+1;
                            if($v['A']==null){
                                $this->error("sheet".$j."---".$k."行A列单元格值不能为空值");
                            }
                            if($v['B']==null){
                                $this->error("sheet".$j."---".$k."行B列单元格值不能为空值");
                            }
                            $data4['id']=$v['A'];
                            $data4['categoryName']=$v['B'];
                            $data4['recommend1']=$v['C'] ? $v['C'] : "";
                            $data4['recommend2']=$v['D'] ? $v['D'] : "";
                            $data4['recommend3']=$v['E'] ? $v['E'] : "";
                            $data4['recommend4']=$v['F'] ? $v['F'] : "";
                            $data4['recommend5']=$v['G'] ? $v['G'] : "";
                            $data4['recommend6']=$v['H'] ? $v['H'] : "";
                            $data4['recommend7']=$v['I'] ? $v['I'] : "";
                            $data4['recommend8']=$v['J'] ? $v['J'] : "";
                            $data4['recommend9']=$v['K'] ? $v['K'] : "";
                            $data4['recommend10']=$v['L'] ? $v['L'] : "";
                            $data4['recommend11']=$v['M'] ? $v['M'] : "";
                            $data4['recommend12']=$v['N'] ? $v['N'] : "";
                            $data4['recommend13']=$v['O'] ? $v['O'] : "";
                            $data4['recommend14']=$v['P'] ? $v['P'] : "";
                            $data4['recommend15']=$v['Q'] ? $v['Q'] : "";
                            //dump($data4);
                            //插入数据库
                            $res4=$TmpShopGoodsRecommend->add($data4);
                            //如果插入失败.则回调
                            if(!$res4){
                               M()->rollback();
                               $this->error("sheet5数据插入失败");
                            }
                        }
                        //die();
                    //当获取到sheet6数据时
                    }elseif($i == 5){
                        $arr5=$this->changeArr($objPHPExcel,$i);
                        foreach($arr5 as $k => $v){
                            $j=$i+1;
                            if($v['A']==null){
                                $this->error("sheet".$j."---".$k."行A列单元格值不能为空值");
                            }
                            if($v['B']==null){
                                $this->error("sheet".$j."---".$k."行B列单元格值不能为空值");
                            }
                            $data5['id']=$v['A'];
                            $data5['articleType']=$v['B'];
                            $data5['titleName']=$v['C'] ? $v['C'] : "";
                            $data5['pic']=$v['D'] ? $v['D'] : "";
                            $data5['describes']=$v['E'] ? $v['E'] : "";
                            //dump($data5);
                            //插入数据库
                            $res5=$TmpShopGoodsArticle->add($data5);
                            //如果插入失败.则回调
                            if(!$res5){
                                M()->rollback();
                                $this->error("sheet6数据插入失败");
                            }
                        }
                    }
                }
                //die;
                //所有数据插入成功则提交
                if($res0 && $res1 && $res2 && $res3 && $res4 && $res5){
                    $num=UID;
                    //存储过程调用的参数为member_id
                    $con1=$M->query("CALL DataImport_GoodsType($num)");
                    $con2=$M->query("CALL DataImport_GoodsData($num)");
                    $con3=$M->query("CALL DataImport_GoodsOtherData($num)");
                    $con4=$M->query("CALL DataImport_ArticleData($num)");
                    if($con1[0][1] ==1 && $con2[0][1]==1 && $con3[0][1]==1 && $con4[0][1]==1){
                        //echo "OK";die();
                        M()->commit();
                        $this->success('导入成功','/Admin/GoodsImport/importexecl');
                    }else{
                        M()->rollback();
                        $this->error("Execl导入失败!存储过程执行失败!");
                    }
                }else{
                    M()->rollback();
                    $this->error("Execl导入失败!数据插入有误!");
                }
            }
        }
    }

    /*
     * $objPHPExcel 实例化的excel对象
     * $number Excel里的sheet 例如:0相当于sheet1,1相当于sheet2
     */
    public function changeArr($objPHPExcel, $number = '')
    {
        $sheet = $objPHPExcel->getSheet($number);//o表示excel里面的第一个sheet,以此类推
        $row = $sheet->getHighestRow();//取得总行数
        $column = $sheet->getHighestColumn();//取得总列数
        for ($i = 1; $i <= $row; $i++) {
            //不想读取第一行的内容直接跳过
            if ($i == 1) {
                continue;
            }
            //从那一列开始.A表示第一列
            for ($j = 'A'; $j <= $column; $j++) {
                //获取每个单元格坐标 例:1-A ,1-B....
                $address = $j . $i;
                //将每个单元格坐标和对应的值存进新数组
                $arr[$i][$j] = $sheet->getCell($address)->getValue();
            }
        }
        return $arr;
    }
}
