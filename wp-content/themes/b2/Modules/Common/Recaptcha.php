<?php namespace B2\Modules\Common;

use \Firebase\JWT\JWT;

class Recaptcha{
    
    public static function code_letter($num,$width,$height){

        //字体路径
        $font_address = B2_THEME_DIR.DIRECTORY_SEPARATOR.'Assets'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.rand(1,8).'.ttf';//字体文件地址

        //生成背景
        $image = imagecreatetruecolor($width,$height);
        imagefill($image,0,0,imagecolorallocate($image,rand(200,255),rand(200,255),rand(200,255)));//区域填充

        $value = '';
        $data="ABCDEFGHJKMNPQRSTUVWXYZ1234567890";
        for($i=0; $i<$num; $i++){
            $fontsize = $width > 300 ? 80 : 40;
            $fontcolor = imagecolorallocate($image,132,132,132);//0,120是代表随机的深色颜色
            $fontcontent = substr($data,rand(0,strlen($data)-1),1);
            $value .= $fontcontent;
            $x = $i*$width/$num+($width > 300 ? 20 : 10);//x坐标
            $y = ($height/2)+($fontsize/2);
            $anger = 0;
            ImageTTFText($image,$fontsize,$anger,$x,$y,$fontcolor,$font_address,$fontcontent);
        }

        //转换成 base64
        ob_start (); 

        imagejpeg ($image);
        $image_data = ob_get_contents (); 

        ob_end_clean (); 
        ob_end_flush();
        $image_data_base64 = base64_encode($image_data);

        //加密验证码
        $issuedAt = time();
        $expire = $issuedAt + 180;

        $token = array(
            "iss" => B2_HOME_URI,
            "iat" => $issuedAt,
            "nbf" => $issuedAt,
            'exp'=>$expire,
            'data'=>array(
                'value'=>md5(md5(AUTH_KEY.strtolower($value)))
            )
        );

        try {
            $jwt = JWT::encode($token, AUTH_KEY);
        } catch (\Throwable $th) {
            wp_die(__('请正确安装jwt插件','b2'));
        }

        return array(
            'token'=>$jwt,
            'base'=>'data:image/png;base64,'.$image_data_base64
        );
    }
}