;(function (window)
{

	BX.namespace('BX.Sender.Letter');
	if (BX.Sender.Letter.Stat)
	{
		return;
	}

	var Page = BX.Sender.Page;
	var Helper = BX.Sender.Helper;

	/**
	 * Letter.
	 *
	 */
	function Stat()
	{
	}
	Stat.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.actionUri = params.actionUri;
		this.letterId = params.letterId;

		Page.initButtons();
		BX.UI.Hint.init(this.context);
		this.ajaxAction = new BX.AjaxAction(this.actionUri);

		this.buttonResendErrors = Helper.getNode('resend-errors', this.context);
		BX.bind(this.buttonResendErrors, 'click', this.resendErrors.bind(this));
	};
	Stat.prototype.resendErrors = function ()
	{
		if (this.isResendErrors)
		{
			return;
		}

		this.request('resendErrors', {letterId: this.letterId}, function () {
			window.location.reload();
		});

		BX.addClass(this.buttonResendErrors, 'ui-btn-clock');
		this.isResendErrors = true;
	};
	Stat.prototype.request = function (actionName, data, onsuccess)
	{
		this.ajaxAction.request({
			action: actionName,
			onsuccess: onsuccess,
			data: data
		});
	};

	BX.Sender.Letter.Stat = new Stat();

})(window);