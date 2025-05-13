import { mount } from '@vue/test-utils';
import { vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import NewChatDialog from '../../components/NewChatDialog.vue';
import { userService } from '../../services/userService';

vi.mock('../../store/UserdataStore', () => ({
  userdataStore: vi.fn(() => ({
    getUserID: () => 'test-user-123'
  }))
}));

vi.mock('../../store/WebSocketStore', () => ({
  useWebSocketStore: vi.fn(() => ({
    getIsConnected: true,
    send: vi.fn()
  }))
}));

vi.mock('../../store/FriendshipStore', () => ({
  useFriendshipStore: vi.fn(() => ({
  }))
}));

vi.mock('../../services/userService', () => ({
  userService: {
    getUserByUsername: vi.fn()
  }
}));

describe('NewChatDialog', () => {
  let pinia;
  
  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    vi.clearAllMocks();
  });

  const factory = () => {
    return mount(NewChatDialog, {
      global: {
        plugins: [pinia],
        stubs: {
          'svg': true
        }
      }
    });
  };

  it('renders correctly with initial state', () => {
    const wrapper = factory();
    expect(wrapper.find('h2').text()).toBe('Új beszélgetés');
    expect(wrapper.find('input').attributes('placeholder')).toBe('Add meg a felhasználónevet');
    expect(wrapper.find('.create-button').text()).toContain('Barátkérelem küldése');
  });

  it('emits close event when clicking overlay', async () => {
    const wrapper = factory();
    await wrapper.find('.new-chat-overlay').trigger('click.self');
    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('emits close event when clicking cancel button', async () => {
    const wrapper = factory();
    await wrapper.find('.cancel-button').trigger('click');
    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('shows error message when username is empty', async () => {
    const wrapper = factory();
    await wrapper.find('.create-button').trigger('click');
    expect(wrapper.vm.errorMessage).toBe('Kérlek adj meg egy felhasználónevet!');
  });

  it('shows error when user not found', async () => {
    userService.getUserByUsername.mockResolvedValue({
      success: false,
      data: null
    });
    
    const wrapper = factory();
    await wrapper.find('input').setValue('nonexistentuser');
    await wrapper.find('.create-button').trigger('click');
    
    await wrapper.vm.$nextTick();
    expect(wrapper.vm.errorMessage).toBe('Nem található felhasználó ezzel a névvel.');
  });

  it('sends friend request when user is found', async () => {
    userService.getUserByUsername.mockResolvedValue({
      success: true,
      data: {
        userID: 'recipient-456'
      }
    });
    
    const wrapper = factory();
    await wrapper.find('input').setValue('validuser');
    await wrapper.find('.create-button').trigger('click');
    
    await wrapper.vm.$nextTick();
    
    expect(wrapper.vm.webSocketStore.send).toHaveBeenCalledWith({
      type: 'friend_add',
      recipient_id: 'recipient-456'
    });
    
    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('handles API errors gracefully', async () => {
    userService.getUserByUsername.mockRejectedValue({
      response: {
        status: 400,
        data: {
          message: 'API error message'
        }
      }
    });
    
    const wrapper = factory();
    await wrapper.find('input').setValue('erroruser');
    await wrapper.find('.create-button').trigger('click');
    
    await wrapper.vm.$nextTick();
    expect(wrapper.vm.errorMessage).toBe('API error message');
  });

  it('handles generic errors', async () => {
    userService.getUserByUsername.mockRejectedValue(new Error('Network error'));
    
    const wrapper = factory();
    await wrapper.find('input').setValue('erroruser');
    await wrapper.find('.create-button').trigger('click');
    
    await wrapper.vm.$nextTick();
    expect(wrapper.vm.errorMessage).toBe('Hiba történt a barátkérelem küldése közben.');
  });

  it('emits open-group-dialog event when clicking group button', async () => {
    const wrapper = factory();
    await wrapper.find('.group-button').trigger('click');
    
    expect(wrapper.emitted('open-group-dialog')).toBeTruthy();
    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('creates chat when pressing enter in input field', async () => {
    userService.getUserByUsername.mockResolvedValue({
      success: true,
      data: {
        userID: 'recipient-456'
      }
    });
    
    const wrapper = factory();
    await wrapper.find('input').setValue('validuser');
    await wrapper.find('input').trigger('keyup.enter');
    
    await wrapper.vm.$nextTick();
    
    expect(wrapper.vm.webSocketStore.send).toHaveBeenCalledWith({
      type: 'friend_add',
      recipient_id: 'recipient-456'
    });
  });
});