;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaService");


	/**
	 * Implements interface for works with Google Maps Search
	 * @inheritDoc
	 */
	BX.Landing.MediaService.GoogleMapsSearch = function(url, settings)
	{
		BX.Landing.MediaService.BaseMediaService.apply(this, arguments);

		this.matcher = BX.Landing.Utils.Matchers.googleMapsSearch;
		this.embedURL = function(matched) {
			var query = "";

			if (matched[5])
			{
				query = matched[5].replace("query=", "q=").replace("api=1", "");
			}
			else if (matched[9])
			{
				query = matched[9];
			}

			return "//maps.google." + matched[2] + "/maps?q=" + query + "&output=embed";
		};
	};


	/**
	 * Checks that URL is valid Google Maps Search
	 * @param {string} url
	 * @return {boolean}
	 */
	BX.Landing.MediaService.GoogleMapsSearch.validate = function(url)
	{
		return BX.Landing.Utils.Matchers.googleMapsSearch.test(url);
	};


	BX.Landing.MediaService.GoogleMapsSearch.prototype = {
		constructor: BX.Landing.MediaService.GoogleMapsSearch,
		__proto__: BX.Landing.MediaService.BaseMediaService.prototype,

		/**
		 * Gets URL preview HTMLElement
		 * @return {Promise<HTMLElement>}
		 */
		getURLPreviewElement: function()
		{
			return new Promise(function(resolve) {
				var title = "<span class=\"fa fa-map\"></span>&nbsp;Google Maps";

				setTimeout(function() {
					resolve(
						BX.create("div", {
							props: {className: "landing-ui-mediaservice-url-preview landing-ui-mediaservice-url-preview-map"},
							children: [
								BX.create("div", {
									props: {className: "landing-ui-mediaservice-url-preview-text"},
									children: [
										BX.create("div", {
											props: {className: "landing-ui-mediaservice-url-preview-text-title"},
											html: title
										})
									]
								})
							]
						})
					);
				}, 400);
			});
		}
	};
})();