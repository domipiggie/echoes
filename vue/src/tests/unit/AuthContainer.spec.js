import { mount } from '@vue/test-utils'
import AuthContainer from '../../components/AuthContainer.vue'
import { vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { authService } from '../../services/authService'

vi.mock('../../store/UserdataStore', () => ({
    userdataStore: vi.fn(() => ({
        setAccessToken: vi.fn(),
        setUserID: vi.fn(),
        setRefreshToken: vi.fn()
    }))
}))

vi.mock('../../store/AlertStore', () => ({
    useAlertStore: vi.fn(() => ({
        addAlert: vi.fn()
    }))
}))

vi.mock('../../services/authService', () => ({
    authService: {
        login: vi.fn(),
        register: vi.fn()
    }
}))

describe('AuthContainer', () => {
    let pinia

    beforeEach(() => {
        pinia = createPinia()
        setActivePinia(pinia)
    });

    const factory = () => {
        return mount(AuthContainer, {
            global: {
                plugins: [pinia]
            }
        });
    };

    it('renders login and register forms', () => {
        const wrapper = factory()
        expect(wrapper.findComponent({ name: 'LoginForm' }).exists()).toBe(true)
        expect(wrapper.findComponent({ name: 'RegisterForm' }).exists()).toBe(true)
    })

    it('handles successful login', async () => {
        const wrapper = factory();
        const mockResponse = {
            success: true,
            data: {
                access_token: 'test-access',
                userID: 'user-123',
                refresh_token: 'test-refresh'
            }
        };
    
        authService.login.mockResolvedValue(mockResponse);
        
        await wrapper.findComponent({ name: 'LoginForm' }).vm.$emit('login', 'test@example.com', 'password');
        
        expect(authService.login).toHaveBeenCalledWith('test@example.com', 'password');
        expect(wrapper.vm.userdata.setAccessToken).toHaveBeenCalledWith('test-access');
    });

    it('handles successful registration', async () => {
        const wrapper = factory();
        vi.mocked(authService.register).mockResolvedValue({});

        await wrapper.findComponent({ name: 'RegisterForm' }).vm.$emit('register',
            'testuser', '2000-01-01', 'test@example.com', 'password'
        );

        expect(authService.register).toHaveBeenCalledWith('testuser', 'test@example.com', 'password');
        expect(wrapper.vm.alertStore.addAlert).toHaveBeenCalled()
        expect(wrapper.vm.isRightPanelActive).toBe(true)
    })

    it('toggles between panels using overlay', async () => {
        const wrapper = factory()

        await wrapper.findComponent({ name: 'OverlayContainer' }).vm.$emit('activate-right-panel')
        expect(wrapper.vm.isRightPanelActive).toBe(true)

        await wrapper.findComponent({ name: 'OverlayContainer' }).vm.$emit('deactivate-right-panel')
        expect(wrapper.vm.isRightPanelActive).toBe(false)
    })
})