<?php

namespace hzfw\web;
use hzfw\base\BaseObject;
use hzfw\base\FileStream;

class Upload extends BaseObject
{
    /**
     * 文件名
     * @var string
     */
    public $fileName = '';
    
    /**
     * 临时文件名
     * @var string
     */
    public $tmpFileName = '';
    
    /**
     * 文件类型（不可信）
     * 需要通过文件验证类型
     * @var string
     */
    public $type = '';
    
    /**
     * 文件大小
     * @var integer
     */
    public $size = 0;
    
    /**
     * 错误
     * @var integer
     */
    public $error = 0;
    
    /**
     * 初始化
     * @param array $file
     */
    public function __construct(array $file)
    {
        $this->error = $file['error'];
        $this->tmpFileName = $file['tmp_name'];
        $this->fileName = $file['name'];
        $this->type = $file['type'];
        $this->size = $file['size'];
    }
    
    /**
     * 保存文件
     * @param string $filename
     * @param bool $deleteTempFile
     * @return bool
     */
    public function SaveFile(string $filename, bool $deleteTempFile = true): bool
    {
        $result = false;
        if(UPLOAD_ERR_OK === $this->error)
        {
            if(false !== $deleteTempFile) {
                $result = move_uploaded_file($this->tmpFileName, $filename);
            }
            else if(is_uploaded_file($this->tmpFileName)) {
                $result = copy($this->tmpFileName, $filename);
            }
        }
        return $result;
    }
    
    /**
     * 删除临时文件
     * @return bool
     */
    public function DeleteTempFile(): bool
    {
        $result = false;
        if(UPLOAD_ERR_OK === $this->error)
        {
            if(is_uploaded_file($this->tmpFileName)) {
                $result = @unlink($this->tmpFileName);
            }
        }
        return $result;
    }
    
    /**
     * 返回流
     * @param string $mode 文件读取模式
     * @return FileStream|NULL
     */
    public function GetStream(string $mode): ?FileStream
    {
        $result = null;
        if(UPLOAD_ERR_OK === $this->error)
        {
            if(is_uploaded_file($this->tmpFileName)) {
                $result = new FileStream($this->tmpFileName, $mode);
            }
        }
        return $result;
    }
}
