import { Type } from 'main.core';
import '../../css/types/counter.css';

export const Counter = {
	name: 'ui-image-stack-steps-counter',
	props: {
		text: {
			type: String,
			required: true,
			validator: (value) => {
				return Type.isStringFilled(value);
			},
		},
	},
	template: `
		<div class="ui-image-stack-steps-counter">{{ text }}</div>
	`,
};
