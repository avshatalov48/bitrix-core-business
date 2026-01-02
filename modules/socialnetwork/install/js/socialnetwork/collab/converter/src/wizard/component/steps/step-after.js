import { Outline } from 'ui.icon-set.api.core';
import { Button as UiButton, AirButtonStyle, ButtonSize } from 'ui.vue3.components.button';
import 'ui.icon-set.outline';

import { Advantage } from './advantage';
import { Step } from './step';

export const StepAfter = {
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
			Outline,
		};
	},
	emits: ['continue', 'close'],
	computed: {
		advantages(): Array<Object>
		{
			return [
				{
					icon: Outline.SHUFFLE,
					iconColor: 'var(--ui-color-collab-accent-primary-alt, #00A94E)',
					title: this.loc('SN_COLLAB_CONVERTER_STEP_AFTER_ADVANTAGE_COLLABORATION_TITLE'),
				},
				{
					icon: Outline.TASK_LIST,
					iconColor: 'var(--ui-color-collab-accent-primary-alt, #00A94E)',
					title: this.loc('SN_COLLAB_CONVERTER_STEP_AFTER_ADVANTAGE_DISCUSSION_TITLE'),
				},
				{
					icon: Outline.CHATS_WITH_CHECK,
					iconColor: 'var(--ui-color-collab-accent-primary-alt, #00A94E)',
					title: this.loc('SN_COLLAB_CONVERTER_STEP_AFTER_ADVANTAGE_FAST_TITLE'),
				},
			];
		},
	},
	template: `
		<Step
			:title="loc('SN_COLLAB_CONVERTER_STEP_AFTER_TITLE')"
			:primaryButtonText="loc('SN_COLLAB_CONVERTER_START')"
			:alertText="loc('SN_COLLAB_CONVERTER_ALERT')"
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
