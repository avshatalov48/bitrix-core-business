BX.namespace("BX.Sale.Admin.OrderBasket.SettingsDialog");

BX.Sale.Admin.OrderBasket.SettingsDialog = function(params)
{
	this.basket = params.basket;

	this.idPrefix = this.basket.idPrefix || "sale_order_basket";
	this.formId = this.idPrefix+"_settings_dialog_form";
	this.contentId = this.idPrefix+"columns_form";
	this.dialog = this.create();
	this.form = null;
	this.columnsLimit = 21;
};

BX.Sale.Admin.OrderBasket.SettingsDialog.prototype.getForm = function()
{
	if(!this.form)
		this.form = BX(this.formId);

	return this.form;
};

BX.Sale.Admin.OrderBasket.SettingsDialog.prototype.unselectExcessFields = function(selectNode)
{
	if(!selectNode || !selectNode.options || selectNode.options.length <= this.columnsLimit)
		return false;

	var limit = this.columnsLimit - selectNode.form.columns.options.length;

	var selectedCounter = 0;

	for(var j=0, l1=selectNode.options.length; j<l1; j++)
	{
		if(!selectNode.options[j].selected)
			continue;

		selectNode.options[j].selected = (limit >= selectedCounter++);
	}

	return false;
};

BX.Sale.Admin.OrderBasket.SettingsDialog.prototype.unselectUnRemovableFields = function(selectNode)
{
	if(this.basket.unRemovableFields.length <= 0)
		return false;

	for(var j=0, l1=selectNode.options.length; j<l1; j++)
	{
		if(!selectNode.options[j].selected)
			continue;

		for(var i=0, l2=this.basket.unRemovableFields.length; i<l2; i++)
			if(selectNode.options[j].value == this.basket.unRemovableFields[i])
				selectNode.options[j].selected = false;
	}

	return false;
};

BX.Sale.Admin.OrderBasket.SettingsDialog.prototype.isUnRemovableFieldSelected = function(selectNode)
{
	if(this.basket.unRemovableFields.length <= 0)
		return false;

	for(var j=0, l1=selectNode.options.length; j<l1; j++)
	{
		if(!selectNode.options[j].selected)
			continue;

		for(var i=0, l2=this.basket.unRemovableFields.length; i<l2; i++)
			if(selectNode.options[j].value == this.basket.unRemovableFields[i])
				return true;
	}

	return false;
};

BX.Sale.Admin.OrderBasket.SettingsDialog.prototype.onSelectedChange = function(selectNode)
{
	if(selectNode.selectedIndex == -1)
	{
		selectNode.form.up_btn.disabled = true;
		selectNode.form.down_btn.disabled = true;
		selectNode.form.del_btn.disabled = true;
	}
/*
	else if(this.isUnRemovableFieldSelected(selectNode))
	{
		selectNode.form.up_btn.disabled = false;
		selectNode.form.down_btn.disabled = false;
		selectNode.form.del_btn.disabled = true;
	}
*/
	else
	{
		this.unselectUnRemovableFields(selectNode);
		selectNode.form.up_btn.disabled = false;
		selectNode.form.down_btn.disabled = false;
		selectNode.form.del_btn.disabled = false;
	}
};

BX.Sale.Admin.OrderBasket.SettingsDialog.prototype.onAvailableChange = function(selectNode)
{
	if(selectNode.selectedIndex == -1)
	{
		selectNode.form.add_btn.disabled = true;
	}
	else
	{
		this.unselectExcessFields(selectNode);
		selectNode.form.add_btn.disabled = false;
	}
};

BX.Sale.Admin.OrderBasket.SettingsDialog.prototype.create = function()
{
	var dialog = new BX.CDialog({
		'content':'<form id="'+this.formId+'" name="'+this.formId+'"></form>',
		'title': BX.message("SALE_ORDER_BASKET_JS_SETTINGS_TITLE"),
		'width': 850,
		'height': 350,
		'resizable': false
	});

	this.setButtons(dialog);

	var form = BX(this.formId),
		content = BX(this.contentId);

	if(form && content)
		form.appendChild(content);

	return dialog;
};

BX.Sale.Admin.OrderBasket.SettingsDialog.prototype.show = function()
{
	var sel = BX('adm-sale-basket-sett-all-cols'),
		pos;

	//workaround to prevent scroll up in chrome
	BX.bind(sel, 'mousedown', function(){pos = BX.GetWindowScrollPos();});
	BX.bind(sel, 'focus', function(){window.scrollTo(pos.scrollLeft, pos.scrollTop);});
	this.dialog.Show();
};

BX.Sale.Admin.OrderBasket.SettingsDialog.prototype.setButtons = function(dialog)
{
	var _this = this;
	dialog.ClearButtons();
	dialog.SetButtons([
		{
			'title': BX.message("SALE_ORDER_BASKET_JS_SETTINGS_APPLY"),
			'name': 'apply',
			'action': function() {
				BX.showWait();
				_this.save(
					_this.getVisibleColumns()
				);
				this.parentWindow.Close();
			}
		},
		BX.CDialog.prototype.btnCancel
	]);
};

BX.Sale.Admin.OrderBasket.SettingsDialog.prototype.changeColumns = function()
{
	console.log("changeColumns");
	BX.closeWait();
};

BX.Sale.Admin.OrderBasket.SettingsDialog.prototype.onAddColumn = function()
{
	console.log("addColumn");

	jsSelectUtils.addSelectedOptions(this.form.allColumns, this.form.columns, false);
	jsSelectUtils.deleteSelectedOptions(this.form.allColumns);
};

BX.Sale.Admin.OrderBasket.SettingsDialog.prototype.save = function(columns)
{
	BX.Sale.Admin.OrderEditPage.blockForm();
	BX.Sale.Admin.OrderAjaxer.sendRequest(
		{
			action: "saveBasketVisibleColumns",
			columns: columns,
			idPrefix: this.idPrefix,
			callback: function(result)
			{
				if(result && result.RESULT && result.RESULT == "OK")
				{
					var form = BX.Sale.Admin.OrderEditPage.getForm();
					form.submit();
				}
				else if(result && result.ERROR)
				{
					BX.debug("Error saving settings: " + result.ERROR);
				}
				else
				{
					BX.debug("Error saving settings!");
				}
			}
		}
	);
};

BX.Sale.Admin.OrderBasket.SettingsDialog.prototype.getVisibleColumns = function()
{
	var form = this.getForm(),
		result = [],
		select = form.elements.columns;

	for(var i= 0, l=select.options.length; i<l; i++)
		result.push(select.options[i].value);

	return result;
};