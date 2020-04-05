;(function (window)
{

	window.BX.namespace('BX.Sender.Message');
	if (BX.Sender.Message.Tester)
	{
		return;
	}

	var Helper = BX.Sender.Helper;

	/**
	 * Editor.
	 *
	 */
	function Tester()
	{
	}
	Tester.prototype.classNameBtnWait = 'ui-btn-wait';
	Tester.prototype.eventNameSend = 'sender-message-test-send';
	Tester.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.id = params.id;
		this.actionUri = params.actionUri;
		this.mess = params.mess || {};
		this.ajaxAction = new BX.AjaxAction(this.actionUri);
		this.messageCode = params.messageCode;
		this.lastRecipients = params.lastRecipients;
		this.type = params.type;
		this.types = params.types;

		this.button = Helper.getNode('test-button', this.context);
		this.result = Helper.getNode('test-result', this.context);

		this.initSelector();
		/*
		Helper.getNodes('address-item', Helper.getNode('address-list', this.context))
			.forEach(function (node) {
				var handler = function (node)
				{
					var value = node.textContent;
					this.selector.addTile(value, {}, value);
				};
				BX.bind(node, 'click', handler.bind(this, node));
			}, this);
		*/

		BX.bind(this.button, 'click', this.send.bind(this));
	};
	Tester.prototype.validate = function (value)
	{
		switch (this.type)
		{
			case this.types.mail:
				return this.validateEmail(value);
				break;
			case this.types.phone:
				return this.validatePhone(value);
				break;
		}

		return true;
	};
	Tester.prototype.initSelector = function ()
	{
		this.selector = BX.Sender.UI.TileSelector.getById(this.id);
		if (!this.selector)
		{
			throw new Error('Tile selector `' + this.id + '` not found.');
		}

		BX.addCustomEvent(this.selector, this.selector.events.search, this.onSearch.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.buttonSelect, this.onButtonSelect.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.buttonSelectFirst, this.onButtonSelectFirst.bind(this));
	};
	Tester.prototype.onButtonSelect = function ()
	{
		var title = '';
		switch (this.type)
		{
			case this.types.mail:
				title = this.mess.searchTitleMail;
				break;
			case this.types.phone:
				title = this.mess.searchTitlePhone;
				break;
		}

		this.selector.showSearcher(title);
	};
	Tester.prototype.onButtonSelectFirst = function ()
	{
		var data = [
			{
				'id': 'last',
				'name': this.mess.categoryLast,
				'items': this.lastRecipients.map(function (code) {
					return {id: code, name: code, data: {}}
				})
			}
		];
		this.selector.setSearcherData(data);
	};
	Tester.prototype.onSearch = function (value)
	{
		(value || '').split(',').forEach(
			function (value)
			{
				value = value.trim();
				if (!value || !this.validate(value))
				{
					return;
				}

				this.selector.addTile(value, {}, value);
			},
			this
		);
	};
	Tester.prototype.validateEmail = function (value)
	{
		return (null !== value.match(/^[\w\.\d-_]+@[\w\.\d-_]+\.\w{2,15}$/i));
	};
	Tester.prototype.validatePhone = function (value)
	{
		return (null !== value.match(/^[\+]?[\d]{4,25}$/i));
	};
	Tester.prototype.printResult = function (data)
	{
		data = data || {isSuccess: null};

		var mess;
		if (data.isSuccess === null)
		{
			mess = '';
		}
		else if (!data.isSuccess)
		{
			mess = data.resultErrors.join("\n");
		}
		else if (this.messageCode === 'mail')
		{
			mess = this.mess.testSuccess;
		}
		else
		{
			mess = this.mess.testSuccessPhone;
		}

		this.result.textContent = mess;
		this.removeWaitingIndicator();
	};
	Tester.prototype.removeWaitingIndicator = function ()
	{
		BX.removeClass(this.button, this.classNameBtnWait)
	};
	Tester.prototype.addWaitingIndicator = function ()
	{
		BX.addClass(this.button, this.classNameBtnWait)
	};
	Tester.prototype.convertDataFromPostToJson = function (data)
	{
		for (var key in data)
		{
			if (!data.hasOwnProperty(key))
			{
				continue;
			}

			if (!/[\[]+/.test(key))
			{
				continue;
			}

			var newKey = key.split('[').map(function (item){
				return item.replace(']', '');
			});

			newKey.reduce(function (accum, currentKey) {
				if (!accum[currentKey] || !BX.type.isPlainObject(accum[currentKey]))
				{
					accum[currentKey] = {};
				}

				return accum[currentKey];
			}, data);

			newKey.reduce(function (accum, currentKey) {
				if (!BX.type.isPlainObject(accum[currentKey]))
				{
					return;
				}

				if (!BX.type.isNotEmptyObject(accum[currentKey]))
				{
					accum[currentKey] = data[key];
					return;
				}

				return accum[currentKey];
			}, data);


			data[key] = null;
		}

		return data;
	};
	Tester.prototype.send = function ()
	{
		var list = this.selector.getTilesId()
			.map(function (item) {
				return item.trim();
			})
			.filter(function (item) {
				return item.length > 0;
			});

		if (list.length === 0)
		{
			this.printResult({isSuccess: false, resultErrors: [this.mess.testEmpty]});
			return;
		}

		var message = {id: null, data: {}};
		BX.onCustomEvent(this, this.eventNameSend, [message]);

		this.printResult();
		this.addWaitingIndicator(this.button, this.classNameBtnWait);

		this.ajaxAction.request({
			action: 'test',
			onsuccess: this.printResult.bind(this),
			onfailure: this.removeWaitingIndicator.bind(this),
			data: {
				'list': list,
				'messageCode': this.messageCode,
				'messageId': message.id,
				'messageData': this.convertDataFromPostToJson(message.data)
			}
		});
	};

	BX.Sender.Message.Tester = new Tester();

})(window);