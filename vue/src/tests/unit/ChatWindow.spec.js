import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import ChatWindow from '../../components/ChatWindow.vue';

describe('ChatWindow', () => {
  it('renders properly with no messages', () => {
    const wrapper = mount(ChatWindow, {
      props: {
        messages: [],
        currentUser: 'user1'
      },
      global: {
        stubs: {
          MessageInput: true
        }
      }
    });
    expect(wrapper.find('.chat-messages').exists()).toBe(true);
    expect(wrapper.find('.no-messages').text()).toBe('Nincsenek üzenetek.');
  });

  it('renders messages correctly', () => {
    const messages = [
      { id: 1, sender: 'user1', text: 'Hello', time: '10:00' },
      { id: 2, sender: 'user2', text: 'Hi', time: '10:01' },
    ];
    const wrapper = mount(ChatWindow, {
      props: {
        messages: messages,
        currentUser: 'user1'
      },
      global: {
        stubs: {
          MessageInput: true
        }
      }
    });
    expect(wrapper.findAll('.message').length).toBe(2);
    expect(wrapper.find('.message.sent .message-text').text()).toBe('Hello');
    expect(wrapper.find('.message.received .message-text').text()).toBe('Hi');
  });
});