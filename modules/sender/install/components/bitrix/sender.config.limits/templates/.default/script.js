;(function ()
{
	BX.namespace('BX.Sender.Config');
	if (BX.Sender.Config.Limits)
	{
		return;
	}

	var Helper = BX.Sender.Helper;
	//var Page = BX.Sender.Page;

	/**
	 * Percentage.
	 *
	 */
	function Percentage(params)
	{
		this.init(params);
	}
	Percentage.prototype.init = function (params)
	{
		this.name = params.name;
		this.context = params.context;
		this.manager = params.manager;

		this.editNode = Helper.getNode('percentage-edit', this.context);
		this.inputNode = Helper.getNode('percentage-input', this.context);
		this.textNode = Helper.getNode('percentage-text', this.context);
		this.viewNode = Helper.getNode('percentage-view', this.context);
		this.limitNode = Helper.getNode('percentage-limit', this.context);
		this.availableNode = Helper.getNode('percentage-available', this.context);

		if (this.inputNode)
		{
			this.input = this.inputNode.querySelector('input');
			this.current = parseInt(this.input.value);

			BX.bind(this.editNode, 'click', this.onEdit.bind(this));
			BX.bind(this.input, 'blur', this.onSave.bind(this));
		}
	};

	Percentage.prototype.setValue = function (response)
	{
		this.current = parseInt(response.percentage);
		this.input.value = this.current;
		this.textNode.textContent = this.current;
		this.viewNode.style.left = this.current + '%';
		Helper.animate.numbers(this.limitNode, response.limit);
		Helper.animate.numbers(this.availableNode, response.available);
	};
	Percentage.prototype.onEdit = function ()
	{
		BX.addClass(this.viewNode, 'sender-config-limits-progress-active');
		var self = this;
		setTimeout(function () {
			BX.focus(self.input);
		}, 50);
	};
	Percentage.prototype.onSave = function ()
	{
		BX.removeClass(this.viewNode, 'sender-config-limits-progress-active');

		var value = parseInt(this.input.value);
		if (this.current === value)
		{
			return;
		}

		var self = this;
		this.manager.ajaxAction.request({
			action: 'setLimitPercentage',
			onsuccess: function (response)
			{
				self.setValue(response);
			},
			data: {
				'percentage': value
			}
		});
	};

	/**
	 * Manager.
	 *
	 */
	function Limits(params)
	{
	}
	Limits.prototype.init = function (params)
	{
		this.mess = params.mess || {};
		this.actionUri = params.actionUri || '';
		this.context = BX(params.containerId);

		Helper.getNodes('percentage-context', this.context)
			.forEach(this.initPercentage, this);

		this.ajaxAction = new BX.AjaxAction(this.actionUri);

		this.canTrackMailNode = document.querySelector('.sender-track-mail-option');
		this.mailConsentNode = document.querySelector('.sender-mail-consent-option');
		this.sendingTimeNode = document.querySelector('.sender-sending-time-option');
		this.sendingTimeConfigurationBlock = document.querySelector('.sender-sending-time-configuration-block');
		this.sendingTimeViewBlock = document.querySelector('.sender-sending-time-view-block');
		this.sendingTimeEditBlock = document.querySelector('.sender-sending-time-edit-block');
		this.sendingTimeEditBtn = document.querySelector('.sender-sending-time-edit-btn');
		this.sendingStartNode = document.querySelector('.sender-sending-start');
		this.sendingEndNode = document.querySelector('.sender-sending-end');
		this.sendingTimeSaveBtn = document.querySelector('.sender-save-time-limit-configuration');

		this.sendingTimeConfigurationBlock.style.display= this.sendingTimeNode.checked ? 'block':'none';
		BX.bind(this.canTrackMailNode, 'change', this.switchCanTrackMail.bind(this));
		BX.bind(this.mailConsentNode, 'change', this.switchMailConsentOption.bind(this));
		BX.bind(this.sendingTimeNode, 'change', this.switchSendingTimeOption.bind(this));
		BX.bind(this.sendingTimeSaveBtn, 'click', this.saveSendingTimeOptions.bind(this));
		BX.bind(this.sendingTimeEditBtn, 'click', this.switchSendingTimeConfigurationBlock.bind(this));

		this.changeLeftMenuOption(params.defaultTab);

	};
	Limits.prototype.changeLeftMenuOption = function(dataTab)
	{
		var tabs = document.querySelectorAll('[data-tab]');

		tabs.forEach(function(element) {
			if (element.dataset.tab === dataTab)
			{
				element.classList.add('sender-type-tab-current')
			}
			else
			{
				element.classList.remove('sender-type-tab-current')
			}
		});
	};
	Limits.prototype.initPercentage = function (context)
	{
		if (!context)
		{
			return;
		}

		var name = context.getAttribute('data-name');
		if (!name)
		{
			return;
		}

		new Percentage({
			'name': name,
			'context': context,
			'manager': this
		});
	};
	Limits.prototype.switchCanTrackMail = function ()
	{
		this.ajaxAction.request({
			action: 'switchTrackMailOption',
			onsuccess: function (response)
			{
			},
			data: {
				'canTrackMail': this.canTrackMailNode.checked
			}
		});
	};
	Limits.prototype.switchMailConsentOption = function ()
	{
		this.ajaxAction.request({
			action: 'switchMailConsentOption',
			onsuccess: function (response)
			{
			},
			data: {
				'mailConsent': this.mailConsentNode.checked
			}
		});
	};
	Limits.prototype.switchSendingTimeOption = function ()
	{
		this.sendingTimeConfigurationBlock.style.display= this.sendingTimeNode.checked ? 'block':'none';
		this.ajaxAction.request({
			action: 'switchSendingTimeOption',
			onsuccess: function (response)
			{
			},
			data: {
				'sendingTime': this.sendingTimeNode.checked
			}
		});
	};

	Limits.prototype.switchSendingTimeConfigurationBlock = function ()
	{
		var viewHidden = this.sendingTimeViewBlock.style.display === 'none';

		document.querySelector('.sender-sending-start-caption').textContent = this.sendingStartNode.value;
		document.querySelector('.sender-sending-end-caption').textContent = this.sendingEndNode.value;
		this.sendingTimeViewBlock.style.display = viewHidden ? 'block' : 'none';
		this.sendingTimeEditBlock.style.display = viewHidden ? 'none' : 'block';
	};

	Limits.prototype.saveSendingTimeOptions = function ()
	{
		this.sendingTimeSaveBtn.classList.add('ui-btn-wait');
		this.ajaxAction.request({
			action: 'setSendingTimeOption',
			onsuccess: function (response)
			{
				this.sendingTimeSaveBtn.classList.remove('ui-btn-wait');
				BX.UI.Notification.Center.notify({
					content: this.mess.success,
					position: 'top-right',
					autoHideDelay: 2000,
				});

				this.switchSendingTimeConfigurationBlock();
			}.bind(this),
			data: {
				'sendingStart': this.sendingStartNode.value,
				'sendingEnd': this.sendingEndNode.value
			}
		});
	};
	Limits.prototype.showMailPopup = function ()
	{
		BX.Sender.B24License.showDefaultPopup();
	};
	Limits.prototype.showMailRatingPopup = function (e)
	{
		this.popup = BX.PopupWindowManager.create(
			'sender-config-limit-mail-rating',
			null,
			{
				titleBar: this.mess.mailDailyLimitTitle,
				width: 620,
				height: 225,
				autoHide: true,
				lightShadow: true,
				closeByEsc: true,
				closeIcon: true,
				overlay: true,
				buttons: [
					new BX.PopupWindowButton({
						text: this.mess.close,
						className: "popup-window-button-accept",
						events: {
							click: function() {
								this.popupWindow.close();
							}
						}
					})
				]
			}
		);

		this.popup.setContent(this.mess.mailDailyLimit);
		this.popup.show();
	};

	BX.Sender.Config.Limits = new Limits();

})(window);