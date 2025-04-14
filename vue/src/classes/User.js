export default class User {
    #userID; #userName; #displayName; #profilePicture;

    constructor(userID, userName, displayName, profilePicture) {
        this.#setUserID(userID);
        this.#setUserName(userName);
        this.#setDisplayName(displayName);
        this.#setProfilePicture(profilePicture);
    }

    getUserID() {
        return this.#userID;
    }

    getUserName() {
        return this.#userName;
    }

    getDisplayName() {
        return this.#displayName;
    }

    getProfilePicture() {
        return this.#profilePicture;
    }

    #setUserID(userID) {
        this.#userID = userID;
    }

    #setUserName(userName) {
        this.#userName = userName;
    }

    #setDisplayName(displayName) {
        this.#displayName = displayName;
    }

    #setProfilePicture(profilePicture) {
        this.#profilePicture = profilePicture;
    }
}