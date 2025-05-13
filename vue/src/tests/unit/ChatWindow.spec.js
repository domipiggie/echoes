import { mount } from '@vue/test-utils';
import { vi } from 'vitest';
import ChatWindow from '../../components/ChatWindow.vue';

vi.mock('../../components/chat/ChatHeader.vue', () => ({
  default: {
    name: 'ChatHeader',
    template: '<div class="mock-chat-header"></div>',
    props: ['currentChannelName', 'isMobile']
  }
}));

vi.mock('../../components/chat/MessageList.vue', () => ({
  default: {
    name: 'MessageList',
    template: '<div class="mock-message-list"></div>',
    props: ['messages', 'currentTheme', 'userID', 'currentChannelName']
  }
}));

vi.mock('../../components/chat/MessageInput.vue', () => ({
  default: {
    name: 'MessageInput',
    template: '<div class="mock-message-input"></div>',
    props: ['replyingTo', 'editingMessage']
  }
}));

vi.mock('../../components/ChatProfile.vue', () => ({
  default: {
    name: 'ChatProfile',
    template: '<div class="mock-chat-profile"></div>',
    props: ['currentChat']
  }
}));

const mockWebSocketSend = vi.fn();

vi.mock('../../store/UserdataStore', () => ({
  userdataStore: vi.fn(() => ({
    getUserID: () => 'test-user-123',
    getCurrentTheme: () => 'messenger'
  }))
}));

vi.mock('../../store/MessageStore', () => ({
  useMessageStore: vi.fn(() => ({
    getMessages: [
      {
        getMessageID: () => 'msg-1',
        getUser: () => ({
          getUserID: () => 'test-user-123',
          getUserName: () => 'Test User'
        }),
        getContent: () => 'Test message content'
      }
    ],
    getCurrentChannelName: 'Test Channel',
    getCurrentChannel: { id: 'channel-1', name: 'Test Channel' }
  }))
}));

vi.mock('../../store/WebSocketStore', () => ({
  useWebSocketStore: vi.fn(() => ({
    isConnected: true,
    send: mockWebSocketSend
  }))
}));

describe('ChatWindow', () => {
  let wrapper;
  
  beforeEach(() => {
    vi.clearAllMocks();
    
    document.querySelector = vi.fn().mockReturnValue({
      focus: vi.fn()
    });
    
    global.innerWidth = 1024;
    global.addEventListener = vi.fn();
    
    wrapper = mount(ChatWindow);
  });
  
  it('renders the component with all child components', () => {
    expect(wrapper.find('.chat-window').exists()).toBe(true);
    expect(wrapper.find('.mock-chat-header').exists()).toBe(true);
    expect(wrapper.find('.mock-message-list').exists()).toBe(true);
    expect(wrapper.find('.mock-message-input').exists()).toBe(true);
  });
  
  it('checks screen size on mount', () => {
    expect(wrapper.vm.isMobile).toBe(false);
    
    global.innerWidth = 500;
    wrapper.vm.checkScreenSize();
    expect(wrapper.vm.isMobile).toBe(true);
  });
  
  it('emits go-back event from ChatHeader', async () => {
    await wrapper.findComponent({ name: 'ChatHeader' }).vm.$emit('go-back');
    expect(wrapper.emitted('go-back')).toBeTruthy();
  });
  
  it('emits send-message event', async () => {
    const messageData = { text: 'Hello world', type: 'text' };
    await wrapper.findComponent({ name: 'MessageInput' }).vm.$emit('send-message', messageData);
    expect(wrapper.emitted('send-message')).toBeTruthy();
    expect(wrapper.emitted('send-message')[0][0]).toEqual(messageData);
  });
  
  it('emits send-file event', async () => {
    const fileData = { file: new File([''], 'test.jpg'), type: 'image' };
    await wrapper.findComponent({ name: 'MessageInput' }).vm.$emit('send-file', fileData);
    expect(wrapper.emitted('send-file')).toBeTruthy();
    expect(wrapper.emitted('send-file')[0][0]).toEqual(fileData);
  });
  
  it('handles reply functionality', async () => {
    const message = {
      getMessageID: () => 'msg-1'
    };
    
    await wrapper.findComponent({ name: 'MessageList' }).vm.$emit('start-reply', message);
    expect(wrapper.vm.replyingTo).toEqual({ id: 'msg-1' });
    
    await wrapper.findComponent({ name: 'MessageInput' }).vm.$emit('cancel-reply');
    expect(wrapper.vm.replyingTo).toBeNull();
  });
  
  it('handles message editing', async () => {
    const message = {
      getMessageID: () => 'msg-1',
      getUser: () => ({ getUserID: () => 'test-user-123' }),
      getContent: () => 'Original message'
    };
    
    await wrapper.findComponent({ name: 'MessageList' }).vm.$emit('start-editing', message);
    expect(wrapper.vm.editingMessage).toEqual({
      id: 'msg-1',
      text: 'Original message'
    });
    
    await wrapper.findComponent({ name: 'MessageInput' }).vm.$emit('cancel-editing');
    expect(wrapper.vm.editingMessage).toBeNull();
  });
  
  it('saves edited message via websocket', async () => {
    wrapper.vm.editingMessage = {
      id: 'msg-1',
      text: 'Original message'
    };
    
    await wrapper.findComponent({ name: 'MessageInput' }).vm.$emit('save-edit', 'Edited message');
    
    expect(mockWebSocketSend).toHaveBeenCalledWith({
      type: 'chatmessage_edit',
      messageId: 'msg-1',
      content: 'Edited message'
    });
    
    expect(wrapper.vm.editingMessage).toBeNull();
  });
  
  it('toggles profile visibility', async () => {
    expect(wrapper.vm.showProfile).toBe(false);
    
    await wrapper.findComponent({ name: 'ChatHeader' }).vm.$emit('toggle-profile', true);
    expect(wrapper.vm.showProfile).toBe(true);
    
    await wrapper.findComponent({ name: 'ChatProfile' }).vm.$emit('close');
    expect(wrapper.vm.showProfile).toBe(false);
  });
  
  it('confirms before deleting a message', async () => {
    global.confirm = vi.fn().mockReturnValue(true);
    
    await wrapper.findComponent({ name: 'MessageList' }).vm.$emit('delete-message', 'msg-1');
    
    expect(global.confirm).toHaveBeenCalledWith('Biztos vissza akarja vonni ezt az üzenetet?');
    expect(wrapper.emitted('delete-message')).toBeTruthy();
    expect(wrapper.emitted('delete-message')[0][0]).toBe('msg-1');
    
    global.confirm = vi.fn().mockReturnValue(false);
    
    await wrapper.findComponent({ name: 'MessageList' }).vm.$emit('delete-message', 'msg-2');
    
    expect(wrapper.emitted('delete-message').length).toBe(1);
  });
  
  it('scrolls to bottom when messages change', async () => {
    wrapper.vm.messagesContainer = {
      scrollTop: 0,
      scrollHeight: 1000
    };
    
    await wrapper.vm.scrollToBottom();
    
    expect(wrapper.vm.messagesContainer.scrollTop).toBe(1000);
  });
  
  it('updates isMobile when window is resized', async () => {
    global.innerWidth = 500;
    wrapper.vm.checkScreenSize();
    expect(wrapper.vm.isMobile).toBe(true);
    
    global.innerWidth = 1024;
    wrapper.vm.checkScreenSize();
    expect(wrapper.vm.isMobile).toBe(false);
  });
});