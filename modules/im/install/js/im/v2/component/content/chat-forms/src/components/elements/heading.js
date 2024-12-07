import { ChatHint } from 'im.v2.component.elements';

import './css/heading.css';

// @vue/component
export const CreateChatHeading = {
	name: 'CreateChatHeading',
	components: { ChatHint },
	props:
	{
		text: {
			type: String,
			required: true,
		},
		hintText: {
			type: String,
			required: false,
			default: '',
		},
	},
	computed:
	{
		preparedText(): string
		{
			return this.text
				.replace('#SUBTITLE_START#', '<span class="bx-im-content-create-chat__subheading">')
				.replace('#SUBTITLE_END#', '</span>');
		},
	},
	template: `
		<div class="bx-im-content-create-chat__heading_container">
			<div class="bx-im-content-create-chat__heading" v-html="preparedText"></div>
			<ChatHint v-if="hintText" :text="hintText" />
		</div>
	`,
};
