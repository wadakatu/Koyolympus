import {shallowMount} from "@vue/test-utils";
import Component from '@/PhotoModalComponent.vue';

let wrapper;
beforeEach(() => {
    wrapper = shallowMount(Component, {
        propsData: {
            val: {
                id: "10",
                url: '/test',
            }
        },
    });
});

describe('Testing computed', () => {
    test('Testing get photoId', () => {
        expect(wrapper.vm.photoId).toBe('10');
        expect(wrapper.vm.photoId).not.toBe('11');
    });
});

describe('Testing v-bind', () => {
    test('Testing src in img tag', () => {
        expect(wrapper.find('img').attributes().src).toBe('/test');
    });
    test('Testing id in like-component', () => {
        expect(wrapper.find('like-component-stub').attributes().id).toBe('10');
    });
});

describe('Testing @event', () => {
    test('emit event fire when clicked div tag(#overlay)', () => {
        wrapper.find('#overlay').trigger('click.self');
        expect(wrapper.emitted('close')).not.toBeUndefined();
    });
    test('emit event fire when clicked div tag(#modal-content)', () => {
        wrapper.find('#modal-content').trigger('click.self');
        expect(wrapper.emitted('close')).not.toBeUndefined();
    });
    test('emit event DO NOT fire when clicked div tag(#modal-content-top)', () => {
        wrapper.find('#modal-content-top').trigger('click.self');
        expect(wrapper.emitted('close')).toBeUndefined();
    });
    test('emit event DO NOT fire when clicked div tag(#modal-content-bottom)', () => {
        wrapper.find('#modal-content-bottom').trigger('click.self');
        expect(wrapper.emitted('close')).toBeUndefined();
    });
});
