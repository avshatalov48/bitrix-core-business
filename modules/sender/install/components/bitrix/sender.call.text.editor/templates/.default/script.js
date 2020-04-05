;(function ()
{
	BX.namespace('BX.Sender.Call');
	if (BX.Sender.Call.TextEditor)
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
		this.speechRates = params.speechRates;
		this.speechRateInterval = params.speechRateInterval;
		this.mess = params.mess;

		this.input = Helper.getNode('input', this.context);
		this.counter = Helper.getNode('counter', this.context);
		this.num = Helper.getNode('num', this.context);
		this.sms = Helper.getNode('sms', this.context);

		this.speed = document.getElementsByName(params.speedInputName);
		this.speed = this.speed.length > 0 ? this.speed.item(0) : null;

		BX.bind(this.input, 'bxchange', this.onChange.bind(this));
		BX.bind(this.input, 'input', this.onChange.bind(this));

		if (this.speed)
		{
			BX.bind(this.speed, 'bxchange', this.onChange.bind(this));
		}

		this.refresh();
	};
	TextEditor.prototype.onChange = function ()
	{
		this.refresh();
	};
	TextEditor.prototype.refresh = function ()
	{
		var value = this.input.value;

		var ratio = this.getRatioPerChar();
		var seconds = value.length === 0 ? 0 : Math.floor(value.length * ratio) + 1;

		this.counter.textContent = BX.date.format('sdiff', Date.now()/1000 - seconds);
	};
	TextEditor.prototype.getRatioPerChar = function ()
	{
		var sec = this.speechRateInterval;
		var charsPerSec = this.speechRates;

		var speed = this.speed ? this.speed.value : null;
		var charNum = charsPerSec[speed];
		if (!charNum)
		{
			charNum = charsPerSec['medium'];
		}

		return sec/charNum;
	};

	BX.Sender.Call.TextEditor = new TextEditor();

})(window);