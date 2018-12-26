<?php
require_once '../../../vendor/autoload.php';
use RainSunshineCloud\Captcha;
use RainSunshineCloud\CaptchaException;

try{
    //文件上传
    echo Upload::upload('file_input_name')->getFileName();
    //图片上传
    echo Upload::uploadImg('img_input_name')->getFilePath(true);
    //base6464图片上传(使用post)
    Upload::uploadImg('img_input_name_string',true)->getFileName(true);
    // //base64图片上传(使用get)
    Upload::uploadImg('img_input_name_string',true,'get')->getFileName(true);
    // //base64图片上传(使用json)
    Upload::uploadImg('img_input_name_string',true,'json')->getFileName(true);

    //设置属性

    //设置基础路径
    Upload::setBasePath('/usr/local/');
    //其他临时设置
    $file = new Upload();
    echo $file->setDir('temp1')->setPrefixName('20180102_')->setValidType(['png','jpg'])->setMaxSize(100000)->upload('file_input_name_1')->getFilePath(true);

} catch (UploadException $e) {
    echo $e->getMessage();
}