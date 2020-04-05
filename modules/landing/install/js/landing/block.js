;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	// Utils
	var deepFreeze = BX.Landing.Utils.deepFreeze;
	var style = BX.Landing.Utils.style;
	var insertAfter = BX.Landing.Utils.insertAfter;
	var insertBefore = BX.Landing.Utils.insertBefore;
	var append = BX.Landing.Utils.append;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isBoolean = BX.Landing.Utils.isBoolean;
	var isNumber = BX.Landing.Utils.isNumber;
	var isString = BX.Landing.Utils.isString;
	var isArray = BX.Landing.Utils.isArray;
	var isEmpty = BX.Landing.Utils.isEmpty;
	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var hasClass = BX.Landing.Utils.hasClass;
	var toggleClass = BX.Landing.Utils.toggleClass;
	var create = BX.Landing.Utils.create;
	var debounce = BX.Landing.Utils.debounce;
	var throttle = BX.Landing.Utils.throttle;
	var fireCustomEvent = BX.Landing.Utils.fireCustomEvent;
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;
	var bind = BX.Landing.Utils.bind;
	var unbind = BX.Landing.Utils.unbind;
	var getClass = BX.Landing.Utils.getClass;
	var rect = BX.Landing.Utils.rect;
	var setTextContent = BX.Landing.Utils.setTextContent;
	var translateY = BX.Landing.Utils.translateY;
	var nextSibling = BX.Landing.Utils.nextSibling;
	var prevSibling = BX.Landing.Utils.prevSibling;
	var join = BX.Landing.Utils.join;
	var slice = BX.Landing.Utils.slice;
	var decodeDataValue = BX.Landing.Utils.decodeDataValue;
	var encodeDataValue = BX.Landing.Utils.encodeDataValue;
	var data = BX.Landing.Utils.data;
	var attr = BX.Landing.Utils.attr;
	var removePanels = BX.Landing.Utils.removePanels;
	var getCSSSelector = BX.Landing.Utils.getCSSSelector;
	var remove = BX.Landing.Utils.remove;
	var clone = BX.Landing.Utils.clone;
	var trim = BX.Landing.Utils.trim;
	var prepend = BX.Landing.Utils.prepend;
	var random = BX.Landing.Utils.random;
	var htmlToElement = BX.Landing.Utils.htmlToElement;
	var proxy = BX.Landing.Utils.proxy;
	var escapeText = BX.Landing.Utils.escapeText;
	var isValidElementId = BX.Landing.Utils.isValidElementId;

	// Collections
	var BaseCollection = BX.Landing.Collection.BaseCollection;
	var NodeCollection = BX.Landing.Collection.NodeCollection;
	var FormCollection = BX.Landing.UI.Collection.FormCollection;
	var CardCollection = BX.Landing.Collection.CardCollection;
	var PanelCollection = BX.Landing.UI.Collection.PanelCollection;

	// Panels
	var BaseButtonPanel = BX.Landing.UI.Panel.BaseButtonPanel;
	var CardActionPanel = BX.Landing.UI.Panel.CardAction;
	var ContentEditPanel = BX.Landing.UI.Panel.ContentEdit;

	// Buttons
	var BaseButton = BX.Landing.UI.Button.BaseButton;
	var ActionButton = BX.Landing.UI.Button.Action;
	var PlusButton = BX.Landing.UI.Button.Plus;
	var CardActionButton = BX.Landing.UI.Button.CardAction;

	// Factories
	var StyleFactory = BX.Landing.UI.Factory.StyleFactory;

	// Forms
	var BaseForm = BX.Landing.UI.Form.BaseForm;
	var StyleForm = BX.Landing.UI.Form.StyleForm;
	var CardForm = BX.Landing.UI.Form.CardForm;
	var CardsForm = BX.Landing.UI.Form.CardsForm;

	// Other
	var Group = BX.Landing.Group;
	var BlockEvent = BX.Landing.Event.Block;
	var TabCard = BX.Landing.UI.Card.TabCard;
	var Menu = BX.Landing.UI.Tool.Menu;

	var Backend = BX.Landing.Backend.getInstance();

	// noinspection JSUnusedLocalSymbols
	/**
	 * Access denied
	 * @type {string}
	 */
	var ACCESS_D = "D";

	/**
	 * Design only
	 * @type {string}
	 */
	var ACCESS_V = "V";

	/**
	 * Edit without delete
	 * @type {string}
	 */
	var ACCESS_W = "W";

	/**
	 * All access
	 * @type {string}
	 */
	var ACCESS_X = "X";


	function getTypeSettings(prop)
	{
		var lp = BX.Landing.Main.getInstance();
		return lp.options.style["bitrix"]["style"][prop];
	}

	function isGroup(prop)
	{
		var lp = BX.Landing.Main.getInstance();
		return prop in lp.options.style["bitrix"]["group"];
	}

	function getGroupTypes(group)
	{
		var lp = BX.Landing.Main.getInstance();
		return lp.options.style["bitrix"]["group"][group];
	}


	/**
	 * Shows loader for button
	 * @param {BX.Landing.UI.Button.BaseButton} button
	 */
	function showButtonLoader(button)
	{
		if (!!button)
		{
			if (!button.loader)
			{
				button.loader = new BX.Loader({
					target: button.layout,
					size: 16
				});

				void style(button.loader.layout.querySelector(".main-ui-loader-svg-circle"), {
					"stroke-width": "4px"
				});
			}

			button.loader.show();
			addClass(button.text, "landing-ui-hide-icon");
		}
	}


	/**
	 * Hides button loader
	 * @param {BX.Landing.UI.Button.BaseButton} button
	 */
	function hideButtonLoader(button)
	{
		if (!!button)
		{
			if (button.loader)
			{
				button.loader.hide();
				removeClass(button.text, "landing-ui-hide-icon");
			}
		}
	}


	/**
	 * @param {string} selector
	 * @return {boolean|*}
	 */
	function isNodeSelector(selector)
	{
		return !!selector && selector.includes("@");
	}

	/**
	 * Implements interface for works with landing block
	 *
	 * @param {HTMLElement} element
	 * @param {blockOptions} options
	 *
	 * @property {BX.Landing.UI.Collection.PanelCollection.<BX.Landing.UI.Panel.BasePanel>} panels - Panels collection
	 * @property {BX.Landing.Collection.CardCollection.<BX.Landing.Block.Card>} cards - Cards collection
	 * @property {BX.Landing.Collection.NodeCollection.<BX.Landing.Block.Node>} nodes - Nodes collection
	 * @property {blockManifest} manifest
	 *
	 * @constructor
	 */
	BX.Landing.Block = function(element, options)
	{
		this.node = element;
		this.parent = element.parentElement;
		this.content = element.firstElementChild;
		this.siteId = data(element.parentElement, "data-site");
		this.lid = data(element.parentElement, "data-landing");
		this.id = isNumber(parseInt(options.id)) ? parseInt(options.id) : 0;
		this.selector = join("#block", (isNumber(options.id) ? options.id : 0), " > :first-child");
		this.active = isBoolean(options.active) ? options.active : true;
		this.manifest = isPlainObject(options.manifest) ? options.manifest : {};
		this.manifest.nodes = isPlainObject(options.manifest.nodes) ? options.manifest.nodes : {};
		this.manifest.cards = isPlainObject(options.manifest.cards) ? options.manifest.cards : {};
		this.manifest.attrs = isPlainObject(options.manifest.attrs) ? options.manifest.attrs : {};
		this.onStyleInputWithDebounce = debounce(this.onStyleInput, 300, this);
		this.changeTimeout = null;
		this.access = options.access;
		this.anchor = options.anchor;
		this.requiredUserActionOptions = options.requiredUserAction;

		// Make entities collections
		this.nodes = new NodeCollection();
		this.cards = new CardCollection();
		this.panels = new PanelCollection();
		this.groups = new BaseCollection();
		this.changedNodes = new BaseCollection();
		this.styles = new BaseCollection();
		this.forms = new FormCollection();

		if (isPlainObject(this.requiredUserActionOptions) && !isEmpty(this.requiredUserActionOptions))
		{
			this.showRequiredUserAction(this.requiredUserActionOptions);
			this.requiredUserActionIsShown = true;
		}

		this.onEditorEnabled = this.onEditorEnabled.bind(this);
		this.onEditorDisabled = this.onEditorDisabled.bind(this);
		this.adjustPanelsPosition = this.adjustPanelsPosition.bind(this);
		this.onMouseMove = this.onMouseMove.bind(this);
		this.onStorage = this.onStorage.bind(this);
		this.onBlockRemove = this.onBlockRemove.bind(this);
		this.onBlockInit = this.onBlockInit.bind(this);

		// Make manifest read only
		deepFreeze(this.manifest);

		// Apply block state
		this.node.classList[this.active ? "remove" : "add"]("landing-block-disabled");

		// Sets state
		this.state = "ready";

		// Init panels
		this.initPanels();
		this.initStyles();
		this.adjustContextSensitivityStyles();

		top.BX.Landing.Block.storage.push(this);

		// Fire block init event

		var eventData = {};

		if (this.requiredUserActionIsShown)
		{
			eventData.requiredUserActionIsShown = true;
			eventData.layout = this.node.firstElementChild;
			eventData.button = this.node.firstElementChild.querySelector(".ui-btn");
		}

		fireCustomEvent(window, "BX.Landing.Block:init", [this.createEvent({data: eventData})]);

		onCustomEvent("BX.Landing.Editor:enable", this.onEditorEnabled);
		onCustomEvent("BX.Landing.Editor:disable", this.onEditorDisabled);
		onCustomEvent("BX.Landing.Block:afterRemove", this.onBlockRemove);
		onCustomEvent("BX.Landing.Block:init", this.onBlockInit);

		bind(this.node, "mousemove", this.onMouseMove);
		bind(this.node, "keydown", this.adjustPanelsPosition);
		bind(top, "storage", this.onStorage);
	};


	BX.Landing.Block.storage = new BX.Landing.Collection.BlockCollection();


	BX.Landing.Block.prototype = {
		/**
		 * Handles mouse move event on block node
		 * Implements lazy initialization entities of block
		 * @private
		 */
		onMouseMove: function()
		{
			if (this.state === "ready")
			{
				unbind(this.node, "mousemove", this.onMouseMove);
				this.initEntities();
				this.lazyInitPanels();
				this.state = "complete";
			}
		},

		showRequiredUserAction: function(data)
		{
			this.node.innerHTML = (
				"<div class=\"landing-block-user-action\">" +
					"<div class=\"landing-block-user-action-inner\">" +
						(data.header ? (
							"<h3>"+"<i class=\"fa fa-exclamation-triangle g-mr-15\"></i>"+data.header+"</h3><hr>"
						) : "") +
						(data.description ? (
							"<p>"+data.description+"</p>"
						) : "") +
						((data.href || data.onClick || data.className) && data.text ? (
							"<div>" +
								"<a href=\""+data.href+"\" class=\"ui-btn "+data.className+"\">"+data.text+"</a>" +
							"</div>"
						) : "") +
					"</div>" +
				"</div>"
			);

			if (data.onClick)
			{
				var button = this.node.querySelector('.landing-block-user-action .ui-btn');
				bind(button, 'click', function(event) {
					event.preventDefault();

					try
					{
						BX.evalGlobal(data.onClick);
					}
					catch (err)
					{
						console.error(err);
					}
				});
			}
		},


		/**
		 * Disables content links and buttons
		 */
		disableLinks: function()
		{
			var selector = "a:not([class*='landing-ui']):not(.landing-trusted-link), .btn:not([class*='landing-ui']):not(.landing-trusted-link), button:not([class*='landing-ui']):not(.landing-trusted-link), input:not([class*='landing-ui'])";
			var items = slice(this.content.querySelectorAll(selector));

			items.forEach(function(item) {
				var isChildOfNode = this.nodes.some(function(node) {
					return node.node.contains(item);
				});

				if (!this.nodes.getByNode(item) && !isChildOfNode)
				{
					item.style.pointerEvents = "none";
				}
			}, this);
		},


		/**
		 * Adjusts context sensitivity styles
		 */
		adjustContextSensitivityStyles: function()
		{
			if (hasClass(this.parent, "landing-sidebar"))
			{
				if (!hasClass(this.content, "landing-adjusted"))
				{
					var selectors = Object.keys(this.manifest.style.nodes);
					var needAdjust = selectors.filter(function(selector) {
						return (
							!!this.manifest.style.nodes[selector].type &&
							this.manifest.style.nodes[selector].type.indexOf("columns") !== -1
						);
					}, this);

					if (isEmpty(needAdjust))
					{
						return;
					}

					var columnsSettings = getTypeSettings("columns");

					needAdjust.forEach(function(selector) {
						var styleNode = this.styles.get(selector);

						if (styleNode)
						{
							styleNode.setValue("col-lg-12", columnsSettings.items);
						}
					}, this);

					var blockStyleNode = this.styles.get(this.selector);

					if (blockStyleNode)
					{
						blockStyleNode.setValue("landing-adjusted", ["landing-adjusted"]);
					}

					this.saveStyles();
				}
			}
		},


		/**
		 * Forces block initialization
		 */
		forceInit: function()
		{
			this.onMouseMove();
		},


		/**
		 * Creates block event object
		 * @param {object} [options]
		 * @returns {BX.Landing.Event.Block}
		 */
		createEvent: function(options)
		{
			return new BlockEvent({
				block: this.node,
				node: !!options && !!options.node ? options.node : null,
				card: !!options && !!options.card ? options.card : null,
				data: (!!options && options.data) || {},
				onForceInit: this.forceInit.bind(this)
			});
		},


		/**
		 * Initializes block entities
		 * @private
		 */
		initEntities: function()
		{
			this.initCards();
			this.initNodes();
			this.initGroups();
			this.initCardsLabels();
			this.disableLinks();
		},


		initCardsLabels: function()
		{
			this.cards.forEach(function(card) {
				card.label = this.createCardLabel(card.node, card.manifest);
			}, this);
		},


		/**
		 * Initializes groups
		 */
		initGroups: function()
		{
			var groupsIds = [];
			var groups = isPlainObject(this.manifest.groups) ? this.manifest.groups : {};

			this.nodes.forEach(function(node) {
				if (isString(node.manifest.group) && !groupsIds.includes(node.manifest.group))
				{
					groupsIds.push(node.manifest.group);
				}
			});

			groupsIds.forEach(function(groupId) {
				var nodes = this.nodes
					.filter(function(node) {
						return node.manifest.group === groupId;
					})
					.reduce(function(accumulator, node) {
						var nodeIndex = parseInt(node.selector.split("@")[1]);

						if (!accumulator[nodeIndex])
						{
							accumulator[nodeIndex] = new NodeCollection();
						}

						accumulator[nodeIndex].push(node);

						return accumulator;
					}, {});

				Object.keys(nodes).forEach(function(key) {
					this.groups.add(
						new Group({
							id: groupId,
							name: groups[groupId],
							nodes: nodes[key],
							onClick: this.onGroupClick.bind(this)
						})
					);
				}, this);
			}, this);
		},


		/**
		 * Handles event on group click
		 * @param {BX.Landing.Group} group
		 */
		onGroupClick: function(group)
		{
			if (!BX.Landing.UI.Panel.StylePanel.getInstance().isShown())
			{
				this.showContentPanel({
					name: group.name,
					nodes: group.nodes,
					compact: true,
					nodesOnly: true,
					showAll: true
				});
			}
		},


		/**
		 * Initializes block panels
		 */
		initPanels: function()
		{
			// Make "add block after this block" button
			if (!this.panels.get("create_action"))
			{
				var createPanel = new BaseButtonPanel(
					"create_action",
					"landing-ui-panel-create-action"
				);

				createPanel.addButton(
					new PlusButton("insert_after", {
						text: BX.message("ACTION_BUTTON_CREATE"),
						onClick: throttle(this.addBlockAfterThis, 600, this)
					})
				);

				createPanel.show();
				this.addPanel(createPanel);

				createPanel.buttons[0].on("mouseover", this.onCreateButtonMouseover.bind(this));
				createPanel.buttons[0].on("mouseout", this.onCreateButtonMouseout.bind(this));
			}
		},

		isLastChildInArea: function()
		{
			return this.parent.querySelector("[class*='block-wrapper']:last-of-type") === this.node;
		},

		onCreateButtonMouseover: function()
		{
			if (this.isLastChildInArea() ||
				hasClass(this.parent, "landing-header") ||
				hasClass(this.parent, "landing-footer"))
			{
				var areas = BX.Landing.Main.getInstance().getLayoutAreas();

				if (areas.length > 1)
				{
					var addBlockPanel = this.panels.get("create_action");
					var addButton = addBlockPanel.buttons.get("insert_after");

					switch (true)
					{
						case hasClass(this.parent, "landing-main"):
							addButton.setText([
								BX.message("ACTION_BUTTON_CREATE"),
								BX.message("LANDING_ADD_BLOCK_TO_MAIN")
							].join(" "));
							break;
						case hasClass(this.parent, "landing-header"):
							addButton.setText([
								BX.message("ACTION_BUTTON_CREATE"),
								BX.message("LANDING_ADD_BLOCK_TO_HEADER")
							].join(" "));
							break;
						case hasClass(this.parent, "landing-sidebar"):
							addButton.setText([
								BX.message("ACTION_BUTTON_CREATE"),
								BX.message("LANDING_ADD_BLOCK_TO_SIDEBAR")
							].join(" "));
							break;
						case hasClass(this.parent, "landing-footer"):
							addButton.setText([
								BX.message("ACTION_BUTTON_CREATE"),
								BX.message("LANDING_ADD_BLOCK_TO_FOOTER")
							].join(" "));
							break;
					}

					clearTimeout(this.fadeTimeout);

					this.fadeTimeout = setTimeout(function() {
						addClass(this.parent, "landing-area-highlight");
						areas.forEach(function(area) {
							if (area !== this.parent)
							{
								addClass(area, "landing-area-fade");
							}
						}, this);
					}.bind(this), 400);
				}
			}
		},

		onCreateButtonMouseout: function()
		{
			clearTimeout(this.fadeTimeout);

			if (this.isLastChildInArea() ||
				hasClass(this.parent, "landing-header") ||
				hasClass(this.parent, "landing-footer"))
			{
				var areas = BX.Landing.Main.getInstance().getLayoutAreas();

				if (areas.length > 1)
				{
					var addButton = this.panels.get("create_action").buttons[0];
					addButton.setText(BX.message("ACTION_BUTTON_CREATE"));

					removeClass(this.parent, "landing-area-highlight");

					areas.forEach(function(area) {
						removeClass(area, "landing-area-fade");
					}, this);
				}
			}
		},


		/**
		 * Initializes action panels of block
		 * @private
		 */
		lazyInitPanels: function()
		{
			var allPlacements = BX.Landing.Main.getInstance().options.placements.blocks;

			// Make content actions panel
			if (!this.panels.contains("content_actions") &&
				!this.requiredUserActionIsShown &&
				(
					(isPlainObject(this.manifest.nodes) && !isEmpty(this.manifest.nodes)) ||
					(isPlainObject(this.manifest.style) && !isEmpty(this.manifest.style)) ||
					(isPlainObject(allPlacements) && !isEmpty(allPlacements))))
			{
				var contentPanel = new BaseButtonPanel(
					"content_actions",
					"landing-ui-panel-content-action"
				);

				contentPanel.addButton(
					new ActionButton("collapse", {
						html: "<span class='fa fa-caret-right'></span>",
						onClick: this.onCollapseActionPanel.bind(this),
						attrs: {title: BX.message("LANDING_TITLE_OF_BLOCK_ACTION_COLLAPSE")}
					})
				);

				if (isPlainObject(this.manifest.nodes) || isPlainObject(this.manifest.attrs))
				{
					contentPanel.addButton(
						new ActionButton("content", {
							text: BX.message("ACTION_BUTTON_CONTENT"),
							onClick: this.onShowContentPanel.bind(this),
							disabled: this.access < ACCESS_W || (
								isEmpty(this.manifest.nodes) && isEmpty(this.manifest.attrs)
							),
							attrs: {title: BX.message("LANDING_TITLE_OF_BLOCK_EDIT")}
						})
					);
				}

				if (isPlainObject(this.manifest.style))
				{
					contentPanel.addButton(
						new ActionButton("style", {
							text: BX.message("ACTION_BUTTON_STYLE"),
							onClick: this.onStyleShow.bind(this),
							disabled: this.access < ACCESS_V || isEmpty(this.manifest.style),
							attrs: {title: BX.message("LANDING_TITLE_OF_BLOCK_DESIGN")}
						})
					);
				}

				if (isPlainObject(allPlacements) && (this.manifest.code in allPlacements || allPlacements["*"]))
				{
					var placementsList = [];

					if (this.manifest.code in allPlacements)
					{
						Object.keys(allPlacements[this.manifest.code]).forEach(function(key) {
							placementsList.push(allPlacements[this.manifest.code][key]);
						}, this);
					}

					if (allPlacements["*"])
					{
						Object.keys(allPlacements["*"]).forEach(function(key) {
							placementsList.push(allPlacements["*"][key]);
						}, this);
					}

					if (placementsList.length)
					{
						contentPanel.addButton(
							new ActionButton("actions", {
								html: BX.message("ACTION_BUTTON_CONTENT_MORE"),
								onClick: this.onPlacementButtonClick.bind(this, placementsList)
							})
						);

						if (typeof top.BX.rest !== "undefined" &&
							typeof top.BX.rest.AppLayout !== "undefined")
						{
							var codes = ["*", this.manifest.code];
							for (var i = 0, c = codes.length; i < c; i++)
							{
								var MessageInterface = top.BX.rest.AppLayout.initializePlacement(
									"LANDING_BLOCK_" + codes[i]
								);
								if (MessageInterface)
								{
									MessageInterface.prototype.refreshBlock = function(params, cb) {
										var block = top.BX.Landing.Block.storage.get(params.id);

										if (block)
										{
											block
												.reload()
												.then(cb);
										}
									};
								}

							}
						}
					}

					addClass(contentPanel.buttons.get("style").layout, "landing-ui-no-rounded");
				}

				if (isPlainObject(this.manifest.style))
				{
					var blockDisplay = new ActionButton("block_display_info", {
						html: "&nbsp;"
					});

					bind(blockDisplay.layout, "mouseenter", this.onBlockDisplayMouseenter.bind(this));
					bind(blockDisplay.layout, "mouseleave", this.onBlockDisplayMouseleave.bind(this));

					contentPanel.addButton(
						blockDisplay
					);
				}

				contentPanel.show();
				this.addPanel(contentPanel);
			}


			// Make block actions panel
			if (!this.panels.get("block_action"))
			{
				var blockPanel = new BaseButtonPanel(
					"block_action",
					"landing-ui-panel-block-action"
				);

				var block = this.getBlockFromRepository(this.manifest.code);

				if (block && block.restricted)
				{
					var restrictedButton = new ActionButton("restricted", {
						html: "&nbsp;",
						className: "landing-ui-block-restricted-button",
						onClick: this.onRestrictedButtonClick.bind(this)
					});

					bind(restrictedButton.layout, "mouseenter", this.onRestrictedButtonMouseenter.bind(this));
					bind(restrictedButton.layout, "mouseleave", this.onRestrictedButtonMouseleave.bind(this));
					blockPanel.addButton(restrictedButton);
				}


				blockPanel.addButton(
					new ActionButton("down", {
						html: BX.message("ACTION_BUTTON_DOWN"),
						onClick: this.moveDown.bind(this),
						attrs: {title: BX.message("LANDING_TITLE_OF_BLOCK_ACTION_SORT_DOWN")}
					})
				);

				blockPanel.addButton(
					new ActionButton("up", {
						html: BX.message("ACTION_BUTTON_UP"),
						onClick: this.moveUp.bind(this),
						attrs: {title: BX.message("LANDING_TITLE_OF_BLOCK_ACTION_SORT_UP")}
					})
				);

				blockPanel.addButton(
					new ActionButton("actions", {
						html: BX.message("ACTION_BUTTON_ACTIONS"),
						onClick: this.showBlockActionsMenu.bind(this),
						attrs: {title: BX.message("LANDING_TITLE_OF_BLOCK_ACTION_ADDITIONAL_ACTIONS")}
					})
				);

				blockPanel.addButton(
					new ActionButton("remove", {
						html: BX.message("ACTION_BUTTON_REMOVE"),
						disabled: this.access < ACCESS_X,
						onClick: this.deleteBlock.bind(this),
						attrs: {title: BX.message("LANDING_TITLE_OF_BLOCK_ACTION_REMOVE")}
					})
				);

				blockPanel.addButton(
					new ActionButton("collapse", {
						html: "<span class='fa fa-caret-right'></span>",
						onClick: this.onCollapseActionPanel.bind(this),
						attrs: {title: BX.message("LANDING_TITLE_OF_BLOCK_ACTION_COLLAPSE")}
					})
				);

				blockPanel.show();
				this.addPanel(blockPanel);
			}

			this.adjustPanelsPosition();
			this.adjustSortButtonsState();
		},

		onCollapseActionPanel: function()
		{
			toggleClass(this.parent, "landing-ui-collapse");
		},

		getBlockFromRepository: function(code)
		{
			var blocks = BX.Landing.Main.getInstance().options.blocks;
			var categories = Object.keys(blocks);
			var category = categories.find(function(categoryId) {
				return code in blocks[categoryId].items;
			});

			if (category)
			{
				return blocks[category].items[code];
			}
		},


		onRestrictedButtonClick: function(event)
		{
			event.preventDefault();
		},

		onPlacementClick: function(placement)
		{
			BX.rest.AppLayout.openApplication(
				placement.app_id,
				{
					ID: this.id,
					CODE: this.manifest.code,
					LID: BX.Landing.Main.getInstance().id
				},
				{
					PLACEMENT: 'LANDING_BLOCK_' + placement.placement,
					PLACEMENT_ID: placement.id
				}
			);

			if (this.blockPlacementsActionsMenu)
			{
				this.blockPlacementsActionsMenu.close();
			}
		},


		onPlacementButtonClick: function(placements)
		{
			this.panels.get("content_actions").buttons.get("actions").activate();

			if (!this.blockPlacementsActionsMenu)
			{
				var blockActionButton = this.panels.get("content_actions").buttons.get("actions");
				var blockActionMenuId = join("block_", this.id, "content_placement_actions_", random());

				var menuItems = placements.map(function(placement) {
					return new BX.PopupMenuItem({
						id: "placement_" + placement.id + "_" + random(),
						text: encodeDataValue(placement.title),
						onclick: this.onPlacementClick.bind(this, placement)
					})
				}, this);

				this.blockPlacementsActionsMenu = new Menu({
					id: blockActionMenuId,
					bindElement: blockActionButton.layout,
					items: menuItems,
					angle: {position: "top", offset: 80},
					offsetTop: -6,
					events: {
						onPopupClose: function() {
							this.panels.get("content_actions").buttons.get("actions").deactivate();
							removeClass(this.node, "landing-ui-hover");
						}.bind(this)
					}
				});
			}

			addClass(this.node, "landing-ui-hover");
			this.blockPlacementsActionsMenu.show();
		},

		onRestrictedButtonMouseenter: function(event)
		{
			clearTimeout(this.displayBlockTimer);
			this.displayBlockTimer = setTimeout(function(target) {
				BX.Landing.UI.Tool.Suggest.getInstance().show(target, {
					description: BX.message("LANDING_BLOCK_RESTRICTED_TEXT")
				});
			}.bind(this), 200, event.currentTarget);
		},

		onRestrictedButtonMouseleave: function()
		{
			clearTimeout(this.displayBlockTimer);
			BX.Landing.UI.Tool.Suggest.getInstance().hide();
		},

		onBlockDisplayMouseenter: function(event)
		{
			clearTimeout(this.displayBlockTimer);
			this.displayBlockTimer = setTimeout(function(target) {
				BX.Landing.UI.Tool.Suggest.getInstance().show(
					target,
					{
						name: create("div", {
							props: {className: "landing-ui-block-display-message-header"},
							html: BX.message("LANDING_BLOCK_DISABLED_ON_DESKTOP_NAME")
						}).outerHTML,
						description: this.getBlockDisplayItems()
					}
				);
			}.bind(this), 300, event.currentTarget);
		},

		onBlockDisplayMouseleave: function()
		{
			clearTimeout(this.displayBlockTimer);
			BX.Landing.UI.Tool.Suggest.getInstance().hide();
		},

		getBlockDisplayItems: function()
		{
			function createItem(mod)
			{
				return create("div", {
					props: {className: "landing-ui-block-display-message"},
					attrs: {"data-mod": mod},
					children: [
						create("div", {
							props: {className: "landing-ui-block-display-message-left"},
							html: "&nbsp;"
						}),
						create("div", {
							props: {className: "landing-ui-block-display-message-right"},
							children: [
								create("p", {html: BX.message("LANDING_BLOCK_HIDDEN_ON_"+(mod ? mod.toUpperCase() : ""))})
							]
						})
					]
				});
			}

			var result = create("div");

			if (hasClass(this.content, "l-d-lg-none"))
			{
				result.appendChild(createItem("desktop"));
			}

			if (hasClass(this.content, "l-d-md-none"))
			{
				result.appendChild(createItem("tablet"));
			}

			if (hasClass(this.content, "l-d-xs-none"))
			{
				result.appendChild(createItem("mobile"));
			}

			return result.outerHTML;
		},


		/**
		 * Adjusts block panels position
		 */
		adjustPanelsPosition: function()
		{
			var blockRect = rect(this.node);
			var contentActionsPanel = this.panels.get("content_actions");
			var blockActionsPanel = this.panels.get("block_action");
			var action = blockRect.height < 80 ? addClass : removeClass;

			if (contentActionsPanel)
			{
				action(contentActionsPanel.layout, "landing-ui-panel-actions-compact");
			}

			if (blockActionsPanel)
			{
				action(blockActionsPanel.layout, "landing-ui-panel-actions-compact");
			}
		},


		/**
		 * Handles editor enable event
		 * Event fires when the editor was show
		 * @param {HTMLElement} element
		 */
		onEditorEnabled: function(element)
		{
			if (this.node.contains(element))
			{
				addClass(this.node, "landing-ui-hover");
			}
		},


		/**
		 * Handles editor disable event
		 * Event fires when the editor was hidden
		 */
		onEditorDisabled: function()
		{
			removeClass(this.node, "landing-ui-hover");
		},


		onStorage: function()
		{
			if (this.blockActionsMenu)
			{
				var item = this.blockActionsMenu.getMenuItem("block_paste");

				if (item)
				{
					if (window.localStorage.landingBlockId)
					{
						item.layout.item.setAttribute("title", window.localStorage.landingBlockName);
						removeClass(item.layout.item, "landing-ui-disabled");
						addClass(item.layout.item, "menu-popup-no-icon");
					}
					else
					{
						item.layout.item.setAttribute("title", "");
						addClass(item.layout.item, "landing-ui-disabled");
					}
				}
			}
		},


		/**
		 * Shows block popup menu with additional action menu
		 */
		showBlockActionsMenu: function()
		{
			this.panels.get("block_action").buttons.get("actions").activate();

			if (!this.blockActionsMenu)
			{
				var useSmallOffset = hasClass(this.node.parentElement, "landing-sidebar");
				var blockActionButton = this.panels.get("block_action").buttons.get("actions");
				var blockActionMenuId = join("block_", this.id, "_actions_", random());
				var landing = BX.Landing.Main.getInstance();

				this.blockActionsMenu = new Menu({
					id: blockActionMenuId,
					bindElement: blockActionButton.layout,
					className: "landing-ui-block-actions-popup",
					angle: {position: "top", offset: useSmallOffset ? 70 : 146},
					offsetTop: -6,
					offsetLeft: -26,
					events: {
						onPopupClose: function() {
							this.panels.get("block_action").buttons.get("actions").deactivate();
							removeClass(this.node, "landing-ui-hover");
						}.bind(this)
					},
					items: [
						new BX.PopupMenuItem({
							id: "show_hide",
							text: BX.message(this.isEnabled() ? "ACTION_BUTTON_HIDE" : "ACTION_BUTTON_SHOW"),
							className: this.access < ACCESS_W ? "landing-ui-disabled" : "",
							onclick: function() {
								this.onStateChange();
								this.blockActionsMenu.close();
							}.bind(this)
						}),
						new BX.PopupMenuItem({
							text: BX.message("ACTION_BUTTON_ACTIONS_CUT"),
							className: this.access < ACCESS_X ? "landing-ui-disabled" : "",
							onclick: function() {
								landing.onCutBlock.bind(landing, this)();
								this.blockActionsMenu.close();
							}.bind(this)
						}),
						new BX.PopupMenuItem({
							text: BX.message("ACTION_BUTTON_ACTIONS_COPY"),
							onclick: function() {
								landing.onCopyBlock.bind(landing, this)();
								this.blockActionsMenu.close();
							}.bind(this)
						}),
						new BX.PopupMenuItem({
							id: "block_paste",
							text: BX.message("ACTION_BUTTON_ACTIONS_PASTE"),
							title: window.localStorage.landingBlockName,
							className: window.localStorage.landingBlockId ? "": "landing-ui-disabled",
							onclick: function() {
								landing.onPasteBlock.bind(landing, this)();
								this.blockActionsMenu.close();
							}.bind(this)
						}),
						new BX.PopupMenuItem({
							text: BX.message("LANDING_BLOCKS_ACTIONS_FEEDBACK_BUTTON"),
							onclick: function() {
								BX.Landing.Main.getInstance().showSliderFeedbackForm({
									blockName: this.manifest.block.name,
									blockCode: this.manifest.code,
									blockSection: this.manifest.block.section,
									landingId: BX.Landing.Main.getInstance().id,
									target: "blockActions"
								});
								this.blockActionsMenu.close();
							}.bind(this)
						})
					]
				});
			}

			addClass(this.node, "landing-ui-hover");
			this.blockActionsMenu.show();
		},


		/**
		 * Moves the block up one position
		 * @param {boolean} [preventHistory = false] - Add this action to history or not. No by default
		 */
		moveUp: function(preventHistory)
		{
			var prev = prevSibling(this.node, "block-wrapper");
			var current = this.node;

			if (prev)
			{
				var result = Promise.all([
					translateY(current, -rect(prev).height),
					translateY(prev, rect(current).height)
				]);

				result.then(function() {
					void style(current, {"transform": null, "transition": null});
					void style(prev, {"transform": null, "transition": null});
					insertBefore(current, prev);

					if (!preventHistory || typeof preventHistory === "object")
					{
						BX.Landing.History.getInstance().push(
							new BX.Landing.History.Entry({
								block: this.id,
								selector: "#block"+this.id,
								command: "sortBlock",
								undo: "moveDown",
								redo: "moveUp"
							})
						);
					}
				}.bind(this));

				Backend.action(
					"Landing::upBlock",
					{block: this.id, lid: this.lid, siteId: this.siteId},
					{code: this.manifest.code}
				);
			}
		},


		/**
		 * Moves the block down one position
		 * @param {boolean} [preventHistory = false] - Add this action to history or not. No by default
		 */
		moveDown: function(preventHistory)
		{
			var next = nextSibling(this.node, "block-wrapper");
			var current = this.node;

			if (!!next)
			{
				var result = Promise.all([
					translateY(current, rect(next).height),
					translateY(next, -rect(current).height)
				]);

				result.then(function() {
					void style(current, {"transform": null, "transition": null});
					void style(next, {"transform": null, "transition": null});
					insertAfter(current, next);

					if (!preventHistory || typeof preventHistory === "object")
					{
						BX.Landing.History.getInstance().push(
							new BX.Landing.History.Entry({
								block: this.id,
								selector: "#block"+this.id,
								command: "sortBlock",
								undo: "moveUp",
								redo: "moveDown"
							})
						);
					}
				}.bind(this));

				Backend.action(
					"Landing::downBlock",
					{block: this.id, lid: this.lid, siteId: this.siteId},
					{code: this.manifest.code}
				);
			}
		},


		/**
		 * Adds panel into this block
		 * @param {BX.Landing.UI.Panel.BasePanel} panel
		 * @param {*} [target = this.node]
		 */
		addPanel: function(panel, target)
		{
			if (!this.panels.contains(panel))
			{
				this.panels.add(panel);

				if (!target)
				{
					append(panel.layout, this.node);
				}
				else
				{
					insertBefore(panel.layout, target);
				}
			}
		},


		/**
		 * Handles show panel event
		 * @private
		 */
		onShowContentPanel: function()
		{
			this.showContentPanel();
			BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
		},


		/**
		 * Handles state change event
		 * @private
		 */
		onStateChange: function()
		{
			if (this.isEnabled())
			{
				this.disable();
			}
			else
			{
				this.enable();
			}
		},


		/**
		 * Checks that block is enabled
		 * @return {boolean}
		 */
		isEnabled: function()
		{
			return this.active;
		},


		/**
		 * Enables block
		 */
		enable: function()
		{
			this.active = true;
			removeClass(this.node, "landing-block-disabled");
			setTextContent(this.blockActionsMenu.getMenuItem("show_hide").getLayout().text, BX.message("ACTION_BUTTON_HIDE"));
			Backend.action(
				"Landing::showBlock",
				{block: this.id, lid: this.lid, siteId: this.siteId},
				{code: this.manifest.code}
			);
		},


		/**
		 * Disables block
		 */
		disable: function()
		{
			this.active = false;
			addClass(this.node, "landing-block-disabled");
			setTextContent(this.blockActionsMenu.getMenuItem("show_hide").getLayout().text, BX.message("ACTION_BUTTON_SHOW"));
			Backend.action(
				"Landing::hideBlock",
				{block: this.id, lid: this.lid, siteId: this.siteId},
				{code: this.manifest.code}
			);
		},


		/**
		 * Creates card label
		 * @param {HTMLElement} node
		 * @param {cardManifest} manifest
		 * @return {div}
		 */
		createCardLabel: function(node, manifest)
		{
			var labelSelectors = [];

			if (isString(manifest.label))
			{
				labelSelectors.push(manifest.label);
			}
			else if (isArray(manifest.label))
			{
				labelSelectors = labelSelectors.concat(manifest.label);
			}

			var cardNodes = this.nodes.filter(function(currentNode) {
				return node.contains(currentNode.node);
			});

			var children = [];

			labelSelectors.forEach(function(selector) {
				var labelNode = cardNodes.find(function(currentNode) {
					return currentNode.manifest.code === selector;
				});

				if (labelNode)
				{
					var currentLabel;

					if (labelNode instanceof BX.Landing.Block.Node.Text)
					{
						currentLabel = create("span", {
							props: {className: "landing-card-title-text"},
							html: escapeText(create("div", {html: labelNode.getValue()}).innerText)
						});
						children.push(currentLabel);

						onCustomEvent(labelNode.getField(), "BX.Landing.UI.Field:change", function(value) {
							currentLabel.innerHTML = escapeText(create("div", {html: value}).innerText);
						});

						return;
					}

					if (labelNode instanceof BX.Landing.Block.Node.Link)
					{
						currentLabel = create("span", {
							props: {className: "landing-card-title-link"},
							html: escapeText(labelNode.getValue().text)
						});
						children.push(currentLabel);

						onCustomEvent(labelNode.getField(), "BX.Landing.UI.Field:change", function(value) {
							currentLabel.innerHTML = escapeText(value.text);
						});

						return;
					}

					if (labelNode instanceof BX.Landing.Block.Node.Icon)
					{
						currentLabel = create("span", {
							props: {className: "landing-card-title-icon"},
							children: [create("span", {props: {className: labelNode.getValue().classList.join(" ")}})]
						});
						children.push(currentLabel);

						onCustomEvent(labelNode.getField(), "BX.Landing.UI.Field:change", function(value) {
							currentLabel.firstChild.className = "landing-card-title-icon " + value.classList.join(" ");
						});

						return;
					}

					if (labelNode instanceof BX.Landing.Block.Node.Img)
					{
						currentLabel = create("span", {
							props: {className: "landing-card-title-img"},
							attrs: {
								style: "background-color: #fafafa"
							},
							children: [create('img', {props: {src: labelNode.getValue().src}})]
						});
						children.push(currentLabel);

						onCustomEvent(labelNode.getField(), "BX.Landing.UI.Field:change", function(value) {
							currentLabel.innerHTML = "";
							currentLabel.appendChild(create('img', {props: {src: value.src}}));
						});
					}
				}
			}, this);

			return create("div", {
				props: {className: "landing-card-title"},
				children: !isEmpty(children) ? children : manifest.name
			});
		},


		/**
		 * Init Cards of Block.
		 */
		initCards: function()
		{
			this.cards.clear();
			this.forEachCard(function(node, selector, index) {
				var manifest = this.manifest.cards[selector];
				var cardSelector = join(selector, "@", index);

				removePanels(node);
				var instance = new BX.Landing.Block.Card(node, manifest, cardSelector);
				this.cards.add(instance);

				if (manifest.allowInlineEdit !== false)
				{
					var cardAction = new CardActionPanel(
						"cardAction",
						"landing-ui-panel-block-card-action"
					);
					cardAction.show();
					instance.addPanel(cardAction);

					cardAction.addButton(new CardActionButton("clone", {
						html: "&nbsp;",
						onClick: function(event) {
							event.stopPropagation();

							if (instance.manifest.sync)
							{
								var syncedSelectors = instance.manifest.sync;

								if (isString(instance.manifest.sync))
								{
									syncedSelectors = [instance.manifest.sync];
								}

								if (isArray(syncedSelectors))
								{
									syncedSelectors.forEach(function(currentSyncSelector) {
										this.cloneCard(join(currentSyncSelector, "@", index));
									}, this);
								}
							}

							this.cloneCard(cardSelector);
						}.bind(this),
						attrs: {title: BX.message("LANDING_TITLE_OF_CARD_ACTION_CLONE")}
					}));

					cardAction.addButton(new CardActionButton("remove", {
						html: "&nbsp;",
						onClick: function(event) {
							event.stopPropagation();

							if (instance.manifest.sync)
							{
								var syncedSelectors = instance.manifest.sync;

								if (isString(instance.manifest.sync))
								{
									syncedSelectors = [instance.manifest.sync];
								}

								if (isArray(syncedSelectors))
								{
									syncedSelectors.forEach(function(currentSyncSelector) {
										this.removeCard(join(currentSyncSelector, "@", index));
									}, this);
								}
							}

							this.removeCard(cardSelector);
						}.bind(this),
						attrs: {title: BX.message("LANDING_TITLE_OF_CARD_ACTION_REMOVE")}
					}));
				}

				instance.selector = cardSelector;
				instance.sortIndex = index;

				this.adjustCardRemoveButton(cardSelector);
			});

			this.cards.sort(function(a, b) {
				return a.getIndex() > b.getIndex();
			});
		},


		/**
		 * Clones Card.
		 * @param {string} selector - Selector of Card, which want clone.
		 * @param {boolean} [preventHistory]
		 * @return {Promise}
		 */
		cloneCard: function(selector, preventHistory)
		{
			var card = this.cards.getBySelector(selector);
			var cloneButton = card.panels.get("cardAction").buttons.get("clone");
			var requestData = {block: this.id, selector: selector, lid: this.lid, siteId: this.siteId};
			var queryParams = {code: this.manifest.code};
			var self = this;

			showButtonLoader(cloneButton);

			return Backend.action("Landing\\Block::cloneCard", requestData, queryParams)
				// Before clone
				.then(function() {
					fireCustomEvent("BX.Landing.Block:Card:beforeAdd", [
						self.createEvent({card: card.node})
					]);
				})

				// Clone
				.then(function() {
					var clonedCard = BX.clone(card.node);
					removePanels(clonedCard);
					insertAfter(clonedCard, card.node);
					return clonedCard;
				})

				// After clone
				.then(function(clonedCard) {
					hideButtonLoader(cloneButton);

					fireCustomEvent("BX.Landing.Block:Card:add", [
						self.createEvent({card: clonedCard})
					]);

					self.initEntities();

					if (!preventHistory)
					{
						var containerSelector = getCSSSelector(clonedCard.parentNode);
						var clonedCardEntity = self.cards.getByNode(clonedCard);

						BX.Landing.History.getInstance().push(
							new BX.Landing.History.Entry({
								block: self.id,
								selector: clonedCardEntity.selector,
								command: "addCard",
								undo: {
									container: containerSelector,
									selector: clonedCardEntity.selector
								},
								redo: {
									container: containerSelector,
									index: card.getIndex(),
									html: clonedCard.outerHTML
								}
							})
						);
					}
				})

				// Handle errors
				.catch(function() {
					hideButtonLoader(cloneButton);
					return Promise.reject();
				});
		},


		/**
		 * Removes Card.
		 * @param {String} selector - Selector of Card, which want remove.
		 * @param {boolean} [preventHistory]
		 * @return {Promise}
		 */
		removeCard: function(selector, preventHistory)
		{
			var card = this.cards.getBySelector(selector);
			var removeButton = card.panels.get("cardAction").buttons.get("remove");
			var requestData = {block: this.id, selector: selector, lid: this.lid, siteId: this.siteId};
			var queryParams = {code: this.manifest.code};
			var self = this;

			showButtonLoader(removeButton);

			return Backend.action("Landing\\Block::removeCard", requestData, queryParams)
				// Before remove
				.then(function() {
					fireCustomEvent("BX.Landing.Block:Card:beforeRemove", [
						self.createEvent({card: card.node})
					]);

					if (!preventHistory)
					{
						var containerSelector = getCSSSelector(card.node.parentElement);

						removePanels(card.node);

						BX.Landing.History.getInstance().push(
							new BX.Landing.History.Entry({
								block: self.id,
								selector: card.selector,
								command: "removeCard",
								undo: {
									container: containerSelector,
									index: card.getIndex(),
									html: card.node.outerHTML
								},
								redo: {
									container: containerSelector,
									selector: card.selector
								}
							})
						);
					}
				})
				// Remove
				.then(function() {
					self.cards.remove(card);
					card.node.remove();
					self.initEntities();
					self.adjustCardRemoveButton(selector);
				})
				// After remove
				.then(function() {
					var afterEvent = self.createEvent({data: {selector: selector}});
					fireCustomEvent("BX.Landing.Block:Card:remove", [afterEvent]);
					hideButtonLoader(removeButton);
				})
				.catch(function() {
					hideButtonLoader(removeButton);
					return Promise.reject();
				});
		},


		adjustCardRemoveButton: function(selector)
		{
			var card = this.cards.getBySelector(selector);

			if (card)
			{
				var lastCardInCollection = card.node.parentElement.children.length === 1;

				if (lastCardInCollection)
				{
					card.panels.get("cardAction").buttons.get("remove").disable();
				}
				else
				{
					card.panels.get("cardAction").buttons.get("remove").enable();
				}
			}
		},


		/**
		 * Adds card
		 * @param {{[index]: !int, container: !HTMLElement, content: string, selector: string}} settings
		 * @return {Promise}
		 */
		addCard: function(settings)
		{
			var selector = settings.selector.split("@")[0] + (settings.index > 0 ? "@"+(settings.index-1) : "");

			var requestData = {
				block: this.id,
				content: settings.content,
				selector: selector,
				lid: this.lid,
				siteId: this.siteId
			};
			var queryParams = {code: this.manifest.code};
			var container = settings.container;
			var card = create("div", {html: settings.content}).firstElementChild;
			var self = this;

			return Backend.action("Landing\\Block::addCard", requestData, queryParams)
				.then(function() {
					fireCustomEvent("BX.Landing.Block:Card:beforeAdd", [
						self.createEvent({card: card})
					]);
				})
				.then(function() {
					var targetCard;
					if (settings.index <= 0)
					{
						targetCard = self.cards.find(function(card) {
							return card.selector.includes(selector.split("@")[0])
						});

						if (targetCard)
						{
							prepend(card, targetCard.node.parentNode);
						}
					}
					else
					{
						targetCard = self.cards.getBySelector(selector.split("@")[0] + "@" + (settings.index-1));

						if (targetCard)
						{
							insertAfter(card, targetCard.node);
						}
					}

					removePanels(container);
					self.initEntities();

					fireCustomEvent("BX.Landing.Block:Card:add", [
						self.createEvent({card: card})
					]);
				})
		},


		/**
		 * @callback cardCallback
		 * @param {HTMLElement} node
		 * @param {string} selector
		 * @param {int} index
		 */
		/**
		 * Applies callback function for each card node
		 * @param {cardCallback} callback
		 */
		forEachCard: function(callback)
		{
			var cardSelectors = Object.keys(this.manifest.cards);

			cardSelectors.map(function(cardSelector) {
				var cards = slice(this.node.querySelectorAll(cardSelector));

				cards.forEach(function(node, index) {
					callback.apply(this, [node, cardSelector, index]);
				}, this);
			}, this);
		},


		/**
		 * Init Nodes of Block.
		 */
		initNodes: function()
		{
			if (this.access < ACCESS_W)
			{
				return;
			}

			var nodes = [];

			this.forEachNodeElements(function(element, selector, index) {
				var instance = this.nodes.getByNode(element);
				var nodeSelector = join(selector, "@", index);

				if (!instance)
				{
					var handler = getClass(this.manifest.nodes[selector].handler);
					var presetNode = element.closest('[data-card-preset]');
					var manifest = clone(this.manifest.nodes[selector]);
					var disallowField = false;

					if (presetNode)
					{
						var presetId = presetNode.dataset.cardPreset;

						Object.keys(this.manifest.cards).forEach(function(cardSelector) {
							if (presetNode.matches(cardSelector))
							{
								if (isPlainObject(this.manifest.cards[cardSelector].presets) &&
									isPlainObject(this.manifest.cards[cardSelector].presets[presetId]) &&
									isArray(this.manifest.cards[cardSelector].presets[presetId].disallow))
								{
									var isDisallow = this.manifest.cards[cardSelector].presets[presetId].disallow.find(function(disallowSelector) {
										return selector === disallowSelector;
									});

									if (isDisallow)
									{
										manifest.allowInlineEdit = false;
										disallowField = true;
									}
								}
							}
						}, this);
					}

					instance = new handler({
						node: element,
						manifest: manifest,
						selector: nodeSelector,
						onChange: this.onNodeChange.bind(this),
						onChangeOptions: this.onNodeOptionsChange.bind(this),
						onAttributeChange: this.onAttributeChange.bind(this),
						onDesignShow: this.showStylePanel.bind(this),
						uploadParams: {
							action: "Block::uploadFile",
							block: this.id
						}
					});

					if (disallowField)
					{
						instance.getField().layout.hidden = true;
					}

					this.nodes.add(instance);
				}

				instance.selector = nodeSelector;
				nodes.push(instance);
			});

			this.nodes.clear();

			nodes.forEach(function(node) {
				this.nodes.add(node);
			}, this);

			this.nodes.sort(function(a, b) {
				return a.getIndex() > b.getIndex();
			});
		},


		/**
		 * Handles node options change event
		 * @param {*} data
		 * @return {Promise<Object, Object>}
		 */
		onNodeOptionsChange: function(data)
		{
			if (!isEmpty(data))
			{
				var queryParams = {code: this.manifest.code};
				var requestBody = {};

				requestBody.data = data;
				requestBody.block = this.id;
				requestBody.siteId = this.siteId;

				return BX.Landing.Backend.getInstance()
					.action("Block::changeNodeName", requestBody, queryParams);
			}
		},


		/**
		 * @callback forEachNodeElementsCallback
		 * @param {HTMLElement} [element]
		 * @param {string} [selector]
		 * @param {int} [index]
		 */
		/**
		 * Applies callback for each element of node
		 * @param {forEachNodeElementsCallback} callback
		 */
		forEachNodeElements: function(callback)
		{
			Object.keys(this.manifest.nodes).forEach(function(selector) {
				try {
					slice(this.node.querySelectorAll(selector)).forEach(function(element, index) {
						if (!element.matches("[data-id=\"content_edit\"] *"))
						{
							callback.apply(this, [element, selector, index]);
						}
					}, this);
				} catch(err) {}
			}, this);
		},


		/**
		 * Shows content edit panel
		 * @param {{name: string, nodes: BX.Landing.Collection.NodeCollection, [compact]: boolean, [nodesOnly]}} [options]
		 */
		showContentPanel: function(options)
		{
			var nodes = !!options && options.nodes ? options.nodes : this.nodes;
			var formName = !!options && options.name ? options.name : null;
			var nodesOnly = !!options && options.nodesOnly ? options.nodesOnly : false;
			var showAll = !!options && options.showAll ? options.showAll : false;
			var compactMode = !!options && options.compact;
			var contentPanel = this.panels.get("content_edit");

			if (!contentPanel)
			{
				contentPanel = new ContentEditPanel("content_edit", {
					title: BX.message("LANDING_CONTENT_PANEL_TITLE"),
					subTitle: this.manifest.block.name,
					footer: [
						new BaseButton("save_block_content", {
							text: BX.message("BLOCK_SAVE"),
							onClick: this.onContentSave.bind(this),
							className: "landing-ui-button-content-save",
							attrs: {title: BX.message("LANDING_TITLE_OF_SLIDER_SAVE")}
						}),
						new BaseButton("cancel_block_content", {
							text: BX.message("BLOCK_CANCEL"),
							onClick: this.onContentCancel.bind(this),
							className: "landing-ui-button-content-cancel",
							attrs: {title: BX.message("LANDING_TITLE_OF_SLIDER_CANCEL")}
						})
					]
				});

				this.addPanel(contentPanel);
			}

			contentPanel.compact(compactMode);
			contentPanel.clear();

			var block = this.getBlockFromRepository(this.manifest.code);

			if (block && block.restricted)
			{
				append(this.getRestrictedMessage(), contentPanel.content);
			}

			this.tmpContent = create("div", {
				props: {hidden: true}
			});

			this.content.appendChild(this.tmpContent);
			var html = "";

			Object.keys(this.manifest.cards).forEach(function(cardSelector) {
				var card = this.manifest.cards[cardSelector];

				if (isPlainObject(card.presets))
				{
					Object.keys(card.presets).forEach(function(presetId) {
						var preset = card.presets[presetId];
						html += preset.html;
					}, this);
				}
			}, this);

			this.tmpContent.innerHTML = html;
			this.initEntities();

			this.getEditForms(nodes, formName, nodesOnly, showAll).forEach(contentPanel.appendForm, contentPanel);
			this.tmpContent.innerHTML = "";
			contentPanel.show();
		},


		getRestrictedMessage: function()
		{
			return create("div", {
				props: {className: "ui-alert ui-alert-warning"},
				html: BX.message("LANDING_BLOCK_RESTRICTED_TEXT"),
				attrs: {style: "margin-bottom: 20px"}
			})
		},


		/**
		 * Handles event on style panel show
		 */
		onStyleShow: function()
		{
			this.showStylePanel(this.selector);
			BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
		},


		/**
		 * Gets className postfix --lg --md --sm
		 */
		getPostfix: function()
		{
			return "";
		},


		/**
		 * Expands type groups
		 * @param {string|string[]} types
		 * @returns {string[]}
		 */
		expandTypeGroups: function(types)
		{
			var result = [];

			if (!BX.type.isArray(types))
			{
				types = [types];
			}

			types.forEach(function(type) {
				if (isGroup(type))
				{
					getGroupTypes(type).forEach(function(groupType) {
						result.push(groupType);
					});
				}
				else
				{
					result.push(type);
				}
			});

			return result;
		},


		/**
		 * Makes style editor form for style node
		 * @param {string} selector
		 * @param {{
		 * 		type: string|string[],
		 * 		name: string,
		 * 		[props],
		 * 		[title]
		 * 	}} settings
		 * @param {boolean} [isBlock = false]
		 * @returns {?BX.Landing.UI.Form.StyleForm}
		 */
		createStyleForm: function(selector, settings, isBlock)
		{
			var form = this.forms.get(selector);

			if (form)
			{
				this.forms.remove(form);
			}

			var type = !!settings.props ? settings.props : !!settings.type ? settings.type : null;
			var name = !!settings.title ? settings.title : !!settings.name ? settings.name : "";

			if (!!type && !!name)
			{
				var styleFactory = new StyleFactory({frame: window, postfix: this.getPostfix()});

				form = new StyleForm({
					id: selector,
					title: name,
					selector: selector,
					iframe: window
				});

				type = this.expandTypeGroups(type);
				type.forEach(function(type) {
					var typeSettings = getTypeSettings(type);
					var styleNode = this.styles.get(selector);
					var field = styleFactory.createField({
						selector: !isBlock ? this.makeRelativeSelector(selector) : selector,
						property: typeSettings.property,
						multiple: typeSettings.multiple === true,
						style: type,
						pseudoElement: typeSettings["pseudo-element"],
						pseudoClass: typeSettings["pseudo-class"],
						type: typeSettings.type,
						title: typeSettings.name,
						items: typeSettings.items,
						onChange: function(value, items, postfix, affect) {
							var exclude = !!typeSettings.exclude ? getTypeSettings(typeSettings.exclude) : null;

							if (exclude)
							{
								form.fields.forEach(function(field) {
									if (field.style === typeSettings.exclude)
									{
										field.reset();
									}
								});
							}

							var oldValue = {className: "", style: ""};
							if (styleNode.node[0])
							{
								oldValue.className = styleNode.node[0].className;
								oldValue.style = styleNode.node[0].style.cssText;
							}


							var event = this.createEvent({
								data: {
									selector: selector,
									value: value,
									items: items,
									postfix: postfix,
									affect: affect,
									exclude: exclude
								}
							});

							fireCustomEvent(window, "BX.Landing.Block:beforeApplyStyleChanges", [event]);

							styleNode.setValue(value, items, postfix, affect, exclude);

							var newValue = {className: "", style: ""};
							if (styleNode.node[0])
							{
								newValue.className = styleNode.node[0].className;
								newValue.style = styleNode.node[0].style.cssText;
							}

							try
							{
								if (JSON.stringify(oldValue) !== JSON.stringify(newValue))
								{
									BX.Landing.History.getInstance().push(
										new BX.Landing.History.Entry({
											block: this.id,
											command: "updateStyle",
											selector: !isBlock ? this.makeRelativeSelector(selector) : selector,
											undo: oldValue,
											redo: newValue
										})
									);
								}
							}
							catch(err) {}

							fireCustomEvent("BX.Landing.Block:updateStyleWithoutDebounce", [
								this.createEvent({node: styleNode.getNode(), data: styleNode.getValue()})
							]);
							this.onStyleInputWithDebounce({node: styleNode.getNode(), data: styleNode.getValue()});
						}.bind(this)
					});

					var preventEvent = true;
					styleNode.getValue().classList.forEach(function(className) {
						if (typeSettings.items.some(function(item) { return item.value === className}))
						{
							if (field.property !== "display")
							{
								field.setValue(className, preventEvent);
							}
						}
					});

					form.addField(field);
				}, this);

				this.forms.add(form);
			}

			form.fields.forEach(function(field) {
				if (field.popup)
				{
					field.popup.close();
				}
			});

			return form;
		},


		initStyles: function()
		{
			if (this.access < ACCESS_V)
			{
				return;
			}

			this.styles.clear();
			var node = new BX.Landing.UI.Style({
				id: this.selector,
				iframe: window,
				selector: this.selector,
				relativeSelector: this.selector,
				onClick: this.onStyleClick.bind(this, this.selector)
			});

			this.styles.add(node);

			if (isPlainObject(this.manifest.style) &&
				isPlainObject(this.manifest.style.nodes))
			{
				Object.keys(this.manifest.style.nodes).forEach(function(selector) {
					var node = new BX.Landing.UI.Style({
						id: selector,
						iframe: window,
						selector: selector,
						relativeSelector: this.makeRelativeSelector(selector),
						onClick: this.onStyleClick.bind(this, selector)
					});

					this.styles.add(node);
				}, this);
			}
		},

		onStyleClick: function(selector)
		{
			this.showStylePanel(selector);
			var form = this.forms.get(selector);

			if (form)
			{
				BX.Landing.PageObject.getInstance().design().then(function(panel) {
					BX.Landing.UI.Panel.Content.scrollTo(panel.content, null);
				});
			}
		},


		/**
		 * Makes selector relative this block
		 * @param {string} selector
		 * @return {string}
		 */
		makeRelativeSelector: function(selector)
		{
			return join(this.selector, " ", selector);
		},


		/**
		 * Makes absolute selector
		 * @param {string} selector
		 * @return {string}
		 */
		makeAbsoluteSelector: function(selector)
		{
			selector = selector || this.selector;
			selector = trim(selector);
			var find = selector === this.selector ? " > :first-child" : this.selector;
			return trim(selector.replace(find, "").replace("!", ""));
		},


		/**
		 * Saves block style changes
		 */
		saveStyles: function()
		{
			var styles = this.styles.fetchChanges();

			if (styles.length)
			{
				styles.forEach(function(style) {
					if (style.selector === this.selector)
					{
						style.selector = style.selector.replace(" > :first-child", "");
					}

					if (!style.isSelectGroup() && style.selector !== this.makeAbsoluteSelector(this.selector))
					{
						style.selector = join(style.selector.split("@")[0], "@", style.getElementIndex(style.getNode()[0]));
					}

					if (style.isSelectGroup())
					{
						style.selector = style.selector.split("@")[0];
					}
				}, this);

				var post = styles.fetchValues();
				Backend.action(
					"Landing\\Block::updateStyles",
					{block: this.id, data: post, lid: this.lid, siteId: this.siteId},
					{code: this.manifest.code}
				);
			}
		},


		/**
		 * Shows style editor panel
		 */
		showStylePanel: function(selector)
		{
			BX.Landing.PageObject.getInstance().design()
				.then(function(stylePanel) {
					stylePanel.clearContent();
					return stylePanel.show();
				})
				.then(function(stylePanel) {
					var isBlock = this.isBlockSelector(selector);
					var options = this.getStyleOptions(selector);

					if (isArray(options.type) || isString(options.type))
					{
						if (options.type.length)
						{
							stylePanel.appendForm(
								this.createStyleForm(selector, options, isBlock)
							);
						}
					}

					if (isPlainObject(options.additional))
					{
						selector = options.selector ? options.selector : selector;
						stylePanel.appendForm(
							this.createAdditionalForm({
								form: StyleForm,
								selector: selector,
								group: options.additional,
								onChange: this.onAttributeChange.bind(this)
							})
						);
						return;
					}

					if (isArray(options.additional))
					{
						options.additional.forEach(function(group) {
							stylePanel.appendForm(
								this.createAdditionalForm({
									form: StyleForm,
									selector: selector,
									group: group,
									onChange: this.onAttributeChange.bind(this)
								})
							);
						}, this);
					}
				}.bind(this))
		},


		/**
		 * Gets style options for selector
		 * @param {!string} selector
		 * @return {object}
		 */
		getStyleOptions: function(selector)
		{
			if (this.isBlockSelector(selector))
			{
				return this.prepareBlockOptions(this.manifest.style.block);
			}

			return this.manifest.style.nodes[selector];
		},


		/**
		 * Creates additional form
		 * @param {object} options
		 * @return {BX.Landing.UI.Form.StyleForm}
		 */
		createAdditionalForm: function(options)
		{
			var form = new options.form({title: options.group.name, type: "attrs"});

			options.group.attrs.forEach(function(attrOptions) {
				var currentSelector = attrOptions.selector || options.selector;
				var field;

				if (isArray(attrOptions.tabs))
				{
					var card = new TabCard({
						tabs: attrOptions.tabs.map(function(tabOptions) {
							return {
								id: random(),
								name: tabOptions.name,
								active: tabOptions.active,
								fields: tabOptions.attrs.map(function(attrOptions) {
									return this.createAttributeField(
										attrOptions,
										attrOptions.selector || options.selector,
										options.onChange
									);
								}, this)
							};
						}, this)
					});

					form.addCard(card);
					return;
				}

				field = this.createAttributeField(attrOptions, currentSelector, options.onChange);
				form.addField(field);
			}, this);

			return form;
		},


		prepareBlockOptions: function(options)
		{
			options = isPlainObject(options) ? options : {};
			options = clone(options);
			options.name = BX.message("BLOCK_STYLE_OPTIONS");

			if (!isPlainObject(options.type) && !isString(options.type) && !isArray(options.type))
			{
				options.type = [
					"display",
					"padding-top",
					"padding-bottom",
					"padding-left",
					"padding-right",
					"margin-top",
					"background-color",
					"background-gradient"
				];
			}

			return options;
		},


		/**
		 * Creates attribute field
		 * @param {object} options
		 * @param {?string} selector
		 * @param {function} [onAttributeChange]
		 * @return {BX.Landing.UI.Field.BaseField}
		 */
		createAttributeField: function(options, selector, onAttributeChange)
		{
			var fieldFactory = this.createFieldFactory(selector, onAttributeChange);
			var element = this.getElementBySelector(selector);

			if (!element && selector.includes("@"))
			{
				var selectorFragments = selector.split("@");
				var elements = this.getElementsBySelector(selectorFragments[0]);

				if (elements.length && elements[parseInt(selectorFragments[1])])
				{
					element = elements[parseInt(selectorFragments[1])];
				}
			}

			var fieldOptions = clone(options);

			if (fieldOptions.value === null || fieldOptions.value === undefined)
			{
				fieldOptions.value = "";
			}

			if (element)
			{
				var value = data(element, fieldOptions.attribute);

				if (value && isString(value))
				{
					value = attr(element, fieldOptions.attribute);
				}

				if (value !== null)
				{
					fieldOptions.value = value;
				}
			}

			return fieldFactory.create(fieldOptions);
		},


		onAttributeChange: function(field)
		{
			clearTimeout(this.attributeChangeTimeout);

			if (!this.requestData)
			{
				this.requestData = {};
			}

			this.appendAttrFieldValue(this.requestData, field);

			Promise.resolve(this.requestData)
				.then(this.applyAttributeChanges.bind(this))
				.then(this.saveChanges.bind(this))
				// Reload only blocks with component
				.then(this.reload.bind(this))
				.then(function() {
					this.requestData = null;
				}.bind(this));
		},


		appendSettingsFieldValue: function(requestData, field)
		{
			requestData["settings"] = requestData["settings"] || {};
			requestData["settings"][field.attribute] = field.getValue();
			return requestData;
		},


		/**
		 * @param {object} requestData
		 * @param {BX.Landing.UI.Field.BaseField} field
		 * @return {object}
		 */
		appendAttrFieldValue: function(requestData, field)
		{
			var selector = this.makeAbsoluteSelector(field.selector);
			var value = field.getValue();

			try {
				value = encodeDataValue(value);
			} catch(e) {
				value = field.getValue();
			}

			requestData[selector] = requestData[selector] || {};
			requestData[selector]["attrs"] = requestData[selector]["attrs"] || {};
			requestData[selector]["attrs"][field.attribute] = value;
			return requestData;
		},


		/**
		 * Gets element by selector
		 * @param selector
		 * @return {*}
		 */
		getElementBySelector: function(selector)
		{
			if (this.isBlockSelector(selector))
			{
				return this.content;
			}

			var element;

			try
			{
				element = this.node.querySelector(selector);
			}
			catch (err)
			{
				element = null;
			}

			return element;
		},

		getElementsBySelector: function(selector)
		{
			if (this.isBlockSelector(selector))
			{
				return [this.content];
			}

			var elements;

			try
			{
				elements = slice(this.node.querySelectorAll(selector));
			}
			catch (err)
			{
				elements = [];
			}

			return elements;
		},


		/**
		 * Checks that this selector is block selector
		 * @param {string} selector
		 * @return {boolean}
		 */
		isBlockSelector: function(selector)
		{
			return !selector || selector === this.selector || ("#block"+this.id) === selector;
		},


		/**
		 * Creates FieldFactory instance
		 * @param {?string} selector
		 * @param {function} [onChange]
		 * @return {BX.Landing.UI.Factory.FieldFactory}
		 */
		createFieldFactory: function(selector, onChange)
		{
			return new BX.Landing.UI.Factory.FieldFactory({
				selector: !this.isBlockSelector(selector) ? this.makeRelativeSelector(selector) : selector,
				uploadParams: {
					action: "Block::uploadFile",
					block: this.id,
					lid: BX.Landing.Main.getInstance().id,
					id: BX.Landing.Main.getInstance().options.site_id
				},
				linkOptions: {
					siteId: BX.Landing.Main.getInstance().options.site_id,
					landingId: BX.Landing.Main.getInstance().id,
					filter: {
						"=TYPE": BX.Landing.Main.getInstance().options.params.type
					}
				},
				onValueChange: onChange || (function() {})
			});
		},


		/**
		 * Delete current block.
		 * @param {?boolean} [preventHistory = false]
		 * @return {void}
		 */
		deleteBlock: function(preventHistory)
		{
			var button = this.panels.get("block_action").buttons.get("remove");
			button.loader = button.loader || new BX.Loader({target: button.layout, size: 28});
			button.loader.show();
			addClass(button.text, "landing-ui-hide-icon");

			void style(button.loader.layout.querySelector(".main-ui-loader-svg-circle"), {
				"stroke-width": "4px"
			});

			BX.Landing.UI.Panel.EditorPanel.getInstance().hide();

			if (this.blockActionsMenu)
			{
				BX.PopupMenu.destroy(this.blockActionsMenu.id);
			}

			window.localStorage.removeItem("landingBlockId");

			Backend.action(
				"Landing::markDeletedBlock",
				{block: this.id, lid: this.lid, siteId: this.siteId},
				{code: this.manifest.code}
			)
				.then(function() {
					button.loader.hide();
					removeClass(button.text, "landing-ui-hide-icon");

					var event = this.createEvent();
					fireCustomEvent("BX.Landing.Block:remove", [event]);

					slice(this.node.querySelectorAll(".landing-ui-panel")).forEach(remove);
					if ((isBoolean(preventHistory) && !preventHistory) || !isBoolean(preventHistory))
					{
						var prevBlock = top.BX.Landing.Block.storage.getByNode(
							BX.findPreviousSibling(this.node, {className: "block-wrapper"})
						);

						BX.Landing.History.getInstance().push(
							new BX.Landing.History.Entry({
								block: this.id,
								selector: "#block"+this.id,
								command: "removeBlock",
								undo: {
									currentBlock: prevBlock ? prevBlock.id : null,
									lid: this.lid,
									code: this.manifest.code
								},
								redo: ""
							})
						);
					}

					top.BX.Landing.Block.storage.remove(this);
					remove(this.node);
					fireCustomEvent("Landing.Block:onAfterDelete", [this]);
					fireCustomEvent("BX.Landing.Block:afterRemove", [event]);
				}.bind(this), function() {
					button.loader.hide();
					removeClass(button.text, "landing-ui-hide-icon");
				});
		},


		/**
		 * Shows blocks list panel
		 */
		addBlockAfterThis: function()
		{
			BX.Landing.Main.getInstance().showBlocksPanel(this);
		},


		/**
		 * Handles node content change event
		 * @param {BX.Landing.Block.Node} node
		 */
		onNodeChange: function(node)
		{
			var event = this.createEvent({node: node.node});
			fireCustomEvent("BX.Landing.Block:Node:update", [event]);

			if (!node.isSavePrevented())
			{
				clearTimeout(this.changeTimeout);
				this.changedNodes.add(node);

				this.changeTimeout = setTimeout(function() {
					Backend.action(
						"Landing\\Block::updateNodes",
						{
							block: this.id,
							data: this.changedNodes.fetchValues(),
							additional: this.changedNodes.fetchAdditionalValues(),
							lid: this.lid,
							siteId: this.siteId
						},
						{code: this.manifest.code}
					);

					this.changedNodes.clear();
				}.bind(this), 100);
			}
		},


		/**
		 * Checks that data contains pseudo selector
		 * @param {object} data
		 * @return {boolean}
		 */
		containsPseudoSelector: function(data)
		{
			return Object.keys(data).some(function(selector) {
				var result;

				if (selector === "cards")
				{
					return false;
				}

				try
				{
					if (selector !== "#block" + this.id && selector !== "")
					{
						result = !this.node.querySelector(selector);
					}
					else
					{
						result = false;
					}
				}
				catch(err)
				{
					result = !isNodeSelector(selector);
				}

				return result;
			}, this);
		},


		/**
		 * Applies content changes
		 * @param {object} data
		 * @return {Promise<Object>}
		 */
		applyContentChanges: function(data)
		{
			if (!isPlainObject(data))
			{
				return Promise.reject(
					new TypeError("BX.Landing.Block.applyContentChanges: data isn't object")
				);
			}

			var eventData = clone(data);

			Object.keys(eventData).forEach(function(selector) {
				if (!isNodeSelector(selector))
				{
					delete eventData[selector];
				}
			});

			if (!isEmpty(eventData))
			{
				var event = this.createEvent({data: eventData});
				fireCustomEvent(window, "BX.Landing.Block:beforeApplyContentChanges", [event]);
			}

			Object.keys(data).forEach(function(selector) {
				if (isNodeSelector(selector))
				{
					var node = this.nodes.getBySelector(selector);

					if (node)
					{
						node.setValue(data[selector], true);
						data[selector] = node.getValue();
					}
				}
			}, this);

			return Promise.resolve(data);
		},


		/**
		 * @private
		 * @param data
		 * @return {Promise}
		 */
		applyCardsChanges: function(data)
		{
			if (!isPlainObject(data))
			{
				return Promise.reject(
					new TypeError("BX.Landing.Block.applyCardsChanges: data isn't object")
				);
			}

			if ('cards' in data && isPlainObject(data.cards))
			{
				fireCustomEvent("BX.Landing.Block:Cards:beforeUpdate", [
					this.createEvent()
				]);

				var map = {};

				Object.keys(data.cards)
					.forEach(function(code) {
						var container = this.node.querySelector(code).parentElement;
						var oldCards = this.node.querySelectorAll(code);

						// Card data
						var values = data.cards[code].values;
						var presets = data.cards[code].presets;
						var indexes = data.cards[code].indexes;
						var source = data.cards[code].source;

						// Remove old cards
						container.innerHTML = "";

						// Make source
						Object.keys(values)
							.forEach(function(index) {
								source[index] = {value: 0, type: "card"};

								if (!isEmpty(presets) && !isEmpty(presets[index]) && !oldCards[indexes[index]])
								{
									source[index].type = "preset";
									source[index].value = presets[index];
									return;
								}

								if (oldCards[indexes[index]])
								{
									source[index].type = "card";
									source[index].value = indexes[index];
								}
							}, this);

						// Make new cards
						Object.keys(values)
							.forEach(function(index) {
								if (source[index].type === "preset")
								{
									var preset = this.manifest.cards[code]["presets"][source[index].value]["html"];
									append(htmlToElement(preset), container);
									return;
								}

								append(clone(oldCards[source[index].value]), container);
							}, this);

						// Reinitialize entities
						this.initNodes();
						this.initCards();
						this.initGroups();

						// Apply nodes values
						Object.keys(values)
							.forEach(function(index) {
								var card = values[index];

								Object.keys(card)
									.forEach(function(key) {
										map[key] = key in map ? map[key] + 1 : 0;

										var node = this.nodes.getBySelector(join(key, "@", map[key]));

										if (node)
										{
											var newValue = card[key];
											var oldValue = node.getValue();

											if (isPlainObject(newValue) && isString(newValue.url))
											{
												newValue.url = decodeDataValue(newValue.url);
											}

											if (isPlainObject(oldValue) && isString(oldValue.url))
											{
												oldValue.url = decodeDataValue(oldValue.url);
											}

											try
											{
												newValue = JSON.stringify(newValue);
											} catch (e)
											{
												newValue = card[key];
											}

											try
											{
												oldValue = JSON.stringify(oldValue);
											} catch (e)
											{
												oldValue = node.getValue();
											}

											var preventHistory = oldValue === newValue;
											node.setValue(card[key], true, preventHistory);
											card[join(key, "@", map[key])] = node.getValue();

											if (node.manifest.type === "img" || node.manifest.type === "icon")
											{
												card[join(key, "@", map[key])]["url"] = encodeDataValue(card[key]["url"]);
											}

											delete card[key];
										}
									}, this);
							}, this);

						// Reinitialize additional entities
						this.initCardsLabels();
						this.initStyles();

						// Remove unnecessary
						delete data.cards[code].presets;
						delete data.cards[code].indexes;

					}, this);

				fireCustomEvent("BX.Landing.Block:Cards:update", [
					this.createEvent()
				]);
			}

			return Promise.resolve(data);
		},


		applySettingsChanges: function(requestData)
		{
			if (!isPlainObject(requestData))
			{
				return Promise.reject(
					new TypeError("BX.Landing.Block.applyAttributeChanges: requestData isn't object")
				);
			}

			if (isPlainObject(requestData.settings) &&
				!isEmpty(requestData.settings))
			{
				if (requestData.settings.id)
				{
					this.content.id = requestData.settings.id;
				}
			}

			return Promise.resolve(requestData);
		},


		/**
		 * Applies attributes changes
		 * @param {Object} requestData
		 */
		applyAttributeChanges: function(requestData)
		{
			if (!isPlainObject(requestData))
			{
				return Promise.reject(
					new TypeError("BX.Landing.Block.applyAttributeChanges: requestData isn't object")
				);
			}

			var eventData = clone(requestData);

			Object.keys(requestData).forEach(function(selector) {
				if (!(isPlainObject(requestData[selector]) && "attrs" in requestData[selector]))
				{
					delete eventData[selector];
				}
			});

			if (!isEmpty(eventData))
			{
				var event = this.createEvent({data: eventData});
				fireCustomEvent(window, "BX.Landing.Block:beforeApplyAttributesChanges", [event]);
			}

			var self = this;

			Object.keys(requestData).forEach(function(selector) {
				if (isPlainObject(requestData[selector]) && "attrs" in requestData[selector])
				{
					var elements = self.getElementsBySelector(selector);

					if (!elements.length && selector.includes("@"))
					{
						var selectorFragments = selector.split("@");

						elements = self.getElementsBySelector(selectorFragments[0]);

						if (elements[parseInt(selectorFragments[1])])
						{
							elements = [elements[parseInt(selectorFragments[1])]];
						}
					}

					Object.keys(requestData[selector].attrs).forEach(function(attribute) {
						elements.forEach(function(element) {
							var decodedValue = decodeDataValue(requestData[selector]["attrs"][attribute]);

							if (!attribute.includes("data-"))
							{
								attr(element, attribute, decodedValue);
							}
							else
							{
								data(element, attribute, decodedValue);
							}

							fireCustomEvent("BX.Landing.Block:Node:updateAttr", [
								self.createEvent({
									node: element,
									data: requestData[selector]["attrs"]
								})
							]);
						});
					});
				}
			});

			return Promise.resolve(requestData);
		},



		/**
		 * Saves changes on backend
		 * @param {Object} data
		 * @return {Promise<Object>}
		 */
		saveChanges: function(data)
		{
			if (!isPlainObject(data))
			{
				return Promise.reject(
					new TypeError("BX.Landing.Block.saveChanges: data isn't object")
				);
			}

			if (Object.keys(data).length)
			{
				var updateNodeParams = {code: this.manifest.code};
				var updateNodesData = {block: this.id, data: data, lid: this.lid, siteId: this.siteId};
				var batch = {};

				if (!isEmpty(data.cards))
				{
					var cardsData = clone(data.cards);

					delete data.cards;

					var cardsSelectors = BX.Landing.Utils.arrayUnique(Object.keys(cardsData));
					cardsSelectors = cardsSelectors.length === 1 ? cardsSelectors + " *" : cardsSelectors.join(" *, ");
					var cardsNodesAdditionalValues = this.nodes.matches(cardsSelectors).fetchAdditionalValues();

					batch.updateCards = {
						action: "Block::updateCards",
						data: {
							block: this.id,
							lid: this.lid,
							siteId: this.siteId,
							data: cardsData,
							additional: cardsNodesAdditionalValues
						}
					};
				}

				if (isPlainObject(data.settings) &&
					!isEmpty(data.settings))
				{
					if (data.settings.id)
					{
						batch.changeAnchor = {
							action: "Block::changeAnchor",
							data: {
								block: this.id,
								lid: this.lid,
								data: data.settings.id
							}
						};
					}

					delete data.settings;
				}

				if (!isEmpty(data))
				{
					var nodes = new NodeCollection();

					Object.keys(updateNodesData).forEach(function(selector) {
						nodes.add(this.nodes.getBySelector(selector));
					}, this);



					batch.updateNodes = {
						action: "Block::updateNodes",
						data: updateNodesData,
						additional: nodes.fetchAdditionalValues()
					};
				}

				return Backend
					.batch("Landing\\Block::updateNodes", batch, updateNodeParams)
					.then(function() {
						return Promise.resolve(data);
					});
			}
			else
			{
				return Promise.resolve(data);
			}
		},


		/**
		 * Fetches request data from content panel
		 * @param {BX.Landing.UI.Panel.BasePanel} panel
		 * @return {Promise<Object>}
		 */
		fetchRequestData: function(panel)
		{
			var requestData = {};
			var forms = {};

			forms.attrs = new FormCollection();
			forms.cards = new FormCollection();
			forms.content = new FormCollection();
			forms.settings = new FormCollection();

			panel.forms
				.forEach(function(form) {
					forms[form.type].push(form);
				});

			forms.content
				.fetchFields()
				.fetchChanges()
				.reduce(proxy(this.appendContentFieldValue, this), requestData);

			var attrFields = new BaseCollection();

			forms.cards.forEach(function(form) {
				form.childForms.forEach(function(childForm) {
					childForm.fields.forEach(function(field) {
						if (field.type === "attr")
						{
							attrFields.add(field);
						}
					});
				});
			});

			attrFields
				.fetchChanges()
				.reduce(proxy(this.appendAttrFieldValue, this), requestData);

			forms.cards
				.reduce(proxy(this.appendCardsFormValue, this), requestData);

			forms.attrs
				.fetchFields()
				.fetchChanges()
				.reduce(proxy(this.appendAttrFieldValue, this), requestData);

			forms.settings
				.fetchFields()
				.fetchChanges()
				.reduce(proxy(this.appendSettingsFieldValue, this), requestData);

			return Promise.resolve(requestData);
		},


		/**
		 * Appends content field value to request data
		 * @param {object} requestData
		 * @param {BX.Landing.UI.Field.BaseField} field
		 * @return {object}
		 */
		appendContentFieldValue: function(requestData, field)
		{
			return requestData[field.selector] = field.getValue(), requestData;
		},


		/**
		 * Appends cards value to request data
		 * @param {object} requestData
		 * @param {BX.Landing.UI.Form.CardsForm} form
		 * @return {object}
		 */
		appendCardsFormValue: function(requestData, form)
		{
			requestData.cards = requestData.cards || {};
			requestData.cards[form.code] = {};
			requestData.cards[form.code]['values'] = form.serialize();
			requestData.cards[form.code]['presets'] = form.getUsedPresets();
			requestData.cards[form.code]['indexes'] = form.getIndexesMap();
			requestData.cards[form.code]['source'] = {};

			return requestData;
		},


		/**
		 * Reloads block
		 * @todo Refactoring
		 * @param {object} data
		 * @return {Promise}
		 */
		reload: function(data)
		{
			if (BX.type.isPlainObject(data) &&
				!this.containsPseudoSelector(data))
			{
				return Promise.resolve(data);
			}

			var loader = new BX.Loader({target: this.parent, color: "rgba(255, 255, 255, .8)"});
			loader.layout.style.position = "fixed";
			loader.layout.style.zIndex = "999";
			loader.show();
			BX.Landing.Main.getInstance().showOverlay();

			var self = this;
			return BX.Landing.Backend.getInstance()
				.action("Block::getContent", {
					block: this.id,
					lid: this.lid,
					siteId: this.siteId,
					editMode: 1
				})
				.then(function(response) {
					BX.Landing.Main.getInstance().currentBlock = self;
					BX.Landing.Main.getInstance().currentArea = self.parent;
					return BX.Landing.Main.getInstance().addBlock(response, true, true);
				})
				.then(function(block) {
					self.node = block;
					return Promise.resolve(data);
				})
				.then(function(data) {
					return new Promise(function(resolve) {
						setTimeout(function() {
							resolve(data);
							loader.hide();
							BX.Landing.Main.getInstance().hideOverlay();
						}, 800);
					});
				});
		},


		/**
		 * Handles content save event
		 */
		onContentSave: function()
		{
			var contentPanel = this.panels.get("content_edit");

			if (contentPanel)
			{
				contentPanel.hide();

				this.fetchRequestData(contentPanel)
					.then(this.applyContentChanges.bind(this))
					.then(this.applyCardsChanges.bind(this))
					.then(this.applyAttributeChanges.bind(this))
					.then(this.applySettingsChanges.bind(this))
					.then(this.saveChanges.bind(this))
					// Reload only blocks with component
					.then(this.reload.bind(this))
					.catch(console.warn);

				var contentForms = new FormCollection();

				contentPanel.forms.forEach(function(form) {
					if (form.type !== "attrs")
					{
						contentForms.add(form);
					}
				});

				var nodesOptions = {};

				contentForms.fetchFields().forEach(function(field) {
					if (field.tag)
					{
						nodesOptions[field.selector] = {tagName: field.tag};
					}
				}, this);

				this.onNodeOptionsChange(nodesOptions);
			}
		},


		/**
		 * Handles content cancel edit event
		 */
		onContentCancel: function()
		{
			this.panels.get("content_edit").hide();
			this.tmpContent.innerHTML = "";
		},


		/**
		 * Gets cards CSS selector
		 * @return {string}
		 */
		getCardsSelector: function()
		{
			var cardsSelectors = Object.keys(this.manifest.cards);
			var allCards = join(cardsSelectors.join(","), ", ");
			var allCardsChild = join(cardsSelectors.join(" *,"), " *");

			return join(allCards, allCardsChild);
		},


		onStyleInput: function(event)
		{
			this.saveStyles();

			var styleEvent = this.createEvent(event);
			fireCustomEvent("BX.Landing.Block:updateStyle", [styleEvent]);
		},


		/**
		 * Gets content edit forms
		 * @param {BX.Landing.Collection.NodeCollection} [nodes = this.nodes]
		 * @param {?string} [formName]
		 * @param {boolean} [nodesOnly]
		 * @param {boolean} [showAll]
		 * @return {BX.Landing.UI.Collection.FormCollection}
		 */
		getEditForms: function(nodes, formName, nodesOnly, showAll)
		{
			var forms = new FormCollection();
			var nodesSelectors = Object.keys(this.manifest.nodes);
			var sortedBlockNodes = new NodeCollection();
			var blockNodes = nodes;

			if (this.cards.length && !showAll)
			{
				blockNodes = nodes.notMatches(this.getCardsSelector());
			}

			nodesSelectors.forEach(function(selector) {
				blockNodes.matches(selector).getVisible().forEach(function(node) {
					sortedBlockNodes.push(node);
				});
			}, this);

			if (sortedBlockNodes.length)
			{
				var blockForm = new BaseForm({
					title: formName || BX.message("BLOCK_HEADER"),
					description: this.manifest.block.formDescription,
					type: "content"
				});

				sortedBlockNodes.forEach(function(node) {
					if (node.manifest.allowFormEdit !== false)
					{
						blockForm.addField(node.getField());
					}
				});

				forms.add(blockForm);
			}


			// Make attributes form
			var keys = Object.keys(this.manifest.attrs);

			if (keys.length && !nodesOnly)
			{
				var attrsForm = new BaseForm({
					id: "attr",
					type: "attrs",
					title: BX.message("BLOCK_SETTINGS"),
					description: this.manifest.block.attrsFormDescription
				});
				forms.add(attrsForm);

				keys.forEach(function(selector) {
					var attr = this.manifest.attrs[selector];

					if (!attr.hidden)
					{
						attr = !isArray(attr) ? [attr] : attr;

						attr.forEach(function(options) {
							if (options.hidden)
							{
								return;
							}

							if (isString(options.type))
							{
								attrsForm.addField(
									this.createAttributeField(options, options.selector || selector)
								);
								return;
							}

							if (isString(options.name) && options.attrs)
							{
								forms.add(
									this.createAdditionalForm({
										form: BaseForm,
										selector: selector,
										group: options,
										onChange: (function() {})
									})
								);
							}
						}, this);
					}
				}, this);

				if (attrsForm.fields.length === 0)
				{
					forms.remove(attrsForm);
				}
			}

			if (!nodesOnly)
			{
				var cardsSelectors = Object.keys(this.manifest.cards);

				cardsSelectors.forEach(function(currentCardSelector) {
					var cardBySelector = this.cards.filter(function(card) {
						return card.selector.split("@")[0] === currentCardSelector;
					});

					if (cardBySelector.length)
					{
						cardBySelector.sort(function(a, b) {
							return a.sortIndex - b.sortIndex;
						});

						var groupLabel = this.manifest.cards[currentCardSelector]['group_label'];
						var cardsForm = new CardsForm({
							title: groupLabel || BX.message("LANDING_CARDS_FROM_TITLE"),
							code: currentCardSelector.split("@")[0],
							presets: cardBySelector[0].manifest.presets,
							sync: cardBySelector[0].manifest.sync,
							description: cardBySelector[0].manifest.formDescription,
							forms: forms
						});

						// Makes card forms
						cardBySelector.forEach(function(card) {
							var cardForm = new CardForm({
								label: card.getLabel() || card.getName(),
								labelBindings: card.manifest.label,
								selector: card.selector,
								preset: card.preset
							});
							var sortedCardNodes = new NodeCollection();

							var cardNodes = nodes.filter(function(node) {
								return card.node.contains(node.node)
							});

							if (cardNodes.length)
							{
								nodesSelectors.forEach(function(selector) {
									var matches = cardNodes.matches(selector);
									matches.forEach(sortedCardNodes.add, sortedCardNodes);
								}, this);

								sortedCardNodes.forEach(function(node) {
									if (node.manifest.allowFormEdit !== false)
									{
										cardForm.addField(node.getField());
									}
								});

								var additional = this.manifest.cards[currentCardSelector].additional;
								if (isPlainObject(additional))
								{
									if (isArray(additional.attrs))
									{
										additional.attrs.forEach(function(attr) {
											var attrField = this.createAttributeField(attr, card.selector, (function() {}));
											attrField.type = "attr";
											cardForm.addField(attrField);
										}, this);
									}
								}

								if (this.tmpContent.contains(card.node))
								{
									cardsForm.addPresetForm(cardForm);
								}
								else
								{
									cardsForm.addChildForm(cardForm);
								}
							}
						}, this);

						if (cardsForm.childForms.length)
						{
							forms.add(cardsForm);
						}
					}
				}, this);

				var blockSettingsForm = new BaseForm({
					title: BX.message("BLOCK_SETTINGS"),
					type: "settings"
				});

				var fieldFactory = this.createFieldFactory("!" + this.selector);
				var errorMessage = null;
				var baseUrl = BX.Landing.Main.getInstance().options.url;

				if (baseUrl[0] === "/")
				{
					baseUrl = top.location.origin + baseUrl;
				}

				var previewText = join(baseUrl, "#", (this.anchor || this.node.id));
				var anchorField = fieldFactory.create({
					type: "text",
					name: BX.message("BLOCK_SETTINGS_ANCHOR_FIELD"),
					description: "<span class='landing-ui-anchor-preview'>"+previewText+"</span>",
					attribute: "id",
					value: this.anchor || this.node.id,
					onInput: function() {
						var preview = anchorField.layout.querySelector(".landing-ui-anchor-preview");

						if (preview)
						{
							preview.innerHTML = join(baseUrl, "#", encodeDataValue(anchorField.getValue()));
						}

						this.anchor = anchorField.getValue();

						if (errorMessage)
						{
							remove(errorMessage);
						}

						if (this.node.id !== anchorField.getValue() &&
							document.getElementById(anchorField.getValue()))
						{
							errorMessage = BX.Landing.UI.Field.BaseField.createDescription(
								BX.message("BLOCK_SETTINGS_ANCHOR_FIELD_VALIDATE_ERROR")
							);

							addClass(errorMessage, "landing-ui-error");

							append(errorMessage, anchorField.layout);
						}

						if (!isValidElementId(anchorField.getValue()))
						{
							errorMessage = BX.Landing.UI.Field.BaseField.createDescription(
								BX.message("BLOCK_SETTINGS_ANCHOR_FIELD_VALIDATE_INVALID_ID")
							);

							addClass(errorMessage, "landing-ui-error");

							append(errorMessage, anchorField.layout);
						}
					}.bind(this)
				});

				blockSettingsForm.addField(anchorField);

				forms.add(blockSettingsForm);
			}

			return forms;
		},


		/**
		 * @return {boolean}
		 */
		isLastBlockInArea: function()
		{
			return this.parent.querySelectorAll(".block-wrapper").length < 2;
		},


		/**
		 * Handles block remove event
		 */
		onBlockRemove: function()
		{
			this.adjustSortButtonsState();
		},


		/**
		 * Handles block init event
		 */
		onBlockInit: function()
		{
			this.adjustSortButtonsState();
		},


		/**
		 * Adjusts sort buttons state (disable/enable)
		 */
		adjustSortButtonsState: function()
		{
			var actionPanel = this.panels.get("block_action");

			if (actionPanel)
			{
				if (this.isLastBlockInArea())
				{
					actionPanel.buttons.get("up").disable();
					actionPanel.buttons.get("down").disable();
				}
				else
				{
					actionPanel.buttons.get("up").enable();
					actionPanel.buttons.get("down").enable();
				}
			}

		}
	};
})();