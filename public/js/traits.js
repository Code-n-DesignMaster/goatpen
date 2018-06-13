$(function () {
    $('#add-trait').on('click', function (event) {
        event.preventDefault();

        var $trait = $('.trait:last').clone();

        $trait.find('select').val(0);

        $('#traits').append($trait);

        $trait.find('select').focus();
    });
});
