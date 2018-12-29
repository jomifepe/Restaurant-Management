<template>
    <v-container grid-list-md>
        <v-layout row wrap>
            <v-flex xs12>
                <v-toolbar flat color="gray">
                    <v-toolbar-title>
                        {{ isUserManager ? 'All Meals' : 'My Meals' }}
                        <span class="body-1">(click to display a meal's orders)</span>
                    </v-toolbar-title>
                    <v-spacer></v-spacer>
                    <CreateMeal @onCreate="onMealCreated"></CreateMeal>
                </v-toolbar>
                <v-card>
                    <v-card-title>
                        <v-spacer></v-spacer>
                        <v-text-field v-model="search" append-icon="search"
                            label="Search" single-line hide-details></v-text-field>
                    </v-card-title>
                    <v-data-table :headers="myMealsHeaders" :items="meals" class="elevation-1"
                            :search="search" :pagination.sync="pagination" :loading="myMealsProgressBar">
                        <v-progress-linear slot="progress" color="blue-grey" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <tr class="clickable" @click="showMealItems(props.item)">
                                <td>{{ props.item.table_number }}</td>
                                <td v-if="isUserManager">{{ getReponsibleWaiterHR(props.item) }}</td>
                                <td>{{ formatDate(props.item.start) }}</td>
                                <td>{{ formatDate(props.item.created_at.date) }}</td>
                                <td>{{ props.item.total_price_preview }}€</td>
                                <td :class="getStateColor(props.item.state)">
                                    <strong>{{ props.item.state }}</strong>
                                </td>
                                <td class="justify-center text-md-center dt-actions">
                                    <v-tooltip top v-if="props.item.state === 'active'">
                                        <v-btn slot="activator" icon
                                            @click="askToConfirmMealTermination(props.item)">
                                            <v-icon>
                                                fas fa-check-circle
                                            </v-icon>
                                        </v-btn>
                                        <span>Terminate meal</span>
                                    </v-tooltip>
                                </td>
                            </tr>
                        </template>
                    </v-data-table>
                </v-card>
            </v-flex>
            <v-flex xs12 id="mealOrders" class="mt-5">
                <router-view></router-view>
            </v-flex>
            <v-dialog v-model="terminateMealDialog" max-width="450">
				<v-card>
					<v-card-text class="subheading">
						Are you sure you want to declare this meal as <span class="orange--text darken-1">terminated</span> ?
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn flat @click="terminateMealDialog = false; mealToTerminate = null">
							No
						</v-btn>
						<v-btn color="orange darken-1" flat="flat" @click="performMealTermination">
							Yes
						</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
            <v-dialog v-model="cancelOrdersDialog" max-width="450">
				<v-card>
					<v-card-text class="subheading">
						This meal has orders that were not delivered. Do you want to cancel these orders?
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn flat @click="cancelOrdersDialog = false">No</v-btn>
						<v-btn color="red accent-3" flat="flat" @click="cancelOrders">
							Yes
						</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
        </v-layout>
    </v-container>
</template>

<script>
    import CreateMeal from './CreateMeal';
    import MealOrders from './MealOrders';
    import axios from 'axios';
    import moment from 'moment';
    import {toasts, helper} from '../mixin';

    export default {
        name: "Orders",
        mixins: [toasts, helper],
        components: {
            CreateMeal,
            MealOrders
        },
        data: () => ({
            meals: [],
            search: '',
            pagination: { sortBy: 'state' },
            myMealsProgressBar: true,
            mealToTerminate: null,
            terminateMealDialog: false,
            cancelOrdersDialog: false,

            /* auxiliary attributes */
            notDeliveredOrders: []
        }), 
        computed: {
            myMealsHeaders() {
                let headers = [{ text: 'Table Number', align: 'left', value: 'table_number' }];
                if (this.isUserManager) {
                    headers.push({ text: 'Waiter', value: 'responsible_waiter_id' });
                }
                headers = headers.concat([
                    { text: 'Started', value: 'start' },
                    { text: 'Created', value: 'created_at' },
                    { text: 'Price', value: 'total_price_preview' },
                    { text: 'State', value: 'state' },
                    { text: 'Actions', value: '', sortable: false, align: 'center'}
                ])
                return headers;
            }
        },
        sockets: {
            new_order_notify_manager(){
                this.showTopRightToast('New Item Ordered');
                this.reload();
            },
            meal_terminated_notify(user){
                this.showTopRightToast('Meal terminated by ' + user.name);
                this.reload();
            },
        },
        methods: {
            loadMeals() {
                let target = this.isUserManager ? 'meals/manager' :
                    `/meals/waiter/${this.$store.state.user.id}`;

                axios.get(target)
                    .then(response => {
                        if (response.status === 200) {
                            this.meals = response.data.data;
                            this.myMealsProgressBar = false;
                        }
                    });
            },
            formatDate: date => moment(date).format("YYYY-MM-DD, HH:mm"),
            onMealCreated(tableNumber) {
                this.showSuccessToast(`Meal successfuly started for table ${tableNumber}`);
                this.loadMeals();
            },
            getStateColor(state) {
                return state === 'active' ? 'red--text' :
                    state === 'terminated' ? 'orange--text' :
                        state === 'not paid' ? 'blue--text' : 'green--text';
            },
            showMealItems(meal) {
                this.$router.push({ name: 'meal.orders', params: { mealId: meal.id }});
            },
            askToConfirmMealTermination(meal) {
                this.mealToTerminate = meal;
                this.terminateMealDialog = true;
            },
            performMealTermination() {
                this.terminateMealDialog = false;
                let meal = this.mealToTerminate;

                this.$store.commit('showProgressBar', {'indeterminate': true});
                /* get the orders for the selected meal */
                axios.get(`/orders/meal/${meal.id}`)
                    .then(response => {
                        if (response.status === 200) {
                            this.notDeliveredOrders = response.data
                                .filter(order => order.state !== 'delivered');

                            /* if there are orders that were not delivered */
                            if (this.notDeliveredOrders.length > 0) {
                                this.$store.commit('hideProgressBar');
                                this.cancelOrdersDialog = true;
                            } else {
                                this.$store.commit('showProgressBar', {'indeterminate': true});
                                /* changes the meal to terminated */
                                this.terminateMeal(meal).then(() => {
                                    this.showSuccessToast('Meal successfully terminated');
                                    this.$store.commit('hideProgressBar');
                                    this.loadMeals();
                                });
                            }
                        } else {
                            this.showErrorToast('Failed to cancel meal orders');
                        }
                    })
                    .catch(error => {
                        this.showErrorLog('Failed to cancel meal orders', error);
                    })
            },
            cancelOrder(orderIndex, callback) {
                let order = this.notDeliveredOrders[orderIndex];
                order.state = 'not delivered';

                axios.patch(`/orders/${order.id}`, order)
                    .then(response => {
                        if (response.status === 200) {
                            this.$store.commit('showProgressBar', {'indeterminate': false, 
                                'value': (orderIndex + 1) * (100 / this.notDeliveredOrders.length)
                            });
                            if (++orderIndex !== this.notDeliveredOrders.length) {
                                this.cancelOrder(orderIndex, callback);
                            } else {
                                callback();
                                return;
                            }
                        } else {
                            this.showErrorToast(`Failed to cancel meal order #${order.id}`);
                            this.$store.commit('hideProgressBar');
                            return;
                        }
                    })
                    .catch(error => {
                        this.showErrorLog(`Failed to cancel meal order #${order.id}`, error);
                        this.$store.commit('hideProgressBar');
                    })
            },
            cancelOrders() {
                this.cancelOrdersDialog = false;
                this.$store.commit('showProgressBar', {'indeterminate': false, 'value': 0});
                /* enters recursion */
                this.cancelOrder(0, () => {
                    this.$store.commit('showProgressBar', {'indeterminate': true});
                    this.terminateMeal(this.mealToTerminate).then(() => {

                        this.$socket.emit('meal_terminated', this.$store.state.user);

                        this.showSuccessToast('Meal successfully terminated');
                        this.loadMeals();
                        this.$store.commit('hideProgressBar');
                    });
                })
            },
            terminateMeal(meal) {
                return new Promise((resolve, reject) => {
                    meal.state = 'terminated';
                    axios.patch(`/meals/${meal.id}`, meal)
                        .then(response => {
                            if (response.status === 200) {
                                resolve('Success');
                            } else {
                                this.showErrorToast(`Failed to terminate meal #${meal.id}`);
                                this.$store.commit('hideProgressBar');
                            }
                        })
                        .catch(error => {
                            this.showErrorLog(`Failed to terminate meal #${meal.id}`, error);
                            this.$store.commit('hideProgressBar');
                        })
                })
            },
            getReponsibleWaiterHR(meal) {
                return `${this.userFirstAndLastName(meal.responsible_waiter_name)}
                    (${meal.responsible_waiter_id})`;
            },
            reload(){
                this.loadMeals();
                setTimeout(function(){ this.showMealItems; }, 2000);
            },
        },
        mounted() {
            this.$store.commit('setPanelTitle', 'Meals');
            this.loadMeals();
        }
    }
</script>

<style scoped>

</style>