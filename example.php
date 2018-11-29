<?php
include './File.php';
try{
    //文件上传
    echo File::upload('file_input_name')->getFileName();
    //图片上传
    echo File::uploadImg('img_input_name')->getFilePath(true);
    //base6464图片上传(使用post)
    File::uploadImg('img_input_name_string',true)->getFileName(true);
    // //base64图片上传(使用get)
    File::uploadImg('img_input_name_string',true,'get')->getFileName(true);
    // //base64图片上传(使用json)
    File::uploadImg('img_input_name_string',true,'json')->getFileName(true);

    //设置属性

    //设置基础路径
    File::setBasePath('/usr/local/');
    //其他临时设置
    $file = new File();
    echo $file->setDir('temp1')->setPrefixName('20180102_')->setValidType(['png','jpg'])->setMaxSize(100000)->upload('file_input_name_1')->getFilePath(true);

} catch (FileException $e) {
    echo $e->getMessage();
}