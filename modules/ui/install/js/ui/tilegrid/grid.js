;(function() {

'use strict';

BX.namespace('BX.TileGrid');

BX.TileGrid.Grid = function(options)
{
	this.options = options;
	this.id = options.id;

	this.tileMargin = options.tileMargin;
	this.sizeRatio = options.sizeRatio;

	this.tileSize = options.tileSize;
	this.itemHeight = options.itemHeight;
	this.itemMinWidth = options.itemMinWidth;
	this.checkBoxing = options.checkBoxing;
	this.items = [];
	this.renderTo = options.container;
	this.multiSelectMode = null;
	this.style = null;
	this.containerWidth = null;
	this.countItemsPerRow = null;
	this.layout = {
		container: null,
		content: null
	};
	this.emptyBlock = null;
	this.loader = null;
	this.dragger = new BX.TileGrid.DragDrop(this);
	this.gridTile = null;
	this.backspaceButton = null;
	this.deleteButton = null;
	this.enterButton = null;
	this.currentItem = null;
	this.firstCurrentItem = null;
	this.currentItem = null;
	this.itemType = this.getItemType(options.itemType);
	this.loadData(options);
	this.bindEvents();

	// after refactoring
	this.pressedShiftKey = null;
	this.pressedDeleteKey = null;
	this.pressedSelectAllKeys = null;
	this.pressedArrowTopMultipleKey = null;
	this.pressedArrowRightMultipleKey = null;
	this.pressedArrowBottomMultipleKey = null;
	this.pressedArrowLeftMultipleKey = null;
	this.pressedArrowTopKey = null;
	this.pressedArrowRightKey = null;
	this.pressedArrowBottomKey = null;
	this.pressedArrowLeftKey = null;
	this.pressedEscapeKey = null;
	this.pressedControlKey = null;

	BX.onCustomEvent("BX.TileGrid.Grid::ready", [this]);
};

BX.TileGrid.Grid.prototype =
{
	getId: function ()
	{
		return this.id;
	},

	getTileMargin: function(options)
	{
		if(!this.tileMargin)
		{
			this.tileMargin = 9;
		}

		return this.tileMargin
	},

	getSizeRatio: function(options)
	{
		if(!this.sizeRatio)
			return false;

		return this.sizeRatio
	},

	bindEvents: function()
	{
		BX.bind(window, 'resize', this.setStyle.bind(this));
		BX.bind(window, 'keydown', function(event)
		{
			this.defineEscapeKey(event);

			if (this.isKeyPressedEscape())
			{
				this.resetSelection();

				return;
			}
			// after refactoring
			this.defineShiftKey(event);
			this.defineDeleteKey(event);
			this.defineSelectAllKeys(event);
			this.defineArrowMultipleKey(event);
			this.defineArrowSingleKey(event);
			this.defineControlKey(event);

			if(this.isKeyPressedSelectAll() && this.isFocusOnTile())
				this.selectAllItems();

			this.setBackspaceButton(event);
			this.setEnterButton(event);
			this.processButtonSelection();
			if (this.isKeyPressedDelete() && !this.isKeyPressedShift() && !this.isKeyControlKey())
			{
				this.removeSelectedItems(event);
			}
			else if (this.backspaceButton && this.isFocusOnTile())
			{
				this.handleBackspace();
			}

			if (this.isFocusOnTile())
			{
				this.handleEnter(event);
			}

		}.bind(this));
		BX.bind(window, 'keyup', function(event) {
			// after refactoring

			this.resetShiftKey(event);
			this.resetDeleteKey(event);
			this.resetSelectAllKeys(event);
			this.resetArrowKey(event);
			this.resetEscapeKey(event);
			this.resetControlKey(event);

			this.resetBackspaceButton();
			this.resetEnterButton();

		}.bind(this));
		BX.bind(window, 'click', function(event)
		{
			if (this.checkParent(event.target))
				return;

			this.resetSelection();
			this.resetSetMultiSelectMode();
		}.bind(this));
	},

	handleEnter: function()
	{
		if(!this.enterButton || !this.currentItem)
			return;

		this.currentItem.handleEnter();
	},

	handleBackspace: function()
	{},

	checkParent: function(selector)
	{
		var parentSelector = BX.findParent(selector, { className: 'ui-grid-tile-item'});

		if(!parentSelector)
			parentSelector = BX.findParent(selector, { attr: {"data-tile-grid": "tile-grid-stop-close"} });

		return parentSelector;
	},

	appendItem: function(item)
	{
		this.addItem(item);
		var itemNode = this.items[this.items.length - 1].render();
		BX.addClass(itemNode, 'ui-grid-tile-item-inserted');
		this.container.appendChild(itemNode);
		this.items[this.items.length - 1].afterRender();
	},

	addItem: function (options)
	{
		// var item = new BX.TileGrid.Item(options);

		var itemType = this.getItemType(options.itemType);
		var item = new itemType(options);

		item.gridTile = this;

		this.items.push(item);
	},

	_deleteItem: function(item, success)
	{
		item.removeNode();
		for (var i = 0; i < this.items.length; i++)
		{
			if (this.items[i].id === item.id)
			{
				if (BX.type.isFunction(success))
				{
					success(item);
				}

				delete this.items[i];
				this.items.splice(i, 1);

				if(this.items.length === 0)
				{
					this.setMinHeightContainer();
					this.appendEmptyBlock();
				}

				return;
			}
		}
	},

	removeItem: function(item)
	{
		this._deleteItem(item, function (item) {
			BX.onCustomEvent(this, "TileGrid.Grid:onItemRemove", [item, this]);
		}.bind(this));
	},

	moveItem: function(sourceItem, destinationItem)
	{
		this._deleteItem(sourceItem, function (sourceItem) {
			BX.onCustomEvent(this, "TileGrid.Grid:onItemMove", [sourceItem, destinationItem, this]);
		}.bind(this));
	},

	getSelectedItems: function()
	{
		return this.items.filter(function(item){
			return item.selected;
		});
	},

	removeSelectedItems: function()
	{
		var lastCurrentItemNumber;

		this.getSelectedItems().forEach(function (item) {
			lastCurrentItemNumber = this.items.indexOf(item);
			this.removeItem(item);
			if(lastCurrentItemNumber === this.items.length)
				lastCurrentItemNumber = this.items.length - 1
		}, this);

		this.currentItem = this.items[lastCurrentItemNumber];
		this.firstCurrentItem = this.items[lastCurrentItemNumber];

		this.resetSetMultiSelectMode();
		this.selectItem(this.currentItem);
	},

	selectAllItems: function()
	{
		this.items.forEach(function(item) {
			this.selectItem(item);
			this.checkItem(item);
		}, this);

		this.currentItem = null;
		this.firstCurrentItem = null;

		if(this.isKeyPressedSelectAll())
			BX.PreventDefault();

		BX.onCustomEvent('BX.TileGrid.Grid:selectAllItems', [this]);
	},

	/**
	 *
	 * @param {object} json
	 */
	loadData: function(json)
	{
		json.items.forEach(function(item) {
			this.addItem(item);
		}, this)
	},

	countItems: function()
	{
		return this.items.length;
	},

	getItem: function(itemId)
	{
		for (var i = 0; i < this.items.length; i++) {
			if (this.items[i].id.toString() === itemId.toString()) return this.items[i];
		}
	},

	changeTileSize: function(tileSize)
	{
		this.tileSize = tileSize;
		this.setStyle();
	},

	setStyle: function()
	{
		if(this.calculateCountItemsPerRow() === this.countItemsPerRow)
		{
			return
		}

		var head = document.head;
		var styles = 	'#' + this.getId() +
						' .ui-grid-tile-item { ' +
						'width: calc(' + (100 / this.calculateCountItemsPerRow()) + '% - ' + this.getTileMargin() * 2 + 'px); ' +
						'} ';

		if (this.sizeRatio)
		{
			var beforeStyles =  '#' + this.getId() +
								' .ui-grid-tile-item:before { ' +
								'padding-top: ' + this.getSizeRatio() +
								'} ';

			styles = styles + beforeStyles;
		}

		if(!this.style)
		{
			this.getStyleNode()
		}

		BX.cleanNode(this.style);
		styles = document.createTextNode(styles);
		this.style.appendChild(styles);
		head.appendChild(this.style);

		this.countItemsPerRow = this.calculateCountItemsPerRow();
	},

	/**
	 *
	 * @param {string} [className]
	 * @returns {BX.TileGrid.Item}
	 */
	getItemType: function(className)
	{
		var classFn = this.getClass(className);
		if (BX.type.isFunction(classFn))
		{
			return classFn;
		}

		return this.itemType || BX.TileGrid.Item;
	},

	getClass: function(fullClassName)
	{
		if (!BX.type.isNotEmptyString(fullClassName))
		{
			return null;
		}

		var classFn = null;
		var currentNamespace = window;
		var namespaces = fullClassName.split('.');
		for (var i = 0; i < namespaces.length; i++)
		{
			var namespace = namespaces[i];
			if (!currentNamespace[namespace])
			{
				return null;
			}

			currentNamespace = currentNamespace[namespace];
			classFn = currentNamespace;
		}

		return classFn;
	},

	getStyleNode: function()
	{
		this.style = BX.create('style', {
			attrs: {
				type: 'text/css'
			}
		})
	},

	calculateCountItemsPerRow: function()
	{
		if (this.tileSize === 'xl')
		{
			return this.calculateCountItemsPerRowXL();
		}

		if(!this.itemMinWidth)
		{
			return this.calculateCountItemsPerRowM();
		}

		var i = -1;
		var itemWidthSum = 0;
		var tileWidth = this.itemMinWidth + (this.tileMargin * 2);

		while (itemWidthSum < this.getContainerWidth())
		{
			itemWidthSum = itemWidthSum + tileWidth;
			i++;
		}

		return i;
	},

	calculateCountItemsPerRowM: function()
	{
		if(this.itemMinWidth)
		{
			return Math.round(this.getContainerWidth() / (this.itemMinWidth + this.itemMinWidth / 5));
		}

		switch (true)
		{
			case this.getContainerWidth() <= 720:
				return 3;

			case this.getContainerWidth() <= 990:
				return 4;

			case this.getContainerWidth() <= 1100:
				return 5;

			case this.getContainerWidth() > 1100:
				return 6
		}
	},

	calculateCountItemsPerRowXL: function()
	{
		switch (true)
		{
			case this.getContainerWidth() <= 990:
				return 2;

			case this.getContainerWidth() <= 1200:
				return 3;

			case this.getContainerWidth() > 1200:
				return 4
		}
	},

	getContainerWidth: function()
	{
		this.containerWidth = this.renderTo.offsetWidth;
		return this.containerWidth
	},

	getContainer: function()
	{
		return this.container;
	},

	getWrapper: function()
	{
		if(this.container)
		{
			return
		}

		this.container = BX.create('div', {
			attrs: {
				id: this.getId(),
				className: 'ui-grid-tile'
			},
			style: {
				margin: "0 -" + this.getTileMargin() + "px"
			}
		});

		return this.container;
	},

	setMinHeightContainer: function()
	{

		var parent = BX.findParent(this.container);

		this.container.style.height = '0';

		BX.cleanNode(this.container);
		for (var i = 0; parent.offsetHeight <= 0; i++)
		{
			parent = BX.findParent(parent);
		}

		this.container.style.minHeight = parent.offsetHeight + 'px';
	},

	unSetMinHeightContainer: function()
	{
		this.container.style.minHeight = '';
	},

	setHeightContainer: function()
	{
		this.container.style.height = this.container.offsetHeight;
	},

	unSetHeightContainer: function()
	{
		this.container.style.height = '';
	},

	setFadeContainer: function()
	{
		BX.addClass(this.container, 'ui-grid-tile-fade')
	},

	unSetFadeContainer: function()
	{
		BX.removeClass(this.container, 'ui-grid-tile-fade')
	},

	getLoader: function()
	{
		if (this.loader === null)
		{
			this.loader = new BX.Loader({
				target: this.container
			});
		}

		return this.loader;
	},

	showLoader: function()
	{
		this.loader.show();

		if(this.container.getBoundingClientRect().top < 0)
		{
			var positionTop = this.container.getBoundingClientRect().top * -1 + BX.pos(this.container).top;
			this.loader.layout.style.top = (positionTop + 100) + 'px';
			this.loader.layout.style.transform = 'translateY(0)';

			return
		}

		if(this.loader.layout.getBoundingClientRect().top < window.innerHeight)
		{
			this.loader.layout.style.top = '100px';
			this.loader.layout.style.transform = 'translateY(0)';
		}
	},

	redraw: function(items)
	{
		BX.onCustomEvent('BX.TileGrid.Grid:beforeRedraw', [this]);

		this.items.forEach(function(item)
		{
			item.removeNode(false);
		}, this);

		this.items = [];
		this.loadData({
			items: items
		});
		this.draw();

		this.resetSelection();

		BX.onCustomEvent('BX.TileGrid.Grid:redraw', [this]);
	},

	draw: function()
	{
		this.getWrapper();

		this.setStyle(this.getContainerWidth());
		for (var x = 0, item; x < this.items.length; x++)
		{
			item = this.items[x];
			this.container.appendChild(item.render());
		}

		this.renderTo.appendChild(this.container);

		for (var i = 0; i < this.items.length; i++)
		{
			this.items[i].afterRender();
		}

		if(this.items.length === 0)
		{
			this.setMinHeightContainer();
			this.appendEmptyBlock();
			return
		}

		this.removeEmptyBlock();
		this.unSetMinHeightContainer();
	},

	buildEmptyBlock: function()
	{
		if (BX.type.isFunction(this.options.generatorEmptyBlock))
		{
			this.emptyBlock = this.options.generatorEmptyBlock.call(this);

			return;
		}

		this.emptyBlock = BX.create('div', {
			props: {
				className: 'ui-grid-tile-no-data-inner'
			},
			children: [
				BX.create('div', {
					props: {
						className: 'ui-grid-tile-no-data-image'
					}
				})
			]
		})
	},

	appendEmptyBlock: function()
	{
		if(!this.emptyBlock)
			this.buildEmptyBlock();

		this.container.appendChild(this.emptyBlock);
	},

	removeEmptyBlock: function()
	{
		if(!this.emptyBlock)
			return;

		this.container.removeChild(this.emptyBlock);
		this.emptyBlock = null;
	},


	setBackspaceButton: function(event)
	{
		event.key === 'Backspace' ? this.backspaceButton = 'Backspace' : null
	},

	resetBackspaceButton: function()
	{
		this.backspaceButton = null;
	},

	setEnterButton: function(event)
	{
		if(event.key !== 'Enter')
			return;

		this.enterButton = 'Enter'
	},

	resetEnterButton: function()
	{
		this.enterButton = null;
	},

	processButtonSelection: function()
	{
		var lastItem, nextToBeSelected;

		if (this.isFocusOnTile() && !this.currentItem && this.items.length > 0)
		{
			this.setCurrentItem(this.items[0]);
		}

		if(!this.currentItem)
			return;

		if(this.isKeyMultipleArrowRight())
		{
			this.selectFromToItems(this.firstCurrentItem, this.items[this.selectNextItemNumber(this.currentItem)]);
		}

		if(this.isKeyArrowRight())
		{
			this.resetSelectAllItems();
			this.selectItem(this.items[this.selectNextItemNumber(this.currentItem)]);

			nextToBeSelected = this.items[this.items.indexOf(this.currentItem) + 1];
			if (nextToBeSelected && !nextToBeSelected.isVisibleItem())
			{
				scrollToSmooth(nextToBeSelected.getContainer().getBoundingClientRect().height);
			}

			return;
		}

		if(this.isKeyMultipleArrowLeft())
		{
			this.selectFromToItems(this.firstCurrentItem, this.items[this.selectPreviousItemNumber(this.currentItem)]);
		}

		if(this.isKeyArrowLeft())
		{
			this.resetSelectAllItems();
			this.selectItem(this.items[this.selectPreviousItemNumber(this.currentItem)]);

			nextToBeSelected = this.items[this.items.indexOf(this.currentItem) - 1];
			if (nextToBeSelected && !nextToBeSelected.isVisibleItem())
			{
				scrollToSmooth(-nextToBeSelected.getContainer().getBoundingClientRect().height);
			}

			return;
		}

		if(this.isKeyMultipleArrowTop())
		{
			lastItem = this.items[this.items.indexOf(this.currentItem) - this.countItemsPerRow];
			lastItem ? 	this.currentItem = lastItem :
				this.currentItem = this.items[0];

			this.selectFromToItems(this.firstCurrentItem, this.currentItem);
		}

		if(this.isKeyArrowTop())
		{
			lastItem = this.items[this.items.indexOf(this.currentItem) - this.countItemsPerRow];
			lastItem ? 	this.currentItem = lastItem :
				this.currentItem = this.items[0];

			this.resetSelectAllItems();
			this.selectItem(this.currentItem);

			nextToBeSelected = this.items[this.items.indexOf(this.currentItem) - this.countItemsPerRow];
			if (nextToBeSelected && !nextToBeSelected.isVisibleItem())
			{
				scrollToSmooth(-nextToBeSelected.getContainer().getBoundingClientRect().height);
			}

			return
		}

		if(this.isKeyMultipleArrowBottom())
		{
			lastItem = this.items[this.items.indexOf(this.currentItem) + this.countItemsPerRow];
			lastItem ? 	this.currentItem = lastItem :
				this.currentItem = this.items[this.items.length - 1];

			this.selectFromToItems(this.firstCurrentItem, this.currentItem);
		}

		if(this.isKeyArrowBottom())
		{
			lastItem = this.items[this.items.indexOf(this.currentItem) + this.countItemsPerRow];
			lastItem ? 	this.currentItem = lastItem :
				this.currentItem = this.items[this.items.length - 1];

			this.resetSelectAllItems();
			this.selectItem(this.currentItem);

			nextToBeSelected = this.items[this.items.indexOf(this.currentItem) + this.countItemsPerRow];
			if (nextToBeSelected && !nextToBeSelected.isVisibleItem())
			{
				scrollToSmooth(nextToBeSelected.getContainer().getBoundingClientRect().height);
			}
		}
	},

	selectNextItemNumber: function(currentItem)
	{
		if(!currentItem)
			return;

		var indexOfItem = this.items.indexOf(currentItem);
		if(indexOfItem + 1 === this.items.length)
		{
			this.setCurrentItem(this.items[this.items.length - 1]);
			return this.items.length - 1;
		}

		this.setCurrentItem(this.items[indexOfItem + 1]);

		return indexOfItem + 1;
	},

	selectPreviousItemNumber: function(currentItem)
	{
		if(!currentItem)
			return;

		var indexOfItem = this.items.indexOf(currentItem);
		if(indexOfItem - 1 < 0)
		{
			this.setCurrentItem(this.items[0]);
			return 0;
		}

		this.setCurrentItem(this.items[indexOfItem - 1]);

		return indexOfItem - 1;
	},

	selectFromToItems: function(itemFrom, itemTo)
	{
		if(!itemFrom || !itemTo)
		{
			return
		}

		this.resetSelectAllItems();
		this.setMultiSelectMode();
		var itemFromNumber = this.items.indexOf(itemFrom);
		var itemToNumber = this.items.indexOf(itemTo);

		if(itemFromNumber > itemToNumber)
		{
			itemFromNumber = this.items.indexOf(itemTo);
			itemToNumber = this.items.indexOf(itemFrom);
		}

		for (var i = itemFromNumber; i <= itemToNumber; i++)
		{
			this.selectItem(this.items[i]);
			this.checkItem(this.items[i]);
		}
	},

	setCheckbox: function(item)
	{
		BX.addClass(item.layout.checkbox, 'ui-grid-tile-item-checkbox-checked');
	},

	resetFromToItems: function()
	{
		this.firstCurrentItem = null;
		this.currentItem = null;
	},

	resetSelection: function ()
	{
		this.resetSetMultiSelectMode();
		this.resetSelectAllItems();
		this.resetFromToItems();
	},

	/**
	 * Arrow & Shift + arrow control
	 */
	defineArrowMultipleKey: function(event)
	{
		if(event.shiftKey && event.code === 'ArrowUp')
			this.pressedArrowTopMultipleKey = true;

		if(event.shiftKey && event.code === 'ArrowRight')
			this.pressedArrowRightMultipleKey = true;

		if(event.shiftKey && event.code === 'ArrowDown')
			this.pressedArrowBottomMultipleKey = true;

		if(event.shiftKey && event.code === 'ArrowLeft')
			this.pressedArrowLeftMultipleKey = true;
	},

	defineArrowSingleKey: function(event)
	{
		if(!event.shiftKey && event.code === 'ArrowUp')
			this.pressedArrowTopKey = true;

		if(!event.shiftKey && event.code === 'ArrowRight')
			this.pressedArrowRightKey = true;

		if(!event.shiftKey && event.code === 'ArrowDown')
			this.pressedArrowBottomKey = true;

		if(!event.shiftKey && event.code === 'ArrowLeft')
			this.pressedArrowLeftKey = true;
	},

	isKeyMultipleArrowTop: function()
	{
		return this.pressedArrowTopMultipleKey
	},

	isKeyMultipleArrowRight: function()
	{
		return this.pressedArrowRightMultipleKey
	},

	isKeyMultipleArrowBottom: function()
	{
		return this.pressedArrowBottomMultipleKey
	},

	isKeyMultipleArrowLeft: function()
	{
		return this.pressedArrowLeftMultipleKey
	},

	isKeyArrowTop: function()
	{
		return this.pressedArrowTopKey
	},

	isKeyArrowRight: function()
	{
		return this.pressedArrowRightKey
	},

	isKeyArrowBottom: function()
	{
		return this.pressedArrowBottomKey
	},

	isKeyArrowLeft: function()
	{
		return this.pressedArrowLeftKey
	},

	resetArrowKey: function(event)
	{
		if(event.code === 'ArrowUp' || event.code === 'ArrowRight' || event.code === 'ArrowDown' || event.code === 'ArrowLeft')
		{
			this.pressedArrowTopMultipleKey = null;
			this.pressedArrowRightMultipleKey = null;
			this.pressedArrowBottomMultipleKey = null;
			this.pressedArrowLeftMultipleKey = null;
			this.pressedArrowTopKey = null;
			this.pressedArrowRightKey = null;
			this.pressedArrowBottomKey = null;
			this.pressedArrowLeftKey = null;
		}
	},

	/**
	 * Ctrl + A action control
	 */
	defineSelectAllKeys: function(event)
	{
		if((event.metaKey || event.ctrlKey) && event.code === 'KeyA')
			this.pressedSelectAllKeys = true;
	},

	resetSelectAllKeys: function(event)
	 {
		if(event.key === 'Control' || event.key === 'Meta')
		{
			this.pressedSelectAllKeys = null;

		}
	},

	/**
	 *
	 * @returns {boolean|null}
	 */
	isKeyPressedSelectAll: function()
	{
		return this.pressedSelectAllKeys
	},

	/**
	 * Delete button control
	 */
	defineDeleteKey: function(event)
	{
		if(event.key === 'Delete')
		{
			this.pressedDeleteKey = true;
			return
		}

		if(event.key === 'Backspace' && event.metaKey)
			this.pressedDeleteKey = true;
	},

	resetDeleteKey: function(event)
	{
		if(event.key === 'Delete')
		{
			this.pressedDeleteKey = null;
			return
		}

		if(event.key === 'Backspace' || event.key === 'Meta')
			this.pressedDeleteKey = null;
	},

	/**
	 *
	 * @returns {boolean|null}
	 */
	isKeyPressedDelete: function()
	{
		return this.pressedDeleteKey
	},

	defineEscapeKey: function(event)
	{
		if(event.key === 'Escape')
		{
			this.pressedEscapeKey = true;
			BX.onCustomEvent('BX.TileGrid.Grid:defineEscapeKey', [this]);
		}
	},

	resetEscapeKey: function(event)
	{
		if(event.key === 'Escape')
			this.pressedEscapeKey = null;
	},

	isKeyPressedEscape: function()
	{
		return this.pressedEscapeKey;
	},

	/**
	 * Control button control
	 */
	defineControlKey: function(event)
	{
		if(event.key === 'Meta' || event.key === 'Control')
			this.pressedControlKey= true;
	},

	resetControlKey: function(event)
	{
		if(event.key === 'Meta' || event.key === 'Control')
			this.pressedControlKey= null;
	},

	isKeyControlKey: function()
	{
		return this.pressedControlKey;
	},

	/**
	 * Shift button control
	 */
	defineShiftKey: function(event)
	{
		if(event.key === 'Shift')
			this.pressedShiftKey= true;
	},

	resetShiftKey: function(event)
	{
		if(event.key === 'Shift')
			this.pressedShiftKey= null;
	},

	/**
	 *
	 * @returns {boolean|null}
	 */
	isKeyPressedShift: function()
	{
		return this.pressedShiftKey;
	},

	setCurrentItem: function(item)
	{
		if(this.currentItem !== item)
			this.currentItem = item
	},

	getCurrentItem: function()
	{
		return this.currentItem
	},

	resetCurrentItem: function()
	{
		this.currentItem = null;
	},

	isFocusOnTile: function()
	{
		if (BX.getClass('BX.UI.Viewer.Instance') && BX.UI.Viewer.Instance.isOpen())
		{
			return false;
		}

		if (!document.activeElement)
		{
			return true;
		}

		var tagName = document.activeElement.tagName.toLowerCase();
		if (tagName === 'body')
		{
			return true;
		}

		if (
			tagName === 'input' ||
			tagName === 'select' ||
			tagName === 'textarea'
		)
		{
			return false;
		}

		return !!BX.findParent(document.activeElement, {className: 'ui-grid-tile'});
	},

	setFirstCurrentItem: function(item)
	{
		if(this.firstCurrentItem !== item)
			this.firstCurrentItem = item
	},

	getFirstCurrentItem: function()
	{
		return this.firstCurrentItem
	},

	resetFirstCurrentItem: function()
	{
		this.firstCurrentItem = null;
	},

	selectItem: function(item)
	{
		if(!item)
			return;

		BX.addClass(item.layout.container, 'ui-grid-tile-item-selected');
		item.selected = true;

		if(this.isLastSelectedItem())
			this.resetSetMultiSelectMode();

		BX.onCustomEvent('BX.TileGrid.Grid:selectItem', [item, this]);
	},

	unSelectItem: function(item)
	{
		if(!item)
			return;

		BX.removeClass(item.layout.container, 'ui-grid-tile-item-selected');
		item.selected = false;

		if(this.isLastSelectedItem())
			this.resetSetMultiSelectMode();

		BX.onCustomEvent('BX.TileGrid.Grid:unSelectItem', [item, this]);
	},

	isLastSelectedItem: function()
	{
		for (var i = 0; i < this.items.length; i++)
		{
			if(this.items[i].selected)
				return false
		}

		BX.onCustomEvent('BX.TileGrid.Grid:lastSelectedItem');

		return true
	},

	checkItem: function(item)
	{
		if(!item)
			return;

		BX.addClass(item.layout.checkbox, 'ui-grid-tile-item-checkbox-checked');
		item.checked = true;

		if(!this.isMultiSelectMode())
			this.setMultiSelectMode();

		BX.onCustomEvent('BX.TileGrid.Grid:checkItem', [item, this]);
	},

	unCheckItem: function(item)
	{
		BX.removeClass(item.layout.checkbox, 'ui-grid-tile-item-checkbox-checked');
		item.checked = false;

		BX.onCustomEvent('BX.TileGrid.Grid:unCheckItem', [item, this]);
	},

	setMultiSelectMode: function()
	{
		BX.addClass(this.container, 'ui-grid-tile-multi-select-mode');
		this.multiSelectMode = true;

		BX.onCustomEvent('BX.TileGrid.Grid:multiSelectModeOn', [this]);
	},

	resetSetMultiSelectMode: function()
	{
		BX.removeClass(this.container, 'ui-grid-tile-multi-select-mode');
		this.multiSelectMode = null;

		BX.onCustomEvent('BX.TileGrid.Grid:multiSelectModeOff', [this]);
	},

	isMultiSelectMode: function()
	{
		return this.multiSelectMode
	},

	resetSelectAllItems: function()
	{
		if(this.isMultiSelectMode())
			this.resetSetMultiSelectMode();

		BX.onCustomEvent('BX.TileGrid.Grid:resetSelectAllItems', [this]);

		for (var i = 0; i < this.items.length; i++)
		{
			this.items[i].selected = false;
			this.items[i].checked = false;
			BX.removeClass(this.items[i].layout.checkbox, 'ui-grid-tile-item-checkbox-checked');
			BX.removeClass(this.items[i].layout.container, 'ui-grid-tile-item-selected');
		}

		BX.onCustomEvent('BX.TileGrid.Grid:afterResetSelectAllItems', [this]);
	}
};


var scrollToSmooth = function (relativeScrollY)
{
	var scrollTop = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
	var easing = new BX.easing({
		duration: 300,
		start: {
			scrollY: scrollTop
		},
		finish: {
			scrollY: scrollTop + relativeScrollY
		},
		transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
		step: function (state)
		{
			window.scrollTo(0, state.scrollY);
		}
	});
	easing.animate();
}

})();
