import {shallowMount} from '@vue/test-utils';
import Component from '@/CardComponent2.vue';

describe('Testing props', () => {
    test('can set title and message properly', () => {
        const wrapper = shallowMount(Component, {
            propsData: {title: 'test_title', msg: 'test_msg'},
        });
        expect(wrapper.find('div.items.head p').html()).toBe('<p>test_title</p>');
        expect(wrapper.find('div.items.cart').html()).toBe("<div class=\"items cart\">\n  test_msg\n</div>");
    });
});

describe('Testing computed method', () => {
    test('cssVars method works as I expected', () => {
        const wrapper = shallowMount(Component, {
            propsData: {dataImage: '/images/test1.jpeg'},
        });
        expect(wrapper.vm.cssVars).toMatchObject({'background-image': 'url(/images/test1.jpeg)'})
    });
});

describe('Testing v-bind', () => {
    test('can apply v-bind:style properly', () => {
        const wrapper = shallowMount(Component, {
            propsData: {dataImage: '/images/test2.jpeg'},
        });
        expect(wrapper.find('div.card_container').attributes().style).toBe('background-image: url(/images/test2.jpeg);');
    });
});

describe('Testing snapshot', () => {
    test('default', () => {
        const wrapper = shallowMount(Component, {
            propsData: {title: 'snapshot_title', msg: 'snapshot_msg', dataImage: '/images/snapshot.jpeg'},
        });
        expect(wrapper.element).toMatchSnapshot();
    });
});
