import { Type } from 'main.core';
import { Locator } from '../popup-header/locator';
import { MasterSwitcher } from '../popup-header/master-switcher';
import { SingleRoleTitle } from '../popup-header/master-switcher/single-role-title';

export const PopupHeader = {
	name: 'DependentVariablesPopupHeader',
	components: { Locator, MasterSwitcher, SingleRoleTitle },
	emits: ['setMax', 'setMin'],
	props: {
		values: {
			/** @type Set<string> */
			type: Set,
			required: true,
		},
	},
	inject: ['right'],
	computed: {
		isChecked(): boolean {
			if (!this.isMinMaxValuesSet)
			{
				return this.values.size > 0;
			}

			return this.isSelectedAnythingBesidesMin;
		},
		isMinMaxValuesSet(): boolean {
			return !Type.isNil(this.right.minValue) && !Type.isNil(this.right.maxValue);
		},
		isSelectedAnythingBesidesMin(): boolean {
			if (this.values.size <= 0)
			{
				return false;
			}

			for (const variableId of this.values)
			{
				if (!this.right.minValue.has(variableId))
				{
					return true;
				}
			}

			return false;
		},
	},
	methods: {
		setMin(): void
		{
			if (this.isMinMaxValuesSet)
			{
				this.$emit('setMin');
			}
		},
		setMax(): void
		{
			if (this.isMinMaxValuesSet)
			{
				this.$emit('setMax');
			}
		},
	},
	template: `
		<div>
			<Locator/>
			<MasterSwitcher
				:is-checked="isChecked"
				@check="setMax"
				@uncheck="setMin"
			>
				<slot/>
			</MasterSwitcher>
		</div>
	`,
};
