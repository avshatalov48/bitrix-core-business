import { Dropdown, ChatHint } from 'im.v2.component.elements';

import '../css/role-selector.css';

// @vue/component
export const RoleSelector = {
	name: 'RoleSelector',
	components: { Dropdown, ChatHint },
	props:
	{
		title: {
			type: String,
			required: true,
		},
		dropdownItems: {
			type: Array,
			required: true,
		},
		dropdownId: {
			type: String,
			required: true,
		},
		hintText: {
			type: String,
			required: false,
			default: '',
		},
	},
	emits: ['itemChange'],
	template: `
		<div class="bx-im-content-create-chat__section_block">
			<div class="bx-im-content-create-chat__section-header">
				<div class="bx-im-content-create-chat__section-heading">
					{{ title }}
				</div>
				<ChatHint v-if="hintText" :text="hintText" />
			</div>
			<div class="bx-im-chat-forms-chat-settings__manage-select">
				<Dropdown
					:items="dropdownItems"
					:id="dropdownId" 
					@itemChange="$emit('itemChange', $event)"
				/>
			</div>
		</div>
	`,
};
