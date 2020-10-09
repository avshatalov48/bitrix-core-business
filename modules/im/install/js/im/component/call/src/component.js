/**
 * Bitrix im
 * Pubic call vue component
 *
 * @package bitrix
 * @subpackage mobile
 * @copyright 2001-2019 Bitrix
 */

import {Vue} from "ui.vue";
import {Vuex} from "ui.vue.vuex";
import {Logger} from "im.lib.logger";
import {CallStateType, CallErrorCode, EventType} from "im.const";

//global components
import "im.view.textarea";

//internal components
import './component/bx-im-component-call-smiles';
import './component/bx-im-component-call-check-devices';

//css
import "./component.css";

const popupModes = Object.freeze({
	preparation: 'preparation',
	checkDevices: 'checkDevices'
});

/**
 * @notice Do not mutate or clone this component! It is under development.
 */
Vue.component('bx-im-component-call',
{
	props:
	{
		chatId: { default: 0 },
	},
	data: function()
	{
		return {
			userNewName: '',
			isSettingNewName: false,
			popupMode: popupModes.preparation
		};
	},
	created()
	{
		window.addEventListener('beforeunload', this.onBeforeUnload.bind(this));
	},
	watch:
	{
		showChat(newValue)
		{
			if (newValue === true)
			{
				this.$nextTick(() => {
					this.$root.$emit(EventType.textarea.focus);
					this.$root.$emit(EventType.dialog.scrollToBottom);
				});
			}

			if (this.user && !this.userHasRealName)
			{
				this.$nextTick(() => {
					this.$refs.nameInput.focus();
				});
			}
		},
		dialogInited(newValue)
		{
			if (newValue === true)
			{
				this.$root.$bitrixApplication.setDialogInited();
			}
		}
	},
	computed:
	{
		EventType: () => EventType,
		userId()
		{
			return this.application.common.userId;
		},
		dialogId()
		{
			return this.application.dialog.dialogId;
		},
		dialogInited()
		{
			if (this.dialog)
			{
				return this.dialog.init;
			}
		},
		dialogName()
		{
			if (this.dialog)
			{
				return this.dialog.name;
			}
		},
		userInited()
		{
			return this.callApplication.common.inited;
		},
		userHasRealName()
		{
			if (this.user)
			{
				return this.user.name !== this.localize['BX_IM_COMPONENT_CALL_DEFAULT_USER_NAME'];
			}
			return false;
		},
		showChat()
		{
			return this.callApplication.common.showChat;
		},
		userCounter()
		{
			return this.callApplication.common.userCount;
		},
		userInCallCounter()
		{
			return this.callApplication.common.userInCallCount;
		},
		isPreparationStep()
		{
			return this.callApplication.common.state === CallStateType.preparation;
		},
		isPopupPreparation()
		{
			return this.popupMode === popupModes.preparation;
		},
		isPopupCheckDevices()
		{
			return this.popupMode === popupModes.checkDevices;
		},
		callError()
		{
			return this.callApplication.common.callError;
		},
		noSignalFromCamera()
		{
			return this.callError === CallErrorCode.noSignalFromCamera;
		},
		newNameButtonClasses()
		{
			return ['ui-btn' ,'ui-btn-sm' ,'ui-btn-success-dark', 'ui-btn-no-caps', {'ui-btn-wait': this.isSettingNewName}, {'ui-btn-disabled': this.isSettingNewName}];
		},
		startCallButtonClasses()
		{
			return ['ui-btn', 'ui-btn-sm', 'ui-btn-success-dark', 'ui-btn-no-caps', 'bx-im-component-call-left-preparation-buttons-start'];
		},
		reloadButtonClasses()
		{
			return ['ui-btn', 'ui-btn-sm', 'ui-btn-no-caps', 'bx-im-component-call-left-preparation-buttons-reload'];
		},
		checkDevicesButtonClasses()
		{
			return ['ui-btn', 'ui-btn-sm', 'ui-btn-no-caps', 'bx-im-component-call-left-preparation-buttons-check-devices'];
		},
		localize()
		{
			return Vue.getFilteredPhrases('BX_IM_COMPONENT_CALL_', this.$root.$bitrixMessages);
		},
		...Vuex.mapState({
			callApplication: state => state.callApplication,
			application: state => state.application,
			user: state => state.users.collection[state.application.common.userId],
			dialog: state => state.dialogues.collection[state.application.dialog.dialogId]
		})
	},
	methods:
	{
		onNewNameButtonClick(name)
		{
			this.isSettingNewName = true;
			name = name.trim();
			if (name.length > 0)
			{
				this.$root.$bitrixApplication.setUserName(name);
			}
		},
		onCloseChat()
		{
			this.$root.$bitrixApplication.toggleChat();
		},
		reloadPage()
		{
			location.reload();
		},
		onTextareaSend(event)
		{
			if (!event.text)
			{
				return false;
			}

			if (this.callApplication.common.showSmiles)
			{
				this.$store.commit('callApplication/toggleSmiles');
			}

			this.$root.$bitrixApplication.addMessage(event.text);
		},
		onTextareaFileSelected(event)
		{
			let fileInput = event && event.fileInput ? event.fileInput : '';
			if (!fileInput)
			{
				return false;
			}

			if (fileInput.files[0].size > this.application.disk.maxFileSize)
			{
				// TODO alert
				//alert(this.localize.BX_LIVECHAT_FILE_SIZE_EXCEEDED.replace('#LIMIT#', Math.round(this.application.disk.maxFileSize / 1024 / 1024)));
				return false;
			}

			this.$root.$bitrixApplication.uploadFile(fileInput);
		},
		onTextareaWrites(event)
		{
			this.$root.$bitrixController.application.startWriting();
		},
		startCall()
		{
			this.$root.$bitrixApplication.startCall();
		},
		onTextareaAppButtonClick(event)
		{
			if (event.appId === 'smile')
			{
				this.$store.commit('callApplication/toggleSmiles');
			}
		},
		onBeforeUnload(event)
		{
			if (!this.isPreparationStep)
			{
				const message = this.localize['BX_IM_COMPONENT_CALL_CLOSE_CONFIRM'];

				event.returnValue = message;

				return message;
			}
		},
		hideSmiles()
		{
			this.$store.commit('callApplication/toggleSmiles');
		},
		onSmilesSelectSmile(event)
		{
			this.$root.$emit(EventType.textarea.insertText, { text: event.text });
		},
		onSmilesSelectSet(event)
		{
			console.warn('select set');
		},
		checkDevices()
		{
			this.popupMode = popupModes.checkDevices;
		},
		onCheckDevicesSave(changedValues)
		{
			this.$root.$bitrixApplication.onCheckDevicesSave(changedValues);
			this.popupMode = popupModes.preparation;
		},
		onCheckDevicesExit()
		{
			this.popupMode = popupModes.preparation;
		}
	},
	template: `
		<div class="bx-im-component-call">
			<template v-if="!userInited">
				<div class="bx-im-component-call-loading">
					<div class="bx-im-component-call-loading-text">{{ localize['BX_IM_COMPONENT_CALL_LOADING'] }}</div>
				</div>
			</template>
			<template v-else>
				<div class="bx-im-component-call-left">
					<div id="bx-im-component-call-container"></div>
					<div v-if="isPreparationStep" class="bx-im-component-call-left-preparation">
						<template v-if="isPopupPreparation">
							<template v-if="callError">
								<template v-if="noSignalFromCamera">
									<div class="bx-im-component-call-left-preparation-title">{{ localize['BX_IM_COMPONENT_CALL_ERROR_NO_SIGNAL_FROM_CAMERA'] }}</div>
									<button @click="reloadPage" :class="reloadButtonClasses">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_RELOAD'] }}</button>
								</template>
							</template>
							<template v-else-if="!callError">
								<template v-if="userInCallCounter > 0">
									<div class="bx-im-component-call-left-preparation-title">{{ localize['BX_IM_COMPONENT_CALL_PREPARE_TITLE_JOIN_CALL'] }}</div>
									<button @click="startCall" :class="startCallButtonClasses">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_JOIN'] }}</button>
									<div class="bx-im-component-call-left-preparation-user-count">{{ localize['BX_IM_COMPONENT_CALL_PREPARE_USER_COUNT'] }} {{ userInCallCounter }}</div>
								</template>
								<template v-else-if="userInCallCounter === 0 && userCounter > 1">
									<div class="bx-im-component-call-left-preparation-title">{{ localize['BX_IM_COMPONENT_CALL_PREPARE_TITLE_START_CALL'] }}</div>
									<button @click="startCall" :class="startCallButtonClasses">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_START'] }}</button>
								</template>
								<template v-else>
									<div class="bx-im-component-call-left-preparation-user-count">{{ localize['BX_IM_COMPONENT_CALL_PREPARE_NO_USERS'] }}</div>
								</template>
								<button @click="checkDevices" :class="checkDevicesButtonClasses">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_CHECK_DEVICES'] }}</button>
							</template>
						</template>
						<template v-else-if="isPopupCheckDevices">
							<bx-im-component-call-check-devices @save="onCheckDevicesSave" @exit="onCheckDevicesExit"/>
						</template>
					</div>
				</div>
				<transition name="videoconf-chat-slide">
					<div v-show="showChat" class="bx-im-component-call-right">
						<div class="bx-im-component-call-right-header">
							<div @click="onCloseChat" class="bx-im-component-call-right-header-close" :title="localize['BX_IM_COMPONENT_CALL_CHAT_CLOSE_TITLE']"></div>
							<div class="bx-im-component-call-right-header-title">{{ localize['BX_IM_COMPONENT_CALL_CHAT_TITLE'] }}</div>
						</div>
						<div class="bx-im-component-call-right-chat">
							<bx-im-component-dialog
								:userId="userId"
								:dialogId="dialogId"
							/>
							<keep-alive include="bx-im-component-call-smiles">
								<template v-if="callApplication.common.showSmiles">
									<bx-im-component-call-smiles @selectSmile="onSmilesSelectSmile" @selectSet="onSmilesSelectSet"/>	
								</template>	
							</keep-alive>
							<div v-if="user && userHasRealName" class="bx-im-component-call-textarea">
								<bx-im-view-textarea
									:userId="userId"
									:dialogId="dialogId" 
									:enableFile="true"
									:enableEdit="true"
									:enableCommand="false"
									:enableMention="false"
									:autoFocus="true"
									:listenEventInsertText="EventType.textarea.insertText"
									:listenEventFocus="EventType.textarea.focus"
									:listenEventBlur="EventType.textarea.blur"
									@send="onTextareaSend"
									@fileSelected="onTextareaFileSelected"
									@writes="onTextareaWrites"
									@appButtonClick="onTextareaAppButtonClick"
								/>
							</div>
							<div v-else-if="user && !userHasRealName" class="bx-im-component-call-textarea-guest">
								<div class="bx-im-component-call-textarea-guest-title">{{ localize['BX_IM_COMPONENT_CALL_INTRODUCE_YOURSELF'] }}</div>
								<div>
									<input ref="nameInput" v-model="userNewName" @keyup.enter="onNewNameButtonClick(userNewName)" type="text" :placeholder="localize['BX_IM_COMPONENT_CALL_INTRODUCE_YOURSELF_PLACEHOLDER']" class="bx-im-component-call-textarea-guest-input"/>
								</div>
								<div>
									<button @click="onNewNameButtonClick(userNewName)" :class="newNameButtonClasses">{{ localize['BX_IM_COMPONENT_CALL_INTRODUCE_YOURSELF_BUTTON'] }}</button>
								</div>
							</div>	
						</div>
					</div>
				</transition>
			</template>
		</div>
	`
});