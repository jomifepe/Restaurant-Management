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
        },
        isUserInShift() {
            if (!this.$store.state.user.shift_active) {
                this.showErrorToast('Your shift hasn\'t started', 2000);
                return false;
            } 
            return true;
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
        arrayAdd(array, value) {
            if (array.indexOf(value) === -1) {
              array.push(value);
            }
        },
        arrayRemove(array, value) {
            var index = array.indexOf(value);
            if (index !== -1) {
                array.splice(index, 1);
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
        getOrderStateColorHEX(state) {
            switch (state) {
                case 'pending': return '#FFC107';
                case 'confirmed': return '#FF9800';
                case 'in preparation': return '#2196F3';
                case 'prepared': return '#AFB42B';
                case 'delivered': return '#4CAF50';
                case 'not delivered': return '#F44336';
            }
        },
        getOrderStateColor(state) {
            switch (state) {
                case 'pending': return 'amber';
                case 'confirmed': return 'orange';
                case 'in preparation': return 'blue';
                case 'prepared': return 'lime darken-2';
                case 'delivered': return 'green';
                case 'not delivered': return 'red';
            }
        },
        getInvoiceStateColor(state) {
            switch (state) {
                case 'pending': return 'amber';
                case 'paid': return 'green';
                case 'not paid': return 'red';
            }
        },
        getMealStateColor(state) {
            switch (state) {
                case 'active': return 'red';
                case 'terminated': return 'orange';
                case 'paid': return 'green';
                case 'not paid': return 'blue';
            }
        },
        getMealStateTextColor(state) {
            return `${this.getMealStateColor(state)}--text`;
        },
        getInvoiceStateTextColor(state) {
            return `${this.getInvoiceStateColor(state)}--text`;
        },
        getOrderStateTextColor(state) {
            return `${this.getOrderStateColor(state)}--text`;
        }
    }
};