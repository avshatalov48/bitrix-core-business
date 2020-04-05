;(function(){
	'use strict';

	var controlStack = {};

	BX.namespace('BX.Currency');

	BX.Currency.MoneyInput = function(param)
	{
		controlStack[param.controlId] = this;

		this.currency = param.currency;
		this.input = param.input;

		this.resultInput = param.resultInput;

		this.editor = null;

		BX.bind(
			this.input, 'focus', BX.once(
				this.input, 'focus', BX.delegate(
					function(){
						this.getEditor();
					}, this
				)
			)
		);
	};

	BX.Currency.MoneyInput.getByNode = function(controlNode)
	{
		if(!controlNode._bxmoneyeditcode)
		{
			controlNode._bxmoneyeditcode = Math.random();
		}

		return BX.Currency.MoneyInput.get(controlNode._bxmoneyeditcode);
	};

	BX.Currency.MoneyInput.get = function(controlId)
	{
		if(typeof controlStack[controlId] === 'undefined')
		{
			controlStack[controlId] = new BX.Currency.MoneyInput();
		}

		return controlStack[controlId];
	};

	BX.Currency.MoneyInput.prototype.getEditor = function()
	{
		if(!this.editor)
		{
			this.editor = new BX.Currency.Editor({
				input: this.input,
				currency: this.currency,
				callback: BX.defer(this.setValue, this) // there must be defer!
			});
		}

		return this.editor;
	};

	BX.Currency.MoneyInput.prototype.setCurrency = function(currency)
	{
		this.getEditor().setCurrency(currency);
		this.currency = currency;
	};

	BX.Currency.MoneyInput.prototype.setValue = function(value)
	{
		if(!!this.resultInput)
		{
			this.resultInput.value = value.length > 0 ? (value + '|' + this.currency) : '';
			BX.fireEvent(this.resultInput, 'change');
		}
	};

})();