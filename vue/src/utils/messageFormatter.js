import { userdataStore } from '../store/UserdataStore';

export const messageFormatter = {
  formatIncomingMessage(message) {
    const userStore = userdataStore();
    return {
      id: message.messageID,
      text: message.content,
      sender: parseInt(message.userID) === parseInt(userStore.getUserID()) ? 'me' : 'other',
      time: new Date(message.sent_at || Date.now()).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      type: message.type || 'text',
      fileName: message.fileName,
      fileSize: message.fileSize,
      fileType: message.fileType
    };
  },

  formatOutgoingMessage(text, newId) {
    if (typeof text === 'object') {
      return {
        id: newId,
        text: text.text,
        type: text.type || 'text',
        fileName: text.fileName,
        fileSize: text.fileSize,
        fileType: text.fileType,
        sender: 'me',
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      };
    } else {
      return {
        id: newId,
        text,
        sender: 'me',
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      };
    }
  }
};