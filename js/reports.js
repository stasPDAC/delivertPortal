$(document).ready(function () {
    $('#projects_table').DataTable({
        columnDefs: [
            {orderable: false, targets: [-1]}
        ],
        order: [[0, 'asc']],
        pageLength: 50,
        language: {
            search: "",
            searchPlaceholder: "חיפוש בכל השדות",
            lengthMenu: "הצג _MENU_ בעמוד",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין דוחות דיירים",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ דוחות דיירים",
            infoFiltered: "(מסונן מתוך _MAX_ דוחות דיירים)",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        }
    });
    setTimeout(() => {
        const box = document.getElementById('msg')
        if(box){
            box.style.display = 'none';
        }
    }, 2000);

    tinymce.init({
        selector: '#articleBodyEditor',
        language: 'he_IL',
        height: '300px',
        toolbar: "link image | undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent",
        plugins: 'image code paste',
        paste_data_images: true,
        image_title: true,
        /* enable automatic uploads of images represented by blob or data URIs*/
        automatic_uploads: true,
        file_picker_types: 'image',
        mobile: {
            theme: 'mobile'
        },
        // mobile: {
        //     menubar: true
        // },
        theme: 'silver',
        file_picker_callback: function (cb, value, meta) {
            // var input = document.createElement('input');
            // input.setAttribute('type', 'file');
            // input.setAttribute('accept', 'image/*');
            const input = document.getElementById("imageInput");
            /*
              Note: In modern browsers input[type="file"] is functional without
              even adding it to the DOM, but that might not be the case in some older
              or quirky browsers like IE, so you might want to add it to the DOM
              just in case, and visually hide it. And do not forget do remove it
              once you do not need it anymore.
            */

            input.onchange = function () {

                var file = this.files[0];

                var reader = new FileReader();
                reader.onload = function () {
                    var img = new Image();
                    var data = this.result;
                    img.src = data;
                    var canvas = document.getElementById('resizingCanvas');
                    var ctx = canvas.getContext('2d');



                    img.onload = function() {
                        if(this.width == 0 || this.height == 0){
                            alert('Image is empty');
                        } else {
                            console.log("this.width", this.width)
                            console.log("this.height", this.height)


                            //TODO: check division by zero
                            const img_ratio = this.width / this.height;
                            const max_width = Math.min(this.width, 600);
                            const max_height = max_width / img_ratio;
                            canvas.setAttribute("height", max_height.toString());
                            canvas.setAttribute("width", max_width.toString());

                            ctx.clearRect(0,0,max_width,max_height);
                            ctx.drawImage(img, 0, 0, this.width, this.height, 0, 0, max_width, max_height);


                            //dataURItoBlob function available here:
                            // http://stackoverflow.com/questions/12168909/blob-from-dataurl
                            // add ')' at the end of this function SO dont allow to update it without a 6 character edit
                            var type = file.type;
                            console.log("type", type);
                            var base64 = canvas.toDataURL(type, 0.4);
                            var blob = dataURItoBlob(canvas.toDataURL(type, 0.4));
                            console.log(base64)
                            console.log(blob)
                            var id = 'blobid' + (new Date()).getTime();
                            var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                            // var base64 = reader.result.split(',')[1];
                            var blobInfo = blobCache.create(id, blob, base64);

                            let test = {
                                id: () => {return 'blobid' + (new Date()).getTime()},
                                name: () => {return file.name},
                                filename: () => {return file.name},
                                blob: () => {return blob},
                                base64: () => {return base64},
                                blobUri: () => {return base64},
                                uri: () => {return undefined}
                            }

                            blobCache.add(test);
                            /* call the callback and populate the Title field with the file name */
                            cb(base64, { title: file.name });
                        }
                    }
                    /*
                      Note: Now we need to register the blob in TinyMCEs image blob
                      registry. In the next release this part hopefully won't be
                      necessary, as we are looking to handle it internally.
                    */

                    // var id = 'blobid' + (new Date()).getTime();
                    // var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                    // var base64 = reader.result.split(',')[1];
                    // var blobInfo = blobCache.create(id, file, base64);
                    // blobCache.add(blobInfo);
                    //
                    //
                    // /* call the callback and populate the Title field with the file name */
                    // cb(blobInfo.blobUri(), { title: file.name });
                };
                reader.readAsDataURL(file);
            };
            input.click();
        }
    });

})
function dataURItoBlob(dataURI) {
    // convert base64 to raw binary data held in a string
    // doesn't handle URLEncoded DataURIs - see SO answer #6850276 for code that does this
    var byteString = atob(dataURI.split(',')[1]);

    // separate out the mime component
    var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0]

    // write the bytes of the string to an ArrayBuffer
    var ab = new ArrayBuffer(byteString.length);

    // create a view into the buffer
    var ia = new Uint8Array(ab);

    // set the bytes of the buffer to the correct values
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }

    // write the ArrayBuffer to a blob, and you're done
    var blob = new Blob([ab], {type: mimeString});
    return blob;
}