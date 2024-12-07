import { validateStatus } from '../helpers/validate-helpers';
import { stackStatusEnum } from '../image-stack-steps-options';
import { BIcon } from 'ui.icon-set.api.vue';

import '../css/stack-status.css';

export const StackStatus = {
	name: 'ui-image-stack-steps-step-stack-status',
	components: {
		BIcon,
	},
	props: {
		/** @var { StackStatusType } status */
		status: {
			type: Object,
			required: true,
			validator: (value) => {
				return validateStatus(value);
			},
		},
	},
	computed: {
		icon(): string
		{
			switch (this.status.type)
			{
				case stackStatusEnum.OK:
					return 'circle-check';
				case stackStatusEnum.WAIT:
					return 'black-clock';
				case stackStatusEnum.CANCEL:
					return 'cross-circle-60';
				default:
					return this.status.data.icon;
			}
		},
		color(): string
		{
			switch (this.status.type)
			{
				case stackStatusEnum.OK:
					return 'var(--ui-color-primary-alt)';
				case stackStatusEnum.WAIT:
					return 'var(--ui-color-palette-blue-60)';
				case stackStatusEnum.CANCEL:
					return 'var(--ui-color-base-35)';
				default:
					return this.status.data.color;
			}
		},
	},
	template: `
		<div class="ui-image-stack-steps-step-stack-status">
			<BIcon
				v-if="icon"
				:name="icon" :color="color" :size="24"
				class="ui-image-stack-steps-step-stack-status-icon"
			/>
			<div class="ui-image-stack-steps-step-stack-status-icon__overlay"></div>
		</div>
	`,
};
