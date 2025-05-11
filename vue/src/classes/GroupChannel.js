export default class GroupChannel {
    #channelID; #users; #name; #picture; #ownerID;

    constructor(channelID, users, name, picture, ownerID) {
        this.#setChannelID(channelID);
        this.#setUsers(users);
        this.#setName(name);
        this.#setPicture(picture);
        this.#setOwnerID(ownerID);

        this.getChannelID = this.getChannelID.bind(this);
        this.getUsers = this.getUsers.bind(this);
        this.getName = this.getName.bind(this);
        this.getPicture = this.getPicture.bind(this);
        this.getOwnerID = this.getOwnerID.bind(this);
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
}