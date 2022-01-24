var app = Vue.createApp({
    data() {
        return {
            tab: '',
            username: '',
            balance: ''
        }
    },
    created() {
        fetch('/api/data.php').then(r => r.json()).then(r => {
            console.log(r);
            this.username=r.success.username;
            this.balance=r.success.amount;
        });
    },
    methods: {
        luckyDraw(){
            this.tab = 'lucky-draw';
        },
        openStore(){
            this.tab = 'my-store';
        },
        back(){
            this.tab = '';
        }
    },
})

app.component('my-store',Vue.defineAsyncComponent(async ()=>({
    template: await fetch('views/my-store.html').then(r => r.text()),
    emits: ['back'],
    data(){
        return{
            items: {},
            notify: "",
            my_items: {},
            secret: "",
            name: "",
            content: "",
            image: ""
        }
    },
    created(){
        fetch('/api/item.php').then(r => r.json()).then(r => {
            this.items = r;
        });
        fetch('/api/item.php?myItem').then(r => r.json()).then(r => {
            this.my_items = r;
        });
    },
    methods:{
        back(){
            this.$emit('back');
        },
        async buy(id){
            var myHeaders = new Headers();
            myHeaders.append("Cookie", document.cookie);
            var formdata = new FormData();
            formdata.append("buy", "buy");
            formdata.append("item", id);
            var requestOptions = {
            method: 'POST',
            headers: myHeaders,
            body: formdata,
            redirect: 'follow'
            };
            const response = await fetch("/api/item.php", requestOptions);
            const data = await response.json();
            if(data['success']!=0){
                this.notify=data['success'];
                modal = document.getElementById("myModal2");
                modal.style.display = "block";
                fetch('/api/item.php?myItem').then(r => r.json()).then(r => {
                    this.my_items = r;
                });
                await new Promise(r => setTimeout(r, 1000));
                modal.style.display = "none";
            }else{
                this.notify=data['error'];
                modal = document.getElementById("myModal2");
                modal.style.display = "block";

                await new Promise(r => setTimeout(r, 1000));
                modal.style.display = "none";
            }
        },
        view(id){
            for(i of this.my_items){
                if(i.id ==id){
                    this.name=i.name;
                    this.secret=i.secret;
                    this.content=i.content;
                    this.image=i.image
                    modal = document.getElementById("myModal");
                    modal.style.display = "block";
                    window.onclick = function(event) {
                        if (event.target == modal) {
                            modal.style.display = "none";
                        }
                    }
                }
            }
        }
    }
})))

app.component('lucky-draw',Vue.defineAsyncComponent(async ()=>({
    template: await fetch('views/lucky-draw.html').then(r => r.text()),
    emits: ['back'],
    data(){
        return{
            notify: "",
            time: 0,
            win: [],
            ticket: "",
            bill_id: "",
            price: 1000,
            captcha: "",
            captcha_input: "",
            history: []
        }
    },
    async created(){
        var myHeaders = new Headers();
        myHeaders.append("Cookie", document.cookie);

        var requestOptions = {
        method: 'GET',
        headers: myHeaders,
        redirect: 'follow'
        };

        response = await fetch("/api/winner.php", requestOptions);
        data = await response.json();
        this.win =data;

        requestOptions = {
        method: 'GET',
        headers: myHeaders,
        redirect: 'follow'
        };

        response = await fetch("/api/draw.php?history", requestOptions);
        data = await response.json();
        this.history=data;

        
    },
    async mounted(){
            response = await fetch('/api/time.php');
            data = await response.json();
            this.time = data.success;
            this.updateTime();

    },
    watch:{
        time: async function(val){
            if(val == 0){
                var myHeaders = new Headers();
                myHeaders.append("Cookie", document.cookie);
                var requestOptions = {
                method: 'GET',
                headers: myHeaders,
                redirect: 'follow'
                };
                response = await fetch("/api/winner.php", requestOptions);
                data = await response.json();
                this.win =data;
            }
        }
    },
    methods:{
        back(){
            this.$emit('back');
        },
        updateTime(){
            var distance = this;
            x=setInterval(async function () { 
                distance.time=distance.time-1;
                if(distance.time == -2){
                    clearInterval(x);
                }
                else if (distance.time <= 0) {
                document.getElementById("headline").innerText = "Đang quay thưởng !!!";
                document.getElementById("countdown").style.display = "none";
                response = await fetch('/api/time.php');
                data = await response.json();
                distance.time = data.success;
                //clearInterval(x);
                }else{
                document.getElementById("headline").innerText = "";
                document.getElementById("countdown").style.display = "block";
                document.getElementById("minutes").innerText = Math.floor(distance.time/60),
                document.getElementById("seconds").innerText = Math.floor(distance.time%60);
                }

            }, 1000);
        },
        async buy() {
            this.notify = "";
            const regex=/^[0-9]+$/;
            if(this.ticket == "" || !regex.test(this.ticket)){
                return
            }
            let myHeaders = new Headers();
            myHeaders.append("Cookie", document.cookie);

            let formdata = new FormData();
            formdata.append("ticket", this.ticket);
            formdata.append("buy", "ticket");

            let requestOptions = {
                method: 'POST',
                headers: myHeaders,
                body: formdata,
                redirect: 'follow'
            };

            let response = await fetch("/api/draw.php", requestOptions);
            let data = await response.json()
            if(data['success']!=0){

                this['bill_id']=data['success'];
                formdata = new FormData();
                formdata.append("verify", "verify");
                formdata.append("billid", this.bill_id);
                formdata.append("ticket", this.ticket);
                requestOptions = {
                    method: 'POST',
                    headers: myHeaders,
                    body: formdata,
                    redirect: 'follow'
                };

                response = await fetch("/api/draw.php", requestOptions)
                data = await response.json()
                if(data['success'] !=0){
                    this['captcha'] = data['success'];
                    modal = document.getElementById("myModal");
                    modal.style.display = "block";
                    window.onclick = function(event) {
                        if (event.target == modal) {
                            modal.style.display = "none";

                        }
                    }
                }
                
            }
        },
        async excuteBuy() {
            if(this.captcha_input == ""){
                this.notify="Chưa nhập captcha!!!";
                return
            }
            myHeaders = new Headers();
            myHeaders.append("Cookie", document.cookie);
            formdata = new FormData();
            formdata.append("captcha", this.captcha_input);
            formdata.append("excute", "excute");
            formdata.append("billid", this.bill_id);
            requestOptions = {
            method: 'POST',
            headers: myHeaders,
            body: formdata,
            redirect: 'follow'
            };

            response = await fetch("/api/draw.php", requestOptions)
            data = await response.json()
            if(data['success']==0){
                this.notify=data['error'];
            }else{
                this.notify=data['success'];
                requestOptions = {
                method: 'GET',
                headers: myHeaders,
                redirect: 'follow'
                };

                response = await fetch("/api/draw.php?history", requestOptions);
                data = await response.json();
                this.history=data;
            }
            
        }
    }
})))
app.mount('#app')