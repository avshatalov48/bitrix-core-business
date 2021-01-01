import {ConferenceFieldState} from "im.const";

export const FieldPassword =
	{
		name: 'conference-field-password',
		component:
			{
				props:
				{
					mode: {type: String},
					password: {type: String},
					passwordNeeded: {type: Boolean}
				},
				data: function() {
					return {
						name: 'password'
					}
				},
				computed:
				{
					isViewMode()
					{
						return this.mode === ConferenceFieldState.view
					},
					codedValue()
					{
						if (this.passwordNeeded)
						{
							return `${this.localize['BX_IM_COMPONENT_CONFERENCE_PASSWORD_EXISTS']} (${this.password.replace(/./g, '*')})`;
						}
						else
						{
							return this.localize['BX_IM_COMPONENT_CONFERENCE_NO_PASSWORD'];
						}
					},
					localize()
					{
						return BX.message;
					}
				},
				methods:
				{
					switchToEdit()
					{
						this.$emit('switchToEdit', this.name);
					},
					onInput(event)
					{
						this.$emit('passwordChange', event.target.value);
					},
					onPasswordNeededChange()
					{
						this.$emit('passwordNeededChange');
					},
					onFocus(fieldName)
					{
						if (this.name === fieldName)
						{
							this.$nextTick(() => {
								if (this.$refs['input'])
								{
									this.$refs['input'].focus();
								}
							});
						}
					}
				},
				created()
				{
					this.$root.$on('focus', this.onFocus);
				},
				template: `
					<div class="im-conference-create-section im-conference-create-password-section">
						<label class="im-conference-create-label" for="im-conference-create-field-password">{{ localize['BX_IM_COMPONENT_CONFERENCE_PASSWORD_LABEL'] }}</label>
						<template v-if="!isViewMode">
							<div class="im-conference-create-field-inline">
								<input @input="onPasswordNeededChange" type="checkbox" id="im-conference-create-field-password-checkbox" :checked="passwordNeeded">
								<label class="im-conference-create-label" for="im-conference-create-field-password-checkbox">{{ localize['BX_IM_COMPONENT_CONFERENCE_PASSWORD_CHECKBOX_LABEL'] }}</label>
							</div>
							<div v-if="passwordNeeded" class="im-conference-create-field-password-container ui-ctl">
								<input
									type="text"
									id="im-conference-create-field-password"
									class="ui-ctl-element"
									:name="name"
									:placeholder="localize['BX_IM_COMPONENT_CONFERENCE_PASSWORD_PLACEHOLDER']"
									:value="password"
									@input="onInput"
									ref="input"
								>
							</div>
						</template>
						<div v-else @click="switchToEdit" class="im-conference-create-field-view">{{ codedValue }}</div>
					</div>
				`
			},
	};