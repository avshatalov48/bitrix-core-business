import './recent-loading-state.css';

export const RecentLoadingState = {
	name: 'RecentLoadingState',
	props: {
		compactMode: {
			type: Boolean,
			default: false
		},
		itemsToShow: {
			type: Number,
			default: 50
		}
	},
	methods:
	{
		isThreeLineVersion()
		{
			return Math.random() < 0.5;
		}
	},
	template: `
		<div v-if="!compactMode" class="bx-im-component-recent-loading-state">
			<div v-for="index in itemsToShow" class="bx-im-component-recent-loading-state-item">
				<div class="bx-im-component-recent-loading-state-avatar-wrap">
					<div class="bx-im-component-recent-loading-state-avatar-placeholder"></div>
				</div>
				<div class="bx-im-component-recent-loading-state-content">
					<div class="bx-im-component-recent-loading-state-line bx-im-component-recent-loading-state-line-long"></div>
					<div class="bx-im-component-recent-loading-state-line bx-im-component-recent-loading-state-line-short"></div>
					<div v-if="isThreeLineVersion()" class="bx-im-component-recent-loading-state-line bx-im-component-recent-loading-state-line-short"></div>
				</div>
			</div>
		</div>
		<div v-if="compactMode" class="bx-im-component-recent-loading-state bx-im-component-recent-loading-state-compact">
			<div v-for="index in itemsToShow" class="bx-im-component-recent-loading-state-item">
				<div class="bx-im-component-recent-loading-state-avatar-wrap">
					<div class="bx-im-component-recent-loading-state-avatar-placeholder"></div>
				</div>
			</div>
		</div>
	`
};
