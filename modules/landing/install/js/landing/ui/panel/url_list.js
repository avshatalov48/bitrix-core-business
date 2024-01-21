;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");

	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var append = BX.Landing.Utils.append;
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;
	var setTextContent = BX.Landing.Utils.setTextContent;
	var create = BX.Landing.Utils.create;
	var style = BX.Landing.Utils.style;
	var isNumber = BX.Landing.Utils.isNumber;
	var isString = BX.Landing.Utils.isString;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isArray = BX.Landing.Utils.isArray;
	var addQueryParams = BX.Landing.Utils.addQueryParams;

	var TYPE_PAGE = "landing";
	var TYPE_BLOCK = "block";
	var TYPE_SYSTEM = "system";
	var TYPE_CRM_FORM = "crmFormPopup";
	var TYPE_CRM_PHONE = "crmPhone";
	var TYPE_USER = "user";

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
		if (BX.Landing.Main.isEditorMode())
		{
			append(this.layout, window.parent.document.body);
		}
		else
		{
			this.overlay.parentNode.removeChild(this.overlay);
			document.body.appendChild(this.overlay);
			append(this.layout, document.body);
			this.layout.style.marginTop = 0;
			this.overlay.style.marginTop = 0;
		}

		// Make loader
		this.loader = new BX.Loader({target: this.content});

		this.promiseResolve = (function() {});
		this.layout.hidden = true;
		this.isNeedLoad = true;
		this.cache = new BX.Cache.MemoryCache();
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
			this.showOptions = options;
			BX.Landing.UI.Panel.Content.prototype.show.call(this);

			this.clear();
			this.showLoader();

			const env = BX.Landing.Env.getInstance();
			if (env.getOptions().specialType === 'crm_forms')
			{
				if (!BX.Type.isPlainObject(options.filter))
				{
					options.filter = {};
				}
				options.filter.SPECIAL = 'Y';
			}

			if (view === TYPE_PAGE)
			{
				removeClass(this.layout, "landing-ui-panel-url-list-blocks");
				setTextContent(this.title, options.panelTitle || BX.Landing.Loc.getMessage("LANDING_LINKS_LANDINGS_TITLE"));
				this.showSites(options);
			}
			else if (view === TYPE_BLOCK)
			{
				setTextContent(this.title, options.panelTitle || BX.Landing.Loc.getMessage("LANDING_LINKS_BLOCKS_TITLE"));
				this.showBlocks(options);
			}
			else if (view === TYPE_CRM_FORM)
			{
				setTextContent(this.title, options.panelTitle || BX.Landing.Loc.getMessage("LANDING_LINKS_CRM_FORMS_TITLE"));
				this.showForms(options);
			}
			else if (view === TYPE_CRM_PHONE)
			{
				setTextContent(this.title, options.panelTitle || BX.Landing.Loc.getMessage("LANDING_LINKS_CRM_PHONES_TITLE"));
				this.showPhones(options);
			}
			else if (view === TYPE_USER)
			{
				setTextContent(this.title, options.panelTitle || BX.Landing.Loc.getMessage("LANDING_LINKS_CRM_PHONES_USERS"));
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
			let currentSiteId = options.siteId;

			void style(this.layout, {
				width: null
			});

			if (!BX.Type.isPlainObject(options.filter))
			{
				options.filter = {};
			}

			var env = BX.Landing.Env.getInstance();
			if (BX.Type.isNil(options.filter.ID) && env.getType() === 'GROUP')
			{
				options.filter.ID =	env.getSiteId();
			}

			if (options.filter.ID === -1)
			{
				delete options.filter.ID;
			}

			if (!BX.Type.isString(options.filter.SPECIAL))
			{
				options.filter.SPECIAL = 'N';
			}

			void BX.Landing.Backend.getInstance()
				.getSites(options).then(sites => {
					sites.forEach(site => {
						// noinspection EqualityComparisonWithCoercionJS
						if (parseInt(site.ID) == currentSiteId)
						{
							this.appendSidebarButton(this.createCurrentSiteButton());

							this.currentSiteButton = new SidebarButton(site.ID, {
								text: site.TITLE,
								onClick: !options.currentSiteOnly ? this.onSiteClick.bind(this, site.ID, options.enableAreas) : null,
								child: true,
								active: true,
							});
							this.appendSidebarButton(this.currentSiteButton);
						}
					});

					if (!options.currentSiteOnly)
					{
						this.appendSidebarButton(
							new SidebarButton("my_sites", {
								text: BX.Landing.Loc.getMessage("LANDING_LINKS_PANEL_MY_SITES")
							})
						);

						sites.forEach(site => {
							const button = new SidebarButton(site.ID, {
								text: site.TITLE,
								onClick: this.onSiteClick.bind(this, site.ID, options.enableAreas),
								child: true,
								active: !this.currentSiteButton,
							});
							// get first site if current not in list
							if (!this.currentSiteButton)
							{
								this.currentSiteButton = button;
								currentSiteId = site.ID;
							}

							this.appendSidebarButton(button);
						});
					}

					BX.Landing.Backend.getInstance()
						.getLandings({siteId: currentSiteId}, options.filterLanding)
						.then(landings => {
							const fakeEvent = {currentTarget: this.currentSiteButton.layout};
							const siteClick = this.onSiteClick.bind(this, currentSiteId, options.enableAreas, fakeEvent);
							if (!options.disableAddPage)
							{
								this.appendCard(
									new BX.Landing.UI.Card.AddPageCard({
										siteId: currentSiteId,
										onSave: this.addPageSave.bind(this, siteClick, currentSiteId)
									})
								);
							}
							landings.forEach(landing => {
								if (!landing.IS_AREA || (landing.IS_AREA && options.enableAreas))
								{
									this.appendCard(
										new BX.Landing.UI.Card.LandingPreviewCard({
											title: landing.TITLE,
											description: landing.DESCRIPTION,
											preview: landing.PREVIEW,
											onClick: this.onLandingClick.bind(this, landing.ID, landing.TITLE)
										})
									);
								}
							});

							this.loader.hide();
						});
				});
		},

		/**
		 * @private
		 * @return {BX.Landing.UI.Button.SidebarButton}
		 */
		createCurrentSiteButton: function()
		{
			return new SidebarButton("current_site", {
				text: BX.Landing.Loc.getMessage("LANDING_LINKS_PANEL_CURRENT_SITE")
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

			if (!BX.Type.isPlainObject(options.filter))
			{
				options.filter = {
					SPECIAL: 'N',
				};
			}
			else if (!BX.Type.isString(options.filter.SPECIAL))
			{
				options.filter.SPECIAL = 'N';
			}

			BX.Landing.Backend.getInstance()
				.getSites(options)
				.then(function(sites) {
					const sitesIds = sites.map(function(site) {
						return site.ID;
					}, this);

					return BX.Landing.Backend.getInstance()
						.getLandings({siteId: sitesIds})
						.then(function(landings) {
							return sites.reduce(function(result, site, index) {
								const currentLandings = landings.filter(function(landing) {
									return site.ID === landing.SITE_ID && !landing.IS_AREA;
								});

								result[site.ID] = {site: site, landings: currentLandings};
								return result;
							}, {});
						})
				}.bind(this))
				.then(function(result) {
					let activeButton = null;
					if (result[currentSiteId])
					{
						this.appendSidebarButton(
							this.createCurrentSiteButton()
						);

						result[currentSiteId].landings.forEach(function (landing)
						{
							const isActive = parseInt(landing.ID) === parseInt(currentLandingId);

							if (!options.currentPageOnly || isActive)
							{
								const button = this.createLandingSidebarButton(landing, isActive);
								this.appendSidebarButton(button);
								if (isActive)
								{
									activeButton = button;
								}
							}

						}, this);
					}

					if (!options.currentPageOnly)
					{
						Object.keys(result).forEach(function(siteId) {
							if (parseInt(siteId) !== parseInt(currentSiteId))
							{
								var site = result[siteId].site;
								this.appendSidebarButton(
									this.createSiteSidebarButton(site)
								);

								result[siteId].landings.forEach(function(landing) {
									const button = this.createLandingSidebarButton(landing);
									this.appendSidebarButton(button);
									if (!activeButton)
									{
										activeButton = button;
									}
								}, this)
							}
						}, this);
					}

					if (activeButton)
					{
						activeButton.layout.click();
					}
				}.bind(this));
		},

		showForms: function()
		{
			void style(this.layout, {
				width: "500px"
			});

			BX.Landing.Backend
				.getInstance()
				.action('Form::getList')
				.then(function(result) {
					result.forEach(function(form) {
						var cardParams = {
							title: form.NAME,
							className: 'landing-ui-card-form-preview',
							onClick: this.onFormChange.bind(this, form)
						};
						if(form.IS_CALLBACK_FORM === 'Y')
						{
							cardParams.className += ' landing-ui-card-form-preview--callback';
							// cardParams.title = BX.message();
							// cardParams.attrs = {
							// 	title: 'callback form'
							// };
						}
						this.appendCard(new BX.Landing.UI.Card.BaseCard(cardParams));
					}.bind(this));

					this.loader.hide();
				}.bind(this));
		},

		onFormChange: function(form)
		{
			this.hide();
			this.promiseResolve({
				id: form.ID,
				type: 'crmFormPopup',
				name: form.NAME
			});
		},

		showPhones: function()
		{
			void style(this.layout, {
				width: "500px"
			});

			BX.Landing.Env
				.getInstance()
				.getOptions()
				.references
				.forEach(function(item) {
				this.appendCard(
					new BX.Landing.UI.Card.BaseCard({
						title: item.text,
						className: 'landing-ui-card-form-preview',
						onClick: this.onPhoneChange.bind(this, item)
					})
				);
			}.bind(this));

			this.loader.hide();
		},

		onPhoneChange: function(phone)
		{
			this.hide();
			this.promiseResolve({
				id: phone.value,
				type: 'crmPhone',
				name: phone.text
			});
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
							[].slice.call(contentDocument.querySelectorAll(".landing-main .block-wrapper"))
								.forEach(function(wrapper) {
									wrapper.setAttribute("data-selectable", 1);
									wrapper.classList.add("landing-ui-block-selectable-overlay");
									wrapper.addEventListener("click", function(event) {
										event.preventDefault();
										var mainNode = wrapper.closest('[data-landing]');
										var landingId = BX.Dom.attr(mainNode, 'data-landing');
										this.onBlockClick(parseInt(wrapper.id.replace("block", "")), event, landingId);
									}.bind(this));
									removeClass(
										wrapper.firstElementChild,
										['l-d-lg-none', 'l-d-md-none', 'l-d-xs-none']
									);
								}, this);
							[].slice.call(contentDocument.querySelectorAll(".block-wrapper"))
								.forEach(function(wrapper) {
									if (!wrapper.getAttribute("data-selectable"))
									{
										wrapper.style.display = "none";
									}
								}, this);
							[].slice.call(contentDocument.querySelectorAll(".landing-empty"))
								.forEach(function(wrapper) {
									wrapper.style.display = "none";
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
						const containerWidth = this.content.clientWidth - 40;
						const styleStr = 'width: 1000px;'
							+ `height: calc((100vh - 113px) * (100 / ${(containerWidth / 1000) * 100}));`
							+ `transform: scale(${containerWidth / 1000}) translateZ(0);`
							+ 'transform-origin: top left;'
							+ 'border: none;';
						this.previewFrame = create('iframe', {
							attrs: { style: styleStr },
						});
						this.previewFrameWrapper = create("div", {
							attrs: {style: "width: 100%; height: 100%; overflow: hidden;"}
						});
						this.previewFrameWrapper.appendChild(this.previewFrame);
						this.content.innerHTML = "";
						this.content.appendChild(this.previewFrameWrapper);
						this.showPreviewLoader();
					}

					resolve(this.previewFrame);
				}.bind(this));
			}.bind(this)
		},


		/**
		 * Handle block click event
		 * @param {Number|String} id
		 * @param event
		 * @param {number} landingId
		 */
		onBlockClick: function(id, event, landingId)
		{
			if (event.isTrusted)
			{
				void BX.Landing.Backend.getInstance()
					.getBlocks({landingId: landingId})
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
				if (
					button.layout === event.currentTarget
					|| (
						!!event.target
						&& button.layout === event.target.closest('.landing-ui-button')
					)
				)
				{
					this.currentSiteButton = button;
					button.activate();
				}
				else
				{
					button.deactivate();
				}
			}, this);

			this.content.innerHTML = "";
			this.showLoader();

			BX.Landing.Backend.getInstance()
				.getLandings({siteId: siteId})
				.then(function(landings) {
					var siteClick = this.onSiteClick.bind(this, siteId, enableAreas, event);
					this.appendCard(
						new BX.Landing.UI.Card.AddPageCard({
							siteId: siteId,
							onSave: this.addPageSave.bind(this, siteClick, siteId)
						})
					);
					landings.forEach(function(landing) {
						if (!landing.IS_AREA || (landing.IS_AREA && enableAreas))
						{
							this.appendCard(this.createLandingPreview(landing));
						}
					}, this);
					this.loader.hide();
				}.bind(this));
		},

		addPageSave: function(reloadFn, siteId)
		{
			this.cache = new BX.Cache.MemoryCache();
			var backend = BX.Landing.Backend.getInstance();
			backend.cache.delete('landings+['+siteId+']');
			backend.cache.delete('landings+["'+siteId+'"]');
			backend.cache.delete('landing+'+siteId);
			reloadFn();
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
		 * Gets landing by id
		 * @param {Number|String} landingId
		 * @param {object} options
		 */
		getLanding: function(landingId, options)
		{
			var key = JSON.stringify(["getLanding" + landingId, options]);

			return this.cache.remember(key, function() {
				return BX.Landing.Backend.getInstance()
					.action("Landing::getList", {params: {
							filter: {ID: landingId},
							get_preview: true
						}})
					.then(function(response) {
						return response;
					}.bind(this))
			}.bind(this));
		},

		getBlock: function(id)
		{
			var cacheKey = "getBlocks" + id;

			return this.cache.remember(cacheKey, function() {
				return BX.Landing.Backend.getInstance()
					.action("Block::getById", {
						block: id,
						params: {
							edit_mode: true
						}
					})
					.then(function(response) {
						return response;
					}.bind(this));
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