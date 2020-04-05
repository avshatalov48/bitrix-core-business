;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var isBoolean = BX.Landing.Utils.isBoolean;
	var isArray = BX.Landing.Utils.isArray;
	var isString = BX.Landing.Utils.isString;
	var isEmpty = BX.Landing.Utils.isEmpty;
	var append = BX.Landing.Utils.append;
	var remove = BX.Landing.Utils.remove;
	var data = BX.Landing.Utils.data;
	var attr = BX.Landing.Utils.attr;
	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var proxy = BX.Landing.Utils.proxy;
	var htmlToElement = BX.Landing.Utils.htmlToElement;
	var bind = BX.Landing.Utils.bind;
	var unbind = BX.Landing.Utils.unbind;
	var join = BX.Landing.Utils.join;
	var fireEvent = BX.Landing.Utils.fireEvent;
	var hash = BX.Landing.Utils.hash;
	var encodeDataValue = BX.Landing.Utils.encodeDataValue;
	var capitalize = BX.Landing.Utils.capitalize;
	var style = BX.Landing.Utils.style;

	var BaseButton = BX.Landing.UI.Button.BaseButton;
	var Dropdown = BX.Landing.UI.Field.Dropdown;
	var Menu = BX.Landing.UI.Tool.Menu;

	/** @type {string} */
	var TYPE_BLOCK = "block";

	/** @type {string} */
	var TYPE_ALIAS = "alias";

	/** @type {string} */
	var TYPE_PAGE = "landing";

	/** @type {string} */
	var TYPE_SYSTEM = "system";

	/** @type {string} */
	var TYPE_CATALOG = "catalog";

	/** @type {string} */
	var TYPE_CATALOG_ELEMENT = "element";

	/** @type {string} */
	var TYPE_CATALOG_SECTION = "section";

	/** @type {string} */
	var TYPE_HREF_LINK = "";

	/** @type {string} */
	var TYPE_HREF_TEL = "tel:";

	/** @type {string} */
	var TYPE_HREF_SKYPE = "skype:";

	/** @type {string} */
	var TYPE_HREF_SMS = "sms:";

	/** @type {string} */
	var TYPE_HREF_MAILTO = "mailto:";

	/**
	 * Href value matchers
	 * @type {{
	 * 		catalog: RegExp,
	 * 		catalogElement: RegExp,
	 * 		catalogSection: RegExp,
	 * 		block: RegExp,
	 * 		page: RegExp,
	 * 		system: RegExp,
	 * 		alias: RegExp
	 * 	}}
	 */
	var matchers = {
		catalog: new RegExp("^#catalog#(Element|Section)([0-9]+)"),
		catalogElement: new RegExp("^#catalogElement([0-9]+)"),
		catalogSection: new RegExp("^#catalogSection([0-9]+)"),
		block: new RegExp("^#block([0-9]+)"),
		page: new RegExp("^#landing([0-9]+)"),
		system: new RegExp("^#system_[a-z_-]+"),
		alias: new RegExp("^#.*")
	};


	/**
	 * Implements interface for works with url href list
	 * @extends {BX.Landing.UI.Field.Text}
	 * @constructor
	 */
	BX.Landing.UI.Field.LinkURL = function(data)
	{
		BX.Landing.UI.Field.Text.apply(this, arguments);

		addClass(this.layout, "landing-ui-field-link-url");

		this.requestOptions = data.options || {};
		this.allowedTypes = isArray(data.allowedTypes) ? data.allowedTypes : [TYPE_BLOCK, TYPE_PAGE];
		this.disableBlocks = isBoolean(data.disableBlocks) ? data.disableBlocks : false;
		this.disableCustomURL = isBoolean(data.disableCustomURL) ? data.disableCustomURL : false;
		this.disallowType = isBoolean(data.disallowType) ? data.disallowType : false;
		this.iblocks = isArray(data.iblocks) ? data.iblocks : null;
		this.allowedCatalogEntityTypes = isArray(data.allowedCatalogEntityTypes) ? data.allowedCatalogEntityTypes : null;
		this.onInitHandler = BX.type.isFunction(data.onInit) ? data.onInit : (function() {});
		this.onNewPageHandler = BX.type.isFunction(data.onNewPage) ? data.onNewPage : (function() {});
		this.enableAreas = data.enableAreas;
		this.customPlaceholder = data.customPlaceholder;
		this.detailPageMode = data.detailPageMode === true;
		this.sourceField = data.sourceField;
		this.currentPageOnly = data.currentPageOnly;
		this.panelTitle = data.panelTitle;

		this.onListShow = this.onListShow.bind(this, this.requestOptions);
		this.onSelectButtonClick = this.onSelectButtonClick.bind(this);
		this.onTypeChange = this.onTypeChange.bind(this);
		this.onListItemClick = this.onListItemClick.bind(this);

		this.popup = null;
		this.dynamic = null;
		this.value = null;
		this.button = this.createButton();
		this.hrefTypeSwithcer = this.createTypeSwitcher();
		this.grid = this.createGridLayout();
		this.gridLeftCell = this.grid.querySelector("[class*=\"left\"]");
		this.gridCenterCell = this.grid.querySelector("[class*=\"center\"]");
		this.gridRightCell = this.grid.querySelector("[class*=\"right\"]");

		remove(this.hrefTypeSwithcer.header);
		append(this.hrefTypeSwithcer.layout, this.gridLeftCell);
		append(this.input, this.gridCenterCell);
		append(this.button.layout, this.gridRightCell);
		append(this.grid, this.layout);

		this.setHrefPlaceholderByType(this.getHrefStringType());
		this.setHrefTypeSwitcherValue(this.getHrefStringType());
		this.removeHrefTypeFromHrefString();
		this.makeDisplayedHrefValue();

		if (this.disallowType)
		{
			void style(this.gridLeftCell, {
				"display": "none"
			});
		}
	};

	BX.Landing.UI.Field.LinkURL.cache = new BX.Cache.MemoryCache();

	BX.Landing.UI.Field.LinkURL.TYPE_BLOCK = TYPE_BLOCK;
	BX.Landing.UI.Field.LinkURL.TYPE_PAGE = TYPE_PAGE;
	BX.Landing.UI.Field.LinkURL.TYPE_CATALOG = TYPE_CATALOG;
	BX.Landing.UI.Field.LinkURL.TYPE_CATALOG_ELEMENT = TYPE_CATALOG_ELEMENT;
	BX.Landing.UI.Field.LinkURL.TYPE_CATALOG_SECTION = TYPE_CATALOG_SECTION;
	BX.Landing.UI.Field.LinkURL.matchers = matchers;


	BX.Landing.UI.Field.LinkURL.prototype = {
		constructor: BX.Landing.UI.Field.LinkURL,
		__proto__: BX.Landing.UI.Field.Text.prototype,


		/**
		 * Sets iblocks list
		 * @param {{name: string, value: int|string}[]} iblocks
		 */
		setIblocks: function(iblocks)
		{
			this.iblocks = isArray(iblocks) ? iblocks : null;
		},


		/**
		 * Makes displayed value placeholder
		 */
		makeDisplayedHrefValue: function()
		{
			var hrefValue = this.getValue();
			var placeholderType = this.getPlaceholderType();
			var valuePromise;

			switch (placeholderType)
			{
				case TYPE_BLOCK:
					valuePromise = this.getBlockData(hrefValue);
					break;
				case TYPE_PAGE:
					valuePromise = this.getPageData(hrefValue);
					break;
				case TYPE_CATALOG_ELEMENT:
					valuePromise = this.getCatalogElementData(hrefValue);
					break;
				case TYPE_CATALOG_SECTION:
					valuePromise = this.getCatalogSectionData(hrefValue);
					break;
				case TYPE_SYSTEM:
					valuePromise = this.getSystemPage(hrefValue);
					break;
			}

			if (valuePromise)
			{
				valuePromise
					.then(proxy(this.createPlaceholder, this))
					.then(function(data) {
						this.setValue(data, true);
						if (!this.inited)
						{
							this.inited = true;
							this.onInitHandler();
						}
						return data;
					}.bind(this))
					.catch(function() {});
				this.disableHrefTypeSwitcher();
				this.setHrefTypeSwitcherValue(TYPE_HREF_LINK);
			}

			this.enableHrefTypeSwitcher();
		},

		/**
		 * Gets placeholder data
		 * @param {string} [hrefValue]
		 * @return {Promise<Object>}
		 */
		getPlaceholderData: function(hrefValue)
		{
			hrefValue = hrefValue || this.getValue();
			var placeholderType = this.getPlaceholderType(hrefValue);
			var valuePromise = Promise.resolve({});

			switch (placeholderType)
			{
				case TYPE_BLOCK:
					valuePromise = this.getBlockData(hrefValue);
					break;
				case TYPE_PAGE:
					valuePromise = this.getPageData(hrefValue);
					break;
				case TYPE_CATALOG_ELEMENT:
					valuePromise = this.getCatalogElementData(hrefValue);
					break;
				case TYPE_CATALOG_SECTION:
					valuePromise = this.getCatalogSectionData(hrefValue);
					break;
				case TYPE_SYSTEM:
					valuePromise = this.getSystemPage(hrefValue);
					break;
			}

			return valuePromise;
		},

		/**
		 * Removes type prefix from href value
		 */
		removeHrefTypeFromHrefString: function()
		{
			var clearHref = this.getValue()
				.replace(new RegExp(this.getHrefStringType(), "g"), "");
			this.setValue(clearHref, true);
		},

		/**
		 * Sets type switcher value
		 * @param type
		 */
		setHrefTypeSwitcherValue: function(type)
		{
			this.hrefTypeSwithcer.setValue(type);
		},

		/**
		 * Disables type switcher
		 */
		disableHrefTypeSwitcher: function()
		{
			this.hrefTypeSwithcer.disable();
		},

		/**
		 * Enables type switcher
		 */
		enableHrefTypeSwitcher: function()
		{
			this.hrefTypeSwithcer.enable();
		},

		/**
		 * Gets selected href type (From type switcher)
		 * @return {string}
		 */
		getSelectedHrefType: function()
		{
			return this.hrefTypeSwithcer.getValue();
		},

		/**
		 * Gets href string type
		 * @return {string}
		 */
		getHrefStringType: function()
		{
			var segment = this.getValue().split(":")[0];
			var type = TYPE_HREF_LINK;

			switch (join(segment, ":"))
			{
				case TYPE_HREF_TEL:
					type = TYPE_HREF_TEL;
					break;
				case TYPE_HREF_SMS:
					type = TYPE_HREF_SMS;
					break;
				case TYPE_HREF_SKYPE:
					type = TYPE_HREF_SKYPE;
					break;
				case TYPE_HREF_MAILTO:
					type = TYPE_HREF_MAILTO;
					break;
			}

			return type;
		},

		/**
		 * Sets placeholder by href type
		 * @param {string} type
		 */
		setHrefPlaceholderByType: function(type)
		{
			var placeholder = this.placeholder || BX.Landing.Loc.getMessage("FIELD_LINK_HREF_PLACEHOLDER");

			switch (type)
			{
				case TYPE_HREF_LINK:
					if (this.disableBlocks && this.disableCustomURL)
					{
						placeholder = BX.Landing.Loc.getMessage("FIELD_LINK_HREF_PLACEHOLDER_PAGES_ONLY");
					}

					if (!this.disableBlocks && this.disableCustomURL)
					{
						placeholder = BX.Landing.Loc.getMessage("FIELD_LINK_HREF_PLACEHOLDER_WITHOUT_CUSTOM_URL");
					}

					if (this.allowedTypes.length === 1 && this.allowedTypes[0] === TYPE_CATALOG)
					{
						placeholder = BX.Landing.Loc.getMessage("FIELD_LINK_HREF_PLACEHOLDER_CATALOG_ONLY");
					}

					if (this.customPlaceholder)
					{
						placeholder = this.customPlaceholder;
					}

					break;
				case TYPE_HREF_TEL:
					placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_FIELD_URL_TYPE_PHONE_PLACEHOLDER");
					break;
				case TYPE_HREF_SKYPE:
					placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_FIELD_URL_TYPE_SKYPE_PLACEHOLDER");
					break;
				case TYPE_HREF_SMS:
					placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_FIELD_URL_TYPE_SMS_PLACEHOLDER");
					break;
				case TYPE_HREF_MAILTO:
					placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_FIELD_URL_TYPE_EMAIL_PLACEHOLDER");
					break;
			}

			data(this.input, "data-placeholder", placeholder);
		},


		/**
		 * Gets placeholder type
		 * @param {string} [hrefValue]
		 * @return {string}
		 */
		getPlaceholderType: function(hrefValue)
		{
			hrefValue = hrefValue || this.getValue();

			if (matchers.block.test(hrefValue))
			{
				return TYPE_BLOCK;
			}

			if (matchers.page.test(hrefValue))
			{
				return TYPE_PAGE;
			}

			if (matchers.catalogElement.test(hrefValue))
			{
				return TYPE_CATALOG_ELEMENT;
			}

			if (matchers.catalogSection.test(hrefValue))
			{
				return TYPE_CATALOG_SECTION;
			}

			if (matchers.system.test(hrefValue))
			{
				return TYPE_SYSTEM;
			}

			if (matchers.alias.test(hrefValue))
			{
				return TYPE_ALIAS;
			}

			return TYPE_HREF_LINK;
		},

		/**
		 * Checks that this field contains url placeholder
		 * @return {boolean}
		 */
		containsPlaceholder: function()
		{
			return this.input.innerHTML.indexOf("span") !== -1;
		},

		/**
		 * Creates field grid layout
		 * @return {Element}
		 */
		createGridLayout: function()
		{
			return htmlToElement(
				"<div class=\"landing-ui-field-link-url-grid\">" +
					"<div class=\"landing-ui-field-link-url-grid-left\"></div>" +
					"<div class=\"landing-ui-field-link-url-grid-center\"></div>" +
					"<div class=\"landing-ui-field-link-url-grid-right\"></div>" +
				"</div>"
			);
		},

		/**
		 * Creates type switcher dropdown
		 * @return {BX.Landing.UI.Field.Dropdown}
		 */
		createTypeSwitcher: function()
		{
			return new Dropdown({
				items: [
					{name: BX.Landing.Loc.getMessage("LANDING_LINK_FIELD_URL_TYPE_LINK"), value: TYPE_HREF_LINK},
					{name: BX.Landing.Loc.getMessage("LANDING_LINK_FIELD_URL_TYPE_PHONE"), value: TYPE_HREF_TEL},
					{name: BX.Landing.Loc.getMessage("LANDING_LINK_FIELD_URL_TYPE_SKYPE"), value: TYPE_HREF_SKYPE},
					{name: BX.Landing.Loc.getMessage("LANDING_LINK_FIELD_URL_TYPE_SMS"), value: TYPE_HREF_SMS},
					{name: BX.Landing.Loc.getMessage("LANDING_LINK_FIELD_URL_TYPE_EMAIL"), value: TYPE_HREF_MAILTO}
				],
				onValueChange: this.onTypeChange
			});
		},

		/**
		 * Creates select button
		 * @return {BX.Landing.UI.Button.BaseButton}
		 */
		createButton: function()
		{
			return new BaseButton("dropdown_button", {
				text: BX.Landing.Loc.getMessage("LINK_URL_SUGGESTS_SELECT"),
				className: "landing-ui-button-select-link",
				onClick: this.onSelectButtonClick
			})
		},

		/**
		 * Handles link type change event
		 * @param {BX.Landing.UI.Field.Dropdown} field
		 */
		onTypeChange: function(field)
		{
			var type = field.getValue();

			switch (type)
			{
				case TYPE_HREF_LINK:
					unbind(this.gridRightCell, "mouseover", proxy(this.onButtonMouseover, this));
					unbind(this.gridRightCell, "mouseout", proxy(this.onButtonMouseout, this));
					this.button.enable();
					break;
				case TYPE_HREF_TEL:
				case TYPE_HREF_SMS:
				case TYPE_HREF_SKYPE:
				case TYPE_HREF_MAILTO:
					bind(this.gridRightCell, "mouseover", proxy(this.onButtonMouseover, this));
					bind(this.gridRightCell, "mouseout", proxy(this.onButtonMouseout, this));
					this.button.disable();
					break;
			}

			this.setHrefPlaceholderByType(type);
		},

		/**
		 * Handles mouse over event on button
		 */
		onButtonMouseover: function()
		{
			this.customTypeSuggestTimeout = setTimeout(function() {
				BX.Landing.UI.Tool.Suggest.getInstance()
					.show(this.button.layout, {
						description: BX.Landing.Loc.getMessage("LANDING_LINK_FIELD_URL_TYPE_CUSTOM_BUTTON_TITLE")
					});

				this.pulseTimeout = setTimeout(function() {
					addClass(this.hrefTypeSwithcer.input, "landing-ui-pulse");
				}.bind(this), 1000);
			}.bind(this), 100);
		},

		/**
		 * Handles mouse up event on button
		 */
		onButtonMouseout: function()
		{
			clearTimeout(this.customTypeSuggestTimeout);
			BX.Landing.UI.Tool.Suggest.getInstance().hide();

			clearTimeout(this.pulseTimeout);
			removeClass(this.hrefTypeSwithcer.input, "landing-ui-pulse");
		},

		/**
		 * Gets block data
		 * @param {string} block - (#block123)
		 * @return {Promise<T>}
		 */
		getBlockData: function(block)
		{
			return BX.Landing.Backend.getInstance()
				.getBlock({blockId: block.replace("#block", "")})
				.then(function(result) {
					return (result.type = "block"), result;
				});
		},

		/**
		 * Gets page data
		 * @param {string} page - (#landing123)
		 */
		getPageData: function(page)
		{
			return BX.Landing.UI.Field.LinkURL.cache.remember(page, function() {
				var pageId = parseInt(page.replace("#landing", ""));

				return BX.Landing.Backend.getInstance()
					.getLanding({landingId: pageId})
					.then(function(landing) {
						if (!landing)
						{
							if (BX.Text.toNumber(pageId) === 0)
							{
								this.onNewPageHandler();

								return {
									type: "landing",
									id: 0,
									name: BX.Landing.Loc.getMessage('LANDING_LINK_PLACEHOLDER_NEW_PAGE'),
									siteId: BX.Landing.Main.getInstance().options.site_id
								};
							}
							else
							{
								return null;
							}
						}

						return {
							type: "landing",
							id: landing.ID,
							name: landing.TITLE,
							siteId: landing.SITE_ID
						};
					}.bind(this));
			}.bind(this));
		},

		/**
		 * Gets system page data
		 * @param {string} page - (#system_([a-z]))
		 */
		getSystemPage: function(page)
		{
			return BX.Landing.UI.Field.LinkURL.cache.remember(page, function() {
				var systemCode = this.content.replace("#system_", "");
				var systemPages = BX.Landing.Main.getInstance().options.syspages;

				if (systemCode in systemPages)
				{
					return Promise.resolve({
						type: "system",
						id: "_" + systemCode,
						name: systemPages[systemCode].name
					});
				}

				return Promise.reject();
			}.bind(this));
		},

		/**
		 * Gets catalog element data
		 * @param {string} element
		 */
		getCatalogElementData: function(element)
		{
			return BX.Landing.UI.Field.LinkURL.cache.remember(element, function() {
				var elementId = element.match(matchers.catalogElement)[1];
				var requestBody = {elementId: elementId};

				return BX.Landing.Backend.getInstance()
					.action("Utils::getCatalogElement", requestBody);
			}.bind(this));
		},

		/**
		 * Gets catalog section data
		 * @param {string} section
		 */
		getCatalogSectionData: function(section)
		{
			return BX.Landing.UI.Field.LinkURL.cache.remember(section, function() {
				var sectionId = section.match(matchers.catalogSection)[1];
				var requestBody = {sectionId: sectionId};

				return BX.Landing.Backend.getInstance()
					.action("Utils::getCatalogSection", requestBody);
			}.bind(this));
		},

		/**
		 * Creates popup menu
		 * @return {BX.Landing.UI.Tool.Menu}
		 */
		createPopup: function()
		{
			var buttons = [];

			if (this.allowedTypes.includes(TYPE_BLOCK))
			{
				buttons.push({
					text: BX.Landing.Loc.getMessage("LANDING_LINKS_BUTTON_BLOCKS"),
					onclick: this.onListShow.bind(this, TYPE_BLOCK)
				});
			}

			if (this.allowedTypes.includes(TYPE_PAGE))
			{
				buttons.push({
					text: BX.Landing.Loc.getMessage("LANDING_LINKS_BUTTON_LANDINGS"),
					onclick: this.onListShow.bind(this, TYPE_PAGE)
				});
			}

			if (this.allowedTypes.includes(TYPE_CATALOG))
			{
				buttons.push({
					text: BX.Landing.Loc.getMessage("LANDING_LINKS_BUTTON_CATALOG"),
					onclick: this.onListShow.bind(this, TYPE_CATALOG)
				});
			}

			this.popup = new Menu({
				id: "link_list_" + (+new Date()),
				bindElement: this.button.layout,
				items: buttons,
				autoHide: true,
				events: {
					onPopupClose: this.button.deactivate.bind(this.button)
				}
			});

			append(this.popup.popupWindow.popupContainer, this.button.layout.parentNode);

			return this.popup;
		},

		onSelectButtonClick: function()
		{
			if (this.allowedTypes.length === 1)
			{
				this.onListShow(this.allowedTypes[0]);
				return;
			}

			this.popup = this.popup || this.createPopup();
			this.button.enable();
			this.popup.show();

			var rect = BX.pos(this.button.layout, this.button.layout.parentNode);
			this.popup.popupWindow.popupContainer.style.top = rect.bottom + "px";
			this.popup.popupWindow.popupContainer.style.left = "auto";
			this.popup.popupWindow.popupContainer.style.right = "0";
		},

		onListShow: function(options, type)
		{
			if (this.popup)
			{
				this.popup.close();
			}

			if (type === TYPE_CATALOG)
			{
				var iblocks = this.iblocks;

				if (!isArray(iblocks))
				{
					iblocks = BX.Landing.Main.getInstance().options.iblocks;
				}

				void BX.Landing.UI.Panel.Catalog.getInstance()
					.show(iblocks, this.allowedCatalogEntityTypes)
					.then(this.onListItemClick);

				return;
			}

			options.enableAreas = this.enableAreas;
			options.dynamicMode = true;
			options.currentPageOnly = this.currentPageOnly;
			options.panelTitle = this.panelTitle;

			if (this.detailPageMode)
			{
				options.source = this.sourceField.getValue().source;
				void BX.Landing.UI.Panel.DetailPage.getInstance()
					.show(options)
					.then(this.onListItemClick);
			}
			else
			{
				var panel = BX.Landing.UI.Panel.URLList.getInstance();

				void panel
					.show(type, options)
					.then(this.onListItemClick);
			}
		},


		/**
		 * Checks that edit mode is prevented
		 * @return {boolean}
		 */
		isEditPrevented: function()
		{
			if (!isBoolean(this.editPrevented))
			{
				this.editPrevented = this.disableCustomURL || this.containsPlaceholder();
			}

			return this.editPrevented;
		},


		/**
		 * Sets edit prevented value
		 * @param {boolean} value
		 */
		setEditPrevented: function(value)
		{
			this.editPrevented = value;
		},

		/**
		 * Enables edit
		 */
		enableEdit: function()
		{
			if (!this.isEditPrevented() && !this.disableCustomURL)
			{
				BX.Landing.UI.Field.Text.prototype.enableEdit.apply(this);
			}
		},


		/**
		 * Creates internal url placeholder
		 * @param {{[type]: string, [id]: string|number, name: string, [url]: string, [image]: string, [subType]: string, [chain]: string[]}} options
		 * @returns {Element}
		 */
		createPlaceholder: function(options)
		{
			if (isString(options))
			{
				return options;
			}

			var placeholder = htmlToElement(
				"<span class=\"landing-ui-field-url-placeholder\">" +
					"<span class=\"landing-ui-field-url-placeholder-preview\"></span>" +
					"<span class=\"landing-ui-field-url-placeholder-text\">" + encodeDataValue(options.name) + "</span>" +
					"<span class=\"landing-ui-field-url-placeholder-delete\"></span>" +
				"</span>"
			);

			var placeholderRemove = placeholder
				.querySelector("[class*=\"delete\"]");
			bind(placeholderRemove, "click", proxy(this.onPlaceholderRemoveClick, this));

			if (!isEmpty(options.image) && isString(options.image))
			{
				var placeholderPreview = placeholder
					.querySelector("[class*=\"preview\"]");

				attr(placeholderPreview, {
					"style": "background-image: url('"+options.image+"')"
				});

				if (options.subType === TYPE_CATALOG_SECTION)
				{
					addClass(placeholderPreview, "section");
				}
			}

			if (options.type === TYPE_CATALOG)
			{
				options.chain.push(options.name);
				var title = join(options.name, "\n", options.chain.join(' / '));

				attr(placeholder, {
					"data-dynamic": {
						type: join(TYPE_CATALOG, capitalize(options.subType)),
						value: options.id
					},
					"data-placeholder": join("#", options.type, capitalize(options.subType), options.id),
					"data-url": join("#", options.type, capitalize(options.subType), options.id)
				});

				placeholder.setAttribute("title", title);

				return placeholder;
			}

			attr(placeholder, {
				"data-placeholder": join("#", options.type, options.id),
				"data-url": join("#", options.type, options.id)
			});

			placeholder.setAttribute("title", options.name);

			return placeholder;
		},

		/**
		 * Handles click event on placeholder remove button
		 * @param event
		 */
		onPlaceholderRemoveClick: function(event)
		{
			this.setEditPrevented(false);
			this.enableEdit();
			remove(event.target.parentNode);
			this.setValue("");
			fireEvent(this.layout, "input");
			this.onInputHandler(this.input.innerText);
		},

		/**
		 * Handles click event on catalog panel item
		 * @param {object} item
		 */
		onListItemClick: function(item)
		{
			var resultPromise = Promise.resolve(item);

			if (item.type === "block")
			{
				resultPromise = this.getBlockData("#block" + item.id);
			}

			resultPromise.then(function(item) {
				this.setValue(this.createPlaceholder(item));
				this.disableHrefTypeSwitcher();
				this.setHrefTypeSwitcherValue(TYPE_HREF_LINK);
				fireEvent(this.layout, "input");
			}.bind(this));
		},

		getNewLabel: function()
		{
			if (!this.newLabel)
			{
				this.newLabel = BX.create({
					tag: 'div',
					props: {className: 'landing-ui-field-link-new-label'},
					text: BX.Landing.Loc.getMessage('LANDING_LINK_NEW_PAGE_LABEL')
				});
			}

			return this.newLabel;
		},

		showNewLabel: function()
		{
			BX.Dom.style(this.gridCenterCell, {
				position: 'relative',
				overflow: 'visible',
			});
			BX.Dom.append(this.getNewLabel(), this.gridCenterCell);
		},

		hideNewLabel: function()
		{
			BX.Dom.style(this.gridCenterCell, 'overflow', null);
			BX.Dom.remove(this.getNewLabel());
		},

		/**
		 * Sets value
		 * @param {object|string} value
		 * @param {boolean} [preventEvent] - Prevents onChange event
		 */
		setValue: function(value, preventEvent)
		{
			if (typeof value === "object" && !BX.Type.isNil(value))
			{
				this.disableEdit();
				this.setEditPrevented(true);
				this.input.innerHTML = "";
				append(value, this.input);
				this.value = value.dataset.placeholder;
				this.dynamic = value.dataset.dynamic;

				if (this.value === '#landing0')
				{
					this.showNewLabel();
				}
				else
				{
					this.hideNewLabel();
				}

				if (!preventEvent)
				{
					this.onInputHandler(this.input.innerText);
				}
			}
			else if (!BX.Type.isNil(value))
			{
				this.setEditPrevented(false);
				this.input.innerText = value.toString().trim();
				this.value = null;
				this.dynamic = null;
				this.enableHrefTypeSwitcher();
				this.hideNewLabel();
			}

			if (!preventEvent)
			{
				if (BX.type.isString(this.value))
				{
					this.getPlaceholderData(this.value)
						.then(function(data) {
							this.onValueChangeHandler(data);
						}.bind(this))
						.catch(function() {

						});
					return;
				}

				this.onValueChangeHandler(null);
			}
		},

		/**
		 * Gets dynamic data
		 * @return {?object}
		 */
		getDynamic: function()
		{
			return this.dynamic;
		},

		/**
		 * Gets value
		 * @return {string}
		 */
		getValue: function()
		{
			return this.getSelectedHrefType() + (this.value ? this.value : this.input.innerText);
		}
	};
})();