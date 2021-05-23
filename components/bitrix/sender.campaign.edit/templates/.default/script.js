;(function ()
{

	BX.namespace('BX.Sender');
	if (BX.Sender.CampaignEditor)
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
		this.mess = params.mess || {};
		this.campaignTile = params.campaignTile || {};

		this.context = BX(params.containerId);

		this.initUi();
		Page.initButtons();
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
			top.BX.onCustomEvent(top, 'sender-campaign-edit-change', [this.campaignTile]);
			BX.Sender.Page.slider.close();
		}
	};


	BX.Sender.CampaignEditor = new Editor();

})(window);