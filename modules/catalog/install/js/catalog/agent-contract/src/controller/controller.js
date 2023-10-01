export class AgentContractController extends BX.UI.EntityEditorController
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);
	}

	onAfterSave()
	{
		super.onAfterSave();
		window.top.BX.onCustomEvent('AgentContract:onDocumentSave');
		let sliders = BX.SidePanel.Instance.getOpenSliders();
		sliders.forEach((slider) => {
			slider.getWindow().BX.onCustomEvent('AgentContract:onDocumentSave');
		});
	}
}
