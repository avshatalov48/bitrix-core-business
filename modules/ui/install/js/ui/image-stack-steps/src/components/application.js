import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Step } from './step';

import '../css/application.css';

export const Application = {
	name: 'ui-image-stack-steps-application',
	components: { Step },
	props: {
		initialSteps: {
			type: Array,
			required: true,
			validator: (value) => {
				return Type.isArrayFilled(value);
			},
		},
	},
	data(): {}
	{
		return { steps: this.initialSteps };
	},
	created()
	{
		this.subscribeOnEvents();
	},
	methods: {
		subscribeOnEvents()
		{
			if (this.$root.$app)
			{
				EventEmitter.subscribe(this.$root.$app, 'UI.ImageStackSteps.onUpdateSteps', () => {
					this.steps = this.$root.$app.getSteps();
				});
			}
		},
	},
	template: `
		<div class="ui-image-stack-steps">
			<template v-for="step in steps" :key="step.id">
				<Step :step="step"/>
			</template>
		</div>
	`,
};
