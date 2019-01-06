<template>
    <v-container grid-list-md fluid>
        <v-layout row>
            <v-flex xs12>
                <v-toolbar flat color="white">
                    <v-toolbar-title>Restaurant Tables</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-dialog v-model="dialog" max-width="500px">
                        <v-btn slot="activator" color="primary" dark class="mb-2">New Table</v-btn>
                        <form id="form" @submit.prevent="validateBeforeSubmit">
                            <v-card>
                                <v-card-title>
                                    <span class="headline">{{ formTitle }}</span>
                                </v-card-title>
                                <v-card-title>
                                    <span >Insert 0 to auto create table</span>
                                </v-card-title>
                                <div v-if="hasValidationErrors" v-for="(value, key, index) in validationErrors" :key="index">
                                    <errors :msg="value"></errors>
                                </div>
                                <v-card-text>
                                    <v-container grid-list-md>
                                        <v-layout wrap>
                                            <v-flex xs12 sm6 md4>
                                                <v-text-field v-model="editedItem.table_number" v-validate="'required|numeric'" name="tableNumber" type="text" label="Table number"></v-text-field>
                                                <span style="color:red">{{ errors.first('tableNumber') }}</span>
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
                        </form>
                    </v-dialog>
                </v-toolbar>
                <v-data-table :headers="headers" :items="tables" :pagination.sync="pagination" :loading="loadingTableEffect"
                                :rowsPerPage="rows" class="elevation-1">
                    <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                    <template slot="items" slot-scope="props">
                        <td class="text-xs-center">{{ props.item.table_number }}</td>
                        <td class="text-xs-left">{{ props.item.created_at.date | moment("YYYY-MM-DD HH:mm:ss") }}</td>
                        <td class="text-xs-left">{{ props.item.updated_at.date | moment("YYYY-MM-DD HH:mm:ss") }}</td>
                        <td v-if="props.item.deleted_at != null" class="text-xs-left">{{ props.item.deleted_at.date }}</td>
                        <td v-else>N/A</td>
                        <td class="justify-center layout px-0">
                            <v-tooltip top v-if="props.item.deleted_at != null">
                                <v-btn icon slot="activator" @click.prevent="restoreTable(props.item)">
                                    <v-icon color="red" dark>undo</v-icon>
                                </v-btn>
                                <span>Undo table deletion</span>
                            </v-tooltip>
                            <v-tooltip top v-else>
                                <v-btn icon slot="activator" @click.prevent="deleteTable(props.item)">
                                    <v-icon color="red" dark>delete</v-icon>
                                </v-btn>
                                <span>Delete table</span>
                            </v-tooltip>
                        </td>
                    </template>
                    <template slot="no-data">
                        <v-alert :value="noData" color="error" icon="warning">
                            Sorry, nothing to display here :(
                        </v-alert>
                    </template>
                    <template slot="no-data">
                        <v-btn color="primary" @click="getTables">Reset</v-btn>
                    </template>
                </v-data-table>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script>
    import AdminItemMenu from '../ItemMenu.vue';
    import Errors from '../Errors.vue';
    import {toasts, helper} from '../../../mixin';

    export default {
        name: "Tables",
        mixins: [toasts, helper],
        components: {
            'items-menu': AdminItemMenu,
            Errors
        },
        data: () => ({
            dialog: false,
            noData: false,
            hasValidationErrors: false,
            validationErrors:[],
            loadingTableEffect: true,
            rows: 20,
            pagination: {
                rowsPerPage: 10,
            },
            headers: [
                { text: 'Table Number', align: 'left', value: 'table_number'},
                { text: 'Created At', value: 'created_at' },
                { text: 'Updated At', value: 'updated_at' },
                { text: 'Deleted At', value: 'deleted_at'},
                { text: 'Action',   align: 'center', value: ''} ],
            tables: [],
            editedIndex: -1,
            editedItem: {
                table_number: 0,
            },
            defaultItem: {
                table_number: 0,
            }
        }),
        methods: {
            getTables () {
                this.loadingTableEffect=true;
                axios.get('tables').then(response => {
                    if (response.status === 200) {
                        this.tables = response.data.data;
                        this.loadingTableEffect=false;
                    }
                }).catch(error => {
                    this.noData = true;
                    this.loadingTableEffect=false;
                });
            },
            deleteTable (item) {
                if (!this.isUserInShift()) return;

                if(confirm('Are you sure you want to delete table ' + item.table_number +' ?')){
                    axios.delete('tables/'+item.table_number).then(response => {
                        if(response.status === 204) {
                            this.$toasted.show('Deleted table successfully', {
                                icon: "check",
                                position: "bottom-center",
                                duration : 3000
                            });
                            this.getTables();
                        }
                    }).catch(error => {
                        this.hasErrors(error.response.data.errors);
                        this.$toasted.show('Problem deleting table', {
                            icon: "check",
                            position: "bottom-center",
                            duration : 3000
                        });
                    });
                }
            },
            restoreTable(item){
                if (!this.isUserInShift()) return;

                if(confirm('Are you sure you want to recover table ' + item.table_number +' ?')){
                    axios.put('table/restore/'+item.table_number).then(response => {
                        if(response.status === 200) {
                            this.$toasted.show('Table recovered successfully', {
                                icon: "check",
                                position: "bottom-center",
                                duration : 3000
                            });
                            this.getTables();
                        }
                    }).catch(error => {
                        this.hasErrors(error.response.data.errors);
                        this.$toasted.show('Problem recovering table', {
                            icon: "check",
                            position: "bottom-center",
                            duration : 3000
                        });
                    });
                }
            },
            close () {
                this.dialog = false
                setTimeout(() => {
                    this.editedItem = Object.assign({}, this.defaultItem)
                    this.editedIndex = -1
                }, 300)
            },
            save () {
                if (!this.isUserInShift()) return;
                
                if (this.editedIndex > -1) {
                } else {
                    axios.post('tables',{"table_number": this.editedItem.table_number,})
                        .then(response => {
                            this.$toasted.show('Created table successfully', {
                                icon: "check",
                                position: "bottom-center",
                                duration : 3000
                            });
                            this.close();
                            this.getTables();
                        }).catch(error =>{
                        this.hasErrors(error.response.data.errors);
                        this.$toasted.show('Problem creating table', {
                            icon: "check",
                            position: "bottom-center",
                            duration : 3000
                        });
                    });
                }
            },
            hasErrors(errors) {
                this.validationErrors = errors;
                this.hasValidationErrors = true;
                setTimeout(() => (this.hasValidationErrors = false), 6000)
            },
            validateBeforeSubmit() {
                if (!this.isUserInShift()) return;

                this.$validator.validateAll().then((result) => {
                    if (!result) {
                        alert('Correct them errors!');
                    }else{
                        this.save();
                        return;
                    }
                });
            },
        },
        created () {
            this.getTables()
        },
        computed: {
            formTitle () {
                return this.editedIndex === -1 ? 'New Table' : 'Edit Table'
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