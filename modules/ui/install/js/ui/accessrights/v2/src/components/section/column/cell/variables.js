import { Type } from 'main.core';
import { MenuManager } from 'main.popup';

const POPUP_ID = 'ui-access-rights-v2-column-item-popup-variables';

export const Variables = {
	name: 'Variables',
	props: {
		// value for selector is id of a selected variable
		value: {
			/** @type AccessRightValue */
			type: Object,
			required: true,
		},
	},
	inject: ['section', 'userGroup', 'right'],
	computed: {
		emptyVariableId(): ?string {
			const emptyValue: Set<string> = this.$store.getters['accessRights/getEmptyValue'](
				this.section.sectionCode,
				this.value.id,
			);

			return emptyValue[0];
		},
		currentVariableId(): ?string {
			if (this.value.values.size <= 0)
			{
				return this.emptyVariableId;
			}

			const [firstItem] = this.value.values;

			return firstItem;
		},
		currentAlias(): ?string {
			return this.$store.getters['accessRights/getSelectedVariablesAlias'](this.section.sectionCode, this.value.id, this.value.values);
		},
		currentVariableTitle(): string {
			if (Type.isString(this.currentAlias))
			{
				return this.currentAlias;
			}

			const variable = this.right.variables.get(this.currentVariableId);
			if (!variable)
			{
				return this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ADD');
			}

			return variable.title;
		},
	},
	methods: {
		showSelector(event: PointerEvent): void {
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
				bindElement: event.target,
				items: menuItems,
				autoHide: true,
				cacheable: false,
			});
		},
		setValue(value): void {
			this.$store.dispatch('userGroups/setAccessRightValues', {
				sectionCode: this.section.sectionCode,
				userGroupId: this.userGroup.id,
				valueId: this.value.id,
				values: new Set([value]),
			});
		},
	},
	template: `
		<div
			class='ui-access-rights-v2-column-item-text-link ui-access-rights-v2-text-ellipsis'
			:title="currentVariableTitle"
			@click="showSelector"
		>
			{{ currentVariableTitle }}
		</div>
	`,
};
