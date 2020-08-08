import {Loc} from 'main.core';

let
	BX = window.BX,
	BXMobileApp = window.BXMobileApp;

let nodeFile = (function ()
{
	let nodeFile = function (node)
	{
		this.node = node;
		this.click = BX.delegate(this.click, this);
		this.callback = BX.delegate(this.callback, this);
		BX.bind(this.node, "click", this.click);

		this.isImage = (this.node.getAttribute('data-is-image') === 'yes');
	};
	nodeFile.prototype = {
		click: function (e)
		{
			this.show();
			return BX.PreventDefault(e);
		},
		show: function ()
		{
			let url = this.node.getAttribute('data-url');

			if (this.isImage)
			{
				let description = this.node.textContent.trim();
				BXMobileApp.UI.Photo.show({
					'photos': [
						{
							'url': url,
							'description': description
						}
					]
				});
			}
			else
			{
				BXMobileApp.UI.Document.open({
					'url': url
				});

			}
		}
	};
	return nodeFile;
})();

window.app.exec('enableCaptureKeyboard', true);

BX.Mobile.Field.File = function (params)
{
	this.init(params);
};

BX.Mobile.Field.File.prototype = {
	__proto__: BX.Mobile.Field.prototype,
	bindElement: function (node)
	{
		let result = null;
		if (BX(node))
		{
			result = new nodeFile(node);
		}
		return result;
	}
};