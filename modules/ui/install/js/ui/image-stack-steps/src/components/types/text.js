import '../../css/types/text.css';

export const Text = {
	name: 'ui-image-stack-steps-text',
	props: {
		text: {
			type: String,
			required: true,
		},
	},
	template: `
		<div class="ui-image-stack-steps-text" :title="text">{{ text }}</div>
	`,
};
