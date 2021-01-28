import {Text} from "main.core";
import {ConferenceFieldState} from "im.const";

export const FieldInvitation =
	{
		name: 'conference-field-invitation',
		component:
			{
				props:
				{
					invitation: {type: Object},
					chatHost: {type: Object},
					title: {type: String},
					defaultTitle: {type: String},
					publicLink: {type: String},
					formMode: {type: String}
				},
				data: function()
				{
					return {
						initialValue: null,
						editedValue: null
					}
				},
				computed:
				{
					isViewMode()
					{
						return this.invitation.mode === ConferenceFieldState.view;
					},
					isFormCreateMode()
					{
						return this.formMode === ConferenceFieldState.create;
					},
					avatarClasses()
					{
						const classes = ['im-conference-create-invitation-user-avatar'];

						if (!this.chatHost.AVATAR)
						{
							classes.push('im-conference-create-invitation-user-avatar-default');
						}

						return classes;
					},
					avatarStyles()
					{
						const styles = {};

						if (this.chatHost.AVATAR)
						{
							styles.backgroundImage = `url(${this.chatHost.AVATAR})`;
						}

						return styles;
					},
					formattedInvitation()
					{
						let title = this.title ? this.title : '';

						if (this.isFormCreateMode && !this.title)
						{
							title = this.defaultTitle;
						}

						return this.invitation.value
							.replace(/#CREATOR#/gm, Text.encode(this.chatHost.FULL_NAME))
							.replace(/#TITLE#/gm, `"${Text.encode(title)}"`)
							.replace(/#LINK#/gm, `<a href="${this.publicLink}" target="_blank">${this.publicLink}</a>`);
					},
					localize()
					{
						return BX.message;
					}
				},
				methods:
				{
					onEditClick()
					{
						const contentWidth = this.$refs['view'].offsetWidth;
						const contentHeight = this.$refs['view'].offsetHeight;
						this.invitation.mode = ConferenceFieldState.edit;
						this.invitation.value = Text.decode(this.invitation.value);
						this.$nextTick(() => {
							this.$refs['editor'].style.width = (contentWidth + 20) + 'px';
							this.$refs['editor'].style.height = (contentHeight + 30) + 'px';
							this.$refs['editor'].focus();
						});
					},
					onInput(event)
					{
						if (!this.initialValue)
						{
							this.initialValue = this.invitation.value;
						}
						this.editedValue =  Text.encode(event.target.value);
					},
					saveChanges()
					{
						if (this.editedValue && this.initialValue && this.initialValue !== this.editedValue)
						{
							this.invitation.value = this.editedValue;
							this.initialValue = null;
							this.editedValue = null;

							this.$emit('invitationUpdate', this.invitation.value);
						}
						else
						{
							this.invitation.value = Text.encode(this.invitation.value);
						}

						this.invitation.mode = ConferenceFieldState.view;
					},
					discardChanges()
					{
						if (this.initialValue)
						{
							this.invitation.value = this.initialValue;
							this.initialValue = null;
							this.editedValue = null;
						}

						this.invitation.value = Text.encode(this.invitation.value);
						this.invitation.mode = ConferenceFieldState.view;
					}
				},
				created()
				{
					if (this.isFormCreateMode || !this.invitation.value)
					{
						this.invitation.value = this.localize['BX_IM_COMPONENT_CONFERENCE_DEFAULT_INVITATION'];
					}

					if (!this.isFormCreateMode && this.invitation.value)
					{
						this.invitation.value = Text.encode(this.invitation.value);
					}
				},
				template: `
					<div>
						<div class="im-conference-create-section im-conference-create-invitation-title">
							{{ localize['BX_IM_COMPONENT_CONFERENCE_INVITATION_TITLE'] }}
						</div>
						<div class="im-conference-create-section im-conference-create-invitation-wrap">
							<div class="im-conference-create-invitation-user">
								<div :class="avatarClasses" :style="avatarStyles"></div>
								<div class="im-conference-create-invitation-user-name">{{ chatHost.FIRST_NAME }}</div>
							</div>
							<div class="im-conference-create-invitation-content">
								<template v-if="isViewMode">
									<div @click="onEditClick" v-html="formattedInvitation" contenteditable="false" ref="view" class="im-conference-create-invitation-content-text"></div>
									<div @click="onEditClick" class="im-conference-create-invitation-edit"></div>
								</template>
								<template v-else>
									<textarea @input="onInput" :value="invitation.value" class="im-conference-create-invitation-editor" ref="editor"></textarea>
									<div>
										<button @click="saveChanges" class="ui-btn ui-btn-sm ui-btn-primary">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_SAVE'] }}</button>
										<button @click="discardChanges" class="ui-btn ui-btn-sm ui-btn-light">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_CANCEL'] }}</button>
									</div>
								</template>
							</div>
						</div>
					</div>
				`
			},
	};