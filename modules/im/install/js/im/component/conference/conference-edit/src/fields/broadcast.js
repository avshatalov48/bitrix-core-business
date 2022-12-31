import "ui.vue.components.hint";
import {ConferenceFieldState} from "im.const";
import { TagSelector } from "ui.entity-selector";

export const FieldBroadcast =
{
	name: 'conference-field-broadcast',
	component:
		{
			props:
				{
					mode: {type: String},
					broadcastMode: {type: Boolean},
					chatHost: {type: Object},
					selectedPresenters: {type: Array}
				},
			data: function() {
				return {
					name: 'broadcast'
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
					if (this.broadcastMode)
					{
						return this.localize['BX_IM_COMPONENT_CONFERENCE_BROADCAST_MODE_ON'];
					}
					else
					{
						return this.localize['BX_IM_COMPONENT_CONFERENCE_BROADCAST_MODE_OFF'];
					}
				},
				presenterListForSelector()
				{
					return this.selectedPresenters.map(user => {
						return ['user', user.id];
					});
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
				onBroadcastModeChange()
				{
					this.$emit('broadcastModeChange');
				},
				onSwitchModeForAll(mode)
				{
					if (mode === ConferenceFieldState.edit)
					{
						this.switchToEdit();
					}
				},
				onPresenterSelect(event)
				{
					this.$emit('presenterSelect', event);
					//this.updatePlanner();
				},
				onPresenterDeselect(event)
				{
					this.$emit('presenterDeselect', event);
					//this.updatePlanner();
				},
				getUserAvatarStyle(user)
				{
					if (user.avatar)
					{
						return {
							backgroundImage: `url('${encodeURI(user.avatar)}')`
						};
					}

					return {};
				},
				initPresenterSelector()
				{
					this.presenterSelector = new TagSelector({
						id: 'presenter-tag-selector',
						dialogOptions: {
							id: 'presenter-tag-selector',
							preselectedItems: this.presenterListForSelector,
							events: {
								'Item:onSelect': (event) => {
									this.onPresenterSelect(event);
								},
								'Item:onDeselect': (event) => {
									this.onPresenterDeselect(event);
								}
							},
							entities: [
								{id: 'user'},
								{id: 'department'}
							],
						}
					});
				},
				onUpdatePresenterSelector()
				{
					this.$nextTick(() => {
						this.$refs['presenterSelector'].innerHTML = '';
						this.initPresenterSelector();
						this.presenterSelector.renderTo(this.$refs['presenterSelector']);
					});
				}
			},
			mounted()
			{
				this.initPresenterSelector();
				this.presenterSelector.renderTo(this.$refs['presenterSelector']);

				this.$root.$on('switchModeForAll', (mode) => {
					this.onSwitchModeForAll(mode);
				});

				this.$root.$on('updatePresenterSelector', () => {
					this.onUpdatePresenterSelector();
				})
			},
			template: `
				<div class="im-conference-create-section im-conference-create-broadcast-section">
					<div class="im-conference-create-broadcast-section-title">
						<label class="im-conference-create-label" for="im-conference-create-field-broadcast">{{ localize['BX_IM_COMPONENT_CONFERENCE_BROADCAST_LABEL'] }}</label>
						<bx-hint :text="localize['BX_IM_COMPONENT_CONFERENCE_BROADCAST_HINT']"/>
					</div>
					<div v-show="!isViewMode">
						<div class="im-conference-create-field-inline im-conference-create-field-broadcast">
							<input @input="onBroadcastModeChange" type="checkbox" id="im-conference-create-field-broadcast-checkbox" :checked="broadcastMode">
							<label class="im-conference-create-label" for="im-conference-create-field-broadcast-checkbox">{{ localize['BX_IM_COMPONENT_CONFERENCE_BROADCAST_CHECKBOX_LABEL'] }}</label>
						</div>
						<div v-show="broadcastMode" class="im-conference-create-user-selector-block">
							<div class="im-conference-create-field">
								<label class="im-conference-create-label im-conference-create-label-broadcast" for="im-conference-create-field-user-selector">{{ localize['BX_IM_COMPONENT_CONFERENCE_PRESENTER_SELECTOR_LABEL'] }}</label>
								<div class="im-conference-create-user-selector" ref="presenterSelector"></div>
							</div>
						</div>
					</div>
					<div v-show="isViewMode">
						<div @click="switchToEdit" class="im-conference-create-field-view">{{ codedValue }}</div>
						<div v-if="broadcastMode" @click="switchToEdit" class="im-conference-create-field im-conference-create-users-view">
							<div class="im-conference-create-label">{{ localize['BX_IM_COMPONENT_CONFERENCE_PRESENTER_SELECTOR_LABEL'] }}</div>
							<div class="im-conference-create-users-view-content">
								<div v-for="user in selectedPresenters" :key="user.id" class="im-conference-create-users-view-item">
									<div class="im-conference-create-users-view-avatar" :style="getUserAvatarStyle(user)"></div>
									<div class="im-conference-create-users-view-title">{{ user.title }}</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			`
		},
};