import { Parser } from 'im.v2.lib.parser';

import { CounterControl } from './counter-control';

import './css/header-pin.css';

import type { ImModelMessage, ImModelUser } from 'im.v2.model';

// @vue/component
export const HeaderPin = {
	name: 'HeaderPin',
	components:
	{
		CounterControl,
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
	},
	emits: ['toggleList', 'messageUnpin', 'messageClick'],
	computed:
	{
		typedMessage(): ImModelMessage
		{
			return this.message;
		},
		isSinglePin(): boolean
		{
			return this.totalPinCounter === 1;
		},
		authorId(): number
		{
			return this.typedMessage.authorId;
		},
		author(): ImModelUser
		{
			return this.$store.getters['users/get'](this.authorId);
		},
		text(): string
		{
			return Parser.purifyMessage(this.typedMessage);
		},
		title(): string
		{
			return this.loc(
				this.isSinglePin
					? 'IM_DIALOG_CHAT_PINNED_TITLE'
					: 'IM_DIALOG_CHAT_PINNED_TITLE_MULTIPLE',
			);
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-dialog-chat__pin-header">
			<div
				class="bx-im-dialog-chat__pin-header_wrapper"
				@click="$emit('messageClick', typedMessage.id)"
			>
				<div class="bx-im-dialog-chat__pin-header_title">
					{{ title }}
				</div>
				<div class="bx-im-dialog-chat__pin-header_content">
					<div v-if="author" class="bx-im-dialog-chat__pin-header_user">
						{{ author.name + ':' }}
					</div>
					<div class="bx-im-dialog-chat__pin-header_text --ellipsis">
						{{ text }}
					</div>
				</div>
			</div>

			<div class="bx-im-dialog-chat__pin-header_controls">
				<button
					v-if="showUnpinIcon && isSinglePin"
					class="bx-im-dialog-chat__pinned_icon-header-unpin"
					@click="$emit('messageUnpin', typedMessage.id)"
				></button>
				<CounterControl
					v-else-if="!isSinglePin"
					:messagePosition="messagePosition"
					:totalPinCounter="totalPinCounter"
					@toggleList="$emit('toggleList')"
				/>
			</div>
		</div>
	`,
};
