var currency = new Intl.NumberFormat('en-GB', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
});

var number = new Intl.NumberFormat('en-GB', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
});

$(function () {
    var $budget = $('#budget');

    $('input[type="number"]').on('input', function () {
        window.warnBeforeUnload = true;

        // Budget
        var $row   = $(this).parents('tr'),
            $price = $row.find('.price'),
            $total = $row.find('.total'),
            price  = parseFloat($price.attr('data-amount'));

        if (! isNaN(price)) {
            var quantity  = parseInt($(this).val() || 0),
                total     = parseFloat($total.attr('data-amount')),
                remaining = (parseFloat($budget.attr('data-raw')) - ((price * quantity) - total));

            total = (price * quantity);

            $total.text(currency.format(total));
            $total.attr('data-amount', total);

            $budget.text(currency.format(remaining));
            $budget.attr('data-raw', remaining);

            if (remaining < 0) {
                $budget.addClass('text-danger');
            } else {
                $budget.removeClass('text-danger');
            }
        }

        // Deliverables
        $('.deliverable').each(function () {
            var id        = $(this).attr('data-id'),
                remaining = parseInt($(this).attr('data-original'));

            $('tr[data-metric-' + id + ']').each(function () {
                remaining -= (parseInt($(this).find('input[type="number"]').val() || 0) * parseFloat($(this).attr('data-metric-' + id)));
            });

            $(this).text(number.format(remaining));

            if (remaining < 0) {
                $(this).addClass('text-success');
            } else {
                $(this).removeClass('text-success');
            }
        });
    });

    $('form').on('submit', function () {
        window.warnBeforeUnload = false;
    });

    window.onbeforeunload = function () {
        if (window.warnBeforeUnload === true) {
            return '';
        }
    };
});
