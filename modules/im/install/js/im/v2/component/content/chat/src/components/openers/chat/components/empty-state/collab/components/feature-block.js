// @vue/component
export const FeatureBlock = {
	name: 'FeatureBlock',
	props:
	{
		name: {
			type: String,
			required: true,
		},
		title: {
			type: String,
			required: true,
		},
		subtitle: {
			type: String,
			required: true,
		},
	},
	template: `
		<div class="bx-im-content-collab-start__block">
			<div class="bx-im-content-collab-start__block_icon" :class="'--' + name"></div>
			<div class="bx-im-content-collab-start__block_content">
				<div class="bx-im-content-collab-start__block_title">
					{{ title }}
				</div>
				<div class="bx-im-content-collab-start__block_subtitle">
					{{ subtitle }}
				</div>
			</div>
		</div>
	`,
};
