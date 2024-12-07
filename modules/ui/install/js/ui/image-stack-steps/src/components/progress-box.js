import { BIcon } from 'ui.icon-set.api.vue';

import '../css/progress-box.css';

export const ProgressBox = {
	name: 'ui-image-stack-steps-step-progress-box',
	components: {
		BIcon,
	},
	props: {
		title: {
			type: String,
			required: true,
		},
	},
	template: `
		<div
			:title="title"
			class="ui-image-stack-steps-step-progress-box"
		>
			<BIcon
				name="more"
				:size="12"
				color="var(--ui-color-base-70)"
				class="ui-image-stack-steps-step-progress-box__icon"
			/>
			<div class="ui-image-stack-steps-step-progress-box__icon-overlay"></div>
		</div>
	`,
};
