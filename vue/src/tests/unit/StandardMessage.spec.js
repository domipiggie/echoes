import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import StandardMessage from '../../components/chat/messages/StandardMessage.vue';
import { API_CONFIG } from '../../config/api';
import { useMessageStore } from '../../store/MessageStore';

vi.mock('../../store/MessageStore', () => ({
  useMessageStore: vi.fn(() => ({
    getMessageById: vi.fn(),
  }))
}));

describe('StandardMessage', () => {
  const mockMessage = {
    getUser: () => ({
      getUserID: () => 'user-123',
      getUserName: () => 'TestUser',
      getProfilePicture: () => '/avatar.png'
    }),
    getContent: () => 'Test message content',
    getType: () => 'text',
    getReplyTo: () => null,
    isRevoked: false,
    getMessageID: () => 'msg-456'
  };

  const factory = (props = {}) => {
    return mount(StandardMessage, {
      props: {
        message: mockMessage,
        index: 0,
        messages: [mockMessage],
        userID: 'user-123',
        currentChannelName: 'general',
        ...props
      }
    });
  };

  it('displays user avatar for first consecutive message', () => {
    const wrapper = factory();
    expect(wrapper.find('.message-avatar').exists()).toBe(true);
  });

  it('hides avatar for consecutive messages from same user', () => {
    const wrapper = factory({ 
      index: 1,
      messages: [mockMessage, mockMessage]
    });
    expect(wrapper.find('.message-avatar').exists()).toBe(false);
  });

  it('displays correct message content based on type', async () => {
    const wrapper = factory({
      message: {
        ...mockMessage,
        getType: () => 'gif',
        getContent: () => 'http://example.gif'
      }
    });
    expect(wrapper.find('.message-gif').exists()).toBe(true);
  });
  
  it('shows fallback text for missing replied messages', async () => {
    const store = useMessageStore();
    store.getMessageById.mockImplementation(() => null);

    const wrapper = factory({
      message: {
        ...mockMessage,
        getReplyTo: () => 'msg-999'
      }
    });
    
    await nextTick();
    expect(wrapper.find('.reply-indicator .reply-text').text()).toContain('Üzenet betöltése sikertelen');
  });

  it('emits events for message actions', async () => {
    const wrapper = factory();
    const buttons = wrapper.findAll('.hover-action-btn');
    
    await buttons[0].trigger('click');
    expect(wrapper.emitted('start-editing')).toBeTruthy();
    
    await buttons[1].trigger('click');
    expect(wrapper.emitted('start-reply')).toBeTruthy();
    
    await buttons[2].trigger('click');
    expect(wrapper.emitted('delete-message')).toEqual([['msg-456']]);
  });

  it('shows different actions for other users messages', async () => {
    const wrapper = factory({ userID: 'different-user' });
    expect(wrapper.findAll('.hover-action-btn').length).toBe(1);
    expect(wrapper.find('[aria-label="Edit"]').exists()).toBe(false);
  });

  it('displays sender name in group chat', () => {
    const wrapper = factory({ currentChannelName: 'group-chat' });
    expect(wrapper.find('.sender-name').text()).toBe('TestUser');
  });
});