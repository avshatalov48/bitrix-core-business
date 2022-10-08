import { SelectorContext, Condition, ConditionGroup } from 'bizproc.automation';
import { InlineSelector } from './inline-selector';
import { Field } from './types';

export class InlineSelectorCondition extends InlineSelector
{
	#condition: Condition;

	constructor(props: {
		context: SelectorContext,
		condition: Condition,
	})
	{
		super(props);

		this.#condition = props.condition;
	}

	renderTo(target: Element)
	{
		this.targetInput = target;
		this.menuButton = target;

		this.parseTargetProperties();
		this.bindTargetEvents();
	}

	fillGroups(): void
	{
		this.fillFieldsGroups();
	}

	onMenuOpen(): void
	{
		this.emit('onOpenMenu', {
			selector: this,
			// TODO - rename
			isMixedCondition: this.#isMixedConditionGroup(),
		});
	}

	onFieldSelect(field: ?Field)
	{
		this.emit('change', { field });
	}

	#isMixedConditionGroup(): boolean
	{
		return (
			this.#condition
			&& this.#condition.parentGroup
			&& this.#condition.parentGroup.type === ConditionGroup.CONDITION_TYPE.Mixed
		);
	}

	getFields(): Array<Field>
	{
		return this.context.fields.map((field) => ({
			...field,
			ObjectId: 'Document',
		}));
	}
}