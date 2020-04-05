;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Tool");

	var isString = BX.Landing.Utils.isString;
	var isEmpty = BX.Landing.Utils.isEmpty;
	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;
	var proxy = BX.Landing.Utils.proxy;


	/**
	 * Implements interface for works with BX.PopupMenuWindow
	 * @extends {BX.PopupMenuWindow}
	 * @param {object} options
	 * @constructor
	 */
	BX.Landing.UI.Tool.Menu = function(options)
	{
		options.bindElement = options.bindElement || null;
		options.items = options.items || [];

		BX.PopupMenuWindow.apply(this, [options.id, options.bindElement, options.items, options]);

		addClass(this.popupWindow.popupContainer, "landing-ui-popup");

		if (!isEmpty(options.className) && isString(options.className))
		{
			addClass(this.popupWindow.popupContainer, options.className);
		}

		// Override default methods
		this.popupWindow.show = BX.Landing.UI.Tool.Popup.prototype.show.bind(this.popupWindow);
		this.popupWindow.close = BX.Landing.UI.Tool.Popup.prototype.close.bind(this.popupWindow);

		// Add instance to BX.PopupMenu.Data
		if (!BX.PopupMenu.Data[options.id])
		{
			BX.PopupMenu.Data[options.id] = this;
			onCustomEvent(this, "onPopupMenuDestroy", BX.PopupMenu.onPopupDestroy.bind(BX.PopupMenu));
		}

		onCustomEvent(this.popupWindow, "onPopupShow", proxy(this.onShow, this));
		onCustomEvent(this.popupWindow, "onPopupClose", proxy(this.onClose, this));
	};


	BX.Landing.UI.Tool.Menu.prototype = {
		constructor: BX.Landing.UI.Tool.Menu,
		__proto__: BX.PopupMenuWindow.prototype,

		onShow: function()
		{
			if (this.bindElement)
			{
				addClass(this.bindElement, "landing-ui-active");
			}
		},

		onClose: function()
		{
			if (this.bindElement)
			{
				removeClass(this.bindElement, "landing-ui-active");
			}
		}
	};
})();