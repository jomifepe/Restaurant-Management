<template>
    <v-flex v-if="meal">
        <v-toolbar flat color="gray">
            <v-toolbar-title>
                Orders for table {{ meal.table_number }} (meal #{{ meal.id }})
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-chip color="teal" text-color="white">
                {{ totalPrice }}
                <v-icon right>euro_symbol</v-icon>
            </v-chip>
            <v-btn dark color="success" v-if="meal.state === 'active'" :to="'/admin/menu/meal/' + meal.id">
                Order new item(s)
            </v-btn>
            <v-progress-circular v-if="progressBar" indeterminate color="blue-grey"></v-progress-circular>
        </v-toolbar>
        <v-container fluid class="white elevation-1">
            <v-data-iterator row wrap :items="items" :rows-per-page-items="rowsPerPageItems"
                            :pagination.sync="pagination" content-tag="v-layout">
                <v-flex slot="item" slot-scope="props" xs12 sm6 md4 lg3>
                    <Card :item="props.item" @onOrderChange="loadMealItems"></Card>
                </v-flex>
            </v-data-iterator>
        </v-container>
    </v-flex>
</template>

<script>
    import Card from './MealOrderCard.vue';
    import axios from 'axios';
    import currency from 'currency.js';
    import {toasts} from '../mixin.js';

    export default {
        name: "MealOrders",
        mixin: [toasts],
        components: {
            Card
        },
        data: () => ({
            meal: null,
            items: [],
            rowsPerPageItems: [4, 8, 12],
            pagination: { rowsPerPage: 4 },
            progressBar: true
        }),
        computed: {
            totalPrice() {
                let total = 0;
                this.items.forEach(item => {
                    if (item.order_state === 'delivered') {
                        total = currency(total).add(item.price).format();
                    }
                })
                return total;
            }
        },
        watch: {
            $route(to, from) {
                this.loadMealOrdersFromRouteId();
            }
        },
        methods: {
            loadMealOrdersFromRouteId() {
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
                axios.get(`/orders/meal/${this.meal.id}/items`)
                    .then(response => {
                        if (response.status === 200) {
                            this.items = response.data;
                            this.progressBar = false;
                        }
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        },
        mounted() {
            this.loadMealOrdersFromRouteId();
        }
    }
</script>

<style scoped>

</style>