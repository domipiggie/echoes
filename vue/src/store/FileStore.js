import { defineStore } from "pinia";
import { ref, computed } from "vue";
import File from "../classes/File";
import { fileService } from "../services/fileService";

export const useFileStore = defineStore("file", () => {
    const files = ref([]);
    const usedSpace = ref(0);
    const usedFormatted = ref("0 B");

    const getFiles = computed(() => files.value);
    const getUsedSpace = computed(() => usedSpace.value);
    const getUsedFormatted = computed(() => usedFormatted.value);

    const getFileByID = computed(() => (fileID) => {
        return files.value.find(file => file.getFileID() === fileID);
    })

    const removeFileByID = (fileID) => {
        files.value = files.value.filter(file => file.getFileID() !== fileID);
    }

    const fetchFiles = async () => {
        try {
            const response = await fileService.getFiles();
            files.value = response.data.map(file => new File(file.fileID, file.file_name, file.unique_name, file.size, file.uploaded_at));
            console.log(files.value);
        } catch (error) {
            console.error("Error fetching files:", error);
        }
    }

    const fetchUsedSpace = async () => {
        try {
            const response = await fileService.getUsedSpace();
            usedSpace.value = response.data.total_size_bytes;
            usedFormatted.value = response.data.total_size_formatted;
        } catch (error) {
            console.error("Error fetching used space:", error);
        }
    }

    const formatFileSize = (size) => {
        const units = ["B", "KB", "MB", "GB", "TB"];
        let index = 0;

        while (size >= 1024 && index < units.length - 1) {
            size /= 1024;
            index++;
        }

        return size.toFixed(2) + " " + units[index];
    }

    return {
        getFiles,
        getUsedSpace,
        getFileByID,
        getUsedFormatted,
        removeFileByID,
        fetchFiles,
        fetchUsedSpace,
        formatFileSize
    }
})
