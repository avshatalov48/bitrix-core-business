import { Text, Type } from 'main.core';

import '../../css/types/image.css';

export const Image = {
	name: 'ui-image-stack-steps-image',
	props: {
		src: {
			type: String,
			required: true,
			validator: (value) => {
				return Type.isStringFilled(value);
			},
		},
		title: {
			type: String,
			required: false,
			validator: (value) => {
				return Type.isStringFilled(value);
			},
		},
	},
	methods: {
		getSafeSrc(): string
		{
			return `url('${encodeURI(Text.encode(this.src))}')`;
		},
	},
	template: `
		<div
			:style="{ backgroundImage: getSafeSrc()}"
			class="ui-image-stack-steps-image"
			:title="title"
		></div>
	`,
};
