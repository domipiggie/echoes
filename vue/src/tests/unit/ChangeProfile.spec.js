import { mount } from '@vue/test-utils';
import { vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import ChangeProfile from '../../components/groupModals/ChangeProfile.vue';
import { fileService } from '../../services/fileService';
import { API_CONFIG } from '../../config/api';

vi.mock('../../store/UserdataStore', () => ({
  userdataStore: vi.fn(() => ({
    getUserID: () => 'test-user-123'
  }))
}));

vi.mock('../../store/MessageStore', () => ({
  useMessageStore: vi.fn(() => ({
    getCurrentChannelId: 'test-channel-123',
    getCurrentChannelName: 'Test Channel'
  }))
}));

vi.mock('../../store/ChannelStore', () => ({
  useChannelStore: vi.fn(() => ({
    getGroupChannelById: vi.fn(() => ({
      getPicture: () => '/group.jpg'
    }))
  }))
}));

vi.mock('../../services/fileService.js', () => ({
  fileService: {
    uploadGroupProfile: vi.fn().mockResolvedValue({
      profilePicture: '/new-group.jpg'
    })
  }
}));

describe('ChangeProfile', () => {
  let pinia;

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    API_CONFIG.BASE_URL = 'http://test.com';
    global.URL.createObjectURL = vi.fn(() => 'blob:test-url');
  });

  const factory = () => {
    return mount(ChangeProfile, {
      global: {
        plugins: [pinia],
        stubs: {
          'svg': true
        }
      }
    });
  };

  it('previews selected image', async () => {
    const wrapper = factory();
    const file = new File(['test'], 'test.jpg', { type: 'image/jpeg' });
    
    await wrapper.vm.handleImageUpload({ target: { files: [file] } });
    
    expect(wrapper.vm.profileImageUrl).toBe('blob:test-url');
    expect(URL.createObjectURL).toHaveBeenCalledWith(file);
  });
  
  it('shows error when upload fails', async () => {
    const wrapper = factory();
    fileService.uploadGroupProfile.mockRejectedValueOnce(new Error('Upload failed'));
    
    await wrapper.vm.handleImageUpload({ target: { files: [new File([''], 'test.jpg')] } });
    await wrapper.find('.save-button').trigger('click');
    
    await wrapper.vm.$nextTick();
    expect(wrapper.vm.errorMessage).toBe('Nem sikerült menteni a felhasználói adatokat.');
  });
});