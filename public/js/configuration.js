(function ($, window, document) {
    'use strict';

    $(document).on('change', '.two-factor-auth-area-provider-select', function () {
        var
            $this = $(this),
            value = $this.val(),
            $container = $this.closest('.provider-area-block').find('.provider-area-configuration-block'),
            $overlay = $this.closest('.col-group');

        if (value === '0') {
            $container.empty();
        } else {
            $overlay.LoadingOverlay('show');
            $.get(value).success(function (response) {
                $container.html(response);
            }).complete(function () {
                $overlay.LoadingOverlay('hide');
            });
        }
    });

    var $providers = $('.two-factor-auth-area-provider-select');

    $providers.trigger('change');

    $providers.each(function () {
        $(this).select2({
            minimumResultsForSearch: Infinity,
            templateResult: providerOptionTemplate,
            templateSelection: providerOptionTemplate
        });
    });

    function providerOptionTemplate(option) {
        var label = option.text,
                iconUrl = $(option.element).data('icon-url');

        if (iconUrl) {
            return $('<span><img src="' + iconUrl + '" style="margin-right:1em; vertical-align:-17%" width="20" height="20" />' + label + '</span>');
        }

        return label;
    }
})(jQuery, window, document);
