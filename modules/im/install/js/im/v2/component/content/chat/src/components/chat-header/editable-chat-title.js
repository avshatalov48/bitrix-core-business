import {ChatTitle} from 'im.v2.component.elements';
import {ChatOption} from 'im.v2.const';

import type {ImModelDialog} from 'im.v2.model';

const INPUT_PADDING = 5;

// @vue/component
export const EditableChatTitle = {
	name: 'EditableChatTitle',
	components: {ChatTitle},
	props:
	{
		dialogId: {
			type: String,
			required: true
		}
	},
	emits: ['newTitleSubmit'],
	data()
	{
		return {
			isEditing: false,
			inputWidth: 0,
			showEditIcon: false,
			chatTitle: ''
		};
	},
	computed:
	{
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		canBeRenamed(): boolean
		{
			if (this.dialog.extranet)
			{
				return false;
			}

			return this.$store.getters['dialogues/getChatOption'](this.dialog.type, ChatOption.rename);
		},
		inputStyle()
		{
			return {
				width: `calc(${this.inputWidth}ch + ${INPUT_PADDING}px)`
			};
		}
	},
	watch:
	{
		chatTitle()
		{
			this.inputWidth = this.chatTitle.length;
		}
	},
	mounted()
	{
		this.chatTitle = this.dialog.name;
	},
	methods:
	{
		onTitleClick()
		{
			if (!this.canBeRenamed)
			{
				return;
			}

			if (!this.chatTitle)
			{
				this.chatTitle = this.dialog.name;
			}

			this.isEditing = true;
			this.$nextTick().then(() => {
				this.$refs['titleInput'].focus();
			});
		},
		onNewTitleSubmit()
		{
			if (!this.isEditing)
			{
				return;
			}
			this.isEditing = false;

			const nameNotChanged = this.chatTitle === this.dialog.name;
			if (nameNotChanged || this.chatTitle === '')
			{
				return;
			}

			this.$emit('newTitleSubmit', this.chatTitle);
		},
		onEditCancel()
		{
			this.isEditing = false;
			this.chatTitle = this.dialog.name;
		},
	},
	template: `
		<div
			v-if="!isEditing"
			@click="onTitleClick"
			@mouseover="showEditIcon = true"
			@mouseleave="showEditIcon = false"
			class="bx-im-chat-header__title --chat"
		>
			<div class="bx-im-chat-header__title_container">
				<ChatTitle :dialogId="dialogId" :withMute="true" />
			</div>
			<div class="bx-im-chat-header__edit-icon_container">
				<div v-if="showEditIcon && canBeRenamed" class="bx-im-chat-header__edit-icon"></div>
			</div>
		</div>
		<div v-else class="bx-im-chat-header__title-input_container">
			<input
				v-model="chatTitle"
				:style="inputStyle"
				@focus="$event.target.select()"
				@blur="onNewTitleSubmit"
				@keyup.enter="onNewTitleSubmit"
				@keyup.esc="onEditCancel"
				type="text"
				class="bx-im-chat-header__title-input"
				ref="titleInput"
			/>
		</div>
	`
};