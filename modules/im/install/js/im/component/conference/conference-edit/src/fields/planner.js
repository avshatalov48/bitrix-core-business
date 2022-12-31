import { Reflection, ajax as Ajax } from "main.core";
import { TagSelector } from "ui.entity-selector";
import { Planner } from "calendar.planner";
import { Util } from "calendar.util";
import { ConferenceFieldState } from "im.const";
import { BaseEvent } from "main.core.events";

export const FieldPlanner =
	{
		name: 'conference-field-planner',
		component:
			{
				props:
					{
						mode: {type: String},
						selectedUsers: {type: Array, default: []},
						chatHost: {type: Object, default: []},
						selectedDate: {type: String, default: ''},
						selectedTime: {type: String, default: ''},
						selectedDuration: {type: String, default: '30'},
						selectedDurationType: {type: String, default: 'm'}
					},
				data: () => {
					return {
						name: 'planner',
						clockInstance: null,
						planner: null
					}
				},
				computed:
					{
						isViewMode()
						{
							return this.mode === ConferenceFieldState.view;
						},
						userListForPlanner()
						{
							return this.selectedUsers.map(user => {
								return `U${user.id}`;
							});
						},
						userListForSelector()
						{
							return this.selectedUsers.map(user => {
								return ['user', user.id];
							});
						},
						formattedDateForView()
						{
							return `${this.selectedDate}, ${this.selectedTime}`;
						},
						formattedDurationForView()
						{
							let durationTypeText;
							if (this.selectedDurationType === 'm')
							{
								durationTypeText = this.localize('BX_IM_COMPONENT_CONFERENCE_DURATION_MINUTES');
							}
							else if (this.selectedDurationType === 'h')
							{
								durationTypeText = this.localize('BX_IM_COMPONENT_CONFERENCE_DURATION_HOURS');
							}

							return `${this.selectedDuration} ${durationTypeText}`;
						},
						startDateTime()
						{
							return BX.parseDate(`${this.selectedDate} ${this.selectedTime}`);
						},
						endDateTime()
						{
							let duration = Number(this.selectedDuration);
							const durationType = this.selectedDurationType;

							if (durationType === 'h')
							{
								duration *= 60 * 60 * 1000;
							}
							else
							{
								duration *= 60 * 1000;
							}

							const endDateTime = new Date();
							endDateTime.setTime(this.startDateTime.getTime() + duration);

							return endDateTime;
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
							this.$nextTick(() => {
								//this.userSelector.renderTo(this.$refs['userSelector']);
								//this.initPlanner();
								//this.updatePlanner();
							});
						},
						onDateFieldClick(event)
						{
							if (Reflection.getClass('BX.calendar'))
							{
								BX.calendar({
									node: event.currentTarget,
									field: this.$refs['dateInput'],
									bTime: false,
									callback_after: (event) => {
										this.$emit('dateChange', event);
									}
								})
							}

							return false;
						},
						onTimeFieldClick()
						{
							this.clockInstance.setNode(this.$refs['timeInput']);
							this.clockInstance.setTime(this.convertToSeconds(this.selectedTime));
							this.clockInstance.setCallback((value) => {
								this.$emit('timeChange', value);
								BX.fireEvent(this.$refs['timeInput'], 'change');
								this.clockInstance.closeWnd();
							});
							this.clockInstance.Show();
						},
						onUpdateDateTime()
						{
							//$nextTick didn't help there
							setTimeout(() => {
								this.planner.updateSelector(this.startDateTime, this.endDateTime, false);
							}, 0);
						},
						onDurationChange(event)
						{
							this.$emit('durationChange', event.target.value);
							this.onUpdateDateTime();
						},
						onDurationTypeChange(event)
						{
							this.$emit('durationTypeChange', event.target.value);
							this.onUpdateDateTime();
						},
						convertToSeconds(time)
						{
							//method converts string '13:12" or '03:20 am' to number of seconds
							const parts = time.split(/[\s:]+/);
							let hours = parseInt(parts[0], 10);
							const minutes = parseInt(parts[1], 10);

							if (parts.length === 3)
							{
								const modifier = parts[2];
								if (modifier === 'pm' && hours < 12)
								{
									//'03:00 pm' => 15:00
									hours = hours + 12;
								}
								if (modifier === 'am' && hours === 12)
								{
									//'12:00 am' => 0:00
									hours = 0;
								}
							}

							const secondsInHours = hours * 3600;
							const secondsInMinutes = minutes * 60;

							return secondsInHours + secondsInMinutes;
						},
						onUserSelect(event)
						{
							this.$emit('userSelect', event);
							//this.updatePlanner();
						},
						onUserDeselect(event)
						{
							this.$emit('userDeselect', event);
							//this.updatePlanner();
						},
						onUpdateUserSelector()
						{
							this.$nextTick(() => {
								this.$refs['userSelector'].innerHTML = '';
								this.initUserSelector();
								this.userSelector.renderTo(this.$refs['userSelector']);
							});
						},
						onSwitchModeForAll(mode)
						{
							if (mode === ConferenceFieldState.edit)
							{
								this.switchToEdit();
							}
						},
						initUserSelector()
						{
							this.userSelector = new TagSelector({
								id: 'user-tag-selector',
								dialogOptions: {
									id: 'user-tag-selector',
									preselectedItems: this.userListForSelector,
									undeselectedItems: [['user', this.chatHost.ID]],
									events: {
										'Item:onSelect': (event) => {
											this.onUserSelect(event);
										},
										'Item:onDeselect': (event) => {
											this.onUserDeselect(event);
										}
									},
									entities: [
										{id: 'user'},
										{id: 'department'}
									],
								}
							});
						},
						initClock()
						{
							this.clockInstance = new BX.CClockSelector({
								start_time: this.convertToSeconds(this.selectedTime),
								node: this.$refs['timeInput'],
								callback: () => {}
							});
						},
						initPlanner()
						{
							this.planner = new Planner({
								wrap: this.$refs['plannerNode'],
								showEntryName: true,
								showEntriesHeader: false,
								entriesListWidth: 200,
								compactMode: false
							});
							this.planner.show();
							this.planner.subscribe('onDateChange', (event) => {
								this.onPlannerSelectorChange(event);
							});
						},
						updatePlanner()
						{
							if (this.selectedUsers.length > 0)
							{
								Ajax.runAction('calendar.api.calendarajax.updatePlanner', {
									data: {
										codes: this.userListForPlanner,
										dateFrom: Util.formatDate(this.startDateTime.getTime() - Util.getDayLength() * 3),
										dateTo: Util.formatDate(this.startDateTime.getTime() + Util.getDayLength() * 10)
									}
								})
								.then(response => {
									this.planner.update(
										response.data.entries,
										response.data.accessibility
									);
									this.planner.updateSelector(this.startDateTime, this.endDateTime, false);
								})
								.catch(error => {});
							}
						},
						onPlannerSelectorChange(event)
						{
							if (event instanceof BaseEvent)
							{
								let data = event.getData();

								const startDateTime = data.dateFrom;
								const duration = (data.dateTo - data.dateFrom) / 1000 / 60; //duration in minutes
								const durationType = this.selectedDurationType;

								this.$emit('dateChange', startDateTime);
								this.$emit('timeChange', this.$parent.formatTime(startDateTime));

								if (durationType === 'h' && duration % 60 === 0)
								{
									this.$emit('durationChange', duration / 60);
									this.$emit('durationTypeChange', 'h');
								}
								else
								{
									this.$emit('durationChange', duration);
									this.$emit('durationTypeChange', 'm');
								}
							}
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
						}
					},
				created()
				{
				},
				mounted()
				{
					this.initUserSelector();
					this.userSelector.renderTo(this.$refs['userSelector']);
					//this.initClock();
					//this.initPlanner();
					//this.updatePlanner();

					this.$root.$on('switchModeForAll', (mode) => {
						this.onSwitchModeForAll(mode);
					});

					this.$root.$on('updateUserSelector', () => {
						this.onUpdateUserSelector();
					})
				},
				template: `
					<div class="im-conference-create-section im-conference-create-planner-block">
						<!-- Date block -->
<!--						<div v-if="!isViewMode" class="im-conference-create-date-block">-->
<!--							<div class="im-conference-create-date-block-left">-->
<!--								<label class="im-conference-create-label" for="im-conference-create-field-date-time">{{ localize['BX_IM_COMPONENT_CONFERENCE_START_DATE_AND_TIME'] }}</label>-->
<!--								<div class="im-conference-create-date-block-left-fields">-->
<!--									&lt;!&ndash; Date field &ndash;&gt;-->
<!--									<div @click="onDateFieldClick" class="ui-ctl ui-ctl-after-icon ui-ctl-date im-conference-create-field-date-container">-->
<!--										<div class="ui-ctl-after ui-ctl-icon-calendar"></div>-->
<!--										<input @change="onUpdateDateTime" type="text" class="ui-ctl-element" ref="dateInput" :value="selectedDate">-->
<!--									</div>-->
<!--									&lt;!&ndash; Time field &ndash;&gt;-->
<!--									<div @click="onTimeFieldClick" class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown im-conference-create-field-time-container">-->
<!--										<div class="ui-ctl-after ui-ctl-icon-angle"></div>-->
<!--										<div @change="onUpdateDateTime" class="ui-ctl-element" ref="timeInput">{{ selectedTime }}</div>-->
<!--									</div>-->
<!--								</div>-->
<!--							</div>-->
<!--							<div class="im-conference-create-date-block-right">-->
<!--								<label class="im-conference-create-label" for="im-conference-create-field-date-time">{{ localize['BX_IM_COMPONENT_CONFERENCE_DURATION'] }}</label>-->
<!--								<div class="im-conference-create-date-block-right-fields">-->
<!--									&lt;!&ndash; Duration field &ndash;&gt;-->
<!--									<div class="ui-ctl im-conference-create-field-duration-container">-->
<!--										<input @change="onDurationChange" type="text" class="ui-ctl-element" :value="selectedDuration">-->
<!--									</div>-->
<!--									&lt;!&ndash; Duration type field &ndash;&gt;-->
<!--									<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown im-conference-create-field-duration-type-container">-->
<!--										<div class="ui-ctl-after ui-ctl-icon-angle"></div>-->
<!--										<select @change="onDurationTypeChange" class="ui-ctl-element">-->
<!--											<option value="m" :selected="selectedDurationType === 'm'">{{ localize['BX_IM_COMPONENT_CONFERENCE_DURATION_MINUTES'] }}</option>-->
<!--											<option value="h" :selected="selectedDurationType === 'h'">{{ localize['BX_IM_COMPONENT_CONFERENCE_DURATION_HOURS'] }}</option>-->
<!--										</select>-->
<!--									</div>-->
<!--								</div>-->
<!--							</div>-->
<!--						</div>-->
<!--						<template v-else-if="isViewMode">-->
<!--							<div class="im-conference-create-field">-->
<!--								<div class="im-conference-create-label">{{ localize['BX_IM_COMPONENT_CONFERENCE_START_DATE_AND_TIME'] }}</div>-->
<!--								<div @click="switchToEdit" class="im-conference-create-field-view">{{ formattedDateForView }}</div>-->
<!--							</div>-->
<!--							<div class="im-conference-create-field">-->
<!--								<div class="im-conference-create-label">{{ localize['BX_IM_COMPONENT_CONFERENCE_DURATION'] }}</div>-->
<!--								<div @click="switchToEdit" class="im-conference-create-field-view">{{ formattedDurationForView }}</div>-->
<!--							</div>-->
<!--						</template>-->
						<div v-show="!isViewMode">
<!--							<div class="im-conference-create-delimiter"></div>-->
							<!-- User selector block -->
							<div class="im-conference-create-user-selector-block">
								<div class="im-conference-create-field">
									<label class="im-conference-create-label" for="im-conference-create-field-user-selector">{{ localize['BX_IM_COMPONENT_CONFERENCE_USER_SELECTOR_LABEL'] }}</label>
									<div class="im-conference-create-user-selector" ref="userSelector"></div>
								</div>
							</div>
							<!-- Planner block -->
<!--							<div v-show="selectedUsers.length > 0" class="im-conference-create-planner-block" ref="plannerNode"></div>-->
						</div>
						<div v-show="isViewMode" class="im-conference-create-field im-conference-create-users-view">
							<div class="im-conference-create-label">{{ localize['BX_IM_COMPONENT_CONFERENCE_USER_SELECTOR_LABEL'] }}</div>
							<div @click="switchToEdit" class="im-conference-create-users-view-content">
								<div v-for="user in selectedUsers" :key="user.id" class="im-conference-create-users-view-item">
									<div class="im-conference-create-users-view-avatar" :style="getUserAvatarStyle(user)"></div>
									<div class="im-conference-create-users-view-title">{{ user.title }}</div>
								</div>
							</div>
						</div>
					</div>
				`
			},
	};