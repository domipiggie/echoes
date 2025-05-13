import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import MessageInput from '../../components/chat/MessageInput.vue';
import GifPicker from '../../components/GifPicker.vue';

describe('MessageInput', () => {
  const factory = (props = {}) => {
    return mount(MessageInput, {
      props: {
        replyingTo: null,
        editingMessage: null,
        ...props
      }
    });
  };

  it('emits send-message event when sending text message', async () => {
    const wrapper = factory();
    await wrapper.find('input[type="text"]').setValue('Test message');
    await wrapper.find('.send-button').trigger('click');
    
    expect(wrapper.emitted('send-message')).toBeTruthy();
    expect(wrapper.emitted('send-message')[0][0]).toMatchObject({
      text: 'Test message',
      type: 'text'
    });
  });

  it('handles enter key for message submission', async () => {
    const wrapper = factory();
    const input = wrapper.find('input[type="text"]');
    await input.setValue('Enter test');
    await input.trigger('keyup.enter');
    
    expect(wrapper.emitted('send-message')).toBeTruthy();
  });

  it('includes reply metadata when replying', async () => {
    const wrapper = factory({
      replyingTo: { id: 123 }
    });
    
    await wrapper.find('input[type="text"]').setValue('Reply test');
    await wrapper.find('.send-button').trigger('click');
    
    expect(wrapper.emitted('send-message')[0][0].replyTo).toBe(123);
  });

  it('handles message editing flow', async () => {
    const wrapper = factory({
      editingMessage: { id: 456, text: 'Original message' }
    });
    
    const input = wrapper.find('input[type="text"]');
    expect(input.element.value).toBe('Original message');
    
    await input.setValue('Edited message');
    await wrapper.find('.send-button').trigger('click');
    
    expect(wrapper.emitted('save-edit')).toBeTruthy();
    expect(wrapper.emitted('save-edit')[0][0]).toBe('Edited message');
  });

  it('handles file upload event', async () => {
    const wrapper = factory();
    const file = new File(['test'], 'test.png', { type: 'image/png' });
    const input = wrapper.find('input[type="file"]');
    
    Object.defineProperty(input.element, 'files', { value: [file] });
    await input.trigger('change');
    
    expect(wrapper.emitted('send-file')).toBeTruthy();
    expect(wrapper.emitted('send-file')[0][0].file).toEqual(file);
  });

  it('handles GIF selection', async () => {
    const wrapper = factory();
    await wrapper.find('.gif-button').trigger('click');
    
    const gifPicker = wrapper.findComponent(GifPicker);
    gifPicker.vm.$emit('select-gif', 'https://example.gif');
    
    expect(wrapper.emitted('send-message')).toBeTruthy();
    expect(wrapper.emitted('send-message')[0][0]).toMatchObject({
      text: 'https://example.gif',
      type: 'gif'
    });
  });

  it('cancels editing on escape key', async () => {
    const wrapper = factory({
      editingMessage: { id: 456, text: 'Original message' }
    });
    
    await wrapper.find('input[type="text"]').trigger('keyup.esc');
    expect(wrapper.emitted('cancel-editing')).toBeTruthy();
  });
});