;(function () {

	'use strict';

	BX.namespace('BX.UI.Viewer');

	BX.UI.Viewer.Controller = function(options)
	{
		/**
		 * @type {BX.UI.Viewer.Item[]}
		 */
		this.items = null;
		this.currentIndex = null;
		this.handlers = {
			handleCarouselItemError: this.handleCarouselItemError.bind(this),
		};

		this.baseContainer = options.baseContainer || document.body;

		this.setItems(options.items || []);

		this.isBodyPaddingAdded = null;
		this.cycleMode = options.hasOwnProperty('cycleMode') ? options.cycleMode : true;
		this.stretch = options.hasOwnProperty('stretch') ? options.stretch : false;
		this.cachedData = {};
		this.optionsByGroup = {};
		this.layout = {
			container: null,
			content: null,
			inner: null,
			title: null,
			itemContainer: null,
			next: null,
			prev: null,
			close: null,
			downloadBtn: null,
			moreBtn: null,
			error: null,
			loader: null,
			loaderContainer: null,
			loaderText: null,
			defaultActions: null,
			extraActions: null,
			carouselItems: null,
			carouselContainer: null,
			carouselScrollable: null,
		};

		// Compatibility for BX.Disk.Viewer.Actions.runActionEdit
		this.actionPanel = {
			getItemById: () => {},
		};

		this.maxParallelLoads = 3;
		this.loadingItems = new Set();
		this.moreMenu = null;

		this.eventsAlreadyBinded = false;

		this.init();
	};

	BX.UI.Viewer.Controller.prototype = {
		/**
		 * @param {HTMLElement} node
		 * @return {BX.Promise}
		 */
		buildItemListByNode: function (node)
		{
			var promise = new BX.Promise();
			var nodes = [];

			if (this.isSeparateItem(node))
			{
				nodes = [node];
			}
			else if(node.dataset.viewerGroupBy)
			{
				nodes = [].slice.call(node.ownerDocument.querySelectorAll("[data-viewer][data-viewer-group-by='" + node.dataset.viewerGroupBy + "']"));
			}
			else
			{
				nodes = [node];
			}

			this.loadExtensions(this.collectExtensionsForItems(nodes)).then(function (){
				var items = nodes.map(function(node) {
					return BX.UI.Viewer.buildItemByNode(node);
				});

				promise.fulfill(items);
			}.bind(this));

			return promise;
		},

		/**
		 * @param {HTMLElement} node
		 * @return {boolean}
		 */
		isSeparateItem: function (node)
		{
			return node.dataset.viewerSeparateItem;
		},

		shouldProcessSeparateMode: function (items)
		{
			return items.length === 1 && items[0].isSeparateItem();
		},

		shouldRunViewer: function (node)
		{
			if (!BX.type.isDomNode(node) || !node.dataset)
			{
				return false;
			}

			if (!node.dataset.hasOwnProperty('viewer'))
			{
				return false;
			}

			return true;
		},

		/**
		 *
		 * @param {HTMLElement[]} nodes
		 * @return {Array}
		 */
		collectExtensionsForItems: function (nodes)
		{
			var extensionSet = new Set();
			nodes.forEach(function (node) {
				if (BX.type.isString(node.dataset.viewerExtension))
				{
					extensionSet.add(node.dataset.viewerExtension);
				}
			});

			var extensions = [];
			extensionSet.forEach(function (ext) {
				if (shouldLoadExtensions(ext))
				{
					extensions.push(ext);
				}
			});

			return extensions;
		},

		/**
		 * @param {MouseEvent} event
		 * @return {HTMLElement|null}
		 */
		extractTargetFromEvent: function (event)
		{
			var target = BX.getEventTarget(event);

			var shouldRunViewer = false;
			var maxDepth = 8;
			do
			{
				if (this.shouldRunViewer(target))
				{
					shouldRunViewer = true;
					break;
				}

				target = target.parentNode;
				maxDepth--;
			}
			while (maxDepth > 0 && target);

			return shouldRunViewer? target : null;
		},

		handleDocumentClick: function (event)
		{
			var target = this.extractTargetFromEvent(event);
			if (!target)
			{
				return;
			}

			if (target.tagName !== 'A' && target.closest('a[target="_blank"]'))
			{
				return false;
			}

			event.preventDefault();
			this.buildItemListByNode(target).then(function(items){
				if (items.length === 0)
				{
					return;
				}

				if (this.shouldProcessSeparateMode(items))
				{
					this.setItems(items).then(function(){
						this.openSeparate(0);
					}.bind(this));

					return;
				}

				// shortcut for download
				if ((BX.browser.IsMac() && event.metaKey) || event.ctrlKey)
				{
					this.runActionByNode(target, 'download');
				}
				else
				{
					this.setItems(items).then(function(){
						this.open(this.getIndexByNode(target));
					}.bind(this));
				}
			}.bind(this));
		},

		bindEvents: function ()
		{
			if (this.eventsAlreadyBinded)
			{
				return;
			}

			this.eventsAlreadyBinded = true;

			this.handlers.keyPress = this.handleKeyPress.bind(this);
			this.handlers.touchStart = this.handleTouchStart.bind(this);
			this.handlers.touchEnd = this.handleTouchEnd.bind(this);
			this.handlers.resize = this.handleWindowResize.bind(this);
			this.handlers.showNext = this.showNext.bind(this);
			this.handlers.showPrev = this.showPrev.bind(this);
			this.handlers.close = this.close.bind(this);
			this.handlers.handleClickOnItemContainer = this.handleClickOnItemContainer.bind(this);
			this.handlers.handleSliderCloseByEsc = this.handleSliderCloseByEsc.bind(this);

			BX.bind(document, 'keydown', this.handlers.keyPress);
			BX.bind(window, 'resize', this.handlers.resize);
			BX.bind(this.getItemContainer(), 'touchstart', this.handlers.touchStart);
			BX.bind(this.getItemContainer(), 'touchend', this.handlers.touchEnd);

			BX.bind(this.getItemContainer(), 'click', this.handlers.handleClickOnItemContainer);
			BX.bind(this.getNextButton(), 'click', this.handlers.showNext);
			BX.bind(this.getPrevButton(), 'click', this.handlers.showPrev);
			BX.bind(this.getCloseButton(), 'click', this.handlers.close);

			BX.addCustomEvent('SidePanel.Slider:onCloseByEsc', this.handlers.handleSliderCloseByEsc);
		},

		handleVisibleControls: function(ev)
		{
			if (BX.browser.IsMobile() || BX.hasClass(document.documentElement, 'bx-touch'))
			{
				return;
			}

			if (this._timerIdReadingMode)
			{
				clearTimeout(this._timerIdReadingMode);
			}

			if (!this.cursorInPerimeter(ev) || BX.findParent(ev.target, {className: 'ui-viewer-next'}) || BX.findParent(ev.target, {className: 'ui-viewer-prev'}))
			{
				this.disableReadingMode();
			}
			else
			{
				this._timerIdReadingMode = setTimeout(function () {
					this.enableReadingMode();
				}.bind(this), 2800);
			}
		},

		handleMoreBtnClick()
		{
			if (this.moreMenu === null)
			{
				const item = this.getCurrentItem();

				const menuItems = [
					...item.getMoreMenuItems(),
					...this.createMoreMenuItems(item),
				];

				if (menuItems.length === 0)
				{
					return;
				}

				this.moreMenu = new BX.Main.Menu({
					angle: true,
					minWidth: 150,
					bindElement: this.getMoreButton(),
					offsetLeft: 30,
					offsetTop: -20,
					items: menuItems,
					cacheable: false,
					events: {
						onDestroy: () => {
							this.moreMenu = null;
						},
					},
				});
			}

			this.moreMenu.show();
		},

		enableReadingMode: function(withTimer)
		{
			if (BX.browser.IsMobile() || !this.isOnTop())
			{
				return;
			}

			if(withTimer)
			{
				this._timerIdReadingMode = setTimeout(function()
				{
					this.layout.container.classList.add('ui-viewer-reading-mode');
				}.bind(this), 5000);

				return;
			}

			this.layout.container.classList.add('ui-viewer-reading-mode');
		},

		disableReadingMode: function()
		{
			if(this._timerIdReadingMode)
			{
				clearTimeout(this._timerIdReadingMode);
			}

			this.layout.container.classList.remove('ui-viewer-reading-mode');
		},

		cursorInPerimeter: function(ev)
		{
			var offsetVertical = document.body.clientHeight / 100 * 30;
			var offsetHorizontal = document.body.clientWidth / 100 * 30;

			offsetHorizontal < 300 ? offsetHorizontal = 300 : null;
			offsetVertical < 300 ? offsetVertical = 300 : null;

			if(	ev.y < offsetVertical || ev.y > document.body.clientHeight - offsetVertical ||
				ev.x < offsetHorizontal || ev.x > document.body.clientWidth - offsetHorizontal)
			{
				return false
			}

			return true;
		},

		/**
		 * @param {BX.SidePanel.Event} event
		 */
		handleSliderCloseByEsc: function(event)
		{
			if (this.isOpen() && (this.getZindex() > event.getSlider().getZindex()))
			{
				event.denyAction();
			}
		},

		adjustViewport: function ()
		{
			var viewportNode = document.querySelector('[name="viewport"]');
			if (!viewportNode)
			{
				return;
			}
			this._viewportContent = viewportNode.getAttribute('content');
			viewportNode.setAttribute('content', 'width=device-width, user-scalable=no');
		},

		restoreViewport: function ()
		{
			var viewportNode = document.querySelector('[name="viewport"]');
			if (!this._viewportContent || !viewportNode)
			{
				return;
			}

			viewportNode.setAttribute('content', this._viewportContent);
		},

		unbindEvents: function()
		{
			this.eventsAlreadyBinded = false;

			BX.unbind(document, 'keydown', this.handlers.keyPress);
			BX.unbind(window, 'resize', this.handlers.resize);
			BX.unbind(this.getItemContainer(), 'touchstart', this.handlers.touchStart);
			BX.unbind(this.getItemContainer(), 'touchend', this.handlers.touchEnd);

			BX.unbind(this.getItemContainer(), 'click', this.handlers.handleClickOnItemContainer);
			BX.unbind(this.getNextButton(), 'click', this.handlers.showNext);
			BX.unbind(this.getPrevButton(), 'click', this.handlers.showPrev);
			BX.unbind(this.getCloseButton(), 'click', this.handlers.close);
		},

		init: function ()
		{},

		openByNode: function (node)
		{
			this.buildItemListByNode(node).then(function (items) {
				if (items.length === 0)
				{
					return;
				}

				if (this.shouldProcessSeparateMode(items))
				{
					this.setItems(items).then(function(){
						this.openSeparate(0);
					}.bind(this));

					return;
				}

				this.setItems(items).then(function(){
					this.open(this.getIndexByNode(node));
				}.bind(this));
			}.bind(this));
		},

		runActionByNode: function (node, actionId, additionalParams)
		{
			this.buildItemListByNode(node).then(function (items) {
				if (items.length === 0)
				{
					return;
				}

				this.setItems(items).then(function(){
					this.runAction(this.getIndexByNode(node), actionId, additionalParams);
				}.bind(this));
			}.bind(this));
		},

		runAction(index, actionId, additionalParams)
		{
			const item = this.getItemByIndex(index);
			if (actionId === 'download')
			{
				// Compatibility: a download action doesn't exist anymore
				window.open(item.getDownloadUrl(), this.isExternalLink(item.getDownloadUrl()) ? '_blank' : '_self');

				return;
			}

			var actionToRun = item.getActions().find(function (action) {
				return action.id === actionId;
			});

			console.log('actionToRun', actionId, actionToRun);
			if (!BX.type.isFunction(actionToRun.action))
			{
				console.log('action is not a function');
				return;
			}

			actionToRun.action.call(this, item, additionalParams);
		},

		/**
		 * @returns {number}
		 */
		getZindex: function ()
		{
			var container = this.getViewerContainer();
			if (!container.parentNode)
			{
				return 0;
			}

			var component = BX.ZIndexManager.getComponent(container);

			return component.getZIndex();
		},

		/**
		 * @param items
		 * @return {Promise<extensionsCollection>}
		 */
		setItems: function (items)
		{
			if (!BX.type.isArray(items))
			{
				throw new Error("BX.UI.Viewer.Controller.setItems: 'items' has to be Array.");
			}

			BX.onCustomEvent('BX.UI.Viewer.Controller:onSetItems', [this, items]);

			if (this.items !== null)
			{
				this.items.forEach((item) => {
					item.destroy();
				});
			}

			this.items = items;
			this.items.forEach(function (item) {
				item.setController(this);
			}, this);

			return this.loadExtensions(this.collectExtensionsForAction(items));
		},

		/**
		 *
		 * @param {String|String[]} extensions - Extension name
		 * @return {Promise<extensionsCollection>}
		 */
		loadExtensions: function (extensions)
		{
			return BX.loadExt(extensions);
		},

		/**
		 *
		 * @param {BX.UI.Viewer.Item[]} items
		 * @return {Array}
		 */
		collectExtensionsForAction: function (items)
		{
			var extensionSet = new Set();

			items.forEach(function (item) {
				var actions = item.getActions() || [];
				actions.forEach(function (action) {
					if (!action.extension)
					{
						return;
					}

					if (!BX.type.isArray(action.extension))
					{
						action.extension = [action.extension];
					}

					action.extension.forEach(function (ext) {
						extensionSet.add(ext);
					});
				});
			});

			var extensions = [];
			extensionSet.forEach(function (ext) {
				if (shouldLoadExtensions(ext))
				{
					extensions.push(ext);
				}
			});

			return extensions;
		},

		appendItem: function (item)
		{
			if (!(item instanceof BX.UI.Viewer.Item))
			{
				throw new Error("BX.UI.Viewer.Controller.appendItem: 'item' has to be instance of BX.UI.Viewer.Item.");
			}

			item.setController(this);
			this.items.push(item);
		},

		/**
		 * Reloads item in collection items. It means that we replace old item by the one new
		 * which is the copy.
		 * @param {BX.UI.Viewer.Item} item
		 * @param {Object} options
		 */
		reloadItem: function (item, options)
		{
			options = options || {};

			if (!(item instanceof BX.UI.Viewer.Item))
			{
				throw new Error("BX.UI.Viewer.Controller.reloadItem: 'item' has to be instance of BX.UI.Viewer.Item.");
			}

			var index = this.items.indexOf(item);
			if (index === -1)
			{
				throw new Error("BX.UI.Viewer.Controller.reloadItem: there is no 'item' in items to reload.");
			}

			var newItem = null;
			if (item.sourceNode)
			{
				newItem = BX.UI.Viewer.buildItemByNode(item.sourceNode);
			}
			else
			{
				newItem = new item.constructor(item.options);
			}

			item.destroy();

			newItem.setController(this);
			newItem.applyReloadOptions(options);

			this.items[index] = newItem;
		},

		show: function (index, options)
		{
			options = options || {};
			if (typeof index === 'undefined')
			{
				index = 0;
			}

			BX.onCustomEvent('BX.UI.Viewer.Controller:onBeforeShow', [this, index]);

			var item = this.getItemByIndex(index);
			if (!item)
			{
				return;
			}

			this.hideErrorBlock();
			this.hideCurrentItem();
			this.disableReadingMode();
			this.showLoading();

			const moveToStart = index === 0 && this.currentIndex === this.items.length - 1;
			const moveToEnd = this.currentIndex === 0 && index === this.items.length - 1;

			const direction = (index < this.currentIndex && !moveToStart) || moveToEnd ? 'backward' : 'forward';
			this.currentIndex = index;

			this.updateActions(this.getCurrentItem());
			this.observeItemLoading(item);

			const title = item.getTitle();
			this.setTitle(BX.Type.isStringFilled(title) ? title : '');

			item.load()
				.then((loadedItem) => {
					this.unobserveItemLoading(item);

					if (this.getCurrentItem() === loadedItem)
					{
						console.log('show item');
						this.processShowItem(loadedItem, options);
						if (options.asFirstToShow)
						{
							loadedItem.asFirstToShow();
						}
					}
				})
				.catch((reason) => {
					this.unobserveItemLoading(item);

					var loadedItem = reason.item;

					console.log('catch viewer');

					BX.onCustomEvent('BX.UI.Viewer.Controller:onItemError', [this, reason, loadedItem]);

					if (this.getCurrentItem() === loadedItem)
					{
						this.processError(reason, loadedItem);
					}

					BX.onCustomEvent('BX.UI.Viewer.Controller:onAfterProcessItemError', [this, reason, loadedItem]);
				})
			;

			this.updateControls();
			this.lockScroll();
			this.adjustViewerHeight();

			const cycleMove = this.items.length > 20 && (moveToStart || moveToEnd);
			this.selectCarouselItem(this.currentIndex, options.asFirstToShow !== true && !cycleMove);

			this.processPreload(this.currentIndex, direction);
		},

		processPreload(fromIndex, direction)
		{
			if (this.maxParallelLoads < 1)
			{
				return;
			}

			const maxPreloads = this.maxParallelLoads - 1;
			let preloadIndex = direction === 'backward' ? fromIndex - 1 : fromIndex + 1;
			const shouldPreload = () => {
				if (direction === 'backward')
				{
					return preloadIndex > (fromIndex - 1 - maxPreloads);
				}

				return preloadIndex < (maxPreloads + fromIndex + 1);
			};

			while (shouldPreload())
			{
				const itemByIndex = this.getItemByIndex(preloadIndex);
				if (!itemByIndex)
				{
					break;
				}

				console.log('Trying to preload', preloadIndex);

				this.observeItemLoading(itemByIndex);
				itemByIndex.load()
					.then(() => {
						this.unobserveItemLoading(itemByIndex);
					})
					.catch(() => {
						this.unobserveItemLoading(itemByIndex);
					})
				;

				if (direction === 'backward')
				{
					preloadIndex--;
				}
				else
				{
					preloadIndex++;
				}
			}
		},

		/**
		 * Reloads item and rerun show if item was as current item.
		 * @param {BX.UI.Viewer.Item} item
		 * @param {Object} options
		 */
		reload: function (item, options)
		{
			var isCurrentVisibleItem = this.getCurrentItem() === item;
			this.reloadItem(item, options);

			if (isCurrentVisibleItem)
			{
				console.log('reload');
				this.show(this.currentIndex);
			}
		},

		/**
		 * Reloads and show current item.
		 * @param {?Object} options
		 */
		reloadCurrentItem: function (options)
		{
			this.reload(this.getCurrentItem(), options || {});
		},

		/**
		 * @param {BX.UI.Viewer.Item} item
		 */
		processShowItem: function(item, options)
		{

			this.hideCurrentItem();
			this.hideLoading();
			this.unlockExtraActions();

			var contentWrapper = BX.create('div', {
				props: {
					className: 'ui-viewer-inner-content-wrapper'
				},
			});

			const title = item.getTitle();
			this.setTitle(BX.Type.isStringFilled(title) ? title : '');

			var fragment = document.createDocumentFragment();
			fragment.appendChild(item.render(options));
			contentWrapper.appendChild(fragment);
			var classList = this.layout.container.classList;
			var containerModifiers = item.listContainerModifiers();
			if (containerModifiers.length)
			{
				classList.add.apply(classList, containerModifiers);
			}

			this.layout.itemContainer.appendChild(contentWrapper);

			item.afterRender();
			this.adjustControlsSize(item.getContentWidth());

			BX.onCustomEvent('BX.UI.Viewer.Controller:onAfterShow', [this, item]);
		},

		adjustControlsSize: function(contentWidth)
		{
			this.getNextButton().style.width = null;
			this.getPrevButton().style.width = null;
			this.getNextButton().style.maxWidth = null;
			this.getPrevButton().style.maxWidth = null;

			if (contentWidth && BX.Type.isFunction(contentWidth.then))
			{
				contentWidth.then((width) => {
					let controlWidth = Math.floor((document.body.offsetWidth - Math.ceil(width)) / 2);
					controlWidth = Math.max(controlWidth, 70);

					this.getNextButton().style.width = controlWidth + 'px';
					this.getPrevButton().style.width = controlWidth + 'px';
					this.getNextButton().style.maxWidth = 'none';
					this.getPrevButton().style.maxWidth = 'none';
				});
			}
		},

		/**
		 * @param {Object} reason
		 * @param {BX.UI.Viewer.Item} item
		 */
		processError: function(reason, item)
		{
			reason = reason || {};

			var message = reason.message || null;
			if (BX.type.isArray(reason.errors) && reason.errors.length)
			{
				if (reason.errors[0].code === 1000 && !reason.message)
				{
					message = BX.message("JS_UI_VIEWER_ITEM_TRANSFORMATION_ERROR_1").replace('#DOWNLOAD_LINK#', item.getSrc());
				}
			}

			this.hideCurrentItem();
			this.hideLoading();
			this.cleanExtraActions();

			var contentWrapper = BX.create('div', {
				props: {
					className: 'ui-viewer-inner-content-wrapper'
				}
			});

			var title = item.getTitle();
			this.setTitle(BX.Type.isStringFilled(title) ? title : '');

			var options = {};
			if (message)
			{
				options.title = message;
			}
			if (reason.description)
			{
				options.description = reason.description;
			}
			contentWrapper.appendChild(this.getErrorBlock(options, item));

			this.layout.itemContainer.appendChild(contentWrapper);
		},

		hideErrorBlock: function()
		{
			if (this.layout.error)
			{
				BX.remove(this.layout.error);
			}
		},

		/**
		 * @param {Object} options
		 * @param {?string} [options.viewType='info']
		 * @param {?string} [options.title]
		 * @param {?string} [options.description]
		 * @param {BX.UI.Viewer.Item} item
		 * @return {null}
		 */
		getErrorBlock: function(options, item)
		{
			this.hideErrorBlock();

			var viewType = BX.prop.getString(options, 'viewType', 'info');
			var title = BX.prop.getString(options, 'title', BX.message("JS_UI_VIEWER_ITEM_TRANSFORMATION_ERROR_1").replace('#DOWNLOAD_LINK#', item.getSrc()));
			var description = BX.prop.getString(options, 'description', BX.message("JS_UI_VIEWER_ITEM_TRANSFORMATION_HINT"));

			this.layout.error = BX.create('div', {
				props: {
					className: 'ui-viewer-error'
				},
				style: {
					maxWidth: description? '440px' : null
				},
				children: [
					BX.create('div', {
						props: {
							className: 'ui-viewer-' + viewType + '-title'
						},
						html: title
					}),
					BX.create('div', {
						props: {
							className: 'ui-viewer-' + viewType + '-text'
						},
						html: description
					})
				]
			});

			return this.layout.error;
		},

		/**
		 * @param {String} link
		 * @return {boolean}
		 */
		isExternalLink: function (link)
		{
			var isAbsoluteLink = new RegExp('^([a-z]+://|//)', 'i');
			if (!isAbsoluteLink.test(link))
			{
				return false;
			}

			if (!BX.getClass('URL'))
			{
				return link.indexOf(location.hostname) === -1;
			}

			try
			{
				return (new URL(link)).hostname !== location.hostname;
			}
			catch(e)
			{}

			return true;
		},

		/**
	 		* @param {BX.UI.Viewer.Item} item
	 	*/
		createMoreMenuItems(item)
		{
			return item.getActions().map((action) => {
				const menuItem = { ...action };

				if (!menuItem.href && BX.type.isFunction(menuItem.action))
				{
					const fn = menuItem.action;
					menuItem.onclick = (event, menuItem) => {
						fn.call(this, item, { event, menuItem });
						this.moreMenu?.close();
					};
				}

				menuItem.disabled = menuItem.disableBeforeLoaded === true && item.isLoaded === false;

				if (BX.Type.isArrayFilled(menuItem.items))
				{
					menuItem.items = menuItem.items.map((subMenuItem) => {
						const newMenuItem = { ...subMenuItem };
						if (BX.type.isString(newMenuItem.onclick))
						{
							const onclick = new Function('event', 'popupItem', newMenuItem.onclick);
							newMenuItem.onclick = (event, popupItem) => {
								onclick(event, popupItem);
								this.moreMenu?.close();
							};
						}

						return newMenuItem;
					});
				}

				return menuItem;
			});
		},

		/**
		 * @param {BX.UI.Viewer.Item} item
		 */
		refineItemActions(item)
		{
			const defaultActions = {
				download: {
					id: 'download',
					type: 'download',
					text: BX.Loc.getMessage('JS_UI_VIEWER_ITEM_ACTION_DOWNLOAD'),
					href: item.src,
					buttonIconClass: 'ui-btn-icon-download',
				},
				edit: {
					id: 'edit',
					type: 'edit',
					text: BX.Loc.getMessage('JS_UI_VIEWER_ITEM_ACTION_EDIT'),
					buttonIconClass: 'ui-btn-icon-edit',
				},
				share: {
					id: 'share',
					type: 'share',
					text: BX.Loc.getMessage('JS_UI_VIEWER_ITEM_ACTION_SHARE'),
					buttonIconClass: 'ui-btn-icon-share',
				},
				print: {
					id: 'print',
					type: 'print',
					text: BX.Loc.getMessage('JS_UI_VIEWER_ITEM_ACTION_PRINT'),
					buttonIconClass: 'ui-btn-icon-print ui-btn-disabled',
					disableBeforeLoaded: true,
				},
				info: {
					id: 'info',
					type: 'info',
					text: BX.Loc.getMessage('JS_UI_VIEWER_ITEM_ACTION_INFO'),
					buttonIconClass: 'ui-btn-icon-info ui-action-panel-item-last',
				},
				delete: {
					id: 'delete',
					type: 'delete',
					text: BX.Loc.getMessage('JS_UI_VIEWER_ITEM_ACTION_DELETE'),
					buttonIconClass: 'ui-btn-icon-remove',
				},
			};

			const actions = [];
			for (const [, nakedAction] of item.getNakedActions().entries())
			{
				const action = (
					defaultActions[nakedAction.type]
						? BX.mergeEx(defaultActions[nakedAction.type], nakedAction)
						: nakedAction
				);

				if (!action.id)
				{
					action.id = action.type;
				}

				if (action.id === 'download' && BX.Type.isStringFilled(action.href))
				{
					item.setDownloadUrl(action.href);

					continue;
				}

				if (!action.action && action.href)
				{
					action.action = () => {
						window.open(action.href, this.isExternalLink(action.href) ? '_blank' : '_self');
					};
				}

				if (BX.type.isString(action.action))
				{
					const params = action.params || {};
					const actionString = action.action;

					action.action = (item, additionalParams) => {
						try
						{
							const fn = eval(actionString);
							fn.call(this, item, params, additionalParams);
						}
						catch (e)
						{
							console.log(e);
						}
					};
				}

				actions.push(action);
			}

			return actions;
		},

		getLoader: function(options)
		{
			if (!this.layout.loader)
			{
				this.layout.loader = BX.create('div', {
					props: {
						className: 'ui-viewer-loader'
					},
					style: {
						zIndex: -1
					},
					children: [
						this.layout.loaderContainer = BX.create('div', {
							props: {
								className: 'ui-viewer-loader-container'
							}
						}),
						this.layout.loaderText = BX.create('div', {
							props: {
								className: 'ui-viewer-loader-text'
							},
							text: ''
						})
					]
				});

				var loader = new BX.Loader({size: 130});
				loader.show(this.layout.loaderContainer);
			}

			return this.layout.loader;
		},

		getPrevButton: function()
		{
			if (!this.layout.prev)
			{
				this.layout.prev = BX.create('div', {
					props: {
						className: 'ui-viewer-prev'
					},
					events: {
						mousewheel: function(event) {
							this.handleMouseWheelOnControlButton(this.layout.prev, event);
						}.bind(this)
					}
				})
			}

			return this.layout.prev;
		},

		getNextButton: function()
		{
			if (!this.layout.next)
			{
				this.layout.next = BX.create('div', {
					props: {
						className: 'ui-viewer-next'
					},
					events: {
						mousewheel: function(event) {
							this.handleMouseWheelOnControlButton(this.layout.next, event);
						}.bind(this)
					}
				});
			}

			return this.layout.next;
		},

		handleMouseWheelOnControlButton: function(controlNode, event)
		{
			if (this._timeoutIdMouseWheel)
			{
				clearTimeout(this._timeoutIdMouseWheel);
			}

			controlNode.style.pointerEvents = 'none';

			this._timeoutIdMouseWheel = setTimeout(function() {
				controlNode.style.pointerEvents = null;
			}, 50);
		},

		getCloseButton: function()
		{
			if (!this.layout.close)
			{
				this.layout.close = BX.create('div', {
					props: {
						className: 'ui-viewer-close',
					},
					html:
						'<div class="ui-viewer-close-icon">'
							+ '<div class="ui-icon-set --cross-40"></div>'
						+ '</div>'
				});
			}

			return this.layout.close;
		},

		getDownloadButton: function()
		{
			if (!this.layout.downloadBtn)
			{
				this.layout.downloadBtn = BX.Tag.render`
					<a 
						class="ui-viewer-download-btn" 
						target="_blank" 
						title="${BX.Text.encode(BX.Loc.getMessage('JS_UI_VIEWER_ITEM_ACTION_DOWNLOAD'))}"
						href="" 
						download
					>
						<div class="ui-icon-set --download ui-viewer-download-btn-icon"></div>
					</a>
				`;
			}

			return this.layout.downloadBtn;
		},

		getMoreButton: function()
		{
			if (!this.layout.moreBtn)
			{
				this.layout.moreBtn = BX.Tag.render`
					<div class="ui-viewer-more-btn" onclick="${this.handleMoreBtnClick.bind(this)}">
						<div class="ui-icon-set --more ui-viewer-more-btn-icon"></div>
					</div>
				`;
			}

			return this.layout.moreBtn;
		},

		isOpen: function ()
		{
			return this._isOpen;
		},

		addBodyPadding: function()
		{
			var padding = window.innerWidth - document.documentElement.clientWidth;

			if (BX.getClass('BXIM.messenger.popupMessenger') ||
				padding === 0)
			{
				return;
			}

			document.body.style.paddingRight = padding + 'px';

			var imBar = document.getElementById('bx-im-bar');
			if(imBar)
			{
				var borderColor = 'rgb(238, 242, 244)';

				if(document.body.classList.contains('bitrix24-light-theme'))
				{
					borderColor = 'rgba(255, 255, 255, .1)';
				}

				if(document.body.classList.contains('bitrix24-dark-theme'))
				{
					borderColor = 'rgba(82, 92, 105, .1)';
				}

				imBar.style.borderRight = padding + 'px solid ' + borderColor;
			}

			this.isBodyPaddingAdded = true;
		},

		removeBodyPadding: function()
		{
			document.body.style.removeProperty('padding-right');

			var imBar = document.getElementById('bx-im-bar');
			if (imBar)
			{
				imBar.style.removeProperty('border-right');
			}

			this.isBodyPaddingAdded = false;
		},

		openSeparate: function(index)
		{
			var item = this.getItemByIndex(index);
			if (!item)
			{
				return;
			}

			item.load()
				.then(function (loadedItem) {}.bind(this))
				.catch(function (reason) {
					var loadedItem = reason.item;

					console.log('catch viewer');

					BX.onCustomEvent('BX.UI.Viewer.Controller:onItemError', [this, reason, loadedItem]);

					if (this.getCurrentItem() === loadedItem)
					{
						this.processError(reason, loadedItem);
					}

					BX.onCustomEvent('BX.UI.Viewer.Controller:onAfterProcessItemError', [this, reason, loadedItem]);
				}.bind(this));
		},

		open: function(index)
		{

			this.adjustViewport();
			this.addBodyPadding();

			var container = this.getViewerContainer();

			const baseContainer = this.baseContainer || document.body;
			baseContainer.appendChild(container);

			BX.focus(container);

			var component = BX.ZIndexManager.getComponent(container);
			if (!component)
			{
				BX.ZIndexManager.register(container);
			}

			BX.ZIndexManager.bringToFront(container);

			this.createCarouselItems(index);

			this.show(index, {
				asFirstToShow: true,
			});

			this.bindEvents();

			this._isOpen = true;
		},

		setTitle: function(title)
		{
			if (BX.Type.isStringFilled(title))
			{
				this.getTitleContainer().textContent = title;
			}
		},

		getTitleContainer: function()
		{
			if (!this.layout.title)
			{
				this.layout.title = BX.Tag.render`
					<span class="ui-viewer-title-text"></span>
				`;
			}

			return this.layout.title;
		},

		getExtraActions: function()
		{
			if (!this.layout.extraActions)
			{
				this.layout.extraActions = BX.Tag.render`
					<div class="ui-viewer-extra-actions"></div>
				`;
			}

			return this.layout.extraActions;
		},

		getDefaultActions: function()
		{
			if (!this.layout.defaultActions)
			{
				this.layout.defaultActions = BX.Tag.render`
					<div class="ui-viewer-default-actions">
						${this.getDownloadButton()}
						${this.getMoreButton()}
					</div>
				`;
			}

			return this.layout.defaultActions;
		},

		observeItemLoading(item)
		{
			const supportAbort = Object.hasOwn(Object.getPrototypeOf(item), 'abort');
			if (item.isLoaded === true || !supportAbort)
			{
				return;
			}

			this.loadingItems.add(item);

			if (this.loadingItems.size > this.maxParallelLoads)
			{
				for (const queueItem of this.loadingItems)
				{
					if (queueItem === item || queueItem === this.getCurrentItem())
					{
						continue;
					}

					const success = queueItem.abort();
					if (success === true)
					{
						this.loadingItems.delete(queueItem);
						break;
					}
				}
			}
		},

		unobserveItemLoading(item)
		{
			this.loadingItems.delete(item);
		},

		updateActions(item)
		{
			this.getDownloadButton().setAttribute('href', item.getDownloadUrl());

			if (item.getActions().length > 0)
			{
				BX.Dom.removeClass(this.getMoreButton(), '--hidden');
			}
			else
			{
				BX.Dom.addClass(this.getMoreButton(), '--hidden');
			}

			this.renderExtraActions(item);
			if (!item.isLoaded)
			{
				this.lockExtraActions();
			}
		},

		getCarouselContainer: function()
		{
			if (!this.layout.carouselContainer)
			{
				this.layout.carouselContainer = BX.Tag.render`
					<div class="ui-viewer-carousel">
						${this.getCarouselScrollable()}
					<div>
				`;
			}

			return this.layout.carouselContainer;
		},

		getCarouselScrollable: function()
		{
			if (!this.layout.carouselScrollable)
			{
				this.layout.carouselScrollable = BX.Tag.render`
					<div 
						class="ui-viewer-carousel-scrollable" 
						onscroll="${BX.Runtime.debounce(this.handleCarouselScroll, 200, this)}"
					>
						${this.getCarouselItems()}
					</div>
				`;
			}

			return this.layout.carouselScrollable;
		},

		getCarouselItems: function()
		{
			if (!this.layout.carouselItems)
			{
				this.layout.carouselItems = BX.Tag.render`
					<div class="ui-viewer-carousel-items" onclick="${this.handleCarouselClick.bind(this)}"></div>
				`;
			}

			return this.layout.carouselItems;
		},

		getCarouselItemWidth: function(selected = false)
		{
			const margin = 4 * 2;

			return selected ? 72 + margin : 46 + margin;
		},

		createCarouselItems: function(selectedIndex)
		{
			const itemsContainer = this.getCarouselItems();
			BX.Dom.clean(itemsContainer);
			BX.Dom.style(itemsContainer, { paddingLeft: null, paddingRight: null });
			BX.Dom.style(this.getCarouselContainer(), { width: null });

			BX.Dom.remove(this.getCarouselContainer());

			if (!this.isCarouselEnabled())
			{
				return;
			}

			for (const [index, item] of this.items.entries())
			{
				const container = BX.Tag.render`
					<div class="ui-viewer-carousel-item" data-index="${index}"></div>
				`;

				if (index === selectedIndex)
				{
					BX.Dom.addClass(container, '--selected');
				}

				if (item.getPreviewUrl() === null)
				{
					const icon = this.createItemIcon(item);
					icon.renderTo(container);
				}
				else
				{
					const img = document.createElement('img');
					img.className = 'ui-viewer-carousel-item-preview-img';
					img.onerror = this.handlers.handleCarouselItemError;

					// Preload first 10 items
					if (index <= 10 || item.getPreviewUrl().startsWith('blob:'))
					{
						img.src = item.getPreviewUrl();
					}
					else
					{
						img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
						container.dataset.lazyloadSrc = item.getPreviewUrl();
					}

					container.appendChild(img);
				}

				BX.Dom.append(container, this.getCarouselItems());
			}

			BX.Dom.append(this.getCarouselContainer(), this.getViewerContainer());

			const maxViewportWidth = this.getCarouselItemWidth() * 6 + this.getCarouselItemWidth(true); // 7 items
			const minViewportWidth = (
				(this.getItemsCount() - 1) * this.getCarouselItemWidth() + this.getCarouselItemWidth(true) / 2
			) * 2;

			const viewportWidth = Math.max(
				this.getCarouselContainer().offsetWidth,
				Math.min(minViewportWidth, maxViewportWidth),
			);
			const offset = viewportWidth / 2 - this.getCarouselItemWidth(true) / 2;

			BX.Dom.style(this.getCarouselContainer(), {
				width: `${viewportWidth}px`,
			});

			BX.Dom.style(itemsContainer, {
				paddingLeft: `${offset}px`,
				paddingRight: `${offset}px`,
			});
		},

		loadCarouselPreviews()
		{
			const itemWidth = this.getCarouselItemWidth();
			const scrollContainer = this.getCarouselItems().parentNode;
			const windowWidth = scrollContainer.parentNode.offsetWidth;
			const itemsInWindow = Math.round(windowWidth / itemWidth);
			const scrollLeft = scrollContainer.scrollLeft;

			const middleItemIndex = Math.ceil(scrollLeft / itemWidth);
			const leftItemIndex = Math.max(middleItemIndex - itemsInWindow, 0);
			const rightItemIndex = Math.min(middleItemIndex + itemsInWindow * 1.5, this.items.length - 1);

			for (let index = leftItemIndex; index <= rightItemIndex; index++)
			{
				const carouselItem = this.getCarouselItems().children[index];
				if (carouselItem.dataset.lazyloadSrc)
				{
					const img = carouselItem.firstElementChild;
					img.src = carouselItem.dataset.lazyloadSrc;

					BX.Dom.attr(carouselItem, 'data-lazyload-src', null);
				}
			}
		},

		getFileExtension: function(filename)
		{
			const position = BX.Type.isStringFilled(filename) ? filename.lastIndexOf('.') : -1;

			return position > 0 ? filename.slice(Math.max(0, position + 1)) : '';
		},

		handleCarouselClick: function(event)
		{
			const container = event.target.closest('.ui-viewer-carousel-item');
			if (!container)
			{
				return;
			}

			const index = Number(container.dataset.index);

			this.show(index);
		},

		handleCarouselScroll(event)
		{
			this.loadCarouselPreviews();
		},

		handleCarouselItemError(event)
		{
			const container = event.target.closest('.ui-viewer-carousel-item');
			const index = container.dataset.index;
			const item = this.getItemByIndex(index);

			BX.Dom.clean(container);

			const icon = this.createItemIcon(item);
			icon.renderTo(container);
		},

		createItemIcon(item)
		{
			return new BX.UI.Icons.Generator.FileIcon({
				name: this.getFileExtension(item.getTitle()) || '...',
				size: 18,
			});
		},

		isCarouselEnabled: function()
		{
			if (this.items.length < 2)
			{
				return false;
			}

			return this.items.some((item) => {
				return item.getPreviewUrl() !== null;
			});
		},

		selectCarouselItem: function(index, smooth = true)
		{
			if (!this.isCarouselEnabled())
			{
				return;
			}

			const currentSelected = this.getCarouselItems().querySelector('.ui-viewer-carousel-item.--selected');
			if (currentSelected)
			{
				BX.Dom.removeClass(currentSelected, '--selected');
			}

			const newSelected = this.getCarouselItems().querySelector(`.ui-viewer-carousel-item[data-index="${index}"]`);
			BX.Dom.addClass(newSelected, '--selected');

			this.adjustCarouselPosition(this.currentIndex, smooth);
			this.loadCarouselPreviews();
		},

		adjustCarouselPosition: function(index, smooth = true)
		{
			if (!this.isCarouselEnabled())
			{
				return false;
			}

			const scrollContainer = this.getCarouselItems().parentNode;
			const carouselItem = this.getCarouselItems().querySelector(`.ui-viewer-carousel-item[data-index="${index}"]`);
			if (carouselItem === null)
			{
				return false;
			}

			const scrollLeft = this.getCarouselItemWidth() * index;
			if (scrollContainer.scrollLeft !== scrollLeft)
			{
				scrollContainer.scrollTo({
					left: scrollLeft,
					behavior: smooth ? 'smooth' : 'instant',
				});

				return true;
			}

			return false;
		},

		hideCurrentItem: function()
		{
			if (this.getCurrentItem())
			{
				var classList = this.layout.container.classList;
				var containerModifiers = this.getCurrentItem().listContainerModifiers();
				if (containerModifiers.length)
				{
					classList.remove.apply(classList, containerModifiers);
				}

				this.getCurrentItem().beforeHide();
			}

			this.moreMenu?.close();

			BX.cleanNode(this.layout.itemContainer);
		},

		updateControls: function()
		{
			if (!this.allowToUseCycleMode() && this.currentIndex + 1 >= this.items.length)
			{
				BX.addClass(this.getNextButton(), 'ui-viewer-navigation-hide');
			}
			else
			{
				BX.removeClass(this.getNextButton(), 'ui-viewer-navigation-hide');
			}

			if (!this.allowToUseCycleMode() && this.currentIndex === 0)
			{
				BX.addClass(this.getPrevButton(), 'ui-viewer-navigation-hide');
			}
			else
			{
				BX.removeClass(this.getPrevButton(), 'ui-viewer-navigation-hide');
			}
		},

		/**
		 * @return {BX.UI.Viewer.Item}
		 */
		getCurrentItem: function ()
		{
			return this.getItemByIndex(this.currentIndex);
		},

		/**
		 * @param {HTMLElement} node
		 * @return {int}
		 */
		getIndexByNode: function (node)
		{
			var nodeIndex = null;
			this.items.forEach(function (item, index) {
				if (item.sourceNode === node)
				{
					nodeIndex = index;
				}
			});

			return nodeIndex;
		},

		/**
		 *
		 * @param index
		 * @returns BX.UI.Viewer.Item
		 */
		getItemByIndex: function (index)
		{
			index = parseInt(index, 10);

			BX.onCustomEvent('BX.UI.Viewer.Controller:onGetItemByIndex', [this, index]);

			if (index < 0 || (index - 1) > this.items.length)
			{
				return null;
			}

			return this.items[index];
		},

		getItemsCount: function()
		{
			return this.items.length;
		},

		handleClickOnItemContainer: function (event)
		{
			this.getCurrentItem().handleClickOnItemContainer(event);
		},

		allowToUseCycleMode: function ()
		{
			var cycleMode = this.cycleMode;
			var groupBy = this.getCurrentItem().getGroupBy();
			if (this.optionsByGroup[groupBy] && this.optionsByGroup[groupBy].hasOwnProperty('cycleMode'))
			{
				cycleMode = this.optionsByGroup[groupBy].cycleMode;
			}

			return this.items.length > 1 && cycleMode;
		},

		showNext: function ()
		{
			var index = this.currentIndex + 1;
			if (this.allowToUseCycleMode() && index >= this.items.length)
			{
				index = 0;
			}

			this.show(index);
		},

		showPrev: function ()
		{
			var index = this.currentIndex - 1;
			if (this.allowToUseCycleMode() && index === -1)
			{
				index = this.items.length - 1;
			}

			this.show(index);
		},

		close: function ()
		{
			this._isOpen = false;

			BX.onCustomEvent('BX.UI.Viewer.Controller:onClose', [this]);

			BX.addClass(this.layout.container, 'ui-viewer-hide');
			this.restoreViewport();
			this.hideCurrentItem();

			BX.bind(this.layout.container, 'transitionend', function()
			{
				BX.ZIndexManager.unregister(this.layout.container);
				BX.remove(this.layout.container);
				BX.removeClass(this.layout.container, 'ui-viewer-hide');
				BX.unbindAll(this.layout.container);

				this.unLockScroll();
				this.unbindEvents();
				this.disableReadingMode();
				if(this.isBodyPaddingAdded)
				{
					this.removeBodyPadding();
				}
			}.bind(this));

			// this.items = null;
			// this.currentIndex = null;
			// this.layout.container = null;
			// this.layout.close = null;
		},

		showLoading: function (options)
		{
			options = options || {};
			options.zIndex = BX.type.isNumber(options.zIndex)? options.zIndex : -1;

			this.layout.inner.appendChild(this.getLoader());
			this.setTextOnLoading(options.text || '');
			this.layout.loader.style.zIndex = options.zIndex;
		},

		setTextOnLoading: function (text)
		{
			this.layout.loaderText.textContent = text;
		},

		hideLoading: function ()
		{
			BX.remove(this.layout.loader);
		},

		lockScroll: function()
		{
			BX.addClass(document.body, 'ui-viewer-lock-body');
		},

		unLockScroll: function()
		{
			BX.removeClass(document.body, 'ui-viewer-lock-body');
		},

		renderExtraActions(item)
		{
			this.cleanExtraActions();
			BX.Dom.append(item.renderExtraActions(), this.getExtraActions());
		},

		cleanExtraActions()
		{
			BX.Dom.clean(this.getExtraActions());
		},

		lockExtraActions: function()
		{
			BX.Dom.addClass(this.getExtraActions(), '--locked');
		},

		unlockExtraActions: function()
		{
			BX.Dom.removeClass(this.getExtraActions(), '--locked');
		},

		handleWindowResize()
		{
			this.adjustViewerHeight();
			const item = this.getCurrentItem();
			if (item)
			{
				item.handleResize();
			}
		},

		adjustViewerHeight: function()
		{
			if(!this.layout.container || BX.browser.IsMobile())
				return;

			this.layout.container.style.height = document.documentElement.clientHeight + 'px';
		},

		getViewerContainer: function()
		{
			if (!this.layout.container)
			{
				this.layout.container = BX.create('div', {
					props: {
						className: 'ui-viewer',
						tabIndex: 22081990,
					},
					style: {
						height: window.clientHeight + 'px'
					},
					children: [
						BX.Tag.render`
							<div class="ui-viewer-header">
								<div class="ui-viewer-author"></div>
								<div class="ui-viewer-title">
									${this.getTitleContainer()}
								</div>
								<div class="ui-viewer-actions">
									${this.getExtraActions()}
									${this.getDefaultActions()}
									${this.getCloseButton()}
								</div>
							</div>
						`,
						this.layout.inner = BX.create('div', {
							props: {
								className: 'ui-viewer-inner',
							},
							children: [
								this.getItemContainer(),
							],
						}),
						this.getPrevButton(),
						this.getNextButton(),
					],
				});
			}

			return this.layout.container;
		},

		getItemContainer: function()
		{
			if (!this.layout.itemContainer)
			{
				this.layout.itemContainer = BX.create('div', {
					props: {
						className: 'ui-viewer-inner-content'
					}
				});
			}

			return this.layout.itemContainer
		},

		handleTouchStart: function(event)
		{
			var touchObject = event.changedTouches[0];
			this.swipeDirection = null;
			this.startX = touchObject.pageX;
			this.startY = touchObject.pageY;
			this.startTime = (new Date()).getTime();
			// event.preventDefault();

		},

		handleTouchEnd: function(event)
		{
			var touchObject = event.changedTouches[0];
			var allowedTime = 300;
			var threshold = 80;
			var restraint = 100;
			var distanceX = touchObject.pageX - this.startX;
			var distanceY = touchObject.pageY - this.startY;
			var elapsedTime = (new Date()).getTime() - this.startTime;

			if (elapsedTime <= allowedTime)
			{
				if (Math.abs(distanceX) >= threshold && Math.abs(distanceY) <= restraint)
				{
					this.swipeDirection = (distanceX < 0) ? 'left' : 'right';
				}
				// else if (Math.abs(distanceY) >= threshold && Math.abs(distanceX) <= restraint)
				// {
				// 	this.swipeDirection = (distanceY < 0) ? 'up' : 'down';
				// }
			}

			switch (this.swipeDirection)
			{
				case 'left':
					this.showPrev();
					break;
				case 'right':
					this.showNext();
					break;
			}

			// event.preventDefault();
		},

		isOnTop: function ()
		{
			if (!this.isOpen())
			{
				return false;
			}

			if (BX.getClass('BXIM.messenger') && BXIM.messenger.popupMessenger)
			{
				return true;
			}

			if (!BX.getClass('BX.SidePanel.Instance') || !BX.SidePanel.Instance.getTopSlider())
			{
				return true;
			}

			return this.getZindex() > BX.SidePanel.Instance.getTopSlider().getZindex();
		},

		handleKeyPress: function (event)
		{
			if (!this.isOnTop())
			{
				return;
			}

			if (event.metaKey)
			{
				return;
			}

			const handled = this.getCurrentItem().handleKeyPress(event);
			if (handled === true)
			{
				return;
			}

			switch (event.code)
			{
				case 'Space':
				case 'ArrowRight':
					this.showNext();
					event.preventDefault();
					event.stopPropagation();

					break;
				case 'ArrowLeft':
					this.showPrev();
					event.preventDefault();
					event.stopPropagation();

					break;
				case 'Escape':
					this.close();
					event.preventDefault();
					event.stopPropagation();

					break;
			}
		},

		setOptionsByGroup: function (groupBy, options)
		{
			this.optionsByGroup[groupBy] = options;

			return this;
		},

		getCachedData: function(id)
		{
			return this.cachedData[id];
		},

		setCachedData: function(id, data)
		{
			this.cachedData[id] = data;
		},

		unsetCachedData: function(id)
		{
			this.cachedData[id] = null;
		},

		/**
		 * @param {String} type
		 * @param {String} className
		 */
		addType: function (type, className)
		{
			return BX.UI.Viewer.addType(type, className);
		}
	};

	/**
	 * @extends {BX.UI.Viewer.Controller}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.InlineController = function (options)
	{
		options = options || {};

		BX.UI.Viewer.Controller.apply(this, arguments);
	};

	BX.UI.Viewer.InlineController.prototype =
	{
		__proto__: BX.UI.Viewer.Controller.prototype,
		constructor: BX.UI.Viewer.Controller,

		adjustViewport: function(){},
		addBodyPadding: function(){},
		adjustZindex: function(){},
		adjustViewerHeight: function(){},
		// showLoading: function(){},

		/**
		 * @param {HTMLElement} node
		 */
		renderItemByNode: function (node)
		{
			if (!node)
			{
				return;
			}

			this.buildItemListByNode(node).then(function(items){
				if (items.length === 0)
				{
					return;
				}

				this.setItems(items).then(function(){
					this.open(0);
				}.bind(this));
			}.bind(this));
		},

		getViewerContainer: function()
		{
			if (!this.layout.container)
			{
				//this.layout.inner? for showLoading
				this.layout.container = this.layout.inner = BX.create('div', {
					props: {
						className: 'ui-viewer-inner'
					},
					children: [
						this.getItemContainer()
					]
				});
			}

			return this.layout.container;
		},

		handleClickOnItemContainer: function(){},
		handleKeyPress: function(){},

		temp()
		{
			// timestamp update 207392
		},
	};
	/**
	 * @param type
	 * @param {HTMLElement} node
	 * @return {BX.UI.Viewer.Item}
	 */
	BX.UI.Viewer.buildItemByTypeAndNode = function (type, node)
	{
		var item = new type();

		if (!(item instanceof BX.UI.Viewer.Item))
		{
			throw new Error("BX.UI.Viewer.buildItemByTypeAndNode: 'item' has to be instance of BX.UI.Viewer.Item.");
		}

		item.bindSourceNode(node);
		item.setPropertiesByNode(node);
		item.setActions(BX.UI.Viewer.Instance.refineItemActions(item));

		return item;
	};

	/**
	 * @param {HTMLElement} node
	 * @returns {BX.UI.Viewer.Item}
	 */
	BX.UI.Viewer.buildItemByNode = function (node)
	{
		if (!BX.type.isDomNode(node))
		{
			throw new Error("BX.UI.Viewer.buildItemByNode: 'node' has to be DomNode.");
		}

		var typeCode = node.dataset.viewerType;
		if (!typeCode && node.tagName.toLowerCase() === 'img')
		{
			typeCode = 'image';
		}

		BX.UI.Viewer.triggerEventToFindTypeClass(typeCode);

		var className = types[typeCode];
		if (className)
		{
			return BX.UI.Viewer.buildItemByTypeAndNode(BX.getClass(className), node);
		}

		if (node.dataset.viewerTypeClass)
		{
			if (!BX.getClass(node.dataset.viewerTypeClass))
			{
				throw new Error("BX.UI.Viewer.buildItemByNode: could not find class " + node.dataset.viewerTypeClass);
			}

			return BX.UI.Viewer.buildItemByTypeAndNode(BX.getClass(node.dataset.viewerTypeClass), node);
		}

		console.warn("BX.UI.Viewer.buildItemByNode: could not find class to build type {" + typeCode + "}");

		return BX.UI.Viewer.buildItemByTypeAndNode(BX.getClass(types.unknown), node);
	};

	var types = {
		image: 'BX.UI.Viewer.Image',
		plainText: 'BX.UI.Viewer.PlainText',
		unknown: 'BX.UI.Viewer.Unknown',
		video: 'BX.UI.Viewer.Video',
		audio: 'BX.UI.Viewer.Audio',
		document: 'BX.UI.Viewer.Document',
		code: 'BX.UI.Viewer.HightlightCode'
	};

	/**
	 * @param {String} type
	 * @param {String} className
	 */
	BX.UI.Viewer.addType = function (type, className)
	{
		types[type] = className;
	};

	BX.UI.Viewer.triggerEventToFindTypeClass = function (type)
	{
		BX.onCustomEvent('BX.UI.Viewer.Controller:onFindType', [BX.UI.Viewer.Instance, type]);
	};

	/**
	 * @param {HTMLElement} container
	 * @param filter
	 * @returns {BX.Promise}
	 */
	BX.UI.Viewer.bind = function (container, filter)
	{
		if (!BX.type.isDomNode(container))
		{
			throw new Error("BX.UI.Viewer.bind: 'container' has to be DomNode.");
		}
		if (!BX.type.isPlainObject(filter) && !BX.type.isFunction(filter))
		{
			filter = function(node) {
				return BX.type.isElementNode(node) && node.dataset.hasOwnProperty('viewer');
			};
		}

		BX.bindDelegate(container, 'click', filter, function(event) {
			var nodes = BX.findChildren(container, filter, true);
			var indexToShow = 0;
			var targetNode = BX.getEventTarget(event);
			if (targetNode.tagName !== 'A' && targetNode.closest('a[target="_blank"]'))
			{
				return false;
			}

			var items = nodes.map(function(node, index) {
				if (node === targetNode)
				{
					indexToShow = index;
				}
				return BX.UI.Viewer.buildItemByNode(node);
			});

			BX.UI.Viewer.Instance.setItems(items).then(function () {
				BX.UI.Viewer.Instance.open(indexToShow);
			});

			event.preventDefault();
		});
	};

	var shouldLoadExtensions = function(extension) {
		if (extension === 'disk.viewer.actions' && BX.getClass('BX.Disk.Viewer.Actions'))
		{
			return false;
		}
		if (extension === 'disk.viewer.document-item' && BX.getClass('BX.Disk.Viewer.DocumentItem'))
		{
			return false;
		}

		return true;
	}


	var instance = null;
	/**
	 * @memberOf BX.UI.Viewer
	 * @name BX.UI.Viewer#Instance
	 * @type BX.UI.Viewer.Controller
	 * @static
	 * @readonly
	 */
	Object.defineProperty(BX.UI.Viewer, 'Instance', {
		enumerable: false,
		get: function()
		{
			if (window.top !== window && BX.getClass('window.top.BX.UI.Viewer.Instance'))
			{
				return window.top.BX.UI.Viewer.Instance;
			}

			if (instance === null)
			{
				instance = new BX.UI.Viewer.Controller({});
			}

			return instance;
		}
	});

	window.document.addEventListener('click', function(event) {
		if (event.button !== 0)
		{
			return;
		}

		if (window.top !== window && !BX.getClass('window.top.BX.UI.Viewer.Instance'))
		{
			top.BX.loadExt('ui.viewer').then(function () {
				top.BX.UI.Viewer.Instance.handleDocumentClick(event);
			});
		}
		else
		{
			top.BX.UI.Viewer.Instance.handleDocumentClick(event);
		}
	}, true);

	//We have to show viewer only in top window not in iframe.
	//So we try to load ui.viewer to the top window if there is no viewer.
	if (window.top !== window && !BX.getClass('window.top.BX.UI.Viewer.Instance'))
	{
		top.BX.loadExt('ui.viewer');
	}
})();
