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
    }).on('show.alert', function(e, type, msg) {
        $('.alert-javascript').addClass('in').removeClass('alert-info alert-success alert-danger alert-warning').addClass(type).html(msg);
        setTimeout(function() {
            $('.alert-javascript').removeClass('in');
        }, 3000);
    }).on('show.alert.modal', function(e, type, msg) {
        $('.alert-modal').addClass('in').removeClass('alert-info alert-success alert-danger alert-warning').addClass(type).html(msg);
        setTimeout(function() {
            $('.alert-modal').removeClass('in');
        }, 3000);
    }).on('close.bs.modal', function() {
        $('.modal').modal('hide');
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
