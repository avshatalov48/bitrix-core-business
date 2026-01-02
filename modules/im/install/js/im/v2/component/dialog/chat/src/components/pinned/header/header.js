import { HeaderTitle } from './title';
import { HeaderPin } from './header-pin';

import './css/header.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const PinnedHeader = {
	name: 'PinnedHeader',
	components:
	{
		HeaderTitle,
		HeaderPin,
	},
	props:
	{
		message: {
			type: Object,
			required: true,
		},
		messagePosition: {
			type: Number,
			required: true,
		},
		showUnpinIcon: {
			type: Boolean,
			required: true,
		},
		totalPinCounter: {
			type: Number,
			required: true,
		},
		isListOpened: {
			type: Boolean,
			required: true,
		},
	},
	emits: ['toggleList', 'messageUnpin', 'messageClick'],
	computed:
	{
		typedMessage(): ImModelMessage
		{
			return this.message;
		},
	},
	template: `
		<div class="bx-im-dialog-chat__pinned_header">
			<HeaderTitle
				v-if="isListOpened"
				:totalPinCounter="totalPinCounter"
				@toggleList="$emit('toggleList')"
			/>
			<HeaderPin
				v-else
				:message="typedMessage"
				:messagePosition="messagePosition"
				:totalPinCounter="totalPinCounter"
				:showUnpinIcon="showUnpinIcon"
				@toggleList="$emit('toggleList')"
				@messageUnpin="$emit('messageUnpin', typedMessage.id)"
				@messageClick="$emit('messageClick', typedMessage.id)"
			/>
		</div>
	`,
};
