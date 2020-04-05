;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");


	/**
	 * Implements interface for works with image library
	 *
	 * @extends {BX.Landing.UI.Panel.Content}
	 *
	 * @param {string} id - Panel id
	 * @constructor
	 */
	BX.Landing.UI.Panel.Unsplash = function(id)
	{
		if (BX.Landing.UI.Panel.Unsplash.instance)
		{
			return BX.Landing.UI.Panel.Unsplash.instance;
		}

		BX.Landing.UI.Panel.Content.apply(this, arguments);
		this.onChangeHandler = null;
		this.onSearchWithDebounce = BX.debounce(this.onSearchWithDebounce, 1000, this);
		this.layout.classList.add("landing-ui-panel-unsplash");
		this.searchContainer = BX.create("div", {props: {className: "landing-ui-panel-unsplash-search-container"}});
		this.loader = BX.create("div", {props: {className: "landing-ui-panel-unsplash-search-loader-container"}});
		this.loader.appendChild(BX.create("div", {props: {className: "landing-ui-panel-unsplash-search-loader"}}));
		(this.searchInputField = new BX.Landing.UI.Field.Unit({
			onInput: this.onSearchInput.bind(this),
			className: "landing-ui-panel-unsplash-search-field",
			placeholder: BX.Landing.Loc.getMessage("UNSPLASH_SEARCH_FIELD_PLACEHOLDER"),
			title: BX.Landing.Loc.getMessage("UNSPLASH_SEARCH_FIELD_LABEL")
		})).enableTextOnly();
		this.searchContainer.appendChild(this.searchInputField.layout);
		this.content.appendChild(this.searchContainer);


		this.searchInputField.input.type = "text";
		this.searchInputField.input.min = null;
		this.searchInputField.input.max = null;

		this.content.appendChild(this.loader);

		this.imagesList = BX.create("div", {props: {className: "landing-ui-panel-unsplash-images-list"}});
		this.content.appendChild(this.imagesList);

		document.body.appendChild(this.layout);

		this.makeLayouts();
	};


	/**
	 * Stores instance
	 * @static
	 * @type {BX.Landing.UI.Panel.Unsplash}
	 */
	BX.Landing.UI.Panel.Unsplash.instance = null;


	/**
	 * Gets panel instance
	 * @static
	 * @returns {BX.Landing.UI.Panel.Unsplash}
	 */
	BX.Landing.UI.Panel.Unsplash.getInstance = function()
	{
		if (!BX.Landing.UI.Panel.Unsplash.instance)
		{
			BX.Landing.UI.Panel.Unsplash.instance = new BX.Landing.UI.Panel.Unsplash("unsplash");
		}

		return BX.Landing.UI.Panel.Unsplash.instance;
	};


	BX.Landing.UI.Panel.Unsplash.prototype = {
		constructor: BX.Landing.UI.Panel.Unsplash,
		__proto__: BX.Landing.UI.Panel.Content.prototype,

		show: function(onChange)
		{
			BX.Landing.UI.Panel.Content.prototype.show.call(this);
			this.onChangeHandler = onChange;
		},

		hide: function()
		{
			BX.Landing.UI.Panel.Content.prototype.hide.call(this);
			this.onChangeHandler = null;
		},

		showLoader: function()
		{
			this.imagesList.innerHTML = "";
			this.loader.classList.add(this.classShow);
		},

		hideLoader: function()
		{
			this.loader.classList.remove(this.classShow);
		},

		isLoaderShown: function()
		{
			return this.loader.classList.contains(this.classShow);
		},

		onSearchInput: function(field)
		{
			var query = field.getValue();
			if (!!query && query.length)
			{
				this.showLoader();
				this.onSearchWithDebounce(query);

			}
			else
			{
				this.makeLayouts();
			}
		},

		onSearchWithDebounce: function(query)
		{
			var unsplash = new BX.Landing.Client.Unsplash.getInstance();
			unsplash.search(query).then(function(res) {
				this.hideLoader();
				this.imagesList.innerHTML = "";
				res.forEach(function(item) {
					var card = new BX.Landing.UI.Card.ImagePreview({
						image: item.urls.small,
						onClick: function() {
							this.onChange(item.urls.full);
						}.bind(this)
					});

					this.imagesList.appendChild(card.layout);
				}, this);
			}.bind(this));
		},

		makeLayouts: function()
		{
			var unsplash = new BX.Landing.Client.Unsplash.getInstance();

			this.showLoader();

			unsplash.popular().then(function(res) {
				this.hideLoader();
				this.imagesList.innerHTML = "";
				res.forEach(function(item) {
					var card = new BX.Landing.UI.Card.ImagePreview({
						image: item.urls.small,
						onClick: function() {
							this.onChange(item.urls.full);
						}.bind(this)
					});

					this.imagesList.appendChild(card.layout);
				}, this);
			}.bind(this));
		},

		onChange: function(path)
		{
			if (typeof this.onChangeHandler === "function")
			{
				this.onChangeHandler(path);
			}

			this.hide();
		}
	};
})();