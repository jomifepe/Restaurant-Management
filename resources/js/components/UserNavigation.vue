<template>
  <v-app class="grey lighten-4">
    <v-content>
      <v-layout align-center justify-center>
        <v-flex xs12 md8 class="white elevation-3">
          <v-toolbar align-center justify-center dark color="grey darken-3">
            <v-toolbar-title>DAD Restaurant</v-toolbar-title>
            <v-progress-circular v-if="$store.state.progressBarShown" color="white"
                class="ml-3" :value="$store.state.progressBarValue" 
                :indeterminate="$store.state.progressBarIndeterminate"></v-progress-circular>
            <v-spacer></v-spacer>
            <v-toolbar-items class="hidden-sm-and-down">
              <v-btn flat to="/">Home</v-btn>
              <v-btn flat to="menu">Menu</v-btn>
              <v-btn flat to="login">Login</v-btn>
            </v-toolbar-items>
          </v-toolbar>
          <v-card>
            <router-view></router-view>
          </v-card>
          <v-footer dark height="auto">
            <v-card flat tile class="grey darken-2 white--text text-xs-center">
              <v-card-text>
                <v-btn v-for="link in links" :key="link" color="white" flat round>{{ link }}</v-btn>
              </v-card-text>
              <v-card-text class="grey darken-3 white--text">
                  &copy;{{ new Date() | moment("YYYY") }} — 
                  <strong>DAD Restaurant</strong>
              </v-card-text>
            </v-card>
          </v-footer>
        </v-flex>
      </v-layout>
    </v-content>
  </v-app>
</template>

<script>
import moment from 'moment';

export default {
  name: "UserNavigation",
  data: () => ({
    links: ["Home", "About Us", "Team", "Services", "Contact Us"]
  }),
  computed: {
    user() {
      return this.$store.state.user;
    }
  }
};
</script>

<style scoped>
.v-footer div {
  width: 100%;
}
</style>