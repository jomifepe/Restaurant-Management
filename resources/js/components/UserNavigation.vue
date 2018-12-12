<template>
    <div>
        <template v-if="!this.$store.state.user">
            <div class="container">
                <nav class="navbar navbar-expand-md navbar-dark bg-dark">
                    <router-link class="navbar-brand" to="/">RM</router-link>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav mr-auto">
                            <router-link to="menu">Menu</router-link>
                        </ul>
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <router-link to="login">Login</router-link>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!--<AlertMessage :classtype="alertType"-->
                               <!--:show="showAlertMessage"-->
                               <!--:message="alertMessage"-->
                               <!--@close-message="onCloseAlertMessage"></AlertMessage>-->

                <!-- Content -->
                <router-view></router-view>
            </div>
        </template>
    </div>
</template>

<script>
    // import AlertMessage from './AlertMessage.vue';

    export default {
        name: "UserNavigation",
        components: {
            // AlertMessage
        },
        data() {
            return {
                // showAlertMessage: false,
                // alertType: "alert-success",
                // alertMessage: "",
            }
        },
        methods: {
            // showMessage(alertType, message) {
            //     this.alertType = alertType;
            //     this.alertMessage = message;
            //     this.showAlertMessage = true;
            //     this.addAlertCloseTimeout(5000);
            // },
            // onCloseAlertMessage(){
            //     this.showAlertMessage = false;
            // },
            // addAlertCloseTimeout(millis) {
            //     setTimeout(() => {
            //         this.showAlertMessage = false
            //     }, millis);
            // },
        },
        created() {
        }
    }
</script>

<style scoped>

</style>