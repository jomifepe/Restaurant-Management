<template>
    <v-container grid-list-md>
        <v-layout row wrap>
            <v-flex xs12>
                <v-toolbar flat color="gray">
                    <v-toolbar-title>
                        My Meals 
                        <span class="body-1">(click to display a meal's orders)</span>
                    </v-toolbar-title>
                    <v-spacer></v-spacer>
                    <CreateMeal @onCreate="onMealCreated"></CreateMeal>
                </v-toolbar>
                <v-card>
                    <v-card-title>
                        <v-spacer></v-spacer>
                        <v-text-field v-model="search" append-icon="search" 
                            label="Search" single-line hide-details></v-text-field>
                    </v-card-title>
                    <v-data-table :headers="myMealsHeaders" :items="meals" class="elevation-1"
                            :search="search" :pagination.sync="pagination" :loading="myMealsProgressBar">
                        <v-progress-linear slot="progress" color="blue-grey" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <tr class="clickable" @click="showMealItems(props.item)">
                                <td>{{ props.item.table_number }}</td>
                                <td>{{ formatDate(props.item.start) }}</td>
                                <td>{{ formatDate(props.item.created_at.date) }}</td>
                                <td>{{ props.item.total_price_preview }}€</td>
                                <td :class="getStateColor(props.item.state)">
                                    <strong>{{ props.item.state }}</strong>
                                </td>
                                <td class="justify-center layout px-0">
                                    <v-icon small v-if="props.item.state === 'active'" @click="terminateMeal(props.item)">
                                        fas fa-user-check
                                    </v-icon>
                                </td>
                            </tr>
                        </template>
                    </v-data-table>
                </v-card>
            </v-flex>
            <v-flex xs12 id="mealOrders" class="mt-5">
                <router-view></router-view>
            </v-flex>
            <v-dialog v-model="terminateMealDialog" max-width="450">
				<v-card>
					<v-card-text class="subheading">
						Are you sure you want to declare this meal as <span class="orange--text darken-1">terminated</span> ?
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn flat @click="terminateMealDialog = false; mealToTerminate = null">
							No
						</v-btn>
						<v-btn color="orange darken-1" flat="flat" @click="performMealTermination"
                            alt="Terminate meal">
							Yes
						</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
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
                    { text: 'State', value: 'state' },
                    { text: 'Actions', value: '', align: 'center' }
                ],
                search: '',
                pagination: {
                    sortBy: 'state'
                },
                myMealsProgressBar: true,
                mealToTerminate: null,
                terminateMealDialog: false
            }
        },
        methods: {
            loadMeals() {
                this.myMealsProgressBar = true;
                axios.get(`/meals/waiter/${this.$store.state.user.id}`)
                    .then(response => {
                        if (response.status === 200) {
                            this.meals = response.data.data;
                            this.myMealsProgressBar = false;
                        }
                    });
            },
            formatDate: date => moment(date).format("YYYY-MM-DD, HH:mm"),
            onMealCreated(tableNumber) {
                this.$toasted.show(`Meal successfuly started for table ${tableNumber}`, {
                        icon : "check",
                        position: "bottom-center",
                        duration : 5000
                    });
                this.loadMeals();
            },
            getStateColor(state) {
                return state === 'active' ? 'red--text' :
                    state === 'terminated' ? 'orange--text' :
                        state === 'not paid' ? 'blue--text' : 'green--text';
            },
            showMealItems(meal) {
                this.$router.push({ name: 'meal.orders', params: { mealId: meal.id }});
            },
            terminateMeal(meal) {
                this.mealToTerminate = meal;
                this.terminateMealDialog = true;
            },
            performMealTermination() {
                this.terminateMealDialog = false;

                let meal = this.mealToTerminate;
                meal.state = 'terminated';
                axios.patch(`/meals/${meal.id}`, meal)
                    .then(response => {
                        if (response.status === 200) {
                            this.$toasted.show('Meal successfully terminated', {
                                icon : 'check',
                                position: "bottom-center",
                                duration : 2000
                            });
                            this.loadMeals();
                        } else {
                            this.$toasted.show('Failed to terminated meal', {
                                icon : 'error',
                                position: "bottom-center",
                                duration : 3000
                            });
                        }
                    })
                    .catch(error => {
                        console.log(error);
                        this.$toasted.show('Failed to terminated meal', {
                            icon : 'error',
                            position: "bottom-center",
                            duration : 3000
                        });
                    })

                this.mealToTerminate = null;
            }
        },
        mounted() {
            this.$store.commit('setPanelTitle', 'Meals');
            this.loadMeals();
        }
    }
</script>

<style scoped>

</style>