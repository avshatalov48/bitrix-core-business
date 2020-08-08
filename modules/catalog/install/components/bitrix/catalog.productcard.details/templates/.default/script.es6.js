import {Event, Loc, Reflection, Tag, Type} from 'main.core';
import {Popup} from 'main.popup';
import {EntityCard} from 'catalog.entity-card';
import {type BaseEvent, EventEmitter} from 'main.core.events';

class ProductCard extends EntityCard
{
	constructor(id, settings = {})
	{
		super(id, settings);
		this.variationGridId = settings.variationGridId;
		this.settingsButtonId = settings.settingsButtonId;

		this.bindCardSettingsButton();

		EventEmitter.subscribe('SidePanel.Slider:onMessage', this.onSliderMessage.bind(this));
		EventEmitter.subscribe('Grid::updated', this.onGridUpdatedHandler.bind(this));
		EventEmitter.subscribe('BX.UI.EntityEditorSection:onLayout', this.onSectionLayout.bind(this));
	}

	getEntityType()
	{
		return 'Product';
	}

	onSectionLayout(event: BaseEvent)
	{
		const [, eventData] = event.getCompatData();

		if (eventData.id === 'catalog_parameters')
		{
			eventData.visible = this.isSimpleProduct && this.isCardSettingEnabled('CATALOG_PARAMETERS');
		}
	}

	onEditorAjaxSubmit(event: BaseEvent)
	{
		super.onEditorAjaxSubmit(event);

		const [, response] = event.getCompatData();

		if (response.data)
		{
			if (response.data.NOTIFY_ABOUT_NEW_VARIATION)
			{
				this.showNotification(Loc.getMessage('CPD_NEW_VARIATION_ADDED'));
			}
		}
	}

	onGridUpdatedHandler(event: BaseEvent)
	{
		const [grid] = event.getCompatData();

		if (grid && grid.getId() === this.getVariationGridId())
		{
			this.updateSettingsCheckboxState();

			if (grid.getRows().getCountDisplayed() <= 0)
			{
				document.location.reload();
			}
		}
	}

	updateSettingsCheckboxState()
	{
		const popupContainer = this.getCardSettingsPopup().getContentContainer();

		this.cardSettings
			.filter(item => item.action === 'grid' && Type.isArray(item.columns))
			.forEach(item => {
				let allColumnsExist = true;

				item.columns.forEach(columnName => {
					if (!this.getVariationGrid().getColumnHeaderCellByName(columnName))
					{
						allColumnsExist = false;
					}
				})

				let checkbox = popupContainer.querySelector('input[data-setting-id="' + item.id + '"]');
				if (Type.isDomNode(checkbox))
				{
					checkbox.checked = allColumnsExist;
				}
			});
	}

	getSettingsButton()
	{
		return BX.UI.ButtonManager.getByUniqid(this.settingsButtonId);
	}

	bindCardSettingsButton()
	{
		const settingsButton = this.getSettingsButton();
		if (settingsButton)
		{
			Event.bind(settingsButton.getContainer(), 'click', this.showCardSettingsPopup.bind(this));
		}
	}

	getCardSettingsPopup()
	{
		if (!this.settingsPopup)
		{
			this.settingsPopup = new Popup(
				this._id,
				this.getSettingsButton().getContainer(),
				{
					autoHide: true,
					draggable: false,
					offsetLeft: 0,
					offsetTop: 0,
					angle: {position: 'top', offset: 43},
					noAllPaddings: true,
					bindOptions: {forceBindPosition: true},
					closeByEsc: true,
					content: this.prepareCardSettingsContent()
				}
			);
		}

		return this.settingsPopup;
	}

	showCardSettingsPopup()
	{
		const okCallback = () => this.getCardSettingsPopup().show();
		const variationGridInstance = Reflection.getClass('BX.Catalog.VariationGrid.Instance');

		if (variationGridInstance)
		{
			variationGridInstance.askToLossGridData(okCallback);
		}
		else
		{
			okCallback();
		}
	}

	prepareCardSettingsContent()
	{
		const content = Tag.render`
			<div class='ui-entity-editor-popup-create-field-list'></div>
		`;

		this.cardSettings.map(item => {
			content.append(this.getSettingItem(item));
		});

		return content;
	}

	getSettingItem(item)
	{
		const input = Tag.render`
			<input type="checkbox">
		`;
		input.checked = item.checked;
		input.dataset.settingId = item.id;

		const setting = Tag.render`
				<label class="ui-ctl-block ui-entity-editor-popup-create-field-item ui-ctl-w100">
					<div class="ui-ctl-w10" style="text-align: center">${input}</div>
					<div class="ui-ctl-w75">
						<span class="ui-entity-editor-popup-create-field-item-title">${item.title}</span>
						<span class="ui-entity-editor-popup-create-field-item-desc">${item.desc}</span>	
					</div>					
				</label>
			`;

		Event.bind(setting, 'change', this.setProductCardSetting.bind(this));

		return setting;
	}

	setProductCardSetting(event: BaseEvent)
	{
		const settingItem = this.getCardSetting(event.target.dataset.settingId);
		if (!settingItem)
		{
			return;
		}

		const settingEnabled = event.target.checked;

		if (settingItem.action === 'grid')
		{
			this.requestGridSettings(settingItem, settingEnabled);
		}
		else
		{
			this.requestCardSettings(settingItem, settingEnabled);
		}
	}

	requestGridSettings(setting, enabled)
	{
		if (!this.getVariationGrid())
		{
			new Error('Cant find variation grid.');
		}

		const headers = [];
		const cells = this.getVariationGrid().getRows().getHeadFirstChild().getCells();

		Array.from(cells).forEach((header) => {
			if ('name' in header.dataset)
			{
				headers.push(header.dataset.name);
			}
		});

		BX.ajax.runComponentAction(
			this.componentName,
			'setGridSetting',
			{
				mode: 'class',
				data: {
					signedParameters: this.componentSignedParams,
					settingId: setting.id,
					selected: enabled,
					currentHeaders: headers
				}
			}
		).then(() => {
			setting.checked = enabled;
			this.reloadVariationGrid();
			this.getCardSettingsPopup().close();

			let message = enabled ? Loc.getMessage('CPD_SETTING_ENABLED') : Loc.getMessage('CPD_SETTING_DISABLED');
			this.showNotification(message.replace('#NAME#', setting.title), {
				category: 'popup-settings'
			});
		});
	}

	requestCardSettings(setting, enabled)
	{
		BX.ajax.runComponentAction(
			this.componentName,
			'setCardSetting',
			{
				mode: 'class',
				data: {
					signedParameters: this.componentSignedParams,
					settingId: setting.id,
					selected: enabled
				}
			}
		).then(() => {
			setting.checked = enabled;

			if (setting.id === 'CATALOG_PARAMETERS')
			{
				const section = this.getEditorInstance().getControlByIdRecursive('catalog_parameters');
				if (section)
				{
					section.refreshLayout();
				}
			}

			this.getCardSettingsPopup().close();

			let message = enabled ? Loc.getMessage('CPD_SETTING_ENABLED') : Loc.getMessage('CPD_SETTING_DISABLED');
			this.showNotification(message.replace('#NAME#', setting.title), {
				category: 'popup-settings'
			});
		});
	}

	onSliderMessage(event: BaseEvent)
	{
		const [sliderEvent] = event.getCompatData();

		if (
			sliderEvent.getEventId() === 'Catalog.VariationCard::onCreate'
			|| sliderEvent.getEventId() === 'Catalog.VariationCard::onUpdate'
		)
		{
			this.reloadVariationGrid();
		}
	}

	/**
	 * @returns {BX.Catalog.VariationGrid|null}
	 */
	getVariationGridComponent()
	{
		return Reflection.getClass('BX.Catalog.VariationGrid.Instance');
	}

	reloadVariationGrid()
	{
		const gridComponent = this.getVariationGridComponent();
		if (gridComponent)
		{
			gridComponent.reloadGrid();
		}
	}

	getVariationGridId()
	{
		return this.variationGridId;
	}

	getVariationGrid()
	{
		if (!Reflection.getClass('BX.Main.gridManager.getInstanceById'))
		{
			return null;
		}

		return BX.Main.gridManager.getInstanceById(this.getVariationGridId());
	}
}

Reflection.namespace('BX.Catalog').ProductCard = ProductCard;