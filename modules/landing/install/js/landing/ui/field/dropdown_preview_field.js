;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	/**
	 * Implements interface for works with dropdown field in editor
	 *
	 * @extends {BX.Landing.UI.Field.BaseField}
	 *
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Field.DropdownPreview = function(data)
	{
		this.items = "items" in data && data.items ? data.items : {};
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		this.layout.classList.add("landing-ui-field-dropdown");
		this.layout.classList.add("landing-ui-field-dropdown-preview");
		this.frame = typeof data.frame === "object" ? data.frame : null;
		this.format = typeof data.format === "function" ? data.format : (function() {});
		this.postfix = typeof data.postfix === "string" ? data.postfix : "";
		this.property = typeof data.property === "string" ? data.property : "";
		this.changeHandler = typeof data.onChange === "function" ? data.onChange : (function() {});
		this.elements = [];

		this.setValue(this.items[0].value, true);
		this.input.innerText = this.items[0].name;

		this.input.addEventListener("click", this.onInputClick.bind(this));

		this.onFrameLoad();
	};

	BX.Landing.UI.Field.DropdownPreview.CreateSelect = function()
	{
		return BX.create("select", {props: {className: "landing-ui-field-input landing-ui-field-dropdown"}});
	};


	BX.Landing.UI.Field.DropdownPreview.prototype = {
		constructor: BX.Landing.UI.Field.DropdownPreview,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,
		superClass: BX.Landing.UI.Field.BaseField,


		/**
		 * Handles click event on field input
		 * @param {MouseEvent} event
		 */
		onInputClick: function(event)
		{
			event.preventDefault();

			if (!this.popup)
			{
				var self = this;
				var items = this.items.map(function(item) {
					return {
						text: item.name,
						className: "landing-ui-field-dropdown-preview-item",
						menuShowDelay: 0,
						subMenuOffsetX: 10,
						items: [
							{
								text: "<div class=\"landing-ui-field-dropdown-preview-item-preview "+item.value+"\">Text</div>",
								className: "landing-ui-field-dropdown-preview-item-child"
							}
						],
						onclick: function()
						{
							this.close();
							self.onChange(item);
						}
					}
				});

				this.popup = new BX.PopupMenuWindow({
					id: this.selector+this.property,
					bindElement: this.input,
					items: items,
					zIndex: 9000,
					angle: false,
					bindOptions: {
						forceBindPosition: true
					},
					events: {
						onPopupClose: function() {
							this.input.classList.remove("landing-ui-active")
						}.bind(this)
					}
				});

				this.input.parentNode.appendChild(this.popup.popupWindow.popupContainer);
			}

			this.input.classList.add("landing-ui-active");

			var rect = BX.pos(this.input, this.input.parentNode);
			this.popup.popupWindow.popupContainer.style.top = rect.bottom + "px";
			this.popup.popupWindow.popupContainer.style.left = "0px";
			this.popup.popupWindow.popupContainer.style.right = "";
		},


		/**
		 * Handles frame load event
		 */
		onFrameLoad: function()
		{
			this.elements = [].slice.call(this.frame.document.querySelectorAll(this.selector));

			if (this.elements.length)
			{
				this.items.some(function(item) {
					if (this.elements[0].classList.contains(item.value))
					{
						this.setValue(item.value, true);
					}
				}, this);
			}
		},


		/**
		 * Handles change event on field input
		 * @param {{name: string, value: string|number}} item
		 * @param {?boolean} [preventEvent = false]
		 */
		onChange: function(item, preventEvent)
		{
			this.setValue(item.value);

			this.input.innerText = item.name;

			if (!preventEvent)
			{
				this.changeHandler(this.getValue(), this.items, this.postfix, this.property);
				BX.fireEvent(this.layout, "input");
			}
		},


		/**
		 * Sets field value
		 * @param value
		 */
		setValue: function(value)
		{
			this.input.dataset.value = value;
		},


		/**
		 * Gets field value
		 * @return {?string}
		 */
		getValue: function()
		{
			return this.input.dataset.value;
		}
	}
})();