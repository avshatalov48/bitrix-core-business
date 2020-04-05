;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Tool");


	var isString = BX.Landing.Utils.isString;
	var isEmpty = BX.Landing.Utils.isEmpty;
	var addClass = BX.Landing.Utils.addClass;
	var show = BX.Landing.Utils.Show;
	var hide = BX.Landing.Utils.Hide;


	/**
	 * Implements interface for works with BX.PopupWindow
	 * @extends {BX.PopupWindow}
	 * @param {object} options
	 * @constructor
	 */
	BX.Landing.UI.Tool.Popup = function(options)
	{
		options.bindElement = options.bindElement || null;

		BX.PopupWindow.apply(this, [options.id, options.bindElement, options]);

		addClass(this.popupContainer, "landing-ui-popup");

		if (!isEmpty(options.className) && isString(options.className))
		{
			addClass(this.popupContainer, options.className);
		}
	};


	BX.Landing.UI.Tool.Popup.prototype = {
		constructor: BX.Landing.UI.Tool.Popup,
		__proto__: BX.PopupWindow.prototype,

		show: function()
		{
			BX.PopupWindow.prototype.show.call(this);
			return Promise.resolve();
		},

		close: function()
		{
			BX.PopupWindow.prototype.close.call(this);
			return Promise.resolve();
		}
	}
})();