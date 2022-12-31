;(function () {
	"use strict";

	BX.namespace("BX.Landing.MediaService");

	/**
	 * Implements interface for works with Rutube
	 * @inheritDoc
	 */
	BX.Landing.MediaService.Rutube = function (url, settings) {
		BX.Landing.MediaService.BaseMediaService.apply(this, arguments);

		this.matcher = BX.Landing.Utils.Matchers.rutube;
		this.embedURL = "//rutube.ru/play/embed/$2";
		this.idPlace = 2;
		this.params = {
			t: 0,
		};
	};

	/**
	 * Checks that URL is valid Rutube url
	 * @param {string} url
	 * @return {boolean}
	 */
	BX.Landing.MediaService.Rutube.validate = function (url) {
		return BX.Landing.Utils.Matchers.rutube.test(url);
	};

	BX.Landing.MediaService.Rutube.prototype = {
		constructor: BX.Landing.MediaService.Rutube,
		__proto__: BX.Landing.MediaService.BaseMediaService.prototype,

		/**
		 * Gets settings form
		 * @return {BX.Landing.UI.Form.BaseForm}
		 */
		getSettingsForm: function () {
			if (!this.form)
			{
				this.form = new BX.Landing.UI.Form.BaseForm();

				var settings = this.getSettings();

				var start = new BX.Landing.UI.Field.Unit({
					title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_START"),
					unit: BX.Landing.Loc.getMessage("LANDING_CONTENT_MEDIA_SECONDS_SHORT"),
					selector: "t",
					content: parseInt(settings.t),
					placeholder: "40"
				});
				this.form.addField(start);
			}

			return this.form;
		},
	};
})();