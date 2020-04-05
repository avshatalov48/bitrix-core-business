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