import { Type } from 'main.core';

// eslint-disable-next-line no-unused-vars
import type { FooterType } from '../image-stack-steps-options';
import { footerTypeEnum } from '../image-stack-steps-options';

import { Text } from './types/text';
import { TextStub } from './types/text-stub';
import { Duration } from './types/duration';

import '../css/footer.css';

export const Footer = {
	name: 'ui-image-stack-steps-step-footer',
	props: {
		/** @var { FooterType } footer */
		footer: {
			type: Object,
			required: true,
			validator: (value) => {
				return Type.isPlainObject(value);
			},
		},
	},
	methods: {
		getComponent(): {}
		{
			switch (this.footer.type)
			{
				case footerTypeEnum.TEXT:
					return Text;
				case footerTypeEnum.DURATION:
					return Duration;
				default:
					return TextStub;
			}
		},
		getCustomStyles(): {}
		{
			const styles = {};
			if (Type.isNumber(this.footer.styles?.maxWidth))
			{
				styles.maxWidth = `${this.footer.styles.maxWidth}px`;
			}

			return styles;
		},
	},
	template: `
		<div class="ui-image-stack-steps-footer" :style="getCustomStyles()">
			<component :is="getComponent()" v-bind="footer.data"/>
		</div>
	`,
};
