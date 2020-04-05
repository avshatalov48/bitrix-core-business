;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	/**
	 * Implements interface for works with inline dropdown
	 *
	 * @extends {BX.Landing.UI.Field.BaseField}
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Field.DropdownInline = function(data)
	{
		this.items = "items" in data && data.items ? data.items : {};
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		this.popup = null;
		this.input.addEventListener("click", this.onInputClick.bind(this));

		if (BX.type.isPlainObject(this.items))
		{
			var keys = Object.keys(this.items);
			this.items = keys.map(function(key) {
				return {name: this.items[key], value: key};
			}, this);
		}

		this.input.innerText = this.items[0].name;
		this.input.dataset.value = this.items[0].value;
		this.setValue(this.content);
	};

	BX.Landing.UI.Field.DropdownInline.prototype = {
		constructor: BX.Landing.UI.Field.DropdownInline,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		onInputClick: function()
		{
			if (!this.popup)
			{
				this.popup = BX.PopupMenu.create(
					this.selector+"_dropdown_popup",
					this.input,
					this.items.map(function(item) {
						return {
							text: item.name,
							onclick: function() {
								this.onItemClick(item)
							}.bind(this)
						}
					}, this)
				);

				this.layout.appendChild(this.popup.popupWindow.popupContainer);
			}

			this.popup.show();

			var rect = BX.pos(this.input, this.layout);
			this.popup.popupWindow.popupContainer.style.top = rect.bottom + "px";
			this.popup.popupWindow.popupContainer.style.left = rect.left + "px";
		},

		closePopup: function()
		{
			if (this.popup)
			{
				this.popup.close();
			}
		},

		onItemClick: function(item)
		{
			this.input.innerText = item.name;
			this.input.dataset.value = item.value;
			this.popup.close();
			BX.fireEvent(this.input, "input");
		},

		/**
		 * @inheritDoc
		 */
		getValue: function()
		{
			return typeof this.input.dataset.value !== "undefined" ? this.input.dataset.value : this.items[0].value;
		},

		setValue: function(value)
		{
			this.items.forEach(function(item) {
				if (value === item.value)
				{
					this.input.innerText = item.name;
					this.input.dataset.value = item.value;
				}
			}, this);
		}
	};
})();