import {Dom, Tag} from 'main.core';
import {BaseField} from 'landing.ui.field.basefield';
import {Loc} from 'landing.loc';
import {IconButton} from 'landing.ui.component.iconbutton';
import {ActionPanel} from 'landing.ui.component.actionpanel';
import 'main.popup';
import type {FormField, RuleFieldOptions} from './types/rule-field-options';
import {FieldRules} from './internal/field-rules/field-rules';

import './css/style.css';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';

/**
 * @memberOf BX.Landing.UI.Field
 */
export class RuleField extends BaseField
{
	options: RuleFieldOptions;

	constructor(options: RuleFieldOptions)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.RuleField');
		this.subscribeFromOptions(fetchEventsFromOptions(options));
		this.setLayoutClass('landing-ui-field-rule');

		this.onRemoveClick = this.onRemoveClick.bind(this);
		this.onRowChange = this.onRowChange.bind(this);

		Dom.clean(this.layout);
		Dom.append(this.getHeaderLayout(), this.getLayout());
		Dom.append(this.getBodyLayout(), this.getLayout());
		Dom.append(this.getFooterLayout(), this.getLayout());

		this.rows = [];

		this.options.rules.forEach((rule) => {
			this.addRule(rule);
		});

		const hideLabel = this.options.rules.some((rule) => {
			return rule.expression.length > 0;
		});

		if (hideLabel)
		{
			const label = this.rows[0].getFieldContainer()
				.querySelector('.landing-ui-field-rule-field-row-field-container-action-title');
			Dom.hide(label);
		}
	}

	addRule(fieldRules: FieldRules)
	{
		const row = new FieldRules({
			...fieldRules,
			fields: this.options.fields,
			dictionary: this.options.dictionary,
		});
		this.rows.push(row);

		row.subscribe('onChange', this.onRowChange);
		Dom.append(row.getLayout(), this.getBodyLayout());
		this.emit('onChange');
	}

	onRowChange(event)
	{
		this.emit('onChange');

		const hideLabel = event.getTarget().getValue().some((rule) => {
			return rule.expression.length > 0;
		});

		const label = this.rows[0].getFieldContainer()
			.querySelector('.landing-ui-field-rule-field-row-field-container-action-title');

		if (hideLabel)
		{
			Dom.hide(label);
		}
		else
		{
			Dom.show(label);
		}
	}

	getHeaderTitleLayout(): HTMLDivElement
	{
		return this.cache.remember('headerTitleLayout', () => {
			return Tag.render`
				<div class="landing-ui-field-rule-header-title">${Loc.getMessage('LANDING_FIELDS_RULES_TYPE_1')}</div>
			`;
		});
	}

	getRemoveButtonLayout(): HTMLDivElement
	{
		return this.cache.remember('removeButtonLayout', () => {
			const button = new IconButton({
				type: IconButton.Types.remove,
				onClick: this.onRemoveClick,
				title: Loc.getMessage('LANDING_RULE_FIELD_REMOVE_BUTTON_TITLE'),
				style: {
					marginLeft: 'auto',
				},
			});

			return button.getLayout();
		});
	}

	getHeaderLayout(): HTMLDivElement
	{
		return this.cache.remember('headerLayout', () => {
			return Tag.render`
				<div class="landing-ui-field-rule-header">
					${this.getHeaderTitleLayout()}
					${this.getRemoveButtonLayout()}
				</div>
			`;
		});
	}

	getBodyLayout(): HTMLDivElement
	{
		return this.cache.remember('bodyLayout', () => {
			return Tag.render`
				<div class="landing-ui-field-rule-body"></div>
			`;
		});
	}

	getFooterLayout(): HTMLDivElement
	{
		return this.cache.remember('footerLayout', () => {
			return Tag.render`
				<div class="landing-ui-field-rule-footer">
					${this.getFooterActionPanel().getLayout()}
				</div>
			`;
		});
	}

	onRemoveClick(event: MouseEvent)
	{
		event.preventDefault();
		Dom.remove(this.getLayout());
		this.emit('onRemove');
		this.emit('onChange');
	}

	getFooterActionPanel(): ActionPanel
	{
		return this.cache.remember('footerActionPanel', () => {
			return new ActionPanel({
				left: [
					{
						id: 'selectField',
						text: Loc.getMessage('LANDING_RULE_FIELD_EXPRESSION_ADD_FIELD_LINK_LABEL'),
						onClick: this.onAddFieldRulesLinkClick.bind(this),
					},
				],
			});
		});
	}

	getFieldsListMenu()
	{
		return this.cache.remember('fieldsMenu', () => {
			return new window.top.BX.Main.Menu({
				bindElement: this.getFooterActionPanel().getLeftContainer().firstElementChild,
				maxHeight: 205,
				items: this.options.fields
					.map((field) => {
						return {
							id: field.id,
							text: field.label,
							onclick: () => {
								this.onAddFieldRulesMenuItemClick(field);
							},
						};
					}),
				autoHide: true,
			});
		});
	}

	onAddFieldRulesLinkClick()
	{
		this.getFieldsListMenu().show();
	}

	onAddFieldRulesMenuItemClick(field: FormField)
	{
		this.addRule({
			condition: {
				field,
				value: '',
				operator: '=',
			},
			expression: [],
		});

		this.getFieldsListMenu().close();
	}

	getValue()
	{
		return this.rows.map((row) => row.getValue());
	}
}