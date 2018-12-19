<template>
    <v-layout row justify-center>
        <v-dialog v-model="dialog" persistent max-width="600px">
            <v-card>
                <v-card-title>
                    <span class="headline">Item</span>
                </v-card-title>
                <form id="form" @submit.prevent="validateBeforeSubmit">
                    <v-card-text>
                        <div v-if="hasValidationErrors">
                            <ul class="alert alert-danger">
                                <li v-for="(value, key, index) in validationErrors">{{ value }}</li>
                            </ul>
                        </div>
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
                        <v-btn color="blue darken-1" flat @click.prevent="onCloseForm()" >Close</v-btn>
                        <v-btn color="blue darken-1" flat type="submit" >Save</v-btn>
                    </v-card-actions>
                </form>
            </v-card>
        </v-dialog>
    </v-layout>
</template>

<script>
    export default {
        name: "ItemForm",
        props:['itemSelectedToEdit'],
        data() {
            return {
                dialog: true,
                isNewItem: false,
                selectedFile: null,
                hasValidationErrors: false,
                validationErrors:[],
                item:{
                    name: '',
                    type:'',
                    description: '',
                    price: '',
                    photo_url: null,
                }
            }
        },
        methods:{
            save() {
                /** FORM **/
                let form = new FormData;
                form.append('name', this.item.name);
                form.append('price', this.item.price);
                form.append('description', this.item.description);
                form.append('type', this.item.type);
                if(this.item.photo_url != null) {
                    form.append('photo_url', this.item.photo_url);  /** HAS IMAGE ? -> ADD TO FORM**/
                }

                if(this.isNewItem) {
                    /** CREATE NEW ITEM**/
                    this.createNewItem(form);
                } else {
                    /** EDIT ITEM**/
                    this.editItem(form);
                }
            },
            createNewItem(form){
                //form.append('photo_url', this.item.photo_url);

                axios.post('items', form).then(response => {
                    this.$toasted.show('New item created successfully', {
                        icon: "check",
                        position: "bottom-center",
                        duration: 3000
                    });
                    this.$emit('onGetItems');
                    this.$emit('onCloseForm');
                }).catch(error => {
                    console.log(error);
                    this.hasErrors(error.response.data.errors);
                    this.$toasted.show('problem occurred in item creation', {
                        icon: "error",
                        position: "bottom-center",
                        duration: 3000
                    });
                });
            },
            editItem(form){
                //if(this.item.photo_url != null) {
                //    form.append('photo_url', this.item.photo_url);
                //}
                form.append('method_', 'PUT');

                axios.post('items/update/'+this.item.id, form
                ).then(response => {
                    this.$toasted.show('Item edited successfully', {
                        icon: "check",
                        position: "bottom-center",
                        duration: 3000
                    });
                    this.$emit('onGetItems');
                    this.$emit('onCloseForm');
                }).catch(error => {
                    this.hasErrors(error.response.data.errors);
                    this.$toasted.show('problem occurred in item creation', {
                        icon: "error",
                        position: "bottom-center",
                        duration: 3000
                    });
                });
            },
            onFileSelected(event){
                this.item.photo_url = event.target.files[0];
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
                this.item.photo_url = null
            },
            hasErrors(errors){
                this.validationErrors = errors;
                this.hasValidationErrors = true;
                setTimeout(() => (this.hasValidationErrors = false), 6000)
            },
            onCloseForm(){
                this.$emit('onCloseForm');
            },
        },
        created(){
            if(this.itemSelectedToEdit != null) {
                this.item = iterationCopy(this.itemSelectedToEdit);
                this.item.photo_url = null;
                this.isNewItem = false;
            }else{
                this.isNewItem = true;
            }
            /** CLONE AN OBJECT **/
            function iterationCopy(src) {
                let target = {};
                for (let prop in src) {
                    if (src.hasOwnProperty(prop)) {
                        target[prop] = src[prop];
                    }
                }
                return target;
            }
        },
    }
</script>

<style scoped>

</style>