let
	BX = window.BX,
	BXMobileApp = window.BXMobileApp;

let nodeBoolean = (function ()
{
	let nodeBoolean = function (node)
	{
		this.node = node;
		let label = BX.findParent(this.node, {tagName: 'LABEL'});
		if (label && label.parentNode && !label.parentNode.hasAttribute('bx-fastclick-bound'))
		{
			label.parentNode.setAttribute('bx-fastclick-bound', 'Y');
			FastClick.attach(label.parentNode);
		}

		BX.bind(this.node, 'change', BX.delegate(this.change, this));
	};
	nodeBoolean.prototype = {
		change: function ()
		{
			BX.onCustomEvent(this, 'onChange', [this, this.node]);
		}
	};
	return nodeBoolean;
})();

window.app.exec('enableCaptureKeyboard', true);

BX.Mobile.Field.Boolean = function (params)
{
	this.init(params);
};

BX.Mobile.Field.Boolean.prototype = {
	__proto__: BX.Mobile.Field.prototype,
	bindElement: function (node)
	{
		let result = null;
		if (BX(node))
		{
			result = new nodeBoolean(node);
		}
		return result;
	},
};