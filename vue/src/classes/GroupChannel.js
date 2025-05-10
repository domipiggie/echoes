export default class GroupChannel {
    #channelID; #users; #name; #picture;

    constructor(channelID, users, name, picture) {
        this.#setChannelID(channelID);
        this.#setUsers(users);
        this.#setName(name);
        this.#setPicture(picture);

        this.getChannelID = this.getChannelID.bind(this);
        this.getUsers = this.getUsers.bind(this);
        this.getName = this.getName.bind(this);
        this.getPicture = this.getPicture.bind(this);
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
}