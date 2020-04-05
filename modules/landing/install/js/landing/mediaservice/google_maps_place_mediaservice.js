;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaService");


	/**
	 * Implements interface for works with Google Maps Place
	 * @inheritDoc
	 */
	BX.Landing.MediaService.GoogleMapsPlace = function(url, settings)
	{
		BX.Landing.MediaService.GoogleMapsSearch.apply(this, arguments);

		this.matcher = BX.Landing.Utils.Matchers.googleMapsPlace;
		this.embedURL = function(matched) {
			var result = "//maps.google."+matched[2]+"/?ll="+(matched[9] ?
				matched[9]+"&z="+Math.floor(matched[10])+(matched[12] ? matched[12].replace(/^\//, "&") : "") :
				matched[12])+"&output="+(matched[12] && matched[12].indexOf("layer=c") > 0 ? "svembed" : "embed");

			if (matched[8])
			{
				result = "//maps.google." + matched[2] + "/maps?q=" + decodeURI(decodeURI(matched[8])) + "&output=embed"
			}

			return result;

		};
	};


	/**
	 * Checks that URL is valid Google Maps Place
	 * @param {string} url
	 * @return {boolean}
	 */
	BX.Landing.MediaService.GoogleMapsPlace.validate = function(url)
	{
		return BX.Landing.Utils.Matchers.googleMapsPlace.test(url);
	};


	BX.Landing.MediaService.GoogleMapsPlace.prototype = {
		constructor: BX.Landing.MediaService.GoogleMapsPlace,
		__proto__: BX.Landing.MediaService.GoogleMapsSearch.prototype
	};
})();