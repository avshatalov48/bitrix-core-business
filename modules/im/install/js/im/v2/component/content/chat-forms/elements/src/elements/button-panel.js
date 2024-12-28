import { Button as MessengerButton, ButtonSize, ButtonColor } from 'im.v2.component.elements';

import './css/button-panel.css';

// @vue/component
export const ButtonPanel = {
	components: { MessengerButton },
	props:
	{
		isCreating: {
			type: Boolean,
			required: true,
		},
		createButtonTitle: {
			type: String,
			required: true,
		},
		createButtonColorScheme: {
			type: [Object, null],
			required: false,
			default: null,
		},
	},
	emits: ['create', 'cancel'],
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-chat-forms-button-panel__container">
			<MessengerButton
				:size="ButtonSize.XL"
				:color="ButtonColor.Success"
				:customColorScheme="createButtonColorScheme"
				:text="createButtonTitle"
				:isLoading="isCreating"
				:isDisabled="isCreating"
				@click="$emit('create')"
				class="bx-im-chat-forms-button-panel__create-button"
			/>
			<MessengerButton
				:size="ButtonSize.XL"
				:color="ButtonColor.Link"
				:text="loc('IM_CREATE_CHAT_CANCEL')"
				:isDisabled="isCreating"
				@click="$emit('cancel')"
			/>
		</div>
	`,
};
