
var askInv = new Vue({
    el:'.ask-inv-box',
    data:{
        postData:''
    }
})

var askSingleTop = new Vue({
    el:'.entry-excerpt',
    data:{
        excerpt:''
    },
    methods:{
        showAc(){

            askSingleBottom.show = !askSingleBottom.show
            if(askSingleBottom.show){
                document.querySelector('.entry-excerpt').style.display = 'none'
                document.querySelector('.entry-content').style.height = 'auto'
                document.querySelector('.entry-content').style.overflow = 'initial'
            }else{
                document.querySelector('.entry-excerpt').style.display = 'block'
                document.querySelector('.entry-content').style.height = '0'
                document.querySelector('.entry-content').style.overflow = 'hidden'
            }
        }
    }
})

var askSingleBottom = new Vue({
    el:'.ask-footer',
    data:{
        answer_count:0,
        favorites:0,
        favorites_isset:false,
        views:0,
        show:false,
        canEdit:false,
        status:''
    },
    methods:{
        showAc(){
            askSingleTop.showAc()
        },
        postFavoriteAc(){
            
            if(!b2token){
                login.show = true
            }else{
                if(this.locked == true) return
                this.locked = true

                this.$http.post(b2_rest_url+'userFavorites','post_id='+b2_global.post_id).then(res=>{
                    if(res.data == true){
                        this.favorites_isset = true
                        this.favorites++
                    }else{
                        this.favorites_isset = false
                        this.favorites--
                    }
                    this.locked = false
                }).catch(err=>{

                    Qmsg['warning'](err.response.data.message,{html:true});
                    this.locked = false
                })
            }
        },
    }
})

var askTop = new Vue({
    el:'.ask-single-top',
    data:{
        metas:[]
    }
})

var askwriteanswer = new Vue({
    el:'.ask-write-answer',
    data:{
        show:false,
        editInit:false,
        id:0,
        editId:0
    },
    mounted(){
        if(!this.$refs.writeanswer) return
        this.id = this.$refs.writeanswer.getAttribute('data-id')
        this.editId = b2GetQueryVariable('answer_id')
    },
    computed:{
        userRole(){
            return this.$store.state.userRole;
        }
    },
    watch:{
        userRole(val){
            if(val.can_answer && this.editId){
                this.showAnswer(this.editId)
            }
        }
    },
    methods:{
         showAnswer(id){

            if(!b2token){
                login.show = true
                return
            }

            if(!this.userRole.can_answer){
                Qmsg['warning'](b2_global.js_text.global.cannotanswer,{html:true});
                return
            }

            if(!this.show){
                document.querySelector('.ask-write-answer-box').style.display = 'block'
                this.show = true
            }else{
                document.querySelector('.ask-write-answer-box').style.display = 'none'
                this.show = false
            }

            if(id){
                document.querySelector('.ask-write-answer-box').style.display = 'block'
                this.show = true
            }

            if(this.editInit){
                if(id){
                    this.$scrollTo('.ask-write-answer', 300, {offset: 0})
                    this.getEditAnswerData(id).then(res=>{
                        poAsk.answer_id = id
                        
                        if(tinymce.editors[0].initialized){
                            tinymce.editors[0].setContent(res)
                        }else{
                            tinymce.editors[0].on('init',(event)=>{
                                event.target.setContent(res)
                            });
                        }
                        
                    })
                }
                document.querySelector('#write-answer-box').style.visibility = 'visible'
                return
            }else{
                this.$http.post(b2_rest_url+'getAanswerHtml','id='+this.id).then(res=>{
                    document.querySelector('#write-answer-box').innerHTML = res.data
                    this.$scrollTo('.ask-write-answer', 300, {offset: 0})
                    b2loadStyle(b2_global.site_info.site_uri+'/Assets/fontend/write.css','',()=>{
                        b2loadScript(b2_global.site_info.site_uri+'/Assets/fontend/library/tinymce/tinymce.min.js','',()=>{
                            b2loadScript(b2_global.site_info.site_uri+'/Assets/fontend/ask.js','ask-js',()=>{
                                if(id){
                                    this.getEditAnswerData(id).then(res=>{
                                        poAsk.answer_id = id
                                        if(tinymce.editors[0].initialized){
                                            tinymce.editors[0].setContent(res)
                                        }else{
                                            tinymce.editors[0].on('init',(event)=>{
                                                event.target.setContent(res)
                                                
                                            });
                                        }
                                        document.querySelector('#write-answer-box').style.visibility = 'visible'
                                    })
                                }else{
                                    tinymce.editors[0].on('init',(event)=>{
                                        document.querySelector('#write-answer-box').style.visibility = 'visible'
                                    });
                                }
                                
                                this.editInit = true
                            },10)
                        },10)
                    },10)
                }).catch(err=>{
                    Qmsg['warning'](err.response.data.message,{html:true});
                })
            }
            
        },
        async getEditAnswerData(id){
            return new Promise((resolve, reject) => {
                this.$http.post(b2_rest_url+'getEditAnswerData','answer_id='+id).then(res=>{
                    resolve(res.data)
                }).catch(err=>{
                    Qmsg['warning'](err.response.data.message,{html:true});
                })
            })

        }
    }
})

var askAnswerList = new Vue({
    el:'.ask-answer-box',
    data:{
        data:'',
        opt:{
            topicId:0,
            pages:0,
            paged:1,
            orderBy:'ASC',
            status:'publish',
        },
        commentBox:{
            index:'',
            childIndex:'',
            focus:false,
            img:'',
            imgId:0,
            showImgBox:'',
            locked:false,
            imageLocked:false,
            parent:0,
            progress:0
        },
        commentList:{
            list:[],
            load:false,
            reload:false,
            height:0
        },
        api:'getAnswerData',
        topt:{
            parent_id:0,
            paged:1,
            count:15,
            pages:1,
            type:'hot',
            author:0,
            cat:0,
            fliter:'hot',
            s:''
        },
        smileShow:false,
        firstLoad:true,
        showMoreButton:false,
        best:0,
        fliter:'hot',
        inv:[]
    },
    mounted(){
        this.topt.parent_id = askwriteanswer.id
        this.$refs.infonav.go(this.topt.paged,'comment',true,true)
    },
    computed:{
        userData(){
            return this.$store.state.userData;
        }
    },
    watch:{
        fliter(val){
            this.firstLoad = true
            this.topt.fliter = val
            this.topt.paged = 1
            this.$refs.infonav.go(this.topt.paged,'comment',true,true)
        }
    },
    methods:{
        isInv(id){
            if(this.inv.length == 0) return false;
            for (let i = 0; i < this.inv.length; i++) {
                if(id == this.inv[i].id) return true
            }

            return false
        },
        editAnswer(id){
            askwriteanswer.showAnswer(id)
        },
        deleteAnswer(i,id){
            if(!confirm(b2_global.js_text.global.delete_post)) return
            this.$http.post(b2_rest_url+'deleteDraftPost','post_id='+id).then(res=>{
                this.$delete(this.data.data,i)
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        get(data){
           
            this.data = data
            this.topt.pages = data.pages

            this.$refs.gujia.style.display = 'none'

            if(!this.firstLoad){
                // this.$scrollTo('.ask-answer-box', 0, {offset: -300})
                this.$scrollTo('.ask-answer-box', 1, {offset: -200})
            }
            this.firstLoad = false

            this.restList()

            
            // this.topt.paged++
        },
        restList(){
            this.$nextTick(()=>{
                setTimeout(() => {
                    lazyLoadInstance.update()
                    b2VideoReset('.b2-player')
                    b2prettyPrint()
                    b2SidebarSticky()
                    b2ImgZooming('.entry-content img')
                }, 20);
                
                for (let i = 0; i < this.data.data.length; i++) {
                    let height = document.querySelector('#answer-item-'+this.data.data[i].post_id+' .answer-content').offsetHeight
                    if(height > 380){
                        this.$set(this.data.data[i],'showMore',true);
                    }
                    
                }
            })
        },
        getData(){
            this.$http.post(b2_rest_url+'getAnswerData','parent_id='+askwriteanswer.id).then(res=>{
                
            })
        },
        followingAc(i,id){
            if(!b2token){
                login.show = true
            }else{
                this.$http.post(b2_rest_url+'AuthorFollow','user_id='+id).then(res=>{
                    this.$set(this.data.data[i].author,'followed',res.data)
                }).catch(err=>{
                    Qmsg['warning'](err.response.data.message,{html:true});
                })
            }
        },
        showChildComment(index,parent){
            if(!this.changeTip()) return
            
            this.setCommentBox('#comment-box-at-'+parent)
            this.commentBox.childIndex = index

            this.commentBox.parent = parent
            this.resetComment(true)
        },
        setCommentBox(where){

            if(where){
                if(where.indexOf('#comment-box-at-') === -1){
                    this.commentBox.parent = ''
                }
                document.querySelector(where).appendChild(document.querySelector('#topic-comment-form'))
            }else{
                document.querySelector('#comment-form-reset').appendChild(document.querySelector('#topic-comment-form'))
            }
        },
        restScroll(ti){
            
            if(this.commentBox.index !== '' && ti > this.commentBox.index){
                console.log(ti,this.commentBox.index)
                window.removeEventListener('scroll',window.bodyScrool,false)
                commentListHeight = document.querySelector('#answer-item-'+this.opt.topicId+' .topic-comments').offsetHeight+16
               
                window.scrollBy(0, -commentListHeight);
                setTimeout(() => {
                    window.addEventListener("scroll",window.bodyScrool , false);
                }, 500);
            }
        },
        resetCommentBox(){

            this.commentBox.index = ''
            this.commentBox.childIndex = ''
            this.commentBox.list = ''
            this.commentList.list = []
            this.commentList.height = 0

            this.opt = {
                topicId:'',
                pages:0,
                paged:1,
                orderBy:'ASC',
                satatus:this.admin.is ? 'pending' : ''
            }
        },
        restCommontScroll(){

            let commentListHeight = document.querySelector('#comment-list-'+this.opt.topicId).clientHeight

            if(commentListHeight > this.commentList.height) return

            window.removeEventListener('scroll',window.bodyScrool,false)
            window.scrollBy(0, commentListHeight - this.commentList.height);
            setTimeout(() => {
                window.addEventListener("scroll",window.bodyScrool , false);
            }, 500);
        },
        getMoreCommentListData(data){
            this.setCommentBox('#comment-box-'+this.opt.topicId)
            this.commentBox.parent = ''
            this.commentBox.childIndex = ''

            this.commentList.height = document.querySelector('#comment-list-'+this.opt.topicId).clientHeight

            this.commentList.list = data.list
                
            this.opt.pages = data.pages
            this.commentList.load = false

            this.$nextTick(()=>{
                this.restCommontScroll()
                this.rebuildZoom()
            })
            this.commentList.reload = false
        },
        resetComment(rePageNav){
            this.$refs.topicForm.value = ''
            this.$refs.topicForm.style.height = '40px'
            this.commentBox.img = ''
            this.commentBox.imgId = 0
            this.commentBox.showImgBox = false
            this.smileShow = false
            
            if(!rePageNav){
                this.opt.pages = 0
                this.opt.paged = 1
            }
        },
        commentDisabled(){
            if(this.commentBox.locked == true || this.commentBox.imageLocked == true) return true
            return false
        },
        showComment(ti,click){

            if(!this.changeTip()) return

            this.restScroll(ti)

            if(this.data.data[ti].post_id == this.opt.topicId){
                this.opt.topicId = ''
                this.commentBox.index = ''
                this.setCommentBox()
            }else{
                
                this.opt.topicId = this.data.data[ti].post_id

                this.commentBox.index = ti
                this.setCommentBox('#comment-box-'+this.opt.topicId)
                this.getCommentList()
            }
 
            this.resetComment()
        },
        getCommentList(){
            if(this.commentList.load == true) return
            this.commentList.load = true

            let data = {
                'topicId':this.opt.topicId,
                'paged': 1,
                'orderBy':this.opt.orderBy
            }
            this.$http.post(b2_rest_url+'getTopicCommentList',Qs.stringify(data)).then(res=>{

                this.commentList.list = res.data.list
                
                this.opt.pages = res.data.pages

                this.commentList.load = false
                this.commentList.reload = false

                this.$nextTick(()=>{
                    this.rebuildZoom()
                    lazyLoadInstance.update()
                })
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.commentList.load = false
                this.commentList.reload = false
            })
        },
        changeTip(){
            if(this.$refs.topicForm.value.length > 0 || this.commentBox.img){
                if(!confirm(b2_global.js_text.circle.change_topic_form)){
                    return false
                }
            }
            return true
        },
        changeOrderBy(){
            this.commentList.reload = true
            if(this.opt.orderBy === 'DESC'){
                this.opt.orderBy = 'ASC'
            }else{
                this.opt.orderBy = 'DESC'
            }
            this.commentBox.parent = ''
            this.commentBox.childIndex = ''
            this.opt.paged = 1
            this.setCommentBox('#comment-box-'+this.opt.topicId)
            this.getCommentList()
        },
        rebuildZoom(){
            let imgList = document.querySelectorAll('#comment-list-'+this.opt.topicId+' .topic-commentlist-img-box img')
            for (let index = 0; index < imgList.length; index++) {
                b2zoom.listen(imgList[index]);
            }
        },
        getFile(event){
            let file = event.target.files[0];
            if(!file || this.commentBox.imageLocked === true) return;
            this.commentBox.imageLocked = true
            let formData = new FormData()

            this.removeImage()
            this.commentBox.showImgBox = true

            formData.append('file',file,file.name)
            formData.append("post_id", this.opt.topicId)
            formData.append("type", 'circle')

            let config = {
                onUploadProgress: progressEvent=>{
                    this.commentBox.progress = progressEvent.loaded / progressEvent.total * 100 | 0
                }
            }

            this.$http.post(b2_rest_url+'fileUpload',formData,config).then(res=>{
                this.commentBox.progress = 'success'
                this.commentBox.img = res.data.url
                this.commentBox.imgId = res.data.id

                this.$refs.imageInput.value = null
                this.commentBox.imageLocked = false
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.commentBox.progress = 'fail'
                this.$refs.imageInput.value = null
                this.commentBox.imageLocked = false
            })
        },
        removeImage(){
            this.commentBox.img = ''
            this.commentBox.imgId = 0
            this.commentBox.progress = 0
            this.commentBox.showImgBox = false
        },
        addSmile(val){
            grin(val,this.$refs.topicForm)
            this.smileShow = false
        },
        deleteComment(commentId,index,_index){
           
            if(!confirm(b2_global.js_text.circle.delete_comment)) return


            this.$http.post(b2_rest_url+'deleteComment','comment_id='+commentId).then(res=>{
                if(res.data == true){

                    // this.setCommentBox()
                    // this.resetCommentBox()

                    Qmsg['success'](b2_global.js_text.circle.delete_success,{html:true});
                    if(_index != 'undefined' && _index != undefined){
                        this.commentList.list[index].child_comments.list.splice(_index,1)
                    }else{
                        this.commentList.list.splice(index,1)
                    }
                    
                }else{
                    Qmsg['warning'](b2_global.js_text.circle.delete_fail,{html:true});
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        answerVote(index,type,topic_id){
            if(!b2token){
                login.loginType = 1
                login.show = true
                return
            }

            if(this.data.data[index].vote.locked === true) return
            this.data.data[index].vote.locked = true

            this.$http.post(b2_rest_url+'postVote','type='+type+'&post_id='+topic_id).then(res=>{

                this.$set(this.data.data[index].vote,'up',this.data.data[index].vote.up + res.data.up)
                this.$set(this.data.data[index].vote,'down',this.data.data[index].vote.down + res.data.down)

                if(res.data.up > 0){
                    this.$set(this.data.data[index].vote,'isset_up',true)
                }else{
                    this.$set(this.data.data[index].vote,'isset_up',false)
                }

                if(res.data.down > 0){
                    this.$set(this.data.data[index].vote,'isset_down',true)
                }else{
                    this.$set(this.data.data[index].vote,'isset_down',false)
                }

                this.$set(this.data.data[index].vote,'locked',false)
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.$set(this.data.data[index].vote,'locked',false)
            })
        },
        bestAnswer(index,id){
            var r = confirm(b2_global.js_text.global.best_answer)
            if(!r) return
            this.$http.post(b2_rest_url+'bestAnswer','answer_id='+id).then(res=>{
                this.data.data[index].best = 1
                this.best = 1
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        vote(index,childindex,comment_id){

            if(!b2token){
                login.loginType = 1
                login.show = true
                return
            }

            this.$http.post(b2_rest_url+'commentVote','type=comment_up&comment_id='+comment_id).then(res=>{
                if(childindex === ''){
                    this.$set(this.commentList.list[index].vote,'up',this.commentList.list[index].vote.up+res.data.comment_up)
                    if(res.data.comment_up === 1){
                        this.$set(this.commentList.list[index].vote,'picked',true)
                    }else{
                        this.$set(this.commentList.list[index].vote,'picked',false)
                    }
                }else{
                    this.$set(this.commentList.list[index].child_comments.list[childindex].vote,'up',this.commentList.list[index].child_comments.list[childindex].vote.up+res.data.comment_up)
                    if(res.data.comment_up === 1){
                        this.$set(this.commentList.list[index].child_comments.list[childindex].vote,'picked',true)
                    }else{
                        this.$set(this.commentList.list[index].child_comments.list[childindex].vote,'picked',false)
                    }
                }
                
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
            
        },
        submitComment(){
            if(this.commentDisabled()) return
            this.commentBox.locked = true
            let data = {
                'comment_post_ID':this.opt.topicId,
                'comment':this.$refs.topicForm.value,
                'comment_parent':this.commentBox.parent,
                'img':{
                    'imgUrl':this.commentBox.img,
                    'imgId':this.commentBox.imgId
                },
                'json':true
            }

            this.$http.post(b2_rest_url+'commentSubmit',Qs.stringify(data)).then(res=>{
                
                if(this.commentBox.parent){
                    this.commentList.list[this.commentBox.childIndex].child_comments.list.push(res.data.list)
                }else{
                    this.commentList.list.unshift(res.data.list)
                }
                this.resetComment(true)
                this.commentBox.locked = false
                this.$nextTick(()=>{
                    if(this.commentBox.parent){
                        this.showChildComment(this.commentBox.childIndex,res.data.list.comment_ID)
                    }
                    document.querySelector('#topic-comment-'+res.data.list.comment_ID).classList += ' new-comment'

                    setTimeout(() => {
                        lazyLoadInstance.update()
                    }, 50);
                    
                })
            }).catch(err=>{
                if(typeof err.response.data.message == 'string'){
                    Qmsg['warning'](err.response.data.message,{html:true});
                }else{
                    Qmsg['warning'](err.response.data.message[0],{html:true});
                }
                this.commentBox.locked = false
            })
        },
    }
})