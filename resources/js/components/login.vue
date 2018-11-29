<template>
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
                        this.$store.commit('setToken', response.data.access_token);
                        return axios.get('users/me');
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