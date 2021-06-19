import {HeaderCard} from 'landing.ui.card.headercard';
import {Loc, Type} from 'main.core';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {RuleField} from 'landing.ui.field.rulefield';
import {ActionPanel} from 'landing.ui.component.actionpanel';
import {RadioButtonField} from 'landing.ui.field.radiobuttonfield';

import './css/style.css';
import {BaseEvent} from 'main.core.events';

export default class FieldsRulesContent extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.FieldsRulesContent');

		this.addItem(this.getHeader());

		if (!Object.keys(this.options.values).length > 0)
		{
			this.addItem(this.getRuleTypeField());
		}
		else
		{
			this.addItem(this.getRulesForm());
			this.addItem(this.getActionPanel());

			const values = this.options.values.reduce((acc, group) => {
				group.list.forEach((item) => {
					const accEntry = acc.find((accItem) => {
						return (
							(
								accItem.condition.field === item.condition.target
								|| accItem.condition.field.id === item.condition.target
							)
							&& accItem.condition.value === item.condition.value
							&& accItem.condition.operator === item.condition.operation
						);
					});

					if (accEntry)
					{
						accEntry.expression.push({
							field: this.options.fields.find((field) => {
								return field.id === item.action.target;
							}),
							action: item.action.type,
						});
					}
					else
					{
						acc.push({
							condition: {
								field: this.options.fields.find((field) => {
									return field.name === item.condition.target;
								}),
								value: item.condition.value,
								operator: item.condition.operation,
							},
							expression: [
								{
									field: this.options.fields.find((field) => {
										return field.name === item.action.target;
									}),
									action: item.action.type,
								},
							],
						});
					}
				});

				return acc;
			}, []);

			this.getRulesForm().addField(
				new RuleField({
					fields: this.getFormFields(),
					rules: values,
					onRemove: this.onFieldRemove.bind(this),
					dictionary: this.options.dictionary,
				}),
			);
		}
	}

	getFormFields()
	{
		const disallowedTypes = (() => {
			if (
				!Type.isPlainObject(this.options.dictionary.deps.field)
				|| !Type.isArrayFilled(this.options.dictionary.deps.field.disallowed)
			)
			{
				return null;
			}

			return this.options.dictionary.deps.field.disallowed;
		})();

		return this.options.fields.filter((field) => {
			return (
				!Type.isArrayFilled(disallowedTypes)
				|| (
					!disallowedTypes.includes(field.type)
					&& (
						!Type.isPlainObject(field.content)
						|| disallowedTypes.includes(field.content.type)
					)
				)
			);
		});
	}

	onFieldRemove(event: BaseEvent)
	{
		this.getRulesForm().removeField(event.getTarget());
		this.clear();

		const header = this.getHeader();
		header.setBottomMargin(true);
		this.addItem(header);
		this.addItem(this.getRuleTypeField());
	}

	getRulesForm(): FormSettingsForm
	{
		return this.cache.remember('rulesForm', () => {
			return new FormSettingsForm({
				selector: 'dependencies',
				description: null,
			});
		});
	}

	getActionPanel(): ActionPanel
	{
		return this.cache.remember('actionPanel', () => {
			return new ActionPanel({
				left: [
					// {
					// 	text: Loc.getMessage('LANDING_FIELDS_ADD_NEW_RULE_LINK_LABEL'),
					// 	onClick: this.onAddRuleClick.bind(this),
					// },
				],
			});
		});
	}

	getHeader(): HeaderCard
	{
		return this.cache.remember('headerCard', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FIELDS_RULES_TITLE'),
			});
		});
	}

	onCreateRule()
	{
		this.clear();

		const header = this.getHeader();
		header.setBottomMargin(false);
		this.addItem(header);

		const ruleForm = this.getRulesForm();

		ruleForm.addField(
			new RuleField({
				fields: this.getFormFields(),
				rules: [],
				onRemove: this.onFieldRemove.bind(this),
				dictionary: this.options.dictionary,
			}),
		);

		this.addItem(ruleForm);
		this.addItem(this.getActionPanel());
	}

	getRuleTypeField(): RadioButtonField
	{
		return this.cache.remember('ruleTypeField', () => {
			return new RadioButtonField({
				selector: 'rules-type',
				items: [
					{
						id: 'type1',
						icon: 'landing-ui-rules-type1-icon',
						title: Loc.getMessage('LANDING_FIELDS_RULES_TYPE_1'),
						button: {
							text: Loc.getMessage('LANDING_FIELDS_RULES_TYPE_BUTTON'),
							onClick: this.onCreateRule.bind(this),
						},
					},
					{
						id: 'type2',
						icon: 'landing-ui-rules-type2-icon',
						title: Loc.getMessage('LANDING_FIELDS_RULES_TYPE_2'),
						button: {
							text: Loc.getMessage('LANDING_FIELDS_RULES_TYPE_BUTTON'),
							onClick: this.onCreateRule.bind(this),
						},
						disabled: true,
						soon: true,
					},
					{
						id: 'type3',
						icon: 'landing-ui-rules-type3-icon',
						title: Loc.getMessage('LANDING_FIELDS_RULES_TYPE_3'),
						button: {
							text: Loc.getMessage('LANDING_FIELDS_RULES_TYPE_BUTTON'),
							onClick: this.onCreateRule.bind(this),
						},
						disabled: true,
						soon: true,
					},
				],
			});
		});
	}

	onAddRuleClick()
	{
		const radioField = this.getRuleTypeField();
		this.insertBefore(radioField, this.items[this.items.length - 1]);
	}

	onChange(event: BaseEvent)
	{
		this.emit('onChange', {...event.getData(), skipPrepare: true});
	}

	valueReducer(value: {[p: string]: any}): {[p: string]: any}
	{
		return {
			dependencies: Object.values(value).map((item) => {
				return {
					typeId: 0,
					list: item.reduce((acc, listItems) => {
						listItems.forEach((listItem) => {
							listItem.expression.forEach((expItem) => {
								acc.push({
									condition: {
										target: listItem.condition.field,
										event: 'change',
										value: listItem.condition.value,
										operation: listItem.condition.operator,
									},
									action: {
										target: expItem.field,
										type: expItem.action,
										value: '',
									},
								});
							});
						});

						return acc;
					}, []),
				};
			}),
		};
	}
}