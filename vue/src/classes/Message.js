export default class Message {
    #messageID; #channelID; #content; #type; #sentAt; #user;

    constructor(messageID, channelID, content, type, sentAt, user) {
        this.#setMessageID(messageID);
        this.#setChannelID(channelID);
        this.#setContent(content);
    }

    getMessageID() {
        return this.#messageID;
    }

    getChannelID() {
        return this.#channelID;
    }

    getContent() {
        return this.#content;
    }

    getType() {
        return this.#type;
    }

    getSentAt() {
        return this.#sentAt;
    }

    getUser() {
        return this.#user;
    }

    #setMessageID(messageID) {
        this.#messageID = messageID;
    }

    #setChannelID(channelID) {
        this.#channelID = channelID;
    }

    #setContent(content) {
        this.#content = content;
    }

    #setType(type) {
        this.#type = type;
    }

    #setSentAt(sentAt) {
        this.#sentAt = sentAt;
    }

    #setUser(user) {
        this.#user = user;
    }
}