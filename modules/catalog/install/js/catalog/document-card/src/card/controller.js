import {EventEmitter} from 'main.core.events';

export default class DocumentCardController extends BX.UI.EntityEditorController
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);
		this._model.lockField('TOTAL');
	}

	doInitialize()
	{
		this.#subscribeToEvents();
	}

	#subscribeToEvents()
	{
		this.#subscribeToProductRowSummaryEvents();
	}

	#subscribeToProductRowSummaryEvents()
	{
		EventEmitter.subscribe(
			'BX.UI.EntityEditorProductRowSummary:onDetailProductListLinkClick',
			() => {
				EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', {tabId: 'tab_products'});
			}
		);
		EventEmitter.subscribe(
			'BX.UI.EntityEditorProductRowSummary:onAddNewRowInProductList',
			() => {
				EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', {tabId: 'tab_products'});
				setTimeout(() => {
					EventEmitter.emit('onFocusToProductList');
				}, 500);
			}
		)

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
