export default class FriendshipChannel {
    #channelID; #user1; #user2; #lastMessageUsername; #lastMessage;

    constructor(channelID, user1, user2, lastMessageUsername, lastMessage) {
        this.#setChannelID(channelID);
        this.#setUser1(user1);
        this.#setUser2(user2);
        this.setLastMessageUsername(lastMessageUsername);
        this.setLastMessage(lastMessage);

        this.getChannelID = this.getChannelID.bind(this);
        this.getUser1 = this.getUser1.bind(this);
        this.getUser2 = this.getUser2.bind(this);
        this.getLastMessageUsername = this.getLastMessageUsername.bind(this);
        this.getLastMessage = this.getLastMessage.bind(this);

        this.setLastMessageUsername = this.setLastMessageUsername.bind(this);
        this.setLastMessage = this.setLastMessage.bind(this);
        
        this.getLastMessageFormat = this.getLastMessageFormat.bind(this);
    }

    getChannelID() {
        return this.#channelID;
    }

    getUser1() {
        return this.#user1;
    }

    getUser2() {
        return this.#user2;
    }

    getLastMessageUsername() {
        return this.#lastMessageUsername;
    }

    getLastMessage() {
        return this.#lastMessage;
    }

    #setChannelID(channelID) {
        this.#channelID = channelID;
    }

    #setUser1(user1) {
        this.#user1 = user1;
    }

    #setUser2(user2) {
        this.#user2 = user2;
    }

    setLastMessageUsername(name) {
        this.#lastMessageUsername = name;
    }

    setLastMessage(msg) {
        this.#lastMessage = msg;
    }

    getLastMessageFormat() {
        return this.getLastMessageUsername() + ": " + this.getLastMessage();
    }
}