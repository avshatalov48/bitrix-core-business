import {Reflection, Text, Dom, ajax as Ajax} from "main.core";
import {BitrixVue} from "ui.vue";
import {ConferenceFieldState} from "im.const";
import {Logger} from "im.lib.logger";
import {Clipboard} from "im.lib.clipboard";

import {FieldTitle} from "./fields/title";
import {FieldPassword} from "./fields/password";
import {FieldInvitation} from "./fields/invitation";
import {FieldPlanner} from "./fields/planner";
import {FieldBroadcast} from "./fields/broadcast";

const FieldTypes = [
	FieldTitle,
	FieldPassword,
	FieldInvitation,
	FieldPlanner,
	FieldBroadcast
];

const FieldComponents = {};
FieldTypes.forEach(fieldType => {
	FieldComponents[fieldType.name] = fieldType.component;
});

BitrixVue.component('bx-im-component-conference-edit',
{
	props:
	{
		conferenceId: {type: Number, default: 0},
		fieldsData: { type: Object, default: {} },
		mode: { type: String, default: ConferenceFieldState.create },
		chatHost: { type: Object, default: {} },
		chatUsers: { type: Array, default: [] },
		presenters: { type: Array, default: [] },
		publicLink: { type: String, default: '' },
		chatId: { type: Number, default: 0 },
		invitationText: { type: String, default: '' },
		gridId: { type: String, default: '' },
		pathToList: { type: String, default: '' },
		broadcastingEnabled: { type: Boolean, default: false }
	},
	data: function()
	{
		return {
			fieldsMode: {
				'title': this.mode,
				'password': this.mode,
				'planner': this.mode,
				'broadcast': this.mode
			},
			fields: {},
			initialValues: {},
			title: {
				currentValue: '',
				initialValue: '',
				defaultValue: ''
			},
			invitation: {
				value: '',
				mode: ConferenceFieldState.view,
				edited: false
			},
			password: {
				currentValue: '',
				initialValue: ''
			},
			passwordNeeded: {
				currentValue: false,
				initialValue: false
			},
			selectedUsers: {
				currentValue: [],
				initialValue: []
			},
			broadcastMode: {
				currentValue: false,
				initialValue: false
			},
			selectedPresenters: {
				currentValue: [],
				initialValue: []
			},
			selectedDate: {
				currentValue: '',
				initialValue: ''
			},
			selectedTime: {
				currentValue: '',
				initialValue: ''
			},
			selectedDuration: {
				currentValue: '30',
				initialValue: '30'
			},
			selectedDurationType: {
				currentValue: 'm',
				initialValue: 'm'
			},
			errors: [],
			linkGenerated: false,
			aliasData: {},
			isSubmitting: false
		};
	},
	created()
	{
		if (this.isFormViewMode)
		{
			this.title.initialValue = this.fieldsData['TITLE'];
			this.password.initialValue = this.fieldsData['PASSWORD'];
			this.broadcastMode.currentValue = this.fieldsData['BROADCAST'];
			this.invitation.value = this.invitationText;
			this.passwordNeeded.currentValue = !!this.fieldsData['PASSWORD'];
			this.publicLink = Text.encode(this.publicLink);

			this.selectedUsers.currentValue = [...this.chatUsers];
			if (this.fieldsData['BROADCAST'])
			{
				this.selectedPresenters.currentValue = [...this.presenters]
			}
		}
		else if (this.isFormCreateMode)
		{
			this.generateLink();

			this.title.initialValue = '';
			this.password.initialValue = '';
			this.passwordNeeded.currentValue = false;
			this.broadcastMode.currentValue = false;

			const currentUser = {
				id: this.chatHost.ID,
				title: this.chatHost.FULL_NAME,
				avatar: this.chatHost.AVATAR
			};
			this.selectedUsers.currentValue.push(currentUser);
			this.selectedPresenters.currentValue.push(currentUser);
		}
		this.title.currentValue = this.title.initialValue;
		this.password.currentValue = this.password.initialValue;
		this.passwordNeeded.initialValue = this.passwordNeeded.currentValue;
		this.broadcastMode.initialValue = this.broadcastMode.currentValue;
		this.selectedUsers.initialValue = [...this.selectedUsers.currentValue];
		this.selectedPresenters.initialValue = [...this.selectedPresenters.currentValue];

		this.setDefaultDateAndTime();
		this.setDefaultDuration();
	},
	mounted()
	{
		if (this.isFormCreateMode)
		{
			this.checkRequirements();
		}
	},
	computed:
	{
		isFormCreateMode()
		{
			return this.mode === ConferenceFieldState.create;
		},
		isFormViewMode()
		{
			return this.mode === ConferenceFieldState.view;
		},
		isTitleEdited()
		{
			return this.fieldsMode['title'] === ConferenceFieldState.edit;
		},
		isPasswordEdited()
		{
			return this.fieldsMode['password'] === ConferenceFieldState.edit;
		},
		isPlannerEdited()
		{
			return this.fieldsMode['planner'] === ConferenceFieldState.edit;
		},
		isPasswordCheckboxEdited()
		{
			return this.passwordNeeded.currentValue !== this.passwordNeeded.initialValue;
		},
		isBroadcastEdited()
		{
			return this.fieldsMode['broadcast'] === ConferenceFieldState.edit;
		},
		isEditing()
		{
			return this.isFormViewMode
				&& (this.isTitleEdited || this.isPasswordEdited || this.invitation.edited || this.isPasswordCheckboxEdited || this.isPlannerEdited || this.isBroadcastEdited);
		},
		conferenceLink()
		{
			if (this.isFormCreateMode)
			{
				if (this.linkGenerated)
				{
					return this.aliasData['LINK'];
				}
				else
				{
					return '#LINK#';
				}
			}
			else if (this.isFormViewMode)
			{
				return this.publicLink;
			}
		},
		submitFormButtonClasses()
		{
			const classes = ['ui-btn', 'ui-btn-success'];

			if (this.isSubmitting)
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
		/* region 01. Mode switching */
		switchToEdit(fieldName)
		{
			this.fieldsMode[fieldName] = ConferenceFieldState.edit;
			this.$root.$emit('focus', fieldName);
		},
		switchModeForAllFields(mode)
		{
			for (let field in this.fieldsMode)
			{
				if (this.fieldsMode.hasOwnProperty(field))
				{
					this.fieldsMode[field] = mode;
				}
			}
			this.$root.$emit('switchModeForAll', mode);
		},
		/* endregion 01. Mode switching */

		/* region 02. Field update handlers */
		onTitleChange(newTitle)
		{
			this.title.currentValue = newTitle;
		},
		onPasswordChange(newPassword)
		{
			this.password.currentValue = newPassword;
		},
		onPasswordNeededChange()
		{
			this.passwordNeeded.currentValue = !this.passwordNeeded.currentValue;

			if (this.passwordNeeded.currentValue)
			{
				this.$root.$emit('focus', 'password');
			}
		},
		onBroadcastModeChange()
		{
			this.broadcastMode.currentValue = !this.broadcastMode.currentValue;
		},
		onInvitationUpdate(newValue)
		{
			this.invitation.value = newValue;
			this.invitation.edited = true;
		},
		onUserSelect(event)
		{
			const index = this.selectedUsers.currentValue.findIndex((user) => {
				return user.id === event.data.item.id;
			});

			if (index === -1)
			{
				this.selectedUsers.currentValue.push({
					id: event.data.item.id,
					title: event.data.item.title,
					avatar: event.data.item.avatar,
				});
			}
		},
		onUserDeselect(event)
		{
			const index = this.selectedUsers.currentValue.findIndex((user) => {
				return user.id === event.data.item.id;
			});

			if (index > -1)
			{
				this.selectedUsers.currentValue.splice(index, 1);
			}
		},
		onPresenterSelect(event)
		{
			const index = this.selectedPresenters.currentValue.findIndex((user) => {
				return user.id === event.data.item.id;
			});

			if (index === -1)
			{
				this.selectedPresenters.currentValue.push({
					id: event.data.item.id,
					title: event.data.item.title,
					avatar: event.data.item.avatar,
				});
			}
		},
		onPresenterDeselect(event)
		{
			const index = this.selectedPresenters.currentValue.findIndex((user) => {
				return user.id === event.data.item.id;
			});

			if (index > -1)
			{
				this.selectedPresenters.currentValue.splice(index, 1);
			}
		},
		onDateChange(newDate)
		{
			this.selectedDate.currentValue = BX.formatDate(newDate, BX.message('FORMAT_DATE'));
		},
		onTimeChange(newTime)
		{
			this.selectedTime.currentValue = newTime;
		},
		onDurationChange(newDuration)
		{
			this.selectedDuration.currentValue = String(newDuration);
		},
		onDurationTypeChange(newDurationType)
		{
			this.selectedDurationType.currentValue = newDurationType;
		},
		/* endregion 02. Field update handlers */

		/* region 03. Actions */
		discardChanges()
		{
			this.clearErrors();

			this.title.currentValue = this.title.initialValue;
			this.password.currentValue = this.password.initialValue;
			this.passwordNeeded.currentValue = this.passwordNeeded.initialValue;
			this.broadcastMode.currentValue = this.broadcastMode.initialValue;
			this.selectedUsers.currentValue = [...this.selectedUsers.initialValue];
			this.$root.$emit('updateUserSelector');
			this.selectedPresenters.currentValue = [...this.selectedPresenters.initialValue];
			this.$root.$emit('updatePresenterSelector');
			this.selectedDate.currentValue = this.selectedDate.initialValue;
			this.selectedTime.currentValue = this.selectedTime.initialValue;
			this.selectedDuration.currentValue = this.selectedDuration.initialValue;
			this.selectedDurationType.currentValue = this.selectedDurationType.initialValue;

			this.switchModeForAllFields(ConferenceFieldState.view);
		},
		copyInvitation()
		{
			let link = '';
			if (this.isFormCreateMode && this.linkGenerated)
			{
				link = Text.decode(this.aliasData['LINK']);
			}
			else if (this.isFormViewMode)
			{
				link = Text.decode(this.publicLink);
			}

			let title = this.localize['BX_IM_COMPONENT_CONFERENCE_DEFAULT_TITLE'];
			if (this.title.currentValue)
			{
				title = this.title.currentValue;
			}

			const copyValue = Text.decode(this.invitation.value)
				.replace(/#CREATOR#/gm, this.chatHost.FULL_NAME)
				.replace(/#TITLE#/gm, `"${title}"`)
				.replace(/#LINK#/gm, `${link}`);
			Clipboard.copy(copyValue);

			if (Reflection.getClass('BX.UI.Notification.Center'))
			{
				top.BX.UI.Notification.Center.notify({
					content: this.localize['BX_IM_COMPONENT_CONFERENCE_INVITATION_COPIED']
				})
			}
		},
		openChat()
		{
			if (window.top["BXIM"])
			{
				window.top["BXIM"].openMessenger('chat' + this.chatId);
			}
		},
		editAll()
		{
			this.switchModeForAllFields(ConferenceFieldState.edit);
		},
		/* endregion 03. Actions */

		/* region 04. Form handling */
		submitForm()
		{
			if (this.isSubmitting)
			{
				return false;
			}
			this.isSubmitting = true;

			const fieldsToSubmit = {};

			fieldsToSubmit['title'] = this.title.currentValue;
			fieldsToSubmit['password_needed'] = this.passwordNeeded.currentValue;
			fieldsToSubmit['password'] = this.password.currentValue;
			fieldsToSubmit['id'] = this.conferenceId;
			fieldsToSubmit['invitation'] = Text.decode(this.invitation.value);
			fieldsToSubmit['users'] = this.selectedUsers.currentValue.map(user => user.id);
			fieldsToSubmit['broadcast_mode'] = this.broadcastMode.currentValue;
			fieldsToSubmit['presenters'] = this.selectedPresenters.currentValue.map(user => user.id);

			this.clearErrors();

			if (this.isFormViewMode || this.linkGenerated)
			{
				Ajax.runAction('im.conference.create', {
					json: {
						fields: fieldsToSubmit,
						aliasData: this.aliasData
					},
					analyticsLabel: {
						creationType: 'section'
					}
				})
				.then((response) => {
					this.onSuccessfulSubmit();
				})
				.catch((response) => {
					this.onFailedSubmit(response);
				});
			}
		},
		onSuccessfulSubmit()
		{
			if (this.isFormCreateMode)
			{
				this.copyInvitation();
			}
			this.isSubmitting = false;
			this.closeSlider();
			this.reloadGrid();
		},
		onFailedSubmit(response)
		{
			this.isSubmitting = false;
			let errorMessage = response["errors"][0].message;
			if (response["errors"][0].code === 'NETWORK_ERROR')
			{
				errorMessage = this.localize['BX_IM_COMPONENT_CONFERENCE_NETWORK_ERROR'];
			}
			this.addError(errorMessage);
		},
		/* endregion 04. Form handling */

		/* region 05. Helpers */
		checkRequirements()
		{
			if (!top.BX.PULL.isPublishingEnabled())
			{
				this.disableButton();
				this.addError(this.localize['BX_IM_COMPONENT_CONFERENCE_PUSH_ERROR']);
			}

			if (!top.BX.Call.Util.isCallServerAllowed())
			{
				this.disableButton();
				this.addError(this.localize['BX_IM_COMPONENT_CONFERENCE_VOXIMPLANT_ERROR_WITH_LINK']);
			}
		},
		disableButton()
		{
			const createButton = document.querySelector('#im-conference-create-wrap #ui-button-panel-save');
			if (createButton)
			{
				Dom.addClass(createButton, ['ui-btn-disabled', 'ui-btn-icon-lock']);
			}
		},
		generateLink()
		{
			Ajax.runAction('im.conference.prepare', {
				json: {},
				analyticsLabel: {
					creationType: 'section'
				}
			})
			.then((response) => {
				this.aliasData = response.data['ALIAS_DATA'];
				this.aliasData['LINK'] = Text.encode(this.aliasData['LINK']);
				this.title.defaultValue = response.data['DEFAULT_TITLE'];
				this.linkGenerated = true;
			})
			.catch((response) => {
				Logger.warn('error', response["errors"][0].message);
			});
		},
		addError(errorText)
		{
			this.errors.push(errorText);
		},
		clearErrors()
		{
			this.errors = [];
		},
		closeSlider()
		{
			if (Reflection.getClass('BX.SidePanel'))
			{
				BX.SidePanel.Instance.close();
			}
		},
		reloadGrid()
		{
			if (Reflection.getClass('top.BX.Main.gridManager'))
			{
				top.BX.Main.gridManager.reload(this.gridId);
			}
			else
			{
				top.window.location = this.pathToList;
			}
		},
		setDefaultDateAndTime()
		{
			const date = new Date();
			const minutes = date.getMinutes();
			const mod = minutes % 5;

			if (mod > 0)
			{
				date.setMinutes(minutes - mod + (mod > 2 ? 5 : 0));
			}

			this.selectedDate.currentValue = BX.formatDate(date, BX.message('FORMAT_DATE'));
			this.selectedDate.initialValue = this.selectedDate.currentValue;
			this.selectedTime.currentValue = this.formatTime(date);
			this.selectedTime.initialValue = this.selectedTime.currentValue;
		},
		setDefaultDuration()
		{
			this.selectedDuration.currentValue = '30';
			this.selectedDuration.initialValue = this.selectedDuration.currentValue;
			this.selectedDurationType.currentValue = 'm';
			this.selectedDurationType.initialValue = this.selectedDurationType.currentValue;
		},
		formatTime(date)
		{
			const dateFormat = BX.date.convertBitrixFormat(BX.message('FORMAT_DATE')).replace(/:?\s*s/, '');
			const timeFormat = BX.date.convertBitrixFormat(BX.message('FORMAT_DATETIME')).replace(/:?\s*s/, '');
			const dateString = BX.date.format(dateFormat, date);
			const timeString = BX.date.format(timeFormat, date);

			return BX.util.trim(timeString.replace(dateString, ''));
		}
		/* endregion 05. Helpers */
	},
	components: FieldComponents,
	template: `
		<div>
			<template v-if="errors.length > 0">
				<div class="ui-alert ui-alert-danger" id="im-conference-create-errors">
					<span v-for="error in errors" class="ui-alert-message" v-html="error"></span>
				</div>
			</template>
			<div class="im-conference-create-block im-conference-create-fields-wrapper">
				<!-- Form fields -->
				<conference-field-title
					:mode="fieldsMode['title']"
					:title="title.currentValue"
					:defaultValue="title.defaultValue"
					@titleChange="onTitleChange"
					@switchToEdit="switchToEdit"
				/>
				<conference-field-planner
					:mode="fieldsMode['planner']"
					:selectedUsers="selectedUsers.currentValue"
					:selectedDate="selectedDate.currentValue"
					:selectedTime="selectedTime.currentValue"
					:selectedDuration="selectedDuration.currentValue"
					:selectedDurationType="selectedDurationType.currentValue"
					:chatHost="chatHost"
					@userSelect="onUserSelect"
					@userDeselect="onUserDeselect"
					@dateChange="onDateChange"
					@timeChange="onTimeChange"
					@durationChange="onDurationChange"
					@durationTypeChange="onDurationTypeChange"
					@switchToEdit="switchToEdit"
				/>
				<conference-field-password
					:mode="fieldsMode['password']"
					:password="password.currentValue"
					:passwordNeeded="passwordNeeded.currentValue"
					@passwordChange="onPasswordChange"
					@passwordNeededChange="onPasswordNeededChange"
					@switchToEdit="switchToEdit"
				/>
<!--				<div v-if="isFormCreateMode" class="im-conference-create-delimiter im-conference-create-delimiter-small"></div>-->
				<template v-if="broadcastingEnabled">
					<conference-field-broadcast
						:mode="fieldsMode['broadcast']"
						:broadcastMode="broadcastMode.currentValue"
						:selectedPresenters="selectedPresenters.currentValue"
						:chatHost="chatHost"
						@broadcastModeChange="onBroadcastModeChange"
						@switchToEdit="switchToEdit"
						@presenterSelect="onPresenterSelect"
						@presenterDeselect="onPresenterDeselect"
					/>
				</template>
				<!-- Action buttons -->
				<template v-if="!isFormCreateMode">
					<div class="im-conference-create-section im-conference-create-actions">
						<a :href="publicLink" target="_blank" class="ui-btn ui-btn-sm ui-btn-primary ui-btn-icon-camera">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_START'] }}</a>
						<button @click="copyInvitation" class="ui-btn ui-btn-sm ui-btn-light-border ui-btn-icon-share">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_INVITATION_COPY'] }}</button>
						<button @click="openChat" class="ui-btn ui-btn-sm ui-btn-light-border ui-btn-icon-chat">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_CHAT'] }}</button>
						<button @click="editAll" class="ui-btn ui-btn-sm ui-btn-light">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_EDIT'] }}</button>
					</div>
				</template>
				<!-- Bottom button panel -->
				<div v-if="isEditing" class="im-conference-create-button-panel-edit ui-button-panel-wrapper ui-pinner ui-pinner-bottom ui-pinner-full-width">
					<div class="ui-button-panel ui-button-panel-align-center">
						<button @click="submitForm" id="ui-button-panel-save" :class="submitFormButtonClasses">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_SAVE'] }}</button>
						<a @click="discardChanges" id="ui-button-panel-cancel" class="ui-btn ui-btn-link">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_CANCEL'] }}</a>
					</div>
				</div>
				<div v-else-if="isFormCreateMode" class="im-conference-create-button-panel-add ui-button-panel-wrapper ui-pinner ui-pinner-bottom ui-pinner-full-width">
					<div class="ui-button-panel ui-button-panel-align-center">
						<button @click="submitForm" id="ui-button-panel-save" name="save" value="Y" :class="submitFormButtonClasses">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_CREATE'] }}</button>
						<a @click="closeSlider" id="ui-button-panel-cancel" class="ui-btn ui-btn-link">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_CANCEL'] }}</a>
					</div>
				</div>
				<div class="im-conference-create-delimiter"></div>
				<!-- Invitation -->
				<conference-field-invitation
					:invitation="invitation"
					:chatHost="chatHost"
					:title="title.currentValue"
					:defaultTitle="title.defaultValue"
					:publicLink="conferenceLink"
					:formMode="mode"
					@invitationUpdate="onInvitationUpdate"
				/>
			</div>
		</div>
	`
});