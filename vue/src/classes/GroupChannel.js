export default class GroupChannel {
    #channelID; #users;

    constructor(channelID, users) {
        this.#setChannelID(channelID);
        this.#setUsers(users);
    }

    getChannelID() {
        return this.#channelID;
    }

    getUsers() {
        return this.#users;
    }

    #setChannelID(channelID) {
        this.#channelID = channelID;
    }

    #setUsers(users) {
        this.#users = users;
    }
}