import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { WS_CONFIG } from '../config/ws';
import { userdataStore } from './UserdataStore';

export const useWebSocketStore = defineStore('websocket', () => {
  // State
  const socket = ref(null);
  const isConnected = ref(false);
  const reconnectAttempts = ref(0);
  const maxReconnectAttempts = ref(5);
  const pingInterval = ref(null);
  const reconnectTimeout = ref(null);
  const messageHandlers = ref(new Map());

  // Getters
  const getIsConnected = computed(() => isConnected.value);

  // Actions
  function connect() {
    if (socket.value && (socket.value.readyState === WebSocket.OPEN || socket.value.readyState === WebSocket.CONNECTING)) {
      console.log('WebSocket already connected or connecting');
      return;
    }

    try {
      socket.value = new WebSocket(`${WS_CONFIG.BASE_URL}`);
      
      socket.value.onopen = onOpen;
      socket.value.onmessage = onMessage;
      socket.value.onclose = onClose;
      socket.value.onerror = onError;
      
      console.log('WebSocket connection initiated');
    } catch (error) {
      console.error('WebSocket connection error:', error);
    }
  }

  function onOpen() {
    console.log('WebSocket connection established');
    isConnected.value = true;
    reconnectAttempts.value = 0;
  }

  function authenticate() {
    const userStore = userdataStore();
    const token = userStore.getAccessToken();
    
    if (token) {
      send({
        type: 'auth',
        token: token
      });
      console.log('Authentication message sent');
    } else {
      console.error('Cannot authenticate: No token available');
    }
  }

  function onMessage(event) {
    try {
      const data = JSON.parse(event.data);
      console.log('WebSocket message received:', data);
      
      if (data.type === 'ping') {
        console.log('Ping received from server, sending pong');
        send({ type: 'pong' });
        return;
      }
      
      if (data.type === 'auth_success') {
        console.log('Authentication successful');
        return;
      }
      
      if (data.type === 'auth_required') {
        console.log('Authentication required, sending token');
        authenticate();
        return;
      }
      
      if (data.type === 'auth_error') {
        console.error('Authentication failed:', data.message);
        return;
      }
      
      if (data.type && messageHandlers.value.has(data.type)) {
        messageHandlers.value.get(data.type)(data);
      }
      
      if (data.type) {
        const event = new CustomEvent(`ws-${data.type}`, { detail: data });
        window.dispatchEvent(event);
      }
    } catch (error) {
      console.error('Error parsing WebSocket message:', error);
    }
  }

  function onClose(event) {
    console.log('WebSocket connection closed:', event.code, event.reason);
    isConnected.value = false;
    
    if (pingInterval.value) {
      clearInterval(pingInterval.value);
      pingInterval.value = null;
    }
    
    if (event.code !== 1000 && reconnectAttempts.value < maxReconnectAttempts.value) {
      attemptReconnect();
    }
  }

  function onError(error) {
    console.error('WebSocket error:', error);
  }

  function attemptReconnect() {
    if (reconnectTimeout.value) {
      clearTimeout(reconnectTimeout.value);
    }
    
    reconnectAttempts.value++;
    const delay = Math.min(30000, Math.pow(2, reconnectAttempts.value) * 1000);
    
    console.log(`Attempting to reconnect in ${delay/1000} seconds (attempt ${reconnectAttempts.value}/${maxReconnectAttempts.value})`);
    
    reconnectTimeout.value = setTimeout(() => {
      connect();
    }, delay);
  }

  function send(data) {
    if (!socket.value || socket.value.readyState !== WebSocket.OPEN) {
      console.error('Cannot send message: WebSocket is not connected');
      return false;
    }
    
    try {
      const message = typeof data === 'string' ? data : JSON.stringify(data);
      socket.value.send(message);
      return true;
    } catch (error) {
      console.error('Error sending WebSocket message:', error);
      return false;
    }
  }

  function registerHandler(messageType, callback) {
    messageHandlers.value.set(messageType, callback);
  }

  function unregisterHandler(messageType) {
    messageHandlers.value.delete(messageType);
  }

  function disconnect() {
    if (pingInterval.value) {
      clearInterval(pingInterval.value);
      pingInterval.value = null;
    }
    
    if (reconnectTimeout.value) {
      clearTimeout(reconnectTimeout.value);
      reconnectTimeout.value = null;
    }
    
    if (socket.value) {
      socket.value.close(1000, 'User disconnected');
      socket.value = null;
    }
    
    isConnected.value = false;
    console.log('WebSocket disconnected');
  }

  return {
    // State exports
    isConnected,
    
    // Getter exports
    getIsConnected,
    
    // Action exports
    connect,
    authenticate,
    send,
    registerHandler,
    unregisterHandler,
    disconnect
  };
});