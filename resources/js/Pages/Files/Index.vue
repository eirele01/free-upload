<template>
  <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4">File Uploader</h1>

    <!-- File Upload Form -->
    <form @submit.prevent="uploadFile" enctype="multipart/form-data" class="mb-6">
      <input type="file" @change="handleFile" class="mb-4 p-2 border rounded w-full" />
      <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded" :disabled="form.processing">
        {{ form.processing ? "Uploading..." : "Upload" }}
      </button>
    </form>

    <!-- Uploaded Files List -->
    <h2 class="text-lg font-semibold mb-2">Uploaded Files</h2>
    <ul>
      <li v-for="file in files" :key="file.id" class="border-b py-2 flex items-center justify-between">
        <div>
          <a
            :href="`/storage/${file.file_path}`"
            target="_blank"
            class="text-blue-600 hover:underline"
          >
            {{ file.file_name }}
          </a>
          <span class="text-sm text-gray-500">
            ({{ (file.file_size / (1024 * 1024)).toFixed(2) }} MB)
          </span>
        </div>
        <div class="flex space-x-2">
          <!-- Download Button -->
          <button
            @click="downloadFile(file.id)"
            class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600"
          >
            Download
          </button>

          <!-- Share Link Button -->
          <button
            @click="copyShareLink(file.share_link)"
            class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            Share Link
          </button>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";

// Props passed from the backend
defineProps({
  files: Array,
});

// Form for handling file uploads
const form = useForm({
  file: null,
});

// Event handler for file input
const handleFile = (e) => {
  form.file = e.target.files[0];
};

// Event handler for form submission
const uploadFile = () => {
  form.post("/upload", {
    onSuccess: () => {
      form.reset("file");
    },
  });
};

// Download file
const downloadFile = async (id) => {
  try {
    const response = await axios.get(`/download/${id}`, { responseType: "blob" });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;

    // Extract file name from the response headers or use fallback
    const contentDisposition = response.headers["content-disposition"];
    const fileName = contentDisposition
      ? contentDisposition.split("filename=")[1]?.replace(/["']/g, "")
      : "downloaded_file";

    link.setAttribute("download", fileName);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error("Failed to download file:", error);
  }
};

// Copy share link to clipboard
const copyShareLink = (shareLink) => {
  const fullUrl = `${window.location.origin}/share/${shareLink}`;
  navigator.clipboard.writeText(fullUrl).then(() => {
    alert("Share link copied to clipboard!");
  });
};
</script>
