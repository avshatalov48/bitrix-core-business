import { Text } from 'main.core';

import { Button as MessengerButton, ButtonSize, ButtonColor } from 'im.v2.component.elements';
import '../../css/notification-item-confirm-buttons.css';

// @vue/component
export const NotificationItemConfirmButtons = {
	name: 'NotificationItemConfirmButtons',
	components: { MessengerButton },
	props: {
		buttons: {
			type: Array,
			required: true,
		},
	},
	emits: ['confirmButtonsClick'],
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		preparedButtons(): Array
		{
			return this.buttons.map((button) => {
				const [id, value] = button.COMMAND_PARAMS.split('|');

				// we need to decode it, because legacy chat does htmlspecialcharsbx on the server side
				// @see \CIMMessenger::Add
				const text = Text.decode(button.TEXT);

				return { id, value, text };
			});
		},
	},
	methods:
	{
		click(button)
		{
			this.$emit('confirmButtonsClick', button);
		},
		getButtonColor(button): string
		{
			return button.value === 'Y' ? ButtonColor.Primary : ButtonColor.LightBorder;
		},
	},
	template: `
		<div class="bx-im-content-notification-item-confirm-buttons__container">
			<MessengerButton
				v-for="(button, index) in preparedButtons" :key="index"
				:text="button.text"
				:color="getButtonColor(button)"
				:size="ButtonSize.M"
				:isRounded="true"
				:isUppercase="false"
				@click="click(button)"
			></MessengerButton>
		</div>
	`,
};
