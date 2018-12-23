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
			this.showErrorToast(message);
			console.error(error);
		},
		showSuccessToast(message, time = 3000) {
			this.$toasted.success(message, {
				icon : 'check',
				position: 'bottom-center',
				duration : time
			});
		},
		showErrorToast(message, time = 3000) {
			this.$toasted.error(message, {
				icon : 'error',
				position: 'bottom-center',
				duration : time
			});
		}
	}
};

export const helper = {
    methods: {
        userFirstName(user) {
            return user.name.split(" ")[0];
        },
        userFirstAndLastName(user) {
            let parts = user.name.split(" ");
            if (parts.length > 1) {
                return `${parts[0]} ${parts[parts.length - 1]}`;
            }
            return user.name;
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
        }
    }
};

export const util = {
    methods: {
        isManager() {
            return this.$store.state.user.type === 'manager' ? true : false;
        },
    },
};
