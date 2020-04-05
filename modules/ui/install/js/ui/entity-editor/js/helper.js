BX.namespace("BX.UI");

if(typeof BX.UI.EditorTextHelper === "undefined")
{
	BX.UI.EditorTextHelper = function()
	{
	};
	BX.UI.EditorTextHelper.prototype =
	{
		selectAll: function(input)
		{
			if(!(BX.type.isElementNode(input) && input.value.length > 0))
			{
				return;
			}

			if(BX.type.isFunction(input.setSelectionRange))
			{
				input.setSelectionRange(0, input.value.length);
			}
			else
			{
				input.select();
			}
		},
		setPositionAtEnd: function(input)
		{
			if(BX.type.isElementNode(input) && input.value.length > 0)
			{
				BX.setCaretPosition(input, input.value.length);
			}
		}
	};
	BX.UI.EditorTextHelper._current = null;
	BX.UI.EditorTextHelper.getCurrent = function ()
	{
		if(!this._current)
		{
			this._current = new BX.UI.EditorTextHelper();
		}
		return this._current;
	}
}