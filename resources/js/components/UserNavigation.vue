<template>
    <v-app class="grey lighten-4">
        <v-content>
            <v-layout align-center justify-center>
                <v-flex xs12 md8 class="white elevation-3">
                    <v-toolbar align-center justify-center>
                        <v-toolbar-title>DAD Restaurant</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-toolbar-items class="hidden-sm-and-down">
                            <v-btn flat to="/">Home</v-btn>
                            <v-btn flat to="menu">Menu</v-btn>
                            <v-btn flat to="login">Login</v-btn>
                        </v-toolbar-items>
                    </v-toolbar>
                    <v-card>
                        <router-view></router-view>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-content>
    </v-app>
    <!-- <div>
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
                <router-view></router-view>
            </div>
        </template>
    </div> -->
</template>

<script>

    export default {
        name: "UserNavigation",
        computed: {
            user() {
                return this.$store.state.user;
            }
        }
    }
</script>

<style scoped>

</style>