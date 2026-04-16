var b2infomationTop = new Vue({
    el:'.infomation-top-sidebar',
    data:{
        hotCommentTopics:'',
        cat:''
    },
    mounted(){
        if(this.$refs.b2infomationwidget){
            this.getInfomationHotCommentTopics()
        }
    },
    methods:{
        getInfomationHotCommentTopics(){
            this.$https.post(b2_rest_url+'getInfomationHotCommentTopics','cat='+this.cat).then(res=>{
                console.log(res)
                this.hotCommentTopics = res.data
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});

            })
        }
    }
})

var b2infomation = new Vue({
    el:'.b2-infomation',
    data:{
        data:'',
        locked:false,
        selecter:'infomation-list',
        opt:{
            paged:1,
            count:20,
            pages:1,
            type:'all',
            fliter:'default',
            author:0,
            cat:0,
            s:''
        },
        navType:'p',
        type:'all',
        api:'getInfomationList',
        fliter:'default',
        showFliter:false,
        cat:0,
        authorData:[],
        isAuthor:false
    },
    watch:{
        type(val){
            this.opt.type = val
            this.opt.paged = 1
            this.$refs.infonav.go(this.opt.paged,'comment',true,true)
        },
        fliter(val){
            if(val == 'my'){
                this.opt.author = 0
            }else{
                if(this.isAuthor){
                    this.opt.author = this.$el.getAttribute('data-people')
                }
            }

            this.opt.fliter = val
            this.opt.paged = 1
            this.$refs.infonav.go(this.opt.paged,'comment',true,true)
        }
    },
    mounted(){
        if(this.$el.getAttribute('data-people')){
            this.opt.author = this.$el.getAttribute('data-people')
            this.isAuthor = true
            this.getAuthorInfo()

        }
        
        if(this.$refs.b2infomation){
            this.opt.paged = this.$refs.b2infomation.getAttribute ('data-paged')

            if(this.$refs.b2infomation.getAttribute ('data-term')){
                this.opt.cat = this.$refs.b2infomation.getAttribute ('data-term')
            }

            if(this.$refs.b2infomation.getAttribute ('data-key')){
                this.opt.s = this.$refs.b2infomation.getAttribute ('data-key')
            }

            this.opt.count = this.$refs.b2infomation.getAttribute ('data-count')

            this.$refs.infonav.go(this.opt.paged,'comment',true,true)
        }
    },
    methods:{
        getAuthorInfo(){
            this.$http.post(b2_rest_url+'getAuthorInfo','author_id='+this.opt.author).then(res=>{
                this.authorData = res.data
            })
        },
        dmsg(){
            if(!b2token){
                login.show = true
            }else{
                b2Dmsg.userid = this.opt.author
                b2Dmsg.show = true
            }
        },
        deleteAc(id,index){
            console.log(id)
            if(!confirm(b2_global.js_text.global.delete_post)) return
            this.$http.post(b2_rest_url+'deleteDraftPost','post_id='+id).then(res=>{
                this.$delete(this.data.data,index)
            })
            
        },
        followingAc(){
            if(!b2token){
                login.show = true
            }else{
                this.$http.post(b2_rest_url+'AuthorFollow','user_id='+this.opt.author).then(res=>{
                    this.authorData.followed = !this.authorData.followed
                }).catch(err=>{
                    Qmsg['warning'](err.response.data.message,{html:true});
                })
            }
        },
        fliterAc(val){

            if(this.fliter == val){
                this.fliter = 'default'
                return
            }

            if(!b2token && val == 'my'){
                login.show = true
                return
            }
            this.fliter = val
        },
        get(data){

            if(this.data == ''){
                this.data = data
            }else{
                this.data.data = data.data
            }
            this.opt.pages = data.pages

            this.locked = false

            this.$refs.gujia.style.display = 'none'
            this.$nextTick(()=>{
                if(data.data.length > 0){
                    timeago.render(this.$refs.b2infomation.querySelectorAll('.b2timeago'), 'zh_CN')
                }
                
            })
        }
    }
})

var b2infomationSingle = new Vue({
    el:'.infomation-meta',
    data:{
        id:'',
        data:''
    },
    mounted(){
        if(!this.$refs.infomationmeta) return
        this.id = this.$refs.infomationmeta.getAttribute('data-id')
        this.getSingleData()
    },
    methods:{
        getSingleData(){
            this.$https.post(b2_rest_url+'getInfomationSingle','id='+this.id).then(res=>{
                this.data = res.data
                console.log(this.data)
                if(this.data.sticky != 0){
                    infometa.sticky = 1
                }
                if(this.data.post_status == 'pending'){
                    document.querySelector('#b2-post-status').style.display = 'block'
                }
                this.$nextTick(()=>{
                    b2tooltip('.b2tips')
                    lazyLoadInstance.update()
                })
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});

            })
        }
    }
})

var infometa = new Vue({
    el:'#infomation-meta-header',
    data:{
        sticky:0
    }
})

var b2poinfomation = new Vue({
    el:'#po-infomation',
    data:{
        showImageBox:false,
        showVideoBox:false,
        locked:{
            next:false,
            per:false
        },
        imageList:[],
        imgTable:'upload',
        imagePages:'',
        videoList:[],
        videoTable:'upload',
        videoPages:'',
        videoPicked:[],
        imagePicked:[],
        thumbPicked:false,
        thumb:'',
        paged:1,
        type:'for',
        opts:'',
        sticky:'',
        attrs:[],
        meta:{
            sticky:'',
            price:'',
            passtime:'',
            contact:{
                type:'',
                number:''
            },
            attrs:[
                {
                    key:'',
                    value:''
                }
            ]
        },
        title:'',
        content:'',
        submitLocked:false,
        id:0
    },
    mounted(){
        if(!this.$refs.poinfomation) return
        if(!b2token){
            login.show = true
        }

        this.id = parseInt(this.$refs.poinfomation.getAttribute('data-id'))
        
        this.initEditor()
        this.getPoinfomationOpts()
        autosize(this.$refs.writeTitle);

    },
    methods:{
        getEditData(editor){
            this.$https.post(b2_rest_url+'editInfomationData','id='+this.id).then(res=>{
                if(res.data.meta.attrs.length > 0 && res.data.meta.attrs[0]['key']){
                    this.attrs.push('attrs')
                }
                if(res.data.meta.contact.number){
                    this.attrs.push('contact')
                }
                // if(res.data.sticky_days){
                //     this.attrs.push('sticky')
                //     this.sticky = res.data.sticky_days
                // }
                this.content = res.data.content
                this.type = res.data.type
                this.title = res.data.title
                this.meta = res.data.meta
                this.$nextTick(()=>{
                    autosize.update(this.$refs.writeTitle);
                    editor.setContent(this.content);
                })
                
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        totalPay(){
            if(this.meta.sticky && this.opts.sticky_pay){
                return Calc.Mul(this.opts.sticky_pay,this.meta.sticky)
            }else{
                return ''
            }
        },
        getPoinfomationOpts(){
            this.$https.post(b2_rest_url+'getPoinfomationOpts').then(res=>{

                let stickys = res.data.sticky
                this.opts = res.data

                for (let index = 0; index < this.opts.allow_opts.length; index++) {
                    if(this.opts.allow_opts.indexOf('price') != -1){
                        this.attrs.push('price')
                    }

                    if(this.opts.allow_opts.indexOf('passtime') != -1){
                        this.attrs.push('passtime')
                    }
                    
                }

                if(stickys.length > 0 && this.id == 0){
                    for (let i = 0; i < stickys.length; i++) {

                        if(stickys[i]['used'] === false && !stickys[i]['post_id']){
                            
                            this.sticky = stickys[i]
                            if(this.attrs.indexOf('sticky') == -1){
                                this.attrs.push('sticky')
                            }
                            break;
                        }
                    }
                }

                if(this.id != 0){
                    for (let i = 0; i < stickys.length; i++) {
                       
                        if(stickys[i]['post_id'] == this.id){
                            
                            this.sticky = stickys[i]
                            if(this.attrs.indexOf('sticky') == -1){
                                this.attrs.push('sticky')
                            }
                            break
                        }else if(stickys[i]['used'] === false){
                            this.sticky = stickys[i]
                            if(this.attrs.indexOf('sticky') == -1){
                                this.attrs.push('sticky')
                            }
                            break
                        }
                    }
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        initEditor(){
            tinymce.init({
                selector: '#info-edit-content',
                body_class:'entry-content',
                content_css:b2_global.site_info.site_uri+'/Assets/fontend/style.css',
                icons: 'b2',
                content_style: "body {padding: 10px}",
                body_style: '.entry-content {margin:16px}',
                language:b2_global.language,
                base_url: b2_global.site_info.site_uri+'/Assets/fontend/library/tinymce',
                suffix: '.min',
                statusbar: false,
                link_context_toolbar: true,
                contextmenu: false,
                menubar:false,
                min_height:400,
                mobile: {
                    toolbar_sticky:true,
                  },
                toolbar_sticky:true,
                relative_urls: false,
                remove_script_host: false,
                default_link_target: "_blank",
                image_advtab:true,
                plugins: 'code,lists,advlist,autoresize,codesample,link,hr,fullscreen,image,paste,b2preview',
                toolbar1:'bold italic forecolor backcolor | heading blockquote codesample numlist bullist | link b2imagelibrary b2video hr | insertShotCode | removeformat | fullscreen',
                image_caption: true,
                content_style: 'img {max-width: 100%;}',
                oninit : "setPlainText",
                paste_auto_cleanup_on_paste : true,
                paste_remove_styles: true,
                paste_remove_styles_if_webkit: true,
                paste_strip_class_attributes: true,
                setup: (editor)=>{
        
                  //图片按钮
                  editor.ui.registry.addIcon('b2-image-library', '<svg class="Zi Zi--InsertImage" width="24" height="24"><path d="M21 17.4c0 .9-.9 1.6-2 1.6H5c-1.1 0-2-.7-2-1.6V6.6C3 5.7 3.9 5 5 5h14c1.1 0 2 .7 2 1.6v10.8zm-9.4-3.9a.5.5 0 01-.9 0l-1.3-2a.5.5 0 00-.8 0l-2.2 3.8a.5.5 0 00.5.7h10.3a.5.5 0 00.4-.7l-3-5.5a.5.5 0 00-.9 0l-2.1 3.7z" fill-rule="evenodd"/></svg>');
                  editor.ui.registry.addButton('b2imagelibrary', {
                      tooltip: "Insert image",
                      icon: 'b2-image-library',
                      onAction: this.showImageBoxAc
                  });
        
                  //视频按钮按钮
                  editor.ui.registry.addIcon('b2-video', '<svg class="Zi Zi--InsertVideo" width="24" height="24"><path d="M10.5 15c-.4.3-.8 0-.8-.5v-5c0-.6.4-.8.8-.5l4.3 2.5c.5.3.5.7 0 1L10.5 15zM5 5S3 5 3 7v10s0 2 2 2h14c2 0 2-2 2-2V7c0-2-2-2-2-2H5z" fill-rule="evenodd"/></svg>');
                  editor.ui.registry.addButton('b2video', {
                      tooltip: "Insert video",
                      icon: 'b2-video',
                      onAction: this.showVideoBoxAc
                  });
                  
                },
                init_instance_callback:(editor)=>{
                    if(this.id != 0){
                        this.getCountent(editor)
                    }
                }
              });
        },
        getCountent(editor){
            this.getEditData(editor)
        },
        next(type){
            if(type === 'image'){
                if(this.paged >= this.imagePages){
                    this.locked.next = true
                    return
                }
            }else{
                if(this.paged >= this.videoPages){
                    this.locked.next = true
                    return
                }
            }
            
            this.paged++
            this.getAttachments(type)
        },
        per(type){
            if(this.paged == 1){
                this.locked.per = true
                return
            }
            this.paged--
            this.getAttachments(type)
        },
        close(type){
            if(type == 'image'){
                this.showImageBox = false
            }
            if(type === 'video'){
                this.showVideoBox = false
            }
        },
        addAttr(){
            this.meta.attrs.push({key:'',value:''})
        },
        subAttr(index){
            this.$delete(this.meta.attrs,index)
        },
        showImageBoxAc(){
            this.showImageBox = true
            this.getAttachments('image')
        },
        showVideoBoxAc(){
            this.showVideoBox = true
            this.getAttachments('video')
        },
        getAttachments(type){
            if(this.locked.next || this.locked.per ) return
            this.locked.next = true
            this.locked.per = true
            this.$http.post(b2_rest_url+'getCurrentUserAttachments','paged='+this.paged+'&type='+type).then(res=>{
                if(type === 'image'){
                    this.imageList = res.data.data
                    this.imagePages = res.data.pages
                }else{
                    this.videoList = res.data.data
                    this.videoPages = res.data.pages
                }
                this.locked.next = false
                this.locked.per = false
            })
        },
        videoplay(event,type){
            if(type === 'play'){
                event.target.play()
            }else{
                event.target.pause()
            } 
        },
        submit(){

            if(this.submitLocked == true) return
            this.submitLocked = true

            if(this.attrs.indexOf('sticky') != -1){
                if(!this.sticky.days){
                    Qmsg['warning'](b2_global.js_text.global.infomation_sticky_pay,{html:true});
                    this.submitLocked = false
                    return
                }
            }

            let cat = document.querySelector('#infomation-cat').value
            if(cat < 0){
                Qmsg['warning'](b2_global.js_text.global.infomation_po_cat,{html:true});
                this.submitLocked = false
                return
            }

            let data = {
                type:this.type,
                title:this.title,
                content:tinymce.activeEditor.getContent(),
                metas:this.meta,
                attrs:this.attrs,
                cat:cat
            }

            if(this.id != 0){
                data['post_id'] = this.id
            }

            this.$http.post(b2_rest_url+'insertInfomation',Qs.stringify(data)).then(res=>{
                
                this.submitLocked = false
                location.href = res.data

            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.submitLocked = false
            })
        },
        fileUpload(event,filetype){
            if(event.target.files.length <= 0) return
            if(this._locked == true) return
            this._locked = true
            
            if(filetype === 'image'){
                for (let i = 0; i < event.target.files.length; i++) {
                    let file = event.target.files[i]
                    let url = URL.createObjectURL(file)
                    this.imageList.splice(i,0,{
                        'id':'',
                        'att_url':'',
                        'thumb':'',
                        'file':file
                    })
                }
                this.imgTable = 'lib'
                for (let i = 0; i < this.imageList.length; i++) {
                    if(this.imageList[i].file){
                        let formData = new FormData()
                        formData.append('file',event.target.files[i],event.target.files[i].name)
                        formData.append("post_id", 1)
                        formData.append("type", 'post')
                        this.$http.post(b2_rest_url+'fileUpload',formData).then(res=>{
                            this.imageList[i].att_url = res.data.url
                            this.imageList[i].id = res.data.id
                            this.imageList[i].progress = 0
                            this.imageList[i].file = ''
                            this.imageList[i].thumb = res.data.url
                            this.picked('image',res.data.url)
                            this._locked = false
    
                        }).catch(err=>{

                            Qmsg['warning'](err.response.data.message,{html:true});
                            this._locked = false
                        })
                    }
                }
            }else if(filetype === 'video'){
                for (let i = 0; i < event.target.files.length; i++) {
                    let file = event.target.files[i]
                    this.videoList.splice(i,0,{
                        'id':'',
                        'att_url':'',
                        'thumb':'',
                        'file':file
                    })
                }

                this.videoTable = 'lib'
                for (let i = 0; i < this.videoList.length; i++) {
                    if(this.videoList[i].file){
                        let formData = new FormData()
                        formData.append('file',event.target.files[i],event.target.files[i].name)
                        formData.append("post_id", 1)
                        formData.append("filetype", 'video')
                        formData.append("type", 'post')
                        this.$http.post(b2_rest_url+'fileUpload',formData).then(res=>{
                            this.videoList[i].att_url = res.data.url
                            this.videoList[i].id = res.data.id
                            this.videoList[i].progress = 0
                            this.videoList[i].file = ''
                            this.videoList[i].thumb = res.data.url
                            this.picked('image',res.data.url)
                            this._locked = false

                        }).catch(err=>{

                            Qmsg['warning'](err.response.data.message,{html:true});
                            this._locked = false
                        })
                    }
                }
            }
        },
        pay(){
            b2DsBox.data = {
                'title':b2_global.js_text.global.infomation_sticky_pay_title,
                'order_type':'infomation_sticky',
                'order_price':this.totalPay(),
                'post_id':0
            }
            b2DsBox.show = true
        },
        picked(type,src){
            console.log(src)
            if(type == 'image'){

                if(this.thumbPicked){
                    this.thumb = src
                }else{
                    if(this.imagePicked.indexOf(src) !== -1){
                        this.imagePicked.splice(this.imagePicked.findIndex(item => item === src), 1)
                    }else{
                        this.imagePicked.push(src)
                    }
                }  
            }

            if(type == 'video'){
                if(this.videoPicked.indexOf(src) !== -1){
                    this.videoPicked.splice(this.videoPicked.findIndex(item => item === src), 1)
                }else{
                    this.videoPicked.push(src)
                }
            }
        },
        insert(type){
            let html = '';
            if(type == 'image'){
                
                if(this.imgTable == 'link'){
                    let src = document.querySelector('#imageLink').value
                    src = src.split("\n")
                    if(src.length > 0){
                      for (let i = 0; i < src.length; i++) {
                        html += '<p><img src="'+src[i]+'" /></p>';
                      }
                    }
                }else{
                    for (let i = 0; i < this.imagePicked.length; i++) {
                        html += '<p><img src="'+this.imagePicked[i]+'" /></p>';
                    }
                }

                if(html){
                    this.imagePicked = []
                    this.showImageBox = false
                }
            }

            if(type === 'video'){
                if(this.videoTable === 'lib'){
                    if(this.videoPicked.length > 0){
                      for (let i = 0; i < this.videoPicked.length; i++) {
                        html += '<p>[b2player src="'+this.videoPicked[i]+'" poster=""]</p>';
                      }
                    }
                }

                if(this.videoTable === 'link'){
                    html = '<p>[b2player src="'+document.querySelector('#videoLink').value+'" poster="'+document.querySelector('#videoThumb').value+'"]</p>'
                }

                if(this.videoTable === 'html'){
                    html = document.querySelector('#videoHtml').value
                }

                if(html){
                    this.videoPicked = []
                    this.showVideoBox = false
                }
            }

            if(html){
                console.log(html)
                tinymce.activeEditor.insertContent(html);
            }
            return
        },
    }
})
