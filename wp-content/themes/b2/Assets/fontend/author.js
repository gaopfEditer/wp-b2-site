var b2Author = new Vue({
    el:'.author-header',
    data:{
        userId:b2_author.author_id,
        admin:false,
        self:false,
        locked:false,
        progress:0,
        cover:'',
        avatar:'',
        toast:'',
        followed:false
    },
    mounted(){
        //if(!this.$refs.fileInput) return
        //获取用户数据
        this.$http.post(b2_rest_url+'getAuthorInfo','author_id='+this.userId).then(res=>{
            this.cover = res.data.cover
            this.avatar = res.data.avatar
            this.admin = res.data.admin
            this.self = res.data.self
            this.followed = res.data.followed
            this.$store.commit('setauthorData', res.data)
        })
    },
    methods:{
        getFile(event,type){
            if(event.target.files.length <= 0) return
            if(this.locked == true) return
            this.locked = true
            this.progress = 0
            let file = event.target.files[0]

            let formData = new FormData()

            formData.append('file',file,file.name)
            formData.append("post_id", 1)
            formData.append("type", type)

            let config = {
                onUploadProgress: progressEvent=>{
                    this.progress = progressEvent.loaded / progressEvent.total * 100 | 0
                }
            }

            this.toast = Qmsg['loading']('Loading...('+this.progress+'%)');
        
            this.$http.post(b2_rest_url+'fileUpload',formData,config).then(res=>{
                if(res.data.status == 401){
                    Qmsg['warning'](res.data.message,{html:true});
                    this.progress = 0
                }

                if(type == 'cover'){
                    this.cover = res.data.url
                    this.saveCover(this.cover,res.data.id)
                }

                if(type == 'avatar'){
                    this.avatar = res.data.url
                    this.saveAvatar(this.avatar,res.data.id)
                }

                this.$refs.fileInput.value = null
                this.locked = false;
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
                this.progress = 0
                this.$refs.fileInput.value = null
                this.toast.close()
            })
        },
        saveCover(url,id){
            this.$http.post(b2_rest_url+'saveCover','url='+url+'&id='+id+'&user_id='+this.userId).then(res=>{
                this.toast.close()
            })
        },
        saveAvatar(url,id){
            this.$http.post(b2_rest_url+'saveAvatar','url='+url+'&id='+id+'&user_id='+this.userId).then(res=>{
                let userData = this.$store.state.userData;
                if(this.self){
                    userData['avatar'] = res.data.avatar
                    userData['avatar_webp'] = res.data.avatar_webp
                    this.$store.commit('setUserData', userData)

                    this.toast.close()
                }
            })
        },
        followingAc(){
            if(!b2token){
                login.show = true
            }else{
                this.$http.post(b2_rest_url+'AuthorFollow','user_id='+this.userId).then(res=>{
                    this.followed = !this.followed
                }).catch(err=>{
                    Qmsg['warning'](err.response.data.message,{html:true});
                })
            }
        },
        dmsg(){
            if(!b2token){
                login.show = true
            }else{
                b2Dmsg.userid = this.userId
                b2Dmsg.show = true
            }
        }
    },
    watch:{
        progress(val){
            this.toast.$elem.firstChild.lastElementChild.innerText = 'Loading...('+val+'%)';
        }
    }
})

var b2AuthorPost = new Vue({
    el:'#author-post-list',
    data:{
        selecter:'#post-list > .b2_gap',
        api:'getPostList',
        pages:0,
        count:0,
        options:[],
        loading:true,
        empty:false
    },
    mounted(){
        if(this.$refs.AuthorSettings){
            this.options = JSON.parse(this.$refs.AuthorSettings.getAttribute('data-settings'));
            this.getPost()
        }
    },
    methods:{
        getPost(){
            this.$http.post(b2_rest_url+'getPostList',Qs.stringify(this.options)).then(res=>{
                if(res.data.data){
                    this.pages = res.data.pages
                    this.count = res.data.count
                    document.querySelector('#post-list > .b2_gap').innerHTML = res.data.data
                    lazyLoadInstance.update()
                    this.empty = false
                }else{
                    this.empty = true
                }
                this.loading = false
            })
        },
        delete(id){
            if(!confirm(b2_global.js_text.global.delete_post)) return
            this.$http.post(b2_rest_url+'deleteDraftPost','post_id='+id).then(res=>{
                document.querySelector('#item-'+id).remove()
            })
        }
    }
})

var b2AuthorNewsflashes = new Vue({
    el:'#author-newsflashes',
    data:{
        selecter:'.author-comment-list ul',
        api:'getNewsflashesList',
        options:[],
        list:'',
        pages:0,
        loading:true,
        empty:false
    },
    mounted(){
        if(this.$refs.authornewsflasheslist){
            console.log(1)
            this.options.user_id = b2_author.author_id
            this.options.term = 0
            this.options.post_paged = parseInt(this.$refs.authornewsflasheslist.getAttribute('data-paged'))
            this.$refs.commentPageNav.go(this.options.post_paged,'comment',true)
        }
    },
    methods:{
        get(data){
            this.list = data.data
            if(this.list.length == 0) this.empty = true
            this.pages = data.pages
            this.loading = false
        }
    }
})

var b2AuthorComment = new Vue({
    el:'.author-comment',
    data:{
        selecter:'.author-comment-list ul',
        api:'getAuthorComments',
        options:[]
    },
    mounted(){
        if(this.$refs.authorCommentSettings){
            this.options = JSON.parse(this.$refs.authorCommentSettings.getAttribute('data-settings'));
        }
    },
    methods:{

    }
})

var b2AuthorFollow = new Vue({
    el:'#author-following',
    data:{
        api:'getAuthorFollowing',
        options:[],
        pages:0
    },
    mounted(){
        if(this.$refs.authorFollow){
            this.options.user_id = b2_author.author_id
            this.options.number = 15
            this.options.post_paged = parseInt(this.$refs.authorFollow.getAttribute('data-paged'))
            this.pages = parseInt(this.$refs.authorFollow.getAttribute('data-pages'))
            this.$refs.commentPageNav.go(this.options.post_paged,'comment',true)
        }
    },
    methods:{
        followCancel(even,user_id){
            let msg = confirm(b2_global.alert_following)
            if(!msg) return
            this.$http.post(b2_rest_url+'AuthorFollow','user_id='+user_id).then(res=>{
                even.target.parentNode.parentNode.remove()
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        }
    }
})

var b2AuthorFollowers = new Vue({
    el:'#author-followers',
    data:{
        api:'getAuthorFollowers',
        options:[],
        pages:0
    },
    mounted(){
        if(this.$refs.authorFollow){
            this.options.user_id = b2_author.author_id
            this.options.number = 15
            this.options.post_paged = parseInt(this.$refs.authorFollow.getAttribute('data-paged'))
            this.pages = parseInt(this.$refs.authorFollow.getAttribute('data-pages'))
            this.$refs.commentPageNav.go(this.options.post_paged,'comment',true)
        }
    },
    methods:{
        following(even,user_id){
            
            this.$http.post(b2_rest_url+'AuthorFollow','user_id='+user_id).then(res=>{

                if(!res.data){
                    even.target.innerHTML = b2_global.nofollowed
                    even.target.className = even.target.className.replace('empty','')
                }else{
                    if(even.target.tagName == 'I'){
                        even.target.parentNode.innerHTML = b2_global.followed
                        even.target.parentNode.className += 'empty'
                    }else{
                        even.target.innerHTML = b2_global.followed
                        even.target.className += 'empty'
                    }
                    
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        }
    }
})

var b2AuthorCollections = new Vue({
    el:'#author-collections',
    data:{
        api:'getUserFavoritesList',
        options:[],
        locked:false,
        pages:0,
    },
    mounted(){
        if(this.$refs.authorFollow){
            this.options.user_id = b2_author.author_id
            this.options.number = 15
            this.options.sub = this.$refs.authorFollow.getAttribute('data-sub')
            this.options.post_paged = parseInt(this.$refs.authorFollow.getAttribute('data-paged'))
            this.pages = parseInt(this.$refs.authorFollow.getAttribute('data-pages'))
            this.$refs.commentPageNav.go(this.options.post_paged,'comment',true)
        }
    },
    methods:{
        userFavorites(even,id){
            if(!b2token){
                login.show = true
            }else{
                let msg = confirm(b2_global.alert_favorites)
                if(!msg) return
                if(this.locked == true) return
                this.locked = true

                this.$http.post(b2_rest_url+'userFavorites','post_id='+id).then(res=>{
                    if(res.data == false){
                        even.target.parentNode.parentNode.remove()
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

var b2AuthorInv = new Vue({
    el:'#user-inv-page',
    data:{
        invList:false,
        none:false
    },
    mounted(){
        if(this.$refs.invpage){
            this.$http.post(b2_rest_url+'getUserInvList','user_id='+b2_author.author_id).then(res=>{
                if(res.data.length != 0){
                    this.invList = res.data
                }else{
                    this.none = true
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        }
    },
    methods:{

    }
})

var b2AuthorSidebar = new Vue({
    el:'#author-index',
    computed:{
        userData(){
            return this.$store.state.authorData
        }
    }
})

var b2AuthorPageleft = new Vue({
    el:'.author-page-right',
    computed:{
        userData(){
            return this.$store.state.authorData
        }
    }
})

var b2AuthorEdit = new Vue({
    el:'#author-edit-page',
    data:{
        userData:{
            open:{
                qq:{
                    open:false
                },
                weibo:{
                    open:false
                },
                weixin:{
                    open:false
                }
            }
        },
        addresses:{
            'address':'',
            'name':'',
            'phone':'',
            'province':'',
            'city':'',
            'county':''
        },
        show:{
            nickname:false,
            sex:false,
            url:false,
            desc:false,
            address:false,
            phone:false,
            email:false,
            password:false
        },
        avatarType:'',
        locked:false,
        checkType:'',
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
        count:60,
        SMSLocked:false
    },
    mounted(){
        if(this.$refs.authorEdit){
            this.$http.post(b2_rest_url+'getAuthorSettings','user_id='+b2_author.author_id).then(res=>{
                this.userData = res.data
                this.avatarType = res.data.open['default'].avatar_set
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        }
    },
    methods:{
        changeAvatar(type){
            if(this.locked) return 
            this.locked = true
            this.avatarType = type
            this.$http.post(b2_rest_url+'changeAvatar','type='+type+'&user_id='+b2_author.author_id).then(res=>{
                
                if(res.data.avatar && b2Author.self){

                    let userData = this.$store.state.userData;
                    
                    userData['avatar'] = res.data.avatar
                    userData['avatar_webp'] = res.data.avatar_webp
                    this.$store.commit('setUserData', userData)

                    b2Author.avatar = res.data.avatar
                }
                this.locked = false
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        getFile(event,type){
            if(event.target.files.length <= 0) return
            if(this.locked == true) return
            this.locked = true
            let file = event.target.files[0]

            let formData = new FormData()

            formData.append('file',file,file.name)
            formData.append("post_id", 1)
            formData.append("type", 'qrcode')

            this.$http.post(b2_rest_url+'fileUpload',formData).then(res=>{
                this.saveQrcode(type,res.data.id,res.data.url)
                this.$refs[type].value = null
                this.locked = false
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.$refs[type].value = null
                this.locked = false
            })
            
        },
        saveQrcode(type,id,url){
            this.$http.post(b2_rest_url+'saveQrcode','type='+type+'&id='+id+'&url='+url+'&user_id='+b2_author.author_id).then(res=>{
                console.log(res);
                if(type == 'weixin'){
                    this.userData.qrcode_weixin = res.data
                }else{
                    this.userData.qrcode_alipay = res.data
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        markHistory(type){
            if((this.userData.open.weixin.mp || this.userData.open.weixin.pc_open) && type === 'weixin'){
                this.$store.commit('setOauthLink',this.userData.open)
                mpCode.show = true
            }
            b2setCookie('b2_back_url',window.location.href)
        },
        unBuild(type){
            if(this.locked == true) return
            this.locked = true
            this.$http.post(b2_rest_url+'unBuild','type='+type+'&user_id='+b2_author.author_id).then(res=>{
                this.userData = res.data
                this.avatarType = res.data.open['default'].avatar_set
                this.locked = false
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        saveNickName(){
            this.$http.post(b2_rest_url+'saveNickName','name='+this.userData.display_name+'&user_id='+b2_author.author_id).then(res=>{
                if(res.data == true){
                    let userData = this.$store.state.userData;
                    if(userData['name']){
                        userData['name'] = this.userData.display_name
                        this.$store.commit('setUserData', userData)
                    }
                    document.querySelector('#userDisplayName').innerText = this.userData.display_name
                    this.show.nickname = false
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        saveSex(){
            this.$http.post(b2_rest_url+'saveSex','sex='+this.userData.sex+'&user_id='+b2_author.author_id).then(res=>{
                if(res.data == true){
                    this.show.sex = false
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        saveUrl(){
            this.$http.post(b2_rest_url+'saveUrl','url='+this.userData.url+'&user_id='+b2_author.author_id).then(res=>{
                if(res.data == true){
                    this.show.url = false
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        saveDesc(){
            this.$http.post(b2_rest_url+'saveDesc','desc='+this.userData.desc+'&user_id='+b2_author.author_id).then(res=>{
                if(res.data == true){
                    this.show.desc = false
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        saveAddress(){
            let key = uuid(8, 16);
            this.$http.post(b2_rest_url+'saveAddress','address='+this.addresses.address+'&name='+this.addresses.name+'&phone='+this.addresses.phone+'&user_id='+b2_author.author_id+'&key='+key).then(res=>{
                if(res.data){
                    this.userData.address = res.data.address
                    this.userData.default_address = res.data.key
                    this.show.address = false
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        saveDefaultAddress(key){
            this.$http.post(b2_rest_url+'saveDefaultAddress','key='+key+'&user_id='+b2_author.author_id).then(res=>{
                if(res.data){
                    this.userData.default_address = res.data
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        addressLength(){
            return Object.keys(this.userData.address).length;
        },
        deleteAddress(key){
            this.$http.post(b2_rest_url+'deleteAddress','key='+key+'&user_id='+b2_author.author_id).then(res=>{
                if(res.data){
                    this.userData.default_address = res.data.default
                    this.userData.address = res.data.address;
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        saveUsername(){
            this.data.user_id = b2_author.author_id
            this.$http.post(b2_rest_url+'saveUsername',Qs.stringify(this.data)).then(res=>{
                if(res.data){
                   this.show.phone = false
                   this.show.email = false
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        //修改手机和邮箱
        checkCode(type){
            recaptcha.type = 'edit'
            recaptcha.show = true
            login.$refs.loginBox.type = 'edit'
            this.checkType = type
        },
        imgCodeAc(arg){
            this.data.img_code = arg.value
            this.data.token = arg.token
            this.sendSMS()
        },
        sendCode(token){
            this.data.img_code = token
            this.sendSMS()
        },
        sendSMS(){
            if(this.SMSLocked == true) return
            this.SMSLocked = true
            if(this.checkType == 'phone'){
                this.data.username = this.userData.phone
            }else{
                this.data.username = this.userData.email
            }
            
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
        //修改密码
        editPass(){
            this.$http.post(b2_rest_url+'editPass','password='+this.userData.password+'&repassword='+this.userData.repassword+'&user_id='+b2_author.author_id).then(res=>{
                if(res.data){
                   this.show.password = false
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        }
    }
})

var b2orders = new Vue({
    el:'#myorders',
    data:{
        list:'',
        paged:1,
        options:[],
        pages:0,
        api:'getMyOrders',
        show:false,
        express:'',
        id:0
    },
    mounted(){
        if(this.$refs.myorders){
            this.paged = parseInt(this.$refs.myorders.getAttribute('data-paged'))
            this.options.paged = this.paged
            this.options.user_id = b2_author.author_id
            this.$refs.commentPageNav.go(this.options.paged,'comment',true)
        }
    },
    methods:{
        get(data){
            this.list = data.data
            this.pages = data.pages            
        },
        getExpressInfo(id,ex_id,address,com){
            this.show = true
            if(this.express === '') this.express = []
            if(this.express[id]) return
            this.$http.post(b2_rest_url+'getOrderExpress','com='+com+'&id='+ex_id+'&address='+address).then(res=>{
                if(res.data){
                    this.id = id
                    this.$set(this.express,id,res.data.showapi_res_body)
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        userChangeOrderState(order_id,index){
            this.$http.post(b2_rest_url+'userChangeOrderState','order_id='+order_id).then(res=>{
                if(res.data == 'success'){
                    this.$set(this.list[index],'_order_state','q')
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        }
    }
})