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

            <!--  pending & confirmed -->
            <v-card class="mt-3">
                <v-toolbar card color="red accent-3" dark>
                    <v-toolbar-title>Not prepared orders</v-toolbar-title>
                </v-toolbar>
                <v-card-text style="border: 1px solid red">
                    <v-data-iterator row wrap :items="notPreparedOrders" :rows-per-page-items="notPreparedOrdersRows"
                                     :pagination.sync="notPreparedOrdersPagination" content-tag="v-layout"
                                     no-data-text="No pending or confirmed orders at the moment">
                        <v-flex slot="item" slot-scope="props" xs12 sm6 md4 lg3>
                            <Card :item="props.item" @onOrderChange="loadMealOrders"></Card>
                        </v-flex>
                    </v-data-iterator>
                </v-card-text>
            </v-card>

            <!--  in preparation -->
            <v-card class="mt-3" v-if="isUserManager">
                <v-toolbar card color="amber darken-1 accent-3" dark>
                    <v-toolbar-title>In preparation orders</v-toolbar-title>
                </v-toolbar>
                <v-card-text style="border: 1px solid red">
                    <v-data-iterator row wrap :items="inPreparationOrders" :rows-per-page-items="inPreparationOrdersRows"
                                     :pagination.sync="inPreparationOrdersPagination" content-tag="v-layout"
                                     no-data-text="No in preparation orders at the moment">
                        <v-flex slot="item" slot-scope="props" xs12 sm6 md4 lg3>
                            <Card :item="props.item" @onOrderChange="loadMealOrders"></Card>
                        </v-flex>
                    </v-data-iterator>
                </v-card-text>
            </v-card>


            <!--  prepared -->
            <v-card>
                <v-toolbar card color="teal" dark>
                    <v-toolbar-title>Prepared orders</v-toolbar-title>
                </v-toolbar>
                <v-card-text style="border: 1px solid teal">
                    <v-data-iterator row wrap :items="preparedOrders" :rows-per-page-items="preparedOrdersRows"
                        :pagination.sync="preparedOrdersPagination" content-tag="v-layout"
                        no-data-text="No prepared orders at the moment">
                        <v-flex slot="item" slot-scope="props" xs12 sm6 md4 lg3>
                            <Card :item="props.item" @onOrderChange="loadMealOrders"></Card>
                        </v-flex>
                    </v-data-iterator>
                </v-card-text>
            </v-card>


            <!--  delivered -->
            <v-card class="mt-3" v-if="isUserManager">
                <v-toolbar card color="green accent-3" dark>
                    <v-toolbar-title>Delivered orders</v-toolbar-title>
                </v-toolbar>
                <v-card-text style="border: 1px solid red">
                    <v-data-iterator row wrap :items="deliveredOrders" :rows-per-page-items="deliveredOrdersRows"
                                     :pagination.sync="deliveredOrdersPagination" content-tag="v-layout"
                                     no-data-text="No delivered orders at the moment">
                        <v-flex slot="item" slot-scope="props" xs12 sm6 md4 lg3>
                            <Card :item="props.item" @onOrderChange="loadMealOrders"></Card>
                        </v-flex>
                    </v-data-iterator>
                </v-card-text>
            </v-card>

            <!--  not delivered -->
            <v-card class="mt-3" v-if="isUserManager">
                <v-toolbar card color="grey darken-3 accent-3" dark>
                    <v-toolbar-title>Not delivered orders</v-toolbar-title>
                </v-toolbar>
                <v-card-text style="border: 1px solid red">
                    <v-data-iterator row wrap :items="notDeliveredOrders" :rows-per-page-items="notDeliveredOrdersRows"
                                     :pagination.sync="notDeliveredOrdersPagination" content-tag="v-layout"
                                     no-data-text="No delivered orders at the moment">
                        <v-flex slot="item" slot-scope="props" xs12 sm6 md4 lg3>
                            <Card :item="props.item" @onOrderChange="loadMealOrders"></Card>
                        </v-flex>
                    </v-data-iterator>
                </v-card-text>
            </v-card>


        </v-container>
    </v-flex>
</template>

<script>
    import Card from './MealOrderCard.vue';
    import axios from 'axios';
    import currency from 'currency.js';
    import {toasts, helper} from '../mixin';

    export default {
        name: "MealOrders",
        mixins: [toasts, helper],
        components: {
            Card
        },
        data: () => ({
            meal: null,
            preparedOrders: [],
            notPreparedOrders: [],
            inPreparationOrders: [],
            deliveredOrders: [],
            notDeliveredOrders: [],

            preparedOrdersRows: [4, 8, 12],
            notPreparedOrdersRows: [4, 8, 12],
            inPreparationOrdersRows: [4, 8, 12],
            deliveredOrdersRows: [4, 8, 12],
            notDeliveredOrdersRows: [4, 8, 12],


            preparedOrdersPagination: { rowsPerPage: 4 },
            notPreparedOrdersPagination: { rowsPerPage: 4 },
            inPreparationOrdersPagination: {rowsPerPage: 4},
            deliveredOrdersPagination: {rowsPerPage: 4},
            notDeliveredOrdersPagination: {rowsPerPage: 4},

            progressBar: true
        }),
        computed: {
            totalPrice() {
                let total = 0;
                this.preparedOrders.forEach(item => {
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
        sockets: {
            order_prepared_waiter(order) {
                this.showTopRightToast(`Meal #${order.meal_id} from table ${order.meal_table_number} is prepared`, 'check');
                if (this.meal.id === order.meal_id) {
                    this.loadMealOrders();
                }
            },
            order_in_preparation_waiter(order) {
                console.log(order);
                this.showTopRightToast(`Meal #${order.meal_id} from table ${order.meal_table_number} is in preparation`, 'restaurant');
                if (this.meal.id === order.meal_id) {
                    this.loadMealOrders();
                }
            },
            new_order_notify_manager(){
                this.progressBar = true;
                this.loadMealOrders();
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
                                this.loadMealOrders();
                            } else {
                                this.progressBar = false;
                                this.showErrorToast('Failed to get orders associated to the selected meal');
                            }
                        })
                        .catch(error => {
                            this.progressBar = false;
                            this.$toasted.show('Failed to get meal identified by the given id', error);
                        })
                }
            },
            loadMealOrders() {
                axios.get(`/orders/meal/${this.meal.id}/items`)
                    .then(response => {
                        if (response.status === 200) {
                            let allItemOrders = response.data;
                            this.preparedOrders = allItemOrders.filter(itemOrder =>
                                itemOrder.order_state === 'prepared');
                            this.notPreparedOrders = allItemOrders.filter(itemOrder =>
                                ['pending', 'confirmed'].includes(itemOrder.order_state));

                            if(this.isUserManager) {
                                this.inPreparationOrders = allItemOrders.filter(itemOrder =>
                                    itemOrder.order_state === 'in preparation');
                                this.deliveredOrders = allItemOrders.filter(itemOrder =>
                                    itemOrder.order_state === 'delivered')
                                this.notDeliveredOrders = allItemOrders.filter(itemOrder =>
                                    itemOrder.order_state === 'not delivered');
                            }

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