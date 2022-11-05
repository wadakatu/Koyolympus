import {shallowMount, RouterLinkStub} from '@vue/test-utils';
import Component from '@/HamburgerMenuComponent.vue';

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
    test('Testing close method', () => {
        const wrapper = shallowMount(Component, {
            stubs: {
                RouterLink: RouterLinkStub
            },
        });

        const targetElement = document.createElement('input');
        targetElement.name = 'test';
        targetElement.type = 'checkbox';
        targetElement.checked = true;

        jest.spyOn(document, 'getElementById').mockReturnValue(targetElement);

        expect(targetElement.checked).toBeTruthy();
        wrapper.vm.close();
        expect(targetElement.checked).toBeFalsy();
    });
});

describe('Testing @click event', () => {
    let wrapper;
    let mockPhoto;
    let mockClose;
    let menuContents;
    beforeEach(() => {
        mockPhoto = jest.spyOn(Component.methods, 'photo').mockImplementation(() => "mock photo");
        mockClose = jest.spyOn(Component.methods, 'close');

        wrapper = shallowMount(Component, {
            stubs: {
                RouterLink: RouterLinkStub
            },
        });
        menuContents = wrapper.findAll('div.menuContent ul li');
    });
    afterEach(() => {
        mockPhoto.mockClear();
        mockClose.mockClear();
    });
    describe('Testing close method', () => {
        test('close modal when click `About Me`', () => {
            const target = menuContents.at(0);
            target.trigger('click');
            expect(target.text()).toBe('About Me');
            expect(mockClose).toHaveBeenCalledTimes(1);
        });
        test('close modal when click `Photograph`', () => {
            const target = menuContents.at(1);
            target.trigger('click');
            expect(target.text()).toBe('Photography');
            expect(mockClose).toHaveBeenCalledTimes(1);
        });
        test('close modal when click `Biz Inquiries`', () => {
            const target = menuContents.at(2);
            target.trigger('click');
            expect(target.text()).toBe('Biz Inquiries');
            expect(mockClose).toHaveBeenCalledTimes(1);
        });
        test('close modal when click `E-Commerce`', () => {
            const target = menuContents.at(3);
            target.trigger('click');
            expect(target.text()).toBe('E-Commerce');
            expect(mockClose).toHaveBeenCalledTimes(1);
        });
        test('close modal when click `Facebook`', () => {
            const target = menuContents.at(4);
            target.trigger('click');
            expect(target.text()).toBe('Facebook');
            expect(mockClose).toHaveBeenCalledTimes(1);
        });
        test('close modal when click `GitHub`', () => {
            const target = menuContents.at(5);
            target.trigger('click');
            expect(target.text()).toBe('GitHub');
            expect(mockClose).toHaveBeenCalledTimes(1);
        });
        test('close modal when click `Instagram`', () => {
            const target = menuContents.at(6);
            target.trigger('click');
            expect(target.text()).toBe('Instagram');
            expect(mockClose).toHaveBeenCalledTimes(1);
        });
    });
    describe('Testing photo method', () => {
        test('call photo method when click `Photography`', () => {
            const target = menuContents.at(1);
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
    test('Testing About Me', () => {
        const target = links.at(0);
        expect(target.text()).toBe('About Me');
        expect(target.props().to).toMatchObject({"name": "about.me"});
    });
    test('Testing Photography', () => {
        const target = links.at(1);
        expect(target.text()).toBe('Photography');
        expect(target.props().to).toMatchObject({});
    });
    test('Testing Biz Inquiries', () => {
        const target = links.at(2);
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
