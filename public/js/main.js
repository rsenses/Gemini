$(document).ready(function() {
    $('body').on('toggle.timetrack', function(e, action, task) {
        if (action === 'start') {
            $('#task-name').text(task);
            $('.timetracker').addClass('active');
        } else {
            $('.timetracker').removeClass('active');
        }
    });
});
function notifyMe(text) {
    // Let's check if the browser supports notifications
    if (!("Notification" in window)) {
        alert("This browser does not support desktop notification");
    }

    // Let's check whether notification permissions have already been granted
    else if (Notification.permission === "granted") {
        // If it's okay let's create a notification
        var notification = new Notification(text);
    }

    // Otherwise, we need to ask the user for permission
    else if (Notification.permission !== 'denied') {
        Notification.requestPermission(function(permission) {
            // If the user accepts, let's create a notification
            if (permission === "granted") {
                var notification = new Notification(text);
            }
        });
    }

    // At last, if the user has denied notifications, and you
    // want to be respectful there is no need to bother them any more.
}
if ($('.tinymce').length) {
    tinymce.init({
        selector: '.tinymce',
        height: 200,
        menubar: false,
        statusbar: false,
        language: 'es',
        language_url: '/js/vendor/tinymce.es.js',
        plugins: [
            'autolink link image anchor',
            'visualblocks lists',
            'paste code'
        ],
        paste_as_text: true,
        // toolbar: 'undo redo | styleselect | bold italic | bullist numlist outdent indent | link image | code',
        toolbar: 'undo redo | styleselect | bold italic | bullist numlist outdent indent | link | code',
        style_formats: [{
                title: 'Headers',
                items: [
                    { title: 'h3', block: 'h3' },
                    { title: 'h4', block: 'h4' },
                    { title: 'h5', block: 'h5' },
                    { title: 'h6', block: 'h6' }
                ]
            },
            {
                title: 'Blocks',
                items: [
                    { title: 'p', block: 'p' },
                    { title: 'div', block: 'div' },
                    { title: 'pre', block: 'pre' }
                ]
            },

            {
                title: 'Containers',
                items: [
                    // { title: 'section', block: 'section', wrapper: true, merge_siblings: false },
                    // { title: 'article', block: 'article', wrapper: true, merge_siblings: false },
                    { title: 'blockquote', block: 'blockquote', wrapper: true },
                    { title: 'hgroup', block: 'hgroup', wrapper: true },
                    // { title: 'aside', block: 'aside', wrapper: true },
                    { title: 'figure', block: 'figure', wrapper: true }
                ]
            }
        ],
        file_picker_callback: function(callback, value, meta) {
            // Provide file and text for the link dialog
            myImagePicker(callback, value, meta);
        },
        visualblocks_default_state: true,
        end_container_on_empty_block: true
    });
}
function myImagePicker(callback, value, meta) {
    tinymce.activeEditor.windowManager.open({
        title: 'Subir Archivo',
        url: '/fileupload',
        width: 511,
        height: 242,
    }, {
        oninsert: function (url, objVals) {
            callback(url, objVals);
        }
    });
}
