;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");

	var create = BX.Landing.Utils.create;
	var append = BX.Landing.Utils.append;
	var prepend = BX.Landing.Utils.prepend;
	var remove = BX.Landing.Utils.remove;
	var create = BX.Landing.Utils.create;

	/**
	 * Adapts item object
	 * @param {object} item
	 * @param {BX.Landing.UI.Card.Google} google
	 * @return {{image, credit: {}, onClick}}
	 */
	function itemAdapter(item, google)
	{
		return {
			image: item.link,
			credit: {},
			dimensions: {
				width: item.image.width,
				height: item.image.height
			},
			onClick: google.onPictureChange.bind(google, item.link)
		}
	}


	/**
	 * Adapts items
	 * @param {object} items
	 * @param {BX.Landing.UI.Card.Google} google
	 * @return {object[]}
	 */
	function itemsAdapter(items, google)
	{
		return items.map(function(item) {
			return itemAdapter(item, google)
		});
	}


	/**
	 * Implements interface for works with unsplash
	 * @extends {BX.Landing.UI.Card.Library}
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Card.Google = function(data)
	{
		BX.Landing.UI.Card.Library.apply(this, arguments);
		this.page = 1;
		this.query = "";
		this.loader = new BX.Loader({target: this.body, offset: {top: '12%'}});
		this.client = BX.Landing.Client.Google.getInstance();
		this.onSearchWithDebounce = BX.debounce(this.onSearchWithDebounce, 1000, this);
		this.showPopular();
	};

	BX.Landing.UI.Card.Google.prototype = {
		constructor: BX.Landing.UI.Card.Google,
		__proto__: BX.Landing.UI.Card.Library.prototype,


		onLoadMore: function()
		{
			this.appendSearch();
		},

		appendSearch: function()
		{
			this.page += 1;
			this.state = "search";

			this.showBottomLoader();
			this.hideLoadMore();
			this.hideError();

			this.client
				.search(this.query, this.page).then(function(res) {
					this.hideBottomLoader();
					this.showLoadMore();
					this.renderItems(itemsAdapter(res, this));
				}.bind(this))
				.catch(this.showError.bind(this));
		},


		showPopular: function()
		{
			this.hideError();
			this.showLoader();
			this.query = "Nature";
			this.state = "popular";
			this.onSearchWithDebounce("Nature");

			if (BX.Landing.Client.Google.allowKeyChange)
			{
				this.showSettingsButton();
			}
			else
			{
				this.hideSettingsButton();
			}
		},


		showSettingsButton: function()
		{
			if (!this.settingsButton)
			{
				this.settingsButton = BX.create("a", {
					props: {className: "ui-btn ui-btn-xs ui-btn-light-border ui-btn-icon-setting landing-google-settings-button"},
					html: BX.Landing.Loc.getMessage("LANDING_GOOGLE_IMAGES_CHANGE_KEY_BUTTON"),
					events: {
						click: this.onSettingsClick.bind(this)
					}
				});

				this.body.appendChild(this.settingsButton);
			}

			this.settingsButton.hidden = false;
		},

		hideSettingsButton: function()
		{
			if (this.settingsButton)
			{
				this.settingsButton.hidden = true;
			}
		},

		onSettingsClick: function(event)
		{
			event.preventDefault();

			if (BX.Landing.Client.Google.allowKeyChange)
			{
				BX.Landing.UI.Panel.GoogleImagesSettings.getInstance().show()
					.then(function() {
						this.showPopular();
						this.searchField.input.innerHTML = "";
					}.bind(this));
			}
		},


		onSearchInput: function(field)
		{
			this.hideError();
			var query = field.getValue();
			if (!!query && query.length)
			{
				this.query = query;
				this.page = 1;
				this.state = "search";
				this.showLoader();
				this.onSearchWithDebounce(query);
				return;
			}

			if (this.state !== "popular")
			{
				this.showPopular();
			}
		},


		onSearchWithDebounce: function(query)
		{
			this.hideError();
			this.client.search(query)
				.then(function(res) {
					this.hideLoader();
					this.clearItems();
					this.hideEmptyResult();

					if (res.length === 0)
					{
						this.showEmptyResult();
						this.hideLoadMore();
						return;
					}

					this.renderItems(itemsAdapter(res, this));
					this.showLoadMore();

					if (BX.Landing.Client.Google.allowKeyChange)
					{
						this.showSettingsButton();
					}
					else
					{
						this.hideSettingsButton();
					}
				}.bind(this))
				.catch(this.showError.bind(this));
		},

		onPictureChange: function(path)
		{
			var url = BX.util.add_url_param("/bitrix/tools/landing/proxy.php", {
				"sessid": BX.bitrix_sessid(),
				"url": path
			});

			this.onChange({
				link: url,
				ext: BX.util.getExtension(path),
				name: BX.Landing.Utils.getFileName(path)
			});
		},

		createKeyError: function()
		{
			return create("div", {
				props: {className: "ui-alert ui-alert-warning"},
				children: [
					create("span", {
						props: {className: "ui-alert-message"},
						html: BX.Landing.Loc.getMessage("LANDING_IMAGES_PANEL_KEY_ERROR")
					})
				]
			});
		},

		createError: function()
		{
			return create("div", {
				props: {className: "ui-alert ui-alert-danger"},
				children: [
					create("span", {
						props: {className: "ui-alert-message"},
						html: BX.Landing.Loc.getMessage("LANDING_IMAGES_PANEL_GOOGLE_ERROR")
					})
				]
			});
		},

		hideError: function()
		{
			BX.Landing.UI.Card.Library.prototype.hideError.call(this);

			if (this.error)
			{
				remove(this.error);
			}
		},

		showError: function()
		{
			if (!BX.Landing.Client.Google.key)
			{
				this.error = this.createKeyError();
			}
			else
			{
				this.error = this.createError();
			}

			BX.insertAfter(this.error, this.imageList);
			BX.Landing.UI.Card.Library.prototype.showError.call(this);
		}
	};
})();