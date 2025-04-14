export default class Friendship {
    #friendshipID; #statusID; #status; #targetUser;

    constructor(friendshipID, statusID, status, targetUser) {
        this.#setFriendshipID(friendshipID);
        this.#setStatusID(statusID);
        this.#setStatus(status);
        this.#setTargetUser(targetUser);
    }

    getFriendshipID() {
        return this.#friendshipID;
    }

    getStatusID() {
        return this.#statusID;
    }

    getStatus() {
        return this.#status;
    }

    getTargetUser() {
        return this.#targetUser;
    }

    #setFriendshipID(friendshipID) {
        this.#friendshipID = friendshipID;
    }

    #setStatusID(statusID) {
        this.#statusID = statusID;
    }

    #setStatus(status) {
        this.#status = status;
    }

    #setTargetUser(targetUser) {
        this.#targetUser = targetUser;
    }
}