import { ChatHint } from 'im.v2.component.elements';

// @vue/component
export const UserSelector = {
	name: 'UserSelector',
	components: { ChatHint },
	props:
	{
		title: {
			type: String,
			required: true,
		},
		hintText: {
			type: String,
			required: false,
			default: '',
		},
	},
	template: `
		<div class="bx-im-content-create-chat__section_block">
			<div class="bx-im-content-create-chat__section-header">
				<div class="bx-im-content-create-chat__section-heading">
					{{ title }}
				</div>
				<ChatHint v-if="hintText" :text="hintText" />
			</div>
			<slot></slot>
		</div>
	`,
};
