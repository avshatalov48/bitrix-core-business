;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");

	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var append = BX.Landing.Utils.append;
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;
	var fireCustomEvent = BX.Landing.Utils.fireCustomEvent;
	var setTextContent = BX.Landing.Utils.setTextContent;
	var rect = BX.Landing.Utils.rect;
	var create = BX.Landing.Utils.create;
	var style = BX.Landing.Utils.style;
	var join = BX.Landing.Utils.join;
	var isNumber = BX.Landing.Utils.isNumber;
	var isString = BX.Landing.Utils.isString;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isArray = BX.Landing.Utils.isArray;
	var addQueryParams = BX.Landing.Utils.addQueryParams;
	var Cache = BX.Landing.Cache;

	var TYPE_PAGE = "landing";
	var TYPE_BLOCK = "block";
	var TYPE_SYSTEM = "system";

	var SidebarButton = BX.Landing.UI.Button.SidebarButton;

	/**
	 * Implements interface for works with links list
	 *
	 * @extends {BX.Landing.UI.Panel.Content}
	 *
	 * @param {string} id
	 * @param {object} [data]
	 * @constructor
	 */
	BX.Landing.UI.Panel.URLList = function(id, data)
	{
		BX.Landing.UI.Panel.Content.apply(this, arguments);

		addClass(this.layout, "landing-ui-panel-url-list");
		addClass(this.overlay, "landing-ui-panel-url-list-overlay");
		addClass(this.overlay, "landing-ui-hide");

		this.overlay.hidden = true;
		this.overlay.dataset.isShown = "false";

		// Bind on block events
		onCustomEvent("BX.Landing.Block:init", this.refresh.bind(this));
 		onCustomEvent("BX.Landing.Block:remove", this.refresh.bind(this));

		// Append panel
		append(this.layout, document.body);

		// Make loader
		this.loader = new BX.Loader({target: this.content});

		this.promiseResolve = (function() {});
		this.layout.hidden = true;
		this.isNeedLoad = true;
		this.cache = new Cache();
	};


	/**
	 * Gets instance
	 * @returns {BX.Landing.UI.Panel.URLList}
	 */
	BX.Landing.UI.Panel.URLList.getInstance = function()
	{
		if (!BX.Landing.UI.Panel.URLList.instance)
		{
			BX.Landing.UI.Panel.URLList.instance = new BX.Landing.UI.Panel.URLList("url_list");
		}

		return BX.Landing.UI.Panel.URLList.instance;
	};


	/**
	 * Stores instance
	 * @static
	 * @type {BX.Landing.UI.Panel.URLList}
	 */
	BX.Landing.UI.Panel.URLList.instance = null;


	BX.Landing.UI.Panel.URLList.prototype = {
		constructor: BX.Landing.UI.Panel.URLList,
		__proto__: BX.Landing.UI.Panel.Content.prototype,


		/**
		 * Refresh cached values
		 */
		refresh: function()
		{
			this.isNeedLoad = true;
		},


		/**
		 * Shows loader
		 */
		showLoader: function()
		{
			this.loader.show(this.content);
		},


		/**
		 * Shows panel
		 * @param {String} view - landings|blocks
		 * @param {object} options
		 * @return {Promise}
		 */
		show: function(view, options)
		{
			BX.Landing.UI.Panel.Content.prototype.show.call(this);

			this.clear();
			this.showLoader();

			if (view === TYPE_PAGE)
			{
				removeClass(this.layout, "landing-ui-panel-url-list-blocks");
				setTextContent(this.title, BX.message("LANDING_LINKS_LANDINGS_TITLE"));
				this.showSites(options);
			}
			else
			{
				setTextContent(this.title, BX.message("LANDING_LINKS_BLOCKS_TITLE"));
				this.showBlocks(options);
			}

			return new Promise(function(resolve) {
				this.promiseResolve = resolve;
			}.bind(this));
		},


		/**
		 * Shows sites list
		 * @param {object} options
		 */
		showSites: function(options)
		{
			var currentSiteId = options.siteId;

			void style(this.layout, {
				width: null
			});

			void this.getSites(options).then(function(sites) {
				this.appendSidebarButton(
					new SidebarButton("current_site", {
						text: BX.message("LANDING_LINKS_PANEL_CURRENT_SITE")
					})
				);

				sites.forEach(function(site) {
					// noinspection EqualityComparisonWithCoercionJS
					if (parseInt(site.ID) == currentSiteId)
					{
						this.appendSidebarButton(
							new SidebarButton(site.ID, {
								text: site.TITLE,
								onClick: this.onSiteClick.bind(this, site.ID, options.enableAreas),
								child: true,
								active: true
							})
						);
					}
				}, this);

				this.getLandings(currentSiteId).then(function(landings) {
					if (isPlainObject(landings))
					{
						landings = Object.keys(landings).reduce(function(acc, key) {
							if (isPlainObject(landings[key]) && isArray(landings[key].result))
							{
								acc = acc.concat(landings[key].result);
							}

							return acc;
						}, []);
					}

					landings.forEach(function(landing) {
						if (!landing.IS_AREA || (landing.IS_AREA && options.enableAreas))
						{
							this.appendCard(
								new BX.Landing.UI.Card.LandingPreviewCard({
									title: landing.TITLE,
									description: landing.DESCRIPTION,
									preview: landing.PREVIEW,
									onClick: this.onLandingClick.bind(this, landing.ID, landing.TITLE)
								})
							)
						}
					}, this);

					var systemPages = this.getSystemPages();

					Object.keys(systemPages).forEach(function(key) {
						var page = systemPages[key];
						this.appendCard(
							new BX.Landing.UI.Card.LandingPreviewCard({
								title: page.name,
								description: page.description,
								preview: page.preview,
								onClick: this.onSystemClick.bind(this, key, page.name)
							})
						)
					}, this);

					this.loader.hide();
				}.bind(this));

				if (!options.currentSiteOnly)
				{
					this.appendSidebarButton(
						new SidebarButton("my_sites", {
							text: BX.message("LANDING_LINKS_PANEL_MY_SITES")
						})
					);

					sites.forEach(function(site) {
						this.appendSidebarButton(
							new SidebarButton(site.ID, {
								text: site.TITLE,
								onClick: this.onSiteClick.bind(this, site.ID, options.enableAreas),
								child: true
							})
						);
					}, this);
				}
			}.bind(this));
		},


		getSystemPages: function()
		{
			var result;

			try
			{
				result = BX.Landing.Main.getInstance().options.syspages;

				if (!isPlainObject(result))
				{
					result = {};
				}
			}
			catch(err)
			{
				result = {};
			}

			return result;
		},


		/**
		 * @private
		 * @return {BX.Landing.UI.Button.SidebarButton}
		 */
		createCurrentSiteButton: function()
		{
			return new SidebarButton("current_site", {
				text: BX.message("LANDING_LINKS_PANEL_CURRENT_SITE")
			});
		},


		/**
		 * Shows blocks list
		 * @param {object} options
		 */
		showBlocks: function(options)
		{
			var currentLandingId = options.landingId;
			var currentSiteId = options.siteId;

			void style(this.layout, {
				width: "880px"
			});

			this.getSites(options)
				.then(function(sites) {
					this.appendSidebarButton(
						this.createCurrentSiteButton()
					);

					var sitesIds = sites.map(function(site) {
						return site.ID;
					}, this);

					return this.getLandings(sitesIds)
						.then(function(landings) {
							return sites.reduce(function(result, site, index) {
								result[site.ID] = {site: site, landings: landings[site.ID].result};
								return result;
							}, {});
						});
				}.bind(this))
				.then(function(result) {
					result[currentSiteId].landings.forEach(function(landing) {
						var active = parseInt(landing.ID) === parseInt(currentLandingId);
						var button = this.createLandingSidebarButton(landing, active);
						this.appendSidebarButton(button);

						if (active)
						{
							button.layout.click();
						}
					}, this);

					Object.keys(result).forEach(function(siteId) {
						if (parseInt(siteId) !== parseInt(currentSiteId))
						{
							var site = result[siteId].site;
							this.appendSidebarButton(
								this.createSiteSidebarButton(site)
							);

							result[siteId].landings.forEach(function(landing) {
								this.appendSidebarButton(
									this.createLandingSidebarButton(landing)
								);
							}, this)
						}
					}, this);
				}.bind(this));
		},


		createLandingSidebarButton: function(landing, active)
		{
			return (
				new SidebarButton(landing.ID, {
					text: landing.TITLE,
					onClick: this.onLandingChange.bind(this, landing),
					child: true,
					active: active
				})
			);
		},

		createSiteSidebarButton: function(site)
		{
			return (
				new SidebarButton(site.ID, {
					text: site.TITLE,
					child: false,
					active: false
				})
			);
		},


		onLandingChange: function(landing, event)
		{
			this.currentSelectedLanding = landing;
			this.sidebarButtons.forEach(function(button) {
				if (button.layout === event.currentTarget)
				{
					button.activate();
					return;
				}

				button.deactivate();
			});

			this.showPreviewLoader()
				.then(this.createIframeIfNeed())
				.then(this.loadPreviewSrc(this.buildLandingPreviewUrl(landing)))
				.then(this.hidePreviewLoader())

		},

		buildLandingPreviewUrl: function(landing)
		{
			var editorUrl = BX.Landing.Main.getInstance().options.params.sef_url.landing_view;
			editorUrl = editorUrl.replace("#site_show#", landing.SITE_ID);
			editorUrl = editorUrl.replace("#landing_edit#", landing.ID);

			return addQueryParams(editorUrl, {
				forceLoad: true,
				landing_mode: "edit"
			});
		},

		loadPreviewSrc: function(src)
		{
			return function()
			{
				return new Promise(function(resolve) {
					if (this.previewFrame.src !== src)
					{
						this.previewFrame.src = src;
						this.previewFrame.onload = function() {
							var contentDocument = this.previewFrame.contentDocument;
							BX.Landing.Utils.removePanels(contentDocument);
							[].slice.call(contentDocument.querySelectorAll(".block-wrapper"))
								.forEach(function(wrapper) {
									wrapper.classList.add("landing-ui-block-selectable-overlay");
									wrapper.addEventListener("click", function(event) {
										event.preventDefault();
										this.onBlockClick(parseInt(wrapper.id.replace("block", "")), event);
									}.bind(this));
								}, this);
							resolve(this.previewFrame);
						}.bind(this);
						return;
					}

					resolve(this.previewFrame);
				}.bind(this));
			}.bind(this)
		},

		showPreviewLoader: function()
		{
			if (!this.loader)
			{
				this.loader = new BX.Loader();
			}

			if (this.previewFrameWrapper)
			{
				void style(this.previewFrameWrapper, {
					opacity: 0
				});
			}

			return new Promise(function(resolve) {
				void this.loader.show(this.content);
				resolve();
			}.bind(this));
		},


		hidePreviewLoader: function()
		{
			return function()
			{
				void style(this.previewFrameWrapper, {
					opacity: null
				});

				return this.loader.hide();
			}.bind(this);
		},


		createIframeIfNeed: function()
		{
			return function()
			{
				new Promise(function(resolve) {
					if (!this.previewFrame)
					{
						this.previewFrame = create("iframe", {});
						this.previewFrameWrapper = create("div", {
							attrs: {style: "width: 100%; height: 100%; overflow: hidden;"}
						});
						this.previewFrameWrapper.appendChild(this.previewFrame);
						this.content.innerHTML = "";
						this.content.appendChild(this.previewFrameWrapper);
						this.showPreviewLoader();

						requestAnimationFrame(function() {
							var containerWidth = this.content.clientWidth - 40;

							void style(this.previewFrame, {
								"width": "1000px",
								"height": "calc((100vh - 113px) * (100 / "+((containerWidth/1000)*100)+"))",
								"transform": "scale("+(containerWidth/1000)+") translateZ(0)",
								"transform-origin": "top left",
								"border": "none"
							});
						}.bind(this));
					}

					resolve(this.previewFrame);
				}.bind(this));
			}.bind(this)
		},


		/**
		 * Handle block click event
		 * @param {Number|String} id
		 * @param event
		 */
		onBlockClick: function(id, event)
		{
			if (event.isTrusted)
			{
				this.getBlocks(this.currentSelectedLanding.ID)
					.then(function(blocks) {
						var currentBlock = blocks.find(function(block) {
							return block.id === id;
						});

						if (currentBlock)
						{
							this.onChange({
								type: TYPE_BLOCK,
								id: currentBlock.id,
								name: currentBlock.name,
								alias: currentBlock.alias
							});
						}
					}.bind(this));
			}
		},


		/**
		 * Handle landing click event
		 * @param {Number|String} id
		 * @param {String} name
		 */
		onLandingClick: function(id, name)
		{
			this.onChange({type: TYPE_PAGE, id: id, name: name});
		},


		/**
		 * Handle system click event
		 * @param {Number|String} id
		 * @param {String} name
		 */
		onSystemClick: function(id, name)
		{
			this.onChange({type: TYPE_SYSTEM, id: "_" + id, name: name});
		},


		/**
		 * Handle site click event
		 * @param {?boolean} enableAreas
		 * @param {object} event
		 * @param {Number|String} siteId
		 */
		onSiteClick: function(siteId, enableAreas, event)
		{
			this.sidebarButtons.forEach(function(button) {
				if (button.layout === event.currentTarget)
				{
					button.activate();
				}
				else
				{
					button.deactivate();
				}
			});

			this.content.innerHTML = "";
			this.showLoader();

			this.getLandings(siteId).then(function(landings) {
				if (isPlainObject(landings))
				{
					landings = Object.keys(landings).reduce(function(acc, key) {
						if (isPlainObject(landings[key]) && isArray(landings[key].result))
						{
							acc = acc.concat(landings[key].result);
						}

						return acc;
					}, []);
				}

				landings.forEach(function(landing) {
					if (!landing.IS_AREA || (landing.IS_AREA && enableAreas))
					{
						this.appendCard(this.createLandingPreview(landing));
					}
				}, this);
				this.loader.hide();
			}.bind(this));
		},


		/**
		 * Creates landing preview
		 * @param {{
		 * 		ID: Number|String,
		 * 		TITLE: String,
		 * 		DESCRIPTION: String,
		 * 		PREVIEW: String
		 *  }} options
		 * @return {BX.Landing.UI.Card.LandingPreviewCard}
		 */
		createLandingPreview: function(options)
		{
			return new BX.Landing.UI.Card.LandingPreviewCard({
				title: options.TITLE,
				description: options.DESCRIPTION,
				preview: options.PREVIEW,
				onClick: this.onLandingClick.bind(this, options.ID, options.TITLE)
			});
		},


		/**
		 * Creates block preview
		 * @param {{id: Number|String, name: String}} options
		 * @return {BX.Landing.UI.Card.BlockHTMLPreview}
		 */
		createBlockPreview: function(options)
		{
			return new BX.Landing.UI.Card.BlockHTMLPreview({
				content: options.id,
				onClick: this.onBlockClick.bind(this, options.id, options.name, options.alias)
			});
		},


		/**
		 * @param {object} options
		 * @return {Promise<Object, Object>}
		 */
		getSites: function(options)
		{
			if (this.cache.has(options))
			{
				return Promise.resolve(this.cache.get(options));
			}

			return BX.Landing.Backend.getInstance()
				.action("Site::getList", {
					params: {
						order: {ID: "DESC"},
						filter: options.filter
					}
				})
				.then(function(response) {
					this.cache.add(options, response);
					return response;
				}.bind(this));
		},


		/**
		 * Gets landings list of site
		 * @param {number|string|Array<string|number>} [siteId]
		 * @param {object} options
		 * @returns {Promise.<Object>}
		 */
		getLandings: function(siteId, options)
		{
			siteId = isNumber(siteId) || isString(siteId) || isArray(siteId) ? siteId : options.siteId;
			var cacheKey = isArray(siteId) ? siteId.join(',') : siteId;

			if (this.cache.has("getLandings" + cacheKey))
			{
				return Promise.resolve(this.cache.get("getLandings" + cacheKey));
			}

			if (isArray(siteId))
			{
				var batchData = siteId.reduce(function(acc, item) {
					acc[item] = {
						action: "Landing::getList",
						data: {
							params: {
								filter: {SITE_ID: item},
								order: {ID: "DESC"},
								get_preview: true,
								check_area: 1
							}
						}
					};

					return acc;
				}, {});

				return BX.Landing.Backend.getInstance()
					.batch("Landing::getList", batchData)
					.then(function(response) {
						this.cache.add("getLandings" + cacheKey, response);
						return response;
					}.bind(this));
			}

			return BX.Landing.Backend.getInstance()
				.action("Landing::getList", {
					params: {
						filter: {SITE_ID: siteId},
						order: {ID: "DESC"},
						get_preview: true,
						check_area: 1
					}
				})
				.then(function(response) {
					this.cache.add("getLandings" + cacheKey, response);
					return response;
				}.bind(this))
		},


		/**
		 * Gets landing by id
		 * @param {Number|String} landingId
		 * @param {object} options
		 * @return {Promise<Object, Object>}
		 */
		getLanding: function(landingId, options)
		{
			if (this.cache.has(["getLanding" + landingId, options]))
			{
				return Promise.resolve(this.cache.get(["getLanding" + landingId, options]));
			}

			return BX.Landing.Backend.getInstance()
				.action("Landing::getList", {params: {
					filter: {ID: landingId},
					get_preview: true
				}})
				.then(function(response) {
					this.cache.add(["getLanding" + landingId, options], response);
					return response;
				}.bind(this))
		},


		/**
		 * Gets landing blocks by landing id
		 * @param {Number|String} [landingId]
		 * @param {object} options
		 * @return {Promise<Object, Object>}
		 */
		getBlocks: function(landingId, options)
		{
			landingId = isNumber(landingId) || isString(landingId) ? landingId : options.landingId;

			if (this.cache.has(["getBlocks" + landingId, options]))
			{
				var cacheResult = this.cache.get(["getBlocks" + landingId, options]);

				if (cacheResult &&
					typeof cacheResult === "object" &&
					typeof cacheResult.then === "function")
				{
					return cacheResult;
				}

				return Promise.resolve(cacheResult);
			}

			var resultPromise = BX.Landing.Backend.getInstance()
				.action("Block::getList", {
					lid: landingId,
					params: {
						get_content: true,
						edit_mode: true
					}
				})
				.then(function(response) {
					this.cache.set(["getBlocks" + landingId, options], response);
					return response;
				}.bind(this));

			this.cache.set(["getBlocks" + landingId, options], resultPromise);

			return resultPromise;
		},

		getBlock: function(id)
		{
			var cacheKey = "getBlocks" + id;

			if (this.cache.has(cacheKey))
			{
				var cacheResult = this.cache.get(cacheKey);

				if (cacheResult &&
					typeof cacheResult === "object" &&
					typeof cacheResult.then === "function")
				{
					return cacheResult;
				}

				return Promise.resolve(cacheResult);
			}

			var resultPromise = BX.Landing.Backend.getInstance()
				.action("Block::getById", {
					block: id,
					params: {
						edit_mode: true
					}
				})
				.then(function(response) {
					this.cache.set(cacheKey, response);
					return response;
				}.bind(this));

			this.cache.set(cacheKey, resultPromise);

			return resultPromise;
		},


		/**
		 * Gets all entries
		 * @returns {Promise<object[]>}
		 */
		getEntries: function()
		{
			return new Promise(function(resolve) {
				if (this.isNeedLoad)
				{
					this.getLandings().then(function(landings) {
						var all = Promise.all(landings.map(function(landing) {
							return this.getBlocks(landing.ID);
						}, this));

						all.then(function(result) {
							var value = landings.map(function(item, index) {
								var resItem = result[index];

								if (isPlainObject(resItem))
								{
									var keys = Object.keys(resItem);
									resItem = keys.map(function(block) {
										return result[index][block];
									});
								}

								item.blocks = resItem;
								return item;
							});

							this.lastEntries = value;
							this.isNeedLoad = false;

							resolve(value);
						}.bind(this));
					}.bind(this));
				}
				else
				{
					resolve(this.lastEntries);
				}
			}.bind(this));
		},

		onChange: function(value)
		{
			this.promiseResolve(value);
			this.hide();
		},

		hide: function()
		{
			this.previewFrame = null;
			return BX.Landing.UI.Panel.Content.prototype.hide.call(this);
		}
	}
})();