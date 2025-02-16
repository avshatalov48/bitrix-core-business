import { PopupContent } from '../../value/dependent-variables/popup-content';
import { AllRolesTitle } from '../../value/popup-header/master-switcher/all-roles-title';
import { ValuePopup } from '../../value/value-popup';

export const DependentVariables = {
	name: 'DependentVariables',
	components: { PopupContent, AllRolesTitle, ValuePopup },
	emits: ['close'],
	inject: ['section', 'right'],
	methods: {
		apply({ values }): void
		{
			this.$store.dispatch('userGroups/setAccessRightValuesForShown', {
				sectionCode: this.section.sectionCode,
				valueId: this.right.id,
				values,
			});
		},
	},
	template: `
		<ValuePopup @close="$emit('close')">
			<PopupContent @apply="apply">
				<template #role-title>
					<AllRolesTitle/>
				</template>
			</PopupContent>
		</ValuePopup>
	`,
};
