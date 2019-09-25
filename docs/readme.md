fcupUpload分片上传插件是使用文档说明

fcupUpload上传插件是使用fcup.js，结合php 后端实现分片上传，断点续传等功能的插件。其中的添加了上传效果，错误显示等弹框提示，以下是引入以及参数说明。

引入说明
由于是基于jquery的所以在引入前先引入jquery，再引入fcupUpload.js

例如：
<script src="/js/upload/jquery.min.js"></script>
<script src="环境地址/js/upload/fcupUpload.js"></script>

使用及参数说明
fcupUpload.fcupInit(“upShardSize”,"upMaxSize","upType","serverUrl","project",function (data) {
        var url = data.url;
});

参数说明：
upShardSize：切片大小,(单次上传最大值)单位M;
upMaxSize：传文件大小,单位M，不设置不限制;
upType：文件上传类型；
serverUrl： 环境地址 必传，（在该demo项目不传默认项目路径）;
project 项目名称 不传默认upload目录;
回调函数中的data：是返回的上传成功后文件的路径以及文件名；

例如：

    $(".start_upload").click(function () {
        var click = $(this);
        fcupUpload.fcupInit("1","2","jpg,png,gif","","demo",function (data) {
            var info = '文件地址：'+data.url+'；文件名：'+data.name;
            click.parent().find(".info").html(info);
        });
});

    $("#start_upload1").click(function () {
        var click = $(this);
        fcupUpload.fcupInit("10","","","","",function (data) {
            var info = '文件地址'+data.url+'；文件名：'+data.name;
            click.parent().find(".info").html(info);
        });
    });