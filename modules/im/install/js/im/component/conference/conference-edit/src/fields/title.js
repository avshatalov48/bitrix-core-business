import {ConferenceFieldState} from "im.const";

export const FieldTitle =
	{
		name: 'conference-field-title',
		component:
			{
				props:
				{
					mode: {type: String},
					title: {type: String},
					defaultValue: {type: String}
				},
				data: function() {
					return {
						name: 'title'
					}
				},
				computed:
				{
					isViewMode()
					{
						return this.mode === ConferenceFieldState.view
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
						this.$emit('titleChange', event.target.value);
					},
					onFocus(fieldName)
					{
						if (this.name === fieldName)
						{
							this.$nextTick(() => {
								this.$refs['input'].focus();
							});
						}
					}
				},
				created()
				{
					this.$root.$on('focus', this.onFocus);
				},
				template: `
					<div class="im-conference-create-section">
						<div class="im-conference-create-field">
							<label class="im-conference-create-label" for="im-conference-create-field-title">{{ localize['BX_IM_COMPONENT_CONFERENCE_TITLE_LABEL'] }}</label>
							<div v-if="!isViewMode" class="im-conference-create-field-title-container ui-ctl">
								<input
									type="text"
									id="im-conference-create-field-title"
									class="ui-ctl-element"
									:name="name"
									:placeholder="defaultValue"
									:value="title"
									@input="onInput"
									ref="input"
								>
							</div>
							<div v-else @click="switchToEdit" class="im-conference-create-field-view">{{ title }}</div>
						</div>
					</div>
				`
			},
	};