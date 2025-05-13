import { mount } from '@vue/test-utils';
import ImageModal from '../../components/chat/messages/ImageModal.vue';
import { describe, it, expect, vi } from 'vitest';

describe('ImageModal', () => {
  let mockOnClose;
  
  beforeEach(() => {
    mockOnClose = vi.fn();
  });

  const factory = () => {
    return mount(ImageModal, {
      props: {
        imageUrl: 'https://example.com/image.jpg',
        onClose: mockOnClose
      }
    });
  };

  it('renders image with correct source', () => {
    const wrapper = factory();
    const img = wrapper.find('.modal-image');
    expect(img.attributes('src')).toBe('https://example.com/image.jpg');
  });

  it('calls onClose when clicking overlay', async () => {
    const wrapper = factory();
    await wrapper.find('.modal-overlay').trigger('click');
    expect(mockOnClose).toHaveBeenCalled();
  });

  it('calls onClose when clicking close button', async () => {
    const wrapper = factory();
    await wrapper.find('.close-button').trigger('click');
    expect(mockOnClose).toHaveBeenCalled();
  });

  it('does not close when clicking modal content', async () => {
    const wrapper = factory();
    await wrapper.find('.modal-content').trigger('click');
    expect(mockOnClose).not.toHaveBeenCalled();
  });
});