(function() {
	'use strict';

	BX.namespace('BX.UI.Viewer');

	BX.UI.Viewer.Item = function(options)
	{
		options = options || {};

		/**
		 * @type {BX.UI.Viewer.Controller}
		 */
		this.controller = null;
		this.title = options.title;
		this.src = options.src;
		this.nakedActions = BX.Type.isArrayFilled(options.nakedActions) ? options.nakedActions : [];
		this.actions = BX.Type.isArrayFilled(options.actions) ? options.actions : [];
		this.contentType = options.contentType;
		this.isLoaded = false;
		this.isLoading = false;
		this.isTransforming = false;
		this.isTransformationError = false;
		this.sourceNode = null;
		this.transformationTimeoutId = null;
		this.longPollingTimeoutId = null;
		this.viewerGroupBy = null;
		this.previewUrl = null;
		this.downloadUrl = null;
		this.isSeparate = false;
		this.transformationTimeout = options.transformationTimeout || 22000;
		this.layout = {
			container: null,
		};

		this.onTransformationComplete = this.handleTransformationComplete.bind(this);
		this.options = options;

		this.init();
	};

	BX.UI.Viewer.Item.prototype = {
		setController(controller)
		{
			if (!(controller instanceof BX.UI.Viewer.Controller))
			{
				throw new TypeError("BX.UI.Viewer.Item: 'controller' has to be instance of BX.UI.Viewer.Controller.");
			}

			this.controller = controller;
		},

		/**
		 * @param {HTMLElement} node
		 */
		setPropertiesByNode(node)
		{
			this.title = node.dataset.title || node.title || node.alt;
			this.src = node.dataset.src;
			this.viewerGroupBy = node.dataset.viewerGroupBy;
			this.isSeparate = node.dataset.viewerSeparateItem || false;
			this.nakedActions = node.dataset.actions ? JSON.parse(node.dataset.actions) : [];

			let previewUrl = null;
			if (BX.Type.isStringFilled(node.dataset.viewerPreview))
			{
				previewUrl = node.dataset.viewerPreview;
			}
			else if (BX.Type.isStringFilled(node.dataset.bxPreview))
			{
				previewUrl = node.dataset.bxPreview;
			}
			else if (BX.Type.isStringFilled(node.dataset.thumbSrc))
			{
				previewUrl = node.dataset.thumbSrc;
			}
			else if (this instanceof BX.UI.Viewer.Image && BX.Type.isStringFilled(node.src))
			{
				previewUrl = node.src;
			}

			this.previewUrl = previewUrl === null || previewUrl.startsWith('data:image') ? null : previewUrl;
		},

		/**
		 * @param {HTMLElement} node
		 */
		bindSourceNode(node)
		{
			this.sourceNode = node;
		},

		applyReloadOptions(options)
		{},

		isSeparateItem()
		{
			return this.isSeparate;
		},

		isPullConnected()
		{
			if (top.BX.PULL)
			{
				// pull_v2
				if (BX.type.isFunction(top.BX.PULL.isConnected))
				{
					return top.BX.PULL.isConnected();
				}

				const debugInfo = top.BX.PULL.getDebugInfoArray();

				return debugInfo.connected;
			}

			return false;
		},

		registerTransformationHandler(pullTag)
		{
			if (this.isLoaded)
			{
				return;
			}

			if (this.controller.getCurrentItem() === this)
			{
				this.controller.setTextOnLoading(BX.message('JS_UI_VIEWER_ITEM_TRANSFORMATION_IN_PROGRESS'));
			}

			if (this.isPullConnected())
			{
				BX.Event.EventEmitter.subscribe('onPullEvent-main', this.onTransformationComplete);
				console.log('BX.PULL.extendWatch');
				BX.PULL.extendWatch(pullTag);
			}
			else
			{
				this.resetLongPollingTimeout();
				this.longPollingTimeoutId = setTimeout(() => {
					BX.ajax.promise({
						url: BX.util.add_url_param(this.src, { ts: 'bxviewer' }),
						method: 'GET',
						dataType: 'json',
						headers: [{
							name: 'BX-Viewer-check-transformation',
							value: null,
						}],
					}).then((response) => {
						if (!response.data || !response.data.transformation)
						{
							this.registerTransformationHandler();
						}
						else
						{
							this.controller.reload(this, {
								forceTransformation: true,
							});
						}
					});
				}, 5000);
			}

			if (this.transformationTimeoutId === null && this.isTransformationError === false)
			{
				this.transformationTimeoutId = setTimeout(() => {
					if (this.isLoading)
					{
						console.log('Throw transformationTimeout');
						if (this._loadPromise)
						{
							this._loadPromise.reject({
								status: 'timeout',
								message: BX.message('JS_UI_VIEWER_ITEM_TRANSFORMATION_ERROR_1').replace('#DOWNLOAD_LINK#', this.getSrc()),
								item: this,
							});

							this.isLoading = false;
							this.isTransformationError = true;
						}
					}
					else
					{
						console.log('We don\'t have transformationTimeout :) ');
					}
				}, this.transformationTimeout);
			}
		},

		handleTransformationComplete(event)
		{
			const [command] = event.getCompatData();
			if (command === 'transformationComplete' && this.isTransforming)
			{
				this.controller.reload(this, {
					forceTransformation: true,
				});
			}
		},

		resetTransformationTimeout()
		{
			if (this.transformationTimeoutId)
			{
				clearTimeout(this.transformationTimeoutId);
			}

			this.transformationTimeoutId = null;
		},

		resetLongPollingTimeout()
		{
			if (this.longPollingTimeoutId)
			{
				clearTimeout(this.longPollingTimeoutId);
			}

			this.longPollingTimeoutId = null;
		},

		init()
		{},

		load()
		{
			const promise = new BX.Promise();

			if (this.isLoaded)
			{
				promise.fulfill(this);
				console.log('isLoaded');

				return promise;
			}

			if (this.isTransformationError)
			{
				promise.reject({
					item: this,
				});

				return promise;
			}

			if (this.isLoading)
			{
				console.log('isLoading');

				if (this.isTransforming && this.controller.getCurrentItem() === this)
				{
					this.controller.setTextOnLoading(BX.Loc.getMessage('JS_UI_VIEWER_ITEM_TRANSFORMATION_IN_PROGRESS'));
				}

				return this._loadPromise;
			}

			this.isLoading = true;
			this._loadPromise = this.loadData().then((item) => {
				this.isLoaded = true;
				this.isLoading = false;
				this.isTransforming = false;

				return item;
			}).catch((reason) => {
				console.log('catch');
				this.isLoaded = false;
				this.isLoading = false;
				this.isTransforming = false;

				if (!reason.item)
				{
					reason.item = this;
				}

				const promise = new BX.Promise();
				promise.reject(reason);

				return promise;
			});

			console.log('will load');

			return this._loadPromise;
		},

		/**
		 * Returns list of classes which will be added to viewer container before showing
		 * and will be deleted after hiding.
		 * @return {Array}
		 */
		listContainerModifiers()
		{
			return [];
		},

		getSrc()
		{
			return this.src;
		},

		hashCode(string)
		{
			let h = 0; const l = string.length; let
				i = 0;
			if (l > 0)
			{
				while (i < l)

				{ h = (h << 5) - h + string.charCodeAt(i++) | 0;
				}
			}

			return h;
		},

		generateUniqueId()
		{
			return this.hashCode(this.getSrc() || '') + (Math.floor(Math.random() * Math.floor(10000)));
		},

		getTitle()
		{
			return this.title;
		},

		getPreviewUrl()
		{
			return this.previewUrl;
		},

		getDownloadUrl()
		{
			return this.downloadUrl === null ? this.src : this.downloadUrl;
		},

		setDownloadUrl(url)
		{
			if (BX.Type.isStringFilled(url) || url === null)
			{
				this.downloadUrl = url;
			}
		},

		getGroupBy()
		{
			return this.viewerGroupBy;
		},

		getNakedActions()
		{
			return this.nakedActions;
		},

		setActions(actions)
		{
			this.actions = actions;
		},

		getActions()
		{
			return this.actions;
		},

		/**
		 * @returns {BX.Promise}
		 */
		loadData()
		{
			const promise = new BX.Promise();
			promise.setAutoResolve(true);
			promise.fulfill(this);

			return promise;
		},

		render()
		{},

		renderExtraActions()
		{},

		getMoreMenuItems()
		{
			return [];
		},

		/**
		 * @returns {BX.Promise}
		 */
		getContentWidth()
		{},

		handleKeyPress(event)
		{},

		handleClickOnItemContainer(event)
		{},

		handleResize()
		{},

		asFirstToShow()
		{},

		afterRender()
		{},

		beforeHide()
		{},

		destroy()
		{
			this.resetTransformationTimeout();
			this.resetLongPollingTimeout();
			BX.Event.EventEmitter.unsubscribe('onPullEvent-main', this.onTransformationComplete);
		},

		abort()
		{
			// Implement this method if an item loading can be aborted
			return false;
		},
	};

	/**
	 * @extends {BX.UI.Viewer.Item}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.Image = function(options)
	{
		options = options || {};

		BX.UI.Viewer.Item.apply(this, arguments);

		this.resizedSrc = options.resizedSrc;
		this.width = options.width;
		this.height = options.height;

		this.scale = 1;
		this.rotation = 0;
		this.translate = { x: 0, y: 0 };
		this.panning = false;

		/**
		 * @type {HTMLImageElement}
		 */
		this.imageNode = null;
		this.layout = {
			container: null,
			actions: null,
		};

		this.xhr = null;

		this.onPointerDownHandler = null;
		this.onPointerMoveHandler = null;
		this.onPointerUpHandler = null;
	};

	BX.UI.Viewer.Image.prototype =	{
		__proto__: BX.UI.Viewer.Item.prototype,
		constructor: BX.UI.Viewer.Item,

		/**
		 * @param {HTMLElement} node
		 */
		setPropertiesByNode(node)
		{
			BX.UI.Viewer.Item.prototype.setPropertiesByNode.apply(this, arguments);

			this.src = node.dataset.src || node.src;
			this.width = node.dataset.width;
			this.height = node.dataset.height;

			if (!BX.Type.isUndefined(node.dataset.viewerResized))
			{
				this.resizedSrc = this.src;
			}
		},

		applyReloadOptions(options)
		{
			this.controller.unsetCachedData(this.src);
		},

		tryToExportResizedSrcFromSourceNode()
		{
			/**
			 * @see .ui-viewer-inner-content-wrapper > * {
			 * max-height: calc(100% - 210px)
			 */
			const paddingHeight = 210;
			if (!(this.sourceNode instanceof Image))
			{
				return;
			}

			if (!this.sourceNode.naturalWidth || this.sourceNode.src.startsWith('data:image'))
			{
				return;
			}

			if (this.sourceNode.src === this.src)
			{
				this.resizedSrc = this.src;
			}
			else if (!this.sourceNode.src.endsWith('.gif') && !this.sourceNode.src.endsWith('.webp'))
			{
				const offsetHeight = this.controller.getItemContainer().offsetHeight;
				const offsetWidth = this.controller.getItemContainer().offsetWidth;
				const scale = offsetHeight / offsetWidth;
				const realMaxHeight = (offsetHeight - paddingHeight);
				const realMaxWidth = realMaxHeight / scale;

				if (this.sourceNode.naturalWidth >= realMaxWidth || this.sourceNode.naturalHeight >= realMaxHeight)
				{
					this.resizedSrc = this.sourceNode.src;
				}
			}
		},

		loadData()
		{
			const promise = new BX.Promise();

			if (!BX.Type.isStringFilled(this.resizedSrc))
			{
				if (!this.shouldRunLocalResize())
				{
					this.resizedSrc = this.src;
				}
				else
				{
					this.tryToExportResizedSrcFromSourceNode();

					if (this.controller.getCachedData(this.src))
					{
						this.resizedSrc = this.controller.getCachedData(this.src).resizedSrc;
					}
				}
			}

			if (this.resizedSrc)
			{
				this.imageNode = new Image();
				this.imageNode.className = 'ui-viewer-image';
				this.imageNode.draggable = false;
				this.imageNode.onload = () => {
					promise.fulfill(this);
				};

				this.imageNode.onerror = this.imageNode.onabort = (event) => {
					console.log('reject');
					promise.reject({
						item: this,
						type: 'error',
					});
				};

				this.imageNode.src = this.resizedSrc;
			}
			else
			{
				this.xhr = new XMLHttpRequest();
				this.xhr.onreadystatechange = ()=> {
					if (this.xhr.readyState !== XMLHttpRequest.DONE)
					{
						return;
					}

					if (
						(this.xhr.status === 200 || this.xhr.status === 0)
						&& BX.Type.isBlob(this.xhr.response)
						&& /^image\/[\d.a-z-]+$/i.test(this.xhr.response.type)
					)
					{
						console.log('resize image');
						this.resizedSrc = URL.createObjectURL(this.xhr.response);
						this.imageNode = new Image();
						this.imageNode.className = 'ui-viewer-image';
						this.imageNode.draggable = false;
						this.imageNode.src = this.resizedSrc;
						this.imageNode.onload = () => {
							promise.fulfill(this);
						};

						this.controller.setCachedData(this.src, { resizedSrc: this.resizedSrc });
					}
					else
					{
						promise.reject({
							item: this,
							type: 'error',
						});
					}
				};

				this.xhr.open('GET', BX.util.add_url_param(this.src, { ts: 'bxviewer' }), true);
				this.xhr.responseType = 'blob';
				this.xhr.setRequestHeader('BX-Viewer-image', 'x');
				this.xhr.send();
			}

			return promise;
		},

		abort()
		{
			if (this.xhr !== null && !this.isLoaded)
			{
				console.log('abort xhr');

				this.xhr.abort();
				this.xhr = null;

				return true;
			}

			return false;
		},

		shouldRunLocalResize()
		{
			return !this.controller.isExternalLink(this.src);
		},

		render()
		{
			const item = document.createDocumentFragment();
			item.appendChild(this.imageNode);
			this.imageNode.alt = this.title;

			return item;
		},

		getMoreMenuItems()
		{
			if (this.title)
			{
				return [{
					text: BX.Loc.getMessage('JS_UI_VIEWER_IMAGE_VIEW_FULL_SIZE_MSGVER_1'),
					href: BX.util.add_url_param(this.src, { ts: 'bxviewer', ibxShowImage: 1 }),
					target: '_blank',
					onclick: () => {
						this.controller.moreMenu?.close();
					},
				}];
			}

			return [];
		},

		renderExtraActions()
		{
			if (this.layout.actions === null)
			{
				this.layout.actions = BX.Tag.render`
					<div class="ui-viewer-image-extra-actions">
						<div 
							class="ui-viewer-action-btn" 
							onclick="${this.handleZoomOut.bind(this)}"
							title="${BX.Text.encode(BX.Loc.getMessage('JS_UI_VIEWER_SINGLE_DOCUMENT_SCALE_ZOOM_OUT'))}"
						>
							<div class="ui-icon-set --zoom-out ui-viewer-action-btn-icon"></div>
						</div>
						<div 
							class="ui-viewer-action-btn" 
							onclick="${this.handleZoomIn.bind(this)}" 
							title="${BX.Text.encode(BX.Loc.getMessage('JS_UI_VIEWER_SINGLE_DOCUMENT_SCALE_ZOOM_IN'))}"
						>
							<div class="ui-icon-set --zoom-in ui-viewer-action-btn-icon"></div>
						</div>
						<div 
							class="ui-viewer-action-btn" 
							onclick="${this.handleRotate.bind(this)}"
							title="${BX.Text.encode(BX.Loc.getMessage('JS_UI_VIEWER_ITEM_ACTION_ROTATE'))}"
						>
							<div class="ui-icon-set --image-rotate-left ui-viewer-action-btn-icon"></div>
						</div>
					</div>
				`;
			}

			return this.layout.actions;
		},

		/**
		 * @returns {BX.Promise}
		 */
		getContentWidth()
		{
			const promise = new BX.Promise();

			promise.fulfill(this.imageNode.offsetWidth * this.scale);

			return promise;
		},

		afterRender()
		{},

		enablePanning()
		{
			if (this.panning)
			{
				return;
			}

			this.onPointerDownHandler = this.handlePointerDown.bind(this);
			BX.Event.bind(this.imageNode, 'pointerdown', this.onPointerDownHandler);

			this.panning = true;
		},

		disablePanning()
		{
			BX.Event.unbind(this.imageNode, 'pointerdown', this.onPointerDownHandler);
			this.onPointerDownHandler = null;
			this.panning = false;
		},

		handleKeyPress(event)
		{
			if (!this.isLoaded)
			{
				return false;
			}

			if (event.code === 'Equal')
			{
				event.preventDefault();
				event.stopPropagation();

				this.zoomIn();

				return true;
			}

			if (event.code === 'Minus')
			{
				event.preventDefault();
				event.stopPropagation();

				this.zoomOut();

				return true;
			}

			return false;
		},

		handleClickOnItemContainer(event)
		{
			if (!this.panning)
			{
				this.controller.showNext();
			}
		},

		handlePointerDown(event)
		{
			this.onPointerMoveHandler = this.handlePointerMove.bind(this);
			this.onPointerUpHandler = this.handlePointerUp.bind(this);

			BX.Event.bind(document, 'pointermove', this.onPointerMoveHandler);
			BX.Event.bind(document, 'pointerup', this.onPointerUpHandler);
		},

		handlePointerMove(event)
		{
			const { movementX, movementY } = event;

			const windowWidth = window.innerWidth;
			const windowHeight = window.innerHeight;

			let imageWidth = this.imageNode.offsetWidth * this.scale;
			let imageHeight = this.imageNode.offsetHeight * this.scale;
			const rotated = Math.abs(this.rotation) / 90 % 2 !== 0;
			if (rotated)
			{
				[imageWidth, imageHeight] = [imageHeight, imageWidth];
			}

			const maxXOffsetX = imageWidth > windowWidth ? (imageWidth - windowWidth) / 2 : 0;
			const maxYOffsetY = imageHeight > windowHeight ? (imageHeight - windowHeight) / 2 : 0;

			let x = this.translate.x + movementX;
			let y = this.translate.y + movementY;
			if (Math.abs(x) > maxXOffsetX)
			{
				x = maxXOffsetX * Math.sign(x);
			}

			if (Math.abs(y) > maxYOffsetY)
			{
				y = maxYOffsetY * Math.sign(y);
			}

			this.translate.x = x;
			this.translate.y = y;

			this.applyTransform();
		},

		handlePointerUp()
		{
			BX.Event.unbind(document, 'pointermove', this.onPointerMoveHandler);
			BX.Event.unbind(document, 'pointerup', this.onPointerUpHandler);

			this.onPointerMoveHandler = null;
			this.onPointerUpHandler = null;
		},

		applyTransform()
		{
			BX.Dom.style(this.imageNode, 'translate', `${this.translate.x}px ${this.translate.y}px`);
			BX.Dom.style(this.imageNode, 'scale', this.scale);
			BX.Dom.style(this.imageNode, 'rotate', `${this.rotation}deg`);

			// BX.Dom.style(
			// 	this.imageNode,
			// 	'transform',
			// 	`translate(${this.translate.x}px, ${this.translate.y}px) scale(${this.scale}) rotate(${this.rotation}deg)`,
			// );
		},

		resetTranslate()
		{
			this.translate.x = 0;
			this.translate.y = 0;
		},

		togglePanning()
		{
			if (this.scale > 1 || this.rotation !== 0)
			{
				this.enablePanning();
			}
			else
			{
				this.disablePanning();
			}
		},

		zoomIn()
		{
			this.scale = Math.min(4, this.scale + 1);
			this.applyTransform();
			this.togglePanning();

			this.controller.adjustControlsSize(this.getContentWidth());
		},

		zoomOut()
		{
			this.scale = Math.max(1, this.scale - 1);

			this.resetTranslate();
			this.applyTransform();
			this.togglePanning();

			this.controller.adjustControlsSize(this.getContentWidth());
		},

		rotate()
		{
			this.rotation -= 90;

			this.resetTranslate();
			this.applyTransform();
			this.togglePanning();
		},

		handleZoomOut()
		{
			this.zoomOut();
		},

		handleZoomIn()
		{
			this.zoomIn();
		},

		handleRotate()
		{
			this.rotate();
		},
	};

	/**
	 * @extends {BX.UI.Viewer.Item}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.PlainText = function(options)
	{
		options = options || {};

		BX.UI.Viewer.Item.apply(this, arguments);

		this.content = options.content;
	};

	BX.UI.Viewer.PlainText.prototype =	{
		__proto__: BX.UI.Viewer.Item.prototype,
		constructor: BX.UI.Viewer.Item,

		/**
		 * @param {HTMLElement} node
		 */
		setPropertiesByNode(node)
		{
			BX.UI.Viewer.Item.prototype.setPropertiesByNode.apply(this, arguments);

			this.content = node.dataset.content;
		},

		render()
		{
			const contentNode = BX.create('span', {
				text: this.content,
			});

			contentNode.style.fontSize = '14px';
			contentNode.style.color = 'white';

			return contentNode;
		},
	};

	/**
	 * @extends {BX.UI.Viewer.Item}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.Audio = function(options)
	{
		options = options || {};

		BX.UI.Viewer.Item.apply(this, arguments);

		this.playerId = `audio-playerId_${this.generateUniqueId()}`;
		this.svgMask = null;
	};

	BX.UI.Viewer.Audio.prototype =	{
		__proto__: BX.UI.Viewer.Item.prototype,
		constructor: BX.UI.Viewer.Item,

		/**
		 * @param {HTMLElement} node
		 */
		setPropertiesByNode(node)
		{
			BX.UI.Viewer.Item.prototype.setPropertiesByNode.apply(this, arguments);

			this.playerId = `audio-playerId_${this.generateUniqueId()}`;
		},

		loadData()
		{
			const promise = new BX.Promise();

			BX.Runtime.loadExtension('ui.video-player').then(() => {
				const headers = [
					{
						name: 'BX-Viewer-src',
						value: this.src,
					},
					{
						name: 'BX-Viewer',
						value: 'audio',
					},
				];

				const ajaxPromise = BX.ajax.promise({
					url: BX.util.add_url_param(this.src, { ts: 'bxviewer' }),
					method: 'GET',
					dataType: 'json',
					headers,
				});

				ajaxPromise.then((response) => {
					if (!response || !response.data)
					{
						const errors = response ? response.errors : [];

						promise.reject({
							item: this,
							type: 'error',
							errors: errors || [],
						});

						return;
					}

					promise.fulfill(this);
				});
			});

			return promise;
		},

		render()
		{
			this.player = new BX.UI.VideoPlayer.Player(this.playerId, {
				width: 320,
				height: 52,
				isAudio: true,
				skin: 'vjs-viewer-audio-player-skin',
				sources: [{
					src: this.src,
					type: 'audio/mp3',
				}],
			});

			return this.player.createElement();
		},

		afterRender()
		{
			this.player.init();
		},
	};

	/**
	 * @extends {BX.UI.Viewer.Item}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.HightlightCode = function(options)
	{
		options = options || {};

		BX.UI.Viewer.Item.apply(this, arguments);

		this.content = options.content;
	};

	BX.UI.Viewer.HightlightCode.prototype =	{
		__proto__: BX.UI.Viewer.Item.prototype,
		constructor: BX.UI.Viewer.Item,

		/**
		 * @param {HTMLElement} node
		 */
		setPropertiesByNode(node)
		{
			BX.UI.Viewer.Item.prototype.setPropertiesByNode.apply(this, arguments);

			this.content = node.dataset.content;
		},

		listContainerModifiers()
		{
			return [
				'ui-viewer-document',
				'ui-viewer-document-hlcode',
			];
		},

		loadData()
		{
			const promise = new BX.Promise();

			BX.loadExt('ui.highlightjs').then(() => {
				if (this.content)
				{
					promise.fulfill(this);
				}
				else
				{
					const xhr = new XMLHttpRequest();
					xhr.onreadystatechange = function() {
						if (xhr.readyState !== XMLHttpRequest.DONE)
						{
							return;
						}

						if ((xhr.status === 200 || xhr.status === 0) && xhr.response)
						{
							this.content = xhr.response;
							console.log('text content is loaded');
							this.controller.setCachedData(this.src, { content: this.content });

							promise.fulfill(this);
						}
						else
						{
							promise.reject({
								item: this,
								type: 'error',
							});
						}
					}.bind(this);
					xhr.open('GET', BX.util.add_url_param(this.src, { ts: 'bxviewerText' }), true);
					xhr.responseType = 'text';
					xhr.send();
				}
			});

			return promise;
		},

		render()
		{
			const ext = this.getTitle().slice(Math.max(0, this.getTitle().lastIndexOf('.') + 1));

			return BX.create('div', {
				props: {
					tabIndex: 2208,
				},
				attrs: {
					className: 'ui-viewer-item-document-content',
				},
				style: {
					width: '100%',
					height: '100%',
					paddingTop: '85px',
					background: 'rgba(0, 0, 0, 0.1)',
					overflow: 'auto',
				},
				children: [
					BX.create('pre', {
						children: [
							this.codeNode = BX.create('code', {
								props: {
									className: hljs.getLanguage(ext) ? ext : 'plaintext',
								},
								style: {
									fontSize: '14px',
									textAlign: 'left',
								},
								text: this.content,
							}),
						],
					}),
				],
			});
		},

		/**
		 * @returns {BX.Promise}
		 */
		getContentWidth()
		{
			const promise = new BX.Promise();

			promise.fulfill(this.codeNode.offsetWidth);

			return promise;
		},

		afterRender()
		{
			hljs.highlightBlock(this.codeNode);
		},
	};

	/**
	 * @extends {BX.UI.Viewer.Item}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.Unknown = function(options)
	{
		BX.UI.Viewer.Item.apply(this, arguments);
	};

	BX.UI.Viewer.Unknown.prototype =	{
		__proto__: BX.UI.Viewer.Item.prototype,
		constructor: BX.UI.Viewer.Item,

		render()
		{
			return BX.create('div', {
				props: {
					className: 'ui-viewer-unsupported',
				},
				children: [
					BX.create('div', {
						props: {
							className: 'ui-viewer-unsupported-title',
						},
						text: BX.message('JS_UI_VIEWER_ITEM_UNKNOWN_TITLE'),
					}),
					BX.create('div', {
						props: {
							className: 'ui-viewer-unsupported-text',
						},
						text: BX.message('JS_UI_VIEWER_ITEM_UNKNOWN_NOTICE'),
					}),
					BX.create('a', {
						props: {
							className: 'ui-btn ui-btn-light-border ui-btn-themes',
							href: this.getSrc(),
							target: '_blank',
						},
						text: BX.message('JS_UI_VIEWER_ITEM_UNKNOWN_DOWNLOAD_ACTION'),
					}),
				],
			});
		},
	};

	/**
	 * @extends {BX.UI.Viewer.Item}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.Video = function(options)
	{
		options = options || {};

		BX.UI.Viewer.Item.apply(this, arguments);

		this.player = null;
		this.sources = [];
		this.contentNode = null;
		this.forceTransformation = false;
		this.videoWidth = null;
		this.playerId = `playerId_${this.generateUniqueId()}`;
	};

	BX.UI.Viewer.Video.prototype =	{
		__proto__: BX.UI.Viewer.Item.prototype,
		constructor: BX.UI.Viewer.Item,

		/**
		 * @param {HTMLElement} node
		 */
		setPropertiesByNode(node)
		{
			BX.UI.Viewer.Item.prototype.setPropertiesByNode.apply(this, arguments);

			this.playerId = `playerId_${this.generateUniqueId()}`;
		},

		applyReloadOptions(options)
		{
			if (options.forceTransformation)
			{
				this.forceTransformation = true;
			}
		},

		loadData()
		{
			const promise = new BX.Promise();

			BX.Runtime.loadExtension('ui.video-player').then(() => {
				const headers = [
					{
						name: 'BX-Viewer-src',
						value: this.src,
					},
				];

				headers.push({
					name: this.forceTransformation ? 'BX-Viewer-force-transformation' : 'BX-Viewer',
					value: 'video',
				});

				const ajaxPromise = BX.ajax.promise({
					url: BX.util.add_url_param(this.src, { ts: 'bxviewer' }),
					method: 'GET',
					dataType: 'json',
					headers,
				});

				ajaxPromise.then((response) => {
					if (!response || !response.data)
					{
						const errors = response ? response.errors : [];

						promise.reject({
							item: this,
							type: 'error',
							errors: errors || [],
						});

						return;
					}

					if (response.data.hasOwnProperty('pullTag'))
					{
						if (!this.isTransforming)
						{
							this.registerTransformationHandler(response.data.pullTag);
						}
						this.isTransforming = true;
					}
					else
					{
						this.isTransforming = false;
						if (response.data.data)
						{
							this.width = response.data.data.width;
							this.height = response.data.data.height;
							this.sources = response.data.data.sources;
						}

						promise.fulfill(this);
					}
				});
			});

			return promise;
		},

		handleAfterInit()
		{
			if (this.handleVideoError())
			{
				return;
			}

			if (this.player.vjsPlayer.videoWidth() > 0 && this.player.vjsPlayer.videoHeight() > 0)
			{
				this.adjustVideo();
			}
			else
			{
				BX.Event.EventEmitter.subscribeOnce(this.player, 'Player:onLoadedMetadata', () => {
					this.adjustVideo();
				});
			}
		},

		handleVideoError()
		{
			if (this.player.vjsPlayer.error() && !this.forceTransformation)
			{
				this.controller.reload(this, {
					forceTransformation: true,
				});

				return true;
			}

			return false;
		},

		adjustVideo()
		{
			const container = this.contentNode;
			if (!container)
			{
				return;
			}

			if (!this.player.vjsPlayer)
			{
				return;
			}

			this.adjustVideoWidth(
				this.player.width,
				this.player.height,
				this.player.vjsPlayer.videoWidth(),
				this.player.vjsPlayer.videoHeight(),
			);

			BX.addClass(container, 'player-loaded');
			BX.style(container, 'opacity', 1);

			this.controller.hideLoading();
		},

		adjustVideoWidth(maxWidth, maxHeight, videoWidth, videoHeight)
		{
			if (!maxWidth || !maxHeight || !videoWidth || !videoHeight)
			{
				return false;
			}

			maxHeight = Math.min(maxHeight, window.innerHeight - 250);

			let width = Math.max(videoWidth, 400);
			let height = Math.max(videoHeight, 130);
			if (videoHeight > maxHeight || videoWidth > maxWidth)
			{
				const resultRelativeSize = maxWidth / maxHeight;
				const videoRelativeSize = videoWidth / videoHeight;
				let reduceRatio = 1;
				if (resultRelativeSize > videoRelativeSize)
				{
					reduceRatio = maxHeight / videoHeight;
				}
				else
				{
					reduceRatio = maxWidth / videoWidth;
				}

				width = Math.max(videoWidth * reduceRatio, 400);
				height = Math.max(videoHeight * reduceRatio, 130);
			}

			this.player.vjsPlayer.fluid(false);

			BX.Dom.style(this.contentNode, 'width', `${width}px`);
			BX.Dom.style(this.contentNode, 'height', `${height}px`);

			this.player.vjsPlayer.width('auto');
			this.player.vjsPlayer.height('auto');

			BX.Dom.style(this.player.vjsPlayer.el(), 'width', '100%');
			BX.Dom.style(this.player.vjsPlayer.el(), 'min-width', '300px');
			BX.Dom.style(this.player.vjsPlayer.el(), 'aspect-ratio', `${width} / ${height}`);
			BX.Dom.style(this.player.vjsPlayer.el(), 'height', 'auto');

			this.videoWidth = width;
			if (!this.contentWidthPromise.state)
			{
				this.contentWidthPromise.fulfill(this.videoWidth);
			}

			return true;
		},

		/**
		 * @returns {BX.Promise}
		 */
		getContentWidth()
		{
			this.contentWidthPromise = new BX.Promise();

			if (this.videoWidth)
			{
				this.contentWidthPromise.fulfill(this.videoWidth);
			}

			return this.contentWidthPromise;
		},

		render(options)
		{
			if (this.player === null)
			{
				this.player = new BX.UI.VideoPlayer.Player(this.playerId, {
					autoplay: options.asFirstToShow === true,
					width: this.width,
					height: this.height,
					sources: this.sources,
					disablePictureInPicture: this.shouldDisablePictureInPicture(),
				});

				this.controller.showLoading();

				BX.Event.EventEmitter.subscribe(this.player, 'Player:onAfterInit', this.handleAfterInit.bind(this));
				BX.Event.EventEmitter.subscribe(this.player, 'Player:onError', this.handleVideoError.bind(this));
				BX.Event.EventEmitter.subscribe(this.player, 'Player:onEnterPictureInPicture', () => {
					this.controller.close();
				});

				BX.Event.EventEmitter.subscribe(this.player, 'Player:onLeavePictureInPicture', () => {
					if (this.player)
					{
						this.player.pause();
						if (!this.controller.isOpen())
						{
							this.player.destroy();
							this.player = null;
						}
					}
				});

				this.contentNode = BX.create('div', {
					attrs: {
						className: 'ui-viewer-video',
					},
					style: {
						opacity: 0,
					},
					children: [
						this.player.createElement(),
					],
				});
			}
			else
			{
				this.adjustVideo();
			}

			return this.contentNode;
		},

		shouldDisablePictureInPicture()
		{
			return BX.Browser.isFirefox() || navigator.userAgent.includes('YaBrowser');
		},

		asFirstToShow()
		{
			if (this.player)
			{
				this.player.vjsPlayer.one('canplay', () => {
					this.player.mute(false);
					this.player.play();
					this.player.focus();
				});
			}
		},

		afterRender()
		{
			const disablePictureInPicture = this.shouldDisablePictureInPicture() && !this.player.isInited();

			this.player.init();

			if (disablePictureInPicture)
			{
				this.player.vjsPlayer.controlBar.removeChild('PictureInPictureToggle');
			}
		},

		destroy()
		{
			BX.UI.Viewer.Item.prototype.destroy.apply(this);

			if (this.player !== null && !this.player.vjsPlayer.isInPictureInPicture())
			{
				this.player.destroy();
				this.player = null;
			}
		},

		beforeHide()
		{
			if (this.player !== null && !this.player.vjsPlayer.isInPictureInPicture())
			{
				this.player.pause();
			}
		},

		handleResize()
		{
			this.adjustVideo();
		},

		temp()
		{
			// timestamp update 207392
		},
	};
})();
