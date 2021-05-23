;(function ()
{
	BX.namespace('BX.Sender.Im');
	if (BX.Sender.Im.Message)
	{
		return;
	}

	var Helper = BX.Sender.Helper;

	/**
	 * TextEditor.
	 *
	 */
	function Message()
	{
	}
	Message.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.mess = params.mess;

		this.input = Helper.getNode('input', this.context);
		this.counter = Helper.getNode('counter', this.context);

		BX.bind(this.input, 'bxchange', this.onChange.bind(this));
		BX.bind(this.input, 'input', this.onChange.bind(this));

		this.refresh();
	};
	Message.prototype.onChange = function ()
	{
		this.refresh();
	};
	Message.prototype.refresh = function ()
	{
		this.counter.textContent = this.input.value.length;
	};

	BX.Sender.Im.Message = new Message();

})(window);