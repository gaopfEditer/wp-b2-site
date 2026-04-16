//挂载axios
axios.defaults.timeout = 300000;
Vue.prototype.$http = axios //需要鉴权
Vue.prototype.$https = axios //不需要鉴权
Vue.prototype.$b2JsonData = []
var b2_rest_url = b2_global.rest_url+'b2/v1/'

const b2timedom = document.querySelectorAll('.b2timeago');

if(b2timedom.length > 0){
    timeago.render(b2timedom, b2_global.language);
}

var b2zoom = new Zooming({enableGrab:true,scrollThreshold:0,transitionDuration:0.2,scaleBase:0.96,scaleExtra:1,customSize:'100%'});

var B2ClientWidth = document.body.clientWidth;

const b2token = b2getCookie('b2_token')

var lazyLoadInstance = new LazyLoad({
    elements_selector: ".lazy",
    threshold: 0,
});

if(b2token){
    Vue.prototype.$http.defaults.headers.common['Authorization'] = 'Bearer ' + b2token
}

Vue.prototype.$store = new Vuex.Store({
    state: {
      userData: '',
      userRole:'',
      announcement:'',
      oauthLink:'',
      openOauth:false,
      authorData:'',
      carts:{},
      _carts:{},
      canImg:false,
      xieyi:0
    },
    mutations: {
      setUserData (state,data) {
        state.userData = data
      },
      setUserRole (state,data) {
        state.userRole = data
      },
      setAnnouncement (state,data) {
        state.announcement = data
      },
      setOauthLink(state,data){
        state.oauthLink = data
      },
      setOpenOauth(state,data){
        state.openOauth = data
      },
      setauthorData(state,data){
        state.authorData = data
      },
      setcartsData(state,data){
        state.carts = data
      },
      set_cartsData(state,data){
        state._carts = data
      },
      setcanImage(state,data){
          state.canImg = data
      }
    }
})

var passiveSupported = false;

try {
  var options = Object.defineProperty({}, "passive", {
    get: function() {
      passiveSupported = true;
    }
  });

  window.addEventListener("test", null, options);
} catch(err) {}


b2WidgetImageLoaded()

function b2WidgetImageLoaded(){
    imagesLoaded( document.querySelectorAll('.widget-area'), function( instance ) {
        b2SidebarSticky()
    });
}

function b2isWeixin() { //判断是否是微信
    var ua = navigator.userAgent.toLowerCase();
    return ua.match(/MicroMessenger/i) == "micromessenger";
};

document.ready = function (callback) {
    ///兼容FF,Google
    if (document.addEventListener) {
        document.addEventListener('DOMContentLoaded', function () {
            document.removeEventListener('DOMContentLoaded', arguments.callee, false);
            callback();
        }, passiveSupported ? { passive: true } : false)
    }
     //兼容IE
    else if (document.attachEvent) {
        document.attachEvent('onreadystatechange', function () {
              if (document.readyState == "complete") {
                    document.detachEvent("onreadystatechange", arguments.callee);
                    callback();
               }
        })
    }
    else if (document.lastChild == document.body) {
        callback();
    }
}

var topsearch = new Vue({
    el:'.top-search',
    data:{
        type:'post',
        data:'',
        show:false,
        b2token:false
    },
    mounted(){
        if(!this.$refs.topsearch) return
        this.b2token = b2token
        const s = this.$refs.topsearch.getAttribute('data-search')
        this.data = JSON.parse(s)

        if(b2GetQueryVariable('type') && b2GetQueryVariable('s')){
            this.type = b2GetQueryVariable('type')
        }else{
            this.type = Object.keys(this.data)[0]
        }
    }
})

var mobileMenu = new Vue({
    el:'#mobile-menu-button',
    data:{
        show:false,
        b2token:false
    },
    mounted(){
        if(B2ClientWidth >= 768) return
        this.b2token = b2token
        this.dorpMenu()
    },
    methods:{
        dorpMenu(){
            let drop = document.querySelectorAll('.has_children .b2-arrow-down-s-line')
            if(drop.length > 0){
                for (let index = 0; index < drop.length; index++) {
                    drop[index].onclick = (event)=>{
                        event.stopPropagation()
                        event.preventDefault()
                        if(event.target.parentNode.parentNode.className.indexOf(' show') == -1){
                            this.hideAll()
                            event.target.parentNode.parentNode.className += ' show'
                        }else{
                            this.hideAll()
                        }
                    }
                }
            }
        },
        hideAll(){
            let sub = document.querySelectorAll('.has_children .sub-menu')
            for (let i = 0; i < sub.length; i++) {
                sub[i].parentNode.className = sub[i].parentNode.className.replace(' show','')
            }
        },
        showMenu(val){
            const menu = document.querySelector('#mobile-menu')
                body = document.querySelector('html')
            if(val){
                menu.className += ' show-menu-box'
                body.className += ' m-open'
                //bodyScrollLock.lock(this.$refs.MobileMenu)
                this.show = true
            }else{
                menu.className = menu.className.replace(' show-menu-box','')
                setTimeout(() => {
                    body.className = body.className.replace(' m-open','')
                }, 300);
                //bodyScrollLock.unlock(this.$refs.MobileMenu)
                this.show = false
            }
        },
        showAc(){
            this.show = !this.show
            this.showMenu(this.show)
        }
    }
})

Vue.component('search-box', {
    props: ['show','searchType'],
    template:b2_global.search_box,
    data(){
        return{
            showSearch:false,
            type:'post'
        }
    },
    mounted(){
        const search = document.querySelector('.top-search')
        if(search){
            const data = search.getAttribute('data-search');

            if(data){
                let json = JSON.parse(data)
                this.type = Object.keys(json)[0]

            }

        }
    },
    methods:{
        close(){
            this.$emit('close')
        }
    },
    watch:{
        searchType(val){
            this.type = val
        }
    }
})

var b2SearchBox = new Vue({
    el:'#search-box',
    data:{
        searchType:'all',
        show:false
    },
    methods:{
        close(){
            this.show = !this.show
        }
    }
})

var userTools = new Vue({
    el:'.top-user-info',
    data:{
        showDrop:false,
        role:{
            write:true,
            newsflashes:false,
            binding_login:false,
            create_circle:false,
            create_topic:false,
            distribution:false,
            user_data:''
        },
        b2token:false
    },
    computed:{
        userData(){
            return this.$store.state.userData;
        }
    },
    mounted() {
        this.b2token = b2token

        document.onclick = (e)=>{
            this.hideAction()
        }

        if(b2token){

            let footer_text = document.querySelector('#footer-menu-user');
            if(footer_text){
                footer_text.innerText = b2_global.js_text.global.my;
            }

            this.$http.post(b2_rest_url +'getUserInfo').then(res=>{
                this.role = res.data

                this.$store.commit('setUserRole', res.data)
                this.$store.commit('setUserData', res.data.user_data)
                this.$store.commit('setcanImage', res.data.can_img)
                b2AsideBar.count = res.data.carts

                topMenuLeft.count = res.data.msg_unread

                b2bindLogin.type = res.data.binding_login
                payCredit.user.credit = res.data.user_data.credit

                this.$nextTick(()=>{
                    b2tooltip('.user-tips')
                    lazyLoadInstance.update()
                })

            }).catch(err=>{
                this.loginOut()
            })
        }else{
            this.$https.get(b2_rest_url+'getOauthLink').then(res=>{
                this.$store.commit('setOauthLink',res.data)
                this.$nextTick(()=>{
                    lazyLoadInstance.update()
                })
                
            })
            let ref = b2GetQueryVariable('ref')
            if(ref){
                b2setCookie('ref',ref)
            }

        }

    },
    methods: {
        hideAction(){
            this.showDrop = false
            if(typeof b2Comment !== 'undefined'){
                b2Comment.show.smile = false
                b2Comment.show.image = false
            }

            if(typeof b2infomation !== 'undefined'){
                b2infomation.showFliter = false
            }

            if(typeof b2TaxTop !== 'undefined'){
                b2TaxTop.showFliter.hot = false
                b2TaxTop.showFliter.cat = false
            }

            if(typeof topsearch !== 'undefined'){
                topsearch.show = false
            }

            if(typeof writeHead !== 'undefined'){
                Qmsg.closeAll()
            }

            if(typeof b2CirclePostBox !== 'undefined'){
                b2CirclePostBox.ask.focus = false
                if(b2CirclePostBox.ask.userInput){
                    b2CirclePostBox.ask.picked = true
                }
                b2CirclePostBox.role.show = false
                b2CirclePostBox.smileShow = false
            }

            if(typeof poAsk !== 'undefined'){
                poAsk.ask.focus = false
                if(poAsk.userInput){
                    poAsk.ask.picked = true
                }
            }

            if(typeof b2CircleList !== 'undefined'){
                if(!b2CircleList.$refs.topicForm) return
                if(!b2CircleList.commentBox.content){
                    if(b2CircleList.$refs.topicForm.value == ''){
                        b2CircleList.commentBox.focus = false
                    }
                    b2CircleList.smileShow = false
                }

                //b2CircleList.circle.current = b2CircleList.circle.picked.type
                b2CircleList.circle.showBox = ''
                b2CircleList.topicFliter.show = false
                b2CircleList.answer.showSmile = false
            }

            b2AsideBar.close()
        },
        login(type){
            login.show = true
            login.loginType = type
        },
        showDropMenu(){
            this.showDrop = !this.showDrop
        },
        loginOut(){
            axios.get(b2_rest_url+'loginOut').then(res=>{
                b2delCookie('b2_token')
                b2CurrentPageReload()
            }).catch(err=>{
                b2delCookie('b2_token')
                b2CurrentPageReload()
            })
        },
        out(){
            this.loginOut()
        },
        // goLinkRegisterPage(){
        //     if(!b2token){
        //         this.login(1)
        //         return
        //     }
        // },
        goUserPage(type){
            if(type === 'back'){
                window.location = b2getCookie('b2_back_url')

            }else
            if(type){
                if(!b2token){
                    this.login(1)
                    return
                }
                window.location = this.$store.state.userData.link+'/'+type
            }else{
                if(!b2token){
                    this.login(1)
                    return
                }
                window.location = this.$store.state.userData.link
            }
        }
    },
})

var topMenuLeft = new Vue({
    el:'.change-theme',
    data:{
        theme:'light',
        count:0,
        login:false
    },
    mounted(){
        if(b2token){
            this.login = true
        }
    },
    methods:{
        changeTheme(type){
            this.theme = type
        },
        showBox(){
            postPoBox.show = true
        },
        go(type){
            if(type === 'orders'){
                if(b2token){
                    window.location.href = this.$store.state.userData.link+'/orders'
                }else{
                    login.show = true
                    login.loginType = 1
                }
            }
            if(type === 'requests'){
                if(b2token){
                    window.location.href = b2_global.home_url+'/requests'
                }else{
                    login.show = true
                    login.loginType = 1
                }
            }
        }
    }
})

var headerTools = new Vue({
    el:'.header-tools',
    computed:{
        userData(){
            return this.$store.state.userData;
        }
    },
    methods:{
        showSearch(){
            b2SearchBox.close()
        },
    }
})

//登陆与注册
Vue.component('login-box', {
    props: ['show','allowRegister','checkType','loginType','loginText','invitation','invitationLink','invitationText','imgBoxCode'],
    template:b2_global.login,
    data(){
        return {
            data:{
                'nickname':'',
                'username':'',
                'password':'',
                'code':'',
                'img_code':'',
                'invitation_code':'',
                'token':'',
                'smsToken':'',
                'luoToken':'',
                'confirmPassword':'',
                'loginType':''
            },
            invitationPass:false,
            eye:false,
            codeImg:'',
            locked:false,
            showLuo:false,
            issetLuo:false,
            imgLocked:false,
            SMSLocked:false,
            count:60,
            repass:false,
            type:'',
            isWeixin:false,
        }
    },
    computed:{
        oauth(){
            return this.$store.state.oauthLink
        },
        openOauth(){
            return this.$store.state.openOauth
        }
    },
    created(){
        window.getResponse = (resp) => {
            if(this.type == 'edit'){
                b2AuthorEdit.sendCode(resp)
                recaptcha.close()
            }else{
                this.data.img_code = resp
                this.$nextTick(()=>{
                    recaptcha.close()
                    this.sendSMS()
                })
            }
        }

        this.isWeixin = b2isWeixin()
    },
    methods:{
        close(val){
            this.$emit('close-form',val)
        },
        loginAc(val){
            this.$emit('login-ac',val)
        },
        loginSubmit(e){
            e.preventDefault()

            if(this.locked == true) return
            this.locked = true

            //邀请码
            if(this.invitation != 0 && (this.loginType == 2 && !this.invitationPass)){
                this.invitationCheck()
            }else if(this.loginType == 1){
                //登录
                this.$https.post(b2_global.rest_url+'jwt-auth/v1/token',Qs.stringify(this.data)).then(res=>{
 
                    b2CurrentPageReload()
                    return

                }).catch(err=>{
                    Qmsg['warning'](err.response.data.message,{html:true,timeout:10000});
                    this.locked = false
                })

            }else if(this.loginType == 2){
                //注册
                this.$https.post(b2_rest_url+'regeister',Qs.stringify(this.data)).then(res=>{
                    b2CurrentPageReload()

                    return

                }).catch(err=>{

                    Qmsg['warning'](err.response.data.message,{html:true});
                    this.locked = false
                })
            }else if(this.loginType == 3){
                this.$https.post(b2_rest_url+'forgotPass',Qs.stringify(this.data)).then(res=>{
                    this.loginAc(4)
                    this.locked = false
                }).catch(err=>{

                    Qmsg['warning'](err.response.data.message,{html:true});
                    this.locked = false
                })
            }else if(this.loginType == 4){
                this.$https.post(b2_rest_url+'resetPass',Qs.stringify(this.data)).then(res=>{
                    this.repass = true
                    this.data.password = ''
                    this.loginAc(1)
                    this.locked = false
                }).catch(err=>{

                    Qmsg['warning'](err.response.data.message,{html:true});
                    this.locked = false
                })
            }
        },
        b2IsPhoneAvailable(val){
            if(b2IsPhoneAvailable(val)) return true
            return false
        },
        invitationCheck(){
            this.$https.post(b2_rest_url+'invitationCheck','code='+this.data.invitation_code).then(res=>{
                this.invitationPass = true
                this.locked = false
                this.showLuo = true
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        sendCode(){
            recaptcha.show = true
            this.close(false)
        },
        getCode(){
            if(this.imgLocked) return
            this.codeImg = ''
            this.imgLocked = true
            this.$https.post(b2_rest_url+'getRecaptcha','number=4&width=186&height=50').then(res=>{
                if(res.data){
                    this.codeImg = res.data.base
                    this.data.token = res.data.token
                }
                this.imgLocked = false
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.imgLocked = false
            })
        },
        changeCode(){
            this.getCode()
        },
        isEmail(email){

            var pattern= /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
            return pattern.test(email);

        },
        checkTips(){
            let text = ''

            if(this.checkType == 'tel'){
                text = b2_global.js_text.global.check_type.tel
            }

            if(this.checkType == 'email'){
                text = b2_global.js_text.global.check_type.mail
            }

            if(this.checkType == 'telandemail'){
                if(this.isEmail(this.data.username)){
                    text = b2_global.js_text.global.check_type.mail
                }else{
                    text = b2_global.js_text.global.check_type.tel
                }

            }

            if(text){
                Qmsg.info(b2_global.js_text.global.check_message,{
                    showClose:true,
                    autoClose:false
                })
            }
        },
        sendSMS(){
            if(this.SMSLocked) return
            this.SMSLocked = true
            this.data.loginType = this.loginType
            this.$https.post(b2_rest_url+'sendCode',Qs.stringify(this.data)).then(res=>{
                if(res.data.token){
                    this.data.smsToken = res.data.token
                }
                this.SMSLocked = false
                this.countdown()

                this.checkTips()


            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.SMSLocked = false
            })
        },
        markHistory(mp,e){
            if(!this.$store.state.xieyi && this.loginType == 2){
                e.preventDefault();
                Qmsg['warning'](b2_global.js_text.global.xieyi,{html:true});
                return
            }
            if(mp){
                this.close()
                mpCode.show = true
                mpCode.type = this.loginType
            }
            b2setCookie('b2_back_url',window.location.href)
        },
        countdown(){
            if(this.count <= 1 ){
                this.count = 60
                return
            }
            this.count --
            setTimeout(()=>{
                this.countdown()
            },1000)
        },
        resetPssNext(){
            this.loginAc(4)
        }
    },
    watch:{
        oauth(val){
            if(val){
                Object.keys(val).forEach((key)=>{
                    if(val[key].open){
                        this.$store.commit('setOpenOauth',true)
                    }
                });

                if(b2isWeixin() && b2_global.wx_mp_in_login == 1){
                    if(!b2token && document.querySelector('#open-page') === null){
                        b2setCookie('b2_back_url',window.location.href)
                        window.location.href = this.oauth.weixin.url
                    }
                }
            }
        },
        loginType(val){
            if((this.invitation == 0 || this.invitationPass) && val == 2){
                this.showLuo = true
            }else{
                this.showLuo = false
            }

            if(this.issetLuo){
                LUOCAPTCHA && LUOCAPTCHA.reset()
            }
        },
        invitationPass(val){
            if(this.issetLuo && val){
                setTimeout(()=>{
                    LUOCAPTCHA && LUOCAPTCHA.reset()
                },100)
            }
        },
        show(val){
            if(val && this.checkType == 'text'){
                this.getCode()
                this.type = ''
            }
        },
        imgBoxCode(val){
            this.data.img_code = val.value
            this.data.token = val.token
        },
        showLuo(val){
            if(this.show && this.checkType == 'luo' && val && !this.issetLuo){
                let s = document.createElement('script')
                s.id = 'luosimao'
                s.type = 'text/javascript'
                s.src = '//captcha.luosimao.com/static/dist/api.js'
                document.getElementsByTagName("head")[0].appendChild(s)
                this.issetLuo = true
            }
        }
    }
})

Vue.component('mp-box', {
    props:['show','invitation','invitationLink','invitationText'],
    template:b2_global.mp_box,
    data(){
        return{
            code:'',
            locked:false,
            token:false,
            invitationCode:'',
            locked:false,
            qrcode:'',
            t:false,
            count:100
        }
    },
    computed:{
        oauthLink(){
            return this.$store.state.oauthLink
        }
    },
    methods:{
        close(){
            this.$emit('close')
            this.t = null
        },
        submit(){

            if(this.locked == true) return
            this.locked = true

            if(this.count <= 1 || this.t === null){
                this.t = null
                this.count = 100
                return
            }

            this.$http.post(b2_rest_url+'mpLogin','code='+this.code).then(res=>{

                if(res.data === 'waiting'){
                    this.locked = false
                    this.t = setTimeout(()=>{
                        this.submit()
                    },1000)
                    return
                }

                if(res.data.type === 'invitation'){
                    this.token = res.data.token
                }else{
                    if(res.data === true){
                        b2CurrentPageReload()
                    }else{
                        this.$store.commit('setUserData', res.data)

                        Vue.prototype.$http.defaults.headers.common['Authorization'] = 'Bearer ' + b2token
                        b2CurrentPageReload()

                    }
                }
                this.t = null
                this.locked = false
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        checkInv(){
            if(this.locked == true) return
            this.locked = true

            this.$https.post(b2_rest_url+'mpLoginInv','token='+this.token+'&inv='+this.invitationCode).then(res=>{
                if(res.data.token){
                    b2CurrentPageReload()
                }
                this.locked = false
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        getQrcode(){
            this.$https.post(b2_rest_url+'getLoginQrcode').then(res=>{
                this.code = res.data.sence_id
                this.qrcode = res.data.qrcode
                this.submit()
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
            })
        }
    },
    watch:{
        show(val){
            if(val){

                if(this.oauthLink.weixin.pc_open){
                    b2loadScript('https://res.wx.qq.com/connect/zh_CN/htmledition/js/wxLogin.js','',()=>{
                        new WxLogin({
                            self_redirect:false,
                            id:"mp-login-box",
                            appid: b2_global.wx_appid,
                            scope: "snsapi_login",
                            redirect_uri: b2_global.home_url+'/open?type=weixin',
                            state: "",
                            style: "black",
                            href: "data:text/css;base64,LmltcG93ZXJCb3ggLnFyY29kZSB7DQogICAgd2lkdGg6IDEwMCU7DQogICAgbWFyZ2luLXRvcDogMDsNCiAgICBib3JkZXI6IDA7DQp9DQouaW1wb3dlckJveCAuaW5mbyB7DQogICAgZGlzcGxheTogbm9uZTsNCn0NCi5pbXBvd2VyQm94IC50aXRsZXsNCmRpc3BsYXk6bm9uZQ0KfQ=="
                        });
                    })
                }else{
                    this.locked = false
                    this.t = false
                    this.count = 100
                    this.getQrcode()
                }
            }
        }
    }
})

var mpCode = new Vue({
    el:'#mp-box',
    data:{
        show:false,
        qrcode:'',
        type:'1',
    },
    methods:{
        close(){
            this.show = !this.show
            if(!b2token){
                login.show = true
                login.loginType = this.type
            }
        }
    }
})

var login = new Vue({
    el:'#login-box',
    data:{
        show:false,
        loginType:1,
        checkCodeSendSuccess:false,
        isAdmin:false,
        imgCode:''
    },
    methods:{
        close(val){
            this.show = val
        },
        loginAc(val){
            this.loginType = val
        },
        imgCodeAc(val){
            this.imgCode = val
        }
    }
})

//验证码组件
Vue.component('recaptcha-box', {
    props: ['show','type'],
    template:b2_global.check_code,
    data(){
        return {
            recaptcha:'',
            token:'',
            recaptchaUrl:'',
            disabled:true,
            issetLuo:false,
            locked:false,
            loginType:2,
            checkType:b2_global.check_type,
        }
    },
    methods:{
        close(){
            this.$emit('close-form')
        },
        change(){
            //获取验证码base64及token
            if(this.locked) return
            this.recaptchaUrl = ''
            this.locked = true
            this.$https.post(b2_rest_url+'getRecaptcha','number=4&width=186&height=50').then(res=>{
                if(res.data){
                    this.recaptchaUrl = res.data.base
                    this.token = res.data.token
                }
                this.locked = false
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        checkCode(){
            if(this.disabled) return
            this.disabled = true
            this.$https.post(b2_rest_url+'imgCodeCheck','img_code='+this.recaptcha+'&token='+this.token+'&loginType='+login.loginType).then(res=>{

                if(this.type == 'edit'){
                    b2AuthorEdit.imgCodeAc({
                        value:this.recaptcha,
                        token:this.token
                    })
                }else if(this.type == 'bind'){
                    b2bindLogin.imgCodeAc({
                        value:this.recaptcha,
                        token:this.token
                    })
                }else{
                    login.imgCodeAc({
                        value:this.recaptcha,
                        token:this.token
                    })
                    setTimeout(()=>{
                        login.$refs.loginBox.sendSMS()
                    },50)
                }

                this.disabled = false
                this.close()


            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.disabled = false
            })
        }
    },
    watch:{
        show(val){
            if(val && b2_global.check_type == 'normal'){
                this.change()
            }
            if(val && !this.issetLuo && login.loginType != 3){
                let s = document.createElement('script')
                s.id = 'luosimao'
                s.type = 'text/javascript'
                s.src = '//captcha.luosimao.com/static/dist/api.js?time='+((new Date()).getTime())
                document.getElementsByTagName("head")[0].appendChild(s)
                this.issetLuo = true
            }else if(val && this.issetLuo && login.loginType != 3){
                LUOCAPTCHA && LUOCAPTCHA.reset()
            }

            if(this.type != 'edit'){
                this.loginType = login.loginType
            }


        },
        recaptcha(val){
            if(val.length == 4){
                this.disabled = false
            }else{
                this.disabled = true
            }
        }
    }
})

var recaptcha = new Vue({
    el:'#recaptcha-form',
    data:{
        show:false,
        type:''
    },
    methods:{
        close(){
            this.show = false
            if(this.type != 'edit' && this.type != 'bind'){
                login.show = true
            }
            if(this.type === 'bind'){
                b2bindLogin.show = true
            }
        },
    }
})

function indexPostModules(){
    let m = document.querySelectorAll('.post-list');
    if(m.length > 0){
        for (let i = 0; i < m.length; i++) {
            let key = m[i].getAttribute('data-key')

            if(m[i].id != 'undefined'){
                new Vue({
                    el:'#post-item-'+key,
                    data:{
                        key:0,
                        index:0,
                        box:'',
                        paged:1,
                        pages:0,
                        type:'cat',
                        showButton:false,
                        locked:false,
                        cat:[],
                        finish:false,
                        id:0
                    },
                    mounted(){
                        if(this.$el.querySelector('.load-more')){
                            this.key = key
                            this.index = this.$el.getAttribute('data-i')
                            this.box = this.$el.querySelectorAll('.b2_gap')[0]
                            this.showButton = this.$el.querySelector('.load-more').getAttribute('data-showButton')
                        }
                    },
                    methods:{
                        getList(id,type,postType){

                            if(this.finish && type == 'more'){
                                return
                            }else{
                                this.finish = false
                            }

                            if(this.locked) return
                            this.locked = true

                            this.id = id

                            if(type){
                                this.type = type
                            }

                            if(this.type == 'cat'){
                                this.paged = 1;
                                if(id){
                                    this.cat = [id]
                                }else{
                                    this.cat = []
                                }
                                this.finish = false
                            }else{
                                this.paged = this.paged + 1
                            }

                            let data = {
                                'index':this.index,
                                'id':this.cat,
                                'post_paged':this.paged
                            }
                            axios.post(b2_rest_url+'getModulePostList',Qs.stringify(data)).then(res=>{
                                if(res.data.count > 0){
                                    this.pages = res.data.pages
                                    if(this.type == 'cat'){
                                        if(this.pages == 1){
                                            this.box.innerHTML = res.data.data
                                        }else{
                                            this.box.innerHTML = res.data.data
                                        }

                                        if(postType  == 'post-2'){
                                           b2PackeryLoad()
                                        }
                                        this.showButton = true
                                    }else if(this.type == 'more'){
                                        this.box.insertAdjacentHTML('beforeend', res.data.data)
                                        if(postType  == 'post-2'){
                                            b2PackeryLoad()
                                         }
                                        this.showButton = true
                                    }

                                    if(this.paged >= this.pages ){
                                        this.finish = true
                                    }
                                }else{
                                    if(postType  == 'post-6'){
                                        this.box.innerHTML = '<tr><td colspan="10" style="border:0">'+b2_global.empty_page+'</td></tr>'
                                    }else{
                                        this.box.innerHTML = b2_global.empty_page
                                    }
                                    this.showButton = false
                                }
                                this.locked = false
                                b2SidebarSticky()
                                this.$nextTick(()=>{
                                    lazyLoadInstance.update()
                                })
                            })
                        }
                    }
                })
            }
        }
    }
}
indexPostModules()

//遍历dom树，依次添加 is-visible 类，使其支持渐变显示
function listFadein(dom,time){
    return
    var i = 0
    dom.forEach(e=>{
        if(e.className.indexOf('is-visible') === -1){
            i++
            if(i== 1){
                e.className += ' is-visible'
            }else{
                setTimeout(function(){
                    e.className += ' is-visible'
                }, i*time )
            }
        }
    })
}
listFadein(document.querySelectorAll('.post-list ul.b2_gap > li'),10)

//加载瀑布流
function b2PackeryLoad(e){
    var grid = document.querySelectorAll('.grid')
    if(grid.length > 0){
        for (let index = 0; index < grid.length; index++) {
           let pack = new Packery( grid[index])

            pack.on( 'layoutComplete', ()=>{
                b2SidebarSticky()
            });
        }
    }
}
b2PackeryLoad()

//新版分页
//type p 分页加载，m 下拉加载
Vue.component('pagenav-new',{
    props: ['type','paged','pages','opt','api','rote'],
    template: b2_global.page_nav,
    data:function(){
        return {
            locked:false,
            next:false,
            per:false,
            cpage:0,
            cpaged:1,
            cpages:[],
            mobile:false,
            showGo:false
        }
    },
    created(){
        window.addEventListener('scroll', _debounce(this.autoLoadMore),passiveSupported ? { passive: true } : false)
        this.cpaged = parseInt(this.paged)
        this.cpages = this.pagesInit()
        //监听前进与后退
        window.addEventListener("popstate", ()=>{
            let state = history.state;
            if(state && state.page && this.type == 'p'){
                this.go(state.page)
            }
        },passiveSupported ? { passive: true } : false);
        this.mobile = B2ClientWidth > 768 ? false : true
    },
    methods:{
        disabled(page){
            return page == this.cpaged && this.locked == true
        },
        pagesInit(){
            let pagearr = []
            if(this.pages <= 6){
                for (let i = 1; i <= this.pages; i++) {
                    pagearr.push(i)
                }
            }else{
                if(!this.cpaged) this.cpaged = this.paged
                if(this.cpaged < 4){
                    for (let i = 1; i <= this.pages; i++) {
                        if(i >= 5) break
                        pagearr.push(i)
                    }
                    pagearr.push(0,this.pages)
                }else if(this.cpaged >= 4 && this.pages - 2 > this.cpaged){
                    pagearr.push(1,0)
                    for (let i = this.cpaged - 1; i <= this.cpaged + 1; i++) {
                        pagearr.push(i)
                    }
                    pagearr.push(0,this.pages)
                }else if(this.pages - 2 <= this.cpaged){
                    pagearr.push(1,0)
                    for (let i = this.cpaged - 2; i <= this.pages; i++) {
                        pagearr.push(i)
                    }
                }
            }
            return pagearr
        },
        focus(){
            this.showGo = true
        },
        blur(){
            setTimeout(() => {
                this.showGo = false
            }, 100);
        },
        autoLoadMore(){
            if(this.type == 'p') return
            if(this.cpaged == this.pages) return;
            let scrollTop = document.documentElement.scrollTop;
            if(scrollTop + window.innerHeight >= document.body.clientHeight - 550) {
                this.go(this.cpaged+1)
            }
        },
        trimSlashes(str){
            return str.replace(/\/+$/, '');
        },
        go(page,type,action){

            page = parseInt(page)
            if(this.opt.length > 0) return

            if(this.cpaged == page && !action) return

            if(this.locked == true) return
            this.locked = true

            if(this.type === 'm' && this.pages <= this.cpaged && page != 1) return

            if(type == 'next'){
                this.next = true
                this.per = false
            }else if(type == 'per'){
                this.per = true
                this.next = false
            }

            this.cpaged = page

            this.opt['post_paged'] = page
            this.opt['paged'] = page

            this.$http.post(b2_rest_url+this.api,Qs.stringify(this.opt)).then(res=>{

                this.locked = false
                this.cpages = this.pagesInit()

                this.$emit('return',res.data)

                if(this.rote){

                    let currentURL = window.location.href,
                    url = this.trimSlashes(currentURL.split('?')[0]),
                    newURL
                    console.log(url)
                    if(this.cpaged == 1){
                        newURL = url.replace(/\/page\/\d/, '');
                    }else{
                        if(currentURL.indexOf('/page/') == -1){
                            newURL = url+'/page/'+this.cpaged
                        }else{
                            newURL = url.replace(/\/page\/[0-9]*$/, '/page/'+page);
                        }
                    }

                    url = currentURL.replace(url,newURL)

                    window.history.pushState({page:page}, null,url )
                    if(this.type === 'p'){
                        b2AsideBar.goTop()
                    }

                }

                b2SidebarSticky()
                this.$nextTick(()=>{
                    lazyLoadInstance.update()
                })
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});

            })

        },
        //获取数据
        getData(fn,data){
            this.cpaged = this.cpage
            this.cpages = this.pagesInit()
        },
        //跳转
        jump(event){

            var val = event.target.value || event.target.previousElementSibling.value || this.$refs.pagenavnumber.value

            val = parseInt(val)
          
            if(val > this.pages) return
        
            this.go(val,'p',true)
        }
    },
    watch: {
        pages(val){
            this.cpages = this.pagesInit()
        },
        paged(){
            this.cpaged = parseInt(this.paged)
            this.cpages = this.pagesInit()
        }
    }
})

//分页代码
Vue.component('page-nav',{
    props: ['paged','navtype','pages','type','box','opt','api','url','title'],
    template: b2_global.page_nav,
    data:function(){
        return {
            locked:false,
            next:false,
            per:false,
            cpage:0,
            cpaged:1,
            cpages:[],
            mobile:false,
            showGo:false
        }
    },
    created(){
        window.addEventListener('scroll', _debounce(this.autoLoadMore),passiveSupported ? { passive: true } : false)
        this.cpaged = parseInt(this.paged)
        this.cpages = this.pagesInit()
        //监听前进与后退
        window.addEventListener("popstate", ()=>{
            let state = history.state;
            if(state && state.page && this.type == 'p'){
                this.go(state.page)
            }
        },passiveSupported ? { passive: true } : false);
        this.mobile = B2ClientWidth > 768 ? false : true
    },
    methods:{
        disabled(page){
            return page == this.cpaged && this.locked == true
        },
        pagesInit(){
            let pagearr = []
            if(this.pages <= 7){
                for (let i = 1; i <= this.pages; i++) {
                    pagearr.push(i)
                }
            }else{
                if(!this.cpaged) this.cpaged = this.paged
                if(this.cpaged < 5){
                    for (let i = 1; i <= this.pages; i++) {
                        if(i >= 6) break
                        pagearr.push(i)
                    }
                    pagearr.push(0,this.pages)
                }else if(this.cpaged >= 5 && this.pages - 3 > this.cpaged){
                    pagearr.push(1,0)
                    for (let i = this.cpaged - 2; i <= this.cpaged + 2; i++) {
                        pagearr.push(i)
                    }
                    pagearr.push(0,this.pages)
                }else if(this.pages - 3 <= this.cpaged){
                    pagearr.push(1,0)
                    for (let i = this.cpaged - 3; i <= this.pages; i++) {
                        pagearr.push(i)
                    }
                }
            }
            return pagearr
        },
        autoLoadMore(){
            setTimeout(() => {
                if(this.type == 'p') return
                let scrollTop = document.documentElement.scrollTop;
                if(scrollTop + window.innerHeight >= document.body.clientHeight - 550) {
                    this.go(this.cpaged+1)
                }
            }, 300);
        },
        focus(){
            this.showGo = true
        },
        blur(){
            setTimeout(() => {
                this.showGo = false
            }, 100);

        },
        go(page,type,action){

            page = parseInt(page)
            if(this.opt.length > 0) return

            if(this.cpaged == page && !action) return
            if(this.locked == true) return

            if(this.type === 'm' && this.pages <= this.cpaged && page != 1) return

            this.locked = true

            if(type == 'next'){
                this.next = true
                this.per = false
            }else if(type == 'per'){
                this.per = true
                this.next = false
            }

            this.cpaged = page
            console.log(this.cpaged)

            this.opt['post_paged'] = page
            this.opt['paged'] = page

            this.$http.post(b2_rest_url+this.api,Qs.stringify(this.opt)).then(res=>{

                this.locked = false
                this.cpages = this.pagesInit()
                let dom = document.querySelector(this.box)

                //如果返回的是json数据
                if(this.navtype === 'json'){
                    this.$emit('return',res.data)
                }else{
                    if(this.type === 'p'){
                        dom.innerHTML = res.data.data
                    }else{
                        dom.insertAdjacentHTML('beforeend', res.data.data)
                    }
                }

                if(page != 1){
                    this.url = b2removeURLParameter(this.url,'action')
                }

                //变更地址栏和title
                if(!!(window.history && history.pushState)){

                    if(page != 1){
                        if(this.navtype === 'comment'){
                            if(b2_global.structure){
                                window.history.pushState({page:page}, null, this.url+'/comment-page-'+page+'#comment')
                            }else{
                                window.history.pushState({page:page}, null, this.url+'&cpage='+page+'#comment')
                            }
                        }else{
                            if(this.navtype != 'authorComments' && this.title){
                                document.title = this.title+' '+b2_global.site_separator+' '+(b2_global.page_title.replace('{#}',page))+' '+b2_global.site_separator+' '+b2_global.site_name
                            }
                            let currentURL = window.location.href,
                            url = currentURL.split('?')[0],
                            newURL

                            if(this.cpaged == 1){
                                newURL = url.replace(/\/page\/\d/, '');
                            }else{
                                if(currentURL.indexOf('/page/') == -1){
                                    newURL = url+'/page/'+this.cpaged
                                }else{
                                    newURL = url.replace(/\/page\/[0-9]*$/, '/page/'+page);
                                }
                            }

                            url = currentURL.replace(url,newURL)

                            window.history.pushState({page:page}, null,url )
                            if(this.type === 'p'){
                                b2AsideBar.goTop()
                            }
                            
                        }

                    }else{
                        if(this.navtype === 'comment'){
                            window.history.pushState({page:page}, null, this.url+'#comment')
                        }else{
                            if(this.navtype != 'authorComments' && this.title){
                                document.title = this.title+' '+b2_global.site_separator+' '+b2_global.site_name
                            }

                            window.history.pushState({page:page}, null, this.url)
                        }
                    }

                }

                if(this.navtype === 'comment' || this.navtype === 'authorComments'){
                    let img = document.querySelectorAll('.comment-img-box img');
                    if(img.length > 0){
                        for (let index = 0; index < img.length; index++) {
                            // var img = new Image();
                            // img.src = img[index].getAttribute('data-original');
                            // img.onload = function(){

                            //     img[index].setAttribute('data-zooming-width',img.width)
                            //     img[index].setAttribute('data-zooming-height',img.height)

                            // };
                            b2zoom.listen(img[index]);
                        }
                    }
                    if(this.navtype === 'comment'){
                        b2CommentList.showSticky()
                        b2SidebarSticky()
                    }
                }else if(this.navtype === 'post'){
                    b2PackeryLoad()
                    //渐显
                    setTimeout(()=>{
                        listFadein(document.querySelectorAll(this.box+' > li'),20)
                    },500)
                }
                this.$emit('finish')
                b2SidebarSticky()
                this.$nextTick(()=>{
                    lazyLoadInstance.update()
                })
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});

            })

        },
        //获取数据
        getData(fn,data){
            this.cpaged = this.cpage
            this.cpages = this.pagesInit()
        },
        //跳转
        jump(event){

            var val = event.target.value || event.target.previousElementSibling.value || this.$refs.pagenavnumber.value

            val = parseInt(val)
          
            if(val > this.pages) return
        
            this.go(val,'p',true)
        }
    },
    watch: {
        pages(val){
            this.cpages = this.pagesInit()
        },
        paged(){
            this.cpaged = parseInt(this.paged)
            this.cpages = this.pagesInit()
        }
    }
})

function b2RestTimeAgo(dom){
    if(dom && dom.length > 0)
    timeago.render(dom, b2_global.language);
}

let pageNavBox = new Vue({
    el:'.post-nav',
    data:{
        selecter:'#post-list .b2_gap',
        opt:'',
        api:'getPostList',
        options:[],
        value:1,
        showGoN:false
    },
    mounted(){
        if(typeof b2_cat !== 'undefined'){
            this.opt = b2_cat.opt
        }
    },
    methods:{
        jumpAc:function(event){

            var val = event.target.value || event.target.previousElementSibling.value || this.$refs.pagenavnumber.value

            if(val > this.opt.pages) return

            let currentURL = window.location.href,
            url = currentURL.split('?')[0],
            newURL

            if(val == 1){
                newURL = url.replace(/\/page\/\d/, '');
            }else{
                if(currentURL.indexOf('/page/') == -1){
                    newURL = url+'/page/'+val
                }else{
                    newURL = url.replace(/\/page\/[0-9]*$/, '/page/'+val);
                }
            }

            url = currentURL.replace(url,newURL)

            window.location.href = url;
        },
        focus(){
            this.showGoN = true
        },
        blur(){
            setTimeout(() => {
                this.showGoN = false
            }, 100);
        },
    }
})

//语音播放
let b2Audio = new Vue({
    el:'.b2-audio-content',
    data:{
        url:'',
        textList:[],
        playStatus:false,
        api:'https://tts.baidu.com/text2audio?cuid=baike&lan=ZH&ctp=1&pdt=301&tex=',
        index:0,
        currentTime:'00:00',
        startTime:'00:00',
        step:0,
        duration:0,
        ds:'',
        width:'0%'
    },
    methods:{
        play(){
            //如果未播放，加载播放列表
            if(!this.url){
                this.getPlayList()
            }else{
                this.playList()
            }
        },
        getPlayList(){
            this.$https.post(b2_rest_url+'getPostAudio','post_id='+this.$refs.audio.getAttribute('data-id')).then((res)=>{
                if(res.data.length > 0){
                    this.textList = res.data
                    //res.data
                    this.playList()
                    this.watchPlay()
                }
            })
        },
        playList(){
            this.url = this.api+this.textList[this.index];
            setTimeout(()=>{
                this.button()
            })
        },
        watchPlay(){
            this.$refs.audio.addEventListener('ended', ()=> {

                if(this.index >= this.textList.length - 1) {
                    this.playStatus = false
                    this.index = 0
                    this.step = 0
                    return;
                }
                this.step = 0
                this.index = this.index + 1
                this.playList()
            },passiveSupported ? { passive: true } : false)
        },
        button(){
            if(this.$refs.audio!==null){
                if(this.$refs.audio.paused){
                    this.$refs.audio.play()
                    this.playStatus = true
                    this.timeSetp()
                }else{
                    this.$refs.audio.pause()
                    this.playStatus = false
                }

                this.$refs.audio.addEventListener("loadedmetadata",()=>{
                    this.duration = Math.round(this.$refs.audio.duration)
                    this.currentTime = this.secondToDate(this.duration)
                },passiveSupported ? { passive: true } : false);

            }
        },
        timeSetp(){
            if(this.playStatus == true){
                this.startTime = this.secondToDate(this.step++)
                if(this.ds){
                    clearTimeout(this.ds)
                }
                this.ds = setTimeout(()=>{
                    this.timeSetp()
                    this.width = this.step/this.duration * 100 + '%'
                },1000)
            }
        },
        secondToDate(s){
            var t;
            if(s > -1){
                var hour = Math.floor(s/3600)
                var min = Math.floor(s/60) % 60
                var sec = s % 60
                if(hour > 1){
                    if(hour < 10) {
                        t = '0'+ hour + ":"
                    } else {
                        t = hour + ":";
                    }
                }else{
                    t = '';
                }

                if(min < 10){t += "0"}
                t += min + ":";
                if(sec < 10){t += "0"}
                t += sec.toFixed(0);
            }
            return t;
        }
    }
})

var socialLogin = new Vue({
    el:'#juhe-social',
    data:{
        locked:false,
        error:''
    },
    mounted(){

        if(!this.$refs.juhebox) return
        let type = b2GetQueryVariable('type')
        this.locked = true
        this.$http.post(b2_rest_url+'juheSocialLogin','type='+type).then(res=>{
            window.location.href = res.data
        }).catch(err=>{
            this.error = err.response.data.message
            this.locked = false
        })
    },
    methods:{
        back(){
            let url = b2getCookie('b2_back_url')

            if(url){
                window.location.href = url
            }else{
                window.location.href = b2_global.home_url
            }

        },
    }
})

var socialBox = new Vue({
    el:'#social-box',
    data:{
        locked:false,
        type:'',
        data:{
            'token':'',
            'invitation':'',
            'subType':''
        },
        error:'',
        oauth:'',
        name:''
    },
    mounted(){
        if(this.$refs.socialBox){
            let code = b2GetQueryVariable('code'),
            juhe = b2GetQueryVariable('juhe') ?? 0;
            this.type = b2GetQueryVariable('type')

            if(code){
                this.locked = true
                this.$http.post(b2_rest_url+'socialLogin','code='+code+'&type='+this.type+'&juhe='+juhe).then(res=>{
                    this.locked = false
                    if(res.data === true){
                        this.back()
                    }else if(res.data.type == 'invitation'){
                        this.type = 'invitation',
                        this.data.token = res.data.token
                    }else{
                        this.back()
                    }
                }).catch(err=>{
                    if(err.response.data.message.msg){
                        this.oauth = err.response.data.message.oauth
                        this.error = err.response.data.message.msg
                        this.name = err.response.data.message.name
                    }else{
                        this.error = err.response.data.message
                    }

                    this.locked = false
                })
            }
        }
    },
    methods:{
        back(){
            let url = b2getCookie('b2_back_url')

            if(url){
                window.location.href = url
            }else{
                window.location.href = b2_global.home_url
            }

        },
        invRegeister(type){
            if(this.locked == 'pass' || this.locked == 'sub') return
            this.locked = type
            this.data.subType = type
            this.$http.post(b2_rest_url+'invRegeister',Qs.stringify(this.data)).then(res=>{
                this.back()
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        loginOut(url){
            b2delCookie('b2_token')
            b2CurrentPageReload(url)

        },
        rebuild(ev){
            this.$http.post(b2_rest_url+'unBuild','type='+this.type+'&user_id='+b2_author.author_id).then(res=>{

            }).catch(err=>{
                ev.preventDefault();

                Qmsg['warning'](err.response.data.message,{html:true});
            })
        }
    }
})

Vue.component('gg-box', {
    props: ['show'],
    template: b2_global.gg_box,
    computed:{
        ggdata(){
            return this.$store.state.announcement[0]
        }
    },
    methods:{
        close(){
            this.$emit('close')
        },
    }
})

var b2GG = new Vue({
    el:'#gg-box',
    data:{
        show:false
    },
    mounted(){
        this.$http.post(b2_rest_url+'getLatestAnnouncement','count=3').then(res=>{

            if(res.data.length > 0){
                this.$store.commit('setAnnouncement',res.data)

                this.show = res.data[0].show
            }else{
                this.$store.commit('setAnnouncement','none')
            }


        })
    },
    methods:{
        close(){
            this.show = false
            let timestamp = new Date().getTime()
            timestamp = parseInt(timestamp/1000)

            b2setCookie('gg_info',timestamp)
        }
    }
})

Vue.component('dmsg-box', {
    props: ['show','userid','type'],
    template: b2_global.dmsg_box,
    data(){
        return {
            user:[],
            content:'',
            locked:false,
            nickname:'',
            UserList:[],
            search:false
        }
    },
    methods:{
        close(){
            this.$emit('close')
            setTimeout(()=>{
                this.user = []
                this.content = ''
                this.nickname = ''
                this.UserList = []
            },100)
        },
        getUserData(id = 0){
            id = !id ? this.userid : id
            this.$http.post(b2_rest_url+'getUserPublicData','user_id='+id).then(res=>{
                this.user = res.data
                b2Dmsg.userid = id
                b2Dmsg.select = ''
                this.UserList = []
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        edit(){
            b2Dmsg.select = 'select'
        },
        send(){
            if(this.locked == true) return
            this.locked = true
            this.$http.post(b2_rest_url+'sendDirectmessage','user_id='+this.userid+'&content='+this.content).then(res=>{
                if(res.data == true){
                    this.close()
                }
                if(b2DmsgPage.$refs.dmsgPage){
                    b2DmsgPage.getList()
                }
                this.locked = false
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        searchUser(val){
            if(this.locked == true) return
            this.locked = true
            this.search = true
            this.$http.post(b2_rest_url+'searchUsers','nickname='+val).then(res=>{
                if(res.data.length > 0){
                    this.UserList = res.data
                }else{
                    this.UserList = []
                }
                this.locked = false
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
                this.UserList = []
            })
        }
    },
    watch:{
        show(val){
            if(val && this.type !== 'select'){
                this.getUserData()
            }
        },
        nickname(val){
            if(val){
                this.searchUser(val);
            }
        }
    }
})

var b2Dmsg = new Vue({
    el:'#dmsg-box',
    data:{
        userid:0,
        show:false,
        select:''
    },
    methods:{
        close(){
            this.show = !this.show
        }
    }
})

var b2DmsgPage = new Vue({
    el:'.dmsg-page',
    data:{
        list:false,
        locked:false,
        count:0,
        pages:0,
        selecter:'.dmsg-header',
        opt:{
            paged:1
        },
        api:'getUserDirectmessageList'
    },
    mounted(){
        if(this.$refs.dmsgPage){
            this.opt.paged = this.$refs.dmsgPage.getAttribute('data-paged')
            this.getList()
        }
    },
    methods:{
        getList(){
            this.$http.post(b2_rest_url+'getUserDirectmessageList',Qs.stringify(this.opt)).then(res=>{
                this.list = res.data.data
                this.pages = res.data.count
                this.locked = false
                this.$nextTick(()=>{
                    b2RestTimeAgo(this.$el.querySelectorAll('.b2timeago'))
                })
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        showDmsgBox(){
            b2Dmsg.select = 'select'
            b2Dmsg.show = true
        },
        get(data){
            this.list = data.data
            this.pages = data.count
            this.$nextTick(()=>{
                b2RestTimeAgo(this.$el.querySelectorAll('.b2timeago'))
            })
        },
        jump(id){
            window.location.href = this.$refs.dmsgPage.getAttribute('data-url')+'/to/'+id;
        },
        deleteDmsg(id){

        }
    }
})

var b2dmsgPageTo = new Vue({
    el:'.dmsg-page-to',
    data:{
        list:false,
        locked:false,
        opt:{
            paged:1,
            userid:0
        },
        count:0,
        pages:0,
        selecter:'.dmsg-header',
        api:'getMyDirectmessageList',
        content:'',
        sendLocked:false
    },
    mounted(){
        if(this.$refs.mydmsg){
            this.opt.userid = this.$refs.mydmsg.getAttribute('data-id')
            this.opt.paged = this.$refs.mydmsg.getAttribute('data-paged')
            this.getList();
        }
    },
    methods:{
        getList(){
            this.$http.post(b2_rest_url+'getMyDirectmessageList',Qs.stringify(this.opt)).then(res=>{
                this.list = res.data.data
                this.locked = false
                this.count = res.data.count
                this.pages = res.data.pages
                this.$nextTick(()=>{
                    b2RestTimeAgo(this.$el.querySelectorAll('.b2timeago'))
                })

            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        get(val){
            this.list = val.data
            this.count = val.count
            this.pages = val.pages
        },
        send(){
            if(this.sendLocked == true) return
            this.sendLocked = true
            this.$http.post(b2_rest_url+'sendDirectmessage','user_id='+this.opt.userid+'&content='+this.content).then(res=>{
                this.getList()
                this.content = ''
                this.sendLocked = false
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.sendLocked = false
            })
        }
    }
})

var b2DownloadPage = new Vue({
    el:'#download-page',
    data:{
        data:'',
        postId:0,
        index:0,
        i:0
    },
    mounted(){
        if(this.$refs.downloadPage){
            this.postId = b2GetQueryVariable('post_id');
            this.index = b2GetQueryVariable('index');
            this.i = b2GetQueryVariable('i');
            this.getData()

            var clipboard = new ClipboardJS('.fuzhi');
            clipboard.on('success', e=>{

                Qmsg['success'](b2_global.js_text.global.copy_success,{html:true});
            });
            clipboard.on('error', e=> {

                Qmsg['warning'](b2_global.js_text.global.copy_select,{html:true});
            });
        }
    },
    methods:{
        getData(){
            let guest = b2getCookie('b2_guest_buy_'+this.postId+'_x')
                if(guest){
                    guest = JSON.parse(guest)
                }

            let data = {
                'post_id':this.postId,
                'index':this.index,
                'i':this.i,
                'guest':guest
            }

            this.$http.post(b2_rest_url+'getDownloadPageData',Qs.stringify(data)).then(res=>{
                this.data = res.data
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        login(){
            login.show = true
        }
    }
})

Vue.component('check-box',{
    props: ['show','title','type','payt'],
    template: b2_global.pay_check,
    data(){
        return {
            success:false,
            timeOut:300,
            timesec:'',
            sTime:'',
            check:false,
            checkTime:''
        }
    },
    methods:{
        close(){
            this.$emit('close')
            this.sTime = ''
            this.checkTime = ''
        },
        checkAc(){
            if((this.sTime === null && this.success == 'fail') || this.checkTime === null || this.success === true){
                this.checkTime = null
                return
            }

            let value = b2getCookie('order_id')
            this.$http.post(b2_rest_url+'payCheck','order_id='+value).then(res=>{

                if(res.data.status === 'success'){
                    if(!b2token){
                        let list = b2getCookie('b2_guest_buy_'+res.data.id+'_'+res.data.type)

                        if(!list){
                            list = new Object()
                        }else{
                            list = JSON.parse(list)
                        }

                        Reflect.set(list,res.data.index,{
                            'id':res.data.id,
                            'order_id':value,
                            'type':res.data.type,
                            'index':res.data.index
                        })

                        list = JSON.stringify(list)

                        b2setCookie('b2_guest_buy_'+res.data.id+'_'+res.data.type,list)

                    }

                    //b2delCookie('order_id')

                    if(typeof(B2VerifyPage) !== "undefined"){
                        B2VerifyPage.data.money = true
                        this.close()

                    }else if(typeof(carts) !== "undefined"){
                        carts.step = 3
                        this.close()

                    }else if(this.payt === 'ask'){
                        b2CircleList.afterCommentGetData(b2CircleList.answer.listParent,b2CircleList.data[b2CircleList.answer.listParent].topic_id,'ask')
                        this.close()

                    }else if(this.payt === 'hidden'){
                        b2CircleList.afterCommentGetData(b2CircleList.hiddenIndex,b2CircleList.data[b2CircleList.hiddenIndex].topic_id,'hidden')
                        this.close()

                    }
                    this.success = true;
                    this.checkTime = null;
                    this.sTime = null

                }else{
                    this.checkTime = setTimeout(()=>{
                        this.checkAc()
                    },1000)
                }
            })
        },
        time(){
            this.currTime = parseInt(Date.parse(new Date())/1000);
            this.endTime = parseInt(this.currTime + this.timeOut);
            this.setTime()
        },
        setTime(){
            if(this.show == false || this.success === true) {
                this.sTime = null
                this.checkTime = null;
                return
            }
            let diff_time = parseInt(this.endTime-this.currTime);
            let m = Math.floor((diff_time / 60 % 60));
            let s = Math.floor((diff_time % 60));
            this.timesec = (m > 0 ? m + '<b>'+b2_global.js_text.global.min+'</b>' : '') + s + '<b>'+b2_global.js_text.global.sec+'</b>';
            if(diff_time > 0){
                this.sTime = setTimeout(()=>{
                    this.endTime = this.endTime - 1;
                    this.setTime()
                },1000)
            }else{
                this.sTime = null
                this.success = 'fail'
            }
        },
        refresh(){

            if(typeof(B2VerifyPage) !== "undefined"){
                B2VerifyPage.data.money = true
                this.close()
            }else if(typeof(carts) !== "undefined"){
                carts.step = 3
                this.close()
            }else if(this.payt === 'ask'){
                b2CircleList.afterCommentGetData(b2CircleList.answer.listParent,b2CircleList.data[b2CircleList.answer.listParent].topic_id,'ask')
                this.close()
            }else if(this.payt === 'hidden'){
                b2CircleList.afterCommentGetData(b2CircleList.hiddenIndex,b2CircleList.data[b2CircleList.hiddenIndex].topic_id,'hidden')
                this.close()
            }else if(typeof(b2poinfomation) !== "undefined" && b2poinfomation.$refs.poinfomation){
                b2poinfomation.getPoinfomationOpts()
                this.close()
            }else{
                var url = new URL(window.location.href);
                url.searchParams.delete('b2paystatus');
                window.location.href=url.href
                b2CurrentPageReload()
            }
        }
    },
    watch:{
        show(val){
            if(this.type == 'card') return
            if(val){
                this.sTime = ''
                this.checkTime = ''
                this.time()

                this.checkAc()
            }else{
                this.sTime = null
                this.success = false
                this.checkTime = null
            }
        }
    }
})

//跳转支付检查支付结果
var b2PayCheck = new Vue({
    el:'#pay-check',
    data:{
        show:false,
        title:'',
        type:'',
        payType:''
    },
    mounted(){
        if(b2GetQueryVariable('b2paystatus') == 'check'){
            this.show = true
        }
    },
    methods:{
        close(){
            this.show = !this.show
            if(!this.show){
                this.payType = ''
            }
        }
    }
})

//支付跳转页面
var b2Pay = new Vue({
    el:'#pay-page',
    data:{
        data:[],
        token:'',
        error:'',
        locked:false,
        payUrl:''
    },
    mounted(){
        if(this.$refs.payPage){
            this.token = this.$refs.payPage.getAttribute('data-token');
            if(!this.token){
                this.data = JSON.parse(this.$refs.payPage.getAttribute('data-pay'));
                this.pay()
            }
        }
    },
    methods:{
        pay(){
            if(this.locked == true) return
            this.locked = true
            this.$http.post(b2_rest_url+'buildOrder',Qs.stringify(this.data)).then(res=>{

                this.token = res.data
                this.payUrl = window.location.href.split('?')[0]+'?token='+this.token
                //b2MakeForm(window.location.href.split('?')[0],this.token)
                this.locked = false
            }).catch(err=>{
                this.error = err.response.data.message
                this.locked = false
            })
        }
    }
})

function b2MakeForm(url,token){

    // 创建一个 form
    var form1 = document.createElement("form");
    form1.id = "form1";
    form1.name = "form1";

    // 添加到 body 中
    document.body.appendChild(form1);

    // 创建一个输入
    var input = document.createElement("input");
    // 设置相应参数
    input.name = "token";
    input.value = token;

    // 将该输入框插入到 form 中
    form1.appendChild(input);

    // form 的提交方式
    form1.method = "POST";
    // form 提交路径
    form1.action = url;

    // 对该 form 执行提交
    form1.submit();
    // 删除该 form
    document.body.removeChild(form1);
}

//扫码支付组件
Vue.component('scan-box',{
    props: ['show','data'],
    template: b2_global.scan_box,
    data(){
        return {
            locked:false,
            qrcode:'',
            timeOut:300,
            timesec:'',
            sTime:'',
            success:'',
            checkTime:'',
            backData:[]
        }
    },
    methods:{
        close(){
            this.$emit('close')
            this.backData = []
            this.checkTime = null
        },
        buildOrder(){
            if(this.locked == true) return
            this.locked = true

            this.currTime = parseInt(Date.parse(new Date())/1000);
            this.endTime = parseInt(this.currTime + this.timeOut);
            this.setTime()

            this.$http.post(b2_rest_url+'buildOrder',Qs.stringify(this.data)).then(res=>{
                this.backData = res.data
                if(res.data.type !== 'mapay' && res.data.type !== 'pay020'){
                    var qr = new QRious({
                        value: this.backData.qrcode,
                        size:200,
                        level:'L'
                      });
                    this.backData.qrcode = qr.toDataURL('image/jpeg')
                }
                this.writeOrder(res.data.order_id)
                this.locked = false
                this.checkAc()
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        writeOrder(id){
            b2setCookie('order_id',id)
        },
        checkAc(){
            if((this.sTime === null && this.success == 'fail') || this.checkTime === null || this.show == false){
                this.checkTime = null
                return
            }

            let value = b2getCookie('order_id')
            this.$http.post(b2_rest_url+'payCheck','order_id='+value).then(res=>{
                if(res.data.status === 'success'){

                    if(!b2token){

                        let list = b2getCookie('b2_guest_buy_'+res.data.id+'_'+res.data.type)
                        if(!list){
                            list = new Object()
                        }else{
                            list = JSON.parse(list)
                        }

                        Reflect.set(list,res.data.index,{
                            'id':res.data.id,
                            'order_id':value,
                            'type':res.data.type,
                            'index':res.data.index
                        })

                        list = JSON.stringify(list)

                        b2setCookie('b2_guest_buy_'+res.data.id+'_'+res.data.type,list)

                    }

                    this.success = true;
                    this.checkTime = null;
                    //b2delCookie('order_id')
                    if(typeof(B2VerifyPage) !== "undefined"){
                        B2VerifyPage.data.money = true
                        this.close()
                    }
                    if(typeof(carts) !== "undefined"){
                        carts.step = 3
                        this.close()
                    }
                    if(this.data.order_type === 'circle_read_answer_pay'){
                        b2CircleList.afterCommentGetData(b2CircleList.answer.listParent,b2CircleList.data[b2CircleList.answer.listParent].topic_id,'ask')
                        this.close()
                    }
                    if(this.data.order_type === 'circle_hidden_content_pay'){
                        b2CircleList.afterCommentGetData(b2CircleList.hiddenIndex,b2CircleList.data[b2CircleList.hiddenIndex].topic_id,'hidden')
                        this.close()
                    }
                }else{
                    this.checkTime = setTimeout(()=>{
                        this.checkAc()
                    },1000)
                }
            })
        },
        setTime(){
            if(this.show == false) return
            let diff_time = parseInt(this.endTime-this.currTime);
            let m = Math.floor((diff_time / 60 % 60));
            let s = Math.floor((diff_time % 60));
            this.timesec = (m > 0 ? m + '<b>'+b2_global.js_text.global.min+'</b>' : '') + s + '<b>'+b2_global.js_text.global.sec+'</b>';
            if(diff_time > 0){
                this.sTime = setTimeout(()=>{
                    this.endTime = this.endTime - 1;
                    this.setTime()
                },1000)
            }else{
                this.sTime = null
                this.success = 'fail'
            }
        },
        refresh(){
            if(this.data.order_type === 'circle_read_answer_pay'){
                b2CircleList.afterCommentGetData(b2CircleList.answer.listParent,b2CircleList.data[b2CircleList.answer.listParent].topic_id,'ask')
                this.close()
            }else if(this.data.order_type === 'circle_hidden_content_pay'){
                b2CircleList.afterCommentGetData(b2CircleList.hiddenIndex,b2CircleList.data[b2CircleList.hiddenIndex].topic_id,'hidden')
                this.close()
            }else{
                b2CurrentPageReload()
            }
        }
    },
    watch:{
        show(val){
            if(val){
                this.sTime = ''
                this.success = false
                this.checkTime = ''
            }else{
                this.sTime = null
                this.success = false
                this.checkTime = null
            }
        },
        data:{
            deep:true,
            handler(newName, oldName) {
                this.buildOrder()
            },
        }
    }
})

//扫码支付
var b2ScanPay = new Vue({
    el:'#scan-box',
    data:{
        data:[],
        show:false
    },
    methods:{
        close(){
            this.show = !this.show
        }
    }
})

Vue.component('ds-box', {
    props: ['show','money','msg','user','author','data','showtype'],
    template:b2_global.ds_box,
    data(){
        return {
            value:0,
            custom:0,
            content:'',
            payType:'',
            payMoney:'',
            locked:false,
            jump:'',
            href:'',
            isWeixin:'',
            isMobile:'',
            allow:[],
            card:[],
            cg:[],
            newWin: null,
            login:false,
            redirect:'',
            payData:'',
            waitOrder:false
        }
    },
    created(){
        this.isWeixin = b2isWeixin()
        if(b2token){
            this.login = true
        }
        this.redirect = b2getCookie('b2_back_url');
    },
    methods:{
        close(){
            this.$emit('close')
            this.locked = false
        },
        clean(){
            this.$emit('clean')
        },
        picked(m,val){
            this.value = val
            this.payMoney = m
        },
        post(url, params) {
            var temp = document.createElement("form"); //创建form表单
            temp.action = url;
            temp.method = "post";
            temp.target = "_blank";
            temp.style.display = "none";//表单样式为隐藏

            if(Object.keys(params).length > 0){
                Object.keys(params).forEach((key)=>{

                    var opt =document.createElement("input");  //添加input标签
                    opt.name = key;    //设置name属性
                    opt.value = params[key];   //设置value属性
                    temp.appendChild(opt);
                })
            }

            document.body.appendChild(temp);
            temp.submit();
            return temp;
        },
        restData(data = []){
            if(this.showtype == 'ds'){
                data = Object.assign(data,{
                    'title':this.$refs.dstitle.innerText,
                    'order_price':this.payMoney,
                    'order_type':'ds',
                    'post_id':b2_global.post_id,
                    'pay_type':this.payType,
                    'order_content':this.content
                })
            }else if(this.showtype == 'cz'){
                data = {
                    'title':b2_global.js_text.global.pay_money,
                    'order_price':this.payMoney,
                    'order_type':'cz',
                    'post_id':0,
                    'pay_type':this.payType
                }
            }else if(this.showtype == 'cg'){
                data = {
                    'title':b2_global.js_text.global.pay_credit,
                    'order_price':this.payMoney,
                    'order_type':'cg',
                    'post_id':0,
                    'pay_type':this.payType
                }
            }else{
                data = Object.assign(this.data,data)
            }
            data['pay_type'] = this.payType

            var url = new URL(window.location.href);
            url.searchParams.set('b2paystatus', 'check');

            data['redirect_url'] = url.href

            return data

        },
        disabled(){
            if(this.data.pay_type !=='card'){
                if(this.jump == '') return true
                if((this.jump == 'jump' || this.jump == 'mweb' || this.jump == 'jsapi') && this.href == '' && this.payData == '') return true
                if(this.locked == true) return true
                if(this.payType == '') return true
                if(this.payMoney === '') return true
            }else{
                if(!this.card.number || !this.card.password) return true
            }

            return false
        },
        chosePayType(val){

            if(this.locked == true) return;
            this.locked = true
            this.payType = val

            this.href = ''
            this.payData = ''
            this.jump = ''

            this.$http.post(b2_rest_url+'checkPayType','pay_type='+val).then(res=>{
                if(res.data.pay_type == 'card'){
                    this.$emit('change','card')
                    this.card.text = res.data.card_text
                    this.jump = res.data.pay_type
                    this.locked = false
                }else{
                    if(this.showtype == 'card'){
                        this.$emit('change','cz')
                    }
                    this.jump = res.data.pay_type

                    this.isMobile = res.data.is_mobile
                    if(this.jump == 'jump' || this.jump === 'mweb' || this.jump === 'jsapi'){

                        let data = Qs.stringify(this.restData());

                        this.waitOrder = true
                        this.$http.post(b2_rest_url+'buildOrder',data).then(res=>{
                            this.writeOrder(res.data.id)

                            if(typeof res.data.url == 'string'){
                                this.href = res.data.url
                            }else{
                                this.payData = res.data.url
                            }

                            this.waitOrder = false
                            this.locked = false

                        }).catch(err=>{

                            if(err.response.data.message.msg === 'bind_weixin'){
                                b2setCookie('b2_back_url',window.location.href)

                                if(typeof err.response.data.message.oauth == 'string'){
                                    b2weixinBind.msg = err.response.data.message.oauth
                                    b2weixinBind.show = true
                                }else{
                                    b2weixinBind.show = true
                                    b2weixinBind.url = err.response.data.message.oauth.weixin.url
                                }

                            }else{

                                Qmsg['warning'](err.response.data.message,{html:true});
                            }
                            this.waitOrder = false
                            this.locked = false
                        })

                        // let url = b2_global.pay_url+'?'+Qs.stringify(this.restData())
                        // this.href = 'javascript:void(0)'
                        //encodeURI(url)
                    }else{

                        this.locked = false
                    }
                }

                b2setCookie('b2_back_url',window.location.href)
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        writeOrder(id){
            b2setCookie('order_id',id)
        },
        balancePay(order_id){
            let data = this.restData();
            this.$http.post(b2_rest_url+'balancePay','order_id='+order_id).then(res=>{
                this.close()
                b2PayCheck.show = true
                b2PayCheck.title = data['title']
                this.locked = false
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        pay(){
            if(this.disabled()) return;

            if(this.jump === 'card'){
                if(this.locked == true) return
                this.locked = true
                this.$http.post(b2_rest_url+'cardPay',Qs.stringify(this.card)).then(res=>{
                    if(res.data === 'success'){
                        b2PayCheck.show = true
                        b2PayCheck.title = b2_global.js_text.global.pay_money_success
                        b2PayCheck.type = 'card'
                        this.close()
                    }
                    this.locked = false
                }).catch(err=>{

                    Qmsg['warning'](err.response.data.message,{html:true});
                    this.locked = false
                })
            }else if(this.jump === 'jump' || this.jump === 'mweb'){
                b2PayCheck.show = true
                b2PayCheck.title = this.$refs.dstitle.innerHTML

                this.close()

                if(this.payData){
                    this.post(this.payData.url,this.payData.data)
                }

            }else if(this.jump === 'balance'){
                if(this.locked == true) return
                this.locked = true

                let data = Qs.stringify(this.restData());
                this.$http.post(b2_rest_url+'buildOrder',data).then(res=>{
                    this.writeOrder(res.data)
                    this.balancePay(res.data)
                }).catch(err=>{

                    Qmsg['warning'](err.response.data.message,{html:true});
                    this.locked = false
                })
            }else if(this.jump === 'scan'){
                if(this.isMobile){
                    b2ScanPay.data = this.restData({
                        'is_weixin':this.isWeixin,
                        'is_mobile':this.isMobile
                    })
                }else{
                    b2ScanPay.data = this.restData()
                }
                b2ScanPay.show = true;
                this.close()
            }else if(this.jump === 'jsapi'){
                let data = this.restData();
                jsApiCall(this.payData)
                this.close()
                b2PayCheck.show = true
                b2PayCheck.title = data['title']
            }

            // else if(this.jump === 'mweb' || this.jump === 'jsapi'){
            //     if(this.locked == true) return
            //     this.locked = true
            //     let data = this.restData();
            //     let title = data['title']
            //     data = Qs.stringify(data);
            //     // if(!b2isWeixin()){
            //     //     this.newWin = window.open('',"_blank");
            //     // }

            //     this.$http.post(b2_rest_url+'buildOrder',data).then(res=>{
            //         this.writeOrder(res.data.order_id)
            //         if(this.jump === 'jsapi'){
            //             jsApiCall(res.data.link)
            //             this.close()
            //             b2PayCheck.show = true
            //             b2PayCheck.title = title
            //         }else{
            //             // window.open(res.data.link)
            //             // if(!b2isWeixin()){
            //             //     window.location=res.data.link
            //             // }else{

            //                 // let form = document.querySelector('#wechataction')
            //                 // form.action = res.data.link
            //                 // form.submit()
            //                window.location.href=res.data.link

            //                // window.location=res.data.link
            //                 // window.open(res.data.link)
            //             //}
            //             //location.assign();
            //             //window.open()
            //             this.close()
            //             b2PayCheck.show = true
            //             b2PayCheck.title = title
            //         }
            //         this.locked = false
            //     }).catch(err=>{
            //         if(err.response.data.message.msg === 'bind_weixin'){
            //             b2setCookie('b2_back_url',window.location.href)

            //             if(typeof err.response.data.message.oauth == 'string'){
            //                 b2weixinBind.msg = err.response.data.message.oauth
            //                 b2weixinBind.show = true
            //             }else{
            //                 b2weixinBind.show = true
            //                 b2weixinBind.url = err.response.data.message.oauth.weixin.url
            //             }

            //         }else{

            //             Qmsg['warning'](err.response.data.message,{html:true});
            //         }

            //         this.locked = false
            //     })
            // }
        },
        allowPayType(){
            this.$http.post(b2_rest_url+'allowPayType','show_type='+this.showtype).then(res=>{
                this.allow = res.data
                this.user.money = res.data.money
                if(res.data.dh){
                    this.cg.min = res.data.min
                    this.cg.dh = res.data.dh
                    this.payMoney = this.cg.min
                }
            })
        },
        creditAdd(){
            return parseInt(this.payMoney*this.cg.dh)
        }
    },
    watch:{
        money(val){
            if(this.payMoney == 0){
                this.payMoney = val[0]
            }
        },
        // payMoney(val){
        //     if(!/^[0-9]+.?[0-9]*/.test(val)){
        //         val = 0
        //     }
        //     if(this.href && val && this.jump == 'jump'){
        //         let url = b2_global.pay_url+'?'+Qs.stringify(this.restData())
        //         this.href = encodeURI(url)
        //     }
        //     this.payMoney = parseFloat(val)
        // },
        show(val){
            if(val){
                b2setCookie('b2_back_url',window.location.href)
                this.allowPayType()
            }
            if(val && this.money.length > 0){
                this.payMoney = this.money[0]
            }else if(val && this.data.length != 0){
                this.payMoney = this.data.order_price
            }else if(val == false){
                setTimeout(() => {
                    this.value = 0
                    this.payMoney = 0
                    this.payType = ''
                    this.clean()
                }, 300);
            }
        },
        payType(val){
            this.data.pay_type = val
        },
        showtype(val){

        }
    }
})

//支付容器
var b2DsBox = new Vue({
    el:'#ds-box',
    data:{
        money:[],
        show:false,
        msg:'',
        user:[],
        author:[],
        data:[],
        showtype:''
    },
    methods: {
        close(){
            this.show = !this.show
        },
        clean(){
            this.data = []
            this.money = []
        },
        change(type){
            this.showtype = type
        }
    },
})

var b2Ds = new Vue({
    el:'#content-ds',
    data:{
        data:''
    },
    methods:{
        show(){
            b2DsBox.money = this.data.moneys
            b2DsBox.show = true
            b2DsBox.showtype = 'ds'
            b2DsBox.msg = this.data.single_post_ds_text
        },
    }
})

function b2pay(event){
    let data = JSON.parse(event.getAttribute('data-pay'));
    b2DsBox.data = data
    b2DsBox.show = true
    b2DsBox.showtype = 'normal'
}

function b2creditpay(event){
    if(!b2token){
        login.show = true
    }else{
        let data = JSON.parse(event.getAttribute('data-pay'));
        payCredit.data = data
        payCredit.show = true
    }
}

//随机数
function uuid(len, radix) {
    var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('');
    var uuid = [], i;
    radix = radix || chars.length;
    if (len) {
      for (i = 0; i < len; i++) uuid[i] = chars[0 | Math.random()*radix];
    } else {
      var r;
      uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
      uuid[14] = '4';
      for (i = 0; i < 36; i++) {
        if (!uuid[i]) {
          r = 0 | Math.random()*16;
          uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
        }
      }
    }
    return uuid.join('');
}

function openWin(url,name,iWidth,iHeight) {
    var iTop = (window.screen.availHeight - 30 - iHeight) / 2;
    var iLeft = (window.screen.availWidth - 10 - iWidth) / 2;
    window.open(url, name, 'height=' + iHeight + ',innerHeight=' + iHeight + ',width=' + iWidth + ',innerWidth=' + iWidth + ',top=' + iTop + ',left=' + iLeft + ',status=no,toolbar=no,menubar=no,location=no,resizable=no,scrollbars=0,titlebar=no');
}

function deleteHtmlTag(str){
    str = str.replace(/<[^>]+>|&[^>]+;/g,"").trim();
    return str;
}

var b2cache=[];
function b2addJs(path,callback){
    var flag=0;//检查是否加载的状态
    for(var i=b2cache.length;i--;){
        b2cache[i]==path?flag=1:flag=0;
    }
    if(flag){//如果已经加载则不加载
        return;
    }
    var head = document.getElementsByTagName('head')[0];
    var script = document.createElement('script');
    script.src = path;
    script.type = 'text/javascript';
    head.appendChild(script);
    script.onload = script.onreadystatechange = function() {/*判断是否加载成功*/
    if (!this.readyState || this.readyState === "loaded" || this.readyState === "complete" ) {
        script.onload = script.onreadystatechange = null;
            callback();
        }
    };
    b2cache.push(path);//把加载过的存起来
}

//微信内支付
function jsApiCall(data){
    WeixinJSBridge.invoke(
        'getBrandWCPayRequest',
        data,
        function(res){}
    );
}

function callpay(){
    if (typeof WeixinJSBridge == "undefined"){
        if( document.addEventListener ){
            document.addEventListener('WeixinJSBridgeReady', jsApiCall, passiveSupported ? { passive: true } : false);
        }else if (document.attachEvent){
            document.attachEvent('WeixinJSBridgeReady', jsApiCall);
            document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
        }
    }else{
        jsApiCall();
    }
}

function b2SidebarSticky(){

    if(B2ClientWidth <= 768) return
    if(document.querySelector('.post-style-5')) return

    if(typeof window.b2Stick !== 'undefined'){
        for (let i = 0; i < window.b2Stick.length; i++) {
            if(window.b2Stick[i]){
                window.b2Stick[i].updateSticky()
            }
        }
        return
    }

    let b2sidebar = document.querySelectorAll('.sidebar');

    if(b2sidebar){
        if(B2ClientWidth > 768){
            var b2Stick = []
            for (let i = 0; i < b2sidebar.length; i++) {
                if(!b2sidebar[i].querySelector('.widget-ffixed')) continue
                b2Stick[i] = new StickySidebar(b2sidebar[i], {
                    containerSelector:'.widget-area',
                    topSpacing: 20,
                    resizeSensor: true,
                    bottomSpacing: 20
                });
            }

            window.b2Stick = b2Stick
        }
    }
}

Vue.component('credit-box', {
    props: ['show','data','user'],
    template:b2_global.credit_box,
    data(){
        return {
            locked:false
        }
    },
    methods:{
        close(){
            this.$emit('close')
        },
        writeOrder(id){
            b2setCookie('order_id',id)
        },
        disabled(){
            if(this.locked === true) return true
            if(parseInt(this.user.credit) < parseInt(this.data.order_price)) return true
            return false
        },
        creditPay(order_id){
            this.writeOrder(order_id)
            this.$http.post(b2_rest_url+'creditPay','order_id='+order_id).then(res=>{
                this.locked = false
                b2PayCheck.show = true
                this.close()
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        pay(){
            if(this.locked == true) return
            this.locked = true

            this.data.pay_type = 'credit'
            let data = Qs.stringify(this.data)
            this.$http.post(b2_rest_url+'buildOrder',data).then(res=>{
                this.creditPay(res.data)
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        }
    }
})

var payCredit = new Vue({
    el:'#credit-box',
    data:{
        show:false,
        data:[],
        user:{
            credit:0
        },
        author:[]
    },
    methods:{
        close(){
            this.show =  !this.show
        }
    }
})

var B2UserWidget = new Vue({
    el:'.b2-widget-user',
    data:{
        show:false,
        b2token:false
    },
    mounted(){
        this.b2token = b2token
    },
    computed:{
        userData(){
            return this.$store.state.userData
        },
        oauth(){
            return this.$store.state.oauthLink
        },
        announcement(){
            return this.$store.state.announcement
        },
        openOauth(){
            return this.$store.state.openOauth
        }
    },
    watch:{
        announcement(val){
            if(val && !this.show){
                this.resize()
            }
        }
    },
    methods:{
        resize(){
            this.$nextTick(()=>{
                if(!this.$refs.userWidget) return
                if(this.$refs.gujia){
                    this.$refs.gujia.style.display = 'none'
                }
                setTimeout(() => {
                    b2tooltip('.user-w-tips')
                }, 300);

                this.show = true
            })
        },
        markHistory(type){
            if(this.oauth.weixin.mp && type === 'weixin'){
                mpCode.show = true
            }
            b2setCookie('b2_back_url',window.location.href)
        }
    }
})

var b2Mission = new Vue({
    el:'.b2-widget-mission',
    data:{
        data:'',
        locked:false,
        type:'today',
        paged:1,
        count:0,
        pages:{
            today:1,
            always:1
        }
    },
    mounted(){
        if(this.$refs.missionWidget){
            this.getData()
        }
    },
    methods:{
        getData(count,paged){
            if(this.$refs.missionWidget){
                this.count = this.$refs.missionWidget.getAttribute('data-count')
            }else{
                this.count = 10
            }
            if(paged){
                this.paged = paged
            }
            this.$http.post(b2_rest_url+'getUserMission','count='+this.count+'&paged='+this.paged).then(res=>{
                this.data = res.data
                this.pages.today = res.data.mission_today_list.pages
                this.pages.always = res.data.mission_always_list.pages
                if(this.$refs.missiongujia){
                    this.$refs.missiongujia.style.display = 'none'
                }
                this.$nextTick(()=>{
                    b2RestTimeAgo(this.$el.querySelectorAll('.b2timeago'))
                })
            })
        },
        mission(){
            if(!b2token){
                login.show = true
            }else{
                if(this.data.mission.credit){

                    Qmsg['warning'](b2_global.js_text.global.has_mission,{html:true});
                    return
                }
                if(this.locked == true) return
                this.locked = true
                this.$http.post(b2_rest_url+'userMission').then(res=>{
                    this.$nextTick(()=>{
                        this.data.mission = res.data.mission
                        this.locked = false
                    })
                    this.getData(this.count,this.paged)
                }).catch(err=>{

                    Qmsg['warning'](err.response.data.message,{html:true});
                    this.locked = false
                })
            }
        }
    }
})

var b2NewComment = new Vue({
    el:'.b2-widget-comment',
    data:{
        data:'',
        paged:1,
        pages:1,
        count:5,
        hidden:1,
        next:false,
        prev:false,
        locked:false
    },
    mounted(){
        if(this.$refs.commentWidget){
            this.count = this.$refs.commentWidget.getAttribute('data-count');
            this.hidden = this.$refs.commentWidget.getAttribute('data-hidden');
            this.getList()
            if(this.paged == 1){
                this.prev = true
            }
        }
    },
    methods:{
        getList(){
            if(this.locked == true) return
            this.locked = true
            this.$https.post(b2_rest_url+'getNewComments','paged='+this.paged+'&count='+this.count+'&hidden='+this.hidden).then(res=>{
                this.data = res.data.data
                this.pages = res.data.pages
                if(this.$refs.gujia){
                    this.$refs.gujia.style.display = 'none'
                }

                this.$nextTick(()=>{
                    b2SidebarSticky()
                    b2RestTimeAgo(this.$refs.commentWidget.querySelectorAll('.b2timeago'))
                })
                this.locked = false
            })
        },
        nexAc(){
            if(this.next || this.locked) return
            this.paged++
            this.getList()
        },
        prevAc(){
            if(this.prev || this.locked) return
            this.paged--
            this.getList()
        }
    },
    watch:{
        paged(val){
            if(val <= 1){
                this.prev = true
            }else{
                this.prev = false
            }
            if(val >= this.pages){
                this.next = true
            }else{
                this.next = false
            }
        }
    }
})

//移动端点击展开筛选
// function b2ShowFilterBox(event){

//     let filterBox = event.parentNode.nextElementSibling

//     if(filterBox.className.indexOf('b2-show') !== -1){
//         filterBox.className = filterBox.className.replace('b2-show','')
//         bodyScrollLock.lock(filterBox)
//     }else{
//         filterBox.className += ' b2-show'
//         bodyScrollLock.unlock(filterBox)
//     }
// }

var b2mobileFooterMenu = new Vue({
    el:'#mobile-footer-menu',
    data:{
        msg:0,
        show:true
    }
})

var postPoBox = new Vue({
    el:'#post-po-box',
    data:{
        show:false,
        login:false,
        allow:''
    },
    mounted(){
        if(b2token){
            this.login = true
        }
    },
    methods:{
        close(){
            this.showPost = !this.showPost
        },
        go(val,type){
           if(!this.login){
                login.show = true
                login.type = 0
                return
           }
           if(type != 'request'){
                if(!userTools.role[type]){

                    Qmsg['warning'](b2_global.js_text.global.not_allow,{html:true});
                    return
                }
           }
           setTimeout(() => {
            window.location.href = val;
           }, 250);

        }
    }
})

var b2AsideBar = new Vue({
    el:'.aside-container',
    data:{
        dmsg:{
            count:0
        },
        msg:{
            count:0
        },
        showBox:false,
        showType:{
            'user':false,
            'msg':false,
            'dmsg':false,
            'mission':false,
            'coupon':false
        },
        locked:false,
        mission:[],
        bool:false,
        coupon:{
            count:0,
            data:''
        },
        showCouponInfo:[],
        qrcode:'',
        ref:'',
        dlocked:false,
        count:0,
        cartLocked:false,
        b2token:false
    },
    computed:{
        userData(){
            return this.$store.state.userData
        },
        carts(){
            if(this.count && !this.cartLocked){
                return {
                    count:this.count,
                    data:''
                }
            }else{
                let carts = this.$store.state.carts
                return {
                    count:carts !== null && carts !== undefined ? Object.keys(carts).length : 0,
                    data:carts
                }
            }
        }
    },
    mounted(){
        this.b2token = b2token
    },
    methods:{
        getQrcode(url){
            var url = new URL(url)
            url.searchParams.set('ref', this.ref)
            var qr = new QRious({
                value: url.href,
                size:120,
                level:'L'
              });
            return qr.toDataURL('image/jpeg');
        },
        goMyPage(){
            if(!b2token){
                login.show = true
                login.type = 0
                return
            }

            window.location.href = this.userData.link
        },
        chat(){
            if(b2_global.chat.type == 'crisp'){
                $crisp.push(['do', 'chat:open'])
            }
            if(b2_global.chat.type == 'qq'){
                window.open("http://wpa.qq.com/msgrd?v=3&uin="+b2_global.chat.qq+"&site=qq&menu=yes", "_blank");
            }
            if(b2_global.chat.type == 'dmsg'){
                if(!b2token){
                    login.show = true
                }else{
                    b2Dmsg.userid = b2_global.chat.dmsg
                    b2Dmsg.show = true
                }
            }
            if(b2_global.chat.type == 'requests'){
                if(!b2token){
                    login.show = true
                }else{
                    window.open(b2_global.home_url+'/requests', "_blank");
                }
            }
        },
        show(type,value){
            this.closeBox()
            if(type === 'user'){
                if(!b2token){
                    login.show = true
                    login.type = 0
                    return
                }
            }
            if(type === 'dmsg' && this.dmsg.count == 0){
                if(!b2token){
                    login.show = true
                    login.type = 0
                    return
                }
                this.jumpTo(value)
                return;
            }
            if(type === 'msg'){
                if(!b2token){
                    login.show = true
                    login.type = 0
                    return
                }
                this.jumpTo(value)
                return;
            }
            if(type == 'mission'){
                this.mission = b2Mission
                if(this.mission.data.length == 0){
                    this.mission.getData(10)
                }
            }
            if(type === 'coupon'){
                this.getMyCoupons()
            }

            if(type === 'cart'){
                if(!b2token){
                    login.show = true
                    login.type = 0
                    return
                }

                this.getMycarts()
            }
            this.showType[type] = true
            this.showBox = true
            // if(B2ClientWidth < 768){
            //     bodyScrollLock.lock(this.$refs.asideContainer)
            // }
            this.$nextTick(()=>{
                b2RestTimeAgo(this.$el.querySelectorAll('.b2timeago'))
            })
        },
        getMycarts(){
            if(this.cartLocked == true) return
            this.cartLocked = true
            this.$http.get(b2_rest_url+'getMyCarts').then((res)=>{
                if(Object.keys(res.data).length > 0){
                    this.$store.commit('setcartsData',res.data)
                }
            })

        },
        showAc(val){
            if(!b2token){
                login.show = true
                return
            }

            if(val){
                this.show('user')
            }else{
                this.close()
            }
        },
        closeBox(){
            Object.keys(this.showType).forEach(key => {
                this.showType[key] = false
            });
            this.showBox = false
            // if(B2ClientWidth < 768){
            //     bodyScrollLock.unlock(this.$refs.asideContainer)
            // }

        },
        getNewDmsg(){
            if(b2token){
                if(this.locked) return
                this.locked = true
                this.$http.post(b2_rest_url+'getNewDmsg').then(res=>{
                    this.dmsg = res.data.dmsg
                    this.locked = false
                })
            }
        },
        close(){
            if(this.$refs.asideContainer && this.$refs.asideContainer.className.indexOf('aside-show') && B2ClientWidth < 768){
                this.$refs.asideContainer.className = this.$refs.asideContainer.className.replace(' aside-show','')
                // if(B2ClientWidth < 768){
                //     bodyScrollLock.unlock(this.$refs.asideContainer)
                // }
                this.showBox = false
            }else{
                this.closeBox()
            }
        },
        goTop(){
            this.$scrollTo('.site', 300, {offset: 0})
        },
        login(){
            if(!b2token){
                login.show = true
                return
            }else if(!this.$refs.asideContent){
                self.location = this.$store.state.userData.link
            }else{
                this.show('user')
                this.$refs.asideContainer.className += ' aside-show';
            }
        },
        showSearch(){
            b2SearchBox.close()
        },
        jumpTo(url){
            window.location.href = url;
        },
        updateCarts(){
            let data = b2getCookie('carts')
            if(data){
                this.carts.data = JSON.parse(data)
            }else{
                this.carts.data = ''
            }


            if(this.carts.data){
                if(this.carts.count > Object.keys(this.carts.data).length){
                    b2mobileFooterMenu.msg = b2mobileFooterMenu.msg - (this.carts.count - Object.keys(this.carts.data).length)
                }else{

                    b2mobileFooterMenu.msg = b2mobileFooterMenu.msg + (Object.keys(this.carts.data).length - this.carts.count)
                }
                this.carts.count = Object.keys(this.carts.data).length
            }

        },
        deleteCarts(id){
            this.$https.post(b2_rest_url+'deleteMyCarts','id='+id).then(res=>{

                if(res.data.length == 0){
                    this.$store.commit('setcartsData',{})
                }else{
                    this.$store.commit('setcartsData',res.data)
                }
            })
        },
        getMyCoupons(){
            if(!b2token){
                login.show = true
                return
            }

            this.showType.coupon = true

            this.$https.get(b2_rest_url+'getMyCoupons').then(res=>{
                this.coupon = res.data
            })
        },
        couponClass(item){
            if(item.expiration_date.expired) return 'stamp04'
            if(item.products.length > 0) return 'stamp01'
            if(item.cats.length > 0) return 'stamp02'
            return 'stamp03'
        },
        couponMoreInfo(id){
            this.$set(this.showCouponInfo,id,!this.showCouponInfo[id])
        },
        deleteCoupon(id){
            var r = confirm(b2_global.js_text.global.delete_coupon)
            if(r){
                this.$https.post(b2_rest_url+'deleteMyCoupon','id='+id).then(res=>{
                    this.$delete(this.coupon.data,id)
                    this.$set(this.coupon,'count',this.coupon.count-1)
                })
            }
            return
        }
    },
    watch:{
        userData(val){
            if(val && this.$refs.asideContent){
                if(b2token){
                    this.ref = val.user_code
                    this.getNewDmsg()
                }

                this.updateCarts()
            }
        },
        dmsg:{
            handler(newVal, old) {
                if(newVal.count > 0){
                    b2mobileFooterMenu.msg += parseInt(newVal.count)
                }
            },
            immediate: true,
            deep: true
        },
        msg:{
            handler(newVal, old) {
                if(newVal.count > 0){
                    b2mobileFooterMenu.msg += parseInt(newVal.count)
                }
            },
            immediate: true,
            deep: true
        },
        showBox(val){
            if(val && B2ClientWidth < 768){
                this.$refs.asideContainer.className += ' aside-show'
            }
        }
    }
})

function b2HiddenFilterBox(event){
    event.parentNode.parentNode.className = event.parentNode.parentNode.className.replace('b2-show','');
    //bodyScrollLock.unlock(event.parentNode.parentNode)
}

function b2flickity(){
    if(B2ClientWidth < 768) return
    var f = document.querySelectorAll('.home-collection-silder');
    if(f){
        var collection = []
        for (let i = 0; i < f.length; i++) {
            collection[i] = new Flickity(f[i],{
                pageDots: false,
                groupCells: true,
                draggable: true,
                prevNextButtons: false,
                freeScroll: false,
                wrapAround:true,
                selectedAttraction:0.15,
                friction:1,
                freeScrollFriction: 0.1,
                cellAlign: 'left'
            });

            let previous,next

            if(f[i].querySelector('.coll-3-box')){
                previous = f[i].parentNode.querySelector('.collection-previous');
            }else{
                previous = f[i].parentNode.parentNode.parentNode.querySelector('.collection-previous');
            }

            previous.addEventListener( 'click', function() {
                collection[i].previous();
            },passiveSupported ? { passive: true } : false);

            if(f[i].querySelector('.coll-3-box')){
                next = f[i].parentNode.querySelector('.collection-next');
            }else{
                next = f[i].parentNode.parentNode.parentNode.querySelector('.collection-next');
            }

            next.addEventListener( 'click', function() {
                collection[i].next();
            },passiveSupported ? { passive: true } : false);
        }
    }
}

b2flickity()

function b2HiddenFooter(){
    let footer = document.querySelector('.site-footer .site-footer-widget-in')
    if(!footer) return
    let footerWidget = footer.querySelectorAll('.mobile-hidden')

    if(footerWidget && footerWidget.length >= footer.childNodes.length){
        document.querySelector('.site-footer').className += ' mobile-hidden';
    }
}
b2HiddenFooter()

var b2SearchUser = new Vue({
    el:'#user-list',
    data:{
        follow:[],
        ids:[]
    },
    mounted(){
        if(this.$refs.searchUser){
            this.ids = b2_search_data.users
            this.checkFollowByids()
        }
    },
    methods:{
        checkFollowByids(){
            let data = {
                'ids':this.ids
            }
            this.$http.post(b2_rest_url+'checkFollowByids',Qs.stringify(data)).then(res=>{
                this.follow = res.data
            })
        },
        followAc(id){
            if(!b2token){
                login.show = true
            }else{
                this.$http.post(b2_rest_url+'AuthorFollow','user_id='+id).then(res=>{
                    this.follow[id] = res.data
                }).catch(err=>{

                    Qmsg['warning'](err.response.data.message,{html:true});
                })
            }
        },
        dmsg(id){
            if(!b2token){
                login.show = true
            }else{
                b2Dmsg.userid = id
                b2Dmsg.show = true
            }
        }
    }
});

//精确计算
(function () {
    var calc = {
        /*
        函数，加法函数，用来得到精确的加法结果
        说明：javascript的加法结果会有误差，在两个浮点数相加的时候会比较明显。这个函数返回较为精确的加法结果。
        参数：arg1：第一个加数；arg2第二个加数；d要保留的小数位数（可以不传此参数，如果不传则不处理小数位数）
        调用：Calc.Add(arg1,arg2,d)
        返回值：两数相加的结果
        */
        Add: function (arg1, arg2) {
            arg1 = arg1.toString(), arg2 = arg2.toString();
            var arg1Arr = arg1.split("."), arg2Arr = arg2.split("."), d1 = arg1Arr.length == 2 ? arg1Arr[1] : "", d2 = arg2Arr.length == 2 ? arg2Arr[1] : "";
            var maxLen = Math.max(d1.length, d2.length);
            var m = Math.pow(10, maxLen);
            var result = Number(((arg1 * m + arg2 * m) / m).toFixed(maxLen));
            var d = arguments[2];
            return typeof d === "number" ? Number((result).toFixed(d)) : result;
        },
        /*
        函数：减法函数，用来得到精确的减法结果
        说明：函数返回较为精确的减法结果。
        参数：arg1：第一个加数；arg2第二个加数；d要保留的小数位数（可以不传此参数，如果不传则不处理小数位数
        调用：Calc.Sub(arg1,arg2)
        返回值：两数相减的结果
        */
        Sub: function (arg1, arg2) {
            return Calc.Add(arg1, -Number(arg2), arguments[2]);
        },
        /*
        函数：乘法函数，用来得到精确的乘法结果
        说明：函数返回较为精确的乘法结果。
        参数：arg1：第一个乘数；arg2第二个乘数；d要保留的小数位数（可以不传此参数，如果不传则不处理小数位数)
        调用：Calc.Mul(arg1,arg2)
        返回值：两数相乘的结果
        */
        Mul: function (arg1, arg2) {
            var r1 = arg1.toString(), r2 = arg2.toString(), m, resultVal, d = arguments[2];
            m = (r1.split(".")[1] ? r1.split(".")[1].length : 0) + (r2.split(".")[1] ? r2.split(".")[1].length : 0);
            resultVal = Number(r1.replace(".", "")) * Number(r2.replace(".", "")) / Math.pow(10, m);
            return typeof d !== "number" ? Number(resultVal) : Number(resultVal.toFixed(parseInt(d)));
        },
        /*
        函数：除法函数，用来得到精确的除法结果
        说明：函数返回较为精确的除法结果。
        参数：arg1：除数；arg2被除数；d要保留的小数位数（可以不传此参数，如果不传则不处理小数位数)
        调用：Calc.Div(arg1,arg2)
        返回值：arg1除于arg2的结果
        */
        Div: function (arg1, arg2) {
            var r1 = arg1.toString(), r2 = arg2.toString(), m, resultVal, d = arguments[2];
            m = (r2.split(".")[1] ? r2.split(".")[1].length : 0) - (r1.split(".")[1] ? r1.split(".")[1].length : 0);
            resultVal = Number(r1.replace(".", "")) / Number(r2.replace(".", "")) * Math.pow(10, m);
            return typeof d !== "number" ? Number(resultVal) : Number(resultVal.toFixed(parseInt(d)));
        }
    };
    window.Calc = calc;
}());

function b2stmap(){
    let stmaps = document.querySelectorAll('.stamp');
    if(stmaps.length > 0){
        let h_axios = axios

        if(b2token){
            h_axios.defaults.headers.common['Authorization'] = 'Bearer ' + b2token
        }

        for (let i = 0; i < stmaps.length; i++) {
            if(stmaps[i].querySelector('.coupon-receive')){
                stmaps[i].querySelector('.coupon-receive').onclick = (event)=>{
                    if(!b2token){
                        login.show = true
                        return
                    }
    
                    h_axios.post(b2_rest_url+'ShopCouponReceive','&id='+event.target.getAttribute('data-id')).then((res)=>{
                        if(res.data){
                            Qmsg['success'](b2_global.js_text.global.get_success,{html:true});
                        }
                    }).catch(err=>{
                        Qmsg['success'](err.response.data.message,{html:true});
                    })
    
                }
                stmaps[i].querySelector('.more-coupon-info').onclick = (event)=>{
                    event.target.nextElementSibling.style.display = 'block'
                }
                stmaps[i].querySelector('.close-coupon-info').onclick = (event)=>{
                    event.target.parentNode.parentNode.style.display = 'none'
                }
            }
        }
    }
}
b2stmap()

// document.ready(function () {
//     let link = document.createElement("link");
//     link.rel = "stylesheet";
//     link.href = "//at.alicdn.com/t/font_1105559_00rtseygv6tsc.css";

//     document.head.appendChild(link);
// })

function b2IsPhoneAvailable(phonevalue){
    var phoneReg = /^1[0-9]{10}/;
    var emailReg = /[a-zA-Z0-9]{1,10}@[a-zA-Z0-9]{1,5}\.[a-zA-Z0-9]{1,5}/;
    if(phoneReg.test(phonevalue) || emailReg.test(phonevalue)){
        return true;
    }else{
        return false;
    }
}

var b2TaxTop = new Vue({
    el:'.tax-header',
    data:{
        showFliter:{
            hot:false,
            cat:false
        }
    },
    methods:{
        show(type){
            if(type === 'hot'){
                this.showFliter.hot = !this.showFliter.hot
                this.showFliter.cat = false
            }
            if(type === 'cat'){
                this.showFliter.cat = !this.showFliter.cat
                this.showFliter.hot = false
            }
        }
    }
})

function b2scroll( fn ) {
    var beforeScrollTop = document.documentElement.scrollTop,
      fn = fn || function() {};

    window.bodyScrool = function() {
        var afterScrollTop = document.documentElement.scrollTop || document.body.scrollTop,
          delta = afterScrollTop - beforeScrollTop;

        if( delta === 0 ) return false;

          fn( delta > 0 ? "down" : "up" ,afterScrollTop);

        beforeScrollTop = afterScrollTop;
    }

    window.addEventListener("scroll",window.bodyScrool , passiveSupported ? { passive: true } : false);
}

function b2HeaderTop(){

    const banner = document.querySelector('.header-banner-left');
    const header = document.querySelector('.site');
    const aside = document.querySelector('.bar-user-info');
    const socialTop = document.querySelector('.social-top');
    const nosub = document.querySelector('.social-no-sub');
    const footer = document.querySelector('.mobile-footer-menu');
    if(!banner) return

    let h = 96
    if(B2ClientWidth < 768){
        h = 77
    }

    if(socialTop){
        h = 113
    }

    if(nosub){
        h = 58
    }

    b2scroll(function(direction,top) {

        if(top > h){
            if(direction === 'down'){
                if(banner.className.indexOf(' hidden') === -1){
                    banner.className += ' hidden'
                }
                if(header.className.indexOf(' up') === -1){
                    header.className += ' up'
                }


                if(footer && B2ClientWidth < 768 && footer.className.indexOf(' footer-down') === -1){
                    footer.className += ' footer-down'
                }

              }else{
                banner.className = banner.className.replace(' hidden','')
                header.className = header.className.replace(' up','')
 

                if(footer && B2ClientWidth < 768){
                    footer.className = footer.className.replace(' footer-down','')
                }
              }
              if(header.className.indexOf(' action') === -1){
                header.className += ' action'
              }
        }else{
            header.className = header.className.replace(' action','')
            banner.className = banner.className.replace(' hidden','')
            header.className = header.className.replace(' up','')
        
        }
    });
}

b2HeaderTop()

var b2NewsfalshesWidget = new Vue({
    el:'.widget-newsflashes-box',
    data:{
        options:[],
        list:''
    },
    mounted(){
        if(this.$refs.newsWidget){
            this.options = JSON.parse(this.$refs.newsWidget.getAttribute('data-json'))
            this.getList()
        }
    },
    methods:{
        getList(){
            this.$https.post(b2_rest_url+'getWidgetNewsflashes',Qs.stringify(this.options)).then(res=>{
                this.list = res.data
                this.$refs.gujia.style.display = 'none'
            })
        }
    }
})

Vue.component('weixin-bind', {
    props: ['show','url','msg'],
    template:b2_global.weixin_bind,
    methods:{
        close(){
            this.$emit('close')
        }
    }
})

//微信绑定
var b2weixinBind = new Vue({
    el:'#weixin-bind',
    data:{
        show:false,
        url:'',
        msg:''
    },
    methods:{
        close(){
            this.show = !this.show
        }
    }
})

function b2CurrentPageReload(url){
    if(!url){
        url = location.href
    }
    setTimeout(() => {
        location.replace(url)
    }, 200);
    
}

function b2GetQueryVariable(variable)
{
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            if(pair[0] == variable){return pair[1];}
    }
    return(false);
}

function b2removeURLParameter(url, parameter) {
    //prefer to use l.search if you have a location/link object
    var urlparts = url.split('?');
    if (urlparts.length >= 2) {

        var prefix = encodeURIComponent(parameter) + '=';
        var pars = urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i = pars.length; i-- > 0;) {
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                pars.splice(i, 1);
            }
        }

        return urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
    }
    return url;
}

function updateURLParameter(uri, key, value) {
	if(!value) {
		return uri;
	}
	var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
	var separator = uri.indexOf('?') !== -1 ? "&" : "?";
	if (uri.match(re)) {
		return uri.replace(re, '$1' + key + "=" + value + '$2');
	}
	else {
		return uri + separator + key + "=" + value;
	}
}

function validate(evt) {
    var theEvent = evt || window.event;

    // Handle paste
    if (theEvent.type === 'paste') {
        key = event.clipboardData.getData('text/plain');
    } else {
    // Handle key press
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
    }
    var regex = /[0-9]|\./;
    if( !regex.test(key) ) {
      theEvent.returnValue = false;
      if(theEvent.preventDefault) theEvent.preventDefault();
    }
}

Vue.component('bind-login', {
    props: ['show','type'],
    template:b2_global.bind_login,
    data(){
        return {
            locked:false,
            count:60,
            SMSLocked:false,
            data:{
                img_code:'',
                token:'',
                username:'',
                password:'',
                confirmPassword:'',
                code:''
            },
            eye:false,
            success:''
        }
    },
    computed:{
        userData(){
            return this.$store.state.userData;
        }
    },
    methods:{
        close(){
            this.$emit('close')
        },
        showCheck(){
            if(this.type !== 'text' && this.type !== 'luo' && this.data.username && this.show){
                return true
            }
            return false
        },
        sendCode(){
            recaptcha.show = true
            recaptcha.type = 'bind'
            this.close()
        },
        sendSMS(){
            if(this.SMSLocked == true) return
            this.SMSLocked = true
            this.$http.post(b2_rest_url+'sendCode',Qs.stringify(this.data)).then(res=>{
                if(res.data.token){
                    this.countdown()
                    this.data.smsToken = res.data.token
                }
                this.SMSLocked = false
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.SMSLocked = false
            })
        },
        countdown(){
            if(this.count <= 1 ){
                this.count = 60
                return
            }
            this.count --;
            setTimeout(()=>{
                this.countdown()
            },1000)
        },
        setToken(val){
            this.data.img_code = val.value
            this.data.token = val.token
            this.sendSMS()
        },
        submit(){
            if(this.locked) return
            this.locked = true
            this.$http.post(b2_rest_url+'bindUserLogin',Qs.stringify(this.data)).then(res=>{
                this.success = res.data
                this.locked = false
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        }
    }
})

//强制绑定登录名
var b2bindLogin = new Vue({
    el:'#binding-login',
    data:{
        show:false,
        type:false
    },
    methods:{
        close(){
            this.show = !this.show
        },
        imgCodeAc(val){
            this.$refs.bindBox.setToken(val)
        }
    },
    watch:{
        type(val){
            if(val){
                this.show = true
            }
        }
    }
})

var b2CreditTop = new Vue({
    el:'.credit-top',
    data:{
        settings:[],
        data:''
    },
    mounted(){
        if(this.$refs.creditTop){
            this.settings = JSON.parse(this.$refs.creditTop.getAttribute('data-settings'));
            this.getList()
        }
    },
    methods:{
        getList(){
            this.$http.post(b2_rest_url+'getGoldTop',Qs.stringify(this.settings)).then(res=>{
                this.data = res.data
                this.$nextTick(()=>{
                    this.$refs.creditTopGujia.style.display = 'none'
                })
            })
        }
    }
})

function grin(tag,myField) {

    if (document.selection) {
        myField.focus();
        sel = document.selection.createRange();
        sel.text = tag;
        myField.focus();
    }
    else if (myField.selectionStart || myField.selectionStart == '0') {
        var startPos = myField.selectionStart;
        var endPos = myField.selectionEnd;
        var cursorPos = startPos;
        myField.value = myField.value.substring(0, startPos)
                + tag
                + myField.value.substring(endPos, myField.value.length);
        cursorPos += tag.length;
        myField.focus();
        myField.selectionStart = cursorPos;
        myField.selectionEnd = cursorPos;

    } else {
        myField.value += tag;
        myField.focus();
    }
}

//圈子小工具
var b2HotCircle = new Vue({
    el:'.b2-widget-hot-circle',
    data:{
        data:'',
        count:6,
        type:'hot',
        paged:{
            hot:1,
            join:1,
            create:1
        },
        locked:false
    },
    mounted(){
        if(!this.$refs.hotCircle) return
        this.count = this.$refs.hotCircle.getAttribute('data-count');
        this.getCirclesList('hot')
    },
    methods:{
        go(link){

            window.location.href = link
        },
        getCirclesList(type){
            if(!b2token && type !== 'hot'){
                login.show = true
                login.type = 1
                return
            }
            if(this.locked === true) return
            this.locked = true
            let data = {
                count:this.count,
                type:type,
                paged:this.paged[type]
            }
            this.$refs.gujia.style.display = 'block'
            this.data = ''
            this.$http.post(b2_rest_url+'getCirclesList',Qs.stringify(data)).then(res=>{
                this.data = res.data
                this.$nextTick(()=>{
                    this.$refs.gujia.style.display = 'none'
                    b2SidebarSticky()
                    lazyLoadInstance.update()
                })
                this.type = type
                this.locked = false
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        }
    }
})

if (!window.scrollTo) {
    window.scrollTo = function (x, y) {
        window.pageXOffset = x;
        window.pageYOffset = y;
    };
}
if (!window.scrollBy) {
    window.scrollBy = function (x, y) {
        window.pageXOffset += x;
        window.pageYOffset += y;
    };
}
if (!document.body.scrollTo) {
    Element.prototype.scrollTo = function (x, y) {
        this.scrollLeft = x;
        this.scrollTop = y;
    };
}
if (!document.body.scrollBy) {
    Element.prototype.scrollBy = function (x, y) {
        this.scrollLeft += x;
        this.scrollTop += y;
    };
}

var payReturn = new Vue({
    el:'#pay-return',
    data:{
        login:false
    },
    mounted(){
        if(b2token){
            this.login = true
        }
    }
})

const searchBox = document.querySelectorAll('.search-module')

if(searchBox){
    for (let index = 0; index < searchBox.length; index++) {
        let id = searchBox[index].getAttribute('data-i')
        new Vue({
            el:'#search-module-'+id,
            data:{
                link:b2_global.home_url,
                keyword:'',
                category:b2_global.js_text.global.all,
                show:false
            },
            mounted(){
                document.onclick = (e)=>{

                    this.show = false
                    userTools.hideAction()
                }
            },
            methods:{
                picked(id,category,link){
                   this.link = link
                   this.category = category
                   this.show = false
                },
                pickedKey(key){

                }
            }
        })

    }
}

var b2recommendedCircle = new Vue({
    el:'.b2-widget-recommended-circle',
    data:{
        data:'',
        current:0
    },
    mounted(){
        if(!this.$refs.recommendedGujia) return
        this.getCircles()
    },
    methods:{
        create(){
            if(!b2token){
                login.show = true
                login.loginType = 1
            }else{
                window.open(b2_global.home_url+'/create-circle', '_blank');
            }
        },
        getCircles(){
            let ids = JSON.parse(this.$refs.recommendedGujia.getAttribute('data-ids'))
            if(ids.length == 0) return
            let data = {
                ids:ids
            }

            this.$http.post(b2_rest_url+'getCircleDataByCircleIds',Qs.stringify(data)).then(res=>{
                this.data = res.data
                this.$nextTick(()=>{
                    this.$refs.recommendedGujia.style.display = 'none'
                    if(typeof b2CirclePostBox != 'undefined'){
                        if(b2CirclePostBox.$refs.textareaTopic){
                            this.current = parseInt(b2CirclePostBox.$refs.textareaTopic.getAttribute('data-circle'))
                        }
                        
                    }
                    this.$nextTick(()=>{
                        lazyLoadInstance.update()
                    })
                    b2SidebarSticky()
                })
            })
        },
        go(even,index){
            if(typeof b2CirclePostBox != 'undefined'){
                even.stopPropagation()
                even.preventDefault()
                let id = this.data[index].id
                b2CirclePostBox.circle.picked = id
                this.current = id
                b2CircleList.pickedCircle('widget',id)
                b2CirclePostBox.getCurrentUserCircleData()
                window.history.pushState(id, this.data[index].name, this.data[index].link)
                document.title = this.data[index].name+' '+b2_global.site_separator+' '+b2_global.site_name
                // b2AsideBar.goTop()
                return false
            }
        }
    }
})

document.b2ready = function (callback) {
    ///兼容FF,Google
    if (document.addEventListener) {
        document.addEventListener('DOMContentLoaded', function () {
            document.removeEventListener('DOMContentLoaded', arguments.callee, false);
            callback();
        }, false)
    }
     //兼容IE
    else if (document.attachEvent) {
        document.attachEvent('onreadystatechange', function () {
              if (document.readyState == "complete") {
                        document.detachEvent("onreadystatechange", arguments.callee);
                        callback();
               }
        })
    }
    else if (document.lastChild == document.body) {
        callback();
    }
}

function b2tooltip(cla){

    const d = document.querySelectorAll(cla)
    if(!d) return
    if(B2ClientWidth < 768) return

    var x = 15;
    var y = 10;

    window.b2thistip = []

    for (let i = 0; i < d.length; i++) {
        d[i].addEventListener("mouseover", function(e) {

            var tooltip = "<div class='b2tooltip' id='b2tooltip"+i+"'>" + this.getAttribute('data-title') + "</div>";

            document.body.insertAdjacentHTML('beforeend', tooltip);

            window.b2thistip[i] = document.querySelector('#b2tooltip'+i)

            window.b2thistip[i].style.top = (e.pageY + y) + "px"
            window.b2thistip[i].style.left = (e.pageX + x) + "px"

            window.b2thistip[i].style.display = 'block'

        }, passiveSupported ? { passive: true } : false);

        d[i].addEventListener("mouseout", function(e) {
            if(typeof window.b2thistip[i] != 'undefined')
            window.b2thistip[i].remove()
        },passiveSupported ? { passive: true } : false)

        d[i].addEventListener("mousemove", function(e) {

            window.b2thistip[i].style.top = (e.pageY + y) + "px"
            window.b2thistip[i].style.left = (e.pageX + x) + "px"
        },passiveSupported ? { passive: true } : false)
    }

}

b2tooltip('.b2tooltipbox')

function b2cpay(){
    const paybox = document.querySelectorAll('.custom-pay-box')

    if(paybox.length > 0){
        for (let i = 0; i < paybox.length; i++) {
            new Vue({
                el:paybox[i],
                data:{
                    locked:{},
                    count:{},
                    files:{},
                    progress:{},
                    // edited by fuzqing
                    related:false,
                    related_field:'',
                    related_prices:[],
                    active_time:{
                        active:false,
                        tips:''
                    },

                    price:'',
                    api:'getCpayResout',
                    selecter:'cpay-resout-list-in',
                    list:{
                        pages:0,
                        paged:1,
                        count:20,
                        id:0,
                        data:''
                    },
                    allow:0,
                    tab:'form'
                },
                mounted(){
                    if(this.$refs.pickprice){
                        this.price = this.$refs.pickprice.getAttribute('data-price')
                    }
                    if(this.$refs.cpayresout){
                        this.list.id = this.$refs.cpayresout.getAttribute('data-id')
                        // this.getList()
                        this.$refs.reslist.go(this.list.paged,'comment',true,true)
                    }
                    // edited by fuzqing
                    this.getCpayInfo();
                },
                // edited by fuzqing
                watch: {
                    related_field: function (val) {
                        this.price = this.related_prices[val];
                    }
                },
                methods:{
                    // edited by fuzqing
                    getCpayInfo() {
                        this.$http.post(b2_rest_url+'getCpayInfo','post_id='+b2_global.post_id).then(res=>{
                            if (res.data.related === true) {
                                this.related = true;
                                this.related_field = res.data.related_field_value;
                                this.related_prices = res.data.related_prices;
                            }
                            this.active_time = res.data.active_time;
                        }).catch(err=>{
                            Qmsg['warning'](err.response.data.message,{html:true});
                        })
                    },
                    getList(res){
                        this.list.data = res.data
                        this.allow = res.allow
                        this.list.pages = res.pages
                    },
                    submit(title,id,postid) {
                        let obj = {};
                        let data = new FormData(this.$refs.form);
                        for (let [key, value] of data) {

                            if (obj[key] !== undefined) {
                                if (!Array.isArray(obj[key])) {
                                    obj[key] = [obj[key]];
                                }
                                obj[key].push(value);
                            } else {
                                obj[key] = value;
                            }

                            if(typeof obj[key] == 'object'){
                                if(obj[key].lastModified){
                                    delete obj[key]
                                }
                            }

                            if(this.files.hasOwnProperty(key)){
                                obj[key] = this.files[key]
                            }

                            if(this.$refs[key+'required']){
                                let required = this.$refs[key+'required'].getAttribute('data-required')

                                required = parseInt(required)
                                if(required && (!obj[key] || (Array.isArray(obj[key]) && obj[key].length == 0))){
                                    Qmsg['warning'](b2_global.js_text.global.cpay_required_fields,{html:true});
                                    return
                                }
                            }
                        }

                        if(!obj.price){
                            Qmsg['warning'](b2_global.js_text.global.cpay_required_fields,{html:true});
                            return
                        }

                        b2DsBox.data = {
                            'title':title,
                            'order_type':'custom',
                            'order_price':obj.price,
                            'post_id':id,
                            'order_key':postid,
                            'order_value':JSON.stringify(obj)
                        }
                        b2DsBox.show = true

                        console.log(b2DsBox.data)

                        return obj;

                    },
                    deleteAc(key,index){
                        if(confirm(b2_global.js_text.circle.remove_file)){
                            this.$delete(this.files[key],index)
                            this.$delete(this.progress[key],index)
                            this.locked[key] = false
                            this.$set(this.count,key,this.count[key] - 1)
                        }
                    },
                    fileExists(mime){
                        let index= mime.lastIndexOf('.');
                        let ext = mime.substr(index+1);
                        return ext.toLowerCase();
                    },
                    readablizeBytes(bytes) {
                        let s = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
                        let e = Math.floor(Math.log(bytes)/Math.log(1024));
                        return (bytes/Math.pow(1024, Math.floor(e))).toFixed(2)+' '+s[e];
                    },
                    fileType(ext){
                        ext = ext.toLowerCase()
                        switch (ext) {
                            case 'jpg':
                            case 'png':
                            case 'gif':
                            case 'jpeg':
                            case 'ico':
                            case 'webp':
                                return 'image'
                            case 'mp3':
                            case 'm4a':
                            case 'ogg':
                            case 'wav':
                                return 'video'
                            case 'mp4':
                            case 'mov':
                            case 'avi':
                            case 'mpg':
                            case 'ogv':
                            case '3gp':
                            case '3g2':
                                return 'audio'
                            default:
                                return 'file'
                        }
                    },
                    fileChange(event,count,key,id){

                        if(event.target.files.length > count){
                            let msg = b2_global.js_text.global.cpay_file_count
                            msg = msg.replace('${count}',count);
                            Qmsg['warning'](msg,{html:true});

                            // event.target.
                            return;
                        }

                        if(event.target.files.length <= 0) return

                        if(!this.files.hasOwnProperty(key)){
                            this.$set(this.files,key,[])
                            this.$set(this.progress,key,[])
                            this.$set(this.locked,key,[])
                            this.$set(this.count,key,0)
                        }

                        if(event.target.files.length > count - this.count[key]){
                            let msg = b2_global.js_text.global.cpay_file_count_less
                            msg = msg.replace('${count}',count - this.count[key]);
                            Qmsg['warning'](msg,{html:true});

                            return;
                        }

                        if(this.locked[key] == true) return

                        let index = parseInt(this.files[key].length)

                        Object.keys(event.target.files).forEach((k)=>{
                            console.log(k)
                            this.$set(this.count,key,this.count[key] + 1)

                            this.locked[key] = true

                            k = parseInt(k)

                            const ext = this.fileExists(event.target.files[k].name)

                            this.$set(this.files[key],k+index,{
                                size:this.readablizeBytes(event.target.files[k].size),
                                name:event.target.files[k].name,
                                ext:ext,
                                type:this.fileType(ext)
                            })

                            this.$set(this.progress[key],k+index,{
                                status:'doing',
                                number:0,
                                msg:''
                            })

                            let formData = new FormData()

                            formData.append('file',event.target.files[k],event.target.files[k].name)
                            formData.append("post_id", id)
                            formData.append("type", 'cpay')

                            let config = {
                                onUploadProgress: progressEvent=>{
                                    this.$set(this.progress[key][k+index],'number',(progressEvent.loaded / progressEvent.total * 100 | 0))
                                }
                            }

                            this.$http.post(b2_rest_url+'fileUpload',formData,config).then(res=>{
                                console.log(res)
                                if(res.data.status == 401){
                                    Qmsg['warning'](res.data.message,{html:true});

                                    this.$set(this.progress[key][k+index],'status','fail')
                                    this.$set(this.progress[key][k+index],'msg',res.data.message)

                                }else{
                                    this.$set(this.files[key][k+index],'url',res.data.url)

                                    this.$set(this.files[key][k+index],'id',res.data.id)

                                    this.$set(this.progress[key][k+index],'status','success')
                                }

                                console.log(this.files)

                                if(this.count[key] >= count){
                                    this.locked[key] = true;
                                }else{
                                    this.locked[key] = false;
                                }
                                event.target.value = ''
                            }).catch(err=>{
                                Qmsg['warning'](err.response.data.message,{html:true});
                                this.locked[key] = false
                                this.$set(this.progress[key][k+index],'status','fail')
                                this.$set(this.progress[key][k+index],'msg',err.response.data.message)
                                event.target.value = ''
                            })
                        })
                    }
                }
            })

        }
    }
}
b2cpay()

function b2fingerprint() {
    var canvas = document.createElement('canvas');
    var ctx = canvas.getContext('2d');
    var txt = 'i9asdm..$#po((^@KbXrww!~cz';
    ctx.textBaseline = "top";
    ctx.font = "16px 'Arial'";
    ctx.textBaseline = "alphabetic";
    ctx.rotate(.05);
    ctx.fillStyle = "#f60";
    ctx.fillRect(125,1,62,20);
    ctx.fillStyle = "#069";
    ctx.fillText(txt, 2, 15);
    ctx.fillStyle = "rgba(102, 200, 0, 0.7)";
    ctx.fillText(txt, 4, 17);
    ctx.shadowBlur=10;
    ctx.shadowColor="blue";
    ctx.fillRect(-20,10,234,5);
    var strng=canvas.toDataURL();

    var hash=0;
    if (strng.length==0) return;
    for (i = 0; i < strng.length; i++) {
        char = strng.charCodeAt(i);
        hash = ((hash<<5)-hash)+char;
        hash = hash & hash;
    }
    return hash;
}

var b2stream = new Vue({
    el:'#b2-stream',
    data:{
        paged:1,
        pages:0,
        author:1,
        data:'',
        empty:false,
        locked:false
    },
    mounted(){
        if(!this.$refs.b2stream) return
        this.paged = this.$refs.b2stream.getAttribute('data-paged')
        this.author = this.$refs.b2stream.getAttribute('data-author')
        this.getList()
    },
    methods:{
        getList(){
            this.$http.post(b2_rest_url+'getStreamList','paged='+this.paged+'&author='+this.author).then(res=>{
                this.data = res.data

                if(this.data.length == 0){
                    this.empty = true
                }
                this.$refs.gujia.style.display = 'none'

                this.$nextTick(()=>{
                    b2SidebarSticky()
                    lazyLoadInstance.update()
                })
            })
        },
        vote(type,id,index){

            if(!b2token){
                login.show = true
            }else{
                if(this.locked == true) return
                this.locked = true

                this.$http.post(b2_rest_url+'postVote','type='+type+'&post_id='+id).then(res=>{

                    this.$set(this.data[index].data.data,'up',parseInt(this.data[index].data.data.up) + parseInt(res.data.up))
                    this.$set(this.data[index].data.data,'down',parseInt(this.data[index].data.data.down) + parseInt(res.data.down))

                    if(res.data.up > 0){
                        this.$set(this.data[index].data.data,'up_isset',1)
                    }else{
                        this.$set(this.data[index].data.data,'up_isset',0)
                    }

                    if(res.data.down > 0){
                        this.$set(this.data[index].data.data,'down_isset',1)
                    }else{
                        this.$set(this.data[index].data.data,'down_isset',0)
                    }

                    this.locked = false
                }).catch(err=>{

                    Qmsg['warning'](err.response.data.message,{html:true});
                    this.locked = false
                })
            }
        }
    }
})

//防抖
function _debounce(fn, delay) {

    var delay = delay || 200;
    var timer;
    return function () {
        var th = this;
        var args = arguments;
        if (timer) {
            clearTimeout(timer);
        }
        timer = setTimeout(function () {
            timer = null;
            fn.apply(th, args);
        }, delay);
    };
}
// 节流
function _throttle(fn, interval) {
    var last;
    var timer;
    var interval = interval || 200;
    return function () {
        var th = this;
        var args = arguments;
        var now = +new Date();
        if (last && now - last < interval) {
            clearTimeout(timer);
            timer = setTimeout(function () {
                last = now;
                fn.apply(th, args);
            }, interval);
        } else {
            last = now;
            fn.apply(th, args);
        }
    }
}

var askWidget = new Vue({
    el:'.b2-widget-ask',
    data:{
        data:'',
        fliter:'last',
        paged:1,
        pages:0,
        count:0,
        cat:0,
        empty:false,
        locked:false,
        prev:true,
        next:false
    },
    mounted(){
        if(!this.$refs.askwidget) return
        this.count = this.$refs.askwidget.getAttribute('data-count')
        this.time = this.$refs.askwidget.getAttribute('data-time')
        const archive = document.querySelector('.ask-archive')

        if(archive){
            this.cat = archive.getAttribute('data-term')
        }

        const single = document.querySelector('.ask-single-top')

        if(single){
            this.cat = single.getAttribute('data-term')
        }

        this.getData()
    },
    watch:{
        fliter(val){
            this.prev = true
            this.next = false
            this.data = ''
            this.paged = 1
            this.empty = false
            this.$refs.askwidget.querySelector('.gujia').style.display = 'block'
            this.getData()
        }
    },
    methods:{
        nexAc(){
            if(this.paged >= this.data.pages) return
            if(this.locked) return
            this.next = true
            this.paged++
            this.getData()
        },
        prevAc(){
            if(this.paged < 1) return
            if(this.locked) return
            this.prev = true
            this.paged--
            this.getData()
        },
        getData(){
            if(this.locked) return
            this.locked = true
            this.$http.post(b2_rest_url+'getAskData','paged='+this.paged+'&type='+this.fliter+'&count='+this.count+'&cat='+this.cat).then(res=>{
                this.locked = false
                if(this.paged == 1){
                    if(res.data.data.length == 0){
                        this.empty = true
                        this.data.data = []
                    }else{
                        this.data = res.data
                    }
                }else{
                    this.data.data = res.data.data
                }
                if(this.data.pages > 1 && this.paged < this.data.pages){
                    this.next = false
                }
                if(this.paged > 1){
                    this.prev = false
                }
                this.$nextTick(()=>{
                    this.$refs.askwidget.querySelector('.gujia').style.display = 'none'
                })

                
            })
        }
    }
})