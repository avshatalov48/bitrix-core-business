import { Dropdown } from 'im.v2.component.elements';

import './css/dropdown.css';

// @vue/component
export const DropdownOption = {
	name: 'DropdownOption',
	components: { Dropdown },
	props:
	{
		items: {
			type: Array,
			required: true,
		},
		text: {
			type: String,
			required: true,
		},
	},
	emits: ['change'],
	data()
	{
		return {};
	},
	template: `
		<div class="bx-im-settings-dropdown__container bx-im-settings-section-content__block_option">
			<div class="bx-im-settings-dropdown__label">{{ text }}</div>
			<div class="bx-im-settings-dropdown__width-container">
				<Dropdown :items="items" id="dropdown" @itemChange="$emit('change', $event)" />
			</div>
		</div>
	`,
};
