;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaService");


	/**
	 * Implements interface for works with Vimeo
	 * @inheritDoc
	 */
	BX.Landing.MediaService.Vimeo = function(url, settings)
	{
		BX.Landing.MediaService.BaseMediaService.apply(this, arguments);

		this.matcher = BX.Landing.Utils.Matchers.vimeo;
		this.embedURL = "//player.vimeo.com/video/$2";
		this.idPlace = 3;
		this.params = {
			autoplay: 1,
			loop: 0,
			hd: 1,
			muted: 0,
			background: 0,
			show_title: 1,
			show_byline: 1,
			show_portrait: 0,
			fullscreen: 1,
			api: 1
		};
	};


	/**
	 * Checks that URL is valid Vimeo url
	 * @param {string} url
	 * @return {boolean}
	 */
	BX.Landing.MediaService.Vimeo.validate = function(url)
	{
		return BX.Landing.Utils.Matchers.vimeo.test(url);
	};


	BX.Landing.MediaService.Vimeo.prototype = {
		constructor: BX.Landing.MediaService.Vimeo,
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
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_AUTOPLAY"),
						selector: "autoplay",
						content: parseInt(settings.autoplay),
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: 1},
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: 0}
						]
					})
				);

				this.form.addField(
					new BX.Landing.UI.Field.Dropdown({
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_CONTROLS"),
						selector: "background",
						content: parseInt(settings.background),
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: 0},
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: 1}
						]
					})
				);

				this.form.addField(
					new BX.Landing.UI.Field.Dropdown({
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_QUALITY"),
						selector: "quality",
						content: settings.quality,
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_QUALITY_AUTO"), value: ""},
							{name: "4k (" + BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_QUALITY_HIGH") + ")", value: "4k"},
							{name: "2k", value: "2k"},
							{name: "1080p", value: "1080p"},
							{name: "780p", value: "780p"},
							{name: "360p ("+BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_QUALITY_LOW")+")", value: "360p"}
						]
					})
				);

				this.form.addField(
					new BX.Landing.UI.Field.Dropdown({
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_LOOP"),
						selector: "loop",
						content: settings.loop,
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: 1},
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: 0}
						]
					})
				);

				this.form.addField(
					new BX.Landing.UI.Field.Dropdown({
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_SOUND"),
						selector: "muted",
						content: settings.muted,
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: 0},
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: 1}
						]
					})
				);
			}

			return this.form;
		}
	};
})();