$(function () {
    var $container = $('#comments-container'),
        relation   = $container.data('relation'),
        id         = $container.data('id');

    $container.comments({
        textareaRows: 1,
        enableReplying: false,
        enableEditing: false,
        enableUpvoting: false,
        enableDeleting: false,
        enableNavigation: false,
        fieldMappings: {
            content: 'comment',
            fullname: 'name',
            createdByCurrentUser: 'is_current_user'
        },
        getComments: function (success, error) {
            $.ajax({
                type: 'get',
                url: '/' + relation + '/' + id + '/comments.json',
                success: function (data) {
                    success(data);
                },
                error: error
            });
        },
        postComment: function (data, success, error) {
            $.ajax({
                type: 'post',
                url: '/' + relation + '/' + id + '/comments.json',
                data: data,
                success: function (data) {
                    success(data);
                },
                error: error
            });
        },
        timeFormatter: function (time) {
            var date    = new Date(time),
                month   = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                hours   = date.getHours(),
                minutes = date.getMinutes();

            if (hours < 10) {
                hours = '0' + hours;
            }

            if (minutes < 10) {
                minutes = '0' + minutes;
            }

            return date.getDate() + ' ' + month[date.getMonth()] + ' ' + date.getFullYear() + ' ' + hours + ':' + minutes;
        }
    });
});
