;(function ()
{
	BX.namespace('BX.Main.Mail');
	if (BX.Main.Mail.Unsubscriber)
	{
		return;
	}

	/**
	 * Unsubscriber.
	 *
	 */
	function Unsubscriber(params)
	{
	}
	Unsubscriber.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.spamBlock = this.context.querySelector('[data-role="spam-block"]');
		this.unsubBlock = this.context.querySelector('[data-role="unsub-block"]');
		this.spamBlockButton = this.context.querySelector('[data-role="spam-block-btn"]');
		this.unsubBlockButton = this.context.querySelector('[data-role="unsub-block-btn"]');
		BX.bind(this.spamBlockButton, 'click', this.showBlock.bind(this, true));
		BX.bind(this.unsubBlockButton, 'click', this.showBlock.bind(this, false));
	};
	Unsubscriber.prototype.showBlock = function (isSpam)
	{
		var className = 'main-mail-unsubscribe-spam-visible';
		if (isSpam)
		{
			BX.addClass(this.spamBlock, className);
			BX.addClass(this.unsubBlock, className);
		}
		else
		{
			BX.removeClass(this.spamBlock, className);
			BX.removeClass(this.unsubBlock, className);
		}
	};

	BX.Main.Mail.Unsubscriber = new Unsubscriber();

})(window);