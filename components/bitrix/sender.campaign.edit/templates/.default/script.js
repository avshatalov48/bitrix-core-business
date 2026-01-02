;(function ()
{

	BX.namespace('BX.Sender');
	if (BX.Sender.CampaignEditor)
	{
		return;
	}

	const Page = BX.Sender.Page;
	const Helper = BX.Sender.Helper;

	/**
	 * Editor.
	 *
	 */
	function Editor()
	{
		this.context = null;
		this.editor = null;
	}

	Editor.prototype.init = function(params)
	{
		this.mess = params.mess || {};
		this.toolbarId = params.toolbarId;
		this.isFrame = params.isFrame || false;
		this.isSaved = params.isSaved || false;
		this.campaignTile = params.campaignTile || {};
		this.prettyDateFormat = params.prettyDateFormat;

		this.context = BX(params.containerId);
		this.uiToolbar = BX.UI.ToolbarManager.get(this.toolbarId);
		this.titleNode = Helper.getNode('campaign-title', this.context);

		this.initUi();
		Page.initButtons();

		if (this.uiToolbar && this.isFrame)
		{
			this.uiToolbar.subscribe(BX.UI.ToolbarEvents.finishEditing, (event) => {
				const updatedTitle = event.getData().updatedTitle;

				if (updatedTitle && this.titleNode)
				{
					this.titleNode.value = updatedTitle;
				}
			});
		}
	};

	Editor.prototype.initUi = function()
	{
		this.ui = { title: this.titleNode };

		if (!this.ui.title.value.trim())
		{
			this.ui.title.value = Helper.replace(
				this.mess.patternTitle,
				{
					name: this.mess.newTitle,
					date: BX.date.format(this.prettyDateFormat),
				},
			);
		}

		if (this.isFrame && this.isSaved)
		{
			top.BX.onCustomEvent(top, 'sender-campaign-edit-change', [this.campaignTile]);
			BX.Sender.Page.slider.close();
		}
	};

	BX.Sender.CampaignEditor = new Editor();
})(window);
