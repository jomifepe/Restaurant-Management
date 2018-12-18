<template>
    <v-container grid-list-md>
		<v-flex zs12 class="elevation-1">
			<v-toolbar class="pb-3 grey lighten-3 elevation-0" light tabs>

				<v-progress-circular v-if="progressBar" indeterminate color="blue-grey"></v-progress-circular>
				<v-text-field v-model="filter" class="mx-3 rounded-text-field" flat small
							label="Search" prepend-inner-icon="search" solo-inverted>
				</v-text-field>
				<NewItem @onGetItems="getItems()"> </NewItem>
				<template v-if="meal">
					<v-chip color="teal" text-color="white">
						<v-avatar>
							<v-icon>fas fa-euro-sign</v-icon>
						</v-avatar>
						{{ selectedItemsSubtotal }}
					</v-chip>
					<v-btn class="chip-btn white--text" @click="orderSubmitDialog = true" color="teal"
						depressed light round :disabled="selectedItems.length === 0">
						Complete order
					</v-btn>
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
								<v-flex slot="item" slot-scope="props"
										xs12 sm6 md4 lg3>
									<MenuCard :item="props.item" :meal="meal" @onItemSelect="selectItem"
										@onItemDeselect="deselectItem" @updateList="getItems"></MenuCard>
								</v-flex>
							</v-data-iterator>
						</v-card-text>
					</v-card>
				</v-tab-item>
			</v-tabs-items>
			<v-dialog v-model="orderSubmitDialog" v-if="meal" max-width="350">
				<v-card>
					<v-card-text class="subheading">
						Do you want to submit a total of {{ selectedItems.length }} item orders for meal #{{ meal.id }} on table {{ meal.table_number }}?
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

    export default {
		name: "OrderItemMenu",
		components: {
            NewItem,
			MenuCard
		},
        data: () => ({
			progressBar: true,
			meal: null,
			items: [],
			selectedItems: [],
			submitedOrders: [],
			selectedItemsSubtotal: 0,
            tab: null,
            tabs: [
				{ name: 'dish', icon: 'fas fa-utensils' },
				{ name: 'drink', icon: 'fas fa-wine-glass-alt' }
			],
			filter: '',
            rowsPerPageItems: [4, 8, 12],
			pagination: { rowsPerPage: 12 },
			orderSubmitDialog: false,
			toastButtonClicked: false
        }),
        methods: {
            getItems() {
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
									this.progressBar = false;
								})
								.catch(error => {
									this.progressBar = false;
									console.log(error);
									this.$toasted.show('Failed to load items of type drink', {
										icon : 'error',
										position: "bottom-center",
										duration : 4000
									});
								})
						}
                    })
                    .catch(error => {
						this.progressBar = false;
						console.log(error);
						this.$toasted.show('Failed to load items of type dish', {
							icon : 'error',
							position: "bottom-center",
							duration : 4000
						});
                    })
			},
			selectItem(item) {
				this.selectedItems.push(item);
				this.selectedItemsSubtotal = currency(this.selectedItemsSubtotal)
					.add(item.price).format();
			},
			deselectItem(itemToRemove) {
				this.selectedItems.forEach((item, i) => {
					if (item.id === itemToRemove.id) {
						this.selectedItems.splice(i, 1);
					}
				});
				this.selectedItemsSubtotal = currency(this.selectedItemsSubtotal)
					.subtract(itemToRemove.price).format();
			}, 
			undoOrders() {
				this.submitedOrders.forEach((orderId, i) => {
					axios.delete(`/orders/${orderId}`)
						.then(response => {
							if (response.status === 204) {
								if (i === (this.submitedOrders.length - 1)) {
									this.$toasted.show('All previously submited orders were deleted', {
										icon : "check",
										position: "bottom-center",
										duration : 3000
									});
								}
							} else {
								this.$toasted.show(`Failed to undo order #${i + 1}`, {
									icon : "error",
									position: "bottom-center",
									duration : 3000
								});
							}
							this.toastButtonClicked = false;
						})
						.catch(error => {
							console.log(error);
							this.$toasted.show(`Failed to undo order #${i + 1}`, {
								icon : "error",
								position: "bottom-center",
								duration : 3000
							});
							this.toastButtonClicked = false;
							return;
						})
				});
			},
			submitOrders() {
				this.orderSubmitDialog = false;
				this.progressBar = true;
				this.selectedItems.forEach((item, i) => {
					let newOrder = {
						state: 'pending',
						item_id: item.id,
						meal_id: this.meal.id,
						responsible_cook_id: null,
						start: moment().format('YYYY-MM-DD HH:mm:ss')
					}
					axios.post(`/orders`, newOrder)
						.then(response => {
							if (response.status === 201) { 
								this.submitedOrders.push(response.data.id);
								if (i === (this.selectedItems.length - 1)) {
									let toast = this.$toasted.show("Order(s) placed, redirecting to the meals page in 5 seconds", {
										position: "bottom-center",
										icon: "check",
										duration : 5000,
										onComplete: () => {
											if (!this.toastButtonClicked) {
												this.$router.push({ name: 'meal.orders',
													params: { mealId: this.meal.id} });
											}
										},
										action : [
										{
											text : 'Skip',
											onClick: (e, toastObject) => {
												this.toastButtonClicked = true;
												toastObject.goAway(1);
												this.$router.push({ name: 'meal.orders',
													params: { mealId: this.meal.id} });
											}
										},
										{
											text : 'Undo' ,
											onClick: (e, toastObject) => {
												this.toastButtonClicked = true;
												toastObject.goAway(1);
												this.undoOrders();
											}
										}
									]
									});
								}
							}
							this.progressBar = false;
						})
						.catch(error => {
							this.progressBar = false;
							console.log(error);
							this.$toasted.show(`Failed to submit order #${i + 1}`, {
								icon : 'error',
								position: "bottom-center",
								duration : 3000
							});
							return;
						})
				});
				
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
						console.log(error);
						this.$toasted.show('Failed to get meal identified by the given id', {
							icon : 'error',
							position: "bottom-center",
							duration : 4000
						});
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