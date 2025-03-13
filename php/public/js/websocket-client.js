class EchoesWebSocket {
    constructor(userID) {
        this.userID = userID;
        this.socket = null;
        this.messageHandlers = [];
        this.connected = false;
    }

    connect() {
        this.socket = new WebSocket('ws://localhost:8080');

        this.socket.onopen = () => {
            console.log('WebSocket connection established');
            this.connected = true;

            this.socket.send(JSON.stringify({
                type: 'auth',
                userID: this.userID
            }));
        };

        this.socket.onmessage = (event) => {
            const data = JSON.parse(event.data);

            if (data.type === 'auth_success') {
                console.log('Authentication successful');
            } else if (data.type === 'new_message') {
                console.log('New message received in channel:', data.channelID);

                this.messageHandlers.forEach(handler => {
                    handler(data.message, data.channelID);
                });
            }
        };

        this.socket.onclose = () => {
            console.log('WebSocket connection closed');
            this.connected = false;

            setTimeout(() => this.connect(), 5000);
        };

        this.socket.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
    }

    onNewMessage(callback) {
        this.messageHandlers.push(callback);
    }

    disconnect() {
        if (this.socket) {
            this.socket.close();
        }
    }
}