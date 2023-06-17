import {Dom, Event, Loc, Reflection, Tag, Type} from 'main.core';
import {type BaseEvent, EventEmitter} from 'main.core.events'
import './entity-card.css';
import 'ui.entity-editor';
import 'ui.notification';
import 'ui.feedback.form';
import 'ui.hint';
import 'ui.design-tokens';
import 'ui.fonts.opensans';
import FieldsFactory from './fields-factory'
import ControllersFactory from './controllers-factory'
import IblockFieldConfigurationManager from './field-configurator/iblock-field-configuration-manager'
import GridFieldConfigurationManager from './field-configurator/grid-field-configuration-manager';
import {Popup} from "main.popup";
import {BaseCard} from "./base-card/base-card";
import {Slider} from 'catalog.store-use'

class EntityCard extends BaseCard
{
	stackWithOffset = null;

	constructor(id, settings = {})
	{
		super(id, settings);

		this.cardSettings = settings.cardSettings || [];
		this.hiddenFields = settings.hiddenFields || [];
		this.feedbackUrl = settings.feedbackUrl || '';
		this.variationGridId = settings.variationGridId;
		this.productStoreGridId = settings.productStoreGridId || null;
		this.settingsButtonId = settings.settingsButtonId;
		this.createDocumentButtonId = settings.createDocumentButtonId;
		this.createDocumentButtonMenuPopupItems = settings.createDocumentButtonMenuPopupItems;

		this.componentName = settings.componentName || null;
		this.componentSignedParams = settings.componentSignedParams || null;
		this.variationGridComponentName = (settings.variationGridComponentName || 'BX.Catalog.VariationGrid') + '.Instance';

		this.isSimpleProduct = settings.isSimpleProduct || false;
		this.isWithOrdersMode = settings.isWithOrdersMode || false;
		this.isInventoryManagementUsed = settings.isInventoryManagementUsed || false;

		this.registerFieldsFactory();
		this.registerControllersFactory();
		this.registerEvents();
		this.bindCardSettingsButton();
		this.bindCreateDocumentButtonMenu();

		EventEmitter.subscribe('SidePanel.Slider:onMessage', this.onSliderMessage.bind(this));
		EventEmitter.subscribe('BX.UI.EntityEditorSection:onLayout', this.onSectionLayout.bind(this));
		EventEmitter.subscribe('Grid::updated', this.onGridUpdatedHandler.bind(this));
	}

	getEntityType()
	{
		return 'Entity';
	}

	getCardSetting(id: string)
	{
		return this.cardSettings.filter(item => {
			return item.id === id;
		})[0];
	}

	isCardSettingEnabled(id: string)
	{
		const settingItem = this.getCardSetting(id);

		return settingItem && settingItem.checked;
	}

	bindCardSettingsButton()
	{
		const settingsButton = this.getSettingsButton();
		if (settingsButton)
		{
			Event.bind(settingsButton.getContainer(), 'click', this.showCardSettingsPopup.bind(this));
		}
	}

	getSettingsButton()
	{
		return BX.UI.ButtonManager.getByUniqid(this.settingsButtonId);
	}

	registerFieldsFactory()
	{
		return new FieldsFactory();
	}

	onGridUpdatedHandler(event: BaseEvent)
	{
		const [grid] = event.getCompatData();

		if (grid && grid.getId() === this.getVariationGridId())
		{
			this.updateSettingsCheckboxState();
		}
	}

	onSectionLayout()
	{

	}

	getProductStoreGridId()
	{
		return this.productStoreGridId;
	}

	getProductStoreGridComponent()
	{
		return Reflection.getClass('BX.Catalog.ProductStoreGridManager.Instance');
	}

	reloadProductStoreGrid()
	{
		const gridComponent = this.getProductStoreGridComponent();
		if (gridComponent)
		{
			if (this.getProductStoreGridId() && this.getProductStoreGridId() === gridComponent.getGridId())
			{
				gridComponent.reloadGrid();
			}
		}
	}

	/**
	 * @returns {BX.Catalog.VariationGrid|BX.Catalog.ProductServiceGrid|null}
	 */
	getVariationGridComponent()
	{
		//return Reflection.getClass('BX.Catalog.VariationGrid.Instance');
		return Reflection.getClass(this.variationGridComponentName);
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

	registerControllersFactory()
	{
		return new ControllersFactory();
	}

	registerEvents()
	{
		EventEmitter.subscribe('BX.UI.EntityConfigurationManager:onInitialize', this.onConfigurationManagerInit.bind(this));
		EventEmitter.subscribe('BX.UI.EntityEditor:onCancel', this.removeFileHiddenInputs.bind(this));
		EventEmitter.subscribe('BX.UI.EntityEditor:onInit', this.onEditorInitHandler.bind(this));

		EventEmitter.subscribe('BX.UI.EntityEditorAjax:onSubmit', this.onEditorAjaxSubmit.bind(this));
		EventEmitter.subscribe('onEntityCreate', this.onEntityCreateHandler.bind(this));
		EventEmitter.subscribe('onEntityUpdate', this.onEntityUpdateHandler.bind(this));

		EventEmitter.subscribe('onAttachFiles', this.onAttachFilesHandler.bind(this));
		EventEmitter.subscribe('BX.Main.Popup:onClose', this.onFileEditorCloseHandler.bind(this));

		EventEmitter.subscribe('onAfterVariationGridSave', this.onAfterVariationGridSave.bind(this));
	}

	onAfterVariationGridSave(event: BaseEvent)
	{
		const data = event.getData();

		if (data.gridId === this.getVariationGridId())
		{
			this.reloadProductStoreGrid();
		}
	}

	onAttachFilesHandler(event: BaseEvent)
	{
		const editor = this.getEditorInstance();
		if (!editor)
		{
			return;
		}

		const [, , uploader] = event.getCompatData();
		if (uploader && Type.isDomNode(uploader.fileInput))
		{
			const parent = uploader.fileInput.closest('[data-cid]');

			if (Type.isDomNode(parent))
			{
				const controlName = parent.getAttribute('data-cid');
				const control = editor.getControlByIdRecursive(controlName);

				if (control)
				{
					control.markAsChanged();
				}
			}
		}
	}

	onFileEditorCloseHandler(event: BaseEvent)
	{
		const [popup] = event.getCompatData();
		if (popup && popup.getId() === 'popupFM' && popup.onApplyFlag)
		{
			this.showNotification(Loc.getMessage('CATALOG_ENTITY_CARD_FILE_CLOSE_NOTIFICATION_2'), {
				id: 'fileCloseNotification',
				blinkOnUpdate: false,
				autoHideDelay: 5000
			});
		}
	}

	onEditorInitHandler(event: BaseEvent)
	{
		const [editor, fields] = event.getCompatData();

		if (editor && !fields.entityId)
		{
			const control = editor.getControlByIdRecursive('NAME');

			if (control)
			{
				requestAnimationFrame(() => {
					control.focus()
				});
			}
		}
	}

	/**
	 * @returns {BX.UI.EntityEditor|null}
	 */
	getEditorInstance()
	{
		if (Reflection.getClass('BX.UI.EntityEditor'))
		{
			return BX.UI.EntityEditor.getDefault();
		}

		return null;
	}

	onEditorAjaxSubmit(event: BaseEvent)
	{
		const [fields, response] = event.getCompatData();

		const title = fields['NAME-CODE'].NAME || '';
		this.changePageTitle(title);

		if (response.data)
		{
			if (Type.isBoolean(response.data.IS_SIMPLE_PRODUCT))
			{
				this.isSimpleProduct = response.data.IS_SIMPLE_PRODUCT;
			}
		}

		if (response.status === 'success')
		{
			this.removeFileHiddenInputs();
		}
	}

	onEntityCreateHandler(event: BaseEvent)
	{
		const [data] = event.getCompatData();
		this.postSliderMessage('onCreate', data)
	}

	onEntityUpdateHandler(event: BaseEvent)
	{
		const [data] = event.getCompatData();
		this.postSliderMessage('onUpdate', data)
	}

	postSliderMessage(action, fields)
	{
		BX.SidePanel.Instance.postMessage(
			window,
			`Catalog.${this.getEntityType()}Card::${action}`,
			fields
		);
	}

	changePageTitle(title)
	{
		const titleNode = document.getElementById('pagetitle');

		if (Type.isDomNode(titleNode))
		{
			titleNode.innerText = title;
		}

		document.title = title;

		if (BX.getClass('BX.SidePanel.Instance.updateBrowserTitle'))
		{
			BX.SidePanel.Instance.updateBrowserTitle();
		}
	}

	removeFileHiddenInputs()
	{
		document.querySelectorAll('form>input[type="hidden"]')
			.forEach(input => {
				let name = input.getAttribute('name');
				let deleteInput = document.querySelector(`form>input[name="${name}_del"]`);

				if (deleteInput)
				{
					Dom.remove(input);
					Dom.remove(deleteInput);
				}
			});
	}

	onConfigurationManagerInit(event: BaseEvent)
	{
		const [, eventArgs] = event.getCompatData();

		if (!eventArgs.type || eventArgs.type === 'editor')
		{
			eventArgs.configurationFieldManager = this.initializeIblockFieldConfigurationManager(eventArgs);
		}

		if (eventArgs.id === 'variation_grid')
		{
			eventArgs.configurationFieldManager = this.initializeVariationPropertyConfigurationManager(eventArgs);
		}

		if (eventArgs.id === 'service_grid')
		{
			eventArgs.configurationFieldManager = this.initializeServicePropertyConfigurationManager(eventArgs);
		}
	}

	initializeIblockFieldConfigurationManager(eventArgs)
	{
		const configurationManager = IblockFieldConfigurationManager.create(this.id, eventArgs);
		configurationManager.setCreationPageUrl(this.settings.creationPropertyUrl);

		return configurationManager;
	}

	initializeVariationPropertyConfigurationManager(eventArgs)
	{
		const configurationManager = GridFieldConfigurationManager.create(this.id, eventArgs);
		configurationManager.setCreationPageUrl(this.settings.creationVariationPropertyUrl);

		return configurationManager;
	}

	initializeServicePropertyConfigurationManager(eventArgs)
	{
		return GridFieldConfigurationManager.create(this.id, eventArgs);
	}

	showNotification(content, options)
	{
		options = options || {};

		if (BX.GetWindowScrollPos().scrollTop <= 10)
		{
			options.stack = this.getStackWithOffset();
		}

		BX.UI.Notification.Center.notify({
			content: content,
			position: 'top-right',
			width: 'auto',
			autoHideDelay: 3000,
			...options
		});
	}

	getStackWithOffset()
	{
		if (this.stackWithOffset === null)
		{
			this.stackWithOffset = new BX.UI.Notification.Stack(BX.mergeEx(
				{},
				BX.UI.Notification.Center.getStackDefaults(),
				{
					id: 'top-right-with-offset',
					position: 'top-right-with-offset',
					offsetY: 74
				}
			));
		}

		return this.stackWithOffset;
	}

	openFeedbackPanel()
	{
		EntityCard.openFeedbackPanelStatic();
	}

	static openFeedbackPanelStatic()
	{
		BX.UI.Feedback.Form.open({
			id: 'catalog-product-card-feedback',
			forms: [
				{'id': 269, 'lang': 'ru', 'sec': 'mqerov', 'zones': ['ru', 'by', 'kz']},
				{'id': 347, 'lang': 'en', 'sec': 'lxfji8', 'zones': ['en']},
				{'id': 349, 'lang': 'es', 'sec': 'gdf9i1', 'zones': ['es']},
				{'id': 355, 'lang': 'de', 'sec': 'x8k56n', 'zones': ['de']},
				{'id': 357, 'lang': 'ua', 'sec': '2z19xl', 'zones': ['ua']},
				{'id': 353, 'lang': 'com.br', 'sec': '5cleqn', 'zones': ['com.br']},
			],
		});
	}

	bindCreateDocumentButtonMenu()
	{
		const createDocumentButtonMenu = this.getCreateDocumentButtonMenu();
		if (createDocumentButtonMenu)
		{
			Event.bind(createDocumentButtonMenu.getContainer(), 'click', this.showCreateDocumentPopup.bind(this));
		}
	}

	getCreateDocumentButtonMenu()
	{
		const createDocumentButton = BX.UI.ButtonManager.getByUniqid(this.createDocumentButtonId);
		if (createDocumentButton)
		{
			return BX.UI.ButtonManager.getByUniqid(this.createDocumentButtonId).getMenuButton();
		}

		return null;
	}

	getCreateDocumentPopup()
	{
		if (!this.createDocumentPopup)
		{
			this.createDocumentPopup = new Popup(
				this.id + '-create-document',
				this.getCreateDocumentButtonMenu().getContainer(),
				{
					autoHide: true,
					draggable: false,
					offsetLeft: 0,
					offsetTop: 0,
					angle: {position: 'top', offset: 43},
					noAllPaddings: true,
					bindOptions: {forceBindPosition: true},
					closeByEsc: true,
					content: this.getCreateDocumentMenuContent()
				}
			);
		}

		return this.createDocumentPopup;
	}

	showCreateDocumentPopup()
	{
		this.getCreateDocumentPopup().show();
	}

	getCreateDocumentMenuContent()
	{
		const popupWrapper = Tag.render`<div class="menu-popup"></div>`;
		const popupItemsContainer = Tag.render`<div class="menu-popup-items"></div>`;
		popupWrapper.appendChild(popupItemsContainer);

		this.createDocumentButtonMenuPopupItems.forEach((item) => {
			popupItemsContainer.appendChild(Tag.render`
				<a class="menu-popup-item menu-popup-item-no-icon" href="${item.link}">
					<span class="menu-popup-item-text">${item.text}</span>
				</a>
			`);
		});

		return popupWrapper;
	}

	getCardSettingsPopup()
	{
		if (!this.settingsPopup)
		{
			this.settingsPopup = new Popup(
				this.id,
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
		this.getCardSettingsPopup().show();
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
		let input = '';
		if (!item.disabledCheckbox)
		{
			input = Tag.render`
				<input type="checkbox">
			`;

			input.checked = item.checked;
			input.disabled = item.disabled ?? false;
			input.dataset.settingId = item.id;
		}

		const hintNode = (
			Type.isStringFilled(item.hint)
				? Tag.render`<span class="catalog-entity-setting-hint" data-hint="${item.hint}"></span>`
				: ''
		);

		const setting = Tag.render`
				<label class="ui-ctl-block ui-entity-editor-popup-create-field-item ui-ctl-w100">
					<div class="ui-ctl-w10" style="text-align: center">${input}</div>
					<div class="ui-ctl-w75">
						<span class="ui-entity-editor-popup-create-field-item-title ${item.disabled ? 'catalog-entity-disabled-setting' : ''}">${item.title}${hintNode}</span>
						<span class="ui-entity-editor-popup-create-field-item-desc">${item.desc}</span>
					</div>
				</label>
			`;

		BX.UI.Hint.init(setting);

		if(item.id === 'SLIDER')
		{
			Event.bind(setting, 'change', (event) =>
			{
				new Slider().open(item.url, {})
				.then(() => {
					this.reloadGrid();
					this.getCardSettingsPopup().close();
				});
			})
		}
		else if(item.id === 'SEO')
		{
			Event.bind(setting, 'click', (event) =>
			{
				BX.SidePanel.Instance.open(item.url, {
					cacheable: false,
					allowChangeHistory: false,
					data: {
						'ELEMENT_ID': this.entityId
					},
					width: 1000
				});
			})
		}
		else
		{
			Event.bind(setting, 'change', this.setProductCardSetting.bind(this));
		}


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

	reloadGrid()
	{
		document.location.reload();
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
			let message = null;
			setting.checked = enabled;
			this.reloadVariationGrid();
			this.postSliderMessage('onUpdate', {});
			this.getCardSettingsPopup().close();

			if(setting.id === 'WAREHOUSE')
			{
				this.reloadGrid()
				message = enabled ? Loc.getMessage('CATALOG_ENTITY_CARD_WAREHOUSE_ENABLED') : Loc.getMessage('CATALOG_ENTITY_CARD_WAREHOUSE_DISABLED');
			}
			else
			{
				message = enabled ? Loc.getMessage('CATALOG_ENTITY_CARD_SETTING_ENABLED') : Loc.getMessage('CATALOG_ENTITY_CARD_SETTING_DISABLED');
				message = message.replace('#NAME#', setting.title)
			}

			this.showNotification(message, {
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

			let message = enabled ? Loc.getMessage('CATALOG_ENTITY_CARD_SETTING_ENABLED') : Loc.getMessage('CATALOG_ENTITY_CARD_SETTING_DISABLED');
			this.showNotification(message.replace('#NAME#', setting.title), {
				category: 'popup-settings'
			});
		});
	}

	updateSettingsCheckboxState()
	{
		const popupContainer = this.getCardSettingsPopup().getContentContainer();

		this.cardSettings
			.filter(item => item.action === 'grid' && Type.isArray(item.columns?.ITEMS))
			.forEach(item => {

				let allColumnsExist = true;
				item.columns.ITEMS.forEach(columnName => {
					if (!this.getVariationGrid().getColumnHeaderCellByName(columnName))
					{
						allColumnsExist = false;
					}
				})

				const checkbox = popupContainer.querySelector('input[data-setting-id="' + item.id + '"]');
				if (Type.isDomNode(checkbox))
				{
					checkbox.checked = allColumnsExist;
				}
			});
	}
}

export {EntityCard, BaseCard};
