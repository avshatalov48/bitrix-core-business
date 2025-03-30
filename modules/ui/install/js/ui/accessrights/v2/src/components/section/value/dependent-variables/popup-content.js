import { Runtime, Type } from 'main.core';
import '../../../../css/value/dependent-variables/popup-content.css';
import 'ui.icon';
import 'ui.icon-set.actions';
import { Switcher } from 'ui.vue3.components.switcher';
import type { Variable, VariableCollection } from '../../../../store/model/access-rights-model';
import { PopupHeader } from './popup-header';

export const PopupContent = {
	name: 'DependentVariablesPopupContent',
	emits: ['apply'],
	components: { Switcher, PopupHeader },
	props: {
		// value for selector is id of a selected variable
		initialValues: {
			type: Set,
			default: new Set(),
		},
	},
	data(): Object {
		return {
			// values modified during popup lifetime and not yet dispatched to store
			notSavedValues: this.getNotSavedValues(),
		};
	},
	inject: ['section', 'right', 'redefineApply'],
	computed: {
		isMinMaxValuesSet(): boolean {
			return !Type.isNil(this.right.minValue) && !Type.isNil(this.right.maxValue);
		},
		variablesShownInList(): VariableCollection {
			if (!this.isMinMaxValuesSet)
			{
				return this.right.variables;
			}

			const variablesWithoutMinAndSecondary: VariableCollection = Runtime.clone(this.right.variables);
			for (const variableId of this.right.minValue)
			{
				variablesWithoutMinAndSecondary.delete(variableId);
			}

			for (const [variableId: string, variable: Variable] of variablesWithoutMinAndSecondary)
			{
				if (variable.secondary)
				{
					variablesWithoutMinAndSecondary.delete(variableId);
				}
			}

			return variablesWithoutMinAndSecondary;
		},
		secondaryVariables(): VariableCollection {
			const result: VariableCollection = new Map();

			for (const [variableId, variable] of this.right.variables)
			{
				if (variable.secondary)
				{
					result.set(variableId, variable);
				}
			}

			return result;
		},
		nothingSelectedValues(): ?Set<string> {
			return this.$store.getters['accessRights/getNothingSelectedValue'](this.section.sectionCode, this.right.id);
		},
		switcherOptions(): Object {
			return {
				size: 'small',
				color: 'primary',
			};
		},
		secondarySwitcherOptions(): Object {
			return {
				size: 'extra-small',
				color: 'green',
			};
		},
	},
	mounted()
	{
		this.redefineApply(() => {
			this.apply();
		});
	},
	methods: {
		addValue(variableId: string): void {
			const variable: ?Variable = this.right.variables.get(variableId);
			if (!variable)
			{
				return;
			}

			this.notSavedValues.add(variableId);

			if (!Type.isNil(variable.requires))
			{
				for (const requiredId of variable.requires)
				{
					this.notSavedValues.add(requiredId);
				}
			}

			if (!Type.isNil(variable.conflictsWith))
			{
				// remove old variables that conflict with variable we want to add
				for (const conflictId of variable.conflictsWith)
				{
					this.notSavedValues.delete(conflictId);
				}
			}

			for (const otherVariable of this.right.variables.values())
			{
				if (otherVariable.id === variableId)
				{
					continue;
				}

				// if one of the current variables conflicts with newly added variables, we remove old variable
				if (this.notSavedValues.has(otherVariable.id) && !Type.isNil(otherVariable.conflictsWith))
				{
					for (const conflictId of otherVariable.conflictsWith)
					{
						if (this.notSavedValues.has(conflictId))
						{
							this.notSavedValues.delete(otherVariable.id);
						}
					}
				}
			}
		},
		removeValue(variableId: string): void {
			this.notSavedValues.delete(variableId);

			for (const otherVariableId of this.notSavedValues)
			{
				if (otherVariableId === variableId)
				{
					continue;
				}

				const otherVariable: ?Variable = this.right.variables.get(otherVariableId);
				if (!otherVariable)
				{
					continue;
				}

				if (!Type.isNil(otherVariable.requires) && otherVariable.requires.has(variableId))
				{
					this.notSavedValues.delete(otherVariableId);
				}
			}
		},
		setMaxValue(): void {
			for (const variableId of this.right.maxValue)
			{
				this.addValue(variableId);
			}
		},
		setMinValue(): void {
			for (const variableId of this.right.minValue)
			{
				this.addValue(variableId);
			}
		},
		apply(): void {
			let values = this.notSavedValues;
			if (values.size <= 0)
			{
				values = this.nothingSelectedValues;
			}

			this.$emit('apply', {
				values,
			});
		},
		getNotSavedValues(): Set {
			const result = new Set();
			this.initialValues.forEach((value) => {
				if (this.right.variables.has(value))
				{
					result.add(value);
				}
			});

			return result;
		},
	},
	// data attributes are needed for e2e automated tests
	template: `
		<div>
			<PopupHeader
				:values="notSavedValues"
				@set-max="setMaxValue"
				@set-min="setMinValue"
			>
				<slot name="role-title"/>
			</PopupHeader>
			<div class="ui-access-rights-v2-dv-popup--line-container">
				<div
					v-for="[variableId, variable] in variablesShownInList"
					:key="variableId"
					class="ui-access-rights-v2-dv-popup--line"
				>
					<span class="ui-access-rights-v2-text-ellipsis" :title="variable.title">{{ variable.title }}</span>
					<Switcher
						:is-checked="notSavedValues.has(variable.id)"
						@check="addValue(variable.id)"
						@uncheck="removeValue(variable.id)"
						:options="switcherOptions"
						:data-accessrights-variable-id="variable.id"
					/>
				</div>
				<div
					v-for="[variableId, variable] in secondaryVariables"
					:key="variableId"
					class="ui-access-rights-v2-dv-popup--line --secondary"
				>
					<Switcher
						:is-checked="notSavedValues.has(variable.id)"
						@check="addValue(variable.id)"
						@uncheck="removeValue(variable.id)"
						:options="secondarySwitcherOptions"
						style="padding-right: 5px;"
						:data-accessrights-variable-id="variable.id"
					/>
					<span class="ui-access-rights-v2-text-ellipsis">{{ variable.title }}</span>
				</div>
			</div>
		</div>
	`,
};
