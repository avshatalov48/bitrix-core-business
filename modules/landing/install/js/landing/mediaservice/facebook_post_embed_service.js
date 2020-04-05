;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaService");

	/**
	 * Implements interface for works with Facebook pages plugin
	 * @inheritDoc
	 */
	BX.Landing.MediaService.FacebookPosts = function(url, settings)
	{
		BX.Landing.MediaService.BaseMediaService.apply(this, arguments);

		this.matcher = BX.Landing.Utils.Matchers.facebookPosts;
		this.pageUrl = encodeURIComponent(url.replace(/\/$/, ""));
		this.embedURL = "https://www.facebook.com/plugins/post.php?href="+this.pageUrl;
		this.params = {
			width: 500,
			show_text: true
		};
	};

	/**
	 * Checks that URL is valid Google Maps Place
	 * @param {string} url
	 * @return {boolean}
	 */
	BX.Landing.MediaService.FacebookPosts.validate = function(url)
	{
		return BX.Landing.Utils.Matchers.facebookPosts.test(url);
	};


	BX.Landing.MediaService.FacebookPosts.prototype = {
		constructor: BX.Landing.MediaService.FacebookPosts,
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
						content: settings.show_text === "true" || settings.show_text === true,
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: true},
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: false}
						]
					})
				);
			}

			return this.form;
		}
	};
})();