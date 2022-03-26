import {Dom, Tag, Text, Type} from 'main.core';
import {BaseEvent} from 'main.core.events';
import {BaseField} from 'landing.ui.field.basefield';
import {Loc} from 'landing.loc';
import {IconButton} from 'landing.ui.component.iconbutton';
import {ActionPanel} from 'landing.ui.component.actionpanel';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';
import type {RuleGroupOptions} from '../../types';
import RuleEntry from '../rule-entry/rule-entry';
import RuleType from '../../rule-type';
import {FieldElement} from '../field-element/field-element';
import FieldValueElement from '../field-value-element/field-value-element';
import FieldActionPanel from '../field-action-panel/field-action-panel';

import './css/style.css';

export default class RuleGroup extends BaseField
{
	options: RuleGroupOptions;

	constructor(options: RuleGroupOptions)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.Content.FieldRules.RuleGroup');
		this.subscribeFromOptions(fetchEventsFromOptions(options));
		this.setLayoutClass('landing-ui-rule-group');

		const layout = this.getLayout();
		Dom.clean(layout);
		Dom.append(this.getHeaderLayout(), layout);
		Dom.append(this.getBodyLayout(), layout);
		Dom.append(this.getFooterLayout(), layout);

		if (Type.isArrayFilled(this.options.data.list))
		{
			const filteredDataList = this.options.data.list.filter((item) => {
				const conditionTarget = this.getField(item.condition.target);
				const actionTarget = this.getField(item.action.target);
				return conditionTarget && actionTarget;
			});

			if (this.getTypeId() === RuleType.TYPE_0)
			{
				const groupedList = filteredDataList.reduce((acc, item) => {
					const {target, operation, value} = item.condition;
					if (!Type.isArray(acc[`${target}${operation}${value}`]))
					{
						acc[`${target}${operation}${value}`] = [];
					}

					acc[`${target}${operation}${value}`].push(item);

					return acc;
				}, {});

				Object.values(groupedList).forEach((group, index) => {
					const [firstItem] = group;
					if (Type.isPlainObject(firstItem))
					{
						const targetField = this.getField(firstItem.condition.target);
						const entry = new RuleEntry({
							enableHeader: index === 0,
							typeId: this.getTypeId(),
							fields: this.options.fields,
							onChange: () => this.emit('onChange'),
							conditions: [
								new FieldElement({
									dictionary: this.options.dictionary,
									fields: this.options.fields,
									id: targetField.id,
									title: targetField.label,
									color: FieldElement.Colors.blue,
									onRemove: () => {
										this.onConditionFieldRemove(entry);
									},
								}),
								new FieldValueElement({
									dictionary: this.options.dictionary,
									fields: this.options.fields,
									removable: false,
									data: group[0].condition,
								}),
							],
							expressions: group.map((groupItem) => {
								const targetField = this.getField(groupItem.action.target);
								return new FieldElement({
									id: targetField.id,
									title: targetField.label,
									removable: true,
									color: FieldElement.Colors.green,
									actionsLabel: Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_LABEL'),
									actionsList: [
										{
											name: Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_SHOW_LABEL'),
											value: 'show',
										},
										{
											name: Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_HIDE_LABEL'),
											value: 'hide',
										},
									],
									actionsValue: groupItem.action.type,
								});
							}),
						});

						this.addEntry(entry);
					}
				});
			}

			if (
				this.getTypeId() === RuleType.TYPE_1
				|| this.getTypeId() === RuleType.TYPE_2
			)
			{
				const entry = new RuleEntry({
					enableHeader: true,
					typeId: this.getTypeId(),
					fields: this.options.fields,
					onChange: () => this.emit('onChange'),
				});

				const groupedList = filteredDataList.reduce((acc, item) => {
					const {target} = item.condition;
					if (!Type.isArray(acc[target]))
					{
						acc[target] = [];
					}

					acc[target].push(item);

					return acc;
				}, {});

				Object.values(groupedList).forEach((group) => {
					const [firstItem] = group;
					if (Type.isPlainObject(firstItem))
					{
						const targetField = this.getField(firstItem.condition.target);

						const allowedMultipleConditions = (
							(
								this.getTypeId() === RuleType.TYPE_2
								&& targetField.multiple
							)
							|| this.getTypeId() === RuleType.TYPE_1
						);

						entry.addCondition(
							new FieldElement({
								dictionary: this.options.dictionary,
								fields: this.options.fields,
								id: targetField.id,
								title: targetField.label,
								color: FieldElement.Colors.blue,
								onRemove: () => {
									this.onConditionFieldRemove(entry);
								},
							}),
						);

						const groupedConditions = group.reduce((acc, item) => {
							acc[`${item.condition.operation}${item.condition.value}`] = item;
							return acc;
						}, {});

						Object.values(groupedConditions).forEach((item) => {
							entry.addCondition(
								new FieldValueElement({
									dictionary: this.options.dictionary,
									fields: this.options.fields,
									removable: allowedMultipleConditions,
									data: item.condition,
								}),
							);
						});

						entry.addCondition(
							new FieldActionPanel({
								style: {
									display: allowedMultipleConditions ? null : 'none',
								},
								onAddCondition: () => {
									this.onAddFieldCondition(
										new BaseEvent({
											data: {
												entry,
												target: targetField.id,
											},
										}),
									);
								},
							}),
						);
					}
				});

				const groupedExpressions = Object.values(filteredDataList).reduce((acc, item) => {
					const {target, type} = item.action;
					acc[`${target}${type}`] = item;
					return acc;
				}, {});

				Object.values(groupedExpressions).forEach((item) => {
					const targetField = this.getField(item.action.target);
					const element = new FieldElement({
						id: targetField.id,
						title: targetField.label,
						removable: true,
						color: FieldElement.Colors.green,
						actionsLabel: Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_LABEL'),
						actionsList: [
							{
								name: Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_SHOW_LABEL'),
								value: 'show',
							},
							{
								name: Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_HIDE_LABEL'),
								value: 'hide',
							},
						],
						actionsValue: item.action.type,
					});

					entry.addExpression(element);
				});

				this.addEntry(entry);
			}
		}
	}

	getEntries(): Array<RuleEntry>
	{
		return this.cache.remember('entries', () => []);
	}

	setEntries(entries: Array<RuleEntry>)
	{
		this.cache.set('entries', entries);
	}

	addEntry(entry: RuleEntry)
	{
		if (entry)
		{
			const entries = this.getEntries();
			if (!entries.includes(entry))
			{
				entry.subscribe('onChange', () => this.emit('onChange'));

				entries.push(entry);
				Dom.append(entry.getLayout(), this.getBodyLayout());

				this.emit('onChange');
			}
		}
	}

	getHeaderLayout(): HTMLDivElement
	{
		return this.cache.remember('headerLayout', () => {
			return Tag.render`
				<div class="landing-ui-rule-group-header">
					${this.getHeaderTitleLayout()}
					${this.getRemoveButtonLayout()}
				</div>
			`;
		});
	}

	getHeaderTitleLayout(): HTMLDivElement
	{
		return this.cache.remember('headerTitleLayout', () => {
			const titleOfRuleType = Loc.getMessage(`LANDING_FIELDS_RULES_TYPE_${this.getTypeId() + 1}`);
			return Tag.render`
				<div class="landing-ui-rule-group-header-title">${titleOfRuleType}</div>
			`;
		});
	}

	getRemoveButtonLayout(): HTMLDivElement
	{
		return this.cache.remember('removeButtonLayout', () => {
			const button = new IconButton({
				type: IconButton.Types.remove,
				onClick: this.onRemoveClick.bind(this),
				title: Loc.getMessage('LANDING_RULE_GROUP_REMOVE_BUTTON_TITLE'),
				style: {
					marginLeft: 'auto',
				},
			});

			return button.getLayout();
		});
	}

	onRemoveClick()
	{
		Dom.remove(this.getLayout());
		this.emit('onRemove');
		this.emit('onChange');
	}

	getBodyLayout(): HTMLDivElement
	{
		return this.cache.remember('bodyLayout', () => {
			return Tag.render`
				<div class="landing-ui-rule-group-body"></div>
			`;
		});
	}

	getFooterLayout(): HTMLDivElement
	{
		return this.cache.remember('footerLayout', () => {
			return Tag.render`
				<div class="landing-ui-rule-group-footer">
					${this.getFooterActionPanel().getLayout()}
				</div>
			`;
		});
	}

	getFooterActionPanel(): ActionPanel
	{
		return this.cache.remember('footerActionPanel', () => {
			return new ActionPanel({
				left: [
					{
						id: 'selectField',
						text: Loc.getMessage('LANDING_RULE_ENTRY_ADD_FIELD_LABEL'),
						onClick: this.onAddFieldClick.bind(this),
					},
				],
			});
		});
	}

	onAddFieldClick(event: BaseEvent)
	{
		const menu = this.getFieldsListMenu();
		menu.getPopupWindow().setBindElement(event.currentTarget);
		menu.show();
	}

	getFieldsListMenu(): BX.Main.Menu
	{
		return this.cache.remember('fieldsMenu', () => {
			return new window.top.BX.Main.Menu({
				maxHeight: 205,
				items: (
					this.options.fields
						.map((field) => {
							return {
								id: field.id,
								text: field.label,
								onclick: () => {
									this.onFieldsListMenuItemClick(field);
									this.getFieldsListMenu().close();
								},
							};
						})
				),
				autoHide: true,
			});
		});
	}

	getDefaultValueState(fieldId: string): string
	{
		const targetField = this.options.fields.find((field) => {
			return String(field.id) === String(fieldId);
		});

		if (targetField)
		{
			const filteredOperations = this.options.dictionary.deps.condition.operations.filter((operation) => {
				return (
					(
						!Type.isArrayFilled(operation.fieldTypes)
						|| operation.fieldTypes.includes(targetField.type)
					)
					&& (
						!Type.isArrayFilled(operation.excludeFieldTypes)
						|| (
							Type.isArrayFilled(operation.excludeFieldTypes)
							&& !operation.excludeFieldTypes.includes(targetField.type)
						)
					)
				);
			});

			if (Type.isArrayFilled(filteredOperations))
			{
				return filteredOperations[0].id;
			}
		}

		return '=';
	}

	onAddFieldCondition(event: BaseEvent)
	{
		const {target, entry}: {target: string, entry: RuleEntry} = event.getData();
		entry.addCondition(
			new FieldValueElement({
				dictionary: this.options.dictionary,
				fields: this.options.fields,
				removable: true,
				data: {
					target,
					operation: this.getDefaultValueState(target),
					value: null,
				},
			}),
		);
	}

	onConditionFieldRemove(entry: RuleEntry)
	{
		const fieldElements = entry.conditions.filter((item) => {
			return item instanceof FieldElement;
		});

		if (fieldElements.length === 1)
		{
			const entries = this.getEntries().filter((item) => {
				return entry !== item;
			});

			this.setEntries(entries);

			Dom.remove(entry.getLayout());
		}
	}

	onFieldsListMenuItemClick(field)
	{
		if (this.getTypeId() === RuleType.TYPE_0)
		{
			const enableHeader = this.getEntries().length === 0;
			const entry = new RuleEntry({
				enableHeader,
				typeId: this.getTypeId(),
				fields: this.options.fields,
				conditions: [
					new FieldElement({
						dictionary: this.options.dictionary,
						fields: this.options.fields,
						id: field.id,
						title: field.label,
						color: FieldElement.Colors.blue,
						onRemove: () => {
							this.onConditionFieldRemove(entry);
						},
					}),
					new FieldValueElement({
						dictionary: this.options.dictionary,
						fields: this.options.fields,
						removable: false,
						data: {
							target: field.id,
							operation: this.getDefaultValueState(field.id),
							value: null,
						},
					}),
				],
				onChange: () => this.emit('onChange'),
			});

			this.addEntry(entry);
		}

		if (
			this.getTypeId() === RuleType.TYPE_1
			|| this.getTypeId() === RuleType.TYPE_2
		)
		{
			const allowedMultipleConditions = (
				(
					this.getTypeId() === RuleType.TYPE_2
					&& field.multiple
				)
				|| this.getTypeId() === RuleType.TYPE_1
			);

			const items = [
				new FieldElement({
					dictionary: this.options.dictionary,
					fields: this.options.fields,
					id: field.id,
					title: field.label,
					color: FieldElement.Colors.blue,
					onRemove: () => {
						this.onConditionFieldRemove(this.getEntries()[0]);
					},
				}),
				new FieldValueElement({
					dictionary: this.options.dictionary,
					fields: this.options.fields,
					removable: allowedMultipleConditions,
					data: {
						target: field.id,
						operation: this.getDefaultValueState(field.id),
						value: null,
					},
				}),
			];

			if (
				this.getTypeId() === RuleType.TYPE_1
				|| this.getTypeId() === RuleType.TYPE_2
			)
			{
				items.push(
					new FieldActionPanel({
						style: {
							display: allowedMultipleConditions ? null : 'none',
						},
						onAddCondition: () => {
							this.onAddFieldCondition(
								new BaseEvent({
									data: {
										entry: this.getEntries()[0],
										target: field.id,
									},
								}),
							);
						},
					}),
				);
			}

			const [entry] = this.getEntries();
			if (entry)
			{
				items.forEach((item) => {
					entry.addCondition(item);
				});
			}
			else
			{
				const newEntry = new RuleEntry({
					enableHeader: true,
					typeId: this.getTypeId(),
					fields: this.options.fields,
					conditions: items,
					onChange: () => this.emit('onChange'),
				});

				this.addEntry(newEntry);
			}
		}
	}

	getId(): number
	{
		if (!Type.isNil(this.options.data.id))
		{
			return this.options.data.id;
		}

		return 0;
	}

	getTypeId(): number
	{
		return Text.toNumber(this.options.data.typeId);
	}

	getLogic(): 'or' | 'and'
	{
		return this.getTypeId() === RuleType.TYPE_2 ? 'and' : 'or';
	}

	getValue()
	{
		const list = this.getEntries().reduce((acc, entry) => {
			return [...acc, ...entry.getValue()];
		}, []);

		return {
			id: this.getId(),
			typeId: this.getTypeId(),
			logic: this.getLogic(),
			list,
		};
	}

	getField(fieldId: string)
	{
		return this.options.fields.find((item) => {
			return String(item.id) === String(fieldId);
		});
	}
}
