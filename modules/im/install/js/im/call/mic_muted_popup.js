;(function()
{
	BX.namespace('BX.Call');

	if (BX.Call.MicMutedPopup)
	{
		return;
	}

	BX.Call.MicMutedPopup = function(options)
	{
		this.popup = null;
		this.autoCloseDelay = BX.prop.getInteger(options, "autoCloseDelay", 5000);
		this.options = options || {};

		this.callbacks = {
			onClose: BX.type.isFunction(this.options.onClose) ? this.options.onClose : BX.DoNothing,
			onUnmuteClick: BX.type.isFunction(this.options.onUnmuteClick) ? this.options.onUnmuteClick : BX.DoNothing,
		}
		this.autoCloseTimeout = 0;
	};

	BX.Call.MicMutedPopup.prototype = {
		show: function()
		{
			clearTimeout(this.autoCloseTimeout);
			this.autoCloseTimeout = setTimeout(this.onAutoClose.bind(this), this.autoCloseDelay);

			if (this.popup)
			{
				this.popup.show();
				return;
			}

			this.popup = new BX.PopupWindow({
				bindElement: this.options.bindElement,
				targetContainer: this.options.targetContainer,
				content: this.render(),
				padding: 0,
				contentPadding: 14,
				height: this.getPopupHeight(),
				className: 'bx-call-view-popup-call-muted',
				contentBackground: 'unset',

				angle: this.options.bindElement ? { position: 'bottom', offset: 20 } : null,
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
				props: {className: "bx-call-view-popup-call-muted-body"},
				children: [
					BX.create("div", {
						props: {className: "bx-call-view-popup-call-muted-icon-mic"},
					}),
					BX.create("div", {
						props: {className: "bx-call-view-popup-call-muted-text"},
						html: this.getPopupMessage()
					}),
					BX.create("div", {
						props: {className: "ui-btn ui-btn-xs ui-btn-light-border ui-btn-round ui-btn-no-caps "},
						text: BX.message("IM_CALL_UNMUTE_MIC"),
						events: {
							click: function()
							{
								this.callbacks.onUnmuteClick();
							}.bind(this)
						}
					}),
					BX.create("div", {
						props: {className: "bx-call-view-popup-call-muted-close"},
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

		getPopupMessage: function()
		{
			var title = this.options.title || BX.util.htmlspecialchars(BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING"));
			if (!BX.Call.Util.isDesktop())
			{
				return title;
			}
			var hotKeyMessage = BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING_HOTKEY");
			if (this.options.callFolded)
			{
				var hotkey = (BX.browser.IsMac() ? 'Shift + &#8984; + A' : 'Ctrl + Shift + A');
				hotKeyMessage = BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING_FOLDED_CALL_HOTKEY").replace('#HOTKEY#', hotkey);
			}
			hotKeyMessage = '<span class="bx-call-view-popup-call-muted-text-hotkey">' + hotKeyMessage + '</span>';

			return title + '<br>' + hotKeyMessage;
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