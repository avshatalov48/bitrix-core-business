import {BasePresetPanel, Preset, ContentWrapper, PresetCategory} from 'landing.ui.panel.basepresetpanel';
import {PageObject} from 'landing.pageobject';
import {Loc} from 'landing.loc';
import {Dom, Reflection, Runtime, Tag, Text, Type, Uri, Cache, Event} from 'main.core';
import type {BaseEvent} from 'main.core.events';
import {Backend} from 'landing.backend';
import {Loader} from 'main.loader';
import {FormClient} from 'crm.form.client';
import {Button} from 'ui.buttons';
import {Env} from 'landing.env';
import {StylePanel} from 'landing.ui.panel.stylepanel';
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';
import type {FormDictionary, FormOptions} from 'crm.form.type';
import {Alert, AlertColor} from 'ui.alerts';
import {SidebarButton} from 'landing.ui.button.sidebarbutton';
import {Guide} from 'ui.tour';
import {FieldsPanel} from 'landing.ui.panel.fieldspanel';
import {PhoneVerify} from 'bitrix24.phoneverify'
import 'ui.switcher';
import 'ui.hint';

import 'ui.fonts.opensans';
import './css/style.css';

type CrmField = {
	type: 'list' | 'string' | 'checkbox' | 'date' | 'text' | 'typed_string' | 'file',
	entity_field_name: string,
	entity_name: string,
	name: string,
	caption: string,
	multiple: boolean,
	required: boolean,
	hidden: boolean,
	items: Array<{ID: any, VALUE: any}>,
};

type CrmFieldsList = {
	[categoryId: string]: {
		CAPTION: string,
		FIELDS: Array<CrmField>
	},
};

type CrmCompany = {
	ID: string,
	TITLE: string,
};

type CrmCategory = {

};

type ResponseErrors = Array<{
	code: string,
	message: string,
	customData: any
}>;

const PHONE_VERIFY_FORM_ENTITY = 'crm_webform';

/**
 * @memberOf BX.Landing.UI.Panel
 */
export class FormSettingsPanel extends BasePresetPanel
{
	static getInstance(): FormSettingsPanel
	{
		const rootWindow = PageObject.getRootWindow();
		const rootWindowPanel = rootWindow.BX.Landing.UI.Panel.FormSettingsPanel;
		if (!rootWindowPanel.instance && !FormSettingsPanel.instance)
		{
			rootWindowPanel.instance = new FormSettingsPanel();
		}

		return (rootWindowPanel.instance || FormSettingsPanel.instance);
	}

	adjustActionsPanels = false;
	#phoneDoesntVerifiedResponseCode = 'PHONE_NOT_VERIFIED';

	constructor()
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel');
		this.setTitle(Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_TITLE'));

		this.lsCache = new Cache.LocalStorageCache();

		Dom.addClass(this.layout, 'landing-ui-panel-form-settings');

		this.subscribe('onCancel', () => {
			BX.onCustomEvent(this, 'BX.Landing.Block:onFormSettingsClose', [this.getCurrentBlock().id]);
		});

		this.disableOverlay();

		if (this.isCrmFormPage())
		{
			const {dictionary} = Env.getInstance().getOptions().formEditorData;

			const preparedSidebarButtons = dictionary.sidebarButtons.map((buttonOptions) => {
				return new SidebarButton({...buttonOptions, child: true});
			});
			this.setSidebarButtons(preparedSidebarButtons);

			const preparedPresets = dictionary.scenarios.map((presetOptions) => {
				return new Preset(presetOptions);
			});
			this.setPresets(preparedPresets);

			const preparedPresetCategories = dictionary.scenarioCategories.map((categoryOptions) => {
				return new PresetCategory(categoryOptions);
			});
			this.setCategories(preparedPresetCategories);
		}
		else
		{
			Dom.append(this.getBlockSettingsButton().render(), this.getRightHeaderControls());
		}

		this.subscribe('onCancel', this.onCancelClick.bind(this));

		Dom.append(this.getExpertSwitcherLayout(), this.layout);
	}

	getExpertSwitcherLayout(): HTMLDivElement
	{
		return this.cache.remember('switcherLayout', () => {
			const onClick = () => {
				this.getExpertModeSwitcher().node.click();
			};
			return Tag.render`
				<div class="landing-ui-expert-switcher">
					${this.getExpertModeSwitcher().node}
					<span onclick="${onClick}" class="landing-ui-expert-switcher-label">
						${Loc.getMessage('LANDING_FORM_EXPERT_MODE_SWITCHER_LABEL')}
					</span>
				</div>
			`;
		});
	}

	getExpertModeSwitcher(): Switcher
	{
		return this.cache.remember('expertModeSwitcher', () => {
			const rootWindow = PageObject.getRootWindow();
			const switcher = new rootWindow.BX.UI.Switcher({
				checked: this.isExpertModeEnabled(),
			});

			Dom.addClass(switcher.node, 'ui-switcher-size-sm ui-switcher-color-green');

			Event.bind(switcher.node, 'click', this.onExpertSwitcherClick.bind(this));

			return switcher;
		});
	}

	onExpertSwitcherClick()
	{
		this.lsCache.set('formEditorExpertMode', this.getExpertModeSwitcher().isChecked());
		this.onExpertModeChange();
	}

	getCurrentPreset(): ?Preset
	{
		const {templateId} = this.getFormOptions();
		const preset = this.getPresets().find((currentPreset) => {
			return currentPreset.options.id === templateId;
		});

		if (preset)
		{
			return preset;
		}

		return this.getPresets().find((currentPreset) => {
			return currentPreset.options.id === 'expert';
		});
	}

	onExpertModeChange()
	{
		const currentPreset = this.getCurrentPreset();

		if (
			this.getExpertModeSwitcher().isChecked()
			&& Type.isArrayFilled(currentPreset.options.expertModeItems)
		)
		{
			this.clearSidebar();
			this.getSidebarButtons()
				.filter((button) => {
					return currentPreset.options.expertModeItems.includes(button.id);
				})
				.forEach((button) => {
					if (!currentPreset.options.items.includes(button.id))
					{
						button.deactivate();
					}
					this.appendSidebarButton(button);
				});
		}
		else
		{
			const currentSidebarButton = this.getSidebarButtons().find((button) => {
				return button.isActive();
			});

			const buttons = this.getSidebarButtons().filter((button) => {
				return currentPreset.options.items.includes(button.id);
			});

			this.clearSidebar();
			buttons.forEach((button) => {
				this.appendSidebarButton(button);
			});

			if (
				currentSidebarButton
				&& !currentPreset.options.items.includes(currentSidebarButton.id)
			)
			{
				const defaultSection = (() => {
					if (Type.isStringFilled(currentPreset.options.defaultSection))
					{
						return currentPreset.options.defaultSection;
					}

					return 'fields';
				})();

				const defaultSectionButton = this.getSidebarButtons().find((button) => {
					return button.id === defaultSection;
				});

				if (defaultSectionButton)
				{
					defaultSectionButton.getLayout().click();
				}
			}
		}
	}

	isExpertModeEnabled(): boolean
	{
		return this.lsCache.get('formEditorExpertMode', false);
	}

	// eslint-disable-next-line class-methods-use-this
	isCrmFormPage(): boolean
	{
		return Env.getInstance().getOptions().specialType === 'crm_forms';
	}

	getFormDesignButton()
	{
		return this.cache.remember('formDesignButton', () => {
			return new Button({
				text: Loc.getMessage('LANDING_FORM_DESIGN_BUTTON'),
				color: Button.Color.LIGHT_BORDER,
				round: true,
				className: 'landing-ui-panel-top-button',
				onclick: this.onFormDesignButtonClick.bind(this),
			});
		});
	}

	getBlockSettingsButton()
	{
		return this.cache.remember('blockSettingsButton', () => {
			return new Button({
				text: Loc.getMessage('LANDING_FORM_SETTINGS_BLOCK_SETTINGS_BUTTON_TEXT'),
				color: Button.Color.LIGHT_BORDER,
				round: true,
				className: 'landing-ui-panel-top-button',
				onclick: this.onBlockSettingsButtonClick.bind(this),
			});
		});
	}

	onBlockSettingsButtonClick()
	{
		if (this.getCurrentBlock())
		{
			this.hide()
				.then(() => {
					this.getCurrentBlock().showContentPanel();
				});
		}
	}

	onFormDesignButtonClick()
	{
		if (this.getCurrentBlock())
		{
			this.getCurrentBlock().onFormDesignClick();
		}
	}

	getLoader(): Loader
	{
		return this.cache.remember('loader', () => {
			return new Loader({
				target: this.body,
			});
		});
	}

	showLoader()
	{
		Dom.addClass(this.layout, 'landing-ui-panel-state-content-load');
		void this.getLoader().show();
		Dom.hide(this.sidebar);
		Dom.hide(this.content);
		Dom.hide(this.getExpertSwitcherLayout());
	}

	hideLoader()
	{
		Dom.removeClass(this.layout, 'landing-ui-panel-state-content-load');
		this.getLoader().hide();
		Dom.show(this.sidebar);
		Dom.show(this.content);

		if (Type.isArrayFilled(this.getCurrentPreset().options.expertModeItems))
		{
			Dom.show(this.getExpertSwitcherLayout());
		}
	}

	showContentLoader()
	{
		Dom.addClass(this.layout, 'landing-ui-panel-state-body-load');
		super.showContentLoader();
	}

	hideContentLoader()
	{
		Dom.removeClass(this.layout, 'landing-ui-panel-state-body-load');
		super.hideContentLoader();
	}

	load(options = {}): Promise<any>
	{
		if (options.showWithOptions)
		{
			const editorData = Env.getInstance().getOptions().formEditorData;
			const {dictionary} = editorData;

			const preparedSidebarButtons = dictionary.sidebarButtons.map((buttonOptions) => {
				return new SidebarButton({...buttonOptions, child: true});
			});
			this.setSidebarButtons(preparedSidebarButtons);

			const preparedPresets = dictionary.scenarios.map((presetOptions) => {
				return new Preset(presetOptions);
			});
			this.setPresets(preparedPresets);

			const preparedPresetCategories = dictionary.scenarioCategories.map((categoryOptions) => {
				return new PresetCategory(categoryOptions);
			});
			this.setCategories(preparedPresetCategories);

			this.setCrmFields(editorData.crmFields);
			this.setCrmCompanies(editorData.crmCompanies);
			this.setCrmCategories(editorData.crmCategories);
			this.setAgreements(editorData.agreements);

			const currentOptions = Runtime.clone(editorData.formOptions);
			if (currentOptions.agreements.use !== true)
			{
				currentOptions.agreements.use = true;
				currentOptions.data.agreements = [];
			}

			this.setFormOptions(currentOptions);
			this.setFormDictionary(editorData.dictionary);

			return Promise.resolve();
		}

		const crmData = Backend.getInstance()
			.batch('Form::getCrmFields', {
				crmFields: {
					action: 'Form::getCrmFields',
					data: null,
				},
				crmCompanies: {
					action: 'Form::getCrmCompanies',
					data: null,
				},
				crmCategories: {
					action: 'Form::getCrmCategories',
					data: null,
				},
				agreements: {
					action: 'Form::getAgreements',
					data: null,
				},
			})
			.then((result) => {
				this.setCrmFields(result.crmFields.result);
				this.setCrmCompanies(result.crmCompanies.result);
				this.setCrmCategories(result.crmCategories.result);
				this.setAgreements(result.agreements.result);
			});

		const formOptions = FormClient.getInstance()
			.getOptions(this.getCurrentFormId())
			.then((options) => {
				const currentOptions = Runtime.clone(options);
				if (currentOptions.agreements.use !== true)
				{
					currentOptions.agreements.use = true;
					currentOptions.data.agreements = [];
				}

				this.setFormOptions(currentOptions);
			});

		const formDictionary = FormClient.getInstance()
			.getDictionary()
			.then((dictionary) => {
				this.setFormDictionary(dictionary);

				const preparedSidebarButtons = dictionary.sidebarButtons.map((buttonOptions) => {
					return new SidebarButton({...buttonOptions, child: true});
				});
				this.setSidebarButtons(preparedSidebarButtons);

				const preparedPresets = dictionary.scenarios.map((presetOptions) => {
					return new Preset(presetOptions);
				});
				this.setPresets(preparedPresets);

				const preparedPresetCategories = dictionary.scenarioCategories.map((categoryOptions) => {
					return new PresetCategory(categoryOptions);
				});
				this.setCategories(preparedPresetCategories);
			});

		return Promise.all([
			crmData,
			formOptions,
			formDictionary,
		]);
	}

	setAgreements(agreements)
	{
		this.cache.set('agreements', Runtime.orderBy(agreements, ['id'], ['asc']));
	}

	getAgreements(): Array<{[key: string]: any}>
	{
		return this.cache.get('agreements');
	}

	isLeadEnabled(): boolean
	{
		return this.getFormDictionary().document.lead.enabled;
	}

	setCurrentBlock(block: BX.Landing.Block)
	{
		this.cache.set('currentBlock', block);
	}

	getCurrentBlock(): ?BX.Landing.Block
	{
		return this.cache.get('currentBlock');
	}

	getSaveOriginalFileNameAlert(): HTMLElement
	{
		return this.cache.remember('saveOriginalFileNameAlert', () => {
			const alert = new Alert({
				text: Loc.getMessage('LANDING_CRM_FORM_MAIN_OPTION_WARNING'),
				color: AlertColor.WARNING,
			});

			return alert.render();
		});
	}

	show(
		options: {
			formId: number,
			instanceId: number,
			state?: 'presets',
			formOptions?: ?{[key: string]: any},
			showWithOptions: true,
		} = {
			formOptions: {},
		},
	): Promise<any>
	{
		if (!this.layout.parentNode)
		{
			this.enableToggleMode();
		}

		if (!this.isFormCreated())
		{
			this.disableTransparentMode();
		}

		const {mainOptions} = Env.getInstance().getOptions();
		if (mainOptions.saveOriginalFileName === false)
		{
			this.prependContent(
				this.getSaveOriginalFileNameAlert(),
			);

			const closeButtonTop = Text.toNumber(Dom.style(this.closeButton.getLayout(), 'top'));
			const alertHeight = this.getSaveOriginalFileNameAlert().getBoundingClientRect().height;

			Dom.style(this.closeButton.getLayout(), 'top', `${closeButtonTop + alertHeight}px`);
		}

		this.setCurrentBlock(options.block);
		this.setCurrentFormId(options.formId);
		this.setCurrentFormInstanceId(options.instanceId);

		this.showLoader();

		this.load(options)
			.then(() => {
				this.hideLoader();

				const formOptions = this.getFormOptions();
				if (Type.isPlainObject(options.formOptions))
				{
					const formOptions = Runtime.merge(
						this.getFormOptions(),
						options.formOptions,
					);
					this.setFormOptions(formOptions);
				}

				if (options.state === 'presets')
				{
					const presetFromRequest = this.getPresetIdFromRequest();
					let preset = false;

					if (presetFromRequest)
					{
						preset = this.getPresets().find((item) => {
							return item.options.id === presetFromRequest;
						});
					}

					if (preset)
					{
						this.applyPreset(preset);
					}
					else
					{
						this.onPresetFieldClick();
						this.activatePreset(formOptions.templateId);
					}
				}
				else
				{
					let preset = this.getPresets().find((item) => {
						return item.options.id === formOptions.templateId;
					});

					if (!preset)
					{
						preset = this.getPresets().find((item) => {
							return item.options.id === 'expert';
						});
					}

					if (this.isFormCreated())
					{
						this.applyPreset(preset);
						this.onPresetFieldClick();
					}
					else
					{
						this.applyPreset(preset, true);
					}
				}

				this.setInitialFormOptions(
					Runtime.clone(this.getFormOptions()),
				);

				if (!this.isFormCreated())
				{
					this.onExpertModeChange();
				}
			})
			.catch((error) => {
				if (Type.isArrayFilled(error))
				{
					const accessDeniedCode = 510;
					const isAccessDenied = error.some((errorItem) => {
						return String(errorItem.code) === String(accessDeniedCode);
					});

					if (isAccessDenied)
					{
						this.getLoader().hide();
						Dom.show(this.sidebar);
						Dom.show(this.content);
						Dom.hide(this.footer);
						Dom.append(this.getAccessError(), this.content);
					}
				}

				console.error(error);
			});

		const editorWindow = PageObject.getEditorWindow();
		Dom.addClass(editorWindow.document.body, 'landing-ui-hide-action-panels-form');

		void StylePanel.getInstance().hide();

		this.disableHistory();

		return super.show(options).then(() => {
			setTimeout(() => {
				const y = this.getCurrentBlock().node.offsetTop;
				PageObject.getEditorWindow().scrollTo(0, y);
			}, 300);

			BX.onCustomEvent(this, 'BX.Landing.Block:onFormSettingsOpen', [this.getCurrentBlock().id]);

			return Promise.resolve(true);
		});
	}

	getHistoryHint(): HTMLSpanElement
	{
		return this.cache.remember('historyHint', () => {
			const layout = Tag.render`
				<span 
					class="landing-ui-history-hint"
					data-hint="${Text.encode(Loc.getMessage('LANDING_FORM_HISTORY_DISABLED_HINT'))}"
					data-hint-no-icon
				></span>
			`;

			const rootWindow = PageObject.getRootWindow();
			rootWindow.BX.UI.Hint.initNode(layout);

			return layout;
		});
	}

	disableHistory()
	{
		const rootWindow = PageObject.getRootWindow();
		const TopPanel: BX.Landing.UI.Panel.Top = rootWindow.BX.Landing.UI.Panel.Top;
		if (TopPanel)
		{
			const {undoButton, redoButton} = TopPanel.getInstance();
			Dom.addClass(undoButton, 'landing-ui-disabled-from-form');
			Dom.addClass(redoButton, 'landing-ui-disabled-from-form');

			Dom.append(this.getHistoryHint(), undoButton.parentElement);
		}
	}

	enableHistory()
	{
		const rootWindow = PageObject.getRootWindow();
		const TopPanel: BX.Landing.UI.Panel.Top = rootWindow.BX.Landing.UI.Panel.Top;
		if (TopPanel)
		{
			const {undoButton, redoButton} = TopPanel.getInstance();
			Dom.removeClass(undoButton, 'landing-ui-disabled-from-form');
			Dom.removeClass(redoButton, 'landing-ui-disabled-from-form');
			Dom.remove(this.getHistoryHint());
		}
	}

	getAccessError(): HTMLDivElement
	{
		return this.cache.remember('accessErrorMessage', () => {
			return Tag.render`
				<div class="landing-ui-access-error-message">
					<div class="landing-ui-access-error-message-text">
						${Loc.getMessage('LANDING_CRM_ACCESS_ERROR_MESSAGE')}
					</div>
				</div>
			`;
		});
	}

	// eslint-disable-next-line class-methods-use-this
	getPresetIdFromRequest(): ?string
	{
		const uri = new Uri(window.top.location.href);
		return uri.getQueryParam('preset');
	}

	// eslint-disable-next-line class-methods-use-this
	isFormCreated(): boolean
	{
		const rootWindow = PageObject.getRootWindow();
		const uri = new Uri(rootWindow.location.href);
		return Text.toBoolean(uri.getQueryParam('formCreated'));
	}

	setCurrentFormId(formId: number)
	{
		this.cache.set('currentFormId', Text.toNumber(formId));
	}

	getCurrentFormId(): number
	{
		return this.cache.get('currentFormId');
	}

	setCurrentFormInstanceId(formId: number)
	{
		this.cache.set('currentFormInstanceId', formId);
	}

	getCurrentFormInstanceId(): number
	{
		return this.cache.get('currentFormInstanceId');
	}

	setCrmFields(fields)
	{
		this.cache.set('fields', fields);
	}

	getCrmFields(): CrmFieldsList
	{
		return this.cache.get('fields') || {};
	}

	setCrmCompanies(companies: Array<CrmCompany>)
	{
		this.cache.set('companies', companies);
	}

	getCrmCompanies(): Array<CrmCompany>
	{
		return this.cache.get('companies') || [];
	}

	setCrmCategories(categories: Array<CrmCategory>)
	{
		this.cache.set('crmCategories', categories);
	}

	getCrmCategories(): Array<CrmCategory>
	{
		return this.cache.get('crmCategories') || [];
	}

	setFormOptions(options: FormOptions)
	{
		this.cache.set('formOptions', options);
	}

	getFormOptions(): FormOptions
	{
		return Runtime.clone(this.cache.get('formOptions') || {});
	}

	setFormDictionary(dictionary: FormDictionary)
	{
		this.cache.set('formDictionary', dictionary);
	}

	getFormDictionary(): FormDictionary
	{
		return this.cache.get('formDictionary') || {};
	}

	setInitialFormOptions(options: FormOptions)
	{
		this.cache.set('initialFormOptions', Runtime.clone(options));
	}

	getInitialFormOptions(): FormOptions
	{
		return this.cache.get('initialFormOptions');
	}

	// eslint-disable-next-line
	getCrmForm()
	{
		const formApp = Reflection.getClass('b24form.App');
		if (formApp)
		{
			if (this.getCurrentFormInstanceId())
			{
				return formApp.get(this.getCurrentFormInstanceId());
			}

			let tmpIndex = -1;
			const currentFormIndex = [
				...this.getCurrentBlock().node.parentElement.childNodes,
			].reduce((acc, item) => {
				if (Dom.attr(item, 'data-subtype') === 'form')
				{
					tmpIndex += 1;
					if (item === this.getCurrentBlock().node)
					{
						return tmpIndex;
					}
				}

				return acc;
			}, 0);

			return formApp.list()[currentFormIndex];
		}

		return null;
	}

	onChange(event: BaseEvent)
	{
		const eventData = event.getData();
		const eventTargetValue = event.getTarget().getValue();

		Promise
			.resolve(eventTargetValue)
			.then((value) => {
				if (eventData.skipPrepare)
				{
					const formOptions = this.getFormOptions();

					if (
						Reflect.has(value, 'presetFields')
						|| Reflect.has(value, 'document')
						|| Reflect.has(value, 'result')
					)
					{
						const additionalValue = {};
						if (Reflect.has(value, 'document'))
						{
							additionalValue.payment = value.document.payment;
							delete value.document.payment;
						}

						return {
							...formOptions,
							...value,
							...additionalValue,
						};
					}

					if (
						Reflect.has(value, 'embedding')
						|| Reflect.has(value, 'callback')
						|| Reflect.has(value, 'whatsapp')
						|| (
							Reflect.has(value, 'name')
							&& Reflect.has(value, 'data')
							&& Reflect.has(value.data, 'useSign')
						)
					)
					{
						const mergedOptions = Runtime.merge(
							formOptions,
							value,
						);

						if (Reflect.has(value, 'responsible'))
						{
							mergedOptions.responsible.users = value.responsible.users;
						}

						return mergedOptions;
					}

					if (Reflect.has(value, 'recaptcha'))
					{
						const {key, secret} = value.recaptcha;
						delete value.recaptcha.key;
						delete value.recaptcha.secret;
						const captcha = {};

						if (!Type.isNil(key))
						{
							captcha.key = key;
						}

						if (!Type.isNil(secret))
						{
							captcha.secret = secret;
						}

						return {
							...formOptions,
							captcha: {
								...formOptions.captcha,
								...captcha,
							},
							data: {
								...formOptions.data,
								...value,
							},
						};
					}

					return {
						...formOptions,
						data: {
							...formOptions.data,
							...value,
						},
					};
				}

				return FormClient.getInstance()
					.prepareOptions(this.getFormOptions(), value)
					.then(result => {
						if (value.agreements)
						{
							result.data = Runtime.merge(result.data, value);
						}

						if (value.integration)
						{
							result.integration = value.integration;
						}

						if (value.fields)
						{
							result.data.fields = result.data.fields.map((field, index) => {
								return Runtime.merge(field, value.fields[index]);
							});
						}

						return result;
					});
			})
			.then((result) => {
				BX.Landing.UI.Panel.Top
					.getInstance()
					.setFormName(result.name);
				this.setFormOptions(result);
				this.getCrmForm().adjust(Runtime.clone(result.data));
			});
	}

	static sanitize(value: any): any
	{
		if (Type.isStringFilled(value))
		{
			return Text.decode(value)
				.replace(/<style[^>]*>.*<\/style>/gm, '')
				.replace(/<script[^>]*>.*<\/script>/gm, '')
				.replace(/<[^>]+>/gm, '');
		}

		return value;
	}

	getPersonalizationVariables(): Array<{name: string, value: string}>
	{
		return this.cache.remember('personalizationVariables', () => {
			return this.getFormDictionary().personalization.list.map((item) => {
				return {name: item.name, value: item.id};
			});
		});
	}

	getDefaultValuesVariables(): Array<{name: string, value: string}>
	{
		return this.cache.remember('personalizationVariables', () => {
			const {properties} = this.getFormDictionary();
			if (Type.isPlainObject(properties) && Type.isArrayFilled(properties.list))
			{
				return properties.list.map((item) => {
					return {name: item.name, value: item.id};
				});
			}

			return [];
		});
	}

	getContent(id: string): Promise<ContentWrapper>
	{
		const currentButton = this.getSidebarButtons().find((button) => {
			return id === button.options.id;
		});

		const {extension} = currentButton.options.data;

		const contentExtension = this.cache.remember(extension, () => {
			const rootWindow = PageObject.getRootWindow();
			return rootWindow.BX.Runtime
				.loadExtension(extension)
				.then((exports) => {
					return exports.default;
				});
		});

		return contentExtension.then((ContentWrapperClass) => {
			if (Type.isFunction(ContentWrapperClass))
			{
				return new ContentWrapperClass({
					formOptions: this.getFormOptions(),
					dictionary: this.getFormDictionary(),
					crmFields: this.getCrmFields(),
					companies: this.getCrmCompanies(),
					categories: this.getCrmCategories(),
					agreements: this.getAgreements(),
					isLeadEnabled: this.isLeadEnabled(),
					form: this.getCrmForm(),
				});
			}

			return null;
		});
	}

	onPresetClick(event: BaseEvent)
	{
		if (event.getTarget().options.openable)
		{
			this.disableTransparentMode();
		}

		const uri = new Uri(window.top.location.toString());
		uri.removeQueryParam('formCreated');
		uri.removeQueryParam('preset');
		window.top.history.replaceState(null, document.title, uri.toString());

		this.applyPreset(event.getTarget());
	}

	getCheckActionConfirm(): MessageBox
	{
		return this.cache.remember('checkActionConfirm', () => {
			const rootWindow = PageObject.getRootWindow();
			return new rootWindow.BX.UI.Dialogs.MessageBox({
				buttons: MessageBoxButtons.OK_CANCEL,
			});
		});
	}

	applyPreset(preset: Preset, skipOptions = false)
	{
		const lastPreset = this.getPresets().find((currentPreset: Preset) => {
			return Dom.hasClass(currentPreset.getLayout(), 'landing-ui-panel-preset-active');
		});

		this.getPresets().forEach((currentPreset) => {
			currentPreset.deactivate();
		});

		if (!skipOptions)
		{
			const runAction = (() => {
				if (Type.isArrayFilled(preset.options.actions))
				{
					return Promise.all(
						preset.options.actions.map((action) => {
							if (action.id === 'showTour')
							{
								const rootWindow = PageObject.getRootWindow();
								const guide = new rootWindow.BX.UI.Tour.Guide({
									onEvents: false,
									steps: action.data.steps,
								});

								guide.start();
							}

							if (action.id === 'showHelp')
							{
								if (window.top.BX.Helper)
								{
									window.top.BX.Helper.show(action.data.href);
								}
							}

							if (action.id === 'check')
							{
								return FormClient
									.getInstance()
									.check({
										templateId: preset.options.id,
									})
									.then((result) => {
										if (result.success === false)
										{
											const checkActionConfirm = this.getCheckActionConfirm();
											checkActionConfirm.setTitle(result.message.title);
											checkActionConfirm.setMessage(result.message.description);
											checkActionConfirm.setOkCaption(result.message.confirmButton);
											checkActionConfirm.setCancelCaption(result.message.cancelButton);

											return new Promise((resolve) => {
												checkActionConfirm.setOkCallback(() => {
													checkActionConfirm.getOkButton().setDisabled(false);
													checkActionConfirm.getCancelButton().setDisabled(false);
													checkActionConfirm.close();
													resolve(true);
												});

												checkActionConfirm.setCancelCallback(() => {
													checkActionConfirm.getOkButton().setDisabled(false);
													checkActionConfirm.getCancelButton().setDisabled(false);
													checkActionConfirm.close();
													resolve(false);
												});

												checkActionConfirm.show();
											});
										}

										return Promise.resolve(true);
									});
							}

							return Promise.resolve();
						}),
					);
				}

				return Promise.resolve();
			})();

			if (preset.options.openable)
			{
				this.showLoader();

				void runAction
					.then((actions) => {
						const actionsResult = (() => {
							if (Type.isArrayFilled(preset.options.actions))
							{
								return preset.options.actions.reduce((acc, item, index) => {
									return {...acc, [item.id]: actions[index]};
								}, {});
							}

							return {};
						})();

						if (
							(
								Reflect.has(actionsResult, 'check')
								&& actionsResult.check === true
							)
							|| (
								!Reflect.has(actionsResult, 'check')
							)
						)
						{
							this.getPresets().forEach((currentPreset) => {
								currentPreset.deactivate();
							});

							preset.activate();

							FormClient.getInstance()
								.prepareOptions(this.getFormOptions(), {templateId: preset.options.id})
								.then((result) => {
									return Backend.getInstance()
										.action('Form::getCrmFields')
										.then((crmFields) => {
											this.setCrmFields(crmFields);
											FieldsPanel.getInstance().setCrmFields(crmFields);
											return result;
										});
								})
								.then((result) => {
									BX.Landing.UI.Panel.Top
										.getInstance()
										.setFormName(result.name);
									this.setFormOptions({
										...result,
										templateId: preset.options.id,
									});
									this.getCrmForm().adjust(Runtime.clone(result.data));
									if (this.isFormCreated())
									{
										this.onPresetFieldClick();
										this.activatePreset(preset.options.id);
									}
									else
									{
										super.applyPreset(preset);

										if (Type.isArrayFilled(preset.options.expertModeItems))
										{
											Dom.show(this.getExpertSwitcherLayout());
											this.onExpertModeChange();
										}
										else
										{
											Dom.hide(this.getExpertSwitcherLayout());
										}
									}
									this.hideLoader();
								});
						}
						else
						{
							this.hideLoader();
							this.enableTransparentMode();

							if (lastPreset)
							{
								lastPreset.activate();
								preset.deactivate();
							}
						}
					});
			}
		}
		else
		{
			if (preset.options.openable)
			{
				super.applyPreset(preset);
				if (Type.isArrayFilled(preset.options.expertModeItems))
				{
					Dom.show(this.getExpertSwitcherLayout());
					this.onExpertModeChange();
				}
				else
				{
					Dom.hide(this.getExpertSwitcherLayout());
				}
				this.hideLoader();
			}

			preset.activate();
		}
	}

	getFormNode(): HTMLDivElement
	{
		return this.cache.remember('formNode', () => {
			return this.getCurrentBlock().node.querySelector('[data-b24form-use-style]');
		});
	}

	useBlockDesign(): boolean
	{
		return this.cache.remember('useBlockDesign', () => {
			return Text.toBoolean(Dom.attr(this.getFormNode(), 'data-b24form-use-style'));
		});
	}

	getCurrentCrmEntityName(): string
	{
		const {scheme} = this.getFormOptions().document;
		const schemeItem = this.getFormDictionary().document.schemes.find((item) => {
			return String(scheme) === String(item.id);
		});

		return schemeItem.name;
	}

	getNotSynchronizedFields(): Promise<any>
	{
		return FormClient
			.getInstance()
			.checkFields(this.getFormOptions())
			.then((result) => {
				return result;
			});
	}

	showSynchronizationPopup(notSynchronizedFields: Array<string>): Promise<boolean>
	{
		return new Promise((resolve) => {
			const onOk = (messageBox: MessageBox) => {
				messageBox.close();
				resolve(true);
			};

			const onCancel = (messageBox: MessageBox) => {
				messageBox.close();
				resolve(false);
			};

			const messageDescription = (() => {
				const entityName = Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_ENTITY_TEMPLATE')
					.replace('{entityName}', Text.encode(this.getCurrentCrmEntityName()));

				return Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_DESCRIPTION')
					.replace('{entityName}', Text.encode(entityName));
			})();

			const messageText = (() => {
				const fields = [...notSynchronizedFields].map((field) => {
					return Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_FIELD_TEMPLATE')
						.replace('{fieldName}', Text.encode(field));
				});

				if (notSynchronizedFields.length > 1)
				{
					const lastField = fields.pop();

					return Loc
						.getMessage('LANDING_SYNCHRONIZATION_POPUP_TEXT')
						.replace('{fieldsList}', fields.join(', '))
						.replace('{lastField}', lastField);
				}

				return Loc
					.getMessage('LANDING_SYNCHRONIZATION_POPUP_TEXT_1')
					.replace('{field}', fields.join(', '));
			})();

			window.top.BX.UI.Dialogs.MessageBox.confirm(
				`${messageDescription}<br><br>${messageText}`,
				Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_TITLE'),
				onOk,
				Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_OK_BUTTON_LABEL'),
				onCancel,
			);
		});
	}

	showSynchronizationErrorPopup(errors: Array<string>)
	{
		const message = errors.reduce((acc, item) => {
			return `${acc}\n\n${item}`;
		}, '');

		window.top.BX.UI.Dialogs.MessageBox.alert(message);
	}

	getErrorAlert(): MessageBox
	{
		return this.cache.remember('errorAlert', () => {
			const rootWindow = PageObject.getRootWindow();
			return new rootWindow.BX.UI.Dialogs.MessageBox({
				title: Loc.getMessage('LANDING_FORM_SAVE_ERROR_ALERT_TITLE'),
				buttons: MessageBoxButtons.OK,
				popupOptions: {
					maxHeight: 310,
				},
			});
		});
	}

	onSaveClick()
	{
		const dictionary = this.getFormDictionary();

		BX.onCustomEvent(this, 'BX.Landing.Block:onFormSave', [this.getCurrentBlock().id]);

		if (
			Type.isPlainObject(dictionary.permissions)
			&& Type.isPlainObject(dictionary.permissions.form)
			&& dictionary.permissions.form.edit === false
		)
		{
			const rootWindow = PageObject.getRootWindow();
			rootWindow.BX.UI.Dialogs.MessageBox.alert(Loc.getMessage('LANDING_FORM_SAVE_PERMISSION_DENIED'));
			return;
		}

		Dom.addClass(this.getSaveButton().layout, 'ui-btn-wait');

		this.getNotSynchronizedFields()
			.then((result) => {
				if (Type.isPlainObject(result.sync))
				{
					if (Type.isArrayFilled(result.sync.errors))
					{
						this.showSynchronizationErrorPopup(result.sync.errors);
						return false;
					}

					if (Type.isArrayFilled(result.sync.fields))
					{
						const fieldLabels = result.sync.fields.map((field) => {
							return field.label;
						});

						return this.showSynchronizationPopup(fieldLabels);
					}
				}

				return true;
			})
			.then((isConfirmed) => {
				if (isConfirmed)
				{
					const uri = new Uri(window.top.location.toString());
					uri.removeQueryParam('formCreated');
					window.top.history.replaceState(null, document.title, uri.toString());

					const initialOptions = this.getInitialFormOptions();
					const currentOptions = this.getFormOptions();
					const options = (() => {
						if (!this.isCrmFormPage())
						{
							const clonedOptions = Runtime.clone(currentOptions);
							clonedOptions.data.design = Runtime.clone(initialOptions.data.design);
							return clonedOptions;
						}

						return currentOptions;
					})();

					if (
						options.data.recaptcha.use
						&& (
							!this.getFormDictionary().captcha.hasKeys
							&& !options.captcha.hasDefaults
						)
					)
					{
						options.data.recaptcha.use = false;

						const rootWindow = PageObject.getRootWindow();
						const alert: MessageBox = new rootWindow.BX.UI.Dialogs.MessageBox({
							title: Loc.getMessage('LANDING_FORM_SAVE_CAPTCHA_ALERT_TITLE'),
							message: Loc.getMessage('LANDING_FORM_SAVE_CAPTCHA_ALERT_TEXT_2'),
							buttons: MessageBoxButtons.OK,
							onOk: () => {
								alert.close();
								Dom.removeClass(this.getSaveButton().layout, 'ui-btn-wait');
							},
						});

						alert.show();
					}

					void FormClient.getInstance()
						.saveOptions(options)
						.then((result) => {
							BX.onCustomEvent(this, 'BX.Landing.Block:onAfterFormSave', [this.getCurrentBlock().id]);

							this.setFormOptions(result);
							this.setInitialFormOptions(result);
							FormClient.getInstance().resetCache(result.id);
							Dom.removeClass(this.getSaveButton().layout, 'ui-btn-wait');

							const activeButton = this.getSidebarButtons().find((button) => {
								return button.isActive();
							});

							return Backend.getInstance()
								.action('Form::getCrmFields')
								.then((crmFields) => {
									this.setCrmFields(crmFields);
									FieldsPanel.getInstance().setCrmFields(crmFields);

									if (
										activeButton
										&& !Dom.hasClass(this.layout, 'landing-ui-panel-mode-transparent')
									)
									{
										activeButton.getLayout().click();
									}

									return result;
								});

							if (this.isCrmFormPage())
							{
								Dom.addClass(this.getSaveButton().layout, 'ui-btn-icon-done');
								const currentButtonText = this.getSaveButton().layout.innerText;
								this.getSaveButton().setText(Loc.getMessage('LANDING_FORM_EDITOR_SAVE_BUTTON_STATE_SAVED'));
								setTimeout(() => {
									Dom.removeClass(this.getSaveButton().layout, 'ui-btn-icon-done');
									this.getSaveButton().setText(currentButtonText);
								}, 1500);
							}
							else
							{
								void this.hide();
							}
						})
						.catch((errors) => {
							if (Type.isArrayFilled(errors))
							{
								if (this.#isPhoneValidationError(errors))
								{
									this.#showPhoneVerifySlider()
								}
								else
								{
									const errorMessage = errors
										.map((item) => {
											return Text.encode(item.message);
										})
										.join('<br><br>');

									const errorAlert = this.getErrorAlert();
									errorAlert.setMessage(errorMessage);
									errorAlert.show();
								}
							}
							else
							{
								const rootWindow = PageObject.getRootWindow();
								rootWindow.BX.UI.Dialogs.MessageBox.alert(
									Loc.getMessage('LANDING_FORM_SAVE_UNKNOWN_ERROR_ALERT_TEXT'),
									Loc.getMessage('LANDING_FORM_SAVE_ERROR_ALERT_TITLE'),
								);
							}

							Dom.removeClass(this.getSaveButton().layout, 'ui-btn-wait');
						});

					if (this.useBlockDesign() && this.isCrmFormPage())
					{
						this.disableUseBlockDesign();
					}
				}
				else
				{
					Dom.removeClass(this.getSaveButton().layout, 'ui-btn-wait');
				}
			});
	}

	#isPhoneValidationError(errors: ResponseErrors): boolean
	{
		return errors.some((error) => {
				return error.code === this.#phoneDoesntVerifiedResponseCode;
			}
		);
	}

	#showPhoneVerifySlider(): void
	{
		if (typeof PhoneVerify !== 'undefined')
		{
			PhoneVerify
				.getInstance()
				.setEntityType(PHONE_VERIFY_FORM_ENTITY)
				.setEntityId(this.getCurrentFormId())
				.startVerify({
					sliderTitle: Loc.getMessage('LANDING_FORM_EDITOR_PHONE_VERIFY_CUSTOM_SLIDER_TITLE'),
					title: Loc.getMessage('LANDING_FORM_EDITOR_PHONE_VERIFY_CUSTOM_TITLE'),
					description: Loc.getMessage('LANDING_FORM_EDITOR_PHONE_VERIFY_CUSTOM_DESCRIPTION'),
				});
		}
	}

	isChanged(): boolean
	{
		return JSON.stringify(this.getFormOptions()) !== JSON.stringify(this.getInitialFormOptions());
	}

	disableUseBlockDesign()
	{
		Dom.attr(this.getFormNode(), 'data-b24form-use-style', 'N');
		this.cache.set('useBlockDesign', false);

		Backend
			.getInstance()
			.action(
				'Landing\\Block::updateNodes',
				{
					block: this.getCurrentBlock().id,
					data: {
						'.bitrix24forms': {
							attrs: {
								'data-b24form-use-style': 'N',
							},
						},
					},
					lid: this.getCurrentBlock().lid,
					siteId: this.getCurrentBlock().siteId,
				},
				{code: this.getCurrentBlock().manifest.code},
			);
	}

	onCancelClick()
	{
		const initialFormOptions = this.getInitialFormOptions();
		this.getCrmForm().adjust(initialFormOptions.data);
		BX.Landing.UI.Panel.Top
			.getInstance()
			.setFormName(initialFormOptions.name);

		void this.hide();
	}

	hide(): Promise<*>
	{
		const editorWindow = PageObject.getEditorWindow();
		Dom.removeClass(editorWindow.document.body, 'landing-ui-hide-action-panels-form');
		this.enableHistory();
		return super.hide();
	}

	onSidebarButtonClick(event: BaseEvent)
	{
		const target = event.getTarget();
		if (target.options.id === 'design')
		{
			this.onFormDesignButtonClick();
		}
		else
		{
			super.onSidebarButtonClick(event);
		}
	}
}
