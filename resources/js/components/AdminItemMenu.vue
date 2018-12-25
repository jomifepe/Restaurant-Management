<template>
    <v-container grid-list-md>
		<v-flex xs12 class="elevation-1">
			<v-toolbar class="pb-3 grey lighten-3 elevation-0" light tabs>
				<v-text-field v-model="filter" class="mx-3 rounded-text-field" flat small
							label="Search" prepend-inner-icon="search" solo-inverted>
				</v-text-field>

				<NewItem v-if="isUserLoggedIn" @onGetItems="getItems"></NewItem>

				<template v-if="isUserLoggedIn && meal">
					<v-menu v-model="orderSummaryMenu" :close-on-content-click="false" left :nudge-width="200" :min-width="450">
						<v-btn slot="activator" light depressed round :disabled="selectedItems.length === 0"
							color="teal" class="white--text">
							{{ selectedItemsSubtotal }}
							<v-icon right dark>fas fa-euro-sign</v-icon>
						</v-btn>

						<v-toolbar flat light color="white">
							<v-toolbar-title>
								Order summary 
							</v-toolbar-title>
							<v-spacer></v-spacer>
							<v-toolbar-title>
								Total: <strong class="teal--text">{{ selectedItemsSubtotal }}€</strong>
							</v-toolbar-title>
						</v-toolbar>
						<v-card>
							<v-data-table :items="selectedItems" item-key="id" 
								:rows-per-page-items="summaryRowsPerPageItems" hide-headers>
								<template slot="items" slot-scope="props">
									<td>{{ getItemQuantity(props.item.id) }}x</td>
									<td>{{ props.item.name }}</td>
									<td class="text-xs-center">{{ props.item.price }}€</td>
									<td class="justify-center text-md-center">
										<v-tooltip top>
											<v-icon class="dt-action" slot="activator" 
												@click="decrementItemQuantity(props.item)">
												fas fa-minus-circle
											</v-icon>
											Remove item
										</v-tooltip>
									</td>
								</template>
							</v-data-table>
							<v-card-actions>
								<v-spacer></v-spacer>
								<v-btn @click="askForOrderConfirmation" color="teal" flat>Complete order</v-btn>
							</v-card-actions>
						</v-card>
					</v-menu>
				</template>

				<v-tabs slot="extension" v-model="tab" centered color="transparent" icons-and-text>
					<v-tabs-slider color="blue-grey darken-1"></v-tabs-slider>
					<v-tab v-for="(item, i) in tabs" :key="i">
						{{ item.name }}
						<v-icon>{{ item.icon }}</v-icon>
					</v-tab>
				</v-tabs>
			</v-toolbar>

			<v-tabs-items v-model="tab">
				<v-tab-item v-for="(itemFromType, i) in items" :key="i">
					<v-card class="elevation-0">
						<v-card-text class="pl-5 pr-5 grey lighten-3">
							<v-data-iterator row wrap :items="itemFromType" :rows-per-page-items="rowsPerPageItems"
							:pagination.sync="pagination" content-tag="v-layout" :search="filter">
								<v-flex slot="item" slot-scope="props" xs12 sm6 md4 lg3>
									<MenuCard :item="props.item" :meal="meal" :exists="hasSelectedItem(props.item.id)"
										@onItemSelect="selectItem" @updateList="getItems" @onGetItems="getItems()"></MenuCard>
								</v-flex>
							</v-data-iterator>
						</v-card-text>
					</v-card>
				</v-tab-item>
			</v-tabs-items>
			<v-dialog v-if="isUserLoggedIn && meal" v-model="orderSubmitDialog" max-width="450">
				<v-card>
					<v-card-text class="subheading">
						Do you want to submit a total of {{ totalItems }} item orders for meal #{{ meal.id }} on table {{ meal.table_number }}?
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn flat @click="orderSubmitDialog = false">
							Cancel
						</v-btn>
						<v-btn color="teal" flat="flat" @click="submitOrders">
							Submit
						</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
		</v-flex>
    </v-container>
</template>

<script>
	import MenuCard from './AdminItemMenuCard.vue';
	import axios from 'axios';
	import currency from 'currency.js'
	import moment from 'moment';
    import NewItem from "./NewItem";
	// const moment = require('moment');
	import {toasts} from '../mixin';

    export default {
		name: "OrderItemMenu",
		mixins: [toasts],
		components: {
            NewItem,
			MenuCard
		},
        data: () => ({
			meal: null,
			items: [],
			selectedItems: [],
			selectedItemsQuantities: [],
			submittedOrders: [],
			totalItems: 0,
			selectedItemsSubtotal: 0,

			/* ui & ux related attributes */
			orderSummaryMenu: false,
			progressBar: true,
			progressValue: 0,
			progressIndeterminate: true,
            tab: null,
            tabs: [
				{ name: 'dish', icon: 'fas fa-utensils' },
				{ name: 'drink', icon: 'fas fa-wine-glass-alt' }
			],
			filter: '',
            rowsPerPageItems: [4, 8, 12],
			pagination: { rowsPerPage: 12 },
			orderSubmitDialog: false,
			toastButtonClicked: false,
			summaryRowsPerPageItems: [],
            summaryPagination: { rowsPerPage: 5 },
		}),
		computed: {
			isUserLoggedIn() {
				return !!this.$store.state.user;
			}
		},
        sockets:{
            connect(){
                console.log('socket connected (socket ID = '+this.$socket.id+')');
            },
            new_order(dataFromServer){
                console.log(dataFromServer);
            },
        },
        methods: {
            getItems() {
				this.$store.commit('showProgressBar', {indeterminate: true});
                axios.get(`/items/type/dish`)
                    .then(dishesResponse => {
						if (dishesResponse.status === 200) {
							this.items = [];
							this.items.push(dishesResponse.data.data);
							axios.get(`/items/type/drink`)
								.then(drinksResponse => {
									if (drinksResponse.status === 200) {
										this.items.push(drinksResponse.data.data);
									}
									this.$store.commit('hideProgressBar');
								})
								.catch(error => {
									this.$store.commit('hideProgressBar');
									this.progressBar = false;
									console.log(error);
									this.showErrorToast('Failed to load items of type drink');
								})
						}
                    })
                    .catch(error => {
						this.$store.commit('hideProgressBar');
						console.log(error);
						this.showErrorToast('Failed to load items of type dish');
                    })
			},
			selectItem(item) {
				let exists = this.incrementItemQuantity(item);
				if (!exists) {
					this.selectedItems.push(item);
					this.selectedItemsQuantities.push(1);
				}

				this.totalItems += 1;
				this.selectedItemsSubtotal = currency(this.selectedItemsSubtotal)
					.add(item.price).format();
			},
			incrementItemQuantity(item) {
				for (let i = 0; i < this.selectedItems.length; i++) {
					if (this.selectedItems[i].id === item.id) {
						this.selectedItemsQuantities[i] =
							this.selectedItemsQuantities[i] + 1;
						return true;
					}
				}
				return false;
			},
			decrementItemQuantity(item) {
				this.totalItems -= 1;
				for (let i = 0; i < this.selectedItems.length; i++) {
					if (this.selectedItems[i].id === item.id) {
						if (this.selectedItemsQuantities[i] === 1) {
							this.selectedItemsQuantities.splice(i, 1);
							this.selectedItems.splice(i, 1);
						} else {
							this.selectedItemsQuantities[i]--;
						}
						this.selectedItemsSubtotal = currency(this.selectedItemsSubtotal)
							.subtract(item.price).format();
						return;
					}
				}
			},
			getItemQuantity(itemId) {
				for (let i = 0; i < this.selectedItems.length; i++) {
					if (this.selectedItems[i].id === itemId) {
						return this.selectedItemsQuantities[i];
					}
				}
				return -1;
			},
			hasSelectedItem(itemId) {
				for (let i = 0; i < this.selectedItems.length; i++) {
					if (this.selectedItems[i].id === itemId) {
						return true;
					}
				}
				return false;
			},
			askForOrderConfirmation() {
				if (this.selectedItems.length > 0) {
					this.orderSummaryMenu = false; 
					this.orderSubmitDialog = true
				}
			},
			submitOrder(itemsToSubmit, itemIndex, callback) {
				let item = itemsToSubmit[itemIndex];
				let newOrder = {
					state: 'pending',
					item_id: item.id,
					meal_id: this.meal.id,
					responsible_cook_id: null,
					start: moment().format('YYYY-MM-DD HH:mm:ss'),

				};

				axios.post('/orders', newOrder)
					.then(response => {
						if (response.status === 201) {
							this.submittedOrders.push(response.data);
							this.$store.commit('increaseProgressBarValue',
								(100 / itemsToSubmit.length));
							if (++itemIndex !== itemsToSubmit.length) {
								this.submitOrder(itemsToSubmit, itemIndex, callback);
							} else {
								callback();
								return;
							}
						} else {
							this.$store.commit('hideProgressBar');
							console.log(`Received status code ${response.status} while submitting order #${itemIndex}`);
							this.showErrorToast('Failed to submit orders');
							return;
						}
					})
					.catch(error => {
						this.$store.commit('hideProgressBar');
						this.showErrorLog('Failed to submit orders', error);
						return;
					})
			},
			submitOrders() {
				let itemsToSubmit = [];
				this.selectedItems.forEach((item, i) => {
					for (let j = 0; j < this.selectedItemsQuantities[i]; j++) {
						itemsToSubmit.push(item);
					}
				});

				this.submittedOrders = [];
				this.orderSubmitDialog = false;
				this.$store.commit('showProgressBar', {indeterminate: false});
				/* enters recursion */
				this.submitOrder(itemsToSubmit, 0, () => {
					this.$store.commit('hideProgressBar');
					this.showOrderSubmitSuccessToast();
					this.resetMenuState();
				});
			},
			undoOrder(orderIndex, callback) {
				let orderId = this.submittedOrders[orderIndex].id;

				axios.delete(`/orders/${orderId}`)
					.then(response => {
						if (response.status === 204) {
							this.$store.commit('decreaseProgressBarValue',
								(100 / this.submittedOrders.length));
							if (++orderIndex !== this.submittedOrders.length) {
								this.undoOrder(orderIndex, callback);
							} else {
								callback();
								return;
							}
						} else {
							this.$store.commit('hideProgressBar');
							console.log(`Received status code ${response.status} while deleting order #${orderIndex}`);
							this.showErrorToast('Failed to undo placed orders');
							return;
						}
					})
					.catch(error => {
						this.$store.commit('hideProgressBar');
						this.showErrorLog('Failed to undo placed orders', error);
						return;
					})
			},
			undoOrders() {
				this.$store.commit('showProgressBar', {indeterminate: false, value: 100});
				/* enters recursion */
				this.undoOrder(0, () => {
					this.$store.commit('hideProgressBar');
					this.showSuccessToast('Successfully deleted all previously submitted orders');
				});
			},
			confirmOrder(orderIndex, callback) {
				let order = this.submittedOrders[orderIndex];
				order.state = 'confirmed';

				axios.put(`/orders/${order.id}`, order)
					.then(response => {
						if (response.status === 200) {
							this.$store.commit('increaseProgressBarValue',
								(100 / this.submittedOrders.length));
							if (++orderIndex !== this.submittedOrders.length) {
								this.confirmOrder(orderIndex, callback);
							} else {
								callback();
								return;
							}
						} else {
							this.$store.commit('hideProgressBar');
							console.log(`Received status code ${response.status} while confirming order #${orderIndex}`);
							this.showErrorToast('Failed to confirm placed orders');
							return;
						}
					})
					.catch(error => {
						this.$store.commit('hideProgressBar');
						this.showErrorLog('Failed to confirm placed orders', error);
						return;
					})
			},
			confirmOrders() {
				this.$store.commit('showProgressBar', {indeterminate: false});
				return new Promise((resolve, reject) => {
					/* enters recursion */
					this.confirmOrder(0, () => {
						this.$store.commit('hideProgressBar');
						this.showSuccessToast('All placed orders were successfully confirmed');
                        this.sendNotificationToKitchen();
						resolve('success');

					});
				});
			},
            sendNotificationToKitchen(){
                this.$socket.emit('new_order', this.$store.state.user);
            },
			showOrderSubmitSuccessToast() {
				let toast = this.$toasted.show("Order(s) placed, redirecting to the meals page in 5 seconds", {
					position: "bottom-center",
					icon: "check",
					duration : 5000,
					onComplete: () => {
						if (!this.toastButtonClicked) {
							this.confirmOrders().then(() => {
								this.$router.push({ name: 'meal.orders', params: { mealId: this.meal.id}});
							})
						}
					},
					action: [
						{
							text: 'Stay', onClick: (e, toastObject) => {
								this.toastButtonClicked = true;
								toastObject.goAway(1);
								this.confirmOrders();
							}
						},
						{
							text: 'Skip', onClick: (e, toastObject) => {
								this.toastButtonClicked = true;
								toastObject.goAway(1);
								this.confirmOrders().then(() => {
									this.$router.push({ name: 'meal.orders', params: { mealId: this.meal.id}});
								})
							}
						},
						{
							text: 'Undo', onClick: (e, toastObject) => {
								this.toastButtonClicked = true;
								toastObject.goAway(1);
								this.undoOrders();
							}
						}
					]
				});
			},
			resetMenuState() {
				this.selectedItems = [];
				this.selectedItemsQuantities = [];
				this.selectedItemsSubtotal = 0;
				this.totalItems = 0;
				this.toastButtonClicked = false;
			}
		},
        mounted() {
			if (this.$route.params.mealId) {
				axios.get(`/meals/${this.$route.params.mealId}`)
					.then(response => {
						this.meal = response.data.data;
						this.$store.commit('setPanelTitle', `Ordering items for meal #${this.meal.id}`);
					})
					.catch(error => {
						this.showErrorLog('Failed to get meal identified by the given id', error);
					})
			} else {
				this.$store.commit('setPanelTitle', 'Menu');
			}
			this.getItems();
        }
    }
</script>

<style scoped>

</style>