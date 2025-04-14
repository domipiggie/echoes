export default class ApiError {
    #error; #message; #code;

    constructor(error, message, code) {
        this.#setError(error);
        this.#setMessage(message);
        this.#setCode(code);
    }

    getError() {
        return this.#error;
    }

    getMessage() {
        return this.#message;
    }

    getCode() {
        return this.#code;
    }

    #setError(error) {
        this.#error = error;
    }

    #setMessage(message) {
        this.#message = message;
    }

    #setCode(code) {
        this.#code = code;
    }
}