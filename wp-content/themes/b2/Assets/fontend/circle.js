var b2CirclePostBox = new Vue({
    el:'#po-topic-box',
    data:{
        single:{
            is:false,
            id:0
        },
        admin:{
            is:false
        },
        edit:{
            is:false,
            topicId:0
        },
        character:{
            length:0,
            min:0,
            max:10,
            over:false
        },
        showPoBox:true,
        disabled:false,
        locked:false,
        allowSubmit:'less',
        topicType:'say',
        topicTypeBox:false,
        joinLocked:false,
        circle:{
            show:false,
            list:'',
            picked:0,
            gc:0
        },
        currentUser:{
            currentCircleRole:{
                type:'free',
                data:[]
            },
            inCircle:false,
            allowCreateTopic:false,
            isAdmin:false,
            isCircleAdmin:false,
            mediaRole:{
                card: '',
                file: '',
                image: '',
                video: ''
            },
            topicTypeRole:{
                ask: '',
                guess: '',
                vote: ''
            },
            readRole:{
                public:true,
                logon:false,
                comment:false,
                credit:false,
                money:false,
                lv:false
            },
            credit:0,
            money:0,
            darkRoom:false,
            canPost:true,
            allowPendings:1
        },
        join:{
            why:'',
            picked:''
        },
        login:true,
        timeout:false,
        ask:{
            userInput:'',
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
        },
        vote:{
            type:'radio',
            list:['']
        },
        guess:{
            list:[''],
            right:0
        },
        image:{
            allow:true,
            list:[],
            count:5,
            oldNum: 0,
            newNum: 0,
            indexMark:0
        },
        video:{
            allow:true,
            list:[],
            count:5,
            oldNum: 0,
            newNum: 0,
            indexMark:0
        },
        file:{
            allow:true,
            list:[],
            count:5,
            oldNum: 0,
            newNum: 0,
            indexMark:0
        },
        card:{
            show:false,
            allow:true,
            list:[],
            count:5,
            oldNum: 0,
            newNum: 0,
            indexMark:0,
            input:'',
            locked:false
        },
        role:{
            show:false,
            list:'',
            see:'public',
            money:'',
            credit:'',
            lv:[],
            lvPicked:[],
            currentCircle:0
        },
        smileShow:false,
        uploadType:'',
        errorFile:false,
        postPromise:'',
        showAdminBox:false,
        _userData:{
            avatar:'',
            avatar_webp:''
        },
        topCats:{
            'circle_square':0,
            'data':[]
        }
    },
    computed:{
        userData(){
            return this.$store.state.userData;
        }
    },
    mounted(){
        B2ClientWidth > 768 ? this.showAdminBox = true : this.showAdminBox = false
        this.single.is = this.$refs.circleSingle
        this.admin.is = this.$refs.circleAdmin
        this.edit.is = document.querySelector('.topicEdit')
        if(this.single.is){
            this.circle.picked = this.$refs.circleSingle.getAttribute('data-circleId')
        }else if(this.admin.is){
            this.circle.picked = this.$refs.circleAdmin.getAttribute('data-circleId')
        }else{
            if(!this.$refs.textareaTopic) return

            this.getCircleTopCats()

            if(B2ClientWidth >= 768){
                this.showPoBox = true
            }else{
                this.showPoBox = false
            }

            autosize(this.$refs.textarea_title)
            autosize(this.$refs.textarea_box)

            if(b2token){
                this.login = true
            }else{
                this.login = false
            }
            
            // this.$set(this.card,'list',JSON.parse(localStorage.getItem('card_test')))
            this.role.list = JSON.parse(this.$refs.role.getAttribute('data-roledata'))
            this.role.lv = JSON.parse(this.$refs.role.getAttribute('data-lvs'))
            if(this.edit.is){
                this.circle.picked = this.edit.is.getAttribute('data-circleId')
            }else{
                this.circle.picked = this.$refs.textareaTopic.getAttribute('data-circle')
            }
            
            this.circle.gc = parseInt(this.$refs.textareaTopic.getAttribute('data-gc'))
        }

        this.getCurrentUserCircleData()
        
    },
    methods:{
        go(index){
            let id = this.topCats.data[index].id
            this.circle.picked = id
            b2CircleList.pickedCircle('widget',id)
            this.getCurrentUserCircleData()
            window.history.pushState(id, this.topCats.data[index].name, this.topCats.data[index].link)
            document.title = this.topCats.data[index].name+' '+b2_global.site_separator+' '+b2_global.site_name
            // b2AsideBar.goTop()
            return false
        },
        getCircleTopCats(){
            this.$http.post(b2_rest_url+'getCircleTopCats','circle_id='+this.circle.picked).then(res=>{
                this.topCats = res.data
            })
        },
        showAdmin(){
            if(B2ClientWidth < 768){
                this.showAdminBox = !this.showAdminBox
            }
        },
        showPoBoxAc(){
            if(B2ClientWidth < 768){
                if(this.showPoBox){
                    b2mobileFooterMenu.show = false
                }else{
                    b2mobileFooterMenu.show = true
                }
            }
            
        },
        getEditTopicData(){
            this.edit.topicId = b2GetQueryVariable('topic_id')
            this.$http.post(b2_rest_url+'getEditData','topic_id='+this.edit.topicId).then(res=>{

                if(this.edit.is){
                    this.userData = res.data.userData
                }

                this.showPoBox = true
                
                this.topicType = res.data.type
                
                this.topicTypeBox = false
                
                if(!this.circle.list.hasOwnProperty(res.data.circleId)){
                    this.$set(this.circle.list,res.data.circleId,res.data.circle)
                }
                
                this.circle.picked = res.data.circleId
                
                this.ask.type = res.data.ask.type ? res.data.ask.type : 'someone'
                this.ask.pay = res.data.ask.pay
                this.ask.userList = res.data.ask.userList
                this.ask.pickedList = res.data.ask.pickedList

                this.ask.reward = res.data.ask.reward ? res.data.ask.reward : 'credit'
                this.ask.time = res.data.ask.time
                
                this.vote.type = res.data.vote.type ? res.data.vote.type : 'radio'
                for (let i = 0; i < res.data.vote.list.length; i++) {
                    this.$set(this.vote.list,i,res.data.vote.list[i].title)
                }

                for (let i = 0; i < res.data.guess.list.length; i++) {
                    this.$set(this.guess.list,i,res.data.guess.list[i].title)
                }
                this.guess.right = res.data.guess.right ? res.data.guess.right : 0
         
                if(res.data.video.length > 0){
                    for (let i = 0; i < res.data.video.length; i++) {
                        res.data.video[i]['locked'] = false
                        res.data.video[i]['progress'] = 100
                        res.data.video[i]['success'] = true
                    }
                }
                this.video.list = res.data.video
                this.video.indexMark = res.data.video.length

                if(res.data.image.length > 0){
                    for (let i = 0; i < res.data.image.length; i++) {
                        res.data.image[i]['locked'] = false
                        res.data.image[i]['progress'] = 100
                        res.data.image[i]['success'] = true
                    }
                }
                this.image.list = res.data.image
                this.image.indexMark = res.data.image.length
                
                if(res.data.file.length > 0){
                    for (let i = 0; i < res.data.file.length; i++) {
                        res.data.file[i]['locked'] = false
                        res.data.file[i]['progress'] = 100
                        res.data.file[i]['success'] = true
                    }
                }
                this.file.list = res.data.file
                this.file.indexMark = res.data.file.length

                if(res.data.card.length > 0){
                    for (let i = 0; i < res.data.card.length; i++) {
                        this.$set(this.card.list,i,{
                            'id':res.data.card[i].id,
                            'progress':100,
                            'success':true,
                            'data':res.data.card[i]
                        })
                    }
                    this.card.indexMark = res.data.card.length
                }

                this.role.see = res.data.role.see
                if(this.role.see !== 'public') this.role.show = true
                this.role.money = res.data.role.money
                this.role.credit = res.data.role.credit
                this.role.lvPicked = res.data.role.lvPicked
                //this.role.currentCircle = res.data.role.currentCircle


                this.$refs.textarea_title.value = res.data.title
                this.$refs.textarea_box.value = res.data.content
                
                autosize.update(this.$refs.textarea_title)
                autosize.update(this.$refs.textarea_box)

                this.changeText()


            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })

        },
        getCurrentUserCircleData(){
            
            this.$http.post(b2_rest_url+'getCurrentUserCircleData','circle_id='+this.circle.picked).then(res=>{
                this.defaultCircle(res.data.circles)
                this.currentUser.allowCreateTopic = res.data.allow_create_topic
                this.currentUser.currentCircleRole = res.data.current_circle_role
                if(this.currentUser.currentCircleRole.type === 'money' && this.currentUser.currentCircleRole.data.length > 0){
                    this.join.picked = this.currentUser.currentCircleRole.data[0].type
                }
                this.currentUser.isAdmin = res.data.is_admin
                this.currentUser.canPost = res.data.can_post
                this.currentUser.allowPendings = res.data.allow_pendings
                this.currentUser.isCircleAdmin = res.data.is_circle_admin
                this.currentUser.inCircle = res.data.in_circle
                this.currentUser.darkRoom = res.data.dark_room
                this.circle.list = res.data.circles
                this.currentUser.money = res.data.money
                this.currentUser.credit = res.data.credit
                Object.keys(res.data.circles).forEach(key => {
                    
                    if(res.data.circles[key].is_circle_admin){
                        b2CircleList.circle.created[key] = res.data.circles[key]
                    }else if(!res.data.circles[key].is_circle_admin && res.data.circles[key].in_circle){
                        b2CircleList.circle.join[key] = res.data.circles[key]
                    }
                    
                });

                this.currentUser.mediaRole = res.data.media_role
                this.currentUser.topicTypeRole = res.data.topic_type_role
                this.currentUser.readRole = res.data.read_role
                if(!this.single.is){
                    this.$refs.poFormBox.style.opacity = 1
                }

                this.resetFileRole(this.circle.picked)

                if(this.edit.is){
                    this.getEditTopicData()
                }
            })
        },
        circlePicked(id){
            this.circle.picked = id
            this.circle.show = false
            this.resetFileRole(this.circle.picked)
            b2CircleList.pickedCircle('widget',id)
        },
        resetFileRole(circleId){
            this.character.min = this.circle.list[circleId].file_role.topic_count.min
            this.character.max = this.circle.list[circleId].file_role.topic_count.max
            this.image.count = this.circle.list[circleId].file_role.image_count
            this.video.count = this.circle.list[circleId].file_role.video_count
            this.file.count = this.circle.list[circleId].file_role.file_count
            this.card.count = this.circle.list[circleId].file_role.card_count
        },
        defaultCircle(data){
            for (var a in data) {
                if (data[a].default === true) {
                    this.$set(this.circle,'picked',data[a].id)
                } 
            }
        },
        loginAc(type){
            login.show = true
            login.loginType = type
        },
        searchUser(){
            if(this.ask.userInput === '') return
            if(this.ask.locked == true) return
            this.ask.locked = true
            this.$http.post(b2_rest_url+'searchUsers','nickname='+this.ask.userInput).then(res=>{
                if(res.data.length > 0){
                    this.ask.userList = res.data
                }else{
                    this.ask.userList = []
                }
                this.$nextTick(()=>{
                    lazyLoadInstance.update()
                })
                this.ask.locked = false
            }).catch(err=>{
                this.ask.locked = false
                this.ask.userList = []
            })
        },
        placeholder(){
            return b2_global.js_text.circle[this.topicType]
        },
        addVoteList(){
            if(this.vote.type === 'pk' && this.vote.list.length >= 2) return
            this.vote.list.push('')
        },
        subVoteList(index){
            this.$delete(this.vote.list,index)
        },
        addGuessList(){
            this.guess.list.push('')
        },
        subGuessList(index){
            this.$delete(this.guess.list,index)
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
            this.ask.userInput = ''
            this.ask.userList = []

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
        percentage(character){
            if(this.character.min > character){
                return Calc.Mul(Calc.Div(character,this.character.min),100);
            }

            if(this.character.max >= character && this.character.min < character){
                return Calc.Mul(Calc.Div(character,this.character.max),100);
            }

            return 100;
        },
        nFormatter(num) {
            isNegative = false
            if (num < 0) {
                isNegative = true
            }
            num = Math.abs(num)
            if (num >= 1000000000) {
                formattedNumber = (num/1000000000).toFixed(1).replace(/.0$/, '') + 'G';
            }else if (num >= 1000000) {
                formattedNumber = (num/1000000).toFixed(1).replace(/.0$/, '') + 'M';
            }else if (num >= 1000) {
                formattedNumber = (num/1000).toFixed(1).replace(/.0$/, '') + 'K';
            }else {
                formattedNumber = num;
            } 
            if(isNegative) { formattedNumber = '-' + formattedNumber }
            return formattedNumber;
        },
        makesvg(percentage, inner_text='',character = 0){

            let abs_percentage = Math.abs(percentage).toString(),classes = '';
            
            if(this.character.min == 0 && character == 0){
                classes = '';
            }else if(this.character.max >= character && this.character.min <= character){
                classes = "success-stroke";
            } else if(character != 0){
                classes = "warning-stroke";
            }
          
           let svg = `<svg class="circle-chart" viewbox="0 0 33.83098862 33.83098862" xmlns="http://www.w3.org/2000/svg">
                    <circle class="circle-chart__background" cx="16.9" cy="16.9" r="15.9" />
                    <circle class="circle-chart__circle ${classes}"
                    stroke-dasharray="${abs_percentage},100"    cx="16.9" cy="16.9" r="15.9" />
                    <g class="circle-chart__info">
                    <text class="circle-chart__subline ${classes}" x="16.91549431" y="22">${inner_text}</text>
                    </g></svg>`;

            return svg
        },
        changeText(){
            this.character.length = this.$refs.textarea_box.value.length;

            if(this.character.length < this.character.min){
                this.allowSubmit = 'less'
            }else if(this.character.length > this.character.max){
                this.allowSubmit = 'more'
            }else{
                this.allowSubmit = 'allow'
            }
        },
        addSmile(val){
            grin(val,this.$refs.textarea_box)
        },
        fileExists(mime){
            let index= mime.lastIndexOf('.');
            let ext = mime.substr(index+1);
            return ext;
        },
        readablizeBytes(bytes) {
            let s = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
            let e = Math.floor(Math.log(bytes)/Math.log(1024));
            return (bytes/Math.pow(1024, Math.floor(e))).toFixed(2)+' '+s[e];
        },
        async getFile(event,type){
            if(this.uploadType != ''){
                Qmsg['warning'](b2_global.js_text.circle['img_locked'],{html:true});
                return false;
            }

            if(this[type].count <= this[type].list.length){
                Qmsg['warning'](b2_global.js_text.circle['file_count'],{html:true});
                return false
            }

            this.uploadType = type
            let files = event.target.files
            if(!files) return;

            let index = this[type].indexMark
            for (let i = index; i < (files.length + index); i++) {
                if(this[type].list.length < this[type].count){
                    if(type === 'image'){
                        this.$set(this[type].list,i,{'id':0,'url':window.URL.createObjectURL(files[i - index]),'poster':'','progress':0,'success':false})
                    }else if(type === 'video'){
 
                        const poster = await this.getVideoCover(files[i - index], 0.1);

                        this.$set(this[type].list,i,{'id':0,'url':window.URL.createObjectURL(files[i - index]),'poster':window.URL.createObjectURL(poster),'postFile':poster,'progress':0,'success':false})

                    }else{
                        this.$set(this[type].list,i,{'id':0,'url':'','progress':0,'success':false,'ext':this.fileExists(files[i - index].name),'size':this.readablizeBytes(files[i - index].size),'name':files[i - index].name})
                    }

                    this.uploadFile(files[i - index],i,type)
                    this[type].indexMark++
                }else{
                    Qmsg['warning'](b2_global.js_text.circle[type+'_count'],{html:true});
                    break;
                }
            }
        },
        getImageBase64(file){
            var reader = new FileReader();
            var AllowImgFileSize = 2100000; //上传图片最大值(单位字节)（ 2 M = 2097152 B ）超过2M上传失败
            var imgUrlBase64;
            if (file) {
                //将文件以Data URL形式读入页面  
                imgUrlBase64 = reader.readAsDataURL(file);
                reader.onload = function (e) {

                if (AllowImgFileSize != 0 && AllowImgFileSize < reader.result.length) {

                        return;
                    }else{
                        //执行上传操作
                        console.log(reader.result);
                    }
                }
            }    
        },
        subLocked(){
            if(this.locked) return true
            if(this.allowSubmit != 'allow') return true
            if(this.uploadType != '') return true
            if(this.errorFile == true) return true
        },
        subText(){
            if(this.locked) return b2_global.js_text.circle.subing
            if(this.allowSubmit == 'less') return b2_global.js_text.circle.text_less
            if(this.allowSubmit == 'more') return b2_global.js_text.circle.text_more
            if(this.uploadType != '') return b2_global.js_text.circle.waiting_uploads
            if(this.errorFile == true) return b2_global.js_text.circle.file_error

            return b2_global.js_text.circle.submit
        },
        uploadLocked(type){

            if(!type){
                type = this.uploadType
            }

            let allow = false
            this.errorFile = false

            for (let i = 0; i < this[type].list.length; i++) {
                if(!this[type].list[i].success){
                    allow = true
                }

                if(this[type].list[i].success == 'fail'){
                    this.errorFile = true
                }
            }

            if(!allow){
                this.uploadType = ''
            }

            return allow
        },
        getVideoCover(file, seekTo = 0.0) {

            return new Promise((resolve, reject) => {

                const videoPlayer = document.createElement('video');
                videoPlayer.setAttribute('src', URL.createObjectURL(file));
                videoPlayer.load();
                videoPlayer.addEventListener('error', (ex) => {
                    Qmsg['warning'](b2_global.js_text.global.video_file_error+file.name,{html:true});
                });

                videoPlayer.addEventListener('loadedmetadata', () => {

                    setTimeout(()=>{
                        if (videoPlayer.duration < seekTo) {
                            reject("video is too short.");
                            return;
                        }
    
                        setTimeout(() => {
                          videoPlayer.currentTime = seekTo;
                        }, 200);
    
                        videoPlayer.addEventListener('seeked', () => {
                            console.log('video is now paused at %ss.', seekTo);
    
                            const canvas = document.createElement("canvas");
                            canvas.width = videoPlayer.videoWidth;
                            canvas.height = videoPlayer.videoHeight;
    
                            const ctx = canvas.getContext("2d");
                            ctx.drawImage(videoPlayer, 0, 0, canvas.width, canvas.height);
        
                            ctx.canvas.toBlob(
                                blob => {
                                    resolve(blob);
                                },
                                "image/jpeg",
                                0.75 
                            );
                        });
                    },100)
                });
            });
        },
        uploadFile(file,i,type){
            let formData = new FormData()

            formData.append('file',file,file.name)
            formData.append("post_id", 1)
            formData.append("type", 'circle')

            let config = {
                onUploadProgress: progressEvent=>{
                    this.$set(this[type].list[i],'progress',progressEvent.loaded / progressEvent.total * 100 | 0)
                }
            }

            this.$http.post(b2_rest_url+'fileUpload',formData,config).then(res=>{

                if(type === 'video'){
                    let videoImg = new FormData()
                    videoImg.append('file',this[type].list[i].postFile,file.name)
                    videoImg.append("post_id", 1)
                    let fimeName = res.data.url.substring(0, res.data.url.lastIndexOf('.'))+'.jpg';
                    fimeName = fimeName.substring(fimeName.lastIndexOf('/')+1,fimeName.length)
                    videoImg.append("set_poster", res.data.id)
                    videoImg.append("file_name", fimeName)
                    videoImg.append("type", 'circle')

                    this.$http.post(b2_rest_url+'fileUpload',videoImg).then(_res=>{
                        this.$set(this[type].list[i],'progress',100)
                        this.$set(this[type].list[i],'id',res.data.id)
                        this.$set(this[type].list[i],'success',true)
                        this.$set(this[type].list[i],'locked',false)
                        
                        this.$refs[type+'Input'].value = null
                        this.uploadLocked()
                    })
                }else{
                    this.$set(this[type].list[i],'progress',100)
                    this.$set(this[type].list[i],'id',res.data.id)
                    this.$set(this[type].list[i],'success',true)
                    this.$set(this[type].list[i],'locked',false)
                    
                    this.$refs[type+'Input'].value = null
                    this.uploadLocked()
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.$set(this[type].list[i],'success','fail')
                this.$refs[type+'Input'].value = null
                this.uploadLocked()
            })
        },
        removeFile(index,type){
            if(this.uploadType == type) return
            if(confirm(b2_global.js_text.circle['remove_'+type])){
                this.$delete(this[type].list,index)
                this[type].indexMark = this[type].list.length
                this.uploadLocked(type)
            }
        },
        dragstart(value,type) {
            if(this.uploadType !== '') return
            this[type].oldNum = value;
        },
        dragend(value,type) {
            if(this.uploadType !== '') return
            if (this[type].oldNum != this[type].newNum) {
                let oldIndex = this[type].list.indexOf(this[type].oldNum);
                let newIndex = this[type].list.indexOf(this[type].newNum);
                let newItems = [...this[type].list];
                newItems.splice(oldIndex, 1); 
                newItems.splice(newIndex, 0, this[type].oldNum);
                this[type].list = [...newItems];
            }
        },
        dragenter(value,type) {
            if(this.uploadType !== '') return
            this[type].newNum = value;
        },
        insertCard(){
            if(this.card.locked === true) return
            this.card.locked = true
            if(!this.card.input) return
            this.$http.post(b2_rest_url+'insertTopicCard','id='+this.card.input).then(res=>{
                this.card.list.push({'data':res.data,'progress':100,'id':res.data.id,'success':true})
                this.card.locked = false
                this.card.input = ''
                this.card.show = false
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.card.locked = false
            })
        },
        resetTopic(){
            this.$refs.textarea_title.value = ''
            this.$refs.textarea_box.value = ''
            this.topicType = 'say'
            this.ask = {
                userInput:'',
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
                hiddenInput:false
            }
            this.vote = {
                type:'radio',
                list:['']
            }
            this.guess = {
                list:[''],
                right:0
            }
            this.image = {
                allow:true,
                list:[],
                count:this.circle.list[this.circle.picked].file_role.image_count,
                oldNum: 0,
                newNum: 0,
                indexMark:0
            }
            this.video = {
                allow:true,
                list:[],
                count:this.circle.list[this.circle.picked].file_role.video_count,
                oldNum: 0,
                newNum: 0,
                indexMark:0
            }
            this.file = {
                allow:true,
                list:[],
                count:this.circle.list[this.circle.picked].file_role.file_count,
                oldNum: 0,
                newNum: 0,
                indexMark:0
            }
            this.card = {
                show:false,
                allow:true,
                list:[],
                count:this.circle.list[this.circle.picked].file_role.card_count,
                oldNum: 0,
                newNum: 0,
                indexMark:0,
                input:'',
                locked:false
            }
            this.role.show = false
            this.role.see = 'public'
            this.role.money = ''
            this.role.credit = ''
            this.role.lvPicked = []
            this.role.currentCircle = 0
        },
        submitTopic(){
            if(this.locked === true) return
            this.locked = true

            let data = {
                'type':this.topicType,
                'circle':this.circle.picked,
                'ask':this.ask,
                'vote':this.vote,
                'guess':this.guess,
                'title':this.$refs.textarea_title.value,
                'content':this.$refs.textarea_box.value,
                'image':this.image,
                'video':this.video,
                'file':this.file,
                'card':this.card,
                'role':this.role
            }

            if(this.edit.is){
                data['topic_id'] = this.edit.topicId
            }

            this.$http.post(b2_rest_url+'insertCircleTopic',Qs.stringify(data)).then(res=>{
                if(this.edit.is){
                    location.href = res.data.link
                }else{
                    b2CircleList.data.unshift(res.data)
                    b2CircleList.$nextTick(()=>{
                        document.querySelector('.circle-topic-item-'+res.data.topic_id).classList += ' new-topic'
                        imagesLoaded( document.querySelectorAll('.circle-topic-item'), function( instance ) {
                            b2SidebarSticky()
                        });
                        lazyLoadInstance.update()
                        this.resetTopic()
                        this.getCurrentUserCircleData()
                        this.showPoBox = false
                    })
                }
                this.locked = false
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        getCircleData(id,type,child){
            if(this.circle.list[id]){
                if(child){
                    return this.circle.list[id][type][child]
                }
                return this.circle.list[id][type]
            }
            
            return ''
        },
        joinCircle(){
            if(this.joinLocked === true) return
            this.joinLocked = true
            this.$http.post(b2_rest_url+'joinCircle','circle_id='+this.circle.picked).then(res=>{
                if(res.data === 'success'){
                    Qmsg['success'](b2_global.js_text.circle.join_success,{html:true});
                    setTimeout(() => {
                        b2CircleList.paged = 1
                        b2CircleList.getTopicList()
                        this.getCurrentUserCircleData()
                        this.joinLocked = false
                        b2CircleList.showJoin = false
                        if(this.single.is){
                            b2CircleList.showComment(0)
                        }
                    }, 500);

                }else{
                    this.getCurrentUserCircleData()
                    this.joinLocked = false
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.joinLocked = false
            })
        },
        joinPay(){
            let money
            for (let i = 0; i < this.currentUser.currentCircleRole.data.length; i++) {
                if(this.currentUser.currentCircleRole.data[i].type === this.join.picked){
                    money = this.currentUser.currentCircleRole.data[i].money
                }
            }

            b2DsBox.data = {
                'title':b2_global.js_text.circle.join_title,
                'order_type':'circle_join',
                'order_price':money,
                'order_key':this.join.picked,
                'post_id':this.circle.picked
            }
            b2DsBox.show = true
            return
        }
    },
    watch:{
        showPoBox(val){
            this.showPoBoxAc()
        }
    }
})

var b2CircleList = new Vue({
    el:'#circle-topic-list',
    data:{
        single:{
            is:false,
            id:0
        },
        people:{
            is:false,
            id:0
        },
        admin:{
            is:false,
            type:'topic',
            userList:''
        },
        edit:{
            is:false,
            topicId:0
        },
        cData:{
            picked:0,
            gc:0
        },
        topicAuthor:0,
        type:'all',
        circleData:'',
        circleId:0,
        paged:1,
        locked:false,
        reload:false,
        login:false,
        data:'',
        pages:1,
        circle:{
            picked:{
                type:'default',
                id:''
            },
            current:'',
            showBox:'default',
            created:{},
            join:{}
        },
        showJoin:false,
        topicFliter:{
            show:false,
            type:'all',
            orderBy:'date',
            role:'all',
            file:'all',
            best:0
        },
        video:{
            index:0,
            id:0,
            action:false
        },
        hiddenIndex:'',
        image:{
            showImageLightBox:false,
            index:0
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
        opt:{
            topicId:'',
            pages:0,
            paged:1,
            orderBy:'ASC',
            status:'publish'
        },
        smileShow:false,
        answer:{
            id:'',
            index:'',
            showSmile:false,
            uploadType:'',
            listParent:'',
            locked:false,
            image:{
                id:'',
                progress:0,
                success:false,
                url:'',
                ext:'',
                size:'',
                name:''
            },
            file:{
                url:'',
                ext:'',
                size:'',
                name:'',
                id:'',
                progress:'',
                success:false
            },
            content:'',
            list:'',
            opt:{
                paged:1,
                api:'getTopicAnswerList',
                pages:1,
                topicId:0
            },
            listHeight:0,
            readAnswer:'',
            answerRightLocked:false
        }
    },
    mounted(){
        this.people.is = document.querySelector('#circle-people')
        if(this.people.is){
            this.topicAuthor = document.querySelector('.c-p-top').getAttribute('data-people')
            this.showMyTopic(this.topicAuthor)
            this.circle.current = 'my'
        }
        this.single.is = this.$refs.circleSingle
        this.admin.is = this.$refs.circleAdmin
        this.edit.is = document.querySelector('.circle-topic-edit')

        if(this.single.is && b2CirclePostBox){
            b2CirclePostBox.single.is = true
            this.single.id = this.$refs.circleSingle.getAttribute('data-id')
            b2CirclePostBox.circle.picked = this.$refs.circleSingle.getAttribute('data-circleId')
            b2CirclePostBox.getCurrentUserCircleData()
        }else if(this.admin.is){
            this.circleId = b2GetQueryVariable('circle_id')
            this.opt.status = 'pending';
        }else if(!this.people.is){
            if(!this.$refs.topicForm) return
            this.circleId = document.querySelector('.po-topic-textarea').getAttribute('data-circle')
        }

        if(b2token){
            this.login = true
        }else{
            this.login = false
        }
  
        if(!this.single.is && !this.admin.is && !this.people.is){
            this.cData.picked = document.querySelector('.po-topic-textarea').getAttribute('data-circle')
            this.cData.gc = parseInt(document.querySelector('.po-topic-textarea').getAttribute('data-gc'))

            if(this.cData.picked == this.cData.gc){
                this.circle.current = 'default'
            }
        }
        
        this.getTopicList()
        autosize(this.$refs.topicForm)
        autosize(this.$refs.topicAnswer)
        var clipboard = new ClipboardJS('.fuzhi');
        clipboard.on('success', e=>{
            Qmsg['success'](b2_global.js_text.global.copy_success,{html:true});
        });
        clipboard.on('error', e=> {
            Qmsg['warning'](b2_global.js_text.global.copy_select,{html:true});
        });
    },
    created() {
        window.addEventListener('scroll', this.scroll)
    },
    computed:{
        voteText(){
            return (ti,index)=>{
                let data = this.data[ti].data.data.list
                let total = 0
                for (let i = 0; i < data.length; i++) {
                    total += data[i].vote;
                }
    
                return Math.round(data[index].vote/total*100)+'%'
            }
            
        },
        userData(){
            return this.$store.state.userData;
        }
    },
    methods:{
        gifAction(){
            image = document.querySelectorAll(".circle-gif ")
            for (let i = 0; i < image.length; i++) {
                image[i].stop();
                
            }
        },
        loginAc(type){
            login.show = true
            login.loginType = type
        },
        scroll(){
            if(this.single.is) return
            if(this.data == '') return
            //文档内容实际高度（包括超出视窗的溢出部分）
            var scrollHeight = Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);
            //滚动条滚动距离
            var scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
            //窗口可视范围高度
            var clientHeight = window.innerHeight || Math.min(document.documentElement.clientHeight,document.body.clientHeight);
            
            if(clientHeight + scrollTop >= (scrollHeight - 850) && !this.locked){
                this.getTopicListMore()
            }
        },
        showCircleListBox(type){
            if(!b2token){
                login.show = true
                login.loginType = 1
                return
            }
            this.topicFliter.show = false
            if(this.circle.showBox == type){
                this.circle.current = this.circle.picked.type
                this.circle.showBox = ''
            }else{
                this.circle.current = type
                this.circle.showBox = type
            }
        },
        topicVote(ti,index){
            if(!b2token){
                login.loginType = 1
                login.show = true
                return
            }
            let topicId = this.data[ti].topic_id
            axios.post(b2_rest_url+'topicVote','topic_id='+topicId+'&index='+index).then(res=>{
                if(res.data){
                    this.$set(this.data[ti],'data',res.data)
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        voteCurrent(ti,index){
            index = index.toString()
            return this.data[ti].data.data.current.indexOf(index) > -1 ? true : false
        },
        voteRadioPicked(ti){
            if(!b2token){
                login.loginType = 1
                login.show = true
                return
            }
            let topicId = this.data[ti].topic_id
            let index = this.data[ti].data.data.picked
            axios.post(b2_rest_url+'topicVote','topic_id='+topicId+'&index='+index).then(res=>{
                if(res.data){
                    this.$set(this.data[ti],'data',res.data)
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        guessPicked(ti,index){
            if(this.data[ti].data.data.answer || this.data[ti].data.data.answer === 0) return
            this.$set(this.data[ti].data.data,'picked',index)
        },
        guessAction(ti){
            if(!b2token){
                login.loginType = 1
                login.show = true
                return
            }
            
            if(!this.data[ti].data.data.picked && this.data[ti].data.data.picked !== 0) return
            let topicId = this.data[ti].topic_id
            let index = this.data[ti].data.data.picked
            axios.post(b2_rest_url+'topicGuess','topic_id='+topicId+'&index='+index).then(res=>{
                if(res.data){
                    this.$set(this.data,ti,res.data)
                    this.$nextTick(()=>{
                        document.querySelector('.circle-topic-item-'+topicId).className += ' new-topic'
                    })
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        showMyTopic(id){
            if(!b2token && !id){
                login.show = true
                login.loginType = 1
                return
            }
            this.setCommentBox()
            this.resetCommentBox()

            if(id){
                this.topicAuthor = id
            }else{
                this.topicAuthor = this.userData.id
            }
            
            this.paged = 1
            this.getTopicList()
        },
        pickedType(type){
            if(b2CirclePostBox && b2CirclePostBox.locked) return
            this.topicFliter.type = type
            this.setCommentBox()
            this.resetCommentBox()
            this.paged = 1
            this.getTopicList()
        },
        pickedOrder(type){
            if(b2CirclePostBox && b2CirclePostBox.locked) return
            this.topicFliter.orderBy = type
            this.setCommentBox()
            this.resetCommentBox()
            this.paged = 1
            this.getTopicList()
        },
        pickedRole(type){
            if(b2CirclePostBox && b2CirclePostBox.locked) return
            this.topicFliter.role = type
            this.setCommentBox()
            this.resetCommentBox()
            this.paged = 1
            this.getTopicList()
        },
        pickedFile(type){
            if(b2CirclePostBox && b2CirclePostBox.locked) return
            this.topicFliter.file = type
            this.setCommentBox()
            this.resetCommentBox()
            this.paged = 1
            this.getTopicList()
        },
        pickedCircle(type,id){
            if(b2CirclePostBox && b2CirclePostBox.locked) return
            this.topicAuthor = 0
            this.circle.picked.type = type
            this.circle.picked.id = id
            this.circleId = id
            this.circle.showBox = false
            this.circle.current = type
            this.paged = 1
            this.resetFliter()
            this.setCommentBox()
            this.resetCommentBox()
            this.getTopicList()
            b2CirclePostBox.circle.picked = id
            b2CirclePostBox.circle.show = false
            if(typeof b2recommendedCircle !== undefined){
                if(b2recommendedCircle.current != id){
                    b2recommendedCircle.current = id
                }
            }
            if(type != 'widget'){
                b2CirclePostBox.getCurrentUserCircleData()
                window.history.pushState(id, b2CirclePostBox.circle.list[id].name, b2CirclePostBox.circle.list[id].link)
                document.title = b2CirclePostBox.circle.list[id].name+' '+b2_global.site_separator+' '+b2_global.site_name
            }
            if(this.circle.picked.id == this.cData.gc){
                this.circle.current = 'default'
            }
        },
        resetFliter(){
            this.topicFliter.show = false
            this.topicFliter.type ='all'
            this.topicFliter.orderBy ='date'
            this.topicFliter.role ='all'
            this.topicFliter.file ='all'
        },
        getTopicListMore(){
            if(this.paged > this.pages) return
            if(this.reload === true) return
            this.reload = true
            this.paged++

            let data = {
                'paged':this.paged,
                'circle_id':this.topicAuthor ? 0 : this.circleId,
                'type':this.topicFliter.type,
                'order_by':this.topicFliter.orderBy,
                'role':this.topicFliter.role,
                'file':this.topicFliter.file,
                'status':this.admin.is ? 'pending' : '',
                'author':this.topicAuthor
            }

            this.$http.post(b2_rest_url+'getTopicList',Qs.stringify(data)).then(res=>{
                if(res.data.data.length > 0){
                    let length = this.data.length
                    for (let i = 0; i < res.data.data.length; i++) {
                        this.$set(this.data,length+i,res.data.data[i])
                    }
                }

                this.reload = false
                this.locked = false
                this.$nextTick(()=>{
                    b2SidebarSticky()
                    lazyLoadInstance.update()
                    this.gifAction()
                })
               
            }).catch(err=>{
                this.reload = false
                this.locked = false
            })
        },
        getData(){
            this.$http.post(b2_rest_url+'getDataByTopicId','topic_id='+this.single.id).then(res=>{

                this.data = []
                this.$set(this.data,0,res.data)
                
                // const that = this
                // if(this.data[0].attachment.image.length > 0){
                //     this.data[0].attachment.image.forEach((element,index) => {
                //         var img = new Image();
                //         img.src = element.big_thumb;
                //         img.onload = function(){

                //             that.$set(that.data[0].attachment.image[index],'big_ratio',img.height/img.width)
                //             console.log(img.height/img.width)

                //         };
                //     });
                // }
                this.$nextTick(()=>{
                    lazyLoadInstance.update()
                    b2SidebarSticky()
                    if(res.data.data.type !== 'ask'){
                        this.showComment(0)
                    }else{
                        this.resetAnswerList(0)
                    }
                    
                })
               
            })
        },
        getTopicList(){

            if(this.edit.is) return

            if(this.single.is){
                this.getData()
                return
            }

            if(this.locked === true) return
            this.locked = true

            let data = {
                'paged':this.paged,
                'circle_id':this.topicAuthor ? 0 : this.circleId,
                'type':this.topicFliter.type,
                'order_by':this.topicFliter.orderBy,
                'role':this.topicFliter.role,
                'file':this.topicFliter.file,
                'status':this.admin.is ? 'pending' : '',
                'author':this.topicAuthor
            }

            //this.topicFliter.show = false

            this.$http.post(b2_rest_url+'getTopicList',Qs.stringify(data)).then(res=>{

                this.data = res.data.data
                this.pages = res.data.pages
                
                this.locked = false
                this.reload = false
                this.$nextTick(()=>{
                    this.$refs.listGujia.style.display = 'none'
                    b2SidebarSticky()
                    lazyLoadInstance.update()
                })
               
            }).catch(err=>{
                this.locked = false
                this.reload = false
            })
        },
        play(ti,id,index){
            if(this.video.index === index && this.video.id === id){
                let video = document.querySelector('#video'+id+'i'+index)
                if(!this.video.action){
                    video.volume  = 0.5;
                    this.$set(this.data[ti].attachment.video[index],'show',true)
                    video.play()
                    
                    this.video.action = true
                }else{
                    video.pause()
                    this.video.action = false
                }
            }else{
                if(this.video.id){
                    document.querySelector('#video'+this.video.id+'i'+this.video.index).pause()
                }
                let video = document.querySelector('#video'+id+'i'+index)
                video.volume  = 0.5;
                this.$set(this.data[ti].attachment.video[index],'show',true)
                video.play()
                this.video.index = index
                this.video.id = id
                this.video.action = true
            }
        },
        watchVideo(video,id,index){
            video.addEventListener('ended', function () {  
                this.style.display = 'none';
            }, false);
        },
        playGif(ti,index){
  
            if(this.data[ti].attachment.image[index].play === 'play'){
                this.$set(this.data[ti].attachment.image[index],'play',false)
                this.$set(this.data[ti].attachment.image[index],'current',this.data[ti].attachment.image[index].gif_first)
            }else if(!this.data[ti].attachment.image[index].play){
                this.$set(this.data[ti].attachment.image[index],'play','loading')
                let img=new Image();
                img.onload=()=>{
                    this.$set(this.data[ti].attachment.image[index],'current',this.data[ti].attachment.image[index].thumb)
                    this.$set(this.data[ti].attachment.image[index],'play','play')
                };
                img.src=this.data[ti].attachment.image[index].thumb
            }
        },
        askContent(ti){
            let users = this.data[ti].data.data.users
            let usersHTML = '';
            if(users.length > 0){
                for (let i = 0; i < users.length; i++) {
                    usersHTML += '<a class="b2-color" href="'+users[i].link+'" target="_blank">'+users[i].name+'</a>，'
                }
                usersHTML = usersHTML.substr(0, usersHTML.length - 1)
            }else{
                usersHTML = '<b class="ask-all-users b2-color">'+b2_global.js_text.circle.all_users+'</b>'
            }

            return usersHTML
        },
        getLastItem(thePath){
            return new URL(thePath).pathname.split('/').filter(Boolean).pop().replace('.html','');
        },
        getQueryFromUrl(url){
            var str = url.split('?')[1];
 
            if(!str) return {}
            var keys = str.split('&');
            var obj = {};
            keys.forEach((item, idx, data)=>{
                var arr = item.split('=');
                obj[arr[0]] = arr[1];
            });
            return obj;
        },
        getVideoData(url){
            let type,id

            if(url.indexOf('bilibili.com')!==-1){
                type = 'bilibili'
            }else if(url.indexOf('v.qq.com')!==-1){
                type = 'qq'
            }

            id = this.getLastItem(url)
            p = this.getQueryFromUrl(url)['p']

            return {
                type:type,
                id:id,
                p:p ? p : 1
            }
        },
        getIframe(url){

            let data = this.getVideoData(url),
            iframe

            if(data.type == 'bilibili'){
                iframe = '<div class="social-video-box"><div class="social-video-box-in"><iframe class="lazy" data-src="//player.bilibili.com/player.html?bvid='+data.id+'&cid=&page='+data.p+'&high_quality=1&danmaku=0" scrolling="no" border="0" frameborder="no" framespacing="0" allowfullscreen="true"> </iframe></div></div>'
            }else if(data.type == 'qq'){
                iframe = '<div class="social-video-box"><div class="social-video-box-in"><iframe class="lazy" frameborder="0" data-src="//v.qq.com/txp/iframe/player.html?vid='+data.id+'" allowFullScreen="true"></iframe></div></div>'
            }
            
            return iframe
        },
        isImage(url){
            return /\.(jpg|jpeg|png|webp|avif|gif|svg)$/.test(url);
        },
        isVideo(url){
            return /\.(mp4|3gp|ogg)$/.test(url);
        },
        fliterContent(content,ti,item){

            let newContent = false

            var re = /(http:\/\/|https:\/\/)((\w|=|\?|\.|\/|&|-)+)/g;
            content = content.replace(re,(a)=>{
                    if(a.indexOf('https://www.bilibili.com/video/')!==-1 || a.indexOf('v.qq.com')!==-1){
                        newContent = true
                        return '\n'+this.getIframe(a)+'\n'
                    }

                    if(this.isImage(a)){
                        newContent = true
                        return '\n'+'<img src="'+a+'" />'+'\n'
                    }

                    if(this.isVideo(a)){
                        newContent = true
                        return '\n'+'<div class="social-video-box"><div class="social-video-box-in"><video class="lazy" controls data-src="'+a+'" objectfit="cover" x5-video-player-type="h5"></video></div></div>'+'\n'
                    }

                    return a

                });

        if(!newContent){
            if(!this.data[ti].full_content && !this.single.is){
                let length = parseInt(content.length)
                if(length > 200){
                    if(length/3 > 200){
                        length = 100
                    }else{
                        length = length/3
                    }
                    content = content.substring(0,length)+'...&nbsp;&nbsp; <button class="text" onclick="b2CircleList.showFullContent('+ti+')">'+b2_global.js_text.global.read_more+'<i class="b2font b2-jt-down"></i></button>'
                    return '<p><a href="'+item.link+'" target="_blank" class="link-block"></a>' + content + '</p>'
                }
            }
        }

        content = this.autoLink(content)

        if(!this.single.is){
            return '<p><a href="'+item.link+'" target="_blank" class="link-block"></a>' + content.replace(/\n*$/g, '').replace(/\n/g, '</p><p>') + '</p>'
        }
        return '<p>' + content.replace(/\n*$/g, '').replace(/\n/g, '</p><p>') + '</p>'
        
        },
        autoLink(text){
            var reg = /(http:\/\/|https:\/\/)((\w|=|\?|\.|\/|&|-)+)/g;
		    return text.replace(reg,(a)=>{
                if(!this.isImage(a) && !this.isVideo(a)){
                    return '<p><a href="'+a+'" target="_blank">'+a+'</a></p>'
                }
                return a
               
            }).replace(/\n/g, "<br />");
        },
        showFullContent(ti){
            this.$set(this.data[ti],'full_content',true);
        },
        showComment(ti,click){
            if(!this.changeTip() || this.admin.is) return

            const allow = !this.data[ti].role.in_circle && !this.data[ti].allow_read.allow

            if(allow){
                if(click){
                    Qmsg['warning'](b2_global.js_text.circle.join_comment,{html:true});
                }
                return
            }

            this.restScroll(ti)

            if(this.data[ti].topic_id == this.opt.topicId){
                this.opt.topicId = ''
                this.commentBox.index = ''
                this.setCommentBox()
            }else{
                this.opt.topicId = this.data[ti].topic_id
                this.commentBox.index = ti
                if(!this.data[ti].can_comment){
                    return
                }
                this.setCommentBox('#comment-box-'+this.opt.topicId)
                this.getCommentList()
            }
 
            this.resetComment()
        },
        answerRight(ti,answerId){
            if(this.answer.answerRightLocked === true) return
            this.answer.answerRightLocked = true

            this.$http.post(b2_rest_url+'answerRight','answerId='+answerId).then(res=>{
                this.$set(this.data[ti].data.data,'best',answerId)
                this.answer.answerRightLocked = false
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.answer.answerRightLocked = false
            })
        },
        showChildComment(index,parent){
            if(!this.changeTip() || this.admin.is) return
            
            this.setCommentBox('#comment-box-at-'+parent)
            this.commentBox.childIndex = index

            this.commentBox.parent = parent
            this.resetComment(true)
        },
        setCommentBox(where){
            if(this.admin.is || this.edit.is) return
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
            if(this.single.id) return
            if(this.commentBox.index !== '' && ti > this.commentBox.index){

                window.removeEventListener('scroll',window.bodyScrool,false)
                commentListHeight = document.querySelector('.circle-topic-item-'+this.opt.topicId+' .topic-comments').clientHeight
                window.scrollBy(0, -commentListHeight);
                setTimeout(() => {
                    window.addEventListener("scroll",window.bodyScrool , false);
                }, 500);
            }
        },
        resetCommentBox(){
            if(b2CirclePostBox && b2CirclePostBox.locked) return
            this.commentBox.index = ''
            this.commentBox.childIndex = ''
            this.commentBox.list = ''
            this.commentList.list = []
            this.commentList.height = 0
            this.resetAnswerList(this.answer.listParent)

            this.opt = {
                topicId:'',
                pages:0,
                paged:1,
                orderBy:'ASC',
                satatus:this.admin.is ? 'pending' : ''
            }
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
        getChildComments(index,parent){
            if(this.commentList.list[index].child_comments.locked === true) return
            this.$set(this.commentList.list[index].child_comments,'locked',true)

            this.$set(this.commentList.list[index].child_comments,'paged',this.commentList.list[index].child_comments.paged + 1)

            this.$http.post(b2_rest_url+'getChildComments','parent='+parent+'&paged='+this.commentList.list[index].child_comments.paged).then(res=>{

                this.commentList.list[index].child_comments.list = this.commentList.list[index].child_comments.list.concat(res.data.list)
                
                this.$set(this.commentList.list[index].child_comments,'locked',false)

                this.$nextTick(()=>{
                    this.rebuildZoom()
                    lazyLoadInstance.update()
                })
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.$set(this.commentList.list[index].child_comments,'locked',false)
            })
        },
        rebuildZoom(){
            let imgList = document.querySelectorAll('#comment-list-'+this.opt.topicId+' .topic-commentlist-img-box img')

            for (let index = 0; index < imgList.length; index++) {
                // var img = new Image();
                // img.src = imgList[index].getAttribute('data-original');
                // img.onload = function(){

                //     imgList[index].setAttribute('data-zooming-width',img.width)
                //     imgList[index].setAttribute('data-zooming-height',img.height)

                // };
                b2zoom.listen(imgList[index]);
            }
        },
        restCommontScroll(){
            if(this.single.id) return
            let commentListHeight = document.querySelector('#comment-list-'+this.opt.topicId).clientHeight

            //if(commentListHeight > this.commentList.height) return

            window.removeEventListener('scroll',window.bodyScrool,false)
            window.scrollBy(0, commentListHeight - this.commentList.height);
            setTimeout(() => {
                window.addEventListener("scroll",window.bodyScrool , false);
            }, 500);
        },
        changeTip(){
            if(this.single.is) return true
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
        commentDisabled(){
            if(this.commentBox.locked == true || this.commentBox.imageLocked == true) return true
            return false
        },
        afterCommentGetData(index,id,type){
            this.$http.post(b2_rest_url+'getDataByTopicId','topic_id='+id).then(res=>{
                this.$set(this.data,index,res.data)
                this.$nextTick(()=>{
                    document.querySelector('.circle-topic-item-'+id).classList += ' new-comment'
                    if(type === 'ask'){
                        this.getAnswerList(index)
                    }
                    b2SidebarSticky()
                    lazyLoadInstance.update()
                })
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
                }
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
                    if(this.data[this.commentBox.index].allow_read.type === 'comment' && this.data[this.commentBox.index].allow_read.allow === false){
                        this.afterCommentGetData(this.commentBox.index,this.opt.topicId)
                    }

                    lazyLoadInstance.update()
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
        rebuildAnswerZoom(){
            let imgList = document.querySelectorAll('#ask-list-'+this.answer.listParent+' .topic-commentlist-img-box img')
            for (let index = 0; index < imgList.length; index++) {
                // var img = new Image();
                // img.src = imgList[index].getAttribute('data-original');
                // img.onload = function(){

                //     imgList[index].setAttribute('data-zooming-width',img.width)
                //     imgList[index].setAttribute('data-zooming-height',img.height)

                // };
                b2zoom.listen(imgList[index]);
            }
        },
        answerFileUpload(e,type){
            if(type === 'image'){
                if(this.answer.image.id){
                    if(!confirm(b2_global.js_text.circle.answer_image)){
                        e.preventDefault();
                    }
                }
            }else if(type === 'file'){
                if(this.answer.file.id){
                    if(!confirm(b2_global.js_text.circle.answer_file)){
                        e.preventDefault();
                    }
                }
            }

        },
        cleanAnswerFile(type){
            if(!confirm(b2_global.js_text.circle['remove_'+type])) return
            if(type === 'image'){
                this.$set(this.answer,'image',{
                    id:'',
                    progress:0,
                    success:false,
                    url:'',
                    ext:'',
                    size:'',
                    name:''
                })
                document.querySelector('#answerImageInput').value = null
            }else{
                this.$set(this.answer,'file',{
                    ext:'',
                    size:'',
                    name:'',
                    id:'',
                    url:'',
                    progress:'',
                    success:false
                })
                document.querySelector('#answerFileInput').value = null
            }
        },
        cleanAnswerSubmit(){
            this.$set(this.answer,'file',{
                ext:'',
                size:'',
                name:'',
                id:'',
                url:'',
                progress:'',
                success:false
            })
            this.$set(this.answer,'image',{
                id:'',
                progress:0,
                success:false,
                url:'',
                ext:'',
                size:'',
                name:''
            })
            this.uploadType = ''
            this.$refs.topicAnswer.value = ''
        },
        submitAnswer(){
            if(this.answer.locked == true) return
            this.answer.locked = true
            let data = {
                'parent':this.data[this.answer.listParent].topic_id,
                'content':this.$refs.topicAnswer.value,
                'image':this.answer.image.id,
                'file':this.answer.file.id,
                'id':this.answer.id
            }

            this.$http.post(b2_rest_url+'submitAnswer',Qs.stringify(data)).then(res=>{

                if(!this.answer.id){
                    if(this.answer.list == ''){
                        this.answer.list = []
    
                        this.$set(this.data[this.answer.listParent].data.data,'answer_count',1)
                        this.$set(this.answer.list,0,res.data)
                    }else{
                        this.answer.list.unshift(res.data)
                    }
                }else{
                    this.$set(this.answer.list,this.answer.index,res.data)
                    this.canelEdit()

                    Qmsg['success'](b2_global.js_text.global.edit_success,{html:true});
                }
                
                this.$nextTick(()=>{
                    b2SidebarSticky()
                    document.querySelector('#ask-list-'+this.answer.listParent+' ul li:first-child').classList += ' new-comment'
                    lazyLoadInstance.update()
                    this.rebuildAnswerZoom()
                })
                
                this.cleanAnswerSubmit()
                this.answer.locked = false
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.answer.locked = false
            })
        },
        answerVote(index,type,topic_id){
            if(!b2token){
                login.loginType = 1
                login.show = true
                return
            }

            if(this.answer.list[index].vote.locked === true) return
            this.answer.list[index].vote.locked = true

            this.$http.post(b2_rest_url+'postVote','type='+type+'&post_id='+topic_id).then(res=>{

                this.$set(this.answer.list[index].vote,'up',this.answer.list[index].vote.up + res.data.up)
                this.$set(this.answer.list[index].vote,'down',this.answer.list[index].vote.down + res.data.down)

                if(res.data.up > 0){
                    this.$set(this.answer.list[index].vote,'isset_up',true)
                }else{
                    this.$set(this.answer.list[index].vote,'isset_up',false)
                }

                if(res.data.down > 0){
                    this.$set(this.answer.list[index].vote,'isset_down',true)
                }else{
                    this.$set(this.answer.list[index].vote,'isset_down',false)
                }

                this.$set(this.answer.list[index].vote,'locked',false)
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.$set(this.answer.list[index].vote,'locked',false)
            })
        },
        readAnswerPay(){
            if(!b2token){
                login.show = true
            }else{
                let type = this.data[this.answer.listParent].data.data.reward
                if(type === 'money'){
                    b2DsBox.data = {
                        'title':b2_global.js_text.circle.read_answer,
                        'order_type':'circle_read_answer_pay',
                        'order_price':this.data[this.answer.listParent].data.data.pay_read,
                        'post_id':this.data[this.answer.listParent].topic_id
                    }
                    b2DsBox.show = true
                }else{
                    payCredit.data = {
                        'title':b2_global.js_text.circle.read_answer,
                        'order_price':this.data[this.answer.listParent].data.data.pay_read,
                        'order_type':'circle_read_answer_pay',
                        'post_id':this.data[this.answer.listParent].topic_id
                    }
                    payCredit.show = true;
                }
                b2PayCheck.payType = 'ask'
            }
        },
        hiddenContentPay(index){
            if(!b2token){
                login.show = true
            }else{
                let type = this.data[index].allow_read.type
                this.hiddenIndex = index
                if(type === 'credit'){
                    payCredit.data = {
                        'title':b2_global.js_text.circle.hidden_pay+this.data[index].title,
                        'order_price':this.data[index].allow_read.data,
                        'order_type':'circle_hidden_content_pay',
                        'post_id':this.data[index].topic_id
                    }
                    payCredit.show = true;
                }else if(type === 'money'){
                    b2DsBox.data = {
                        'title':b2_global.js_text.circle.hidden_pay+this.data[index].title,
                        'order_type':'circle_hidden_content_pay',
                        'order_price':this.data[index].allow_read.data,
                        'post_id':this.data[index].topic_id
                    }
                    b2DsBox.show = true
                }

                b2PayCheck.payType = 'hidden'
            }
        },
        fliterAnswer(content,ai){

            if(!this.answer.list[ai].full_answer){
                let length = parseInt(content.length)
                if(length > 200){
                    if(length/3 > 200){
                        length = 200
                    }else{
                        length = length/3
                    }
                    content = content.substring(0,length)+'...&nbsp;&nbsp; <button class="text" onclick="b2CircleList.showFullAnswer('+ai+')">'+b2_global.js_text.global.read_more+'<i class="b2font b2-arrow-down-s-line"></i></button>'
                    return '<p>' + content + '</p>'
                }
            }

            content = this.autoLink(content)

            return '<p>' + content.replace(/\n*$/g, '').replace(/\n/g, '</p><p>') + '</p>'
        },
        showFullAnswer(ai){
            this.$set(this.answer.list[ai],'full_answer',true)
        },
        getAnswerFile(event,type){

            let file = event.target.files[0]
            if(!file || this.answer.uploadType !=='') return

            this.answer.uploadType = type

            this.answer[type].ext = b2CirclePostBox.fileExists(file.name)
            this.answer[type].size = b2CirclePostBox.readablizeBytes(file.size)
            this.answer[type].name = file.name

            if(type === 'image'){
                this.answer.image.url = window.URL.createObjectURL(file)
            }

            let formData = new FormData()

            formData.append('file',file,file.name)
            formData.append("post_id", this.data[this.answer.listParent].topic_id)
            formData.append("type", 'circle')

            let config = {
                onUploadProgress: progressEvent=>{
                    this.answer[type].progress = progressEvent.loaded / progressEvent.total * 100 | 0
                }
            }

            this.$http.post(b2_rest_url+'fileUpload',formData,config).then(res=>{

                if(type === 'image'){
                    this.answer.image.url = res.data.url
                    this.answer.image.id = res.data.id
                }else{
                    this.answer.file.url = res.data.url
                    this.answer.file.id = res.data.id
                }

                this.answer[type].success = false
                this.answer.uploadType = ''
                this.answer[type].progress = 100
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.answer[type].success = false
                this.answer.uploadType = ''
                this.answer[type].progress = 100
            })
        },
        reSetAnswerHeight(ti){
            let answerBox = document.querySelector('#answer-box-'+this.answer.listParent)
            if(!answerBox) return
            if(this.answer.listParent < ti){
                window.removeEventListener('scroll',window.bodyScrool,false)
                commentListHeight = answerBox.clientHeight + 10
                window.scrollBy(0, -commentListHeight);
                setTimeout(() => {
                    window.addEventListener("scroll",window.bodyScrool , false);
                }, 500);
            }
        },
        canAnswer(ti){
            return this.data[ti].data.data.end_time != -1 && this.data[ti].data.data.can_answer
        },
        getMoreAnswers(data){
            this.answer.listHeight = document.querySelector('#ask-list-'+this.answer.listParent).clientHeight
            this.answer.list = data.list
            
            this.$nextTick(()=>{
                this.resetAnswerListPageNav()
                this.rebuildAnswerZoom()
            })
        },
        resetAnswerListPageNav(){
            let commentListHeight = document.querySelector('#ask-list-'+this.answer.listParent).clientHeight
            if(commentListHeight > this.answer.listHeight) return
            window.removeEventListener('scroll',window.bodyScrool,false)
            window.scrollBy(0, commentListHeight - this.answer.listHeight);
            setTimeout(() => {
                window.addEventListener("scroll",window.bodyScrool , false);
            }, 500);
        },
        resetAnswerList(ti){
            if(this.edit.is) return
            if(ti !== this.answer.listParent){
                this.reSetAnswerHeight(ti)
                this.answer.listParent = ti
                this.answer.list = ''
                this.answer.opt.paged = 1
                this.answer.opt.pages = 1
                this.$nextTick(()=>{
                    if(this.canAnswer(ti)){
                        document.querySelector('#ask-box-'+ti).appendChild(this.$refs.answerBox)
                    }
                    this.getAnswerList(ti)
                })
                
            }else if(!this.admin.is){
                document.querySelector('#topic-answer').appendChild(this.$refs.answerBox)
                this.answer.listParent = ''
                this.answer.list = ''
                this.answer.opt.paged = 1
                this.answer.opt.pages = 1
            }
            this.answer.listHeight = 0
        },
        getAnswerList(ti){
            if(this.data[ti].data.type !== 'ask') return
            if(this.data[ti].data.data.answer_count <= 0) return
            
            this.answer.opt.topicId = this.data[ti].topic_id
            this.$http.post(b2_rest_url+'getTopicAnswerList','topicId='+this.data[ti].topic_id+'&paged='+this.answer.opt.paged).then(res=>{
                if(res.data === false){
                    this.answer.readAnswer = false
                }else{
                    this.answer.list = res.data.list
                    this.answer.opt.pages = res.data.pages
                    this.$nextTick(()=>{
                        this.answer.listHeight = document.querySelector('#ask-list-'+this.answer.listParent).clientHeight
                        this.rebuildAnswerZoom()
                        lazyLoadInstance.update()
                    })
                }
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        canelEdit(){
            this.answer.id = '' 
            this.answer.index = ''
            this.cleanAnswerSubmit()
        },
        deleteAnswer(index,id){
            if(!confirm(b2_global.js_text.circle.delete_answer)) return
            this.$http.post(b2_rest_url+'deleteAnswer','answerId='+id).then(res=>{
                Qmsg['success'](b2_global.js_text.circle.delete_success,{html:true});

                this.answer.list.splice(index,1)
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        editAnswer(index,id){
            this.answer.id = id
            this.answer.index = index

            //this.data[this.answer.listParent].topic_id,
            this.$refs.topicAnswer.value = this.answer.list[index].content
            if(this.answer.list[index].image){
                this.answer.image.url = this.answer.list[index].image.full
                this.answer.image.id = this.answer.list[index].image.id
                this.answer.image.ext = b2CirclePostBox.fileExists(this.answer.image.url)
                this.answer.image.size = b2CirclePostBox.readablizeBytes(this.answer.list[index].image.size)
                this.answer.image.name = this.answer.image.url
            }
            if(this.answer.list[index].file){
                this.answer.file.url = this.answer.list[index].file.link
                this.answer.file.id = this.answer.list[index].file.id
                this.answer.file.ext = this.answer.list[index].file.ext
                this.answer.file.size = b2CirclePostBox.readablizeBytes(this.answer.list[index].file.size)
                this.answer.file.name = this.answer.list[index].file.name
            }
            
            this.$scrollTo('.topic-ask-box', 300, {offset: -200})
                // 'image':this.answer.image.id,
                // 'file':this.answer.file.id
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
        postVote(index,type,topic_id){
            if(!b2token){
                login.loginType = 1
                login.show = true
                return
            }

            if(this.data[index].meta.vote.locked === true) return
            this.data[index].meta.vote.locked = true

            this.$http.post(b2_rest_url+'postVote','type='+type+'&post_id='+topic_id).then(res=>{

                this.$set(this.data[index].meta.vote,'up',this.data[index].meta.vote.up + res.data.up)
                this.$set(this.data[index].meta.vote,'down',this.data[index].meta.vote.down + res.data.down)

                if(res.data.up > 0){
                    this.$set(this.data[index].meta.vote,'isset_up',true)
                }else{
                    this.$set(this.data[index].meta.vote,'isset_up',false)
                }

                if(res.data.down > 0){
                    this.$set(this.data[index].meta.vote,'isset_down',true)
                }else{
                    this.$set(this.data[index].meta.vote,'isset_down',false)
                }

                this.$set(this.data[index].meta.vote,'locked',false)
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.$set(this.data[index].meta.vote,'locked',false)
            })
        },
        setSticky(topic_id,index){
            this.$http.post(b2_rest_url+'setSticky','topic_id='+topic_id).then(res=>{

                if(res.data === false){
                    this.$set(this.data[index],'sticky',0)
                    Qmsg['warning'](b2_global.js_text.circle.set_sticky_fail,{html:true});
                }else{
                    this.$set(this.data[index],'sticky',1)
                    Qmsg['success'](b2_global.js_text.circle.set_sticky_success,{html:true});
                }

            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        setBest(topic_id,index){
            this.$http.post(b2_rest_url+'setBest','topic_id='+topic_id).then(res=>{

                if(res.data === false){
                    this.$set(this.data[index],'best',0)
                    Qmsg['warning'](b2_global.js_text.circle.set_best_fail,{html:true});
                }else{
                    this.$set(this.data[index],'best',1)
                    Qmsg['success'](b2_global.js_text.circle.set_best_success,{html:true});
                }

            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        isAdmin(){
            return typeof b2CirclePostBox !== 'undefined' && (b2CirclePostBox.currentUser.isCircleAdmin || b2CirclePostBox.currentUser.isAdmin);
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
        deleteTopic(index,topic_id){
            if(!confirm(b2_global.js_text.circle.delete_confirm)) return

            this.$http.post(b2_rest_url+'deleteTopic','topic_id='+topic_id).then(res=>{
                if(res.data == true){

                    this.setCommentBox()
                    this.resetCommentBox()

                    Qmsg['success'](b2_global.js_text.circle.delete_success,{html:true});

                    this.data.splice(index,1)
                }else{

                    Qmsg['warning'](b2_global.js_text.circle.delete_fail,{html:true});
                }
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        topicChangeStatus(index,topic_id){
            this.$http.post(b2_rest_url+'topicChangeStatus','topic_id='+topic_id).then(res=>{
                if(res.data){

                    Qmsg['success'](b2_global.js_text.circle.status_success,{html:true});

                    this.data.splice(index,1)
                }else{

                    Qmsg['warning'](b2_global.js_text.circle.status_fail,{html:true});
                }
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        showImageLight(ti,index){
            
            this.$set(this.data[ti].attachment,'Showimage',true)
            this.$set(this.data[ti].attachment,'imageIndex',index)
        },
        closeImageBox(ti){
            this.$set(this.data[ti].attachment,'Showimage',false)
        },
        showImageBox(event,ti){
            let current = event.currentTarget
            const zooming = new Zooming()
            const img = current.nextSibling.nextSibling
            
            // var _img = new Image();
            // _img.src = img.src;
            // _img.onload = function(){
            //     console.log(_img)
            //     img.setAttribute('data-zooming-width',_img.width)
            //     img.setAttribute('data-zooming-height',_img.height)
            // };

            zooming.open(img)
        },
        getRotationAngle(target){
            const obj = window.getComputedStyle(target, null);
            const matrix = obj.getPropertyValue('-webkit-transform') || 
                obj.getPropertyValue('-moz-transform') ||
                obj.getPropertyValue('-ms-transform') ||
                obj.getPropertyValue('-o-transform') ||
                obj.getPropertyValue('transform');

            let angle = 0; 

            if (matrix !== 'none') 
            {
                const values = matrix.split('(')[1].split(')')[0].split(',');
                const a = values[0];
                const b = values[1];
                angle = Math.round(Math.atan2(b, a) * (180/Math.PI));
            } 

            return (angle < 0) ? angle +=360 : angle;
        },
        rotate(type,ti){
            
            let id = this.data[ti].topic_id
            let box = document.querySelector('.circle-topic-item-'+id+' .image-show')
   
            //容器宽度
            let w = box.offsetWidth

            //当前状态
            let status = box.querySelector('.box-in').style.paddingTop

            let big_ratio = this.data[ti].attachment.image[this.data[ti].attachment.imageIndex].big_ratio
            let left_ratio = (1/big_ratio).toFixed(5)

            let _h,_w

            if(status == Calc.Mul(big_ratio,100)+'%'){
                box.querySelector('.box-in').style.paddingTop = Calc.Mul(left_ratio,100)+'%'
                _h = w
                _w = w/big_ratio
                
            }else{
                box.querySelector('.box-in').style.paddingTop = Calc.Mul(big_ratio,100)+'%'
                _w = w
                _h = w*big_ratio
            }

            let ratio = this.getRotationAngle(box.querySelector('img'))

            if(type === 'right'){
                ratio = ratio+90
            }else{
                ratio = ratio-90
            }

            if(ratio >= 360 || ratio <= -360) {
                ratio = 0
                box.querySelector('.box-in img').style.transform = 'none'
                // box.querySelector('.zoom-img').style.transform = 'none'
                
            }else{
                box.querySelector('.box-in img').style.transform = 'rotate('+ratio+'deg) '+this.reTransform(ratio)
                // box.querySelector('.zoom-img').style.transform = 'rotate('+ratio+'deg) '+this.reTransform(ratio)
            }

            
            box.querySelector('.box-in img').style.width = _w+'px'
            box.querySelector('.box-in img').style.height = _h+'px'

            // box.querySelector('.zoom-img').style.height = _h+'px'
            // box.querySelector('.zoom-img').style.width = _w+'px'
        },
        reTransform(ratio){
            if(ratio == 90){
                return 'translate(0, -100%)'
            }

            if(ratio == -90){
                return 'translate(-100%, 0)'
            }

            if(ratio == -180 || ratio == 180){
                return 'translate(-100%, -100%)'
            }

            if(ratio == 270){
                return 'translate(-100%, 0)'
            }
            
            if(ratio == -270){
                return 'translate(0, -100%)'
            }

            return 'translate(0, 0)'
        },
        imageNav(type,ti){
            let index = this.data[ti].attachment.imageIndex

            if(type === 'prev'){
                if(index == 0){
                    index = this.data[ti].attachment.image.length - 1
                }else{
                    index = index - 1
                }
            }else{
                if(index >= this.data[ti].attachment.image.length - 1){
                    index = 0
                }else{
                    index = index + 1
                }
            }

            this.$set(this.data[ti].attachment,'imageIndex',index)
        },
        answerAddSmile(val){
            grin(val,this.$refs.topicAnswer)
            this.answer.showSmile = false
        },
        jionCircleAction(item){
            if(!b2token){
                login.loginType = 1
                login.show = true
                return
            }
            this.showJoin = true
            return
            if(this.single.is){
                window.open(item.circle.link, '_blank');
            }else{
                this.showJoin = true
                //this.$scrollTo('.site', 300, {offset: 0})
            }
        },
        readablizeBytes(size){
            return b2CirclePostBox.readablizeBytes(size)
        }
    },
    watch:{
        smileShow(val){
            if(val){
                this.commentBox.showImgBox = false
            }else if(this.commentBox.img){
                this.commentBox.showImgBox = true
            }
        },
        // 'admin.type':{
        //     handler(newVal, oldVal) {
        //         // if(newVal == 'users' && admin.userList === ''){
        //         //     this.getAdminList(this.circleId)
        //         // }
        //     },
        //     immediate: true,
        // }
    }
})

var b2CreateCircle = new Vue({
    el:'#create-circle',
    data:{
        tags:{
            status:'edit',
            picked:''
        },
        pay:{
            status:'hidden',
            type:'free',
        },
        role:{
            status:'hidden',
            money:{
                permanent:'',
                year:'',
                halfYear:'',
                season:'',
                month:'',
            },
            join:'free',
            lv:[]
        },
        read:'public',
        info:{
            status:'hidden',
            icon:'',
            cover:'',
            name:'',
            desc:'',
            slug:'',
            iconUpload:false,
            coverUpload:false
        },
        other:{
            status:'hidden'
        },
        locked:false
    },
    methods:{
        checkForm(type,ret){
            let status = false,text = ''
            if(type === 'pay'){
                if(!this.pay.type){
                    status = true
                    text = b2_global.js_text.circle.create_circle_pay_error
                }
            }else if(type === 'role'){
                if(this.pay.type === 'money'){
                    if(this.role.money.permanent === '' && 
                    this.role.money.year === '' &&
                    this.role.money.halfYear === '' &&
                    this.role.money.season === '' &&
                    this.role.money.month === ''
                    ){
                        status = true
                        text = b2_global.js_text.circle.create_circle_role_money_error
                    }
                }else if(this.pay.type === 'lv'){
                    if(this.role.lv.length <= 0){
                        status = true
                        text = b2_global.js_text.circle.create_circle_role_lv_error
                    }
                }else{
                    if(!this.role.join){
                        status = true
                        text = b2_global.js_text.circle.create_circle_role_join_error
                    }
                }
            }else if(type === 'info'){
                if(this.info.icon == '' || this.info.name == '' || this.info.desc == ''){
                    status = true
                    text = b2_global.js_text.circle.create_circle_info_error
                }else if(this.info.name.length < 2 || this.info.name.length > 20){
                    status = true
                    text = b2_global.js_text.circle.create_circle_info_name_error
                }else if(this.info.desc.length < 10 || this.info.desc.length > 100){
                    status = true
                    text = b2_global.js_text.circle.create_circle_info_desc_error
                }
            }
            if(status){
                if(!ret){
 
                    Qmsg['warning'](text,{html:true});
                }
                return false
            }

            return true
        },
        getFile(event,type){
            let file = event.target.files[0];
            if(!file) return;

            this.info[type+'Upload'] = true

            let formData = new FormData()

            formData.append('file',file,file.name)
            formData.append("post_id", 1)
            formData.append("type", 'circle')

            this.$http.post(b2_rest_url+'fileUpload',formData).then(res=>{

                this.info[type] = res.data.url

                this.info[type+'Upload'] = false
                this.$refs[type+'Input'].value = null
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.info[type+'Upload'] = false
                this.$refs[type+'Input'].value = null
            })
        },
        submit(){

            if(!this.checkForm('info')) return
            if(this.locked === true) return
            this.locked = true

            let data = {
                tags:this.tags.picked,
                pay:this.pay,
                role:this.role,
                info:this.info,
                read:this.read
            }

            this.$http.post(b2_rest_url+'createCircle',Qs.stringify(data)).then(res=>{
                window.location.href = res.data.link
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});

                this.locked = false
            })
        }
    }
})

//所有圈子页面
var b2AllCircle = new Vue({
    el:'#all-circles',
    data:{
        tag:'',
        paged:1,
        list:'',
        locked:false,
        count:[]
    },
    mounted(){
        if(!this.$refs.allCircle) return
        this.getList()
    },
    methods:{
        getList(){
            if(this.locked === true) return
            this.locked = true
            let data = {
                'tag':this.tag,
                'paged':this.paged
            }

            this.$http.post(b2_rest_url+'getAllCircleData',Qs.stringify(data)).then(res=>{
                this.list = res.data
                for (let index = 0; index < this.list.list.length; index++) {
                    this.$set(this.count,index,5)
                }
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        go(index){
            this.$scrollTo('#circle-'+index, 300, {offset: -80})
        },
        open(index){
            if(this.count[index] > 5){
                this.$set(this.count,index,5)
            }else{
                this.$set(this.count,index,60)
            }
        }
    }
})

//所有用户
var b2AllUsers = new Vue({
    el:'#circle-users',
    data:{
        circleId:0,
        paged:1,
        list:'',
        follow:[],
        ids:[],
        opt:{
            paged:1,
            circleId:0
        },
        canEdit:false
    },
    mounted(){
        if(!this.$refs.allUsers) return
        this.opt.circleId = b2GetQueryVariable('circle_id')
        this.getList();
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
        },
        getMoreUserListData(data){
            this.list = data
            for (let i = 0; i < this.list.list.length; i++) {
                this.ids.push(this.list.list[i].id)
            }
            this.checkFollowByids()
        },
        getList(){
            this.$http.post(b2_rest_url+'getCircleUserList','circleId='+this.opt.circleId+'&paged='+this.opt.paged).then(res=>{
                this.canEdit = res.data.is_admin
                this.list = res.data
                this.opt.pages = res.data.pages
                for (let i = 0; i < this.list.list.length; i++) {
                    this.ids.push(this.list.list[i].id)
                }
                this.checkFollowByids()
            })
        },
        remove(id){

            if(!confirm(b2_global.js_text.circle.delete_user)) return

            this.$http.post(b2_rest_url+'removeUserFormCircle','user_id='+id+'&circle_id='+this.opt.circleId).then(res=>{
                if(res.data == 1){
                    this.getList()
                }
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        checkUser(id){
            this.$http.post(b2_rest_url+'changeUserRole','user_id='+id+'&circle_id='+this.opt.circleId).then(res=>{
                if(res.data == 1){
                    this.getList()
                }
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
            })
        }
    }
})

var circlePeople = new Vue({
    el:'.c-p-top',
    data:{
        id:0,
        data:''
    },
    mounted(){

        if(!this.$el.getAttribute('data-people')) return
        
        this.id = this.$el.getAttribute('data-people')
        this.getAuthorInfo()
    },
    methods:{
        getAuthorInfo(){
            this.$http.post(b2_rest_url+'getAuthorInfo','author_id='+this.id).then(res=>{
                this.data = res.data
            })
        },
        dmsg(){
            if(!b2token){
                login.show = true
            }else{
                b2Dmsg.userid = this.id
                b2Dmsg.show = true
            }
        },
        followingAc(){
            if(!b2token){
                login.show = true
            }else{
                this.$http.post(b2_rest_url+'AuthorFollow','user_id='+this.id).then(res=>{
                    this.data.followed = !this.data.followed
                }).catch(err=>{
                    Qmsg['warning'](err.response.data.message,{html:true});
                })
            }
        },
    }    

})