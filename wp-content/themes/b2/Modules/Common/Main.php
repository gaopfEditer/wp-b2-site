<?php namespace B2\Modules\Common;

class Main{

    public function init(){ 

        //插件兼容
        $PluginsSupport = new PluginsSupport();
        $PluginsSupport->init();

        //用户登录与注册
        $login = new Login();
        $login->init();

        //用户相关
        $user = new User();
        $user->init();

        //文件上传
        $fileUpload = new FileUpload();
        $fileUpload->init();

        //语音合成
        // $radio = new Radio();
        // $radio->init();

        //重写网址
        $rewrite = new Rewrite();
        $rewrite->init();

        //文章相关
        $post = new Post();
        $post->init();

        //短代码
        $shortcode = new Shortcode();
        $shortcode->init();

        //评论相关函数
        $comment = new Comment();
        $comment->init();

        //播放器
        $Player = new Player();
        $Player->init();

        //seo
        $seo = new Seo();
        $seo->init();

        //订单管理
        $orders = new Orders();
        $orders->init();

        //消息
        $message = new Message();
        $message->init();

        //任务
        $task = new Task();
        $task->init();

        //任务
        $ask = new Ask();
        $ask->init();

        //分销
        $distribution = new Distribution();
        $distribution->init();

        //rest api
        $resapi = new RestApi();
        $resapi->init();

        $cache = new Cache();
        $cache->init();
        
    }
}