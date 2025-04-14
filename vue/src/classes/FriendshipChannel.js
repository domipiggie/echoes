export default class FriendshipChannel {
    #channelID; #user1; #user2;

    constructor(channelID, user1, user2) {
        this.#setChannelID(channelID);
        this.#setUser1(user1);
        this.#setUser2(user2);
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

    #setChannelID(channelID) {
        this.#channelID = channelID;
    }

    #setUser1(user1) {
        this.#user1 = user1;
    }

    #setUser2(user2) {
        this.#user2 = user2;
    }
}