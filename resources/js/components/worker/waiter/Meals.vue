<template>
    <v-container grid-list-md fluid>
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
                        <v-text-field v-model="search" class="mr-2" append-icon="search"
                            label="Search" single-line hide-details></v-text-field>
                        <v-dialog v-model="filterDialog" max-width="450">
                            <v-btn slot="activator" color="primary" fab dark>
                                <v-icon>filter_list</v-icon>
                            </v-btn>
                            <v-card>
                                <v-card-title>
                                    <span class="headline">Filters</span>
                                </v-card-title>
                                <v-card-text class="pr-5 pb-3 pl-5 pt-3">
                                    <v-select :items="mealStateFilterList" v-model="mealStateFilter" chips
                                        label="Meal state" multiple>
                                    </v-select>
                                    <v-select v-if="this.isUserManager" :items="waiterFilterList" v-model="waiterFilter"
                                        label="Responsible waiter" multiple item-text="name" item-value="id">
                                        <template slot="selection" slot-scope="{ item, index }">
                                            <v-chip v-if="index === 0">
                                                <span>{{ item.name }}</span>
                                            </v-chip>
                                            <span v-if="index === 1" class="grey--text caption">
                                                (+{{ waiterFilter.length - 1 }} others)
                                            </span>
                                        </template>
                                    </v-select>
                                    <v-menu v-if="this.isUserManager" :close-on-content-click="false"
                                        v-model="filterDatePickerMenu" :nudge-right="40" lazy
                                        transition="scale-transition" offset-y full-width min-width="290px">
                                        <v-text-field slot="activator" v-model="filterDate" clearable
                                            label="Start date" prepend-icon="event" readonly>
                                        </v-text-field>
                                        <v-date-picker v-model="filterDate" @input="filterDatePickerMenu = false">
                                        </v-date-picker>
                                    </v-menu>
                                </v-card-text>
                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn flat @click="filterDialog = false">
                                        Close
                                    </v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-dialog>
                    </v-card-title>
                    <v-data-table :headers="myMealsHeaders" :items="filteredMeals" class="elevation-1" 
                            :search="search" :pagination.sync="pagination" :loading="myMealsProgressBar">
                        <v-progress-linear slot="progress" color="blue-grey" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <tr :class="{'newTableRecord': isSecondDateAfter(mountedTime, props.item.start), 
                                'clickable': true}" @click="showMealItems(props.item)">
                                <td>{{ props.item.id }}</td>
                                <td>{{ props.item.table_number }}</td>
                                <td v-if="isUserManager">{{ getReponsibleWaiterHR(props.item) }}</td>
                                <td>{{ formatDate(props.item.start) }}</td>
                                <td>{{ props.item.total_price_preview }}€</td>
                                <td :class="getMealStateTextColor(props.item.state)">
                                    <strong>{{ props.item.state }}</strong>
                                </td>
                                <td class="justify-center text-md-center dt-actions">
                                    <span class="grey--text" v-if="props.item.state !== 'active' && 
                                        (props.item.state !== 'terminated' || !isUserManager)">
                                        No actions
                                    </span>
                                    <v-tooltip top v-if="props.item.state === 'active'">
                                        <v-btn slot="activator" icon
                                            @click.stop="askToConfirmMealTermination(props.item)">
                                            <v-icon>fas fa-check-circle</v-icon>
                                        </v-btn>
                                        <span>Terminate meal</span>
                                    </v-tooltip>
                                    <v-tooltip top v-if="props.item.state === 'terminated'">
                                        <v-btn icon slot="activator" v-if="isUserManager" 
                                            @click.stop="askToConfirmMealNotPaid(props.item)">
                                            <v-icon>
                                               money_off
                                            </v-icon>
                                        </v-btn>
                                        <span>Declare meal as not paid</span>
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
						<v-btn color="orange darken-1" flat="flat" @click="checkForNotDeliveredOrders">
							Yes
						</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
            <v-dialog v-model="cancelOrdersDialog" max-width="450">
				<v-card>
					<v-card-text class="subheading">
						This meal has orders that were not delivered. These orders are going to be canceled.
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn flat @click="cancelOrdersDialog = false">Cancel</v-btn>
						<v-btn color="red accent-3" flat="flat" 
                            @click="cancelOrdersDialog = false; performMealTermination()">
							Ok
						</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
            <v-dialog v-model="mealNotPaidDialog" max-width="450">
				<v-card>
					<v-card-text class="subheading">
						Are you sure you want to declare this meal as <span class="red--text text--accent-3">Not Paid</span>, as well as change it's orders as <span class="red--text text--accent-3">Not Delivered</span>?
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn flat @click="mealNotPaidDialog = false">Cancel</v-btn>
						<v-btn color="red accent-3" flat="flat" 
                            @click="mealNotPaidDialog = false; declareMealAsNotPaid()">
							Confirm
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
    import {toasts, helper} from '../../../mixin';

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
            terminateMealDialog: false,
            cancelOrdersDialog: false,
            mealNotPaidDialog: false,
            filterDialog: false,
            mealStateFilterList: ['active', 'terminated', 'paid', 'not paid'],
            mealStateFilter: ['active', 'terminated'],
            waiterFilterList: [],
            waiterFilter: [],
            filterDate: '',
            filterDatePickerMenu : false,
            mountedTime: null,

            /* auxiliary attributes */
            hasDeliveredOrders: false,
            mealToTerminate: null,
            mealToDeclareAsNotPaid: null
        }), 
        computed: {
            myMealsHeaders() {
                let headers = [
                    { text: 'Id', align: 'left', value: 'id' },
                    { text: 'Table Number', align: 'left', value: 'table_number' }
                ];
                if (this.isUserManager) {
                    headers.push({ text: 'Waiter', value: 'responsible_waiter_id' });
                }
                headers = headers.concat([
                    { text: 'Started', value: 'start' },
                    { text: 'Price', value: 'total_price_preview' },
                    { text: 'State', value: 'state' },
                    { text: 'Actions', value: '', sortable: false, align: 'center'}
                ])
                return headers;
            },
            user() {
                return this.$store.state.user;
            },
            filteredMeals() {
                return this.meals.filter(meal => {
                    let hasState = this.mealStateFilter.includes(meal.state);
                    let hasWaiter = this.waiterFilter.length == 0 ||
                        this.waiterFilter.includes(meal.responsible_waiter_id);
                    let filterDateFormatted = moment(this.filterDate).format('YYYY-MM-DD');
                    let mealDateFormatted = moment(meal.start).format('YYYY-MM-DD');
                    let isOnDate = !this.filterDate || filterDateFormatted === mealDateFormatted;
                    return hasState && hasWaiter && isOnDate;
                });
            },
        },
        sockets: {
            new_order_notify_manager() {
                this.showTopRightToast('New item ordered');
                this.reload();
            },
            meal_terminated_notify(user) {
                this.showTopRightToast('Meal terminated by ' + user.name);
                this.reload();
            },
            newTable_meal_notify(table) {
                this.showTopRightToast('New table('+ table +') for meal');
                this.reload();
            },
            meal_not_paid_reload_waiter_table(meal){
                this.reload();
            },
            reload_meals(){
                this.reload();
            },
            order_prepared_notify_manager(cook){
                this.showTopRightToast('Order prepared by ('+cook.name+')');
                this.reload();
            },
            order_in_preparation_notify(cook){
                this.showTopRightToast('Order in preparation by ('+ cook.name+')');
                this.reload();
            },
            order_delivered_notify(waiter){
                this.showTopRightToast('Order delivered by ('+ waiter.name+')');
                this.reload();
            },
            meal_paid_notify(meal){
                this.showTopRightInfoToast("Invoice paid table(" + meal.table_number +")")
                this.reload();
            }
        },  
        methods: {
            saveOrder(order){
                return axios.patch(`/orders/${order.id}`, order);
            },            
            getAllNotDelOrdersFormAMeal(meal){
                return axios.get(`/orders/meal/${meal.id}/notDeliveredItems`);
            },
            changeAllNotDeliveredOrdersFromAMealToNotDelivered(meal){
                let orders= null;
                var promises= [];
                this.getAllNotDelOrdersFormAMeal(meal)
                    .then(response => {
                        orders = response.data;
                        orders.forEach(order => {
                            order.state = 'not delivered';
                            promises.push(this.saveOrder(order));
                            
                        })
                        //console.log(promises);
                        axios.all(promises)
                            .then(axios.spread((...responses) => {
                                // responses.forEach(res => console.log('Success'))
                                // console.log('submitted all axios calls');
                                this.$socket.emit('update_not_pending');
                            }))

                    })
                    .catch(error => {
                        this.showErrorLog('Failed to change orders', error);
                    })
            },
            getInvoice(meal){
                return axios.get(`/meals/${meal.id}/invoice`);
            },
            updateInvoice(invoice){
                return axios.patch(`/invoices/${invoice.id}`, invoice);
            },
            askToConfirmMealNotPaid(meal) {
                if (this.isUserInShift()) {
                    this.mealToDeclareAsNotPaid = meal;
                    this.mealNotPaidDialog = true;
                }
            },
            declareMealAsNotPaid() {
                if (!this.isUserInShift()) return;

                let meal = this.mealToDeclareAsNotPaid;
                meal.state = 'not paid';
                let invoice = null;

                axios.patch(`/meals/${meal.id}`, meal)
                    .then(response => {
                        if (response.status === 200) {
                            this.$socket.emit('meal_not_paid', meal);
                            this.showSuccessToast('Meal edited');
                            this.getInvoice(meal)
                                .then(responseInvoice => {
                                    invoice = responseInvoice.data.data;
                                    invoice.state = 'not paid';
                                    this.updateInvoice(invoice)
                                        .then(responseInvoiceUpdate => {
                                            this.showSuccessToast('Invoice edited');
                                            this.changeAllNotDeliveredOrdersFromAMealToNotDelivered(meal);
                                            this.loadMeals();
                                            if (this.$route.params.mealId === meal.id) {
                                                this.$router.push({ name: 'meals' });
                                            }
                                        })
                                        .catch(error => {
                                            this.showErrorToast('Failed to edit invoice');
                                        })
                                })
                                .catch(error => {
                                    if(error.response.status != 404){
                                        this.showErrorLog('Failed to get meal invoice', error);
                                    }
                                })                              
                        } 
                    })
                    .catch(error => {
                        this.showErrorLog(`Failed to update meal #${meal.id}`, error);
                        this.$store.commit('hideProgressBar');
                    })
            },
            loadMeals() {
                return new Promise(resolve => {
                    let target = this.isUserManager ? '/meals' : `/meals/waiter/${this.user.id}`;
                    axios.get(target)
                        .then(response => {
                            if (response.status === 200) {
                                this.meals = response.data.data;
                                this.myMealsProgressBar = false;
                                resolve();
                            }
                        });
                })
            },
            loadWaiters() {
                axios.get('/users/all/waiter')
                    .then(response => {
                        if (response.status === 200) {
                            this.waiterFilterList = response.data.data;
                        } else {
                            this.showErrorToast('Failed to get all waiters to populate filters');
                        }
                    })
                    .catch(error => {
                        this.showErrorLog('Failed to get all waiters to populate filters', error);
                    })
            },
            formatDate: date => moment(date).format("YYYY-MM-DD, HH:mm"),
            onMealCreated(tableNumber) {
                this.$socket.emit('newTable_meal', tableNumber);
                this.showSuccessToast(`Meal successfuly started for table ${tableNumber}`);
                this.loadMeals();
            },
            showMealItems(meal) {
                this.$router.push({ name: 'meal.orders', params: { mealId: meal.id }});
            },
            askToConfirmMealTermination(meal) {
                if (this.isUserInShift()) {
                    this.mealToTerminate = meal;
                    this.terminateMealDialog = true;
                }
            },
            checkForNotDeliveredOrders() {
                this.terminateMealDialog = false;
                let meal = this.mealToTerminate;

                this.$store.commit('showProgressBar', {'indeterminate': true});

                /* get the orders for the selected meal */
                axios.get(`/orders/meal/${meal.id}`)
                    .then(response => {
                        if (response.status === 200) {
                            let notDeliveredOrders = response.data
                                .filter(itemOrder => itemOrder.state !== 'delivered');
                            this.hasDeliveredOrders = (response.data.length > notDeliveredOrders.length);

                            /* if there are orders that were not delivered */
                            if (notDeliveredOrders.length > 0) {
                                this.$store.commit('hideProgressBar');
                                this.cancelOrdersDialog = true;
                            } else {
                                this.$store.commit('showProgressBar', {'indeterminate': true});
                                this.performMealTermination();
                            }
                        } else {
                            this.showErrorToast('Failed to cancel meal orders');
                        }
                    })
                    .catch(error => {
                        this.showErrorLog('Failed to cancel meal orders', error);
                    });
            },
            performMealTermination() {
                let meal = this.mealToTerminate;
                this.terminateMeal(meal).then(() => {
                    let showSuccessMessages = (() => {
                        this.loadMeals();
                        this.showSuccessToast('Meal successfully terminated');
                        this.$socket.emit('meal_terminated', this.user);
                        if (this.hasDeliveredOrders) {
                            this.showSuccessToast('Meal invoice successfully generated');
                            this.$socket.emit('invoice_generated', this.user);
                        }
                        this.$store.commit('hideProgressBar')
                        if (this.$route.params.mealId === meal.id) {
                            this.$router.push({ name: 'meals' });
                        }
                    });

                    if (this.hasDeliveredOrders) {
                        this.generateMealInvoice(meal).then(showSuccessMessages());
                    } else {
                        showSuccessMessages();
                    }
                });
            },
            terminateMeal(meal) {
                return new Promise(resolve => {
                    axios.post(`/meals/${meal.id}/terminate`)
                        .then(response => {
                            if (response.status === 200) {
                                this.sendNotificationMealTerminatedToManagers(meal);
                                resolve();
                            } else {
                                this.showErrorToast(`Failed to terminate meal #${meal.id}`);
                                this.$store.commit('hideProgressBar');
                            }
                        })
                        .catch(error => {
                            this.showErrorLog(`Failed to terminate meal #${meal.id}`, error);
                            this.$store.commit('hideProgressBar');
                        });
                })
            },
            generateMealInvoice(meal) {
                return new Promise(resolve => {
                    axios.post('/invoices', { meal_id: meal.id })
                        .then(response => {
                            if (response.status === 201) {
                                let createdInvoice = response.data.data;
                                this.createInvoiceItems(createdInvoice.id, meal.id).then(() => resolve());
                            } else {
                                this.showErrorToast(`Failed to generate invoice for meal #${meal.id}`);
                            }

                        })
                        .catch(error => {
                            this.showErrorLog(`Failed to generate invoice for meal #${meal.id}`, error);
                        });
                });
            },
            createInvoiceItems(invoiceId, mealId) {
                return new Promise((resolve,) => {
                    axios.post(`/invoices/${invoiceId}/items/${mealId}`)
                        .then(response => {
                            if (response.status === 201) {
                                resolve();
                            } else {
                                this.showErrorToast(`Failed to create invoice items for invoice ${invoiceId}`);
                            }
                        })
                        .catch(error => {
                            this.showErrorLog(`Failed to create invoice items for invoice ${invoiceId}`, error);
                        });
                })
            },
            sendNotificationMealTerminatedToManagers(meal){
                let content = {
                        'sender': this.user,
                        'title': `Meal Terminated`,
                        'text': `Meal ${meal.id} from table ${meal.table_number} is now terminated`
                }
                
                this.$socket.emit('to_all_managers', content);
            },
            getReponsibleWaiterHR(meal) {
                return `${this.userFirstAndLastName(meal.responsible_waiter_name)}
                    (${meal.responsible_waiter_id})`;
            },
            reload(){
                this.loadMeals();
                setTimeout(function(){ this.showMealItems; }, 2000);
            }
        },
        mounted() {
            this.$store.commit('setPanelTitle', 'Meals');
            this.arrayAdd(this.mealStateFilter, 'active');
            this.arrayAdd(this.mealStateFilter, 'terminated');
            this.loadMeals();
            if (this.isUserManager) {
                this.loadWaiters();
            }
            this.mountedTime = moment().format();
        }
    }
</script>

<style scoped>

</style>