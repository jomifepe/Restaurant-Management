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
            mealToTerminate: null,
            terminateMealDialog: false,
            cancelOrdersDialog: false,

            /* auxiliary attributes */
            notDeliveredOrders: [],
            consumedItems: []
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
            },
            user() {
                return this.$store.state.user;
            }
        },
        sockets: {
            new_order_notify_manager(){
                this.showTopRightToast('New item ordered');
                this.reload();
            },
            meal_terminated_notify(user){
                this.showTopRightToast('Meal terminated by ' + user.name);
                this.reload();
            },
            newTable_meal_notify(table){
                this.showTopRightToast('New table('+ table +') for meal');
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
            }
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
                this.$socket.emit('newTable_meal', tableNumber);
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

            /* Meal termination process:
                1. Asks for confirmation
                2. performMealTermination() (get orders associated with meal)
                3. If the meal has not delivered orders, asks if the user wants to cancel them, 
                if not, goes to step 6
                4. cancelOrders()
                5. cancelOrder() (cancels each order recursively)
                6. terminateMeal() (finally terminated the meal)
                7. generateMealInvoice()
                8. createInvoiceItems()
                9. createInvoiceItem() (creates invoice items recursively)
                10. Refreshes the meals list */

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
                            let consumedItems = response.data
                                .filter(itemOrder => itemOrder.state === 'delivered');
                            this.notDeliveredOrders = response.data
                                .filter(itemOrder => itemOrder.state !== 'delivered');

                            /* if there are orders that were not delivered */
                            if (this.notDeliveredOrders.length > 0) {
                                this.$store.commit('hideProgressBar');
                                this.cancelOrdersDialog = true;
                            } else {
                                this.$store.commit('showProgressBar', {'indeterminate': true});

                                /* changes the meal to terminated */
                                this.terminateMeal(meal, consumedItems).then(() => {
                                    this.showSuccessToast('Meal successfully terminated');
                                    this.$store.commit('hideProgressBar');

                                    /* generate meal invoice and invoice items */
                                    // this.generateMealInvoice(meal).then(() => {
                                    //     this.$socket.emit('meal_terminated', this.$store.state.user);
                                    //     this.loadMeals();
                                    // });
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
            cancelOrders() {
                this.cancelOrdersDialog = false;
                this.$store.commit('showProgressBar', {'indeterminate': false, 'value': 0});
                /* enters recursion */
                this.cancelOrder(0, () => {
                    this.$store.commit('showProgressBar', {'indeterminate': true});
                    this.terminateMeal(this.mealToTerminate).then(() => {
                        //this.$socket.emit('meal_terminated', this.$store.state.user);
                        this.showSuccessToast('Meal successfully terminated');
                        this.loadMeals();
                        this.$store.commit('hideProgressBar');
                    });
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
                        }
                    })
                    .catch(error => {
                        this.showErrorLog(`Failed to cancel meal order #${order.id}`, error);
                        this.$store.commit('hideProgressBar');
                    })
            },
            terminateMeal(meal) {
                return new Promise((resolve, reject) => {
                    meal.state = 'terminated';
                    axios.patch(`/meals/${meal.id}`, meal)
                        .then(response => {
                            if (response.status === 200) {
                                this.sendNotificationMealTerminatedToManagers(meal);
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
            generateMealInvoice(meal, consumedItems) {
                return new Promise(resolve => {
                    let priceSum = consumedItems.reduce((acc, item) => acc + item.price);
                    let invoice = {
                        meal_id: meal.id,
                        total_price: priceSum
                    }
                    axios.post('/invoices', invoice)
                        .then(response => {
                            if (response.status === 201) {
                                let createdInvoice = response.data.data;
                                console.log(`Created invoice: ${createdInvoice}`);
                                this.createInvoiceItems(consumedItems, createdInvoice.id).then(() => {
                                    resolve();
                                })
                            } else {
                                this.showErrorToast(`Failed to generate invoice for meal #${meal.id}`);
                            }

                        })
                        .catch(error => {
                            this.showErrorLog(`Failed to generate invoice for meal #${meal.id}`, error);
                        });
                });
            },
            createInvoiceItems(consumedItems, invoiceId) {
                return new Promise(resolve => {
                    let groupedList = this.groupItemOrders(consumedItems);
                    createInvoiceItem(0, groupedList, invoiceId, () => resolve());
                })
            },
            createInvoiceItem(itemIndex, consumedItems, invoiceId, callback) {
                let currentItem = consumedItems[itemIndex];
                let invoiceItem = {
                    invoice_id: invoiceId,
                    item_id: consumedItems[itemIndex].id,
                    quantity: listFilteredByItem.length,
                    unit_price: currentItem.price,
                    sub_total_price: currentItem.quantity * currentItem.price
                }
                axios.post('/invoices/items', invoiceItem)
                    .then(response => {
                        if (response.status === 201) {
                            this.$store.commit('showProgressBar', {'indeterminate': false, 
                                'value': (itemIndex + 1) * (100 / consumedItems.length)
                            });
                            if (++itemIndex !== consumedItems.length) {
                                this.cancelOcreateInvoiceItemrder(itemIndex, 
                                    consumedItems, invoiceId, callback);
                            } else {
                                callback();
                                return;
                            }
                        } else {
                            this.showErrorToast(`Failed to create invoice item #${itemIndex + 1} 
                                for invoice ${invoiceId}`);
                            this.$store.commit('hideProgressBar');
                        }
                    })
                    .catch(error => {
                        this.showErrorLog(`Failed to create invoice item #${itemIndex + 1} 
                            for invoice ${invoiceId}`, error);
                        this.$store.commit('hideProgressBar');
                    });
            },
            groupItemOrders(itemList) {
                let grouped = [];
                invoiceItemsList.forEach((item, i) => {
                    if (!grouped.includes(item)) {
                        let groupedItem = item;
                        item.quantity = itemList.reduce((acc, it) => acc + it);
                        grouped.push(groupedItem);
                    }
                });
                return grouped;
            },
            sendNotificationMealTerminatedToManagers(meal){
                console.log("entrou send");
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