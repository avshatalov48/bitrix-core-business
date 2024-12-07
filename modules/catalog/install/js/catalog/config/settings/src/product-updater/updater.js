class ProductSettingsUpdater
{
	constructor(params)
	{
		this.url = '/bitrix/tools/catalog/product_settings.php';
		this.stepOptions = {
			ajaxSessionID: '',
			maxExecutionTime: 30,
			maxOperationCounter: 10,
		};
		this.finish = false;
		this.currentState = {
			counter: 0,
			operationCounter: 0,
			errorCounter: 0,
			lastID: 0,
		};
		this.ajaxParams = {
			operation: 'Y',
		};
		this.iblocks = [];
		this.iblockIndex = -1;

		this.stepOptions.ajaxSessionID = 'productSettings';
		this.currentState.counter = 0;

		this.events = params.events;
		this.settings = params.settings;
	}

	nextStep()
	{
		for (let key in this.stepOptions)
		{
			if (this.stepOptions.hasOwnProperty(key))
			{
				this.ajaxParams[key] = this.stepOptions[key];
			}
		}
		for (let key in this.currentState)
		{
			if (this.currentState.hasOwnProperty(key))
			{
				this.ajaxParams[key] = this.currentState[key];
			}
		}

		this.ajaxParams.sessid = BX.bitrix_sessid();
		this.ajaxParams.lang = BX.message('LANGUAGE_ID');
		BX.ajax.loadJSON(
			this.url,
			this.ajaxParams,
			BX.proxy(this.nextStepResult, this)
		);
	}

	nextStepResult(result)
	{
		if (BX.type.isPlainObject(result))
		{
			this.currentState.lastID = result.lastID;
			this.stepOptions.maxOperationCounter = result.maxOperationCounter;

			this.currentState.operationCounter = parseInt(result.operationCounter, 10);
			if (isNaN(this.currentState.operationCounter))
			{
				this.currentState.operationCounter = 0;
			}

			this.currentState.errorCounter = parseInt(result.errorCounter, 10);
			if (isNaN(this.currentState.errorCounter))
			{
				this.currentState.errorCounter = 0;
			}

			if (this.events.onProgress)
			{
				this.events.onProgress({
					allCnt: result.allCounter,
					doneCnt: result.allOperationCounter,
					currentIblockName: this.iblocks[this.iblockIndex].NAME,
				});
			}

			if (this.finish)
			{
				this.finishOperation();
			}
			else
			{
				this.checkOperation(result.finishOperation);
			}
		}
	}

	finishOperation()
	{
		this.currentState.operationCounter = 0;
		this.currentState.errorCounter = 0;
		this.currentState.lastID = 0;
		this.finish = false;

		if (this.events.onComplete)
		{
			this.events.onComplete();
		}
	}

	startOperation()
	{
		BX.ajax.loadJSON(
			this.url,
			{
				sessid: BX.bitrix_sessid(),
				changeSettings: 'Y',
				...this.settings
			},
			BX.proxy(this.changeSettingsResult, this)
		);
	}

	changeSettingsResult(result)
	{
		if (!BX.type.isPlainObject(result))
		{
			return;
		}

		if (result.success === 'Y')
		{
			this.loadIblockList();
		}
		else
		{
			this.stopOperation();
		}
	}

	stopOperation()
	{
		this.finish = true;
	}

	checkIblockIndex()
	{
		return !(
			this.iblocks.length === 0
			|| this.iblockIndex < 0
			|| this.iblockIndex >= this.iblocks.length
		);
	}

	loadIblockList()
	{
		BX.ajax.loadJSON(
			this.url,
			{
				sessid: BX.bitrix_sessid(),
				getIblock: 'Y'
			},
			(result) => {
				if (BX.type.isArray(result))
				{
					this.iblocks = result;
					if (this.iblocks.length > 0)
					{
						this.iblockIndex = 0;
						this.iblockReindex();
					}
					else
					{
						this.stopOperation();
					}
				}
			}
		);
	}

	iblockReindex()
	{
		if (this.finish || !this.checkIblockIndex())
		{
			return;
		}

		this.initStep();
		this.nextStep();
	}

	initStep()
	{
		this.currentState.iblockId = this.iblocks[this.iblockIndex].ID;
		this.currentState.counter = this.iblocks[this.iblockIndex].COUNT;
		this.currentState.operationCounter = 0;
		this.currentState.errorCounter = 0;
		this.currentState.lastID = 0;
	}

	checkOperation(result)
	{
		if (!!result)
		{
			this.iblockIndex++;
			if (this.iblockIndex >= this.iblocks.length || this.currentState.errorCounter > 0)
			{
				this.finishOperation();
				if (this.currentState.errorCounter == 0)
				{
					this.finalRequest();
				}
			}
			else
			{
				this.initStep();
				this.nextStep();
			}
		}
		else
		{
			this.nextStep();
		}
	}

	finalRequest()
	{
		let iblockList = [];

		if (this.iblocks.length > 0)
		{
			for (let i = 0; i < this.iblocks.length; i++)
			{
				iblockList[iblockList.length] = this.iblocks[i].ID;
			}

			BX.ajax.get(
				this.url,
				{
					sessid: BX.bitrix_sessid(),
					finalRequest: 'Y',
					iblockList,
				}
			);
		}
	}
}

export {
	ProductSettingsUpdater
};
