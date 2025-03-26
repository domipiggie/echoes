import { ref, onUnmounted } from 'vue';
import { userdataStore } from '../store/UserdataStore';
import { WS_CONFIG } from '../config/ws';

export function useWebSocket(onMessageReceived) {
  const userStore = userdataStore();
  const socket = ref(null);
  const connected = ref(false);
  const reconnectAttempts = ref(0);
  let pingTimer = null;

  const connectWebSocket = () => {
    try {
      socket.value = new WebSocket(WS_CONFIG.BASE_URL);
      console.log('[WebSocket] Attempting to connect to:', WS_CONFIG.BASE_URL);
    } catch (e) {
      console.error('Error creating WebSocket connection:', e.message);
      scheduleReconnect();
      return;
    }

    socket.value.onopen = () => {
      console.log('[WebSocket] Connection established');
      connected.value = true;
      reconnectAttempts.value = 0;

      const token = userStore.getAccessToken();
      if (!token) {
        console.error('[WebSocket] No authentication token available');
        return;
      }

      // Log the user ID from the token for debugging
      try {
        const tokenParts = token.split('.');
        if (tokenParts.length === 3) {
          const payload = JSON.parse(atob(tokenParts[1]));
          console.log('[WebSocket] Token contains user ID:', payload.id || payload.sub || payload.userID);
        }
      } catch (e) {
        console.error('[WebSocket] Error parsing token:', e);
      }

      console.log('[WebSocket] Sending authentication with token:', token.substring(0, 10) + '...');
      
      socket.value.send(JSON.stringify({
        type: 'auth',
        token: token
      }));

      startPingInterval();
    };

    socket.value.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);

        if (data.type === 'ping') {
          handlePing(data);
          return;
        }

        if (data.type === 'auth_success') {
          console.log('[WebSocket] Authentication successful');
        } else if (data.type === 'auth_error') {
          console.error('[WebSocket] Authentication failed:', data.message);
          disconnectWebSocket();
        } else if (data.type === 'error') {
          console.error('[WebSocket] Server error:', data.message);
        } else if (data.type === 'new_message') {
          onMessageReceived(data);
        } else if (data.type === 'friend_request') {
          console.log('[WebSocket] Friend request received:', data);
          onMessageReceived(data);
        }
      } catch (e) {
        console.error('[WebSocket] Error processing message:', e.message);
      }
    };

    socket.value.onclose = (event) => {
      console.log('[WebSocket] Connection closed:', event.reason || 'No reason provided');
      connected.value = false;
      stopPingInterval();

      scheduleReconnect();
    };

    socket.value.onerror = (error) => {
      console.error('[WebSocket] Error occurred');
    };
  };

  const handlePing = (data) => {
    if (connected.value && socket.value && socket.value.readyState === WebSocket.OPEN) {
      socket.value.send(JSON.stringify({
        type: 'pong',
        timestamp: data.timestamp
      }));
    }
  };

  const startPingInterval = () => {
    stopPingInterval();
    pingTimer = setInterval(() => {
      if (connected.value && socket.value && socket.value.readyState === WebSocket.OPEN) {
        socket.value.send(JSON.stringify({
          type: 'ping',
          timestamp: Date.now()
        }));
      }
    }, WS_CONFIG.PING_INTERVAL);
  };

  const stopPingInterval = () => {
    if (pingTimer) {
      clearInterval(pingTimer);
      pingTimer = null;
    }
  };

  const scheduleReconnect = () => {
    if (reconnectAttempts.value >= WS_CONFIG.MAX_RECONNECT_ATTEMPTS) {
      console.error('[WebSocket] Maximum reconnection attempts reached');
      return;
    }

    reconnectAttempts.value++;
    const delay = WS_CONFIG.RECONNECT_INTERVAL * Math.pow(1.5, reconnectAttempts.value - 1);

    console.log(`[WebSocket] Scheduling reconnect attempt ${reconnectAttempts.value} in ${delay}ms`);
    setTimeout(() => connectWebSocket(), delay);
  };

  const disconnectWebSocket = () => {
    stopPingInterval();
    if (socket.value) {
      socket.value.close();
      socket.value = null;
    }
    connected.value = false;
  };

  onUnmounted(() => {
    disconnectWebSocket();
  });

  return {
    connected,
    connectWebSocket,
    disconnectWebSocket
  };
}