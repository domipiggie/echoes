export default class Message {
    #messageID; #channelID; #content; #type; #sentAt; #user; #replyTo; #replyToText; #replyToSender; #replyToType;

    constructor(messageID, channelID, content, type, sentAt, user, replyTo = null, replyToText = null, replyToSender = null, replyToType = null) {
        this.#setMessageID(messageID);
        this.#setChannelID(channelID);
        this.#setContent(content);
        this.#setType(type);
        this.#setSentAt(sentAt);
        this.#setUser(user);
        this.#setReplyTo(replyTo);
        this.#setReplyToText(replyToText);
        this.#setReplyToSender(replyToSender);
        this.#setReplyToType(replyToType);

        this.getMessageID = this.getMessageID.bind(this);
        this.getChannelID = this.getChannelID.bind(this);
        this.getContent = this.getContent.bind(this);
        this.getType = this.getType.bind(this);
        this.getSentAt = this.getSentAt.bind(this);
        this.getUser = this.getUser.bind(this);
        this.getReplyTo = this.getReplyTo.bind(this);
        this.getReplyToText = this.getReplyToText.bind(this);
        this.getReplyToSender = this.getReplyToSender.bind(this);
        this.getReplyToType = this.getReplyToType.bind(this);
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
    
    getReplyTo() {
        return this.#replyTo;
    }
    
    getReplyToText() {
        return this.#replyToText;
    }
    
    getReplyToSender() {
        return this.#replyToSender;
    }
    
    getReplyToType() {
        return this.#replyToType;
    }
    
    #setReplyTo(replyTo) {
        this.#replyTo = replyTo;
    }
    
    #setReplyToText(replyToText) {
        this.#replyToText = replyToText;
    }
    
    #setReplyToSender(replyToSender) {
        this.#replyToSender = replyToSender;
    }
    
    #setReplyToType(replyToType) {
        this.#replyToType = replyToType;
    }
}