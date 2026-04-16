<?php namespace B2\Modules\Common;

class Poster{

    /**
     * 从图片文件创建Image资源
     * @param $file 图片文件，支持url
     * @return bool|resource    成功返回图片image资源，失败返回false
     */
    public static function createImageFromFile($file){
        if(preg_match('/http(s)?:\/\//',$file)){
            $fileSuffix = self::getNetworkImgType($file);
        }else{
            $fileSuffix = pathinfo($file, PATHINFO_EXTENSION);
        }
    
        if(!$fileSuffix) return false;
    
        switch ($fileSuffix){
            case 'jpeg':
                $theImage = @imagecreatefromjpeg($file);
                break;
            case 'jpg':
                $theImage = @imagecreatefromjpeg($file);
                break;
            case 'png':
                $theImage = @imagecreatefrompng($file);
                break;
            case 'gif':
                $theImage = @imagecreatefromgif($file);
                break;
            default:
                $theImage = @imagecreatefromstring(file_get_contents($file));
                break;
        }
    
        return $theImage;
    }

    /**
     * 分行连续截取字符串
     * @param $str  需要截取的字符串,UTF-8
     * @param int $row  截取的行数
     * @param int $number   每行截取的字数，中文长度
     * @param bool $suffix  最后行是否添加‘...’后缀
     * @return array    返回数组共$row个元素，下标1到$row
     */
    public static function cn_row_substr($str,$row = 1,$number = 10,$suffix = true){
        $result = array();
        for ($r=1;$r<=$row;$r++){
            $result[$r] = '';
        }
    
        $str = trim($str);
        if(!$str) return $result;
    
        $theStrlen = strlen($str);
    
        //每行实际字节长度
        $oneRowNum = $number * 3;
        for($r=1;$r<=$row;$r++){
            if($r == $row and $theStrlen > $r * $oneRowNum and $suffix){
                $result[$r] = self::mg_cn_substr($str,$oneRowNum-15,($r-1)* $oneRowNum).'...';
            }else{
                $result[$r] = self::mg_cn_substr($str,$oneRowNum,($r-1)* $oneRowNum);
            }
            if($theStrlen < $r * $oneRowNum) break;
        }
    
        return $result;
    }

    public static function autoLineSplit ($str, $fontFamily, $fontSize, $charset, $width,$line) {
        $result = [];
    
        $len = (strlen($str) + mb_strlen($str, $charset)) / 2;
    
        // 计算总占宽
        $dimensions = imagettfbbox($fontSize, 0, $fontFamily, $str);
        $textWidth = abs($dimensions[4] - $dimensions[0]);
    
        // 计算每个字符的长度
        $singleW = $textWidth / $len;
        // 计算每行最多容纳多少个字符
        $maxCount = floor($width / $singleW);

        $i = 1;

        while ($len > $maxCount) {
            if($i > $line) break;
            if($i == $line){
                // 成功取得一行
                $result[] = mb_strimwidth($str, 0, $maxCount - 3, '', $charset).'...';
            }else{
                // 成功取得一行
                $result[] = mb_strimwidth($str, 0, $maxCount, '', $charset);
            }
            
            // 移除上一行的字符
            $str = str_replace($result[count($result) - 1], '', $str);
            // 重新计算长度
            $len = (strlen($str) + mb_strlen($str, $charset)) / 2;
            $i++;
        }
        // 最后一行在循环结束时执行
        $result[] = $str;
        
        return $result;
    }

    /**
     * 按字节截取utf-8字符串
     * 识别汉字全角符号，全角中文3个字节，半角英文1个字节
     * @param $str  需要切取的字符串
     * @param $len  截取长度[字节]
     * @param int $start    截取开始位置，默认0
     * @return string
     */
    public static function mg_cn_substr($str,$len,$start = 0){
        $q_str = '';
        $q_strlen = ($start + $len)>strlen($str) ? strlen($str) : ($start + $len);
    
        //如果start不为起始位置，若起始位置为乱码就按照UTF-8编码获取新start
        if($start and json_encode(substr($str,$start,1)) === false){
            for($a=0;$a<3;$a++){
                $new_start = $start + $a;
                $m_str = substr($str,$new_start,3);
                if(json_encode($m_str) !== false) {
                    $start = $new_start;
                    break;
                }
            }
        }
    
        //切取内容
        for($i=$start;$i<$q_strlen;$i++){
            //ord()函数取得substr()的第一个字符的ASCII码，如果大于0xa0的话则是中文字符
            if(ord(substr($str,$i,1))>0xa0){
                $q_str .= substr($str,$i,3);
                $i+=2;
            }else{
                $q_str .= substr($str,$i,1);
            }
        }
        return $q_str;
    }
}
