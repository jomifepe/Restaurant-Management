<template>
    <div>
        <v-container grid-list-md>
            <v-toolbar flat color="white">
                <v-toolbar-title>Restaurant Tables</v-toolbar-title>
                <v-divider class="mx-2" inset vertical></v-divider>
                <v-spacer></v-spacer>
                <v-dialog v-model="dialog" max-width="500px">
                    <v-btn slot="activator" color="primary" dark class="mb-2">New Table</v-btn>
                    <v-card>
                        <v-card-title>
                            <span class="headline">{{ formTitle }}</span>
                        </v-card-title>
                        <v-card-text>
                            <v-container grid-list-md>
                                <v-layout wrap>
                                    <v-flex xs12 sm6 md4>
                                        <v-text-field v-model="editedItem.table_number" label="Table number"></v-text-field>
                                    </v-flex>
                                </v-layout>
                            </v-container>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="blue darken-1" flat @click="close">Cancel</v-btn>
                            <v-btn color="blue darken-1" flat @click="save">Save</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </v-toolbar>
            <v-data-table :headers="headers" :items="tables" :pagination.sync="pagination" :loading="loadingTableEffect" :rowsPerPage="rows" class="elevation-1">
                <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                <template slot="items" slot-scope="props">
                    <td class="text-xs-center">{{ props.item.table_number }}</td>
                    <td class="text-xs-left">{{ props.item.created_at.date }}</td>
                    <td class="text-xs-left">{{ props.item.updated_at.date }}</td>
                    <td class="justify-center layout px-0">
                        <!--<v-icon small class="mr-2" @click="editItem(props.item)">edit</v-icon>-->
                        <!--<v-icon arge color="red darken-2" dark right>delete</v-icon>-->
                        <v-icon arge color="red darken-2" @click="deleteItem(props.item)">delete</v-icon>
                    </td>
                </template>
                <template slot="no-data">
                    <v-alert :value="noData" color="error" icon="warning">
                        Sorry, nothing to display here :(
                    </v-alert>
                </template>
                <template slot="no-data">
                    <v-btn color="primary" @click="initialize">Reset</v-btn>
                </template>
            </v-data-table>
           <v-snackbar v-model="snackbar" :color="color" :multi-line="mode === 'multi-line'"
                        :timeout="timeout" :vertical="mode === 'vertical'">
                {{ text }}
                <v-btn dark flat @click="snackbar = false"> Close</v-btn>
            </v-snackbar>

        </v-container>
        <items-menu></items-menu>
    </div>
</template>

<script>
    import AdminItemMenu from './AdminItemMenu.vue';
    export default {
        name: "ManageRestaurant",
        components: {
            'items-menu':AdminItemMenu
        },
        data: () => ({
            snackbar: false,
            color: 'black',
            mode: '',
            timeout: 3000,
            text: '',
            dialog: false,
            noData: false,
            loadingTableEffect: true,
            rows: 20,
            pagination:{
                rowsPerPage: 10,
            },
            headers: [
                {
                    text: 'Table Number',
                    align: 'left',
                    value: 'table_number'
                },
                { text: 'Created At', value: 'created_at' },
                { text: 'Updated At', value: 'updated_at' },
                { text: 'Delete',   align: 'center'},
            ],
            tables: [],
            editedIndex: -1,
            editedItem: {
                table_number: 0,
                created_at: 0,
                updated_at: 0
            },
            defaultItem: {
                table_number: 0,
                created_at: 0,
                updated_at: 0
            }
        }),
        methods: {
            initialize () {
                this.loadingTableEffect=true;
                axios.get('tables').then(response => {
                    if (response.status === 200) {
                        this.tables = response.data.data;
                        this.loadingTableEffect=false;
                    }
                }).catch(error => {
                    this.noData = true;
                    this.loadingTableEffect=false;
                    console.log(error);
                });
            },
            editItem (item) {
                this.editedIndex = this.tables.indexOf(item)
                this.editedItem = Object.assign({}, item)
                this.dialog = true
            },
            deleteItem (item) {
                const index = this.tables.indexOf(item);
                axios.delete('table/delete/'+item.table_number).then(response => {
                    if(response.status === 204) {
                        this.text = 'Deleted Table Sucessfully';
                        this.snackbar = true;
                        this.initialize();
                    }
                }).catch(error => {
                    console.log(error);
                });
                confirm('Are you sure you want to delete this table?')
            },
            close () {
                this.dialog = false
                setTimeout(() => {
                    this.editedItem = Object.assign({}, this.defaultItem)
                    this.editedIndex = -1
                }, 300)
            },
            save () {
                if (this.editedIndex > -1) {
                } else {
                    axios.post('tables',{
                        "table_number": this.editedItem.table_number,
                    }).then(response => {
                        console.log(response);
                        this.text = 'Created Table Sucessfully';
                        this.snackbar = true;
                        this.initialize();
                    }).catch(error =>{
                        console.log(error);
                    });
                }
                this.close()
            },
        },
        created () {
            this.initialize()
        },
        computed: {
            formTitle () {
                return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
            }
        },
        watch: {
            dialog (val) {
                val || this.close()
            }
        },
    }
</script>

<style scoped>
</style>