import {Dom, Loc, Reflection, Tag, Text, Type} from 'main.core';
import {type BaseEvent, EventEmitter} from 'main.core.events'
import './entity-card.css';
import TabManager from './tab/manager';
import 'ui.entity-editor';
import 'ui.notification';
import FieldsFactory from './fields-factory'
import ControllersFactory from './controllers-factory'
import IblockFieldConfigurationManager from './field-configurator/iblock-field-configuration-manager'
import GridFieldConfigurationManager from './field-configurator/grid-field-configuration-manager';

export class EntityCard
{
	stackWithOffset = null;

	constructor(id, settings = {})
	{
		this.id = Type.isStringFilled(id) ? id : Text.getRandom();
		this.settings = settings;
		this.cardSettings = settings.cardSettings || [];
		this.feedbackUrl = settings.feedbackUrl || ''

		this.entityId = Text.toInteger(settings.entityId) || 0;
		this.container = document.getElementById(settings.containerId);

		this.componentName = settings.componentName || null;
		this.componentSignedParams = settings.componentSignedParams || null;

		this.isSimpleProduct = settings.isSimpleProduct || false;

		this.initializeTabManager();
		this.checkFadeOverlay();
		this.registerFieldsFactory();
		this.registerControllersFactory();
		this.registerEvents();
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

	initializeTabManager()
	{
		return new TabManager(this.id, {
			container: document.getElementById(this.settings.tabContainerId),
			menuContainer: document.getElementById(this.settings.tabMenuContainerId),
			data: this.settings.tabs || []
		});
	}

	checkFadeOverlay()
	{
		if (this.entityId <= 0)
		{
			this.overlay = Tag.render`<div class="catalog-entity-overlay"></div>`;
			Dom.append(this.overlay, this.container);

			if (window === window.top)
			{
				this.overlay.style.position = 'absolute';
				this.overlay.style.top = this.overlay.style.left = this.overlay.style.right = '-15px';
			}
		}
	}

	registerFieldsFactory()
	{
		return new FieldsFactory();
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
			this.showNotification(Loc.getMessage('CATALOG_ENTITY_CARD_FILE_CLOSE_NOTIFICATION'), {
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

		const title = fields.NAME || '';
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

	showNotification(content, options)
	{
		options = options || {};

		if (BX.GetWindowScrollPos().scrollTop <= 10)
		{
			options.stack = this.getStackWithOffset();
		}

		BX.UI.Notification.Center.notify({
			content: content,
			stack: options.stack || null,
			position: 'top-right',
			width: 'auto',
			category: options.category || null,
			autoHideDelay: options.autoHideDelay || 3000
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
		if (!Reflection.getClass('BX.SidePanel.Instance') || !Type.isStringFilled(this.feedbackUrl))
		{
			return;
		}

		BX.SidePanel.Instance.open(this.feedbackUrl, {
			cacheable: false,
			allowChangeHistory: false,
			width: 580
		});
	}
}