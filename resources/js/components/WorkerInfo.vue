<template>
    <v-layout align-end justify-end>
        <div class="text-xs-center">
            <v-chip small :color="userType.color" text-color="white">
                <v-avatar>
                    <v-icon>{{ userType.icon }}</v-icon>
                </v-avatar>
                {{ this.$store.state.user.type }}
            </v-chip>
            <v-chip small>{{ shiftElapsedTime }}</v-chip>
            <v-btn small depressed
                   :color="this.$store.getters.hasUserShiftStarted ? 'error' : 'success'" @click="toggleShift">
                {{ this.$store.getters.hasUserShiftStarted ? 'End' : 'Start' }} shift
            </v-btn>
        </div>
    </v-layout>
</template>

<script>
    const moment = require('moment');

    export default {
        name: "WorkerInfo",
        data() {
            return {
                shiftElapsedTimeIntervalId: null,
                shiftElapsedTime: "",
                userType: {
                    icon: '',
                    color: ''
                },
            }
        },
        methods: {
            toggleShift() {
                if (this.$store.getters.hasUserShiftStarted) {
                    this.endShift();
                    this.showLastShiftTime();
                } else {
                    this.startShift();
                    this.startShiftElapsedTimeUpdated();
                }
            },
            startShift() {
                let user = this.$store.state.user;
                user.last_shift_start = moment().format("YYYY-DD-MM HH:mm:ss");
                user.shift_active = 1;

                this.updateUser(user, () => this.startShiftElapsedTimeUpdated());
            },
            endShift() {
                let user = this.$store.state.user;
                user.last_shift_end = moment().format("YYYY-DD-MM HH:mm:ss");
                user.shift_active = 0;

                this.updateUser(user, () => this.showLastShiftTime())
            },
            updateUser(user, success) {
                let photoPathParts = user.photo_url.split("/");
                user.photo_url = photoPathParts[photoPathParts.length - 1];

                axios.put(`/users/${user.id}`, user)
                    .then(response => {
                        if (response.status === 200) {
                            this.$store.commit('setUser', response.data.data);
                            success();
                        } else {
                            this.$store.commit("showAlertMessage", {
                                message: "Failed to start shift",
                                alertType: "alert-danger"
                            });
                        }
                    })
                    .catch(error => {
                        console.log(error);
                        this.$store.commit("showAlertMessage", {
                            message: "Failed to start shift",
                            alertType: "alert-danger"
                        });
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
                let startedTime = moment(this.$store.state.user.last_shift_start)
                    .format("DD/MM/YYYY HH:mm:ss");
                return `Your shift started at ${moment(startedTime).format("HH:mm")}
                    (${moment(startedTime).fromNow()})`;
            },
            showLastShiftTime() {
                this.clearShiftTimer();
                let endTime = moment(this.$store.state.user.last_shift_end)
                    .format("DD/MM/YYYY HH:mm:ss");
                this.shiftElapsedTime = `Your last shift ended at ${moment(endTime).format("HH:mm")}
                    (${moment(endTime).fromNow()})`
            },
            clearShiftTimer() {
                if (this.shiftElapsedTimeIntervalId != null) {
                    clearInterval(this.shiftElapsedTimeIntervalId);
                }
            },
            getUserTypeIcon() {
                switch (this.$store.state.user.type) {
                    case 'manager':
                        this.userType.icon = 'supervisor_account';
                        this.userType.color = 'red';
                        break;
                    case 'waiter':
                        this.userType.icon = 'sentiment_satisfied_alt';
                        this.userType.color = 'primary';
                        break;
                    case 'cook':
                        this.userType.icon = 'restaurant';
                        this.userType.color = 'orange';
                        break;
                    case 'cashier':
                        this.userType.icon = 'attach_money';
                        this.userType.color = 'teal';
                        break;
                }
            }
        },
        mounted() {
            this.getUserTypeIcon();
            if (this.$store.getters.hasUserShiftStarted) {
                this.startShiftElapsedTimeUpdated();
            } else {
                this.showLastShiftTime();
            }
        }
    }
</script>

<style scoped>

</style>