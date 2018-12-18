<template>
    <div class="text-xs-center">
        <v-btn :disabled="loading" :loading="loading" dark class="mb-2" color="primary" @click="loading = true">
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
                                <div v-if="hasValidationErrors">
                                    <ul class="alert alert-danger">
                                        <li v-for="(value, key, index) in validationErrors">{{ value }}</li>
                                    </ul>
                                </div>`
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
                                            <input type="file" name="photo_url" @change="onFileSelected">
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
                hasValidationErrors: false,
                validationErrors:[],
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
                let toast;
                let form = new FormData;
                console.log(this.item);

                form.append('name', this.item.name);
                form.append('price', this.item.price);
                form.append('description', this.item.description);
                form.append('type', this.item.type);
                form.append('photo_url', this.item.photo);
                //const config = {headers: {'Content-Type': 'multipart/form-data'}};

                axios.post('items', form).then(response =>{
                    toast = this.$toasted.show('New item created successfully', {
                        icon: "check",
                        position: "bottom-center",
                        duration : 3000
                    });
                    this.$emit('onGetItems');
                    this.clearItemData();
                    this.dialog = false;
                }).catch(error =>{
                    this.hasErrors(error.response.data.errors);
                    toast = this.$toasted.show('problem occurred in item creation', {
                        icon: "error",
                        position: "bottom-center",
                        duration : 3000
                    });
                });
            },
            onFileSelected(event){
                console.log('entered event');
                this.item.photo = event.target.files[0];
            },
            validateBeforeSubmit() {
                this.$validator.validateAll().then((result) => {
                    if (!result) {
                        alert('Correct them errors!');
                    }else{
                        this.save();
                        return;
                    }
                });
            },
            clearItemData(){
                this.item.name='',
                this.item.type='',
                this.item.description = '',
                this.item.price = '',
                this.item.photo = null
            },
            hasErrors(errors){
                this.validationErrors = errors;
                this.hasValidationErrors = true;
                setTimeout(() => (this.hasValidationErrors = false), 6000)
            }
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