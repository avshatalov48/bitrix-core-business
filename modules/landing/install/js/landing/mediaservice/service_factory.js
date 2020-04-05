;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaService");


	/**
	 * Implements services factory interface
	 * @constructor
	 */
	BX.Landing.MediaService.Factory = function() {};

	BX.Landing.MediaService.Factory.prototype = {

		/**
		 * Available services
		 */
		services: {
			youtube: "BX.Landing.MediaService.Youtube",
			vimeo: "BX.Landing.MediaService.Vimeo",
			vine: "BX.Landing.MediaService.Vine",
			instagram: "BX.Landing.MediaService.Instagram",
			googleMapsSearch: "BX.Landing.MediaService.GoogleMapsSearch",
			googleMapsPlace: "BX.Landing.MediaService.GoogleMapsPlace"
		},


		/**
		 * Creates service
		 * @param {string} url - Service url. ex. https://www.youtube.com/watch?v=ukdbnzCNN2Y
		 * @param {object} [options] - Service url params. See official documentation for each service.
		 *
		 * @see Youtube https://developers.google.com/youtube/player_parameters
		 * @see Vimeo https://developer.vimeo.com/apis/oembed
		 *
		 * Vine, Instagram, Google Maps no supported URL params
		 *
		 * @return {*}
		 */
		create: function(url, options)
		{
			var result = null;

			for (var provider in this.services)
			{
				if (this.services.hasOwnProperty(provider) &&
					BX.getClass(this.services[provider])["validate"](url))
				{
					var service = BX.getClass(this.services[provider]);
					result = new service(url, options);
					break;
				}
			}

			return result;
		}
	}
})();