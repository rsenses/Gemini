Number.prototype.toHHMMSS = function () {
    var sec_num = parseInt(this, 10); // don't forget the second param
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    return hours+':'+minutes+':'+seconds;
};
$(document).ready(function() {
    $('body').on('toggle.timetrack', function(e, action, task, start) {
        if (action === 'start') {
            $('#task-name').text(task);
            $('.timetracker').addClass('active');

            var splitDate = start.date.split(/[- :]/);
            var startDate = new Date(Date.UTC(splitDate[0], splitDate[1]-1, splitDate[2], splitDate[3], splitDate[4], splitDate[5]));
            var now = new Date();
            var totalSeconds = parseInt((now - startDate) / 1000);

            setInterval(function() {
                ++totalSeconds;
                $('#datetime').text(totalSeconds.toHHMMSS());
            }, 1000);
        } else {
            $('.timetracker').removeClass('active');
        }
    }).on('user.invalid', function(e) {
        window.location = '/auth/signin';
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
