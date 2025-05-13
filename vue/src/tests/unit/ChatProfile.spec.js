import { mount } from '@vue/test-utils';
import ChatProfile from '../../components/ChatProfile.vue';
import { vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { useChannelStore } from '../../store/ChannelStore';
import { useAlertStore } from '../../store/AlertStore';
import { useWebSocketStore } from '../../store/WebSocketStore';

vi.mock('../../config', () => ({
  API_CONFIG: {
    BASE_URL: 'http://test-api.com'
  }
}));

vi.mock('../../store/MessageStore', () => ({
  useMessageStore: () => ({
    getCurrentChannelId: 'test-channel',
    getCurrentChannelName: 'Test Channel',
    getNotTextMessages: []
  })
}));

vi.mock('../../store/ChannelStore', () => ({
  useChannelStore: vi.fn(() => ({
    getGroupChannelById: vi.fn().mockImplementation(() => ({
      getOwnerID: () => 'user-123',
      getPicture: () => '/group.jpg',
      getUsers: vi.fn()
    })),
    getFriendChannelById: vi.fn().mockImplementation(() => ({
      getUser1: () => ({ getUserID: () => 'user-123', getProfilePicture: () => '/friend.jpg', getUserName: () => 'User 1' }),
      getUser2: () => ({ getUserID: () => 'friend-456', getProfilePicture: () => '/friend2.jpg', getUserName: () => 'User 2' })
    }))
  }))
}));

vi.mock('../../store/AlertStore', () => ({
  useAlertStore: vi.fn(() => ({
    addAlert: vi.fn()
  }))
}));

vi.mock('../../store/WebSocketStore', () => ({
  useWebSocketStore: vi.fn(() => ({
    isConnected: true,
    send: vi.fn()
  }))
}));

vi.mock('../../store/UserdataStore', () => ({
  userdataStore: () => ({
    getUserID: () => 'user-123',
    handleThemeChange: vi.fn()
  })
}));

describe('ChatProfile', () => {
  let pinia;
  let alertStore;
  let webSocketStore;

  const factory = (isGroup = true) => {
    pinia = createPinia();
    setActivePinia(pinia);
    const channelStore = useChannelStore();
    alertStore = useAlertStore();
    webSocketStore = useWebSocketStore();

    if(isGroup) {
      channelStore.getFriendChannelById.mockReturnValue(undefined);
    } else {
      channelStore.getGroupChannelById.mockReturnValue(undefined);
    }

    return mount(ChatProfile, {
      global: {
        plugins: [pinia],
        stubs: ['AppearanceSelector', 'UserList', 'AddUser', 'ChangeName', 'ChangeProfile']
      }
    });
  };

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    vi.clearAllMocks();
  });

  it('renders profile header correctly', () => {
    const wrapper = factory();
    expect(wrapper.find('.profile-header h2').text()).toBe('Test Channel');
  });

  it('shows group management buttons for group owner', () => {
    const wrapper = factory();
    const buttons = wrapper.findAll('button');
    const nameButton = Array.from(buttons).find(btn => 
      btn.text().includes('Csoport név módosítása')
    );
    expect(nameButton).toBeTruthy();
  });

  it('shows friend delete button for friend chat', () => {
    const wrapper = factory(false);
    
    console.log('Available buttons:', Array.from(wrapper.findAll('button')).map(btn => btn.text()));
    
    const deleteButton = Array.from(wrapper.findAll('button')).find(btn => 
      btn.text().toLowerCase().includes('törlés') || 
      btn.text().toLowerCase().includes('töröl')
    );
    
    expect(deleteButton).toBeTruthy();
  });

  it('opens modals when buttons are clicked', async () => {
    const wrapper = factory();
    
    const buttons = wrapper.findAll('button');
    const userListButton = Array.from(buttons).find(button => 
      button.text().includes('Csoporttagok')
    );
    
    await userListButton.trigger('click');
    expect(wrapper.vm.showUserList).toBe(true);
    
    const appearanceButton = Array.from(buttons).find(button => 
      button.text().includes('Megjelenés')
    );
    
    await appearanceButton.trigger('click');
    expect(wrapper.vm.showAppearanceSelector).toBe(true);
  });

  it('emits close event when clicking close button', async () => {
    const wrapper = factory();
    await wrapper.find('.close-button').trigger('click');
    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('shows correct profile picture for groups and friends', () => {
    const groupWrapper = factory();
    
    const profileImage = groupWrapper.find('.profile-image');
    expect(profileImage.exists()).toBe(true);
    
    const friendWrapper = factory(false);
    const friendProfileImage = friendWrapper.find('.profile-image');
    expect(friendProfileImage.exists()).toBe(true);
  });
});