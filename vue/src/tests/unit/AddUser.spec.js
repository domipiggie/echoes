import { mount } from '@vue/test-utils';
import AddUser from '../../components/groupModals/AddUser.vue';
import { vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';

vi.mock('../../store/UserdataStore', () => ({
  userdataStore: () => ({
    getUserID: () => 'current-user-123'
  })
}));

vi.mock('../../store/FriendshipStore', () => ({
  useFriendshipStore: vi.fn(() => ({
    getAcceptedFriendships: [
      {
        getTargetUser: () => ({
          getUserID: () => 'friend-1',
          getUserName: () => 'Friend One',
          getProfilePicture: () => null
        })
      },
      {
        getTargetUser: () => ({
          getUserID: () => 'friend-2',
          getUserName: () => 'Friend Two',
          getProfilePicture: () => '/avatar.jpg'
        })
      }
    ]
  }))
}));

vi.mock('../../store/ChannelStore', () => ({
  useChannelStore: vi.fn(() => ({
    getGroupChannelById: () => ({
      getUsers: () => [
        { getUserID: () => 'existing-user-123' }
      ]
    }),
    fetchGroupChannels: vi.fn()
  }))
}));

const mockSend = vi.fn();

vi.mock('../../store/WebSocketStore', () => ({
  useWebSocketStore: vi.fn(() => ({
    isConnected: true,
    send: mockSend
  }))
}));

vi.mock('../../store/MessageStore', () => ({
  useMessageStore: vi.fn(() => ({
    getCurrentChannelId: 'test-channel-123'
  }))
}));

describe('AddUser', () => {
  let pinia;
  
  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    mockSend.mockClear();
  });

  const factory = () => {
    return mount(AddUser, {
      global: {
        plugins: [pinia],
        stubs: {
          'svg': true
        }
      }
    });
  };

  it('sends add user request via websocket', async () => {
    const wrapper = factory();
    
    const friendItem = wrapper.find('.friend-item');
    await friendItem.trigger('click');
    
    await wrapper.find('.create-button').trigger('click');
    
    expect(mockSend).toHaveBeenCalledWith({
      type: 'group_add_user',
      channelId: 'test-channel-123',
      userIds: ['friend-1']
    });
  });

  it('renders available users not in channel', async () => {
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    
    const friendItems = wrapper.findAll('.friend-item');
    expect(friendItems).toHaveLength(2);
    expect(friendItems[0].text()).toContain('Friend One');
    expect(friendItems[1].text()).toContain('Friend Two');
  });

  it('selects and deselects users', async () => {
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    
    const firstFriend = wrapper.find('.friend-item');
    await firstFriend.trigger('click');
    expect(wrapper.vm.selectedFriends).toHaveLength(1);
    
    await firstFriend.trigger('click');
    expect(wrapper.vm.selectedFriends).toHaveLength(0);
  });

  it('emits close event after adding users', async () => {
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    
    await wrapper.find('.friend-item').trigger('click');
    await wrapper.find('.create-button').trigger('click');
    
    expect(wrapper.emitted('close')).toBeTruthy();
  });
});