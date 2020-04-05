;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var addClass = BX.Landing.Utils.addClass;
	var hasClass = BX.Landing.Utils.hasClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var isArray = BX.Landing.Utils.isArray;
	var isFunction = BX.Landing.Utils.isFunction;
	var create = BX.Landing.Utils.create;
	var random = BX.Landing.Utils.random;
	var escapeHtml = BX.Landing.Utils.escapeHtml;
	var append = BX.Landing.Utils.append;
	var slice = BX.Landing.Utils.slice;
	var encodeDataValue = BX.Landing.Utils.encodeDataValue;
	var remove = BX.Landing.Utils.remove;
	var data = BX.Landing.Utils.data;
	var bind = BX.Landing.Utils.bind;
	var style = BX.Landing.Utils.style;
	var clone = BX.Landing.Utils.clone;
	var offsetLeft = BX.Landing.Utils.offsetLeft;
	var offsetTop = BX.Landing.Utils.offsetTop;
	var findParent = BX.Landing.Utils.findParent;
	var BaseCollection = BX.Landing.Collection.BaseCollection;

	/**
	 * Implements interface for works with multi select field
	 * @extends {BX.Landing.UI.Field.BaseField}
	 * @param options
	 * @constructor
	 */
	BX.Landing.UI.Field.Source = function(options)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		addClass(this.layout, "landing-ui-field-source");

		this.fields = new BaseCollection();
		this.onChangeHandler = isFunction(options.onChange) ? options.onChange : (function() {});
		this.items = isArray(options.items) ? options.items : [];
		this.value = options.value;

		if (!BX.type.isPlainObject(this.value))
		{
			this.value = {
				source: this.items[0].value,
				filter: this.items[0].filter || [],
				sort: {}
			};
		}

		this.value.sort.items = this.getCurrentSource().sort.items;

		this.filterStub = {
			key: 'filterStub',
			name: BX.Landing.Loc.getMessage('LANDING_BLOCK__SOURCE_FILTER_STUB'),
			value: ''
		};

		if (!BX.type.isArray(this.value.filter) || this.value.filter.length <= 0)
		{
			this.value.filter = [this.filterStub];
		}

		this.button = this.createButtonField();
		this.grid = this.createGrid(this.input, this.button.layout);

		this.sortByDropdown = this.createSortByField({
			items: this.value.sort.items,
			value: this.value.sort.value ? this.value.sort.value.by : undefined
		});
		this.sortOrderDropdown = this.createSortOrderField(
			this.value.sort.value ? this.value.sort.value.order : undefined
		);
		this.valueLayout = this.createValueLayout();
		this.valueLayoutWrapper = this.createValueLayoutWrapper(this.valueLayout);

		append(this.grid, this.layout);
		append(this.sortByDropdown.layout, this.layout);
		append(this.sortOrderDropdown.layout, this.layout);
		append(this.valueLayoutWrapper, this.header);

		this.setValue(this.value);

		this.currentSource = this.value.source;
		var rootWindow = BX.Landing.PageObject.getRootWindow();
		bind(rootWindow.document, "click", this.onDocumentClick.bind(this));

		rootWindow.BX.addCustomEvent(rootWindow, "SidePanel.Slider:onMessage", this.onSliderMessageReducer.bind(this));
	};


	BX.Landing.UI.Field.Source.prototype = {
		constructor: BX.Landing.UI.Field.Source,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		getCurrentSource: function()
		{
			var currentSourceId = this.value.source;

			return this.items.find(function(item) {
				return item.value === currentSourceId;
			});
		},

		createGrid: function(input, buttonLayout)
		{
			return create("div", {
				props: {className: "landing-ui-field-source-grid"},
				children: [
					create("div", {
						props: {className: "landing-ui-field-source-grid-left"},
						children: [
							input
						]
					}),
					create("div", {
						props: {className: "landing-ui-field-source-grid-right"},
						children: [
							buttonLayout
						]
					})
				]
			});
		},

		createValueLayout: function()
		{
			return create("span", {
				text: ""
			});
		},

		createValueLayoutWrapper: function(valueLayout)
		{
			return create("span", {
				children: [
					document.createTextNode(" ("),
					valueLayout,
					document.createTextNode(")")
				]
			});
		},

		createSortByField: function(data)
		{
			return new BX.Landing.UI.Field.DropdownInline({
				title: BX.Landing.Loc.getMessage("LANDING_CARDS__SOURCE_FIELD_SORT_TITLE").toLowerCase(),
				items: data.items,
				content: data.value
			});
		},

		createSortOrderField: function(value)
		{
			return new BX.Landing.UI.Field.DropdownInline({
				title: ", ",
				items: [
					{name: BX.Landing.Loc.getMessage("LANDING_CARDS__SOURCE_FIELD_SORT_DESC"), value: "DESC"},
					{name: BX.Landing.Loc.getMessage("LANDING_CARDS__SOURCE_FIELD_SORT_ASC"), value: "ASC"}
				],
				content: value
			});
		},

		createButtonField: function()
		{
			return new BX.Landing.UI.Button.BaseButton("dropdown_button", {
				text: BX.Landing.Loc.getMessage("LINK_URL_SUGGESTS_SELECT"),
				className: "landing-ui-button-select-link",
				onClick: this.onButtonClick.bind(this)
			});
		},

		/**
		 * Adds placeholder
		 * @param item
		 */
		addPlaceholder: function(item)
		{
			var placeholder = create("div", {
				props: {className: "landing-ui-field-source-placeholder"},
				attrs: {
					"data-item": encodeDataValue(item),
					title: escapeHtml(item.name)
				},
				children: [
					create("span", {
						props: {
							className: (function() {
								if (!item.url)
								{
									return "landing-ui-field-source-placeholder-text landing-ui-field-source-placeholder-text-plain";
								}

								return "landing-ui-field-source-placeholder-text";
							})()
						},
						html: escapeHtml(item.name)
					}),
					(function() {
						if (item.url)
						{
							return create("span", {
								props: {className: "landing-ui-field-source-placeholder-remove"},
								events: {click: this.onPlaceholderRemoveClick.bind(this, item)}
							});
						}
					}.bind(this))()
				],
				events: {
					click: (function() {
						if (item.url)
						{
							this.onPlaceholderClick.bind(this, item.url)
						}
						return function() {};
					}.bind(this))()
				}
			});

			append(placeholder, this.input);
		},

		openSourceFilterSlider: function(url)
		{
			if (!url)
			{
				this.onFilterSliderSave({
					getData: function() {
						return {filter: this.getValue().filter};
					}.bind(this)
				});

				return;
			}

			var siteId = BX.Landing.Env.getInstance().getOptions().site_id;

			BX.SidePanel.Instance.open(url, {
				cacheable: false,
				requestMethod: "post",
				requestParams: {
					filter: this.getValue().filter,
					landingParams: {
						siteId: siteId
					}
				}
			});
		},

		onPlaceholderClick: function(url, event)
		{
			event.stopPropagation();
			this.openSourceFilterSlider(url);
		},

		onSliderMessageReducer: function(event)
		{
			switch (event.getEventId())
			{
				case "save":
					this.onFilterSliderSave(event);
					break;
				case "cancel":
					this.onFilterSliderCancel(event);
					break;
			}
		},

		onFilterSliderSave: function(event)
		{
			var data = event.getData();

			if (BX.type.isArray(data.filter))
			{
				var value = this.getValue();
				value.source = this.currentSource;
				value.filter = data.filter;

				this.setValue(value);
			}
		},

		onFilterSliderCancel: function(event)
		{

		},

		getPlaceholders: function()
		{
			return [].slice.call(
				this.input.querySelectorAll(".landing-ui-field-source-placeholder")
			);
		},

		/**
		 * Handles item remove event
		 * @param item
		 * @param event
		 * @param [preventEvent]
		 */
		onPlaceholderRemoveClick: function(item, event, preventEvent)
		{
			if (event)
			{
				event.preventDefault();
				event.stopPropagation();
			}

			var placeholder = this.getPlaceholderByItem(item);

			if (placeholder)
			{
				remove(placeholder);
			}

			if (!preventEvent)
			{
				this.onValueChangeHandler(this);
			}

			var placeholders = this.getPlaceholders();

			if (placeholders.length < 1)
			{
				this.openSourceFilterSlider(item.url);
			}

			this.getPopup().close();
			this.popup = null;
		},

		/**
		 * Handles menu item click event
		 * @param value
		 * @param event
		 * @param menuItem
		 */
		onMenuItemClick: function(value, event, menuItem)
		{
			menuItem.getMenuWindow().close();

			var item = this.getItemByValue(this.items, value);

			if (Boolean(item))
			{
				this.currentSource = item.value;
				if (BX.type.isArray(item.filter))
				{
					var defaultValue = this.getValue();
					defaultValue.filter = BX.clone(item.filter);
					this.setValue(defaultValue, true);
				}

				BX.Dom.clean(this.input);
				this.openSourceFilterSlider(item.url);
			}

			this.popup = null;
		},

		/**
		 * Gets placeholder element by item
		 * @param item
		 * @return {?HTMLElement}
		 */
		getPlaceholderByItem: function(item)
		{
			return slice(this.input.children).find(function(element) {
				return JSON.stringify(data(element, "data-item").value) === JSON.stringify(item.value);
			});
		},


		/**
		 * Gets popup menu
		 * @return {BX.Landing.UI.Tool.Menu}
		 */
		getPopup: function()
		{
			if (this.popup)
			{
				return this.popup;
			}

			this.popup = new BX.Landing.UI.Tool.Menu({
				id: (this.selector + "_" + random()),
				bindElement: this.button.layout,
				autoHide: true,
				items: this.items.map(function(item) {
					return {
						id: item.value,
						text: BX.Landing.Utils.escapeText(item.name),
						onclick: this.onMenuItemClick.bind(this, item.value)
					}
				}, this),
				events: {
					onPopupClose: function()
					{
						removeClass(this.input, "landing-ui-active");

						if (hasClass(this.layout.parentElement.parentElement, "landing-ui-form-style"))
						{
							void style(this.layout.parentElement.parentElement, {
								"z-index": null,
								"position": null
							});
						}
					}.bind(this)
				}
			});

			if (this.popup.popupWindow.popupContainer)
			{
				addClass(this.popup.popupWindow.popupContainer, "landing-ui-field-source-popup");

				var parent = findParent(this.input, {className: "landing-ui-panel-content-body-content"}, document.body);

				if (parent)
				{
					append(this.popup.popupWindow.popupContainer, parent);
				}
			}

			return this.popup;
		},

		showPopup: function()
		{
			this.getPopup().show();
			this.adjustPopupPosition();
		},

		adjustPopupPosition: function()
		{
			if (this.popup)
			{
				var offsetParent = findParent(this.button.layout, {
					className: "landing-ui-panel-content-body-content"
				});

				var buttonTop = offsetTop(this.button.layout, offsetParent);
				var buttonLeft = offsetLeft(this.button.layout, offsetParent);
				var buttonRect = this.button.layout.getBoundingClientRect();
				var popupRect = this.popup.popupWindow.popupContainer.getBoundingClientRect();

				var offsetY = 2;
				var popupWindowTop = buttonTop + buttonRect.height + offsetY;
				var popupWindowLeft = buttonLeft - (popupRect.width - buttonRect.width);

				requestAnimationFrame(function() {
					this.popup.popupWindow.popupContainer.style.top = popupWindowTop + "px";
					this.popup.popupWindow.popupContainer.style.left = popupWindowLeft + "px";
				}.bind(this));
			}
		},

		onInputClick: function(event)
		{
			if (event)
			{
				event.stopPropagation();
			}

			var popup = this.getPopup();

			if (popup.popupWindow.isShown())
			{
				removeClass(this.input, "landing-ui-active");
				return popup.close();
			}

			addClass(this.input, "landing-ui-active");
			return this.showPopup();
		},

		onButtonClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			this.onInputClick();
		},

		onDocumentClick: function()
		{
			this.getPopup().close();
		},

		getValue: function()
		{
			var stubKey = this.filterStub.key;
			var filter = slice(this.input.children).reduce(function(acc, element) {
				var item = data(element, "data-item");

				if (item.key !== stubKey)
				{
					acc.push({
						key: item.key,
						name: item.name,
						value: item.value
					});
				}

				return acc;
 			}, []);

			var source = this.valueLayout.dataset.value;
			var sortBy = this.sortByDropdown.getValue();
			var sortOrder = this.sortOrderDropdown.getValue();

			return {
				source: source,
				filter: filter,
				sort: {
					by: sortBy,
					order: sortOrder
				}
			};
		},

		isChanged: function()
		{
			var content = clone(this.value).sort();
			var value = this.getValue().sort();

			return JSON.stringify(content) !== JSON.stringify(value);
		},

		getItemByValue: function(items, value)
		{
			var result = null;

			function findItem(items, value)
			{
				return items.forEach(function(item) {
					// noinspection EqualityComparisonWithCoercionJS
					if (value == item.value)
					{
						result = item;
						return;
					}

					if (item.items)
					{
						findItem(item.items, value);
					}
				}, this)
			}

			findItem(items, value);

			return result;
		},

		setValue: function(value, preventEvent)
		{
			if (BX.type.isPlainObject(value))
			{
				if (BX.type.isArray(value.filter))
				{
					value.filter = value.filter.filter(function(item) {
						return BX.type.isPlainObject(item);
					});
				}
				if (!BX.type.isArray(value.filter) || value.filter.length <= 0)
				{
					value.filter = [this.filterStub];
				}

				var source = this.getItemByValue(this.items, value.source);

				if (Boolean(source))
				{
					this.valueLayout.dataset.value = source.value;
					this.valueLayout.innerText = source.name;
					this.input.innerHTML = "";

					var filter = source.filter || [];

					if (BX.type.isArray(value.filter))
					{
						filter = value.filter;
					}

					filter.forEach(function(item) {
						if (BX.type.isPlainObject(item))
						{
							this.addPlaceholder(Object.assign({}, item, {url: source.url}));
						}
					}, this);
				}

				if (this.value.source !== source.value)
				{
					this.value = source;

					BX.remove(this.sortByDropdown.layout);
					BX.remove(this.sortOrderDropdown.layout);

					this.sortByDropdown = this.createSortByField({
						items: this.value.sort.items,
						value: this.value.sort.value ? this.value.sort.value.by : undefined
					});
					this.sortOrderDropdown = this.createSortOrderField(
						this.value.sort.value ? this.value.sort.value.order : undefined
					);

					append(this.sortByDropdown.layout, this.layout);
					append(this.sortOrderDropdown.layout, this.layout);
				}
			}

			if (!preventEvent)
			{
				this.onValueChangeHandler(this);
			}
		}
	};
})();