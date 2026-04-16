var B2ShopHome = new Vue({
    el:'.shop-home-left',
    data:{
        ids:[],
        data:''
    },
    mounted(){
        const list = this.$el.querySelectorAll('.shop-normal-item')
        for (let i = 0; i < list.length; i++) {
            this.ids.push(list[i].getAttribute('data-id'))
        }
        if(this.ids.length > 0){
            this.getList()
        }
    },
    methods:{
        getList(){
            const ids = {
                ids:this.ids,
                return:{
                    'images':0
                }
            }
            this.$http.post(b2_rest_url+'getShopItemsData',Qs.stringify(ids)).then((res)=>{
                this.data = res.data
                this.$nextTick(()=>{
                    lazyLoadInstance.update()
                })
            })
        },
        go(url){
            window.location.href=url
        }
    }
})

function b2shopflickity(){
    var f = document.querySelector('.shop-cats-list');
    if(f){
            let shop = new Flickity(f,{
                pageDots: false,
                groupCells: true,
                draggable: true,
                prevNextButtons: false,
                freeScroll: true,
                wrapAround:true,
                cellAlign: 'left'
            });
            
            let previous = f.parentNode.querySelector('.shop-previous');
            previous.addEventListener( 'click', function() {
                shop.previous();
            });

            let next = f.parentNode.querySelector('.shop-next');
            next.addEventListener( 'click', function() {
                shop.next();
            });
        
    }
}
    
b2shopflickity()

var b2ShopResout = new Vue({
    el:'#shop-single',
    data:{
        id:0,
        resout:{
            'data':''
        }
    },
    mounted(){
        if(this.$refs.shopRes && b2token){
            this.id = this.$refs.shopRes.getAttribute('data-id')
            this.getUserBuyResout()
        }
    },
    methods:{
        //用户购买信息
        getUserBuyResout(){
            this.$http.post(b2_rest_url+'getUserBuyResout','post_id='+this.id).then(res=>{
                this.resout = res.data
            })
        },
    }
})

var B2ShopSingle = new Vue({
    el:'.shop-top-box',
    data:{
        id:0,
        data:'',
        count:1,
        locked:false,
        postData:'',
        //地址信息
        address:{
            addresses:{}
        },
        showAddressBox:false,
        editAddressKey:'',
        pickedAddress:'',
        selectAddress:false,
        //抽奖
        fir:[0,0,0,0],
        sec:[0,0,0,0],
        resData:{
            fir:0,
            sec:0
        },
        m:5,
        f:'=',
        //邮箱信息
        showEmailBox:false,
        pickedEmail:'',
        pickedMultiId:0,
        thumbs:[],
        currentThumb:{
            'thumb':'',
            'thumb_webp':''
        },
        thumbIndex:0
    },
    computed:{
        carts(){
            return this.$store.state.carts
        },
        userData(){
            return this.$store.state.userData
        }
    },
    watch:{
        pickedMultiId(val){
            this.resetPrice()
            if(this.data[this.id].multi.attrs[val].thumb){
                this.thumbIndex = 'none'
                this.currentThumb = {
                    'thumb': this.data[this.id].multi.attrs[val].thumb,
                    'thumb_webp':this.data[this.id].multi.attrs[val].thumb_webp
                }
            }
        }
    },
    mounted(){
        if(this.$refs.shopSingle){

            let c = b2getCookie('carts')
            if(c){
                this.carts = JSON.parse(c)
            }

            this.id = this.$refs.shopSingle.getAttribute('data-id')
            const ids = {
                ids:[this.id],
                return:{
                    'images':0,
                    'attrs':0
                }
            }
            this.$http.post(b2_rest_url+'getShopItemsData',Qs.stringify(ids)).then((res)=>{
                this.data = res.data

                this.thumbs = b2_global.shop_images
                if(this.thumbs.length > 0){
                    this.currentThumb = this.thumbs[0]
                }else{
                    this.currentThumb['thumb'] = this.data[this.id].thumb
                    this.currentThumb['thumb_webp'] = this.data[this.id].thumb_webp
                }

                if(this.data[this.id].multi != ''){
                    this.setMultiId()
                    if(this.data[this.id].multi.attrs[0].thumb){
                        this.currentThumb = {
                            'thumb': this.data[this.id].multi.attrs[0].thumb,
                            'thumb_webp':this.data[this.id].multi.attrs[0].thumb_webp
                        }
                    }   
                }
                
                this.$nextTick(()=>{
                    lazyLoadInstance.update()
                })
            })

            if(b2token){
                this.getAddress()
                this.getEmail()
                b2AsideBar.getMycarts()
            }
        }
    },
    methods:{
        pickedThumb(index){
            this.thumbIndex = index
            this.currentThumb = {
                'thumb':this.thumbs[index].thumb,
                'thumb_webp':this.thumbs[index].thumb_webp
            }
        },
        resetPrice(){
            this.$set(this.data[this.id],'price',this.data[this.id]['multi']['attrs'][this.pickedMultiId].price)
            this.$set(this.data[this.id],'stock',this.data[this.id]['multi']['attrs'][this.pickedMultiId].stock)
            this.$set(this.data[this.id],'can_buy',this.data[this.id]['multi']['attrs'][this.pickedMultiId].can_buy)
            
        },
        multiPicked(index,i,value){
            this.$set(this.data[this.id]['multi']['picked'],index,i)
            this.rebuildPrice(index,value)
        },
        rebuildPrice(index,value){
            this.$set(this.data[this.id]['multi']['pickedVaule'],index,value)
            this.setMultiId()
        },
        setMultiId(){
            
            this.data[this.id].multi.skuList.forEach((val,i) => {
                if(typeof val == 'string'){
                    if(this.data[this.id]['multi']['pickedVaule'][0] == val){
                        this.pickedMultiId = i
                        return
                    }
                }
                if(this.equar(val,this.data[this.id]['multi']['pickedVaule'])){
                    this.pickedMultiId = i
                    return
                }
            });
        },
        equar(a, b) {
            // 判断数组的长度
            if (a.length !== b.length) {
                return false
            } else {
                // 循环遍历数组的值进行比较
                for (let i = 0; i < a.length; i++) {
                    if (a[i] !== b[i]) {
                        return false
                    }
                }
                return true;
            }
        },
        countAdd(){
            if(this.count >= this.data[this.id].stock.total) return
            this.count++
        },
        countSub(){
            if(this.count <= 1) return
            this.count--
        },
        postFavoriteAc(){
            b2ContentFooter.postFavoriteAc()
        },
        inCart(){
            if(this.carts && this.carts.hasOwnProperty(this.id+'_'+this.pickedMultiId) && this.pickedMultiId == this.carts[this.id+'_'+this.pickedMultiId].index) return true
            return false
        },
        addCart(){
            if(!b2token){
                login.show = true
                return
            }
            this.$http.post(b2_rest_url+'setMyCarts','id='+this.id+'&count='+this.count+'&index='+this.pickedMultiId).then(res=>{
                this.$store.commit('setcartsData',res.data)
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        exchange(id){
            
            if(!b2token){
                login.show = true
                return
            }
            if(this.data[id].commodity == 1 && !this.pickedAddress){

                Qmsg['warning'](b2_global.js_text.global.add_address,{html:true});

                return
            }
            payCredit.data = {
                'title':this.data[id].title,
                'order_price':Calc.Mul(this.data[id].price.current_price,this.count),
                'order_type':'d',
                'order_count':this.count,
                'post_id':id,
                'order_address':this.showAddress(id) ? this.pickedAddress : (this.showEmail(id) ? this.pickedEmail : '')
            }
            payCredit.show = true;
        },
        lottery(id){
            
            if(!b2token){
                login.show = true
                return
            }

            var r = confirm(b2_global.js_text.global.credit_pay.replace('${credit}',Calc.Mul(this.data[id].price.current_price,this.count)));

            if(r){
                this.m = 30;
                this.lotteryAc()
                this.$http.post(b2_rest_url+'shopLottery','post_id='+id+'&address='+(this.showAddress(id) ? this.pickedAddress : (this.showEmail(id) ? this.pickedEmail : ''))).then(res=>{
                    this.resData = res.data
                }).catch(err=>{

                    Qmsg['warning'](err.response.data.message,{html:true});
                    this.resData.fir = '1010'
                    this.resData.sec = '0101'
                    this.m = -1
                })
            }
        },
        lotteryAc(){
            this.fir = this.numToStr(b2randomNum(1000,9999));
            this.sec = this.numToStr(b2randomNum(1000,9999));
            this.m--;
            if(this.m <=0 && this.resData.fir !== 0){
                this.fir = this.numToStr(this.resData.fir);
                this.sec = this.numToStr(this.resData.sec);
                if(this.resData.fir !== this.resData.sec){
                    this.f = '≠'
                }else{
                    this.f = '='
                }
                return
            } 
            setTimeout(() => {
                this.lotteryAc()
            }, 100);
        },
        numToStr(num){
            num = num.toString()
            let arr = []
            for (let i = 0; i < num.length; i++) {
                arr.push(num[i])
            }
            return arr
        },
        emptyData(){
            if(typeof this.data === 'object' && this.data !== null){
                return true
            }

            return false
        },
        disabled(id){
            if(this.data == '') return true
            if(!this.data[id].can_buy.allow) return true
            return false
        },
        //邮件信息
        showEmail(id){
            
            if(b2token && this.data != '' && this.emptyData() && this.data[id].commodity == 0){
                return true
            }

            return false
        },
        getEmail(){
            this.$http.post(b2_rest_url+'getEmail').then(res=>{
                this.pickedEmail = res.data
            })
        },
        //地址信息
        showAddress(id){
            
            if(b2token && this.data != '' && this.emptyData() && this.data[id].commodity == 1){
                return true
            }

            return false
        },
        close(){
            this.showAddressBox = false
            this.showEmailBox = false
        },
        getAddress(){
            this.$http.post(b2_rest_url+'getAddresses').then(res=>{
                if(Object.keys(res.data.addresses).length > 0){
                    this.address = res.data
                    this.pickedAddress = this.address.default
                }
            })
        },
        emptyAddress(){
            if(this.address === '') return false

            if(!this.emptyData()) return false
            
            if(this.pickedAddress === '') return false
            
            return true
        },
        deleteAddress(key){
            var r = confirm("确定要删除这个地址吗？");
            if (r == true) {
                this.$http.post(b2_rest_url+'deleteAddress','key='+key).then(res=>{
                    if(res.data){
                        this.address.addresses = res.data.address;
                        if(key === this.address.default){
                            this.address.default = res.data.default
                        }
                        if(this.pickedAddress === key){
                            this.pickedAddress = res.data.default
                        }
                    }
                }).catch(err=>{
                    Qmsg['warning'](err.response.data.message,{html:true});
                })
            } 
            return
        },
        pickedAddressAc(key){
            this.pickedAddress = key
            this.close()
        },
        editAddress(key){
            this.editAddressKey = key
            this.addressEditData = this.address.addresses[key]
        },
        addNewAddress(){
            this.editAddressKey = uuid(8, 16);
            this.addressEditData = []
        },
        saveAddress(){

            this.$http.post(b2_rest_url+'saveAddress','address='+this.addressEditData.address+'&name='+this.addressEditData.name+'&phone='+this.addressEditData.phone+'&key='+this.editAddressKey).then(res=>{
                this.address.addresses = res.data.address;
                this.pickedAddressAc(res.data.key)
                this.editAddressKey = ''
                this.key = ''
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        reflush(){
            location.reload();
        }
    }
})

function b2randomNum(minNum,maxNum){ 
    switch(arguments.length){ 
        case 1: 
            return parseInt(Math.random()*minNum+1,10); 
        break; 
        case 2: 
            return parseInt(Math.random()*(maxNum-minNum+1)+minNum,10); 
        break; 
            default: 
                return 0; 
            break; 
    } 
} 