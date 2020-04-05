BX.namespace('BX.Sale.Admin.StepOperations');
/**
 * @extends {BX.Catalog.StepOperations}
 */
BX.Sale.Admin.StepOperations.StepOperationsFilter = (function()
{
/**
 * @constructor
 * @extends {BX.Catalog.StepOperations}
 */
var classDescription = function(params)
{
	this.useFilter = false;
	this.filter = [];
	this.filterFields = [];
	this.filterValues = {};
	if (!!params.filter && BX.type.isArray(params.filter))
		this.filterFields = params.filter;
	this.useFilter = this.filterFields.length > 0;
	this.emptyOrders = null;

	classDescription.superclass.constructor.apply(this, arguments);
};
BX.extend(classDescription, BX.Catalog.StepOperations);

classDescription.prototype.init = function()
{
	var i,
		fieldDom;

	classDescription.superclass.init.apply(this, arguments);
	if (this.errorCode === 0 && this.useFilter)
	{
		for (i = 0; i < this.filterFields.length; i++)
		{
			fieldDom = BX(this.filterFields[i]);
			if (!!fieldDom)
				this.filter[this.filter.length] = fieldDom;
		}
		this.useFilter = this.filter.length > 0;

		if (this.useFilter)
		{
			for (i = 0; i < this.filter.length; i++)
			{
				switch (this.filter[i].type.toLowerCase())
				{
					case 'text':
					case 'select-one':
						BX.bind(this.filter[i], 'change', BX.proxy(this.getFilterCounter, this));
						break;
				}
			}
		}
		this.getFilterCounter();

		if (BX.type.isNotEmptyString(this.visual.emptyOrdersId))
			this.emptyOrders = BX(this.visual.emptyOrdersId);
	}
};

classDescription.prototype.nextStep = function()
{
	if (this.useFilter)
	{
		this.getFilterValues();
		this.ajaxParams.filter = this.filterValues;
	}
	classDescription.superclass.nextStep.apply(this, arguments);
};

classDescription.prototype.finishOperation = function()
{
	classDescription.superclass.finishOperation.apply(this, arguments);
	BX.ajax.get(
		this.url,
		{
			sessid: BX.bitrix_sessid(),
			clearTags: 'Y'
		}
	);
};

classDescription.prototype.getFilterCounter = function()
{
	var params = {
		sessid: BX.bitrix_sessid(),
		lang: BX.message('LANGUAGE_ID'),
		getCount: 'Y'
	};

	if (this.useFilter)
	{
		this.getFilterValues();
		params.filter = this.filterValues;
		BX.showWait();
		this.disableFilterFields();
		BX.ajax.loadJSON(
			this.url,
			params,
			BX.proxy(this.getFilterCounterResult, this)
		);
	}
};

classDescription.prototype.getFilterCounterResult = function(result)
{
	BX.closeWait();
	this.enableFilterFields();
	if (typeof result === 'object')
	{
		this.currentState.allCounter = parseInt(result.counter, 10);
		if (isNaN(this.currentState.allCounter))
			this.currentState.allCounter = 0;
		this.buttons.start.disabled = (this.currentState.allCounter <= 0);
		if (BX.type.isElementNode(this.emptyOrders))
			BX.style(this.emptyOrders, 'display', (this.currentState.allCounter <= 0 ? 'block' : 'none'));
	}
};

classDescription.prototype.getFilterValues = function()
{
	var i;

	if (!this.useFilter)
		return;

	this.filterValues = {};
	for (i = 0; i < this.filter.length; i++)
	{
		switch(this.filter[i].type.toLowerCase())
		{
			case 'text':
			case 'select-one':
				this.filterValues[this.filter[i].name] = this.filter[i].value;
				break;
			default:
				break;
		}
	}
};

classDescription.prototype.enableFilterFields = function()
{
	if (!this.useFilter)
		return;
	var i;
	for (i = 0; i < this.filter.length; i++)
		this.filter[i].disabled = false;
};

classDescription.prototype.disableFilterFields = function()
{
	if (!this.useFilter)
		return;
	var i;
	for (i = 0; i < this.filter.length; i++)
		this.filter[i].disabled = false;
};

return classDescription;
})();