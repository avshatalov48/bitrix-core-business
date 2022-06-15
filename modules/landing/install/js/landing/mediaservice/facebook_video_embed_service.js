;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaService");

	/**
	 * Implements interface for works with Facebook pages plugin
	 * @inheritDoc
	 */
	BX.Landing.MediaService.FacebookVideos = function(url, settings)
	{
		BX.Landing.MediaService.BaseMediaService.apply(this, arguments);

		this.matcher = BX.Landing.Utils.Matchers.facebookVideos;
		this.pageUrl = encodeURIComponent(url.replace(/\/$/, ""));
		this.embedURL = "https://www.facebook.com/plugins/video.php?href="+this.pageUrl;
		this.params = {
			width: 500,
			show_text: 0
		};
	};

	/**
	 * Checks that URL is valid Google Maps Place
	 * @param {string} url
	 * @return {boolean}
	 */
	BX.Landing.MediaService.FacebookVideos.validate = function(url)
	{
		return BX.Landing.Utils.Matchers.facebookVideos.test(url);
	};


	BX.Landing.MediaService.FacebookVideos.prototype = {
		constructor: BX.Landing.MediaService.FacebookVideos,
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
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_FB_POST_FULL"),
						selector: "show_text",
						content: !isNaN(parseInt(settings.show_text)) ? parseInt(settings.show_text) : 0,
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: 1},
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: 0}
						]
					})
				);
			}

			return this.form;
		}
	};
})();