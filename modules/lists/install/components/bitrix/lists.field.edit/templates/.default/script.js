BX.namespace("BX.Lists");
BX.Lists.ListsFieldEditClass = (function ()
{
	var ListsFieldEditClass = function (parameters)
	{
		this.randomString = parameters.randomString;
		this.ajaxUrl = '/bitrix/components/bitrix/lists.field.edit/ajax.php';
		this.iblockTypeId = parameters.iblockTypeId;
		this.iblockId = parameters.iblockId;
		this.socnetGroupId = parameters.socnetGroupId;
		this.jsClass = 'ListsFieldEditClass'+parameters.randomString;
		this.maxSort = 0;
		this.generateCode = Boolean(parameters.generateCode);
		this.prefixFieldId = 'bx-lists-field-';
		this.listAction = parameters.listAction;
		this.init();
	};

	ListsFieldEditClass.prototype.init = function()
	{
		this.transliterate();
		if(this.generateCode && BX(this.prefixFieldId+'name'))
		{
			BX(this.prefixFieldId+'name').onkeyup = BX.delegate(function() {
				this.transliterate();
			}, this);
			BX(this.prefixFieldId+'name').onblur = BX.delegate(function() {
				this.transliterate();
			}, this);
		}

		this.setStyleForForm();

		this.actionButton = BX('lists-title-action');
		this.actionPopupItems = [];
		this.actionPopupObject = null;
		this.actionPopupId = 'lists-title-action';
		BX.bind(this.actionButton, 'click', BX.delegate(this.showListAction, this));
	};

	ListsFieldEditClass.prototype.showListAction = function ()
	{
		if(!this.actionPopupItems.length)
		{
			for(var k = 0; k < this.listAction.length; k++)
			{
				this.actionPopupItems.push({
					text : this.listAction[k].text,
					onclick : this.listAction[k].action
				});
			}
		}
		if(!BX.PopupMenu.getMenuById(this.actionPopupId))
		{
			var buttonRect = this.actionButton.getBoundingClientRect();
			this.actionPopupObject = BX.PopupMenu.create(
				this.actionPopupId,
				this.actionButton,
				this.actionPopupItems,
				{
					closeByEsc : true,
					angle: true,
					offsetLeft: buttonRect.width/2,
					events: {
						onPopupShow: BX.proxy(function () {
							BX.addClass(this.actionButton, 'webform-button-active');
						}, this),
						onPopupClose: BX.proxy(function () {
							BX.removeClass(this.actionButton, 'webform-button-active');
						}, this)
					}
				}
			);
		}
		if(this.actionPopupObject) this.actionPopupObject.popupWindow.show();
	};

	ListsFieldEditClass.prototype.setStyleForForm = function()
	{
		var rows = BX('tab1_edit_table').rows;

		[].forEach.call((rows || []), function(row) {
			if(row.cells[0] && !row.cells[0].className)
				row.cells[0].className = 'bx-field-name';
			if(row.cells[1] && !row.cells[1].className)
				row.cells[1].className = 'bx-field-value';
		});
	};

	ListsFieldEditClass.prototype.transliterate = function()
	{
		if(!this.generateCode || !BX(this.prefixFieldId+'name') || !BX(this.prefixFieldId+'code'))
			return false;

		var value = BX.translit(BX(this.prefixFieldId+'name').value, { change_case: 'U' });
		while(true)
		{
			var firstSymbol = value.charAt(0);
			if(!isNaN(parseInt(firstSymbol))) value = value.substr(1);
			else break;
		}
		value = value.replace(/([\'`]+)+/g, '');
		BX(this.prefixFieldId+'code').value = value;
	};

	ListsFieldEditClass.prototype.changeType = function(formId)
	{
		var _form = BX(formId);
		var _flag = BX('action');
		if (_form && _flag)
		{
			BX.showWait();
			_flag.value = 'type_changed';
			_form.submit();
		}
	};

	ListsFieldEditClass.prototype.deleteField = function(formId, message)
	{
		var _form = BX(formId);
		var _flag = BX('action');
		if (_form && _flag)
		{
			if (confirm(message))
			{
				_flag.value = 'delete';
				_form.submit();
			}
		}
	};

	ListsFieldEditClass.prototype.deleteListItem = function(item)
	{
		var tableRow = BX.findParent(item, {'tag': 'tr'});
		if (tableRow)
		{
			var hidden = BX.findChild(tableRow, {'tag': 'input', 'class': 'sort-input'}, true);
			if (hidden)
			{
				var table = tableRow.parentNode;
				table.parentNode.appendChild(hidden);
				table.removeChild(tableRow);
			}
		}
	};

	ListsFieldEditClass.prototype.toggleInput = function(inputId)
	{
		var _input = BX(inputId);
		if (_input)
		{
			if (_input.style.display == 'block')
				_input.style.display = 'none';
			else
				_input.style.display = 'block';
		}
	};

	ListsFieldEditClass.prototype.addNewTableRow = function(tableId, regexp, rindex)
	{
		var tbl = BX(tableId);
		var cnt = tbl.rows.length;
		var oRow = tbl.insertRow(cnt);
		var col_count = tbl.rows[cnt - 1].cells.length;

		if (!this.maxSort)
		{
			var inpSort = BX.findChild(tbl.rows[cnt - 1], {'tag': 'input', 'class': 'sort-input'}, true);
			if (inpSort)
				this.maxSort = parseInt(inpSort.value) + 10;
		}

		for (var i = 0; i < col_count; i++)
		{
			var oCell = oRow.insertCell(i);
			var html = tbl.rows[cnt - 1].cells[i].innerHTML;
			oCell.align = tbl.rows[cnt - 1].cells[i].align;
			if (i == 0)
				oCell.style.display = 'none';
			else
				oCell.className = tbl.rows[cnt - 1].cells[i].className;
			oCell.innerHTML = html.replace(regexp,
				function (html)
				{
					return html.replace('[n' + arguments[rindex] + ']', '[n' + (1 + parseInt(arguments[rindex])) + ']');
				}
			);
		}

		var newSort = BX.findChild(tbl.rows[cnt], {'tag': 'input', 'class': 'sort-input'}, true);
		if (newSort)
		{
			newSort.value = this.maxSort;
			this.maxSort += 10;
		}
	};

	return ListsFieldEditClass;
})();

/* A function for moving the rows in the list */
var dragTable = function (table, callbacks)
{
	var dragTr = false;
	var tbody = false;
	var startY = false;
	var indexStart = false;
	var trStart = false;
	var init = function ()
	{
		tbody = table.getElementsByTagName('tbody')[0];
		table.onmousedown = start;
		table.onmouseleave = stop;
		table.onmouseup = stop;
		table.onmousemove = move;
	};

	var start = function (e)
	{
		var target = e.target || e.srcElement;
		if (target.tagName == 'TD')
		{
			BX.eventReturnFalse(e);
			var tr = target.parentNode;
			if (tr.parentNode.nodeName !== 'TBODY' && target.tagName == "TD") return false;
			if (dragTr && target.tagName == "TD") return false;
			trStart = tr;
			dragTr = tr;
			dragTr.setAttribute('class', 'lists-field-drag-tr');
			startY = e.y || e.clientY;
			indexStart = __getIndex(dragTr);
			if (callbacks && callbacks.start) callbacks.start(table, trStart, indexStart);
		}

	};

	var stop = function (e)
	{
		var target = e.target || e.srcElement;
		if (target.tagName == 'TD')
		{
			if (!dragTr) return false;
			dragTr.removeAttribute('class');
			startY = false;
			dragTr = false;
			if (callbacks && callbacks.stop) callbacks.stop(table, trStart, indexStart, __getIndex(trStart));
		}
	};

	var move = function (e)
	{
		var target = e.target || e.srcElement;
		if (target.tagName == 'TD')
		{
			if (!dragTr) return false;
			var currentTr = target.parentNode;
			if (currentTr === dragTr || currentTr.nodeName !== 'TR' || currentTr.parentNode.nodeName !== 'TBODY') return false;
			var y = e.y || e.clientY;
			var top = y < startY;
			startY = y;
			if (top)
			{
				tbody.insertBefore(dragTr, currentTr);
			}
			else
			{
				tbody.insertBefore(currentTr, dragTr);
			}
			if (callbacks && callbacks.dragging) callbacks.dragging(table, dragTr, currentTr, __getIndex(trStart))
		}
	};

	var __getIndex = function (tr)
	{
		var trs = tbody.getElementsByTagName('tr');
		for (var i = 0, length = trs.length; i < length; i++)
		{
			if (trs[i] === tr) return (i + 1);
		}
		return 0;
	};
	init();
};

function enumerationValues(table)
{
	var listValue = BX.findChildren(table, {"tag": "input", "attribute": {"type": "hidden"}}, true);
	if (listValue[listValue.length - 1].getAttribute('name') == "LIST[n0][SORT]")
	{
		for (var i = 1; i <= listValue.length; i++)
		{
			listValue[i - 1].setAttribute('value', i * 10);
		}
	}
}