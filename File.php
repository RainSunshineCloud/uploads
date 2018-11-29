<?php

class File
{
    protected $types = [
        1 => 'gif',
        2 => 'jpg',
        3 => 'png',
        4 => 'swf',
        5 => 'psd',
        6 => 'bmp',
        7 => 'tiff',
        8 => 'jpc',
        9 => 'jp2', 
        10 => 'jpx',
        11 => 'jb2',
        12 => 'swc',
        13 => 'iff',
        14 => 'wbmp',
    ];
    //文件夹权限
    protected $mode = '0755';
    //文件名
    protected $file_name = '';
    //文件前缀
    protected $prefix_name = '';
    //设置基础路径
    protected static $base_root_path = '/';
    //文件夹中间路径
    protected $dir = 'get/';
    //最大文件大小
    protected static $max_size = 200000;

    /**
     * 通过图片内容获取图片后缀名，并精确判断是否是图片
     * @param  [string] $data [图片内容]
     * @return string
     */
    protected function getExtByString(string $data)
    {

        $file_info = getimagesizefromstring($data);
        if (!$file_info || empty($file_info[2]) || empty($this->types[$file_info[2]])) {
            throw new FileException('获取文件格式失败',8);
        }

        return $this->types[$file_info[2]];
    }

    /**
     * 通过图片路径获取图片后缀名（通过图片路径获取文件内容并判断后缀名）并进行精确判断是否是图片
     * @param  [string] $file_path [文件路径]
     * @return string
     */
    protected function getExtByPath(string $file_path)
    {
        if (strpos($file_path,'/') !== false || strpos($file_path,'./') !== false) {
            throw new FileException('只能加载本地文件',9);
        }

        if (!is_file($file_path)) {//文件路径
            throw new FileException('未找到该文件',8);
        }

        $file_info = getimagesize($file_path);

        if (!$file_info || empty($file_info[2]) || empty($this->types[$file_info[2]])) {
            throw new FileException('获取文件格式失败',8);
        }

        return $this->types[$file_info[2]];
    }

    /**
     * 直接通过文件名获取后缀名
     * @param  [string] $file_name [图片名称]
     * @return string
     */
    protected function getExtByFileName(string $file_name) 
    {
        $ext = trim(strrchr($file_name,'.'),'.');

        if (!$ext) {
            throw new FileException('获取文件格式失败',8);
        }
        return $ext;
    }

    /**
     * 通过后缀判断类型是否合法
     * @param  [string]  $ext 后缀
     * @return $this;
     */
    protected function isValidType(string $ext)
    {
        if (!in_array($ext,$this->types)) {
            throw new FileException('不合法的文件类型',11);
        }
        return $this;
    }

    /**
     * 判断大小是否合法
     * @param  [int]  $size [图片大小]
     * @return $this;
     */
    protected function isValidSize(int $size)
    {
        if ($size > self::$max_size) {
            throw new FileException('文件大小必须小于'.self::$max_size /1000 .'KB',12);
        }
        return $this;
    }

    /**
     * 获取文件上传信息
     * @param  string $input_name [字段名]
     * @return $this;
     */
    protected function getFileUpload(string $input_name)
    {

        if (empty($_FILES[$input_name])) {
            throw new FileException('未找到该文件',8);
        }

        switch ($_FILES[$input_name]['error']) {
            case 0:
                break;
            case 1:
                throw new FileException('文件大小必须小于'.($this->max_size /1000).'KB',1);
            case 2:
                throw new FileException('文件大小必须小于'.($this->max_size /1000).'KB',2);
            case 3:
                throw new FileException('文件损坏，只有部分上传成功',3);
            case 4:
                throw new FileException('文件上传失败',4);
            case 6:
                throw new FileException('找不到临时文件夹',6);
            case 7:
                throw new FileException('文件上传失败',7);
        }

        return $_FILES[$input_name];
    }

    /**
     * 获取base64的图片信息和内容
     * @param  string $input_name [字段名]
     * @param  string $method     [上传方式]
     * @return [array]             [size 大小，data 数据]
     */
    protected function getBase64Upload(string $input_name,string $method = 'post')
    {   

        $file_data = $this->getData($input_name,$method);
        $base64 = explode('base64,', $file_data);

        if (!$base64 || count($base64) < 2) {
            throw new FileException('文件格式错误',12);
        }

        $data = base64_decode(trim($base64[1]));

        if (!$data) {
            throw new FileException('文件格式错误',12);
        }

        $size = strlen($data);

        if ($size < 8) {
            throw new FileException('文件格式错误',12);
        }

        return ['size' => $size,'data' => $data];
    }

    /**
     * 获取图拍呢
     * @param  string $input_name [input名称]
     * @param  string $method     [上传方式]
     * @return 
     */
    protected function getData(string $input_name,string $method = 'post')
    {
        switch (strtoupper($method)) {
            case 'POST':
                if (empty($_POST[$input_name])) {
                    throw new FileException('未找到该文件',8);
                } 
                return  $_POST[$input_name];
            case 'GET':
                if (empty($_GET[$input_name])) {
                    throw new FileException('未找到该文件',8);
                }
                return $_GET[$input_name];
            case 'JSON':
                $data = file_get_contents('php://input');

                if (empty($data)) {
                    throw new FileException('未找到该文件',8);
                } 

                $data = json_decode($data,true);

                if (empty($data) || empty($data[$input_name])) {
                     throw new FileException('未找到该文件',8);
                }

                return $data[$input_name];
        }
    }

    /**
     * 获取ini 设置
     * @param  string $name [名称]
     * @return string
     */
    protected function getIni(string $name)
    {
        $post_max_size = ini_get($name);
        $info = '';
        preg_match('/(^[0-9\.]+)(\w+)/',$post_max_size,$info);

        if (count($info) < 3) {
            throw new FileException('获取ini设置失败',13);
        }

        $size = strtoupper($info[2]);
        
        $arr = array("K" => 10, "M" => 20);

        if (!isset($arr[$size])) {
            throw new FileException('获取ini设置失败',13);
        } 

        return $info[1] << $arr[$info[2]];
    }

    /**
     * 通过路径名保存文件
     * @param  string $file_path [文件路径]
     * @param  string $ext       [后缀名]
     * @return 
     */
    protected function save(string $file_path,string $ext)
    {
        $this->file_name = $this->createFileName() . '.' . $ext;
        $path = self::$base_root_path.$this->dir.$this->file_name;
        if (!move_uploaded_file($file_path,$path)) {
            throw new FileException('文件上传失败',14);
        }
    }

    /**
     * 通过文本保存文件
     * @param  [string] $data [文件路径]
     * @param  [string] $ext  [后缀名]
     * @return 
     */
    protected function saveString(string $data,string $ext)
    {
        $this->file_name = $this->createFileName() . '.' . $ext;
        $path = self::$base_root_path.$this->dir.$this->file_name;

        if (!file_put_contents($path,$data)) {
            throw new FileException('文件上传失败',14);
        }
    }
    
    /**
     * 获取文件名称
     * @return [string] [文件名,不包含后缀]
     */
    protected function createFileName()
    {
        return $this->prefix_name.mt_rand(1,10).uniqid();
    }

    /**
     * 添加文件夹
     */
    protected function addDir()
    {
        $path = self::$base_root_path.$this->dir;

        if (!file_exists($path)) {
            if(! mkdir($path,$this->mode,true)) {
                throw new FileException('创建文件夹失败',15);
            }
        }
    }

/**************************外部调用*************************************************/

    /**
     * 设置合法的文件类型,若有文件类型，则只取文件类型
     * @param array        $types  [description]
     * @param $this;
     */
    public function setValidType(array $types) 
    {   

        $img_valid_ext = [
            1 => 'gif',
            2 => 'jpg',
            3 => 'png',
            4 => 'swf',
            5 => 'psd',
            6 => 'bmp',
            7 => 'tiff',
            8 => 'jpc',
            9 => 'jp2', 
            10 => 'jpx',
            11 => 'jb2',
            12 => 'swc',
            13 => 'iff',
            14 => 'wbmp',
            15 => 'xbm'
        ];
        $this->types = array_intersect($img_valid_ext, $types);

        if (empty($this->types)) { 
            $this->types = $types;
        }
           
        if (empty($this->types)) {
            throw new FileException('设置合法的文件类型失败',16);
        }

        return $this;
    }


    /**
    * 设置大小
    * @param int          $size      [大小单位B]
    * @param bool|boolean $check_ini [检查设置值是否大于php.ini配置值]
    */
    public function setMaxSize(int $size,bool $check_ini = true)
    {
        if ($check_ini) {
            $post_max_size = $this->getIni('post_max_size');
            
            if ($post_max_size === false) {
                return false;
            }

            if ($size > $post_max_size ) {
                 throw new FileException('文件最大传送值设置失败',17);
                return false;
            }

            $upload_max_filesize = $this->getIni('upload_max_filesize');

            if ($post_max_size === false) {

                return false;
            }

            if ($size > $upload_max_filesize) {
                throw new FileException('文件最大传送值设置失败',18);
                return false;
            }

        }

        self::$max_size = $size;
        return $this;
    }

    /**
     * 上传
     * @param  string $input_name [长传的input名称]
     * @param  string $path       [文件路径，不传则使用默认路径]
     * @return bool
     */
    protected function upload(string $input_name)
    {

        $data = $this->getFileUpload($input_name);
        $ext = $this->getExtByFileName($data['name']);
        $this->isValidType($ext);
        $this->isValidSize($data['size']);
        $this->addDir();
        $this->save($data['tmp_name'],$ext);
        return $this;
    }

    protected function uploadImg($input_name,bool $is_base64 = false,string $method = 'post')
    {
        $this->methods = $method;

        if ($is_base64) {
            $data = $this->getBase64Upload($input_name,$method);
            $ext = $this->getExtByString($data['data']);
            $this->isValidSize($data['size']);
            $this->addDir();
            $this->saveString($data['data'],$ext);
        } else {
            $data = $this->getFileUpload($input_name);
            $ext = $this->getExtByPath($data['tmp_name']);
            $this->isValidSize($data['size']);
            $this->addDir();
            $this->save($data['tmp_name'],$ext);
        }

        return $this;
    }

/*************************************外部调用文件相关*******************************************/

    /**
     * 获取图片地址
     * @return [bool] [是否返回包含绝对路径]
     */
    public function getFilePath(bool $include_root_path = false)
    {

        if ($include_root_path) {
           return self::$base_root_path.$this->dir.$this->file_name;
        } else {
            return $this->dir.$this->file_name;
        }
    }

    /**
     * 获取文件名
     * @return [type] [description]
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * 设置前缀
     * @param [string] $prefix_name [名称]
     */
    public function setPrefixName(string $prefix_name)
    {
        $this->prefix_name = $prefix_name;
        return $this;
    }

    /**
     * 绝对基础
     * @param string $base [基础路径]
     */
    public static function setBasePath(string $base)
    {
        self::$base_root_path = $base;
        // self::$base_root_path = '/'.trim($base,'/\/').'/';
    }

    /**
     * 设置中间路径
     * @param [string] $dir [路径]
     */
    public function setDir(string $dir)
    {
        $this->dir = trim($dir,'/\/').'/';
        return $this;
    }

/***************************静态调用********************************************/

    public static function __callStatic($name,$args)
    {
        if ($name == 'upload' || $name == 'uploadImg') {
            $file = new self();
            return call_user_func_array([$file,$name],$args);
        }
    }

    public function __call($name,$args)
    {
        if ($name == 'upload' || $name == 'uploadImg') {
            return call_user_func_array([$this,$name],$args);
        }
    }
}

class FileException extends Exception{}