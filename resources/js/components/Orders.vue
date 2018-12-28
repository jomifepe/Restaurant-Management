<template>

    <v-container grid-list-md>
        <v-layout row wrap>
            <v-flex xs12>
                <v-toolbar flat color="gray">
                    <v-toolbar-title>Orders</v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-toolbar>
                <v-data-table :headers="myOrderHeaders"
                              :items="orders"
                              :pagination.sync="pagination"
                              :loading="loading"
                              class="elevation-1">

                    <template slot="items" slot-scope="props">
                        <tr @click="props.expanded = !props.expanded">
                            <td>{{ props.item.id }}</td>
                            <td>{{ props.item.item_name }}</td>
                            <td class="text-xs-left">
                                <v-avatar class="ma-1"  slot="activator" size="50px" >
                                    <img :src="props.item.item_photo_src" alt="Avatar">
                                </v-avatar>
                            </td>
                            <td>{{ props.item.item_type }}</td>
                            <td>{{ props.item.created_at.date | moment("YYYY-MM-DD HH:mm:ss") }}</td>
                            <td :class="getStateColor(props.item.state)">
                                <strong>{{ props.item.state }}</strong>
                            </td>
                        </tr>
                    </template>
                    <template slot="expand" slot-scope="props">
                        <v-card flat>
                            <v-card-text>
                                <v-btn  v-if="props.item.state === 'confirmed'" round color="primary" dark
                                    @click="assignOrderToMe(props.item)">Assign to me</v-btn>
                                <v-btn  v-if="props.item.state === 'in preparation' ||
                                    props.item.state === 'confirmed'" round color="success" dark
                                        @click.prevent="markAsDone(props.item)">Done</v-btn>
                            </v-card-text>
                        </v-card>
                    </template>
                </v-data-table>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script>
    import axios from 'axios';
    import {toasts} from '../mixin';

    export default {
        name: "Orders",
        mixins: [toasts],
        data: () => ({
            orders: [],
            myOrderHeaders: [
                { text: 'Id', value: 'id' },
                { text: 'Item Name', value: 'item_name' },               
                { text: 'Item Photo', value: 'item_photo' },
                { text: 'Item Type', value: 'item_type' },
                { text: 'Date', value: 'created_at' },
                { text: 'State', value: 'state' }
            ],
            totalOrders: 0,
            loading: true,
            pagination: {}
        }),
        sockets:{
            connect(){
                console.log('socket connected (socket ID = '+this.$socket.id+')');
            },
            order_received_list(){
                console.log("chegou");
                this.loadOrders();
            },

        },
        methods: {
            getWaiter(order) {
                return new Promise(resolve => {
                    console.log(order.meal_id);
                    axios.get(`/meals/${order.meal_id}/waiter`)
                        .then(response => {
                            if (response.status === 200) {
                                resolve(response.data);
                            } else {
                                this.showErrorMessage('Failed to get the responsible for the meal');
                            }
                        })
                        .catch(error => {
                            this.showErrorLog('Failed to get the responsible for the meal', error);
                        })
                });
            },
            markAsDone(order){
                order.state= "prepared";
                this.saveOrder(order);
                this.getWaiter(order).then(userDest => {
                    this.$socket.emit('order_prepared', this.$store.state.user, userDest, order);
                });
                this.loadOrders();
            },
            saveOrder(order){
                //console.log(order.state);
                axios.patch(`/orders/${order.id}`, order)
                    .then(response => {
                        if (response.status === 200) {
                            this.$toasted.show('Order successfully assigned', {
                                icon : 'check',
                                position: "bottom-center",
                                duration : 2000
                            });
                            this.loadOrders();
                        } else {
                            this.$toasted.show('Failed to assign meal', {
                                icon : 'error',
                                position: "bottom-center",
                                duration : 3000
                            });
                        }
                    })
            },
            assignOrderToMe(order) {
                order.state = 'in preparation';
                order.responsible_cook_id = this.$store.state.user.id;
                this.saveOrder(order);
                this.getWaiter(order).then(userDest => {
                    this.$socket.emit('order_in_preparation',
                        this.$store.state.user, userDest, order);
                });
            },
            loadOrders() {
                this.loading = true;
                axios.get(`/orders/${this.$store.state.user.id}/toprepare`)
                    .then(response => {
                        if (response.status === 200) {
                            this.orders = response.data.data;
                            this.loading = false;
                        }
                    })
                    .catch(error => {
                        this.showErrorLog('Failed to get orders', error);
                    })
            },
            getStateColor(state) {
                return state === 'in preparation' ? 'yellow--text' : 'green--text';
            },

        },
        mounted() {
            this.loadOrders();
        }
    }
</script>

<style scoped>

</style>