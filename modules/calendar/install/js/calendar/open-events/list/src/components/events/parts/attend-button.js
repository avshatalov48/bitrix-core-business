import { Button, ButtonColor, ButtonSize, ButtonIcon } from 'ui.buttons';

//TODO: remove when button icons ready
import 'ui.icon-set.actions';
import '../css/attend-button.css';

export const AttendButton = {
	props: {
		isAttendee: Boolean,
	},
	methods: {
		renderButton(): void
		{
			const button = new Button({
				color: this.isAttendee ? ButtonColor.LIGHT_BORDER : ButtonColor.SUCCESS,
				size: ButtonSize.SMALL,
				round: true,
				//TODO: replace with icon property when icons ready
				// icon: this.isAttendee ? ButtonIcon. : ButtonIcon.,
				className: this.isAttendee
					? 'calendar-open-events-list-item__attend-button --off'
					: 'calendar-open-events-list-item__attend-button --on'
				,
			});

			this.$refs.bindBtn.innerHTML = '';
			button.renderTo(this.$refs.bindBtn);
		},
	},
	watch: {
		isAttendee(): void
		{
			this.renderButton();
		},
	},
	mounted(): void
	{
		this.renderButton();
	},
	template: `
		<div ref="bindBtn"></div>
	`,
};
