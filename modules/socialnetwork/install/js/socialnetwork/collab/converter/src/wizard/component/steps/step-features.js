import { Outline, BIcon } from 'ui.icon-set.api.core';
import { Button as UiButton, AirButtonStyle, ButtonSize } from 'ui.vue3.components.button';
import 'ui.icon-set.outline';

import { Advantage } from './advantage';
import { Step } from './step';

import type { Feature } from '../../../service/types';

export const StepFeatures = {
	components: {
		UiButton,
		Advantage,
		Step,
		BIcon,
	},
	props: {
		features: {
			/** @type Array<Feature> */
			type: Array,
			required: true,
		},
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
	methods: {
		getFeatureName(featureCode: string): string
		{
			const phrase = {
				[featureCode === 'group_lists']: 'SN_COLLAB_CONVERTER_FEATURE_GROUP_LISTS',
				[featureCode === 'forum']: 'SN_COLLAB_CONVERTER_FEATURE_FORUM',
				[featureCode === 'photo']: 'SN_COLLAB_CONVERTER_FEATURE_PHOTO',
				[featureCode === 'wiki']: 'SN_COLLAB_CONVERTER_FEATURE_WIKI',
				[featureCode === 'marketplace']: 'SN_COLLAB_CONVERTER_FEATURE_MARKETPLACE',
				[featureCode === 'landing_knowledge']: 'SN_COLLAB_CONVERTER_FEATURE_LANDING_KNOWLEDGE',
			}.true;

			return this.loc(phrase);
		},
		handleLinkMoreClick(): void
		{
			BX.Helper.show('redirect=detail&code=25356654#tool');
		},
	},
	template: `
		<Step
			:title="loc('SN_COLLAB_CONVERTER_STEP_FEATURES_TITLE')"
			:primaryButtonText="loc('SN_COLLAB_CONVERTER_START')"
			:alertText="loc('SN_COLLAB_CONVERTER_ALERT')"
			class="--features"
			@continue="$emit('continue')"
			@close="$emit('close')"
		>
			<div class="socialnetwork-collab-converter-wizard-step-subtitle">
				<div class="socialnetwork-collab-converter-wizard-step-subtitle-text" v-html="loc('SN_COLLAB_CONVERTER_STEP_FEATURES_SUBTITLE')"/>
				<div class="socialnetwork-collab-converter-wizard-step-subtitle-link" @click="handleLinkMoreClick">
					{{ loc('SN_COLLAB_CONVERTER_LINK_MORE') }}
				</div>
			</div>
			<div class="socialnetwork-collab-converter-wizard-features">
				<div v-for="feature in features" class="socialnetwork-collab-converter-wizard-feature">
					<BIcon :name="Outline.CIRCLE_MINUS" :color="'var(--ui-color-base-6, #DFE0E3)'" :size="20"/>
					<div class="socialnetwork-collab-converter-wizard-feature-name">
						{{ getFeatureName(feature.name) }}
					</div>
				</div>
			</div>
		</Step>
	`,
};
