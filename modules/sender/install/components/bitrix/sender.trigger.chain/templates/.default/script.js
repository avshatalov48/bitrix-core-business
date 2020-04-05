;(function ()
{

	BX.namespace('BX.Sender.Letter');
	if (BX.Sender.Letter.Chain)
	{
		return;
	}

	var Page = BX.Sender.Page;
	var Helper = BX.Sender.Helper;

	/**
	 * Editor.
	 *
	 */
	function Editor()
	{
		this.context = null;
		this.editor = null;
	}
	Editor.prototype.init = function (params)
	{
		this.isFrame = params.isFrame || false;
		this.isSaved = params.isSaved || false;
		this.prettyDateFormat = params.prettyDateFormat;
		this.campaignId = params.campaignId || '';
		this.pathToLetterEdit = params.pathToLetterEdit || '';
		this.actionUri = params.actionUri || '';
		this.mess = params.mess || {};
		this.campaignTile = params.campaignTile || {};
		this.dictionaryTimeList = params.dictionaryTimeList || [];

		this.context = BX(params.containerId);
		this.lettersNode = Helper.getNode('letters', this.context);
		this.popup = [];

		this.timer = new Timer({
			'manager': this,
			'dictionary': this.dictionaryTimeList
		});
		this.ajaxAction = new BX.AjaxAction(this.actionUri);
		this.drawNumbers();
		this.initUi();
		Page.initButtons();
	};
	Editor.prototype.initUi = function ()
	{
		top.BX.addCustomEvent(top, 'sender-letter-edit-change', this.onLetterEditChange.bind(this));

		Helper.getNodes('letter', this.context).forEach(this.initLetterNode.bind(this));
	};
	Editor.prototype.onLetterEditChange = function (tile)
	{
		var letterNode = this.getLetterNode(tile.id);
		if (letterNode)
		{
			this.updateLetterNode(letterNode, tile.data);
		}
		else
		{
			this.addLetter(tile.id, tile.data)
		}
	};
	Editor.prototype.drawNumbers = function ()
	{
		Helper.getNodes('letter', this.context)
			.forEach(function (letterNode, index) {
				Helper.getNode('letter-num', letterNode).textContent = index + 1;
			});
	};
	Editor.prototype.initLetterNode = function (letterNode)
	{
		var letterId = parseInt(letterNode.getAttribute('data-letter-id'));

		var menuNode = Helper.getNode('letter-menu', letterNode);
		BX.bind(menuNode, 'click', this.showMenu.bind(this, menuNode, letterId));

		var timeNode = Helper.getNode('letter-time', letterNode);
		var timeBtnNode = Helper.getNode('letter-time-btn', letterNode);
		BX.bind(timeBtnNode, 'click', this.timer.show.bind(this.timer, timeNode, timeBtnNode));
		this.timer.setText(timeNode);
	};

	Editor.prototype.addLetter = function (letterId, data)
	{
		var letterNode = Helper.getTemplatedNode(Helper.getNode('template-letter', this.context), {}, true);
		letterNode.setAttribute('data-letter-id', letterId);
		BX.bind(
			Helper.getNode('letter-btn-edit', letterNode),
			'click',
			Page.open.bind(
				Page,
				this.pathToLetterEdit
					.replace('#id#', letterId)
					.replace('#campaign_id#', this.campaignId)
			)
		);
		this.updateLetterNode(letterNode, data);
		this.initLetterNode(letterNode);
		this.lettersNode.appendChild(letterNode);
		this.drawNumbers();
		if (top.BX.Sender.Page)
		{
			top.BX.Sender.Page.reloadGrid();
		}
	};
	Editor.prototype.updateLetterNode = function (letterNode, data)
	{
		if (!letterNode)
		{
			return;
		}

		Helper.getNode('letter-title', letterNode).textContent = data.title;
		Helper.getNode('letter-date', letterNode).textContent = data.dateInsert;
		Helper.getNode('letter-user', letterNode).textContent = data.userName;
		Helper.getNode('letter-time', letterNode).setAttribute('data-time-value', data.timeShift);
		Helper.getNode('letter-user', letterNode).href = '';
	};
	Editor.prototype.getLetterNode = function (letterId)
	{
		letterId = parseInt(letterId);
		var letters = Helper.getNodes('letter', this.context)
			.filter(function (letterNode) {
				return letterId === parseInt(letterNode.getAttribute('data-letter-id'));
			});

		return letters.length > 0 ? letters[0] : null;
	};
	Editor.prototype.showMenu = function (node, letterId)
	{
		if (!this.popup[letterId])
		{
			this.popup[letterId] = new BX.PopupMenuWindow(
				'sender-trigger-letter-menu-' + letterId,
				node,
				[
					{
						'id': 'sender-move-up-' + letterId,
						'text': this.mess.moveUp,
						'onclick': this.moveUp.bind(this, letterId)
					},
					{
						'id': 'sender-move-down-' + letterId,
						'text': this.mess.moveDown,
						'onclick': this.moveDown.bind(this, letterId)
					},
					{
						'id': 'sender-remove-' + letterId,
						'text': this.mess.remove,
						'onclick': this.remove.bind(this, letterId)
					}
				],
				{
					autoHide: true,
					autoClose: true
				},
				{
					events: {
						onclick: function () {

						}
					}
				}
			);
		}

		this.popup[letterId].bindElement = node;
		this.popup[letterId].show();
	};
	Editor.prototype.shiftTime = function (letterId, timeShift)
	{
		this.doAction('shiftTime', letterId, null, {'timeShift': timeShift});
	};
	Editor.prototype.moveDown = function (letterId)
	{
		var letterNode = this.getLetterNode(letterId);
		if (!letterNode.nextElementSibling)
		{
			if (this.popup[letterId])
			{
				this.popup[letterId].close();
			}
			return;
		}

		letterNode.parentNode.insertBefore(letterNode.nextElementSibling, letterNode);
		Helper.display.animateShowing(letterNode, true);

		this.doAction('moveDown', letterId, this.drawNumbers.bind(this));
	};
	Editor.prototype.moveUp = function (letterId)
	{
		var letterNode = this.getLetterNode(letterId);
		if (!letterNode.previousElementSibling)
		{
			if (this.popup[letterId])
			{
				this.popup[letterId].close();
			}
			return;
		}

		letterNode.parentNode.insertBefore(letterNode, letterNode.previousElementSibling);
		Helper.display.animateShowing(letterNode, true);

		this.doAction('moveUp', letterId, this.drawNumbers.bind(this));
	};
	Editor.prototype.remove = function (letterId)
	{
		var letterNode = this.getLetterNode(letterId);
		Helper.display.animateHiding(letterNode, true, function () {
			BX.remove(letterNode);
		});
		this.doAction('remove', letterId, this.drawNumbers.bind(this));
	};
	Editor.prototype.doAction = function (actionName, id, callback, dataParameters)
	{
		if (this.popup[id])
		{
			this.popup[id].close();
		}

		dataParameters = dataParameters || {};
		dataParameters.id = this.campaignId;
		dataParameters.letterId = id;

		var self = this;
		this.ajaxAction.request({
			action: actionName,
			onsuccess: function (data) {
				if (callback)
				{
					callback.apply(self, [data]);
				}
				if (top.BX.Sender.Page)
				{
					top.BX.Sender.Page.reloadGrid();
				}
			},
			onfailure: function () {

			},
			data: dataParameters
		});
	};

	function Timer (params)
	{
		this.manager = params.manager;
		this.dictionary = params.dictionary;
		this.container = BX('SENDER_TIME_DIALOG');
		this.typeNode = BX('SENDER_TIME_DIALOG_TYPE');
		this.valueNode = BX('SENDER_TIME_DIALOG_VALUE');
	}
	Timer.prototype.getLetterId = function(node)
	{
		return node.closest('[data-role="letter"]').getAttribute('data-letter-id');
	};
	Timer.prototype.show = function(node, anchorNode)
	{
		var popupWindow = BX.PopupWindowManager.create(
			'sender-trigger-chain-time-dialog',
			anchorNode,
			{
				'darkMode': false,
				'closeIcon': true,
				'content': this.container
			}
		);
		popupWindow.close();
		popupWindow.setBindElement(anchorNode);

		var btnTimeCancel = BX('SENDER_TIME_DIALOG_BTN_CANCEL');
		var btnTimeSave = BX('SENDER_TIME_DIALOG_BTN_SAVE');

		popupWindow.close();

		BX.unbindAll(btnTimeCancel);
		BX.bind(btnTimeCancel, 'click', function(){popupWindow.close();});

		BX.unbindAll(btnTimeSave);
		BX.bind(btnTimeSave, 'click', BX.delegate(function(){
			this.setTimeFromDialog(node);
			this.setText(node);
			popupWindow.close();
		}, this));

		this.setTimeToDialog(node);
		popupWindow.show();
	};
	Timer.prototype.getValue = function(node)
	{
		return parseInt(node.getAttribute('data-time-value'));
	};
	Timer.prototype.setValue = function(node, value)
	{
		value = parseInt(value);
		node.setAttribute('data-time-value', value);
		this.manager.shiftTime(this.getLetterId(node), value);
	};
	Timer.prototype.setText = function(node)
	{
		if(!node)
		{
			return;
		}

		var timeObj = this.convertTime(this.getValue(node));
		node.textContent = timeObj.VALUE + ' ' + timeObj.TEXT;
	};
	Timer.prototype.setTimeToDialog = function(node)
	{
		if(!node)
		{
			return;
		}

		var timeObj = this.convertTime(this.getValue(node));
		this.typeNode.value = timeObj.TYPE;
		this.valueNode.value = timeObj.VALUE;
	};
	Timer.prototype.setTimeFromDialog = function(node)
	{
		var value = this.convertTime(null, {
			'TYPE': this.typeNode.value,
			'VALUE': this.valueNode.value
		});
		this.setValue(node, value);
	};
	Timer.prototype.convertTime = function(minutes, timeObj)
	{
		if(minutes !== null)
		{
			minutes = parseInt(minutes);
			if(isNaN(minutes))
			{
				minutes = 0;
			}

			if (minutes > 0)
			{
				var filtered = this.dictionary.filter(function (item) {
					return (minutes % item.VALUE) === 0;
				}).map(function (item) {
					return {
						'TYPE': item.TYPE,
						'VALUE': minutes/item.VALUE,
						'TEXT': item.TEXT
					};
				});
				if (filtered.length > 0)
				{
					return filtered[filtered.length - 1];
				}
			}

			var result = this.dictionary[0];
			return {
				'TYPE': result.TYPE,
				'VALUE': 0,
				'TEXT': result.TEXT
			};
		}
		else
		{
			var value = parseInt(timeObj.VALUE);
			if(isNaN(value))
			{
				value = 0;
			}

			var objFiltered = this.dictionary.filter(function (item) {
				if (!item.VALUE)
				{
					return false;
				}

				return item.TYPE === timeObj.TYPE
			});
			if (objFiltered.length > 0)
			{
				return value * parseInt(objFiltered[0].VALUE);
			}

			return 0;
		}
	};


	BX.Sender.Letter.Chain = new Editor();

})(window);