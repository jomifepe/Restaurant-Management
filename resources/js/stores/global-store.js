/*jshint esversion: 6 */

import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios'

Vue.use(Vuex);

export default new Vuex.Store({
    state: { 
        token: "",
        user: null,
        panelTitle: "",
        alertShown: false,
        alertMessage: "",
        alertType: "success",
        progressBarShown: false,
        progressBarValue: 0,
        progressBarIndeterminate: false
    },  
    mutations: {
        clearUserAndToken: (state) => {
            state.user = null;
            state.token = "";
            sessionStorage.removeItem('user');
            sessionStorage.removeItem('token');
            axios.defaults.headers.common.Authorization = undefined;
        },
        clearUser: (state) => {
            state.user = null;
            sessionStorage.removeItem('user');
        },
        clearToken: (state) => {
            state.token = "";
            sessionStorage.removeItem('token');
            axios.defaults.headers.common.Authorization = undefined;
        },
        setUser: (state, user) => {
            state.user = user;
            sessionStorage.setItem('user', JSON.stringify(user));
        },
        setToken: (state, token) => {
            state.token= token;
            sessionStorage.setItem('token', token);
            axios.defaults.headers.common.Authorization = "Bearer " + token;
        },
        loadTokenAndUserFromSession: (state) => {
            state.token = "";
            state.user = null;
            let token = sessionStorage.getItem('token');
            let user = sessionStorage.getItem('user');
            if (token) {
                state.token = token;
                axios.defaults.headers.common.Authorization = "Bearer " + token;
            }
            if (user) {
                state.user = JSON.parse(user);
            }
        },
        setPanelTitle(state, title) {
            state.panelTitle = title;
        },
        showAlertMessage(state, {message, alertType}) {
            state.alertMessage = message;
            state.alertType = alertType;
            state.alertShown = true;
        },
        hideAlertMessage: state => {
            state.alertShown = false;
        },
        setUserLastShiftStartTime: (state, time) => {
            state.user.last_shift_start = time;
        },
        setUserLastShiftEndTime: (state, time) => {
            state.user.last_shift_end = time;
        },
        showProgressBar: (state, {indeterminate, value = 0}) => {
            state.progressBarIndeterminate = indeterminate;
            state.progressBarValue = value;
            state.progressBarShown = true;
        },
        decreaseProgressBarValue: (state, value) => {
            state.progressBarValue -= value;
        },
        increaseProgressBarValue: (state, value) => {
            state.progressBarValue += value;
        },
        showProgressValue: (state, value) => {
            state.progressBarValue = value;
        },
        hideProgressBar: state => {
            state.progressBarShown = false;
        }
    },
    getters: {
        userFirstName: state => {
            return state.user.name.split(" ")[0];
        },
        userFirstAndLastName: state => {
            let parts = state.user.name.split(" ");
            if (parts.length > 1) {
                return `${parts[0]} ${parts[parts.length - 1]}`;
            }
            return state.user.name;
        },
        hasUserShiftStarted: state => {
            return !!state.user.shift_active;
        }
    },
    
});