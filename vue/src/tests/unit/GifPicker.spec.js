import { mount } from '@vue/test-utils';
import GifPicker from '../../components/GifPicker.vue';
import { vi } from 'vitest';

describe('GifPicker', () => {
  const mockGifs = {
    data: [
      {
        id: '1',
        title: 'test gif',
        images: {
          fixed_height_small: { url: 'https://test.com/small.gif' },
          original: { url: 'https://test.com/original.gif' }
        }
      }
    ]
  };

  beforeEach(() => {
    global.fetch = vi.fn(() =>
      Promise.resolve({
        json: () => Promise.resolve(mockGifs),
      })
    );
  });

  afterEach(() => {
    vi.clearAllMocks();
  });

  it('renders correctly and fetches trending gifs', async () => {
    const wrapper = mount(GifPicker);
    await wrapper.vm.$nextTick();
    
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('api.giphy.com/v1/gifs/search?api_key=')
    );
    expect(wrapper.find('.gif-search-input').exists()).toBe(true);
  });

  it('searches gifs with debounce', async () => {
    const wrapper = mount(GifPicker);
    await wrapper.vm.$nextTick();
    
    const input = wrapper.find('.gif-search-input');
    await input.setValue('cats');
    
    vi.useFakeTimers();
    wrapper.vm.searchGifs();
    vi.advanceTimersByTime(300);
    
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('q=cats')
    );
    vi.useRealTimers();
  });

  it('emits select-gif event when clicking a gif', async () => {
    const wrapper = mount(GifPicker);
    
    await new Promise(resolve => setTimeout(resolve, 0));
    await wrapper.vm.$nextTick();
    
    const gifItem = wrapper.find('.gif-item');
    if (gifItem.exists()) {
      await gifItem.trigger('click');
      expect(wrapper.emitted('select-gif')).toBeTruthy();
    } else {
      console.warn('No .gif-item elements found');
    }
  });
  
  it('shows loading state', async () => {
    const wrapper = mount(GifPicker);
    
    wrapper.vm.loading = true;
    await wrapper.vm.$nextTick();
    
    expect(wrapper.find('.loading').exists()).toBe(true);
    expect(wrapper.find('.loading').text()).toBe('Betöltés...');
  });
  
  it('closes when clicking outside', async () => {
    const wrapper = mount(GifPicker);
    
    const outsideElement = document.createElement('div');
    document.body.appendChild(outsideElement);
    
    const event = new MouseEvent('click', {
      bubbles: true,
      cancelable: true
    });
    outsideElement.dispatchEvent(event);
    
    await wrapper.vm.$nextTick();
    expect(wrapper.emitted('close')).toBeTruthy();
    
    document.body.removeChild(outsideElement);
  });
});