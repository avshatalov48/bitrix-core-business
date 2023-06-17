import {Button, ButtonSize, ButtonColor} from 'im.v2.component.elements';
import '../../css/notification-item-confirm-buttons.css';

// @vue/component
export const NotificationItemConfirmButtons = {
	name: 'NotificationItemConfirmButtons',
	components: {Button},
	props: {
		buttons: {
			type: Array,
			required: true
		},
	},
	emits: ['confirmButtonsClick'],
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		preparedButtons(): Array
		{
			return this.buttons.map(button => {
				const [id, value] = button.COMMAND_PARAMS.split('|');

				return {
					id: id,
					value: value,
					text: button.TEXT,
				};
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
		}
	},
	template: `
		<div class="bx-im-content-notification-item-confirm-buttons__container">
			<Button
				v-for="(button, index) in preparedButtons" :key="index"
				:text="button.text"
				:color="getButtonColor(button)"
				:size="ButtonSize.M"
				:isRounded="true"
				:isUppercase="false"
				@click="click(button)"
			></Button>
		</div>
	`
};