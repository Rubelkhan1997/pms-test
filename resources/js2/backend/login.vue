<template>
    <div class="login-page col-sm-10 col-md-8 col-lg-5 mx-auto d-table pt-5 h-100">
        <div class="d-table-cell align-middle">
            <div class="card">
                <div class="card-body">
                    <div class="m-sm-4">
                        <div class="text-center">
                            <img :src="`${asset}/backend/img/avatars/rubel.jpg`" alt="Chris Wood" class="img-fluid rounded-circle" width="132" height="132" />
                        </div>
                        <form @submit.prevent="login()" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-Mail Address</label>
                                <input type="email"  v-model="form.email" class="form-control"  placeholder="email">
                                <!-- Error -->   
                                <span class="text-danger" v-if="form.errors.has('email')" v-html="form.errors.get('email')" /> 
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input  type="password" v-model="form.password" class="form-control" placeholder="Password">
                                <!-- Error -->
                                <span  class="text-danger" v-if="form.errors.has('password')" v-html="form.errors.get('password')" /> 
                            </div>
                            <!-- <div class="mb-3"> -->
                                <!-- <input class="form-check-input" type="checkbox" name="remember" id="remember">

                                <label class="form-check-label" for="remember">
                                    Remember Me
                                </label>
                            </div>
                            <a class="btn btn-link" @click="password_request()">
                                Forgot Your Password?
                            </a> -->
                            <button type="submit"  class="btn btn-primary" :disabled="form.busy">
                                Log In
                            </button> 
                        </form> 
                    </div>
                </div>
            </div>
        </div>
    </div> 
</template>

<script>
export default {
    name: 'login-page',
    data(){
      return {
        form: new Form({
            email: '',
            password: '',
            
        }),
        asset: "",
      }
    },
    mounted(){
        this.asset = Base_Url;
    },
    methods: {
        login(){
            this.form.post(Api_Base_Url+'/login').then(res =>{
                if(res.data.error){
                    this.$message({
                        showClose: true,
                        message: res.data.error,
                        type: 'error',
                    });
                }else{
                    // Message
                    this.$message({
                        showClose: true,
                        message: res.data.success,
                        type: 'success',
                    });
                    axios.defaults.headers.common['Authorization'] = `Bearer ${res.data.token}`;
                    // Local Storage Information Store
                    localStorage.setItem('token', res.data.token);
                    localStorage.setItem('auth',  JSON.stringify(res.data.auth));
                    // Dashboard Page Redirect 
                    this.$router.push({ name: 'dashboard-page' });
                }
            });
        },
    }
}

// console.log(JSON.parse(Auth).name);
</script>
