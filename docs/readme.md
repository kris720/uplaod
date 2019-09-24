fcupUpload分片上传插件是使用文档说明

fcupUpload上传插件是使用fcup.js，结合php 后端实现分片上传，断点续传等功能的插件。其中的添加了上传效果，错误显示等弹框提示，以下是引入以及参数说明。

引入说明
由于是基于jquery的所以在引入前先引入jquery，再引入fcupUpload.js

例如：
<script src="/js/upload/jquery.min.js"></script>
<script src="/js/upload/fcupUpload.js"></script>

使用及参数说明
fcupUpload.fcupInit("domId",upShardSize,"upMaxSize","upType",function (data) {
        var url = data;
});

参数说明：
domId：触发上传的按钮的id名；
upShardSize：切片大小,(单次上传最大值)单位M;
upMaxSize：传文件大小,单位M，不设置不限制;
upType：文件上传类型；
回调函数中的data：是返回的上传成功后文件的路径；

例如：
 fcupUpload.fcupInit("start_upload","","","",function (data) {
        var url = data+',';
        $("#url").append(url);
});

  fcupUpload.fcupInit("start_upload1","","","",function (data) {
        var url = data+',';
        $("#url").append(url);
});


