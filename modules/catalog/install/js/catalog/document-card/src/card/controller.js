export default class DocumentCardController extends BX.UI.EntityEditorController
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);
		this._model.lockField('TOTAL');
	}

	onAfterSave()
	{
		super.onAfterSave();
		window.top.BX.onCustomEvent('DocumentCard:onDocumentCardSave');
		let sliders = BX.SidePanel.Instance.getOpenSliders();
		sliders.forEach((slider) => {
			if (slider.getWindow()?.BX.Catalog?.DocumentGridManager)
			{
				slider.getWindow().BX.onCustomEvent('DocumentCard:onDocumentCardSave');
			}
		});
	}
}
