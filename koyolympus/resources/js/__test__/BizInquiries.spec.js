import {shallowMount} from '@vue/test-utils';
import Component from '@/BizInquiriesComponent.vue';

let wrapper;
const csrfToken = 'abc';

beforeEach(() => {
    window.axios = {
        post: jest.fn(),
    };
    const meta = document.createElement('meta');
    meta.name = 'csrf-token';
    meta.content = csrfToken;
    document.body.appendChild(meta);
    wrapper = shallowMount(Component, {
        attachTo: document.body,
        sync: false,
    });
})

afterEach(() => {
    wrapper.destroy();
});

describe('Testing data', () => {
    test('Csrf-token is valid and string', () => {
        expect(wrapper.vm.csrf).toBe(csrfToken);
    });
});

describe('Testing methods', () => {
    describe('Testing postInquiry method', () => {
        test('send inquiry without confirmation', async () => {
            window.confirm = jest.fn(() => false);
            wrapper.vm.reset = jest.fn();
            await wrapper.vm.postInquiry();
            expect(window.axios.post).toHaveBeenCalledTimes(0);
            expect(wrapper.vm.reset.mock.calls.length).toBe(0);
            expect(wrapper.vm.sentEmail).toBeFalsy();
            expect(wrapper.vm.isPush).toBeFalsy();
            expect(wrapper.vm.loading).toBeFalsy();
            expect(wrapper.vm.errors).toMatchObject({});
        });
        test('succeed to send inquiry with confirmation', async () => {
            window.axios.post.mockResolvedValue({});
            window.confirm = jest.fn(() => true);
            wrapper.vm.params = {name: 'wadakatu', email: 'wadakatu@test.com', opinion: 'successful'};
            wrapper.vm.reset = jest.fn();
            await wrapper.vm.postInquiry();
            wrapper.vm.$nextTick(() => {
                expect(window.axios.post).toHaveBeenCalled();
                expect(window.axios.post).toHaveBeenCalledWith('/api/bizinq/send', {
                    name: 'wadakatu',
                    email: 'wadakatu@test.com',
                    opinion: 'successful'
                });
                expect(wrapper.vm.reset.mock.calls.length).toBe(1);
                expect(wrapper.vm.sentEmail).toBeTruthy();
                expect(wrapper.vm.isPush).toBeTruthy();
                expect(wrapper.vm.loading).toBeFalsy();
                expect(wrapper.vm.errors).toMatchObject({});
            });
        });
        test('fail to send inquiry with confirmation', async () => {
            window.axios.post.mockRejectedValue({response: {data: {errors: {name: ['name is required.']}}}});
            window.confirm = jest.fn(() => true);
            wrapper.vm.params = {name: 'wadakatu', email: 'wadakatu@test.com', opinion: 'failed'};
            wrapper.vm.reset = jest.fn();
            await wrapper.vm.postInquiry();
            wrapper.vm.$nextTick(() => {
                expect(window.axios.post).toHaveBeenCalled();
                expect(window.axios.post).toHaveBeenCalledWith('/api/bizinq/send', {
                    name: 'wadakatu',
                    email: 'wadakatu@test.com',
                    opinion: 'failed'
                });
                expect(wrapper.vm.reset.mock.calls.length).toBe(0);
                expect(wrapper.vm.sentEmail).toBeFalsy();
                expect(wrapper.vm.isPush).toBeFalsy();
                expect(wrapper.vm.loading).toBeFalsy();
                expect(wrapper.vm.errors).toMatchObject({name: 'name is required.'})
            });
        });
    });

    describe('Testing reset method', () => {
        test('reset all data to default', () => {
            wrapper.vm.errors = {name: 'reset'};
            wrapper.vm.params = {name: 'wadakatu', email: 'wadakatu@test.com', opinion: 'reset'};
            wrapper.vm.isPush = true;
            wrapper.vm.sentEmail = true;
            wrapper.vm.loading = true;
            wrapper.vm.csrf = '123';
            wrapper.vm.reset();
            expect(wrapper.vm.errors).toMatchObject({});
            expect(wrapper.vm.params).toMatchObject({name: '', email: '', opinion: ''});
            expect(wrapper.vm.isPush).toBeFalsy();
            expect(wrapper.vm.sentEmail).toBeFalsy();
            expect(wrapper.vm.loading).toBeFalsy();
            expect(wrapper.vm.csrf).toBe(csrfToken);
        });
    });
});

describe('Testing mounted lifecycle', () => {
    test('reset method called', () => {
        const resetSpy = jest.spyOn(Component.methods, 'reset');
        wrapper = shallowMount(Component);
        expect(resetSpy).toHaveBeenCalled();
    });
});

describe('Testing v-model', () => {
    test('all of input are empty string when mounted ', () => {
        expect(wrapper.vm.params.name).toBe('');
        expect(wrapper.vm.params.email).toBe('');
        expect(wrapper.vm.params.opinion).toBe('');
        expect(wrapper.find('input[name="_token"]').element.value).toBe(csrfToken);
    });
    test('can reflect user input to params data', () => {
        wrapper.find('input[name="name"]').setValue('wadakatu');
        wrapper.find('input[name="email"]').setValue('wadakatu@test.com');
        wrapper.find('textarea[name="opinion"]').setValue('hello, world.');
        expect(wrapper.vm.params.name).toBe('wadakatu');
        expect(wrapper.vm.params.email).toBe('wadakatu@test.com');
        expect(wrapper.vm.params.opinion).toBe('hello, world.');
        expect(wrapper.find('input[name="_token"]').element.value).toBe(csrfToken);
    });
});

describe('Testing v-if', () => {
    describe('Testing sending message', () => {
        test('if loading is false, sending message does not exist', () => {
            expect(wrapper.vm.loading).toBeFalsy();
            expect(wrapper.find('div.loading').exists()).toBeFalsy();
        });
        test('if loading is true, sending message exists', () => {
            wrapper.vm.loading = true;
            wrapper.vm.$nextTick(() => {
                expect(wrapper.vm.loading).toBeTruthy();
                expect(wrapper.find('div.loading').exists()).toBeTruthy();
            });
        });
    });
    describe('Testing success message after sending inquiry', () => {
        test('if sentEmail is false, success message does not exist', () => {
            expect(wrapper.vm.sentEmail).toBeFalsy();
            expect(wrapper.find('div.alert-success').exists()).toBeFalsy();
        });
        test('if sentEmail is true, success message exists', () => {
            wrapper.vm.sentEmail = true;
            wrapper.vm.$nextTick(() => {
                expect(wrapper.vm.sentEmail).toBeTruthy();
                expect(wrapper.find('div.alert-success').exists()).toBeTruthy();
            });
        });
    });
});

describe('Testing v-html', () => {
    test('if validation error is none, error text does not exist', () => {
        expect(wrapper.vm.errors).toMatchObject({});
        const errorDivs = wrapper.findAll('div.error_text');
        expect(errorDivs.length).toBe(3);
        expect(errorDivs.at(0).html()).toBe('<div class="error_text"></div>');
        expect(errorDivs.at(1).html()).toBe('<div class="error_text"></div>');
        expect(errorDivs.at(2).html()).toBe('<div class="error_text"></div>');
    });
    test('if validation error occurs, error text exists', () => {
        wrapper.vm.errors = {
            name: 'name is required.',
            email: 'email should be valid format.',
            opinion: 'opinion is empty.'
        };
        wrapper.vm.$nextTick(() => {
            const errorDivs = wrapper.findAll('div.error_text');
            expect(errorDivs.length).toBe(3);
            expect(errorDivs.at(0).html()).toBe('<div class="error_text">name is required.</div>');
            expect(errorDivs.at(1).html()).toBe('<div class="error_text">email should be valid format.</div>');
            expect(errorDivs.at(2).html()).toBe('<div class="error_text">opinion is empty.</div>');
        });
    });
});

describe('Testing v-bind', () => {
    describe('Testing submit button', () => {
        test('disabled attribute is undefined when isPush is false', () => {
            wrapper.vm.isPush = false;
            wrapper.vm.$nextTick(() => {
                expect(wrapper.find('input[type="submit"]').attributes().disabled).toBeUndefined();
            });
        });
        test('disabled attribute is defined when isPush is true', () => {
            wrapper.vm.isPush = true;
            wrapper.vm.$nextTick(() => {
                expect(wrapper.find('input[type="submit"]').attributes().disabled).toBeDefined();
            });
        });
    });
});

describe('Testing @ event', () => {
    describe('Testing submit event', () => {
        test('submit is working when isPush is false', () => {
            wrapper.vm.isPush = false;
            wrapper.vm.postInquiry = jest.fn();
            wrapper.vm.$nextTick(() => {
                wrapper.find('input[type="submit"]').trigger('submit.prevent');
                expect(wrapper.vm.postInquiry.mock.calls.length).toBe(1);
            });
        });
        test('submit is not working when isPush is true', () => {
            wrapper.vm.isPush = true;
            wrapper.vm.postInquiry = jest.fn();
            wrapper.vm.$nextTick(() => {
                wrapper.find('input[type="submit"]').trigger('submit.prevent');
                expect(wrapper.vm.postInquiry.mock.calls.length).toBe(0);
            });
        });
    });
    describe('Testing home button', () => {
        test('never click and never move to top page', () => {
            const routerPush = jest.fn();
            wrapper = shallowMount(Component, {
                mocks: {
                    $router: {
                        push: routerPush
                    },
                }
            });
            expect(routerPush.mock.calls.length).toBe(0);
        });
        test('click once and move to top page', () => {
            const routerPush = jest.fn();
            wrapper = shallowMount(Component, {
                mocks: {
                    $router: {
                        push: routerPush
                    }
                }
            });
            wrapper.find('input[type="button"].mov_home').trigger('click');
            expect(routerPush.mock.calls.length).toBe(1);
            expect(routerPush).toHaveBeenCalledWith('/');
        });
    });
});

describe('Testing snapshot', () => {
    test('default', () => {
        expect(wrapper.element).toMatchSnapshot();
    });
    test('succeed to send inquiry', async () => {
        window.axios.post.mockResolvedValue({});
        window.confirm = jest.fn(() => true);
        wrapper.vm.params = {name: 'wadakatu', email: 'wadakatu@test.com', opinion: 'successful'};
        wrapper.vm.reset = jest.fn(() => wrapper.vm.isPush = false);
        await wrapper.find('input[type="submit"]').trigger('submit.prevent');
        wrapper.vm.$nextTick(() => {
            expect(wrapper.element).toMatchSnapshot();
        });
    });
    test('fail to send inquiry with validation error', async () => {
        window.axios.post.mockRejectedValue({
            response: {
                data: {
                    errors: {
                        name: ['name is required.'],
                        email: ['email should be valid format.'],
                        opinion: ['opinion should be string.']
                    }
                }
            }
        });
        window.confirm = jest.fn(() => true);
        wrapper.vm.params = {name: null, email: 'email.com', opinion: 12345};
        await wrapper.find('input[type="submit"]').trigger('submit.prevent');
        await wrapper.vm.$nextTick();
        expect(wrapper.element).toMatchSnapshot();
    });
});
