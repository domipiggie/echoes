import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import MessageInput from '../../components/MessageInput.vue';

describe('MessageInput', () => {
  it('renders properly', () => {
    const wrapper = mount(MessageInput);
    expect(wrapper.find('input[type="text"]').exists()).toBe(true);
    expect(wrapper.find('button').exists()).toBe(true);
  });

  it('emits send-message event with message text when form is submitted', async () => {
    const wrapper = mount(MessageInput);
    const messageText = 'Teszt üzenet';
    await wrapper.find('input[type="text"]').setValue(messageText);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.emitted()['send-message']).toBeTruthy();
    expect(wrapper.emitted()['send-message'][0]).toEqual([messageText]);
    expect(wrapper.find('input[type="text"]').element.value).toBe('');
  });

  it('does not emit send-message event if message is empty', async () => {
    const wrapper = mount(MessageInput);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.emitted()['send-message']).toBeFalsy();
  });
});