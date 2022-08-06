export const LoadingState = {
	name: 'LoadingState',
	data: function()
	{
		return {
			itemsToShow: 50
		};
	},
	template: `
		<div class="bx-im-recent-loading-state">
			<div v-for="index in itemsToShow" class="bx-im-recent-item">
				<div class="bx-im-recent-avatar-wrap">
					<div class="bx-im-recent-avatar-image-wrap">
						<div class="bx-im-recent-avatar bx-im-recent-placeholder-avatar"></div>
					</div>
				</div>
				<div class="bx-im-recent-item-content">
					<div class="bx-im-recent-item-content-header">
						<div class="bx-im-recent-item-placeholder-title"></div>
					</div>
					<div class="bx-im-recent-item-content-bottom">
						<div class="bx-im-recent-message-text-wrap">
							<div class="bx-im-recent-item-placeholder-subtitle"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	`
};
