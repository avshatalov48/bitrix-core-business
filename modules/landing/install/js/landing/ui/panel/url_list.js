;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");

	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var append = BX.Landing.Utils.append;
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;
	var setTextContent = BX.Landing.Utils.setTextContent;
	var rect = BX.Landing.Utils.rect;
	var style = BX.Landing.Utils.style;
	var join = BX.Landing.Utils.join;
	var isNumber = BX.Landing.Utils.isNumber;
	var isString = BX.Landing.Utils.isString;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var Cache = BX.Landing.Cache;

	var TYPE_PAGE = "landing";
	var TYPE_BLOCK = "block";
	var TYPE_SYSTEM = "system";

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
		this.loader = new BX.Landing.UI.Card.Loader({id: "url_list_loader"});
		this.appendCard(this.loader);

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
			this.appendCard(this.loader);
			this.loader.show();
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
				addClass(this.layout, "landing-ui-panel-url-list-blocks");
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

			void this.getSites(options).then(function(sites) {
				this.appendSidebarButton(
					new BX.Landing.UI.Button.SidebarButton("current_site", {
						text: BX.message("LANDING_LINKS_PANEL_CURRENT_SITE")
					})
				);

				sites.forEach(function(site) {
					// noinspection EqualityComparisonWithCoercionJS
					if (parseInt(site.ID) == currentSiteId)
					{
						this.appendSidebarButton(
							new BX.Landing.UI.Button.SidebarButton(site.ID, {
								text: site.TITLE,
								onClick: this.onSiteClick.bind(this, site.ID),
								child: true,
								active: true
							})
						);
					}
				}, this);

				this.getLandings(currentSiteId).then(function(landings) {
					landings.forEach(function(landing) {
						this.appendCard(
							new BX.Landing.UI.Card.LandingPreviewCard({
								title: landing.TITLE,
								description: landing.DESCRIPTION,
								preview: landing.PREVIEW,
								onClick: this.onLandingClick.bind(this, landing.ID, landing.TITLE)
							})
						)
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
						new BX.Landing.UI.Button.SidebarButton("my_sites", {
							text: BX.message("LANDING_LINKS_PANEL_MY_SITES")
						})
					);

					sites.forEach(function(site) {
						this.appendSidebarButton(
							new BX.Landing.UI.Button.SidebarButton(site.ID, {
								text: site.TITLE,
								onClick: this.onSiteClick.bind(this, site.ID),
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
		 * Shows blocks list
		 * @param {object} options
		 */
		showBlocks: function(options)
		{
			var currentLandingId = options.landingId;

			void this.getBlocks(currentLandingId, options).then(function(blocks) {
				blocks.forEach(function(block) {
					var preview = this.createBlockPreview(block);
					this.appendCard(preview);

					requestAnimationFrame(function() {
						var blockRect = rect(preview.block);
						var layoutRect = rect(preview.layout);

						var scale = Math.min(
							layoutRect.width / blockRect.width,
							layoutRect.height / blockRect.height
						);

						void style(preview.block, {
							transform: join("translate(-50%, -50%) ", "scale(", scale,")")
						});
					});
				}, this);
				this.loader.hide();
			}.bind(this));
		},


		/**
		 * Handle block click event
		 * @param {Number|String} id
		 * @param {String} name
		 */
		onBlockClick: function(id, name)
		{
			this.onChange({type: TYPE_BLOCK, id: id, name: name});
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
		 * @param {object} options
		 * @param {Number|String} siteId
		 */
		onSiteClick: function(siteId, options)
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

			this.getLandings(siteId, options).then(function(landings) {
				landings.forEach(function(landing) {
					this.appendCard(this.createLandingPreview(landing))
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
				onClick: this.onBlockClick.bind(this, options.id, options.name)
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
		 * @param {number|string} [siteId]
		 * @param {object} options
		 * @returns {Promise.<Object>}
		 */
		getLandings: function(siteId, options)
		{
			siteId = isNumber(siteId) || isString(siteId) ? siteId : options.siteId;

			if (this.cache.has("getLandings" + siteId))
			{
				return Promise.resolve(this.cache.get("getLandings" + siteId));
			}

			return BX.Landing.Backend.getInstance()
				.action("Landing::getList", {
					params: {
						filter: {SITE_ID: siteId},
						order: {ID: "DESC"},
						get_preview: true
					}
				})
				.then(function(response) {
					this.cache.add("getLandings" + siteId, response);
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
			landingId = isNumber(landingId) || isString(landingId) ? landingId : options.id;

			if (this.cache.has(["getBlocks" + landingId, options]))
			{
				return Promise.resolve(this.cache.get(["getBlocks" + landingId, options]));
			}

			return BX.Landing.Backend.getInstance()
				.action("Block::getList", {
					lid: landingId,
					params: {
						get_content: true,
						edit_mode: true
					}
				})
				.then(function(response) {
					this.cache.add(["getBlocks" + landingId, options], response);
					return response;
				}.bind(this))
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
		}
	}
})();