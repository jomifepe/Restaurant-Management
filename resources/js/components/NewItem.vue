<template>
    <div class="text-xs-center">
        <v-btn
                :disabled="loading"
                :loading="loading"
                dark class="mb-2"
                color="primary"
                @click="loading = true">
            New Item
        </v-btn>
        <v-dialog
                v-model="loading"
                hide-overlay
                persistent
                width="300">
            <v-card color="primary" dark>
                <v-card-text>
                    Please stand by
                    <v-progress-linear
                            indeterminate
                            color="white"
                            class="mb-0"
                    ></v-progress-linear>
                </v-card-text>
            </v-card>
        </v-dialog>


        <div v-if="dialog">
            <v-layout row justify-center>
                <v-dialog v-model="dialog" persistent max-width="600px">
                    <v-card>
                        <v-card-title>
                            <span class="headline">User Profile</span>
                        </v-card-title>
                        <form @submit.prevent="validateBeforeSubmit">
                            <v-card-text>
                                <v-container grid-list-md>
                                    <v-layout wrap>
                                        <v-flex xs12 sm6 md4>
                                            <v-text-field v-model="item.name" v-validate="'required|alpha_spaces'" name="name" type="text" label="Item name*" required></v-text-field>
                                            <span style="color:red">{{ errors.first('name') }}</span>
                                        </v-flex>
                                        <v-flex xs12 sm6 md4>
                                            <v-text-field v-model="item.price" v-validate="'required|decimal:2'" name="price" type="text" label="Price*" hint="example: 10,30"></v-text-field>
                                            <span style="color:red">{{ errors.first('price') }}</span>
                                        </v-flex>
                                        <v-flex xs12 sm6 md4>
                                            <input type="file" @change="onFileSelected">
                                        </v-flex>
                                        <v-flex xs12>
                                            <v-text-field v-model="item.description" v-validate="'required|alpha_spaces'" name="description" type="text" label="Description*" required></v-text-field>
                                            <span style="color:red">{{ errors.first('description') }}</span>
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-select v-model="item.type" v-validate="'required'" name="type" type="text"
                                                      :items="['dish', 'drink']"
                                                      label="Type"
                                                      required
                                            ></v-select>
                                            <span style="color:red">{{ errors.first('type') }}</span>
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                        </v-flex>
                                    </v-layout>

                                </v-container>
                                <small>*indicates required field</small>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="blue darken-1" flat @click="dialog = false" >Close</v-btn>
                                <v-btn color="blue darken-1" flat type="submit" >Save</v-btn>
                            </v-card-actions>
                        </form>
                    </v-card>
                </v-dialog>
            </v-layout>
        </div>


    </div>
</template>

<script>
    import UploadButton from 'vuetify-upload-button';

    export default {
        name: "NewItem",
        components:{
            UploadButton
        },
        data () {
            return {
                loading: false,
                dialog: false,
                selectedFile: null,
                item:{
                    name: '',
                    type:'',
                    description: '',
                    price: '',
                    photo: null,
                }
            }
        },
        methods: {
            save() {
                console.log('entered SAVED function');
                console.log(this.item);

                axios.post('items',
                    { "name": this.item.name,
                     "price": this.item.price,
                     "description": this.item.description,
                     "type": this.item.type,
                     "photo_url": this.item.photo,
                    }).
                then(response =>{
                    console.log('Saved Success!');
                    console.log(response);

                }).catch(error =>{
                    console.log(error);
                });
            },
            onFileSelected(event){
                this.item.photo = event.target.files[0];
            },

            validateBeforeSubmit() {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        // eslint-disable-next-line
                        alert('Form Submitted!');
                        this.dialog = false;
                        this.save();
                        return;
                    }

                    alert('Correct them errors!');
                });
            },
        },
        watch: {
            loading (val) {
                if (!val) return;
                setTimeout(() => (this.loading = false, this.dialog= true), 1000)
            }
        },
    }
</script>

<style scoped>

</style>