<template>

    <sidebar-menu :menu="menu"></sidebar-menu>
</template>


<script>
    module.exports = {
        data() {
            return {
                menu: [
                    {
                        href: '',
                        title: 'My Profile',
                        user: this.$store.state.user,
                    },
                    {
                        href: '#',
                        title: 'STOCK',
                        icon: 'fa fa-chart-area'
                    },
                ]
            }
        }
    }
</script>