<?php

namespace Upload\Controller;

use Zero\Service\UserInfoService;
use Plume\Core\Controller;
use Plume\Core\Service;
use Plume\Core\Dao;
use Zero\Dao\UserInfoDao;

class IndexController extends Controller
{

    public function __construct($app)
    {
        //使用自定义Service
        parent::__construct($app);
    }


    public function demoAction()
    {
        return $this->result(array('title' => "fcup demo"))->response();
    }

    /**
     * @return mixed
     * fcup 上传接口
     */
    public function uploadAction()
    {
        // 设置跨域头
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:PUT,POST,GET,DELETE,OPTIONS');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        header('Content-Type:application/json; charset=utf-8');
        $this->api();
        $file = isset($_FILES['file_data']) ? $_FILES['file_data'] : null; //分段的文件
        $name = isset($_POST['file_name']) ? $_POST['file_name'] : null; //要保存的文件名
        $total = isset($_POST['file_total']) ? $_POST['file_total'] : 0; //总片数
        $index = isset($_POST['file_index']) ? $_POST['file_index'] : 0; //当前片数
        $md5 = isset($_POST['file_md5']) ? $_POST['file_md5'] : 0; //文件的md5值
        $size = isset($_POST['file_size']) ? $_POST['file_size'] : null; //文件大小
        $project = isset($_POST['project']) ? $_POST['project'] : null; //项目名称
        $info = pathinfo($name);
        $ext = isset($info['extension']) ? $info['extension'] : '';// 文件名称
        $fileName = $md5 . '.'.$ext;//生成文件名称 用md5来给文件命名，这样可以减少冲突
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://'; //根据当前端口，判断是http还是https
        if(!empty($project)){
            $path = $_SERVER['DOCUMENT_ROOT'] . '/upload/'.$project;
            if(!is_dir($path)){
                mkdir($path, 0700, true);
            }
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/'.$project.'/' . $fileName;
            $fileUrl = $sys_protocal.$_SERVER['HTTP_HOST'] . '/upload/'.$project.'/' . $fileName;
        }else{
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $fileName;
            $fileUrl = $sys_protocal.$_SERVER['HTTP_HOST'] . '/upload/'.$fileName;
        }
        // 简单的判断文件类型
        $imgarr = $this->getConfigValue("fileType");
        if (!in_array($ext, $imgarr)) {
            return $this->result(array('status' => "0", 'msg' => '文件类型出错'))->json()->response();
        }
        if (!$file || !$name) {
            return $this->result(array('status' => "0", 'msg' => '没有文件'))->json()->response();
        }

        // 这里判断有没有上传的文件流
        if ($file['error'] == 0) {
            $is_exists = file_exists($filePath);//是否存在
            $res = $this->fileSpot($fileName);//redis中的状态

            if ($is_exists){
                if ($res == false || $res == -1){
                    $this->recordSpot($fileName,-1);
                    return $this->result(array('status' => "3", 'msg' => '极速上传完成', 'url' => $fileUrl, 'finish_spot' => $total,'name'=>$name))->json()->response();
                }
                if ($res >= $index){
                    return $this->result(array('status' => "4", 'msg' => '正在计算上次上传进度', 'finish_spot'=>$res))->json()->response();
                }
            }else{
                if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                    return $this->result(array('status' => "0", 'msg' => '无法移动文件'))->json()->response();
                }
                // 片数相等，等于完成了
                if ($index == $total) {
                    $this->recordSpot($fileName,-1);
                    return $this->result(array('status' => "2", 'msg' => '上传完成', 'url' => $fileUrl,'name'=>$name))->json()->response();
                }
                $this->recordSpot($fileName,$index);
                return $this->result(array('status' => "1", 'msg' => '正在上传'))->json()->response();
            }

            // 如果当前片数小于等于总片数,就在文件后继续添加
            if ($index <= $total) {
                $content = file_get_contents($file['tmp_name']);
                if (!file_put_contents($filePath, $content, FILE_APPEND)) {
                    return $this->result(array('status' => "0", 'msg' => '无法写入文件'))->json()->response();
                }
                // 片数相等，等于完成了
                if ($index == $total) {
                    $this->recordSpot($fileName,-1);
                    return $this->result(array('status' => "2", 'msg' => '上传完成', 'url' => $fileUrl,'name'=>$name))->json()->response();
                }
                $this->recordSpot($fileName,$index);
                return $this->result(array('status' => "1", 'msg' => '正在上传'))->json()->response();
            }else{
                return $this->result(array('status' => "2", 'msg' => '上传片:'.$index.' ,总片数:'.$total))->json()->response();
            }
        } else {
            return $this->result(array('status' => "0", 'msg' => '没有上传文件'))->json()->response();
        }
    }

    /**
     * 裁剪图片上传
     */
    public function tailoringAction(){
        // 设置跨域头
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:PUT,POST,GET,DELETE,OPTIONS');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        header('Content-Type:application/json; charset=utf-8');
        $this->api();
        $img = isset($_POST['images']) ? $_POST['images'] : null; //文件
        $name = isset($_POST['file_name']) ? $_POST['file_name'] : null; //要保存的文件名
        $md5 = isset($_POST['file_md5']) ? $_POST['file_md5'] : 0; //文件的md5值
        $project = isset($_POST['project']) ? $_POST['project'] : null; //项目名称
        $info = pathinfo($name);
        $ext = isset($info['extension']) ? $info['extension'] : '';// 文件名称
        $fileName = $md5 . '.'.$ext;//生成文件名称 用md5来给文件命名，这样可以减少冲突
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://'; //根据当前端口，判断是http还是https
        if(!empty($project)){
            $path = $_SERVER['DOCUMENT_ROOT'] . '/upload/'.$project;
            if(!is_dir($path)){
                mkdir($path, 0700, true);
            }
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/'.$project.'/' . $fileName;
            $fileUrl = $sys_protocal.$_SERVER['HTTP_HOST'] . '/upload/'.$project.'/' . $fileName;
        }else{
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $fileName;
            $fileUrl = $sys_protocal.$_SERVER['HTTP_HOST'] . '/upload/'.$fileName;
        }
        $result=$this->imgPng($filePath,$img,$fileUrl);
        return $this->result(array('status' => "2", 'msg' => '上传完成', 'url' => $result,'name'=>$name))->json()->response();
    }

    function imgPng($dir,$imgInfo,$fileName){
        if(empty($imgInfo)){
            return '';
        }
        try {
            $base64Info=substr(strstr($imgInfo,','),1);
            $imgInfo=base64_decode($base64Info);
            if(empty($fileName)){
                $fileName=time().'.png';
                $imgUrl='/'.$dir.'/'.$fileName;
            }
            $imgUrl=$fileName;
            file_put_contents($dir,$imgInfo);
            return $imgUrl;
        }catch(\Exception $e) {
            throw new \Exception('图片base64解码异常：'.$e->getMessage(),$e->getCode());
        }
    }
    /**
     * 记录断点
     */
    public function recordSpot($filename, $spot_order){
        $redis = $this->getRedis();

        $key = 'recordSpot:'.$filename;
        $res = $redis->set($key, $spot_order);
        if ($res){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 判断文件是否上传完成,未完成则返回断点位置
     */
    public function fileSpot($filename){
        $redis = $this->getRedis();
        $key = 'recordSpot:'.$filename;
        $res = $redis->get($key);
        return $res;
    }

}