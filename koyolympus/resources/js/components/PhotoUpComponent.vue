<template>
    <div class="col">
        <div class="genre">
            <select v-model="genre">
                <option value="#">which genre?</option>
                <option value="1">Landscape</option>
                <option value="2">Animal</option>
                <option value="3">Portrait</option>
                <option value="4">SnapShot</option>
                <option value="5">Live Composite</option>
                <option value="6">PinHole/Film</option>
            </select>
        </div>
        <vue-dropzone
            ref="myVueDropzone"
            id="dropzone"
            :options="dropzoneOptions"
            :useCustomSlot="true"
            v-on:vdropzone-success="uploadSuccess"
            v-on:vdropzone-error="uploadError"
            v-on:vdropzone-removed-file="fileRemoved"
            v-on:vdropzone-sending="sendingEvent"
        >
            <div class="dropzone-custom-content">
                <h3 class="dropzone-custom-title">Drag and drop to upload content!</h3>
                <div class="subtitle">...or click to select a file from your computer</div>
            </div>
        </vue-dropzone>
    </div>
</template>

<script>
import vue2Dropzone from "vue2-dropzone";
import "vue2-dropzone/dist/vue2Dropzone.min.css";

export default {
    components: {
        vueDropzone: vue2Dropzone
    },
    data() {
        return {
            dropzoneOptions: {
                url: "/api/photo/upload",
                addRemoveLinks: true,
                maxFiles: 10,
                acceptedFiles: '.jpg, .jpeg',
                thumbnailWidth: 150,
                //TODO: jQuery使わない様にする。
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            },
            genre: '',
            fileName: [],
        };
    },
    methods: {
        uploadSuccess(file, response) {
            console.log('File Successfully Uploaded with file name: ' + response.file);
            file.custom = response.file;
        },
        uploadError(file, message) {
            console.log('An Error Occurred');
        },
        fileRemoved(file) {
            let genre = this.genre;
            let params = {
                file,
                genre,
            }
            axios.post('/api/photo/remove', params)
                .then((response) => {
                    console.log('Your photo which is ' + file.name + ' is deleted completely');
                })
                .catch((error) => {
                    console.log('something went wrong!');
                })
        },
        sendingEvent(file, xhr, formData) {
            formData.append('genre', this.genre);
        },
    }
};
</script>

<style scoped>

.col {
    flex-basis: 50%;
}

</style>
