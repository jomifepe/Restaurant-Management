<template>
    <!--<div class="jumbotron mt-4 mb-4">
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
    </div>-->

    <!-- Default form login -->
    <form class="jumbotron  text-center border border-light p-5">
        <p class="h4 mb-4">Login</p>
        <!-- Email -->
        <input type="email" id="defaultLoginFormEmail" class="form-control mb-4" placeholder="E-mail" v-model.trim="user.email">
        <!-- Password -->
        <input type="password" id="defaultLoginFormPassword" class="form-control mb-4" placeholder="Password" v-model="user.password">
        <div class="d-flex justify-content-around">
            <div>
                <!-- Forgot password -->
                <a href="">Forgot password?</a>
            </div>
        </div>
        <!-- Sign in button -->
        <button class="btn btn-info btn-block my-4" type="submit" v-on:click.prevent="login">Login</button>
    </form>
    <!-- Default form login -->
</template>

<script type="text/javascript">
    export default {
        data() {
            return {
                user: {
                    email: "",
                    password: ""
                }
            }
        },
        methods: {
            login() {
                axios.post('login', this.user)
                    .then(response => {
                        if (response.status === 200) {
                            this.$store.commit('setToken', response.data.access_token);
                            return axios.get('users/me');
                        }
                    })
                    .then(response => {
                        this.$store.commit('setUser', response.data.data);
                        let message = `Welcome back ${ response.data.data.name }`;
                        this.$emit("login-successful", message);
                    })
                    .catch(error => {
                        this.$store.commit('clearUserAndToken');
                        let message = "Invalid credentials";
                        this.$emit("login-failed", message);
                    })
            },
        }
    }
</script>