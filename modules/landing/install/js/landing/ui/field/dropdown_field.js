;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var setTextContent = BX.Landing.Utils.setTextContent;

	var escapeText = BX.Landing.Utils.escapeText;
	var data = BX.Landing.Utils.data;
	var offsetTop = BX.Landing.Utils.offsetTop;
	var offsetLeft = BX.Landing.Utils.offsetLeft;
	var bind = BX.Landing.Utils.bind;
	var unbind = BX.Landing.Utils.unbind;

	var Menu = BX.Landing.UI.Tool.Menu;

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

		setTextContent(this.input, this.items[0].name);
		data(this.input, "value", this.items[0].value);

		if (this.content !== "")
		{
			this.setValue(this.content);
			this.onMouseWheel = this.onMouseWheel.bind(this);
		}
	};

	BX.Landing.UI.Field.Dropdown.prototype = {
		constructor: BX.Landing.UI.Field.Dropdown,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		onInputClick: function(event)
		{
			event.stopPropagation();
			if (!this.popup || (this.popupRoot && !this.popupRoot.contains(this.popup.popupWindow.popupContainer)))
			{
				this.popup = new Menu({
					id: "dropdown_" + (+new Date()),
					bindElement: this.input,
					items: this.items.map(function(item) {
						return {
							text: item.html ? item.html : escapeText(item.name),
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

				this.popupRoot = this.layout.parentElement.parentElement.parentElement;
				this.popupRoot.appendChild(this.popup.popupWindow.popupContainer);
				this.popupRoot.style.position = "relative";
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

			this.popup.layout.menuContainer.style.maxHeight = "calc((36px * 5) + 16px)";
			this.popup.popupWindow.contentContainer.style.overflowX = "hidden";

			bind(this.popup.popupWindow.popupContainer, "mouseover", this.onMouseOver.bind(this));
			bind(this.popup.popupWindow.popupContainer, "mouseleave", this.onMouseLeave.bind(this));

			var rect = this.input.getBoundingClientRect();
			var left = offsetLeft(this.input, this.popupRoot);
			var top = offsetTop(this.input, this.popupRoot);
			this.popup.popupWindow.popupContainer.style.top = top + rect.height + "px";
			this.popup.popupWindow.popupContainer.style.left = left + "px";
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

			return this.items[0].value;
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
		},

		/**
		 * Handles mouse over event
		 */
		onMouseOver: function()
		{
			var mouseEvent = "onwheel" in window ? "wheel" : "mousewheel";
			bind(this.popup.popupWindow.popupContainer, mouseEvent, this.onMouseWheel);
			bind(this.popup.popupWindow.popupContainer, "touchmove", this.onMouseWheel);
		},


		/**
		 * Handles mouse leave event
		 */
		onMouseLeave: function()
		{
			var mouseEvent = "onwheel" in window ? "wheel" : "mousewheel";
			unbind(this.popup.popupWindow.popupContainer, mouseEvent, this.onMouseWheel);
			unbind(this.popup.popupWindow.popupContainer, "touchmove", this.onMouseWheel);
		},


		/**
		 * Handle mouse wheel event
		 * @param event
		 */
		onMouseWheel: function(event)
		{
			event.stopPropagation();
			event.preventDefault();

			if (this.popup)
			{
				var delta = BX.Landing.UI.Panel.Content.getDeltaFromEvent(event);
				var scrollTop = this.popup.popupWindow.contentContainer.scrollTop;

				requestAnimationFrame(function() {
					this.popup.popupWindow.contentContainer.scrollTop = scrollTop - delta.y;
				}.bind(this));
			}
		}
	};
})();