import {Loc} from 'landing.loc';
import {Button, ButtonColor} from 'ui.buttons';
import {HeaderCard} from 'landing.ui.card.headercard';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {RadioButtonField} from 'landing.ui.field.radiobuttonfield';
import {BaseEvent} from 'main.core.events';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {Text, Type} from 'main.core';
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';
import StageField from './internal/stagefield/stagefield';
import { SchemeManager } from 'landing.ui.panel.formsettingspanel.content.crm.schememanager';

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

export default class CrmContent extends ContentWrapper
{
	#schemeManager: SchemeManager;

	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.CrmContent');

		this.#schemeManager = new SchemeManager([ ...options.dictionary.document.schemes ]);

		this.addItem(this.getHeader());
		this.addItem(this.getTypesField());
		if (this.isDynamicAvailable())
		{
			this.addItem(this.getDynamicEntitySettingsForm());
		}

		this.addItem(this.getExpertSettingsForm());
		this.addItem(this.getOrderSettingsForm());

		this.setLastScheme(this.options.formOptions.document.scheme);
		this.setLastDealCategory(this.options.formOptions.document.deal.category);
	}

	isDynamicAvailable(): boolean
	{
		return Type.isArrayFilled(this.options.dictionary.document.dynamic);
	}

	getHeader(): HeaderCard
	{
		return this.cache.remember('header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TITLE'),
			});
		});
	}

	getDuplicatesField(): BX.Landing.UI.Field.Radio
	{
		return this.cache.remember('duplicatesField', () => {
			return new BX.Landing.UI.Field.Radio({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_DUPLICATES_FIELD_TITLE'),
				selector: 'duplicateMode',
				value: [this.options.formOptions.document.duplicateMode ? this.options.formOptions.document.duplicateMode : 'ALLOW'],
				items: [
					{
						name: Loc.getMessage('LANDING_FORM_SETTINGS_DUPLICATES_ALLOW'),
						value: 'ALLOW',
					},
					{
						name: Loc.getMessage('LANDING_FORM_SETTINGS_DUPLICATES_REPLACE'),
						value: 'REPLACE',
					},
					{
						name: Loc.getMessage('LANDING_FORM_SETTINGS_DUPLICATES_MERGE'),
						value: 'MERGE',
					},
				],
			});
		});
	}

	getOrderSettingsForm(): FormSettingsForm
	{
		return this.cache.remember('formSettingsForm', () => {
			const scheme = this.getSchemeById(this.options.formOptions.document.scheme);
			const isOpened = (() => {
				if (scheme && scheme.dynamic === true)
				{
					return String(scheme.id).endsWith('1');
				}

				return Text.toNumber(this.options.formOptions.document.scheme) > 4;
			})();

			return new FormSettingsForm({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_ORDER_HEADER'),
				toggleable: true,
				opened: isOpened,
				fields: [],
			});
		});
	}

	getType1Header(): HeaderCard
	{
		return this.cache.remember('type1header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_1').replace('&nbsp;', ' '),
				level: 2,
			});
		});
	}

	getType2Header(): HeaderCard
	{
		return this.cache.remember('type2header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_2').replace('&nbsp;', ' '),
				level: 2,
			});
		});
	}

	getType3Header(): HeaderCard
	{
		return this.cache.remember('type3header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_3').replace('&nbsp;', ' '),
				level: 2,
			});
		});
	}

	getType4Header(): HeaderCard
	{
		return this.cache.remember('type4header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_4').replace('&nbsp;', ' '),
				level: 2,
			});
		});
	}

	getType6Header(): HeaderCard
	{
		return this.cache.remember('type6header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_6').replace('&nbsp;', ' '),
				level: 2,
			});
		});
	}

	getDynamicHeader(headerText: string): HeaderCard
	{
		const header = this.cache.remember('dynamicHeader', () => {
			return new HeaderCard({
				title: '',
				level: 2,
			});
		});

		if (Type.isString(headerText))
		{
			header.setTitle(headerText);
		}

		return header;
	}

	getDynamicEntitiesField(): BX.Landing.UI.Field.Dropdown
	{
		return this.cache.remember('dynamicEntitiesField', () => {
			const currentScheme = this.getSchemeById(this.options.formOptions.document.scheme);

			return new BX.Landing.UI.Field.Dropdown({
				selector: 'dynamicScheme',
				title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_SMART_ENTITY_LIST'),
				items: this.options.dictionary.document.dynamic.map((scheme) => {
					return {name: scheme.name, value: scheme.id};
				}),
				content: currentScheme.mainEntity,
				onChange: () => {
					this.onTypeChange(
						new BaseEvent({
							data: {
								item: {
									id: this.getSelectedSchemeId(),
								},
							},
						}),
					);
				},
			});
		});
	}

	getDynamicEntitySettingsForm(): FormSettingsForm
	{
		return this.cache.remember('dynamicEntitySettingsForm', () => {
			return new FormSettingsForm({
				opened: true,
				hidden: true,
				fields: [
					this.getDynamicEntitiesField(),
				],
			});
		});
	}

	getExpertSettingsForm(): FormSettingsForm
	{
		return this.cache.remember('expertSettingsForm', () => {
			return new FormSettingsForm({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_EXPERT_MODE'),
				toggleable: true,
				toggleableType: FormSettingsForm.ToggleableType.Link,
				opened: false,
				fields: [
					new HeaderCard({
						title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_1').replace('&nbsp;', ' '),
						level: 2,
					}),
					this.getDuplicatesField(),
				],
			});
		});
	}

	getTypesField(): RadioButtonField
	{
		return this.cache.remember('typesField', () => {
			setTimeout(() => {
				this.onTypeChange(
					new BaseEvent({
						data: {
							item: {
								id: this.options.formOptions.document.scheme,
							},
						},
					}),
				);
			});

			const items = [
				{
					id: '2',
					title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_2'),
					icon: 'landing-ui-crm-entity-type2',
				},
				{
					id: '3',
					title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_3'),
					icon: 'landing-ui-crm-entity-type3',
				},
				{
					id: '4',
					title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_4'),
					icon: 'landing-ui-crm-entity-type4',
				},
				{
					id: '310',
					title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_310'),
					icon: 'landing-ui-crm-entity-type310',
				},
			];

			if (this.isDynamicAvailable())
			{
				items.push({
					id: 'smart',
					title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_5'),
					icon: 'landing-ui-crm-entity-type5',
				});
			}

			if (this.options.isLeadEnabled)
			{
				items.unshift({
					id: '1',
					title: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_1'),
					icon: 'landing-ui-crm-entity-type1',
				});
			}

			return new RadioButtonField({
				selector: 'scheme',
				value: (() => {
					const schemeId = Text.toNumber(this.options.formOptions.document.scheme);
					if (this.#schemeManager.isDefaultScheme(schemeId) && this.#schemeManager.isInvoice(schemeId))
					{
						return this.#schemeManager.getSpecularSchemeId(schemeId);
					}

					if (String(this.options.formOptions.document.scheme) === '310')
					{
						return 310;
					}

					const scheme = this.getSchemeById(this.options.formOptions.document.scheme);
					if (Type.isPlainObject(scheme) && scheme.dynamic === true)
					{
						return 'smart';
					}

					return String(schemeId);
				})(),
				items,
				onChange: this.onTypeChange.bind(this),
			});
		});
	}

	getDealCategoryField()
	{
		return this.cache.remember('dealCategoryField', () => {
			return new StageField({
				categories: this.options.categories,
				value: {
					category: this.options.formOptions.document.deal.category,
				},
			});
		});
	}

	getDynamicCategoriesField(schemeId: string | number)
	{
		return this.cache.remember(`dynamicCategories#${schemeId}`, () => {
			const scheme = this.getDynamicSchemeById(schemeId);
			return new StageField({
				listTitle: Loc.getMessage('LANDING_FORM_SETTINGS_SMART_STAGES_FIELD_TITLE'),
				categories: scheme.categories,
				value: {
					category: this.options.formOptions.document.dynamic.category,
				},
			});
		});
	}

	getDuplicatesEnabledField()
	{
		return this.cache.remember('duplicatesEnabledField', () => {
			return new BX.Landing.UI.Field.Checkbox({
				selector: 'duplicatesEnabled',
				compact: true,
				value: [this.options.formOptions.document.deal.duplicatesEnabled || 'Y'],
				items: [
					{
						name: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_DUPLICATES_ENABLED'),
						value: true,
					},
				],
			});
		});
	}

	getSchemeById(id: number)
	{
		return this.options.dictionary.document.schemes.find((scheme) => {
			return (
				(String(scheme.id) === String(id))
				|| (
					id === 'smart'
					&& scheme.dynamic
					&& String(scheme.id) === String(this.getSelectedSchemeId())
				)
			);
		});
	}

	getDynamicSchemeById(id: number)
	{
		const {mainEntity} = this.getSchemeById(id);
		return this.options.dictionary.document.dynamic.find((scheme) => {
			return String(scheme.id) === String(mainEntity);
		});
	}

	setLastScheme(schemeId: number)
	{
		this.cache.set('lastScheme', schemeId);
	}

	getLastScheme(): number
	{
		return this.cache.get('lastScheme');
	}

	setLastDealCategory(categoryId: number)
	{
		this.cache.set('lastDealCategory', categoryId);
	}

	getLastDealCategory(): ?number
	{
		return this.cache.get('lastDealCategory', null);
	}

	onTypeChange(event: BaseEvent)
	{
		const {item} = event.getData();
		const scheme = this.getSchemeById(item.id);

		this.clear();

		this.addItem(this.getHeader());
		this.addItem(this.getTypesField());
		if (this.isDynamicAvailable())
		{
			this.addItem(this.getDynamicEntitySettingsForm());
			this.getDynamicEntitySettingsForm().hide();
		}

		const expertSettingsForm = this.getExpertSettingsForm();
		expertSettingsForm.clear();

		if (String(item.id) === '1' || String(item.id) === '8')
		{
			expertSettingsForm.addField(this.getType1Header());
			expertSettingsForm.addField(this.getDuplicatesField());
		}

		if (String(item.id) === '2' || String(item.id) === '5')
		{
			expertSettingsForm.addField(this.getType2Header());
			expertSettingsForm.addField(this.getDuplicatesField());
		}

		if (String(item.id) === '3' || String(item.id) === '6')
		{
			expertSettingsForm.addField(this.getType3Header());
			expertSettingsForm.addField(this.getDealCategoryField());
			expertSettingsForm.addField(this.getDuplicatesEnabledField());
			expertSettingsForm.addField(this.getDuplicatesField());
		}

		if (String(item.id) === '4' || String(item.id) === '7')
		{
			expertSettingsForm.addField(this.getType4Header());
			expertSettingsForm.addField(this.getDuplicatesField());
		}
		if (String(item.id) === '310')
		{
			expertSettingsForm.addField(this.getType6Header());
			expertSettingsForm.addField(this.getDuplicatesField());
		}

		if (
			(
				Text.toNumber(item.id) > 4
				&& Type.isPlainObject(scheme)
				&& scheme.dynamic !== true && String(item.id)  !== '9'
			)
			|| this.getOrderSettingsForm().isOpened()
		)
		{
			this.getOrderSettingsForm().onSwitchChange(true);
		}

		if (
			Type.isPlainObject(scheme)
			&& (String(item.id) === 'smart' || scheme.dynamic === true)
			&& this.isDynamicAvailable()
		)
		{
			expertSettingsForm.addField(this.getDynamicHeader(scheme.name));
			const dynamicScheme = this.getDynamicSchemeById(scheme.id);
			if (dynamicScheme && dynamicScheme.categories)
			{
				expertSettingsForm.addField(this.getDynamicCategoriesField(scheme.id));
			}
			expertSettingsForm.addField(this.getDuplicatesField());

			if (String(scheme.id).endsWith('1'))
			{
				this.getOrderSettingsForm().onSwitchChange(true);
			}

			this.getDynamicEntitySettingsForm().show();
		}

		this.addItem(expertSettingsForm);
		if (String(item.id) !== '310')
		{
			this.addItem(this.getOrderSettingsForm());
		}
	}

	setAdditionalValue(value: {[key: string]: any})
	{
		this.cache.set('additionalValue', value);
	}

	getAdditionalValue(): {[key: string]: any}
	{
		return this.cache.get('additionalValue', {});
	}

	getEntityChangeConfirm(): MessageBox
	{
		return this.cache.remember('entityChangeConfirm', () => {
			return new MessageBox({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_SCHEME_CHANGE_CONFIRM_TITLE'),
				buttons: MessageBoxButtons.OK_CANCEL,
			});
		});
	}

	getDealCategoryChangeConfirm(): MessageBox
	{
		return this.cache.remember('dealCategoryChangeConfirm', () => {
			return new MessageBox({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_SCHEME_CHANGE_CONFIRM_TITLE'),
				buttons: MessageBoxButtons.OK_CANCEL,
			});
		});
	}

	getCreateOrderChangeConfirm(): MessageBox
	{
		return this.cache.remember('createOrderChangeConfirm', () => {
			return new MessageBox({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_SCHEME_CREATE_ORDER_CHANGE_CONFIRM_TITLE'),
				buttons: MessageBoxButtons.OK_CANCEL,
				message: Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_CREATE_ORDER_MESSAGE_BOX_TITLE_1')
			});
		});
	}

	onChange(event: BaseEvent)
	{
		const value = this.getValue();
		const scheme = this.getSchemeById(value.document.scheme);

		if (Type.isPlainObject(scheme))
		{
			const allowedEntities = scheme.entities;
			const removedFields = this.options.formOptions.presetFields
				.filter((presetField) => {
					return !allowedEntities.includes(presetField.entityName);
				})
				.map((presetField) => {
					return this.getCrmFieldById(
						`${presetField.entityName}_${presetField.fieldName}`,
					);
				});

			if (Type.isArrayFilled(removedFields))
			{
				const itemTemplate = Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_ITEM_TEMPLATE');
				const entityName = Text.encode(itemTemplate.replace('{text}', scheme.name));

				const messageText = (() => {
					const fields = removedFields.map((field) => {
						return itemTemplate.replace('{text}', Text.encode(field.caption));
					});

					if (removedFields.length > 1)
					{
						const lastField = fields.pop();

						return Loc
							.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_SCHEME_CHANGE_CONFIRM_TEXT')
							.replace('{fieldsList}', fields.join(', '))
							.replace('{lastField}', Text.encode(lastField))
							.replaceAll('{entityName}', entityName);
					}

					return Loc
						.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_SCHEME_CHANGE_CONFIRM_TEXT_1')
						.replace('{fieldName}', fields.join(', '))
						.replaceAll('{entityName}', entityName);
				})();

				const entityChangeConfirm = this.getEntityChangeConfirm();

				entityChangeConfirm.setOkCallback(
					() => {
						entityChangeConfirm.close();
						entityChangeConfirm.getOkButton().setDisabled(false);
						entityChangeConfirm.getCancelButton().setDisabled(false);

						const filteredFields = this.options.formOptions.presetFields.filter((presetField) => {
							return allowedEntities.includes(presetField.entityName);
						});

						this.setLastScheme(scheme.id);
						this.setAdditionalValue({
							presetFields: filteredFields,
						});

						this.options.formOptions.presetFields = filteredFields;

						this.emit('onChange', {...event.getData(), skipPrepare: true});

						this.setAdditionalValue({});
					},
				);

				entityChangeConfirm.setCancelCallback(
					() => {
						entityChangeConfirm.close();
						entityChangeConfirm.getOkButton().setDisabled(false);
						entityChangeConfirm.getCancelButton().setDisabled(false);

						const lastScheme = this.getSchemeById(this.getLastScheme());

						if (lastScheme.dynamic)
						{
							this.getTypesField().setValue('smart', true);
							this.getDynamicEntitiesField().setValue(lastScheme.mainEntity, true);
						}
						else
						{
							this.getTypesField().setValue(lastScheme.id);
						}

						this.onTypeChange(
							new BaseEvent({
								data: {
									item: {
										id: lastScheme.id,
									},
								},
							}),
						);
					},
				);

				entityChangeConfirm.setMessage(messageText);

				entityChangeConfirm.show();

				return;
			}

			if (
				String(scheme.id) === '3'
				|| String(scheme.id) === '6'
			)
			{
				const lastDealCategory = this.getLastDealCategory();
				if (Text.toNumber(value.document.deal.category) !== Text.toNumber(lastDealCategory))
				{
					const dealStageField = this.options.formOptions.presetFields.find((presetField) => {
						return (
							presetField.entityName === 'DEAL'
							&& presetField.fieldName === 'STAGE_ID'
						);
					});

					if (dealStageField)
					{
						const crmField = this.getCrmFieldById('DEAL_STAGE_ID');
						const dealCategoryChangeConfirm = this.getDealCategoryChangeConfirm();

						const fieldName = Loc
							.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_ITEM_TEMPLATE')
							.replace('{text}', Text.encode(crmField.caption));
						const messageText = Loc
							.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_CATEGORY_CHANGE_CONFIRM_TEXT')
							.replace('{fieldName}', fieldName);

						dealCategoryChangeConfirm.setMessage(messageText);
						dealCategoryChangeConfirm.setOkCallback(
							() => {
								dealCategoryChangeConfirm.close();
								dealCategoryChangeConfirm.getOkButton().setDisabled(false);
								dealCategoryChangeConfirm.getCancelButton().setDisabled(false);

								const filteredFields = (
									this.options.formOptions.presetFields.filter((presetField) => {
										return presetField !== dealStageField;
									})
								);

								this.options.formOptions.presetFields = filteredFields;

								this.setLastDealCategory(value.document.deal.category);
								this.setAdditionalValue({
									presetFields: filteredFields,
								});

								this.emit('onChange', {...event.getData(), skipPrepare: true});
								this.setAdditionalValue({});
							},
						);
						dealCategoryChangeConfirm.setCancelCallback(
							() => {
								dealCategoryChangeConfirm.close();
								dealCategoryChangeConfirm.getOkButton().setDisabled(false);
								dealCategoryChangeConfirm.getCancelButton().setDisabled(false);

								this.getDealCategoryField().setValue({
									category: this.getLastDealCategory(),
								});
								this.setAdditionalValue({});
							},
						);

						dealCategoryChangeConfirm.show();

						return;
					}
				}
			}
		}
		if (!this.#schemeManager.isInvoice(scheme.id) && value.document.payment.use)
		{
			const createOrderMessageBox = this.getCreateOrderChangeConfirm();
			createOrderMessageBox.setButtons(
				[
					(new Button())
						.setColor(ButtonColor.PRIMARY)
						.setText(Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_CREATE_ORDER_MESSAGE_BOX_CANCEL'))
						.setNoCaps(true)
						.bindEvent('click', (button) => {
							createOrderMessageBox.close();
							button.setDisabled(false)

							const orderSettingsSwitch = this.getOrderSettingsForm().getSwitch();
							orderSettingsSwitch.setValue(true);
							orderSettingsSwitch.onChange();

							this.onChange(event);
						}),
					(new Button())
						.setColor(ButtonColor.LIGHT)
						.setText(Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_CREATE_ORDER_MESSAGE_BOX_OK'))
						.setNoCaps(true)
						.bindEvent('click', (button) => {
							createOrderMessageBox.close();
							button.setDisabled(false);

							this.options.formOptions.payment.use = false;

							this.onChange(event);
						}),
				],
			);
			createOrderMessageBox.show();
		}

		this.emit('onChange', {...event.getData(), skipPrepare: true});
	}

	getCrmFieldById(id: string): ?CrmField
	{
		return Object.values(this.options.crmFields)
			.reduce((acc, category) => {
				return [...acc, ...category.FIELDS];
			}, [])
			.find((currentField) => {
				return currentField.name === id;
			});
	}

	getSelectedSchemeId(): number
	{
		const typeId = this.getTypesField().getValue();
		if (String(typeId) === 'smart')
		{
			const entityId = this.getDynamicEntitiesField().getValue();
			if (this.getOrderSettingsForm().isOpened())
			{
				return `${entityId}1`;
			}

			return `${entityId}0`;
		}

		return typeId;
	}

	valueReducer(value: {[p: string]: any}): {[p: string]: any}
	{
		const duplicateMode = this.getDuplicatesField().getValue()[0];
		const reducedValue = {
			duplicateMode: duplicateMode === 'ALLOW' ? '' : duplicateMode,
			scheme: this.getSelectedSchemeId(),
			deal: {
				duplicatesEnabled: Text.toBoolean(this.getDuplicatesEnabledField().getValue()[0]),
			},
			payment: {
				use: this.options.formOptions.payment.use,
				payer: this.options.formOptions.payment.payer,
				disabledSystems: this.options.formOptions.payment.disabledSystems,
			},
			dynamic: {
				category: null,
			},
		};

		if (this.getOrderSettingsForm().isOpened())
		{
			if (String(reducedValue.scheme) === '1')
			{
				reducedValue.scheme = '8';
			}

			if (String(reducedValue.scheme) === '2')
			{
				reducedValue.scheme = '5';
			}

			if (String(reducedValue.scheme) === '3')
			{
				reducedValue.scheme = '6';
			}

			if (String(reducedValue.scheme) === '4')
			{
				reducedValue.scheme = '7';
			}
		}

		if (
			String(reducedValue.scheme) === '3'
			|| String(reducedValue.scheme) === '6'
		)
		{
			reducedValue.deal.category = this.getDealCategoryField().getValue().category;
		}

		const scheme = this.getSchemeById(reducedValue.scheme);
		const dynamicScheme = this.getDynamicSchemeById(reducedValue.scheme);
		if (Type.isPlainObject(scheme) && scheme.dynamic && dynamicScheme && dynamicScheme.categories)
		{
			reducedValue.dynamic.category = this.getDynamicCategoriesField(scheme.id).getValue().category;
		}

		return {
			document: reducedValue,
			...this.getAdditionalValue(),
		};
	}
}