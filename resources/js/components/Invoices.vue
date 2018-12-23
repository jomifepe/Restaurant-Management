<template>
    <v-container grid-list-md>
        <v-layout row wrap>
            <v-flex xs12>
                <v-toolbar flat color="gray">
                    <v-toolbar-title>Pending invoices</v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-toolbar>
                <v-data-table :headers="myInvoicesHeaders"
                              :items="invoices"
                              :pagination.sync="pagination"
                              :total-items="totalInvoices"
                              :loading="loading"
                              class="elevation-1"
                >

                    <template slot="items" slot-scope="props">
                        <tr>
                            <td>{{ props.item.id }}</td>
                            <td>{{props.item.table_number}}</td>
                            <td>{{ props.item.responsible_waiter_name}}</td>
                            <td>{{ props.item.total_price}}€</td>
                        </tr>
                    </template>
                </v-data-table>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script>
    import axios from 'axios';
    export default {
        name: "Invoices",
        data(){
            return {
                invoices: [],
                myInvoicesHeaders: [
                    { text: 'Id', value: 'id' },
                    { text: 'Table number', value: 'tableNumber' },
                    { text: 'Waiter', value: 'waiter' },
                    { text: 'Total Price', value: 'totalPrice' }
                ],
                totalInvoices: 0,
                loading: true,
                pagination: {},
            }
        },
        watch: {
            pagination: {
                handler () {
                    this.getDataFromApi()
                        .then(data => {
                            this.invoices = data.items;
                            this.totalInvoices = data.total;
                        })
                },
                deep: true
            }
        },
        mounted() {
            this.getDataFromApi()
                .then(data => {
                    this.invoices = data.items;
                    this.totalInvoices= data.total
                })
           
        },
        methods: {
            getTableNumber(mealId){
                console.log("entrou GTN")
                axios.get(`/meals/${mealId}/tableNumber`)
                    .then(data => {
                        //console.log(data);
                        return data.data;
                    })
                    .catch(error => {
                        console.log(error);
                    });
            },
            getDataFromApi (){
                this.loading = true;
                return new Promise((resolve, reject) => {
                    const { sortBy, descending, page, rowsPerPage } = this.pagination;

                    let items;

                    this.loadInvoices()
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
                });
                
            },
            loadInvoices() {
                return axios.get(`/invoices/pending`);
                //axios.get(`/orders/${this.$store.state.user.id}/toprepare`)
                //    .then(response => {
                //        if (response.status === 200) {
                //           console.log(response.data.data);
                //           return response.data.data;
                //        }
                //    });

            },

        }
    }
</script>

<style scoped>

</style>