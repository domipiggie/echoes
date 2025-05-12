export default class Alert {
    #title; #message; #type; #callbackFunction; #index;

    getTitle() {
        return this.#title;
    }

    getMessage() {
        return this.#message;
    }

    getType() {
        return this.#type;
    }

    getCallbackFunction() {
        return this.#callbackFunction;
    }

    getIndex() {
        return this.#index;
    }

    #setTitle(title) {
        this.#title = title;
    }

    #setMessage(message) {
        this.#message = message;
    }

    #setType(type) {
        this.#type = type;
    }

    #setCallbackFunction(callbackFunction) {
        this.#callbackFunction = callbackFunction;
    }

    setIndex(index) {
        this.#index = index;
    }

    constructor(title, message, type, callbackFunction) {
        this.#setTitle(title);
        this.#setMessage(message);
        this.#setType(type);
        this.#setCallbackFunction(callbackFunction);

        this.getTitle = this.getTitle.bind(this);
        this.getMessage = this.getMessage.bind(this);
        this.getType = this.getType.bind(this);
        this.getCallbackFunction = this.getCallbackFunction.bind(this);
        this.getIndex = this.getIndex.bind(this);
        this.setIndex = this.setIndex.bind(this);
    }
}