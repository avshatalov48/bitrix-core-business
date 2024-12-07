export const HeaderTitle = {
	props: {
		hasBackButton: {
			type: Boolean,
			default: false,
		},
		backButtonCallback: {
			type: Function,
			default: () => {},
		},
		text: {
			type: String,
			required: true,
		},
	},
	template: `
		<div class="calendar-sharing-header-title_container" :class="{'--center': !hasBackButton}">
			<div class="calendar-sharing-header-title_icon"></div>
			<div
				v-show="hasBackButton"
				@click="backButtonCallback"
				class="calendar-sharing-header-title_back-button"
			>
			</div>
			<div class="calendar-sharing-header-title_text">{{text}}</div>
		</div>
	`,
};
