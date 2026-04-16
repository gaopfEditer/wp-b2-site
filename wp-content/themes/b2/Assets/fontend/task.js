var b2Task = new Vue({
    el:'#task',
    data:{
        taskData:'',
        locked:false
    },
    mounted(){
        if(this.$refs.task){
            this.getTaskData()
        }
    },
    methods:{
        getTaskData(){
            this.$http.post(b2_rest_url+'getTaskData').then(res=>{
                console.log(res)
                this.taskData = res.data
                if(this.taskData.task.task_mission.finish == 1){
                    this.locked = true
                }
                if(this.taskData.task_user.length == 0){
                    this.$nextTick(()=>{
                        this.$refs.userTask.style.display = 'none'
                    })
                }
            })
        },
        mission(type){
            if(type === 'task_mission'){
                
                if(!b2token){
                    login.show = true
                }else{
                    if(this.locked == true) return
                    this.locked = true

                    this.$http.post(b2_rest_url+'userMission').then(res=>{

                        Qmsg['info'](b2_global.js_text.global.missioning,{html:true});

                        setTimeout(()=>{
                            this.$set(this.taskData.task.task_mission,'finish',1)

                            Qmsg['success'](b2_global.js_text.global.mission_success,{html:true});
                        },2005)
                    }).catch(err=>{

                        Qmsg['warning'](err.response.data.message,{html:true});
                        this.locked = false
                    })
                }
            }
        }
    }
})