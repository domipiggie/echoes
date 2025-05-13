export default class File {
    #fileID; #fileName; #uniqueName; #size; #uploadedAt;

    constructor(fileID, fileName, uniqueName, size, uploadedAt) {
        this.#setFileID(fileID);
        this.#setFileName(fileName);
        this.#setUniqueName(uniqueName);
        this.#setSize(size);
        this.#setUploadedAt(uploadedAt);

        this.getFileID = this.getFileID.bind(this);
        this.getFileName = this.getFileName.bind(this);
        this.getUniqueName = this.getUniqueName.bind(this);
        this.getSize = this.getSize.bind(this);
        this.getUploadedAt = this.getUploadedAt.bind(this);
    }

    getFileID() {
        return this.#fileID;
    }

    getFileName() {
        return this.#fileName;
    }

    getUniqueName() {
        return this.#uniqueName;
    }

    getSize() {
        return this.#size;
    }

    getUploadedAt() {
        return this.#uploadedAt;
    }

    #setFileID(fileID) {
        this.#fileID = fileID;
    }

    #setFileName(fileName) {
        this.#fileName = fileName;
    }

    #setUniqueName(uniqueName) {
        this.#uniqueName = uniqueName;
    }

    #setSize(size) {
        this.#size = size;
    }

    #setUploadedAt(uploadedAt) {
        this.#uploadedAt = uploadedAt;
    }
}