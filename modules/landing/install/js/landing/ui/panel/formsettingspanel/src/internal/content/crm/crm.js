import {Loc} from 'landing.loc';
import {HeaderCard} from 'landing.ui.card.headercard';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {RadioButtonField} from 'landing.ui.field.radiobuttonfield';
import {BaseEvent} from 'main.core.events';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {MessageCard} from 'landing.ui.card.messagecard';
import {Text} from 'main.core';
import OrderField from './internal/orderfield/orderfield';
import StageField from './internal/stagefield/stagefield';

import messageIcon from './images/message-icon.svg';

import './css/style.css';


export default class CrmContent extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.CrmContent');

		this.addItem(this.getHeader());
		this.addItem(this.getTypesField());
		this.addItem(this.getExpertSettingsForm());
		this.addItem(this.getOrderSettingsForm());
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
				value: [this.options.values.duplicateMode ? this.options.values.duplicateMode : 'ALLOW'],
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

	getPaymentField(): BX.Landing.UI.Field.Checkbox
	{
		return this.cache.remember('paymentField', () => {
			return new BX.Landing.UI.Field.Checkbox({
				selector: 'payment',
				value: [this.options.values.payment],
				items: [
					{name: Loc.getMessage('LANDING_FORM_SETTINGS_ORDER_SHOW_PAYMENT'), value: true},
				],
			});
		});
	}

	getOrderSettingsForm(): FormSettingsForm
	{
		return this.cache.remember('formSettingsForm', () => {
			return new FormSettingsForm({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_ORDER_HEADER'),
				toggleable: true,
				opened: Text.toNumber(this.options.values.scheme) > 4,
				fields: [
					this.getPaymentField(),
					new MessageCard({
						id: 'orderMessage',
						header: Loc.getMessage('LANDING_FORM_SETTINGS_ORDER_MESSAGE_HEADER'),
						description: Loc.getMessage('LANDING_FORM_SETTINGS_ORDER_MESSAGE_DESCRIPTION'),
						angle: false,
						icon: messageIcon,
						restoreState: true,
					}),
				],
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
								id: this.options.values.scheme,
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
			];

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
					if (String(this.options.values.scheme) === '8')
					{
						return 1;
					}

					if (String(this.options.values.scheme) === '5')
					{
						return 2;
					}

					if (String(this.options.values.scheme) === '6')
					{
						return 3;
					}

					if (String(this.options.values.scheme) === '7')
					{
						return 4;
					}

					return this.options.values.scheme;
				})(),
				items,
				onChange: this.onTypeChange.bind(this),
			});
		});
	}
	
	getStagesField()
	{
		return this.cache.remember('stagesField', () => {
			return new StageField({
				categories: this.options.categories,
				value: {
					category: this.options.values.category,
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
				value: [this.options.values.duplicatesEnabled],
				items: [
					{
						name: Loc.getMessage('LANDING_FORM_SETTINGS_CRM_DUPLICATES_ENABLED'),
						value: true,
					},
				],
			});
		});
	}

	onTypeChange(event: BaseEvent)
	{
		const {item} = event.getData();

		this.clear();

		this.addItem(this.getHeader());
		this.addItem(this.getTypesField());

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
			expertSettingsForm.addField(this.getStagesField());
			expertSettingsForm.addField(this.getDuplicatesEnabledField());
			expertSettingsForm.addField(this.getDuplicatesField());
		}

		if (String(item.id) === '4' || String(item.id) === '7')
		{
			expertSettingsForm.addField(this.getType4Header());
			expertSettingsForm.addField(this.getDuplicatesField());
		}

		if (Text.toNumber(item.id) > 4 || this.getOrderSettingsForm().isOpened())
		{
			this.getOrderSettingsForm().onSwitchChange(true);
		}

		this.addItem(expertSettingsForm);
		this.addItem(this.getOrderSettingsForm());
	}

	onChange(event: BaseEvent)
	{
		this.emit('onChange', {...event.getData(), skipPrepare: true});
	}

	valueReducer(value: {[p: string]: any}): {[p: string]: any}
	{
		const duplicateMode = this.getDuplicatesField().getValue()[0];
		const reducedValue = {
			duplicateMode: duplicateMode === 'ALLOW' ? '' : duplicateMode,
			scheme: this.getTypesField().getValue(),
			deal: {
				duplicatesEnabled: Text.toBoolean(this.getDuplicatesEnabledField().getValue()[0]),
			},
			payment: {
				use: this.getPaymentField().getValue().length > 0,
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
			reducedValue.deal.category = this.getStagesField().getValue().category;
		}

		return {
			document: reducedValue,
		};
	}
}