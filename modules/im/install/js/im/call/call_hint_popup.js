;(function()
{
	BX.namespace('BX.Call');

	if (BX.Call.CallHint)
	{
		return;
	}

	BX.Call.CallHint = function(options)
	{
		this.popup = null;

		this.title = BX.prop.getString(options, "title", BX.util.htmlspecialchars(BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING")));
		this.icon =  BX.prop.getString(options, "icon", "mic");

		this.bindElement = BX.prop.getElementNode(options, "bindElement", null);
		this.targetContainer = BX.prop.getElementNode(options, "targetContainer", null);
		this.callFolded = BX.prop.getBoolean(options, "callFolded", false);
		this.autoCloseDelay = BX.prop.getInteger(options, "autoCloseDelay", 5000);

		this.buttonsLayout = BX.prop.getString(options, "buttonsLayout", "right");
		this.buttons = BX.prop.getArray(options, "buttons", []);

		this.callbacks = {
			onClose: BX.prop.getFunction(options, "onClose", BX.DoNothing),
		}
		this.autoCloseTimeout = 0;
	};

	BX.Call.CallHint.prototype = {
		show: function()
		{
			clearTimeout(this.autoCloseTimeout);
			if (this.autoCloseDelay > 0)
			{
				this.autoCloseTimeout = setTimeout(this.onAutoClose.bind(this), this.autoCloseDelay);
			}

			if (this.popup)
			{
				this.popup.show();
				return;
			}

			this.popup = new BX.PopupWindow({
				bindElement: this.bindElement,
				targetContainer: this.targetContainer,
				content: this.render(),
				padding: 0,
				contentPadding: 14,
				// height: this.getPopupHeight(),
				className: 'bx-call-view-popup-call-hint',
				contentBackground: 'unset',
				maxWidth: 600,

				angle: this.bindElement ? { position: 'bottom', offset: 20 } : null,
				events: {
					onClose: function ()
					{
						this.popup.destroy();
					}.bind(this),
					onDestroy: function ()
					{
						this.popup = null;
					}.bind(this)
				}
			});

			this.popup.show();
		},

		render: function()
		{
			return BX.create("div", {
				props: {className: "bx-call-view-popup-call-hint-body layout-" + this.buttonsLayout},
				children: [
					BX.create("div", {
						props: {className: "bx-call-view-popup-call-hint-icon " + this.icon},
					}),
					BX.create("div", {
						props: {className: "bx-call-view-popup-call-hint-middle-block"},
						children: [
							BX.create("div", {
								props: {className: "bx-call-view-popup-call-hint-text"},
								html: this.getPopupMessage()
							}),
							(this.buttonsLayout == "bottom" ? this.renderButtons() : null),
						]
					}),

					(this.buttonsLayout == "right" ? this.renderButtons() : null),
					BX.create("div", {
						props: {className: "bx-call-view-popup-call-hint-close"},
						events: {
							click: function()
							{
								this.callbacks.onClose();
								if (this.popup)
								{
									this.popup.close();
								}
							}.bind(this)
						},
					})
				]
			})
		},

		renderButtons: function()
		{
			return BX.create("div", {
				props: {className: "bx-call-view-popup-call-hint-buttons-container"},
				children: this.buttons.map(button => button.render())
			})
		},

		getPopupMessage: function()
		{
			if (!BX.Call.Util.isDesktop())
			{
				return this.title;
			}
			var hotKeyMessage = BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING_HOTKEY");
			if (this.callFolded)
			{
				var hotkey = (BX.browser.IsMac() ? 'Shift + &#8984; + A' : 'Ctrl + Shift + A');
				hotKeyMessage = BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING_FOLDED_CALL_HOTKEY").replace('#HOTKEY#', hotkey);
			}
			hotKeyMessage = '<span class="bx-call-view-popup-call-hint-text-hotkey">' + hotKeyMessage + '</span>';

			return this.title + '<br>' + hotKeyMessage;
		},

		/**
		 * Returns height in pixels for the popup.
		 * The height depends on the hotkey hint (hint appears only in the desktop app).
		 *
		 * @returns {number}
		 */
		getPopupHeight: function()
		{
			return BX.Call.Util.isDesktop() ? 60 : 54;
		},

		close: function()
		{
			if (this.popup)
			{
				this.popup.close();
				this.callbacks.onClose();
			}
		},

		onAutoClose: function()
		{
			this.close();
		},

		destroy: function()
		{
			if (this.popup)
			{
				this.popup.destroy();
			}

			clearTimeout(this.autoCloseTimeout)
		}
	}
})();