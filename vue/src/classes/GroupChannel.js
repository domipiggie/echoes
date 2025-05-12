export default class GroupChannel {
    #channelID; #users; #name; #picture; #ownerID; #lastMessageUsername; #lastMessage;

    constructor(channelID, users, name, picture, ownerID, lastMessageUsername, lastMessage) {
        this.#setChannelID(channelID);
        this.#setUsers(users);
        this.#setName(name);
        this.#setPicture(picture);
        this.#setOwnerID(ownerID);
        this.setLastMessageUsername(lastMessageUsername);
        this.setLastMessage(lastMessage);

        this.getChannelID = this.getChannelID.bind(this);
        this.getUsers = this.getUsers.bind(this);
        this.getName = this.getName.bind(this);
        this.getPicture = this.getPicture.bind(this);
        this.getOwnerID = this.getOwnerID.bind(this);
        this.getLastMessageUsername = this.getLastMessageUsername.bind(this);
        this.getLastMessage = this.getLastMessage.bind(this);

        this.setLastMessageUsername = this.setLastMessageUsername.bind(this);
        this.setLastMessage = this.setLastMessage.bind(this);

        this.getLastMessageFormat = this.getLastMessageFormat.bind(this);
    }

    getChannelID() {
        return this.#channelID;
    }

    getUsers() {
        return this.#users;
    }

    getName() {
        return this.#name;
    }

    getPicture() {
        return this.#picture;
    }

    getOwnerID() {
        return this.#ownerID;
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

    #setUsers(users) {
        this.#users = users;
    }

    #setName(name) {
        this.#name = name;
    }

    #setPicture(picture) {
        this.#picture = picture;
    }

    #setOwnerID(ownerID) {
        this.#ownerID = ownerID;
    }

    setLastMessageUsername(name) {
        this.#lastMessageUsername = name;
    }

    setLastMessage(msg) {
        this.#lastMessage = msg;
    }

    getLastMessageFormat() {
        return this.getLastMessageUsername() == undefined ? undefined : this.getLastMessageUsername() + ": " + this.getLastMessage();
    }
}