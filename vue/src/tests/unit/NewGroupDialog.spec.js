import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import NewGroupDialog from '../../components/NewGroupDialog.vue';
import { useFriendshipStore } from '../../store/FriendshipStore';
import { useWebSocketStore } from '../../store/WebSocketStore';
import { vi } from 'vitest';

vi.mock('../../config/api.js', () => ({
  API_CONFIG: {
    BASE_URL: 'http://test.com'
  }
}));

vi.mock('../../store/FriendshipStore', () => ({
  useFriendshipStore: vi.fn()
}));

vi.mock('../../store/WebSocketStore', () => ({
  useWebSocketStore: vi.fn()
}));

describe('NewGroupDialog', () => {
  let pinia;
  let friendshipStore;
  let webSocketStore;
  
  const mockFriendship = {
    getTargetUser: () => ({
      getUserID: () => 'user-123',
      getUserName: () => 'Test User',
      getProfilePicture: () => '/profile.jpg'
    })
  };

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    
    friendshipStore = {
      getFriendships: [],
      getAcceptedFriendships: [mockFriendship],
      fetchFriendships: vi.fn().mockResolvedValue([])
    };
    
    friendshipStore.fetchFriendships.mockClear();
    
    webSocketStore = {
      getIsConnected: true,
      send: vi.fn()
    };
    
    vi.mocked(useFriendshipStore).mockReturnValue(friendshipStore);
    vi.mocked(useWebSocketStore).mockReturnValue(webSocketStore);
  });

  const factory = () => {
    return mount(NewGroupDialog, {
      global: {
        plugins: [pinia],
        stubs: {
          'svg': true
        }
      }
    });
  };

  it('renders correctly and fetches friendships', async () => {
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    
    expect(wrapper.find('h2').text()).toBe('Csoport létrehozása');
    expect(friendshipStore.fetchFriendships).toHaveBeenCalled();
  });

  it('shows validation errors', async () => {
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    
    await wrapper.find('.friend-item').trigger('click');
    await wrapper.find('input').setValue('');
    await wrapper.vm.$nextTick();
    
    const createButton = wrapper.find('.create-button');
    if (createButton.element.disabled) {
      wrapper.vm.createGroup();
    } else {
      await createButton.trigger('click');
    }
    await wrapper.vm.$nextTick();
    expect(wrapper.find('.error-message').text()).toContain('Kérlek adj meg egy csoportnevet');

    await wrapper.find('.friend-item').trigger('click');
    await wrapper.find('input').setValue('Test Group');
    await wrapper.vm.$nextTick();
    
    if (wrapper.find('.create-button').element.disabled) {
      wrapper.vm.createGroup();
    }
    await wrapper.vm.$nextTick();
    expect(wrapper.find('.error-message').text()).toContain('Válassz ki legalább egy barátot');
  });

  it('selects and deselects friends', async () => {
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    
    await wrapper.find('.friend-item').trigger('click');
    expect(wrapper.vm.selectedFriends.length).toBe(1);
    
    await wrapper.find('.friend-item').trigger('click');
    expect(wrapper.vm.selectedFriends.length).toBe(0);
  });

  it('sends websocket message when creating group', async () => {
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    
    await wrapper.find('input').setValue('Test Group');
    
    await wrapper.find('.friend-item').trigger('click');
    
    await wrapper.find('.create-button').trigger('click');
    
    expect(webSocketStore.send).toHaveBeenCalledWith({
      type: 'group_create',
      groupName: 'Test Group',
      userIds: ['user-123']
    });
  });

  it('emits close event', async () => {
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    
    await wrapper.find('.new-group-overlay').trigger('click.self');
    expect(wrapper.emitted('close')).toBeTruthy();
    
    await wrapper.find('.close-button').trigger('click');
    expect(wrapper.emitted('close').length).toBe(2);
  });

  it('shows no friends message', async () => {
    friendshipStore.getAcceptedFriendships = [];
    
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    
    expect(wrapper.find('.no-friends-message').exists()).toBe(true);
  });
});