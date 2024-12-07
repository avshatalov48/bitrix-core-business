import { Text, Type } from 'main.core';

import 'ui.tooltip';

import '../../css/types/image.css';
import '../../css/types/user.css';

export const User = {
	name: 'ui-image-stack-steps-user',
	props: {
		src: {
			type: String,
			required: true,
		},
		userId: {
			type: Number,
			required: true,
			validator: (value) => {
				return value > 0;
			},
		},
	},
	data(): {}
	{
		return {
			style: {
				backgroundImage: Type.isStringFilled(this.src) ? this.getSafeSrc() : '',
			},
		};
	},
	methods: {
		getSafeSrc(): string
		{
			return `url('${encodeURI(Text.encode(this.src))}')`;
		},
	},
	template: `
		<div 
			class="ui-image-stack-steps-image --user"
			:style="style"
			:bx-tooltip-user-id="userId"
		></div>
	`,
};
