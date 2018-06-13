$(function () {
    $('.add-to-campaign').on('click', function (event) {
        event.preventDefault();

        var self = $(this);

        var jqxhr = $.post('/campaigns/' + $(this).attr('data-campaign') + '/builder/add-channel', {
            channel_id: $(this).attr('data-channel')
        });

        jqxhr.fail(function () {
            window.alert('There was an error adding the channel to the campaign');
        });

        jqxhr.done(function () {
            self.parents('tr').addClass('success');
            self.tooltip('hide');
            self.remove();
        });
    });
});
