import {BaseEvent, EventEmitter} from 'main.core.events';
import {Cache, Dom, Tag, Type} from 'main.core';
import {Loc} from 'landing.loc';
import {ActionPanel} from 'landing.ui.component.actionpanel';
import {FieldElement} from '../field-element/field-element';
import FieldValueElement from '../field-value-element/field-value-element';
import TypeSeparator from '../type-separator/type-separator';

import './css/style.css';

type RuleEntryOptions = {
	enableHeader?: boolean,
	typeId: number,
	fields: Array<any>,
	conditions: Array<FieldElement>,
	expressions: Array<FieldElement>,
};

export default class RuleEntry extends EventEmitter
{
	options: RuleEntryOptions;
	conditions: Array<FieldElement | FieldValueElement> = [];
	expressions: Array<FieldElement> = [];

	constructor(options: RuleEntryOptions) {
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.RuleEntry');

		this.options = {enableHeader: true, expressions: [], ...options};
		this.cache = new Cache.MemoryCache();

		this.onConditionFieldValueRemove = this.onConditionFieldValueRemove.bind(this);
		this.onConditionFieldRemove = this.onConditionFieldRemove.bind(this);

		if (Type.isArrayFilled(this.options.conditions))
		{
			this.options.conditions.forEach((item) => {
				this.addCondition(item);
			});

			this.options.expressions.forEach((item) => {
				this.addExpression(item);
			});
		}
	}

	getConditionsLayout(): HTMLDivElement
	{
		return this.cache.remember('conditionsLayout', () => {
			return Tag.render`
				<div class="landing-ui-rule-entry-conditions"></div>
			`;
		});
	}

	getExpressionsLayout(): HTMLDivElement
	{
		return this.cache.remember('expressionsLayout', () => {
			return Tag.render`
				<div class="landing-ui-rule-entry-expressions">
					${this.getAddExpresionFieldLinkLayout()}
				</div>
			`;
		});
	}

	getHeaderLayout(): HTMLDivElement
	{
		return this.cache.remember('headerLayout', () => {
			return Tag.render`
				<div class="landing-ui-rule-entry-header">${Loc.getMessage('LANDING_RULE_ENTRY_HEADER')}</div>
			`;
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-rule-entry">
					${this.options.enableHeader ? this.getHeaderLayout() : ''}
					<div class="landing-ui-rule-entry-body">
						${this.getConditionsLayout()}
						${this.getExpressionsLayout()}
					</div>
				</div>
			`;
		});
	}

	onConditionFieldRemove(event: BaseEvent)
	{
		const target: FieldElement = event.getTarget();
		const targetLayout = target.getLayout();

		this.conditions = this.conditions.filter((item) => {
			return item !== target;
		});

		let nextNode = targetLayout.nextElementSibling;
		while (
			Type.isDomNode(nextNode)
			&& !nextNode.matches('[class*="landing-ui-field-element"]')
		)
		{
			this.conditions = this.conditions.filter((item) => {
				return item.getLayout() !== nextNode;
			});

			Dom.remove(nextNode);
			nextNode = targetLayout.nextElementSibling;
		}

		if (!Type.isDomNode(nextNode))
		{
			const prevNode = targetLayout.previousElementSibling;
			if (
				Type.isDomNode(prevNode)
				&& Dom.hasClass(prevNode, 'landing-ui-rule-entry-type-separator')
			)
			{
				Dom.remove(prevNode);
			}
		}

		Dom.remove(targetLayout);

		this.emit('onChange');
	}

	onConditionFieldValueRemove(event: BaseEvent)
	{
		const target: FieldValueElement = event.getTarget();
		const targetLayout = target.getLayout();

		this.conditions = this.conditions.filter((item) => {
			return item !== target;
		});

		if (Dom.hasClass(targetLayout.nextElementSibling, 'landing-ui-rule-entry-type-separator'))
		{
			Dom.remove(targetLayout.nextElementSibling);
		}
		else if (Dom.hasClass(targetLayout.previousElementSibling, 'landing-ui-rule-entry-type-separator'))
		{
			Dom.remove(targetLayout.previousElementSibling);
		}

		Dom.remove(targetLayout);
	}

	addCondition(element: FieldValueElement)
	{
		if (!this.conditions.includes(element))
		{
			this.conditions.push(element);

			if (element instanceof FieldValueElement)
			{
				element.subscribe('onRemove', this.onConditionFieldValueRemove);
				element.subscribe('onChange', () => this.emit('onChange'));

				const conditionsNodes = [...this.getConditionsLayout().childNodes];
				const lastElement = conditionsNodes.reduce((acc, node) => {
					if (
						(
							Dom.hasClass(node, 'landing-ui-rule-value')
							&& String(Dom.attr(node, 'data-target')) === String(element.options.data.target)
						)
						|| (
							node.matches('[class*="landing-ui-field-element"]')
							&& String(Dom.attr(node, 'data-field-id')) === String(element.options.data.target)
						)
					)
					{
						return node;
					}

					return acc;
				}, null);

				if (Type.isDomNode(lastElement))
				{
					Dom.insertAfter(element.getLayout(), lastElement);

					if (Dom.hasClass(lastElement, 'landing-ui-rule-value'))
					{
						const separator = new TypeSeparator({
							typeId: this.options.typeId,
						});

						Dom.insertBefore(separator.getLayout(), element.getLayout());
					}
					return;
				}
			}

			if (element instanceof FieldElement)
			{
				element.subscribe('onRemove', this.onConditionFieldRemove);
				element.subscribe('onChange', () => this.emit('onChange'));

				if ([...this.getConditionsLayout().childNodes].length > 0)
				{
					const separator = new TypeSeparator({
						typeId: this.options.typeId,
					});

					Dom.append(separator.getLayout(), this.getConditionsLayout());
				}
			}

			Dom.append(
				element.getLayout(),
				this.getConditionsLayout(),
			);

			this.emit('onChange');
		}
	}

	getExpressionActionPanel(): ActionPanel
	{
		return this.cache.remember('expressionActionPanel', () => {
			return new ActionPanel({
				left: [
					{
						id: 'addField',
						text: Loc.getMessage('LANDING_RULE_ENTRY_ADD_FIELD_LABEL'),
						onClick: this.onAddExpressionFieldClick.bind(this),
					},
				],
			});
		});
	}

	onAddExpressionFieldClick(event: MouseEvent)
	{
		event.preventDefault();

		const menu = this.getFieldsListMenu();
		menu.getMenuItems().forEach((item) => {
			const isUsed = this.expressions.some((expressionItem) => {
				return String(expressionItem.options.id) === String(item.getId());
			});

			if (isUsed)
			{
				Dom.addClass(item.getLayout().item, 'landing-ui-disabled');
			}
			else
			{
				Dom.removeClass(item.getLayout().item, 'landing-ui-disabled');
			}
		});

		this.getFieldsListMenu().show();
	}

	getExpressionAllowedFieldsList(): Array<any>
	{
		const disallowedTypes = ['page', 'layout'];
		return this.options.fields.filter((field) => {
			if (!disallowedTypes.includes(field.type))
			{
				return !this.conditions.find((condition) => {
					return (
						Type.isPlainObject(condition.options)
						&& (
							(
								Type.isPlainObject(condition.options.data)
								&& String(condition.options.data.target) === String(field.id)
							)
							|| String(condition.options.id) === String(field.id)
						)
					);
				});
			}

			return true;
		});
	}

	getFieldsListMenu()
	{
		return this.cache.remember('fieldsListMenu', () => {
			return new window.top.BX.Main.Menu({
				bindElement: this.getExpressionActionPanel().getLayout(),
				maxHeight: 205,
				items: this.getExpressionAllowedFieldsList()
					.map((item) => {
						return {
							id: item.id,
							text: item.label,
							onclick: this.onAddExpressionField.bind(this, item),
						};
					}),
			});
		});
	}

	getAddExpresionFieldLinkLayout(): HTMLDivElement
	{
		return this.cache.remember('addExpressionFieldLinkLayout', () => {
			return Tag.render`
				<div class="landing-ui-rule-entry-add-expression-field-link">
					<div class="landing-ui-rule-entry-add-expression-field-link-action-panel">
						${this.getExpressionActionPanel().getLayout()}
					</div>
					<div class="landing-ui-rule-entry-add-expression-field-link-separator"></div>
				</div>
			`;
		});
	}

	onAddExpressionField(field)
	{
		const element = new FieldElement({
			id: field.id,
			title: field.label,
			removable: true,
			color: FieldElement.Colors.green,
			actionsLabel: Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_LABEL'),
			actionsList: [
				{name: Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_SHOW_LABEL'), value: 'show'},
				{name: Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_HIDE_LABEL'), value: 'hide'},
			],
			actionsValue: 'show',
		});

		this.addExpression(element);

		this.getFieldsListMenu().close();

		this.emit('onChange');
	}

	onExpressionFieldRemove(event: BaseEvent)
	{
		const target = event.getTarget();

		Dom.remove(target.getLayout());

		this.expressions = this.expressions.filter((field) => {
			return String(field.options.id) !== String(target.options.id);
		});

		this.adjustExpressionFieldsZIndexes();

		this.emit('onChange');
	}

	onExpressionFieldChange()
	{
		this.emit('onChange');
	}

	adjustExpressionFieldsZIndexes()
	{
		[...this.getExpressionsLayout().children]
			.reverse()
			.forEach((node, index) => {
				if (node.matches('[class*="landing-ui-field-element"]'))
				{
					Dom.style(node, 'z-index', index + 2);
				}
			});
	}

	addExpression(element: FieldElement)
	{
		if (!this.expressions.includes(element))
		{
			this.expressions.push(element);

			element.subscribe('onRemove', this.onExpressionFieldRemove.bind(this));
			element.subscribe('onChange', this.onExpressionFieldChange.bind(this));

			// @todo: refactoring
			void this.getLayout();

			Dom.insertBefore(element.getLayout(), this.getAddExpresionFieldLinkLayout());

			this.adjustExpressionFieldsZIndexes();
		}
	}

	getValue()
	{
		return this.conditions
			.filter((item) => item instanceof FieldValueElement)
			.reduce((acc, conditionsItem: FieldValueElement) => {
				return [
					...acc,
					...this.expressions.map((expressionItem) => {
						return {
							condition: {
								...conditionsItem.getValue(),
								event: 'change',
							},
							action: {
								target: expressionItem.options.id,
								type: expressionItem.getActionsDropdown().getValue(),
							},
						};
					}),
				];
			}, []);
	}
}
