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
				height: 60,  // 32 + (14 * 2) = 60 total
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
			var message = BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING_HOTKEY");
			if (this.options.callFolded)
			{
				var hotkey = (BX.browser.IsMac() ? 'Shift + &#8984; + A' : 'Ctrl + Shift + A');
				message = BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING_FOLDED_CALL_HOTKEY").replace('#HOTKEY#', hotkey);
			}
			var hotkeyText = '<span class="bx-call-view-popup-call-muted-text-hotkey">' + message + '</span>';

			return 	BX.util.htmlspecialchars(BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING")) + '<br>' + hotkeyText;
		},

		close: function()
		{
			if (this.popup)
			{
				this.popup.close();
			}
		},

		onAutoClose: function()
		{
			if (this.popup)
			{
				this.popup.close();
				this.callbacks.onClose();
			}
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