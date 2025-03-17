class EchoesWebSocket {
    constructor(userID, options = {}) {
        this.userID = userID;
        this.socket = null;
        this.messageHandlers = [];
        this.connected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = options.maxReconnectAttempts || 5;
        this.reconnectInterval = options.reconnectInterval || 5000;
        this.pingInterval = options.pingInterval || 25000;
        this.pingTimer = null;
        this.url = options.url || 'ws://localhost:8080';
        this.debug = options.debug || false;
    }

    connect() {
        this.log('Connecting to WebSocket server...');
        
        try {
            this.socket = new WebSocket(this.url);
        } catch (e) {
            this.log('Error creating WebSocket connection: ' + e.message, 'error');
            this.scheduleReconnect();
            return;
        }

        this.socket.onopen = () => {
            this.log('WebSocket connection established');
            this.connected = true;
            this.reconnectAttempts = 0;

            this.socket.send(JSON.stringify({
                type: 'auth',
                userID: this.userID
            }));
            
            this.startPingInterval();
        };

        this.socket.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                
                if (data.type === 'ping') {
                    this.handlePing(data);
                    return;
                }

                if (data.type === 'auth_success') {
                    this.log('Authentication successful');
                } else if (data.type === 'auth_error') {
                    this.log('Authentication failed: ' + data.message, 'error');
                    this.disconnect();
                } else if (data.type === 'error') {
                    this.log('Server error: ' + data.message, 'error');
                } else if (data.type === 'new_message') {
                    this.log('New message received in channel: ' + data.channelID);
                    
                    this.messageHandlers.forEach(handler => {
                        handler(data.message, data.channelID);
                    });
                }
            } catch (e) {
                this.log('Error processing message: ' + e.message, 'error');
            }
        };

        this.socket.onclose = (event) => {
            this.log('WebSocket connection closed: ' + (event.reason || 'No reason provided'));
            this.connected = false;
            this.stopPingInterval();
            
            this.scheduleReconnect();
        };

        this.socket.onerror = (error) => {
            this.log('WebSocket error', 'error');
        };
    }
    
    handlePing(data) {
        if (this.connected && this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify({
                type: 'pong',
                timestamp: data.timestamp
            }));
        }
    }
    
    startPingInterval() {
        this.stopPingInterval();
        this.pingTimer = setInterval(() => {
            if (this.connected && this.socket.readyState === WebSocket.OPEN) {
                this.log('Sending ping', 'debug');
                this.socket.send(JSON.stringify({
                    type: 'ping',
                    timestamp: Date.now()
                }));
            }
        }, this.pingInterval);
    }
    
    stopPingInterval() {
        if (this.pingTimer) {
            clearInterval(this.pingTimer);
            this.pingTimer = null;
        }
    }
    
    scheduleReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            this.log('Maximum reconnection attempts reached', 'error');
            return;
        }
        
        this.reconnectAttempts++;
        const delay = this.reconnectInterval * Math.pow(1.5, this.reconnectAttempts - 1);
        
        this.log(`Scheduling reconnect attempt ${this.reconnectAttempts} in ${delay}ms`);
        setTimeout(() => this.connect(), delay);
    }

    onNewMessage(callback) {
        if (typeof callback === 'function') {
            this.messageHandlers.push(callback);
        }
        return this;
    }

    disconnect() {
        this.stopPingInterval();
        if (this.socket) {
            this.socket.close();
            this.socket = null;
        }
        this.connected = false;
    }
    
    log(message, level = 'info') {
        if (this.debug || level === 'error') {
            const prefix = `[EchoesWebSocket][${level.toUpperCase()}]`;
            if (level === 'error') {
                console.error(`${prefix} ${message}`);
            } else {
                console.log(`${prefix} ${message}`);
            }
        }
    }
    
    isConnected() {
        return this.connected && this.socket && this.socket.readyState === WebSocket.OPEN;
    }
}