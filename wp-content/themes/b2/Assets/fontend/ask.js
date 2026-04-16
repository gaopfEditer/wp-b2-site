var poAsk = new Vue({
    el:'.ask-page',
    data:{
        imageList:[],
        imgTable:'upload',
        imagePages:'',
        videoList:[],
        videoTable:'upload',
        videoPages:'',
        videoPicked:[],
        imagePicked:[],
        type:'everyone',
        userInput:'',
        ask:{
            locked:false,
            userList:[],
            focus:false,
            pickedList:[],
            picked:false,
            type:'someone',
            reward:'credit',
            time:'',
            pay:'',
            userCount:4,
            hiddenInput:false,
            empty:false
        },
        showModal:false,
        reward:0,
        rewardType:'credit',
        passTime:0,
        locked:false,
        showImageBox:false,
        showVideoBox:false,
        showPostBox:false,
        showFileBox:false,
        thumbPicked:false,
        thumb:'',
        paged:1,
        _locked:false,
        id:0,
        passtime:'',
        cats:{},
        money:'',
        answerLocked:false,
        editor:'',
        answer_id:0
    },
    mounted(){
        if(!this.$refs.poask) return
        this.initEditor()

        if(!this.$refs.catpicked) return
        this.catPicked()
        autosize(this.$refs.title)
    },
    computed:{
        userData(){
            return this.$store.state.userData;
        },
        userRole(){
            return this.$store.state.userRole;
        }
    },
    watch:{
        userInput(val){
            if(val === ''){
                this.ask.empty = false
                this.ask.userList = []
            }
        }
    },
    methods:{
        dmsg(){
            b2Dmsg.userid = 1
            b2Dmsg.show = true
        },
        catPicked(){
            const select = document.querySelector('.register-cat #parent');
            select.addEventListener('change', (event)=>{
                if(event.target.selectedIndex == 0) return
                if(Object.keys(this.cats).length >= 3) {
                    Qmsg['warning'](b2_global.js_text.global.max_ask_cat,{html:true});
                    return
                }
                this.$set(this.cats,event.target.value,event.target.options[event.target.selectedIndex].innerText)
            })

        },
        removeCat(key){
            this.$delete(this.cats,key)
        },  
        pickedUser(id,name,avatar){

            //检查是否添加过
            for (let i = 0; i < this.ask.pickedList.length; i++) {
                if(this.ask.pickedList[i].id === id){
                    Qmsg['warning'](b2_global.js_text.circle.repeat_id,{html:true});
                    return
                }
            }
            this.ask.pickedList.push({'id':id,'name':name,'avatar':avatar})
            this.ask.focus = false
            this.ask.picked = true
            this.userInput = ''
            this.ask.userList = []

            this.showModal = false

            if(this.ask.pickedList.length >= this.ask.userCount){
                this.ask.hiddenInput = true
                return
            }else{
                this.ask.hiddenInput = false
            }

            this.$nextTick(()=>{
                lazyLoadInstance.update()
            })
        },
        removePickedUser(index){
            this.$delete(this.ask.pickedList,index)
            if(this.ask.pickedList.length >= this.ask.userCount){
                this.ask.hiddenInput = true
                return
            }else{
                this.ask.hiddenInput = false
            }
        },
        searchUser(){
            if(this.userInput === '') return
            if(this.ask.locked == true) return
            this.ask.locked = true
            
            this.$http.post(b2_rest_url+'searchUsers','nickname='+this.userInput).then(res=>{
                if(res.data.length > 0){
                    this.ask.userList = res.data
                }else{
                    this.ask.userList = []
                }
                this.$nextTick(()=>{
                    lazyLoadInstance.update()
                })
                this.ask.locked = false
                this.ask.empty = false
            }).catch(err=>{
                this.ask.empty = true
                this.ask.locked = false
                this.ask.userList = []
            })
        },
        initEditor(){
            if(!b2token){
                login.show = true
            }
            tinymce.init({
                selector: '#ask-edit-content',
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
                mobile: {
                    toolbar_sticky:true,
                  },
                min_height:400,
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
                    this.id = b2GetQueryVariable('id')
                    console.log(this.id)
                    
                    if(this.id){
                        this.getCountent(editor)
                    }
                }
            });
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
        close(type){
            if(type == 'image'){
              this.showImageBox = false
            }
            if(type === 'video'){
              this.showVideoBox = false
            }
            if(type === 'post'){
              this.showPostBox = false
            }
            if(type === 'file'){
              this.showFileBox = false
            }
            this.paged = 1
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
        getCountent(editor){
            this.$http.post(b2_rest_url+'getAskEditData','id='+this.id).then(res=>{
                this.$refs.title.value = res.data.title
                this.ask.pickedList = res.data.inv
                if(res.data.tags.length != 0){
                    this.cats = res.data.tags
                }
                this.reward = res.data.reward
                this.rewardType = res.data.rewardType
                this.passtime = res.data.passtime 
                this.money = res.data.money
                this.$nextTick(()=>{
                    autosize.update(this.$refs.title);
                    editor.setContent(res.data.content);
                })
                
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this._locked = false
            })
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
            if(this.locked == true) return
            this.locked = true
            let content = tinymce.activeEditor.getContent();

            let data = {
                title:this.$refs.title.value,
                content:content,
                cats:this.cats,
                post_id:this.id,
                rewardType:this.rewardType,
                reward:this.reward,
                passtime:this.passtime,
                inv:this.ask.pickedList,
                money:this.money
            }

            this.$http.post(b2_rest_url+'poAsk',Qs.stringify(data)).then(res=>{
                
                location.href = res.data

                this.locked = false
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        submitAnswer(){
            if(this.answerLocked) return
            this.answerLocked = true

            let data = {
                content:tinymce.activeEditor.getContent(),
                parent_id:this.$refs.poask.getAttribute('data-id'),
                post_id:this.answer_id
            }
            
            this.$http.post(b2_rest_url+'poAskAnswer',Qs.stringify(data)).then(res=>{
                if(askAnswerList.data.data.length > 0){
                    this.$set(askAnswerList.data.data[0],'new',false)
                    for (let i = 0; i < askAnswerList.data.data.length; i++) {
                        if(askAnswerList.data.data[i].post_id == this.answer_id){
                            this.$delete(askAnswerList.data.data,i);
                            break
                        }
                    }
                }

                askAnswerList.data.data.unshift(res.data)
                setTimeout(() => {
                    this.$set(askAnswerList.data.data[0],'new',true)
                }, 100);
                askAnswerList.restList()
                tinymce.editors[0].setContent('');
                askwriteanswer.showAnswer()
                this.answerLocked = false
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.answerLocked = false
            })
        }
    }
})

var askList = new Vue({
    el:'.ask-archive',
    data:{
        paged:1,
        locked:false,
        data:'',
        empty:false,
        authorData:[],
        isAuthor:false,
        opt:{
            paged:1,
            count:15,
            pages:1,
            type:'hot',
            author:0,
            cat:0,
            s:''
        },
        api:'getAskData',
        aapi:'getAnswerData',
        first:true,
        type:'ask',
        aopt:{
            paged:1,
            count:4,
            pages:1,
            author_id:0,
            type:'hot',
            author:0,
            cat:0,
            s:''
        },
        answerData:'',
        afirst:true,
        inv:[],
        best:0
    },
    mounted(){
        if(!this.$refs.askarchive) return
        this.opt.paged = this.$refs.askarchive.getAttribute ('data-paged')

        if(this.$refs.askarchive.getAttribute ('data-term')){
            this.opt.cat = this.$refs.askarchive.getAttribute ('data-term')
        }

        if(this.$refs.askarchive.getAttribute ('data-key')){
            this.opt.s = this.$refs.askarchive.getAttribute ('data-key')
        }

        if(this.$refs.askarchive.getAttribute('data-people')){
            this.opt.author = this.$refs.askarchive.getAttribute('data-people')
            this.aopt.author_id = this.opt.author
            this.isAuthor = true
            this.getAuthorInfo()
        }

        this.opt.count = this.$refs.askarchive.getAttribute ('data-count')
        this.$refs.infonav.go(this.opt.paged,'comment',true,true)
        
    },
    computed:{
        userData(){
            return this.$store.state.userData;
        }
    },
    watch:{
        type(val){
            if(val == 'answer'){
                this.$refs.answer.go(this.aopt.paged,'comment',true,true)
            }
        }
    },
    methods:{
        search(){
            this.opt.paged = 1
            this.$refs.gujia.style.display = 'block'
            this.data = ''
            this.empty = false
            this.$refs.infonav.go(this.opt.paged,'comment',true,true)
        },
        deleteAsk(i,id){
            if(!confirm(b2_global.js_text.global.delete_post)) return
            this.$http.post(b2_rest_url+'deleteDraftPost','post_id='+id).then(res=>{
                this.$delete(this.data.data,i)
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        fliter(type){
            this.opt.type = type
            this.opt.paged = 1
            this.$refs.gujia.style.display = 'block'
            this.data = ''
            this.empty = false
            this.$refs.infonav.go(this.opt.paged,'comment',true,true)
        },
        getAuthorInfo(){
            this.$http.post(b2_rest_url+'getAuthorInfo','author_id='+this.opt.author).then(res=>{
                this.authorData = res.data
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
        dmsg(){
            if(!b2token){
                login.show = true
            }else{
                b2Dmsg.userid = this.opt.author
                b2Dmsg.show = true
            }
        },
        get(res){
            if(res.data.length == 0 && this.paged == 1){
                this.empty = true
            }
            this.data = res
            this.opt.pages = res.pages
            this.$nextTick(()=>{
                if(!this.first){
                    this.$scrollTo('.site', 1, {offset: 0})
                }else{
                    this.first = false
                }
                this.$refs.gujia.style.display = 'none'
                setTimeout(() => {
                    lazyLoadInstance.update()
                    b2SidebarSticky()
                }, 150);
                b2tooltip('.user-tips')

            })

        },
        deleteAnswer(i,id){
            if(!confirm(b2_global.js_text.global.delete_post)) return
            this.$http.post(b2_rest_url+'deleteDraftPost','post_id='+id).then(res=>{
                this.$delete(this.answerData.data,i)
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        isInv(id){
            if(this.inv.length == 0) return false;
            for (let i = 0; i < this.inv.length; i++) {
                if(id == this.inv[i].id) return true
            }

            return false
        },
        getAnswer(res){
            if(res.data.length == 0 && this.paged == 1){
                this.empty = true
            }
            this.answerData = res
            this.aopt.pages = res.pages
            this.$nextTick(()=>{
                if(!this.afirst){
                    this.$scrollTo('.site', 1, {offset: 0})
                }else{
                    this.afirst = false
                }
                this.$refs.gujiaanswer.style.display = 'none'
                setTimeout(() => {
                    lazyLoadInstance.update()
                    b2SidebarSticky()
                }, 150);
                b2tooltip('.user-tips')

            })
        }
    }
})