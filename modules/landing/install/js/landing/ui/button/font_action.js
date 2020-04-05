;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");


	/**
	 * Implements interface for works with font settings
	 *
	 * @extends {BX.Landing.UI.Button.EditorAction}
	 *
	 * @param {string} id - Action id
	 * @param {?object} [options]
	 *
	 * @constructor
	 */
	BX.Landing.UI.Button.FontAction = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.layout.classList.add("landing-ui-button-editor-action-font");
		this.content = BX.create("div", {props: {className: "landing-ui-button-editor-action-font-popup-content"}});
		this.fontSize = new BX.Landing.UI.Field.Range({
			title: BX.Landing.Loc.getMessage("EDITOR_ACTION_FIELD_LABEL_FONT_SIZE"),
			items: Array.apply(null, {length: 160}).map(function(item, index) {
				return {value: index, name: index};
			}),
			jsDD: window.jsDD,
			property: "font-size",
			onDragStart: this.onDragStart.bind(this),
			onDragEnd: this.onDragEnd.bind(this),
			onChange: this.onChange.bind(this)
		});
		BX.Landing.UI.Button.FontAction.instances.push(this);
	};

	BX.Landing.UI.Button.FontAction.instances = [];

	BX.Landing.UI.Button.FontAction.hideAll = function()
	{
		BX.Landing.UI.Button.FontAction.instances.forEach(function(button) {
			if (button.popup)
			{
				button.popup.close();
			}
		});
	};

	BX.Landing.UI.Button.FontAction.prototype = {
		constructor: BX.Landing.UI.Button.FontAction,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,


		onDragStart: function()
		{
			this.popup.popupContainer.classList.add("landing-ui-fade");
		},

		onDragEnd: function()
		{
			this.popup.popupContainer.classList.remove("landing-ui-fade");
		},

		onChange: function(value, items, postfix, property)
		{
			if (property === "font-size")
			{
				var selection = window.getSelection();
				var range = selection.getRangeAt(0);
				var editable = BX.Landing.UI.Panel.EditorPanel.getInstance().currentElement.contains(range.startContainer);

				if (selection.toString() && editable)
				{
					document.execCommand("fontSize", false, value);
					range = window.getSelection().getRangeAt(0);
					var wrapper = range.startContainer.parentNode;
					wrapper.style.fontSize = value + "px";
				}
			}
		},


		/**
		 * Handles event on this button click
		 * @param {MouseEvent} event
		 */
		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			if (!this.popup)
			{
				this.popup = this.createPopup();
				this.popup.popupContainer.addEventListener("click", this.onPopupClick.bind(this));
				this.popup.popupContainer.classList.add("landing-ui-button-editor-action-font-popup");
				this.popup.contentContainer.appendChild(this.content);
				this.content.appendChild(this.fontSize.layout);
			}

			if (!this.popup.isShown())
			{
				this.popup.show();
				var position = BX.Landing.UI.Panel.EditorPanel.getInstance().isFixed() ? "fixed" : "relative";
				this.adjustPosition(position);
				BX.Landing.UI.Button.ColorAction.hideAll();

				var selection = window.getSelection();
				var range = selection.getRangeAt(0);

				var editable = BX.Landing.UI.Panel.EditorPanel.getInstance().currentElement.contains(range.startContainer);

				if (editable)
				{
					var wrapper = range.startContainer.parentNode;
					var size = parseInt(getComputedStyle(wrapper).getPropertyValue("font-size"));
					this.fontSize.setValue(size, true);
				}
			}
			else
			{
				this.popup.close();
			}
		},


		onPopupClick: function(event)
		{
			event.stopPropagation();
		},


		/**
		 * @private
		 * @param {string} position
		 */
		adjustPosition: function(position)
		{
			BX.DOM.read(function() {
				var popupRect = this.popup.popupContainer.getBoundingClientRect();
				var parentRect = this.layout.parentNode.getBoundingClientRect();
				var offsetLeft = Math.abs(popupRect.width - parentRect.width);
				var props = {};

				props["left"] = parentRect.left + (offsetLeft / 2) + "px";

				if (position === "fixed")
				{
					var buttonRect = this.layout.getBoundingClientRect();
					props["top"] = buttonRect.bottom + "px";
					props["position"] = "fixed";
				}
				else
				{
					var buttonPos = BX.pos(this.layout);
					props["top"] = buttonPos.bottom + "px";
				}


				BX.DOM.write(function() {
					for (var prop in props)
					{
						this.popup.popupContainer.style[prop] = props[prop];
					}
				}.bind(this));
			}.bind(this));
		},

		createPopup: function()
		{
			return new BX.PopupWindow(
				"landing_font_editor_popup",
				this.layout,
				{
					autoHide: true,
					closeByEsc: true,
					noAllPaddings: true,
					width: 463,
					zIndex: -979
				}
			);
		}
	};
})();