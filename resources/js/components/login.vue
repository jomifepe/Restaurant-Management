<template>
    <!-- -->
    <div>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <a class="navbar-brand" href="#"><strong>RM</strong></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" v-on:click.prevent="showLogin">Login</a>
                    </li>
                </ul>
            </div>
            <div>
                <logout v-if="userLoggedIn()"></logout>
            </div>
        </nav>

        <div class="alert" :class="typeofmsg" v-if="showMessage">
            <button type="button" class="close-btn" v-on:click="showMessage=false">&times;</button>
            <strong >{{ message }}</strong>
        </div>

        <div v-if="showLoginFields">

            <div class="jumbotron">
                <h2>Login</h2>
                <div class="form-group">
                    <label for="inputEmail">Email</label>
                    <input
                            type="email" class="form-control" v-model.trim="user.email"
                            name="email" id="inputEmail"
                            placeholder="Email address"/>
                </div>
                <div class="form-group">
                    <label for="inputPassword">Password</label>
                    <input
                            type="password" class="form-control" v-model="user.password"
                            name="password" id="inputPassword"/>
                </div>
                <div class="form-group">
                    <a class="btn btn-primary" v-on:click.prevent="login">Login</a>
                </div>
            </div>
        </div>
    </div>

</template>


<script type="text/javascript">
    export default {
        data: function(){
            return {
                user: {
                    email:"",
                    password:""
                },
                typeofmsg: "alert-success",
                showMessage: false,
                message: "",
                showLoginFields: false,
            }
        },
        methods: {
            login() {
                this.showMessage = false;
                axios.post('login', this.user)
                    .then(response => {
                        this.$store.commit('setToken', response.data.access_token);
                        return axios.get('users/me');
                    })
                    .then(response => {
                        this.$store.commit('setUser', response.data.data);
                        this.typeofmsg = "alert-success";
                        this.message = "Welcome back " + response.data.data.name;
                        this.showMessage = true;
                        this.showLoginFields = false;
                    })
                    .catch(error => {
                        this.$store.commit('clearUserAndToken');
                        this.typeofmsg = "alert-danger";
                        this.message = "Invalid credentials";
                        this.showMessage = true;
                        console.log(error);
                    })
            },
            showLogin() {
                this.showLoginFields = true;
            },
            userLoggedIn(){
               return this.$store.state.user != null;
            },
        }
    }
</script>