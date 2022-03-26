;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var setTextContent = BX.Landing.Utils.setTextContent;

	var escapeText = BX.Landing.Utils.escapeText;
	var data = BX.Landing.Utils.data;
	var offsetTop = BX.Landing.Utils.offsetTop;
	var offsetLeft = BX.Landing.Utils.offsetLeft;

	/**
	 * Implements interface for works with dropdown
	 *
	 * @extends {BX.Landing.UI.Field.BaseField}
	 * @param {object} options
	 * @constructor
	 */
	BX.Landing.UI.Field.Dropdown = function(options)
	{
		this.items = "items" in options && options.items ? options.items : {};
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		this.setEventNamespace('BX.Landing.UI.Field.Dropdown');
		this.subscribeFromOptions(BX.Landing.UI.Component.fetchEventsFromOptions(options));
		this.onChangeHandler = typeof options.onChange === "function" ? options.onChange : (function() {});
		this.layout.classList.add("landing-ui-field-dropdown");
		this.popup = null;
		this.input.addEventListener("click", this.onInputClick.bind(this));
		document.addEventListener("click", this.onDocumentClick.bind(this));
		var rootWindow = BX.Landing.PageObject.getRootWindow();
		rootWindow.document.addEventListener("click", this.onDocumentClick.bind(this));

		if (BX.type.isPlainObject(this.items))
		{
			var keys = Object.keys(this.items);
			this.items = keys.map(function(key) {
				return {name: this.items[key], value: key};
			}, this);
		}

		if (BX.Type.isArrayFilled(this.items))
		{
			setTextContent(this.input, this.items[0].name);
			data(this.input, "value", this.items[0].value);
		}
		else
		{
			setTextContent(this.input, BX.Landing.Loc.getMessage("LANDING_DROPDOWN_NOT_FILLED"));
			data(this.input, "value", "");
		}

		if (this.content !== "")
		{
			this.setValue(this.content);
		}
	};

	BX.Landing.UI.Field.Dropdown.prototype = {
		constructor: BX.Landing.UI.Field.Dropdown,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		onInputClick: function(event)
		{
			event.stopPropagation();
			if (
				!this.popup
				|| (!this.contentRoot && this.popupRoot && !this.popupRoot.contains(this.popup.popupWindow.popupContainer))
			)
			{
				this.popup = new BX.PopupMenuWindow({
					id: "dropdown_" + (+new Date()),
					bindElement: this.input,
					bindOptions: {
						forceBindPosition: true
					},
					targetContainer: this.contentRoot,
					maxHeight: 196,
					items: this.items.map(function(item) {
						return {
							html: item.html,
							text: !item.html ? escapeText(item.name) : undefined,
							onclick: function() {
								this.onItemClick(item)
							}.bind(this)
						}
					}, this),
					events: {
						onPopupClose: function() {
							this.input.classList.remove("landing-ui-active");
							this.layout.classList.remove("landing-ui-active");
						}.bind(this)
					}
				});

				if (!this.contentRoot)
				{
					this.popupRoot = this.layout.parentElement.parentElement.parentElement;
					this.popupRoot.appendChild(this.popup.popupWindow.popupContainer);
					this.popupRoot.style.position = "relative";
				}
			}

			this.layout.classList.add("landing-ui-active");
			this.input.classList.add("landing-ui-active");

			if (this.popup.popupWindow.isShown())
			{
				this.popup.close();
			}
			else
			{
				this.popup.show();
			}

			var rect = this.input.getBoundingClientRect();
			if (!this.contentRoot)
			{
				var left = offsetLeft(this.input, this.popupRoot);
				var top = offsetTop(this.input, this.popupRoot);
				this.popup.popupWindow.popupContainer.style.top = top + rect.height + "px";
				this.popup.popupWindow.popupContainer.style.left = left + "px";
			}
			this.popup.popupWindow.popupContainer.style.width = rect.width + "px";
		},


		onItemClick: function(item)
		{
			setTextContent(this.input, item.name);
			data(this.input, "value", item.value);
			this.popup.close();
			this.onChangeHandler(item.value, this.items, this.postfix, this.property);
			this.onValueChangeHandler(this);
			BX.fireEvent(this.input, "input");
			this.emit('onChange');
		},

		/**
		 * @inheritDoc
		 */
		getValue: function()
		{
			var value = this.input.dataset.value;

			if (value !== "undefined" && typeof value !== "undefined")
			{
				return value;
			}

			if (BX.Type.isArrayFilled(this.items))
			{
				return this.items[0].value;
			}
		},

		setValue: function(value)
		{
			this.items.forEach(function(item) {
				// noinspection EqualityComparisonWithCoercionJS
				if (value == item.value)
				{
					setTextContent(this.input, item.name);
					data(this.input, "value", item.value);
				}
			}, this);
		},


		/**
		 * @inheritDoc
		 * @return {boolean}
		 */
		isChanged: function()
		{
			// noinspection EqualityComparisonWithCoercionJS
			return this.content != this.getValue();
		},

		onDocumentClick: function()
		{
			if (this.popup)
			{
				this.popup.close();
			}
		}
	};
})();