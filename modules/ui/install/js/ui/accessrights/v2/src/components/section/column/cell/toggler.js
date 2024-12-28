import { Switcher } from 'ui.vue3.components.switcher';

export const Toggler = {
	name: 'Toggler',
	components: { Switcher },
	props: {
		value: {
			/** @type AccessRightValue */
			type: Object,
			required: true,
		},
	},
	inject: ['section', 'userGroup'],
	computed: {
		isChecked(): boolean {
			return this.value.values.has('1');
		},
	},
	methods: {
		setValue(value): void {
			this.$store.dispatch('userGroups/setAccessRightValues', {
				userGroupId: this.userGroup.id,
				sectionCode: this.section.sectionCode,
				valueId: this.value.id,
				values: new Set([value]),
			});
		},
	},
	// eslint-disable-next-line quotes
	template: `
		<Switcher
			:is-checked="isChecked"
			@check="setValue('1')"
			@uncheck="setValue('0')"
			:options="{
				size: 'extra-small',
				color: 'green',
			}"
		/>
	`,
};
