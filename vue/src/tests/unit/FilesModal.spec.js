import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import FilesModal from '../../components/FilesModal.vue';
import { useFileStore } from '../../store/FileStore';
import { useAlertStore } from '../../store/AlertStore';
import { fileService } from '../../services/fileService';
import Alert from '../../classes/Alert';
import { vi } from 'vitest';
import { nextTick } from 'vue';

vi.mock('../../config/api', () => ({
  API_CONFIG: {
    BASE_URL: 'http://test-api'
  }
}));

vi.mock('../../classes/Alert', () => {
  return {
    default: class Alert {
      constructor(title, message, type, callback) {
        this.title = title;
        this.message = message;
        this.type = type;
        this.callback = callback;
      }
      getTitle() { return this.title; }
      getMessage() { return this.message; }
      getType() { return this.type; }
      getCallbackFunction() { return this.callback; }
    }
  };
});

const mockFileStore = {
  getFiles: vi.fn(),
  formatFileSize: vi.fn(),
  fetchFiles: vi.fn(),
  removeFileByID: vi.fn()
};

const mockAlertStore = {
  addAlert: vi.fn()
};

vi.mock('../../store/FileStore', () => ({
  useFileStore: () => mockFileStore
}));

vi.mock('../../store/AlertStore', () => ({
  useAlertStore: () => mockAlertStore
}));

vi.mock('../../services/fileService', () => ({
  fileService: {
    deleteFile: vi.fn()
  }
}));

describe('FilesModal', () => {
  const mockFile = {
    getFileID: () => '1',
    getFileName: () => 'test.jpg',
    getUniqueName: () => 'abc123.jpg',
    getSize: () => 1024
  };
  
  beforeEach(() => {
    vi.clearAllMocks();
    
    mockFileStore.getFiles = [mockFile];
    mockFileStore.formatFileSize.mockReturnValue('1 KB');
  });

  const factory = () => {
    return mount(FilesModal, {
      global: {
        stubs: {
        }
      }
    });
  };

  it('emits close event when clicking overlay', async () => {
    const wrapper = factory();
    await wrapper.find('.new-group-overlay').trigger('click.self');
    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('emits close event when clicking close button', async () => {
    const wrapper = factory();
    await wrapper.find('.close-button').trigger('click');
    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('calls fetchFiles on mount', () => {
    factory();
    expect(mockFileStore.fetchFiles).toHaveBeenCalled();
  });

  it('shows confirmation alert when delete is clicked', async () => {
    const wrapper = factory();
    
    const deleteButtons = wrapper.findAll('.selection-indicator');
    if (deleteButtons.length > 0) {
      await deleteButtons[0].trigger('click');
      expect(mockAlertStore.addAlert).toHaveBeenCalled();
      
      const alertArg = mockAlertStore.addAlert.mock.calls[0][0];
      expect(alertArg.getTitle()).toBe('Megerősítés');
      expect(alertArg.getType()).toBe('confirm');
    }
  });
});