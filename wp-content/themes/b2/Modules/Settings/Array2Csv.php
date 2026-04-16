<?php
namespace B2\Modules\Settings;

class Array2Csv{
 
    public $_useGbk = true;
	private $fp = null;
	
	public function __construct(){
		//打开PHP文件句柄,php://output 表示直接输出到php缓存
 
        ob_end_clean();
		$this->fp = fopen('php://output', 'w');
	}
	
    //设置头部
    public function cvsHeader($filename)
    {
        
        //error_reporting(0);
        if($this->_useGbk) {
            header("Content-type:text/csv;charset=gbk");
        } else {
            header("Content-type:text/csv;charset=utf-8");
        }
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0,max-age=0');
        header('Expires:0');
        header('Pragma:public');
    }
	
	//采用putcsv封装格式
    public function outputData($data){


        foreach ($data as $key => $value) {
            //CSV的Excel支持GBK编码，一定要转换，否则乱码
            $data[$key] = iconv('utf-8', 'gbk', $value);
            
            // $data[$key] = $value;
        }
        fputcsv($this->fp,$data);
    }
	
    //刷新缓存，将PHP的输出缓存输出到浏览器上
    public function csvFlush(&$cnt){
        ob_flush();
        flush();
    }
	
	//关闭输出流
	public function closeFile(){
		fclose($this->fp);
	}
}