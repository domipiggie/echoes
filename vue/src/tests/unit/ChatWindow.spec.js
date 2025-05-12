import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import ChatWindow from '../../components/ChatWindow.vue';
import { createPinia } from 'pinia';

vi.mock('../../components/chat/MessageInput.vue', () => ({ default: { template: '<div class="mock-message-input"></div>', name: 'MessageInput' }}));

describe('ChatWindow', () => {
  const pinia = createPinia();

  const mockMessages = [
    { id: 1, sender: 'user1', text: 'Hello', time: '10:00', type: 'text' },
    { id: 2, sender: 'user2', text: 'Hi there', time: '10:01', type: 'text' },
  ];

  const currentUser = { id: 'user1', name: 'Test User' };

  it('renders properly with no messages', () => {
    const wrapper = mount(ChatWindow, {
      props: {
        messages: [],
        currentUser: currentUser,
        selectedUser: { id: 'user2', name: 'Other User' }
      },
      global: {
        plugins: [pinia],
        stubs: { MessageInput: true }
      }
    });
    expect(wrapper.find('.chat-messages').exists()).toBe(true);
    expect(wrapper.find('.no-messages-placeholder').exists()).toBe(true);
  });

  it('renders messages correctly', () => {
    const wrapper = mount(ChatWindow, {
      props: {
        messages: mockMessages,
        currentUser: currentUser,
        selectedUser: { id: 'user2', name: 'Other User' }
      },
      global: {
        plugins: [pinia],
        stubs: { MessageInput: true }
      }
    });
    expect(wrapper.findAll('.message-item').length).toBe(2);
  });
});