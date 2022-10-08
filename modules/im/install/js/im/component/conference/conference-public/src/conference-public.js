/**
 * Bitrix im
 * Pubic conference Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2021 Bitrix
 */

import 'ui.design-tokens';
import 'ui.fonts.opensans';

import {BitrixVue} from "ui.vue";
import {Vuex} from "ui.vue.vuex";
import {Utils} from "im.lib.utils";
import {
	ConferenceStateType,
	EventType,
	ConferenceErrorCode,
	ConferenceRightPanelMode as RightPanelMode
} from "im.const";
import { SendMessageHandler, ReadingHandler, ReactionHandler } from "im.event-handler";
import {ConferenceTextareaHandler} from "./event-handler/conference-textarea-handler";
import {ConferenceTextareaUploadHandler} from "./event-handler/conference-textarea-upload-handler";
import {EventEmitter} from "main.core.events";

//global components
import "im.component.dialog";
import "im.component.textarea";
import "ui.switcher";

//internal components
import {ConferenceSmiles} from './component/conference-smiles';
import {CheckDevices} from './component/check-devices';
import {Error} from "./component/error";
import {OrientationDisabled} from "./component/orientation-disabled";
import {PasswordCheck} from "./component/password-check";
import {LoadingStatus} from "./component/loading-status";
import {RequestPermissions} from "./component/request-permissions";
import {MobileChatButton} from "./component/mobile-chat-button";
import {ConferenceInfo} from "./component/conference-info";
import {UserForm} from "./component/user-form";
import {ChatHeader} from "./component/chat-header";
import {WaitingForStart} from "./component/waiting-for-start";
import {UserList} from "./component/user-list/user-list";
import {UserListHeader} from "./component/user-list/user-list-header";

//css
import "./conference-public.css";

//const
const popupModes = Object.freeze({
	preparation: 'preparation'
});

BitrixVue.component('bx-im-component-conference-public',
{
	components: {
		Error, CheckDevices, OrientationDisabled, PasswordCheck, LoadingStatus,
		RequestPermissions, MobileChatButton, ConferenceInfo, UserForm, ChatHeader, WaitingForStart, UserList, UserListHeader,
		ConferenceSmiles
	},
	props: {
		dialogId: { type: String, default: "0" }
	},
	data: function()
	{
		return {
			waitingForStart: false,
			popupMode: popupModes.preparation,
			viewPortMetaNode: null,
			chatDrag: false,
			// in %
			rightPanelSplitMode: {
				usersHeight: 50,
				chatHeight: 50,
				chatMinHeight: 30,
				chatMaxHeight: 80
			}
		};
	},
	created()
	{
		this.initEventHandlers();

		EventEmitter.subscribe(EventType.conference.waitForStart, this.onWaitForStart);
		EventEmitter.subscribe(EventType.conference.hideSmiles, this.onHideSmiles);

		if (this.isMobile())
		{
			this.setMobileMeta();
		}
		else
		{
			document.body.classList.add('bx-im-application-call-desktop-state');
		}

		if (!this.isDesktop())
		{
			window.addEventListener('beforeunload', this.onBeforeUnload.bind(this));
		}
	},
	mounted()
	{
		if (!this.isHttps())
		{
			this.getApplication().setError(ConferenceErrorCode.unsafeConnection);
		}

		if (!this.passwordChecked)
		{
			EventEmitter.emit(EventType.conference.setPasswordFocus);
		}
	},
	beforeDestroy()
	{
		this.destroyHandlers();

		EventEmitter.unsubscribe(EventType.conference.waitForStart, this.onWaitForStart);
		EventEmitter.unsubscribe(EventType.conference.hideSmiles, this.onHideSmiles);

		clearInterval(this.durationInterval);
	},
	computed:
	{
		EventType: () => EventType,
		RightPanelMode: () => RightPanelMode,
		userId()
		{
			return this.application.common.userId;
		},
		dialogInited()
		{
			if (this.dialog)
			{
				return this.dialog.init;
			}
		},
		conferenceStarted()
		{
			return this.conference.common.conferenceStarted;
		},
		userInited()
		{
			return this.conference.common.inited;
		},
		userHasRealName()
		{
			if (this.user)
			{
				return this.user.name !== this.localize['BX_IM_COMPONENT_CALL_DEFAULT_USER_NAME'];
			}

			return false;
		},
		rightPanelMode()
		{
			return this.conference.common.rightPanelMode;
		},
		userListClasses()
		{
			const result = [];
			if (this.rightPanelMode === 'split')
			{
				result.push('bx-im-component-call-right-top');
			}
			else if (this.rightPanelMode === 'users')
			{
				result.push('bx-im-component-call-right-full');
			}

			return result;
		},
		userListStyles()
		{
			if (this.rightPanelMode !== RightPanelMode.split)
			{
				return {};
			}

			return {
				height: `${this.rightPanelSplitMode.usersHeight}%`
			}
		},
		chatClasses()
		{
			const result = [];
			if (this.rightPanelMode === 'split')
			{
				result.push('bx-im-component-call-right-bottom');
			}
			else if (this.rightPanelMode === 'chat')
			{
				result.push('bx-im-component-call-right-full');
			}

			return result;
		},
		chatStyles()
		{
			if (this.rightPanelMode !== RightPanelMode.split)
			{
				return {};
			}

			return {
				height: `${this.rightPanelSplitMode.chatHeight}%`
			}
		},
		isChatShowed()
		{
			return this.conference.common.showChat;
		},
		isPreparationStep()
		{
			return this.conference.common.state === ConferenceStateType.preparation;
		},
		isBroadcast()
		{
			return this.conference.common.isBroadcast;
		},
		presentersList()
		{
			return this.conference.common.presenters;
		},
		isCurrentUserPresenter()
		{
			return this.presentersList.includes(this.userId);
		},
		errorCode()
		{
			return this.conference.common.error;
		},
		passwordChecked()
		{
			return this.conference.common.passChecked;
		},
		permissionsRequested()
		{
			return this.conference.common.permissionsRequested;
		},
		callContainerClasses()
		{
			return [this.conference.common.callEnded ? 'with-clouds': ''];
		},
		wrapClasses()
		{
			const classes = ['bx-im-component-call-wrap'];

			if (this.isMobile() && this.isBroadcast && !this.isCurrentUserPresenter && this.isPreparationStep)
			{
				classes.push('bx-im-component-call-mobile-viewer-mode');
			}

			return classes;
		},
		chatId()
		{
			if (this.application)
			{
				return this.application.dialog.chatId;
			}

			return 0;
		},
		localize()
		{
			return BitrixVue.getFilteredPhrases(['BX_IM_COMPONENT_CALL_', 'IM_DIALOG_CLIPBOARD_']);
		},
		...Vuex.mapState({
			conference: state => state.conference,
			application: state => state.application,
			user: state => state.users.collection[state.application.common.userId],
			dialog: state => state.dialogues.collection[state.application.dialog.dialogId]
		})
	},
	watch:
	{
		isChatShowed(newValue)
		{
			if (this.isMobile())
			{
				return false;
			}

			if (newValue === true)
			{
				this.$nextTick(() => {
					EventEmitter.emit(EventType.dialog.scrollOnStart, {chatId: this.chatId});
					EventEmitter.emit(EventType.textarea.setFocus);
				});
			}
		},
		rightPanelMode(newValue)
		{
			if (newValue === RightPanelMode.chat || newValue === RightPanelMode.split)
			{
				this.$nextTick(() => {
					EventEmitter.emit(EventType.dialog.scrollOnStart, {chatId: this.chatId});
					EventEmitter.emit(EventType.textarea.setFocus);
				});
			}
		},
		dialogInited(newValue)
		{
			if (newValue === true)
			{
				this.getApplication().setDialogInited();
			}
		},
		//to skip request permissions step in desktop
		userInited(newValue)
		{
			if (newValue === true && this.isDesktop() && this.passwordChecked)
			{
				this.$nextTick(() => {
					EventEmitter.emit(EventType.conference.requestPermissions);
				});
			}
		},
		user()
		{
			if (this.user && this.userHasRealName)
			{
				this.getApplication().setUserWasRenamed();
			}
		}
	},
	methods:
	{
		initEventHandlers()
		{
			this.sendMessageHandler = new SendMessageHandler(this.$Bitrix);
			this.textareaHandler = new ConferenceTextareaHandler(this.$Bitrix);
			this.readingHandler = new ReadingHandler(this.$Bitrix);
			this.reactionHandler = new ReactionHandler(this.$Bitrix);
			this.textareaUploadHandler = new ConferenceTextareaUploadHandler(this.$Bitrix);
		},
		destroyHandlers()
		{
			this.sendMessageHandler.destroy();
			this.textareaHandler.destroy();
			this.readingHandler.destroy();
			this.reactionHandler.destroy();
			this.textareaUploadHandler.destroy();
		},
		onHideSmiles()
		{
			this.getApplication().toggleSmiles();
		},
		onBeforeUnload(event)
		{
			if (!this.getApplication().callView)
			{
				return;
			}

			if (!this.isPreparationStep)
			{
				event.preventDefault();
                event.returnValue = '';
			}
		},
		onSmilesSelectSmile(event)
		{
			EventEmitter.emit(EventType.textarea.insertText, { text: event.text });
		},
		onSmilesSelectSet()
		{
			EventEmitter.emit(EventType.textarea.setFocus);
		},
		onWaitForStart()
		{
			this.waitingForStart = true;
		},
		onChatStartDrag(event)
		{
			if (this.chatDrag)
			{
				return;
			}

			this.chatDrag = true;

			this.chatDragStartPoint = event.clientY;
			this.chatDragStartHeight = this.rightPanelSplitMode.chatHeight;

			this.addChatDragEvents();
		},
		onChatContinueDrag(event)
		{
			if (!this.chatDrag)
			{
				return;
			}

			this.chatDragControlPoint = event.clientY;
			const availableHeight = document.body.clientHeight;

			const maxHeightInPx = availableHeight * (this.rightPanelSplitMode.chatMaxHeight / 100);
			const minHeightInPx = availableHeight * (this.rightPanelSplitMode.chatMinHeight / 100)
			const startHeightInPx = availableHeight * (this.chatDragStartHeight / 100);
			const chatHeightInPx = Math.max(
				Math.min(startHeightInPx + this.chatDragStartPoint - this.chatDragControlPoint, maxHeightInPx),
				minHeightInPx
			);

			const chatHeight = (chatHeightInPx / availableHeight) * 100;

			if (this.rightPanelSplitMode.chatHeight !== chatHeight)
			{
				this.rightPanelSplitMode.chatHeight = chatHeight;
				this.rightPanelSplitMode.usersHeight = 100 - chatHeight;
			}
		},
		onChatStopDrag(event)
		{
			if (!this.chatDrag)
			{
				return;
			}

			this.chatDrag = false;
			this.removeChatDragEvents();
			EventEmitter.emit(EventType.dialog.scrollToBottom, {chatId: this.chatId, force: true});
		},
		addChatDragEvents()
		{
			document.addEventListener('mousemove', this.onChatContinueDrag);
			document.addEventListener('mouseup', this.onChatStopDrag);
			document.addEventListener('mouseleave', this.onChatStopDrag);
		},
		removeChatDragEvents()
		{
			document.removeEventListener('mousemove', this.onChatContinueDrag);
			document.removeEventListener('mouseup', this.onChatStopDrag);
			document.removeEventListener('mouseleave', this.onChatStopDrag);
		},

		isMobile()
		{
			return Utils.device.isMobile();
		},
		isDesktop()
		{
			return Utils.platform.isBitrixDesktop();
		},
		setMobileMeta()
		{
			if (!this.viewPortMetaNode)
			{
				this.viewPortMetaNode = document.createElement('meta');
				this.viewPortMetaNode.setAttribute('name', 'viewport');
				this.viewPortMetaNode.setAttribute("content", "width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0");
				document.head.appendChild(this.viewPortMetaNode);
			}

			document.body.classList.add('bx-im-application-call-mobile-state');

			if (Utils.browser.isSafariBased())
			{
				document.body.classList.add('bx-im-application-call-mobile-safari-based');
			}
		},
		isHttps()
		{
			return location.protocol === 'https:';
		},
		getUserHash()
		{
			return this.conference.user.hash;
		},
		getApplication()
		{
			return this.$Bitrix.Application.get();
		},
		/* endregion 03. Helpers */
	},
	template: `
	<div :class="wrapClasses">
		<div class="bx-im-component-call">
			<div class="bx-im-component-call-left">
				<div id="bx-im-component-call-container" :class="callContainerClasses"></div>
				<div v-if="isPreparationStep" class="bx-im-component-call-left-preparation">
					<!-- Step 1: Errors page -->
					<Error v-if="errorCode"/>
					<!-- Step 2: Password page -->
					<PasswordCheck v-else-if="!passwordChecked"/>
					<template v-else-if="!errorCode && passwordChecked">
						<!-- Step 3: Loading page -->
						<LoadingStatus v-if="!userInited"/>
						<template v-else-if="userInited">
							<!-- BROADCAST MODE -->
						  	<template v-if="isBroadcast">
						  		<template v-if="!isDesktop() && !permissionsRequested && isCurrentUserPresenter">
									<ConferenceInfo/>
									<RequestPermissions>
										<template v-if="isMobile()">
											<MobileChatButton/>
										</template>
									</RequestPermissions>
								</template>
								<!-- Skip permissions request for desktop and show button with loader  -->
								<template v-if="isDesktop() && (!permissionsRequested || !user) && isCurrentUserPresenter">
									<ConferenceInfo/>
									<RequestPermissions :skipRequest="true"/>
								</template>
								<!-- Step 5: Page with video and mic check -->
								<div v-if="permissionsRequested || !isCurrentUserPresenter" class="bx-im-component-call-video-step-container">
									<!-- Compact conference info -->
									<ConferenceInfo :compactMode="true"/>
									<CheckDevices v-if="isCurrentUserPresenter" />
									<!-- Bottom part of interface -->
									<div class="bx-im-component-call-bottom-container">
										<UserForm v-if="!waitingForStart"/>
										<WaitingForStart v-else>
											<template v-if="isMobile()">
												<MobileChatButton/>
											</template>
										</WaitingForStart>
									</div>
								</div>
							</template>
							<!-- END BROADCAST MODE -->
							<!-- NORMAL MODE (NOT BROADCAST) -->
						  	<template v-else-if="!isBroadcast">
								<!-- Step 4: Permissions page -->
								<template v-if="!isDesktop() && !permissionsRequested">
									<ConferenceInfo/>
									<RequestPermissions>
										<template v-if="isMobile()">
											<MobileChatButton/>
										</template>
									</RequestPermissions>
								</template>
								<!-- Skip permissions request for desktop and show button with loader  -->
								<template v-if="isDesktop() && (!permissionsRequested || !user)">
									<ConferenceInfo/>
									<RequestPermissions :skipRequest="true"/>
								</template>
								<!-- Step 5: Page with video and mic check -->
								<div v-else-if="permissionsRequested" class="bx-im-component-call-video-step-container">
									<!-- Compact conference info -->
									<ConferenceInfo :compactMode="true"/>
									<CheckDevices/>
									<!-- Bottom part of interface -->
									<div class="bx-im-component-call-bottom-container">
										<UserForm v-if="!waitingForStart"/>
										<WaitingForStart v-else>
											<template v-if="isMobile()">
												<MobileChatButton/>
											</template>
										</WaitingForStart>
									</div>
								</div>
							</template>
							<!-- END NORMAL MODE (NOT BROADCAST) -->
						</template>
					</template>
				</div>
			</div>
			<template v-if="userInited && !errorCode">
				<transition :name="!isMobile()? 'videoconf-chat-slide': ''">
					<div v-show="rightPanelMode !== RightPanelMode.hidden" class="bx-im-component-call-right">
						<!-- Start users list -->
						<div v-show="rightPanelMode === RightPanelMode.split || rightPanelMode === RightPanelMode.users" :class="userListClasses" :style="userListStyles">
							<UserListHeader />
							<div class="bx-im-component-call-right-users">
								<UserList />
							</div>
						</div>
						<!-- End users list -->
						<!-- Start chat -->
						<div v-show="rightPanelMode === RightPanelMode.split || rightPanelMode === RightPanelMode.chat" :class="chatClasses" :style="chatStyles">
							<!-- Resize handler -->
							<div
								v-if="rightPanelMode === RightPanelMode.split"
								@mousedown="onChatStartDrag"
								class="bx-im-component-call-right-bottom-resize-handle"
							></div>
							<ChatHeader />
							<div class="bx-im-component-call-right-chat">
								<bx-im-component-dialog
									:userId="userId"
									:dialogId="dialogId"
								/>
								<keep-alive include="bx-im-component-call-smiles">
									<ConferenceSmiles
										v-if="conference.common.showSmiles"
										@selectSmile="onSmilesSelectSmile"
										@selectSet="onSmilesSelectSet"
									/>
								</keep-alive>
								<div v-if="user" class="bx-im-component-call-textarea">
									<bx-im-component-textarea
										:userId="userId"
										:dialogId="dialogId"
										:writesEventLetter="3"
										:enableFile="true"
										:enableEdit="true"
										:enableCommand="false"
										:enableMention="false"
										:autoFocus="true"
									/>
								</div>
							</div>
						<!-- End chat -->
						</div>
					</div>
				</transition>
			</template>
		</div>
	</div>
	`
});
