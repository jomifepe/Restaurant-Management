<template>
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
    <!--</navigation>-->
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
                let toast;
                axios.post('login', this.user)
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