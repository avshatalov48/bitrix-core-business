;(function ()
{

	BX.namespace('BX.Sender');
	if (BX.Sender.ContactImport)
	{
		return;
	}

	var Helper = BX.Sender.Helper;
	var Page = BX.Sender.Page;

	/**
	 * Importer.
	 *
	 */
	function Importer()
	{

	}
	Importer.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.listId = params.listId || null;
		this.listData = null;
		this.blacklist = params.blacklist || false;
		this.limit = params.limit || 500;
		this.pathToList = params.pathToList;
		this.actionUri = params.actionUri;

		this.textarea = Helper.getNode('text-list', this.context);
		this.button = Helper.getNode('panel-button-save', this.context);
		this.process = Helper.getNode('process', this.context);
		this.loader = Helper.getNode('loader', this.context);
		this.indicator = Helper.getNode('indicator', this.context);

		Page.initButtons();
		this.ajaxAction = new BX.AjaxAction(this.actionUri);

		if (this.button)
		{
			BX.bind(this.button, 'click', this.run.bind(this));
		}
	};
	Importer.prototype.exit = function ()
	{
		if (Page.slider.isInSlider())
		{
			if (this.listData)
			{
				top.BX.onCustomEvent(
					top,
					'BX.Sender.ContactImport::loaded',
					[this.listData]
				);
			}
			Page.slider.close();
		}
		else
		{
			window.location.href = this.pathToList;
		}
	};
	Importer.prototype.run = function ()
	{
		var loader = this.loader;
		loader.style.display = '';
		setTimeout(function () {
			loader.style.opacity = 1;
		}, 50);

		this.updateProcess();
		var list = this.getTextPortion();
		if (list.length === 0)
		{
			setTimeout(this.exit.bind(this), 500);
			return;
		}

		var listIdNode = Helper.getNode('set-id', this.context);
		var listNameNode = Helper.getNode('set-name', this.context);

		var self = this;
		this.ajaxAction.request({
			action: 'importList',
			onsuccess: function (response)
			{
				var data = response.data || {};
				self.listId = data.ID;
				self.listData = data;
				self.run();
			},
			data: {
				'list': list,
				'listId': this.listId ? this.listId : (listIdNode ? listIdNode.value : ''),
				'listName': listNameNode ? listNameNode.value : ''
			},
			urlParams: {
				'blacklist': this.blacklist ? 'Y' : 'N'
			}
		});
	};
	Importer.prototype.updateProcess = function ()
	{
		if (!this.initialValue)
		{
			this.initialValue = this.getTextLength();
		}

		var value = 100;
		if (this.initialValue)
		{
			value = (this.initialValue - this.getTextLength()) / this.initialValue;
			value = Math.round(value * 100);
		}

		this.process.textContent = value;
		this.indicator.style.width = value + '%';
	};
	Importer.prototype.getTextLength = function ()
	{
		var matches = this.textarea.value.match(/\n/g);
		return matches ? matches.length : 0;
	};
	Importer.prototype.getTextPortion = function ()
	{
		var list = [];

		var regexp = /\n/g;
		var text = this.textarea.value.trim();
		var startIndex = regexp.lastIndex;
		do
		{
			var result = regexp.exec(text);
			var lastIndex = result ? regexp.lastIndex : text.length;
			var code = text.substring(startIndex, lastIndex).trim();

			startIndex = lastIndex;
			if (code.length === 0 || code.length > 255)
			{
				if (!result)
				{
					break;
				}
				continue;
			}

			list.push(code);
			if (list.length >= this.limit)
			{
				break;
			}
		} while (result);

		this.textarea.value = !code ? '' : this.textarea.value.trim().substring(lastIndex);

		return list;
	};


	BX.Sender.ContactImport = new Importer();

})(window);