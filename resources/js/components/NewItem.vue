<template>
    <div class="text-xs-center">
        <v-btn dark class="mb-2" color="primary" @click.prevent="showForm = true">
            New Item
        </v-btn>

        <div v-if="showForm">
            <item-form  @onGetItems="onGetItems()" @onCloseForm="closeForm()"></item-form>
        </div>
    </div>
</template>

<script>
    import ItemForm from './ItemForm';

    export default {
        name: "NewItem",
        components:{
            ItemForm
        },
        data () {
            return {
                showForm: false,
            }
        },
        methods:{
            closeForm(){
                this.showForm = false;
            },
            onGetItems(){
                this.$emit('onGetItems');
            }
        }
    }
</script>

<style scoped>

</style>