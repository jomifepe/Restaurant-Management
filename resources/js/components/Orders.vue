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
                              :total-items="totalOrders"
                              :loading="loading"
                              class="elevation-1"
                >

                    <template slot="items" slot-scope="props">
                        <tr @click="props.expanded = !props.expanded">
                            <td>{{ props.item.id }}</td>
                            <td>{{ props.item.start }}</td>
                            <td>{{ props.item.created_at}}</td>
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
    export default {
        name: "Orders",
        data() {
            return {
                orders: [],
                myOrderHeaders: [
                    { text: 'Id', value: 'id' },
                    { text: 'Started', value: 'start' },
                    { text: 'Created', value: 'created_at' },
                    { text: 'State', value: 'state' }
                ],
                totalOrders: 0,
                loading: true,
                pagination: {},
            }
        },
        watch: {
            pagination: {
                handler () {
                    this.getDataFromApi()
                        .then(data => {
                            this.orders = data.items;
                            this.totalOrders = data.total;
                        })
                },
                deep: true
            }
        },
        mounted() {
            this.getDataFromApi()
                .then(data => {
                    this.orders = data.items;
                    this.totalOrders= data.total
                })
        },
        sockets:{
            connect(){
                console.log('socket connected (socket ID = '+this.$socket.id+')');
            },
            order_received_list(){
                console.log("chegou");
                this.getDataFromApi()
                    .then(data => {
                        //console.log(data);
                        this.orders = data.items;
                        this.totalOrders = data.total;
                    })
            },

        },
        methods: {
            getWaiter(order){
                return new Promise(resolve => {
                    console.log(order.meal_id);
                    axios.get(`/meals/${order.meal_id}/waiter`)
                        .then(response => {
                            if (response.status === 200) {
                                resolve(response.data)
                            } else {
                                this.$toasted.show('Failed to get the responsible for the meal', {
                                    icon : 'error',
                                    position: "bottom-center",
                                    duration : 3000
                                });
                            }
                        });
                });

            },
            sendNotificationToWaiter(order){
                this.getWaiter(order)
                    .then(userDest => {
                        console.log(userDest);
                        console.log(order);
                        this.$socket.emit('order_prepared', this.$store.state.user, userDest, order);
                    })

            },
            markAsDone(order){
                order.state= "prepared";
                this.saveOrder(order);
                this.sendNotificationToWaiter(order);
                this.getDataFromApi()
                    .then(data => {
                        //console.log(data);
                        this.orders = data.items;
                        this.totalOrders = data.total;
                    })
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
                            this.getDataFromApi();
                        } else {
                            this.$toasted.show('Failed to assign meal', {
                                icon : 'error',
                                position: "bottom-center",
                                duration : 3000
                            });
                        }
                    })
            },
            assignOrderToMe (order){
                order.state= 'in preparation';
                order.responsible_cook_id= this.$store.state.user.id;
                this.saveOrder(order);

            },
            getDataFromApi (){
                this.loading = true;
                return new Promise((resolve, reject) => {
                    const { sortBy, descending, page, rowsPerPage } = this.pagination;

                    let items;

                    this.loadOrders()
                        .then(data => {
                            items= data.data;
                            const total = items.length;


                            if (rowsPerPage > 0) {
                                items = items.slice((page - 1) * rowsPerPage, page * rowsPerPage)
                            }

                            setTimeout(() => {

                                this.loading = false;
                                resolve({
                                    items,
                                    total
                                })
                            }, 1000)
                        });



                })
            },
            loadOrders() {
                return axios.get(`/orders/${this.$store.state.user.id}/toprepare`);
                //axios.get(`/orders/${this.$store.state.user.id}/toprepare`)
                //    .then(response => {
                //        if (response.status === 200) {
                //           console.log(response.data.data);
                //           return response.data.data;
                //        }
                //    });

            },
            getStateColor(state) {
                return state === 'in preparation' ? 'yellow--text' : 'green--text';
            },

        },

    }
</script>

<style scoped>

</style>