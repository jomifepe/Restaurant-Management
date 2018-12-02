<template>
    <sidebar-menu :menu="menu"></sidebar-menu>
</template>


<script>
    import SideBar from './SideBarMenu.vue';

    export default {
        components: {
          "sidebar-menu": SideBar
        },
        data() {
            return {
                menu: [
                    {
                        href: 'profile',
                        title: 'My Profile',
                        user: this.$store.state.user,
                    },
                    {
                        href: 'menu',
                        title: 'Menu',
                        icon: 'fa fa-chart-area'
                    },
                ]
            }
        }
    }
</script>