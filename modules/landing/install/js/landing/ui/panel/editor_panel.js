;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");

	var proxy = BX.Landing.Utils.proxy;
	var getSelectedElement = BX.Landing.Utils.getSelectedElement;

	/**
	 * Implements interface of editor actions panel.
	 * Implements singleton pattern.
	 *
	 * Not use as constructor
	 *
	 * @extends {BX.Landing.UI.Panel.BaseButtonPanel}
	 *
	 * @param bool outOfFrame - if panel place out of editor window
	 *
	 * @constructor
	 */
	BX.Landing.UI.Panel.EditorPanel = function()
	{
		BX.Landing.UI.Panel.BaseButtonPanel.apply(this, arguments);
		this.layout.classList.add("landing-ui-panel-editor");
		this.position = "absolute";
		this.currentElement = null;
		this.outOfFrame = true;

		this.onKeydown = this.onKeydown.bind(this);
		this.onTabDown = this.onTabDown.bind(this);
		this.onScroll = this.onScroll.bind(this);
	};


	/**
	 * Stores instance of BX.Landing.UI.Panel.EditorPanel
	 * @static
	 * @type {?BX.Landing.UI.Panel.EditorPanel}
	 */
	BX.Landing.UI.Panel.EditorPanel.instance = null;


	/**
	 * Gets instance on BX.Landing.UI.Panel.EditorPanel
	 * @static
	 * @return {BX.Landing.UI.Panel.EditorPanel}
	 */
	BX.Landing.UI.Panel.EditorPanel.getInstance = function()
	{
		if (!BX.Landing.UI.Panel.EditorPanel.instance)
		{
			BX.Landing.UI.Panel.EditorPanel.instance = new BX.Landing.UI.Panel.EditorPanel();
			BX.Landing.UI.Panel.EditorPanel.instance.init();
		}

		return BX.Landing.UI.Panel.EditorPanel.instance;
	};


	var scrollHandler = null;
	var target = null;

	/**
	 * Makes editor as draggable
	 * @param {BX.Landing.UI.Panel.EditorPanel} editor
	 */
	function makeDraggable(editor)
	{
		var dragButton = new BX.Landing.UI.Button.EditorAction("drag", {
			html: "<strong class=\"landing-ui-drag\">&nbsp;</strong>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_DRAG")}
		});

		dragButton.layout.onbxdrag = onDrag.bind(this);
		dragButton.layout.onbxdragstop = onDragEnd.bind(this);

		jsDD.registerObject(dragButton.layout);
		editor.prependButton(dragButton);

		var offsetCalculates;
		var offsetLeft;
		var offsetTop;

		function onDrag(x, y)
		{
			if (!offsetCalculates)
			{
				var pos = BX.pos(jsDD.current_node);
				offsetLeft = Math.max(Math.abs(x - pos.left), 0);
				offsetTop = Math.max(Math.abs(y - pos.top), 0);
				if (editor.currentElement.closest('.landing-ui-panel'))
				{
					offsetTop += BX.Landing.PageObject.getEditorWindow().scrollY;
				}

				offsetCalculates = true;
			}

			BX.DOM.write(function() {
				editor.layout.classList.remove("landing-ui-transition");
				editor.layout.style.top = (y - offsetTop) + "px";
				editor.layout.style.left = (x - offsetLeft) + "px";
			}.bind(this));
		}

		function onDragEnd()
		{
			offsetCalculates = false;
			editor.layout.classList.add("landing-ui-transition");
		}
	}


	/**
	 * Register base editor actions
	 * @param {BX.Landing.UI.Panel.EditorPanel} editor
	 */
	function registerBaseActions(editor)
	{
		editor.addButton(new BX.Landing.UI.Button.EditorAction("bold", {
			html: "<span class=\"landing-ui-icon-editor-bold\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_BOLD")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("italic", {
			html: "<span class=\"landing-ui-icon-editor-italic\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_ITALIC")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("underline", {
			html: "<span class=\"landing-ui-icon-editor-underline\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_UNDERLINE")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("strikeThrough", {
			html: "<span class=\"landing-ui-icon-editor-strike\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_STRIKE")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("justifyLeft", {
			html: "<span class=\"landing-ui-icon-editor-left\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_LEFT")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("justifyCenter", {
			html: "<span class=\"landing-ui-icon-editor-center\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_CENTER")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("justifyRight", {
			html: "<span class=\"landing-ui-icon-editor-right\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_RIGHT")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("justifyFull", {
			html: "<span class=\"landing-ui-icon-editor-justify\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_JUSTIFY")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.CreateLink("createLink", {
			html: "<span class=\"landing-ui-icon-editor-link\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_CREATE_LINK")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		var rights = BX.Landing.Env.getInstance().getOptions().rights;
		if (rights && rights.includes('edit'))
		{
			editor.addButton(new BX.Landing.UI.Button.CreatePage("createPage", {
				html: "<span class=\"landing-ui-icon-editor-new-page\"></span>",
				attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_CREATE_PAGE")},
				onClick: proxy(editor.adjustButtonsState, editor)
			}));
		}

		editor.addButton(new BX.Landing.UI.Button.EditorAction("unlink", {
			html: "<span class=\"landing-ui-icon-editor-unlink\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_UNLINK")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("insertUnorderedList", {
			html: "<span class=\"fa fa-list-ul\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_UL")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("insertOrderedList", {
			html: "<span class=\"fa fa-list-ol\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_OL")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("removeFormat", {
			html: "<span class=\"landing-ui-icon-editor-eraser\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_CLEAR")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.ColorAction("foreColor", {
			text: BX.Landing.Loc.getMessage("EDITOR_ACTION_SET_FORE_COLOR"),
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_COLOR")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.TextBackgroundAction("hiliteColor", {
			html: "<span class=\"landing-ui-icon-editor-text-background\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_TEXT_BACKGROUND")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.CreateTable("createTable", {
			html: "<span class=\"landing-ui-icon-editor-table\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_CREATE_TABLE")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.PasteTable("pasteTable", {
			html: "<span class=\"landing-ui-icon-editor-copy\"></span>",
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_EDITOR_ACTION_PASTE_TABLE")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));
	}


	var lastPosition = {top: 0, left: 0};
	function adjustAbsolutePosition(editor, node, force)
	{
		var nodeRect = node.getBoundingClientRect();

		var left = nodeRect.left + (nodeRect.width / 2) - (editor.rect.width / 2);
		var top = (nodeRect.top - editor.rect.height - 4);
		var position = 'absolute';
		var windowScope = editor.outOfFrame
			? window.parent
			: window;
		var bodyContent = node.closest('.landing-ui-panel-content-body-content');
		if (bodyContent)
		{
			if (node.classList.contains('landing-ui-field'))
			{
				top += windowScope.pageYOffset;
			}
			else
			{
				top = bodyContent.getBoundingClientRect().top + 5;
				position = 'fixed';
			}
		}
		else if (BX.Landing.Main.getInstance().isControlsExternal())
		{
			top += 71;
		}
		else
		{
			if (
				top <= 5
				&& (
					nodeRect.bottom > windowScope.innerHeight
					|| nodeRect.height > (windowScope.innerHeight / 1.5)
				)
			)
			{
				top = 5;
				position = 'fixed';
			}
			else
			{
				if (top > 5)
				{
					top += windowScope.pageYOffset + 66;
				}
				else
				{
					top = nodeRect.bottom + 4 + windowScope.pageYOffset;
				}
			}
		}

		if ((left + editor.rect.width) > (windowScope.innerWidth - 20))
		{
			left -= ((left + editor.rect.width) - (windowScope.innerWidth - 20));
		}

		left = Math.max(20, left);

		if (lastPosition.top !== top || lastPosition.left !== left || force)
		{
			BX.DOM.write(function() {
				editor.layout.style.position = position;
				editor.layout.style.top = top + "px";
				editor.layout.style.left = left + "px";
			});

			lastPosition.top = top;
			lastPosition.left = left;

			hideButtonsPopups(editor);
		}
	}

	/**
	 * Appends editor to document body
	 * @param {BX.Landing.UI.Panel.EditorPanel} editor
	 */
	function appendToBody(editor)
	{
		if (editor.outOfFrame)
		{
			window.parent.document.body.appendChild(editor.layout);
		}
		else
		{
			window.document.body.appendChild(editor.layout);
		}
	}

	var mouseTarget = null;
	function onMousedown(event)
	{
		mouseTarget = event.target;
	}

	var preventClick = false;
	function onMouseUp(event)
	{
		preventClick = mouseTarget !== event.target
	}

	function onClick(event)
	{
		if (preventClick)
		{
			event.preventDefault();
			event.stopPropagation();
		}
	}

	function closePopup(button)
	{
		if (button.popup)
		{
			button.popup.close();
		}

		if (button.menu)
		{
			button.menu.close();
		}
	}

	function hideButtonsPopups(editor)
	{
		editor.buttons.forEach(closePopup);

		if (editor.additionalButtons)
		{
			editor.additionalButtons.forEach(closePopup);
		}

		BX.Landing.UI.Tool.ColorPicker.hideAll();
	}


	BX.Landing.UI.Panel.EditorPanel.prototype = {
		constructor: BX.Landing.UI.Panel.EditorPanel,
		__proto__: BX.Landing.UI.Panel.BaseButtonPanel.prototype,

		/**
		 * Method for separate init actions from object create
		 */
		init: function()
		{
			makeDraggable(this);
			registerBaseActions(this);
			appendToBody(this);
			this.rect = this.layout.getBoundingClientRect();
		},

		/**
		 * Shows editor
		 * @param {HTMLElement} element - Editable element
		 * @param {?string} [position = "absolute"]
		 * @param {BX.Landing.UI.Button.BaseButton[]} [additionalButtons]
		 * @param {boolean} isTable
		 * @param {array} hideButtons - List base buttons
		 */
		show: function(
			element,
			position,
			additionalButtons,
			isTable,
			hideButtons
		)
		{
			if (!isTable)
			{
				this.showBaseButtons();
			}
			else
			{
				if (hideButtons)
				{
					if (hideButtons.length > 0)
					{
						this.showBaseButtons();
						this.hideBaseButtons(hideButtons);
					}
					else
					{
						this.hideAllBaseButtons();
					}
				}
				else
				{
					this.hideAllBaseButtons();
				}
			}

			this.currentElement = element;
			this.setContextDocument(this.currentElement ? this.currentElement.ownerDocument : document);

			if (this.additionalButtons)
			{
				this.additionalButtons.forEach(function(button) {
					this.buttons.remove(button);
					closePopup(button);
					BX.remove(button.layout);
				}, this);

				this.additionalButtons = null;
			}

			if (additionalButtons)
			{
				this.additionalButtons = additionalButtons;
				this.additionalButtons.forEach(function(button) {
					if (button.insertAfter)
					{
						var prevSibling = this.layout.querySelector("[data-id=\""+button.insertAfter+"\"]");

						if (prevSibling)
						{
							BX.insertAfter(button.layout, prevSibling);
							this.buttons.add(button);
						}
					}
					else
					{
						this.addButton(button);
					}
				}, this);
			}

			if (!this.isShown())
			{
				BX.onCustomEvent("BX.Landing.Editor:enable", [element]);
				this.contextDocument.addEventListener("mousedown", onMousedown, true);
				this.contextDocument.addEventListener("mouseup", onMouseUp, true);
				this.contextDocument.addEventListener("click", onClick, true);
				this.currentElement.addEventListener("click", proxy(this.adjustButtonsState, this), true);

				setTimeout(function() {
					this.layout.classList.add("landing-ui-transition");
				}.bind(this), 100);
			}

			BX.Landing.UI.Panel.BaseButtonPanel.prototype.show.call(this, arguments);

			BX.DOM.write(function() {
				this.rect = this.layout.getBoundingClientRect();
				this.adjustPosition(element, position, true);
			}.bind(this));

			this.onShow(element);
			this.adjustButtonsState();
			this.adjustButtonsContextDocument();
		},

		onShow: function(node)
		{
			target = node;
			scrollHandler = scrollHandler || this.onScroll.bind(null, node);
			this.contextDocument.addEventListener("keydown", this.onKeydown);
			this.contextWindow.addEventListener("resize", scrollHandler);

			try {
				this.contextDocument.addEventListener("scroll", scrollHandler, {passive: true});
			} catch (err) {
				this.contextDocument.addEventListener("scroll", scrollHandler);
			}
		},

		hide: function()
		{
			if (this.isShown())
			{
				BX.onCustomEvent("BX.Landing.Editor:disable", [null]);
				this.contextDocument.removeEventListener("mousedown", onMousedown, true);
				this.contextDocument.removeEventListener("mouseup", onMouseUp, true);
				this.contextDocument.removeEventListener("click", onClick, true);
				this.currentElement.removeEventListener("click", proxy(this.adjustButtonsState, this), true);

				setTimeout(function() {
					this.rect = this.layout.getBoundingClientRect();
					this.layout.classList.remove("landing-ui-transition");
				}.bind(this), 100);
			}

			BX.Landing.UI.Panel.BaseButtonPanel.prototype.hide.call(this, arguments);
			this.onHide();
		},

		onHide: function()
		{
			this.contextDocument.removeEventListener("keydown", this.onKeydown);
			this.contextWindow.removeEventListener("resize", scrollHandler);

			try {
				this.contextDocument.removeEventListener("scroll", scrollHandler, {passive: true});
			} catch (err) {
				this.contextDocument.removeEventListener("scroll", scrollHandler);
			}
		},

		onKeydown: function(event)
		{
			// TAB key
			if (
				event.key === 'Tab'
				&& event.target.nodeName !== "LI"
			)
			{
				event.preventDefault();

				if (!event.shiftKey)
				{
					if (event.code === 'Tab')
					{
						this.onTabDown();
					}
					else
					{
						this.contextDocument.execCommand('indent');
					}
				}
				else
				{
					this.contextDocument.execCommand('outdent');
				}
			}

			// ENTER key
			if (
				event.key === 'Enter'
				&& event.target.nodeName !== "LI"
				&& event.target.nodeName !== "UL"
				&& event.metaKey === true
			)
			{
				event.preventDefault();
				const range = this.contextWindow.getSelection().getRangeAt(0);
				const br = BX.create("br");
				range.deleteContents();
				range.insertNode(br);

				const newRange = this.contextDocument.createRange();
				newRange.setStartAfter(br);
				newRange.collapse(true);

				const sel = this.contextWindow.getSelection();
				sel.removeAllRanges();
				sel.addRange(newRange);
			}

			setTimeout(function() {
				BX.Landing.UI.Panel.EditorPanel.getInstance().adjustPosition(target);
			}, 10);
		},

		onTabDown: function()
		{
			var TAB_COUNT = 10;
			var isAllowedTab = true;
			var parentNode = this.contextWindow.getSelection().focusNode.parentNode.parentNode;
			while (parentNode.tagName === 'DIV')
			{
				parentNode = parentNode.parentNode;
			}
			var countUlTag = 0;
			var parentsNodeArr = [];
			var allowedTagName = ['UL', 'LI', 'BLOCKQUOTE', 'DIV'];
			while (allowedTagName.indexOf(parentNode.tagName) !== -1)
			{
				if (parentNode.tagName !== 'DIV')
				{
					countUlTag++;
					parentsNodeArr.push(parentNode);
				}
				parentNode = parentNode.parentNode;
			}
			if (countUlTag > TAB_COUNT)
			{
				if (parentsNodeArr[parentsNodeArr.length - 1].tagName === 'BLOCKQUOTE')
				{
					var previousElement = parentsNodeArr[parentsNodeArr.length - 1].previousSibling;
					while ((previousElement !== null) && (previousElement.nodeType !== 1))
					{
						previousElement = previousElement.previousSibling;
					}
					var countBlockquote = 0;
					while (previousElement && previousElement.tagName === 'BLOCKQUOTE')
					{
						previousElement = previousElement.firstChild;
						countBlockquote++;
					}
					if ((countUlTag - countBlockquote) > 0)
					{
						isAllowedTab = false;
					}
				}
				else
				{
					for (var i = 1; i < parentsNodeArr.length; i++) {
						if (parentsNodeArr[i].childNodes.length < 2)
						{
							isAllowedTab = false;
							break;
						}
					}
					if (parentsNodeArr[0].firstChild.nextSibling === null)
					{
						isAllowedTab = false;
					}
				}
			}
			if (isAllowedTab)
			{
				this.contextDocument.execCommand('indent');
			}
		},

		onScroll: function()
		{
			BX.Landing.UI.Panel.EditorPanel.getInstance().adjustPosition(target);
		},

		adjustButtonsState: function()
		{
			var getAction = function(value) {
				return (!value ? "de" : "") + "activate";
			};

			var button = function(key) {
				return this.buttons.get(key);
			}.bind(this);

			requestAnimationFrame(function() {
				var format = this.getFormat();
				void (button("bold") && button("bold")[getAction(format.bold)]());
				void (button("italic") && button("italic")[getAction(format.italic)]());
				void (button("underline") && button("underline")[getAction(format.underline)]());
				void (button("strikeThrough") && button("strikeThrough")[getAction(format.strike)]());
				void (button("justifyLeft") && button("justifyLeft")[getAction(format.align === "left")]());
				void (button("justifyCenter") && button("justifyCenter")[getAction(format.align === "center")]());
				void (button("justifyRight") && button("justifyRight")[getAction(format.align === "right")]());
				void (button("justifyFull") && button("justifyFull")[getAction(format.align === "justify")]());
			}.bind(this));
		},

		adjustButtonsContextDocument: function()
		{
			this.buttons.forEach(button => {
				if ('setContextDocument' in button)
				{
					button.setContextDocument(this.contextDocument);
				}
			});
		},

		getFormat: function()
		{
			var element = getSelectedElement(this.contextDocument);
			var format = {};

			if (element)
			{
				var style = getComputedStyle(element);

				switch (style.getPropertyValue("font-weight"))
				{
					case "bold":
					case "bolder":
					case "500":
					case "600":
					case "700":
					case "800":
					case "900":
						format["bold"] = true;
						break;
				}

				if (element.tagName === 'B')
				{
					format["bold"] = true;
				}

				if (style.getPropertyValue("font-style") === "italic")
				{
					format["italic"] = true;
				}

				if (style.getPropertyValue("text-decoration").includes("underline") ||
					style.getPropertyValue("text-decoration-line").includes("underline"))
				{
					format["underline"] = true;
				}

				if (style.getPropertyValue("text-decoration").includes("line-through") ||
					style.getPropertyValue("text-decoration-line").includes("line-through"))
				{
					format["strike"] = true;
				}

				var align = style.getPropertyValue("text-align") || "left";
				if (align.match(/[left|center|rigth|custiffy]/))
				{
					format["align"] = align;
				}

				if (this.currentElement.nodeName === "A" || this.currentElement.closest("a"))
				{
					format["link"] = true;
				}
			}

			return format;
		},

		adjustPosition: function(node, position, force)
		{
			adjustAbsolutePosition(this, node, force);
		},

		isFixed: function()
		{
			return this.position === "fixed-top" || this.position === "fixed-right";
		},

		hideAllBaseButtons: function()
		{
			this.layout.childNodes.forEach(function(button){
				if (button.dataset.id !== 'drag')
				{
					button.hidden = true;
				}
			});
		},

		hideBaseButtons: function(hideButtons)
		{
			this.layout.childNodes.forEach(function(button){
				if (hideButtons.indexOf(button.dataset.id) !== -1)
				{
					button.hidden = true;
				}
			});
		},

		showBaseButtons: function()
		{
			this.layout.childNodes.forEach(button => {
				if (button.dataset.id === 'pasteTable')
				{
					if (top.window.copiedTable)
					{
						button.hidden = false;
					}
					else
					{
						button.hidden = true;
					}
				}
				else
				{
					button.hidden = false;
				}
			});
		},

		isOutOfFrame: function()
		{
			return this.outOfFrame;
		}
	};
})();