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
		this.sourceNode = null;
		this.transformationPromise = null;
		this.transformationTimeoutId = null;
		this.viewerGroupBy = null;
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
					if (command === 'transformationComplete' && this.transformationPromise)
					{
						this.loadData().then(function(){
							this.transformationPromise.fulfill(this);
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
				if (!this.isLoaded)
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
			if (this.isLoading)
			{
				console.log('isLoading');

				return this._loadPromise;
			}

			this.isLoading = true;
			this._loadPromise = this.loadData().then(function(item){
				this.isLoaded = true;
				this.isLoading = false;

				return item;
			}.bind(this)).catch(function (reason) {
				console.log('catch');
				this.isLoaded = false;
				this.isLoading = false;

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
					promise.reject({
						item: this,
						type: 'error',
						errors: response.errors || []
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
		this.transformationPromise = null;
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
					promise.reject({
						item: this,
						type: 'error',
						errors: response.errors || []
					});

					return;
				}

				if (response.data.hasOwnProperty('pullTag'))
				{
					this.transformationPromise = promise;
					this.registerTransformationHandler(response.data.pullTag);
				}
				else
				{
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

	/**
	 * @extends {BX.UI.Viewer.Item}
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.Document = function (options)
	{
		BX.UI.Viewer.Item.apply(this, arguments);

		options = options || {};

		this.scale = options.scale || 1.4;
		this.pdfDocument = null;
		this.pdfPages = {};
		this.pdfRenderedPages = {};
		this.lastRenderedPdfPage = null;
		this.contentNode = null;
		this.previewHtml = null;
		this.previewScriptToProcess = null;
		this.transformationPromise = null;
		this.disableAnnotationLayer = false;
	};

	BX.UI.Viewer.Document.prototype =
	{
		__proto__: BX.UI.Viewer.Item.prototype,
		constructor: BX.UI.Viewer.Item,

		/**
		 * @param {HTMLElement} node
		 */
		setPropertiesByNode: function (node)
		{
			BX.UI.Viewer.Item.prototype.setPropertiesByNode.apply(this, arguments);

			this.disableAnnotationLayer = node.dataset.hasOwnProperty('disableAnnotationLayer');
		},

		applyReloadOptions: function (options)
		{
			this.controller.unsetCachedData(this.src);
		},

		listContainerModifiers: function()
		{
			return [
				'ui-viewer-document'
			]
		},

		loadData: function ()
		{
			var promise = new BX.Promise();

			console.log('loadData pdf');
			var ajaxPromise = BX.ajax.promise({
				url: BX.util.add_url_param(this.src, {ts: 'bxviewer'}),
				method: 'GET',
				dataType: 'json',
				headers: [
					{
						name: 'BX-Viewer-src',
						value: this.src
					},
					{
						name: 'BX-Viewer',
						value: 'document'
					}
				]
			});

			ajaxPromise.then(function (response) {
				if (!response || !response.data)
				{
					promise.reject({
						item: this,
						message: BX.message("JS_UI_VIEWER_ITEM_TRANSFORMATION_ERROR_1").replace('#DOWNLOAD_LINK#', this.getSrc()),
						type: 'error'
					});

					return;
				}

				if (response.data.hasOwnProperty('pullTag'))
				{
					this.transformationPromise = promise;
					this.registerTransformationHandler(response.data.pullTag);
				}

				if (response.data.data && response.data.data.src)
				{
					this._pdfSrc = response.data.data.src;
					BX.loadExt('ui.' + this.getPdfJsExtensionName()).then(function () {
						if (!pdfjsLib.GlobalWorkerOptions.workerSrc)
						{
							pdfjsLib.GlobalWorkerOptions.workerSrc = '/bitrix/js/ui/' + this.getPdfJsExtensionName() + '/pdf.worker.js';
						}

						promise.fulfill(this);
					}.bind(this), function(){});
				}
			}.bind(this));

			return promise;
		},

		getPdfJsExtensionName: function()
		{
			return BX.browser.IsIE11()? 'pdfjs-ie11' : 'pdfjs';
		},

		render: function ()
		{
			this.controller.showLoading();

			this.contentNode = BX.create('div', {
				props: {
					className: 'ui-viewer-item-document-content',
					tabIndex: 2208
				},
				style: {
					width: '100%',
					height: '100%',
					paddingTop: '67px',
					background: 'rgba(0, 0, 0, 0.1)',
					overflow: 'auto'
				}
			});

			BX.bind(this.contentNode, 'scroll', BX.throttle(this.handleScrollDocument.bind(this), 100));

			return this.contentNode;
		},

		getNakedActions: function()
		{
			var nakedActions = BX.UI.Viewer.Item.prototype.getNakedActions.apply(this, arguments) || [];

			return this.insertPrintBeforeInfo(nakedActions);
		},

		insertPrintBeforeInfo: function(actions)
		{
			actions = actions || [];

			var infoIndex = null;
			for (var i = 0; i < actions.length; i++)
			{
				if (actions[i].type === 'info')
				{
					infoIndex = i;
				}
			}

			var printAction = {
				type: 'print',
				action: this.print.bind(this)
			};

			if (infoIndex === null)
			{
				actions.push(printAction);
			}
			else
			{
				actions = BX.util.insertIntoArray(actions, infoIndex, printAction);
			}

			return actions;
		},

		getFirstDocumentPageHeight: function ()
		{
			var promise = new BX.Promise();
			if (this._height)
			{
				promise.fulfill(this._height);
			}
			else
			{
				this.getDocumentPage(this.pdfDocument, 1).then(function (page) {
					var viewport = page.getViewport(this.scale);
					this._height = viewport.height;

					promise.fulfill(this._height);
				}.bind(this))
			}

			return promise;
		},

		handleScrollDocument: function (event)
		{
			var sizeToLoad = 3;
			this.getFirstDocumentPageHeight().then(function (height) {
				var scrollBottom = this.contentNode.scrollHeight - this.contentNode.scrollTop - this.contentNode.clientHeight;
				if (scrollBottom < height * sizeToLoad && this.pdfDocument.numPages > this.lastRenderedPdfPage)
				{
					for (var i = this.lastRenderedPdfPage + 1; i <= Math.min(this.pdfDocument.numPages, this.lastRenderedPdfPage + sizeToLoad); i++)
					{
						this.renderDocumentPage(this.pdfDocument, i);
					}
				}

			}.bind(this));
		},

		loadDocument: function ()
		{
			var promise = new BX.Promise();
			if (this.pdfDocument)
			{
				promise.fulfill(this.pdfDocument);
			}
			else
			{
				pdfjsLib.getDocument(this._pdfSrc).promise.then(function(pdf) {
					this.pdfDocument = pdf;
					promise.fulfill(this.pdfDocument);
				}.bind(this));
			}

			return promise;
		},

		getDocumentPage: function(pdf, pageNumber)
		{
			var promise = new BX.Promise();

			if (this.pdfPages[pageNumber])
			{
				promise.fulfill(this.pdfPages[pageNumber]);
			}
			else
			{
				pdf.getPage(pageNumber).then(function (page) {
					this.pdfPages[pageNumber] = page;

					promise.fulfill(this.pdfPages[pageNumber]);
				}.bind(this));
			}

			return promise;
		},

		renderDocumentPage: function(pdf, pageNumber)
		{
			if (this.pdfRenderedPages[pageNumber])
			{
				return;
			}

			this.pdfRenderedPages[pageNumber] = true;
			this.getDocumentPage(pdf, pageNumber).then(function (page) {

				var canvas = this.createCanvasPage();
				var viewport = page.getViewport(this.scale);
				canvas.height = viewport.height;
				canvas.width = viewport.width;
				var renderPromise = page.render({canvasContext: canvas.getContext('2d'), viewport: viewport});

				if (!this.disableAnnotationLayer)
				{
					renderPromise.then(function(){
						return page.getAnnotations();
					}).then(function(annotationData){
						var positionData = BX.pos(canvas);
						var annotationLayer = BX.create('div', {
							props : { className: 'ui-viewer-pdf-annotation-layer'}
						});

						BX.insertAfter(annotationLayer, canvas);
						BX.adjust(annotationLayer, {style: {
							margin: '-' + canvas.offsetHeight + 'px auto 0 auto',
							height: canvas.height + 'px',
							width: canvas.width + 'px'
						}});

						pdfjsLib.AnnotationLayer.render({
							viewport: viewport.clone({dontFlip: true}),
							linkService: pdfjsLib.SimpleLinkService,
							div: annotationLayer,
							annotations: annotationData,
							page: page
						});
					});
				}

				renderPromise.then(function(){
					return page.getTextContent();
				}).then(function(textContent){
					var positionData = BX.pos(canvas);
					var textLayer = BX.create('div', {
						props : { className: 'ui-viewer-pdf-text-layer'}
					});

					BX.insertAfter(textLayer, canvas);
					BX.adjust(textLayer, {style: {
						margin: '-' + canvas.offsetHeight + 'px auto 0 auto',
						height: canvas.height + 'px',
						width: canvas.width + 'px'
					}});

					pdfjsLib.renderTextLayer({
						textContent: textContent,
						container: textLayer,
						viewport: viewport,
						textDivs: []
					});
				});

				this.lastRenderedPdfPage = Math.max(pageNumber, this.lastRenderedPdfPage);

				if (pageNumber === 1)
				{
					this.firstWidthDocumentPage = canvas.width;
					this.contentWidthPromise.fulfill(this.firstWidthDocumentPage);
				}

				this.controller.hideLoading();
			}.bind(this));
		},

		createCanvasPage: function ()
		{
			var canvas = document.createElement('canvas');
			canvas.className = 'ui-viewer-document-page-canvas';
			this.contentNode.appendChild(canvas);

			return canvas;
		},

		/**
		 * @returns {BX.Promise}
		 */
		getContentWidth: function()
		{
			this.contentWidthPromise = new BX.Promise();

			if (this.firstWidthDocumentPage)
			{
				this.contentWidthPromise.fulfill(this.firstWidthDocumentPage);
			}

			return this.contentWidthPromise;
		},

		afterRender: function ()
		{
			this.loadDocument().then(function (pdf) {
				for (var i = 1; i <= Math.min(pdf.numPages, 3); i++)
				{
					if (i === 1)
					{
						this._handleControls = this.controller.handleVisibleControls.bind(this.controller);
						this.controller.enableReadingMode(true);

						var printAction = this.controller.actionPanel.getItemById('print');
						if (printAction)
						{
							printAction.layout.container.classList.remove('ui-btn-disabled');
						}

						BX.throttle(BX.bind(window, 'mousemove', this._handleControls), 20);
					}

					this.renderDocumentPage(pdf, i);
				}
			}.bind(this));
		},

		beforeHide: function()
		{
			this.pdfRenderedPages = [];
			BX.unbind(window, 'mousemove', this._handleControls);
			if (this.printer)
			{
				this.hidePrintProgress();
				this.printer.destroy();
			}
		},

		updatePrintProgressMessage: function (index, total)
		{
			var progress = Math.round((index/total)*100);
			this.controller.setTextOnLoading(BX.message('JS_UI_VIEWER_ITEM_PREPARING_TO_PRINT').replace('#PROGRESS#', progress));
		},

		showPrintProgress: function (index, total)
		{
			this.contentNode.style.opacity = 0.7;
			this.contentNode.style.filter = 'blur(2px)';

			this.controller.showLoading({
				zIndex: 1
			});

			this.updatePrintProgressMessage(index, total);
		},

		hidePrintProgress: function ()
		{
			this.contentNode.style.opacity = null;
			this.contentNode.style.filter = null;

			this.controller.hideLoading();
		},

		print: function ()
		{
			if (!this.pdfDocument)
			{
				console.warn('Where is pdf document to print?');

				return;
			}

			this.showPrintProgress(0, this.pdfDocument.numPages);

			this.printer = new BX.UI.Viewer.Document.PrintService({
				pdf: this.pdfDocument
			});

			this.printer.init().then(function () {
				this.printer.prepare({
					onProgress: this.updatePrintProgressMessage.bind(this)
				}).then(function(){
					this.hidePrintProgress();
					this.printer.performPrint();
				}.bind(this));
			}.bind(this));
		},

		handleKeyPress: function (event)
		{
			switch (event.code)
			{
				case 'PageDown':
				case 'PageUp':
				case 'ArrowDown':
				case 'ArrowUp':
					BX.focus(this.contentNode);
					break;
			}
		}
	};

	/**
	 * @param options
	 * @constructor
	 */
	BX.UI.Viewer.Document.PrintService = function (options)
	{
		options = options || {};
		this.pdf = options.pdf;
		this.iframe = null;
		this.documentOverview = {};
	};

	BX.UI.Viewer.Document.PrintService.prototype =
	{
		init: function ()
		{
			var promise = new BX.Promise();

			this.pdf.getPage(1).then(function (page) {
				var viewport = page.getViewport(1);

				this.documentOverview = {
					width: viewport.width,
					height: viewport.height,
					rotation: viewport.rotation
				};

				promise.fulfill(this.documentOverview);
			}.bind(this));

			return promise;
		},

		/**
		 * @param {?Object} options
		 * @param {Function} [options.onProgress]
		 * @return {BX.Promise}
		 */
		prepare: function (options)
		{
			options = options || {};
			var pageCount = this.pdf.numPages;
			var currentPage = -1;
			var promise = new BX.Promise();
			var onProgress = null;
			if (BX.type.isFunction(options.onProgress))
			{
				onProgress = options.onProgress;
			}

			this.frame = this.createIframe();

			var process = function() {
				if (++currentPage >= pageCount)
				{
					console.log('finish', this.frame.contentWindow.document);

					setTimeout(function(){
						promise.fulfill();
					}.bind(this), 1000);

					return;
				}

				this.renderPage(currentPage+1).then(function(){
					if (onProgress)
					{
						onProgress(currentPage+1, pageCount);
					}
					process();
				});
			}.bind(this);

			process();

			return promise;
		},

		renderPage: function (pageNumber)
		{
			return this.pdf.getPage(pageNumber).then(function(page) {
				var scratchCanvas = document.createElement('canvas');
				var viewport = page.getViewport(1);
				// The size of the canvas in pixels for printing.
				var PRINT_RESOLUTION = 150;
				var PRINT_UNITS = PRINT_RESOLUTION / 72.0;
				scratchCanvas.width = Math.floor(viewport.width * PRINT_UNITS);
				scratchCanvas.height = Math.floor(viewport.height * PRINT_UNITS);

				// The physical size of the img as specified by the PDF document.
				var CSS_UNITS = 96.0 / 72.0;
				var width = Math.floor(viewport.width * CSS_UNITS) + 'px';
				var height = Math.floor(viewport.height * CSS_UNITS) + 'px';

				var ctx = scratchCanvas.getContext('2d');
				ctx.save();
				ctx.fillStyle = 'rgb(255, 255, 255)';
				ctx.fillRect(0, 0, scratchCanvas.width, scratchCanvas.height);
				ctx.restore();

				var renderContext = {
					canvasContext: ctx,
					transform: [PRINT_UNITS, 0, 0, PRINT_UNITS, 0, 0],
					viewport: page.getViewport(1, viewport.rotation),
					intent: 'print'
				};

				return page.render(renderContext).promise.then(function() {
					return {
						scratchCanvas: scratchCanvas,
						width: width,
						height: height
					}
				});
			}).then(function(printItem) {

				var img = document.createElement('img');
				img.style.width = printItem.width;
				img.style.height = printItem.height;

				var scratchCanvas = printItem.scratchCanvas;
				if (('toBlob' in scratchCanvas) && !this.disableCreateObjectURL)
				{
					scratchCanvas.toBlob(function (blob) {
						img.src = URL.createObjectURL(blob);
					});
				}
				else
				{
					img.src = scratchCanvas.toDataURL();
				}

				var wrapper = document.createElement('div');
				wrapper.appendChild(img);

				this.frame.contentWindow.document.body.appendChild(wrapper);
			}.bind(this));
		},

		destroy: function()
		{
			if (this.frame)
			{
				BX.remove(this.frame);
			}
		},

		createIframe: function ()
		{
			var frame = document.createElement("iframe");
			frame.src = "about:blank";
			frame.name = "document-print-frame";
			frame.style.display = "none";
			document.body.appendChild(frame);

			var frameWindow = frame.contentWindow;
			var frameDoc = frameWindow.document;
			frameDoc.open();
			frameDoc.write('<html><head>');

			var pageSize = this.getDocumentOverview();
			var headTags = "<style>";
			headTags += "html, body { background: #fff !important; height: 100%; }";
			headTags += '@supports ((size:A4) and (size:1pt 1pt)) {' +
				'@page { size: ' + pageSize.width + 'pt ' + pageSize.height + 'pt;}' +
				'}';
			headTags += "</style>";

			frameDoc.write(headTags);

			frameDoc.write('</head><body>');
			frameDoc.write('</body></html>');
			frameDoc.close();

			return frame;
		},

		performPrint: function ()
		{
			this.frame.contentWindow.focus();
			this.frame.contentWindow.print();
		},

		getDocumentOverview: function ()
		{
			return this.documentOverview;
		}
	};
})();
