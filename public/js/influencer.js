$(function () {
    $('#add-channel').on('click', function (event) {
        event.preventDefault();

        var $channel = $('.channel:last').clone();
        var count = parseInt($channel.attr('data-count'));
        var newCount = (count + 1);

        $channel.attr('data-count', newCount);
        $channel.find('.count').text(newCount);
        $channel.find('.delete').remove();
        $channel.find('input').val('');
        $channel.find('select').val(0);

        $channel.find('label[for]').each(function () {
            $(this).attr('for', $(this).attr('for').replace(/_(new_)?\d+$/, '_new_' + newCount));
        });

        $channel.find('input[id], select[id]').each(function () {
            $(this).attr('id', $(this).attr('id').replace(/_(new_)?\d+$/, '_new_' + newCount));
        });

        $channel.find('input[name], select[name]').each(function () {
            $(this).attr('name', $(this).attr('name').replace(/\[(new_)?\d+\]/, '[new_' + newCount + ']'));
        });

        $channel.find('.demographics .form-group:not(:last)').remove();

        $('#channels').append($channel);

        $channel.find('select[name$="[platform_id]"]').trigger('change');
    });

    $(document).on('change', '.channel select[name$="[platform_id]"]', function () {
        var platform_id = parseInt($(this).val());
        var $metrics    = $(this).parents('.channel').find('.metrics');

        if (platform_id === 0) {
            $metrics.addClass('hidden');
        } else {
            $metrics.removeClass('hidden');
            $metrics.find('li[data-platforms]').addClass('hidden').find('input').val('');
            $metrics.find('li[data-platforms*=",' + platform_id + ',"]').removeClass('hidden');
        }
    });
});
