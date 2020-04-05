;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaService");


	/**
	 * Implements interface for works with Vine
	 * @inheritDoc
	 */
	BX.Landing.MediaService.Vine = function(url, settings)
	{
		BX.Landing.MediaService.BaseMediaService.apply(this, arguments);

		this.matcher = BX.Landing.Utils.Matchers.vine;
		this.embedURL = "//vine.co/v/$1/embed/simple";
		this.idPlace = 1;
		this.params = {
			autoplay: 1
		};
	};


	/**
	 * Checks that URL is valid Vine url
	 * @param {string} url
	 * @return {boolean}
	 */
	BX.Landing.MediaService.Vine.validate = function(url)
	{
		return BX.Landing.Utils.Matchers.vine.test(url);
	};


	BX.Landing.MediaService.Vine.prototype = {
		constructor: BX.Landing.MediaService.Vine,
		__proto__: BX.Landing.MediaService.BaseMediaService.prototype,


		/**
		 * Gets settings form
		 * @return {BX.Landing.UI.Form.BaseForm}
		 */
		getSettingsForm: function()
		{
			if (!this.form)
			{
				this.form = new BX.Landing.UI.Form.BaseForm();

				var settings = this.getSettings();

				this.form.addField(
					new BX.Landing.UI.Field.Dropdown({
						title: BX.message("LANDING_CONTENT_URL_MEDIA_AUTOPLAY"),
						selector: "autoplay",
						content: parseInt(settings.autoplay),
						items: [
							{name: BX.message("LANDING_CONTENT_URL_MEDIA_YES"), value: 1},
							{name: BX.message("LANDING_CONTENT_URL_MEDIA_NO"), value: 0}
						]
					})
				);
			}

			return this.form;
		}
	};
})();