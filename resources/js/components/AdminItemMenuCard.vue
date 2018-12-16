<template>
	<v-card>
		<v-img :src="item.photo_url" :alt="item.name"
			   aspect-ratio="2"></v-img>
		<v-card-text class="pt-4"
					 style="position: relative;">
			<v-btn
					v-if="meal"
					@click="toggleItem"
					absolute
					style="z-index: 0"
					:color="buttonColor"
					class="white--text"
					fab right top>
				<v-scroll-x-transition>
					<v-icon>{{ buttonIcon }}</v-icon>
				</v-scroll-x-transition>
			</v-btn>
			<v-card-text
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
			<div v-if="this.$route.name == 'restaurantManagement' && this.$store.state.user.type == 'manager'" >
				<v-icon arge color="red darken-2" dark right @click.prevent="deleteItem(item)">delete</v-icon>
				<v-snackbar v-model="snackbar" :color="color" :multi-line="mode === 'multi-line'"
							:timeout="timeout" :vertical="mode === 'vertical'">
					{{ text }}
					<v-btn dark flat @click="snackbar = false"> Close</v-btn>
				</v-snackbar>
			</div>
		</v-card-text>
	</v-card>
</template>

<script>
    export default {
        props: ['item', 'meal'],
        data: () => ({
            isSelected: false,
            snackbar: false,
            color: 'black',
            mode: '',
            timeout: 3000,
            text: '',
            buttonColor: 'blue-grey',
            buttonIcon: 'fas fa-plus',
        }),
        methods: {
            toggleItem() {
                if (this.isSelected) {
                    this.isSelected = false;
                    this.buttonColor = 'blue-grey';
                    this.buttonIcon = 'fas fa-plus';
                    this.$emit('onItemDeselect', this.item);
                } else {
                    this.isSelected = true;
                    this.buttonColor = 'teal lighten-1';
                    this.buttonIcon = 'fas fa-check';
                    this.$emit('onItemSelect', this.item);
                }
            },
            deleteItem(item){
                if( confirm('Are you sure you want to delete ' + item.name + ' ?')) {
                    axios.delete('items/' + item.id).then(response => {
                        if (response.status === 204) {
                            this.text = 'Deleted Item Sucessfully';
                            this.snackbar = true;
                            this.$emit('updateList');
                        }
                    }).catch(error => {
                        console.log(error);
                    });
                }
            }
        },
    }
</script>

<style scoped>

</style>