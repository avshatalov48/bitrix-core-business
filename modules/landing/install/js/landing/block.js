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
	var ActionButton = BX.Landing.UI.Button.ActionButton;
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
	var DynamicFieldsGroup = BX.Landing.UI.Card.DynamicFieldsGroup;

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
		let lp = BX.Landing.Main.getInstance();
		let namespaces = Object.keys(lp.options.style);

		for (let i = 0; i < namespaces.length; i++)
		{
			let namespace = namespaces[i];
			let type = lp.options.style[namespace]["style"][prop];

			if (!type)
			{
				continue;
			}

			type.attrKey = prop;

			if (prop === "background")
			{
				type.items = type.items.concat(lp.options.style[namespace]["style"]["background-overlay"].items);
			}

			return type;
		}

		return null;
	}

	function getAttrsTypeSettings(prop)
	{
		let lp = BX.Landing.Main.getInstance();
		let namespaces = Object.keys(lp.options.attrs);

		for (let i = 0; i < namespaces.length; i++)
		{
			let namespace = namespaces[i];
			let attr = lp.options.attrs[namespace]["attrs"][prop];

			if (!attr)
			{
				continue;
			}

			attr.attrKey = prop;
			return attr;
		}

		return {};
	}

	function isGroup(prop)
	{
		let lp = BX.Landing.Main.getInstance();
		let namespaces = Object.keys(lp.options.style);

		for (let i = 0; i < namespaces.length; i++)
		{
			let namespace = namespaces[i];

			if (!lp.options.style[namespace]["group"])
			{
				continue;
			}

			if (prop in lp.options.style[namespace]["group"])
			{
				return true;
			}
		}

		return false;
	}

	function getGroupTypes(group)
	{
		let lp = BX.Landing.Main.getInstance();
		let namespaces = Object.keys(lp.options.style);

		for (let i = 0; i < namespaces.length; i++)
		{
			let namespace = namespaces[i];

			if (!lp.options.style[namespace]["group"])
			{
				continue;
			}

			if (lp.options.style[namespace]["group"][group])
			{
				return lp.options.style[namespace]["group"][group];
			}
		}

		return [];
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

	var onBlockInitDebounced = BX.debounce(function() {
		BX.Landing.PageObject.getBlocks().forEach(function(block) {
			block.adjustSortButtonsState();
		});
	}, 400);

	onCustomEvent("BX.Landing.Block:init", onBlockInitDebounced);

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
		this.repoId = isNumber(options.repoId) ? options.repoId : null;
		this.active = isBoolean(options.active) ? options.active : true;
		this.allowedByTariff = isBoolean(options.allowedByTariff) ? options.allowedByTariff : true;
		this.manifest = isPlainObject(options.manifest) ? options.manifest : {};
		this.manifest.nodes = isPlainObject(options.manifest.nodes) ? options.manifest.nodes : {};
		this.manifest.cards = isPlainObject(options.manifest.cards) ? options.manifest.cards : {};
		this.manifest.attrs = isPlainObject(options.manifest.attrs) ? options.manifest.attrs : {};
		this.onStyleInputWithDebounce = debounce(this.onStyleInput, 300, this);
		this.changeTimeout = null;
		this.php = options.php;
		this.designed = options.designed;
		this.access = options.access;
		this.anchor = options.anchor;
		this.savedAnchor = options.anchor;
		this.requiredUserActionOptions = options.requiredUserAction;
		this.dynamicParams = options.dynamicParams || {};
		this.sections = options.sections ? options.sections.split(',') : [];

		// Make entities collections
		this.nodes = new NodeCollection();
		this.cards = new CardCollection();
		this.panels = new PanelCollection();
		this.groups = new BaseCollection();
		this.changedNodes = new BaseCollection();
		this.styles = new BaseCollection();
		this.forms = new FormCollection();
		this.menu = [];

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

		// Make manifest read only
		deepFreeze(this.manifest);

		// Apply block state
		this.node.classList[this.active ? "remove" : "add"]("landing-block-disabled");

		// Sets state
		this.state = "ready";

		// Init panels
		this.initPanels();
		this.initStyles();
		this.initMenu();
		this.adjustContextSensitivityStyles();

		var envOptions = BX.Landing.Env.getInstance().getOptions();
		var specialType = envOptions.specialType;
		if (this.isDefaultCrmFormBlock())
		{
			var showOptions = {
				formId: envOptions.formEditorData.formOptions.id,
				formOptions: this.getCrmFormOptions(),
				block: this,
				showWithOptions: true,
			};
			var uri = new BX.Uri(window.top.location.toString());
			if (BX.Text.toBoolean(uri.getQueryParam('formCreated')))
			{
				showOptions.state = 'presets';
			}

			void BX.Landing.UI.Panel.FormSettingsPanel
				.getInstance()
				.show(showOptions);
		}

		BX.Landing.PageObject.getBlocks().push(this);

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

		getBlockNode: function()
		{
			return this.node;
		},

		isAllowedByTariff: function()
		{
			return this.allowedByTariff;
		},

		showRequiredUserAction: function(data)
		{
			let container = this.node;
			if (data.targetNodeSelector)
			{
				container = this.node.querySelector(data.targetNodeSelector);
			}
			container.innerHTML = (
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
								"<a href=\""+data.href+"\" class=\"landing-trusted-link ui-btn "+data.className+"\" target=\""+(data.target ? data.target : '')+"\">"+data.text+"</a>" +
							"</div>"
						) : "") +
					"</div>" +
				"</div>"
			);

			if (data.onClick)
			{
				var button = container.querySelector('.landing-block-user-action .ui-btn');
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

				var isMenuItem = this.menu.some(function(menu) {
					return menu.root.contains(item);
				});

				if (!this.nodes.getByNode(item) && !isChildOfNode && !isMenuItem)
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

					if (columnsSettings === null)
					{
						return;
					}

					needAdjust.forEach(function(selector) {
						var styleNode = this.styles.get(selector);

						if (styleNode)
						{
							styleNode.setIsSelectGroup(true);
							styleNode.setValue("col-lg-12", columnsSettings.items);
							styleNode.unsetIsSelectGroupFlag();
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
			this.disableLinks();
		},

		initMenu: function()
		{
			if (BX.type.isPlainObject(this.manifest.menu))
			{
				this.menu = Object.entries(this.manifest.menu).map(function(entry) {
					var code = entry[0];
					var value = entry[1];

					return new BX.Landing.Menu.Menu({
						code: code,
						root: this.node.querySelector(code),
						manifest: value,
						block: this.id,
					});
				}, this);
			}
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
					showAll: true,
					hideCheckbox: true
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
						text: BX.Landing.Loc.getMessage("ACTION_BUTTON_CREATE"),
						onClick: throttle(this.addBlockAfterThis, 600, this)
					})
				);

				createPanel.show();
				this.addPanel(createPanel);

				if (this.isCrmFormPage())
				{
					var createBeforePanel = new BaseButtonPanel(
						"create_before_action",
						"landing-ui-panel-create-before-action"
					);

					createBeforePanel.addButton(
						new PlusButton("insert_before", {
							text: BX.Landing.Loc.getMessage("ACTION_BUTTON_CREATE"),
							onClick: throttle(this.addBlockBeforeThis, 600, this)
						})
					);

					createBeforePanel.show();
					this.addPanel(createBeforePanel);
				}

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
								BX.Landing.Loc.getMessage("ACTION_BUTTON_CREATE"),
								BX.Landing.Loc.getMessage("LANDING_ADD_BLOCK_TO_MAIN")
							].join(" "));
							break;
						case hasClass(this.parent, "landing-header"):
							addButton.setText([
								BX.Landing.Loc.getMessage("ACTION_BUTTON_CREATE"),
								BX.Landing.Loc.getMessage("LANDING_ADD_BLOCK_TO_HEADER")
							].join(" "));
							break;
						case hasClass(this.parent, "landing-sidebar"):
							addButton.setText([
								BX.Landing.Loc.getMessage("ACTION_BUTTON_CREATE"),
								BX.Landing.Loc.getMessage("LANDING_ADD_BLOCK_TO_SIDEBAR")
							].join(" "));
							break;
						case hasClass(this.parent, "landing-footer"):
							addButton.setText([
								BX.Landing.Loc.getMessage("ACTION_BUTTON_CREATE"),
								BX.Landing.Loc.getMessage("LANDING_ADD_BLOCK_TO_FOOTER")
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
					addButton.setText(BX.Landing.Loc.getMessage("ACTION_BUTTON_CREATE"));

					removeClass(this.parent, "landing-area-highlight");

					areas.forEach(function(area) {
						removeClass(area, "landing-area-fade");
					}, this);
				}
			}
		},

		isInSidebar: function()
		{
			return !!this.node.closest(".landing-sidebar");
		},


		initSidebarActionPanel: function()
		{
			if (this.isInSidebar() && !this.panels.contains("sidebar_actions"))
			{
				var sidebarActionsPanel = new BaseButtonPanel(
					"sidebar_actions",
					"landing-ui-panel-sidebar-actions"
				);

				sidebarActionsPanel.addButton(
					new ActionButton("showSidebarActions", {
						onClick: this.onShowSidebarActionsClick.bind(this),
					})
				);

				this.addPanel(sidebarActionsPanel);
				sidebarActionsPanel.show();
			}
		},

		showFeedbackForm: function() {
			BX.Landing.Main.getInstance().showSliderFeedbackForm({
				blockName: this.manifest.block.name,
				blockCode: this.manifest.code,
				blockSection: this.manifest.block.section,
				landingId: BX.Landing.Main.getInstance().id,
				target: "blockActions"
			});
			if (this.blockActionsMenu)
			{
				this.blockActionsMenu.close();
			}
			if (this.sidebarActionsMenu)
			{
				this.sidebarActionsMenu.close();
			}
		},

		onShowSidebarActionsClick: function(event)
		{
			var bindElement = (
				this.panels.get("sidebar_actions").buttons.get('showSidebarActions')
			);

			if (!this.sidebarActionsMenu)
			{
				this.sidebarActionsMenu = BX.Main.MenuManager.create({
					id: this.id + '_sidebar_actions',
					bindElement: bindElement.layout,
					className: "landing-ui-block-actions-popup",
					angle: {position: "top", offset: 95},
					offsetTop: -6,
					offsetLeft: -26,
					events: {
						onPopupClose: function() {
							this.panels.get("sidebar_actions").buttons.get("showSidebarActions").deactivate();
							removeClass(this.node, "landing-ui-hover");
						}.bind(this)
					},
					items: [
						(function() {
							if (
								(isPlainObject(this.manifest.nodes) || isPlainObject(this.manifest.attrs))
								&& this.isAllowedByTariff()
							)
							{
								return new BX.Main.MenuItem({
									id: "content",
									text: BX.Landing.Loc.getMessage("ACTION_BUTTON_CONTENT"),
									onclick: function() {
										this.onShowContentPanel();
										this.sidebarActionsMenu.close();
									}.bind(this)
								});
							}
						}.bind(this))(),
						(function() {
							if (isPlainObject(this.manifest.style) && this.isAllowedByTariff())
							{
								return new BX.Main.MenuItem({
									id: "style",
									text: BX.Landing.Loc.getMessage("ACTION_BUTTON_STYLE"),
									onclick: function() {
										this.onStyleShow();
										this.sidebarActionsMenu.close();
									}.bind(this),
									className: this.access < ACCESS_V ? "landing-ui-disabled" : ""
								});
							}
						}.bind(this))(),
						(function() {
							if (isPlainObject(this.manifest.style) && this.isAllowedByTariff())
							{
								return new BX.Main.MenuItem({
									id: "designblock",
									text: BX.Landing.Loc.getMessage("LANDING_BLOCKS_ACTIONS_DESIGN_BLOCK"),
									className: !this.isDesignBlockAllowed() ? "landing-ui-disabled" : "",
									onclick: function() {
										this.onDesignerBlockClick();
										this.sidebarActionsMenu.close();
									}.bind(this)
								});
							}
						}.bind(this))(),
						(function() {
							if (this.isAllowedByTariff())
							{
								return new BX.Main.MenuItem({
									delimiter: true,
								});
							}
						}.bind(this))(),
						(function() {
							var allPlacements = BX.Landing.Main.getInstance().options.placements.blocks;

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
									if (typeof BX.Landing.PageObject.getRootWindow().BX.rest !== "undefined" &&
										typeof BX.Landing.PageObject.getRootWindow().BX.rest.AppLayout !== "undefined")
									{
										var codes = ["*", this.manifest.code];
										for (var i = 0, c = codes.length; i < c; i++)
										{
											var MessageInterface = BX.Landing.PageObject.getRootWindow().BX.rest.AppLayout.initializePlacement(
												"LANDING_BLOCK_" + codes[i]
											);
											if (MessageInterface)
											{
												MessageInterface.prototype.refreshBlock = function(params, cb) {
													var block = BX.Landing.PageObject.getBlocks().get(params.id);

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

									return new BX.Main.MenuItem({
										id: "actions",
										text: BX.Landing.Loc.getMessage("ACTION_BUTTON_CONTENT_MORE"),
										items: placementsList.map(function(placement) {
											return new BX.Main.MenuItem({
												id: "placement_" + placement.id + "_" + random(),
												text: encodeDataValue(placement.title),
												onclick: this.onPlacementClick.bind(this, placement)
											})
										}, this),
										className: this.access < ACCESS_V ? "landing-ui-disabled" : ""
									});
								}
							}
						}.bind(this))(),

						new BX.Main.MenuItem({
							id: "down",
							text: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_BLOCK_ACTION_SORT_DOWN"),
							onclick: function() {
								this.moveDown();
								this.sidebarActionsMenu.close();
							}.bind(this)
						}),
						new BX.Main.MenuItem({
							id: "up",
							text: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_BLOCK_ACTION_SORT_UP"),
							onclick: function() {
								this.moveUp();
								this.sidebarActionsMenu.close();
							}.bind(this)
						}),
						new BX.Main.MenuItem({
							delimiter: true,
						}),
						new BX.Main.MenuItem({
							id: "show_hide",
							text: BX.Landing.Loc.getMessage(this.isEnabled() ? "ACTION_BUTTON_HIDE" : "ACTION_BUTTON_SHOW"),
							className: !this.isChangeStateBlockAllowed() ? "landing-ui-disabled" : "",
							onclick: function() {
								this.onStateChange();
								this.sidebarActionsMenu.close();
							}.bind(this)
						}),
						new BX.Main.MenuItem({
							delimiter: true,
						}),
						new BX.Main.MenuItem({
							text: BX.Landing.Loc.getMessage("ACTION_BUTTON_ACTIONS_CUT"),
							className: !this.isRemoveBlockAllowed() ? "landing-ui-disabled" : "",
							onclick: function() {
								BX.Landing.Main.getInstance().onCutBlock.bind(BX.Landing.Main.getInstance(), this)();
								this.sidebarActionsMenu.close();
							}.bind(this)
						}),
						new BX.Main.MenuItem({
							text: BX.Landing.Loc.getMessage("ACTION_BUTTON_ACTIONS_COPY"),
							onclick: function() {
								BX.Landing.Main.getInstance().onCopyBlock.bind(BX.Landing.Main.getInstance(), this)();
								this.sidebarActionsMenu.close();
							}.bind(this)
						}),
						new BX.Main.MenuItem({
							id: "block_paste",
							text: BX.Landing.Loc.getMessage("ACTION_BUTTON_ACTIONS_PASTE"),
							title: window.localStorage.landingBlockName,
							className: this.isPasteBlockAllowed() ? "": "landing-ui-disabled",
							onclick: function() {
								BX.Landing.Main.getInstance().onPasteBlock.bind(BX.Landing.Main.getInstance(), this)();
								this.sidebarActionsMenu.close();
							}.bind(this)
						}),
						new BX.Main.MenuItem({
							delimiter: true,
						}),
						new BX.Main.MenuItem({
							text: BX.Landing.Loc.getMessage("LANDING_BLOCKS_ACTIONS_FEEDBACK_BUTTON"),
							onclick: this.showFeedbackForm.bind(this)
						}),
						new BX.Main.MenuItem({
							text: BX.Landing.Loc.getMessage("LANDING_BLOCKS_ACTIONS_SAVE_BLOCK_BUTTON_MSGVER_1"),
							className: !this.isSaveBlockInLibraryAllowed() ? "landing-ui-disabled" : "",
							onclick: function() {
								this.saveBlock();
								this.sidebarActionsMenu.close();
							}.bind(this)
						}),
						new BX.Main.MenuItem({
							delimiter: true,
						}),
						new BX.Main.MenuItem({
							id: "remove",
							text: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_BLOCK_ACTION_REMOVE"),
							onclick: function() {
								this.deleteBlock();
								this.sidebarActionsMenu.close();
							}.bind(this),
							className: !this.isRemoveBlockAllowed() ? "landing-ui-disabled" : ""
						})
					]
				});
			}

			this.sidebarActionsMenu.show();
			addClass(this.node, "landing-ui-hover");
		},


		/**
		 * Initializes action panels of block
		 * @private
		 */
		lazyInitPanels: function()
		{
			if (this.isInSidebar())
			{
				this.initSidebarActionPanel();
			}

			var allPlacements = BX.Landing.Main.getInstance().options.placements.blocks;

			// Make content actions panel
			if (
				!this.panels.contains("content_actions")
				&& (
					(isPlainObject(this.manifest.nodes) && !isEmpty(this.manifest.nodes))
					|| (isPlainObject(this.manifest.style) && !isEmpty(this.manifest.style))
					|| (isPlainObject(allPlacements) && !isEmpty(allPlacements))
				)
			)
			{
				var contentPanel = new BaseButtonPanel(
					"content_actions",
					"landing-ui-panel-content-action"
				);

				contentPanel.addButton(
					new ActionButton("collapse", {
						html: "<span class='fas fa-caret-right'></span>",
						onClick: this.onCollapseActionPanel.bind(this),
						attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_BLOCK_ACTION_COLLAPSE")},
						separate: true,
					})
				);

				if (this.isAllowedByTariff())
				{
					if (isPlainObject(this.manifest.style))
					{
						contentPanel.addButton(
							new ActionButton("designblock", {
								text: BX.Landing.Loc.getMessage("LANDING_BLOCKS_ACTIONS_DESIGN_BLOCK"),
								onClick: this.onDesignerBlockClick.bind(this),
								disabled: !this.isDesignBlockAllowed(),
								attrs: { title: BX.Landing.Loc.getMessage("LANDING_BLOCKS_ACTIONS_DESIGN_BLOCK") },
								separate: true,
							})
						);

						contentPanel.addButton(
							new ActionButton("style", {
								text: BX.Landing.Loc.getMessage("ACTION_BUTTON_STYLE"),
								onClick: this.onStyleShow.bind(this),
								disabled: !this.isStyleModifyAllowed(),
								attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_BLOCK_DESIGN")},
								separate: true,
							})
						);
					}

					if (isPlainObject(this.manifest.nodes) || isPlainObject(this.manifest.attrs) )
					{
						contentPanel.addButton(
							new ActionButton("content", {
								text: BX.Landing.Loc.getMessage("ACTION_BUTTON_CONTENT"),
								onClick: this.onShowContentPanel.bind(this),
								attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_BLOCK_EDIT")},
								separate: true,
							})
						);
					}
				}
				else {
					contentPanel.addButton(
						new ActionButton("expired", {
							text: BX.Landing.Loc.getMessage("ACTION_BUTTON_EXPIRED"),
							separate: true,
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
								html: BX.Landing.Loc.getMessage("ACTION_BUTTON_CONTENT_MORE"),
								onClick: this.onPlacementButtonClick.bind(this, placementsList),
								separate: true,
							})
						);

						if (typeof BX.Landing.PageObject.getRootWindow().BX.rest !== "undefined" &&
							typeof BX.Landing.PageObject.getRootWindow().BX.rest.AppLayout !== "undefined")
						{
							var codes = ["*", this.manifest.code];
							for (var i = 0, c = codes.length; i < c; i++)
							{
								var MessageInterface = BX.Landing.PageObject.getRootWindow().BX.rest.AppLayout.initializePlacement(
									"LANDING_BLOCK_" + codes[i]
								);
								if (MessageInterface)
								{
									MessageInterface.prototype.refreshBlock = function(params, cb) {
										var block = BX.Landing.PageObject.getBlocks().get(params.id);

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
				}

				if (isPlainObject(this.manifest.style))
				{
					var blockDisplay = new ActionButton("block_display_info", {
						html: "&nbsp;",
						separate: true,
						onClick: this.onStyleShow.bind(this),
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
						html: "!",
						className: "landing-ui-block-restricted-button",
						onClick: this.onRestrictedButtonClick.bind(this),
						separate: true
					});

					bind(restrictedButton.layout, "mouseenter", this.onRestrictedButtonMouseenter.bind(this));
					bind(restrictedButton.layout, "mouseleave", this.onRestrictedButtonMouseleave.bind(this));
					blockPanel.addButton(restrictedButton);
				}


				blockPanel.addButton(
					new ActionButton("down", {
						html: BX.Landing.Loc.getMessage("ACTION_BUTTON_DOWN"),
						onClick: this.moveDown.bind(this),
						attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_BLOCK_ACTION_SORT_DOWN")}
					})
				);

				blockPanel.addButton(
					new ActionButton("up", {
						html: BX.Landing.Loc.getMessage("ACTION_BUTTON_UP"),
						onClick: this.moveUp.bind(this),
						attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_BLOCK_ACTION_SORT_UP")}
					})
				);

				blockPanel.addButton(
					new ActionButton("actions", {
						html: BX.Landing.Loc.getMessage("ACTION_BUTTON_ACTIONS"),
						onClick: this.showBlockActionsMenu.bind(this),
						attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_BLOCK_ACTION_ADDITIONAL_ACTIONS")}
					})
				);

				blockPanel.addButton(
					new ActionButton("remove", {
						html: BX.Landing.Loc.getMessage("ACTION_BUTTON_REMOVE"),
						disabled: !this.isRemoveBlockAllowed(),
						onClick: this.deleteBlock.bind(this),
						attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_BLOCK_ACTION_REMOVE")}
					})
				);

				blockPanel.addButton(
					new ActionButton("collapse", {
						html: "<span class='fas fa-caret-right'></span>",
						onClick: this.onCollapseActionPanel.bind(this),
						attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_BLOCK_ACTION_COLLAPSE")},
						separate: true
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
					return new BX.Main.MenuItem({
						id: "placement_" + (placement.id || random()) + "_" + random(),
						text: encodeDataValue(placement.title),
						disabled: placement.disabled === true,
						onclick: (typeof placement.onClick === 'function')
							? placement.onClick
							: this.onPlacementClick.bind(this, placement)
					})
				}, this);

				this.blockPlacementsActionsMenu = new BX.PopupMenuWindow({
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

		onDesignerBlockClick: function()
		{
			// get actual block content before designer edit
			var oldContent = null;
			BX.Landing.Backend.getInstance()
				.action("Block::getContent", {
					block: this.id,
					lid: this.lid,
					siteId: this.siteId,
					editMode: 1
				})
				.then(function(response) {
					oldContent = response.content;
				});

			// open slider with designer
			var envOptions = BX.Landing.Env.getInstance().getOptions();
			var sliderUrl = envOptions.params.sef_url["design_block"]
				.replace("__block_id__", this.id)
				.replace("__site_show__", this.siteId)
				.replace("__landing_edit__", this.lid)
				+ "&code=" + this.manifest.code
				+ "&designed=" + (this.designed ? "Y" : "N")
				+ "&deviceCode=" + BX.Landing.Main.getInstance().getDeviceCode();
			BX.SidePanel.Instance.open(
				sliderUrl,
				{
					cacheable: false,
					allowChangeHistory: false,
					requestMethod: "post",
					customLeftBoundary: 40,
					events: {
						onClose: function(event)
						{
							// get actual block content after designer edit
							BX.Landing.Backend.getInstance()
								.action("Block::getContent", {
									block: this.id,
									lid: this.lid,
									siteId: this.siteId,
									editMode: 1
								})
								.then(function(response) {
									var newContent = response.content;
									if (oldContent !== newContent)
									{
										BX.Landing.History.getInstance().push();
										this.reload().then(function()
										{
											fireCustomEvent("BX.Landing.Block:onDesignerBlockSave", [this.id]);
										}.bind(this));
										// analytic label on close
										var metrika = new BX.Landing.Metrika(true);
										metrika.sendLabel(
											null,
											"designerBlock",
											"close" +
											"&designed=" + (this.designed ? "Y" : "N") +
											"&code=" + this.manifest.code
										);
									}
								}.bind(this));
						}.bind(this)
					}
				}
			);

			if (this.blockPlacementsActionsMenu)
			{
				this.blockPlacementsActionsMenu.close();
			}
		},

		isDesignBlockAllowed: function()
		{
			return !(this.access < ACCESS_W || this.php || (this.isCrmFormPage() && this.isCrmFormBlock()));
		},

		isStyleModifyAllowed:function()
		{
			return !(this.access < ACCESS_V || isEmpty(this.manifest.style));
		},

		isEditBlockAllowed: function()
		{
			return this.access >= ACCESS_W;
		},

		isRemoveBlockAllowed: function()
		{
			return !(this.access < ACCESS_X || (this.isCrmFormBlock() && this.isDefaultCrmFormBlock()));
		},

		isPasteBlockAllowed: function()
		{
			return window.localStorage.landingBlockId && !this.isDefaultCrmFormBlock();
		},

		isSaveBlockInLibraryAllowed: function()
		{
			return !this.isDefaultCrmFormBlock();
		},

		isChangeStateBlockAllowed: function()
		{
			return !((this.access < ACCESS_W) || this.isDefaultCrmFormBlock());
		},

		saveBlock: function()
		{
			BX.Landing.Main.getInstance().showSaveBlock(this);
		},

		onRestrictedButtonMouseenter: function(event)
		{
			clearTimeout(this.displayBlockTimer);
			this.displayBlockTimer = setTimeout(function(target) {
				BX.Landing.UI.Tool.Suggest.getInstance().show(target, {
					description: BX.Landing.Loc.getMessage("LANDING_BLOCK_RESTRICTED_TEXT")
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
							html: BX.Landing.Loc.getMessage("LANDING_BLOCK_DISABLED_ON_DESKTOP_NAME_2")
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
								create("p", {html: BX.Landing.Loc.getMessage("LANDING_BLOCK_HIDDEN_ON_"+(mod ? mod.toUpperCase() : ""))})
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
			var menu = (this.blockActionsMenu || this.sidebarActionsMenu);
			if (menu)
			{
				var item = menu.getMenuItem("block_paste");

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

				this.blockActionsMenu = new BX.PopupMenuWindow({
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
						}.bind(this),
						onPopupShow: function() {
							BX.Event.EventEmitter.emit('BX.Landing.PopupMenuWindow:onShow');
						}.bind(this)
					},
					items: [
						new BX.Main.MenuItem({
							id: "show_hide",
							text: BX.Landing.Loc.getMessage(this.isEnabled() ? "ACTION_BUTTON_HIDE" : "ACTION_BUTTON_SHOW"),
							className: !this.isChangeStateBlockAllowed() ? "landing-ui-disabled" : "",
							onclick: function() {
								this.onStateChange();
								this.blockActionsMenu.close();
							}.bind(this)
						}),
						new BX.Main.MenuItem({
							text: BX.Landing.Loc.getMessage("ACTION_BUTTON_ACTIONS_CUT"),
							className: !this.isRemoveBlockAllowed() ? "landing-ui-disabled" : "",
							onclick: function() {
								landing.onCutBlock.bind(landing, this)();
								this.blockActionsMenu.close();
							}.bind(this)
						}),
						new BX.Main.MenuItem({
							text: BX.Landing.Loc.getMessage("ACTION_BUTTON_ACTIONS_COPY"),
							className: this.isDefaultCrmFormBlock() ? "landing-ui-disabled" : "",
							onclick: function() {
								landing.onCopyBlock.bind(landing, this)();
								this.blockActionsMenu.close();
							}.bind(this)
						}),
						new BX.Main.MenuItem({
							id: "block_paste",
							text: BX.Landing.Loc.getMessage("ACTION_BUTTON_ACTIONS_PASTE"),
							title: window.localStorage.landingBlockName,
							className: this.isPasteBlockAllowed() ? "": "landing-ui-disabled",
							onclick: function() {
								landing.onPasteBlock.bind(landing, this)();
								this.blockActionsMenu.close();
							}.bind(this)
						}),
						new BX.Main.MenuItem({
							text: BX.Landing.Loc.getMessage("LANDING_BLOCKS_ACTIONS_FEEDBACK_BUTTON"),
							onclick: this.showFeedbackForm.bind(this)
						}),
						new BX.Main.MenuItem({
							delimiter: true,
						}),
						new BX.Main.MenuItem({
							text: BX.Landing.Loc.getMessage("LANDING_BLOCKS_ACTIONS_SAVE_BLOCK_BUTTON_MSGVER_1"),
							className: !this.isSaveBlockInLibraryAllowed() ? "landing-ui-disabled" : "",
							onclick: function() {
								this.saveBlock();
								this.blockActionsMenu.close();
							}.bind(this)
						}),
					]
				});
			}

			addClass(this.node, "landing-ui-hover");
			this.blockActionsMenu.show();
		},


		/**
		 * Moves the block up one position
		 * @param {boolean} [preventHistory = false] - Add this action to history or not. By default - add
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
						BX.Landing.Backend.getInstance()
							.action(
								"Landing::upBlock",
								{block: this.id, lid: this.lid, siteId: this.siteId},
								{code: this.manifest.code}
							)
							.then(() => {
								BX.Landing.History.getInstance().push();
							});
					}
				}.bind(this));
			}
		},


		/**
		 * Moves the block down one position
		 * @param {boolean} [preventHistory = false] - Add this action to history or not. By default - add
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
						BX.Landing.Backend.getInstance()
							.action(
								"Landing::downBlock",
								{block: this.id, lid: this.lid, siteId: this.siteId},
								{code: this.manifest.code}
							)
							.then(() => {
								BX.Landing.History.getInstance().push();
							});
					}
				}.bind(this));
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
					if (panel.id === 'content_edit' && window.parent)
					{
						let topWindow = BX.Landing.PageObject.getRootWindow();
						append(panel.layout, topWindow.document.body);
					}
					else
					{
						append(panel.layout, this.node);
					}
				}
				else
				{
					insertBefore(panel.layout, target);
				}
			}
		},

		/**
		 *
		 * @return {{code: string, id: string, type: string}|null}
		 */
		getBlockFormId: function()
		{
			var formScriptNode = this.node.querySelector('script[data-b24-form]');
			if (BX.Type.isDomNode(formScriptNode))
			{
				var formId = BX.Dom.attr(formScriptNode, 'data-b24-form');
				if (BX.Type.isStringFilled(formId))
				{
					var parsedFormId = formId.split('/');
					if (BX.Type.isArray(parsedFormId) && parsedFormId.length === 3)
					{
						var instanceId = '';
						var formUid = BX.Dom.attr(
							formScriptNode.previousSibling.firstChild,
							'id'
						);

						if (formUid)
						{
							instanceId = formUid.replace('b24-', '');
						}

						return {
							id: parsedFormId[1],
							type: parsedFormId[0],
							code: parsedFormId[2],
							instanceId: instanceId
						};
					}
				}
			}

			formScriptNode = this.node.querySelector('[data-b24form]');
			if (BX.Type.isDomNode(formScriptNode))
			{
				formId = BX.Dom.attr(formScriptNode, 'data-b24form');
				if (BX.Type.isStringFilled(formId))
				{
					parsedFormId = formId.split('|');
					if (BX.Type.isArray(parsedFormId) && parsedFormId.length === 3)
					{
						instanceId = '';
						formUid = BX.Dom.attr(
							formScriptNode.querySelector('.b24-form > div[id]'),
							'id'
						);

						if (formUid)
						{
							instanceId = formUid.replace('b24-', '');
						}

						return {
							id: parsedFormId[0],
							type: parsedFormId[2] || 'inline',
							code: parsedFormId[1],
							instanceId: instanceId
						};
					}
				}
			}

			return null;
		},

		getCrmFormOptions: function()
		{
			var formNode = this.node.querySelector('[data-b24form-use-style]');
			var useAllowed = BX.Dom.attr(formNode, 'data-b24form-use-style');
			var primaryMatcher = /--primary([\da-fA-F]{2})/;

			if (BX.Type.isDomNode(formNode) && BX.Text.toBoolean(useAllowed))
			{
				var designOptions = BX.Dom.attr(formNode, 'data-b24form-design');
				if (BX.Type.isPlainObject(designOptions))
				{
					var primaryColor = BX.Dom.style(document.documentElement, '--primary').trim();
					Object.entries(designOptions.color).forEach(function(entry) {
						if (
							entry[1] === '--primary'
							|| entry[1].match(primaryMatcher) !== null
						)
						{
							designOptions.color[entry[0]] = entry[1].replace('--primary', primaryColor);
						}
					});

					return {
						data: {
							design: designOptions,
						}
					};
				}
			}

			return {};
		},

		isCrmFormPage: function()
		{
			return BX.Landing.Env.getInstance().getOptions().specialType === 'crm_forms';
		},

		isCrmFormBlock: function()
		{
			return this.isCrmFormPage() && BX.Dom.attr(this.node, 'data-subtype') === 'form';
		},

		isDefaultCrmFormBlock: function()
		{
			return BX.Dom.hasClass(this.node, 'block-66-90-form-new-default');
		},

		/**
		 * Handles show panel event
		 * @private
		 */
		onShowContentPanel: function()
		{
			var formId = this.getBlockFormId();
			var type = BX.Text.capitalize(
				BX.Landing.Env.getInstance().getOptions().params.type
			);
			if (
				BX.Type.isPlainObject(formId)
				&& type !== 'SMN'
			)
			{
				var rootWindow = BX.Landing.PageObject.getRootWindow();
				void (function() {
						if (BX.Landing.UI.Panel.FormSettingsPanel)
						{
							return Promise.resolve([
								rootWindow.BX.Landing.UI.Panel,
								BX.Landing.UI.Panel
							]);
						}

						return Promise
							.all([
								rootWindow.BX.Runtime
									.loadExtension('landing.ui.panel.formsettingspanel'),
								BX.Runtime
									.loadExtension('landing.ui.panel.formsettingspanel')
							]);
					})()
					.then(function(result) {
						var FormSettingsPanel = result[1].FormSettingsPanel;
						if (FormSettingsPanel)
						{
							return FormSettingsPanel
								.getInstance()
								.show({
									formId: formId.id,
									instanceId: formId.instanceId,
									formOptions: this.getCrmFormOptions(),
									block: this,
								});
						}
					}.bind(this));
			}
			else
			{
				this.showContentPanel();
			}

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

			let menu = (this.blockActionsMenu || this.sidebarActionsMenu);
			if (menu)
			{
				setTextContent(menu.getMenuItem("show_hide").getLayout().text, BX.Landing.Loc.getMessage("ACTION_BUTTON_HIDE"));
			}

			fireCustomEvent("BX.Landing.Block:changeState", [this.id, true]);

			BX.Landing.Backend.getInstance().action(
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

			let menu = (this.blockActionsMenu || this.sidebarActionsMenu);
			if (menu)
			{
				setTextContent(menu.getMenuItem("show_hide").getLayout().text, BX.Landing.Loc.getMessage("ACTION_BUTTON_SHOW"));
			}

			fireCustomEvent("BX.Landing.Block:changeState", [this.id, false]);

			BX.Landing.Backend.getInstance().action(
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

						onCustomEvent(labelNode.getField(), "change", function(value) {
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

						onCustomEvent(labelNode.getField(), "change", function(value) {
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

						onCustomEvent(labelNode.getField(), "change", function(value) {
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

						onCustomEvent(labelNode.getField(), "change", function(value) {
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
			if (this.access < ACCESS_W)
			{
				return;
			}

			this.cards.clear();
			this.forEachCard(function(node, selector, index) {
				var manifest = BX.clone(this.manifest.cards[selector]);
				var cardSelector = join(selector, "@", index);

				if (this.isDynamicCards(selector))
				{
					manifest.allowInlineEdit = false;
				}

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
						attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_CARD_ACTION_CLONE")}
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
						attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_CARD_ACTION_REMOVE")}
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
		 * @param {?boolean} [preventHistory = false]
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

			let clonePromise = Promise.resolve();
			if ((isBoolean(preventHistory) && !preventHistory) || !isBoolean(preventHistory))
			{
				requestData.preventHistory = 0;
				clonePromise = BX.Landing.Backend.getInstance()
					.action("Landing\\Block::cloneCard", requestData, queryParams)
					.then(result => {
						BX.Landing.History.getInstance().push();

						return result;
					});
			}

			return clonePromise
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
					self.initStyles();
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
		 * @param {?boolean} [preventHistory = false]
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

			let removePromise = Promise.resolve();
			if ((isBoolean(preventHistory) && !preventHistory) || !isBoolean(preventHistory))
			{
				requestData.preventHistory = 0;
				removePromise = BX.Landing.Backend.getInstance()
					.action("Landing\\Block::removeCard", requestData, queryParams)
					.then(result => {
						BX.Landing.History.getInstance().push();

						return result;
					});
			}

			return removePromise
				// Before remove
				.then(function() {
					fireCustomEvent("BX.Landing.Block:Card:beforeRemove", [
						self.createEvent({card: card.node})
					]);

					removePanels(card.node);
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
				var cardAction = card.panels.get("cardAction");
				if (cardAction)
				{
					var cardIntoSlider = BX.hasClass(card.node, 'landing-block-card-carousel-element');
					var cardsParent = card.node.closest(".landing-block-node-carousel-container");
					if (!cardIntoSlider || !cardsParent)
					{
						var lastCardInCollection = card.node.parentElement.children.length === 1;
						if (lastCardInCollection)
						{
							cardAction.buttons.get("remove").disable();
						}
						else
						{
							cardAction.buttons.get("remove").enable();
						}
					}
					else
					{
						var cardsAmount = cardsParent.querySelectorAll('.landing-block-card-carousel-element').length;
						if (cardsAmount > 1)
						{
							cardAction.buttons.get("remove").enable();
						}
						else
						{
							cardAction.buttons.get("remove").disable();
						}
					}
				}
			}
		},


		/**
		 * Adds card
		 * @param {{[index]: !int, container: !HTMLElement, content: string, selector: string}} settings
		 * @param {boolean} [preventHistory]
		 * @return {Promise}
		 */
		addCard: function(settings, preventHistory)
		{
			var selector = settings.selector.split("@")[0] + (settings.index > 0 ? "@"+(settings.index-1) : "");

			var requestData = {
				block: this.id,
				content: settings.content,
				selector: selector,
				lid: this.lid,
				siteId: this.siteId,
			};
			var queryParams = {code: this.manifest.code};
			var container = settings.container;
			var card = create("div", {html: settings.content}).firstElementChild;
			var self = this;

			let addPromise = Promise.resolve();
			if ((isBoolean(preventHistory) && !preventHistory) || !isBoolean(preventHistory))
			{
				requestData.preventHistory = 0;
				addPromise = BX.Landing.Backend.getInstance()
					.action("Landing\\Block::addCard", requestData, queryParams)
					.then(result => {
						BX.Landing.History.getInstance().push();

						return result;
					});
			}

			return addPromise
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

					manifest.sections = this.sections;

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

					var isDynamic = this.cards.some(function(currentCard) {
						var cardCode = currentCard.selector.split("@")[0];

						return (
							this.isDynamicCards(cardCode)
							&& currentCard.node.contains(element)
						);
					}, this);

					if (isDynamic)
					{
						manifest.allowInlineEdit = false;
					}
					else
					{
						var isCardNode = this.cards.some(function(currentCard) {
							return currentCard.node.contains(element);
						});

						if (!isCardNode)
						{
							if (this.isDynamic())
							{
								manifest.allowInlineEdit = false;
							}
						}
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
				this.initStyles();

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
			var nodes = !!options && options.nodes ? options.nodes : null;
			var formName = !!options && options.name ? options.name : null;
			var nodesOnly = !!options && options.nodesOnly ? options.nodesOnly : false;
			var showAll = !!options && options.showAll ? options.showAll : false;
			var compactMode = !!options && options.compact;
			var hideCheckbox = !!options && options.hideCheckbox;
			var contentPanel = this.panels.get("content_edit");

			if (!contentPanel)
			{
				contentPanel = new ContentEditPanel("content_edit", {
					title: BX.Landing.Loc.getMessage("LANDING_CONTENT_PANEL_TITLE"),
					subTitle: this.manifest.block.name,
					onSaveHandler: this.onContentSave.bind(this),
					onCancelHandler: this.onContentCancel.bind(this),
				});

				var formId = this.getBlockFormId();
				var type = BX.Text.capitalize(
					BX.Landing.Env.getInstance().getOptions().params.type
				);
				if (
					BX.Type.isPlainObject(formId)
					&& type !== 'SMN'
				)
				{
					var button = new BX.UI.Button({
						text: BX.Landing.Loc.getMessage('LANDING_SHOW_FORM_EDITOR'),
						color: BX.UI.Button.Color.LIGHT_BORDER,
						round: true,
						className: 'landing-ui-panel-top-button',
						onclick: function() {
							contentPanel.hide()
								.then(function() {
									this.onShowContentPanel();
								}.bind(this));
						}.bind(this)
					});

					BX.Dom.style(button.render(), {
						position: 'absolute',
						right: '50px',
					});

					BX.Dom.append(button.render(), contentPanel.header);
				}

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
			this.initCardsLabels();

			var forms = this.getEditForms({
				nodes: nodes,
				formName: formName,
				nodesOnly: nodesOnly,
				showAll: showAll,
				hideCheckbox: hideCheckbox
			});

			forms.forEach(function(form) {
				contentPanel.appendForm(form);
			});

			this.tmpContent.innerHTML = "";
			contentPanel.show();

			setTimeout(function() {
				this.lastBlockState = this.fetchRequestData(contentPanel, true);
			}.bind(this), 300);
		},

		createHistoryEntry: function(newState)
		{
			Promise
				.all([
					this.lastBlockState,
					newState
				])
				.then(function(states) {
					var undoState = states[0];
					var redoState = states[1];

					BX.Landing.History.getInstance().push(
						new BX.Landing.History.Entry({
							block: this.id,
							selector: "#block" + this.id,
							command: "updateBlockState",
							undo: undoState,
							redo: redoState
						})
					);
				}.bind(this));

			return Promise.resolve(clone(newState));
		},

		/**
		 * Updates block's content.
		 * @param {string} content
		 * @param {boolean} [preventHistory = false] - Add this action to history or not. By default - add
		 */
		updateContent: function(content, preventHistory)
		{
			let updatePromise = Promise.resolve();
			if ((isBoolean(preventHistory) && !preventHistory) || !isBoolean(preventHistory))
			{
				updatePromise = BX.Landing.Backend.getInstance().action(
					"Block::updateContent",
					{
						lid: this.lid,
						block: this.id,
						content: content.replaceAll(' style="', ' bxstyle="'),
						preventHistory: 0,
					},
					{code: this.manifest.code}
				);
			}

			const reloadPromise = this.reload();

			return Promise.all([updatePromise, reloadPromise]);
		},

		updateBlockState: function(state, preventHistory)
		{
			if (
				BX.type.isPlainObject(state)
				&& BX.type.isPlainObject(state.dynamicParams)
			)
			{
				this.dynamicParams = clone(state.dynamicParams);
			}
			else
			{
				this.dynamicParams = {};
			}

			Promise.resolve(state)
				.then(function(currentState) {
					return preventHistory ? currentState : this.createHistoryEntry(currentState);
				}.bind(this))
				.then(this.applyMenuChanges.bind(this))
				.then(this.applyContentChanges.bind(this))
				.then(this.applyCardsChanges.bind(this))
				.then(this.applyAttributeChanges.bind(this))
				.then(this.applySettingsChanges.bind(this))
				.then(data => {
					return this.saveChanges.bind(this)(data, preventHistory);
				})
				// Reload only blocks with component
				.then(this.reload.bind(this))
				.catch(console.warn);

			var contentPanel = this.panels.get('content_edit');

			if (contentPanel)
			{
				var contentForms = new FormCollection();

				contentPanel.forms.forEach(function(form) {
					if (form.type !== "attrs")
					{
						contentForms.add(form);

						if (form.childForms && form.childForms.length > 0)
						{
							form.childForms.forEach(function(childForm) {
								contentForms.add(childForm);
							});
						}
					}
				});

				contentForms.fetchFields().forEach(function(field) {
					if (field.tag)
					{
						var node = this.nodes.getBySelector(field.selector);
						if (node)
						{
							node.onChangeTag(field.tag);
						}
					}
				}, this);
			}
		},

		getRestrictedMessage: function()
		{
			return create("div", {
				props: {className: "ui-alert ui-alert-warning"},
				html: BX.Landing.Loc.getMessage("LANDING_BLOCK_RESTRICTED_TEXT"),
				attrs: {style: "margin-bottom: 20px"}
			})
		},


		/**
		 * Handles event on style panel show
		 */
		onStyleShow: function()
		{
			BX.Landing.UI.Panel.EditorPanel.getInstance().hide();

			if (this.isCrmFormPage() && this.isCrmFormBlock())
			{
				var formSelector = Object.entries(this.manifest.style.nodes).reduce(function(acc, item) {
					if (item[1].type === 'crm-form')
					{
						return item[0];
					}

					return acc;
				}, null);

				if (formSelector)
				{
					this.showStylePanel(formSelector);
				}
				else
				{
					this.showStylePanel(this.selector);
				}
			}
			else
			{
				this.showStylePanel(this.selector);
			}
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

				type = this.expandTypeGroups(type).reduce(function(acc, item) {
					if (!acc.includes(item))
					{
						acc.push(item);
					}

					return acc;
				}, []);

				type.forEach(function(type) {
					var typeSettings = getTypeSettings(type);
					if (typeSettings === null)
					{
						return;
					}
					var styleNode = this.styles.get(selector);
					var field = styleFactory.createField({
						block: this,
						styleNode: styleNode,
						selector: !isBlock ? this.makeRelativeSelector(selector) : selector,
						property: typeSettings.property,
						multiple: typeSettings.multiple === true,
						style: type,
						pseudoElement: typeSettings["pseudo-element"],
						pseudoClass: typeSettings["pseudo-class"],
						attrKey: typeSettings.attrKey,
						type: typeSettings.type,
						subtype: typeSettings.subtype,
						title: typeSettings.name,
						items: typeSettings.items,
						help: typeSettings.help,
						onChange: onChange.bind(this),
						onReset: onReset.bind(this)
					});

					// when field changed
					function onChange(value, items, postfix, affect) {
						// false handler by some fields events
						if (value instanceof  BX.Event.BaseEvent)
						{
							return;
						}

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

						const event = this.createEvent({
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

						const data = {node: styleNode.getNode(), data: styleNode.getValue()};
						fireCustomEvent("BX.Landing.Block:updateStyleWithoutDebounce", [
							this.createEvent(data)
						]);
						this.onStyleInputWithDebounce(data, false);
					}

					// when field reset
					function onReset(items, postfix, affect) {
						// todo: add cache for backend
						BX.Landing.Backend.getInstance()
							.action("Landing\\Block::getContentFromRepository", {
								code: this.manifest.code
							})
							.then(function(response) {
								var repo = document.createElement('div');
								repo.id = 'fake';
								repo.innerHTML = response;
								repo.style.display = 'none';
								window.document.body.append(repo);

								var targetNode = null;
								var targetSelector = null;
								if (isBlock)
								{
									targetSelector = '#fake > :first-child';
									targetNode = repo.firstElementChild;
								}
								else
								{
									targetSelector = '#fake ' + selector;
									var index = styleNode.getElementIndex(styleNode.getTargetElement());
									targetNode = repo.querySelectorAll(targetSelector)[index];
								}
								var fakeStyleNode = new BX.Landing.UI.Style({
									iframe: window,
									selector: targetSelector,
									relativeSelector: targetSelector,
									node: targetNode
								});
								initFieldByStyleNode(fakeStyleNode);

								// match new class list
								var resetStyleValue = fakeStyleNode.getValue();
								var resetClasses = [];
								var currStyleValue = styleNode.getValue();
								items.forEach(function(item) {
									if(resetStyleValue.classList.indexOf(item.value) !== -1)
									{
										resetClasses.push(item.value);
									}
									var currIndex = currStyleValue.classList.indexOf(item.value);
									if(currIndex !== -1)
									{
										delete currStyleValue.classList[currIndex];
									}
								});
								resetStyleValue.classList = currStyleValue.classList.concat(resetClasses);
								resetStyleValue.className = resetStyleValue.classList;
								onChange.bind(this)(resetStyleValue, items, postfix, affect);
								repo.remove();
							}.bind(this))
							.catch(function(error){
								// todo: show err panel
								console.error("Error on reset", error);
							});
					}

					// when field init
					function initFieldByStyleNode(styleNode)
					{
						styleNode.setInlineProperty(field.getInlineProperties());
						styleNode.setComputedProperty(field.getComputedProperties());
						styleNode.setPseudoElement(field.getPseudoElement());

						var preventEvent = true;
						var styleValue = styleNode.getValue(true);
						if (field.getInlineProperties().length > 0 || field.getComputedProperties().length > 0)
						{
							field.setValue(styleValue.style, preventEvent);
						}
						else
						{
							styleValue.classList.forEach(className => {
								if (typeSettings.items.some(item => item.value === className))
								{
									// buttons group set value via onFrameLoad
									if (!!field.buttons && field.multiple === true)
									{
										return;
									}
									field.setValue(className, preventEvent);
								}
							});
						}
					}
					initFieldByStyleNode(styleNode);

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
		 * @param {boolean} [preventHistory = false] - Add this action to history or not. By default - add
		 */
		saveStyles: function(preventHistory)
		{
			const styles = this.styles.fetchChanges();

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

				if ((isBoolean(preventHistory) && !preventHistory) || !isBoolean(preventHistory))
				{
					const post = styles.fetchValues();
					BX.Landing.Backend.getInstance()
						.action(
							"Landing\\Block::updateStyles",
							{block: this.id, data: post, lid: this.lid, siteId: this.siteId, preventHistory: 0},
							{code: this.manifest.code},
						)
						.then(() => {
							BX.Landing.History.getInstance().push();
						});
				}
			}
		},


		/**
		 * Shows style editor panel
		 */
		showStylePanel: function(selector)
		{
			var FormSettingsPanel = BX.Reflection.getClass('BX.Landing.UI.Panel.FormSettingsPanel');
			var formMode = (
				FormSettingsPanel
				&& FormSettingsPanel.getInstance().isShown()
				|| BX.Landing.Main.getInstance().isControlsExternal()
			);
			var isBlock = this.isBlockSelector(selector);
			var options = this.getStyleOptions(selector);
			this.isMultiselection = this.content.querySelectorAll(selector).length > 1;

			BX.Landing.PageObject.getInstance().design()
				.then(function(stylePanel) {
					stylePanel.clearContent();

					if (options.type === 'crm-form')
					{
						var rootWindow = BX.Landing.PageObject.getRootWindow();

						return Promise
							.all([
								rootWindow.BX.Runtime.loadExtension('landing.formstyleadapter'),
								BX.Runtime.loadExtension('landing.formstyleadapter')
							])
							.then(function(result) {
								var FormStyleAdapter = result[1].FormStyleAdapter;
								var formStyleAdapter = new FormStyleAdapter({
									formId: this.getBlockFormId().id,
									instanceId: this.getBlockFormId().instanceId,
									currentBlock: this,
								});

								return Promise.all([
									stylePanel.show(formMode),
									formStyleAdapter.load()
								]);
							}.bind(this))
							.catch(function(error) {
								console.log(error);
							});
					}

					return stylePanel
						.show(formMode)
						.then(function(result) {
							return [result];
						});
				}.bind(this))
				.then(function(result) {
					var stylePanel = result[0];
					var formStyleAdapter = result[1];

					stylePanel.prepareFooter(this.isMultiselection);

					if (formStyleAdapter)
					{
						stylePanel.appendForm(formStyleAdapter.getStyleForm());
						return;
					}

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
								attrsType: options.additional.attrsType,
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
				.catch(function(error) {
					if (BX.Type.isArrayFilled(error))
					{
						var accessDeniedCode = 510;
						var isAccessDenied = error.some(function(error) {
							return String(error.code) === String(accessDeniedCode);
						});

						if (isAccessDenied)
						{
							BX.Dom.append(
								this.getAccessMessage(),
								BX.Landing.UI.Panel.StylePanel.getInstance().content
							);
						}
					}
				}.bind(this));
		},

		getAccessMessage: function()
		{
			if (!this.accessMessage)
			{
				this.accessMessage = BX.create({
					tag: 'div',
					props: {className: 'landing-ui-access-error-message'},
					children: [
						BX.create({
							tag: 'div',
							props: {className: 'landing-ui-access-error-message-text'},
							text: BX.Landing.Loc.getMessage('LANDING_CRM_ACCESS_ERROR_MESSAGE')
						})
					]
				})
			}

			return this.accessMessage;
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

			var attrsSet = [];
			if (!BX.Type.isUndefined(options.group.attrs))
			{
				attrsSet = options.group.attrs;
			}
			else
			{
				options.attrsType.forEach((atr) => {
					let attrSetItem = getAttrsTypeSettings(atr);
					if (attrSetItem)
					{
						attrsSet.push(attrSetItem);
					}
				})
			}
			attrsSet.forEach(function(attrOptions) {
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

			BX.Event.EventEmitter.subscribe('BX.Landing.UI.Form.StyleForm:attributeChange', (event) => {
				var eventData = event.data;
				this.prepareAttributeValue(eventData, form);
			});
			return form;
		},

		prepareAttributeValue: function(eventData, form) {
			var dependencySet = eventData.data.dependency;
			if (dependencySet)
			{
				dependencySet.forEach((dependency) => {
					var value = eventData.getValue();
					var index = dependency['conditions'].indexOf(value);
					if (index >= 0)
					{
						form.fields.forEach((field) => {
							if (field.attribute === dependency['attribute'])
							{
								//action 'changeValue'
								if (dependency['action'] === 'changeValue')
								{
									var value = field.getValue();
									var index = dependency['attributeCurrentValues'].indexOf(value);
									if (index >= 0)
									{
										field.setValue(dependency['attributeNewValue'], true);
										this.onAttributeChange(field);
									}
								}
								//action 'hideSetting' need to do
							}
						})
					}
				})
			}
		},

		prepareBlockOptions: function(options)
		{
			options = isPlainObject(options) ? options : {};
			options = clone(options);
			options.name = BX.Landing.Loc.getMessage("BLOCK_STYLE_OPTIONS");

			if (!isPlainObject(options.type) && !isString(options.type) && !isArray(options.type))
			{
				options.type = [
					"display",
					"background",
					"padding-top",
					"padding-bottom",
					"padding-left",
					"padding-right",
					"margin-top"
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

				if (BX.Type.isNil(value))
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
			BX.Event.EventEmitter.emit('BX.Landing.UI.Form.StyleForm:attributeChange', field);
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

			requestData[selector] = requestData[selector] || {};
			requestData[selector]["attrs"] = requestData[selector]["attrs"] || {};
			if(BX.Type.isArray(field.attribute))
			{
				field.attribute.forEach(function(attr){
					var attrData = attr.replace('data-', '');
					var itemValue = value[attrData];
					if(itemValue !== undefined)
					{
						try {
							itemValue = encodeDataValue(itemValue);
						} catch(e) {
							itemValue = field.getValue()[attrData];
						}
						requestData[selector]["attrs"][attr] = itemValue;
					}
				});
			}
			else
			{
				try {
					value = encodeDataValue(value);
				} catch(e) {
					value = field.getValue();
				}
				requestData[selector]["attrs"][field.attribute] = value;
			}
			return requestData;
		},


		appendMenuValue: function(requestData, menu)
		{
			requestData[menu.code] = menu.serialize();
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
			void style(button.loader.layout.querySelector(".main-ui-loader-svg"), {
				"margin-top": "-10px"
			});

			BX.Landing.UI.Panel.EditorPanel.getInstance().hide();

			if (this.blockActionsMenu)
			{
				BX.Main.MenuManager.destroy(this.blockActionsMenu.id);
			}

			if (this.sidebarActionsMenu)
			{
				BX.Main.MenuManager.destroy(this.sidebarActionsMenu.id);
			}

			if (String(window.localStorage.getItem("landingBlockId")) === String(this.id))
			{
				window.localStorage.removeItem("landingBlockId");
			}

			let removePromise = Promise.resolve();
			if ((isBoolean(preventHistory) && !preventHistory) || !isBoolean(preventHistory))
			{
				removePromise = BX.Landing.Backend.getInstance()
					.action(
						"Landing::markDeletedBlock",
						{block: this.id, lid: this.lid, siteId: this.siteId, preventHistory: 0},
						{code: this.manifest.code}
					)
					.then(result => {
						// Change history steps
						BX.Landing.History.getInstance().push();

						return result;
					});
			}
			removePromise
				.then(() => {
					button.loader.hide();
					removeClass(button.text, "landing-ui-hide-icon");

					var event = this.createEvent();
					fireCustomEvent("BX.Landing.Block:remove", [event]);

					slice(this.node.querySelectorAll(".landing-ui-panel")).forEach(remove);

					BX.Landing.PageObject.getBlocks().remove(this);
					remove(this.node);
					fireCustomEvent("Landing.Block:onAfterDelete", [this]);
					fireCustomEvent("BX.Landing.Block:afterRemove", [event]);
				}, () => {
					button.loader.hide();
					removeClass(button.text, "landing-ui-hide-icon");
				});
		},

		getFormEditorAddBlockTour: function()
		{
			var rootWindow = BX.Landing.PageObject.getRootWindow();
			return new rootWindow.BX.UI.Tour.Guide({
				steps: [
					{
						target: '[data-id="save_settings"]',
						title: BX.Landing.Loc.getMessage('LANDING_FORM_EDITOR_ADD_BLOCK_TOUR_STEP_1_TITLE'),
						text: BX.Landing.Loc.getMessage('LANDING_FORM_EDITOR_ADD_BLOCK_TOUR_STEP_1_TEXT'),
					},
				],
			});
		},

		/**
		 * Shows blocks list panel
		 */
		addBlockAfterThis: function()
		{
			var formSettingsPanel =
				(BX.Landing.UI && BX.Landing.UI.Panel && BX.Landing.UI.Panel.FormSettingsPanel)
					? BX.Landing.UI.Panel.FormSettingsPanel.getInstance()
					: null;
			if (
				this.isCrmFormPage()
				&& formSettingsPanel
				&& formSettingsPanel.isShown()
			)
			{
				if (!formSettingsPanel.isChanged())
				{
					formSettingsPanel
						.hide()
						.then(function() {
							BX.Landing.Main.getInstance().showBlocksPanel(this, null, null, true);
						}.bind(this));
				}
				else
				{
					this.getFormEditorAddBlockTour().start();
				}
			}
			else
			{
				BX.Landing.Main.getInstance().showBlocksPanel(this);
			}
		},

		addBlockBeforeThis: function()
		{
			var formSettingsPanel = BX.Landing.UI.Panel.FormSettingsPanel.getInstance();
			if (
				this.isCrmFormPage()
				&& formSettingsPanel.isShown()
			)
			{
				if (!formSettingsPanel.isChanged())
				{
					formSettingsPanel
						.hide()
						.then(function() {
							BX.Landing.Main.getInstance().showBlocksPanel(this, null, null, true);
						}.bind(this));
				}
				else
				{
					this.getFormEditorAddBlockTour().start();
				}
			}
			else
			{
				BX.Landing.Main.getInstance().showBlocksPanel(this, null, null, true);
			}
		},

		getFormEditorDesignTour: function()
		{
			var rootWindow = BX.Landing.PageObject.getRootWindow();
			return new rootWindow.BX.UI.Tour.Guide({
				steps: [
					{
						target: '[data-id="save_settings"]',
						title: BX.Landing.Loc.getMessage('LANDING_FORM_EDITOR_FORM_DESIGN_TOUR_STEP_1_TITLE'),
						text: BX.Landing.Loc.getMessage('LANDING_FORM_EDITOR_FORM_DESIGN_TOUR_STEP_1_TEXT'),
					},
				],
			});
		},

		onFormDesignClick: function()
		{
			var formSelector = Object.entries(this.manifest.style.nodes).reduce(function(acc, item) {
				if (item[1].type === 'crm-form')
				{
					return item[0];
				}

				return acc;
			}, null);

			if (formSelector)
			{
				this.showStylePanel(formSelector);
			}
			else
			{
				this.showStylePanel(this.selector);
			}
		},


		/**
		 * Handles node content change event
		 * @param {BX.Landing.Block.Node} node
		 * @param {?boolean} [preventHistory = false]
		 */
		onNodeChange: function(node, preventHistory)
		{
			const event = this.createEvent({node: node.node});
			fireCustomEvent("BX.Landing.Block:Node:update", [event]);

			if (!node.isSavePrevented())
			{
				clearTimeout(this.changeTimeout);
				this.changedNodes.add(node);

				this.changeTimeout = setTimeout(() => {
					if ((isBoolean(preventHistory) && !preventHistory) || !isBoolean(preventHistory))
					{
						BX.Landing.Backend.getInstance()
							.action(
								"Landing\\Block::updateNodes",
								{
									block: this.id,
									data: this.changedNodes.fetchValues(),
									additional: this.changedNodes.fetchAdditionalValues(),
									lid: this.lid,
									siteId: this.siteId,
									preventHistory: 0,
								},
								{code: this.manifest.code}
							);
					}
					this.changedNodes.clear();
				}, 300);
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

				if (selector === "dynamicState")
				{
					return false;
				}

				if (
					BX.type.isPlainObject(this.manifest.menu)
					&& selector in this.manifest.menu
				)
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
		 * Check if data contains attributes, that require block reload
		 * @param {object} data
		 * @return {boolean}
		 */
		containsReloadRequireAttributes: function(data)
		{
			if (
				isPlainObject(data)
				&& isPlainObject(this.manifest)
				&& isPlainObject(this.manifest.attrs)
			)
			{
				return Object.keys(this.manifest.attrs).some(function (selector)
				{
					return this.manifest.attrs[selector].some(function (attr)
					{
						if (
							attr.requireReload
							&& isPlainObject(data[selector])
							&& isPlainObject(data[selector].attrs)
							&& data[selector].attrs[attr.attribute]
						)
						{
							return true;
						}
						return false;
					}, this);
				}, this);
			}

			return false;
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

			var valuePromises = [];

			Object.keys(data).forEach(function(selector) {
				if (isNodeSelector(selector))
				{
					var node = this.nodes.getBySelector(selector);

					if (node)
					{
						var valuePromise = node.setValue(data[selector], true, true);
						node.preventSave(false);
						if (valuePromise)
						{
							valuePromises.push(valuePromise);
							valuePromise.then(function() {
								data[selector] = node.getValue();
							});
						}
						else
						{
							data[selector] = node.getValue();
						}
					}
				}
			}, this);

			return Promise
				.all(valuePromises)
				.then(function() {
					return data;
				});
		},

		applyMenuChanges: function(data)
		{
			if (!isPlainObject(data))
			{
				return Promise.reject(
					new TypeError("BX.Landing.Block.applyContentChanges: data isn't object")
				);
			}

			var menuKeys = Object.keys(this.manifest.menu || {});
			if (menuKeys.length > 0)
			{
				menuKeys.forEach(function(code) {
					if (code in data)
					{
						var menu = this.menu.find(function(menuItem) {
							return menuItem.code === code;
						});

						menu.rebuild(data[code]);
					}
				}.bind(this));

				data.forceReload = true;
			}

			this.initMenu();

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

			var nodePromises = [];

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

								if (!isEmpty(presets) && !isEmpty(presets[index]))
								{
									if (
										!oldCards[indexes[index]]
										|| !BX.type.isString(indexes[index])
									)
									{
										source[index].type = "preset";
										source[index].value = presets[index];
										return;
									}
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

											var nodePromise = node.setValue(card[key], true, true) || Promise.resolve();
											node.preventSave(false);
												nodePromise.then(function(selectorKey, mapKey, cardKey) {
													card[join(selectorKey, "@", mapKey)] = node.getValue();

													if (node.manifest.type === "img" || node.manifest.type === "icon")
													{
														card[join(selectorKey, "@", mapKey)]["url"] = encodeDataValue(cardKey["url"]);
													}

													delete card[key];
												}.bind(this, key, map[key], card[key]));

											nodePromises.push(nodePromise);
										}
									}, this);
							}, this);

						Promise
							.all(nodePromises)
							.then(function() {
								// Reinitialize additional entities
								this.initCardsLabels();
								this.initStyles();

								// Remove unnecessary
								delete data.cards[code].presets;
								delete data.cards[code].indexes;
							}.bind(this));
					}, this);

				Promise
					.all(nodePromises)
					.then(function() {
						fireCustomEvent("BX.Landing.Block:Cards:update", [
							this.createEvent()
						]);
					}.bind(this));
			}

			return Promise
				.all(nodePromises)
				.then(function() {
					return Promise.resolve(data);
				});
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
		saveChanges: function(data, preventHistory)
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

				if (data.cardsFirst)
				{
					var oldBatch = batch;
					batch = {};

					if (oldBatch.changeAnchor)
					{
						batch.changeAnchor = oldBatch.changeAnchor;
					}

					if (oldBatch.updateCards)
					{
						batch.updateCards = oldBatch.updateCards;
					}

					if (oldBatch.updateNodes)
					{
						batch.updateNodes = oldBatch.updateNodes;
					}

					delete data.cardsFirst;
				}

				if ((isBoolean(preventHistory) && !preventHistory) || !isBoolean(preventHistory))
				{
					return BX.Landing.Backend.getInstance()
						.batch("Landing\\Block::updateNodes", batch, updateNodeParams)
						.then(function() {
							return Promise.resolve(data);
						});
				}
			}

			return Promise.resolve(data);
		},


		/**
		 * Fetches request data from content panel
		 * @param {BX.Landing.UI.Panel.BasePanel} panel
		 * @param {boolean} all
		 * @return {Promise<Object>}
		 */
		fetchRequestData: function(panel, all)
		{
			var requestData = {};
			var forms = {};

			var fetchFields = function(collection, all) {
				return all ? collection : collection.fetchChanges();
			};

			forms.attrs = new FormCollection();
			forms.cards = new FormCollection();
			forms.dynamicCards = new FormCollection();
			forms.dynamicBlock = new FormCollection();
			forms.content = new FormCollection();
			forms.settings = new FormCollection();
			forms.menu = new FormCollection();

			panel.forms
				.forEach(function(form) {
					forms[form.type].push(form);
				});

			fetchFields(forms.content.fetchFields(), all)
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

			fetchFields(attrFields, true)
				.reduce(proxy(this.appendAttrFieldValue, this), requestData);

			forms.cards
				.reduce(proxy(this.appendCardsFormValue, this), requestData);

			forms.dynamicCards
				.reduce(proxy(this.appendDynamicCardsFormValue, this), requestData);

			forms.dynamicBlock
				.reduce(proxy(this.appendDynamicBlockFormValue, this), requestData);

			fetchFields(forms.attrs.fetchFields(), all)
				.reduce(proxy(this.appendAttrFieldValue, this), requestData);

			fetchFields(forms.settings.fetchFields(), all)
				.reduce(proxy(this.appendSettingsFieldValue, this), requestData);

			forms.menu
				.reduce(proxy(this.appendMenuValue, this), requestData);

			requestData.dynamicState = Object.keys(this.manifest.cards)
				.reduce(function(acc, cardsCode) {
					acc[cardsCode] = (
						BX.type.isPlainObject(requestData.dynamicParams)
						&& cardsCode in requestData.dynamicParams
					);
					return acc;
				}, {});

			requestData.dynamicState.wrapper = (
				!!requestData.dynamicParams
				&& "wrapper" in requestData.dynamicParams
			);

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

		appendDynamicCardsFormValue: function(requestData, form)
		{
			requestData.dynamicParams = requestData.dynamicParams || {};
			requestData.dynamicParams[form.code] = {};
			requestData.dynamicParams[form.code] = form.serialize();

			return requestData;
		},

		appendDynamicBlockFormValue: function(requestData, form)
		{
			requestData.dynamicParams = requestData.dynamicParams || {};
			requestData.dynamicParams.wrapper = form.serialize();

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
			if (isPlainObject(data))
			{
				var isNeedReload = this.containsPseudoSelector(data) || this.containsReloadRequireAttributes(data);
				if (!isNeedReload)
				{
					return Promise.resolve(data);
				}
			}

			var loader = new BX.Loader({target: this.parent.parentElement, color: "rgba(255, 255, 255, .8)"});
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
					var event = this.createEvent();
					fireCustomEvent("BX.Landing.Block:remove", [event]);

					BX.Landing.Main.getInstance().currentBlock = self;
					BX.Landing.Main.getInstance().currentArea = self.parent;
					return BX.Landing.Main.getInstance().addBlock(response, true);
				}.bind(this))
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
					.then(function(requestData) {
						fireCustomEvent("BX.Landing.Block:onContentSave", [this.id]);
						return Object.assign(
							{},
							requestData,
							{
								cardsFirst: true
							}
						);
					}.bind(this))
					.then(this.updateBlockState.bind(this));
			}
		},

		/**
		 * Handles content cancel edit event
		 */
		onContentCancel: function()
		{
			this.panels.get("content_edit").hide();
			this.tmpContent.innerHTML = "";
			this.anchor = this.savedAnchor;
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

		/**
		 * Style change handler
		 * @param event
		 * @param {boolean} [preventHistory = false] - Add this action to history or not. By default - add
		 */
		onStyleInput: function(event, preventHistory)
		{
			this.saveStyles(preventHistory);

			const styleEvent = this.createEvent(event);
			fireCustomEvent("BX.Landing.Block:updateStyle", [styleEvent]);
		},

		/**
		 * @private
		 * @param {object} options
		 * @return {BX.Landing.UI.Form.BaseForm}
		 */
		getBlockEditForm: function(options)
		{
			var preparedOptions = {};

			if (BX.type.isPlainObject(options))
			{
				preparedOptions = Object.assign({}, options);
			}

			var blockNodes = preparedOptions.nodes || this.nodes;

			// exclude nodes from cards
			if (this.cards.length > 0 && !options.hideCheckbox)
			{
				blockNodes = this.nodes.notMatches(
					this.getCardsSelector()
				);
			}

			var selectors = Object.keys(this.manifest.nodes);

			blockNodes = selectors
				.reduce(function(acc, selector) {
					if (!selector.includes(':'))
					{
						blockNodes
							.matches(selector)
							.getVisible()
							.filter(function(node) {
								return node.manifest.allowFormEdit !== false;
							})
							.forEach(function(node) {
								acc.push(node);
							});
					}

					return acc;
				}, new NodeCollection());

			var onBlockFormTypeChangeHandler = this.onBlockFormTypeChange.bind(this);

			var dynamicState = !!(
				!options.skipBlockState
				&& BX.type.isPlainObject(this.dynamicParams)
				&& this.dynamicParams.wrapper
			);

			var help = "";
			var helps = BX.Landing.Main.getInstance().options.helps;

			if (BX.type.isPlainObject(helps))
			{
				help = helps.DYNAMIC_BLOCKS;
			}

			var headerCheckbox = {
				text: BX.Landing.Loc.getMessage("LANDING_BLOCK__MAKE_A_DYNAMIC"),
				onChange: onBlockFormTypeChangeHandler,
				state: dynamicState,
				help: help
			};

			var blockForm = new BaseForm({
				title: options.formName || BX.Landing.Loc.getMessage("BLOCK_ELEMENTS"),
				description: this.manifest.block.formDescription,
				type: "content",
				code: this.id,
				headerCheckbox: (function() {
					if (!options.hideCheckbox && this.manifest.block.dynamic !== false)
					{
						return headerCheckbox;
					}

					return undefined;
				}.bind(this))()
			});

			if (dynamicState)
			{
				setTimeout(function() {
					onBlockFormTypeChangeHandler({
						form: blockForm,
						state: true
					});
				});
			}

			blockNodes.forEach(function(node) {
				blockForm.addField(node.getField());
			});

			return blockForm;
		},

		getMenuEditForms: function()
		{
			return this.menu.map(function(menu) {
				return menu.getForm();
			}, this);
		},

		/**
		 * @private
		 * @return {BX.Landing.UI.Form.BaseForm}
		 */
		getAttrsEditForm: function()
		{
			var keys = Object.keys(this.manifest.attrs);
			var fields = [];

			keys.forEach(function(selector) {
				var attr = this.manifest.attrs[selector];

				if (!attr.hidden)
				{
					attr = !isArray(attr) ? [attr] : attr;

					attr.forEach(function(options) {
						if (!options.hidden && isString(options.type))
						{
							fields.push(
								this.createAttributeField(options, options.selector || selector)
							);
						}
					}, this);
				}
			}, this);

			var attrsForm = new BaseForm({
				id: "attr",
				type: "attrs",
				title: BX.Landing.Loc.getMessage("BLOCK_SETTINGS"),
				description: this.manifest.block.attrsFormDescription
			});

			fields.forEach(function(field) {
				attrsForm.addField(field);
			});

			return attrsForm;
		},

		/**
		 * @private
		 * @return {Array}
		 */
		getAttrsAdditionalEditForms: function()
		{
			var keys = Object.keys(this.manifest.attrs);
			var forms = [];

			keys.forEach(function(selector) {
				var attr = this.manifest.attrs[selector];

				if (!attr.hidden)
				{
					attr = !isArray(attr) ? [attr] : attr;

					attr.forEach(function(options) {
						if (!options.hidden && isString(options.type))
						{
							return;
						}

						if (isString(options.name) && options.attrs)
						{
							forms.push(
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

			return forms;
		},

		/**
		 * @private
		 * @param skipState
		 * @return {Array}
		 */
		getCardsEditForms: function(skipState)
		{
			var cardsSelectors = Object.keys(this.manifest.cards);
			var nodesSelectors = Object.keys(this.manifest.nodes);
			var forms = [];

			var groupedCards = cardsSelectors.reduce(function(acc, selector) {
				var cards = this.cards.filter(function(card) {
					return card.selector.split("@")[0] === selector;
				});

				if (cards.length > 0)
				{
					cards.sort(function(a, b) {
						return a.sortIndex - b.sortIndex;
					});

					acc.set(selector, cards);
				}

				return acc;
			}.bind(this), new Map());

			groupedCards.forEach(function(cards, selector) {
				var checkboxState = (
					BX.type.isPlainObject(this.dynamicParams)
					&& selector in this.dynamicParams
					&& !skipState
				);

				var onCardsFormTypeHandler = this.onCardsFormTypeChange.bind(this);
				var groupLabel = this.manifest.cards[selector]['group_label'];

				var help = "";
				var helps = BX.Landing.Main.getInstance().options.helps;

				if (BX.type.isPlainObject(helps))
				{
					help = helps.DYNAMIC_BLOCKS;
				}

				var headerCheckbox = {
					text: BX.Landing.Loc.getMessage("LANDING_CARDS__MAKE_A_DYNAMIC"),
					onChange: onCardsFormTypeHandler,
					state: checkboxState,
					help: help
				};

				var cardsForm = new CardsForm({
					title: groupLabel || BX.Landing.Loc.getMessage("LANDING_CARDS_FROM_TITLE"),
					code: selector.split("@")[0],
					presets: cards[0].manifest.presets,
					sync: cards[0].manifest.sync,
					description: cards[0].manifest.formDescription,
					forms: forms,
					headerCheckbox: (function() {
						if (this.manifest.block.dynamic !== false)
						{
							return headerCheckbox;
						}

						return undefined;
					}.bind(this))()
				});

				forms.push(cardsForm);

				if (checkboxState)
				{
					setTimeout(function() {
						onCardsFormTypeHandler({
							form: cardsForm,
							state: true
						});
					});
				}

				cards.forEach(function(card) {
					var cardForm = new CardForm({
						label: card.getLabel() || card.getName(),
						labelBindings: card.manifest.label,
						selector: card.selector,
						preset: card.preset
					});

					var sortedCardNodes = new NodeCollection();

					var cardNodes = this.nodes.filter(function(node) {
						return card.node.contains(node.node)
					});

					if (cardNodes.length)
					{
						nodesSelectors.forEach(function(nodeSelector) {
							var matches = cardNodes.matches(nodeSelector);
							matches.forEach(sortedCardNodes.add, sortedCardNodes);
						}, this);

						sortedCardNodes.forEach(function(node) {
							if (node.manifest.allowFormEdit !== false)
							{
								cardForm.addField(node.getField());
							}
						});

						var additional = this.manifest.cards[selector].additional;
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
			}, this);

			return forms;
		},

		/**
		 * @private
		 * @return {BX.Landing.UI.Form.BaseForm}
		 */
		getBlockSettingsForm: function()
		{
			var blockSettingsForm = new BaseForm({
				title: BX.Landing.Loc.getMessage("BLOCK_SETTINGS"),
				type: "settings"
			});

			var fieldFactory = this.createFieldFactory("!" + this.selector);
			var errorMessage = null;
			var baseUrl = BX.Landing.Main.getInstance().options.url;

			if (baseUrl[0] === "/")
			{
				baseUrl = top.location.origin + baseUrl;
			}

			this.savedAnchor = (this.anchor || this.node.id);

			var previewText = join(baseUrl, "#", (this.anchor || this.node.id));
			var anchorField = fieldFactory.create({
				type: "text",
				name: BX.Landing.Loc.getMessage("BLOCK_SETTINGS_ANCHOR_FIELD"),
				description: "<span class='landing-ui-anchor-preview'>"+BX.Text.encode(previewText)+"</span>",
				attribute: "id",
				value: this.anchor || this.node.id,
				onInput: function() {
					var preview = anchorField.layout.querySelector(".landing-ui-anchor-preview");

					if (preview)
					{
						preview.innerHTML = BX.Text.encode(join(baseUrl, "#", BX.Text.decode(anchorField.getValue())));
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
							BX.Landing.Loc.getMessage("BLOCK_SETTINGS_ANCHOR_FIELD_VALIDATE_ERROR")
						);

						addClass(errorMessage, "landing-ui-error");

						append(errorMessage, anchorField.layout);
					}

					if (!isValidElementId(anchorField.getValue()))
					{
						errorMessage = BX.Landing.UI.Field.BaseField.createDescription(
							BX.Landing.Loc.getMessage("BLOCK_SETTINGS_ANCHOR_FIELD_VALIDATE_INVALID_ID")
						);

						addClass(errorMessage, "landing-ui-error");

						append(errorMessage, anchorField.layout);
					}
				}.bind(this)
			});

			blockSettingsForm.addField(anchorField);

			return blockSettingsForm;
		},

		/**
		 * Gets content edit forms
		 * @param {object} options
		 * @return {BX.Landing.UI.Collection.FormCollection}
		 */
		getEditForms: function(options)
		{
			var preparedOptions = {};

			if (BX.type.isPlainObject(options))
			{
				preparedOptions = Object.assign({}, options);
			}

			if (arguments.length > 1)
			{
				preparedOptions.nodes = arguments[0];
				preparedOptions.formName = arguments[1];
				preparedOptions.nodesOnly = arguments[2];
				preparedOptions.showAll = arguments[3];
				preparedOptions.skipCardsState = arguments[4];
				preparedOptions.skipBlockState = arguments[5];
			}

			var forms = new FormCollection();

			if (this.access >= ACCESS_W)
			{
				var isEditable = !(
					isEmpty(this.manifest.nodes)
					&& isEmpty(this.manifest.attrs)
					&& isEmpty(this.manifest.menu)
				);

				if (isEditable)
				{
					// Block form
					var blockEditForm = this.getBlockEditForm(preparedOptions);
					if (blockEditForm.fields.length > 0)
					{
						forms.add(blockEditForm);
					}

					var menuEditForms = this.getMenuEditForms(preparedOptions);
					if (menuEditForms.length > 0)
					{
						menuEditForms.forEach(function(menuForm) {
							forms.add(menuForm);
						});
					}

					if (!preparedOptions.nodesOnly)
					{
						// Attrs forms
						var attrsEditForm = this.getAttrsEditForm();

						if (attrsEditForm.fields.length > 0)
						{
							forms.add(attrsEditForm);
						}

						// Attrs additional forms
						var attrsAdditionalEditForms = this.getAttrsAdditionalEditForms();
						if (attrsAdditionalEditForms.length > 0)
						{
							attrsAdditionalEditForms.forEach(function(form) {
								forms.add(form);
							});
						}

						// Cards forms
						var cardsEditForms = this.getCardsEditForms(preparedOptions.skipCardsState);
						if (cardsEditForms.length > 0)
						{
							cardsEditForms.forEach(function(form) {
								forms.add(form);
							});
						}
					}
				}

				// Block settings
				var blockSettingsForm = this.getBlockSettingsForm();
				if (blockSettingsForm.fields.length > 0)
				{
					forms.push(blockSettingsForm);
				}
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
		},

		getFieldType: function(field)
		{
			var node = this.nodes.getBySelector(field.selector);

			if (node)
			{
				return node.type;
			}

			return null;
		},

		getTypeReferences: function(references, type)
		{
			return references.filter(function(reference) {
				return reference.type === type;
			});
		},

		convertReferencesToDropdownItems: function(references)
		{
			var items = references.map(function(reference) {
				return {name: reference.name, value: reference.id};
			});

			items.push({
				name: BX.Landing.Loc.getMessage('LANDING_BLOCK__DYNAMIC_REFERENCE_HIDE'),
				html: (
					"<span class=\"landing-ui-field-dropdown-sep\"></span>" + BX.Landing.Loc.getMessage('LANDING_BLOCK__DYNAMIC_REFERENCE_HIDE')
				),
				value: '@hide'
			});

			return items;
		},

		getDefaultDropdownItems: function()
		{
			return [
				{name: BX.Landing.Loc.getMessage("LANDING_CARDS__DYNAMIC_FIELD_NOT_SET"), value: ""}
			];
		},

		getDynamicFiledValue: function(cardsCode, fieldSelector)
		{
			var state = this.dynamicParams || {};

			if (
				BX.type.isPlainObject(state[cardsCode])
				&& BX.type.isPlainObject(state[cardsCode].references)
			)
			{
				return state[cardsCode].references[fieldSelector];
			}
		},

		convertToDynamicFields: function(fields, cardCode, references)
		{
			return fields.map(function(field) {
				var type = this.getFieldType(field);

				if (
					type !== "text"
					&& type !== "img"
					&& type !== "link"
					&& type !== "link_ref"
				)
				{
					return field;
				}

				var typeReferences = this.getTypeReferences(references, type);
				var dropDownItems = this.convertReferencesToDropdownItems(typeReferences);
				var value = this.getDynamicFiledValue(cardCode, field.selector);

				if (type === "link")
				{
					if (
						BX.type.isPlainObject(typeReferences[0])
						&& BX.type.isArray(typeReferences[0].actions)
					)
					{
						return new BX.Landing.UI.Field.ClickAction({
							title: field.title,
							selector: field.selector,
							reference: typeReferences[0],
							linkField: field,
							value: value
						});
					}

					return field;
				}

				if (dropDownItems.length === 0)
				{
					dropDownItems = this.getDefaultDropdownItems();
				}

				if (type === "img")
				{
					return new BX.Landing.UI.Field.DynamicImage({
						title: field.title,
						selector: field.selector,
						dropdownItems: dropDownItems,
						value: BX.type.isString(value) ? {id: value} : value,
						hideCheckbox: cardCode === "wrapper"
					});
				}

				return new BX.Landing.UI.Field.DynamicDropdown({
					title: field.title,
					selector: field.selector,
					dropdownItems: dropDownItems,
					value: BX.type.isString(value) ? {id: value} : value,
					hideCheckbox: cardCode === "wrapper" || type === "link_ref"
				});
			}, this);
		},

		createDynamicCardsForm: function(options)
		{
			var help = "";
			var helps = BX.Landing.Main.getInstance().options.helps;

			if (BX.type.isPlainObject(helps))
			{
				help = helps.DYNAMIC_BLOCKS;
			}

			var dynamicForm = new BX.Landing.UI.Form.DynamicCardsForm({
				title: options.title,
				code: options.code,
				type: "dynamicCards",
				dynamicParams: options.dynamicParams,
				headerCheckbox: {
					text: BX.Landing.Loc.getMessage("LANDING_CARDS__MAKE_A_DYNAMIC"),
					onChange: this.onCardsFormTypeChange.bind(this),
					state: true,
					help: help
				},
				onSourceChange: function(source)
				{
					var dynamicFields = this.convertToDynamicFields(
						options.form.childForms[0].fields,
						options.code,
						source.references
					);

					var dynamicGroup = new DynamicFieldsGroup({
						id: "references",
						items: dynamicFields
					});

					var detailPageField = dynamicForm.detailPageGroup.fields[0];
					if (!BX.Type.isStringFilled(detailPageField.getValue().href))
					{
						var content = {text: '', href: ''};
						if (source && source.default && source.default.detail)
						{
							content.href = source.default.detail;
						}

						detailPageField.setValue(content);
						detailPageField.hrefInput.makeDisplayedHrefValue();
					}

					var oldCard = dynamicForm.cards.get('references');
					dynamicForm.replaceCard(oldCard, dynamicGroup);
				}.bind(this)
			});

			return dynamicForm;
		},

		onCardsFormTypeChange: function(event)
		{
			var contentPanel = this.panels.get("content_edit");
			var isDynamicEnabled = !!event.state;

			if (isDynamicEnabled)
			{
				var dynamicCardParams = {};

				if (
					BX.type.isPlainObject(this.dynamicParams)
					&& this.dynamicParams[event.form.code]
				)
				{
					dynamicCardParams = this.dynamicParams[event.form.code];
				}

				var value = Object.assign(
					{},
					dynamicCardParams
				);

				if (BX.type.isPlainObject(value.settings))
				{
					if (!('pagesCount' in value.settings))
					{
						value.settings.pagesCount = event.form.childForms.length;
					}
				}
				else
				{
					value.settings = {
						pagesCount: event.form.childForms.length
					};
				}

				var dynamicForm = this.createDynamicCardsForm({
					title: event.form.title,
					code: event.form.code,
					form: event.form,
					dynamicParams: value
				});

				contentPanel.replaceForm(event.form, dynamicForm);
				return;
			}

			delete this.dynamicParams[event.form.code];

			var staticForm = this.getCardsEditForms(true)
				.find(function(item) {
					return item.code === event.form.code;
				});

			contentPanel.replaceForm(event.form, staticForm);
		},

		isDynamicCards: function(cardsCode)
		{
			return cardsCode in this.dynamicParams;
		},

		onBlockFormTypeChange: function(event)
		{
			var contentPanel = this.panels.get("content_edit");
			var isDynamicEnabled = !!event.state;

			let restrictMessage = this.content.parentElement.querySelector('.landing-html-lock');
			if (restrictMessage)
			{
				if (!isDynamicEnabled)
				{
					this.content.style.display = 'flex';
					restrictMessage.style.display = 'none';
				}
				else
				{
					this.content.style.display = 'none';
					restrictMessage.style.display = 'flex';
				}
			}

			if (isDynamicEnabled)
			{
				var dynamicForm = this.createDynamicBlockForm({
					title: event.form.title,
					code: event.form.code,
					form: event.form,
					dynamicParams: this.dynamicParams
				});

				contentPanel.replaceForm(event.form, dynamicForm);
				return;
			}

			delete this.dynamicParams.wrapper;

			var staticForm = this.getBlockEditForm({
				skipBlockState: true
			});

			contentPanel.replaceForm(event.form, staticForm);
		},

		createDynamicBlockForm: function(options)
		{
			var help = "";
			var helps = BX.Landing.Main.getInstance().options.helps;

			if (BX.type.isPlainObject(helps))
			{
				help = helps.DYNAMIC_BLOCKS;
			}

			var dynamicForm = new BX.Landing.UI.Form.DynamicBlockForm({
				title: options.title,
				code: this.id,
				type: "dynamicBlock",
				dynamicParams: options.dynamicParams,
				headerCheckbox: {
					text: BX.Landing.Loc.getMessage("LANDING_BLOCK__MAKE_A_DYNAMIC"),
					onChange: this.onBlockFormTypeChange.bind(this),
					state: true,
					help: help
				},
				onSourceChange: function(source)
				{
					var oldCard = dynamicForm.cards.get('references');

					if (BX.type.isPlainObject(source))
					{
						var dynamicFields = this.convertToDynamicFields(
							options.form.fields,
							"wrapper",
							source.references
						);

						var dynamicGroup = new DynamicFieldsGroup({
							id: "references",
							items: dynamicFields
						});

						dynamicForm.replaceCard(oldCard, dynamicGroup);
						return;
					}

					dynamicForm.removeCard(oldCard);
				}.bind(this)
			});

			return dynamicForm;
		},

		isDynamic: function(code)
		{
			code = code || this.id;
			var panel = this.panels.get('content_edit');

			if (panel)
			{
				var form = panel.forms.toArray().find(function(form) {
					return form.code === code;
				});

				if (form)
				{
					return form.isCheckboxChecked();
				}
			}

			code = code === this.id ? "wrapper" : code;

			return (
				!!this.dynamicParams
				&& code in this.dynamicParams
			);
		}
	};
})();
