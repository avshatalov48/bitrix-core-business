;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaService");

	var isArray = BX.Landing.Utils.isArray;

	/**
	 * Implements base interface for works with media services
	 * @param {string} url
	 * @param {object} [settings]
	 *
	 * @property {RegExp} matcher - Service URl matcher. Should be implements in child
	 * @property {string|function} embedURL - Embed URL mask. You can use group ids as placeholders $0, $1, ... Should be implements in child
	 * @property {int} idPlace - Group id from matcher. Should be implements in child
	 * @property {object} params - Available service URL params. Should be implements in child
	 *
	 * @throws {TypeError}
	 *
	 * @constructor
	 */
	BX.Landing.MediaService.BaseMediaService = function(url, settings)
	{
		if (typeof url !== "string")
		{
			throw new TypeError("URL not a string");
		}

		this.url = encodeURI(url);
		this.settings = settings || {};
		this.matcher = new RegExp("//");
		this.embedURL = "";
		this.idPlace = 0;
		this.type = "iframe";
		this.params = {};
		this.isDataLoaded = true;
		this.isBgVideoMode = false;
		this.isVertical = false;
	};

	BX.Landing.MediaService.BaseMediaService.prototype = {
		/**
		 * Gets media id
		 * @return {?(string|number)}
		 */
		getMediaId: function()
		{
			return this.url.match(this.matcher)[this.idPlace];
		},

		/**
		 * Gets user settings from settings form
		 * @return {object}
		 */
		getUserSettings: function()
		{
			var result = {};
			var form = this.getSettingsForm();

			if (form)
			{
				result = form.fields.fetchValues();

				Object.keys(result).forEach(function(key) {
					if (isArray(result[key]))
					{
						result[key] = encodeURIComponent(result[key].join(", "));
					}
				});
			}

			return result;
		},


		getSettings: function()
		{
			return BX.util.objectMerge(
				BX.clone(this.params),
				BX.Landing.Utils.getQueryParams(this.url),
				BX.clone(this.settings)
			);
		},


		/**
		 * Gets embed URL
		 * @return {string}
		 */
		getEmbedURL: function()
		{
			var result = this.embedURL;
			var matchedUrl = this.url.match(this.matcher);

			if (typeof this.embedURL === "string")
			{
				[].slice.call(matchedUrl)
					.forEach(function(value, index) {
						result = result.replace(new RegExp("\\$" + index, "g"), value);
					});

				var params = BX.util.objectMerge(
					this.getSettings(),
					this.getUserSettings()
				);

				result = BX.util.add_url_param(result, params);
			}

			if (typeof this.embedURL === "function")
			{
				result = this.embedURL(matchedUrl);
			}

			return result;
		},


		/**
		 * Try create image preview, if service can
		 */
		getEmbedPreview: function ()
		{
			var result = this.previewURL;
			var matchedUrl = this.url.match(this.matcher);

			if (typeof this.previewURL === "string")
			{
				[].slice.call(matchedUrl).forEach(function (value, index)
				{
					result = result.replace(new RegExp("\\$" + index, "g"), value);
				});

				return result;
			}
			else if (typeof this.previewURL === "function")
			{
				return this.previewURL(matchedUrl);
			}

			return false;
		},


		/**
		 * Gets embed Element
		 * @return {HTMLIFrameElement}
		 */
		getEmbedElement: function()
		{
			return BX.create("iframe", {
				attrs: {
					src: this.getEmbedURL(),
					frameborder: "0",
					gesture: "media",
					allow: "encrypted-media",
					allowfullscreen: true
				}
			});
		},


		/**
		 * Gets URL preview object
		 * @return {Promise<Object, Object>}
		 */
		getURLPreview: function()
		{
			return BX.Landing.Utils.getURLPreview(this.url);
		},


		/**
		 * Gets URL preview HTMLElement
		 * @return {Promise<HTMLElement>}
		 */
		getURLPreviewElement: function()
		{
			return this.getURLPreview()
				.then(function(preview) {
					var description = preview.DESCRIPTION;
					var title = preview.TITLE;

					if ((title.length + description.length) > 120)
					{
						if (title.length > 120)
						{
							description = "";
							title = title.slice(0, 120) + "..."
						}
						else if ((title.length + description.length) > 120)
						{
							description = description.slice(0, description.length - ((title.length + description.length) - 120)) + "...";
						}
					}

					return BX.create("div", {
						props: {className: "landing-ui-mediaservice-url-preview"},
						children: [
							BX.create("div", {
								props: {className: "landing-ui-mediaservice-url-preview-image"},
								attrs: {
									style: "background-image: url(\""+preview.IMAGE+"\")"
								}
							}),
							BX.create("div", {
								props: {className: "landing-ui-mediaservice-url-preview-text"},
								children: [
									BX.create("div", {
										props: {className: "landing-ui-mediaservice-url-preview-text-title"},
										text: title
									}),
									BX.create("div", {
										props: {className: "landing-ui-mediaservice-url-preview-text-description"},
										text: description
									})
								]
							})
						]
					})
				}.bind(this));
		},


		/**
		 * Gets settings form
		 * @return {BX.Landing.UI.Form.BaseForm}
		 */
		getSettingsForm: function()
		{
			return null;
		},

		/**
		 * Set true if current service used for BG video
		 * @param {bool} value
		 */
		setBgVideoMode(value)
		{
			this.isBgVideoMode = !!value;
		},
	}
})();