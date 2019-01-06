<template>
	<v-card>
		<v-img :src="item.photo_url" :alt="item.name" aspect-ratio="2"></v-img>
		<v-card-text class="item-card pt-4" style="position: relative;">
			<v-btn v-if="isUserLoggedIn && meal" @click="selectItem" absolute style="z-index: 0"
					:color="buttonColor" class="white--text" fab right top>
				<v-scroll-x-transition>
					<v-icon>fas fa-plus</v-icon>
				</v-scroll-x-transition>
			</v-btn>
			<h4 class="subheading font-weight-medium blue-grey--text ml-2 mb-2">{{ item.name }}</h4>
			<v-list dense>
				<v-list-tile>
					<v-list-tile-content>Price:</v-list-tile-content>
					<v-list-tile-content class="align-end">
						<h4>{{ item.price }}€</h4>
					</v-list-tile-content>
				</v-list-tile>
				<div class="item-description">
					<span class="text-description font-weight-regular">
						{{ item.description }}
					</span>
					&hellip; <a @click="fullDescriptionDialog = true" 
						class="blue--text text--darken-2">Show more</a>
					<v-dialog v-model="fullDescriptionDialog" max-width="350">
						<v-card>
							<v-card-title class="headline">
								{{ item.name }}
							</v-card-title>
							<v-card-text>
								{{ item.description }}
							</v-card-text>
							<v-card-actions>
								<v-spacer></v-spacer>
								<v-btn flat @click="fullDescriptionDialog = false">
									Close
								</v-btn>
							</v-card-actions>
						</v-card>
					</v-dialog>
				</div>
			</v-list>
		</v-card-text>
		<v-card-actions>
			<v-spacer></v-spacer>
			<v-tooltip top>
				<v-btn slot="activator" icon flat color="red" @click.prevent="deleteItem(item)">
					<v-icon>delete</v-icon>
				</v-btn>
				<span>Delete item</span>
			</v-tooltip>
			<v-tooltip top>
				<v-btn slot="activator" icon flat color="blue darken-1" @click.prevent="showForm = true">
					<v-icon>edit</v-icon>
				</v-btn>
				<span>Edit item</span>
			</v-tooltip>
			<item-form :showDialog="showForm" :itemSelectedToEdit="item" @onGetItems="onGetItems()" @onCloseForm="onCloseForm()"></item-form>
		</v-card-actions>
	</v-card>
</template>

<script>
	import ItemForm from './manager/ItemForm';
	import {toasts, helper} from '../../mixin';

    export default {
		props: ['item', 'meal', 'exists'],
		mixins: [toasts, helper],
        components:{
            ItemForm,
        },
        data: () => ({
            showForm: false,
			isSelected: false,
			selectedQuantity: 0,
            snackbar: false,
            color: 'black',
            mode: '',
            timeout: 3000,
            text: '',
            buttonColor: 'blue-grey',
			buttonIcon: 'fas fa-plus',
			fullDescriptionDialog: false
		}),
		computed: {
			isUserLoggedIn() {
				return !!this.$store.state.user
			}
		},
		watch: {
			exists() {
				if (!this.exists) {
					this.deselectItem();
				}
			}
		},
        methods: {
			selectItem() {
				this.$emit('onItemSelect', this.item);
				if (!this.isSelected) {
					this.isSelected = true;
					this.buttonColor = 'teal lighten-1';
				}
			},
			deselectItem() {
				if (this.isSelected) {
					this.isSelected = false;
					this.buttonColor = 'blue-grey';
				}
			},
            deleteItem(item) {
				if (this.isUserInShift()) return;
				
                if(confirm('Are you sure you want to delete ' + item.name + ' ?')) {
                    axios.delete('items/' + item.id).then(response => {
                        if (response.status === 204) {
							this.showSuccessToast('Deleted Item Sucessfully')
                            this.$emit('updateList');
                        }
                    }).catch(error => {
						this.showErrorLog('Problem occurred in deleting Item', error);
                    });
                }
            },
            onCloseForm(){
                this.showForm = false;
            },
            onGetItems(){
                this.$emit('onGetItems');
            }
		}
    }
</script>

<style scoped>
	.button-select {
		font-size: 20px;
	}
	.item-description {
		padding: 0 8px;
	}
	.text-description {
		overflow: hidden;
		text-overflow: ellipsis;
		display: -webkit-box;
		line-height: 16px;
		max-height: 32px;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
		text-align: justify;
  		text-justify: inter-character;
	}
</style>