import { mount } from '@vue/test-utils';
import Chat from '../../components/Chat.vue';
import { vi } from 'vitest';
import { nextTick } from 'vue';
import { createPinia, setActivePinia } from 'pinia';

vi.mock('../../composables/websocketFunctions.js', () => ({
  onGroupChange: vi.fn(),
  onRemovedFromGroup: vi.fn(),
  handleOnFriendChange: vi.fn(),
  handleFriendRemove: vi.fn(),
  handleNewMessage: vi.fn(),
  handleDeleteMessage: vi.fn(),
  handleOnFriendAdded: vi.fn(),
  handleGroupCreated: vi.fn(),
  handleMessageUpdate: vi.fn(),
  handleGroupDeleted: vi.fn(),
  handleFriendRemoved: vi.fn()
}));

const mockConnect = vi.fn();
const mockRegisterHandler = vi.fn();
const mockSend = vi.fn();
const mockDisconnect = vi.fn();
const mockUnregisterHandler = vi.fn();
const mockGetCurrentChannelId = vi.fn().mockReturnValue('test-channel');

vi.mock('../../store/UserdataStore', () => ({
  userdataStore: vi.fn(() => ({
    getUserID: () => 'test-user-id',
    getAccessToken: () => 'mock-token',
    fetchUserInfo: vi.fn().mockResolvedValue({})
  }))
}));

vi.mock('../../store/WebSocketStore', () => ({
  useWebSocketStore: vi.fn(() => ({
    connect: mockConnect,
    disconnect: mockDisconnect,
    registerHandler: mockRegisterHandler,
    unregisterHandler: mockUnregisterHandler,
    send: mockSend,
    getIsConnected: true
  }))
}));

vi.mock('../../store/MessageStore', () => ({
  useMessageStore: vi.fn(() => ({
    setCurrentChannelId: vi.fn(),
    setCurrentChannelName: vi.fn(),
    fetchMessages: vi.fn().mockResolvedValue([]),
    getCurrentChannelId: mockGetCurrentChannelId,
    getMessages: []
  }))
}));

vi.mock('../../store/FriendshipStore', () => ({
  useFriendshipStore: vi.fn(() => ({
    fetchFriendships: vi.fn().mockResolvedValue([]),
    getFriendships: []
  }))
}));

vi.mock('../../store/ChannelStore', () => ({
  useChannelStore: vi.fn(() => ({
    fetchAllChannels: vi.fn().mockResolvedValue([]),
    getFriendChannels: []
  }))
}));

vi.mock('../../services/fileService.js', () => ({
  fileService: {
    uploadFile: vi.fn()
  }
}));

describe('Chat Component', () => {
  let wrapper;
  let pinia;

  beforeEach(async () => {
    vi.clearAllMocks();
    
    pinia = createPinia();
    setActivePinia(pinia);
    
    global.document.querySelector = vi.fn().mockReturnValue({
      scrollTop: 0,
      scrollHeight: 100
    });
    
    wrapper = mount(Chat, {
      global: {
        plugins: [pinia],
        stubs: ['Sidebar', 'ChatWindow']
      }
    });
    
    await nextTick();
    await new Promise(resolve => setTimeout(resolve, 0));
  });

  it('connects websocket on mount', async () => {
    expect(mockConnect).toHaveBeenCalled();
  });

  it('registers websocket handlers correctly', async () => {
    expect(mockRegisterHandler).toHaveBeenCalledWith('friend_add', expect.any(Function));
    expect(mockRegisterHandler).toHaveBeenCalledWith('new_message', expect.any(Function));
  });

  it('handles message sending correctly', async () => {
    mockGetCurrentChannelId.mockReturnValue('test-channel');
    
    await wrapper.vm.sendMessage({ text: 'Test message', type: 'text' });
  });

  it('toggles chat visibility on mobile', async () => {
    window.innerWidth = 500;
    wrapper.vm.checkScreenSize();
    
    const mockChat = {
      getChannelID: () => 'test',
      getName: () => 'Test Chat',
      getUser1: () => ({ getUserID: () => 'user1', getUserName: () => 'User 1' }),
      getUser2: () => ({ getUserID: () => 'user2', getUserName: () => 'User 2' })
    };
    
    await wrapper.vm.handleChatSelect(mockChat);
    expect(wrapper.vm.showChat).toBe(true);
    
    wrapper.vm.goBackToList();
    expect(wrapper.vm.showChat).toBe(false);
  });
});