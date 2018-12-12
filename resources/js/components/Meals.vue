<template>
    <v-container grid-list-md>
        <v-layout row wrap>
            <v-flex xs12>
                <v-toolbar flat color="gray">
                    <v-toolbar-title>My Meals</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <CreateMeal @onCreate="onMealCreated"></CreateMeal>
                </v-toolbar>
                <v-data-table :headers="myMealsHeaders"
                        :items="meals"
                        class="elevation-1">
                    <template slot="items" slot-scope="props">
                        <tr class="clickable" @click="showMealItems(props.item)">
                            <td>{{ props.item.table_number }}</td>
                            <td>{{ formatDate(props.item.start) }}</td>
                            <td>{{ formatDate(props.item.created_at.date) }}</td>
                            <td>{{ props.item.total_price_preview }}€</td>
                            <td :class="getStateColor(props.item.state)">
                                <strong>{{ props.item.state }}</strong>
                            </td>
                        </tr>
                    </template>
                </v-data-table>
            </v-flex>
            <v-flex xs12 class="mt-5" v-if="mealItemsShown">
                <MealOrders v-if="mealItemsShown" :meal="chosenMeal"></MealOrders>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script>
    import CreateMeal from './CreateMeal';
    import MealOrders from './MealOrders';
    import axios from 'axios';
    const moment = require('moment');

    export default {
        name: "Orders",
        components: {
            CreateMeal,
            MealOrders
        },
        data() {
            return {
                meals: [],
                myMealsHeaders: [
                    { text: 'Table Number', align: 'left', value: 'table_number' },
                    { text: 'Started', value: 'start' },
                    { text: 'Created', value: 'created_at' },
                    { text: 'Price', value: 'total_price_preview' },
                    { text: 'State', value: 'state' }
                ],
                chosenMeal: null,
                mealItemsShown: false,
                items: [],
                itemsRowsPerPageItems: [4, 8, 12],
                itemsPagination: {
                    rowsPerPage: 4
                },
                alertShown: false,
                alertMessage: '',
                alertType: 'success'
            }
        },
        methods: {
            loadMeals() {
                axios.get(`/meals/waiter/${this.$store.state.user.id}`)
                    .then(response => {
                        if (response.status === 200) {
                            this.meals = response.data.data;
                        }
                    });
            },
            formatDate: date => moment(date).format("YYYY-MM-DD, HH:mm"),
            onMealCreated(tableNumber) {
                this.$toasted.show(`Meal successfuly started for table ${tableNumber}`, {
                        icon : 'check',
                        position: "bottom-center",
                        duration : 5000
                    });
                this.loadMeals();
            },
            getStateColor(state) {
                return state === 'active' ? 'red--text' :
                    state === 'terminated' ? 'yellow--text' :
                        state === 'not paid' ? 'blue--text' : 'green--text';
            },
            showMealItems(meal) {
                this.chosenMeal = meal;
                this.mealItemsShown = true;
            }
        },
        showAlertMessage(show, message, type) {
            this.alertMessage = message;
            this.alertType = type;
            this.alertShown = true;
        },
        mounted() {
            this.$store.commit('setPanelTitle', 'Meals');
            this.loadMeals();
        }
    }
</script>

<style scoped>

</style>