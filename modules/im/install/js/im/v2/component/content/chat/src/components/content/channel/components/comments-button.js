import { FloatButton, FloatButtonIcon, FloatButtonColor } from 'im.v2.component.dialog.chat';

// @vue/component
export const CommentsButton = {
	name: 'CommentsButton',
	components: {
		FloatButton,
	},
	props:
	{
		counter: {
			type: Number,
			required: true,
		},
	},
	computed:
	{
		floatButtonProps(): { icon: string, color: string, counter: number }
		{
			return {
				icon: FloatButtonIcon.comment,
				color: FloatButtonColor.success,
				counter: this.counter,
			};
		},
	},
	template: `
		<FloatButton v-bind="floatButtonProps" />
	`,
};
