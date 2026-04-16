<?php namespace B2\Modules\Common;

use B2\Modules\Templates\Modules\Posts;
use B2\Modules\Templates\PostType\Announcement;
use B2\Modules\Templates\Single;
use B2\Modules\Common\Post;
use B2\Modules\Common\Shop;
use B2\Modules\Common\User;
use B2\Modules\Common\Distribution;
use B2\Modules\Common\Circle;
use B2\Modules\Common\FileUpload;
use B2\Modules\Common\Comment;
use B2\Modules\Common\Links;
use B2\Modules\Common\Infomation;
use B2\Modules\Common\Cpay;

class RestApi{

    public function init(){
       
        
	    
        add_action( 'rest_api_init', array($this,'b2_rest_regeister'));
    }


    public function b2_rest_regeister(){

        /**
         * 获取当前登录用户的个人信息
         */
        register_rest_route('b2/v1','/getUserInfo',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getUserInfo'),
            'permission_callback' => '__return_true'
        ));

        /************************************ 登录与注册开始 ************************************************/

        //用户登出
        register_rest_route('b2/v1','/loginOut',array(
            'methods'=>'get',
            'callback'=>array('B2\Modules\Common\Login','login_out'),
            'permission_callback' => '__return_true'
        ));

        //用户注销
        register_rest_route('b2/v1','/deleteUser',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'deleteUser'),
            'permission_callback' => '__return_true'
        ));

        //邀请码检查
        register_rest_route('b2/v1','/invitationCheck',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'invitationCheck'),
            'permission_callback' => '__return_true'
        ));

        //获取图形验证码
        register_rest_route('b2/v1','/getRecaptcha',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getRecaptcha'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/bindUserLogin',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'bindUserLogin'),
            'permission_callback' => '__return_true'
        ));

        //图形验证码检查
        register_rest_route('b2/v1','/imgCodeCheck',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'imgCodeCheck'),
            'permission_callback' => '__return_true'
        ));

        //发送短信或者邮箱验证码
        register_rest_route('b2/v1','/sendCode',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'sendCode'),
            'permission_callback' => '__return_true'
        ));

        //找回密码验证
        register_rest_route('b2/v1','/forgotPass',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'forgotPass'),
            'permission_callback' => '__return_true'
        ));

        //重设密码
        register_rest_route('b2/v1','/resetPass',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'resetPass'),
            'permission_callback' => '__return_true'
        ));

        //用户注册
        register_rest_route('b2/v1','/regeister',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'regeister'),
            'permission_callback' => '__return_true'
        ));

        //社交登录
        register_rest_route('b2/v1','/socialLogin',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'socialLogin'),
            'permission_callback' => '__return_true'
        ));

        //聚合登录
        register_rest_route('b2/v1','/juheSocialLogin',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'juheSocialLogin'),
            'permission_callback' => '__return_true'
        ));

        //重新绑定社交账户
        register_rest_route('b2/v1','/rebuildOauth',array(
            'methods'=>'get',
            'callback'=>array(__CLASS__,'rebuildOauth'),
            'permission_callback' => '__return_true'
        ));

        //社交登录，检查邀请码
        register_rest_route('b2/v1','/invRegeister',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'invRegeister'),
            'permission_callback' => '__return_true'
        ));

        //解除绑定社交账户
        register_rest_route('b2/v1','/unBuild',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'unBuild'),
            'permission_callback' => '__return_true'
        ));

        //保存昵称
        register_rest_route('b2/v1','/saveNickName',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'saveNickName'),
            'permission_callback' => '__return_true'
        ));

        //保存性别
        register_rest_route('b2/v1','/saveSex',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'saveSex'),
            'permission_callback' => '__return_true'
        ));

        //保存网址
        register_rest_route('b2/v1','/saveUrl',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'saveUrl'),
            'permission_callback' => '__return_true'
        ));

        //保存个人描述
        register_rest_route('b2/v1','/saveDesc',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'saveDesc'),
            'permission_callback' => '__return_true'
        ));

        //获取收货地址
        register_rest_route('b2/v1','/getAddresses',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getAddresses'),
            'permission_callback' => '__return_true'
        ));

        //保存收货地址
        register_rest_route('b2/v1','/saveAddress',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'saveAddress'),
            'permission_callback' => '__return_true'
        ));

        //保存默认收货地址
        register_rest_route('b2/v1','/saveDefaultAddress',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'saveDefaultAddress'),
            'permission_callback' => '__return_true'
        ));

        //删除收货地址
        register_rest_route('b2/v1','/deleteAddress',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'deleteAddress'),
            'permission_callback' => '__return_true'
        ));

        //保存用户名
        register_rest_route('b2/v1','/saveUsername',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'saveUsername'),
            'permission_callback' => '__return_true'
        ));

        //后台修改地址
        register_rest_route('b2/v1','/editPass',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'editPass'),
            'permission_callback' => '__return_true'
        ));

        //获取公众号二维码
        register_rest_route('b2/v1','/getLoginQrcode',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getLoginQrcode'),
            'permission_callback' => '__return_true'
        ));

        //关注并登录
        register_rest_route('b2/v1','/mpLogin',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'mpLogin'),
            'permission_callback' => '__return_true'
        ));

        //关注并使用邀请码登录
        register_rest_route('b2/v1','/mpLoginInv',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'mpLoginInv'),
            'permission_callback' => '__return_true'
        ));

        //获取社交登录连接
        register_rest_route('b2/v1','/getOauthLink',array(
            'methods'=>'get',
            'callback'=>array(__CLASS__,'getOauthLink'),
            'permission_callback' => '__return_true'
        ));

        /************************************ 登录与注册结束 ************************************************/

        /************************************ 用户相关开始 ************************************************/

        //获取用户页面的用户信息
        register_rest_route('b2/v1','/getAuthorInfo',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getAuthorInfo'),
            'permission_callback' => '__return_true'
        ));

        //保存用户cover
        register_rest_route('b2/v1','/saveCover',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'saveCover'),
            'permission_callback' => '__return_true'
        ));

        //保存avatar
        register_rest_route('b2/v1','/saveAvatar',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'saveAvatar'),
            'permission_callback' => '__return_true'
        ));

        //获取用户的评论列表
        register_rest_route('b2/v1','/getAuthorComments',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getAuthorComments'),
            'permission_callback' => '__return_true'
        ));

        //获取用户的关注列表
        register_rest_route('b2/v1','/getAuthorFollowing',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getAuthorFollowing'),
            'permission_callback' => '__return_true'
        ));

        //获取用户的粉丝列表
        register_rest_route('b2/v1','/getAuthorFollowers',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getAuthorFollowers'),
            'permission_callback' => '__return_true'
        ));

        //检查多个ID是否关注
        register_rest_route('b2/v1','/checkFollowByids',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'checkFollowByids'),
            'permission_callback' => '__return_true'
        ));

        //关注与取消关注
        register_rest_route('b2/v1','/AuthorFollow',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'AuthorFollow'),
            'permission_callback' => '__return_true'
        ));

        //检查用户是否关注某人，或者是不是本人
        register_rest_route('b2/v1','/checkFollowing',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'checkFollowing'),
            'permission_callback' => '__return_true'
        ));

        //用户收藏与取消收藏
        register_rest_route('b2/v1','/userFavorites',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'userFavorites'),
            'permission_callback' => '__return_true'
        ));

        //用户收藏列表
        register_rest_route('b2/v1','/getUserFavoritesList',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getUserFavoritesList'),
            'permission_callback' => '__return_true'
        ));

        //获取用户设置项
        register_rest_route('b2/v1','/getAuthorSettings',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getAuthorSettings'),
            'permission_callback' => '__return_true'
        ));

        //用户头像选择
        register_rest_route('b2/v1','/changeAvatar',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'changeAvatar'),
            'permission_callback' => '__return_true'
        ));

        //储存用户的QRcode
        register_rest_route('b2/v1','/saveQrcode',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'saveQrcode'),
            'permission_callback' => '__return_true'
        ));

        //获取用户的邀请码列表
        register_rest_route('b2/v1','/getUserInvList',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getUserInvList'),
            'permission_callback' => '__return_true'
        ));

        //获取用户的公开信息
        register_rest_route('b2/v1','/getUserPublicData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getUserPublicData'),
            'permission_callback' => '__return_true'
        ));

        //搜索用户
        register_rest_route('b2/v1','/searchUsers',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'searchUsers'),
            'permission_callback' => '__return_true'
        ));

        //小工具用户面板
        register_rest_route('b2/v1','/getUserWidget',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getUserWidget'),
            'permission_callback' => '__return_true'
        ));

        //用户签到
        register_rest_route('b2/v1','/userMission',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'userMission'),
            'permission_callback' => '__return_true'
        ));

        //获取签到数据
        register_rest_route('b2/v1','/getUserMission',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getUserMission'),
            'permission_callback' => '__return_true'
        ));

        //获取签到数据
        register_rest_route('b2/v1','/getMissionList',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getMissionList'),
            'permission_callback' => '__return_true'
        ));

        //随机获取认证用户
        register_rest_route('b2/v1','/getVerifyUsers',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getVerifyUsers'),
            'permission_callback' => '__return_true'
        ));

        //获取公众号关注二维码
        register_rest_route('b2/v1','/getVerifyInfo',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getVerifyInfo'),
            'permission_callback' => '__return_true'
        ));

        //检查用户是否已经关注公众号
        register_rest_route('b2/v1','/checkSubscribe',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'checkSubscribe'),
            'permission_callback' => '__return_true'
        ));

        //提交认证信息
        register_rest_route('b2/v1','/submitVerify',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'submitVerify'),
            'permission_callback' => '__return_true'
        ));

        //提交认证信息
        register_rest_route('b2/v1','/getCurrentUserAttachments',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getCurrentUserAttachments'),
            'permission_callback' => '__return_true'
        ));

        //获取用户任务数据
        register_rest_route('b2/v1','/getTaskData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getTaskData'),
            'permission_callback' => '__return_true'
        ));

        /************************************ 用户相关结束 ************************************************/

        /************************************ 私信相关开始 ************************************************/

        //给用户发私信
        register_rest_route('b2/v1','/sendDirectmessage',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'sendDirectmessage'),
            'permission_callback' => '__return_true'
        ));

        //获取私信列表
        register_rest_route('b2/v1','/getUserDirectmessageList',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getUserDirectmessageList'),
            'permission_callback' => '__return_true'
        ));

        //获取私信对话
        register_rest_route('b2/v1','/getMyDirectmessageList',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getMyDirectmessageList'),
            'permission_callback' => '__return_true'
        ));

        //获取新的私信数量
        register_rest_route('b2/v1','/getNewDmsg',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getNewDmsg'),
            'permission_callback' => '__return_true'
        ));

        /************************************ 私信相关结束 ************************************************/

        /************************************ 用户的互动信息相关开始 ************************************************/

        //获取财富页面信息
        register_rest_route('b2/v1','/getUserGoldData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getUserGoldData'),
            'permission_callback' => '__return_true'
        ));
        
        //获取财富页面积分、余额记录
        register_rest_route('b2/v1','/getGoldList',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getGoldList'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getUserMessage',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getUserMessage'),
            'permission_callback' => '__return_true'
        ));

        //获取财富排行信息
        register_rest_route('b2/v1','/getGoldTop',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getGoldTop'),
            'permission_callback' => '__return_true'
        ));

        //提现申请
        register_rest_route('b2/v1','/cashOut',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'cashOut'),
            'permission_callback' => '__return_true'
        ));

        //获取用户的订单
        register_rest_route('b2/v1','/getMyOrders',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getMyOrders'),
            'permission_callback' => '__return_true'
        ));

        //卡密充值
        register_rest_route('b2/v1','/cardPay',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'cardPay'),
            'permission_callback' => '__return_true'
        ));

        //获取vip信息
        register_rest_route('b2/v1','/getVipInfo',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getVipInfo'),
            'permission_callback' => '__return_true'
        ));

        /************************************ 用户的互动信息相关结束 ************************************************/

        /************************************ 订单开始 ************************************************/

        //检查支付方式
        register_rest_route('b2/v1','/checkPayType',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'checkPayType'),
            'permission_callback' => '__return_true'
        ));

        //批量支付
        register_rest_route('b2/v1','/BatchPayment',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'BatchPayment'),
            'permission_callback' => '__return_true'
        ));

        /************************************ 订单结束 ************************************************/

        /************************************ 支付相关开始 ************************************************/

        //获取允许的支付
        register_rest_route('b2/v1','/allowPayType',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'allowPayType'),
            'permission_callback' => '__return_true'
        ));

        //开始支付
        register_rest_route('b2/v1','/buildOrder',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'buildOrder'),
            'permission_callback' => '__return_true'
        ));

        //余额支付
        register_rest_route('b2/v1','/balancePay',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'balancePay'),
            'permission_callback' => '__return_true'
        ));

        //积分支付
        register_rest_route('b2/v1','/creditPay',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'creditPay'),
            'permission_callback' => '__return_true'
        ));

        //支付确认
        register_rest_route('b2/v1','/payCheck',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'payCheck'),
            'permission_callback' => '__return_true'
        ));

        /************************************ 支付相关结束 ************************************************/

        /************************************ 文章开始 ************************************************/

        //获取文章模块内容（分页显示）
        register_rest_route('b2/v1','/getPostList',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getPostList'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getModulePostList',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getModulePostList'),
            'permission_callback' => '__return_true'
        ));

        //获取公告列表
        register_rest_route('b2/v1','/getAnnouncements',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getAnnouncements'),
            'permission_callback' => '__return_true'
        ));

        //获取视频播放列表
        register_rest_route('b2/v1','/getPostVideos',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getPostVideos'),
            'permission_callback' => '__return_true'
        ));

        //获取语音播放字符串
        register_rest_route('b2/v1','/getPostAudio',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getPostAudio'),
            'permission_callback' => '__return_true'
        ));

        //获取外链视频的html
        register_rest_route('b2/v1','/getVideoHtml',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getVideoHtml'),
            'permission_callback' => '__return_true'
        ));

        //获取隐藏段代码内容
        register_rest_route('b2/v1','/getHiddenContent',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getHiddenContent'),
            'permission_callback' => '__return_true'
        ));

        //获取文章相关信息
        register_rest_route('b2/v1','/getPostData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getPostData'),
            'permission_callback' => '__return_true'
        ));

        //文章顶踩
        register_rest_route('b2/v1','/postVote',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'postVote'),
            'permission_callback' => '__return_true'
        ));

        //文章顶踩
        register_rest_route('b2/v1','/getPostVote',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getPostVote'),
            'permission_callback' => '__return_true'
        ));

        //获取文章下载数据
        register_rest_route('b2/v1','/getDownloadData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getDownloadData'),
            'permission_callback' => '__return_true'
        ));

        //获取文章下载数据
        register_rest_route('b2/v1','/getDogeVideo',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getDogeVideo'),
            'permission_callback' => '__return_true'
        ));


        //获取下载跳转页面数据
        register_rest_route('b2/v1','/getDownloadPageData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getDownloadPageData'),
            'permission_callback' => '__return_true'
        ));

        //获取下载文件的真实地址
        register_rest_route('b2/v1','/downloadFile',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'downloadFile'),
            'permission_callback' => '__return_true'
        ));

        //检查投稿权限
        register_rest_route('b2/v1','/checkUserWriteRole',array(
            'methods'=>'get',
            'callback'=>array(__CLASS__,'checkUserWriteRole'),
            'permission_callback' => '__return_true'
        ));

        //预览
        register_rest_route('b2/v1','/previewPost',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'previewPost'),
            'permission_callback' => '__return_true'
        ));

        //投稿
        register_rest_route('b2/v1','/insertPost',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'insertPost'),
            'permission_callback' => '__return_true'
        ));

        //删除文章
        register_rest_route('b2/v1','/deleteDraftPost',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'deleteDraftPost'),
            'permission_callback' => '__return_true'
        ));

        //检查文章编辑权限
        register_rest_route('b2/v1','/checkWriteUser',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'checkWriteUser'),
            'permission_callback' => '__return_true'
        ));

        //获取海报信息
        register_rest_route('b2/v1','/getPosterData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getPosterData'),
            'permission_callback' => '__return_true'
        ));

        //url转base64
        register_rest_route('b2/v1','/urlToBase64',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'urlToBase64'),
            'permission_callback' => '__return_true'
        ));

        /************************************ 文章结束 ************************************************/

        /************************************ 评论开始 ************************************************/

        //获取评论
        register_rest_route('b2/v1','/getCommentList',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getCommentList'),
            'permission_callback' => '__return_true'
        ));

        //获取tips
        register_rest_route('b2/v1','/getCommentTips',array(
            'methods'=>'get',
            'callback'=>array(__CLASS__,'getCommentTips'),
            'permission_callback' => '__return_true'
        ));

        //给评论赞踩
        register_rest_route('b2/v1','/commentVote',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'commentVote'),
            'permission_callback' => '__return_true'
        ));

        //获取某一组评论的踩赞数据
        register_rest_route('b2/v1','/commentVoteData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'commentVoteData'),
            'permission_callback' => '__return_true'
        ));

        //获取用户的权限
        register_rest_route('b2/v1','/getUserRole',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getUserRole'),
            'permission_callback' => '__return_true'
        ));

        //置顶评论
        register_rest_route('b2/v1','/commentSticky',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'commentSticky'),
            'permission_callback' => '__return_true'
        ));

        //发布评论
        register_rest_route('b2/v1','/commentSubmit',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'commentSubmit'),
            'permission_callback' => '__return_true'
        ));

        //获取小工具里面的最新评论
        register_rest_route('b2/v1','/getNewComments',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getNewComments'),
            'permission_callback' => '__return_true'
        ));

        /************************************ 评论结束 ************************************************/

        //图片上传
        register_rest_route('b2/v1','/fileUpload',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'fileUpload'),
            'permission_callback' => '__return_true'
        ));

        //获取最新公告
        register_rest_route('b2/v1','/getLatestAnnouncement',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getLatestAnnouncement'),
            'permission_callback' => '__return_true'
        ));

        /************************************ 商铺相关 ************************************************/

        //通过ID获取商品信息
        register_rest_route('b2/v1','/getShopItemsData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getShopItemsData'),
            'permission_callback' => '__return_true'
        ));

        //领取优惠劵
        register_rest_route('b2/v1','/ShopCouponReceive',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'ShopCouponReceive'),
            'permission_callback' => '__return_true'
        ));

        //获取我的优惠劵
        register_rest_route('b2/v1','/getMyCoupons',array(
            'methods'=>'get',
            'callback'=>array(__CLASS__,'getMyCoupons'),
            'permission_callback' => '__return_true'
        ));

        //删除我的优惠劵
        register_rest_route('b2/v1','/deleteMyCoupon',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'deleteMyCoupon'),
            'permission_callback' => '__return_true'
        ));

        //获取商品优惠劵信息
        register_rest_route('b2/v1','/getCouponsByPostId',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getCouponsByPostId'),
            'permission_callback' => '__return_true'
        ));

        //积分抽奖
        register_rest_route('b2/v1','/shopLottery',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'shopLottery'),
            'permission_callback' => '__return_true'
        ));

        //获取当前用户的邮箱
        register_rest_route('b2/v1','/getEmail',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getEmail'),
            'permission_callback' => '__return_true'
        ));

        //获取购买结果信息
        register_rest_route('b2/v1','/getUserBuyResout',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getUserBuyResout'),
            'permission_callback' => '__return_true'
        ));

        //快递查询
        register_rest_route('b2/v1','/getOrderExpress',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getOrderExpress'),
            'permission_callback' => '__return_true'
        ));

        //获取分销基本信息
        register_rest_route('b2/v1','/getMyDistributionData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getMyDistributionData'),
            'permission_callback' => '__return_true'
        ));

        //获取分销订单列表
        register_rest_route('b2/v1','/getMyDistributionOrders',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getMyDistributionOrders'),
            'permission_callback' => '__return_true'
        ));

        //获取分销伙伴
        register_rest_route('b2/v1','/getMyPartner',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getMyPartner'),
            'permission_callback' => '__return_true'
        ));

        //提交工单
        register_rest_route('b2/v1','/submitRequest',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'submitRequest'),
            'permission_callback' => '__return_true'
        ));

        //文档评价
        register_rest_route('b2/v1','/documentVote',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'documentVote'),
            'permission_callback' => '__return_true'
        ));

        //发布快讯
        register_rest_route('b2/v1','/submitNewsflashes',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'submitNewsflashes'),
            'permission_callback' => '__return_true'
        ));

        //获取快讯列表
        register_rest_route('b2/v1','/getNewsflashesList',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getNewsflashesList'),
            'permission_callback' => '__return_true'
        ));

        //获取快讯小工具数据
        register_rest_route('b2/v1','/getWidgetNewsflashes',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getWidgetNewsflashes'),
            'permission_callback' => '__return_true'
        ));

        //确认收货
        register_rest_route('b2/v1','/userChangeOrderState',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'userChangeOrderState'),
            'permission_callback' => '__return_true'
        ));

        /************************************ 圈子相关 ************************************************/

        register_rest_route('b2/v1','/getTopicCommentList',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getTopicCommentList'),
            'permission_callback' => '__return_true'
        ));

        $open = b2_get_option('circle_main','circle_open');
        if($open){
            register_rest_route('b2/v1','/insertTopicCard',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'insertTopicCard'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route('b2/v1','/insertCircleTopic',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'insertCircleTopic'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route('b2/v1','/getCurrentUserCircleData',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'getCurrentUserCircleData'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route('b2/v1','/createCircle',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'createCircle'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route('b2/v1','/getCirclesList',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'getCirclesList'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route('b2/v1','/getTopicList',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'getTopicList'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route('b2/v1','/getChildComments',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'getChildComments'),
                'permission_callback' => '__return_true'
            ));

            //获取所有圈子数据
            register_rest_route('b2/v1','/getAllCircleData',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'getAllCircleData'),
                'permission_callback' => '__return_true'
            ));

            //话题置顶
            register_rest_route('b2/v1','/setSticky',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'setSticky'),
                'permission_callback' => '__return_true'
            ));

            //话题加精
            register_rest_route('b2/v1','/setBest',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'setBest'),
                'permission_callback' => '__return_true'
            ));

            //通过ID获取某个帖子的内容
            register_rest_route('b2/v1','/getDataByTopicId',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'getDataByTopicId'),
                'permission_callback' => '__return_true'
            ));

            //删除话题
            register_rest_route('b2/v1','/deleteTopic',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'deleteTopic'),
                'permission_callback' => '__return_true'
            ));

            //话题审核
            register_rest_route('b2/v1','/topicChangeStatus',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'topicChangeStatus'),
                'permission_callback' => '__return_true'
            ));

            //通过ID获取圈子的数据
            register_rest_route('b2/v1','/getCircleDataByCircleIds',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'getCircleDataByCircleIds'),
                'permission_callback' => '__return_true'
            ));

            //加入圈子
            register_rest_route('b2/v1','/joinCircle',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'joinCircle'),
                'permission_callback' => '__return_true'
            ));

            //获取圈子用户
            register_rest_route('b2/v1','/getCircleUserList',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'getCircleUserList'),
                'permission_callback' => '__return_true'
            ));

            //审核会员
            register_rest_route('b2/v1','/changeUserRole',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'changeUserRole'),
                'permission_callback' => '__return_true'
            ));

            //删除圈友
            register_rest_route('b2/v1','/removeUserFormCircle',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'removeUserFormCircle'),
                'permission_callback' => '__return_true'
            ));

            //话题投票
            register_rest_route('b2/v1', '/topicVote', array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'topicVote'),
                'permission_callback' => '__return_true'
            ));

            //话题，你猜
            register_rest_route('b2/v1', '/topicGuess', array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'topicGuess'),
                'permission_callback' => '__return_true'
            ));

            //回答问题
            register_rest_route('b2/v1','/submitAnswer',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'submitAnswer'),
                'permission_callback' => '__return_true'
            ));

            //获取回答列表
            register_rest_route('b2/v1','/getTopicAnswerList',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'getTopicAnswerList'),
                'permission_callback' => '__return_true'
            ));

            //采纳答案
            register_rest_route('b2/v1','/answerRight',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'answerRight'),
                'permission_callback' => '__return_true'
            ));

            //删除答案
            register_rest_route('b2/v1','/deleteAnswer',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'deleteAnswer'),
                'permission_callback' => '__return_true'
            ));

            //编辑话题
            register_rest_route('b2/v1','/getEditData',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'getEditData'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route('b2/v1','/getCircleTopCats',array(
                'methods'=>'post',
                'callback'=>array(__CLASS__,'getCircleTopCats'),
                'permission_callback' => '__return_true'
            ));
        }

        //获取文章公告
        register_rest_route('b2/v1','/getPostGG',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getPostGG'),
            'permission_callback' => '__return_true'
        ));

        /*数据更新*/
        register_rest_route('b2/v1','/ajaxupdate',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'ajaxupdate'),
            'permission_callback' => '__return_true'
        ));

        //删除评论
        register_rest_route('b2/v1','/deleteComment',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'deleteComment'),
            'permission_callback' => '__return_true'
        ));

        //获取小黑屋用户
        register_rest_route('b2/v1','/getDarkRoomUsers',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getDarkRoomUsers'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getWriteCountent',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getWriteCountent'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getStreamList',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getStreamList'),
            'permission_callback' => '__return_true'
        ));

        //商品收藏夹
        register_rest_route('b2/v1','/getMyCarts',array(
            'methods'=>'get',
            'callback'=>array(__CLASS__,'getMyCarts'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getMyCarts2',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getMyCarts'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/setMyCarts',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'setMyCarts'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/deleteMyCarts',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'deleteMyCarts'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/circleSearch',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'circleSearch'),
            'permission_callback' => '__return_true'
        ));

		if(b2_get_option('links_main','link_open')){

			register_rest_route('b2/v1','/submitLink',array(
				'methods'=>'post',
				'callback'=>array(__CLASS__,'submitLink'),
				'permission_callback' => '__return_true'
			));

			register_rest_route('b2/v1','/linkHasPending',array(
				'methods'=>'get',
				'callback'=>array(__CLASS__,'linkHasPending'),
				'permission_callback' => '__return_true'
			));

			register_rest_route('b2/v1','/getLinkVote',array(
				'methods'=>'post',
				'callback'=>array(__CLASS__,'getLinkVote'),
				'permission_callback' => '__return_true'
			));

			register_rest_route('b2/v1','/linkVote',array(
				'methods'=>'post',
				'callback'=>array(__CLASS__,'linkVote'),
				'permission_callback' => '__return_true'
			));

		}

        register_rest_route('b2/v1','/getInfomationList',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getInfomationList'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getInfomationHotComments',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getInfomationHotComments'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getInfomationCats',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getInfomationCats'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getInfomationSingle',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getInfomationSingle'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getPoinfomationOpts',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getPoinfomationOpts'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/shieldAuthor',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'shieldAuthor'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getCpayResout',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getCpayResout'),
            'permission_callback' => '__return_true'
        ));

        // edited by fuzqing
        register_rest_route('b2/v1','/getCpayInfo',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getCpayInfo'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/insertInfomation',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'insertInfomation'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getInfomationHotCommentTopics',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getInfomationHotCommentTopics'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/editInfomationData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'editInfomationData'),
            'permission_callback' => '__return_true'
        ));

        // register_rest_route('b2/v1','/registerByOtherSite',array(
        //     'methods'=>'post',
        //     'callback'=>array(__CLASS__,'registerByOtherSite'),
        //     'permission_callback' => '__return_true'
        // ));

        register_rest_route('b2/v1','/getPostFavorites',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getPostFavorites'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/poAsk',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'poAsk'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getAskData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getAskData'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getAskEditData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getAskEditData'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getAanswerHtml',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getAanswerHtml'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/poAskAnswer',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'poAskAnswer'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getAnswerData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getAnswerData'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/getEditAnswerData',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'getEditAnswerData'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/setAnswerRight',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'setAnswerRight'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1','/bestAnswer',array(
            'methods'=>'post',
            'callback'=>array(__CLASS__,'bestAnswer'),
            'permission_callback' => '__return_true'
        ));
    }

    public static function deleteUser($request){
        $res = Login::delete_user($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getPostFavorites($request){
        $res = Post::get_post_favorites(0,$request['post_id']);

        if(isset($res['error'])){
            return new \WP_Error('cpay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function editInfomationData($request){
        $info = new Infomation();
        $res = $info->edit_infomation_data($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('cpay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getInfomationHotCommentTopics($request){
        $info = new Infomation();
        $res = $info->get_infomation_hot_comments($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('cpay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function insertInfomation($request){
        $info = new Infomation();
        $res = $info->insert_infomation($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('cpay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getCpayResout($request){

        $res = Cpay::get_pay_resout($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('cpay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    // edited by fuzqing
    public static function getCpayInfo($request){

        $res = Cpay::getCpayInfo($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('cpay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getPoinfomationOpts($request){

        $info = new Infomation();

        $res = $info->get_po_infomation_opts($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('infomation_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }

    }

    public static function getInfomationSingle($request){
        $info = new Infomation();

        $res = $info->get_infomation_single_data($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('infomation_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getInfomationCats($request){
        $info = new Infomation();

        $res = $info->get_infomation_cats($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('infomation_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getInfomationHotComments($request){
        $info = new Infomation();

        $res = $info->get_infomation_hot_comments($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('infomation_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function shieldAuthor($request){

        $res = User::shield_author($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getInfomationList($request){

        $info = new Infomation();

        $res = $info->get_infomation_list($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    // public static function registerByOtherSite($request){

    //     $ip = b2_get_user_ip();

    //     if($ip !== '你的ip') return false;

    //     $username = $request['username'];
    //     $password = $request['password'];
    //     $email = $request['email'];

    //     $user_id = wp_create_user($username,$password,$email);

    //     if(is_wp_error( $user_id )){
    //         return new \WP_Error('regeister_error',$user_id->get_error_message(),array('status'=>403));
    //     }else{
    //         return wp_authenticate($username,$password);
    //     }
    // }

    public static function linkVote($request){
        $res = Links::link_vote($request['link_id']);

        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getLinkVote($request){
        $res = Links::get_link_vote($request['link_id']);

        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function linkHasPending(){
        $res = Links::has_pending();

        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function submitLink($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Links::submit_link($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getMyCarts(){
        $res = Shop::get_my_carts();

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function deleteMyCarts($request){
        $res = Shop::delete_my_carts($request['id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function setMyCarts($request){
        $res = Shop::set_my_carts($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getStreamList($request){
        $res = Stream::get_list($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getDarkRoomUsers($request){
        $user = User::get_dark_room_users($request['paged'],$request['type']);

        if(isset($user['error'])){
            return new \WP_Error('comment_error',$user['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($user,200);
        }
    }

    public static function getUserInfo($request){
        $user = \B2\Modules\Common\Login::get_user_info($request['ref']);

        if(isset($user['error'])){
            return new \WP_Error('comment_error',$user['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($user,200);
        }
    }

    //获取语音播放字符串
    public static function getPostAudio($request){
        return Single::get_content_arr($request['post_id']);
    }

    /**
     * 获取文章列表
     *
     * @param array $request
     *
     * @return array
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function getPostList($request){
        $type = str_replace('-','_',$request['post_type']);

        if(!method_exists('B2\Modules\Templates\Modules\Posts',$type)) return;

        return Posts::$type($request,$request['post_i'],true);
    }

        /**
     * 获取文章列表
     *
     * @param array $request
     *
     * @return array
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function getModulePostList($request){

        $index = $request['index'] - 1;

        $opt = b2_get_option('template_index','index_group');

        if(isset($opt[$index])){

            if(isset($opt[$index]['post_type'])){
                $type = $opt[$index]['post_type'];

                $type = str_replace('-','_',$type);
                $opt[$index]['width'] = b2_get_page_width($opt[$index]['show_widget']);

                $data = Posts::opt_to_json($opt[$index]);

                //return $data;

                $data = json_decode($data,true);

                if(!empty($request['id'])){
                    $data['post_cat'] = $request['id'];
                }else{
                    $data['post_cat'] = $opt[$index]['post_cat'];
                }

                if(isset($request['rand']) && $request['rand'] == 1){
                    $data['post_order'] = 'random';
                }

                $data['post_paged'] = $request['post_paged'];

                return Posts::$type($data,$request['index'],true);
            }
        }

        return '';
    }

    /**
     * 获取公告数据
     *
     * @param array $request 传入的数据，参考 wp_query 的 $arg，如果特别传入 'announcement_type' => 'title' 则只获取标题和日期参数，否则获取指定列表的html数据
     *
     * @return array
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function getAnnouncements($request){
        return Announcement::get_announcements($request->get_params());
    }

    public static function getPostVideos($request){
        return Player::check_video_allow($request['post_id'],$request['order_id']);
    }

    public static function getVideoHtml($request){

        if(strpos('[',$request['url']) !== false || strpos(']',$request['url']) !== false) return;

        b2_remove_filters_with_method_name('the_content','b2_lazyload',2);

        return apply_filters('the_content', urldecode($request['url']));
    }

    /**
     * 获取隐藏内容
     *
     * @param [type] $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function getHiddenContent($request){
        return Shortcode::get_content_hide_arg($request['id'],$request['order_id']);
    }

    public static function getPostData($request){
        $res = Post::get_post_data($request['post_id']);

        if(isset($res['error'])){
            return new \WP_Error('comment_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response(array('list'=>$res),200);
        }
    }

    public static function getCommentList($request){
        return Comment::more_comments($request['post_id'],$request['post_paged']);
    }

    public static function getCommentTips($request){
        return Comment::get_tips();
    }

    public static function commentVote($request){

        if(!b2_check_repo()) return new \WP_Error('invitation_error',__('点的太快啦！','b2'),array('status'=>403));
        $res = Comment::comment_vote($request['type'],$request['comment_id']);

        if(isset($res['error'])){
            return new \WP_Error('post_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function commentVoteData($request){
        return Comment::comment_vote_data($request['ids'],$request['post_id']);
    }

    public static function commentSticky($request){
        return Comment::comment_sticky($request['post_id'],$request['comment_id']);
    }

    public static function commentSubmit($request){
        $res = Comment::submit_comment($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('comment_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response(array('list'=>$res),200);
        }
    }

    //获取小工具里的最新评论
    public static function getNewComments($request){
        $res = Comment::get_new_comments($request['paged'],$request['hidden'],$request['count']);

        if(isset($res['error'])){
            return new \WP_Error('comment_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getUserRole($request){
        $res = User::get_current_role($request['post_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    /**
     * 图片上传
     *
     * @param object $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function fileUpload($request){

        $res = FileUpload::file_upload($request);
        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    /**
     * 检查邀请码的合法性
     *
     * @param object $request
     *
     * @return array
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function invitationCheck($request){

        $code = strtoupper(trim($request['code']));
        if(!b2_check_repo($code)) return new \WP_Error('invitation_error',__('点的太快啦！','b2'),array('status'=>403));

        $res = Invitation::invitationCheck($code);

        if(isset($res['error'])){
            return new \WP_Error('invitation_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response(array('msg'=>__('邀请码可用','b2')),200);
        }

    }

    /**
     * 检查图形验证码
     *
     * @param object $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function imgCodeCheck($request){
        $res = Login::code_check($request->get_params());
        if(isset($res['error'])){
            return new \WP_Error('img_code_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response(array('code'=>$res),200);
        }
    }

    /**
     * 用户注册
     *
     * @param object $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function regeister($request){

        $res = Login::regeister($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response(array('msg'=>$res),200);
        }
    }

    /**
     * 获取图形验证码
     *
     * @param object $request number:验证码位数；width:验证码图片宽度；height:验证码图片高度
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function getRecaptcha($request){
        $res = Recaptcha::code_letter($request['number'],$request['width'],$request['height']);
        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    /**
     * 发送验证码
     *
     * @param object $request username:用户名是手机或者邮箱
     *
     * @return string 验证码token
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function bindUserLogin($request){
        $res = Login::bind_user_login($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    /**
     * 社交登录绑定用户名
     *
     * @param object $request username:用户名是手机或者邮箱
     *
     * @return string 验证码token
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function sendCode($request){
        if(!b2_check_repo($request['token'])) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Login::send_code($request);

        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response(array('token'=>$res),200);
        }
    }

    /**
     * 忘记密码
     *
     * @param [type] $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function forgotPass($request){
        $res = Login::forgot_pass($request);

        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response(array('token'=>$res),200);
        }
    }

    /**
     * 重设密码
     *
     * @param [type] $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function resetPass($request){
        $res = Login::rest_pass($request);

        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    /**
     * 社交登录/注册
     *
     * @param [type] $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function socialLogin($request){
        $res = OAuth::init($request['type'],$request['code'],$request['juhe']);

        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //聚合社交登录
    public static function juheSocialLogin($request){
        $res = Login::juhe_social_login($request['type']);

        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //重新绑定社交账户
    public static function rebuildOauth($request){
        $res = OAuth::rebuild_oauth();

        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    /**
     * 社交注册-使用邀请码注册
     *
     * @param [type] $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function invRegeister($request){

        if(isset($request['token'])){
            $res = OAuth::invitation_action(array(
                'token'=>$request['token'],
                'invitation'=>$request['invitation'],
                'subType'=>$request['subType']
            ));
        }else{
            return new \WP_REST_Response(array('error'=>__('数据错误','b2')),200);
        }


        if(isset($res['error'])){
            return new \WP_Error('regeister_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    /**
     * 用户页面-获取用户的基本信息
     *
     * @param [type] $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function getAuthorInfo($request){
        $res = User::get_author_info($request['author_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    /**
     * 用户页面-保存用户的cover
     *
     * @param [type] $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function saveCover($request){
        if(!b2_check_repo($request['id'])) return new \WP_Error('invitation_error',__('点的太快啦！','b2'),array('status'=>403));
        $res = User::save_cover($request['url'],$request['id'],$request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    /**
     * 用户页面-保存用户的cover
     *
     * @param [type] $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function saveAvatar($request){
        if(!b2_check_repo($request['id'])) return new \WP_Error('invitation_error',__('点的太快啦！','b2'),array('status'=>403));
        $res = User::save_avatar($request['url'],$request['id'],$request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取用户评论列表
    public static function getAuthorComments($request){
        $res = Comment::get_user_comment_list(array('user_id'=>$request['user_id'],'paged'=>$request['post_paged'],'number'=>$request['number']));

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{

            $html = '';

            foreach ($res['data'] as $k => $v) {
                $html .= '<li>
                    <div class="author-comment-date">'.$v['comment_date'].'</div>
                    <div class="author-comment-content b2-radius">
                        '.($v['comment_img'] ? '<div class="comment-img-box"><img class="comment-img b2-radius" src="'.$v['comment_img'].'" /></div>' : '').'
                        <div class="author-comment-content-text">'.$v['comment_content'].'</div>
                    </div>
                    <div class="author-comment-post"><a href="'.$v['post_link'].'">'.b2_get_icon('b2-external-link-line').$v['post_title'].'</a></div>
                </li>';
            }

            return array('data'=>$html);
        }
    }

    //获取用户关注列表
    public static function getAuthorFollowing($request){
        $res = User::get_following($request['user_id'],$request['post_paged'],$request['number']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }elseif(isset($request['json']) && $request['json']){
            return $res;
        }else{

            $html = '';
            $current_user_id = b2_get_current_user_id();

            foreach ($res['data'] as $k => $v) {
                $html .= '<li>
                    <div class="following-avatar">
                    <a href="'.$v['link'].'" target="_blank"><img src="'.$v['avatar'].'" class="avatar b2-radius" /></a>
                    </div>
                    <div class="following-info">
                        <div class="following-name">    
                            <a href="'.$v['link'].'">'.$v['display_name'].'</a>
                        </div>
                        '.($v['desc'] ? '<div class="following-info-desc b2-radius">
                        '.$v['desc'].'
                        </div>' : '').'
                        <div class="following-info-count">
                            <span>'.$v['post_count'].__('文章','b2').'</span>
                            <span>'.$v['following'].__('关注','b2').'</span>
                            <span>'.$v['followers'].__('粉丝','b2').'</span>
                        </div>
                    </div>
                    '.($request['user_id'] == $current_user_id ? '<div class="following-cancel">
                    <button class="empty" onclick="b2AuthorFollow.followCancel(event,\''.$v['id'].'\')">'.__('取消关注','b2').'</button>
                </div>' : '').'
                    
                </li>';
            }

            if(!$html){
                $html = B2_EMPTY;
            }

            return array('data'=>$html);

        }
    }

    //获取粉丝列表
    public static function getAuthorFollowers($request){
        $res = User::get_followers($request['user_id'],$request['post_paged'],$request['number']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }elseif(isset($request['json']) && $request['json']){
            return $res;
        }else{

            $html = '';
            $current_user_id = b2_get_current_user_id();

            foreach ($res['data'] as $k => $v) {
                $html .= '<li>
                    <div class="following-avatar">
                        <a href="'.$v['link'].'" target="_blank"><img src="'.$v['avatar'].'" class="avatar b2-radius" /></a>
                    </div>
                    <div class="following-info">
                        <div class="following-name">    
                            <a href="'.$v['link'].'">'.$v['display_name'].'</a>
                        </div>
                        '.($v['desc'] ? '<div class="following-info-desc b2-radius">
                        '.$v['desc'].'
                        </div>' : '').'
                        <div class="following-info-count">
                            <span>'.$v['post_count'].__('文章','b2').'</span>
                            <span>'.$v['following'].__('关注','b2').'</span>
                            <span>'.$v['followers'].__('粉丝','b2').'</span>
                        </div>
                    </div>
                    '.($request['user_id'] == $current_user_id ? '<div class="following-cancel">
                    <button class="'.($v['followed'] ? 'empty' : '').'" onclick="b2AuthorFollowers.following(event,\''.$v['id'].'\')"> '.($v['followed'] ? __('已关注','b2') : b2_get_icon('b2-add-line').__('关注','b2')).'</button>
                </div>' : '').'
                    
                </li>';
            }

            if(!$html){
                $html = B2_EMPTY;
            }

            return array('data'=>$html);

        }
    }

    //关注与取消关注
    public static function AuthorFollow($request){
        if(!b2_check_repo()) return new \WP_Error('invitation_error',__('点的太快啦！','b2'),array('status'=>403));
        $res = User::user_follow_action($request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('post_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //检查是否已经关注
    public static function checkFollowing($request){
        $res = User::check_following($request['user_id'],$request['post_id'],isset($request['show_meta']) ? $request['show_meta'] : true);

        if(isset($res['error'])){
            return new \WP_Error('post_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //收藏与取消收藏文章
    public static function userFavorites($request){
        if(!b2_check_repo()) return new \WP_Error('invitation_error',__('点的太快啦！','b2'),array('status'=>403));
        $res = User::user_favorites($request['post_id']);

        if(isset($res['error'])){
            return new \WP_Error('post_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取收藏列表
    public static function getUserFavoritesList($request){
        $res = User::get_user_favorites_list($request['user_id'],$request['post_paged'],$request['number'],$request['sub']);

        if(isset($res['error'])){
            return new \WP_Error('post_error',$res['error'],array('status'=>403));
        }else{
            $html = '';
            $current_user_id = b2_get_current_user_id();

            foreach ($res['data'] as $k => $v) {
                $html .= '<li>
                    '.($v['thumb'] ? '<div class="following-avatar">
                    <a href="'.$v['link'].'"><img src="'.b2_get_thumb(array('thumb'=>$v['thumb'],'type'=>'fill','width'=>'120','height'=>'74')).'" class="avatar b2-radius" /></a>
                </div>' : '').'
                    <div class="following-info">
                        '.($v['type'] ? '<div class="following-info-type b2-radius">
                        '.$v['type'].'
                        </div>' : '').'
                        <div class="following-name">    
                            <a href="'.$v['link'].'">'.$v['title'].'</a>
                        </div>
                    </div>
                    '.($request['user_id'] == $current_user_id ? '<div class="following-cancel">
                    <button class="empty" onclick="b2AuthorCollections.userFavorites(event,\''.$v['id'].'\')">'.__('取消收藏','b2').'</button>
                </div>' : '').'
                </li>';
            }

            if(!$html){
                $html = B2_EMPTY;
            }

            return array('data'=>$html,'pages'=>$res['pages']);
        }
    }

    //文章顶踩
    public static function postVote($request){
        if(!b2_check_repo()) return new \WP_Error('invitation_error',__('点的太快啦！','b2'),array('status'=>403));
        $res = Post::post_vote($request['type'],$request['post_id']);

        if(isset($res['error'])){
            return new \WP_Error('post_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //文章顶踩数据
    public static function getPostVote($request){
        $res = Post::get_post_vote($request['post_id']);

        if(isset($res['error'])){
            return new \WP_Error('post_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取某人的设置项数据
    public static function getAuthorSettings($request){
        $res = User::get_author_settings($request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //选择自己的头像
    public static function changeAvatar($request){

        $res = User::change_avatar($request['type'],$request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //储存收款码
    public static function saveQrcode($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = User::save_qrcode($request['type'],$request['id'],$request['url'],$request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //解除绑定社交账户
    public static function unBuild($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = User::un_build($request['type'],$request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //保存用户的昵称
    public static function saveNickName($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = User::save_nick_name($request['name'],$request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //保存性别
    public static function saveSex($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = User::save_sex($request['sex'],$request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //保存网址
    public static function saveUrl($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = User::save_url($request['url'],$request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //保存描述
    public static function saveDesc($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = User::save_desc($request['desc'],$request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取收货地址
    public static function getAddresses(){
        $res = User::get_addresses();

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //保存收货地址
    public static function saveAddress($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = User::save_address($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //设为默认地址
    public static function saveDefaultAddress($request){
        //if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = User::save_default_address($request['key'],$request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //删除地址
    public static function deleteAddress($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = User::delete_address($request['key'],$request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //保存用户名
    public static function saveUsername($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = User::save_username($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //修改密码
    public static function editPass($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = User::edit_pass($request['password'],$request['repassword'],$request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取最新一个公告
    public static function getLatestAnnouncement($request){

        $res = \B2\Modules\Common\Announcement::get_latest_announcement($request['count']);

        if(isset($res['error'])){
            return new \WP_Error('announcement_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取用户的邀请码列表
    public static function getUserInvList($request){
        $res = Invitation::get_user_inv_list($request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取用户的公开信息
    public static function getUserPublicData($request){
        $res = User::get_user_public_data($request['user_id'],true);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取小工具用户面板
    public static function getUserWidget($request){
        $res = User::get_user_widget();

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取用户签到数据
    public static function getUserMission($request){
        $res = User::get_user_Mission($request['count'],$request['paged']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //签到数据分页
    public static function getMissionList($request){
        $res = User::get_mission_list($request['type'],$request['count'],$request['paged']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //用户签到
    public static function userMission($request){
        if(!b2_check_repo()) return new \WP_Error('invitation_error',__('点的太快啦！','b2'),array('status'=>403));
        $res = User::user_mission();

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //给用户发私信
    public static function sendDirectmessage($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Directmessage::send_directmessage($request['user_id'],$request['content']);

        if(isset($res['error'])){
            return new \WP_Error('dmsg_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取私信列表
    public static function getUserDirectmessageList($request){
        $res = Directmessage::get_user_directmessage_list($request['paged']);

        if(isset($res['error'])){
            return new \WP_Error('dmsg_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //搜索用户
    public static function searchUsers($request){
        $res = User::search_users($request['nickname']);

        if(isset($res['error'])){
            return new \WP_Error('dmsg_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取私信对话
    public static function getMyDirectmessageList($request){

        $res = Directmessage::get_my_directmessage_list($request['userid'],$request['paged']);

        if(isset($res['error'])){
            return new \WP_Error('dmsg_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取新的私信内容
    public static function getNewDmsg($request){
        $res = Directmessage::get_new_dmsg();

        if(isset($res['error'])){
            return new \WP_Error('dmsg_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取内页中的下载数据
    public static function getDownloadData($request){
        $res = Post::get_post_download_data($request['post_id'],$request['guest']);

        if(isset($res['error'])){
            return new \WP_Error('download_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getDogeVideo($request){
        $res = Player::get_doge_video($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('download_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取下载跳转页面的下载信息
    public static function getDownloadPageData($request){
        $res = Post::get_download_page_data($request['post_id'],$request['index'],$request['i'],$request['guest']);

        if(isset($res['error'])){
            return new \WP_Error('download_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取真实的下载地址
    public static function downloadFile($request){
        $res = Post::download_file($request['token']);

        if(isset($res['error'])){
            return new \WP_Error('download_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //批量检查用户是否关注
    public static function checkFollowByids($request){

        $res = User::check_follow_by_ids($request['ids']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //创建订单
    public static function buildOrder($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Orders::build_order($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('order_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取当前设置的支付方式
    public static function checkPayType($request){
        $res = Pay::check_pay_type($request['pay_type']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //余额支付
    public static function balancePay($request){
        if(!b2_check_repo($request['order_id'])) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Pay::balance_pay($request['order_id']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //积分支付
    public static function creditPay($request){
        if(!b2_check_repo($request['order_id'])) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Pay::credit_pay($request['order_id']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    /**
     * 批量支付
     *
     * @param object $request
     *
     * products:支付产品信息，包含产品ID(id)，购买数量(count)。
     * comment:用户留言
     * address:邮寄地址（实物才会生效）
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function BatchPayment($request){
        $user_id = b2_get_current_user_id();
        if(!b2_check_repo($user_id.'batch_payment')) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Pay::batch_payment($request['products'],$request['comment'],$request['address']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //检查支付结果
    public static function payCheck($request){

        $res = Pay::pay_check($request['order_id']);

        return $res;
    }

    //允许使用的支付形式
    public static function allowPayType($request){
        $res = Pay::allow_pay_type($request['show_type']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取用户的财富信息
    public static function getUserGoldData($request){
        $res = User::get_user_gold_data($request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取通知列表
    public static function getUserMessage($request){
        $res = Message::get_msg_list($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获得财富页面积分、余额记录
    public static function getGoldList($request){
        $res = Gold::get_gold_list($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //提现申请
    public static function cashOut($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = User::cash_out($request['money']);

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取财富排行信息
    public static function getGoldTop($request){
        $res = User::get_top_data($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取用户的订单数据
    public static function getMyOrders($request){
        $res = Orders::get_my_orders($request['user_id'],$request['paged'],isset($request['state']) ? $request['state'] : 'all');

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //卡密充值
    public static function cardPay($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Card::card_pay($request['number'],$request['password']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取vip数据
    public static function getVipInfo($request){
        $res = User::get_vip_info();

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getVerifyUsers(){
        $res = User::get_verify_users();

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取关注二维码
    public static function getVerifyInfo(){
        $res = Verify::get_verify_info();

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //检查用户是否关注了公众号
    public static function checkSubscribe(){
        $res = Verify::check_subscribe();

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //提交认证
    public static function submitVerify($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Verify::submit_verify($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取当前用户的附件
    public static function getCurrentUserAttachments($request){
        $res = User::get_current_user_attachments($request['type'],$request['paged']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取关注登录的二维码
    public static function getLoginQrcode($request){
        $res = Wecatmp::get_login_qrcode();

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //提交关注登录验证码
    public static function mpLogin($request){
        $res = Wecatmp::mp_login($request['code']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //提交关注登录邀请码
    public static function mpLoginInv($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Wecatmp::mp_login_inv($request['token'],$request['inv']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取社交登录连接
    public static function getOauthLink($request){

        $res = User::get_oauth_link();

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //检查投稿权限
    public static function checkUserWriteRole(){
        $res = User::check_user_write_role();

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //发布文章
    public static function insertPost($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Post::insert_post($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //预览文章
    public static function previewPost($request){
        $res = Post::preview_post($request['data']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //删除文章
    public static function deleteDraftPost($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Post::delete_draft_post($request['post_id']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //检查文章的编辑权限
    public static function checkWriteUser($request){
        $res = Post::check_write_user($request['post_id']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取海报信息
    public static function getPosterData($request){

        $res = FileUpload::get_poster_data($request['post_id']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //url转base64
    public static function urlToBase64($request){
        $res = FileUpload::url_to_base64($request['url']);

        if(isset($res['error'])){
            return new \WP_Error('pay_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getTaskData(){
        $res = Task::get_task_data();

        if(isset($res['error'])){
            return new \WP_Error('task_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //通过ID获取商品信息
    public static function getShopItemsData($request){

        $res = Shop::get_shop_items_data($request['ids'],$request['return'],$request['index']);

        if(isset($res['error'])){
            return new \WP_Error('shop_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //领取优惠劵
    public static function ShopCouponReceive($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Coupon::coupon_receive($request['id']);

        if(isset($res['error'])){
            return new \WP_Error('shop_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取我的优惠劵
    public static function getMyCoupons(){
        $res = Coupon::get_my_coupons();

        if(isset($res['error'])){
            return new \WP_Error('shop_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //删除我的优惠劵
    public static function deleteMyCoupon($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Coupon::delete_my_coupon($request['id']);

        if(isset($res['error'])){
            return new \WP_Error('shop_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //通过商品ID获取优惠劵信息
    public static function getCouponsByPostId($request){

        $res = Coupon::check_coupon($request['ids']);

        if(isset($res['error'])){
            return new \WP_Error('shop_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //积分抽奖
    public static function shopLottery($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Shop::shop_lottery_action($request['post_id'],$request['address']);

        if(isset($res['error'])){
            return new \WP_Error('shop_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取当前用户的默认邮箱
    public static function getEmail($request){
        $user_id = b2_get_current_user_id();
        $data = get_userdata($user_id);
        if(isset($data->user_email) && $data->user_email && strpos(B2_HOME_URI,explode('@',$data->user_email)[1]) === false) {
            return new \WP_REST_Response($data->user_email,200);
        }else{
            return new \WP_REST_Response('',200);
        }
    }

    //获取用户购买信息
    public static function getUserBuyResout($request){
        $res = Shop::get_user_buy_resout($request['post_id']);

        if(isset($res['error'])){
            return new \WP_Error('shop_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getOrderExpress($request){
        $res = Shop::shop_get_express_data($request['com'],$request['id'],$request['address']);

        if(isset($res['error'])){
            return new \WP_Error('shop_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //分销信息
    public static function getMyDistributionData($request){

        $res = Distribution::get_my_distribution_data($request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('shop_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //分销订单
    public static function getMyDistributionOrders($request){
        $res = Distribution::get_my_distribution_orders($request['user_id'],$request['paged']);

        if(isset($res['error'])){
            return new \WP_Error('shop_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //分销订单
    public static function getMyPartner($request){
        $res = Distribution::get_my_partner($request['user_id'],$request['paged']);

        if(isset($res['error'])){
            return new \WP_Error('shop_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //用户确认收货
    public static function userChangeOrderState($request){
        $res = Orders::user_change_order_state($request['order_id']);

        if(isset($res['error'])){
            return new \WP_Error('shop_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //提交工单
    public static function submitRequest($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Document::submit_request($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('document_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //发布快讯
    public static function submitNewsflashes($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Newsflashes::submit_newsflashes($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('newsflashes_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取快讯列表
    public static function getNewsflashesList($request){
        $res = Newsflashes::get_newsflashes_data($request['paged'],$request['term'],$request['user_id']);

        if(isset($res['error'])){
            return new \WP_Error('newsflashes_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取快讯小工具
    public static function getWidgetNewsflashes($request){
        $res = Newsflashes::get_widget_Newsflashes($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('newsflashes_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取当前用户权限和圈子信息
    public static function getCurrentUserCircleData($request){
        $user_id = b2_get_current_user_id();

        $res = Circle::check_topic_role($user_id,$request['circle_id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //搜索圈子
    public static function circleSearch($request){
        $res = Circle::circle_search($request['key']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取圈子卡片
    public static function insertTopicCard($request){
        $res = Circle::insert_topic_card($request['id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //发布帖子
    public static function insertCircleTopic($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Circle::insert_circle_topic($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //创建圈子
    public static function createCircle($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Circle::create_circle($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //热门圈子
    public static function getCirclesList($request){

        $res = Circle::get_circles_list($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取话题列表
    public static function getTopicList($request){
        $res = Circle::get_topic_list($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取json格式评论列表
    public static function getTopicCommentList($request){

        $user_id = b2_get_current_user_id();

        $res = Comment::get_comments_json($request['topicId'],$request['paged'],$request['orderBy']);
        $role = apply_filters('b2_circle_user_topic_role',array('user_id'=>$user_id,'topic_id'=>$request['topicId']));

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            $allow = Circle::allow_read($request['topicId'],$user_id);

            if(!$role['is_admin'] && !$role['is_circle_admin'] && !$role['in_circle'] && !$allow['allow']){
                return new \WP_Error('circle_error',__('无权查看评论','b2'),array('status'=>403));
            }

            return new \WP_REST_Response($res,200);
        }
    }

    public static function getChildComments($request){
        $res = Comment::get_child_comments($request['parent'],$request['paged']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    /*数据更新*/
    public static function ajaxupdate($request){

        $type = $request['type'];
        $paged = $request['paged'];

        if(!method_exists('B2\Modules\Common\UpdateData', $type)) return array('error'=>__('非法请求','b2'));

        $res = UpdateData::$type($paged);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取所有圈子数据
    public static function getAllCircleData($request){

        $res = Circle::get_all_circles($request['tag'],$request['paged']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //话题置顶
    public static function setSticky($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Circle::set_sticky($request['topic_id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //话题加精
    public static function setBest($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Circle::set_best($request['topic_id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //通过ID获取某个话题内容
    public static function getDataByTopicId($request){
        $res = Circle::get_data_by_topic_id($request['topic_id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //删除话题
    public static function deleteTopic($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Circle::delete_topic($request['topic_id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function deleteComment($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Comment::delete_comment($request['comment_id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

     //话题审核
    public static function topicChangeStatus($request){
        $res = Circle::topic_change_status($request['topic_id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //通过ID获取话题数据
    public static function getCircleDataByCircleIds($request){

        $res = Circle::get_circle_data_by_circle_ids($request['ids']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //加入圈子（包括直接入圈和申请入圈）
    public static function joinCircle($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Circle::join_circle($request['circle_id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取圈子用户
    public static function getCircleUserList($request){
        $res = Circle::get_circle_users($request['circleId'],$request['paged']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //删除圈友
    public static function removeUserFormCircle($request){
        $res = Circle::remove_user_form_circle($request['user_id'],$request['circle_id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //审核会员入圈
    public static function changeUserRole($request){
        $res = Circle::change_user_role($request['user_id'],$request['circle_id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //话题投票
    public static function topicVote($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Circle::topic_vote($request['topic_id'],$request['index']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function topicGuess($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Circle::topic_guess($request['topic_id'],$request['index']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //回答问题
    public static function submitAnswer($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Circle::submit_topic_answer($request);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取回答列表
    public static function getTopicAnswerList($request){
        $res = Circle::get_topic_answer_list($request['topicId'],$request['paged']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //采纳答案
    public static function answerRight($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Circle::answer_right($request['answerId']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //删除答案
    public static function deleteAnswer($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Circle::delete_answer($request['answerId']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //编辑话题
    public static function getEditData($request){

        $res = Circle::get_edit_data($request['topic_id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    //获取广场顶部圈子分类
    public static function getCircleTopCats($request){

        $res = Circle::get_circle_top_cats();

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getPostGG($request){
        $res = Post::get_post_gg((int)$request['post_id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getWriteCountent($request){
        $res = Post::get_write_countent((int)$request['post_id']);

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function poAsk($request){
        if(!b2_check_repo()) return new \WP_Error('user_error',__('操作频次过高','b2'),array('status'=>403));
        $res = Ask::po_ask($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getAskData($request){
        $res = Ask::get_ask_data($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('circle_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getAskEditData($request){
        $res = Ask::get_edit_data($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getAanswerHtml($request){
        $res = Ask::ask_answer_html($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function poAskAnswer($request){
        $res = Ask::po_ask_answer($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getAnswerData($request){
        $res = Ask::get_answer_data($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function getEditAnswerData($request){
        $res = Ask::get_edit_answer_data($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function setAnswerRight($request){
        $res = Ask::answer_right($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }

    public static function bestAnswer($request){
        $res = Ask::answer_right($request->get_params());

        if(isset($res['error'])){
            return new \WP_Error('user_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        }
    }
}
