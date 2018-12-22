<template>
	<v-card>
		<v-img :src="item.photo_url" aspect-ratio="1.75"></v-img>
		<v-card-title>
			<h4>{{item.name }}</h4>
		</v-card-title>
		<v-divider></v-divider>
		<v-list dense>
			<v-list-tile>
				<v-list-tile-content>Type:</v-list-tile-content>
				<v-list-tile-content class="align-end">{{ item.type }}</v-list-tile-content>
			</v-list-tile>
			<v-list-tile>
				<v-list-tile-content>Price:</v-list-tile-content>
				<v-list-tile-content class="align-end">{{ item.price }}€</v-list-tile-content>
			</v-list-tile>
			<v-list-tile>
				<v-list-tile-content>Order time:</v-list-tile-content>
				<v-list-tile-content class="align-end">{{ item.order_created_at }}</v-list-tile-content>
			</v-list-tile>
			<v-list-tile>
				<v-list-tile-content>Order end:</v-list-tile-content>
				<v-list-tile-content class="align-end">{{ item.order_end || 'N/A' }}</v-list-tile-content>
			</v-list-tile>
			<v-list-tile>
				<v-list-tile-content>Order state:</v-list-tile-content>
				<v-list-tile-content class="align-end">
					<v-btn small :color="getStateColor(item.order_state)" round @click="showDeliverMealDialog(item.order_state)"
						class="text-capitalize white--text btn-no-margin elevation-0" depressed="">
						{{ item.order_state }}
					</v-btn>
					<v-dialog v-model="deliverMealDialog" max-width="380">
						<v-card>
							<v-card-text class="subheading">
								Do you want to change this order to delivered?
							</v-card-text>
							<v-card-actions>
								<v-spacer></v-spacer>
								<v-btn flat @click="deliverMealDialog = false">
									No
								</v-btn>
								<v-btn color="teal" flat="flat" @click="deliverMealDialog = false; deliverOrder()">
									Yes
								</v-btn>
							</v-card-actions>
						</v-card>
					</v-dialog>
				</v-list-tile-content>
			</v-list-tile>
		</v-list>
	</v-card>
</template>

<script>
	import {toasts} from '../mixin.js';

	export default {
		props: ['item'],
		mixins: [toasts],
		data: () => ({
			deliverMealDialog: false
		}),
		methods: {
            getStateColor(state) {
                switch (state) {
                    case 'pending': return 'orange';
                    case 'confirmed': return 'cyan';
                    case 'in preparation': return 'blue darken-2';
                    case 'prepared': return 'light-green';
                    case 'delivered': return 'green';
                    case 'not delivered': return 'red';
                }
			},
			showDeliverMealDialog(state) {
				if (state === 'prepared') {
					this.deliverMealDialog = true;
				}
			},
            deliverOrder() {
				this.$store.commit('showProgressBar', {indeterminate: true});
				axios.patch(`/orders/${this.item.order_id}`, {state: 'delivered'})
					.then(response => {
						if (response.status === 200) {
							this.$emit('onOrderChange');
							this.showSuccessToast('Successfully changed the order to delivered');
							this.$store.commit('hideProgressBar');
						} else {
							this.showErrorToast('Successfully changed the order to delivered')
							this.$store.commit('hideProgressBar');
						}
					})
					.catch(error => {
						this.showErrorLog('Failed to changed the order to delivered', error);
						this.$store.commit('hideProgressBar');
					})
            }
		}
	}
</script>

<style scoped>

</style>