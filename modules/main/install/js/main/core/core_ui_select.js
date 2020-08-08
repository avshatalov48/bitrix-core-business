;(function() {
	'use strict';

	BX.namespace('BX.Main.ui');
	BX.namespace('BX.Main.ui.block');

	BX.Main.ui.select = function(node, params)
	{
		this.params = null;
		this.node = null;
		this.items = null;
		this.value = null;
		this.tabindex = null;
		this.classSearchButton = null;
		this.classClearButton = '';
		this.classSquareRemove = 'main-ui-square-delete';
		this.classSquareText = 'main-ui-square-item';
		this.classSquareIcon = 'main-ui-item-icon';
		this.classPopup = 'main-ui-select-inner';
		this.classShow = 'main-ui-popup-fast-show-animation';
		this.classClose = 'main-ui-popup-fast-close-animation';
		this.classInput = 'main-ui-square-search-item';
		this.classMenuItem = 'main-ui-select-inner-item';
		this.classLegend = 'main-ui-select-inner-item-legend';
		this.classMenuItemText = 'main-ui-select-inner-item-element';
		this.classMenuMultiItemText = 'main-ui-select-inner-label';
		this.classMenuItemChecked = 'main-ui-checked';
		this.classSquare = 'main-ui-square';
		this.classSquareContainer = 'main-ui-square-container';
		this.classTextValueNode = 'main-ui-select-name';
		this.classMultiSelect = 'main-ui-multi-select';
		this.classSelect = 'main-ui-select';
		this.classValueDelete = 'main-ui-control-value-delete';
		this.classValueDeleteItem = 'main-ui-control-value-delete-item';
		this.classSquareSelected = 'main-ui-square-selected';
		this.classPopupItemSelected = 'main-ui-select-inner-item-selected';
		this.classHide = 'main-ui-hide';
		this.classFocus = 'main-ui-focus';
		this.classDisableScroll = 'main-ui-disable-scroll';
		this.classScroll = 'main-ui-mac-scroll';
		this.marginForEachLevel = 10;
		this.popup = null;
		this.popupItems = null;
		this.isShown = false;
		this.isMulti = false;
		this.input = null;
		this.init(node, params);
	};

	BX.Main.ui.select.prototype = {
		init: function(node, params)
		{
			var popup, input, popupContainer;

			if (BX.type.isDomNode(node))
			{
				this.node = node;
			}

			try {
				params = params || JSON.parse(BX.data(node, 'params'));
			} catch (err) {}

			if (BX.type.isPlainObject(params))
			{
				this.params = params;
				this.classSearchButton = this.prepareParam('classSearchButton');
				this.classClearButton = this.prepareParam('classClearButton');
				this.classSquareRemove = this.prepareParam('classSquareRemove');

				var customPopupClassName = BX.prop.getString(this.params, 'classPopup', '');
				if(customPopupClassName !== '')
				{
					this.classPopup += " " + customPopupClassName;
				}

				this.isMulti = this.prepareParam('isMulti');
			}

			popup = this.getPopup();
			input = this.getInput();
			popupContainer = popup.popupContainer;
			node = this.getNode();

			BX.bind(input, 'blur', BX.delegate(this._onBlur, this));
			BX.bind(input, 'focus', BX.delegate(this._onFocus, this));
			BX.bind(input, 'keydown', BX.delegate(this._onKeyDown, this));
			BX.bind(input, 'input', BX.delegate(this._onInput, this));
			BX.bind(popupContainer, 'click', BX.delegate(this._onPopupClick, this));
			BX.bind(node, 'click', BX.delegate(this._onControlClick, this));
			this.controlValueDeleteButton();
		},

		controlValueDeleteButton: function()
		{
			if (this.isMulti)
			{
				if (!this.getDataValue().length)
				{
					BX.addClass(this.getValueDeleteButton(), this.classHide);
				}
				else
				{
					BX.removeClass(this.getValueDeleteButton(), this.classHide);
				}
			}
		},

		getValueDeleteButton: function()
		{
			if (!BX.type.isDomNode(this.valueDeleteButton))
			{
				this.valueDeleteButton = BX.findChild(this.getNode(), {className: this.classValueDelete}, true, false);
			}

			return this.valueDeleteButton;
		},

		_onInput: function(event)
		{
			var target = event.currentTarget;

			clearTimeout(this.inputTimer);
			this.inputTimer = setTimeout(function() {
				target.value = '';
			}, 1000);

			this.selectPopupItemBySubstring(target.value);
		},

		_onKeyDown: function(event)
		{
			var target = event.currentTarget;
			var lastSquare, data;

			if (this.isMulti)
			{
				if (BX.hasClass(target, this.classInput))
				{
					lastSquare = this.getLastSquare();

					if (target.value.length === 0 && event.code === 'Backspace')
					{
						if (BX.type.isDomNode(lastSquare))
						{
							if (this.isSelected(lastSquare))
							{
								data = JSON.parse(BX.data(lastSquare, 'item'));
								this.unselectItem(data);
							}
							else
							{
								this.selectSquare(lastSquare);
							}
						}
					}
					else
					{
						this.unselectSquare(lastSquare);
					}
				}
			}

			if (event.code === 'ArrowDown')
			{
				this.selectNextPopupItem();
				target.value = '';
			}

			if (event.code === 'ArrowUp')
			{
				this.selectPrevPopupItem();
				target.value = '';
			}

			if (event.code === 'Enter')
			{
				this.selectSelectedItem();
				event.stopPropagation();
				target.value = '';
			}
		},

		selectPopupItemBySubstring: function(substr)
		{
			substr = substr.toLowerCase();

			var items = this.getPopupItems();
			var isSelected = false;

			this.unselectAllPopupItems();

			items.forEach(function(item) {
				if (!isSelected && item.innerText.toLowerCase().indexOf(substr) === 0)
				{
					isSelected = true;
					BX.addClass(item, this.classPopupItemSelected);
					this.selectedItem = item;
					this.adjustScroll();
				}
			}, this);
		},

		selectSelectedItem: function()
		{
			if (BX.type.isDomNode(this.selectedItem))
			{
				BX.fireEvent(this.selectedItem, 'mousedown');
				this.selectNextPopupItem();
			}
		},

		unselectAllPopupItems: function()
		{
			var items = this.getPopupItems();

			if (BX.type.isArray(items))
			{
				items.forEach(function(current) {
					BX.removeClass(current, this.classPopupItemSelected);
				}, this);
			}
		},

		resetPopupScroll: function()
		{
			var popup = this.getPopup();
			var popupContainer = popup.contentContainer.parentNode;
			BX.scrollTop(popupContainer, 0);
		},

		selectNextPopupItem: function()
		{
			var items = this.getPopupItems();
			var selected, nextSelected;

			if (BX.type.isArray(items))
			{
				selected = items.filter(function(current) {
					return BX.hasClass(current, this.classPopupItemSelected);
				}, this);

				selected = selected.length > 0 ? selected[0] : null;
			}

			if (BX.type.isDomNode(selected))
			{
				nextSelected = BX.nextSibling(selected);

				if (!BX.type.isDomNode(nextSelected))
				{
					nextSelected = items[0];
				}

				BX.removeClass(selected, this.classPopupItemSelected);
			}
			else
			{
				nextSelected = items[0];
			}

			this.selectedItem = nextSelected;
			BX.addClass(nextSelected, this.classPopupItemSelected);
			this.adjustScroll(false);
		},

		selectPrevPopupItem: function()
		{
			var items = this.getPopupItems();
			var selected, prevSelected;

			if (BX.type.isArray(items))
			{
				selected = items.filter(function(current) {
					return BX.hasClass(current, this.classPopupItemSelected);
				}, this);

				selected = selected.length > 0 ? selected[0] : null;
			}

			if (BX.type.isDomNode(selected))
			{
				prevSelected = BX.previousSibling(selected);

				if (!BX.type.isDomNode(prevSelected))
				{
					prevSelected = items[items.length-1];
				}

				BX.removeClass(selected, this.classPopupItemSelected);
			}
			else
			{
				prevSelected = items[items.length-1];
			}

			this.selectedItem = prevSelected;
			BX.addClass(prevSelected, this.classPopupItemSelected);
			this.adjustScroll(true);
		},

		adjustScroll: function(isTop)
		{
			var popupContainer = this.getPopup().contentContainer.parentNode;
			var itemRect = BX.pos(this.selectedItem);
			var popupRect = BX.pos(popupContainer);
			var scrollTop = BX.scrollTop(popupContainer);

			if (!isTop)
			{
				if (itemRect.bottom > popupRect.bottom)
				{
					scrollTop = scrollTop + (itemRect.bottom - popupRect.bottom);
					BX.scrollTop(popupContainer, scrollTop);
				}

				if (itemRect.top < popupRect.top)
				{
					scrollTop = scrollTop - itemRect.bottom;
					BX.scrollTop(popupContainer, scrollTop);
				}
			}
			else
			{
				if (itemRect.top < popupRect.top)
				{
					scrollTop = scrollTop - (popupRect.top - itemRect.top);
					BX.scrollTop(popupContainer, scrollTop);
				}

				if (itemRect.bottom > popupRect.bottom)
				{
					scrollTop = scrollTop + (itemRect.bottom - popupRect.bottom);
					BX.scrollTop(popupContainer, scrollTop);
				}
			}

		},

		isSelected: function(square)
		{
			return BX.hasClass(square, this.classSquareSelected);
		},

		selectSquare: function(square)
		{
			BX.addClass(square, this.classSquareSelected);
		},

		unselectSquare: function(square)
		{
			BX.removeClass(square, this.classSquareSelected);
		},

		getLastSquare: function()
		{
			var squares = this.getSquares();
			var lastSquare;

			if (BX.type.isArray(squares) && squares.length)
			{
				lastSquare = squares[squares.length-1];
			}

			return lastSquare;
		},

		_onMenuItemClick: function(event)
		{
			event.stopPropagation();
			event.preventDefault();

			var target = event.currentTarget;
			var data, square;

			if (!this.isLegend(target))
			{
				try {
					data = JSON.parse(BX.data(target, 'item'));
				} catch (err) {}

				if (this.isMulti)
				{
					square = this.getSquare(data);

					if (!BX.type.isDomNode(square))
					{
						this.selectItem(data);
					}
					else
					{
						this.unselectItem(data);
					}

					this.adjustPopupPosition();
					this.inputFocus();
				}
				else
				{
					this.uncheckAllItems();
					this.checkItem(target);
					this.updateDataValue(data);
					this.updateValue(data);
					this.closePopup();
					this.inputBlur();
				}

				BX.onCustomEvent(window, 'UI::Select::change', [this, data]);
				this.controlValueDeleteButton();
			}
		},

		selectItem: function(data)
		{
			var popupItem = this.getPopupItem(data);

			this.addSquare(data);

			if (BX.type.isDomNode(popupItem))
			{
				this.checkItem(popupItem);
			}

			this.addMultiValue(data);
		},

		unselectItem: function(data)
		{
			var square = this.getSquare(data);
			var popupItem = this.getPopupItem(data);

			this.removeSquare(square);
			this.uncheckItem(popupItem);
			this.removeMultiValue(data);
		},

		uncheckAllItems: function()
		{
			var items = this.getPopupItems();

			if (BX.type.isArray(items))
			{
				items.forEach(this.uncheckItem, this);
			}
		},

		addMultiValue: function(data)
		{
			var currentValue = this.getDataValue();

			if (BX.type.isArray(currentValue))
			{
				currentValue.push(data);
				this.updateDataValue(currentValue);
			}
		},

		removeMultiValue: function(data)
		{
			var currentValue = this.getDataValue();

			if (BX.type.isArray(currentValue) && currentValue.length)
			{
				currentValue = currentValue.filter(function(current) {
					return current.VALUE !== data.VALUE && current.NAME !== data.NAME;
				}, this);

				this.updateDataValue(currentValue);
			}
		},

		getPopupItems: function()
		{
			if (!BX.type.isArray(this.popupItems))
			{
				var popupContainer = this.getPopup().popupContainer;
				this.popupItems = BX.findChild(popupContainer, {class: this.classMenuItem}, true, true);
			}

			return this.popupItems;
		},

		getPopupItem: function(data)
		{
			var popupItems = this.getPopupItems();
			var tmp;
			var item = (popupItems || []).filter(function(current) {
				tmp = JSON.parse(BX.data(current, 'item'));
				return data.VALUE === tmp.VALUE && data.NAME === tmp.NAME;
			});

			return BX.type.isArray(item) && item.length > 0 ? item[0] : null;
		},

		checkItem: function(item)
		{
			if (!BX.hasClass(item, this.classMenuItemChecked))
			{
				BX.addClass(item, this.classMenuItemChecked);
			}
		},

		uncheckItem: function(item)
		{
			if (BX.hasClass(item, this.classMenuItemChecked))
			{
				BX.removeClass(item, this.classMenuItemChecked);
			}
		},

		updateDataValue: function(data)
		{
			var node = this.getNode();
			node.dataset.value = JSON.stringify(data);
			this.controlValueDeleteButton();
		},

		getDataValue: function()
		{
			var node = this.getNode();
			var value;

			try {
				value = JSON.parse(BX.data(node, 'value'));
			} catch (err) {}

			if (!BX.type.isPlainObject(value) && !BX.type.isArray(value))
			{
				value = this.isMulti ? [] : {};
			}

			return value;
		},

		getTextValueNode: function()
		{
			var node = this.getNode();

			return BX.findChild(node, {class: this.classTextValueNode}, true, false);
		},

		updateValue: function(data)
		{
			var textValueNode = this.getTextValueNode();
			BX.html(textValueNode, BX.util.htmlspecialchars(data.NAME));
		},

		adjustPopupPosition: function()
		{
			var popup = this.getPopup();
			var pos = BX.pos(this.getNode());
			pos.forceBindPosition = true;
			popup.adjustPosition(pos);
		},

		_onControlClick: function(event)
		{
			var target = event.target;

			if (!BX.hasClass(target, this.classValueDelete) && !BX.hasClass(target, this.classValueDeleteItem))
			{
				if (BX.hasClass(target, this.classSquareRemove))
				{
					var square = target.parentNode;
					var squareData = JSON.parse(BX.data(square, 'item'));
					this.unselectItem(squareData);
				}
				else
				{
					if (event && event.type === "click")
					{
						if (!this.getPopup().isShown())
						{
							this.inputFocus();
						}
						else
						{
							this.inputBlur();
						}
					}
				}
			}
			else
			{
				var squares = this.getSquares();

				(squares || []).forEach(function(current) {
					squareData = JSON.parse(BX.data(current, 'item'));
					this.unselectItem(squareData);
				}, this);

				this.getInput().value = '';

				return false;
			}
		},

		inputBlur: function()
		{
			var input = this.getInput();

			if (BX.type.isDomNode(input))
			{
				this.getInput().blur();
			}
			else
			{
				this._onBlur();
			}
		},

		inputFocus: function()
		{
			var input = this.getInput();

			if (BX.type.isDomNode(input))
			{
				if (document.activeElement !== input)
				{
					input.focus();
				}
			}
		},

		_onPopupClick: function()
		{
			this.inputFocus();
		},

		_onFocus: function()
		{
			var popup = this.getPopup();

			if (!popup.isShown())
			{
				this.showPopup();
			}
		},

		_onBlur: function()
		{
			this.closePopup();
		},

		getInput: function()
		{
			if (!BX.type.isDomNode(this.input))
			{
				this.input = BX.findChild(this.getNode(), {class: this.classInput}, true, false);
			}

			return this.input;
		},

		getSquares: function()
		{
			return BX.findChild(this.getSquareContainer(), {class: this.classSquare}, true, true);
		},

		getSquare: function(data)
		{
			var squares = this.getSquares();
			var filtered, currentData;

			if (!BX.type.isPlainObject(data))
			{
				try {
					data = JSON.parse(data);
				} catch (err) {}
			}

			filtered = (squares || []).filter(function(current) {
				try {
					currentData = JSON.parse(BX.data(current, 'item'));
				} catch (err) {
					currentData = {};
				}

				return currentData.VALUE === data.VALUE && currentData.NAME === data.NAME;
			});

			return filtered.length ? filtered[0] : null;
		},

		removeSquare: function(squareNodeOrSquareData)
		{
			var square;

			if (BX.type.isDomNode(squareNodeOrSquareData))
			{
				square = squareNodeOrSquareData;
			}
			else
			{
				square = this.getSquare(data);
			}

			BX.remove(square);

			this.adjustPopupPosition();
		},

		createItem: function(itemData)
		{
			var itemText, itemContainer;

			itemContainer = BX.create('div', {
				props: {
					className: this.classMenuItem
				},
				attrs: {
					'data-item': JSON.stringify(itemData)
				}
			});

			if ('LEGEND' in itemData && itemData.LEGEND === true)
			{
				BX.addClass(itemContainer, this.classLegend);
			}

			if ('DEPTH' in itemData)
			{
				var depth = parseInt(itemData.DEPTH);
				depth = BX.type.isNumber(depth) ? depth * this.marginForEachLevel : 0;
				BX.style(itemContainer, 'margin-left', depth + 'px');
			}

			if (!this.isMulti)
			{
				itemText = BX.create('div', {props: {
					className: this.classMenuItemText
				}, html: BX.util.htmlspecialchars(itemData.NAME)});
			}
			else
			{
				itemText = BX.create('div', {props: {
					className: this.classMenuMultiItemText
				}, html: BX.util.htmlspecialchars(itemData.NAME)});
			}

			BX.append(itemText, itemContainer);

			return itemContainer;
		},

		isLegend: function(item)
		{
			return BX.hasClass(item, this.classLegend);
		},

		createSquare: function(data)
		{
			if (!BX.type.isPlainObject(data))
			{
				try {
					data = JSON.parse(data);
				} catch (err) {}
			}

			var square = BX.create('span', {
				props: {
					className: this.classSquare
				}
			});

			square.dataset.item = JSON.stringify(data);

			var squareText = BX.create('span', {
				props: {
					className: this.classSquareText
				},
				html: BX.util.htmlspecialchars(data.NAME)
			});

			var squareRemove = BX.create('span', {
				props: {
					className: [this.classSquareIcon, this.classSquareRemove].join(' ')
				}
			});

			BX.append(squareText, square);
			BX.append(squareRemove, square);

			return square;
		},

		getSquareContainer: function()
		{
			if (!BX.type.isDomNode(this.squareContainer))
			{
				this.squareContainer = BX.findChild(this.getNode(), {class: this.classSquareContainer}, true, false);
			}

			return this.squareContainer;
		},

		addSquare: function(data)
		{
			var container = this.getSquareContainer();
			var square = this.createSquare(data);
			BX.append(square, container);
		},

		closePopup: function()
		{
			var popup = this.getPopup();
			var popupContainer = popup.popupContainer;
			var closeDelay = parseFloat(BX.style(popupContainer, 'animation-duration'));
			var self = this;

			if (!BX.hasClass(document.documentElement, 'bx-ie'))
			{
				BX.removeClass(popupContainer, this.classShow);
				BX.addClass(popupContainer, this.classClose);

				if (BX.type.isNumber(closeDelay))
				{
					closeDelay = closeDelay * 1000;
				}

				setTimeout(function() {
					popup.close();
					self.inputBlur();
				}, closeDelay);
			}
			else
			{
				setTimeout(function() {
					popup.close();
				});
			}

			BX.removeClass(this.getNode(), this.classFocus);

			this.unselectAllPopupItems();
			this.resetPopupScroll();
		},

		getNode: function()
		{
			return this.node;
		},

		showPopup: function()
		{
			var popup = this.getPopup();
			var popupContainer = popup.popupContainer;
			var squares, squareData, currentPopupItem, currentPopupItemPos;

			if (!popup.isShown())
			{
				setTimeout(function() {
					this.adjustPopupPosition();
					popup.show();
				}.bind(this));


				if (!BX.hasClass(document.documentElement, 'bx-ie'))
				{
					BX.removeClass(popupContainer, this.classClose);
					BX.addClass(popupContainer, this.classShow);
				}

				BX.addClass(this.getNode(), this.classFocus);

				if (this.isMulti)
				{
					squares = this.getSquares();
					(squares || []).forEach(function(current) {
						squareData = JSON.parse(BX.data(current, 'item'));
						this.checkItem(this.getPopupItem(squareData));
					}, this);
				}
				else
				{
					currentPopupItem = this.getPopupItem(this.getDataValue());
					currentPopupItemPos = BX.pos(currentPopupItem, popupContainer);
					BX.scrollTop(popupContainer, currentPopupItemPos.top);
					this.checkItem(currentPopupItem);
				}

				if (!this.trackMouse && this.getPopupItemsCount() > 5)
				{
					BX.addClass(popupContainer, this.classScroll);
					BX.bind(popupContainer, 'mouseenter', BX.delegate(this._onMouseOver, this));
					BX.bind(popupContainer, 'mouseleave', BX.delegate(this._onMouseOut, this));
					this.trackMouse = true;
				}
			}
		},

		_onMouseOver: function()
		{
			var popupContainer = this.getPopup().popupContainer;
			var containerHeight = BX.height(popupContainer);
			var contentHeight = BX.height(BX.firstChild(popupContainer));
			var scrollDist = contentHeight - containerHeight;

			popupContainer.onmousewheel = function(event)
			{
				if ((event.deltaY < 0 && this.scrollTop <= 0) ||
					(event.deltaY > 0 && this.scrollTop >= scrollDist)) {
					event.preventDefault();
				}
			}
		},

		_onMouseOut: function()
		{
			var popupContainer = this.getPopup().popupContainer;
			popupContainer.onmousewheel = null;
		},

		getItems: function()
		{
			var dataItems = BX.data(this.getNode(), 'items');

			if (!BX.type.isArray(this.items))
			{
				if (!BX.type.isArray(dataItems))
				{
					this.items = JSON.parse(dataItems);
				}
				else
				{
					this.items = dataItems;
				}
			}

			return this.items;
		},

		getPopup: function()
		{
			if (!this.popup)
			{
				this.popup = this.createPopup(this.getItems());
			}

			return this.popup;
		},

		createPopupItems: function(items)
		{
			var container = BX.create('div');
			var item;

			if (this.isMulti)
			{
				BX.addClass(container, 'popup-multiselect-content');
			}
			else
			{
				BX.addClass(container, 'popup-select-content');
			}

			items.forEach(function(current) {
				item = this.createItem(current);
				BX.append(item, container);
				BX.bind(item, 'mousedown', BX.delegate(this._onMenuItemClick, this));
			}, this);

			return container;
		},

		createPopup: function(items)
		{
			var popup, nodeRect, popupItems;

			if (BX.type.isArray(items) && !this.popup)
			{
				nodeRect = BX.pos(this.getNode());
				this.popup = new BX.Main.Popup({
					bindElement: this.getNode(),
					autoHide : false,
					offsetTop : 2,
					offsetLeft : 0,
					lightShadow : true,
					closeIcon : false,
					closeByEsc : false,
					noAllPaddings: true,
					zIndex: 2000
				});

				BX.style(this.popup.popupContainer, 'width', nodeRect.width + 'px');
				BX.addClass(this.popup.popupContainer, this.classPopup);

				popupItems = this.createPopupItems(items);
				this.popup.setContent(popupItems);
			}

			return this.popup;
		},

		getPopupItemsCount: function()
		{
			var popupItems;

			if (!this.popupItemsCount)
			{
				popupItems = this.getPopupItems();
				this.popupItemsCount = BX.type.isArray(popupItems) ? popupItems.length : 0;
			}

			return this.popupItemsCount;
		},


		/**
		 * Returns custom or preset value
		 * @param paramName
		 * @returns {*}
		 */
		prepareParam: function(paramName)
		{
			return (paramName in this.params) ? this.params[paramName] : this[paramName];
		},

		getParams: function()
		{
			return this.params;
		}
	};


	BX.Main.ui.block['main-ui-square'] = function(data)
	{
		return {
			block: 'main-ui-square',
			attrs: {
				'data-item': 'item' in data ? JSON.stringify(data.item) : ''
			},
			content: [
				{
					block: 'main-ui-square-item',
					content: 'name' in data ? data.name : ''
				},
				{
					block: 'main-ui-square-delete',
					mix: ['main-ui-item-icon']
				}
			]
		}
	};

	BX.Main.ui.block['main-ui-multi-select'] = function(data)
	{
		var control, square, squareContainer, valueDelete, search;
		var squares = [];

		var attrs = BX.type.isPlainObject(data.attrs) ? data.attrs : {};

		attrs = BX.util.objectMerge({}, attrs, {
			'data-name': data.name,
			'data-params': JSON.stringify(data.params),
			'data-items': JSON.stringify(data.items),
			'data-value': JSON.stringify(data.value)
		});

		if ('value' in data && BX.type.isArray(data.value))
		{
			squares = data.value.map(function(current) {
				return {
					block: 'main-ui-square',
					name: 'NAME' in current ? current.NAME : '',
					item: current
				};
			}, this);
		}

		control = {
			block: 'main-ui-multi-select',
			mix: ['main-ui-control'],
			attrs: attrs,
			content: []
		};

		squareContainer = {
			block: 'main-ui-square-container',
			tag: 'span',
			content: squares
		};

		search = {
			block: 'main-ui-square-search',
			tag: 'span',
			content: {
				block: 'main-ui-square-search-item',
				tag: 'input',
				attrs: {
					type: 'text',
					tabindex: 'tabindex' in data ? data.tabindex : '',
					placeholder: 'placeholder' in data ? data.placeholder : ''
				}
			}
		};

		control.content.push(squareContainer);
		control.content.push(search);

		if ('valueDelete' in data && data.valueDelete === true)
		{
			valueDelete = {
				block: 'main-ui-control-value-delete',
				mix: ['main-ui-hide'],
				tag: 'span',
				content: {
					block: 'main-ui-control-value-delete-item'
				}
			};

			control.content.push(valueDelete);
		}

		return control;
	};


	/**
	 *
	 * @param data
	 */
	BX.Main.ui.block['main-ui-select'] = function(data)
	{
		var control, name, search, valueDelete;
		var attrs = BX.type.isPlainObject(data.attrs) ? data.attrs : {};

		attrs = BX.util.objectMerge({}, attrs, {
			'data-name': data.name,
			'data-params': JSON.stringify(data.params),
			'data-items': JSON.stringify(data.items),
			'data-value': JSON.stringify(data.value)
		});

		control = {
			block: 'main-ui-select',
			mix: ['main-ui-control'],
			attrs: attrs,
			content: []
		};

		name = {
			block: 'main-ui-select-name',
			tag: 'span',
			content: 'value' in data && BX.type.isPlainObject(data.value) ? data.value.NAME : ''
		};

		search = {
			block: 'main-ui-square-search',
			tag: 'span',
			content: {
				block: 'main-ui-square-search-item',
				tag: 'input',
				attrs: {
					type: 'text',
					tabindex: data.tabindex
				}
			}
		};

		if ('valueDelete' in data && data.valueDelete === true)
		{
			valueDelete = {
				block: 'main-ui-control-value-delete',
				content: {
					block: 'main-ui-control-value-delete-item',
					tag: 'span'
				}
			};
		}

		control.content.push(name);
		control.content.push(search);

		if (BX.type.isPlainObject(valueDelete))
		{
			control.content.push(valueDelete);
		}

		return control;
	};

})();