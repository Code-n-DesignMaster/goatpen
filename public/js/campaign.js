$(function () {
    $('#search-go').on('click', function (event) {
        event.preventDefault();

        $('#search-error').remove();
        $('#search-results').empty();

        var influencer = $('#search-influencer').val(),
            platform   = $('#search-platform').val();

        $.ajax({
            url: '/channels.json',
            data: {
                influencer: influencer,
                platform:   platform
            },
            dataType: 'json',
            success: function (data) {
                $.each(data, function () {
                    $('#search-results').append(
                        $('<option>')
                            .val(this.id)
                            .attr({
                                'data-platform': this.platform_id,
                                'data-price': this.price
                            })
                            .text(this.influencer + ' \\ ' + this.channel + ' [' + this.platform + ']')
                    );
                });
            },
            error: function (request) {
                $('#channel-search-modal').find('.modal-body hr').after(
                    '<div class="alert alert-dismissible alert-danger" id="search-error">' +
                        '<button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>' +
                        request.responseJSON.error +
                    '</div>'
                );
            }
        });
    });

    $('#search-add-post, #search-add-stat').on('click', function (event) {
        event.preventDefault();

        var $searchResults = $('#search-results'),
            $item          = $('.' + ($(this).attr('id') === 'search-add-post' ? 'post' : 'stat') + ':last'),
            isHidden       = $item.hasClass('hidden');

        if (! isHidden) {
            $item = $item.clone();
        }

        var count    = parseInt($item.attr('data-count')),
            newCount = (isHidden ? count : (count + 1));

        $item.attr('data-count', newCount);
        $item.find('.count').text(newCount);
        $item.find('.delete').remove();
        $item.find('.channel-description').text($searchResults.find('option:selected').text());
        $item.find('input').val('');
        $item.find('select').val(0);
        $item.find('input:hidden[name$="[channel_id]"]').val($searchResults.val());
        $item.find('input[name$="[posted]"]').prop('required', true);

        $item.find('label[for]').each(function () {
            $(this).attr('for', $(this).attr('for').replace(/_(new_)?\d+$/, '_new_' + newCount));
        });

        $item.find('input[id]').each(function () {
            $(this).attr('id', $(this).attr('id').replace(/_(new_)?\d+$/, '_new_' + newCount));
        });

        $item.find('input[name]').each(function () {
            $(this).attr('name', $(this).attr('name').replace(/\[(new_)?\d+\]/, '[new_' + newCount + ']'));
        });

        $item.find('input[data-name]').each(function () {
            $(this).attr('data-name', $(this).attr('data-name').replace(/\[(new_)?\d+\]/, '[new_' + newCount + ']'));
        });

        var $metrics = $item.find('.metrics');

        $metrics.removeClass('hidden');
        $metrics.find('li[data-platforms]').addClass('hidden').find('input').val('');
        $metrics.find('li[data-platforms*=",' + $searchResults.find('option:selected').attr('data-platform') + ',"]').removeClass('hidden');
        $metrics.find('input[name$="[metric][28]"]').val($searchResults.find('option:selected').attr('data-price'));
        $metrics.find('input[data-name$="[metric][28]"]').val($searchResults.find('option:selected').attr('data-price'));

        $metrics.find('li.hidden input[name]').each(function () {
            $(this).attr('data-name', $(this).attr('name')).removeAttr('name');
        });

        $metrics.find('li:not(.hidden) input[data-name]').each(function () {
            $(this).attr('name', $(this).attr('data-name')).removeAttr('data-name');
        });

        if (isHidden) {
            $item.removeClass('hidden');
        } else {
            if ($(this).attr('id') === 'search-add-post') {
                $('#posts').append($item);
            } else {
                $('#stats').append($item);
            }
        }

        $('#channel-search-modal').modal('hide');
    });
});
