/*jshint esversion: 6 */

"use strict";

require('./bootstrap');

window.Vue = require('vue');

import MenuList from './components/MenuComponent.vue';
import sideBar from './components/sidebar/sideBarMenu.vue';
import Login from './components/login.vue';
import Logout from './components/logout';
import Vue from 'vue';
import Vuex from 'vuex';
import store from './stores/global-store';
import VueRouter from 'vue-router';
import Navigation from './components/sidebar/Navigation.vue';
import Register from './components/RegisterUserComponent.vue';
import AlertMessage from './components/AlertMessage.vue';


Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(store);

Vue.component('alert-message', AlertMessage);
Vue.component('sidebar-menu', sideBar);
Vue.component('menu-component', MenuList);
Vue.component('login-form', Login);
Vue.component('logout', Logout);
Vue.component('navigation', Navigation);
Vue.component('register', Register);

axios.defaults.baseURL = 'http://project.dad/api';


const Profile = Vue.component('profile', require('./components/profile.vue'));


const routes = [
    { path: '/', component: MenuList, name: 'menu'},
    { path: '/profile', component: Profile, name: 'profile'},
    { path: '/login', component: Login, name: 'login'},
];


const router = new VueRouter({
    routes:routes
});

/*
router.beforeEach((to, from, next) => {
    if ((to.name == 'profile') || (to.name == 'logout')) {
        if (!store.state.user) {
            next("/login");
            return;
        }
    }
    next();
});*/




const app = new Vue({
    data: {
        isUserLoggedIn: false,
        showMessage: false,
        showLoginForm: false,
        alertClass: "alert-success",
        alertMessage: "",
        showLogoutButton: false,
        showRegisterForm: false,
        loggedUser: null,
    },
    //router,
    store,
    router,
    methods: {
        onShowLogin() {
            this.showLoginForm = true;
            this.showRegisterForm = false;
        },
        onLoginSuccessful(message) {
            this.isUserLoggedIn = true;
            this.showMessage = true;
            this.showRegisterForm = false;
            this.showLoginForm = false;
            this.alertClass = "alert-success";
            this.alertMessage = message;
            this.closeAlertMessage();
        },
        onLoginFailed(message) {
            this.showMessage = true;
            this.alertClass = "alert-danger";
            this.alertMessage = message;
        },
        onLogoutSuccessful() {
            this.isUserLoggedIn = false;
            this.showMessage = true;
            this.alertClass = "alert-success";
            this.alertMessage = "User was logged out successfully";
            this.closeAlertMessage();
            this.$router.go('/');
        },
        onLogoutFailed(){
            this.showMessage = true;
            this.alertClass = "alert-danger";
            this.alertMessage = "User logout out failed";
        },
        hasUserLoggedIn(){
          return this.$store.state.user != null;
        },
        onShowRegisterForm(){
            this.showLoginForm = false;
            this.showRegisterForm = true;
        },
        onHideRegisterForm(){
            this.showRegisterForm = false;
        },
        closeAlertMessage() {
            setTimeout(() => {
                    this.showMessage = false
            }, 4000);
        },
        onCloseAlertMessage(){
            this.showMessage = false;
        }
    },
    created() {
        //CHECK IF USER IS LOGGED
        console.log('-----');
        this.$store.commit('loadTokenAndUserFromSession');
        console.log("isUserLogged -> " + this.hasUserLoggedIn());
        console.log('-----');

        //GET LOGGED USER INFO
        if(this.hasUserLoggedIn()){
            this.isUserLoggedIn = true;
            this.showLogoutButton = true;
            this.loggedUser = this.$store.state.user
        }
    }
}).$mount('#app');
