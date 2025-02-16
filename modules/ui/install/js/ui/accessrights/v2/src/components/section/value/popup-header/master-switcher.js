import { Switcher } from 'ui.vue3.components.switcher';
import '../../../../css/value/popup-header/master-switcher.css';

export const MasterSwitcher = {
	name: 'MasterSwitcher',
	components: { Switcher },
	emits: ['check', 'uncheck'],
	props: {
		isChecked: {
			type: Boolean,
			required: true,
		},
	},
	inject: ['section', 'right'],
	computed: {
		switcherOptions(): Object {
			return {
				size: 'small',
				color: 'green',
			};
		},
	},
	template: `
		<div class="ui-access-rights-v2-cell-popup-header-master-switcher" :class="{
			'--checked': isChecked,
		}">
			<slot/>
			<div class="ui-access-rights-v2-cell-popup-header-toggle-container">
				<span class="ui-access-rights-v2-cell-popup-header-toggle-caption">{{
					$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ACCESS')
				}}</span>
				<Switcher
					:is-checked="isChecked"
					@check="$emit('check')"
					@uncheck="$emit('uncheck')"
					:options="switcherOptions"
					data-accessrights-min-max
				/>
			</div>
		</div>
	`,
};
