import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import ChatList from '../../components/ChatList.vue';

describe('ChatList', () => {
  it('renders properly with no chats', () => {
    const wrapper = mount(ChatList, {
      props: {
        chats: []
      }
    });
    expect(wrapper.find('h4').text()).toBe('Chatszobák');
    expect(wrapper.find('.no-chats').text()).toBe('Nincsenek aktív chatjeid.');
  });

  it('renders list of chats', () => {
    const chats = [
      { id: 1, name: 'Chat 1', lastMessage: 'Hello', avatar: 'avatar1.png' },
      { id: 2, name: 'Chat 2', lastMessage: 'Szia', avatar: 'avatar2.png' },
    ];
    const wrapper = mount(ChatList, {
      props: {
        chats: chats
      }
    });
    expect(wrapper.findAll('.chat-item').length).toBe(2);
    expect(wrapper.find('.chat-item .chat-name').text()).toBe('Chat 1');
    expect(wrapper.find('.chat-item .last-message').text()).toBe('Hello');
  });
});