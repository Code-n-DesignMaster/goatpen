$(function () {
    $('#channels').on('click', '#add-demographic', function (event) {
        event.preventDefault();

        var $demographics = $(this).parents('.form-group').find('.demographics'),
            $demographic  = $demographics.find('.demographic:last').clone();

        $demographic.find('select, input').val('');

        $demographics.append($demographic);

        $demographic.find('select').focus();
    });
});
