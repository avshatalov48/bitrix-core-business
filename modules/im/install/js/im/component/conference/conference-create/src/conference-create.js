import { ajax as Ajax, Reflection, Runtime } from "main.core";
import {Vue} from "ui.vue";
import {Logger} from "im.lib.logger";
import {Clipboard} from "im.lib.clipboard";

import "./conference-create.css";

Vue.component('bx-im-component-conference-create',
	{
		props: ['userId', 'darkTheme'],
		data: function() {
			return {
				title: '',
				defaultTitle: '',
				linkGenerated: false,
				aliasData: null,
				userSelectorLoaded: false,
				userSelector: null,
				selectedUsers: [],
				chatId: null,
				errors: []
			};
		},
		created()
		{
			this.checkRequirements();
			this.selectedUsers.push(this.userId);
			this.generateLink();
		},
		mounted()
		{
			this.initUserSelector().then(() => {
				this.userSelector.renderTo(this.$refs['userSelector']);
				this.$nextTick(() => {
					this.$refs['titleInput'].focus();
				});
			});
		},
		computed:
		{
			conferenceLink()
			{
				if (this.linkGenerated)
				{
					return this.aliasData['LINK'];
				}

				return this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_LINK_LOADING'];
			},
			defaultTitlePlaceholder()
			{
				if (this.linkGenerated)
				{
					return this.defaultTitle;
				}

				return this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_PLACEHOLDER_TITLE_2'];
			},
			containerClasses()
			{
				const classes = ['bx-conference-quick-create-wrap'];

				if (this.darkTheme)
				{
					classes.push('bx-conference-quick-create-wrap-dark');
				}

				return classes;
			},
			startButtonClasses()
			{
				const classes = ['ui-btn', 'ui-btn-primary', 'bx-conference-quick-create-button-start'];

				if (!this.userSelectorLoaded)
				{
					classes.push('ui-btn-disabled');
				}
				return classes;
			},
			localize()
			{
				return BX.message;
			}
		},
		methods:
		{
			generateLink()
			{
				Ajax.runAction('im.conference.prepare', {
					json: {},
					analyticsLabel: {
						creationType: 'chat'
					}
				})
				.then((response) => {
					this.aliasData = response.data['ALIAS_DATA'];
					this.defaultTitle = response.data['DEFAULT_TITLE'];
					this.linkGenerated = true;
				})
				.catch((response) => {
					Logger.warn('error', response["errors"][0].message);
				});
			},
			copyLink()
			{
				if (this.linkGenerated && Reflection.getClass('BX.UI.Notification.Center'))
				{
					Clipboard.copy(this.aliasData['LINK']);

					top.BX.UI.Notification.Center.notify({
						content: this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_COPY_CONFIRMATION']
					})
				}
			},
			startConference()
			{
				if (this.linkGenerated)
				{
					const fieldsToSubmit = {};

					fieldsToSubmit['title'] = this.title;
					fieldsToSubmit['id'] = 0;
					fieldsToSubmit['password_needed'] = false;
					fieldsToSubmit['users'] = this.selectedUsers;

					this.clearErrors();
					Ajax.runAction('im.conference.create', {
						json: {
							fields: fieldsToSubmit,
							aliasData: this.aliasData
						},
						analyticsLabel: {
							creationType: 'chat'
						}
					})
					.then((response) => {
						this.onSuccessfulSubmit(response);
					})
					.catch((response) => {
						this.onFailedSubmit(response);
					});
				}
			},
			cancelCreation()
			{
				if (BXIM && BXIM.messenger)
				{
					BXIM.messenger.extraClose();
				}
			},
			openChat()
			{
				if (window.top["BXIM"] && this.chatId)
				{
					window.top["BXIM"].openMessenger('chat' + this.chatId);
				}
			},
			initUserSelector()
			{
				return Runtime.loadExtension('ui.entity-selector').then((exports) => {
					const {TagSelector} = exports;
					this.userSelectorLoaded = true;

					this.userSelector = new TagSelector({
						id: 'tag-selector',
						dialogOptions: {
							id: 'tag-selector',
							preselectedItems: [['user', this.userId]],
							undeselectedItems: [['user', this.userId]],
							events: {
								'Item:onSelect': (event) => {
									this.onUserSelect(event);
								},
								'Item:onDeselect': (event) => {
									this.onUserDeselect(event);
								}
							},
							entities: [
								{id: 'user', options: {inviteEmployeeLink: false}},
								{id: 'department'}
							],
							zIndex: 4000
						},
						addButtonCaption: this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_ADD_USERS'],
						addButtonCaptionMore: this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_ADD_USERS']
					});
				});
			},
			checkRequirements()
			{
				if (!BX.PULL.isPublishingEnabled())
				{
					this.disableButton();
					this.addError(this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_PUSH_ERROR']);
				}

				if (!BX.Call.Util.isCallServerAllowed())
				{
					this.disableButton();
					this.addError(this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_VOXIMPLANT_ERROR']);
				}
			},
			addError(errorText)
			{
				this.errors.push(errorText);
			},
			clearErrors()
			{
				this.errors = [];
			},
			disableButton()
			{
				this.startButtonClasses.push('ui-btn-disabled', 'ui-btn-icon-lock');
			},
			onUserSelect(event)
			{
				const index = this.selectedUsers.findIndex((userId) => {
					return userId === event.data.item.id;
				});

				if (index === -1)
				{
					this.selectedUsers.push(event.data.item.id);
				}
			},
			onUserDeselect(event)
			{
				const index = this.selectedUsers.findIndex((userId) => {
					return userId === event.data.item.id;
				});

				if (index > -1)
				{
					this.selectedUsers.splice(index, 1);
				}
			},
			onSuccessfulSubmit(response)
			{
				this.chatId = response.data['CHAT_ID'];
				this.openChat();
				if (BXIM)
				{
					BXIM.openVideoconf(this.aliasData['ALIAS']);
				}
			},
			onFailedSubmit(response)
			{
				this.addError(response["errors"][0].message);
			}
		},
		template:
		`
			<div :class="containerClasses">
				<div class="bx-conference-quick-create-content">
					<!-- Title -->
					<div class="bx-conference-quick-create-title">
						{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_HEADER_TITLE'] }}
					</div>
					<!-- Errors -->
					<template v-if="errors.length > 0">
						<div class="ui-alert ui-alert-danger bx-conference-quick-create-error-wrap">
							<span v-for="error in errors" class="ui-alert-message">{{ error }}</span>
						</div>
					</template>
					<!-- Title field -->
					<div class="bx-conference-quick-create-field-block">
						<div class="bx-conference-quick-create-field-label">
							{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_LABEL_TITLE'] }}
						</div>
						<input
							v-model="title"
							:placeholder="defaultTitlePlaceholder"
							type="text"
							class="bx-conference-quick-create-field-input"
							ref="titleInput"
						>
					</div>
					<!-- User selector field -->
					<div class="bx-conference-quick-create-field-block">
						<div class="bx-conference-quick-create-field-label">
							{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_LABEL_USERS'] }}
						</div>
						<template v-if="userSelectorLoaded">
							<div class="bx-conference-quick-create-selector-wrap" ref="userSelector"></div>
						</template>
						<template v-else>
							<input type="text" class="bx-conference-quick-create-field-input" :placeholder="localize['BX_IM_COMPONENT_CONFERENCE_CREATE_USERS_LOADING']" disabled>
						</template>
					</div>
					<!-- Link field -->
					<div class="bx-conference-quick-create-field-block">
						<div class="bx-conference-quick-create-field-label">
							{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_LABEL_LINK'] }}
						</div>
						<div class="bx-conference-quick-create-link-wrap">
							<input type="text" class="bx-conference-quick-create-field-input" :placeholder="conferenceLink" disabled>
							<div @click="copyLink" class="bx-conference-quick-create-link-copy"></div>
						</div>
					</div>
					<!-- Create button -->
					<div class="bx-conference-quick-create-button">
						<button @click="startConference" :class="startButtonClasses">{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_BUTTON_START'] }}</button>
						<button @click="cancelCreation" class="ui-btn ui-btn-link bx-conference-quick-create-button-cancel">{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_BUTTON_CANCEL'] }}</button>
					</div>
				</div>
			</div>
		`
	}
);