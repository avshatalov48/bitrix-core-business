;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Tool");


	/**
	 * Implements interface for works with color picker
	 *
	 * @param {BX.Landing.UI.Button.BaseButton} button
	 * @param {function} onChange
	 *
	 * @constructor
	 */
	BX.Landing.UI.Tool.ColorPicker = function(button, onChange)
	{
		this.picker = new BX.ColorPicker({
			bindElement: button.layout,
			popupOptions: BX.Landing.UI.Tool.ColorPicker.getDefaultPopupOptions(button),
			onColorSelected: BX.delegate(this.onColorSelected, this),
			colors: BX.Landing.UI.Tool.ColorPicker.getDefaultColors()
		});
		this.button = button;
		this.onChangeHandler = onChange;
		this.pickerWindow = this.picker.getPopupWindow();
		this.range = document.createRange();
		BX.Landing.UI.Tool.ColorPicker.activePickers.add(this);
	};


	/**
	 * Gets default popupWindow options for color picker
	 * @return {{angle: boolean, position: {top: number, left: number}}}
	 */
	BX.Landing.UI.Tool.ColorPicker.getDefaultPopupOptions = function(button)
	{
		return {angle: false, position: {top: 0, left: 0}, zIndex: -678};
	};


	/**
	 * Stores initiated pickers as collection
	 *
	 * @static
	 * @type {BX.Landing.Collection.BaseCollection}
	 */
	BX.Landing.UI.Tool.ColorPicker.activePickers = new BX.Landing.Collection.BaseCollection();


	/**
	 * Hides all showed pickers
	 * @static
	 */
	BX.Landing.UI.Tool.ColorPicker.hideAll = function()
	{
		BX.Landing.UI.Tool.ColorPicker.activePickers.forEach(function(picker) {
			picker.hide();
		});
	};


	/**
	 * Gets default colors
	 * @static
	 * @return {Array} - colors list
	 */
	BX.Landing.UI.Tool.ColorPicker.getDefaultColors = function()
	{
		return [
			["#f5f5f5", "#eeeeee", "#e0e0e0", "#9e9e9e", "#757575", "#616161", "#212121"],
			["#cfd8dc", "#b0bec5", "#90a4ae", "#607d8b", "#546e7a", "#455a64", "#263238"],
			["#d7ccc8", "#bcaaa4", "#a1887f", "#795548", "#6d4c41", "#5d4037", "#3e2723"],
			["#ffccbc", "#ffab91", "#ff8a65", "#ff5722", "#f4511e", "#e64a19", "#bf360c"],
			["#ffe0b2", "#ffcc80", "#ffb74d", "#ff9800", "#fb8c00", "#f57c00", "#e65100"],
			["#ffecb3", "#ffe082", "#ffd54f", "#ffc107", "#ffb300", "#ffa000", "#ff6f00"],
			["#fff9c4", "#fff59d", "#fff176", "#ffeb3b", "#fdd835", "#fbc02d", "#f57f17"],
			["#f0f4c3", "#e6ee9c", "#dce775", "#cddc39", "#c0ca33", "#afb42b", "#827717"],
			["#dcedc8", "#c5e1a5", "#aed581", "#8bc34a", "#7cb342", "#689f38", "#33691e"],
			["#c8e6c9", "#a5d6a7", "#81c784", "#4caf50", "#43a047", "#388e3c", "#1b5e20"],
			["#b2dfdb", "#80cbc4", "#4db6ac", "#009688", "#00897b", "#00796b", "#004d40"],
			["#b2ebf2", "#80deea", "#4dd0e1", "#00bcd4", "#00acc1", "#0097a7", "#006064"],
			["#b3e5fc", "#81d4fa", "#4fc3f7", "#03a9f4", "#039be5", "#0288d1", "#01579b"],
			["#bbdefb", "#90caf9", "#64b5f6", "#2196f3", "#1e88e5", "#1976d2", "#0d47a1"],
			["#c5cae9", "#9fa8da", "#7986cb", "#3f51b5", "#3949ab", "#303f9f", "#1a237e"],
			["#d1c4e9", "#b39ddb", "#9575cd", "#673ab7", "#5e35b1", "#512da8", "#311b92"],
			["#e1bee7", "#ce93d8", "#ba68c8", "#9c27b0", "#8e24aa", "#7b1fa2", "#4a148c"],
			["#f8bbd0", "#f48fb1", "#f06292", "#e91e63", "#d81b60", "#c2185b", "#880e4f"],
			["#ffcdd2", "#ef9a9a", "#e57373", "#f44336", "#e53935", "#d32f2f", "#b71c1c"]
		].map(function(item, index, arr) {
			return arr.map(function(row) {
				return row[index];
			});
		});
	};


	BX.Landing.UI.Tool.ColorPicker.prototype = {
		/**
		 * @private
		 * @param {string} position
		 */
		adjustPosition: function(position)
		{
			BX.DOM.read(function() {
				var pickerRect = this.pickerWindow.popupContainer.getBoundingClientRect();
				var parentRect = this.button.layout.parentNode.getBoundingClientRect();
				var offsetLeft = Math.abs(pickerRect.width - parentRect.width);
				var props = {};

				props["left"] = parentRect.left + (offsetLeft / 2) + "px";

				if (position === "fixed")
				{
					var buttonRect = this.button.layout.getBoundingClientRect();
					props["top"] = buttonRect.bottom + "px";
					props["position"] = "fixed";
				}
				else
				{
					var buttonPos = BX.pos(this.button.layout);
					props["top"] = buttonPos.bottom + "px";
				}


				BX.DOM.write(function() {
					for (var prop in props)
					{
						this.pickerWindow.popupContainer.style[prop] = props[prop];
					}
				}.bind(this));
			}.bind(this));
		},

		/**
		 * Shows color picker
		 * @param {string} position
		 */
		show: function(position)
		{
			this.picker.open();
			this.adjustPosition(position);
			this.range = document.getSelection().getRangeAt(0);
		},


		/**
		 * Hides color picker
		 */
		hide: function()
		{
			this.picker.close();
		},


		/**
		 * Checks that color picker is shown
		 * @return {boolean}
		 */
		isShown: function()
		{
			return this.pickerWindow.isShown();
		},


		/**
		 * Handles event on color selected
		 * @private
		 * @param {string} color
		 * @param {BX.ColorPicker} picker
		 */
		onColorSelected: function(color, picker)
		{
			document.getSelection().removeAllRanges();
			document.getSelection().addRange(this.range);

			if (BX.type.isFunction(this.onChangeHandler))
			{
				this.onChangeHandler(color, picker);
			}
		}
	};
})();