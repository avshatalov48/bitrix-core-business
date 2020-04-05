BX.namespace("BX.Catalog");
BX.Catalog.StepOperations = (function()
{
	/** @param {{url:string, options: {}, ajaxParams: {}, visual: {}}} params */
	var classDescription = function(params)
	{
		this.errorCode = 0;
		this.url = '';
		this.stepOptions = {
			ajaxSessionID: '',
			maxExecutionTime: 0,
			maxOperationCounter: 0
		};
		this.finish = false;
		this.currentState = {
			counter: 0,
			operationCounter: 0,
			errorCounter: 0,
			lastID: 0
		};
		this.ajaxParams = {};
		this.visual = {
			startBtnID: '',
			stopBtnID: '',
			resultContID: '',
			errorContID: '',
			errorDivID: '',
			timeFieldID: ''
		};
		this.buttons = {
			start: null,
			stop: null
		};
		this.content = {
			result: null,
			errors: null,
			errorsFrame: null,
			timeField: null
		};

		if (BX.type.isPlainObject(params))
		{
			if (params.url === undefined || !BX.type.isNotEmptyString(params.url))
				this.addError(-0x0002);
			else
				this.url = params.url;

			if (BX.type.isPlainObject(params.options))
			{
				this.stepOptions.ajaxSessionID = params.options.ajaxSessionID;
				this.stepOptions.maxExecutionTime = params.options.maxExecutionTime;
				this.stepOptions.maxOperationCounter = params.options.maxOperationCounter;
				this.currentState.counter = params.options.counter;
			}
			else
			{
				this.addError(-0x0004);
			}

			if (BX.type.isPlainObject(params.ajaxParams))
				this.ajaxParams = params.ajaxParams;

			if (BX.type.isPlainObject(params.visual))
				this.visual = params.visual;
		}
		else
		{
			this.addError(-0x0001);
		}

		if (this.errorCode === 0)
			BX.ready(BX.proxy(this.init, this));
	};

	classDescription.prototype.init = function()
	{
		if (this.errorCode === 0)
		{
			if (!!this.visual.startBtnID)
			{
				this.buttons.start = BX(this.visual.startBtnID);
				if (!this.buttons.start)
					this.addError(-0x20000);
			}
			else
			{
				this.addError(-0x10000);
			}
			if (!!this.visual.stopBtnID)
			{
				this.buttons.stop = BX(this.visual.stopBtnID);
				if (!this.buttons.stop)
					this.addError(-0x80000);
			}
			else
			{
				this.addError(-0x40000);
			}
			this.content.timeField = BX(this.visual.timeFieldID);
		}

		if (this.errorCode === 0)
		{
			BX.bind(this.buttons.start, 'click', BX.proxy(this.startOperation, this));
			BX.bind(this.buttons.stop, 'click', BX.proxy(this.stopOperation, this));
			if (!!this.content.timeField)
				BX.bind(this.content.timeField, 'change', BX.proxy(this.changeMaxTime, this));
		}
	};

	classDescription.prototype.extendAjaxParams = function()
	{

	};

	classDescription.prototype.initResultDom = function()
	{
		if (this.content.result === null)
		{
			this.content.result = BX(this.visual.resultContID);
			this.content.errorsFrame = BX(this.visual.errorDivID);
			this.content.errors = BX(this.visual.errorContID);
		}
	};

	classDescription.prototype.nextStep = function()
	{
		var key;

		for (key in this.stepOptions)
		{
			if (this.stepOptions.hasOwnProperty(key))
				this.ajaxParams[key] = this.stepOptions[key];
		}
		for (key in this.currentState)
		{
			if (this.currentState.hasOwnProperty(key))
				this.ajaxParams[key] = this.currentState[key];
		}
		this.ajaxParams.sessid = BX.bitrix_sessid();
		this.ajaxParams.lang = BX.message('LANGUAGE_ID');
		this.extendAjaxParams();
		BX.showWait();
		BX.ajax.loadJSON(
			this.url,
			this.ajaxParams,
			BX.proxy(this.nextStepResult, this)
		);
	};

	classDescription.prototype.nextStepResult = function(result)
	{
		BX.closeWait();
		if (BX.type.isPlainObject(result))
		{
			this.initResultDom();
			this.currentState.lastID = result.lastID;
			this.stepOptions.maxOperationCounter = result.maxOperationCounter;

			this.currentState.operationCounter = parseInt(result.operationCounter, 10);
			if (isNaN(this.currentState.operationCounter))
				this.currentState.operationCounter = 0;

			this.showResult(result.message);

			this.currentState.errorCounter = parseInt(result.errorCounter, 10);
			if (isNaN(this.currentState.errorCounter))
				this.currentState.errorCounter = 0;
			if (this.currentState.errorCounter > 0)
				this.showErrors(result.errors);

			if (this.finish)
				this.finishOperation();
			else
				this.checkOperation(result.finishOperation);
		}
	};

	classDescription.prototype.checkOperation = function(result)
	{
		if (!!result)
			this.finishOperation();
		else
			this.nextStep();
	};

	classDescription.prototype.showResult = function(result)
	{
		if (!!this.content.result)
			BX.adjust(this.content.result, { html: result, style: { display: 'block' } });
	};

	classDescription.prototype.showErrors = function(errorList)
	{
		if (!!this.content.errors)
		{
			if (BX.type.isNotEmptyString(errorList))
				this.content.errors.innerHTML = this.content.errors.innerHTML + errorList;
			BX.style(this.content.errorsFrame, 'display', 'block');
		}
	};

	classDescription.prototype.finishOperation = function()
	{
		this.currentState.operationCounter = 0;
		this.currentState.errorCounter = 0;
		this.currentState.lastID = 0;
		this.buttons.start.disabled = false;
		this.buttons.stop.disabled = true;
		this.finish = false;
	};

	classDescription.prototype.startOperation = function()
	{
		if (!this.buttons.start.disabled)
		{
			this.changeMaxTime();
			this.buttons.start.disabled = true;
			this.buttons.stop.disabled = false;
			this.nextStep();
		}
	};

	classDescription.prototype.stopOperation = function()
	{
		if (!this.buttons.stop.disabled)
		{
			this.buttons.start.disabled = false;
			this.buttons.stop.disabled = true;
			this.finish = true;
		}
	};

	classDescription.prototype.changeMaxTime = function()
	{
		var maxTime;

		if (!!this.content.timeField)
		{
			maxTime = parseInt(this.content.timeField.value, 10);
			if (!isNaN(maxTime))
				this.stepOptions.maxExecutionTime = maxTime;
		}
	};

	classDescription.prototype.addError = function(code)
	{
		this.errorCode = this.errorCode || code;
	};

	return classDescription;
})();

/**
 * @extends {BX.Catalog.StepOperations}
 */
BX.Catalog.Iblocks = (function()
{
	/**
	 * @constructor
	 * @extends {BX.Catalog.StepOperations}
	 */
	var classDescription = function (params)
	{
		var i;

		this.iblocks = [];
		this.iblockIndex = -1;
		this.report = null;
		this.iblockContent = [];
		this.messages = {
			iblockErrorTitle: ''
		};

		classDescription.superclass.constructor.apply(this, arguments);
		if (typeof(this.visual.reportID) === 'undefined')
			this.visual.reportID = '';
		if (BX.type.isPlainObject(params.messages))
		{
			for (i in params.messages)
			{
				this.messages[i] = params.messages[i];
			}
		}
	};
	BX.extend(classDescription, BX.Catalog.StepOperations);

	classDescription.prototype.init = function ()
	{
		if (this.errorCode === 0)
		{
			if (!!this.visual.reportID)
			{
				this.report = BX(this.visual.reportID);
				if (!this.report)
					this.addError(-0x200000);
			}
			else
			{
				this.addError(-0x100000);
			}
		}
		classDescription.superclass.init.apply(this, arguments);
	};

	classDescription.prototype.checkIblockIndex = function()
	{
		return !(
			this.iblocks.length == 0
			|| this.iblockIndex < 0
			|| this.iblockIndex >= this.iblocks.length
		);
	};

	classDescription.prototype.startOperation = function()
	{
		if (!this.buttons.start.disabled)
		{
			this.clearOldReports();
			this.getIblockList();
		}
	};

	classDescription.prototype.clearOldReports = function()
	{
		var i;

		if (this.iblockContent.length > 0)
		{
			for (i = 0; i < this.iblockContent.length; i++)
			{
				if (!!this.iblockContent[i].container)
				{
					this.iblockContent[i].container = BX.cleanNode(this.iblockContent[i].container, true);
					this.iblockContent[i].result = null;
					this.iblockContent[i].errorsFrame = null;
					this.iblockContent[i].errors = null;
				}
			}
			this.iblockContent.length = 0;
		}
	};

	classDescription.prototype.createReindexReport = function()
	{
		var iblockId;

		if (!this.report)
			return;

		if (this.iblockIndex > 0)
			BX.adjust(this.iblockContent[this.iblockIndex-1].container, {style: { display: 'none' }});

		this.iblockContent[this.iblockIndex] = {
			container: null,
			result: null,
			errors: null,
			errorsFrame: null
		};

		iblockId = this.iblocks[this.iblockIndex].ID;

		this.report.appendChild(BX.create(
			'div',
			{
				props: {
					id: this.visual.prefix + iblockId
				},
				html: '<div id="' + this.visual.resultContID + iblockId + '" style="margin:0; width: 100%; display: none;"></div>' +
				'<div id="' + this.visual.errorDivID + iblockId + '" style="margin:0; width: 100%; display: none;">' +
				'<div class="adm-info-message-wrap adm-info-message-red">' +
				'<div class="adm-info-message">' +
				'<div id="' + this.visual.errorContID + iblockId + '"></div>' +
				'<div class="adm-info-message-icon"></div>' +
				'</div></div></div>'
			}
		));
	};

	classDescription.prototype.getIblockList = function()
	{
		BX.showWait();
		BX.ajax.loadJSON(
			this.url,
			{
				sessid: BX.bitrix_sessid(),
				getIblock: 'Y'
			},
			BX.proxy(this.getIblockListResult, this)
		);
	};

	classDescription.prototype.getIblockListResult = function(result)
	{
		BX.closeWait();
		if (BX.type.isArray(result))
		{
			this.iblocks = result;
			if (this.iblocks.length > 0)
			{
				this.changeMaxTime();
				this.buttons.start.disabled = true;
				this.buttons.stop.disabled = false;
				this.iblockIndex = 0;
				this.iblockReindex();
			}
			else
			{
				this.stopOperation();
			}
		}
	};

	classDescription.prototype.iblockReindex = function()
	{
		if (!this.checkIblockIndex() || this.finish)
			return;
		this.createReindexReport();
		this.initStep();
		this.nextStep();
	};

	classDescription.prototype.initStep = function()
	{
		this.currentState.iblockId = this.iblocks[this.iblockIndex].ID;
		this.currentState.counter = this.iblocks[this.iblockIndex].COUNT;
		this.currentState.operationCounter = 0;
		this.currentState.errorCounter = 0;
		this.currentState.lastID = 0;
	};

	classDescription.prototype.initResultDom = function()
	{
		var iblockId;

		if (!this.checkIblockIndex())
			return;

		if (this.iblockContent[this.iblockIndex].container === null)
		{
			iblockId = this.iblocks[this.iblockIndex].ID;
			this.iblockContent[this.iblockIndex].container = BX(this.visual.prefix + iblockId);
			this.iblockContent[this.iblockIndex].result = BX(this.visual.resultContID + iblockId);
			this.iblockContent[this.iblockIndex].errors = BX(this.visual.errorContID + iblockId);
			this.iblockContent[this.iblockIndex].errorsFrame = BX(this.visual.errorDivID + iblockId);
		}
	};

	classDescription.prototype.checkOperation = function(result)
	{
		if (!!result)
		{
			this.iblockIndex++;
			if (this.iblockIndex >= this.iblocks.length || this.currentState.errorCounter > 0)
			{
				this.finishOperation();
				if (this.currentState.errorCounter == 0)
					this.finalRequest();
			}
			else
			{
				this.createReindexReport();
				this.initStep();
				this.nextStep();
			}
		}
		else
		{
			BX.WindowManager.Get().adjustSizeEx();
			this.nextStep();
		}
	};

	classDescription.prototype.showResult = function(result)
	{
		if (!this.checkIblockIndex())
			return;

		if (!this.iblockContent[this.iblockIndex].container)
			return;

		if (!!this.iblockContent[this.iblockIndex].result)
			BX.adjust(this.iblockContent[this.iblockIndex].result, {html: result, style: {display: 'block'}});
		BX.adjust(this.iblockContent[this.iblockIndex].container, { style: {display: 'block'} });
		BX.adjust(this.report, { style: { display: 'block' }});
	};

	classDescription.prototype.showErrors = function(errorList)
	{
		if (!this.checkIblockIndex())
			return;

		if (!this.iblockContent[this.iblockIndex].container)
			return;

		if (!!this.iblockContent[this.iblockIndex].errors)
		{
			if (BX.type.isNotEmptyString(errorList))
				this.iblockContent[this.iblockIndex].errors = this.iblockContent[this.iblockIndex].errors.innerHTML + errorList;
			BX.style(this.iblockContent[this.iblockIndex].errorsFrame, 'display', 'block');
		}
	};

	classDescription.prototype.finalRequest = function()
	{
		var iblockList = [],
			i;

		if (this.iblocks.length > 0)
		{
			for (i = 0; i < this.iblocks.length; i++)
				iblockList[iblockList.length] = this.iblocks[i].ID;

			BX.ajax.get(
				this.url,
				{
					sessid: BX.bitrix_sessid(),
					finalRequest: 'Y',
					iblockList: iblockList
				}
			);
		}
	};

	return classDescription;
})();

/**
* @extends {BX.Catalog.Iblocks}
*/
BX.Catalog.CatalogReindex = (function()
{
	/**
	* @constructor
	* @extends {BX.Catalog.Iblocks}
	*/
	var classDescription = function(params)
	{
		this.catalogSelect = null;

		classDescription.superclass.constructor.apply(this, arguments);
		if (typeof(this.visual.catalogSelectID) === 'undefined')
			this.visual.catalogSelectID = '';
	};
	BX.extend(classDescription, BX.Catalog.Iblocks);

	/**
	 * @extends {BX.Catalog.Iblocks.init}
	 * @this {BX.Catalog.CatalogReindex}
	 */
	classDescription.prototype.init = function()
	{
		if (this.errorCode === 0)
		{
			if (!!this.visual.catalogSelectID)
			{
				this.catalogSelect = BX(this.visual.catalogSelectID);
				if (!this.catalogSelect)
					this.addError(-0x800000);
			}
			else
			{
				this.addError(-0x400000);
			}
		}
		classDescription.superclass.init.apply(this, arguments);
	};

	classDescription.prototype.getIblockList = function()
	{
		if (this.catalogSelect.selectedIndex != -1 && this.catalogSelect.options[this.catalogSelect.selectedIndex].value !== '')
		{
			BX.showWait();
			BX.ajax.loadJSON(
				this.url,
				{
					sessid: BX.bitrix_sessid(),
					getIblock: 'Y',
					iblock: this.catalogSelect.options[this.catalogSelect.selectedIndex].value
				},
				BX.proxy(this.getIblockListResult, this)
			);
		}
	};

	return classDescription;
})();

/**
 * @extends {BX.Catalog.Iblocks}
 */
BX.Catalog.ProductSettings = (function()
{
	/**
	 * @constructor
	 * @extends {BX.Catalog.Iblocks}
	 */
	var classDescription = function(params)
	{
		this.checkboxList = [];

		classDescription.superclass.constructor.apply(this, arguments);
		if (BX.type.isArray(params.checkboxList))
			this.checkboxList = params.checkboxList;
		else
			this.addError(-0x0008);
	};
	BX.extend(classDescription, BX.Catalog.Iblocks);

	/**
	 * @extends {BX.Catalog.Iblocks.init}
	 * @this {BX.Catalog.ProductSettings}
	 */
	classDescription.prototype.init = function()
	{
		classDescription.superclass.init.apply(this, arguments);
	};

	classDescription.prototype.startOperation = function()
	{
		if (!this.buttons.start.disabled)
		{
			this.clearOldReports();
			this.changeSettings();
		}
	};

	classDescription.prototype.changeSettings = function()
	{
		var ajaxData = {
			sessid: BX.bitrix_sessid(),
			changeSettings: 'Y'
		}, i, check;

		for (i = 0; i < this.checkboxList.length; i++)
		{
			check = BX(this.checkboxList[i]);
			if (check)
				ajaxData[check.name] = (check.checked ? 'Y' : 'N');
			check = null;
		}
		BX.showWait();
		BX.ajax.loadJSON(
			this.url,
			ajaxData,
			BX.proxy(this.changeSettingsResult, this)
		);
		ajaxData = null;
	};

	classDescription.prototype.changeSettingsResult = function(result)
	{
		var settings = {},
			i,
			check;
		BX.closeWait();
		if (!BX.type.isPlainObject(result))
			return;
		if (BX.type.isNotEmptyString(result.success) && result.success == 'Y')
		{
			if (!!top.changeProductSettings)
			{
				for (i = 0; i < this.checkboxList.length; i++)
				{
					check = BX(this.checkboxList[i]);
					if (check)
						settings[check.name] = (check.checked ? this.messages.status_yes : this.messages.status_no);
					check = null;
				}
				top.changeProductSettings(settings);
			}
			this.getIblockList();
		}
		else
		{
			this.stopOperation();
		}
	};

	classDescription.prototype.getIblockListResult = function(result)
	{
		BX.closeWait();
		if (BX.type.isArray(result))
		{
			this.iblocks = result;
			if (this.iblocks.length > 0)
			{
				this.changeMaxTime();
				this.buttons.start.disabled = true;
				this.buttons.stop.disabled = false;
				this.iblockIndex = 0;
				this.iblockReindex();
			}
			else
			{
				BX.WindowManager.Get().AllowClose();
				BX.WindowManager.Get().Close();
			}
		}
	};

	classDescription.prototype.finalRequest = function()
	{
		var iblockList = [],
			i;

		if (this.iblocks.length > 0)
		{
			for (i = 0; i < this.iblocks.length; i++)
				iblockList[iblockList.length] = this.iblocks[i].ID;

			BX.ajax.get(
				this.url,
				{
					sessid: BX.bitrix_sessid(),
					finalRequest: 'Y',
					iblockList: iblockList
				}
			);
			BX.WindowManager.Get().AllowClose();
			BX.WindowManager.Get().Close();
		}
	};

	return classDescription;
})();