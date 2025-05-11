import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import AuthContainer from '../../components/AuthContainer.vue';
import authService from '../../services/authService';
import { useRouter } from 'vue-router';

vi.mock('../../services/authService');
vi.mock('vue-router', () => ({
  useRouter: vi.fn(() => ({
    push: vi.fn(),
  })),
}));

vi.mock('../../components/LoginForm.vue', () => ({ default: { template: '<div class="mock-login-form"></div>', name: 'LoginForm' }}));
vi.mock('../../components/RegisterForm.vue', () => ({ default: { template: '<div class="mock-register-form"></div>', name: 'RegisterForm' }}));
vi.mock('../../components/OverlayContainer.vue', () => ({ default: { template: '<div class="mock-overlay-container"></div>', name: 'OverlayContainer' }}));

describe('AuthContainer', () => {
  let routerPushMock;
  let windowAlertSpy;

  beforeEach(() => {
    vi.clearAllMocks();
    routerPushMock = vi.fn();
    useRouter.mockReturnValue({ push: routerPushMock });
    windowAlertSpy = vi.spyOn(window, 'alert').mockImplementation(() => {});
    
    authService.setToken = vi.fn();
  });

  it('renders child components', () => {
    const wrapper = mount(AuthContainer);
    expect(wrapper.findComponent({ name: 'LoginForm' }).exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'RegisterForm' }).exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'OverlayContainer' }).exists()).toBe(true);
  });

  it('toggles right panel active class via methods', () => {
    const wrapper = mount(AuthContainer);
    expect(wrapper.vm.isRightPanelActive).toBe(false);
    wrapper.vm.activateRightPanel();
    expect(wrapper.vm.isRightPanelActive).toBe(true);
    wrapper.vm.deactivateRightPanel();
    expect(wrapper.vm.isRightPanelActive).toBe(false);
  });

  it('calls authService.login and navigates on successful login', async () => {
    authService.login.mockResolvedValue({ token: 'test_token' });
    const wrapper = mount(AuthContainer);
    
    await wrapper.vm.sendLoginRequest('test@example.com', 'password');
    
    expect(authService.login).toHaveBeenCalledWith('test@example.com', 'password');
    expect(authService.setToken).toHaveBeenCalledWith('test_token');
    expect(routerPushMock).toHaveBeenCalledWith('/chat');
  });

  it('calls authService.register and activates right panel on successful registration', async () => {
    authService.register.mockResolvedValue({ message: 'Registration successful' });
    const wrapper = mount(AuthContainer);
    const spyActivateRightPanel = vi.spyOn(wrapper.vm, 'activateRightPanel');
    
    await wrapper.vm.sendRegisterRequest('user', '2000-01-01', 'test@example.com', 'password');
    
    expect(authService.register).toHaveBeenCalledWith('user', '2000-01-01', 'test@example.com', 'password');
    expect(spyActivateRightPanel).toHaveBeenCalled();
    expect(windowAlertSpy).toHaveBeenCalledWith('Sikeres regisztráció!');
  });

  it('shows alert on login failure', async () => {
    authService.login.mockRejectedValue(new Error('Login failed'));
    const wrapper = mount(AuthContainer);
    
    await wrapper.vm.sendLoginRequest('test@example.com', 'password');
    
    expect(windowAlertSpy).toHaveBeenCalledWith('Sikertelen bejelentkezés!');
    expect(routerPushMock).not.toHaveBeenCalled();
  });

  it('shows alert on registration failure', async () => {
    authService.register.mockRejectedValue(new Error('Registration failed'));
    const wrapper = mount(AuthContainer);
    
    await wrapper.vm.sendRegisterRequest('user', '2000-01-01', 'test@example.com', 'password');
    
    expect(windowAlertSpy).toHaveBeenCalledWith('Sikertelen regisztráció!');
  });
});