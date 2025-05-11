import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import AuthContainer from '../../components/AuthContainer.vue';
import LoginForm from '../../components/LoginForm.vue';
import RegisterForm from '../../components/RegisterForm.vue';
import OverlayContainer from '../../components/OverlayContainer.vue';

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: vi.fn(),
  }),
}));

vi.mock('../../services/authService', () => ({
  authService: {
    login: vi.fn(),
    register: vi.fn(),
  },
}));

vi.mock('../../store/UserdataStore', () => ({
  userdataStore: () => ({
    setAccessToken: vi.fn(),
    setUserID: vi.fn(),
    setRefreshToken: vi.fn(),
  }),
}));

describe('AuthContainer', () => {
  let wrapper;
  let mockAuthService;
  let mockUserdataStore;
  let mockRouterPush;

  beforeEach(async () => {
    vi.clearAllMocks();

    mockAuthService = (await import('../../services/authService')).authService;
    mockUserdataStore = (await import('../../store/UserdataStore')).userdataStore();
    mockRouterPush = (await import('vue-router')).useRouter().push;

    wrapper = mount(AuthContainer, {
      global: {
        stubs: {
          LoginForm: true,
          RegisterForm: true,
          OverlayContainer: true,
        },
      },
    });
  });

  it('renders child components', () => {
    expect(wrapper.findComponent(LoginForm).exists()).toBe(true);
    expect(wrapper.findComponent(RegisterForm).exists()).toBe(true);
    expect(wrapper.findComponent(OverlayContainer).exists()).toBe(true);
  });

  it('toggles right panel active class via methods', async () => {
    expect(wrapper.classes('right-panel-active')).toBe(false);
    await wrapper.vm.activateRightPanel();
    expect(wrapper.classes('right-panel-active')).toBe(true);
    await wrapper.vm.deactivateRightPanel();
    expect(wrapper.classes('right-panel-active')).toBe(false);
  });

  it('calls authService.login and navigates on successful login', async () => {
    mockAuthService.login.mockResolvedValue({
      success: true,
      data: { access_token: 'test_token', userID: 'test_id', refresh_token: 'test_rtoken' },
    });

    await wrapper.vm.sendLoginRequest('user@example.com', 'password123');

    expect(mockAuthService.login).toHaveBeenCalledWith('user@example.com', 'password123');
    expect(mockUserdataStore.setAccessToken).toHaveBeenCalledWith('test_token');
    expect(mockUserdataStore.setUserID).toHaveBeenCalledWith('test_id');
    expect(mockUserdataStore.setRefreshToken).toHaveBeenCalledWith('test_rtoken');
    expect(mockRouterPush).toHaveBeenCalledWith('/chat');
  });

  it('calls authService.register and activates right panel on successful registration', async () => {
    mockAuthService.register.mockResolvedValue({ message: 'Registration successful' });
    const activateSpy = vi.spyOn(wrapper.vm, 'activateRightPanel');

    await wrapper.vm.sendRegisterRequest('testuser', '2000-01-01', 'newuser@example.com', 'newpass123');

    expect(mockAuthService.register).toHaveBeenCalledWith('testuser', 'newuser@example.com', 'newpass123');
    expect(activateSpy).toHaveBeenCalled();
  });

  it('shows alert on login failure', async () => {
    vi.spyOn(window, 'alert').mockImplementation(() => {});
    mockAuthService.login.mockRejectedValue({ response: { data: { message: 'Login failed' } } });
    await wrapper.vm.sendLoginRequest('user@example.com', 'wrongpassword');
    expect(window.alert).toHaveBeenCalledWith('Login failed');
    window.alert.mockRestore();
  });

  it('shows alert on registration failure', async () => {
    vi.spyOn(window, 'alert').mockImplementation(() => {});
    mockAuthService.register.mockRejectedValue({ response: { data: { message: 'Registration failed' } } });
    await wrapper.vm.sendRegisterRequest('testuser', '2000-01-01', 'newuser@example.com', 'newpass123');
    expect(window.alert).toHaveBeenCalledWith('Registration failed');
    window.alert.mockRestore();
  });
});