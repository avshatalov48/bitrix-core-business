;(function () {

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
		this.nakedActions = options.nakedActions;
		this.actions = options.actions;
		this.contentType = options.contentType;
		this.isLoaded = false;
		this.isLoading = false;
		this.isTransforming = false;
		this.isTransformationError = false;
		this.sourceNode = null;
		this.transformationTimeoutId = null;
		this.viewerGroupBy = null;
		this.isSeparate = false;
		this.transformationTimeout = options.transformationTimeout || 22000;
		this.layout = {
			container: null
		};

		this.options = options;

		this.init();
	};

	BX.UI.Viewer.Item.prototype =
	{
		setController: function (controller)
		{
			if (!(controller instanceof BX.UI.Viewer.Controller))
			{
				throw new Error("BX.UI.Viewer.Item: 'controller' has to be instance of BX.UI.Viewer.Controller.");
			}

			this.controller = controller;
		},

		/**
		 * @param {HTMLElement} node
		 */
		setPropertiesByNode: function (node)
		{
			this.title = node.dataset.title || node.title || node.alt;
			this.src = node.dataset.src;
			this.viewerGroupBy = node.dataset.viewerGroupBy;
			this.isSeparate = node.dataset.viewerSeparateItem || false;
			this.nakedActions = node.dataset.actions? JSON.parse(node.dataset.actions) : undefined;
		},

		/**
		 * @param {HTMLElement} node
		 */
		bindSourceNode: function (node)
		{
			this.sourceNode = node;
		},

		applyReloadOptions: function (options)
		{},

		isSeparateItem: function ()
		{
			return this.isSeparate;
		},

		isPullConnected: function()
		{
			if(top.BX.PULL)
			{
				// pull_v2
				if(BX.type.isFunction(top.BX.PULL.isConnected))
				{
					return top.BX.PULL.isConnected();
				}
				else
				{
					var debugInfo = top.BX.PULL.getDebugInfoArray();
					return debugInfo.connected;
				}
			}

			return false;
		},

		registerTransformationHandler: function(pullTag)
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
				BX.addCustomEvent('onPullEvent-main', function (command, params) {
					if (command === 'transformationComplete' && this.isTransforming)
					{
						this.loadData().then(function(){
							if (this.transformationPromise)
							{
								this.transformationPromise.fulfill(this);
							}
						}.bind(this));
					}
				}.bind(this));

				console.log('BX.PULL.extendWatch');
				BX.PULL.extendWatch(pullTag);
			}
			else
			{
				setTimeout(function(){
					BX.ajax.promise({
						url: BX.util.add_url_param(this.src, {ts: 'bxviewer'}),
						method: 'GET',
						dataType: 'json',
						headers: [{
							name: 'BX-Viewer-check-transformation',
							value: null
						}]
					}).then(function(response){
						if (!response.data || !response.data.transformation)
						{
							this.registerTransformationHandler();
						}
						else
						{
							this.loadData().then(function(){
								this.transformationPromise.fulfill(this);
							}.bind(this));
						}
					}.bind(this));
				}.bind(this), 5000);
			}

			this.transformationTimeoutId = setTimeout(function(){
				if (this.isLoading)
				{
					console.log('Throw transformationTimeout');
					if (this._loadPromise)
					{
						this._loadPromise.reject({
							status: "timeout",
							message: BX.message("JS_UI_VIEWER_ITEM_TRANSFORMATION_ERROR_1").replace('#DOWNLOAD_LINK#', this.getSrc()),
							item: this
						});

						this.isLoading = false;
						this.isTransformationError = true;
					}
				}
				else
				{
					console.log('We don\'t have transformationTimeout :) ');
				}

				this.resetTransformationTimeout();
			}.bind(this), this.transformationTimeout);
		},

		resetTransformationTimeout: function ()
		{
			if(this.transformationTimeoutId)
			{
				clearTimeout(this.transformationTimeoutId);
			}

			this.transformationTimeoutId = null;
		},

		init: function ()
		{},

		load: function ()
		{
			var promise = new BX.Promise();

			if (this.isLoaded)
			{
				promise.fulfill(this);
				console.log('isLoaded');

				return promise;
			}
			if (this.isTransformationError)
			{
				promise.reject({
					item: this
				});
				return promise;
			}
			if (this.isLoading)
			{
				console.log('isLoading');

				return this._loadPromise;
			}

			this.isLoading = true;
			this._loadPromise = this.loadData().then(function(item){
				this.isLoaded = true;
				this.isLoading = false;
				this.isTransforming = false;

				return item;
			}.bind(this)).catch(function (reason) {
				console.log('catch');
				this.isLoaded = false;
				this.isLoading = false;
				this.isTransforming = false;

				if(!reason.item)
				{
					reason.item = this;
				}

				var promise = new BX.Promise();
				promise.reject(reason);

				return promise;
			}.bind(this));

			console.log('will load');

			return this._loadPromise;
		},

		/**
		 * Returns list of classes which will be added to viewer container before showing
		 * and will be deleted after hiding.
		 * @return {Array}
		 */
		listContainerModifiers: function()
		{
			return [];
		},

		getSrc: function()
		{
			return this.src;
		},

		hashCode: function (string)
		{
			var h = 0, l = string.length, i = 0;
			if (l > 0)
			{
				while (i < l)
					h = (h << 5) - h + string.charCodeAt(i++) | 0;
			}
			return h;
		},

		generateUniqueId: function ()
		{
			return this.hashCode(this.getSrc() || '') + (Math.floor(Math.random() * Math.floor(10000)));
		},

		getTitle: function()
		{
			return this.title;
		},

		getGroupBy: function()
		{
			return this.viewerGroupBy;
		},

		getNakedActions: function()
		{
			if (typeof this.nakedActions === 'undefined')
			{
				return [{
					type: 'download'
				}];
			}

			return this.nakedActions;
		},

		setActions: function(actions)
		{
			this.actions = actions;
		},

		getActions: function()
		{
			return this.actions;
		},

		/**
		 * @returns {BX.Promise}
		 */
		loadData: function ()
		{
			var promise = new BX.Promise();
			promise.setAutoResolve(true);
			promise.fulfill(this);

			return promise;
		},

		render: function ()
		{},

		/**
		 * @returns {BX.Promise}
		 */
		getContentWidth: function()
		{},

		handleKeyPress: function (event)
		{},

		asFirstToShow: function ()
		{},

		afterRender: function ()
		{},

		beforeHide: function()
		{}
	};

	/**
	 * @extends {BX.UI.Viewer.Item}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.Image = function (options)
	{
		options = options || {};

		BX.UI.Viewer.Item.apply(this, arguments);

		this.resizedSrc = options.resizedSrc;
		this.width = options.width;
		this.height = options.height;
		/**
		 * @type {HTMLImageElement}
		 */
		this.imageNode = null;
		this.layout = {
			container: null
		}
	};

	BX.UI.Viewer.Image.prototype =
	{
		__proto__: BX.UI.Viewer.Item.prototype,
		constructor: BX.UI.Viewer.Item,

		/**
		 * @param {HTMLElement} node
		 */
		setPropertiesByNode: function (node)
		{
			BX.UI.Viewer.Item.prototype.setPropertiesByNode.apply(this, arguments);

			this.src = node.dataset.src || node.src;
			this.width = node.dataset.width;
			this.height = node.dataset.height;
		},

		applyReloadOptions: function (options)
		{
			this.controller.unsetCachedData(this.src);
		},

		tryToExportResizedSrcFromSourceNode: function()
		{
			/**
			 * @see .ui-viewer-inner-content-wrapper > * {
			 * max-height: calc(100% - 210px)
			 */
			var paddingHeight = 210;
			if (!(this.sourceNode instanceof Image))
			{
				return;
			}

			if (!this.sourceNode.naturalWidth)
			{
				return;
			}

			var offsetHeight = this.controller.getItemContainer().offsetHeight;
			var offsetWidth = this.controller.getItemContainer().offsetWidth;
			var scale = offsetHeight / offsetWidth;
			var realMaxHeight = (offsetHeight - paddingHeight);
			var realMaxWidth = realMaxHeight / scale;

			if (this.sourceNode.naturalWidth >= realMaxWidth || this.sourceNode.naturalHeight >= realMaxHeight)
			{
				this.resizedSrc = this.sourceNode.src;
			}
		},

		loadData: function ()
		{
			var promise = new BX.Promise();

			if (!this.shouldRunLocalResize())
			{
				this.resizedSrc = this.src;
			}
			this.tryToExportResizedSrcFromSourceNode();

			if (this.controller.getCachedData(this.src))
			{
				this.resizedSrc = this.controller.getCachedData(this.src).resizedSrc;
			}

			if (!this.resizedSrc)
			{
				var xhr = new XMLHttpRequest();
				xhr.onreadystatechange = function () {
					if(xhr.readyState !== XMLHttpRequest.DONE)
					{
						return;
					}
					if ((xhr.status === 200 || xhr.status === 0) && xhr.response)
					{
						console.log('resize image');
						this.resizedSrc = URL.createObjectURL(xhr.response);
						this.imageNode = new Image();
						this.imageNode.src = this.resizedSrc;
						this.imageNode.onload = function () {
							promise.fulfill(this);
						}.bind(this);

						this.controller.setCachedData(this.src, {resizedSrc: this.resizedSrc});
					}
					else
					{
						promise.reject({
							item: this,
							type: 'error'
						});
					}

				}.bind(this);
				xhr.open('GET', BX.util.add_url_param(this.src, {ts: 'bxviewer'}), true);
				xhr.responseType = 'blob';
				xhr.setRequestHeader('BX-Viewer-image', 'x');
				xhr.send();
			}
			else
			{
				this.imageNode = new Image();
				this.imageNode.onload = function () {
					promise.fulfill(this);
				}.bind(this);
				this.imageNode.onerror = this.imageNode.onabort = function (event) {
					console.log('reject');
					promise.reject({
						item: this,
						type: 'error'
					});
				}.bind(this);

				this.imageNode.src = this.resizedSrc;
			}

			return promise;
		},

		shouldRunLocalResize: function ()
		{
			return !this.controller.isExternalLink(this.src);
		},

		render: function ()
		{
			var item = document.createDocumentFragment();

			item.appendChild(this.imageNode);

			if (this.title)
			{
				item.appendChild(BX.create('div', {
					props: {
						className: 'viewer-inner-fullsize'
					},
					children: [
						BX.create('a', {
							props: {
								href: BX.util.add_url_param(this.src, {ts: 'bxviewer', ibxShowImage: 1}),
								target: '_blank'
							},
							text: BX.message('JS_UI_VIEWER_IMAGE_VIEW_FULL_SIZE'),
							events: {
								click: function(e){
									e.stopPropagation();
								}
							}
						})
					]
				}));
			}

			this.imageNode.alt = this.title;

			return item;
		},

		/**
		 * @returns {BX.Promise}
		 */
		getContentWidth: function()
		{
			var promise = new BX.Promise();
			promise.fulfill(this.imageNode.offsetWidth);

			return promise;
		},

		afterRender: function ()
		{
			//it's a dirty hack for IE11 and working with Image and blob content to prevent unexpected width&height attributes
			if (!window.chrome)
			{
				setTimeout(function () {
					this.imageNode.removeAttribute('width');
					this.imageNode.removeAttribute('height');
				}.bind(this), 200);
			}
		}
	};


	/**
	 * @extends {BX.UI.Viewer.Item}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.PlainText = function (options)
	{
		options = options || {};

		BX.UI.Viewer.Item.apply(this, arguments);

		this.content = options.content;
	};

	BX.UI.Viewer.PlainText.prototype =
	{
		__proto__: BX.UI.Viewer.Item.prototype,
		constructor: BX.UI.Viewer.Item,

		/**
		 * @param {HTMLElement} node
		 */
		setPropertiesByNode: function (node)
		{
			BX.UI.Viewer.Item.prototype.setPropertiesByNode.apply(this, arguments);

			this.content = node.dataset.content;
		},

		render: function ()
		{
			var contentNode = BX.create('span', {
				text: this.content
			});

			contentNode.style.fontSize = '14px';
			contentNode.style.color = 'white';

			return contentNode;
		}
	};

	/**
	 * @extends {BX.UI.Viewer.Item}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.Audio = function (options)
	{
		options = options || {};

		BX.UI.Viewer.Item.apply(this, arguments);

		this.playerId = 'audio-playerId_' + this.generateUniqueId();
		this.svgMask = null;
	};

	BX.UI.Viewer.Audio.prototype =
	{
		__proto__: BX.UI.Viewer.Item.prototype,
		constructor: BX.UI.Viewer.Item,

		/**
		 * @param {HTMLElement} node
		 */
		setPropertiesByNode: function (node)
		{
			BX.UI.Viewer.Item.prototype.setPropertiesByNode.apply(this, arguments);

			this.playerId = 'audio-playerId_' + this.generateUniqueId();
		},

		loadData: function ()
		{
			var promise = new BX.Promise();
			if (BX.getClass('BX.Fileman.Player'))
			{
				promise.fulfill(this);

				return promise;
			}

			var headers = [
				{
					name: 'BX-Viewer-src',
					value: this.src
				},
				{
					name: 'BX-Viewer',
					value: 'audio'
				}
			];
			var ajaxPromise = BX.ajax.promise({
				url: BX.util.add_url_param(this.src, {ts: 'bxviewer'}),
				method: 'GET',
				dataType: 'json',
				headers: headers
			});

			ajaxPromise.then(function (response) {
				if (!response || !response.data)
				{
					var errors = response? response.errors : [];

					promise.reject({
						item: this,
						type: 'error',
						errors: errors || []
					});

					return;
				}

				if (response.data.html && !BX.getClass('BX.Fileman.Player'))
				{
					var html = BX.processHTML(response.data.html);

					BX.load(html.STYLE, function(){
						BX.ajax.processScripts(html.SCRIPT, undefined, function(){
							promise.fulfill(this);
						}.bind(this));
					}.bind(this));
				}
				else
				{
					promise.fulfill(this);
				}

			}.bind(this));

			return promise;
		},

		render: function ()
		{
			this.player = new BX.Fileman.Player(this.playerId, {
				width: 320,
				height: 52,
				isAudio: true,
				skin: 'vjs-viewer-audio-player-skin',
				sources: [{
					src: this.src,
					type: 'audio/mp3'
				}],
				onInit: function(player)
				{
					player.vjsPlayer.controlBar.removeChild('timeDivider');
					player.vjsPlayer.controlBar.removeChild('durationDisplay');
					player.vjsPlayer.controlBar.removeChild('fullscreenToggle');
					player.vjsPlayer.hasStarted(true);
				}
			});

			return this.player.createElement();
		},

		afterRender: function()
		{
			this.player.init();
		}
	};

	/**
	 * @extends {BX.UI.Viewer.Item}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.HightlightCode = function (options)
	{
		options = options || {};

		BX.UI.Viewer.Item.apply(this, arguments);

		this.content = options.content;
	};

	BX.UI.Viewer.HightlightCode.prototype =
	{
		__proto__: BX.UI.Viewer.Item.prototype,
		constructor: BX.UI.Viewer.Item,

		/**
		 * @param {HTMLElement} node
		 */
		setPropertiesByNode: function (node)
		{
			BX.UI.Viewer.Item.prototype.setPropertiesByNode.apply(this, arguments);

			this.content = node.dataset.content;
		},

		listContainerModifiers: function()
		{
			return [
				'ui-viewer-document',
				'ui-viewer-document-hlcode'
			]
		},

		loadData: function ()
		{
			var promise = new BX.Promise();

			BX.loadExt('ui.highlightjs').then(function () {
				if (!this.content)
				{
					var xhr = new XMLHttpRequest();
					xhr.onreadystatechange = function () {
						if(xhr.readyState !== XMLHttpRequest.DONE)
						{
							return;
						}
						if ((xhr.status === 200 || xhr.status === 0) && xhr.response)
						{
							this.content = xhr.response;
							console.log('text content is loaded');
							this.controller.setCachedData(this.src, {content: this.content});

							promise.fulfill(this);
						}
						else
						{
							promise.reject({
								item: this,
								type: 'error'
							});
						}

					}.bind(this);
					xhr.open('GET', BX.util.add_url_param(this.src, {ts: 'bxviewerText'}), true);
					xhr.responseType = 'text';
					xhr.send();
				}
				else
				{
					promise.fulfill(this);
				}
			}.bind(this));

			return promise;
		},

		render: function ()
		{
			var ext = this.getTitle().substring(this.getTitle().lastIndexOf('.') + 1);

			return BX.create('div', {
				props: {
					tabIndex: 2208
				},
				style: {
					width: '100%',
					height: '100%',
					paddingTop: '67px',
					background: 'rgba(0, 0, 0, 0.1)',
					overflow: 'auto'
				},
				children: [
					BX.create('pre', {
						children: [
							this.codeNode = BX.create('code', {
								props: {
									className: hljs.getLanguage(ext)? ext : 'plaintext'
								},
								style: {
									fontSize: '14px',
									textAlign: 'left'
								},
								text: this.content
							})
						]
					})
				]
			});
		},

		/**
		 * @returns {BX.Promise}
		 */
		getContentWidth: function()
		{
			var promise = new BX.Promise();

			promise.fulfill(this.codeNode.offsetWidth);

			return promise;
		},

		afterRender: function()
		{
			hljs.highlightBlock(this.codeNode)
		}
	};

	/**
	 * @extends {BX.UI.Viewer.Item}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.Unknown = function (options)
	{
		BX.UI.Viewer.Item.apply(this, arguments);
	};

	BX.UI.Viewer.Unknown.prototype =
	{
		__proto__: BX.UI.Viewer.Item.prototype,
		constructor: BX.UI.Viewer.Item,

		render: function ()
		{
			return BX.create('div', {
				props: {
					className: 'ui-viewer-unsupported'
				},
				children: [
					BX.create('div', {
						props: {
							className: 'ui-viewer-unsupported-title'
						},
						text: BX.message('JS_UI_VIEWER_ITEM_UNKNOWN_TITLE')
					}),
					BX.create('div', {
						props: {
							className: 'ui-viewer-unsupported-text'
						},
						text: BX.message('JS_UI_VIEWER_ITEM_UNKNOWN_NOTICE')
					}),
					BX.create('a', {
						props: {
							className: 'ui-btn ui-btn-light-border ui-btn-themes',
							href: this.getSrc(),
							target: '_blank'
						},
						text: BX.message('JS_UI_VIEWER_ITEM_UNKNOWN_DOWNLOAD_ACTION')
					})
				]
			});
		}
	};

	/**
	 * @extends {BX.UI.Viewer.Item}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.Video = function (options)
	{
		options = options || {};

		BX.UI.Viewer.Item.apply(this, arguments);

		this.player = null;
		this.sources = [];
		this.contentNode = null;
		this.forceTransformation = false;
		this.videoWidth = null;
		this.playerId = 'playerId_' + this.generateUniqueId();
	};

	BX.UI.Viewer.Video.prototype =
	{
		__proto__: BX.UI.Viewer.Item.prototype,
		constructor: BX.UI.Viewer.Item,

		/**
		 * @param {HTMLElement} node
		 */
		setPropertiesByNode: function (node)
		{
			BX.UI.Viewer.Item.prototype.setPropertiesByNode.apply(this, arguments);

			this.playerId = 'playerId_' + this.generateUniqueId();
		},

		applyReloadOptions: function (options)
		{
			if (options.forceTransformation)
			{
				this.forceTransformation = true;
			}
		},

		init: function ()
		{
			BX.addCustomEvent('PlayerManager.Player:onAfterInit', this.handleAfterInit.bind(this));
			BX.addCustomEvent('PlayerManager.Player:onError', this.handleAfterInit.bind(this));
		},

		loadData: function ()
		{
			var promise = new BX.Promise();

			var headers = [
				{
					name: 'BX-Viewer-src',
					value: this.src
				}
			];

			headers.push({
				name: this.forceTransformation? 'BX-Viewer-force-transformation' : 'BX-Viewer',
				value: 'video'
			});

			var ajaxPromise = BX.ajax.promise({
				url: BX.util.add_url_param(this.src, {ts: 'bxviewer'}),
				method: 'GET',
				dataType: 'json',
				headers: headers
			});

			ajaxPromise.then(function (response) {
				if (!response || !response.data)
				{
					var errors = response? response.errors : [];

					promise.reject({
						item: this,
						type: 'error',
						errors: errors || []
					});

					return;
				}

				if (response.data.hasOwnProperty('pullTag'))
				{
					if (!this.isTransforming)
					{
						this.transformationPromise = promise;
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

					if (response.data.html)
					{
						var html = BX.processHTML(response.data.html);

						BX.load(html.STYLE, function(){
							BX.ajax.processScripts(html.SCRIPT, undefined, function(){
								promise.fulfill(this);
							}.bind(this));
						}.bind(this));
					}
				}
			}.bind(this));

			return promise;
		},

		handleAfterInit: function (player)
		{
			if (player.id !== this.playerId)
			{
				return;
			}

			if (this.handleVideoError(player))
			{
				return;
			}

			if(player.vjsPlayer.videoWidth() > 0 && player.vjsPlayer.videoHeight() > 0)
			{
				this.adjustVideo();
			}
			else
			{
				player.vjsPlayer.one('loadedmetadata', this.adjustVideo.bind(this));
			}
		},

		handleVideoError: function (player)
		{
			if (player.id !== this.playerId)
			{
				return false;
			}

			if (player.vjsPlayer.error() && !this.forceTransformation)
			{
				console.log('forceTransformation');
				this.controller.reload(this, {
					forceTransformation: true
				});

				return true;
			}

			return false;
		},

		adjustVideo: function()
		{
			var container = this.contentNode;
			if (!container)
			{
				return;
			}

			if (!this.player.vjsPlayer)
			{
				return;
			}

			if (this.adjustVideoWidth(container, this.player.width, this.player.height, this.player.vjsPlayer.videoWidth(), this.player.vjsPlayer.videoHeight()))
			{
				this.player.vjsPlayer.fluid(true);
			}

			BX.addClass(container, 'player-loaded');
			BX.style(container, 'opacity', 1);
		},

		adjustVideoWidth: function(node, maxWidth, maxHeight, videoWidth, videoHeight)
		{
			if (!BX.type.isDomNode(node))
			{
				return false;
			}
			if (!maxWidth || !maxHeight || !videoWidth || !videoHeight)
			{
				return false;
			}
			if (videoHeight < maxHeight && videoWidth < maxWidth)
			{
				BX.width(node, videoWidth);
				this.videoWidth = videoWidth;
				if (!this.contentWidthPromise.state)
				{
					this.contentWidthPromise.fulfill(this.videoWidth);
				}

				return true;
			}
			else
			{
				maxHeight = window.innerHeight - 250;

				var resultRelativeSize = maxWidth / maxHeight;
				var videoRelativeSize = videoWidth / videoHeight;
				var reduceRatio = 1;
				if (resultRelativeSize > videoRelativeSize)
				{
					reduceRatio = maxHeight / videoHeight;
				}
				else
				{
					reduceRatio = maxWidth / videoWidth;
				}

				BX.width(node, Math.floor(videoWidth * reduceRatio));
				this.videoWidth = Math.floor(videoWidth * reduceRatio);
				if (!this.contentWidthPromise.state)
				{
					this.contentWidthPromise.fulfill(this.videoWidth);
				}
			}

			return true;
		},

		/**
		 * @returns {BX.Promise}
		 */
		getContentWidth: function()
		{
			this.contentWidthPromise = new BX.Promise();

			if (this.videoWidth)
			{
				this.contentWidthPromise.fulfill(this.videoWidth);
			}

			return this.contentWidthPromise;
		},

		render: function ()
		{
			this.player = new BX.Fileman.Player(this.playerId, {
				width: this.width,
				height: this.height,
				sources: this.sources
			});

			this.controller.showLoading();

			return this.contentNode = BX.create('div', {
				style: {
					opacity: 0
				},
				children: [
					this.player.createElement()
				]
			});
		},

		asFirstToShow: function ()
		{
			if (this.player)
			{
				this.player.mute(true);
				this.player.play();
			}
		},

		afterRender: function()
		{
			this.player.init();
		}
	};
})();
