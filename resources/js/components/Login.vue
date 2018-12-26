<template>
    <v-container class="blue-grey lighten-5 pa-5 rounded" fluid>
        <v-layout wrap>
            <v-flex xs12>
                <v-form v-model="valid" @keyup.enter.native="login">
                    <v-text-field box v-model="usernameOrEmail" :rules="usernameOrEmailRules"
                        label="Username or email"
                        required autofocus></v-text-field>
                    <v-text-field box
                        v-model="password"
                        :rules="passwordRules"
                        label="Password"
                        :append-icon="showPassword ? 'visibility_off' : 'visibility'"
                        :type="showPassword ? 'text' : 'password'"
                        @click:append="showPassword = !showPassword"
                        required></v-text-field>

                    <v-btn :disabled="!valid" ref="btnLogin" @click="login">Login</v-btn>
                </v-form>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script type="text/javascript">
    import {toasts, helper} from '../mixin'

    export default {
        mixins: [toasts, helper],
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
                this.performLogin(user).then((toast) => {
                    this.$router.push('/admin');
                    this.$store.commit('hideProgressBar');
                    toast.goAway(1);
                });
            },
            performLogin(user) {
                return new Promise(resolve => {
                    let toast;
                    this.$store.commit('showProgressBar', {indeterminate: true});
                    axios.post('login', user)
                        .then(response => {
                            let toast = this.showSuccessToast('Signing in...', 10000);
                            if (response.status === 200) {
                                this.$store.commit('setToken', response.data.access_token);
                                axios.get('users/me').then(userResponse => {
                                    this.$store.commit('setUser', userResponse.data.data);
                                    resolve(toast);
                                });
                            } else {
                                this.showErrorToast(`Failed to sign in (${response.status})`);
                            }
                        })
                        .catch(error => {
                            this.$store.commit('clearUserAndToken');
                            this.$store.commit('hideProgressBar');
                            this.showErrorLog('Failed to sign in', error.response.data);
                        }) 
                });
            }
        }
    }
</script>