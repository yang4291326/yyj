/**
 * 上传图片
 * btnUpload: 上传按钮的ID
 * inputImg: 保存图片路径的input ID
 * savePath: 指定存放在服务器上的路径
 * item: 一个页面上传多个图片时的标识
 **/
function ajaxUpload(btnUpload, inputImg, savePath, item){

    var filename = ""; // 旧文件的文件名
    var oldImg = $(inputImg).val();
    if($.trim(oldImg) != "" && oldImg.indexOf('/') != -1){
        var arr = oldImg.split('/');
        var file = arr[arr.length - 1];
        filename = file.split('.')[0];
    }
    if(!savePath) savePath = "";  // 要存放的路径
    
    new AjaxUpload($(btnUpload), {
        action: UPLOAD_FIELD_URL,  
        name: 'photo',   //这相当于<input type = "file" name = "photo"/> 
        data:{},  //附加参数值
        dataType : 'text',
        onSubmit : function(file, ext){
            if(!(ext && /^(jpg|png|gif|jpeg|jn|unity3d)$/.test(ext.toLowerCase()))){
                toastr.error('格式不支持');  
                return false;
            }
            this.setData({'oldImg':filename, 'savePath':savePath});
            this.disable();
        },
        onComplete: function(file, response){
            json = $.parseJSON($(response).text());
            if(json['status'] == true || json['status'] == 1 || json['status'].toString() == '1'){
                $("#img_" + item).html(json['src']);
                $("#img" + item).val(json['src']);
                $('#btn_delete_' + item).show();
                $('#btnUpload' + item).hide();
            }else{
                toastr.error(json['msg'])
            }
            this.enable(); 
        }  
    }); 
}


/**
 * 删除
 **/
function delFile(file, item, message, showpic){
    if(! message) message = "确认删除吗? 此步骤无法恢复!"
    if(! confirm(message)) return false;
    var url;
    if (DELETE_FILE_URL.indexOf('?') > -1) {
        url = DELETE_FILE_URL + "&file="+ file;
    } else {
        url = DELETE_FILE_URL + "?file="+ file;
    }
    if (item != '') {
        var strs = new Array();
        strs = item.split('_');
    }
    $.get(url, function(data){
        if(data.status == 1){
            $('#img_'+item).html('');
            $('#img' + item).val('').hide();
            $('#btn_delete_' + item).hide();
            $('#btnUpload' + item).show();
            toastr.success(data.msg);
        }else{
            toastr.error(data.msg)
        }
    })
}

//追加户型
var m = parseInt(layout_num) + 1;
if (layout_num != 0) {
  for (var p = 1; p < m; p++) {
      for(var i = 1; i <= 2; i++){
          ajaxUpload('#btnUpload'+ i +'_'+ p +'', $('#img'+ i +'_'+ p +''), 'VRModel', ''+ i +'_'+ p +'');
      }
  }
}
function addUnit(){
    var newRow = "<tr class='unit-tr"+m+"'><td colspan='4'><div class='td-line'></div></td></tr>"
              + "<tr class='unit-tr"+m+"'><th valign='top'>户型名称:</th><td colspan='3'><input type='text' name='vr[name]["+ m +"]' class='text input-large' placeholder='输入户型名称'/>"
              + "<input class='btn btn-default btn-xs' type='button' value='删除' onclick='delLineModel(this, "+ m +", \"unit-tr\")'/></td></tr>"
              + "<tr class='unit-tr"+m+"'><th>户型图标:</th><td colspan='3'><span id='img_1_"+ m +"'/></span><input type='hidden' name='vr[layout_photo_path]["+ m +"]' id='img1_"+m+"'/><input class='btn btn-default btn-xs' type='button' value='资源选择' id='btnUpload1_"+ m +"'/><input class='btn btn-danger btn-xs del-img-btn' type='button' value='删除' id='btn_delete_1_"+ m +"' del-img-id='1_"+ m +"' style='display:none'/></td></tr>"
              + "<tr class='unit-tr"+m+"'><th>户型模型:</th><td colspan='3'><span id='img_2_"+ m +"'/></span><input type='hidden' name='vr[layout_path]["+ m +"]' id='img2_"+m+"'/><input class='btn btn-default btn-xs' type='button' value='资源选择' id='btnUpload2_"+ m +"'/><input class='btn btn-danger btn-xs del-img-btn' type='button' value='删除' id='btn_delete_2_"+ m +"' del-img-id='2_"+ m +"' style='display:none'/></td></tr>"
              + "<tr class='unit-tr"+m+"'><th valign='top'>户型风格:</th><td><input type='text' name='vr[layout_style]["+ m +"]' class='text input-5x' placeholder='输入户型风格'/></td><th valign='top'>户型场景:</th><td><input type='text' name='vr[layout_scene]["+ m +"]' class='text input-5x' placeholder='输入户型场景'/></td></tr>"
              + "<tr class='unit-tr"+m+"'><th valign='top'>初始位置:</th><td><input type='text' name='vr[initial_position]["+ m +"]' class='text input-5x' placeholder='输入初始位置'/></td><th valign='top'>初始方向:</th><td><input type='text' name='vr[initial_direction]["+ m +"]' class='text input-5x' placeholder='输入初始方向'/></td></tr>"
              + "<tr class='unit-tr"+m+"'><th valign='top'>是否默认:</th><td colspan='3'><label class='radio'><input type='radio' name='vr[is_default_hx]["+ m +"]' value='1'/>是</label> <label class='radio'><input type='radio' name='vr[is_default_hx]["+ m +"]' value='0' checked/>否</label></td></tr>";
    $("#goods-unit-table tr:last").after(newRow);
    for (var i = 1; i <= 2; i++) {
        ajaxUpload('#btnUpload'+ i +'_'+ m +'', $('#img'+ i +'_'+ m ), 'VRModel', ''+ i +'_'+ m +'');
    }
    m++;
}

//追加商品模型类型
var n = parseInt(resource_num) + 1;
if (resource_num != 0) {
  for (var p = 1; p < n; p++) {
      for(var i = 3; i <= 6; i++){
          ajaxUpload('#btnUpload'+ i +'_'+ p +'', $('#img'+ i +'_'+ p +''), 'VRModel', ''+ i +'_'+ p +'');
      }
  }
}
function addUnitAttr(){
    var newRow = "<tr class='unit-attr-tr"+n+"'><td colspan='2'><div class='td-line'></div></td></tr>"
              + "<tr class='unit-attr-tr"+n+"'><th>图标资源:</th><td><span id='img_5_"+ n +"'/></span><input type='hidden' name='vr[photo_resource_path]["+ n +"]' id='img5_"+ n +"'/><input class='btn btn-default btn-xs' type='button' value='资源选择' id='btnUpload5_"+ n +"'/><input class='btn btn-danger btn-xs del-img-btn' type='button' value='删除' id='btn_delete_5_"+ n +"' del-img-id='5_"+ n +"' style='display:none'/></td></tr>"
              + "<input class='btn btn-default btn-xs' type='button' value='删除' onclick='delLineModel(this, "+ n +", \"unit-attr-tr\")'/></td></tr>"
              + "<tr class='unit-attr-tr"+n+"'><th valign='top'>组号:</th><td><input type='text' name='vr[group_no]["+ n +"]' class='text input-large' placeholder='请输入组号'/></td></tr>"
              + "<tr class='unit-attr-tr"+n+"'><th>贴图:</th><td><span id='img_3_"+ n +"'/></span><input type='hidden' name='vr[map_path]["+ n +"]' id='img3_"+ n +"'/><input class='btn btn-default btn-xs' type='button' value='资源选择' id='btnUpload3_"+ n +"'/><input class='btn btn-danger btn-xs del-img-btn' type='button' value='删除' id='btn_delete_3_"+ n +"' del-img-id='3_"+ n +"' style='display:none'/></td></tr>"
              + "<tr class='unit-attr-tr"+n+"'><th>法线贴图:</th><td><span id='img_4_"+ n +"'/></span><input type='hidden' name='vr[normal_map_path]["+ n +"]' id='img4_"+ n +"'/><input class='btn btn-default btn-xs' type='button' value='资源选择' id='btnUpload4_"+ n +"'/><input class='btn btn-danger btn-xs del-img-btn' type='button' value='删除' id='btn_delete_4_"+ n +"' del-img-id='4_"+ n +"' style='display:none'/></td></tr>"
              + "<tr class='unit-attr-tr"+n+"'><th>材质球:</th><td><span id='img_6_"+ n +"'/></span><input type='hidden' name='vr[material_ball]["+ n +"]' id='img6_"+ n +"'/><input class='btn btn-default btn-xs' type='button' value='资源选择' id='btnUpload6_"+ n +"'/><input class='btn btn-danger btn-xs del-img-btn' type='button' value='删除' id='btn_delete_6_"+ n +"' del-img-id='6_"+ n +"' style='display:none'/></td></tr>"
              + "<tr class='unit-attr-tr"+n+"'><th valign='top'>材质球名称:</th><td><input type='text' name='vr[material_ball_name]["+ n +"]' class='text input-large' placeholder='输入材质球名称'/></td></tr>"
              + "<tr class='unit-attr-tr"+n+"'><th valign='top'>是否默认:</th><td><label class='radio'><input type='radio' name='vr[is_default_mx]["+ n +"]' value='1'/>是</label> <label class='radio'><input type='radio' name='vr[is_default_mx]["+ n +"]' value='0' checked/>否</label></td></tr>";

    $("#goods-unit-attr-table tr:last").after(newRow); 
    for (var i = 3; i <= 6; i++) {
        ajaxUpload('#btnUpload'+ i +'_'+ n+'', $('#img'+ i +'_'+ n), 'VRModel', ''+ i +'_'+ n +'');
    }
    n++;
}  

/**
 * 删除整行数据
 **/
function delLineModel(obj, k, type){
    message = "确认删除吗? 此步骤无法恢复!"
    if(! confirm(message)) return false;
    $('.'+type+k+'').remove();
}