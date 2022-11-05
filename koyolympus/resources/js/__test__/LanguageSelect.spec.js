import {shallowMount} from '@vue/test-utils';
import Component from '@/LanguageSelectComponent.vue';

describe('Testing method', () => {
    test('Testing changeLang method', () => {
        const wrapper = shallowMount(Component);
        wrapper.vm.changeLang('japanese');
        expect(wrapper.emitted('update')).not.toBeUndefined();
        expect(wrapper.emitted('update')[0][0]).toBe('japanese');
    });
});

describe('Testing click', () => {
    let wrapper;
    let selects;
    let inputs;
    beforeEach(() => {
        wrapper = shallowMount(Component);
        wrapper.vm.changeLang = jest.fn();
        inputs = wrapper.findAll('input.selectopt');
        selects = wrapper.findAll('label.option');
    });
    test('click Japanese', () => {
        inputs.at(0).trigger('click');
        expect(selects.at(0).text()).toBe('日本語');
        expect(wrapper.vm.changeLang).toHaveBeenCalled();
    });
    test('click English', () => {
        inputs.at(1).trigger('click');
        expect(selects.at(1).text()).toBe('English');
        expect(wrapper.vm.changeLang).toHaveBeenCalled();
    });
    test('click French', () => {
        inputs.at(2).trigger('click');
        expect(selects.at(2).text()).toBe('French');
        expect(wrapper.vm.changeLang).toHaveBeenCalled();
    });
    test('click Korean', () => {
        inputs.at(3).trigger('click');
        expect(selects.at(3).text()).toBe('한국말');
        expect(wrapper.vm.changeLang).toHaveBeenCalled();
    });
    test('click Chinese', () => {
        inputs.at(4).trigger('click');
        expect(selects.at(4).text()).toBe('中文');
        expect(wrapper.vm.changeLang).toHaveBeenCalled();
    });
});

describe('Testing snapshot', () => {
    test('default', () => {
        const wrapper = shallowMount(Component);
        expect(wrapper.element).toMatchSnapshot();
    });
});
