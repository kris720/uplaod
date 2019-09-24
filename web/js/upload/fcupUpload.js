/**
 *
 * @type {{createHtml: fcup_alert.createHtml, fcupInit: fcup_alert.fcupInit}}
 */
;
var fcupUpload = {
    isStop:false,
    createHtml:function(serverUrl){
        if($(".upload-mask").length<=0){
            $("body").before(' <link rel="stylesheet" href="'+serverUrl+'/css/upload/upload.css">');
            var html = '<div class="upload-mask">';
            html +='<div class="upload-content uploading">';
            html +='<div class="tip-type">';
            html +='<p class="progressNumb">0%</p>';
            html +='<div class="progress-box"><span class="progress"></span></div>';
            html +='</div>';
            html +='<div class="upload-btn-block">';
            html +='<span class="upload-btn gray-btn cancelBtn gray-btn">取消上传</span>';
            html +='</div>';
            html +='</div>';
            html +='<div class="upload-content error" >';
            html +='<div class="tip-txt-box">';
            html +='<span class="tip-txt">上传失败</span>';
            html +='</div>';
            html +='<div class="upload-btn-block">';
            html +='<span class="upload-btn gray-btn closeBtn gray-btn">知道了</span>';
            html +='</div>';
            html +='</div>';
            html +='</div>';
            $("body").append(html);
            fcupUpload.bindEvent();
        }
    },
    //上传进度
    uploading:function (progress) {
        $(".upload-mask").show();
        $(".upload-content").hide();
        $(".uploading").show();
        $(".progressNumb").html(progress);
        $(".progress").css("width",progress);
    },
    //错误提示
    errorTip:function (txt) {
        $(".upload-mask").show();
        $(".upload-content").hide();
        $(".error").show();
        $(".error .tip-txt").html(txt);
    },
    //关闭弹窗
    closeMask:function () {
        $(".upload-mask").hide();
        $(".progress").css("width",0);
    },
//    fc分片上传初始化
    fcupInit:function (domId,upShardSize,upMaxSize,upType,serverUrl,project,fcupUploadCallback) {
        fcupUpload.createHtml(serverUrl);
        var upShardSize = upShardSize ==""?"2":upShardSize;
        $.getScript(serverUrl+"/js/upload/jquery.fcup.js",function(){
            $.fcup({
                upId: domId, //上传dom的id
                upShardSize: upShardSize, //切片大小,(单次上传最大值)单位M，默认2M
                upMaxSize: upMaxSize, //上传文件大小,单位M，不设置不限制
                upUrl: serverUrl+'/upload/index/upload', //文件上传接口
                upType: upType, //jpg,png,jpeg,gif 上传类型检测,用,号分割
                //接口返回结果回调，根据结果返回的数据来进行判断，可以返回字符串或者json来进行判断处理
                project:project,//项目名称
                upCallBack: function (res) {
                    // 状态
                    var status = res.status;
                    // 信息
                    var msg = res.msg;
                    // url
                    var url = res.url + "?" + Math.random();

                    // 接口返回错误
                    if (status == 0) {
                        // 停止上传并且提示信息
                        $.upStop(msg);
                    }

                    // 还在上传中
                    if (status == 1) {
                        console.log(msg);
                    }
                    // 已经完成了
                    if (status == 2) {
                        //清空input的值
                        fcupUploadCallback(url);
                    }
                    // 已经存在提示极速上传完成
                    if (status == 3) {
                        this.i2 = res.finish_spot;
                        this.i3 = res.finish_spot;
                        fcupUploadCallback(url);
                    }
                    // 断点续传
                    if (status == 4) {
                        this.i2 = res.finish_spot;
                        this.i3 = Number(res.finish_spot)+1;
                    }
                },

                // 上传过程监听，可以根据当前执行的进度值来改变进度条
                upEvent: function (num) {
                    // num的值是上传的进度，从1到100
                    if(fcupUpload.isStop == false){
                        fcupUpload.uploading(num+'%');
                        if(num == 100){
                            setTimeout(function () {
                                fcupUpload.closeMask();
                            },1000);
                        }
                    }
                },

                // 发生错误后的处理
                upStop: function (errmsg) {
                    this.is_stop = 1;
                    fcupUpload.errorTip(errmsg);
                },

                // 开始上传前的处理和回调,比如进度条初始化等
                upStart: function () {
                    fcupUpload.uploading("0%");
                }
            });
        });

    },
    //事件绑定
    bindEvent:function () {
        //关闭弹窗
        $("body").on("click",".closeBtn",function () {
            fcupUpload.closeMask();
        });
        //取消上传
        $("body").on("click",".cancelBtn",function () {
            $.upCancel();
            fcupUpload.isStop = true;
            fcupUpload.closeMask();
        });
    }
};


