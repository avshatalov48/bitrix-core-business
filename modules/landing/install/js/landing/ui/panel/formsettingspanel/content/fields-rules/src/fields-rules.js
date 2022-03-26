import {Loc, Type, Dom} from 'main.core';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {HeaderCard} from 'landing.ui.card.headercard';
import {ActionPanel} from 'landing.ui.component.actionpanel';
import {RadioButtonField} from 'landing.ui.field.radiobuttonfield';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {BaseEvent} from 'main.core.events';
import RuleType from './rule-type';
import RuleGroup from './internal/rule-group/rule-group';

import './css/style.css';

export default class FieldsRules extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.FieldsRulesContent');

		this.addItem(this.getHeader());

		if (!Type.isArrayFilled(this.options.formOptions.data.dependencies))
		{
			this.addItem(this.getRuleTypeField());
		}
		else
		{
			this.addItem(this.getRulesForm());
			this.addItem(this.getActionPanel());
		}
	}

	getHeader(): HeaderCard
	{
		return this.cache.remember('headerCard', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FIELDS_RULES_TITLE'),
			});
		});
	}

	getRulesForm(): FormSettingsForm
	{
		return this.cache.remember('rulesForm', () => {
			return new FormSettingsForm({
				selector: 'dependencies',
				description: null,
				fields: this.options.formOptions.data.dependencies.map((groupData) => {
					return new RuleGroup({
						dictionary: this.options.dictionary,
						fields: this.getFormFields(),
						data: groupData,
						onRemove: this.onRuleGroupRemove.bind(this),
					});
				}),
			});
		});
	}

	getActionPanel(): ActionPanel
	{
		return this.cache.remember('actionPanel', () => {
			return new ActionPanel({
				left: [
					{
						text: Loc.getMessage('LANDING_FIELDS_ADD_NEW_RULE_LINK_LABEL'),
						onClick: this.onAddRuleClick.bind(this),
					},
				],
			});
		});
	}

	onAddRuleClick()
	{
		this.insertBefore(this.getRuleTypeField(), this.getActionPanel());
		this.items.remove(this.getActionPanel());
		Dom.remove(this.getActionPanel().getLayout());
		this.getActionPanel().unsubscribe('onChange', this.onChange);
	}

	getRuleTypeField(): RadioButtonField
	{
		return this.cache.remember('ruleTypeField', () => {
			return new RadioButtonField({
				selector: 'rules-type',
				items: Object.entries(RuleType).map(([, value]) => {
					return {
						id: `ruleType${value}`,
						icon: `landing-ui-rules-type${value + 1}-icon`,
						title: Loc.getMessage(`LANDING_FIELDS_RULES_TYPE_${value + 1}`),
						button: {
							text: Loc.getMessage('LANDING_FIELDS_RULES_TYPE_BUTTON'),
							onClick: this.onCreateRuleButtonClick.bind(this, {type: value}),
						},
					};
				}),
			});
		});
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

		return this.options.formOptions.data.fields.filter((field) => {
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

	onCreateRuleButtonClick({type})
	{
		this.clear();

		const header = this.getHeader();
		header.setBottomMargin(false);
		this.addItem(header);

		const ruleForm = this.getRulesForm();

		ruleForm.addField(
			new RuleGroup({
				dictionary: this.options.dictionary,
				fields: this.getFormFields(),
				data: {
					id: 0,
					typeId: type,
					list: [],
					logic: type === RuleType.TYPE_2 ? 'and' : 'or',
				},
				onRemove: this.onRuleGroupRemove.bind(this),
			}),
		);

		this.addItem(ruleForm);
		this.addItem(this.getActionPanel());
	}

	onRuleGroupRemove(event: BaseEvent)
	{
		this.onChange(event);

		this.getRulesForm().removeField(event.getTarget());
		event.getTarget().unsubscribe('onChange', this.onChange);
	}

	onChange(event: BaseEvent)
	{
		this.emit('onChange', {...event.getData(), skipPrepare: true});
	}

	valueReducer(value: {[p: string]: any}): {[p: string]: any}
	{
		return {
			dependencies: Object.values(value).filter((group) => {
				return Type.isArrayFilled(group.list);
			}),
		};
	}
}
