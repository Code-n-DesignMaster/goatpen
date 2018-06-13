$(function() {
    var $platforms = $('#platforms');

    $platforms.find('tbody tr').css('cursor', 'row-resize');

    $platforms.find('tbody').sortable({
        axis: 'y',
        cursor: 'row-resize',
        helper: function (e, ui) {
            ui.children().each(function () {
                $(this).width($(this).width()).height($(this).height());
            });

            return ui;
        },
        opacity: 0.6,
        stop: function () {
            $.post('/platforms/order', {
                id: $platforms.find('tbody').sortable('toArray', {
                    attribute: 'data-id'
                })
            });
        }
    });
});
