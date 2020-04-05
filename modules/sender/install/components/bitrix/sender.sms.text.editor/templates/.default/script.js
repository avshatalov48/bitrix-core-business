;(function (window)
{
	BX.namespace('BX.Sender.SMS');
	if (BX.Sender.SMS.TextEditor)
	{
		return;
	}

	var Helper = BX.Sender.Helper;

	/**
	 * TextEditor.
	 *
	 */
	function TextEditor()
	{
	}
	TextEditor.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.mess = params.mess;

		this.input = Helper.getNode('input', this.context);
		this.counter = Helper.getNode('counter', this.context);
		this.num = Helper.getNode('num', this.context);
		this.sms = Helper.getNode('sms', this.context);

		BX.bind(this.input, 'bxchange', this.onChange.bind(this));
		BX.bind(this.input, 'input', this.onChange.bind(this));

		this.refresh();
	};
	TextEditor.prototype.onChange = function ()
	{
		this.refresh();
	};
	TextEditor.prototype.refresh = function ()
	{
		var value = this.input.value;

		var ratio = (this.hasMultiBites() ? 2 : 1);
		var count = value.length;
		var numberCharsAtSms = (140 / ratio);
		var smsCount = Math.floor(count / numberCharsAtSms) + 1;

		this.num.textContent = numberCharsAtSms;
		this.sms.textContent = smsCount;
		this.counter.textContent = count;
	};
	TextEditor.prototype.hasMultiBites = function ()
	{
		var value = this.input.value;
		if (value.length == 0)
		{
			return false;
		}
		for (var i = 0; i < value.length; i++)
		{
			if (value.charCodeAt(i) > 128)
			{
				return true;
			}
		}

		return false;
	};

	BX.Sender.SMS.TextEditor = new TextEditor();

})(window);