/*jshint esversion: 6 */

"use strict";

require('./bootstrap');

window.Vue = require('vue');

import '@fortawesome/fontawesome-free/css/all.css'
import Vue from 'vue';
import VeeValidate from 'vee-validate';
import Vuetify from 'vuetify'
import Toasted from 'vue-toasted';
import axios from 'axios';
import PasswordReset from './components/worker/PasswordReset.vue';

Vue.use(VeeValidate);
Vue.use(Vuetify, {iconfont: 'fa'});
Vue.use(Toasted);


axios.defaults.baseURL = 'http://dad-restaurant.ml/';

new Vue({
    el: "#app",
	components: { 'passwordreset': PasswordReset }
});