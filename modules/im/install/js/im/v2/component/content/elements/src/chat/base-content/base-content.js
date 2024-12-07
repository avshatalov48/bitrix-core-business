import { EventEmitter, BaseEvent } from 'main.core.events';
import { computed } from 'ui.vue3';

import { ChatDialog } from 'im.v2.component.dialog.chat';
import { ChatTextarea } from 'im.v2.component.textarea';
import { ThemeManager } from 'im.v2.lib.theme';
import { PermissionManager } from 'im.v2.lib.permission';
import { ResizeManager } from 'im.v2.lib.textarea';
import { ChatSidebar } from 'im.v2.component.sidebar';
import { ChatActionType, Settings, UserRole, EventType, SidebarDetailBlock } from 'im.v2.const';

import { Height } from './const/size';
import { ChatHeader } from '../header/chat-header';
import { DropArea } from './components/drop-area';
import { MutePanel } from './components/mute-panel';
import { JoinPanel } from './components/join-panel';
import { LoadingBar } from './components/loading-bar';
import { TextareaObserverDirective } from './utils/observer-directive';

import './css/base-chat-content.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';
import type { BackgroundStyle } from 'im.v2.lib.theme';

type SidebarDetailBlockType = $Values<typeof SidebarDetailBlock>;

// @vue/component
export const BaseChatContent = {
	name: 'BaseChatContent',
	components:
	{
		ChatHeader,
		ChatDialog,
		ChatTextarea,
		ChatSidebar,
		DropArea,
		MutePanel,
		JoinPanel,
		LoadingBar,
	},
	directives: { 'textarea-observer': TextareaObserverDirective },
	provide(): { currentSidebarPanel: SidebarDetailBlockType }
	{
		return {
			currentSidebarPanel: computed(() => this.currentSidebarPanel),
		};
	},
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
		backgroundId: {
			type: [Number, null],
			default: null,
		},
	},
	data(): JsonObject
	{
		return {
			textareaHeight: 0,
			showLoadingBar: false,
			currentSidebarPanel: '',
		};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		canSend(): boolean
		{
			return PermissionManager.getInstance().canPerformAction(ChatActionType.send, this.dialog.dialogId);
		},
		isGuest(): boolean
		{
			return this.dialog.role === UserRole.guest;
		},
		hasCommentsOnTop(): boolean
		{
			return this.$store.getters['messages/comments/areOpenedForChannel'](this.dialogId);
		},
		containerClasses(): string[]
		{
			const alignment = this.$store.getters['application/settings/get'](Settings.appearance.alignment);

			return [`--${alignment}-align`];
		},
		backgroundStyle(): BackgroundStyle
		{
			if (this.backgroundId)
			{
				return ThemeManager.getBackgroundStyleById(this.backgroundId);
			}

			return ThemeManager.getCurrentBackgroundStyle();
		},
		dialogContainerStyle(): Object
		{
			let textareaHeight = this.textareaHeight;
			if (!this.canSend)
			{
				textareaHeight = Height.blockedTextarea;
			}

			return {
				height: `calc(100% - ${Height.chatHeader}px - ${textareaHeight}px)`,
			};
		},
	},
	watch:
	{
		textareaHeight(newValue, oldValue)
		{
			if (!this.dialog.inited || oldValue === 0)
			{
				return;
			}

			EventEmitter.emit(EventType.dialog.scrollToBottom, {
				chatId: this.dialog.chatId,
				animation: false,
			});
		},
	},
	created()
	{
		this.initTextareaResizeManager();
		this.bindEvents();
	},
	beforeUnmount()
	{
		this.unbindEvents();
	},
	methods:
	{
		initTextareaResizeManager()
		{
			this.textareaResizeManager = new ResizeManager();
			this.textareaResizeManager.subscribe(ResizeManager.events.onHeightChange, this.onTextareaHeightChange);
		},
		onTextareaMount()
		{
			const textareaContainer: HTMLDivElement = this.$refs['textarea-container'];
			this.textareaHeight = textareaContainer.clientHeight;
		},
		onTextareaHeightChange(event: BaseEvent<{newHeight: number}>)
		{
			const { newHeight } = event.getData();
			this.textareaHeight = newHeight;
		},
		onShowLoadingBar(event: BaseEvent<{ dialogId: string }>)
		{
			const { dialogId } = event.getData();
			if (dialogId !== this.dialogId)
			{
				return;
			}
			this.showLoadingBar = true;
		},
		onHideLoadingBar(event: BaseEvent<{ dialogId: string }>)
		{
			const { dialogId } = event.getData();
			if (dialogId !== this.dialogId)
			{
				return;
			}
			this.showLoadingBar = false;
		},
		onChangeSidebarPanel({ panel }: { panel: SidebarDetailBlockType })
		{
			this.currentSidebarPanel = panel;
		},
		bindEvents()
		{
			EventEmitter.subscribe(EventType.dialog.showLoadingBar, this.onShowLoadingBar);
			EventEmitter.subscribe(EventType.dialog.hideLoadingBar, this.onHideLoadingBar);
		},
		unbindEvents()
		{
			EventEmitter.unsubscribe(EventType.dialog.showLoadingBar, this.onShowLoadingBar);
			EventEmitter.unsubscribe(EventType.dialog.hideLoadingBar, this.onHideLoadingBar);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-content-chat__scope bx-im-content-chat__container" :class="containerClasses" :style="backgroundStyle">
			<div class="bx-im-content-chat__content" ref="content">
				<slot name="header">
					<ChatHeader :dialogId="dialogId" :key="dialogId" />
				</slot>
				<div :style="dialogContainerStyle" class="bx-im-content-chat__dialog_container">
					<Transition name="loading-bar-transition">
						<LoadingBar v-if="showLoadingBar" />
					</Transition>
					<div class="bx-im-content-chat__dialog_content">
						<slot name="dialog">
							<ChatDialog :dialogId="dialogId" :key="dialogId" />
						</slot>
					</div>
				</div>
				<!-- Textarea -->
				<div v-if="canSend" v-textarea-observer class="bx-im-content-chat__textarea_container" ref="textarea-container">
					<slot name="textarea" :onTextareaMount="onTextareaMount">
						<ChatTextarea 
							:dialogId="dialogId" 
							:key="dialogId" 
							:withAudioInput="false" 
							@mounted="onTextareaMount" 
						/>
					</slot>
				</div>
				<slot v-else-if="isGuest" name="join-panel">
					<JoinPanel :dialogId="dialogId" />
				</slot>
				<MutePanel v-else :dialogId="dialogId" />
				<!-- End textarea -->
				<DropArea :dialogId="dialogId" :container="$refs.content || {}" :key="dialogId" />
			</div>
			<ChatSidebar
				v-if="dialogId.length > 0" 
				:originDialogId="dialogId"
				:isActive="!hasCommentsOnTop"
				@changePanel="onChangeSidebarPanel"
			/>
		</div>
	`,
};
