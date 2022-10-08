import {Event, Loc, Reflection} from 'main.core';
import {EntityCard} from 'catalog.entity-card';
import {type BaseEvent, EventEmitter} from 'main.core.events';
import {Popup} from "main.popup";
import {Button} from "ui.buttons";

class VariationCard extends EntityCard
{
	#isQuantityTraceNoticeShown = false;

	constructor(id, settings = {})
	{
		super(id, settings);
		EventEmitter.subscribe('BX.Grid.SettingsWindow:save', () => this.postSliderMessage('onUpdate', {}));
	}

	getEntityType()
	{
		return 'Variation';
	}

	onSectionLayout(event: BaseEvent)
	{
		const [section, eventData] = event.getCompatData();

		if (eventData.id === 'catalog_parameters')
		{
			eventData.visible = this.isCardSettingEnabled('CATALOG_PARAMETERS');
		}

		EventEmitter.subscribe('BX.UI.EntityEditorList:onItemSelect', (event) => {
			const isQuantityTraceRestricted = !(this.isWithOrdersMode && !this.isInventoryManagementUsed);
			if (this.#isQuantityTraceNoticeShown || !isQuantityTraceRestricted)
			{
				return;
			}

			const field = event.getData()[1]?.field;
			if (!field)
			{
				return;
			}

			if (field.getId() !== 'QUANTITY_TRACE' || field._selectedValue !== 'N')
			{
				return;
			}

			const popup = new Popup({
				content: Loc.getMessage('CPVD_QUANTITY_TRACE_NOTICE'),
				overlay: true,
				titleBar: Loc.getMessage('CPVD_QUANTITY_TRACE_NOTICE_TITLE'),
				closeByEsc: true,
				closeIcon: true,
				buttons: [
					new Button({
						text: Loc.getMessage('CPVD_QUANTITY_TRACE_ACCEPT'),
						className: 'ui-btn ui-btn-md ui-btn-primary',
						events: {
							click: (function()
							{
								this.#isQuantityTraceNoticeShown = false;
								popup.destroy();
							}).bind(this),
						}
					}),
				],
				events: {
					onAfterClose: (function () {
						this.#isQuantityTraceNoticeShown = false;
					}).bind(this),
				}
			});
			popup.show();

			this.#isQuantityTraceNoticeShown = true;
		});

		EventEmitter.subscribe('onEntityUpdate', (event) => {
			const editor = event.getData()[0]?.sender;
			if (!editor)
			{
				return;
			}

			const quantityTraceValue = editor._model.getField('QUANTITY_TRACE', 'D');
			const isQuantityTraceRestricted = !(this.isWithOrdersMode && !this.isInventoryManagementUsed);
			if (quantityTraceValue !== 'N' && isQuantityTraceRestricted)
			{
				editor.getControlById('QUANTITY_TRACE')?.setVisible(false);
			}
		});

		section?.getChildren().forEach((field) => {
			if (this.hiddenFields.includes(field?.getId()))
			{
				field.setVisible(false);
			}
		});
	}
}

Reflection.namespace('BX.Catalog').VariationCard = VariationCard;