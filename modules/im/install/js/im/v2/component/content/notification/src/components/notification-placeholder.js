import '../css/notification-placeholder.css';

export const NotificationPlaceholder = {
	name: 'NotificationPlaceholder',
	props: {
		itemsToShow: {
			type: Number,
			default: 50
		}
	},
	template: `
		<div class="bx-im-content-notification-placeholder__container" v-for="index in itemsToShow">
			<div class="bx-im-content-notification-placeholder__element">
				<div class="bx-im-content-notification-placeholder__avatar-container">
					<div class="bx-im-content-notification-placeholder__avatar"></div>
				</div>
				<div class="bx-im-content-notification-placeholder__content-container">
					<div class="bx-im-content-notification-placeholder__content-inner">
						<div class="bx-im-content-notification-placeholder__content --top"></div>
						<div class="bx-im-content-notification-placeholder__content --short"></div>
					</div>
					<div class="bx-im-content-notification-placeholder__content --full"></div>
					<div class="bx-im-content-notification-placeholder__content --middle"></div>
					<div class="bx-im-content-notification-placeholder__content --bottom"></div>
				</div>
			</div>
		</div>
	`
};