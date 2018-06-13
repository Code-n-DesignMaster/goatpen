$(function () {
    $('#add-tag').on('click', function (event) {
        event.preventDefault();

        var $tag = $('.tag:last').clone();

        $tag.find('select').val(0);

        $('#tags').append($tag);

        $tag.find('input').focus();
    });
});
