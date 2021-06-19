import { ajax as Ajax, Reflection, Runtime } from "main.core";
import {BitrixVue} from "ui.vue";
import {Logger} from "im.lib.logger";
import {Clipboard} from "im.lib.clipboard";
import "ui.vue.components.hint";

import "./conference-create.css";

BitrixVue.component('bx-im-component-conference-create',
{
	props: ['userId', 'darkTheme', 'broadcastingEnabled'],
	data: function() {
		return {
			title: '',
			defaultTitle: '',
			broadcastMode: false,
			linkGenerated: false,
			aliasData: null,
			userSelectorLoaded: false,
			userSelector: null,
			selectedUsers: [],
			selectedPresenters: [],
			chatId: null,
			errors: []
		};
	},
	created()
	{
		this.checkRequirements();
		this.selectedUsers.push(this.userId);
		this.selectedPresenters.push(this.userId);
		this.generateLink();
	},
	mounted()
	{
		this.initUserSelector().then(() => {
			this.userSelector.renderTo(this.$refs['userSelector']);
			this.initPresenterSelector();
			this.presenterSelector.renderTo(this.$refs['presenterSelector']);
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
				fieldsToSubmit['broadcast_mode'] = this.broadcastMode;
				fieldsToSubmit['presenters'] = this.selectedPresenters;

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
				this.TagSelector = exports.TagSelector;
				this.userSelectorLoaded = true;

				this.userSelector = new this.TagSelector({
					id: 'user-tag-selector',
					dialogOptions: {
						id: 'user-tag-selector',
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
		initPresenterSelector()
		{
			this.presenterSelector = new this.TagSelector({
				id: 'presenter-tag-selector',
				dialogOptions: {
					id: 'presenter-tag-selector',
					preselectedItems: [['user', this.userId]],
					events: {
						'Item:onSelect': (event) => {
							this.onPresenterSelect(event);
						},
						'Item:onDeselect': (event) => {
							this.onPresenterDeselect(event);
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
		onPresenterSelect(event)
		{
			const index = this.selectedPresenters.findIndex((userId) => {
				return userId === event.data.item.id;
			});

			if (index === -1)
			{
				this.selectedPresenters.push(event.data.item.id);
			}
		},
		onPresenterDeselect(event)
		{
			const index = this.selectedPresenters.findIndex((userId) => {
				return userId === event.data.item.id;
			});

			if (index > -1)
			{
				this.selectedPresenters.splice(index, 1);
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
			let errorMessage = response["errors"][0].message;
			if (response["errors"][0].code === 'NETWORK_ERROR')
			{
				errorMessage = this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_NETWORK_ERROR'];
			}
			this.addError(errorMessage);
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
				<!-- Broadcast mode field -->
				<template v-if="broadcastingEnabled">
					<div class="bx-conference-quick-create-field-block-inline">
						<input type="checkbox" id="bx-conference-quick-create-field-broadcast-mode" v-model="broadcastMode">
						<label class="bx-conference-quick-create-field-label bx-conference-quick-create-broadcast-mode-label" for="bx-conference-quick-create-field-broadcast-mode">{{ localize['BX_IM_COMPONENT_CONFERENCE_CREATE_BROADCAST_MODE'] }}</label>
						<bx-hint :text="localize['BX_IM_COMPONENT_CONFERENCE_CREATE_BROADCAST_MODE_HINT']"/>
					</div>
					<!-- Presenter selector field -->
					<div class="bx-conference-quick-create-field-block" v-show="broadcastMode">
						<div class="bx-conference-quick-create-field-label">
							{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_PRESENTERS'] }}
						</div>
						<div class="bx-conference-quick-create-selector-wrap" ref="presenterSelector"></div>
					</div>
				</template>
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
});