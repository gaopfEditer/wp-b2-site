var b2Distribution = new Vue({
    el:'#distribution-page',
    data:{
        paged:1,
        pages:0,
        selecter:'.my-distribution-orders ul',
        opt:{
            user_id:0,
            paged:1,
            pages:1
        },
        popt:{
            user_id:0,
            paged:1,
            partner_paged:1,
            pages:20
        },
        papi:'getMyPartner',
        api:'getMyDistributionOrders',
        money:0,
        ref:'*********',
        orderList:'',
        total:0,
        ppage:1,
        partnerList:'',
        show:false,
        url:b2_global.home_url,
        link:'',
        qrcode:''
    },
    mounted(){
        if(this.$refs.distribution){
            this.paged = this.$refs.distribution.getAttribute('data-paged')
            this.opt.paged = this.paged
            this.opt.user_id = b2GetQueryVariable('u') ? b2GetQueryVariable('u') : 0
            this.popt.user_id = this.opt.user_id
            this.$refs.goldNav.go(this.opt.paged,'comment',true)
            this.$refs.pnav.go(this.popt.paged,'comment',true)
            this.getMyDistributionData()
            // this.getMyPartner()

            var clipboard = new ClipboardJS('.btn');
            clipboard.on('success', e=>{
                Qmsg['success'](b2_global.js_text.global.copy_success,{html:true});
            });
            clipboard.on('error', e=> {
                Qmsg['warning'](b2_global.js_text.global.copy_select,{html:true});
            });
        }
    },
    methods:{
        ratio(v){
            if(v){
                return Calc.Mul(v,100)
            }else{
                return 'NaN'
            }
        },
        pget(data){
            this.partnerList = data.data
            this.popt.pages = data.pages
            this.$refs.partnergujia.remove()
            this.popt.partner_paged++
        },
        get(data){
            this.orderList = data.list
            this.opt.pages = data.pages
            this.total = data.total
            this.$refs.gujia.remove()
        },
        getMyDistributionData(){
            this.$http.post(b2_rest_url+'getMyDistributionData','user_id='+this.opt.user_id).then((res)=>{
                this.ref = res.data.ref
                this.money = res.data.total_money
                // if(res.data.tuiguang.open){
                //     this.url = res.data.tuiguang.domains[Math.floor((Math.random()*res.data.tuiguang.domains.length))]
                // }
            })
        },
        // getMyPartner(){
        //     this.$http.post(b2_rest_url+'getMyPartner','user_id='+this.opt.user_id+'&partner_paged='+this.ppage).then((res)=>{
        //         this.partnerList = res.data
        //         this.$refs.partnergujia.remove()
        //     })
        // },
        build(){
            if(!this.url){
                Qmsg['warning'](b2_global.js_text.global.put_link,{html:true});
                return
            }
            this.show = !this.show
            if(this.show){
                this.linkAc()
                this.qrcodeAc()
            }
        },
        download(){
            return this.base64ToBlob(this.qrcode)
        },
        linkAc(){
            var url = new URL(this.url)
            url.searchParams.set('ref', this.ref)
            this.link = url.href
        },
        qrcodeAc(){
            var url = new URL(this.url)
            url.searchParams.set('ref', this.ref)
            console.log(url)
            var qr = new QRious({
                value: url.href,
                size:100
              });
            this.qrcode = qr.toDataURL('image/jpeg')
        },
        base64ToBlob(code) {
            var parts = code.split(';base64,');
            var contentType = parts[0].split(':')[1];
            var raw = window.atob(parts[1]);
            var rawLength = raw.length;

            var uInt8Array = new Uint8Array(rawLength);

            for (var i = 0; i < rawLength; ++i) {
                uInt8Array[i] = raw.charCodeAt(i);
            }
            return new Blob([uInt8Array], {type: contentType});
        }
    }
})