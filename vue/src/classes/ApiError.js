export default class ApiError {
    #error; #message; #code;

    constructor(error, message, code) {
        this.#setError(error);
        this.#setMessage(message);
        this.#setCode(code);

        this.getError = this.getError.bind(this);
        this.getMessage = this.getMessage.bind(this);
        this.getCode = this.getCode.bind(this);
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