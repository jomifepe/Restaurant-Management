<template>
    <v-flex v-if="meal">
        <v-toolbar flat color="gray">
            <v-toolbar-title>Orders for table {{ meal.table_number }} (meal #{{ meal.id }})</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn dark color="success" v-if="meal.state === 'active'" :to="'/admin/menu/meal/' + meal.id">
                Order new item(s)
            </v-btn>
            <v-progress-circular v-if="progressBar" indeterminate color="blue-grey"></v-progress-circular>
        </v-toolbar>
        <v-container fluid class="white elevation-1">
            <v-data-iterator row wrap :items="items"
                            :rows-per-page-items="rowsPerPageItems"
                            :pagination.sync="pagination"
                            content-tag="v-layout">
                <v-flex slot="item" slot-scope="props" xs12 sm6 md4 lg3>
                    <v-card>
                        <v-img :src="getItemPhotoUrl(props.item.photo_url)" aspect-ratio="1.75"></v-img>
                        <v-card-title><h4>{{ props.item.name }}</h4></v-card-title>
                        <v-divider></v-divider>
                        <v-list dense>
                            <v-list-tile>
                                <v-list-tile-content>Type:</v-list-tile-content>
                                <v-list-tile-content class="align-end">{{ props.item.type }}</v-list-tile-content>
                            </v-list-tile>
                            <v-list-tile>
                                <v-list-tile-content>Price:</v-list-tile-content>
                                <v-list-tile-content class="align-end">{{ props.item.price }}€</v-list-tile-content>
                            </v-list-tile>
                            <v-list-tile>
                                <v-list-tile-content>Order time:</v-list-tile-content>
                                <v-list-tile-content class="align-end">{{ props.item.order_created_at }}</v-list-tile-content>
                            </v-list-tile>
                            <v-list-tile>
                                <v-list-tile-content>Order end:</v-list-tile-content>
                                <v-list-tile-content class="align-end">{{ props.item.order_end }}</v-list-tile-content>
                            </v-list-tile>
                            <v-list-tile>
                                <v-list-tile-content>Order state:</v-list-tile-content>
                                <v-list-tile-content class="align-end">{{ props.item.order_state }}</v-list-tile-content>
                            </v-list-tile>
                        </v-list>
                    </v-card>
                </v-flex>
            </v-data-iterator>
        </v-container>
    </v-flex>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "MealOrders",
        data: () => ({
            meal: null,
            items: [],
            rowsPerPageItems: [4, 8, 12],
            pagination: {
                rowsPerPage: 4
            },
            progressBar: true
        }),
        watch: {
            $route(to, from) {
                this.loadMealsOrdersFromRouteId();
            }
        },
        methods: {
            loadMealsOrdersFromRouteId() {
                if (this.$route.params.mealId) {
                    this.progressBar = true;
                    axios.get(`/meals/${this.$route.params.mealId}`)
                        .then(response => {
                            if (response.status === 200) {
                                this.meal = response.data.data;
                                this.$store.commit('setPanelTitle', `Orders for meal #${this.meal.id}`);
                                this.loadMealItems();
                            } else {
                                this.progressBar = false;
                                this.$toasted.show('Failed to get orders associated to the selected meal', {
                                    icon : 'error',
                                    position: "bottom-center",
                                    duration : 3000
                                });
                            }
                        })
                        .catch(error => {
                            this.progressBar = false;
                            console.log(error);
                            this.$toasted.show('Failed to get meal identified by the given id', {
                                icon : 'error',
                                position: "bottom-center",
                                duration : 3000
                            });
                        })
                }
            },
            loadMealItems() {
                axios.get(`/meals/${this.meal.id}/items`)
                    .then(response => {
                        if (response.status === 200) {
                            this.items = response.data;
                            this.progressBar = false;
                        }
                    })
                    .catch(error => {
                        console.log(error);
                    });
            },
            getItemPhotoUrl(filename) {
                return `/storage/items/${filename}`;
            }
        },
        mounted() {
            this.loadMealsOrdersFromRouteId();
        }
    }
</script>

<style scoped>

</style>