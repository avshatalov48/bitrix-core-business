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
import {Utils} from "im.lib.utils";
import {
	CallStateType,
	CallErrorCode,
	EventType,
	CallApplicationErrorCode,
	DeviceType,
	DeviceOrientation
} from "im.const";
import { MessageBox, MessageBoxButtons } from 'ui.dialogs.messagebox';

//global components
import "im.view.textarea";
import "im.component.dialog";
import "ui.switcher";

//internal components
import './component/bx-im-component-call-smiles';
import {CheckDevices} from './component/check-devices';
import {ErrorComponent} from "./component/error";
import {OrientationDisabled} from "./component/orientation-disabled";

//css
import "./component.css";

const popupModes = Object.freeze({
	preparation: 'preparation'
});

/**
 * @notice Do not mutate or clone this component! It is under development.
 */
Vue.component('bx-im-component-call',
{
	props: ['chatId'],
	data: function()
	{
		return {
			userNewName: '',
			password: '',
			checkingPassword: false,
			wrongPassword: false,
			permissionsRequested: false,
			waitingForStart: false,
			popupMode: popupModes.preparation,
			viewPortMetaNode: null,
			conferenceDuration: '',
			durationInterval: null
		};
	},
	created()
	{
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
			this.getApplication().setError(CallApplicationErrorCode.unsafeConnection);
		}

		if (!this.passwordChecked)
		{
			this.$refs['passwordInput'].focus();
		}
	},
	destroyed()
	{
		clearInterval(this.durationInterval);
	},
	watch:
	{
		showChat(newValue)
		{
			if (this.isMobile())
			{
				return false;
			}

			if (newValue === true)
			{
				this.$nextTick(() => {
					this.$root.$emit(EventType.textarea.focus);
					this.$root.$emit(EventType.dialog.scrollToBottom);
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
		conferenceStarted(newValue)
		{
			if (newValue === true)
			{
				this.durationInterval = setInterval(() => {
					this.updateConferenceDuration();
				}, 1000);
			}

			this.updateConferenceDuration();
		},
		userInited(newValue)
		{
			if (newValue === true && this.isDesktop() && this.passwordChecked)
			{
				this.requestPermissions();
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
		conferenceTitle()
		{
			return this.callApplication.common.conferenceTitle;
		},
		conferenceStarted()
		{
			return this.callApplication.common.conferenceStarted;
		},
		conferenceStartDate()
		{
			return this.callApplication.common.conferenceStartDate;
		},
		conferenceStatusClasses()
		{
			const classes = ['bx-im-component-call-info-status'];

			if (this.conferenceStarted === true)
			{
				classes.push('bx-im-component-call-info-status-active');
			}
			else
			{
				classes.push('bx-im-component-call-info-status-not-active');
			}

			return classes;
		},
		conferenceStatusText()
		{
			if (this.conferenceStarted === true)
			{
				return `${this.localize['BX_IM_COMPONENT_CALL_STATUS_STARTED']}, ${this.conferenceDuration}`;
			}
			else if (this.conferenceStarted === false)
			{
				return this.localize['BX_IM_COMPONENT_CALL_STATUS_NOT_STARTED'];
			}
			else if (this.conferenceStarted === null)
			{
				return this.localize['BX_IM_COMPONENT_CALL_STATUS_LOADING'];
			}
		},
		intranetAvatarStyle()
		{
			if (this.user && !this.user.extranet && this.user.avatar)
			{
				return {
					backgroundImage: `url('${this.user.avatar}')`
				}
			}

			return '';
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
		dialogCounter()
		{
			if (this.dialog)
			{
				return this.dialog.counter;
			}
		},
		publicLink()
		{
			if (this.dialog)
			{
				return this.dialog.public.link;
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
			return this.dialog.userCounter;
		},
		userInCallCounter()
		{
			return this.callApplication.common.userInCallCount;
		},
		isPreparationStep()
		{
			return this.callApplication.common.state === CallStateType.preparation;
		},
		error()
		{
			return this.callApplication.common.error;
		},
		passwordChecked()
		{
			return this.callApplication.common.passChecked;
		},
		mobileDisabled()
		{
			return false;
			if (this.application.device.type === DeviceType.mobile)
			{
				if (navigator.userAgent.toString().includes('iPad'))
				{
				}
				else if (this.application.device.orientation === DeviceOrientation.horizontal)
				{
					if (navigator.userAgent.toString().includes('iPhone'))
					{
						return true;
					}
					else
					{
						return !(typeof window.screen === 'object' && window.screen.availHeight >= 800);
					}
				}
			}

			return false;
		},
		logoutLink()
		{
			return `${this.publicLink}?logout=yes&sessid=${BX.bitrix_sessid()}`;
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
		/* region 01. Actions */
		setNewName()
		{
			if (this.userNewName.length > 0)
			{
				this.getApplication().setUserName(this.userNewName.trim());
			}
		},
		startCall()
		{
			this.getApplication().startCall();
		},
		hideSmiles()
		{
			this.getApplication().toggleSmiles();
		},
		checkPassword()
		{
			if (!this.password || this.checkingPassword)
			{
				this.wrongPassword = true;

				return false;
			}
			this.checkingPassword = true;
			this.wrongPassword = false;
			this.getApplication().checkPassword(this.password)
				.catch(checkResult => {
					this.wrongPassword = true;
				})
				.finally(() => {
					this.checkingPassword = false;
				});
		},
		requestPermissions()
		{
			this.getApplication().initHardware().then(() => {
				this.$nextTick(() => {
					this.permissionsRequested = true;
				});
			}).catch((error) => {
				MessageBox.show({
					message: this.localize['BX_IM_COMPONENT_CALL_HARDWARE_ERROR'],
					modal: true,
					buttons: MessageBoxButtons.OK
				});
			});
		},
		startConference({ video })
		{
			this.getApplication().startCall(video);
		},
		joinConference({ video })
		{
			if (this.user.extranet && !this.userHasRealName)
			{
				this.setNewName();
			}

			if (!this.conferenceStarted)
			{
				this.waitingForStart = true;
				this.getApplication().setUserReadyToJoin();
				this.getApplication().setJoinType(video);
			}
			else
			{
				this.getApplication().startCall(video);
			}
		},
		openChat()
		{
			this.getApplication().toggleChat();
		},
		/* endregion 01. Actions */
		/* region 02. Handlers */
		onCloseChat()
		{
			this.getApplication().toggleChat();
		},
		onTextareaSend(event)
		{
			if (!event.text)
			{
				return false;
			}

			if (this.callApplication.common.showSmiles)
			{
				this.getApplication().toggleSmiles();
			}

			this.getApplication().addMessage(event.text);
		},
		onTextareaFileSelected(event)
		{
			let fileInput = event && event.fileChangeEvent && event.fileChangeEvent.target.files.length > 0 ? event.fileChangeEvent : '';
			if (!fileInput)
			{
				return false;
			}

			this.getApplication().uploadFile(fileInput);
		},
		onTextareaWrites(event)
		{
			this.getController().application.startWriting();
		},
		onTextareaAppButtonClick(event)
		{
			if (event.appId === 'smile')
			{
				this.getApplication().toggleSmiles();
			}
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
			this.$root.$emit(EventType.textarea.insertText, { text: event.text });
		},
		onSmilesSelectSet()
		{
			this.$root.$emit(EventType.textarea.focus);
		},
		/* endregion 02. Handlers */
		/* region 03. Helpers */
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
		getApplication()
		{
			return this.$root.$bitrixApplication;
		},
		getController()
		{
			return this.$root.$bitrixController;
		},
		updateConferenceDuration()
		{
			if (!this.conferenceStartDate)
			{
				return false;
			}

			const startDate = this.conferenceStartDate;
			const currentDate = new Date();

			let durationInSeconds = Math.floor((currentDate - startDate) / 1000);
			let minutes = 0;
			if (durationInSeconds > 60)
			{
				minutes = Math.floor(durationInSeconds / 60);
				if (minutes < 10)
				{
					minutes = '0' + minutes;
				}
			}
			let seconds = durationInSeconds - (minutes * 60);
			if (seconds < 10)
			{
				seconds = '0' + seconds;
			}
			this.conferenceDuration = `${minutes}:${seconds}`;

			return true;
		},
		isHttps()
		{
			return location.protocol === 'https:';
		}
		/* endregion 03. Helpers */
	},
	components: {ErrorComponent, CheckDevices, OrientationDisabled},

	// language=Vue
	template: `
		<div :class="['bx-im-component-call-wrap', {'bx-im-component-call-wrap-with-chat': showChat}]">
			<div v-show="mobileDisabled">
				<orientation-disabled/>
			</div>
			<div v-show="!mobileDisabled" class="bx-im-component-call">
				<div class="bx-im-component-call-left">
					<div id="bx-im-component-call-container"></div>
					<div v-if="isPreparationStep" class="bx-im-component-call-left-preparation">
						<!-- Step 1: Errors -->
						<template v-if="error">
							<error-component :errorCode="error" />
						</template>
						<!-- Step 2: Password check -->
						<template v-else-if="!passwordChecked">
							<div class="bx-im-component-call-info-container">
								<div class="bx-im-component-call-info-logo"></div>
								<div class="bx-im-component-call-info-title">{{ conferenceTitle }}</div>
	<!--						<div class="bx-im-component-call-info-date">26.08.2020, 12:00 - 13:00</div>-->
							</div>
							<div class="bx-im-component-call-password-container">
								<template v-if="wrongPassword">
									<div class="bx-im-component-call-password-error">
										{{ localize['BX_IM_COMPONENT_CALL_PASSWORD_WRONG'] }}
									</div>
								</template>
								<template v-else>
									<div class="bx-im-component-call-password-title">
										<div class="bx-im-component-call-password-title-logo"></div>
										<div class="bx-im-component-call-password-title-text">{{ localize['BX_IM_COMPONENT_CALL_PASSWORD_TITLE'] }}</div>
									</div>
								</template>
								<input @keyup.enter="checkPassword" type="text" v-model="password" class="bx-im-component-call-password-input" :placeholder="localize['BX_IM_COMPONENT_CALL_PASSWORD_PLACEHOLDER']" ref="passwordInput"/>
								<button @click="checkPassword" class="ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-password-button">{{ localize['BX_IM_COMPONENT_CALL_PASSWORD_JOIN'] }}</button>
							</div>
						</template>
						<template v-else-if="!error && passwordChecked">
							<!-- Step 3: Loading -->
							<template v-if="!userInited">
								<div class="bx-im-component-call-loading">
									<div class="bx-im-component-call-loading-text">{{ localize['BX_IM_COMPONENT_CALL_LOADING'] }}</div>
								</div>
							</template>
							<template v-else>
								<!-- Step 4: Permissions -->
								<template v-if="!isDesktop() && !permissionsRequested">
									<div class="bx-im-component-call-info-container">
										<div class="bx-im-component-call-info-logo"></div>
										<div class="bx-im-component-call-info-title">{{ conferenceTitle }}</div>
	<!--								<div class="bx-im-component-call-info-date">26.08.2020, 12:00 - 13:00</div>-->
										<div :class="conferenceStatusClasses">{{ conferenceStatusText }}</div>
									</div>
									<div class="bx-im-component-call-permissions-container">
										<div class="bx-im-component-call-permissions-text">{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_TEXT'] }}</div>
										<button @click="requestPermissions" class="ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-permissions-button">{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_BUTTON'] }}</button>
										<template v-if="isMobile()">
											<div class="bx-im-component-call-open-chat-button-container">
												<button @click="openChat" class="ui-btn ui-btn-sm ui-btn-icon-chat bx-im-component-call-open-chat-button">{{ localize['BX_IM_COMPONENT_CALL_OPEN_CHAT'] }}</button>
												<template v-if="dialogCounter > 0">
													<div class="bx-im-component-call-open-chat-button-counter">{{ dialogCounter }}</div>
												</template>
											</div>
										</template>
									</div>
								</template>
								<template v-if="isDesktop() && (!permissionsRequested || !user)">
									<div class="bx-im-component-call-info-container">
										<div class="bx-im-component-call-info-logo"></div>
										<div class="bx-im-component-call-info-title">{{ conferenceTitle }}</div>
	<!--								<div class="bx-im-component-call-info-date">26.08.2020, 12:00 - 13:00</div>-->
										<div :class="conferenceStatusClasses">{{ conferenceStatusText }}</div>
									</div>
									<div class="bx-im-component-call-permissions-container">
										<div class="bx-im-component-call-permissions-text">{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_LOADING'] }}</div>
										<button class="ui-btn ui-btn-sm ui-btn-wait bx-im-component-call-permissions-button">{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_BUTTON'] }}</button>
									</div>
								</template>
								<!-- Step 5: Usual interface with video and mic check -->
								<template v-else-if="permissionsRequested">
									<div class="bx-im-component-call-video-step-container">
										<!-- Compact conference info -->
										<div class="bx-im-component-call-info-container-compact">
											<div class="bx-im-component-call-info-title-container">
												<div class="bx-im-component-call-info-logo"></div>
												<div class="bx-im-component-call-info-title">{{ conferenceTitle }}</div>
											</div>
	<!--									<div class="bx-im-component-call-info-date">26.08.2020, 12:00 - 13:00</div>-->
											<div :class="conferenceStatusClasses">{{ conferenceStatusText }}</div>
										</div>
										<!-- Video and mic check -->
										<div class="bx-im-component-call-device-check-container">
											<check-devices />
										</div>
										<div class="bx-im-component-call-bottom-container">
											<template v-if="!waitingForStart">
												<!-- If we know user name -->
												<template v-if="user && userHasRealName">
													<template v-if="!user.extranet">
														<div class="bx-im-component-call-intranet-name-container">
															<div class="bx-im-component-call-intranet-name-title">{{ localize['BX_IM_COMPONENT_CALL_INTRANET_NAME_TITLE'] }}</div>
															<div class="bx-im-component-call-intranet-name-content">
																<div class="bx-im-component-call-intranet-name-content-left">
																	<div :style="intranetAvatarStyle" class="bx-im-component-call-intranet-name-avatar"></div>
																	<div class="bx-im-component-call-intranet-name-text">{{ user.name }}</div>
																</div>
																<template v-if="!isDesktop()">
																	<a :href="logoutLink" class="bx-im-component-call-intranet-name-logout">{{ localize['BX_IM_COMPONENT_CALL_INTRANET_LOGOUT'] }}</a>
																</template>
															</div>
														</div>
													</template>
													<template v-else-if="user.extranet">
														<div class="bx-im-component-call-guest-name-container">
															<div class="bx-im-component-call-guest-name-text">{{ user.name }}</div>
														</div>
													</template>
												</template>
												<!-- New guest, need to specify name -->
												<template v-else-if="user && !userHasRealName">
													<input
														v-model="userNewName"
														type="text"
														:placeholder="localize['BX_IM_COMPONENT_CALL_NAME_PLACEHOLDER']"
														class="bx-im-component-call-name-input"
														ref="nameInput"
													/>
												</template>
												<!-- Action buttons -->
												<!-- Intranet user can start conference -->
												<template v-if="user">
													<template v-if="!user.extranet && !conferenceStarted">
														<button @click="startConference({video: true})" class="ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-join-video">{{ localize['BX_IM_COMPONENT_CALL_START_WITH_VIDEO'] }}</button>
														<button @click="startConference({video: false})" class="ui-btn ui-btn-sm bx-im-component-call-join-audio">{{ localize['BX_IM_COMPONENT_CALL_START_WITH_AUDIO'] }}</button>
													</template>
													<!-- Others can join -->
													<template v-else>
														<button @click="joinConference({video: true})" class="ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-join-video">{{ localize['BX_IM_COMPONENT_CALL_JOIN_WITH_VIDEO'] }}</button>
														<button @click="joinConference({video: false})" class="ui-btn ui-btn-sm bx-im-component-call-join-audio">{{ localize['BX_IM_COMPONENT_CALL_JOIN_WITH_AUDIO'] }}</button>
													</template>
												</template>
											</template>
											<!-- Waiting for start-->
											<template v-else>
												<div class="bx-im-component-call-wait-container">
													<div class="bx-im-component-call-wait-logo"></div>
													<div class="bx-im-component-call-wait-title">{{ localize['BX_IM_COMPONENT_CALL_WAIT_START_TITLE'] }}</div>
													<div class="bx-im-component-call-wait-user-counter">{{ localize['BX_IM_COMPONENT_CALL_WAIT_START_USER_COUNT'] }} {{ userCounter }}</div>
													<template v-if="isMobile()">
														<div class="bx-im-component-call-open-chat-button-container">
															<button @click="openChat" class="ui-btn ui-btn-sm ui-btn-icon-chat bx-im-component-call-open-chat-button">{{ localize['BX_IM_COMPONENT_CALL_OPEN_CHAT'] }}</button>
															<template v-if="dialogCounter > 0">
																<div class="bx-im-component-call-open-chat-button-counter">{{ dialogCounter }}</div>
															</template>
														</div>
													</template>
												</div>
											</template>
										</div>
									</div>
								</template>
							</template>
						</template>
					</div>
				</div>
				<template v-if="userInited && !error">
					<transition :name="!isMobile()? 'videoconf-chat-slide': ''">
						<div v-show="showChat" class="bx-im-component-call-right">
							<div class="bx-im-component-call-right-header">
								<div @click="onCloseChat" class="bx-im-component-call-right-header-close" :title="localize['BX_IM_COMPONENT_CALL_CHAT_CLOSE_TITLE']"></div>
								<div class="bx-im-component-call-right-header-title">{{ localize['BX_IM_COMPONENT_CALL_CHAT_TITLE'] }}</div>
							</div>
							<div class="bx-im-component-call-right-chat">
								<bx-im-component-dialog
									:userId="userId"
									:dialogId="dialogId"
									:listenEventScrollToBottom="EventType.dialog.scrollToBottom"
								/>
								<keep-alive include="bx-im-component-call-smiles">
									<template v-if="callApplication.common.showSmiles">
										<bx-im-component-call-smiles @selectSmile="onSmilesSelectSmile" @selectSet="onSmilesSelectSet"/>	
									</template>	
								</keep-alive>
								<div v-if="user" class="bx-im-component-call-textarea">
									<bx-im-view-textarea
										:userId="userId"
										:dialogId="dialogId" 
										:writesEventLetter="3"
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
							</div>
						</div>
					</transition>
				</template>
			</div>
		</div>
	`
});