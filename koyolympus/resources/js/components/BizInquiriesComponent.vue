<template>
    <section class="contact">
        <div class="container">
            <div class="contactForm">
                <form action="./api/bizinq/send" method="post" @submit.prevent="postInquiry">
                    <input type="hidden" name="_token" v-model="csrf">
                    <h1 class="biz_title">Biz Inquiries</h1>
                    <div class="inputBox">
                        <input type="text" name="name" class="params_input" v-model="params.name" required placeholder=" ">
                        <span class="params_name">Your name</span>
                    </div>
                    <div class="error_text" v-html="errors.name"></div>
                    <div class="inputBox">
                        <input type="email" name="email" class="params_input" v-model="params.email" required placeholder=" ">
                        <span class="params_name">Email</span>
                    </div>
                    <div class="error_text" v-html="errors.email"></div>
                    <div class="inputBox">
                        <textarea name="opinion" class="params_textarea" v-model="params.opinion" required placeholder=" "></textarea>
                        <span class="params_name">Type your thoughts...</span>
                    </div>
                    <div class="error_text" v-html="errors.opinion"></div>
                    <div class="loading" v-if="loading">Sending...</div>
                    <div class="alert-success" v-if="sentEmail">Thank you for getting in touch!</div>
                    <div class="inputBox">
                        <input type="submit" class="sub_inq" v-bind:disabled="isPush" value="Send">
                        <input type="button" class="mov_home" @click="$router.push('/')" value="Home">
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>

<script>

export default {
    name: "BizInquiriesComponent.vue",
    components: {},
    data() {
        return {
            errors: {},
            params: {
                name: '',
                email: '',
                opinion: '',
            },
            isPush: false,
            sentEmail: false,
            loading: false,
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    },
    methods: {
        postInquiry() {
            this.sentEmail = false;
            this.isPush = true;
            this.errors = {};
            let self = this;
            const confirm = window.confirm('Would it be okay to send this inquiry?\nこの内容で送信してよろしいですか？');
            if (!confirm) {
                this.isPush = false;
            } else {
                self.loading = true;
                axios.post('/api/bizinq/send', this.params)
                    .then((response) => {
                        this.reset();
                        self.loading = false;
                        self.sentEmail = true;
                    })
                    .catch((error) => {
                        self.loading = false;
                        var errors = {};

                        for (var key in error.response.data.errors) {
                            if (error.response.data.errors.hasOwnProperty(key)) {
                                errors[key] = error.response.data.errors[key].join('<br>');
                            }
                        }
                        self.errors = errors;
                        self.isPush = false;
                    });
            }
        },
        reset() {
            Object.assign(this.$data, this.$options.data.call(this));
        },
    },
    mounted() {
        this.reset();
    }
}
</script>

<style scoped>

.contact {
    flex-basis: 50%;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}

.container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.contactForm {
    background: transparent;
}

.biz_title {
    font-size: 40px;
    margin-bottom: 20px;
    color: #fff;
    font-weight: 500;
    text-align: center;
}

.inputBox {
    position: relative;
    width: 100%;
    margin-top: 15px;
    text-align: center;
}

.params_input, .params_textarea, .sub_inq, .mov_home {
    width: 32vw;
    padding: 10px 0;
    font-size: 16px;
    margin: 10px 0;
    border: none;
    border-bottom: 2px solid #1f6fb2;
    outline: none;
    background: transparent;
    color: #fff;
}

.params_textarea {
    resize: none;
    height: 20vh;
}

.params_name {
    position: absolute;
    left: 0;
    top: 1vh;
    padding: 5px 0;
    font-size: 16px;
    margin: 10px 0;
    pointer-events: none;
    transition: 0.5s;
    color: #fff;
}

.params_input:focus ~ .params_name,
.params_textarea:focus ~ .params_name {
    font-size: 12px;
    transform: translateY(-20px);
}

.params_input:valid ~ .params_name,
.params_textarea:valid ~ .params_name {
    color: #43e91e;
    font-size: 12px;
    transform: translateY(-20px);
}

.params_input:invalid:not(:placeholder-shown) ~ .params_name,
.params_textarea:invalid:not(:placeholder-shown) ~ .params_name {
    color: #e91e63;
    font-size: 12px;
    transform: translateY(-20px);
}

.sub_inq {
    color: #FFF;
    display: inline-block;
    font-size: 15px;
    font-weight: bold;
    line-height: 20px;
    width: 9vw;
    margin-right: 10px;
    text-decoration: none;
    text-transform: uppercase;
    border: 1px solid transparent;
    outline: rgb(50, 230, 0) solid 2px;
    outline-offset: 0;
    text-align: center;
    text-shadow: none;
    transition: all 1.2s cubic-bezier(0.2, 1, 0.2, 1);
}

.mov_home {
    color: #FFF;
    display: inline-block;
    font-size: 15px;
    font-weight: bold;
    line-height: 20px;
    width: 9vw;
    margin-left: 10px;
    text-decoration: none;
    text-transform: uppercase;
    border: 1px solid transparent;
    outline: rgb(233, 8, 0) solid 2px;
    outline-offset: 0;
    text-align: center;
    text-shadow: none;
    transition: all 1.2s cubic-bezier(0.2, 1, 0.2, 1);
}

.mov_home:hover {
    border-color: #ffced1;
    box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.5), 0 0 20px rgba(255, 255, 255, 0.2);
    outline-color: transparent;
    outline-offset: 12px;
    text-shadow: 2px 2px 3px #000;
}

.sub_inq:hover {
    border-color: #d1ffd3;
    box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.5), 0 0 20px rgba(255, 255, 255, 0.2);
    outline-color: transparent;
    outline-offset: 12px;
    text-shadow: 2px 2px 3px #000;
}

.error_text {
    color: #f9ff17;
}

.alert-success {
    color: #2eff18;
    text-align: center;
}

.loading {
    color: #fff;
    text-align: center;
}

@media screen and (max-width: 950px) {
    .container {
        width: 100vw;
    }

    .contactForm {
        width: 100vw;
        text-align: center;
    }

    .params_input, .params_textarea, .sub_inq, .mov_home {
        width: 70vw;
    }

    .params_name {
        left: 15vw;
    }

    .sub_inq {
        width: 18vw;
        left: 28vw;
    }

    .mov_home {
        width: 18vw;
        right: 28vw;
    }
}

@media screen and (max-width: 480px) {
    .contact {
        height: 100%;
    }

    .contactForm {
        width: 100vw;
        height: 100%;
        padding: 2em;
    }

    .params_input, .params_textarea, .sub_inq, .mov_home {
        width: 80vw;
    }

    .params_textarea {
        height: 18vh;
    }

    .params_name {
        position: static;
    }

    .sub_inq {
        width: 25vw;
        position: relative;
        left: -5vw;
    }

    .mov_home {
        width: 25vw;
        position: relative;
        right: -5vw;
    }
}

@media screen and (min-height: 910px) and (max-width: 1330px) {
    .params_textarea {
        height: 20vh;
    }

    .params_name {
        top: 0;
    }

    .sub_inq {
        width: 9vw;
        left: 18vw;
    }

    .mov_home {
        width: 9vw;
        right: 60vw;
    }
}

@media screen and (min-height: 910px) and (max-width: 950px) {

    .sub_inq {
        width: 12vw;
        left: 30vw;
    }

    .mov_home {
        width: 12vw;
        right: 30vw;
    }
}

</style>
