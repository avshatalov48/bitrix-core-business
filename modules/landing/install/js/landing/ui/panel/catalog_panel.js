;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");

	var addClass = BX.Landing.Utils.addClass;
	var create = BX.Landing.Utils.create;
	var append = BX.Landing.Utils.append;
	var debounce = BX.Landing.Utils.debounce;
	var trim = BX.Landing.Utils.trim;
	var bind = BX.Landing.Utils.bind;
	var proxy = BX.Landing.Utils.proxy;
	var setTextContent = BX.Landing.Utils.setTextContent;
	var htmlToElement = BX.Landing.Utils.htmlToElement;
	var remove = BX.Landing.Utils.remove;
	var style = BX.Landing.Utils.style;
	var isArray = BX.Landing.Utils.isArray;
	var prepend = BX.Landing.Utils.prepend;
	var data = BX.Landing.Utils.data;
	var encodeDataValue = BX.Landing.Utils.encodeDataValue;

	var TYPE_CATALOG_SECTION = "section";
	var TYPE_CATALOG_ELEMENT = "element";
	var TYPE_CATALOG_ALL = "all";


	/**
	 * Implements interface for works with catalog panel
	 * @extends {BX.Landing.UI.Panel.Content}
	 * @param {string} id
	 * @constructor
	 */
	BX.Landing.UI.Panel.Catalog = function(id)
	{
		BX.Landing.UI.Panel.Content.apply(this, arguments);

		this.searchContainer = this.createSearchContainer();
		this.listContainer = this.createListContainer();
		this.searchField = this.createSearchField();
		this.typeSwitcher = this.createTypeSwitcher();
		this.iBlockSwitcher = this.createIblockSwitcher();
		this.resolver = (function() {});
		this.iblocks = null;

		addClass(this.layout, "landing-ui-panel-catalog");
		addClass(this.overlay, "landing-ui-panel-catalog");
		setTextContent(this.title, BX.Landing.Loc.getMessage("LANDING_STYLE_PANEL_CATALOG_TITLE"));

		if (!this.getIblocks() || this.getIblocks().length > 1)
		{
			append(this.iBlockSwitcher.layout, this.searchContainer);
		}
		else
		{
			void style(this.listContainer, {
				"margin-top": "94px"
			});
		}

		append(this.searchField.layout, this.searchContainer);
		append(this.typeSwitcher.layout, this.searchContainer);
		append(this.searchContainer, this.content);
		append(this.listContainer, this.content);
		append(this.layout, document.body);
	};


	BX.Landing.UI.Panel.Catalog.TYPE_CATALOG_ALL = TYPE_CATALOG_ALL;
	BX.Landing.UI.Panel.Catalog.TYPE_CATALOG_SECTION = TYPE_CATALOG_SECTION;
	BX.Landing.UI.Panel.Catalog.TYPE_CATALOG_ELEMENT = TYPE_CATALOG_ELEMENT;


	/**
	 * Gets instance of BX.Landing.UI.Panel.Catalog
	 * @returns {BX.Landing.UI.Panel.Catalog}
	 */
	BX.Landing.UI.Panel.Catalog.getInstance = function()
	{
		return (
			BX.Landing.UI.Panel.Catalog.instance ||
			(BX.Landing.UI.Panel.Catalog.instance = new BX.Landing.UI.Panel.Catalog("catalog_panel"))
		);
	};


	BX.Landing.UI.Panel.Catalog.prototype = {
		constructor: BX.Landing.UI.Panel.Catalog,
		__proto__: BX.Landing.UI.Panel.Content.prototype,
		superClass: BX.Landing.UI.Panel.Content.prototype,

		/**
		 * Search catalog items
		 * @param {string} query
		 * @return {Promise<Object, Object>}
		 */
		search: function(query)
		{
			var requestData = {
				query: trim(query.replace("&nbsp;", "")),
				type: this.typeSwitcher.getValue(),
				iblock: this.iBlockSwitcher.getValue()
			};
			var queryParams = {action: "Utils::catalogSearch"};

			return BX.Landing.Backend.getInstance()
				.action("Utils::catalogSearch", requestData, queryParams);
		},


		/**
		 * Creates search type switcher
		 * @return {BX.Landing.UI.Field.ButtonGroup}
		 */
		createTypeSwitcher: function()
		{
			var typeSwitcher = new BX.Landing.UI.Field.ButtonGroup({
				items: [
					{"name": BX.Landing.Loc.getMessage("LANDING_STYLE_PANEL_CATALOG_SEARCH_TYPE_ALL"), value: TYPE_CATALOG_ALL},
					{"name": BX.Landing.Loc.getMessage("LANDING_STYLE_PANEL_CATALOG_SEARCH_TYPE_ELEMENTS"), value: TYPE_CATALOG_ELEMENT},
					{"name": BX.Landing.Loc.getMessage("LANDING_STYLE_PANEL_CATALOG_SEARCH_TYPE_SECTIONS"), value: TYPE_CATALOG_SECTION}
				],
				content: TYPE_CATALOG_ALL,
				onChange: this.onSearchTypeChange.bind(this)
			});

			addClass(typeSwitcher.layout, "landing-ui-panel-catalog-switch");

			return typeSwitcher;
		},


		/**
		 * Gets iblocks list
		 * @return {*[]}
		 */
		getIblocks: function()
		{
			if (isArray(this.iblocks))
			{
				return this.iblocks;
			}

			return [
				{name: "", value: ""}
			];
		},

		/**
		 * Gets iBlock switcher
		 * @return {BX.Landing.UI.Field.Dropdown}
		 */
		createIblockSwitcher: function()
		{
			var iBlockSwitcher = new BX.Landing.UI.Field.Dropdown({
				title: BX.Landing.Loc.getMessage("LANDING_STYLE_PANEL_CATALOG_IBLOCK_SWITCHER"),
				items: this.getIblocks(),
				content: isArray(this.getIblocks()) ? this.getIblocks()[0].value : "",
				onChange: this.onIblockChange.bind(this)
			});

			addClass(iBlockSwitcher.layout, "landing-ui-panel-catalog-iblock-switch");

			return iBlockSwitcher;
		},


		/**
		 * Handles iblock change event
		 */
		onIblockChange: function()
		{
			this.onSearch();
		},


		/**
		 * Creates search field
		 * @return {BX.Landing.UI.Field.Text}
		 */
		createSearchField: function()
		{
			return new BX.Landing.UI.Field.Text({
				placeholder: BX.Landing.Loc.getMessage("LANDING_STYLE_PANEL_CATALOG_SEARCH_PLACEHOLDER"),
				textOnly: true,
				onValueChange: debounce(this.onSearch, 200, this)
			});
		},


		/**
		 * Creates search container
		 * @return {HTMLElement}
		 */
		createSearchContainer: function()
		{
			return create("div", {
				props: {className: "landing-ui-panel-catalog-search-container"}
			});
		},


		/**
		 * Creates list container
		 * @return {HTMLElement}
		 */
		createListContainer: function()
		{
			return create("div", {
				props: {className: "landing-ui-panel-catalog-list-container"}
			});
		},


		/**
		 * Handles search type change event
		 */
		onSearchTypeChange: function()
		{
			this.onSearch();
		},


		/**
		 * Renders search response
		 * @param response
		 */
		renderResponse: function(response)
		{
			var oldResult = this.listContainer.querySelector(".landing-ui-panel-catalog-list");

			if (oldResult)
			{
				remove(oldResult);
			}

			this.body.scrollTop = 0;

			append(htmlToElement(
				"<div class=\"landing-ui-panel-catalog-list\">" +
					response.map(function(item) {
						if (item.subType === TYPE_CATALOG_SECTION && !item.image)
						{
							item.image = "/bitrix/images/landing/folder.svg";
						}

						var chain = item.chain.reduce(function(accumulator, chainItem) {
							if (chainItem)
							{
								accumulator.push(encodeDataValue(chainItem));
							}

							return accumulator;
						}, []);

						return (
							"<div class='landing-ui-panel-catalog-list-row landing-ui-panel-catalog-list-row-"+item.subType+"'>" +
								"<div class='landing-ui-panel-catalog-list-row-left'>" +
									"<div class='landing-ui-panel-catalog-list-cell-preview' style=\"background-image: url('"+item.image+"')\"></div>" +
								"</div>" +
								"<div class='landing-ui-panel-catalog-list-row-right'>" +
									"<div class='landing-ui-panel-catalog-list-cell-name'>" +
										"<div>"+encodeDataValue(item.name)+"</div>" +
									"</div>" +
									"<div class='landing-ui-panel-catalog-list-cell-chain'>" +
										"<div>"+(chain ? chain.join("&nbsp;/&nbsp;") : "")+"</div>" +
									"</div>" +
								"</div>" +
							"</div>"
						);
					}).join("") +
				"</div>"
			), this.listContainer);

			return response;
		},


		/**
		 * Initializes events handlers on items rendered from response
		 * @param {object[]} response
		 */
		initResponseItems: function(response)
		{
			var items = this.listContainer.querySelector(".landing-ui-panel-catalog-list");

			response.forEach(function(item, index) {
				bind(items.children[index], "click", this.onItemClick.bind(this, item));
			}, this);

			return response;
		},


		/**
		 * Shows catalog panel
		 * @param {?object[]} [iblocks]
		 * @param {?string[]} [entityTypes]
		 * @return {Promise<Object>}
		 */
		show: function(iblocks, entityTypes)
		{
			this.superClass.show.call(this);
			this.iblocks = iblocks || null;
			this.entityTypes = entityTypes;

			this.onSearch();
			this.adjustIblockSwitcher();
			this.adjustEntityTypes();
			this.adjustSearchPlaceholder();

			return new Promise(function(resolve) {
				this.resolver = resolve;
			}.bind(this));
		},


		adjustSearchPlaceholder: function()
		{
			var entityTypes = this.getEntityTypes();

			if (entityTypes.length === 1 && entityTypes[0] === TYPE_CATALOG_SECTION)
			{
				data(this.searchField.input, {
					"data-placeholder": BX.Landing.Loc.getMessage("LANDING_STYLE_PANEL_CATALOG_SEARCH_SECTION_PLACEHOLDER")
				})
			}
		},


		/**
		 * Gets catalog entity types
		 * @return {string[]}
		 */
		getEntityTypes: function()
		{
			if (isArray(this.entityTypes) && this.entityTypes.length > 0)
			{
				return this.entityTypes;
			}

			return [
				TYPE_CATALOG_ALL,
				TYPE_CATALOG_ELEMENT,
				TYPE_CATALOG_SECTION
			];
		},


		adjustEntityTypes: function()
		{
			this.typeSwitcher.buttons.forEach(function(button) {
				button.layout.hidden = !this.getEntityTypes().includes(button.id);
			}, this);

			this.typeSwitcher.setValue(this.getEntityTypes()[0]);
		},


		adjustIblockSwitcher: function()
		{
			remove(this.iBlockSwitcher.layout);
			this.iBlockSwitcher = this.createIblockSwitcher();
			prepend(this.iBlockSwitcher.layout, this.searchContainer);

			if (!this.getIblocks() || this.getIblocks().length < 2)
			{
				void style(this.iBlockSwitcher.layout, {
					display: "none"
				});

				void style(this.loaderContainer, {
					"top": "182px"
				});

				void style(this.listContainer, {
					"margin-top": "94px"
				});
			}
			else
			{
				void style(this.iBlockSwitcher.layout, {
					display: null
				});

				void style(this.loaderContainer, {
					"top": null
				});

				void style(this.listContainer, {
					"margin-top": null
				});
			}
		},


		/**
		 * Handles search event
		 */
		onSearch: function()
		{
			this.showLoader();

			clearTimeout(this.searchTimeout);
			this.searchTimeout = setTimeout(function() {
				this.search(this.searchField.getValue())
					.then(proxy(this.renderResponse, this))
					.then(proxy(this.initResponseItems, this))
					.then(function() {
						this.hideLoader();
					}.bind(this));
			}.bind(this), 500);
		},


		showLoader: function()
		{
			if (!this.loader)
			{
				this.loader = new BX.Loader({offset: {top: "-70px"}});
				this.loaderContainer = create("div", {
					props: {className: "landing-ui-panel-catalog-loader-container"},
					children: [this.loader.layout]
				});
				append(this.loaderContainer, this.listContainer);
				this.loader.show();
			}

			this.loaderContainer.hidden = false;
		},

		hideLoader: function()
		{
			if (this.loaderContainer)
			{
				this.loaderContainer.hidden = true;
			}
		},


		/**
		 * Handles item click event
		 * @param {object} item
		 */
		onItemClick: function(item)
		{
			this.resolver(item);
			this.hide();
		}
	};
})();