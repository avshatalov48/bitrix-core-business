import {Loc} from 'main.core';

let
	BX = window.BX,
	BXMobileApp = window.BXMobileApp;

let nodeUrl = (function ()
{
	let nodeUrl = function (node, container)
	{
		this.node = node;
		this.container = container;
		this.nodeLink = this.node.previousElementSibling;
		this.click = BX.delegate(this.click, this);
		this.callback = BX.delegate(this.callback, this);
		BX.bind(this.container, 'click', this.click);
	};
	nodeUrl.prototype = {
		click(e)
		{
			if (e.toElement.tagName !== 'A')
			{
				this.show();
				return BX.PreventDefault(e);
			}
		},
		show()
		{
			window.app.exec('showPostForm', {
				attachButton: {items: []},
				attachFileSettings: {},
				attachedFiles: [],
				extraData: {},
				mentionButton: {},
				smileButton: {},
				message: {
					text: BX.util.htmlspecialcharsback(this.nodeLink.value)
				},
				okButton: {
					callback: this.callback,
					name: Loc.getMessage('interface_form_save')
				},
				cancelButton: {
					callback: function ()
					{
					},
					name: Loc.getMessage('interface_form_cancel')
				}
			});
		},
		callback(data)
		{
			data.text = (BX.util.htmlspecialchars(data.text) || '');
			if (data.text === '')
			{
				this.node.textContent = this.nodeLink.getAttribute('placeholder');
				this.node.setAttribute('href', '#');
			}
			else
			{
				this.node.textContent = data.text;
				if (this.checkUrl(data.text))
				{
					this.node.setAttribute('href', data.text);
				}
				else
				{
					this.node.setAttribute('href', 'http://'+data.text);
				}
			}
			this.nodeLink.value = data.text;
			BX.onCustomEvent(this, 'onChange', [this, this.nodeLink]);
		},
		checkUrl(url)
		{
			return /^(callto:|mailto:|https:\/\/|http:\/\/)/i.test(url);
		}
	};
	return nodeUrl;
})();

window.app.exec('enableCaptureKeyboard', true);

BX.Mobile.Field.Url = function (params)
{
	this.init(params);
};

BX.Mobile.Field.Url.prototype = {
	__proto__: BX.Mobile.Field.prototype,
	bindElement: function (node)
	{
		let result = null;
		if (BX(node))
		{
			result = new nodeUrl(node, node.parentElement);
		}
		return result;
	}
};