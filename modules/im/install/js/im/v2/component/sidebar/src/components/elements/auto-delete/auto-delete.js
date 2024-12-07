import { hint } from 'ui.vue3.directives.hint';

import { Toggle, ToggleSize } from 'im.v2.component.elements';

import './css/auto-delete.css';

// @vue/component
export const AutoDelete = {
	name: 'AutoDelete',
	directives: { hint },
	components: { Toggle },
	computed:
	{
		ToggleSize: () => ToggleSize,
		hintAutoDeleteNotAvailable()
		{
			return {
				text: this.loc('IM_MESSENGER_NOT_AVAILABLE'),
				popupOptions: {
					bindOptions: {
						position: 'top',
					},
					angle: true,
					targetContainer: document.body,
					offsetLeft: 125,
					offsetTop: -10,
				},
			};
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-sidebar-auto-delete__container --not-active" v-hint="hintAutoDeleteNotAvailable">
			<div class="bx-im-sidebar-auto-delete__title">
				<div class="bx-im-sidebar-auto-delete__title-text bx-im-sidebar-auto-delete__icon">
					{{ loc('IM_SIDEBAR_ENABLE_AUTODELETE_TITLE') }}
				</div>
				<Toggle :size="ToggleSize.M" :isEnabled="false" />
			</div>
			<div class="bx-im-sidebar-auto-delete__status">
				{{ loc('IM_SIDEBAR_AUTODELETE_STATUS_OFF') }}
			</div>
		</div>
	`,
};
