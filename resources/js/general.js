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

Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(store);

Vue.component('sidebar-menu', sideBar);
Vue.component('menu-list', MenuList);
Vue.component('login', Login);
Vue.component('logout', Logout);
Vue.component('navigation', Navigation);
Vue.component('register', Register);

axios.defaults.baseURL = 'http://project.dad/api';

const user = Vue.component('user', require('./components/user.vue'));
const login = Vue.component('login', require('./components/login.vue'));
const logout = Vue.component('logout', require('./components/logout.vue'));
const profile = Vue.component('profile', require('./components/profile.vue'));

/*
const routes = [
    { path: '/users', component: user, name: 'users'},
    { path: '/login', component: login, name: 'login'},
    { path: '/logout', component: logout, name: 'logout'},
    { path: '/profile', component: profile, name: 'profile'},
];

const router = new VueRouter({
    routes:routes
});
*/

const app = new Vue({
    data: {
        isUserLoggedIn: false,
        showMessage: false,
        showLoginForm: false,
        alertClass: "alert-success",
        alertMessage: "",
        showLogoutButton: false,
        showRegisterForm: false,
    },
    //router,
    store,
    methods: {
        onShowLogin() {
            this.showLoginForm = true;
            this.showRegisterForm = false;
        },
        onLoginSuccessful(message) {
            this.isUserLoggedIn = true;
            this.showMessage = true;
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
    },
    created() {
        console.log('-----');
        this.$store.commit('loadTokenAndUserFromSession');
        console.log(this.$store.state.user);
        console.log('-----');
        if(this.hasUserLoggedIn()){
            this.isUserLoggedIn=true;
            this.showLogoutButton = true;
        }
    }
}).$mount('#app');
