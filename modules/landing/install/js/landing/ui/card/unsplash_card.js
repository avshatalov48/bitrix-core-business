;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");

	var getFileExtension = BX.Landing.Utils.getFileExtension;


	/**
	 * Adds unsplash utm
	 * @param {string} url
	 * @return {string}
	 */
	function addUtm(url)
	{
		return BX.util.add_url_param(url, {
			"utm_source": "bitrix",
			"utm_medium": "referral",
			"utm_campaign": "api-credit"
		});
	}


	/**
	 * Adapts item object
	 * @param {object} item
	 * @param {BX.Landing.UI.Card.Unsplash} unsplash
	 * @return {{image, credit: {name, link: string}, onClick}}
	 */
	function itemAdapter(item, unsplash)
	{
		return {
			image: item.urls.small,
			credit: {
				name: item.user.name,
				link: addUtm(item.user.links.html)
			},
			dimensions: {
				width: item.width,
				height: item.height
			},
			onClick: unsplash.onPictureChange.bind(unsplash, item)
		}
	}


	/**
	 * Adapts items
	 * @param {object} items
	 * @param {BX.Landing.UI.Card.Unsplash} unsplash
	 * @return {object[]}
	 */
	function itemsAdapter(items, unsplash)
	{
		return items.map(function(item) {
			return itemAdapter(item, unsplash)
		});
	}



	/**
	 * Implements interface for works with unsplash
	 * @extends {BX.Landing.UI.Card.Library}
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Card.Unsplash = function(data)
	{
		BX.Landing.UI.Card.Library.apply(this, arguments);
		this.state = "popular";
		this.page = 1;
		this.query = "";
		this.onSearchWithDebounce = BX.debounce(this.onSearchWithDebounce, 1000, this);
		this.showPopular();
	};

	BX.Landing.UI.Card.Unsplash.prototype = {
		constructor: BX.Landing.UI.Card.Unsplash,
		__proto__: BX.Landing.UI.Card.Library.prototype,


		/**
		 * Handle click on load more button
		 */
		onLoadMore: function()
		{
			if (this.state === "popular")
			{
				this.appendPopular();
			}

			if (this.state === "search")
			{
				this.appendSearch();
			}
		},


		appendSearch: function()
		{
			this.state = "search";
			this.page += 1;

			this.showBottomLoader();
			this.hideLoadMore();

			var unsplash = BX.Landing.Client.Unsplash.getInstance();
			unsplash.search(this.query, this.page).then(function(res) {
				this.hideBottomLoader();
				this.showLoadMore();
				this.renderItems(itemsAdapter(res, this));
			}.bind(this));
		},


		appendPopular: function()
		{
			this.state = "popular";
			this.page += 1;
			this.query = "";
			var unsplash = BX.Landing.Client.Unsplash.getInstance();
			var self = this;
			this.showBottomLoader();
			this.hideLoadMore();

			unsplash.popular(this.page)
				.then(function(res) {
					self.hideBottomLoader();
					self.showLoadMore();
					self.renderItems(itemsAdapter(res, self));
				});
		},


		onSearchInput: function(field)
		{
			var query = field.getValue();

			if (!!query && query.length)
			{
				this.state = "search";
				this.query = query;
				this.page = 1;
				this.showLoader();
				this.onSearchWithDebounce(query);
				this.hideEmptyResult();
				return;
			}

			if (this.state !== "popular")
			{
				this.showPopular();
			}
		},

		onSearchWithDebounce: function(query)
		{
			var unsplash = BX.Landing.Client.Unsplash.getInstance();
			unsplash.search(query)
				.then(function(res) {
					this.hideLoader();
					this.clearItems();

					if (res.length === 0)
					{
						this.hideLoadMore();
						this.showEmptyResult();
						return;
					}

					this.renderItems(itemsAdapter(res, this));
				}.bind(this));
		},

		showPopular: function()
		{
			this.state = "popular";
			this.page = 1;
			this.query = "";

			var unsplash = BX.Landing.Client.Unsplash.getInstance();
			this.showLoader();
			this.hideEmptyResult();

			unsplash.popular().then(function(res) {
				this.hideLoader();
				this.clearItems();

				if (res.length === 0)
				{
					this.hideLoadMore();
					this.showEmptyResult();
					return;
				}

				this.showLoadMore();
				this.renderItems(itemsAdapter(res, this));
			}.bind(this));
		},

		onPictureChange: function(item)
		{
			BX.Landing.Client.Unsplash
				.getInstance()
				.download(item)
				.then(function(path) {
					this.onChange({
						link: path,
						ext: getFileExtension(path),
						name: item.id + '.' + getFileExtension(path)
					});
				}.bind(this));
		}
	};
})();