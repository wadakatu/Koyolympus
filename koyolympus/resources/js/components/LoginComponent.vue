<template>
    <form action="/api/login" method="post" class="wrapper" @submit.prevent="login">
        <div class="contact-form">
            <div class="input-fields">
                <input type="email" class="input" name="email" placeholder="Email" v-model="loginForm.email" required>
                <div v-if="loginErrors" class="errors">
                    <div class="error_text" v-for="msg in loginErrors.email" :key="msg">{{ msg }}}</div>
                </div>
                <input type="password" class="input" name="password" placeholder="Password"
                       v-model="loginForm.password" required>
                <div v-if="loginErrors" class="errors">
                    <div class="error_text" v-for="msg in loginErrors.password" :key="msg">{{ msg }}</div>
                </div>
            </div>
            <button type="submit" class="btn">Send</button>
            <router-link v-bind:to="{name: 'main'}">
                <button class="btn">HOME</button>
            </router-link>
        </div>
    </form>
</template>

<script>
export default {
    name: "LoginComponent.vue",
    data() {
        return {
            errors: {},
            loginForm: {
                email: '',
                password: '',
            },
        }
    },
    methods: {
        async login() {
            await this.$store.dispatch('auth/login', this.loginForm)

            if (this.apiStatus) {
                await this.$router.push('/upload');
            }
        },
        async logout() {
            await this.$store.dispatch('auth/logout');
            await this.$router.push('/login')
        },
        reset() {
            console.log('reset done.');
            Object.assign(this.$data, this.$options.data.call(this));
        },
        clearError() {
            this.$store.commit('auth/setLoginErrorMessages', null)
        },
    },
    created() {
        this.clearError()
    },
    computed: {
        apiStatus() {
            return this.$store.state.auth.apiStatus;
        },
        loginErrors() {
            return this.$store.state.auth.loginErrorMessages;
        }
    }
}
</script>

<style scoped>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    outline: none;
    font-family: 'Roboto', sans-serif;
}

.wrapper {
    flex-basis: 50%;
    transform: translateY(-50%);
    width: 100%;
    padding: 0 20px;
}

.contact-form {
    max-width: 70%;
    background: transparent;
    padding: 10px;
    margin-left: auto;
    margin-right: auto;
    position: relative;
    top: 115px;
    border-radius: 5px;
    text-align: center;
}

.input-fields {
    display: flex;
    flex-direction: column;
    margin-right: 10%;
}

.input-fields .input {
    margin-bottom: 25px;
    margin-top: 25px;
    background: transparent;
    border: 0;
    border-bottom: 2px solid #c5ecfd;
    padding: 10px;
    color: #c5ecfd;
    width: 100%;
}

::-webkit-input-placeholder {
    color: #c5ecfd;
}

::-ms-input-placeholder {
    color: #c5ecfd;
}

.btn {
    background: #39b7dd;
    text-align: center;
    padding: 15px;
    border-radius: 5px;
    color: #fff;
    cursor: pointer;
    text-transform: uppercase;
    margin-right: 10%;
}

.error_text {
    color: #f9ff17;
    padding-bottom: 15px;
}

</style>
