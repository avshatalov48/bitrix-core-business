;(function() {
	"use strict";

	BX.namespace("BX.Landing");


	var escapeText = BX.Landing.Utils.escapeText;
	var headerTagMatcher = BX.Landing.Utils.Matchers.headerTag;
	var russianTextMatcher = BX.Landing.Utils.Matchers.russianText;
	var changeTagName = BX.Landing.Utils.changeTagName;
	var textToPlaceholders = BX.Landing.Utils.textToPlaceholders;

	/**
	 * Implements interface for works with text node of blocks
	 *
	 * @param {nodeOptions} options
	 *
	 * @extends {BX.Landing.Block.Node}
	 * @inheritDoc
	 */
	BX.Landing.Block.Node.Text = function(options)
	{
		BX.Landing.Block.Node.apply(this, arguments);

		this.onClick = this.onClick.bind(this);
		this.onPaste = this.onPaste.bind(this);
		this.onDrop = this.onDrop.bind(this);
		this.onInput = this.onInput.bind(this);
		this.onMousedown = this.onMousedown.bind(this);
		this.onMouseup = this.onMouseup.bind(this);

		// Bind on node events
		this.node.addEventListener("mousedown", this.onMousedown);
		this.node.addEventListener("click", this.onClick);
		this.node.addEventListener("paste", this.onPaste);
		this.node.addEventListener("drop", this.onDrop);
		this.node.addEventListener("input", this.onInput);
		this.node.addEventListener("keydown", this.onInput);

		document.addEventListener("mouseup", this.onMouseup);
	};


	/**
	 * Stores node with active editor
	 * @type {?BX.Landing.Block.Node.Text}
	 */
	BX.Landing.Block.Node.Text.currentNode = null;


	BX.Landing.Block.Node.Text.prototype = {
		__proto__: BX.Landing.Block.Node.prototype,
		superClass: BX.Landing.Block.Node.prototype,
		constructor: BX.Landing.Block.Node.Text,


		/**
		 * Handles allow inline edit event
		 */
		onAllowInlineEdit: function()
		{
			// Show title "Click to edit" for node
			this.node.setAttribute("title", escapeText(BX.message("LANDING_TITLE_OF_TEXT_NODE")));
		},


		/**
		 * Handles change event
		 * @param {boolean} [preventAdjustPosition]
		 * @param {boolean} [preventHistory]
		 */
		onChange: function(preventAdjustPosition, preventHistory)
		{
			this.superClass.onChange.call(this, arguments);

			if (this.containsRussianText())
			{
				var buttons = [this.getGlavredButton(), this.getDesignButton()];
				BX.Landing.UI.Panel.EditorPanel.getInstance().show(this.node, null, buttons);
			}

			if (!preventAdjustPosition)
			{
				BX.Landing.UI.Panel.EditorPanel.getInstance().adjustPosition(this.node);
			}

			if (!preventHistory)
			{
				BX.Landing.History.getInstance().push(
					new BX.Landing.History.Entry({
						block: top.BX.Landing.Block.storage.getByChildNode(this.node).id,
						selector: this.selector,
						command: "editText",
						undo: this.lastValue,
						redo: this.getValue()
					})
				);
			}
		},


		onInput: function(event)
		{
			clearTimeout(this.inputTimeout);

			var key = event.keyCode || event.which;

			if (!(key === 90 && (top.window.navigator.userAgent.match(/win/i) ? event.ctrlKey : event.metaKey)))
			{
				this.inputTimeout = setTimeout(function() {
					if (this.lastValue !== this.getValue())
					{
						this.onChange(true);
						this.lastValue = this.getValue();
					}
				}.bind(this), 400);
			}
		},


		/**
		 * Handles escape press event
		 */
		onEscapePress: function()
		{
			// Hide editor by press on Escape button
			if (this.isEditable())
			{
				if (this === BX.Landing.Block.Node.Text.currentNode)
				{
					BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
				}

				this.disableEdit();
			}
		},


		/**
		 * Handles drop event on this node
		 *
		 * @param {DragEvent} event
		 */
		onDrop: function(event)
		{
			// Prevents drag and drop any content into editor area
			event.preventDefault();
		},


		/**
		 * Handles paste event on this node
		 *
		 * @param {ClipboardEvent} event
		 * @param {function} event.preventDefault
		 * @param {object} event.clipboardData
		 */
		onPaste: function(event)
		{
			event.preventDefault();

			// Prevents XSS and prevents insert potential dangerously code
			if (event.clipboardData && event.clipboardData.getData)
			{
				document.execCommand("insertText", false, event.clipboardData.getData("text/plain"));
			}
			else
			{
				// ie11
				document.execCommand("paste", true, window.clipboardData.getData("text"));
			}

			this.onChange();
		},


		/**
		 * Handles click on document
		 */
		onDocumentClick: function(event)
		{
			if (this.isEditable() && !this.fromNode)
			{
				BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
				this.disableEdit();
			}

			this.fromNode = false;
		},


		onMousedown: function(event)
		{
			this.fromNode = true;

			if (this.manifest.allowInlineEdit !== false &&
				BX.Landing.Main.getInstance().isControlsEnabled())
			{
				event.stopPropagation();

				this.enableEdit();
				BX.Landing.UI.Tool.ColorPicker.hideAll();
				BX.Landing.UI.Button.FontAction.hideAll();

				if (event.target.nodeName === "A" || event.target.parentElement.nodeName === "A")
				{
					var range = document.createRange();
					range.selectNode(event.target);
					window.getSelection().removeAllRanges();
					window.getSelection().addRange(range);
				}
			}
		},


		onMouseup: function()
		{
			setTimeout(function() {
				this.fromNode = false;
			}.bind(this), 10);
		},


		/**
		 * Click on field - switch edit mode.
		 */
		onClick: function(event)
		{
			event.stopPropagation();
			event.preventDefault();
			this.fromNode = false;

			if (event.target.nodeName === "A" || event.target.parentElement.nodeName === "A")
			{
				var range = document.createRange();
				range.selectNode(event.target);
				window.getSelection().removeAllRanges();
				window.getSelection().addRange(range);
			}
		},


		/**
		 * Checks that is editable
		 * @return {boolean}
		 */
		isEditable: function()
		{
			return this.node.isContentEditable;
		},


		/**
		 * Checks that Glavred should be enabled
		 * @return {boolean}
		 */
		containsRussianText: function()
		{
			return false;
			// return russianTextMatcher.test(this.node.innerText);
		},


		/**
		 * Enables edit mode
		 */
		enableEdit: function()
		{
			if (!this.isEditable() && !BX.Landing.UI.Panel.StylePanel.getInstance().isShown())
			{
				if (this !== BX.Landing.Block.Node.Text.currentNode && BX.Landing.Block.Node.Text.currentNode !== null)
				{
					BX.Landing.Block.Node.Text.currentNode.disableEdit();
				}

				BX.Landing.Block.Node.Text.currentNode = this;

				var buttons = [];

				if (this.containsRussianText())
				{
					buttons.push(this.getGlavredButton());
				}

				buttons.push(this.getDesignButton());

				if (this.isHeader())
				{
					buttons.push(this.getChangeTagButton());
					this.getChangeTagButton().changeHandler = this.onChangeTag.bind(this);
				}

				BX.Landing.UI.Panel.EditorPanel.getInstance().show(this.node, null, buttons);

				this.lastValue = this.getValue();
				this.node.contentEditable = true;
				this.node.focus();

				this.node.setAttribute("title", "");
			}
		},


		/**
		 * Gets design button for editor
		 * @return {BX.Landing.UI.Button.Design}
		 */
		getDesignButton: function()
		{
			if (!this.designButton)
			{
				this.designButton = new BX.Landing.UI.Button.Design("design", {
					html: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_DESIGN"),
					attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_DESIGN")},
					onClick: function() {
						BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
						this.disableEdit();
						this.onDesignShow(this.manifest.code);
					}.bind(this)
				});
			}

			return this.designButton;
		},


		/**
		 * Gets Glavred button for editor
		 * @return {BX.Landing.UI.Button.Design}
		 */
		getGlavredButton: function()
		{
			if (!this.glavredButton)
			{
				this.glavredButton = new BX.Landing.UI.Button.EditorAction("glavred", {
					html: "<span class=\"fa fa-check\"></span> " + BX.message("LANDING_TITLE_OF_EDITOR_ACTION_GLAVRED"),
					attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_GLAVRED_TITLE")},
					onClick: this.onGlavredButtonClick.bind(this)
				});
			}

			return this.glavredButton;
		},


		onGlavredButtonClick: function()
		{
			var buttonHTML = this.glavredButton.layout.innerHTML;
			var buttonRect = this.glavredButton.layout.getBoundingClientRect();
			var loader = new BX.Landing.UI.Card.Loader();
			this.glavredButton.layout.style.width = buttonRect.width + "px";
			this.glavredButton.layout.style.overflow = "hidden";
			this.glavredButton.layout.innerHTML = "";
			this.glavredButton.layout.appendChild(loader.layout);
			loader.layout.classList.add("landing-ui-loader-small");
			loader.show();

			BX.Landing.Client.Glavred.getInstance().proofread(this.node).then(function() {
				this.glavredButton.layout.classList.add("landing-ui-active");
				this.glavredButton.layout.innerHTML = buttonHTML;
			}.bind(this));
		},


		/**
		 * Disables edit mode
		 */
		disableEdit: function()
		{
			if (this.isEditable())
			{
				if (this.containsRussianText() && this.glavredButton)
				{
					this.glavredButton.layout.classList.remove("landing-ui-active");
					BX.Landing.Client.Glavred.getInstance().removeMarkup(this.node);
				}

				this.node.contentEditable = false;

				if (this.lastValue !== this.getValue())
				{
					this.onChange();
					this.lastValue = this.getValue();
				}

				if (this.isAllowInlineEdit())
				{
					this.node.setAttribute("title", escapeText(BX.message("LANDING_TITLE_OF_TEXT_NODE")));
				}
			}
		},


		/**
		 * Gets form field
		 * @return {BX.Landing.UI.Field.Text}
		 */
		getField: function()
		{
			if (!this.field)
			{
				this.field = new BX.Landing.UI.Field.Text({
					selector: this.selector,
					title: this.manifest.name,
					content: this.node.innerHTML,
					bind: this.node
				});

				if (this.isHeader())
				{
					this.field.changeTagButton = this.getChangeTagButton();
				}
			}
			else
			{
				this.field.setValue(this.node.innerHTML);
			}

			return this.field;
		},


		/**
		 * Sets node value
		 * @param value
		 * @param {?boolean} [preventSave = false]
		 * @param {?boolean} [preventHistory = false]
		 */
		setValue: function(value, preventSave, preventHistory)
		{
			this.preventSave(preventSave);
			this.lastValue = this.isSavePrevented() ? this.getValue() : this.lastValue;
			this.node.innerHTML = value;
			this.onChange(false, preventHistory);
		},


		/**
		 * Gets node value
		 * @return {string}
		 */
		getValue: function()
		{
			return textToPlaceholders(BX.util.htmlspecialcharsback(this.node.innerHTML));
		},


		/**
		 * Checks that this node is header
		 * @return {boolean}
		 */
		isHeader: function()
		{
			return headerTagMatcher.test(this.node.nodeName);
		},

		/**
		 * Gets Glavred button for editor
		 * @return {BX.Landing.UI.Button.Design}
		 */
		getChangeTagButton: function()
		{
			if (!this.changeTagButton)
			{
				this.changeTagButton = new BX.Landing.UI.Button.ChangeTag("changeTag", {
					html: this.node.nodeName,
					attrs: {title: BX.message("LANDING_TITLE_OF_EDITOR_ACTION_CHANGE_TAG")},
					onChange: this.onChangeTag.bind(this)
				});
			}

			this.changeTagButton.insertAfter = "strikeThrough";

			this.changeTagButton.activateItem(this.node.nodeName);

			return this.changeTagButton;
		},


		/**
		 * Handles change tag event
		 * @param value
		 */
		onChangeTag: function(value)
		{
			this.node = changeTagName(this.node, value);

			this.node.addEventListener("mousedown", this.onMousedown);
			this.node.addEventListener("click", this.onClick);
			this.node.addEventListener("paste", this.onPaste);
			this.node.addEventListener("drop", this.onDrop);
			this.node.addEventListener("input", this.onInput);
			this.node.addEventListener("keydown", this.onInput);

			if (!this.getField().isEditable())
			{
				this.disableEdit();
				this.enableEdit();
			}

			var data = {};
			data[this.selector] = value;
			this.changeOptionsHandler(data);
		}
	};

})();