;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");


	/**
	 * Implements interface for works with image panel
	 *
	 * @extends {BX.Landing.UI.Panel.Content}
	 *
	 * @param {string} id
	 *
	 * @constructor
	 */
	BX.Landing.UI.Panel.Image = function(id)
	{
		BX.Landing.UI.Panel.Content.apply(this, arguments);
		this.layout.classList.add("landing-ui-panel-image");
		this.onChangeHandler = (function() {});
		this.headerButtonsField = this.createHeaderButtons();
		this.cards = new BX.Landing.Collection.BaseCollection();

		this.overlay.classList.add("landing-ui-panel-image");
		this.title.appendChild(this.headerButtonsField.layout);
		document.body.appendChild(this.layout);
		this.title.innerText = BX.message("LANDING_IMAGE_LIBRARY_PANEL_TITLE");
	};


	/**
	 * Gets instance
	 * @returns {BX.Landing.UI.Panel.Image}
	 */
	BX.Landing.UI.Panel.Image.getInstance = function()
	{
		if (!BX.Landing.UI.Panel.Image.instance)
		{
			BX.Landing.UI.Panel.Image.instance = new BX.Landing.UI.Panel.Image("image_panel");
		}

		return BX.Landing.UI.Panel.Image.instance;
	};


	/**
	 * Stores instance
	 * @type {BX.Landing.UI.Panel.Image}
	 */
	BX.Landing.UI.Panel.Image.instance = null;


	BX.Landing.UI.Panel.Image.prototype = {
		constructor: BX.Landing.UI.Panel.Image,
		__proto__: BX.Landing.UI.Panel.Content.prototype,


		onChangeView: function(value)
		{
			if (value === "unsplash")
			{
				this.showUnsplash();
			}

			if (value === "google")
			{
				this.showGoogle();
			}

			if (value === "disk")
			{
				this.showUploader();
			}
		},


		/**
		 * Shows uploader
		 */
		showUploader: function()
		{
			var uploader = this.cards.get("uploader");

			if (!uploader)
			{
				uploader = new BX.Landing.UI.Card.Uploader({
					id: "uploader"
				});
				this.appendCard(uploader);
				this.cards.add(uploader);
			}

			this.hideAll();
			uploader.show();
		},


		/**
		 * Shows google images
		 */
		showGoogle: function()
		{
			var google = this.cards.get("google");

			if (!google)
			{
				google = new BX.Landing.UI.Card.Google({
					id: "google",
					searchLabel: BX.message("GOOGLE_SEARCH_FIELD_LABEL"),
					searchTips: [
						{name: "Nature", value: "Nature"},
						{name: "People", value: "People"},
						{name: "Buildings", value: "Buildings"},
						{name: "Sunset", value: "Sunset"}
					],
					description: BX.message("LANDING_IMAGE_GOOGLE_DESCRIPTION"),
					onChange: this.onChange.bind(this),
					params: this.uploadParams
				});
				this.appendCard(google);
				this.cards.add(google);
			}

			this.hideAll();
			google.showPopular();
			google.show();
		},


		/**
		 * Shows unsplash library
		 */
		showUnsplash: function()
		{
			var unsplash = this.cards.get("unsplash");

			if (!unsplash)
			{
				unsplash = new BX.Landing.UI.Card.Unsplash({
					id: "unsplash",
					searchLabel: BX.message("UNSPLASH_SEARCH_FIELD_LABEL"),
					searchTips: [
						{name: "Nature", value: "Nature"},
						{name: "People", value: "People"},
						{name: "Buildings", value: "Buildings"},
						{name: "Sunset", value: "Sunset"}
					],
					description: BX.message("LANDING_IMAGE_UNSPLASH_DESCRIPTION"),
					onChange: this.onChange.bind(this)
				});
				this.appendCard(unsplash);
				this.cards.add(unsplash);
			}

			this.hideAll();
			unsplash.show();
		},


		/**
		 * Hides all
		 */
		hideAll: function()
		{
			this.cards.forEach(function(card) {
				card.layout.hidden = true;
			});
		},


		/**
		 * Creates header button
		 * @returns {BX.Landing.UI.Field.ButtonGroup}
		 */
		createHeaderButtons: function()
		{
			return new BX.Landing.UI.Field.ButtonGroup({
				items: [
					{name: BX.message("LANDING_CONTENT_EDIT_IMAGE_HEADER_BUTTON_UNSPLASH"), value: "unsplash", active: true},
					{name: BX.message("LANDING_CONTENT_EDIT_IMAGE_HEADER_BUTTON_GOOGLE"), value: "google"},
					{name: BX.message("LANDING_CONTENT_EDIT_IMAGE_HEADER_BUTTON_FROM_DISK"), value: "disk"}
				],
				onChange: this.onChangeView.bind(this)
			});
		},


		/**
		 * Shows panel
		 * @param {string} view
		 * @param {?object} [params]
		 * @param {BX.Loader} loader
		 * @param {object} uploadParams
		 * @returns {Promise}
		 */
		show: function(view, params, loader, uploadParams)
		{
			this.uploadParams = uploadParams || {};
			this.externalLoader = loader;
			this.params = params;
			this.onChangeView(view);
			BX.Landing.UI.Panel.Content.prototype.show.call(this);
			return new Promise(function(resolve) {
				this.promiseResolve = resolve;
			}.bind(this));
		},


		/**
		 * Hides panel
		 */
		hide: function()
		{
			this.params = null;
			BX.Landing.UI.Panel.Content.prototype.hide.call(this);
		},


		/**
		 * Handles on change event
		 * @param {object} value
		 */
		onChange: function(value)
		{
			this.externalLoader.show();
			BX.Landing.Utils.urlToBlob(value.link)
				.then(function(/* File|Blob */blob) {
					blob.lastModifiedDate = new Date();
					blob.name = value.name;
					return blob;
				})
				.then(this.promiseResolve.bind(this));

			this.hide();
		}
	}
})();