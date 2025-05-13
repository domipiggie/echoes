import { mount } from '@vue/test-utils';
import ProfileSettingsDialog from '../../components/ProfileSettingsDialog.vue';
import { vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';

vi.mock('../../config/api.js', () => ({
  API_CONFIG: {
    BASE_URL: 'http://test-api.com'
  }
}));

vi.mock('../../services/fileService.js', () => ({
  fileService: {
    uploadUserProfilePicture: vi.fn().mockResolvedValue({
      data: {
        profilePicture: '/new-avatar.jpg'
      }
    })
  }
}));

vi.mock('../../services/userService.js', () => ({
  userService: {
    updateUser: vi.fn().mockResolvedValue({})
  }
}));

vi.mock('../../store/UserdataStore', () => ({
  userdataStore: vi.fn(() => ({
    getUserID: () => 'test-user-123',
    getUsername: () => 'Test User',
    getProfilePicture: () => '/default-avatar.jpg',
    fetchUserInfo: vi.fn().mockResolvedValue({})
  }))
}));

describe('ProfileSettingsDialog', () => {
  let pinia;
  
  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    
    global.URL.createObjectURL = vi.fn(() => 'blob:test-url');
    
    vi.clearAllMocks();
  });
  
  const factory = () => {
    return mount(ProfileSettingsDialog, {
      global: {
        plugins: [pinia],
        stubs: {
          'svg': true
        }
      }
    });
  };

  it('renders the dialog with user information', async () => {
    const wrapper = factory();
    
    await wrapper.vm.$nextTick();
    
    expect(wrapper.find('h2').text()).toBe('Profil beállítások');
  });

  it('emits close event when clicking close button', async () => {
    const wrapper = factory();
    
    await wrapper.find('.close-button').trigger('click');
    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('updates username when input changes', async () => {
    const wrapper = factory();
    
    wrapper.vm.username = 'New Username';
    expect(wrapper.vm.username).toBe('New Username');
  });

  it('previews selected profile image', async () => {
    const wrapper = factory();
    const file = new File(['test'], 'test.jpg', { type: 'image/jpeg' });
    
    wrapper.vm.handleImageUpload({ target: { files: [file] } });
    
    expect(wrapper.vm.profileImage).toBe(file);
    expect(wrapper.vm.profileImageUrl).not.toBe('/default-avatar.jpg');
  });

  it('shows error when no changes are made', async () => {
    const wrapper = factory();
    
    await wrapper.vm.saveUserData();
    
    expect(wrapper.vm.errorMessage).toBe('Nincs új adat!');
  });

  it('uploads profile picture when saving', async () => {
    const wrapper = factory();
    const file = new File(['test'], 'test.jpg', { type: 'image/jpeg' });
    const { fileService } = await import('../../services/fileService.js');
    
    wrapper.vm.profileImage = file;
    wrapper.vm.username = 'New Username';
    
    await wrapper.vm.saveUserData();
    
    expect(fileService.uploadUserProfilePicture).toHaveBeenCalledWith(file);
  });

  it('updates user data when saving', async () => {
    const wrapper = factory();
    const { userService } = await import('../../services/userService.js');
    
    wrapper.vm.username = 'New Username';
    wrapper.vm.email = 'new@example.com';
    
    await wrapper.vm.saveUserData();
    
    expect(userService.updateUser).toHaveBeenCalledWith({
      username: 'New Username',
      email: 'new@example.com'
    });
    
    expect(wrapper.vm.successMessage).toBe('A profil adatok sikeresen frissítve!');
  });

  it('handles errors during save', async () => {
    const wrapper = factory();
    const { userService } = await import('../../services/userService.js');
    
    userService.updateUser.mockRejectedValueOnce(new Error('Update failed'));
    
    wrapper.vm.username = 'New Username';
    
    await wrapper.vm.saveUserData();
    
    expect(wrapper.vm.errorMessage).toBe('Nem sikerült menteni a felhasználói adatokat.');
  });
});