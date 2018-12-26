## 文件上传

## 功能
1. 普通form表单文件上传
2. base64图片上传
3. 针对图片专门使用了 getImageSize 和 getImageSizeString 方法判断文件类型（uploadImg方法）。

## 用法
```
try{
    //文件上传
    Upload::upload('file_input_name')->getFilePath(true);
    //图片上传
    Upload::uploadImg('img_input_name')->getFilePath(true);
    //base6464图片上传(使用post)
    Upload::uploadImg('img_input_name',true)->getFileName(true);
    //base64图片上传(使用get)
    Upload::uploadImg('img_input_name',true,'get')->getFileName(true);
    //base64图片上传(使用json)
    Upload::uploadImg('img_input_name',true.'json')->getFileName(true);

    //设置属性

    //设置基础路径
    Upload::setBasePath('/usr/local/public/');
    //其他临时设置
    $file = new Upload();
    $file->setDir('文件夹')->setPrefixName('前缀')->setValidType(['jpg'])->setMaxSize(100000)->upload('file_input_name')->getFilePath(true);

} catch (UploadException $e) {
    echo $e->getMessage();
}



```