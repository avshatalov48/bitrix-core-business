import { Type } from 'main.core';
import { validateHeader } from '../helpers/validate-helpers';

// eslint-disable-next-line no-unused-vars
import type { HeaderType } from '../image-stack-steps-options';
import { headerTypeEnum } from '../image-stack-steps-options';

import { Text } from './types/text';
import { TextStub } from './types/text-stub';

import '../css/header.css';

export const Header = {
	name: 'ui-image-stack-steps-step-header',
	props: {
		/** @var {HeaderType} header */
		header: {
			type: Object,
			required: true,
			validator: (value) => {
				return validateHeader(value);
			},
		},
	},
	methods: {
		getComponent(): {}
		{
			if (this.header.type === headerTypeEnum.TEXT)
			{
				return Text;
			}

			return TextStub;
		},
		getCustomStyles(): {}
		{
			const styles = {};
			if (Type.isNumber(this.header.styles?.maxWidth))
			{
				styles.maxWidth = `${this.header.styles.maxWidth}px`;
			}

			return styles;
		},
	},
	template: `
		<div class="ui-image-stack-steps-header" :style="getCustomStyles()">
			<component :is="getComponent()" v-bind="header.data"/>
		</div>
	`,
};
