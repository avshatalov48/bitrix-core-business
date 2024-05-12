import { DialogStatus } from 'im.v2.component.elements';

import { BitrixVue } from 'ui.vue3';

// @vue/component
export const CopilotDialogStatus = BitrixVue.cloneComponent(DialogStatus, {
	template: `
		<div class="bx-im-dialog-chat-status__container">
			<div v-if="typingStatus" class="bx-im-dialog-chat-status__content">
				<div class="bx-im-dialog-chat-status__icon --typing"></div>
				<div class="bx-im-dialog-chat-status__text">{{ typingStatus }}</div>
			</div>
		</div>
	`,
});
