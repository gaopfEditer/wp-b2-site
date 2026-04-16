<?php
/**
 * 网络相册图片防盗链破解程序 - PHP版
 *
 * 使用方法：
 *
 *   http://yourdomain/url.php?url=http://hiphotos.baidu.com/verdana/pic/item/baidupicture.jpg&referer=
 *   其中url是指需要破解的图片URL,而referer是为了兼容一些不需要设置来路域名才能显示的相册,例如360我喜欢网,必须设置来路为空才能正常浏览. 所以,此时应该设置referer为1
 *
 * @author 雪狐博客
 * @version 1.0
 * @since  July 16, 2012
 * @URL http://www.xuehuwang.com
 */
class Frivoller
{
  /**
   * HTTP 版本号 (1.0, 1.1) , 百度使用的是 version 1.1
   *
   * @var string
   */
  protected $version;
  /**
   * 进行HTTP请求后响应的数据
   *
   * @var 字符串格式
   */
  protected $body;
  /**
   * 需要获取的远程URL
   *
   * @var 字符串格式
   */
  protected $link;
  /**
   * An array that containing any of the various components of the URL.
   *
   * @var array
   */
  protected $components;
  /**
   * HTTP请求时HOST数据
   *
   * @var 字符串
   */
  protected $host;
  /**
   * The path of required file.
   * (e.g. '/verdana/abpic/item/mygirl.png')
   *
   * @var string
   */
  protected $path;
  /**
   * The HTTP referer, extra it from original URL
   *
   * @var string
   */
  protected $referer;
  /**
   * The HTTP method, 'GET' for default
   *
   * @var string
   */
  protected $method  = 'GET';
  /**
   * The HTTP port, 80 for default
   *
   * @var int
   */
  protected $port   = 80;
  /**
   * Timeout period on a stream
   *
   * @var int
   */
  protected $timeout = 100;
  /**
   * The filename of image
   *
   * @var string
   */
  protected $filename;
  /**
   * The ContentType of image file.
   * image/jpeg, image/gif, image/png, image
   *
   * @var string
   */
  protected $contentType;
  /**
   * Frivoller constructor
   *
   * @param string $link
   */
  public function __construct($link,$referer='')
  {
    $this->referer = $referer;
    // parse the http link
    $this->parseLink($link);
    // begin to fetch the image
    $stream = pfsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
    if (!$stream){
      header("Content-Type: $this->contentType;");
      echo $this->CurlGet($link);
    }else{
      fwrite($stream, $this->buildHeaders());
      $this->body = "";
      $img_size = get_headers($link,true);
      while (!feof($stream)) {
        $this->body .= fgets($stream, $img_size['Content-Length']);
        //fwrite($jpg,fread($stream, $img_size['Content-Length']));
      }
      $content = explode("\r\n\r\n", $this->body, 2);
      $this->body = $content[1];
      fclose($stream);
      // send 'ContentType' header for saving this file correctly
      // 如果不发送CT，则在试图保存图片时，IE7 会发生错误 (800700de)
      // Flock, Firefox 则没有这个问题，Opera 没有测试
      header("Content-Type: $this->contentType;");
      header("Cache-Control: max-age=315360000");
      echo $this->body;
       //保存图片
       //file_put_contents('hello.jpg', $this->body);
    }
  }
  /**
   * Compose HTTP request header
   *
   * @return string
   */
  private function buildHeaders()
  {
    $request = "$this->method $this->path HTTP/1.1\r\n";
    $request .= "Host: $this->host\r\n";
    $request .= "Accept-Encoding: gzip, deflate\r\n";
    $request .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.0; zh-CN; rv:1.9.0.1) Gecko/2008070208 Firefox/3.0.1\r\n";
    $request .= "Content-Type: image/jpeg\r\n";
    $request .= "Accept: */*\r\n";
    $request .= "Keep-Alive: 300\r\n";
    $request .= "Referer: $this->referer\r\n";
    $request .= "Cache-Control: max-age=315360000\r\n";
    $request .= "Connection: close\r\n\r\n";
    return $request;
  }
  /**
   * Strip initial header and filesize info
   */
  private function extractBody(&$body)
  {
    // The status of link
    if(strpos($body, '200 OK') > 0) {
      // strip header
      $endpos = strpos($body, "\r\n\r\n");
      $body = substr($body, $endpos + 4);
      // strip filesize at nextline
      $body = substr($body, strpos($body, "\r\n") + 2);
    }
  }
  /**
   * Extra the http url
   *
   * @param $link
   */
  private function parseLink($link)
  {
    $this->link     = $link;
    $this->components  = parse_url($this->link);
    $this->host     = $this->components['host'];
    $this->path     = $this->components['path'];
    if(empty($this->referer)){
      $this->referer   = $this->components['scheme'] . '://' . $this->components['host'];
    }elseif($this->referer == '1'){
      $this->referer   = '';
    }
    $this->filename   = basename($this->path);
    // extract the content type
    $ext = substr(strrchr($this->path, '.'), 1);
    if ($ext == 'jpg' or $ext == 'jpeg') {
      $this->contentType = 'image/pjpeg';
    }
    elseif ($ext == 'gif') {
      $this->contentType = 'image/gif';
    }
    elseif ($ext == 'png') {
      $this->contentType = 'image/x-png';
    }
    elseif ($ext == 'bmp') {
      $this->contentType = 'image/bmp';
    }
    else {
      $this->contentType = 'application/octet-stream';
    }
  }
  //抓取网页内容
  function CurlGet($url){
    $url = str_replace('&','&',$url);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_REFERER,$url);
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; SeaPort/1.2; Windows NT 5.1; SV1; InfoPath.2)");
    curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie.txt');
    curl_setopt($curl, CURLOPT_COOKIEFILE, 'cookie.txt');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
    $values = curl_exec($curl);
    curl_close($curl);
    return $values;
  }
}
/**
 * 取得根域名
 *
 * @author   lonely
 * @create    2011-3-11
 * @version  0.11
 * @lastupdate lonely
 * @package Sl
*/
class RootDomain{
   private static $self;
  private $domain=null;
  private $host=null;
  private $state_domain;
  private $top_domain;
  /**
   * 取得域名分析实例
   * Enter description here ...
   */
  public static function instace(){
    if(!self::$self)
      self::$self=new self();
    return self::$self;
  }
  public function __construct(){
    $this->state_domain=array(
      'al','dz','af','ar','ae','aw','om','az','eg','et','ie','ee','ad','ao','ai','ag','at','au','mo','bb','pg','bs','pk','py','ps','bh','pa','br','by','bm','bg','mp','bj','be','is','pr','ba','pl','bo','bz','bw','bt','bf','bi','bv','kp','gq','dk','de','tl','tp','tg','dm','do','ru','ec','er','fr','fo','pf','gf','tf','va','ph','fj','fi','cv','fk','gm','cg','cd','co','cr','gg','gd','gl','ge','cu','gp','gu','gy','kz','ht','kr','nl','an','hm','hn','ki','dj','kg','gn','gw','ca','gh','ga','kh','cz','zw','cm','qa','ky','km','ci','kw','cc','hr','ke','ck','lv','ls','la','lb','lt','lr','ly','li','re','lu','rw','ro','mg','im','mv','mt','mw','my','ml','mk','mh','mq','yt','mu','mr','us','um','as','vi','mn','ms','bd','pe','fm','mm','md','ma','mc','mz','mx','nr','np','ni','ne','ng','nu','no','nf','na','za','aq','gs','eu','pw','pn','pt','jp','se','ch','sv','ws','yu','sl','sn','cy','sc','sa','cx','st','sh','kn','lc','sm','pm','vc','lk','sk','si','sj','sz','sd','sr','sb','so','tj','tw','th','tz','to','tc','tt','tn','tv','tr','tm','tk','wf','vu','gt','ve','bn','ug','ua','uy','uz','es','eh','gr','hk','sg','nc','nz','hu','sy','jm','am','ac','ye','iq','ir','il','it','in','id','uk','vg','io','jo','vn','zm','je','td','gi','cl','cf','cn','yr'
    );
    $this->top_domain=array('com','arpa','edu','gov','int','mil','net','org','biz','info','pro','name','museum','coop','aero','xxx','idv','me','mobi');
    $this->url=$_SERVER['HTTP_HOST'];
  }
  /**
   * 设置URL
   * Enter description here ...
   * @param string $url
   */
  public function setUrl($url=null){
    $url=$url?$url:$this->url;
    if(empty($url))return $this;
    if(!preg_match("/^http:/is", $url))
      $url="http://".$url;
    $url=parse_url(strtolower($url));
    $urlarr=explode(".", $url['host']);
    $count=count($urlarr);
    if ($count<=2){
      $this->domain=$url['host'];
    }else if ($count>2){
      $last=array_pop($urlarr);
      $last_1=array_pop($urlarr);
      if(in_array($last, $this->top_domain)){
        $this->domain=$last_1.'.'.$last;
        $this->host=implode('.', $urlarr);
      }else if (in_array($last, $this->state_domain)){
        $last_2=array_pop($urlarr);
        if(in_array($last_1, $this->top_domain)){
          $this->domain=$last_2.'.'.$last_1.'.'.$last;
          $this->host=implode('.', $urlarr);
        }else{
          $this->host=implode('.', $urlarr).$last_2;
          $this->domain=$last_1.'.'.$last;
        }
      }
    }
    return $this;
  }
  /**
   * 取得域名
   * Enter description here ...
   */
  public function getDomain(){
    return $this->domain;
  }
  /**
   * 取得主机
   * Enter description here ...
   */
  public function getHost(){
    return $this->host;
  }
}
$referer = array('xuehuwang.com','zangbala.cn','qianzhebaikou.net','sinaapp.com','163.com','sina.com.cn','weibo.com','abc.com','static.7b2.com');
// Get the url, maybe you should check the given url
if (isset($_GET['url']) and $_GET['url'] != '') {
  //获取来路域名
  $site = (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
  //匹配是否是一个图片链接
  if(preg_match('/(http|https|ftp|rtsp|mms):(\/\/|\\\\){1}((\w)+[.]){1,}([a-zA-Z]|[0-9]{1,3})(\S*\/)((\S)+[.]{1}(gif|jpg|png|bmp))/i',$_GET['url'])){
    if(!empty($site)){
      $tempu = parse_url($site);
      $host = $tempu['host'];
      $root = new RootDomain();
      $root->setUrl($site);
      if(in_array($root->getDomain(),$referer)){
        $img_referer = (isset($_GET['referer']) && !empty($_GET['referer']))? trim($_GET['referer']) : '';
        new Frivoller($_GET['url'],$img_referer);
      }
    }else{
      $img_referer = (isset($_GET['referer']) && !empty($_GET['referer']))? trim($_GET['referer']) : '';
      new Frivoller($_GET['url'],$img_referer);
    }
  }
}
?>