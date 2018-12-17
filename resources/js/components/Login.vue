<template>
    <v-container class="blue-grey lighten-5 pa-5 rounded" fluid>
        <v-layout wrap>
            <v-flex xs12>
                <v-form v-model="valid">
                    <v-text-field box
                        v-model="usernameOrEmail"
                        :rules="usernameOrEmailRules"
                        label="Username or email"
                        required></v-text-field>
                    <v-text-field box
                        v-model="password"
                        :rules="passwordRules"
                        label="Password"
                        :append-icon="showPassword ? 'visibility_off' : 'visibility'"
                        :type="showPassword ? 'text' : 'password'"
                        @click:append="showPassword = !showPassword"
                        required></v-text-field>

                    <v-btn :disabled="!valid" @click="login">Login</v-btn>
                </v-form>
            </v-flex>
        </v-layout>
    </v-container>
        <!-- <form class="jumbotron  text-center border border-light p-5">
            <p class="h4 mb-4">Login</p>
            <input type="email" id="defaultLoginFormEmail" class="form-control mb-4" placeholder="E-mail" v-model.trim="user.email">
            <input type="password" id="defaultLoginFormPassword" class="form-control mb-4" placeholder="Password" v-model="user.password">
            <button class="btn btn-info btn-block my-4" type="submit" v-on:click.prevent="login">Login</button>
        </form> -->
</template>

<script type="text/javascript">

    export default {
        data: () => ({
            valid: false,
            usernameOrEmail: '',
            password: '',
            usernameOrEmailRules: [
                v => !!v || 'Username or Email is required',
            ],
            passwordRules: [
                v => !!v || 'Password is required',
            ],
            showPassword: false
        }),
        methods: {
            login() {
                let re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
                let isValidEmail = re.test(this.usernameOrEmail);
                let user = {
                    [isValidEmail ? 'email' : 'username']: this.usernameOrEmail,
                    'password': this.password
                };
                this.performLogin(user);
            },
            performLogin(user) {
                let toast;
                axios.post('login', user)
                    .then(response => {
                        if (response.status === 200) {
                            toast = this.$toasted.show(`Signing in...`, { 
                                icon: "check",
                                position: "bottom-center", 
                                duration : 20000
                            });
                            this.$store.commit('setToken', response.data.access_token);
                            return axios.get('users/me');
                        } else {
                            this.$toasted.show(`Failed to sign in (${response.status})`, { 
                                icon: "error",
                                position: "bottom-center", 
                                duration : 4000
                            });
                        }
                    })
                    .then(response => {
                        this.$store.commit('setUser', response.data.data);
                        this.$router.push('/admin');
                        toast.goAway(1);
                    })
                    .catch(error => {
                        // console.log(error);
                        this.$store.commit('clearUserAndToken');
                        this.$toasted.show(`Failed to sign in due to invalid credentials`, { 
                            icon: "error",
                            position: "bottom-center", 
                            duration : 4000
                        });
                    })
            },
        }
    }
</script>