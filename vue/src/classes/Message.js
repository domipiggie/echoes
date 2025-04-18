export default class Message {
    #messageID; #channelID; #content; #type; #sentAt; #user;

    constructor(messageID, channelID, content, type, sentAt, user) {
        this.#setMessageID(messageID);
        this.#setChannelID(channelID);
        this.#setContent(content);
        this.#setType(type);
        this.#setSentAt(sentAt);
        this.#setUser(user);

        this.getMessageID = this.getMessageID.bind(this);
        this.getChannelID = this.getChannelID.bind(this);
        this.getContent = this.getContent.bind(this);
        this.getType = this.getType.bind(this);
        this.getSentAt = this.getSentAt.bind(this);
        this.getUser = this.getUser.bind(this);
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