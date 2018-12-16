<template>
    <v-container grid-list-md>
		<v-flex zs12 class="elevation-1">
			<v-toolbar class="pb-3 grey lighten-3 elevation-0" light tabs>
				<v-text-field v-model="filter"
							class="mx-3 rounded-text-field"
							flat small
							label="Search"
							prepend-inner-icon="search"
							solo-inverted></v-text-field>
				<template v-if="meal">
					<v-chip color="teal"
						text-color="white">
						<v-avatar>
							<v-icon>fas fa-euro-sign</v-icon>
						</v-avatar>
						{{ selectedItemsSubtotal }}
					</v-chip>
					<v-btn class="chip-btn white--text" 
						@click="orderSubmitDialog = true"
						color="teal" 
						depressed light round
						:disabled="selectedItems.length === 0">
						Complete order
					</v-btn>
				</template>

				<v-tabs slot="extension"
						v-model="tab"
						centered
						color="transparent"
						icons-and-text>
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
							<v-data-iterator row wrap :items="itemFromType"
							:rows-per-page-items="rowsPerPageItems"
							:pagination.sync="pagination"
							content-tag="v-layout"
							:search="filter">
								<v-flex slot="item" slot-scope="props"
										xs12 sm6 md4 lg3>
									<MenuCard :item="props.item" 
										:meal="meal"
										@onItemSelect="selectItem"
										@onItemDeselect="deselectItem"></MenuCard>
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
						<v-btn flat
							@click="orderSubmitDialog = false">
							Cancel
						</v-btn>
						<v-btn
							color="teal"
							flat="flat"
							@click="submitOrders">
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
	// const moment = require('moment');

    export default {
		name: "OrderItemMenu",
		components: {
			MenuCard
		},
        data: () => ({
			meal: null,
			items: [],
			selectedItems : [],
			selectedItemsSubtotal: 0,
            tab: null,
            tabs: [
				{ name: 'dish', icon: 'fas fa-utensils' },
				{ name: 'drink', icon: 'fas fa-wine-glass-alt' }
			],
			filter: '',
            rowsPerPageItems: [4, 8, 12],
			pagination: { rowsPerPage: 12 },
			orderSubmitDialog: false
        }),
        methods: {
            getItems() {
                axios.get(`/items/type/dish`)
                    .then(response => {
                        this.items.push(response.data.data);
                        axios.get(`/items/type/drink`)
                            .then(response => this.items.push(response.data.data))
                            .catch(error => {
                                console.log(error);
                            })
                    })
                    .catch(error => {
                        console.log(error);
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
			submitOrders() {
				let count = 0;
				this.selectedItems.forEach(item => {
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
								console.log("Success");
								if (++count === this.selectedItems.length) {
									this.$router.push({ name: 'meals' });
								}
							}
						})
						.catch(error => {
							console.log(error);
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