import {Loc} from 'main.core';

let
	BX = window.BX,
	BXMobileApp = window.BXMobileApp;

let nodeInteger = (function ()
{
	let nodeInteger = function (node, container)
	{
		this.node = node;
		this.container = container;
		this.click = BX.delegate(this.click, this);
		this.callback = BX.delegate(this.callback, this);
		BX.bind(this.container, 'click', this.click);
	};
	nodeInteger.prototype = {
		click: function (e)
		{
			this.show();
			return BX.PreventDefault(e);
		},
		show: function ()
		{
			window.app.exec('showPostForm', {
				attachButton: {items: []},
				attachFileSettings: {},
				attachedFiles: [],
				extraData: {},
				mentionButton: {},
				smileButton: {},
				message: {text: BX.util.htmlspecialcharsback(this.node.value)},
				okButton: {
					callback: this.callback,
					name: Loc.getMessage('interface_form_save')
				},
				cancelButton: {
					callback: function ()
					{
					},
					name:  Loc.getMessage('interface_form_cancel')
				}
			});
		},
		callback: function (data)
		{
			data.text = (BX.util.htmlspecialchars(data.text) || '');
			this.node.value = data.text;
			if (data.text == '')
			{
				this.container.innerHTML = `<span class="placeholder">${this.node.getAttribute('placeholder')}</span>`;
			}
			else
			{
				this.container.innerHTML = data.text;
			}
			BX.onCustomEvent(this, 'onChange', [this, this.node]);
		}
	};
	return nodeInteger;
})();

window.app.exec('enableCaptureKeyboard', true);

BX.Mobile.Field.Integer = function (params)
{
	this.init(params);
};

BX.Mobile.Field.Integer.prototype = {
	__proto__: BX.Mobile.Field.prototype,
	bindElement: function (node)
	{
		let result = null;

		if (BX(node))
		{
			result = new nodeInteger(node, BX(`${node.id}_container`));
		}
		return result;
	}
};