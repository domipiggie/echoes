import { mount } from '@vue/test-utils';
import MessageList from '../../components/chat/MessageList.vue';
import { useMessageStore } from '../../store/MessageStore';
import { vi, describe, it, expect, beforeEach, afterEach } from 'vitest';

const mockLoadMoreMessages = vi.fn().mockResolvedValue(true);
vi.mock('../../store/MessageStore', () => ({
  useMessageStore: vi.fn(() => ({
    loadMoreMessages: mockLoadMoreMessages,
    getCurrentChannelId: 'test-channel'
  }))
}));

describe('MessageList', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });
  
  afterEach(() => {
    vi.restoreAllMocks();
    vi.useRealTimers();
  });
  
  const mockMessages = [
    {
      getMessageID: () => 'msg1',
      getUser: () => ({
        getUserID: () => 'user1',
        getUserName: () => 'Test User',
        getProfilePicture: () => '/avatar.jpg'
      }),
      getContent: () => 'Test message 1',
      getType: () => 'text',
      getReplyTo: () => null,
      isRevoked: () => false
    }
  ];

  const factory = (props = {}) => {
    return mount(MessageList, {
      props: {
        messages: mockMessages,
        currentTheme: 'standard',
        userID: 'user1',
        currentChannelName: 'general',
        ...props
      }
    });
  };

  it('renders correct message component based on theme', async () => {
    const wrapper = factory({ currentTheme: 'discord' });
    expect(wrapper.findComponent({ name: 'DiscordMessage' }).exists()).toBe(true);
    
    await wrapper.setProps({ currentTheme: 'standard' });
    expect(wrapper.findComponent({ name: 'StandardMessage' }).exists()).toBe(true);
  });

  it('emits events when child components trigger them', async () => {
    const wrapper = factory();
    
    await wrapper.findComponent({ name: 'StandardMessage' }).vm.$emit('delete-message', 'msg1');
    expect(wrapper.emitted('delete-message')).toEqual([['msg1']]);
    
    await wrapper.findComponent({ name: 'StandardMessage' }).vm.$emit('start-editing', mockMessages[0]);
    expect(wrapper.emitted('start-editing')).toBeTruthy();
  });

  it('handles scroll to load more messages', async () => {
    mockLoadMoreMessages.mockClear();
    
    const wrapper = factory();
    
    const mockContainer = {
      scrollTop: 10,
      scrollHeight: 500,
      dataset: { noMoreMessages: 'false' },
      addEventListener: vi.fn(),
      removeEventListener: vi.fn()
    };
    
    wrapper.vm.messagesContainer = mockContainer;
    
    wrapper.vm.messagesContainer.scrollTop = 0;
    
    await wrapper.vm.handleScroll();
    
    vi.advanceTimersByTime(100);
    
    expect(mockLoadMoreMessages).toHaveBeenCalled();
  });
});