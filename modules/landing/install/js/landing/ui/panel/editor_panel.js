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
	 * @constructor
	 */
	BX.Landing.UI.Panel.EditorPanel = function()
	{
		BX.Landing.UI.Panel.BaseButtonPanel.apply(this, arguments);
		this.layout.classList.add("landing-ui-panel-editor");
		this.position = "absolute";
		this.currentElement = null;
		makeDraggable(this);
		registerBaseActions(this);
		appendToBody(this);
		this.rect = this.layout.getBoundingClientRect();
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
		}

		return BX.Landing.UI.Panel.EditorPanel.instance;
	};


	var scrollHandler = null;
	var target = null;

	function onShow(node)
	{
		target = node;
		scrollHandler = scrollHandler || onScroll.bind(null, node);
		document.addEventListener("keydown", onKeydown);
		window.addEventListener("resize", scrollHandler);


		try {
			document.addEventListener("scroll", scrollHandler, {passive: true});
		} catch (err) {
			document.addEventListener("scroll", scrollHandler);
		}
	}

	function onHide()
	{
		document.removeEventListener("keydown", onKeydown);
		window.removeEventListener("resize", scrollHandler);

		try {
			document.removeEventListener("scroll", scrollHandler, {passive: true});
		} catch (err) {
			document.removeEventListener("scroll", scrollHandler);
		}
	}

	function onKeydown(event)
	{
		if (event.which === 13 &&
			event.target.nodeName !== "LI" &&
			event.target.nodeName !== "UL")
		{
			event.preventDefault();

			var range = window.getSelection().getRangeAt(0);
			var br = BX.create("br");
			range.deleteContents();
			range.insertNode(br);

			range = document.createRange();
			range.setStartAfter(br);
			range.collapse(true);

			var sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);
		}

		setTimeout(function() {
			BX.Landing.UI.Panel.EditorPanel.getInstance().adjustPosition(target);
		}, 10);
	}

	function onScroll()
	{
		BX.Landing.UI.Panel.EditorPanel.getInstance().adjustPosition(target);
	}

	/**
	 * Makes editor as draggable
	 * @param {BX.Landing.UI.Panel.EditorPanel} editor
	 */
	function makeDraggable(editor)
	{
		var dragButton = new BX.Landing.UI.Button.EditorAction("drag", {
			html: "<strong class=\"landing-ui-drag\">&nbsp;</strong>",
			attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_DRAG")}
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
			attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_BOLD")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("italic", {
			html: "<span class=\"landing-ui-icon-editor-italic\"></span>",
			attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_ITALIC")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("underline", {
			html: "<span class=\"landing-ui-icon-editor-underline\"></span>",
			attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_UNDERLINE")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("strikeThrough", {
			html: "<span class=\"landing-ui-icon-editor-strike\"></span>",
			attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_STRIKE")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("justifyLeft", {
			html: "<span class=\"landing-ui-icon-editor-left\"></span>",
			attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_LEFT")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("justifyCenter", {
			html: "<span class=\"landing-ui-icon-editor-center\"></span>",
			attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_CENTER")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("justifyRight", {
			html: "<span class=\"landing-ui-icon-editor-right\"></span>",
			attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_RIGHT")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("justifyFull", {
			html: "<span class=\"landing-ui-icon-editor-justify\"></span>",
			attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_ALIGN_JUSTIFY")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.CreateLink("createLink", {
			html: "<span class=\"landing-ui-icon-editor-link\"></span>",
			attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_CREATE_LINK")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("unlink", {
			html: "<span class=\"landing-ui-icon-editor-unlink\"></span>",
			attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_UNLINK")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		// editor.addButton(new BX.Landing.UI.Button.FontAction("font", {
		// 	html: BX.message("EDITOR_ACTION_FONT"),
		// 	attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_FONT")}
		// }));

		editor.addButton(new BX.Landing.UI.Button.EditorAction("removeFormat", {
			html: "<span class=\"landing-ui-icon-editor-eraser\"></span>",
			attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_CLEAR")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));

		editor.addButton(new BX.Landing.UI.Button.ColorAction("foreColor", {
			text: BX.message("EDITOR_ACTION_SET_FORE_COLOR"),
			attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_COLOR")},
			onClick: proxy(editor.adjustButtonsState, editor)
		}));
	}


	var lastPosition = {top: 0, left: 0};
	function adjustAbsolutePosition(editor, node, force)
	{
		var nodeRect = node.getBoundingClientRect();
		var left = nodeRect.left + (nodeRect.width / 2) - (editor.rect.width / 2);
		var top = (nodeRect.top - editor.rect.height - 4);
		top = (top > 0 ? top : nodeRect.bottom + 4) + window.pageYOffset;

		if ((left + editor.rect.width) > (window.innerWidth - 20))
		{
			left -= ((left + editor.rect.width) - (window.innerWidth - 20));
		}

		left = Math.max(20, left);

		if (lastPosition.top !== top || lastPosition.left !== left || force)
		{
			BX.DOM.write(function() {
				editor.layout.style.position = "absolute";
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
		document.body.appendChild(editor.layout);
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
		 * Shows editor
		 * @param {HTMLElement} element - Editable element
		 * @param {?string} [position = "absolute"]
		 * @param {BX.Landing.UI.Button.BaseButton[]} [additionalButtons]
		 */
		show: function(element, position, additionalButtons)
		{
			this.currentElement = element;

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
				document.addEventListener("mousedown", onMousedown, true);
				document.addEventListener("mouseup", onMouseUp, true);
				document.addEventListener("click", onClick, true);
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

			onShow(element);
			this.adjustButtonsState();
		},

		hide: function()
		{
			if (this.isShown())
			{
				BX.onCustomEvent("BX.Landing.Editor:disable", [null]);
				document.removeEventListener("mousedown", onMousedown, true);
				document.removeEventListener("mouseup", onMouseUp, true);
				document.removeEventListener("click", onClick, true);
				this.currentElement.removeEventListener("click", proxy(this.adjustButtonsState, this), true);

				setTimeout(function() {
					this.rect = this.layout.getBoundingClientRect();
					this.layout.classList.remove("landing-ui-transition");
				}.bind(this), 100);
			}

			BX.Landing.UI.Panel.BaseButtonPanel.prototype.hide.call(this, arguments);
			onHide();
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

		getFormat: function()
		{
			var element = getSelectedElement();
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
		}
	};
})();