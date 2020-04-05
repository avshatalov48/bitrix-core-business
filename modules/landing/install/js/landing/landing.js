(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var onAnimationEnd = BX.Landing.Utils.onAnimationEnd;
	var htmlToElement = BX.Landing.Utils.htmlToElement;
	var deepFreeze = BX.Landing.Utils.deepFreeze;
	var fireCustomEvent = BX.Landing.Utils.fireCustomEvent;

	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isEmpty = BX.Landing.Utils.isEmpty;
	var isBoolean = BX.Landing.Utils.isBoolean;
	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var hasClass = BX.Landing.Utils.hasClass;
	var append = BX.Landing.Utils.append;
	var prepend = BX.Landing.Utils.prepend;
	var insertAfter = BX.Landing.Utils.insertAfter;
	var remove = BX.Landing.Utils.remove;
	var slice = BX.Landing.Utils.slice;
	var data = BX.Landing.Utils.data;
	var arrayUnique = BX.Landing.Utils.arrayUnique;
	var create = BX.Landing.Utils.create;
	var delay = BX.Landing.Utils.delay;

	var PlusButton = BX.Landing.UI.Button.Plus;
	var ContentPanel = BX.Landing.UI.Panel.Content;

	var LANG_RU = "ru";
	var LANG_BY = "by";
	var LANG_KZ = "kz";
	var LANG_LA = "la";
	var LANG_DE = "de";
	var LANG_BR = "br";
	var LANG_UA = "ua";


	/**
	 * Checks that element contains block
	 * @param {HTMLElement} element
	 * @return {boolean}
	 */
	function hasBlock(element)
	{
		return !!element && !!element.querySelector(".block-wrapper");
	}


	/**
	 * Checks that element contains "Add new Block" button
	 * @param {HTMLElement} element
	 * @return {boolean}
	 */
	function hasCreateButton(element)
	{
		return !!element && !!element.querySelector("button[data-id=\"insert_first_block\"]");
	}


	/**
	 * Implements interface for works with Landing
	 *
	 * Implements singleton design patter.
	 * !important Don't use as function constructor.
	 * @see BX.Landing.Main.getInstance()
	 *
	 * @param {int} id - Landing id
	 * @param {object} options
	 *
	 * @constructor
	 */
	BX.Landing.Main = function(id, options)
	{
		this.id = id;
		this.options = deepFreeze(options || {});
		this.blocksPanel = null;
		this.currentBlock = null;
		this.loadedDeps = {};

		this.onSliderFormLoaded = this.onSliderFormLoaded.bind(this);
		this.onBlockDelete = this.onBlockDelete.bind(this);

		BX.addCustomEvent("Landing.Block:onAfterDelete", this.onBlockDelete);

		this.adjustEmptyAreas();

		if (this.options.blocks)
		{
			if (!this.blocksPanel)
			{
				this.blocksPanel = this.createBlocksPanel();
				this.onBlocksListCategoryChange("last");
				this.blocksPanel.layout.hidden = true;
				append(this.blocksPanel.layout, document.body);
			}

			this.blocksPanel.content.hidden = false;
		}

		BX.Landing.UI.Panel.StatusPanel.setLastModified(options.lastModified);
		BX.Landing.UI.Panel.StatusPanel.getInstance().show();
	};

	/** @type {string} */
	BX.Landing.Main.TYPE_PAGE = "PAGE";

	/** @type {string} */
	BX.Landing.Main.TYPE_STORE = "STORE";


	/**
	 * Gets current landing mode
	 * @returns {string} - edit|design|view
	 */
	BX.Landing.getMode = function()
	{
		return "edit";
	};


	/**
	 * Current instance of class.
	 * @static
	 * @type {?BX.Landing.Main}
	 */
	BX.Landing.Main.instance = null;


	/**
	 * Create current instance of class.
	 *
	 * @param {number} id
	 * @param {Object} options - Option object.
	 */
	BX.Landing.Main.createInstance = function(id, options)
	{
		top.BX.Landing.Main.instance = new BX.Landing.Main(id, options);
	};


	/**
	 * Get current instance of class.
	 * @returns {?BX.Landing.Main}
	 * @throws {Error}
	 */
	BX.Landing.Main.getInstance = function()
	{
		if (top.BX.Landing.Main.instance)
		{
			return top.BX.Landing.Main.instance;
		}

		top.BX.Landing.Main.instance = new BX.Landing.Main(-1, {});

		return top.BX.Landing.Main.instance;
	};


	BX.Landing.Main.prototype = {
		/**
		 * Hides blocks list panel
		 * @return {Promise}
		 */
		hideBlocksPanel: function()
		{
			if (this.blocksPanel)
			{
				return this.blocksPanel.hide();
			}

			return Promise.resolve();
		},


		/**
		 * Gets layout areas
		 * @return {HTMLElement[]}
		 */
		getLayoutAreas: function()
		{
			if (!this.layoutAreas)
			{
				this.layoutAreas = [].concat(
					slice(document.body.querySelectorAll(".landing-header")),
					slice(document.body.querySelectorAll(".landing-sidebar")),
					slice(document.body.querySelectorAll(".landing-main")),
					slice(document.body.querySelectorAll(".landing-footer"))
				);
			}

			return this.layoutAreas;
		},

		/**
		 * Creates insert block button
		 * @param {HTMLElement} area
		 * @return {BX.Landing.UI.Button.Plus}
		 */
		createInsertBlockButton: function(area)
		{
			var button = new PlusButton("insert_first_block", {
				text: BX.message("ACTION_BUTTON_CREATE")
			});

			button.on("click", this.showBlocksPanel.bind(this, null, area, button));
			button.on("mouseover", this.onCreateButtonMouseover.bind(this, area, button));
			button.on("mouseout", this.onCreateButtonMouseout.bind(this, area, button));

			return button;
		},

		onCreateButtonMouseover: function(area, button)
		{
			if (hasClass(area, "landing-header") ||
				hasClass(area, "landing-footer"))
			{
				var areas = this.getLayoutAreas();

				if (areas.length > 1)
				{
					switch (true)
					{
						case hasClass(area, "landing-main"):
							button.setText([
								BX.message("ACTION_BUTTON_CREATE"),
								BX.message("LANDING_ADD_BLOCK_TO_MAIN")
							].join(" "));
							break;
						case hasClass(area, "landing-header"):
							button.setText([
								BX.message("ACTION_BUTTON_CREATE"),
								BX.message("LANDING_ADD_BLOCK_TO_HEADER")
							].join(" "));
							break;
						case hasClass(area, "landing-sidebar"):
							button.setText([
								BX.message("ACTION_BUTTON_CREATE"),
								BX.message("LANDING_ADD_BLOCK_TO_SIDEBAR")
							].join(" "));
							break;
						case hasClass(area, "landing-footer"):
							button.setText([
								BX.message("ACTION_BUTTON_CREATE"),
								BX.message("LANDING_ADD_BLOCK_TO_FOOTER")
							].join(" "));
							break;
					}

					clearTimeout(this.fadeTimeout);

					this.fadeTimeout = setTimeout(function() {
						addClass(area, "landing-area-highlight");
						areas.forEach(function(currentArea) {
							if (currentArea !== area)
							{
								addClass(currentArea, "landing-area-fade");
							}
						}, this);
					}.bind(this), 400);
				}
			}
		},

		onCreateButtonMouseout: function(area, button)
		{
			clearTimeout(this.fadeTimeout);

			if (hasClass(area, "landing-header") ||
				hasClass(area, "landing-footer"))
			{
				var areas = this.getLayoutAreas();

				if (areas.length > 1)
				{
					button.setText(BX.message("ACTION_BUTTON_CREATE"));
					areas.forEach(function(currentArea) {
						removeClass(currentArea, "landing-area-highlight");
						removeClass(currentArea, "landing-area-fade");
					}, this);
				}
			}
		},

		/**
		 * Initializes empty layout area
		 * @param {HTMLElement} area
		 */
		initEmptyArea: function(area)
		{
			if (area)
			{
				area.innerHTML = "";
				append(this.createInsertBlockButton(area).layout, area);
				addClass(area, "landing-empty");
			}
		},


		/**
		 * Destroy empty area
		 * @param {HTMLElement} area
		 */
		destroyEmptyArea: function(area)
		{
			if (area)
			{
				var button = area.querySelector("button[data-id=\"insert_first_block\"]");

				if (button)
				{
					remove(button);
				}

				removeClass(area, "landing-empty");
			}
		},


		/**
		 * Adjusts areas
		 */
		adjustEmptyAreas: function()
		{
			this.getLayoutAreas()
				.filter(function(area) {
					return hasBlock(area) && hasCreateButton(area);
				})
				.forEach(this.destroyEmptyArea, this);

			this.getLayoutAreas()
				.filter(function(area) {
					return !hasBlock(area) && !hasCreateButton(area);
				})
				.forEach(this.initEmptyArea, this);

			var main = document.body.querySelector("main.landing-edit-mode");
			var isAllEmpty = !this.getLayoutAreas().some(hasBlock);

			if (main)
			{
				if (isAllEmpty)
				{
					addClass(main, "landing-empty");
					return;
				}

				removeClass(main, "landing-empty");
			}
		},


		/**
		 * Enables landing controls
		 */
		enableControls: function()
		{
			removeClass(document.body, "landing-ui-hide-controls");
		},


		/**
		 * Disables landing controls
		 */
		disableControls: function()
		{
			addClass(document.body, "landing-ui-hide-controls");
		},


		/**
		 * Checks that landing controls is enabled
		 * @return {boolean}
		 */
		isControlsEnabled: function()
		{
			return !hasClass(document.body, "landing-ui-hide-controls");
		},


		/**
		 * Appends block
		 * @param {addBlockResponse} data
		 * @param {boolean} [withoutAnimation]
		 * @returns {HTMLElement}
		 */
		appendBlock: function(data, withoutAnimation)
		{
			var block = htmlToElement(data.content);
			block.id = "block" + data.id;

			if (!withoutAnimation)
			{
				addClass(block, "landing-ui-show");
				onAnimationEnd(block, "showBlock").then(function() {
					removeClass(block, "landing-ui-show");
				});
			}

			this.insertToBlocksFlow(block);

			return block;
		},


		/**
		 * Shows blocks list panel
		 * @param {BX.Landing.Block} block
		 * @param {HTMLElement} [area]
		 * @param [button]
		 */
		showBlocksPanel: function(block, area, button)
		{
			this.currentBlock = block;
			this.currentArea = area;
			this.blocksPanel.show();

			if (!!area && !!button)
			{
				this.onCreateButtonMouseout(area, button);
			}
		},


		/**
		 * Creates blocks list panel
		 * @returns {BX.Landing.UI.Panel.Content}
		 */
		createBlocksPanel: function()
		{
			var blocks = this.options.blocks;
			var categories = Object.keys(blocks);

			var panel = new ContentPanel("blocks_panel", {
				title: BX.message("LANDING_CONTENT_BLOCKS_TITLE"),
				className: "landing-ui-panel-block-list",
				scrollAnimation: true
			});

			categories.forEach(function(categoryId) {
				var hasItems = !isEmpty(blocks[categoryId].items);
				var isPopular = categoryId === "popular";
				var isSeparator = blocks[categoryId].separator;

				if ((hasItems && !isPopular) || isSeparator)
				{
					panel.appendSidebarButton(
						this.createBlockPanelSidebarButton(categoryId, blocks[categoryId])
					);
				}
			}, this);

			panel.appendSidebarButton(
				new BX.Landing.UI.Button.SidebarButton("feedback_button", {
					className: "landing-ui-button-sidebar-feedback",
					text: BX.message("LANDING_BLOCKS_LIST_FEEDBACK_BUTTON"),
					onClick: this.showFeedbackForm.bind(this)
				})
			);

			return panel;
		},


		/**
		 * Shows feedback form
		 * @param data
		 */
		showSliderFeedbackForm: function(data)
		{
			if (!this.sliderFeedbackInited)
			{
				this.sliderFeedbackInited = true;
				this.sliderFeedback = new ContentPanel("slider_feedback", {
					title: BX.message("LANDING_PANEL_FEEDBACK_TITLE"),
					className: "landing-ui-panel-feedback"
				});
				append(this.sliderFeedback.layout, document.body);
				this.sliderFormLoader = new BX.Loader({target: this.sliderFeedback.content});
				this.sliderFormLoader.show();
				this.initFeedbackForm();
			}

			data = isPlainObject(data) ? data : {};
			data.bitrix24 = this.options.server_name;
			data.siteId = this.options.site_id;
			data.siteUrl = this.options.url;
			data.siteTemplate = this.options.xml_id;
			data.productType = this.options.productType || "Undefined";

			var form = this.getFeedbackFormOptions();

			b24formFeedBack({
				"id": form.id,
				"lang": form.lang,
				"sec": form.sec,
				"type": "slider_inline",
				"node": this.sliderFeedback.content,
				"handlers": {
					"load": this.onSliderFormLoaded.bind(this)
				},
				"presets": isPlainObject(data) ? data : {}
			});

			this.sliderFeedback.show();
		},


		/**
		 * Gets feedback form options
		 * @return {{id: string, sec: string, lang: string}}
		 */
		getFeedbackFormOptions: function()
		{
			var currentLanguage = BX.message("LANGUAGE_ID");
			var options = {"id": "16", "sec": "3h483y", "lang": "en"};

			switch (currentLanguage)
			{
				case LANG_RU:
				case LANG_BY:
				case LANG_KZ:
					options = {"id": "8", "sec": "x80yjw", "lang": "ru"};
					break;
				case LANG_LA:
					options = {"id": "14", "sec": "wu561i", "lang": "la"};
					break;
				case LANG_DE:
					options = {"id": "10", "sec": "eraz2q", "lang": "de"};
					break;
				case LANG_BR:
					options = {"id": "12", "sec": "r6wvge", "lang": "br"};
					break;
				case LANG_UA:
					options = {"id": "18", "sec": "d9e09o", "lang": "ua"};
					break;
			}

			return options;
		},


		/**
		 * Handles feedback loaded event
		 */
		onSliderFormLoaded: function()
		{
			this.sliderFormLoader.hide();
		},


		/**
		 * Shows feedback form for blocks list panel
		 */
		showFeedbackForm: function()
		{
			this.showSliderFeedbackForm({target: "blocksList"});
		},


		/**
		 * Initialises feedback form
		 */
		initFeedbackForm: function()
		{
			(function(w,d,u,b){w['Bitrix24FormObject']=b;w[b] = w[b] || function(){arguments[0].ref=u;
				(w[b].forms=w[b].forms||[]).push(arguments[0])};
				if(w[b]['forms']) return;
				var s=d.createElement('script');
				var r=1*new Date();s.async=1;s.src=u+'?'+r;
				var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
			})(window,document,'https://landing.bitrix24.ru/bitrix/js/crm/form_loader.js','b24formFeedBack');
		},


		/**
		 * Creates blocks list panel sidebar button
		 * @param {string} category
		 * @param {object} options
		 * @returns {BX.Landing.UI.Button.SidebarButton}
		 */
		createBlockPanelSidebarButton: function(category, options)
		{
			return new BX.Landing.UI.Button.SidebarButton(category, {
				text: options.name,
				child: !options.separator,
				className: options.new ? "landing-ui-new-section" : "",
				onClick: this.onBlocksListCategoryChange.bind(this, category)
			});
		},


		/**
		 * Handles event on blocks list category change
		 * @param {string} category - Category id
		 */
		onBlocksListCategoryChange: function(category)
		{
			this.blocksPanel.content.hidden = false;

			this.blocksPanel.sidebarButtons.forEach(function(button) {
				button.layout.classList[button.id === category ? "add" : "remove"]("landing-ui-active");
			});

			this.blocksPanel.content.innerHTML = "";

			if (category === "last")
			{
				if (!this.lastBlocks)
				{
					this.lastBlocks = Object.keys(this.options.blocks["last"].items);
				}

				this.lastBlocks = arrayUnique(this.lastBlocks);

				this.lastBlocks.forEach(function(blockKey) {
					var block = this.getBlockFromRepository(blockKey);
					this.blocksPanel.appendCard(this.createBlockCard(blockKey, block));
				}, this);

				return;
			}

			Object.keys(this.options.blocks[category].items).forEach(function(blockKey) {
				var block = this.options.blocks[category].items[blockKey];
				this.blocksPanel.appendCard(this.createBlockCard(blockKey, block));
			}, this);

			if (this.blocksPanel.content.scrollTop)
			{
				requestAnimationFrame(function() {
					this.blocksPanel.content.scrollTop = 0;
				}.bind(this));
			}
		},

		getBlockFromRepository: function(code)
		{
			var blocks = this.options.blocks;
			var categories = Object.keys(blocks);
			var category = categories.find(function(categoryId) {
				return code in blocks[categoryId].items;
			});

			if (category)
			{
				return blocks[category].items[code];
			}
		},


		/**
		 * Handles copy block event
		 * @param {BX.Landing.Block} block
		 */
		onCopyBlock: function(block)
		{
			window.localStorage.landingBlockId = block.id;
			window.localStorage.landingBlockName = block.manifest.block.name;
			window.localStorage.landingBlockAction = "copy";

			try
			{
				window.localStorage.requiredUserAction = JSON.stringify(
					block.requiredUserActionOptions
				);
			}
			catch(err)
			{
				window.localStorage.requiredUserAction = "";
			}
		},


		/**
		 * Handles cut block event
		 * @param {BX.Landing.Block} block
		 */
		onCutBlock: function(block)
		{
			window.localStorage.landingBlockId = block.id;
			window.localStorage.landingBlockName = block.manifest.block.name;
			window.localStorage.landingBlockAction = "cut";

			try
			{
				window.localStorage.requiredUserAction = JSON.stringify(
					block.requiredUserActionOptions
				);
			}
			catch(err)
			{
				window.localStorage.requiredUserAction = "";
			}

			top.BX.Landing.Block.storage.remove(block);
			remove(block.node);
			fireCustomEvent("Landing.Block:onAfterDelete", [block]);
		},


		/**
		 * Handles paste block event
		 * @param {BX.Landing.Block} block
		 */
		onPasteBlock: function(block)
		{
			if (window.localStorage.landingBlockId)
			{
				var action = "Landing::copyBlock";

				if (window.localStorage.landingBlockAction === "cut")
				{
					action = "Landing::moveBlock";
				}

				var requestBody = {};

				requestBody[action] = {
					action: action,
					data: {
						lid: block.lid || BX.Landing.Main.getInstance().id,
						block: window.localStorage.landingBlockId,
						params: {
							"AFTER_ID": block.id,
							"RETURN_CONTENT": "Y"
						}
					}
				};

				BX.Landing.Backend.getInstance()
					.batch(action, requestBody, {action: action})
					.then(function(res) {
						this.currentBlock = block;
						return this.addBlock(res[action].result.content);
					}.bind(this))
			}
		},


		/**
		 * Adds block from server response
		 * @param {addBlockResponse} res
		 * @param {boolean} [preventHistory = false]
		 * @param {boolean} [withoutAnimation = false]
		 * @return {Promise<T>}
		 */
		addBlock: function(res, preventHistory, withoutAnimation)
		{
			if (this.lastBlocks)
			{
				this.lastBlocks.unshift(res.manifest.code);
			}

			var self = this;
			var block = this.appendBlock(res, withoutAnimation);

			return this.loadBlockDeps(res)
				.then(function(res) {
					if (!isBoolean(preventHistory) || preventHistory === false)
					{
						var lid = null;
						var id = null;

						if (self.currentBlock)
						{
							lid = self.currentBlock.lid;
							id = self.currentBlock.id;
						}

						if (self.currentArea)
						{
							lid = data(self.currentArea, "landing");
							id = data(self.currentArea, "site");
						}

						// Add history entry
						BX.Landing.History.getInstance().push(
							new BX.Landing.History.Entry({
								block: res.id,
								selector: "#block"+res.id,
								command: "addBlock",
								undo: "",
								redo: {
									currentBlock: id,
									lid: lid,
									code: res.manifest.code
								}
							})
						);
					}

					self.currentBlock = null;
					self.currentArea = null;

					var blockId = parseInt(res.id);
					var oldBlock = top.BX.Landing.Block.storage.get(blockId);

					if (oldBlock)
					{
						remove(oldBlock.node);
						top.BX.Landing.Block.storage.remove(oldBlock);
					}

					// Init block entity
					new BX.Landing.Block(block, {
						id: blockId,
						active: true,
						requiredUserAction: res.requiredUserAction,
						manifest: res.manifest
					});

					return self.runBlockScripts(res)
						.then(function() {
							return block;
						});
				})
				.catch(function(err) {
					console.warn(err);
				})
		},


		/**
		 * Handles edd block event
		 * @param {string} blockCode
		 * @param {*} [restoreId]
		 * @param {?boolean} [preventHistory = false]
		 * @return {Promise<BX.Landing.Block>}
		 */
		onAddBlock: function(blockCode, restoreId, preventHistory)
		{
			restoreId = parseInt(restoreId);

			this.hideBlocksPanel();

			return this.showBlockLoader()
				.then(this.loadBlock(blockCode, restoreId))
				.then(function(res) {
					return delay(500, res);
				})
				.then(function(res) {
					var p = this.addBlock(res, preventHistory);
					this.adjustEmptyAreas();
					this.hideBlockLoader();
					return p;
				}.bind(this));
		},


		/**
		 * Inserts element to blocks flow.
		 * Element can be inserted after current block or after last block
		 * @param {HTMLElement} element
		 */
		insertToBlocksFlow: function(element)
		{
			var insertAfterCurrentBlock = (
				this.currentBlock &&
				this.currentBlock.node &&
				this.currentBlock.node.parentNode
			);

			if (insertAfterCurrentBlock)
			{
				insertAfter(element, this.currentBlock.node);
				return;
			}

			prepend(element, this.currentArea);
		},


		/**
		 * Gets block loader
		 * @return {HTMLElement}
		 */
		getBlockLoader: function()
		{
			if (!this.blockLoader)
			{
				this.blockLoader = new BX.Loader({size: 60});
				this.blockLoaderContainer = create("div", {
					props: {className: "landing-block-loader-container"},
					children: [this.blockLoader.layout]
				});
			}

			return this.blockLoaderContainer;
		},


		/**
		 * Shows block loader
		 * @return {Function}
		 */
		showBlockLoader: function()
		{
			this.insertToBlocksFlow(this.getBlockLoader());
			return Promise.resolve();
		},


		/**
		 * Hides block loader
		 * @return {Function}
		 */
		hideBlockLoader: function()
		{
			remove(this.getBlockLoader());
			return Promise.resolve();
		},


		/**
		 * Loads block dependencies
		 * @param {addBlockResponse} data
		 * @returns {Promise<addBlockResponse>}
		 */
		loadBlockDeps: function(data)
		{
			var ext = BX.processHTML(data.content_ext);

			if (BX.type.isArray(ext.SCRIPT))
			{
				ext.SCRIPT = ext.SCRIPT.filter(function(item) {
					return !item.isInternal;
				});
			}

			var loadedScripts = 0;
			var scriptsCount = (data.js.length + ext.SCRIPT.length + ext.STYLE.length + data.css.length);
			var resPromise = null;

			if (!this.loadedDeps[data.manifest.code] && scriptsCount > 0)
			{
				resPromise = new Promise(function(resolve) {
					function onLoad()
					{
						loadedScripts += 1;
						loadedScripts === scriptsCount && resolve(data);
					}

					if (scriptsCount > loadedScripts)
					{
						// Load extensions files
						ext.SCRIPT.forEach(function(item) {
							if (!item.isInternal)
							{
								BX.loadScript(item.JS, onLoad);
							}
						});

						ext.STYLE.forEach(function(item) {
							BX.loadScript(item, onLoad);
						});

						// Load block files
						data.css.forEach(function(item) {
							BX.loadScript(item, onLoad);
						});

						data.js.forEach(function(item) {
							BX.loadScript(item, onLoad);
						});
					}
					else
					{
						onLoad();
					}

					this.loadedDeps[data.manifest.code] = true;
				}.bind(this));
			}
			else
			{
				resPromise = Promise.resolve(data);
			}

			return resPromise;
		},


		/**
		 * Executes block scripts
		 * @param data
		 * @return {Promise}
		 */
		runBlockScripts: function(data)
		{
			return new Promise(function(resolve) {
				var scripts = BX.processHTML(data.content).SCRIPT;

				if (scripts.length)
				{
					BX.ajax.processScripts(scripts, undefined, function() {
						resolve(data);
					});
				}
				else
				{
					resolve(data);
				}
			});
		},


		/**
		 * Load new block from server
		 * @param {string} blockCode
		 * @param {int} [restoreId]
		 * @returns {Function}
		 */
		loadBlock: function(blockCode, restoreId)
		{
			return function()
			{
				var lid = this.id;
				var siteId = this.options.site_id;

				if (this.currentBlock)
				{
					lid = this.currentBlock.lid;
					siteId = this.currentBlock.siteId;
				}

				if (this.currentArea)
				{
					lid = data(this.currentArea, "landing");
					siteId = data(this.currentArea, "site");
				}

				var requestBody = {
					lid: lid,
					siteId: siteId
				};

				var fields = {
					ACTIVE: "Y",
					CODE: blockCode,
					AFTER_ID: !!this.currentBlock ? this.currentBlock.id : 0,
					RETURN_CONTENT: "Y"
				};

				if (!restoreId)
				{
					requestBody.fields = fields;
					return BX.Landing.Backend.getInstance()
						.action("Landing::addBlock", requestBody, {code: blockCode});
				}
				else
				{
					requestBody = {
						"undeleete": {
							action: "Landing::markUndeletedBlock",
							data: {
								lid: lid,
								block: restoreId
							}
						},
						"getContent": {
							action: "Block::getContent",
							data: {
								block: restoreId,
								lid: lid,
								fields: fields,
								editMode: 1
							}
						}
					};

					return BX.Landing.Backend.getInstance()
						.batch("Landing::addBlock", requestBody, {code: blockCode})
						.then(function(res) {
							res.getContent.result.id = restoreId;
							return res.getContent.result;
						})
				}
			}.bind(this)
		},


		/**
		 * Creates block preview card
		 * @param {string} blockKey - Block key (folder name)
		 * @param {{name: string, [preview]: ?string, [new]: ?boolean}} block - Object with block data
		 * @param {string} [mode]
 		 * @returns {BX.Landing.UI.Card.BlockPreviewCard}
		 */
		createBlockCard: function(blockKey, block, mode)
		{
			return new BX.Landing.UI.Card.BlockPreviewCard({
				title: block.name,
				image: block.preview,
				code: blockKey,
				mode: mode,
				isNew: block.new === true,
				onClick: this.onAddBlock.bind(this, blockKey)
			});
		},


		/**
		 * Handles block delete event
		 */
		onBlockDelete: function(block)
		{
			if (!block.parent.querySelector(".block-wrapper"))
			{
				this.adjustEmptyAreas();
			}
		},


		/**
		 * Shows page overlay
		 */
		showOverlay: function()
		{
			var main = document.querySelector("main.landing-edit-mode");

			if (main)
			{
				addClass(main, "landing-ui-overlay");
			}
		},


		/**
		 * Hides page overlay
		 */
		hideOverlay: function()
		{
			var main = document.querySelector("main.landing-edit-mode");

			if (main)
			{
				removeClass(main, "landing-ui-overlay");
			}
		}
	};

})();