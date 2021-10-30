;(function () {
	"use strict";

	BX.namespace("BX.Landing.MediaService");

	/**
	 * Implements interface for works with Youtube
	 * @inheritDoc
	 */
	BX.Landing.MediaService.Youtube = function (url, settings) {
		BX.Landing.MediaService.BaseMediaService.apply(this, arguments);

		this.matcher = BX.Landing.Utils.Matchers.youtube;
		this.embedURL = "//www.youtube.com/embed/$4";
		this.previewURL = "//img.youtube.com/vi/$4/sddefault.jpg";
		this.idPlace = 4;
		this.params = {
			autoplay: 0,
			controls: 1,
			loop: 0,
			mute: 0,
			rel: 0,
			start: 0,
			html5: 1
		};
	};

	/**
	 * Checks that URL is valid Youtube url
	 * @param {string} url
	 * @return {boolean}
	 */
	BX.Landing.MediaService.Youtube.validate = function (url) {
		return BX.Landing.Utils.Matchers.youtube.test(url);
	};

	BX.Landing.MediaService.Youtube.prototype = {
		constructor: BX.Landing.MediaService.Youtube,
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

				this.form.addField(
					new BX.Landing.UI.Field.Dropdown({
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_AUTOPLAY"),
						description: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_AUTOPLAY_DESC_NEW"),
						selector: "autoplay",
						content: parseInt(settings.autoplay),
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: 1},
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: 0},
						],
						onChange: onAutoplayChange
					})
				);

				function onAutoplayChange(value) {
					if (value === 1)
					{
						muteField.setValue(1);
						muteField.disable();
						if (!muteField.descriptionText)
						{
							muteField.setDescription(
								BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_SOUND_ALERT")
							);
						}
					}
					else if (value === 0)
					{
						muteField.removeDescription();
						muteField.enable();
						muteField.setValue(0);
					}
				}

				var muteField = new BX.Landing.UI.Field.Dropdown({
					title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_SOUND"),
					selector: "mute",
					content: parseInt(settings.mute),
					items: [
						{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: 0},
						{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: 1}
					]
				});
				this.form.addField(muteField);
				onAutoplayChange(parseInt(settings.autoplay));

				this.form.addField(
					new BX.Landing.UI.Field.Dropdown({
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_CONTROLS"),
						selector: "controls",
						content: parseInt(settings.controls),
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: 1},
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: 0}
						]
					})
				);

				this.form.addField(
					new BX.Landing.UI.Field.Dropdown({
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_LOOP"),
						selector: "loop",
						content: parseInt(settings.loop),
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: 1},
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: 0}
						]
					})
				);

				var start = new BX.Landing.UI.Field.Unit({
					title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_START"),
					selector: "start",
					unit: BX.Landing.Loc.getMessage("LANDING_CONTENT_MEDIA_SECONDS_SHORT"),
					content: parseInt(settings.start),
					placeholder: "40"
				});
				this.form.addField(start);
			}

			return this.form;
		},
	};
})();