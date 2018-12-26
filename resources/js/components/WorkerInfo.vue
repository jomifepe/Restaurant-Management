<template>
    <v-layout align-end justify-end>
        <div class="text-xs-center">
            <v-chip small :color="getUserAppearence(user).color" text-color="white">
                <v-avatar>
                    <v-icon>{{ getUserAppearence(user).icon }}</v-icon>
                </v-avatar>
                {{ user.type }}
            </v-chip>
            <v-chip small>{{ shiftElapsedTime }}</v-chip>
            <v-btn small depressed :color="!!user.shift_active ? 'error' : 'success'" @click="toggleShift">
                {{ !!user.shift_active ? 'End' : 'Start' }} shift
            </v-btn>
        </div>
    </v-layout>
</template>

<script>
    import axios from 'axios';
    import moment from 'moment';
    import {toasts, helper} from '../mixin';

    export default {
        name: "WorkerInfo",
        mixins: [toasts, helper],
        props: ['user'],
        data: () => ({
            shiftElapsedTimeIntervalId: null,
            shiftElapsedTime: ''
        }),
        methods: {
            clearNotifications(){
                this.$emit('onClearNotifications');
            },
            joinSockets(user){
                this.$socket.emit('user_enter', user);
            },
            leaveSockets(user){
                this.$socket.emit('user_exit', user);
            },
            toggleShift() {
                if (this.user.shift_active) {
                    this.endShift();
                } else {
                    this.startShift();
                }
            },
            startShift() {
                this.updateUser(true).then((updatedUser) => {
                    this.$store.commit('setUser', updatedUser);
                    this.startShiftElapsedTimeUpdated();
                    this.joinSockets(updatedUser);
                });
            },
            endShift() {
                this.updateUser(false).then((updatedUser) => {
                    this.$store.commit('setUser', updatedUser);
                    this.showLastShiftTime();
                    this.leaveSockets(updatedUser);
                    this.clearNotifications();
                });
            },
            updateUser(startShift) {
                return new Promise(resolve => {
                    axios.patch(`/users/${this.user.id}`, {
                        [startShift ?
                            'last_shift_start' :
                            'last_shift_end'
                        ]: moment().format("YYYY-MM-DD HH:mm:ss"),
                        shift_active: +startShift
                    })
                        .then(response => {
                            if (response.status === 200) {
                                resolve(response.data.data);
                            } else {
                                this.showErrorToast('Failed update shift state');
                            }
                        })
                        .catch(error => {
                            this.showErrorLog('Failed update shift state', error);
                        })
                })
            },
            startShiftElapsedTimeUpdated() {
                this.clearShiftTimer();
                this.shiftElapsedTime = this.getStartedShiftTimeDescription();
                this.shiftElapsedTimeIntervalId = setInterval(function () {
                    this.shiftElapsedTime = this.getStartedShiftTimeDescription();
                }.bind(this), 10000);
            },
            getStartedShiftTimeDescription() {
                let startedTime = this.$store.state.user.last_shift_start;
                return `Your shift started at ${moment(startedTime).format("HH:mm")}
                    (${moment(startedTime).fromNow()})`;
            },
            showLastShiftTime() {
                this.clearShiftTimer();
                let endTime = moment(this.$store.state.user.last_shift_end)
                this.shiftElapsedTime = `Your last shift ended at ${moment(endTime).format("HH:mm")}
                    (${moment(endTime).fromNow()})`
            },
            clearShiftTimer() {
                if (this.shiftElapsedTimeIntervalId != null) {
                    clearInterval(this.shiftElapsedTimeIntervalId);
                }
            },
            joinSockets(user) {
                this.$socket.emit('user_enter', user);
            },
            leaveSockets(user) {
                this.$socket.emit('user_exit', user);
            }
        },
        mounted() {
            if (this.user.shift_active) {
                this.startShiftElapsedTimeUpdated();
            } else {
                this.showLastShiftTime();
            }
            this.$store.watch(() => {
                if (!this.$store.state.user) {
                    this.clearShiftTimer();
                }
            });
        }
    }
</script>

<style scoped>

</style>