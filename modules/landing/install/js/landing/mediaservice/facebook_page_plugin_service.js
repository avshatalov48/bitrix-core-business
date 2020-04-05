;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaService");

	var isString = BX.Landing.Utils.isString;


	/**
	 * Implements interface for works with Facebook pages plugin
	 * @inheritDoc
	 */
	BX.Landing.MediaService.FacebookPages = function(url, settings)
	{
		BX.Landing.MediaService.BaseMediaService.apply(this, arguments);

		this.matcher = BX.Landing.Utils.Matchers.facebookPages;
		this.pageUrl = encodeURIComponent(url.replace(/\/$/, ""));
		this.embedURL = "https://www.facebook.com/plugins/page.php?href="+this.pageUrl;
		this.params = {
			tabs: "timeline",
			width: 340,
			small_header: false,
			adapt_container_width: true,
			hide_cover: false,
			show_facepile: true
		};
	};

	/**
	 * Checks that URL is valid Google Maps Place
	 * @param {string} url
	 * @return {boolean}
	 */
	BX.Landing.MediaService.FacebookPages.validate = function(url)
	{
		if (
			url.endsWith
			&& (
				url.endsWith('facebook.com/')
				|| url.endsWith('facebook.com')
			)
		)
		{
			return false;
		}

		return BX.Landing.Utils.Matchers.facebookPages.test(url);
	};


	BX.Landing.MediaService.FacebookPages.prototype = {
		constructor: BX.Landing.MediaService.FacebookPages,
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
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_FB_PAGE_SMALL_HEADER"),
						selector: "small_header",
						content: settings.small_header === "true" || settings.small_header === true,
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: true},
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: false}
						]
					})
				);

				this.form.addField(
					new BX.Landing.UI.Field.Dropdown({
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_FB_PAGE_COVER"),
						selector: "hide_cover",
						content: settings.hide_cover === "true" || settings.hide_cover === true,
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: false},
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: true}
						]
					})
				);

				this.form.addField(
					new BX.Landing.UI.Field.Dropdown({
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_FB_PAGE_FACES"),
						selector: "show_facepile",
						content: settings.show_facepile === "true" || settings.show_facepile === true,
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: true},
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: false}
						]
					})
				);

				var tabsItems = [
					{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_FB_PAGE_TABS_TIMELINE"), value: "timeline"},
					{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_FB_PAGE_TABS_MESSAGES"), value: "messages"},
					{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_FB_PAGE_TABS_EVENTS"), value: "events"}
				];

				var selectedTabs = isString(settings.tabs) ? settings.tabs.split(", ") : [];

				if (selectedTabs.length)
				{
					tabsItems.forEach(function(item) {
						item.selected = selectedTabs.includes(item.value);
					});
				}

				this.form.addField(
					new BX.Landing.UI.Field.MultiSelect({
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_FB_PAGE_TABS"),
						selector: "tabs",
						value: selectedTabs,
						items: tabsItems
					})
				);
			}

			return this.form;
		}
	};
})();