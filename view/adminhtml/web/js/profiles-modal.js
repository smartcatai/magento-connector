define([
        "jquery", "Magento_Ui/js/modal/modal"
    ], function($){
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Select profile which will be used for localize',
            buttons: [{
                text: $.mage.__('Localize All'),
                class: '',
                click: function () {
                    $("#modal-form").submit();
                }
            }]
        };

        var ProfilesModal = {
            initModal: function(config, element) {
                $target = $(config.target);
                $target.modal(options);
                $element = $(element);
                $element.click(function() {
                    $target.modal('openModal');
                });
            }
        };

        return {
            'profiles-modal': ProfilesModal.initModal
        };
    }
);