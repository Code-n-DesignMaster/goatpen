$(function () {
    $('#add-metric').on('click', function (event) {
        event.preventDefault();

        var $metric = $('.metric:last').clone();
        var count = parseInt($metric.attr('data-count'));
        var newCount = (count + 1);

        $metric.attr('data-count', newCount);
        $metric.find('input').val('');
        $metric.find('select').val(0);

        $metric.find('input, select').each(function () {
            $(this).attr({
                name: $(this).attr('name').replace(/\[\d+\]/, '[' + newCount + ']')
            });
        });

        $('#metrics').append($metric);
    });
});
