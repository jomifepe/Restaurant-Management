<template>
    <div class="alert mt-4" :class="classtype" v-if="show">
        <button type="button" class="close" aria-label="Close" @click="closeAlertMessage">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong>@{{ message }}</strong>
    </div>
</template>

<script>
    export default {
        props:['classtype', 'show', 'message'],
        methods:{
            closeAlertMessage(){
                this.$emit('close-message');
            }
        }
    }
</script>

<style scoped>

</style>