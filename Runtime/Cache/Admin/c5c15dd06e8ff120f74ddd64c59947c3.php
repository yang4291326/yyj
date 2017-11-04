<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo ($meta_title); ?></title>
    <link rel="stylesheet" type="text/css" href="/Application/Admin/Static/css/base.css" media="all">
    <link rel="stylesheet" type="text/css" href="/Application/Admin/Static/css/common.css" media="all">
    <link rel="stylesheet" type="text/css" href="/Application/Admin/Static/css/module.css">
    <link rel="stylesheet" type="text/css" href="/Public/assets/css/style.css" media="all">
    <link rel="stylesheet" type="text/css" href="/Application/Admin/Static/css/default_color.css" media="all">
    <link rel="stylesheet" type="text/css" href="/Public/toastr/toastr.css" media="all">
    <link rel="stylesheet" type="text/css" href="/Public/dropdownlist/dropdownlist.css" media="all">
    <!--[if lt IE 9]-->
    <script type="text/javascript" src="/Application/Admin/Static/js/jquery-1.10.2.min.js"></script>
    <!--[endif]-->
    <script type="text/javascript" src="/Application/Admin/Static/js/jquery-2.0.3.min.js"></script>
    
    <style>
        .wf-form-table {
            border-left: 1px solid #E8E8E8;
            border-right: 1px solid #E8E8E8;
        }
    </style>

</head>
<body>
    
    <!-- 内容区 -->
    <div id="content">
        
    <div class="tw-layout">
        <div class="tw-list-hd">用户数据授权</div>
        <form action="/index.php/Admin/Member/dataAuth/id/2" method="post" class="ajaxForm">
            <div class="tw-list-wrap tw-edit-wrap">
                <table class="wf-form-table">
                    <colgroup>
                        <col width="15%">
                        <col width="35%">
                        <col width="15%">
                        <col width="35%">
                    </colgroup>
                    <tbody>
                        <tr>
                            <th colspan="4" class="information"><div class="fl offset">用户权限</div></th>
                        </tr>
                        <tr>
                            <th>属性配置权限：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="attribute_configuration" id="isAttributeConfigurationTrue"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="attribute_configuration" id="isAttributeConfigurationFalse" checked="checked"> 否
                                </label>
                                <?php if($info['attribute_configuration'] == 1): ?><script>
                                        $('#isAttributeConfigurationTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td>
                            <!-- <th>升级方式（强制升级）：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="force_upgrade" id="isForceUpgradeTrue"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="force_upgrade" id="isForceUpgradeFalse" checked="checked"> 否
                                </label>
                                <?php if($info['force_upgrade'] == 1): ?><script>
                                        $('#isForceUpgradeTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td> -->
                        </tr> 
                        <tr>
                            <!-- <th>升级方式（静默升级）：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="silent_upgrade" id="isSilentUpgradeTrue"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="silent_upgrade" id="isSilentUpgradeFalse" checked="checked"> 否
                                </label>
                                <?php if($info['silent_upgrade'] == 1): ?><script>
                                        $('#isSilentUpgradeTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td> -->
                            <th>升级方式（提示升级）：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="tip_upgrade" id="isTipUpgradeTrue"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="tip_upgrade" id="isTipUpgradeFalse" checked="checked"> 否
                                </label>
                                <?php if($info['tip_upgrade'] == 1): ?><script>
                                        $('#isTipUpgradeTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td>
                        </tr> 
                        <tr>
                            <th colspan="4" class="information"><div class="fl offset">机器码权限设置</div></th>
                        </tr>
                        <tr>
                            <th>用户机器码个数：</th>
                            <td colspan="3">
                                <input type="text" class="text input-5x" name="machine_code_num" value="<?php echo ($info['machine_code_num']); ?>" placeholder="必须输入数字">
                            </td>
                        </tr> 
                    
                        <tr>
                            <th colspan="4" class="information"><div class="fl offset">日志权限</div></th>
                        </tr>
                        <tr>
                            <th>界面切换权限：</th>
                            <td colspan="3">
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="interface_switch" id="isInterfaceSwitchTrue"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="interface_switch" id="isInterfaceSwitchFalse" checked="checked"> 否
                                </label>
                                <?php if($info['interface_switch'] == 1): ?><script>
                                        $('#isInterfaceSwitchTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td>
                        </tr> 
                        <tr>
                            <th>日志权限（数据个数）：</th>
                            <td>
                                <input type="text" class="text input-5x" name="data_num" value="<?php echo ($info['data_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                            <th>日志权限（发送间隔天数）：</th>
                            <td>
                                <input type="text" class="text input-5x" name="send_num" value="<?php echo ($info['send_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                        </tr> 
                        <tr>
                            <th colspan="4" class="information"><div class="fl offset">商品分类权限</div></th>
                        </tr>
                        <tr>
                            <th>商品属性个数：</th>
                            <td>
                                <input type="text" class="text input-5x" name="goods_attribute_num" value="<?php echo ($info['goods_attribute_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                            <!-- <th>一级商品分类个数：</th>
                            <td>
                                <input type="text" class="text input-5x" name="goods_type_num_1" value="<?php echo ($info['goods_type_num_1']); ?>" placeholder="必须输入数字" id="username">
                            </td> -->
                        </tr> 
                        <tr>
                            <th>二级商品分类个数：</th>
                            <td>
                                <input type="text" class="text input-5x" name="goods_type_num_2" value="<?php echo ($info['goods_type_num_2']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                            <th>三级商品分类个数：</th>
                            <td>
                                <input type="text" class="text input-5x" name="goods_type_num_3" value="<?php echo ($info['goods_type_num_3']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                        </tr> 
                        <tr>
                            <th>四级商品分类个数：</th>
                            <td>
                                <input type="text" class="text input-5x" name="goods_type_num_4" value="<?php echo ($info['goods_type_num_4']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                            <!-- <th>五级商品分类个数：</th>
                            <td>
                                <input type="text" class="text input-5x" name="goods_type_num_5" value="<?php echo ($info['goods_type_num_5']); ?>" placeholder="必须输入数字" id="username">
                            </td> -->
                        </tr> 
                        
                        <tr>
                            <th colspan="4" class="information"><div class="fl offset">模板编辑权限</div></th>
                        </tr>
                        <tr>
                            <th>轮播图模板图片数量：</th>
                            <td>
                                <input type="text" class="text input-5x" name="carousel_num" value="<?php echo ($info['carousel_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                            <th>商品详情图片数量：</th>
                            <td>
                                <input type="text" class="text input-5x" name="goods_detail_photo_num" value="<?php echo ($info['goods_detail_photo_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                            <!-- <th>商品推荐个数：</th>
                            <td>
                                <input type="text" class="text input-5x" name="goods_recommendation_num" value="<?php echo ($info['goods_recommendation_num']); ?>" placeholder="必须输入数字" id="username">
                            </td> -->
                        </tr> 
                        <tr>
                            <!-- <th>自定义模板数量：</th>
                            <td>
                                <input type="text" class="text input-5x" name="custom_template_num" value="<?php echo ($info['custom_template_num']); ?>" placeholder="必须输入数字" id="username">
                            </td> -->
                            <th>互相引流个数：</th>
                            <td>
                                <input type="text" class="text input-5x" name="drainage_num" value="<?php echo ($info['drainage_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                            <th>背景介绍模板（标题数量）：</th>
                            <td>
                                <input type="text" class="text input-5x" name="interface_template_num" value="<?php echo ($info['interface_template_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>

                        </tr> 
                        <tr>
                            
                            <th>实例分享模板（分类数量）：</th>
                            <td>
                                <input type="text" class="text input-5x" name="interface_switch_num" value="<?php echo ($info['interface_switch_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                            <th>解决方案（标题数量）：</th>
                            <td>
                                <input type="text" class="text input-5x" name="olution_num" value="<?php echo ($info['olution_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                        </tr> 
                        <tr>
                            
                            
                        </tr> 
                        <tr>
                            <th>解决方案 （视频数量）：</th>
                            <td>
                                <input type="text" class="text input-5x" name="solution_video_num" value="<?php echo ($info['solution_video_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                            <th>解决方案（图片数量）：</th>
                            <td>
                                <input type="text" class="text input-5x" name="olution_photo_num" value="<?php echo ($info['olution_photo_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                        </tr> 
                        <tr>
                            <th>实例分享模板（视频权限）：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="interface_switch_video_access" id="isInterfaceSwitchVideoAccessTrue" onclick="show_value(this.value, 'interface_switch_video_access')"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="interface_switch_video_access" id="isInterfaceSwitchVideoAccessFalse" onclick="show_value(this.value, 'interface_switch_video_access')" checked="checked"> 否
                                </label>
                                <?php if($info['interface_switch_video_access'] == 1): ?><script>
                                        $('#isInterfaceSwitchVideoAccessTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td>
                            <th class="interface-switch-video-access">实例分享模板（视频数量）：</th>
                            <td class="interface-switch-video-access">
                                <input type="text" class="text input-5x" name="interface_switch_video_num" value="<?php echo ($info['interface_switch_video_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                        </tr> 
                        <tr>
                            <th>实例分享模板（图片权限）：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="interface_switch_photo_access" id="isInterfaceSwitchPhotoAccessTrue" onclick="show_value(this.value, 'interface_switch_photo_access')"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="interface_switch_photo_access" id="isInterfaceSwitchPhotoAccessFalse" onclick="show_value(this.value, 'interface_switch_photo_access')" checked="checked"> 否
                                </label>
                                <?php if($info['interface_switch_photo_access'] == 1): ?><script>
                                        $('#isInterfaceSwitchPhotoAccessTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td>
                            <th class="interface-switch-photo-access">实例分享模板（图片数量）：</th>
                            <td class="interface-switch-photo-access">
                                <input type="text" class="text input-5x" name="interface_switch_photo_num" value="<?php echo ($info['interface_switch_photo_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                        </tr> 
                        <tr>
                            <th>背景介绍模板（视频权限）：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="background_template_video_access" id="isBackgroundTemplateVideoAccessTrue" onclick="show_value(this.value, 'background_template_video_access')"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="background_template_video_access" id="isBackgroundTemplateVideoAccessFalse" onclick="show_value(this.value, 'background_template_video_access')" checked="checked"> 否
                                </label>
                                <?php if($info['background_template_video_access'] == 1): ?><script>
                                        $('#isBackgroundTemplateVideoAccessTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td>
                            <th class="background-template-video-access">背景介绍模板（视频数量）：</th>
                            <td class="background-template-video-access">
                                <input type="text" class="text input-5x" name="interface_template_video_num" value="<?php echo ($info['interface_template_video_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                        </tr> 
                        <tr>
                            <th>背景介绍模板（图片权限）：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="interface_template_photo_access" id="isInterfaceTemplatePhotoAccessTrue" onclick="show_value(this.value, 'interface_template_photo_access')"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="interface_template_photo_access" id="isInterfaceTemplatePhotoAccessFalse" onclick="show_value(this.value, 'interface_template_photo_access')" checked="checked"> 否
                                </label>
                                <?php if($info['interface_template_photo_access'] == 1): ?><script>
                                        $('#isInterfaceTemplatePhotoAccessTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td>
                            <th class="interface-template-photo-access">背景介绍模板（图片数量）：</th>
                            <td class="interface-template-photo-access">
                                <input type="text" class="text input-5x" name="interface_template_photo_num" value="<?php echo ($info['interface_template_photo_num']); ?>" placeholder="必须输入数字" id="username">
                            </td>
                        </tr> 
                        
                        <tr>
                            <th colspan="4" class="information"><div class="fl offset">VR展示权限</div></th>
                        </tr>
                        <tr>
                            <th>VR移动 ：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="vr_move" id="isVrMoveTrue"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="vr_move" id="isVrMoveFalse" checked="checked"> 否
                                </label>
                                <?php if($info['vr_move'] == 1): ?><script>
                                        $('#isVrMoveTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td>
                            <th>VR旋转 ：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="vr_rotate" id="isVrRotateTrue"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="vr_rotate" id="isVrRotateFalse" checked="checked"> 否
                                </label>
                                <?php if($info['vr_rotate'] == 1): ?><script>
                                        $('#isVrRotateTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td>
                        </tr> 
                        <tr>
                            <th>缩放 ：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="vr_zoom" id="isVrZoomTrue"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="vr_zoom" id="isVrZoomFalse" checked="checked"> 否
                                </label>
                                <?php if($info['vr_zoom'] == 1): ?><script>
                                        $('#isVrZoomTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td>
                            <th>换色 ：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="vr_change_color" id="isVrChangeColorTrue"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="vr_change_color" id="isVrChangeColorFalse" checked="checked"> 否
                                </label>
                                <?php if($info['vr_change_color'] == 1): ?><script>
                                        $('#isVrChangeColorTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td>
                        </tr> 
                        <tr>
                            <th>收藏夹 ：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="vr_favorites" id="isVrFavoritesTrue"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="vr_favorites" id="isVrFavoritesFalse"checked="checked"> 否
                                </label>
                                <?php if($info['vr_favorites'] == 1): ?><script>
                                        $('#isVrFavoritesTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td>
                            <th>户型风格切换 ：</th>
                            <td>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="1" name="vr_layout_style_switch" id="isVrLayoutStyleSwitchTrue"> 是
                                </label>
                                <label class="wf-form-label-rc">
                                    <input type="radio" value="0" name="vr_layout_style_switch" id="isVrLayoutStyleSwitchFalse" checked="checked"> 否
                                </label>
                                <?php if($info['vr_layout_style_switch'] == 1): ?><script>
                                        $('#isVrLayoutStyleSwitchTrue').attr('checked', 'checked');
                                    </script><?php endif; ?>
                            </td>
                        </tr> 
                        
	            </tbody>
                </table>
            </div>
            <div class="tw-tool-bar-bot">
                <input type="hidden" name="id" value="<?php echo ($info['id']); ?>" >
                <input type="hidden" name="member_id" value="<?php echo ($id); ?>" >
                <button type="submit" class="tw-act-btn-confirm"  >提交</button>
                <button type="button" onclick="goback()" class="tw-act-btn-cancel">返回</button>
            </div>
        </form>
	</div>

    </div>
    <!-- /内容区 -->
    <!--[if gte IE 9]><!-->
    <script type="text/javascript" src="/Application/Admin/Static/js/jquery.mousewheel.js"></script>
    <!--<![endif]-->
    <script type="text/javascript" src="/Public/toastr/toastr.js" ></script>
    <script type="text/javascript" src="/Public/assets/js/wf-list.js" ></script>
    <script type="text/javascript" src="/Public/assets/plugins/layer-v2.0/layer/layer.js"></script>
    <script type="text/javascript" src="/Public/assets/plugins/laydate-v1.1/laydate/laydate.js"></script>
    <script type="text/javascript" src="/Public/assets/js/common.js"></script>
<!--    <script type="text/javascript" src="/Public/dropdownlist/dropdownlist.js"></script>-->
    <script type="text/javascript" src="/Application/Admin/Static/js/common.js"></script>
    <script>
        // 定义全局变量
        RECYCLE_URL = "<?php echo U('recycle');?>"; // 默认逻辑删除操作执行的地址
        RESTORE_URL = "<?php echo U('restore');?>"; // 默认逻辑删除恢复执行的地址
        DELETE_URL = "<?php echo U('del');?>"; // 默认删除操作执行的地址
        UPLOAD_IMG_URL = "<?php echo U('uploadImg');?>"; // 默认上传图片地址
        UPLOAD_FIELD_URL = "<?php echo U('uploadField');?>"; // 默认上传图片地址
        DELETE_FILE_URL = "<?php echo U('delFile');?>"; // 默认删除图片执行的地址
        CHANGE_STAUTS_URL = "<?php echo U('changeDisabled');?>"; // 修改数据的启用状态
        CROPPER_IMG_URL = "<?php echo U('/Admin/Ajax/cropper');?>";//裁剪图片地址
    </script>
    
    <script>

        // 刚进入页面的时候判断属性值是否显示
        $(function(){
            if ("<?php echo ($info['interface_switch_video_access']); ?>" === "1") {
                $(".interface-switch-video-access").show();                
            } else {
                $(".interface-switch-video-access").hide();   
            }    
            if ("<?php echo ($info['interface_switch_photo_access']); ?>" === "1") {
                $(".interface-switch-photo-access").show();                
            } else {
                $(".interface-switch-photo-access").hide();   
            }    
            if ("<?php echo ($info['background_template_video_access']); ?>" === "1") {
                $(".background-template-video-access").show();                
            } else {
                $(".background-template-video-access").hide();   
            }    
            if ("<?php echo ($info['interface_template_photo_access']); ?>" === "1") {
                $(".interface-template-photo-access").show();                
            } else {
                $(".interface-template-photo-access").hide();   
            }    
        }); 
        
        // js设置属性值是否显示
        function show_value(type, status){

            if ( status == 'interface_switch_video_access' ) {
                if (type == 1) {
                    $(".interface-switch-video-access").show();                
                } else if(type == 0)  {
                    $(".interface-switch-video-access").hide(); 
                }
            } else if( status == 'interface_switch_photo_access' ) {
                if (type == 1) {
                    $(".interface-switch-photo-access").show();                
                } else if(type == 0)  {
                    $(".interface-switch-photo-access").hide(); 
                }
            } else if( status == 'background_template_video_access' ) {
                if (type == 1) {
                    $(".background-template-video-access").show();                
                } else if(type == 0)  {
                    $(".background-template-video-access").hide(); 
                }
            } else if( status == 'interface_template_photo_access' ) {
                if (type == 1) {
                    $(".interface-template-photo-access").show();                
                } else if(type == 0)  {
                    $(".interface-template-photo-access").hide(); 
                }
            } else {
                alert("错误数据！");
            }
        }
   
    </script>

</body>
</html>