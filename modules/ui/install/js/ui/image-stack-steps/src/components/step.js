import { Type, Text } from 'main.core';
import { validateStep } from '../helpers/validate-helpers';

// eslint-disable-next-line no-unused-vars
import type { StepType } from '../image-stack-steps-options';

import { ProgressBox } from './progress-box';
import { Header } from './header';
import { Stack } from './stack';
import { Footer } from './footer';

import '../css/step.css';

export const Step = {
	name: 'ui-image-stack-steps-step',
	components: {
		ProgressBox,
		Header,
		Stack,
		Footer,
	},
	props: {
		/** @var {StepType} step */
		step: {
			type: Object,
			required: true,
			validator: (value) => {
				return validateStep(value);
			},
		},
	},
	computed: {
		hasProgressBox(): boolean
		{
			return Type.isPlainObject(this.step.progressBox);
		},
		hasHeader(): boolean
		{
			return !Type.isNil(this.step.header);
		},
		hasFooter(): boolean
		{
			return !Type.isNil(this.step.footer);
		},
		getCustomStyles(): {}
		{
			const styles = {};
			if (this.step.styles?.minWidth)
			{
				styles.minWidth = `${Text.toInteger(this.step.styles.minWidth)}px`;
			}

			return styles;
		},
	},
	template: `
		<div 
			class="ui-image-stack-steps-step"
			:class="{'--with-header': hasHeader, '--with-footer': hasFooter}"
			:style="getCustomStyles"
		>
			<ProgressBox v-if="hasProgressBox" :title="step.progressBox.title"/>
			<Header v-if="hasHeader" :header="step.header"/>
			<Stack :stack="step.stack"/>
			<Footer v-if="hasFooter" :footer="step.footer"/>
		</div>
	`,
};
