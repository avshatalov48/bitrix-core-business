;(function() {
	"use strict";

	BX.ready(function() {
		{
			// Init Fancybox for link without embed URL
			$("[target=\"_popup\"]:not([data-url])").fancybox({type: "iframe"});

			// Init Fancybox for link with embed URL
			$("[target=\"_popup\"][data-url]").on("click", function(event) {
				event.preventDefault();

				$.fancybox.open({
					src: this.dataset.url,
					type: "iframe",
					afterShow: afterFancyboxIframeShow
				},
				{
					iframe: {
						scrolling : "auto"
					}
				});
			});

			$("[data-pseudo-url*='_popup']").on("click", function(event) {
				event.preventDefault();

				var linkOptions = BX.Landing.Utils.data(this, "data-pseudo-url");

				if (linkOptions.href && linkOptions.enabled)
				{
					var src = linkOptions.href;

					if (BX.type.isPlainObject(linkOptions.attrs) &&
						linkOptions.attrs['data-url'])
					{
						src = linkOptions.attrs['data-url'];
					}
					$.fancybox.open({
							src: src,
							type: "iframe",
							afterShow: afterFancyboxIframeShow
						},
						{
							iframe: {
								scrolling : "auto"
							}
						});
				}

			});
		}

		function afterFancyboxIframeShow(instance, current)
		{
			var iframe = current.$slide.find("iframe")[0];
			void BX.Landing.MediaPlayer.Factory.create(iframe);
		}
	});

})();