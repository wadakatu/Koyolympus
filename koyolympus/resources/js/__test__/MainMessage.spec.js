import {shallowMount} from '@vue/test-utils';
import Component from '@/MainMessageComponent.vue';

describe('Testing methods', () => {
    test('Testing photo method', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {commit: jest.fn()},
                $router: {
                    push: jest.fn().mockResolvedValue({})
                }
            }
        });
        wrapper.vm.photo();

        expect(wrapper.vm.$store.commit).toHaveBeenCalledTimes(2);
        expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('photo/setUrl', '/photo/random');
        expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('photo/setGenre', null);
        expect(wrapper.vm.$router.push).toHaveBeenCalled();
        expect(wrapper.vm.$router.push).toHaveBeenCalledWith({name: 'photo.random'});
    });
});

describe('Testing @ event', () => {
    test('called photo method when click explore button', async () => {
        const spy = jest.spyOn(Component.methods, 'photo');
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {commit: jest.fn()},
                $router: {
                    push: jest.fn().mockResolvedValue({})
                }
            }
        });

        wrapper.find('.photo_button').trigger('click');
        await wrapper.vm.$nextTick();
        expect(spy).toHaveBeenCalled();
    });
});

describe('Testing SnapShot', () => {
    test('Default', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {commit: jest.fn()},
                $router: {
                    push: jest.fn().mockResolvedValue({})
                }
            }
        });
        expect(wrapper.element).toMatchSnapshot();
    });
});
