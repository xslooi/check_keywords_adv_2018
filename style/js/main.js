/**
 * 操作JS文件
 */

// ========================================================================
// 函数区
function ShowMsg(msg, type){
    var msgBox = $("#msgBoxContainer");

    if(!!msg){
        if(!!type){
            switch(type){
                case 'success':
                    type = 'green';
                    break;
                case 'error':
                    type = 'red';
                    break;

            }
        }else{
            type = 'red';
        }
    }else{
        msg = '暂无操作';
        if(!type){
            type = 'lightslategray';
        }
    }

    var msgHtml = '<b style="color: '+ type +';">' + msg + '</b>';
    msgBox.html(msgHtml);
    //提示后自动还原
    setTimeout(function () {$("#msgBoxContainer").html('<b style="color: lightslategray;">暂无操作</b>');}, 1500);
}


function customCopy(returnData){
    // 自动复制
    var clipboard = new ClipboardJS('.btn', {
        text: function(trigger){
            return returnData;
        }
    });

    clipboard.on('success', function(e) {
        ShowMsg('替换且复制成功，请直接粘贴（CTRL + V）使用！', 'success');
        $("#sourcebox").val('');
        $("#sourcebox").focus();
    });

    clipboard.on('error', function(e) {
        ShowMsg('复制失败');
    });

    clipboard = null;
}

// ========================================================================

// 页面开始执行
$(function () {

    //清空输入
    $("#siteurl").val('');
    $("#sourcebox").val('');
    $("#siteurl").focus();

    //选择类型事件，显示行业相关词性说明
    $("input:radio").click(function () {
        var selectValue = $(this).val();

        if('normal' == selectValue){
            console.log('一般性关键词！');
        }else{
            //ajax 后台处理
            $.ajax({
                url: 'api.php',
                type: 'POST',
                data: {info: selectValue},
                dataType: 'json',
                async: false,
                beforeSend: function(xhr){
                },
                success: function (data) {
                    if(data.state == 1){
                        var result_html = '';

                        if(data.data){
                            result_html = '<ol>';
                            for(var i=0, len=data.data.length;i < len; i++){
                                result_html += '<li>'+ data.data[i] +'</li>';
                            }
                            result_html += '</ol>';
                        }else{
                            result_html = '<h1 style="color:green;">暂无说明！</h1>';
                        }

                        $("#msgBoxContainer").html(result_html);

                    }
                    else{
                        ShowMsg(data.msg);
                    }
                },
                error: function (xhr) {

                }
            });
        }
    });


    //监听所有button的点击事件
    $("button").click(function () {

        var siteurl = $("#siteurl").val();
        var sourceCode = $("#sourcebox").val();
        var platform = $("input[name='platform']:checked").val();
        var tagName = $(this).val();

        if(!tagName){
            $("#sourcebox").focus();
            return -1;
        }

        if(!sourceCode && !siteurl){
            $("#siteurl").focus();
            ShowMsg('请输入网址或源代码！');
            return -1;
        }

        //ajax 后台处理
        $.ajax({
            url: 'api.php',
            type: 'POST',
            data: {sc: sourceCode, pf: platform, site: siteurl, tn: tagName},
            dataType: 'json',
            async: false,
            beforeSend: function(xhr){
                ShowMsg('正在检测中，请稍等。。。', 'success');
                $("#checkResults").html('<h3 style="color:green;">正在检测中。。。</h3>');
            },
            success: function (data) {
                if(data.state == 1){
                    var result_html = '';

                    if(data.data.length > 0){
                        result_html = '<ol>';
                        for(var i=0, len=data.data.length;i < len; i++){
                            result_html += '<li><b>序号：</b><em class="rs_number">['+ data.data[i].number +']</em> &nbsp; &nbsp; <b>关键字：</b><em class="rs_key">【'+ data.data[i].key +'】</em> <b>匹配词：</b><em class="rs_word">'+ data.data[i].word +'</em></li>';
                        }
                        result_html += '</ol>';
                    }else{
                        result_html = '<h1 style="color:green;">恭喜暂时没有匹配到关键词！</h1>';
                    }

                    $("#checkResults").html(result_html);

                }else if(data.state == 0){
                    ShowMsg(data.msg, 'success');
                }
                else{
                    ShowMsg(data.msg);
                }
            },
            error: function (xhr) {

            }
        });

    });

    //固定的复制内容-元素属性目标值复制
    var clipboard = new ClipboardJS('.fixed');

    clipboard.on('success', function(e) {
        ShowMsg('代码复制成功，请直接粘贴（CTRL + V）使用！', 'success');
        $("#sourcebox").val('');
        $("#sourcebox").focus();
    });

    clipboard.on('error', function(e) {
        ShowMsg('复制失败');
    });

    //固定内容复制-HTML id 元素内text
    var clipboard_hmtl = new ClipboardJS('.fixed_html', {
        target: function(trigger) {
            return document.getElementById("fixed_html_" + trigger.value);
        }
    });

    clipboard_hmtl.on('success', function(e) {
        ShowMsg('代码复制成功，请直接粘贴（CTRL + V）使用！', 'success');
        $("#sourcebox").val('');
        $("#sourcebox").focus();
    });

    clipboard_hmtl.on('error', function(e) {
        ShowMsg('复制失败');
    });
});