<template>
	<v-card>
		<v-img :src="item.photo_url" :alt="item.name"
			   aspect-ratio="2"></v-img>
		<v-card-text class="pt-4"
					 style="position: relative;">
			<v-btn v-if="meal" @click="selectItem" absolute style="z-index: 0"
					:color="buttonColor" class="white--text" fab right top>
				<v-scroll-x-transition>
					<v-icon>fas fa-plus</v-icon>
				</v-scroll-x-transition>
			</v-btn>
			<h4 class="font-weight-light blue-grey--text mb-2">{{ item.name }}</h4>
			<v-list dense>
				<v-list-tile>
					<v-list-tile-content>Type:</v-list-tile-content>
					<v-list-tile-content class="align-end">{{ item.type }}</v-list-tile-content>
				</v-list-tile>
				<v-list-tile>
					<v-list-tile-content>Price:</v-list-tile-content>
					<v-list-tile-content class="align-end">
						<h4>{{ item.price }}€</h4>
					</v-list-tile-content>
				</v-list-tile>
			</v-list>
			<div v-if="this.$store.state.user.type === 'manager'" >
				<v-icon large color="red darken-2" dark right @click.prevent="deleteItem(item)">delete</v-icon>
				<v-icon large color="yellow darken-2" dark right @click.prevent="showForm = true">border_color</v-icon>
				<div v-if="showForm">
					<item-form  :itemSelectedToEdit="item" @onGetItems="onGetItems()" @onCloseForm="onCloseForm()"></item-form>
				</div>
			</div>
		</v-card-text>
	</v-card>
</template>

<script>
	import ItemForm from './ItemForm';
	import {toasts} from '../mixin';

    export default {
		props: ['item', 'meal', 'exists'],
		mixins: [toasts],
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
		}),
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
            deleteItem(item){
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
        },
    }
</script>

<style scoped>
	.button-select {
		font-size: 20px;
	}
</style>