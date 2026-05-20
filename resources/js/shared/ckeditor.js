import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import '../../css/ckeditor.css';

class MyUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    upload() {
        return this.loader.file
            .then(file => new Promise((resolve, reject) => {
                const data = new FormData();
                data.append("upload", file);
                data.append("_token", document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                fetch("/ckeditor/upload", {
                    method: "POST",
                    body: data
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.url) {
                            resolve({default: result.url});
                        } else {
                            reject(result.error || "Upload mislukt");
                        }
                    })
                    .catch(error => reject(error));
            }));
    }
}

function MyCustomUploadAdapterPlugin(editor) {
    editor.plugins.get("FileRepository").createUploadAdapter = (loader) => {
        return new MyUploadAdapter(loader);
    };
}

document.querySelectorAll('.ckeditor').forEach(editorElement => {
    ClassicEditor.create(editorElement, {
        contentsCss: '/build/assets/ckeditor.css',
        extraPlugins: [MyCustomUploadAdapterPlugin],
        sourceEditing: {
            allowCollaborationFeatures: true
        }
    })
        .then(() => {})
        .catch(error => {
            console.error("Fout bij het laden van CKEditor:", error);
        });
});


