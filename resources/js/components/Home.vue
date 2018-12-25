<template>
    <v-flex xs12 class="mt-4">
        <v-parallax dark src="https://cdn.vuetifyjs.com/images/backgrounds/vbanner.jpg">
            <v-layout align-center column justify-center>
            <h1 class="display-2 font-weight-thin mb-3">DAD Restaurant</h1>
            <h4 class="subheading">Where the food’s the star</h4>
            <v-btn depressed class="mt-4" color="primary" to="menu">
                View our menu
            </v-btn>
            </v-layout>
        </v-parallax>
        <v-container>
            <h5 class="headline">Lorem Impsum</h5>
            <p class="body-1 font-weight-light">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. At ultrices mi tempus imperdiet nulla malesuada. Pellentesque id nibh tortor id aliquet lectus. Posuere lorem ipsum dolor sit amet. Eu non diam phasellus vestibulum. Amet consectetur adipiscing elit ut aliquam purus sit. Turpis in eu mi bibendum neque egestas congue quisque egestas. Id consectetur purus ut faucibus. Tempus quam pellentesque nec nam aliquam sem et tortor consequat. Ultrices dui sapien eget mi proin sed libero enim sed. Maecenas sed enim ut sem viverra aliquet. Non blandit massa enim nec. Nunc lobortis mattis aliquam faucibus purus in. Diam vel quam elementum pulvinar etiam non quam. Ut sem viverra aliquet eget sit amet tellus cras adipiscing. Tristique senectus et netus et malesuada fames ac turpis.
            </p>
        </v-container>
    </v-flex>
</template>

<script>

    export default {
        name: "Home",
        mounted() {
            this.$store.commit('setPanelTitle', 'Home');
        }
    }
</script>

<style scoped>

</style>