;(function() {
	"use strict";

	BX.ready(function() {
		if (BX.Landing.getMode() !== "edit")
		{
			// Init template Fancybox wrapper
			$.HSCore.components.HSPopup.init(".js-fancybox");

			// Init Fancybox for link without embed URL
			$("[target=\"_popup\"]:not([data-url])").fancybox({type: "iframe"});

			// Init Fancybox for link with embed URL
			$("[target=\"_popup\"][data-url]").on("click", function(event) {
				event.preventDefault();

				$.fancybox.open({
					src: this.dataset.url,
					type: "iframe"
				}, {
					iframe: {
						scrolling : "auto"
					}
				});
			});
		}
	});

})();