import { Type } from 'main.core';
import { BIcon } from 'ui.icon-set.api.vue';

import '../../css/types/image.css';
import '../../css/types/icon.css';

export const Icon = {
	name: 'ui-image-stack-steps-icon',
	components: { BIcon },
	props: {
		icon: {
			type: String,
			required: true,
			validator: (value) => {
				return Type.isStringFilled(value);
			},
		},
		color: {
			type: String,
			required: true,
			validator: (value) => {
				return Type.isStringFilled(value);
			},
		},
	},
	template: `
		<div class="ui-image-stack-steps-image --icon">
			<BIcon :name="icon" :color="color" :size="24"/>
		</div>
	`,
};
