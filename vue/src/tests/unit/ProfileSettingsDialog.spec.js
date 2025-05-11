import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import ProfileSettingsDialog from '../../components/ProfileSettingsDialog.vue';
import axios from 'axios';

vi.mock('../../store/UserdataStore', () => ({
  userdataStore: () => ({
    getUserID: vi.fn(() => 'testUserID'),
    getAccessToken: vi.fn(() => 'testAccessToken'),
  }),
}));

vi.mock('axios');

vi.mock('../../config/api.js', () => ({
  API_CONFIG: {
    BASE_URL: 'http://localhost:3000/api',
  },
}));

describe('ProfileSettingsDialog', () => {
  let wrapper;

  beforeEach(async () => {
    vi.clearAllMocks();
    axios.get.mockResolvedValue({ data: {} });

    wrapper = mount(ProfileSettingsDialog);
    await wrapper.vm.$nextTick();
    await wrapper.vm.$nextTick();
  });

  it('renders form elements with initial placeholder data', () => {
    expect(wrapper.find('input#username').element.value).toBe('Felhasználó');
    expect(wrapper.find('input#email').element.value).toBe('felhasznalo@example.com');
    expect(wrapper.find('textarea#bio').element.value).toBe('Ez egy rövid bemutatkozás...');
  });

  it('emits "close" event when "Mégse" button is clicked', async () => {
    const closeButton = wrapper.findAll('button').filter(b => b.text() === 'Mégse').at(0);
    await closeButton.trigger('click');
    expect(wrapper.emitted().close).toBeTruthy();
  });

  it('calls saveUserData and axios.put on form submit', async () => {
    axios.put.mockResolvedValue({ data: { message: 'Profile updated successfully' } });

    await wrapper.find('input#username').setValue('UpdatedUser');
    await wrapper.find('textarea#bio').setValue('Updated Bio');

    await wrapper.find('form').trigger('submit.prevent');

    expect(axios.put).toHaveBeenCalled();
    const calledUrl = axios.put.mock.calls[0][0];
    const calledData = axios.put.mock.calls[0][1];

    expect(calledUrl).toBe('http://localhost:3000/api/userInfo/testUserID');
    expect(calledData.get('username')).toBe('UpdatedUser');
    expect(calledData.get('bio')).toBe('Updated Bio');
    expect(wrapper.vm.successMessage).toBe('A profil adatok sikeresen frissítve!');
  });

  it('handles image upload and updates preview', async () => {
    const file = new File(['(⌐□_□)'], 'chucknorris.png', { type: 'image/png' });
    const mockCreateObjectURL = vi.fn(() => 'blob:http://localhost/testimage');
    global.URL.createObjectURL = mockCreateObjectURL;

    const fileInput = wrapper.find('input[type="file"]');
    await wrapper.vm.handleImageUpload({ target: { files: [file] } });

    expect(wrapper.vm.profileImage).toBe(file);
    expect(wrapper.vm.profileImageUrl).toBe('blob:http://localhost/testimage');
    expect(mockCreateObjectURL).toHaveBeenCalledWith(file);
    expect(wrapper.find('img.w-32.h-32').attributes('src')).toBe('blob:http://localhost/testimage');

    global.URL.createObjectURL = undefined;
  });

  it('shows error message if saveUserData fails', async () => {
    axios.put.mockRejectedValue(new Error('Failed to save'));
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessage).toBe('Nem sikerült menteni a felhasználói adatokat.');
  });
});