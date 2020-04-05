;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaService");


	/**
	 * Implements interface for works with Instagram
	 * @inheritDoc
	 */
	BX.Landing.MediaService.Instagram = function(url, settings)
	{
		BX.Landing.MediaService.BaseMediaService.apply(this, arguments);

		this.matcher = BX.Landing.Utils.Matchers.instagram;
		this.embedURL = "//instagram.com/p/$2/embed/captioned";
		this.idPlace = 1;
		this.params = {};
	};


	/**
	 * Checks that URL is valid Instagram url
	 * @param {string} url
	 * @return {boolean}
	 */
	BX.Landing.MediaService.Instagram.validate = function(url)
	{
		return BX.Landing.Utils.Matchers.instagram.test(url);
	};


	BX.Landing.MediaService.Instagram.prototype = {
		constructor: BX.Landing.MediaService.Instagram,
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
				this.form.addField(
					new BX.Landing.UI.Field.Dropdown({
						title: BX.message("LANDING_CONTENT_URL_MEDIA_CAPTIONED"),
						selector: "captioned",
						items: [
							{name: BX.message("LANDING_CONTENT_URL_MEDIA_YES"), value: 1},
							{name: BX.message("LANDING_CONTENT_URL_MEDIA_NO"), value: 0}
						],
						onChange: function(value)
						{
							if (value === 1)
							{
								if (this.embedURL.indexOf("captioned") === -1)
								{
									this.embedURL = this.embedURL + "captioned";
								}
							}
							else
							{
								this.embedURL = this.embedURL.replace("captioned", "");
							}
						}.bind(this)
					})
				);
			}

			return this.form;
		}
	};
})();