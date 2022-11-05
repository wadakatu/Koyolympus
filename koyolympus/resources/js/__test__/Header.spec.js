import {shallowMount, RouterLinkStub} from '@vue/test-utils';
import Component from '@/HeaderComponent.vue';

describe('Testing methods', () => {
    test('Testing photo method', async () => {
        let mocks = {
            $store: {commit: jest.fn()},
            $router: {push: jest.fn().mockResolvedValue({})},
        }
        const wrapper = shallowMount(Component, {
            mocks,
            stubs: {
                RouterLink: RouterLinkStub
            },
        });
        await wrapper.vm.photo();
        expect(wrapper.vm.$store.commit).toHaveBeenCalledTimes(2);
        expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('photo/setUrl', '/photo/random');
        expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('photo/setGenre', null);
        expect(wrapper.vm.$router.push).toHaveBeenCalled();
        expect(wrapper.vm.$router.push).toHaveBeenCalledWith({name: 'photo.random'});
    });
});

describe('Testing @click event', () => {
    describe('Testing photo method', () => {
        test('call photo method when click `Photography`', () => {
            const mockPhoto = jest.spyOn(Component.methods, 'photo').mockImplementation(() => "mock photo");
            const wrapper = shallowMount(Component, {
                stubs: {
                    RouterLink: RouterLinkStub
                },
            });
            const target = wrapper.find('nav ul li.photo a');
            target.trigger('click');
            expect(target.text()).toBe('Photography');
            expect(mockPhoto).toHaveBeenCalledTimes(1);
        });
    });
});

describe('Testing router-link', () => {
    let wrapper;
    let links;
    beforeEach(() => {
        wrapper = shallowMount(Component, {
            stubs: {
                RouterLink: RouterLinkStub
            },
        });
        links = wrapper.findAllComponents(RouterLinkStub)
    });
    test('Testing home icon', () => {
        const target = links.at(0);
        expect(target.text()).toBe('');
        expect(target.props().to).toMatchObject({"name": "main"});
    });
    test('Testing About Me', () => {
        const target = links.at(1);
        expect(target.text()).toBe('About Me');
        expect(target.props().to).toMatchObject({"name": "about.me"});
    });
    test('Testing Photography', () => {
        const target = links.at(2);
        expect(target.text()).toBe('Photography');
        expect(target.props().to).toMatchObject({});
    });
    test('Testing Biz Inquiries', () => {
        const target = links.at(3);
        expect(target.text()).toBe('Biz Inquiries');
        expect(target.props().to).toMatchObject({"name": "main.biz"});
    });
});

describe('Testing snapshot', () => {
    test('default', () => {
        const wrapper = shallowMount(Component, {
            stubs: {
                RouterLink: RouterLinkStub
            },
        });

        expect(wrapper.element).toMatchSnapshot();
    });
});
