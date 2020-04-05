;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var isFunction = BX.Landing.Utils.isFunction;
	var isBoolean = BX.Landing.Utils.isBoolean;
	var clone = BX.Landing.Utils.clone;
	var bind = BX.Landing.Utils.bind;
	var remove = BX.Landing.Utils.remove;
	var escapeHtml = BX.Landing.Utils.escapeHtml;
	var fireCustomEvent = BX.Landing.Utils.fireCustomEvent;

	/**
	 * Implements interface for works with text field
	 *
	 * @extends {BX.Landing.UI.Field.BaseField}
	 * 
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Field.Text = function(data)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);

		// Element for live reload changes
		this.bind = data.bind;

		// Additional buttons for editor
		this.changeTagButton = data.changeTagButton;

		// Make input event external handler
		this.onInputHandler = isFunction(data.onInput) ? data.onInput : (function() {});
		this.onValueChangeHandler = isFunction(data.onValueChange) ? data.onValueChange : (function() {});

		// Set text only
		this.textOnly = isBoolean(data.textOnly) ? data.textOnly : false;
		this.content = this.textOnly ? escapeHtml(this.content) : this.content;
		this.input.innerHTML = this.content;

		// Make event handlers
		this.onInputClick = this.onInputClick.bind(this);
		this.onInputMousedown = this.onInputMousedown.bind(this);
		this.onDocumentMouseup = this.onDocumentMouseup.bind(this);
		this.onInputInput = this.onInputInput.bind(this);
		this.onDocumentClick = this.onDocumentClick.bind(this);
		this.onDocumentKeydown = this.onDocumentKeydown.bind(this);
		this.onInputKeydown = this.onInputKeydown.bind(this);

		// Bind on field events
		bind(this.input, "click", this.onInputClick);
		bind(this.input, "mousedown", this.onInputMousedown);
		bind(this.input, "input", this.onInputInput);
		bind(this.input, "keydown", this.onInputKeydown);

		// Bind on document events
		bind(document, "click", this.onDocumentClick);
		bind(document, "keydown", this.onDocumentKeydown);
		bind(document, "mouseup", this.onDocumentMouseup);
	};


	BX.Landing.UI.Field.Text.prototype = {
		constructor: BX.Landing.UI.Field.Text,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,
		/**
		 * Handles input event on input field
		 */
		onInputInput: function()
		{
			this.onInputHandler(this.input.innerText);
			this.onValueChangeHandler(this);

			fireCustomEvent(this, "BX.Landing.UI.Field:change", [this.getValue()]);
		},


		/**
		 * Handles event on document key down
		 * @param {KeyboardEvent} event
		 */
		onDocumentKeydown: function(event)
		{
			/**
			 * Disable edit mode by escape
			 */
			if (event.keyCode === 27)
			{
				if (this.isEditable())
				{
					if (this === BX.Landing.UI.Field.BaseField.currentField)
					{
						BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
					}

					this.disableEdit();
				}
			}
		},


		onInputKeydown: function(event)
		{
			/**
			 * Disable enter for text-only mode
			 */
			if (event.keyCode === 13)
			{
				if (this.isTextOnly())
				{
					event.preventDefault();
				}
			}
		},


		/**
		 * Enables mode plaintext-only
		 */
		enableTextOnly: function()
		{
			this.textOnly = true;
			this.input.innerHTML = BX.util.trim(this.input.innerText);
		},


		/**
		 * Disables mode plaintext-only
		 */
		disableTextOnly: function()
		{
			this.textOnly = false;
		},


		/**
		 * Checks that plaintext-only is enabled
		 * @return {boolean}
		 */
		isTextOnly: function()
		{
			return this.textOnly;
		},


		/**
		 * Handles document click event
		 */
		onDocumentClick: function()
		{
			if (this.isEditable() && !this.fromInput)
			{
				if (this === BX.Landing.UI.Field.BaseField.currentField)
				{
					BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
				}

				this.disableEdit();
			}

			this.fromInput = false;
		},


		onDocumentMouseup: function()
		{
			setTimeout(function() {
				this.fromInput = false;
			}.bind(this), 10);
		},


		/**
		 * Handles input click event
		 * @param {MouseEvent} event
		 */
		onInputClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
			this.fromInput = false;
		},


		onInputMousedown: function(event)
		{
			this.enableEdit();

			BX.Landing.UI.Tool.ColorPicker.hideAll();
			BX.Landing.UI.Button.FontAction.hideAll();

			requestAnimationFrame(function() {
				if (event.target.nodeName === "A")
				{
					var range = document.createRange();
					range.selectNode(event.target);
					window.getSelection().removeAllRanges();
					window.getSelection().addRange(range);
				}
			});

			this.fromInput = true;

			event.stopPropagation();
		},


		/**
		 * Enables edit mode
		 */
		enableEdit: function()
		{
			if (!this.isEditable())
			{
				if (this !== BX.Landing.UI.Field.BaseField.currentField && BX.Landing.UI.Field.BaseField.currentField !== null)
				{
					// Disable preview active field
					BX.Landing.UI.Field.BaseField.currentField.disableEdit();
				}

				// Set this field as active
				BX.Landing.UI.Field.BaseField.currentField = this;

				// Hide editor panel if current field works with plaintext-only mode
				if (!this.isTextOnly())
				{
					if (this.changeTagButton)
					{
						this.changeTagButton.onChangeHandler = this.onChangeTag.bind(this);
					}

					BX.Landing.UI.Panel.EditorPanel.getInstance().show(this.layout, null, this.changeTagButton ? [this.changeTagButton] : null);
					this.input.contentEditable = true;
				}
				else
				{
					BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
					this.input.contentEditable = true;
				}

				// Adjust input focus
				this.input.focus();
			}
		},


		onChangeTag: function(value)
		{
			this.tag = value;
		},


		/**
		 * Disables edit mode
		 */
		disableEdit: function()
		{
			this.input.contentEditable = false;
		},


		/**
		 * Checks that edit mode is enabled
		 * @return {boolean}
		 */
		isEditable: function()
		{
			return this.input.isContentEditable;
		},


		reset: function()
		{
			this.setValue("");
		},


		/**
		 * @param {HTMLElement} element
		 * @return {HTMLElement}
		 */
		adjustTags: function(element)
		{
			if (element.lastChild && element.lastChild.nodeName === "BR")
			{
				remove(element.lastChild);
				this.adjustTags(element);
			}

			return element;
		},


		getValue: function()
		{
			return this.adjustTags(clone(this.input)).innerHTML;
		}
	}
})();