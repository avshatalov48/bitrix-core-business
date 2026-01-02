import { Button as UiButton, AirButtonStyle, ButtonSize } from 'ui.vue3.components.button';
import { Outline } from 'ui.icon-set.api.core';
import { BIcon } from 'ui.icon-set.api.vue';
import 'ui.icon-set.outline';

export const Step = {
	components: {
		BIcon,
		UiButton,
	},
	props: {
		title: {
			type: String,
			required: true,
		},
		primaryButtonText: {
			type: String,
			required: true,
		},
		alertText: {
			type: String,
			default: '',
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
	template: `
		<div class="socialnetwork-collab-converter-wizard-step">
			<div class="socialnetwork-collab-converter-wizard-step-content">
				<div class="socialnetwork-collab-converter-wizard-step-title">
					{{ title }}
				</div>
				<slot/>
			</div>
			<div class="socialnetwork-collab-converter-wizard-step-footer">
				<div v-if="alertText.length > 0" class="socialnetwork-collab-converter-wizard-step-footer-alert">
					<BIcon :name="Outline.ALERT" :size="16" :color="'var(--ui-color-accent-main-warning, #FAA72C)'"/>
					<div class="socialnetwork-collab-converter-wizard-step-footer-alert-text" v-html="alertText"/>
				</div>
				<div class="socialnetwork-collab-converter-wizard-step-footer-buttons">
					<UiButton
						:text="primaryButtonText"
						:size="ButtonSize.LARGE"
						@click="$emit('continue')"
						data-test-id="socialnetwork-collab-converter-continue-button"
					/>
					<UiButton
						:text="loc('SN_COLLAB_CONVERTER_CANCEL')"
						:size="ButtonSize.LARGE"
						:style="AirButtonStyle.OUTLINE"
						@click="$emit('close')"
						data-test-id="socialnetwork-collab-converter-cancel-button"
					/>
				</div>
			</div>
		</div>
	`,
};
