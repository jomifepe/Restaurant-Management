export const itemMixin = {
    data() {
        return {
            active: false,
            childActive: false
        }
    },
    created() {
        this.active = this.item && this.item.href ? this.isLinkActive(this.item) : false
        this.childActive = this.item && this.item.child ? this.isChildActive(this.item.child) : false
        this.show = this.item && this.item.child ? this.isChildActive(this.item.child) : false
    },
    methods: {
        toggleDropdown() {
            this.show = !this.show
        },
        isLinkActive(item) {
            if ( this.$route ) {
                return item.href == this.$route.path
            } else {
                return item.href == window.location.pathname
            }
        },
        isChildActive(child) {
            for (let item of child) {
                if (this.isLinkActive(item)) {
                    return true
                }
                if (item.child) {
                    if ( this.isChildActive(item.child) ) {
                        return true
                    }
                }
            }
            return false
        }
    },
    computed: {
        isRouterLink() {
            return this.$router && this.item && this.item.href !== undefined
        }
    },
    watch: {
        $route() {
            this.active = this.item && this.item.href ? this.isLinkActive(this.item) : false
            this.childActive = this.item && this.item.child ? this.isChildActive(this.item.child) : false
        }
    },
};

export const toasts = {
	methods: {
		showErrorLog(message, error) {
			return this.showErrorToast(message);
        },
        showToast(message, icon, time = 3000) {
			return this.$toasted.show(message, {
				icon : icon,
				position: 'bottom-center',
				duration : time
			});
		},
        showTopRightToast(message, icon, time = 3000) {
			return this.$toasted.show(message, {
				icon : icon,
				position: 'top-right',
				duration : time
			});
        },
        showTopRightInfoToast(message, time = 3000) {
			return this.$toasted.info(message, {
				icon : 'info',
				position: 'top-right',
				duration : time
			});
        },
        showTopRightSuccessToast(message, time = 3000) {
			return this.$toasted.success(message, {
				icon : 'check_circle',
				position: 'top-right',
				duration : time
			});
        },
        showTopRightErrorToast(message, time = 3000) {
			return this.$toasted.error(message, {
				icon : 'error',
				position: 'top-right',
				duration : time
			});
		},
		showSuccessToast(message, time = 3000) {
			return  this.$toasted.success(message, {
				icon : 'check_circle',
				position: 'bottom-center',
				duration : time
			});
		},
		showErrorToast(message, time = 3000) {
			return  this.$toasted.error(message, {
				icon : 'error',
				position: 'bottom-center',
				duration : time
			});
		}
	}
};

export const helper = {
    computed: {
        isUserManager() {
            return this.$store.state.user && 
                this.$store.state.user.type === 'manager';
        }
    },
    methods: {
        userFirstName(name) {
            return name.split(" ")[0];
        },
        userFirstAndLastName(name) {
            let parts = name.split(" ");
            if (parts.length > 2) {
                return `${parts[0]} ${parts[parts.length - 1]}`;
            }
            return name;
        },
        getImage(obj) {
            if (!obj) {
                return 'https://via.placeholder.com/150';
            } else {
                return obj.photo_url;
            }
        },
        getUserAppearence(user) {
            switch (user.type) {
                case 'manager':
                    return {
                        icon: 'supervisor_account',
                        color: 'red'
                    };
                case 'waiter':
                    return {
                        icon: 'sentiment_satisfied_alt',
                        color: 'blue'
                    }
                case 'cook':
                    return {
                        icon: 'restaurant',
                        color: 'orange'
                    }
                case 'cashier':
                    return {
                        icon: 'attach_money',
                        color: 'teal'
                    }
            }
        },
        getOrderStateColor(state) {
            switch (state) {
                case 'pending': return 'amber';
                case 'confirmed': return 'orange';
                case 'in preparation': return 'blue';
                case 'prepared': return 'darken-1 lime';
                case 'delivered': return 'green';
                case 'not delivered': return 'red';
            }
        },
        getOrderStateTextColor(state) {
            return `${this.getOrderStateColor(state)}--text`;
        }
    }
};