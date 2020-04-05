;(function ()
{

	BX.namespace('BX.Sender');
	if (BX.Sender.TriggerEditor)
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
		this.triggers = params.triggers;
		this.prettyDateFormat = params.prettyDateFormat;
		this.mess = params.mess || {};
		this.campaignTile = params.campaignTile || {};

		this.context = BX(params.containerId);

		this.initUi();
		Page.initButtons();
		Helper.hint.init(this.context);
	};
	Editor.prototype.initUi = function ()
	{
		this.ui = {
			title: Helper.getNode('campaign-title', this.context)
		};

		if (!this.ui.title.value.trim())
		{
			this.ui.title.value = Helper.replace(
				this.mess.patternTitle,
				{
					'name': this.mess.newTitle,
					'date': BX.date.format(this.prettyDateFormat)
				}
			);
		}

		if (this.isFrame)
		{
			Helper.titleEditor.init({'dataNode': this.ui.title});
		}

		if (this.isFrame && this.isSaved)
		{
			if (top.BX.Sender.Page)
			{
				top.BX.Sender.Page.reloadGrid();
			}
			top.BX.onCustomEvent(top, 'sender-campaign-edit-change', [this.campaignTile]);
			BX.Sender.Page.slider.close();
		}
	};
	Editor.prototype.setTrigger = function (bEnd, id)
	{
		var fieldName = 'START';
		if(bEnd)
			fieldName = 'END';

		var moduleId = BX('ENDPOINT_' + fieldName + '_MODULE_ID');
		var code = BX('ENDPOINT_' + fieldName + '_CODE');
		var form = BX('ENDPOINT_' + fieldName + '_FORM');
		var isClosed = BX('ENDPOINT_' + fieldName + '_IS_CLOSED_TRIGGER');
		var closedTime = BX('ENDPOINT_' + fieldName + '_CLOSED_TRIGGER_TIME');
		var runForOldData = BX('ENDPOINT_' + fieldName + '_RUN_FOR_OLD_DATA_FORM');
		var closedForm = BX('ENDPOINT_' + fieldName + '_CLOSED_FORM');
		var settingsForm = BX('ENDPOINT_' + fieldName + '_SETTINGS');

		if(id && this.triggers[fieldName][id])
		{
			moduleId.value = this.triggers[fieldName][id].MODULE_ID;
			code.value = this.triggers[fieldName][id].CODE;
			form.innerHTML = this.triggers[fieldName][id].FORM;
			isClosed.value = this.triggers[fieldName][id].IS_CLOSED_TRIGGER;
			closedTime.value = this.triggers[fieldName][id].CLOSED_TRIGGER_TIME;
			var canRunForOldData = this.triggers[fieldName][id].CAN_RUN_FOR_OLD_DATA;

			if(isClosed.value === 'Y')
				closedForm.style.display = '';
			else
				closedForm.style.display = 'none';

			if(isClosed.value === 'Y' || form.innerHTML.length > 0)
				settingsForm.style.display = '';
			else
				settingsForm.style.display = 'none';

			if(runForOldData)
			{
				if(canRunForOldData === 'Y')
					runForOldData.style.display = '';
				else
					runForOldData.style.display = 'none';
			}
		}
		else
		{
			moduleId.value = '';
			code.value = '';
			form.innerHTML = '';
			isClosed.value = 'N';

			closedForm.style.display = 'none';
			settingsForm.style.display = 'none';
		}
	};


	BX.Sender.TriggerEditor = new Editor();

})(window);