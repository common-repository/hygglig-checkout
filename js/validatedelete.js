/**
 * Validate delete
 * Javascript code
 * by Oliver Bredenberg, @Olibre
 */

(function($) {
    "use strict";
    var ValidateDelete = {
        init: function () {
			$('.submitdelete').click(function() {
				if(confirm("Om du raderar din order kommer du även makulera/kreditera den i Hygglig!")) {
					return true;
				} else {
					$(this).removeClass('button-primary-disabled');
					return false;
				}
			});
        },
    };
    $(function() {
        ValidateDelete.init();
    });
}(jQuery));