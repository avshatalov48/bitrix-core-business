import { IconHint } from './icon-hint';

import 'ui.forms';

export const EnableWarning = {
	props: {
		text: {
			type: String,
			required: true,
		},
		hint: {
			type: String,
			required: true,
			default: '',
		},
		helpLink: {
			type: String,
			required: false,
			default: '',
		},
	},
	components: {
		IconHint,
	},
	template: `
		<div class="inventory-management__card-limit">
			<div v-html="text" class="inventory-management__card-limit-text"></div>
			<icon-hint
				v-if="hint"
				:title="hint"
				:helpLink="helpLink"
			/>
		</div>
	`,
};
