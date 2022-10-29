import {RouterLinkStub, shallowMount} from '@vue/test-utils';
import Component from '@/LoginComponent.vue';

describe('Testing data', () => {
    let wrapper;
    beforeEach(() => {
        const mocks = {
            $store: {
                state: {
                    auth: {
                        apiStatus: jest.fn(),
                        loginErrorMessages: jest.fn()
                    }
                },
                commit: jest.fn()
            },
        };
        wrapper = shallowMount(Component, {
            mocks,
            stubs: {
                RouterLink: RouterLinkStub
            },
        });
    });
    test('email is empty string in loginForm', () => {
        expect(wrapper.vm.loginForm.email).toBe('');
    });
    test('password is empty string in loginForm', () => {
        expect(wrapper.vm.loginForm.password).toBe('');
    });
});

describe('Testing methods', () => {
    let wrapper;
    beforeEach(() => {
        const mocks = {
            $store: {
                dispatch: jest.fn(),
                state: {
                    auth: {
                        loginErrorMessages: jest.fn()
                    }
                },
                commit: jest.fn(),
            },
            $router: {
                push: jest.fn().mockResolvedValue({}),
            }
        };
        wrapper = shallowMount(Component, {
            mocks,
            stubs: {
                RouterLink: RouterLinkStub
            }
        });
    });
    describe('Testing Login method', () => {
        test('success to Login', async () => {
            wrapper.vm.loginForm = {email: 'success@test.com', password: 'password'}
            wrapper.vm.$store.state.auth.apiStatus = true;
            wrapper.vm.login();

            await wrapper.vm.$nextTick();
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalled();
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith('auth/login', {
                email: 'success@test.com',
                password: 'password'
            });
            expect(wrapper.vm.$router.push).toHaveBeenCalled();
            expect(wrapper.vm.$router.push).toHaveBeenCalledWith('/upload');
        });
        test('fail to Login', async () => {
            wrapper.vm.loginForm = {email: 'fail@test.com', password: 'password'}
            wrapper.vm.$store.state.auth.apiStatus = false;
            wrapper.vm.login();

            await wrapper.vm.$nextTick();
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalled();
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith('auth/login', {
                email: 'fail@test.com',
                password: 'password'
            });
            expect(wrapper.vm.$router.push).toHaveBeenCalledTimes(0);
        });
    });
    describe('Testing Logout method', () => {
        test('success to logout', () => {
            wrapper.vm.logout();

            wrapper.vm.$nextTick(() => {
                expect(wrapper.vm.$store.dispatch).toHaveBeenCalled();
                expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith('auth/logout');
                expect(wrapper.vm.$router.push).toHaveBeenCalled();
                expect(wrapper.vm.$router.push).toHaveBeenCalledWith('/login');
            });
        });
    });
    describe('Testing reset method', () => {
        test('reset data successfully', () => {
            wrapper.vm.loginForm = {email: 'test@test.com', password: 'password'};

            expect(wrapper.vm.loginForm.email).toBe('test@test.com');
            expect(wrapper.vm.loginForm.password).toBe('password');
            wrapper.vm.reset();
            expect(wrapper.vm.loginForm.email).toBe('');
            expect(wrapper.vm.loginForm.password).toBe('');
        });
    });
    describe('Testing clearError method', () => {
        test('set null to LoginErrorMessages', () => {
            wrapper.vm.clearError();

            expect(wrapper.vm.$store.commit).toHaveBeenCalled();
            expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('auth/setLoginErrorMessages', null);
        });
    });
});

describe('Testing created', () => {
    test('call clearError method', () => {
        const clearErrorSpy = jest.spyOn(Component.methods, 'clearError');
        const mocks = {
            $store: {
                dispatch: jest.fn(),
                state: {
                    auth: {
                        loginErrorMessages: jest.fn()
                    }
                },
                commit: jest.fn(),
            },
        }
        shallowMount(Component, {
            mocks,
            stubs: {
                RouterLink: RouterLinkStub
            }
        });
        expect(clearErrorSpy).toHaveBeenCalled();
    });
});

describe('Testing @click event', () => {
    test('call login method when submit', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    state: {
                        auth: {
                            apiStatus: jest.fn(),
                            loginErrorMessages: jest.fn()
                        }
                    },
                    commit: jest.fn()
                },
            },
            stubs: {
                RouterLink: RouterLinkStub
            }
        });
        wrapper.vm.login = jest.fn();

        wrapper.find('form.wrapper').trigger('submit.prevent');

        expect(wrapper.vm.login).toHaveBeenCalled();
    });
});

describe('Testing v-if', () => {
    test('error divs are invisible when error object is empty', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    state: {
                        auth: {
                            apiStatus: jest.fn(),
                            loginErrorMessages: {},
                        }
                    },
                    commit: jest.fn()
                },
            },
            stubs: {
                RouterLink: RouterLinkStub
            },
        });
        expect(wrapper.findAll('div.errors').length).toBe(0);
        expect(wrapper.findAll('div.error_text').length).toBe(0);
    });
    test('error divs are visible when error object has error message of email', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    state: {
                        auth: {
                            apiStatus: jest.fn(),
                            loginErrorMessages: {email: ['email is not valid.']},
                        }
                    },
                    commit: jest.fn()
                },
            },
            stubs: {
                RouterLink: RouterLinkStub
            },
        });
        expect(wrapper.findAll('div.errors').length).toBe(2);
        expect(wrapper.findAll('div.error_text').length).toBe(1);
        expect(wrapper.findAll('div.error_text').at(0).text()).toBe('email is not valid.');
    });
    test('error divs are visible when error object has error message of password', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    state: {
                        auth: {
                            apiStatus: jest.fn(),
                            loginErrorMessages: {password: ['password is not valid.']},
                        }
                    },
                    commit: jest.fn()
                },
            },
            stubs: {
                RouterLink: RouterLinkStub
            },
        });
        expect(wrapper.findAll('div.errors').length).toBe(2);
        expect(wrapper.findAll('div.error_text').length).toBe(1);
        expect(wrapper.findAll('div.error_text').at(0).text()).toBe('password is not valid.');
    });
    test('error divs are visible when error object has multiple error messages', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    state: {
                        auth: {
                            apiStatus: jest.fn(),
                            loginErrorMessages: {email: ['email is not valid.'], password: ['password is not valid.']},
                        }
                    },
                    commit: jest.fn()
                },
            },
            stubs: {
                RouterLink: RouterLinkStub
            },
        });
        expect(wrapper.findAll('div.errors').length).toBe(2);
        expect(wrapper.findAll('div.error_text').length).toBe(2);
        expect(wrapper.findAll('div.error_text').at(0).text()).toBe('email is not valid.');
        expect(wrapper.findAll('div.error_text').at(1).text()).toBe('password is not valid.');
    });
});

describe('Testing router-link', () => {
    test('move to Home', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    state: {
                        auth: {
                            apiStatus: jest.fn(),
                            loginErrorMessages: jest.fn()
                        }
                    },
                    commit: jest.fn()
                },
            },
            stubs: {
                RouterLink: RouterLinkStub
            },
        });
        const links = wrapper.findAllComponents(RouterLinkStub)
        const target = links.at(0);
        expect(target.text()).toBe('HOME');
        expect(target.props().to).toMatchObject({"name": "main"});
    });
});

describe('Testing snapshot', () => {
    test('default (no errors)', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    state: {
                        auth: {
                            apiStatus: jest.fn(),
                            loginErrorMessages: {}
                        }
                    },
                    commit: jest.fn()
                },
            },
            stubs: {
                RouterLink: RouterLinkStub
            },
        });
        expect(wrapper.element).toMatchSnapshot();
    });
    test('validation error (email)', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    state: {
                        auth: {
                            apiStatus: jest.fn(),
                            loginErrorMessages: {email: ['email is not valid.']}
                        }
                    },
                    commit: jest.fn()
                },
            },
            stubs: {
                RouterLink: RouterLinkStub
            },
        });
        expect(wrapper.element).toMatchSnapshot();
    });
    test('validation error (password)', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    state: {
                        auth: {
                            apiStatus: jest.fn(),
                            loginErrorMessages: {password: ['password is not valid.']}
                        }
                    },
                    commit: jest.fn()
                },
            },
            stubs: {
                RouterLink: RouterLinkStub
            },
        });
        expect(wrapper.element).toMatchSnapshot();
    });
    test('validation error (email & password)', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    state: {
                        auth: {
                            apiStatus: jest.fn(),
                            loginErrorMessages: {email: ['email is not valid.'], password: ['password is not valid.']}
                        }
                    },
                    commit: jest.fn()
                },
            },
            stubs: {
                RouterLink: RouterLinkStub
            },
        });
        expect(wrapper.element).toMatchSnapshot();
    });
});
