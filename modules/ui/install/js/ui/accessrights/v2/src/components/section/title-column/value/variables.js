import { MenuManager } from 'main.popup';

const POPUP_ID = 'ui-access-rights-v2-row-value-variables';

export const Variables = {
	name: 'Variables',
	emits: ['close'],
	inject: ['section', 'right'],
	mounted()
	{
		this.showSelector();
	},
	beforeUnmount()
	{
		this.closeSelector();
	},
	methods: {
		showSelector(): void {
			const menuItems = [];
			for (const variable of this.right.variables.values())
			{
				menuItems.push({
					id: variable.id,
					text: variable.title,
					onclick: (innerEvent, item) => {
						item
							.getMenuWindow()
							?.close()
						;

						this.setValue(variable.id);
					},
				});
			}

			MenuManager.show({
				id: POPUP_ID,
				bindElement: this.$el,
				items: menuItems,
				autoHide: true,
				cacheable: false,
				events: {
					onClose: () => {
						this.$emit('close');
					},
				},
			});
		},
		setValue(value): void {
			this.$store.dispatch('userGroups/setAccessRightValuesForShown', {
				sectionCode: this.section.sectionCode,
				valueId: this.right.id,
				values: new Set([value]),
			});
		},
		closeSelector(): void {
			MenuManager.getMenuById(POPUP_ID)?.close();
		},
	},
	// invisible div for binding selector to it
	template: `
		<div></div>
	`,
};
