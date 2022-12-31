;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");

	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var slice = BX.Landing.Utils.slice;
	var show = BX.Landing.Utils.Show;
	var hide = BX.Landing.Utils.Hide;


	/**
	 * Implements interface for works with images library
	 *
	 * @extends {BX.Landing.UI.Card.BaseCard}
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Card.Library = function(data)
	{
		BX.Landing.UI.Card.BaseCard.apply(this, arguments);
		this.layout.classList.add("landing-ui-card-library");

		this.searchLabel = !!data.searchLabel ? data.searchLabel : "";
		this.searchPlaceholder = !!data.searchPlaceholder ? data.searchPlaceholder : "";
		this.serchTipsList = !!data.searchTips ? data.searchTips : [];
		this.onChangeHandler = typeof data.onChange === "function" ? data.onChange : (function() {});

		// Make search
		this.search = BX.create("div", {props: {className: "landing-ui-card-library-search"}});
		this.searchField = this.createSearchField();
		this.searchTips = this.createSearchTips();

		if (data.description)
		{
			this.description = BX.create("div", {
				props: {className: "landing-ui-card-library-description"},
				html: data.description
			});
			this.searchField.layout.insertBefore(this.description, this.searchField.input);
		}

		this.search.appendChild(this.searchField.layout);
		this.search.appendChild(this.searchTips.layout);
		this.body.appendChild(this.search);

		// Make loader
		this.loader = new BX.Loader({target: this.body});

		// Make search list
		this.imageList = BX.create("div", {props: {className: "landing-ui-card-library-list"}});
		this.body.appendChild(this.imageList);

		this.bottomLoader = new BX.Loader({target: this.body, mode: "inline", offset: {left: "calc(50% - 55px)"}});

		// Make load more button
		this.loadMore = BX.create("div", {props: {className: "landing-ui-card-library-load-more"}});
		this.loadMoreButton = this.createLoadMoreButton();
		this.loadMore.hidden = true;
		this.loadMore.dataset.isShown = "false";
		this.loadMore.appendChild(this.loadMoreButton.layout);
		this.body.appendChild(this.loadMore);

	};

	BX.Landing.UI.Card.Library.prototype = {
		constructor: BX.Landing.UI.Card.Library,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype,

		onSearchInput: function(field)
		{

		},

		onTipsChange: function(value)
		{
			this.searchField.setValue(value);
		},

		onLoadMore: function()
		{

		},


		onChange: function(value)
		{
			this.onChangeHandler(value);
		},


		/**
		 * Renders library items
		 * @param {object[]} items
		 */
		renderItems: function(items)
		{
			items.forEach(function(item) {
				var card = new BX.Landing.UI.Card.ImagePreview(item);
				this.imageList.appendChild(card.layout);
				requestAnimationFrame(function() {
					addClass(card.layout, "landing-ui-show");
				});
			}, this);
		},


		showEmptyResult: function()
		{
			if (!this.empty)
			{
				this.empty = BX.create("div", {
					props: {className: "landing-ui-card-library-empty"},
					html: BX.Landing.Loc.getMessage("LANDING_IMAGES_PANEL_EMPTY_RESULT")
				});

				BX.insertAfter(this.empty, this.imageList);
			}

			return show(this.empty);
		},

		hideEmptyResult: function()
		{
			if (this.empty)
			{
				return hide(this.empty);
			}
		},

		showError: function()
		{
			if (!this.error)
			{
				this.error = BX.create("div", {
					props: {className: "landing-ui-card-library-error"},
					html: BX.Landing.Loc.getMessage("LANDING_IMAGES_PANEL_ERROR") || "Error"
				});

				BX.insertAfter(this.error, this.imageList);
			}

			this.hideEmptyResult();
			this.hideLoadMore();
			this.hideBottomLoader();
			this.hideLoadMore();
			this.hideLoader();
			this.clearItems();

			return show(this.error);
		},

		hideError: function()
		{
			if (this.error)
			{
				return hide(this.error);
			}
		},


		/**
		 * Clears items list
		 */
		clearItems: function()
		{
			this.imageList.innerHTML = "";
		},


		createLoadMoreButton: function()
		{
			return new BX.Landing.UI.Button.BaseButton("load_more", {
				text: BX.Landing.Loc.getMessage("LANDING_IMAGE_LIBRARY_LOAD_MORE"),
				className: "landing-ui-card-library-load-more-button",
				onClick: this.onLoadMore.bind(this)
			});
		},


		createSearchTips: function()
		{
			return new BX.Landing.UI.Field.ButtonGroup({
				items: this.serchTipsList,
				className: "landing-ui-card-library-search-tips",
				onChange: this.onTipsChange.bind(this)
			});
		},


		createSearchField: function()
		{
			var field = new BX.Landing.UI.Field.Unit({
				onInput: this.onSearchInput.bind(this),
				className: "landing-ui-card-library-search-field",
				placeholder: BX.Landing.Loc.getMessage("SEARCH_FIELD_PLACEHOLDER"),
				title: this.searchLabel,
				skipPasteControl: true,
			});

			field.input.type = "text";
			field.input.min = null;
			field.input.max = null;
			field.enableTextOnly();

			return field;
		},

		showLoader: function()
		{
			slice(this.imageList.children).forEach(function(item) {
				removeClass(item, "landing-ui-show");
			});
			this.loader.show();
		},

		hideLoader: function()
		{
			this.loader.hide();
		},

		showBottomLoader: function()
		{
			this.bottomLoader.show();
		},

		hideBottomLoader: function()
		{
			this.bottomLoader.hide();
		},

		showLoadMore: function()
		{
			BX.Landing.Utils.Show(this.loadMore);
		},

		hideLoadMore: function()
		{
			BX.Landing.Utils.Hide(this.loadMore);
		}
	};
})();