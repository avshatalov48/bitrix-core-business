import { Outline } from 'ui.icon-set.api.core';
import { Button as UiButton, AirButtonStyle, ButtonSize } from 'ui.vue3.components.button';
import 'ui.icon-set.outline';

import { Advantage } from './advantage';
import { Step } from './step';

export const StepAdvantages = {
	components: {
		UiButton,
		Advantage,
		Step,
	},
	setup(): Object
	{
		return {
			AirButtonStyle,
			ButtonSize,
		};
	},
	emits: ['continue', 'close'],
	computed: {
		advantages(): Array<Object>
		{
			return [
				{
					icon: Outline.MESSAGES,
					iconColor: 'var(--ui-color-accent-main-primary, #0075FF)',
					title: this.loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_ADVANTAGE_CHAT_TITLE'),
					description: this.loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_ADVANTAGE_CHAT_DESCRIPTION'),
				},
				{
					icon: Outline.SUITCASE,
					iconColor: 'var(--ui-color-accent-extra-aqua, #37C5D8)',
					title: this.loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_ADVANTAGE_DISCUSSION_TITLE'),
					description: this.loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_ADVANTAGE_DISCUSSION_DESCRIPTION'),
				},
				{
					icon: Outline.WINDOW_FLAG,
					iconColor: 'var(--ui-color-accent-soft-element-green, #02BB9A)',
					title: this.loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_ADVANTAGE_FAST_TITLE'),
					description: this.loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_ADVANTAGE_FAST_DESCRIPTION'),
				},
			];
		},
	},
	template: `
		<Step
			:title="loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_TITLE')"
			:primaryButtonText="loc('SN_COLLAB_CONVERTER_CONTINUE')"
			@continue="$emit('continue')"
			@close="$emit('close')"
		>
			<div class="socialnetwork-collab-converter-wizard-advantages">
				<Advantage
					v-for="(advantage, index) in advantages"
					:icon="advantage.icon"
					:iconColor="advantage.iconColor"
					:title="advantage.title"
					:description="advantage.description"
					:style="{'--i': index}"
				/>
			</div>
		</Step>
	`,
};
