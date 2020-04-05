;(function ()
{
	BX.namespace('BX.Sender');
	if (BX.Sender.Agreement)
	{
		return;
	}

	function Agreement()
	{
		this.actionUri = '/bitrix/components/bitrix/sender.letter.list/ajax.php';
		this.isAccepted = !BX.message.SENDER_AGREEMENT_IS_REQUIRED;
		if (!this.isAccepted)
		{
			var self = this;
			BX.ready(function () {
				self.showPopup();
			});
		}

		this.ajaxAction = new BX.AjaxAction(this.actionUri);
	}
	Agreement.prototype.onApply = function ()
	{
		this.isAccepted = true;

		var waitClassName = 'popup-window-button-wait';
		this.button.addClassName(waitClassName);

		var self = this;
		this.ajaxAction.request({
			action: 'acceptAgreement',
			onsuccess: function () {
				self.popup.close();
			},
			onfailure: function () {
				self.popup.show();
				self.button.removeClassName(waitClassName);
			}
		});
	};
	Agreement.prototype.onClose = function ()
	{
		if (this.isAccepted)
		{
			return;
		}

		window.location.href = '/';
		setTimeout(this.popup.show.bind(this.popup), 0);
	};
	Agreement.prototype.showPopup = function ()
	{
		if (!this.popup)
		{
			this.button = new BX.PopupWindowButton({
				text: BX.message('SENDER_AGREEMENT_BUTTON_ACCEPT'),
				className: "popup-window-button-accept",
				events: {
					click: this.onApply.bind(this)
				}
			});
			this.popup = BX.PopupWindowManager.create(
				'sender-agreement-popup',
				null,
				{
					content: '<div class="sender-agreement-wrap">' + BX.message('SENDER_AGREEMENT_TEXT') + '</div>',
					titleBar: BX.message('SENDER_AGREEMENT_TITLE'),
					maxHeight: 400,
					autoHide: false,
					lightShadow: false,
					overlay: {
						opacity: 500,
						backgroundColor: 'black'
					},
					closeByEsc: true,
					closeIcon: true,
					//contentColor: 'white',
					buttons: [
						this.button
					]
				}
			);

			BX.addCustomEvent(this.popup, "onPopupClose", this.onClose.bind(this));
		}

		if (this.popup.isShown())
		{
			return;
		}

		this.popup.show();
	};

	BX.Sender.Agreement = new Agreement();

})(window);