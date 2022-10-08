import 'ui.design-tokens';

import {Cache, Dom, Tag} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Loc} from 'landing.loc';
import type {FieldRulesOptions, RuleEntryOptions} from '../../types/rule-field-options';
import {FieldElement} from '../field-element/field-element';
import {RuleEntry} from './internal/rule-entry/rule-entry';

import './css/style.css';

export class FieldRules extends EventEmitter
{
	constructor(options: FieldRulesOptions = {})
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Field.RuleField.FieldRow');
		this.options = {...options};
		this.cache = new Cache.MemoryCache();
		this.entries = [];

		this.onEntryChange = this.onEntryChange.bind(this);

		this.addRule(this.options);
	}

	addRule(ruleOptions: RuleEntryOptions)
	{
		const entry = new RuleEntry({
			...ruleOptions,
			fieldsList: this.options.fields,
			dictionary: this.options.dictionary,
		});
		this.entries.push(entry);
		entry.subscribe('onChange', this.onEntryChange);
		Dom.append(entry.getLayout(), this.getListContainer());
		this.emit('onChange');
	}

	onEntryChange()
	{
		this.emit('onChange');
	}

	getConditionFieldLayout(): HTMLDivElement
	{
		return this.cache.remember('conditionField', () => {
			const fieldElement = new FieldElement({
				title: this.options.condition.field.label,
				removable: true,
				onRemove: this.onConditionFieldRemoveClick.bind(this),
			});

			return fieldElement.getLayout();
		});
	}

	onConditionFieldRemoveClick(event: MouseEvent)
	{
		event.preventDefault();
		Dom.remove(this.getLayout());
		this.entries = [];
		this.emit('onChange');
	}

	getFieldContainer(): HTMLDivElement
	{
		return this.cache.remember('fieldContainer', () => {
			return Tag.render`
				<div class="landing-ui-field-rule-field-row-field-container">
					<div class="landing-ui-field-rule-field-row-field-container-title">
						${Loc.getMessage('LANDING_RULE_FIELD_CONDITION_FIELD_TITLE')}
					</div>
					${this.getConditionFieldLayout()}
					<div class="landing-ui-field-rule-field-row-field-container-action-title">
						${Loc.getMessage('LANDING_RULE_FIELD_CONDITION_FIELD_SHOW_ACTION_TITLE')}
					</div>
				</div>
			`;
		});
	}

	getListContainer(): HTMLDivElement
	{
		return this.cache.remember('listContainer', () => {
			return Tag.render`
				<div class="landing-ui-field-rule-field-row-list"></div>
			`;
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-field-rule-field-row">
					${this.getFieldContainer()}
					${this.getListContainer()}
				</div>
			`;
		});
	}

	getValue()
	{
		return this.entries
			.map((entry) => entry.getValue());
	}
}