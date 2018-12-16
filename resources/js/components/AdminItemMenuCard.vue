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
		</v-card-text>
	</v-card>
</template>

<script>
	export default {
		props: ['item', 'meal'],
		data: () => ({
			isSelected: false,
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
			}
		}
	}
</script>

<style scoped>

</style>