import {BasePresetPanel, Preset, ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {PageObject} from 'landing.pageobject';
import {Loc} from 'landing.loc';
import {Dom, Reflection, Runtime, Tag, Text, Type, Uri} from 'main.core';
import type {BaseEvent} from 'main.core.events';
import {Backend} from 'landing.backend';
import {Loader} from 'main.loader';
import {FormClient} from 'crm.form.client';
import {Button} from 'ui.buttons';
import {Env} from 'landing.env';
import {StylePanel} from 'landing.ui.panel.stylepanel';
import {MessageBox} from 'ui.dialogs.messagebox';
import type {FormDictionary, FormOptions} from 'crm.form.type';

import HeaderAndButtonContent from './internal/content/header-and-buttons/header-and-buttons';
import AgreementsContent from './internal/content/agreements/agreements';
import SpamProtection from './internal/content/spam-protection/spam-protection';
import AnalyticsContent from './internal/content/analytics/analytics';
import FieldsContent from './internal/content/fields/fields';
import FieldsRulesContent from './internal/content/fields-rules/fields-rules';
import ActionsContent from './internal/content/actions/actions';
import EmbedContent from './internal/content/embed/embed';
import Identify from './internal/content/identify/identify';
import CrmContent from './internal/content/crm/crm';
import DefaultValues from './internal/content/defaultvalues/defaultvalues';
import FacebookContent from './internal/content/facebook/facebook';
import VkContent from './internal/content/vk/vk';
import Callback from './internal/content/callback/callback';
import Other from './internal/content/other/other';

import sidebarButtons from './internal/sidebar-buttons';
import presetCategories from './internal/preset-categories';
import presets from './internal/presets';

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

	constructor()
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel');
		this.setTitle(Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_TITLE'));

		this.disableOverlay();

		const preparedSidebarButtons = sidebarButtons.filter((button) => {
			return this.isCrmFormPage() || button.id !== 'embed';
		});

		this.setSidebarButtons(preparedSidebarButtons);
		this.setCategories(presetCategories);

		const filteredPresets = presets.filter((preset) => {
			return (
				Loc.getMessage('LANGUAGE_ID') === 'ru'
				|| (
					Loc.getMessage('LANGUAGE_ID') !== 'ru'
					&& preset.options.id !== 'vk'
				)
			);
		});

		this.setPresets(filteredPresets);

		if (!this.isCrmFormPage())
		{
			Dom.append(this.getBlockSettingsButton().render(), this.getRightHeaderControls());
		}

		this.subscribe('onCancel', this.onCancelClick.bind(this));
	}

	// eslint-disable-next-line class-methods-use-this
	isCrmFormPage(): boolean
	{
		return Env.getInstance().getOptions().specialType === 'crm_forms';
	}

	getBlockSettingsButton()
	{
		return this.cache.remember('blockSettingsButton', () => {
			return new Button({
				text: Loc.getMessage('LANDING_FORM_SETTINGS_BLOCK_SETTINGS_BUTTON_TEXT'),
				color: Button.Color.LIGHT_BORDER,
				onclick: this.onBlockSettingsButtonClick.bind(this),
				size: Button.Size.SMALL,
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
		void this.getLoader().show();
		Dom.hide(this.sidebar);
		Dom.hide(this.content);
		Dom.hide(this.getPresetField().getLayout());
	}

	hideLoader()
	{
		this.getLoader().hide();
		Dom.show(this.sidebar);
		Dom.show(this.content);
		Dom.show(this.getPresetField().getLayout());
	}

	load(options = {}): Promise<any>
	{
		if (options.showWithOptions)
		{
			const editorData = Env.getInstance().getOptions().formEditorData;

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

		this.setCurrentBlock(options.block);
		this.setCurrentFormId(options.formId);
		this.setCurrentFormInstanceId(options.instanceId);

		this.showLoader();

		this.load(options)
			.then(() => {
				this.hideLoader();

				const formOptions = this.getFormOptions();

				if (!this.isLeadEnabled())
				{
					const presets = this.getPresets().map((preset: Preset) => {
						if (
							Type.isPlainObject(preset.options.options)
							&& Type.isPlainObject(preset.options.options.data)
						)
						{
							if (Type.isArrayFilled(preset.options.options.data.fields))
							{
								preset.options.options.data.fields = preset.options.options.data.fields.map((field) => {
									const preparedField = {...field};
									if (Type.isStringFilled(field.name))
									{
										preparedField.name = field.name.replace(/^LEAD_/, 'CONTACT_');
									}
									return preparedField;
								});
							}

							if (Type.isPlainObject(preset.options.options.document))
							{
								preset.options.options.document.scheme = 3;
							}
						}

						return preset;
					});

					this.setPresets(presets);
				}

				if (Type.isPlainObject(options.formOptions))
				{
					const formOptions = Runtime.merge(
						this.getFormOptions(),
						options.formOptions,
					);
					this.setFormOptions(formOptions);
				}

				if (options.state === 'presets' && formOptions.templateId !== 'callback')
				{
					this.onPresetFieldClick();
					this.activatePreset(formOptions.templateId);
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

					if (this.isFormCreated() && formOptions.templateId !== 'callback')
					{
						this.applyPreset(preset);
					}
					else
					{
						this.applyPreset(preset, true);
					}
				}

				this.setInitialFormOptions(
					Runtime.clone(this.getFormOptions()),
				);
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

		void StylePanel.getInstance().hide();

		return super.show(options);
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
	isFormCreated(): boolean
	{
		const uri = new Uri(window.location.origin);
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

			return formApp.list()[0];
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
						Type.isArrayFilled(formOptions.data.dependencies)
						&& Type.isArrayFilled(value.fields)
					)
					{
						const {dependencies} = formOptions.data;
						formOptions.data.dependencies = dependencies.reduce((acc, item) => {
							const preparedItem = {...item};
							preparedItem.list = preparedItem.list.filter((rule) => {
								return (
									value.fields.some((field) => {
										return field.id === rule.condition.target;
									})
									&& value.fields.some((field) => {
										return field.id === rule.action.target;
									})
								);
							});

							if (preparedItem.list.length > 0)
							{
								acc.push(preparedItem);
							}

							return acc;
						}, []);
					}

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
						const captcha = {key, secret};

						return {
							...formOptions,
							captcha,
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
					.then((result) => {
						if (value.agreements)
						{
							result.data = Runtime.merge(result.data, value);
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

	getContent(id: string): ContentWrapper
	{
		const crmForm = this.getCrmForm();
		if (crmForm)
		{
			crmForm.sent = false;
			crmForm.error = false;
		}

		if (id === 'button_and_header')
		{
			return new HeaderAndButtonContent({
				personalizationVariables: this.getPersonalizationVariables(),
				values: {
					title: FormSettingsPanel.sanitize(this.getFormOptions().data.title),
					desc: FormSettingsPanel.sanitize(this.getFormOptions().data.desc),
					buttonCaption: this.getFormOptions().data.buttonCaption,
				},
			});
		}

		if (id === 'spam_protection')
		{
			return new SpamProtection({
				values: {
					key: this.getFormOptions().captcha.key,
					secret: this.getFormOptions().captcha.secret,
					use: Text.toBoolean(this.getFormOptions().data.recaptcha.use),
				},
			});
		}

		if (id === 'agreements')
		{
			return new AgreementsContent({
				formOptions: this.getFormOptions(),
				agreementsList: this.getAgreements(),
				values: {
					agreements: this.getFormOptions().data.agreements,
				},
			});
		}

		if (id === 'analytics')
		{
			return new AnalyticsContent({
				events: this.getFormOptions().analytics.steps,
				values: {},
			});
		}

		if (id === 'fields')
		{
			return new FieldsContent({
				crmFields: this.getCrmFields(),
				formOptions: this.getFormOptions(),
				dictionary: this.getFormDictionary(),
				isLeadEnabled: this.isLeadEnabled(),
				values: {
					fields: this.getFormOptions().data.fields,
				},
			});
		}

		if (id === 'fields_rules')
		{
			return new FieldsRulesContent({
				fields: this.getFormOptions().data.fields,
				values: this.getFormOptions().data.dependencies,
				dictionary: this.getFormDictionary(),
			});
		}

		if (id === 'actions')
		{
			const actionsContent = new ActionsContent({
				fields: this.getFormOptions().data.fields,
				values: this.getFormOptions().result,
			});

			actionsContent
				.subscribe('onShowSuccess', () => {
					crmForm.stateText = this.getFormOptions().result.success.text;
					crmForm.sent = !crmForm.sent;
					crmForm.error = false;
				})
				.subscribe('onShowFailure', () => {
					crmForm.stateText = this.getFormOptions().result.failure.text;
					crmForm.error = !crmForm.error;
					crmForm.sent = false;
				});

			return actionsContent;
		}

		if (id === 'embed')
		{
			return new EmbedContent({
				fields: this.getFormOptions().data.fields,
				values: this.getFormOptions().embedding,
			});
		}

		if (id === 'identify')
		{
			return new Identify({
				fields: this.getFormOptions().data.fields,
				values: {},
			});
		}

		if (id === 'crm')
		{
			return new CrmContent({
				fields: this.getFormOptions().data.fields,
				companies: this.getCrmCompanies(),
				categories: this.getCrmCategories(),
				isLeadEnabled: this.isLeadEnabled(),
				formDictionary: this.getFormDictionary(),
				values: {
					scheme: this.getFormOptions().document.scheme,
					duplicatesEnabled: this.getFormOptions().document.deal.duplicatesEnabled || 'Y',
					category: this.getFormOptions().document.deal.category,
					dynamicCategory: this.getFormOptions().document.dynamic.category,
					payment: this.getFormOptions().payment.use,
					duplicateMode: this.getFormOptions().document.duplicateMode,
				},
			});
		}

		if (id === 'default_values')
		{
			return new DefaultValues({
				crmFields: this.getCrmFields(),
				formOptions: this.getFormOptions(),
				dictionary: this.getFormDictionary(),
				isLeadEnabled: this.isLeadEnabled(),
				personalizationVariables: this.getDefaultValuesVariables(),
				values: {
					fields: this.getFormOptions().presetFields,
				},
			});
		}

		if (id === 'facebook')
		{
			return new FacebookContent({
				formOptions: this.getFormOptions(),
			});
		}

		if (id === 'vk')
		{
			return new VkContent({
				formOptions: this.getFormOptions(),
			});
		}

		if (id === 'callback')
		{
			return new Callback({
				dictionary: this.getFormDictionary(),
				values: this.getFormOptions().callback,
			});
		}

		if (id === 'other')
		{
			return new Other({
				formOptions: this.getFormOptions(),
				dictionary: this.getFormDictionary(),
				values: {
					name: this.getFormOptions().name,
					useSign: this.getFormOptions().data.useSign,
					users: this.getFormOptions().responsible.users,
					language: this.getFormOptions().data.language,
				},
			});
		}

		return null;
	}

	applyPreset(preset: Preset, skipOptions = false)
	{
		this.getPresets().forEach((currentPreset) => {
			currentPreset.deactivate();
		});

		preset.activate();

		if (Type.isPlainObject(preset.options.options) && !skipOptions)
		{
			this.showLoader();
			const clonedPresetOptions = {
				data: {},
				...Runtime.clone(preset.options.options),
			};

			let agreementsOptions = [];

			if (Reflect.has(clonedPresetOptions.data, 'agreements'))
			{
				const sourceAgreements = this.getAgreements();
				agreementsOptions = [...clonedPresetOptions.data.agreements];

				clonedPresetOptions.data.agreements = (
					clonedPresetOptions.data.agreements.map((item, index) => {
						return sourceAgreements[index].id;
					})
				);
			}

			FormClient.getInstance()
				.prepareOptions(this.getFormOptions(), clonedPresetOptions.data)
				.then((result) => {
					if (Reflect.has(clonedPresetOptions.data, 'fields'))
					{
						delete clonedPresetOptions.data.fields;
					}

					if (Reflect.has(clonedPresetOptions.data, 'agreements'))
					{
						delete clonedPresetOptions.data.agreements;
					}

					const preparedOptions = Runtime.merge({}, result, clonedPresetOptions);

					preparedOptions.data.agreements = (
						preparedOptions.data.agreements.map((agreement, index) => {
							return {
								...agreement,
								...agreementsOptions[index],
							};
						})
					);

					this.setFormOptions(preparedOptions);
					this.getCrmForm().adjust(Runtime.clone(preparedOptions.data));
					super.applyPreset(preset);
					this.hideLoader();
				});
		}
		else
		{
			super.applyPreset(preset);
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
					.replace('{entityName}', this.getCurrentCrmEntityName());

				return Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_DESCRIPTION')
					.replace('{entityName}', entityName);
			})();

			const messageText = (() => {
				const fields = [...notSynchronizedFields].map((field) => {
					return Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_FIELD_TEMPLATE')
						.replace('{fieldName}', field);
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

	onSaveClick()
	{
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

					void FormClient.getInstance()
						.saveOptions(options)
						.then((result) => {
							this.setFormOptions(result);
							FormClient.getInstance().resetCache(result.id);
							Dom.removeClass(this.getSaveButton().layout, 'ui-btn-wait');
							void this.hide();
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
		this.getCrmForm().adjust(this.getInitialFormOptions().data);
		void this.hide();
	}
}