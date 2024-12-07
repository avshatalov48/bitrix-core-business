import { BaseMessage } from 'im.v2.component.message.base';

import './css/supervisor-base.css';

// @vue/component
export const SupervisorBaseMessage = {
	name: 'SupervisorBaseMessage',
	components: { BaseMessage },
	props: {
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		title: {
			type: String,
			required: false,
			default: '',
		},
		description: {
			type: String,
			required: false,
			default: '',
		},
	},
	template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withBackground="false"
			:withContextMenu="false"
			:withReactions="false"
			class="bx-im-message-supervisor-base__scope"
		>
			<div class="bx-im-message-supervisor-base__container">
				<slot name="image" />
				<div class="bx-im-message-supervisor-base__content">
					<div class="bx-im-message-supervisor-base__title">
						{{ title }}
					</div>
					<div class="bx-im-message-supervisor-base__description">
						{{ description }}
					</div>
					<div class="bx-im-message-supervisor-base__buttons_container">
						<slot name="actions" />
					</div>
				</div>
			</div>
		</BaseMessage>
	`,
};
