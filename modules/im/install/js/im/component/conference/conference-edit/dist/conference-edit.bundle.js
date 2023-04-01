this.BX = this.BX || {};
(function (exports,ui_vue,im_lib_logger,im_lib_clipboard,main_core,calendar_planner,calendar_util,main_core_events,ui_vue_components_hint,im_const,ui_entitySelector) {
	'use strict';

	var FieldTitle = {
	  name: 'conference-field-title',
	  component: {
	    props: {
	      mode: {
	        type: String
	      },
	      title: {
	        type: String
	      },
	      defaultValue: {
	        type: String
	      }
	    },
	    data: function data() {
	      return {
	        name: 'title'
	      };
	    },
	    computed: {
	      isViewMode: function isViewMode() {
	        return this.mode === im_const.ConferenceFieldState.view;
	      },
	      localize: function localize() {
	        return BX.message;
	      }
	    },
	    methods: {
	      switchToEdit: function switchToEdit() {
	        this.$emit('switchToEdit', this.name);
	      },
	      onInput: function onInput(event) {
	        this.$emit('titleChange', event.target.value);
	      },
	      onFocus: function onFocus(fieldName) {
	        var _this = this;
	        if (this.name === fieldName) {
	          this.$nextTick(function () {
	            _this.$refs['input'].focus();
	          });
	        }
	      }
	    },
	    created: function created() {
	      this.$root.$on('focus', this.onFocus);
	    },
	    template: "\n\t\t\t\t\t<div class=\"im-conference-create-section\">\n\t\t\t\t\t\t<div class=\"im-conference-create-field\">\n\t\t\t\t\t\t\t<label class=\"im-conference-create-label\" for=\"im-conference-create-field-title\">{{ localize['BX_IM_COMPONENT_CONFERENCE_TITLE_LABEL'] }}</label>\n\t\t\t\t\t\t\t<div v-if=\"!isViewMode\" class=\"im-conference-create-field-title-container ui-ctl\">\n\t\t\t\t\t\t\t\t<input\n\t\t\t\t\t\t\t\t\ttype=\"text\"\n\t\t\t\t\t\t\t\t\tid=\"im-conference-create-field-title\"\n\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t\t\t\t\t:name=\"name\"\n\t\t\t\t\t\t\t\t\t:placeholder=\"defaultValue\"\n\t\t\t\t\t\t\t\t\t:value=\"title\"\n\t\t\t\t\t\t\t\t\t@input=\"onInput\"\n\t\t\t\t\t\t\t\t\tref=\"input\"\n\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div v-else @click=\"switchToEdit\" class=\"im-conference-create-field-view\">{{ title }}</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t"
	  }
	};

	var FieldPassword = {
	  name: 'conference-field-password',
	  component: {
	    props: {
	      mode: {
	        type: String
	      },
	      password: {
	        type: String
	      },
	      passwordNeeded: {
	        type: Boolean
	      }
	    },
	    data: function data() {
	      return {
	        name: 'password'
	      };
	    },
	    computed: {
	      isViewMode: function isViewMode() {
	        return this.mode === im_const.ConferenceFieldState.view;
	      },
	      codedValue: function codedValue() {
	        if (this.passwordNeeded) {
	          return "".concat(this.localize['BX_IM_COMPONENT_CONFERENCE_PASSWORD_EXISTS'], " (").concat(this.password.replace(/./g, '*'), ")");
	        } else {
	          return this.localize['BX_IM_COMPONENT_CONFERENCE_NO_PASSWORD'];
	        }
	      },
	      localize: function localize() {
	        return BX.message;
	      }
	    },
	    methods: {
	      switchToEdit: function switchToEdit() {
	        this.$emit('switchToEdit', this.name);
	      },
	      onInput: function onInput(event) {
	        this.$emit('passwordChange', event.target.value);
	      },
	      onPasswordNeededChange: function onPasswordNeededChange() {
	        this.$emit('passwordNeededChange');
	      },
	      onFocus: function onFocus(fieldName) {
	        var _this = this;
	        if (this.name === fieldName) {
	          this.$nextTick(function () {
	            if (_this.$refs['input']) {
	              _this.$refs['input'].focus();
	            }
	          });
	        }
	      }
	    },
	    created: function created() {
	      this.$root.$on('focus', this.onFocus);
	    },
	    template: "\n\t\t\t\t\t<div class=\"im-conference-create-section im-conference-create-password-section\">\n\t\t\t\t\t\t<label class=\"im-conference-create-label\" for=\"im-conference-create-field-password\">{{ localize['BX_IM_COMPONENT_CONFERENCE_PASSWORD_LABEL'] }}</label>\n\t\t\t\t\t\t<template v-if=\"!isViewMode\">\n\t\t\t\t\t\t\t<div class=\"im-conference-create-field-inline\">\n\t\t\t\t\t\t\t\t<input @input=\"onPasswordNeededChange\" type=\"checkbox\" id=\"im-conference-create-field-password-checkbox\" :checked=\"passwordNeeded\">\n\t\t\t\t\t\t\t\t<label class=\"im-conference-create-label\" for=\"im-conference-create-field-password-checkbox\">{{ localize['BX_IM_COMPONENT_CONFERENCE_PASSWORD_CHECKBOX_LABEL'] }}</label>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div v-if=\"passwordNeeded\" class=\"im-conference-create-field-password-container ui-ctl\">\n\t\t\t\t\t\t\t\t<input\n\t\t\t\t\t\t\t\t\ttype=\"text\"\n\t\t\t\t\t\t\t\t\tid=\"im-conference-create-field-password\"\n\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t\t\t\t\t:name=\"name\"\n\t\t\t\t\t\t\t\t\t:placeholder=\"localize['BX_IM_COMPONENT_CONFERENCE_PASSWORD_PLACEHOLDER']\"\n\t\t\t\t\t\t\t\t\t:value=\"password\"\n\t\t\t\t\t\t\t\t\t@input=\"onInput\"\n\t\t\t\t\t\t\t\t\tref=\"input\"\n\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<div v-else @click=\"switchToEdit\" class=\"im-conference-create-field-view\">{{ codedValue }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t"
	  }
	};

	var FieldInvitation = {
	  name: 'conference-field-invitation',
	  component: {
	    props: {
	      invitation: {
	        type: Object
	      },
	      chatHost: {
	        type: Object
	      },
	      title: {
	        type: String
	      },
	      defaultTitle: {
	        type: String
	      },
	      publicLink: {
	        type: String
	      },
	      formMode: {
	        type: String
	      }
	    },
	    data: function data() {
	      return {
	        initialValue: null,
	        editedValue: null
	      };
	    },
	    computed: {
	      isViewMode: function isViewMode() {
	        return this.invitation.mode === im_const.ConferenceFieldState.view;
	      },
	      isFormCreateMode: function isFormCreateMode() {
	        return this.formMode === im_const.ConferenceFieldState.create;
	      },
	      avatarClasses: function avatarClasses() {
	        var classes = ['im-conference-create-invitation-user-avatar'];
	        if (!this.chatHost.AVATAR) {
	          classes.push('im-conference-create-invitation-user-avatar-default');
	        }
	        return classes;
	      },
	      avatarStyles: function avatarStyles() {
	        var styles = {};
	        if (this.chatHost.AVATAR) {
	          styles.backgroundImage = "url(".concat(this.chatHost.AVATAR, ")");
	        }
	        return styles;
	      },
	      formattedInvitation: function formattedInvitation() {
	        var title = this.title ? this.title : '';
	        if (this.isFormCreateMode && !this.title) {
	          title = this.defaultTitle;
	        }
	        return this.invitation.value.replace(/#CREATOR#/gm, main_core.Text.encode(this.chatHost.FULL_NAME)).replace(/#TITLE#/gm, "\"".concat(main_core.Text.encode(title), "\"")).replace(/#LINK#/gm, "<a href=\"".concat(this.publicLink, "\" target=\"_blank\">").concat(this.publicLink, "</a>"));
	      },
	      localize: function localize() {
	        return BX.message;
	      }
	    },
	    methods: {
	      onEditClick: function onEditClick() {
	        var _this = this;
	        var contentWidth = this.$refs['view'].offsetWidth;
	        var contentHeight = this.$refs['view'].offsetHeight;
	        this.invitation.mode = im_const.ConferenceFieldState.edit;
	        this.invitation.value = main_core.Text.decode(this.invitation.value);
	        this.$nextTick(function () {
	          _this.$refs['editor'].style.width = contentWidth + 20 + 'px';
	          _this.$refs['editor'].style.height = contentHeight + 30 + 'px';
	          _this.$refs['editor'].focus();
	        });
	      },
	      onInput: function onInput(event) {
	        if (!this.initialValue) {
	          this.initialValue = this.invitation.value;
	        }
	        this.editedValue = main_core.Text.encode(event.target.value);
	      },
	      saveChanges: function saveChanges() {
	        if (this.editedValue && this.initialValue && this.initialValue !== this.editedValue) {
	          this.invitation.value = this.editedValue;
	          this.initialValue = null;
	          this.editedValue = null;
	          this.$emit('invitationUpdate', this.invitation.value);
	        } else {
	          this.invitation.value = main_core.Text.encode(this.invitation.value);
	        }
	        this.invitation.mode = im_const.ConferenceFieldState.view;
	      },
	      discardChanges: function discardChanges() {
	        if (this.initialValue) {
	          this.invitation.value = this.initialValue;
	          this.initialValue = null;
	          this.editedValue = null;
	        }
	        this.invitation.value = main_core.Text.encode(this.invitation.value);
	        this.invitation.mode = im_const.ConferenceFieldState.view;
	      }
	    },
	    created: function created() {
	      if (this.isFormCreateMode || !this.invitation.value) {
	        this.invitation.value = this.localize['BX_IM_COMPONENT_CONFERENCE_DEFAULT_INVITATION'];
	      }
	      if (!this.isFormCreateMode && this.invitation.value) {
	        this.invitation.value = main_core.Text.encode(this.invitation.value);
	      }
	    },
	    template: "\n\t\t\t\t\t<div>\n\t\t\t\t\t\t<div class=\"im-conference-create-section im-conference-create-invitation-title\">\n\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CONFERENCE_INVITATION_TITLE'] }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"im-conference-create-section im-conference-create-invitation-wrap\">\n\t\t\t\t\t\t\t<div class=\"im-conference-create-invitation-user\">\n\t\t\t\t\t\t\t\t<div :class=\"avatarClasses\" :style=\"avatarStyles\"></div>\n\t\t\t\t\t\t\t\t<div class=\"im-conference-create-invitation-user-name\">{{ chatHost.FIRST_NAME }}</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"im-conference-create-invitation-content\">\n\t\t\t\t\t\t\t\t<template v-if=\"isViewMode\">\n\t\t\t\t\t\t\t\t\t<div @click=\"onEditClick\" v-html=\"formattedInvitation\" contenteditable=\"false\" ref=\"view\" class=\"im-conference-create-invitation-content-text\"></div>\n\t\t\t\t\t\t\t\t\t<div @click=\"onEditClick\" class=\"im-conference-create-invitation-edit\"></div>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t\t\t<textarea @input=\"onInput\" :value=\"invitation.value\" class=\"im-conference-create-invitation-editor\" ref=\"editor\"></textarea>\n\t\t\t\t\t\t\t\t\t<div>\n\t\t\t\t\t\t\t\t\t\t<button @click=\"saveChanges\" class=\"ui-btn ui-btn-sm ui-btn-primary\">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_SAVE'] }}</button>\n\t\t\t\t\t\t\t\t\t\t<button @click=\"discardChanges\" class=\"ui-btn ui-btn-sm ui-btn-light\">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_CANCEL'] }}</button>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t"
	  }
	};

	var FieldPlanner = {
	  name: 'conference-field-planner',
	  component: {
	    props: {
	      mode: {
	        type: String
	      },
	      selectedUsers: {
	        type: Array,
	        "default": []
	      },
	      chatHost: {
	        type: Object,
	        "default": []
	      },
	      selectedDate: {
	        type: String,
	        "default": ''
	      },
	      selectedTime: {
	        type: String,
	        "default": ''
	      },
	      selectedDuration: {
	        type: String,
	        "default": '30'
	      },
	      selectedDurationType: {
	        type: String,
	        "default": 'm'
	      }
	    },
	    data: function data() {
	      return {
	        name: 'planner',
	        clockInstance: null,
	        planner: null
	      };
	    },
	    computed: {
	      isViewMode: function isViewMode() {
	        return this.mode === im_const.ConferenceFieldState.view;
	      },
	      userListForPlanner: function userListForPlanner() {
	        return this.selectedUsers.map(function (user) {
	          return "U".concat(user.id);
	        });
	      },
	      userListForSelector: function userListForSelector() {
	        return this.selectedUsers.map(function (user) {
	          return ['user', user.id];
	        });
	      },
	      formattedDateForView: function formattedDateForView() {
	        return "".concat(this.selectedDate, ", ").concat(this.selectedTime);
	      },
	      formattedDurationForView: function formattedDurationForView() {
	        var durationTypeText;
	        if (this.selectedDurationType === 'm') {
	          durationTypeText = this.localize('BX_IM_COMPONENT_CONFERENCE_DURATION_MINUTES');
	        } else if (this.selectedDurationType === 'h') {
	          durationTypeText = this.localize('BX_IM_COMPONENT_CONFERENCE_DURATION_HOURS');
	        }
	        return "".concat(this.selectedDuration, " ").concat(durationTypeText);
	      },
	      startDateTime: function startDateTime() {
	        return BX.parseDate("".concat(this.selectedDate, " ").concat(this.selectedTime));
	      },
	      endDateTime: function endDateTime() {
	        var duration = Number(this.selectedDuration);
	        var durationType = this.selectedDurationType;
	        if (durationType === 'h') {
	          duration *= 60 * 60 * 1000;
	        } else {
	          duration *= 60 * 1000;
	        }
	        var endDateTime = new Date();
	        endDateTime.setTime(this.startDateTime.getTime() + duration);
	        return endDateTime;
	      },
	      localize: function localize() {
	        return BX.message;
	      }
	    },
	    methods: {
	      switchToEdit: function switchToEdit() {
	        this.$emit('switchToEdit', this.name);
	        this.$nextTick(function () {
	          //this.userSelector.renderTo(this.$refs['userSelector']);
	          //this.initPlanner();
	          //this.updatePlanner();
	        });
	      },
	      onDateFieldClick: function onDateFieldClick(event) {
	        var _this = this;
	        if (main_core.Reflection.getClass('BX.calendar')) {
	          BX.calendar({
	            node: event.currentTarget,
	            field: this.$refs['dateInput'],
	            bTime: false,
	            callback_after: function callback_after(event) {
	              _this.$emit('dateChange', event);
	            }
	          });
	        }
	        return false;
	      },
	      onTimeFieldClick: function onTimeFieldClick() {
	        var _this2 = this;
	        this.clockInstance.setNode(this.$refs['timeInput']);
	        this.clockInstance.setTime(this.convertToSeconds(this.selectedTime));
	        this.clockInstance.setCallback(function (value) {
	          _this2.$emit('timeChange', value);
	          BX.fireEvent(_this2.$refs['timeInput'], 'change');
	          _this2.clockInstance.closeWnd();
	        });
	        this.clockInstance.Show();
	      },
	      onUpdateDateTime: function onUpdateDateTime() {
	        var _this3 = this;
	        //$nextTick didn't help there
	        setTimeout(function () {
	          _this3.planner.updateSelector(_this3.startDateTime, _this3.endDateTime, false);
	        }, 0);
	      },
	      onDurationChange: function onDurationChange(event) {
	        this.$emit('durationChange', event.target.value);
	        this.onUpdateDateTime();
	      },
	      onDurationTypeChange: function onDurationTypeChange(event) {
	        this.$emit('durationTypeChange', event.target.value);
	        this.onUpdateDateTime();
	      },
	      convertToSeconds: function convertToSeconds(time) {
	        //method converts string '13:12" or '03:20 am' to number of seconds
	        var parts = time.split(/[\s:]+/);
	        var hours = parseInt(parts[0], 10);
	        var minutes = parseInt(parts[1], 10);
	        if (parts.length === 3) {
	          var modifier = parts[2];
	          if (modifier === 'pm' && hours < 12) {
	            //'03:00 pm' => 15:00
	            hours = hours + 12;
	          }
	          if (modifier === 'am' && hours === 12) {
	            //'12:00 am' => 0:00
	            hours = 0;
	          }
	        }
	        var secondsInHours = hours * 3600;
	        var secondsInMinutes = minutes * 60;
	        return secondsInHours + secondsInMinutes;
	      },
	      onUserSelect: function onUserSelect(event) {
	        this.$emit('userSelect', event);
	        //this.updatePlanner();
	      },
	      onUserDeselect: function onUserDeselect(event) {
	        this.$emit('userDeselect', event);
	        //this.updatePlanner();
	      },
	      onUpdateUserSelector: function onUpdateUserSelector() {
	        var _this4 = this;
	        this.$nextTick(function () {
	          _this4.$refs['userSelector'].innerHTML = '';
	          _this4.initUserSelector();
	          _this4.userSelector.renderTo(_this4.$refs['userSelector']);
	        });
	      },
	      onSwitchModeForAll: function onSwitchModeForAll(mode) {
	        if (mode === im_const.ConferenceFieldState.edit) {
	          this.switchToEdit();
	        }
	      },
	      initUserSelector: function initUserSelector() {
	        var _this5 = this;
	        this.userSelector = new ui_entitySelector.TagSelector({
	          id: 'user-tag-selector',
	          dialogOptions: {
	            id: 'user-tag-selector',
	            preselectedItems: this.userListForSelector,
	            undeselectedItems: [['user', this.chatHost.ID]],
	            events: {
	              'Item:onSelect': function ItemOnSelect(event) {
	                _this5.onUserSelect(event);
	              },
	              'Item:onDeselect': function ItemOnDeselect(event) {
	                _this5.onUserDeselect(event);
	              }
	            },
	            entities: [{
	              id: 'user'
	            }, {
	              id: 'department'
	            }]
	          }
	        });
	      },
	      initClock: function initClock() {
	        this.clockInstance = new BX.CClockSelector({
	          start_time: this.convertToSeconds(this.selectedTime),
	          node: this.$refs['timeInput'],
	          callback: function callback() {}
	        });
	      },
	      initPlanner: function initPlanner() {
	        var _this6 = this;
	        this.planner = new calendar_planner.Planner({
	          wrap: this.$refs['plannerNode'],
	          showEntryName: true,
	          showEntriesHeader: false,
	          entriesListWidth: 200,
	          compactMode: false
	        });
	        this.planner.show();
	        this.planner.subscribe('onDateChange', function (event) {
	          _this6.onPlannerSelectorChange(event);
	        });
	      },
	      updatePlanner: function updatePlanner() {
	        var _this7 = this;
	        if (this.selectedUsers.length > 0) {
	          main_core.ajax.runAction('calendar.api.calendarajax.updatePlanner', {
	            data: {
	              codes: this.userListForPlanner,
	              dateFrom: calendar_util.Util.formatDate(this.startDateTime.getTime() - calendar_util.Util.getDayLength() * 3),
	              dateTo: calendar_util.Util.formatDate(this.startDateTime.getTime() + calendar_util.Util.getDayLength() * 10)
	            }
	          }).then(function (response) {
	            _this7.planner.update(response.data.entries, response.data.accessibility);
	            _this7.planner.updateSelector(_this7.startDateTime, _this7.endDateTime, false);
	          })["catch"](function (error) {});
	        }
	      },
	      onPlannerSelectorChange: function onPlannerSelectorChange(event) {
	        if (event instanceof main_core_events.BaseEvent) {
	          var data = event.getData();
	          var startDateTime = data.dateFrom;
	          var duration = (data.dateTo - data.dateFrom) / 1000 / 60; //duration in minutes
	          var durationType = this.selectedDurationType;
	          this.$emit('dateChange', startDateTime);
	          this.$emit('timeChange', this.$parent.formatTime(startDateTime));
	          if (durationType === 'h' && duration % 60 === 0) {
	            this.$emit('durationChange', duration / 60);
	            this.$emit('durationTypeChange', 'h');
	          } else {
	            this.$emit('durationChange', duration);
	            this.$emit('durationTypeChange', 'm');
	          }
	        }
	      },
	      getUserAvatarStyle: function getUserAvatarStyle(user) {
	        if (user.avatar) {
	          return {
	            backgroundImage: "url('".concat(encodeURI(user.avatar), "')")
	          };
	        }
	        return {};
	      }
	    },
	    created: function created() {},
	    mounted: function mounted() {
	      var _this8 = this;
	      this.initUserSelector();
	      this.userSelector.renderTo(this.$refs['userSelector']);
	      //this.initClock();
	      //this.initPlanner();
	      //this.updatePlanner();

	      this.$root.$on('switchModeForAll', function (mode) {
	        _this8.onSwitchModeForAll(mode);
	      });
	      this.$root.$on('updateUserSelector', function () {
	        _this8.onUpdateUserSelector();
	      });
	    },
	    template: "\n\t\t\t\t\t<div class=\"im-conference-create-section im-conference-create-planner-block\">\n\t\t\t\t\t\t<!-- Date block -->\n<!--\t\t\t\t\t\t<div v-if=\"!isViewMode\" class=\"im-conference-create-date-block\">-->\n<!--\t\t\t\t\t\t\t<div class=\"im-conference-create-date-block-left\">-->\n<!--\t\t\t\t\t\t\t\t<label class=\"im-conference-create-label\" for=\"im-conference-create-field-date-time\">{{ localize['BX_IM_COMPONENT_CONFERENCE_START_DATE_AND_TIME'] }}</label>-->\n<!--\t\t\t\t\t\t\t\t<div class=\"im-conference-create-date-block-left-fields\">-->\n<!--\t\t\t\t\t\t\t\t\t&lt;!&ndash; Date field &ndash;&gt;-->\n<!--\t\t\t\t\t\t\t\t\t<div @click=\"onDateFieldClick\" class=\"ui-ctl ui-ctl-after-icon ui-ctl-date im-conference-create-field-date-container\">-->\n<!--\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-calendar\"></div>-->\n<!--\t\t\t\t\t\t\t\t\t\t<input @change=\"onUpdateDateTime\" type=\"text\" class=\"ui-ctl-element\" ref=\"dateInput\" :value=\"selectedDate\">-->\n<!--\t\t\t\t\t\t\t\t\t</div>-->\n<!--\t\t\t\t\t\t\t\t\t&lt;!&ndash; Time field &ndash;&gt;-->\n<!--\t\t\t\t\t\t\t\t\t<div @click=\"onTimeFieldClick\" class=\"ui-ctl ui-ctl-after-icon ui-ctl-dropdown im-conference-create-field-time-container\">-->\n<!--\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>-->\n<!--\t\t\t\t\t\t\t\t\t\t<div @change=\"onUpdateDateTime\" class=\"ui-ctl-element\" ref=\"timeInput\">{{ selectedTime }}</div>-->\n<!--\t\t\t\t\t\t\t\t\t</div>-->\n<!--\t\t\t\t\t\t\t\t</div>-->\n<!--\t\t\t\t\t\t\t</div>-->\n<!--\t\t\t\t\t\t\t<div class=\"im-conference-create-date-block-right\">-->\n<!--\t\t\t\t\t\t\t\t<label class=\"im-conference-create-label\" for=\"im-conference-create-field-date-time\">{{ localize['BX_IM_COMPONENT_CONFERENCE_DURATION'] }}</label>-->\n<!--\t\t\t\t\t\t\t\t<div class=\"im-conference-create-date-block-right-fields\">-->\n<!--\t\t\t\t\t\t\t\t\t&lt;!&ndash; Duration field &ndash;&gt;-->\n<!--\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl im-conference-create-field-duration-container\">-->\n<!--\t\t\t\t\t\t\t\t\t\t<input @change=\"onDurationChange\" type=\"text\" class=\"ui-ctl-element\" :value=\"selectedDuration\">-->\n<!--\t\t\t\t\t\t\t\t\t</div>-->\n<!--\t\t\t\t\t\t\t\t\t&lt;!&ndash; Duration type field &ndash;&gt;-->\n<!--\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-after-icon ui-ctl-dropdown im-conference-create-field-duration-type-container\">-->\n<!--\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>-->\n<!--\t\t\t\t\t\t\t\t\t\t<select @change=\"onDurationTypeChange\" class=\"ui-ctl-element\">-->\n<!--\t\t\t\t\t\t\t\t\t\t\t<option value=\"m\" :selected=\"selectedDurationType === 'm'\">{{ localize['BX_IM_COMPONENT_CONFERENCE_DURATION_MINUTES'] }}</option>-->\n<!--\t\t\t\t\t\t\t\t\t\t\t<option value=\"h\" :selected=\"selectedDurationType === 'h'\">{{ localize['BX_IM_COMPONENT_CONFERENCE_DURATION_HOURS'] }}</option>-->\n<!--\t\t\t\t\t\t\t\t\t\t</select>-->\n<!--\t\t\t\t\t\t\t\t\t</div>-->\n<!--\t\t\t\t\t\t\t\t</div>-->\n<!--\t\t\t\t\t\t\t</div>-->\n<!--\t\t\t\t\t\t</div>-->\n<!--\t\t\t\t\t\t<template v-else-if=\"isViewMode\">-->\n<!--\t\t\t\t\t\t\t<div class=\"im-conference-create-field\">-->\n<!--\t\t\t\t\t\t\t\t<div class=\"im-conference-create-label\">{{ localize['BX_IM_COMPONENT_CONFERENCE_START_DATE_AND_TIME'] }}</div>-->\n<!--\t\t\t\t\t\t\t\t<div @click=\"switchToEdit\" class=\"im-conference-create-field-view\">{{ formattedDateForView }}</div>-->\n<!--\t\t\t\t\t\t\t</div>-->\n<!--\t\t\t\t\t\t\t<div class=\"im-conference-create-field\">-->\n<!--\t\t\t\t\t\t\t\t<div class=\"im-conference-create-label\">{{ localize['BX_IM_COMPONENT_CONFERENCE_DURATION'] }}</div>-->\n<!--\t\t\t\t\t\t\t\t<div @click=\"switchToEdit\" class=\"im-conference-create-field-view\">{{ formattedDurationForView }}</div>-->\n<!--\t\t\t\t\t\t\t</div>-->\n<!--\t\t\t\t\t\t</template>-->\n\t\t\t\t\t\t<div v-show=\"!isViewMode\">\n<!--\t\t\t\t\t\t\t<div class=\"im-conference-create-delimiter\"></div>-->\n\t\t\t\t\t\t\t<!-- User selector block -->\n\t\t\t\t\t\t\t<div class=\"im-conference-create-user-selector-block\">\n\t\t\t\t\t\t\t\t<div class=\"im-conference-create-field\">\n\t\t\t\t\t\t\t\t\t<label class=\"im-conference-create-label\" for=\"im-conference-create-field-user-selector\">{{ localize['BX_IM_COMPONENT_CONFERENCE_USER_SELECTOR_LABEL'] }}</label>\n\t\t\t\t\t\t\t\t\t<div class=\"im-conference-create-user-selector\" ref=\"userSelector\"></div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<!-- Planner block -->\n<!--\t\t\t\t\t\t\t<div v-show=\"selectedUsers.length > 0\" class=\"im-conference-create-planner-block\" ref=\"plannerNode\"></div>-->\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div v-show=\"isViewMode\" class=\"im-conference-create-field im-conference-create-users-view\">\n\t\t\t\t\t\t\t<div class=\"im-conference-create-label\">{{ localize['BX_IM_COMPONENT_CONFERENCE_USER_SELECTOR_LABEL'] }}</div>\n\t\t\t\t\t\t\t<div @click=\"switchToEdit\" class=\"im-conference-create-users-view-content\">\n\t\t\t\t\t\t\t\t<div v-for=\"user in selectedUsers\" :key=\"user.id\" class=\"im-conference-create-users-view-item\">\n\t\t\t\t\t\t\t\t\t<div class=\"im-conference-create-users-view-avatar\" :style=\"getUserAvatarStyle(user)\"></div>\n\t\t\t\t\t\t\t\t\t<div class=\"im-conference-create-users-view-title\">{{ user.title }}</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t"
	  }
	};

	var FieldBroadcast = {
	  name: 'conference-field-broadcast',
	  component: {
	    props: {
	      mode: {
	        type: String
	      },
	      broadcastMode: {
	        type: Boolean
	      },
	      chatHost: {
	        type: Object
	      },
	      selectedPresenters: {
	        type: Array
	      }
	    },
	    data: function data() {
	      return {
	        name: 'broadcast'
	      };
	    },
	    computed: {
	      isViewMode: function isViewMode() {
	        return this.mode === im_const.ConferenceFieldState.view;
	      },
	      codedValue: function codedValue() {
	        if (this.broadcastMode) {
	          return this.localize['BX_IM_COMPONENT_CONFERENCE_BROADCAST_MODE_ON'];
	        } else {
	          return this.localize['BX_IM_COMPONENT_CONFERENCE_BROADCAST_MODE_OFF'];
	        }
	      },
	      presenterListForSelector: function presenterListForSelector() {
	        return this.selectedPresenters.map(function (user) {
	          return ['user', user.id];
	        });
	      },
	      localize: function localize() {
	        return BX.message;
	      }
	    },
	    methods: {
	      switchToEdit: function switchToEdit() {
	        this.$emit('switchToEdit', this.name);
	      },
	      onBroadcastModeChange: function onBroadcastModeChange() {
	        this.$emit('broadcastModeChange');
	      },
	      onSwitchModeForAll: function onSwitchModeForAll(mode) {
	        if (mode === im_const.ConferenceFieldState.edit) {
	          this.switchToEdit();
	        }
	      },
	      onPresenterSelect: function onPresenterSelect(event) {
	        this.$emit('presenterSelect', event);
	        //this.updatePlanner();
	      },
	      onPresenterDeselect: function onPresenterDeselect(event) {
	        this.$emit('presenterDeselect', event);
	        //this.updatePlanner();
	      },
	      getUserAvatarStyle: function getUserAvatarStyle(user) {
	        if (user.avatar) {
	          return {
	            backgroundImage: "url('".concat(encodeURI(user.avatar), "')")
	          };
	        }
	        return {};
	      },
	      initPresenterSelector: function initPresenterSelector() {
	        var _this = this;
	        this.presenterSelector = new ui_entitySelector.TagSelector({
	          id: 'presenter-tag-selector',
	          dialogOptions: {
	            id: 'presenter-tag-selector',
	            preselectedItems: this.presenterListForSelector,
	            events: {
	              'Item:onSelect': function ItemOnSelect(event) {
	                _this.onPresenterSelect(event);
	              },
	              'Item:onDeselect': function ItemOnDeselect(event) {
	                _this.onPresenterDeselect(event);
	              }
	            },
	            entities: [{
	              id: 'user'
	            }, {
	              id: 'department'
	            }]
	          }
	        });
	      },
	      onUpdatePresenterSelector: function onUpdatePresenterSelector() {
	        var _this2 = this;
	        this.$nextTick(function () {
	          _this2.$refs['presenterSelector'].innerHTML = '';
	          _this2.initPresenterSelector();
	          _this2.presenterSelector.renderTo(_this2.$refs['presenterSelector']);
	        });
	      }
	    },
	    mounted: function mounted() {
	      var _this3 = this;
	      this.initPresenterSelector();
	      this.presenterSelector.renderTo(this.$refs['presenterSelector']);
	      this.$root.$on('switchModeForAll', function (mode) {
	        _this3.onSwitchModeForAll(mode);
	      });
	      this.$root.$on('updatePresenterSelector', function () {
	        _this3.onUpdatePresenterSelector();
	      });
	    },
	    template: "\n\t\t\t\t<div class=\"im-conference-create-section im-conference-create-broadcast-section\">\n\t\t\t\t\t<div class=\"im-conference-create-broadcast-section-title\">\n\t\t\t\t\t\t<label class=\"im-conference-create-label\" for=\"im-conference-create-field-broadcast\">{{ localize['BX_IM_COMPONENT_CONFERENCE_BROADCAST_LABEL'] }}</label>\n\t\t\t\t\t\t<bx-hint :text=\"localize['BX_IM_COMPONENT_CONFERENCE_BROADCAST_HINT']\"/>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div v-show=\"!isViewMode\">\n\t\t\t\t\t\t<div class=\"im-conference-create-field-inline im-conference-create-field-broadcast\">\n\t\t\t\t\t\t\t<input @input=\"onBroadcastModeChange\" type=\"checkbox\" id=\"im-conference-create-field-broadcast-checkbox\" :checked=\"broadcastMode\">\n\t\t\t\t\t\t\t<label class=\"im-conference-create-label\" for=\"im-conference-create-field-broadcast-checkbox\">{{ localize['BX_IM_COMPONENT_CONFERENCE_BROADCAST_CHECKBOX_LABEL'] }}</label>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div v-show=\"broadcastMode\" class=\"im-conference-create-user-selector-block\">\n\t\t\t\t\t\t\t<div class=\"im-conference-create-field\">\n\t\t\t\t\t\t\t\t<label class=\"im-conference-create-label im-conference-create-label-broadcast\" for=\"im-conference-create-field-user-selector\">{{ localize['BX_IM_COMPONENT_CONFERENCE_PRESENTER_SELECTOR_LABEL'] }}</label>\n\t\t\t\t\t\t\t\t<div class=\"im-conference-create-user-selector\" ref=\"presenterSelector\"></div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div v-show=\"isViewMode\">\n\t\t\t\t\t\t<div @click=\"switchToEdit\" class=\"im-conference-create-field-view\">{{ codedValue }}</div>\n\t\t\t\t\t\t<div v-if=\"broadcastMode\" @click=\"switchToEdit\" class=\"im-conference-create-field im-conference-create-users-view\">\n\t\t\t\t\t\t\t<div class=\"im-conference-create-label\">{{ localize['BX_IM_COMPONENT_CONFERENCE_PRESENTER_SELECTOR_LABEL'] }}</div>\n\t\t\t\t\t\t\t<div class=\"im-conference-create-users-view-content\">\n\t\t\t\t\t\t\t\t<div v-for=\"user in selectedPresenters\" :key=\"user.id\" class=\"im-conference-create-users-view-item\">\n\t\t\t\t\t\t\t\t\t<div class=\"im-conference-create-users-view-avatar\" :style=\"getUserAvatarStyle(user)\"></div>\n\t\t\t\t\t\t\t\t\t<div class=\"im-conference-create-users-view-title\">{{ user.title }}</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"
	  }
	};

	var FieldTypes = [FieldTitle, FieldPassword, FieldInvitation, FieldPlanner, FieldBroadcast];
	var FieldComponents = {};
	FieldTypes.forEach(function (fieldType) {
	  FieldComponents[fieldType.name] = fieldType.component;
	});
	ui_vue.BitrixVue.component('bx-im-component-conference-edit', {
	  props: {
	    conferenceId: {
	      type: Number,
	      "default": 0
	    },
	    fieldsData: {
	      type: Object,
	      "default": {}
	    },
	    mode: {
	      type: String,
	      "default": im_const.ConferenceFieldState.create
	    },
	    chatHost: {
	      type: Object,
	      "default": {}
	    },
	    chatUsers: {
	      type: Array,
	      "default": []
	    },
	    presenters: {
	      type: Array,
	      "default": []
	    },
	    publicLink: {
	      type: String,
	      "default": ''
	    },
	    chatId: {
	      type: Number,
	      "default": 0
	    },
	    invitationText: {
	      type: String,
	      "default": ''
	    },
	    gridId: {
	      type: String,
	      "default": ''
	    },
	    pathToList: {
	      type: String,
	      "default": ''
	    },
	    broadcastingEnabled: {
	      type: Boolean,
	      "default": false
	    }
	  },
	  data: function data() {
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
	        mode: im_const.ConferenceFieldState.view,
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
	  created: function created() {
	    if (this.isFormViewMode) {
	      this.title.initialValue = this.fieldsData['TITLE'];
	      this.password.initialValue = this.fieldsData['PASSWORD'];
	      this.broadcastMode.currentValue = this.fieldsData['BROADCAST'];
	      this.invitation.value = this.invitationText;
	      this.passwordNeeded.currentValue = !!this.fieldsData['PASSWORD'];
	      this.publicLink = main_core.Text.encode(this.publicLink);
	      this.selectedUsers.currentValue = babelHelpers.toConsumableArray(this.chatUsers);
	      if (this.fieldsData['BROADCAST']) {
	        this.selectedPresenters.currentValue = babelHelpers.toConsumableArray(this.presenters);
	      }
	    } else if (this.isFormCreateMode) {
	      this.generateLink();
	      this.title.initialValue = '';
	      this.password.initialValue = '';
	      this.passwordNeeded.currentValue = false;
	      this.broadcastMode.currentValue = false;
	      var currentUser = {
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
	    this.selectedUsers.initialValue = babelHelpers.toConsumableArray(this.selectedUsers.currentValue);
	    this.selectedPresenters.initialValue = babelHelpers.toConsumableArray(this.selectedPresenters.currentValue);
	    this.setDefaultDateAndTime();
	    this.setDefaultDuration();
	  },
	  mounted: function mounted() {
	    if (this.isFormCreateMode) {
	      this.checkRequirements();
	    }
	  },
	  computed: {
	    isFormCreateMode: function isFormCreateMode() {
	      return this.mode === im_const.ConferenceFieldState.create;
	    },
	    isFormViewMode: function isFormViewMode() {
	      return this.mode === im_const.ConferenceFieldState.view;
	    },
	    isTitleEdited: function isTitleEdited() {
	      return this.fieldsMode['title'] === im_const.ConferenceFieldState.edit;
	    },
	    isPasswordEdited: function isPasswordEdited() {
	      return this.fieldsMode['password'] === im_const.ConferenceFieldState.edit;
	    },
	    isPlannerEdited: function isPlannerEdited() {
	      return this.fieldsMode['planner'] === im_const.ConferenceFieldState.edit;
	    },
	    isPasswordCheckboxEdited: function isPasswordCheckboxEdited() {
	      return this.passwordNeeded.currentValue !== this.passwordNeeded.initialValue;
	    },
	    isBroadcastEdited: function isBroadcastEdited() {
	      return this.fieldsMode['broadcast'] === im_const.ConferenceFieldState.edit;
	    },
	    isEditing: function isEditing() {
	      return this.isFormViewMode && (this.isTitleEdited || this.isPasswordEdited || this.invitation.edited || this.isPasswordCheckboxEdited || this.isPlannerEdited || this.isBroadcastEdited);
	    },
	    conferenceLink: function conferenceLink() {
	      if (this.isFormCreateMode) {
	        if (this.linkGenerated) {
	          return this.aliasData['LINK'];
	        } else {
	          return '#LINK#';
	        }
	      } else if (this.isFormViewMode) {
	        return this.publicLink;
	      }
	    },
	    submitFormButtonClasses: function submitFormButtonClasses() {
	      var classes = ['ui-btn', 'ui-btn-success'];
	      if (this.isSubmitting) {
	        classes.push('ui-btn-disabled');
	      }
	      return classes;
	    },
	    localize: function localize() {
	      return BX.message;
	    }
	  },
	  methods: {
	    /* region 01. Mode switching */switchToEdit: function switchToEdit(fieldName) {
	      this.fieldsMode[fieldName] = im_const.ConferenceFieldState.edit;
	      this.$root.$emit('focus', fieldName);
	    },
	    switchModeForAllFields: function switchModeForAllFields(mode) {
	      for (var field in this.fieldsMode) {
	        if (this.fieldsMode.hasOwnProperty(field)) {
	          this.fieldsMode[field] = mode;
	        }
	      }
	      this.$root.$emit('switchModeForAll', mode);
	    },
	    /* endregion 01. Mode switching */
	    /* region 02. Field update handlers */
	    onTitleChange: function onTitleChange(newTitle) {
	      this.title.currentValue = newTitle;
	    },
	    onPasswordChange: function onPasswordChange(newPassword) {
	      this.password.currentValue = newPassword;
	    },
	    onPasswordNeededChange: function onPasswordNeededChange() {
	      this.passwordNeeded.currentValue = !this.passwordNeeded.currentValue;
	      if (this.passwordNeeded.currentValue) {
	        this.$root.$emit('focus', 'password');
	      }
	    },
	    onBroadcastModeChange: function onBroadcastModeChange() {
	      this.broadcastMode.currentValue = !this.broadcastMode.currentValue;
	    },
	    onInvitationUpdate: function onInvitationUpdate(newValue) {
	      this.invitation.value = newValue;
	      this.invitation.edited = true;
	    },
	    onUserSelect: function onUserSelect(event) {
	      var index = this.selectedUsers.currentValue.findIndex(function (user) {
	        return user.id === event.data.item.id;
	      });
	      if (index === -1) {
	        this.selectedUsers.currentValue.push({
	          id: event.data.item.id,
	          title: event.data.item.title,
	          avatar: event.data.item.avatar
	        });
	      }
	    },
	    onUserDeselect: function onUserDeselect(event) {
	      var index = this.selectedUsers.currentValue.findIndex(function (user) {
	        return user.id === event.data.item.id;
	      });
	      if (index > -1) {
	        this.selectedUsers.currentValue.splice(index, 1);
	      }
	    },
	    onPresenterSelect: function onPresenterSelect(event) {
	      var index = this.selectedPresenters.currentValue.findIndex(function (user) {
	        return user.id === event.data.item.id;
	      });
	      if (index === -1) {
	        this.selectedPresenters.currentValue.push({
	          id: event.data.item.id,
	          title: event.data.item.title,
	          avatar: event.data.item.avatar
	        });
	      }
	    },
	    onPresenterDeselect: function onPresenterDeselect(event) {
	      var index = this.selectedPresenters.currentValue.findIndex(function (user) {
	        return user.id === event.data.item.id;
	      });
	      if (index > -1) {
	        this.selectedPresenters.currentValue.splice(index, 1);
	      }
	    },
	    onDateChange: function onDateChange(newDate) {
	      this.selectedDate.currentValue = BX.formatDate(newDate, BX.message('FORMAT_DATE'));
	    },
	    onTimeChange: function onTimeChange(newTime) {
	      this.selectedTime.currentValue = newTime;
	    },
	    onDurationChange: function onDurationChange(newDuration) {
	      this.selectedDuration.currentValue = String(newDuration);
	    },
	    onDurationTypeChange: function onDurationTypeChange(newDurationType) {
	      this.selectedDurationType.currentValue = newDurationType;
	    },
	    /* endregion 02. Field update handlers */
	    /* region 03. Actions */
	    discardChanges: function discardChanges() {
	      this.clearErrors();
	      this.title.currentValue = this.title.initialValue;
	      this.password.currentValue = this.password.initialValue;
	      this.passwordNeeded.currentValue = this.passwordNeeded.initialValue;
	      this.broadcastMode.currentValue = this.broadcastMode.initialValue;
	      this.selectedUsers.currentValue = babelHelpers.toConsumableArray(this.selectedUsers.initialValue);
	      this.$root.$emit('updateUserSelector');
	      this.selectedPresenters.currentValue = babelHelpers.toConsumableArray(this.selectedPresenters.initialValue);
	      this.$root.$emit('updatePresenterSelector');
	      this.selectedDate.currentValue = this.selectedDate.initialValue;
	      this.selectedTime.currentValue = this.selectedTime.initialValue;
	      this.selectedDuration.currentValue = this.selectedDuration.initialValue;
	      this.selectedDurationType.currentValue = this.selectedDurationType.initialValue;
	      this.switchModeForAllFields(im_const.ConferenceFieldState.view);
	    },
	    copyInvitation: function copyInvitation() {
	      var link = '';
	      if (this.isFormCreateMode && this.linkGenerated) {
	        link = main_core.Text.decode(this.aliasData['LINK']);
	      } else if (this.isFormViewMode) {
	        link = main_core.Text.decode(this.publicLink);
	      }
	      var title = this.localize['BX_IM_COMPONENT_CONFERENCE_DEFAULT_TITLE'];
	      if (this.title.currentValue) {
	        title = this.title.currentValue;
	      }
	      var copyValue = main_core.Text.decode(this.invitation.value).replace(/#CREATOR#/gm, this.chatHost.FULL_NAME).replace(/#TITLE#/gm, "\"".concat(title, "\"")).replace(/#LINK#/gm, "".concat(link));
	      im_lib_clipboard.Clipboard.copy(copyValue);
	      if (main_core.Reflection.getClass('BX.UI.Notification.Center')) {
	        top.BX.UI.Notification.Center.notify({
	          content: this.localize['BX_IM_COMPONENT_CONFERENCE_INVITATION_COPIED']
	        });
	      }
	    },
	    openChat: function openChat() {
	      if (window.top["BXIM"]) {
	        window.top["BXIM"].openMessenger('chat' + this.chatId);
	      }
	    },
	    editAll: function editAll() {
	      this.switchModeForAllFields(im_const.ConferenceFieldState.edit);
	    },
	    /* endregion 03. Actions */
	    /* region 04. Form handling */
	    submitForm: function submitForm() {
	      var _this = this;
	      if (this.isSubmitting) {
	        return false;
	      }
	      this.isSubmitting = true;
	      var fieldsToSubmit = {};
	      fieldsToSubmit['title'] = this.title.currentValue;
	      fieldsToSubmit['password_needed'] = this.passwordNeeded.currentValue;
	      fieldsToSubmit['password'] = this.password.currentValue;
	      fieldsToSubmit['id'] = this.conferenceId;
	      fieldsToSubmit['invitation'] = main_core.Text.decode(this.invitation.value);
	      fieldsToSubmit['users'] = this.selectedUsers.currentValue.map(function (user) {
	        return user.id;
	      });
	      fieldsToSubmit['broadcast_mode'] = this.broadcastMode.currentValue;
	      fieldsToSubmit['presenters'] = this.selectedPresenters.currentValue.map(function (user) {
	        return user.id;
	      });
	      this.clearErrors();
	      if (this.isFormViewMode || this.linkGenerated) {
	        main_core.ajax.runAction('im.conference.create', {
	          json: {
	            fields: fieldsToSubmit,
	            aliasData: this.aliasData
	          },
	          analyticsLabel: {
	            creationType: 'section'
	          }
	        }).then(function (response) {
	          _this.onSuccessfulSubmit();
	        })["catch"](function (response) {
	          _this.onFailedSubmit(response);
	        });
	      }
	    },
	    onSuccessfulSubmit: function onSuccessfulSubmit() {
	      if (this.isFormCreateMode) {
	        this.copyInvitation();
	      }
	      this.isSubmitting = false;
	      this.closeSlider();
	      this.reloadGrid();
	    },
	    onFailedSubmit: function onFailedSubmit(response) {
	      this.isSubmitting = false;
	      var errorMessage = response["errors"][0].message;
	      if (response["errors"][0].code === 'NETWORK_ERROR') {
	        errorMessage = this.localize['BX_IM_COMPONENT_CONFERENCE_NETWORK_ERROR'];
	      }
	      this.addError(errorMessage);
	    },
	    /* endregion 04. Form handling */
	    /* region 05. Helpers */
	    checkRequirements: function checkRequirements() {
	      if (!top.BX.PULL.isPublishingEnabled()) {
	        this.disableButton();
	        this.addError(this.localize['BX_IM_COMPONENT_CONFERENCE_PUSH_ERROR']);
	      }
	      if (!top.BX.Call.Util.isCallServerAllowed()) {
	        this.disableButton();
	        this.addError(this.localize['BX_IM_COMPONENT_CONFERENCE_VOXIMPLANT_ERROR_WITH_LINK']);
	      }
	    },
	    disableButton: function disableButton() {
	      var createButton = document.querySelector('#im-conference-create-wrap #ui-button-panel-save');
	      if (createButton) {
	        main_core.Dom.addClass(createButton, ['ui-btn-disabled', 'ui-btn-icon-lock']);
	      }
	    },
	    generateLink: function generateLink() {
	      var _this2 = this;
	      main_core.ajax.runAction('im.conference.prepare', {
	        json: {},
	        analyticsLabel: {
	          creationType: 'section'
	        }
	      }).then(function (response) {
	        _this2.aliasData = response.data['ALIAS_DATA'];
	        _this2.aliasData['LINK'] = main_core.Text.encode(_this2.aliasData['LINK']);
	        _this2.title.defaultValue = response.data['DEFAULT_TITLE'];
	        _this2.linkGenerated = true;
	      })["catch"](function (response) {
	        im_lib_logger.Logger.warn('error', response["errors"][0].message);
	      });
	    },
	    addError: function addError(errorText) {
	      this.errors.push(errorText);
	    },
	    clearErrors: function clearErrors() {
	      this.errors = [];
	    },
	    closeSlider: function closeSlider() {
	      if (main_core.Reflection.getClass('BX.SidePanel')) {
	        BX.SidePanel.Instance.close();
	      }
	    },
	    reloadGrid: function reloadGrid() {
	      if (main_core.Reflection.getClass('top.BX.Main.gridManager')) {
	        top.BX.Main.gridManager.reload(this.gridId);
	      } else {
	        top.window.location = this.pathToList;
	      }
	    },
	    setDefaultDateAndTime: function setDefaultDateAndTime() {
	      var date = new Date();
	      var minutes = date.getMinutes();
	      var mod = minutes % 5;
	      if (mod > 0) {
	        date.setMinutes(minutes - mod + (mod > 2 ? 5 : 0));
	      }
	      this.selectedDate.currentValue = BX.formatDate(date, BX.message('FORMAT_DATE'));
	      this.selectedDate.initialValue = this.selectedDate.currentValue;
	      this.selectedTime.currentValue = this.formatTime(date);
	      this.selectedTime.initialValue = this.selectedTime.currentValue;
	    },
	    setDefaultDuration: function setDefaultDuration() {
	      this.selectedDuration.currentValue = '30';
	      this.selectedDuration.initialValue = this.selectedDuration.currentValue;
	      this.selectedDurationType.currentValue = 'm';
	      this.selectedDurationType.initialValue = this.selectedDurationType.currentValue;
	    },
	    formatTime: function formatTime(date) {
	      var dateFormat = BX.date.convertBitrixFormat(BX.message('FORMAT_DATE')).replace(/:?\s*s/, '');
	      var timeFormat = BX.date.convertBitrixFormat(BX.message('FORMAT_DATETIME')).replace(/:?\s*s/, '');
	      var dateString = BX.date.format(dateFormat, date);
	      var timeString = BX.date.format(timeFormat, date);
	      return BX.util.trim(timeString.replace(dateString, ''));
	    } /* endregion 05. Helpers */
	  },
	  components: FieldComponents,
	  template: "\n\t\t<div>\n\t\t\t<template v-if=\"errors.length > 0\">\n\t\t\t\t<div class=\"ui-alert ui-alert-danger\" id=\"im-conference-create-errors\">\n\t\t\t\t\t<span v-for=\"error in errors\" class=\"ui-alert-message\" v-html=\"error\"></span>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<div class=\"im-conference-create-block im-conference-create-fields-wrapper\">\n\t\t\t\t<!-- Form fields -->\n\t\t\t\t<conference-field-title\n\t\t\t\t\t:mode=\"fieldsMode['title']\"\n\t\t\t\t\t:title=\"title.currentValue\"\n\t\t\t\t\t:defaultValue=\"title.defaultValue\"\n\t\t\t\t\t@titleChange=\"onTitleChange\"\n\t\t\t\t\t@switchToEdit=\"switchToEdit\"\n\t\t\t\t/>\n\t\t\t\t<conference-field-planner\n\t\t\t\t\t:mode=\"fieldsMode['planner']\"\n\t\t\t\t\t:selectedUsers=\"selectedUsers.currentValue\"\n\t\t\t\t\t:selectedDate=\"selectedDate.currentValue\"\n\t\t\t\t\t:selectedTime=\"selectedTime.currentValue\"\n\t\t\t\t\t:selectedDuration=\"selectedDuration.currentValue\"\n\t\t\t\t\t:selectedDurationType=\"selectedDurationType.currentValue\"\n\t\t\t\t\t:chatHost=\"chatHost\"\n\t\t\t\t\t@userSelect=\"onUserSelect\"\n\t\t\t\t\t@userDeselect=\"onUserDeselect\"\n\t\t\t\t\t@dateChange=\"onDateChange\"\n\t\t\t\t\t@timeChange=\"onTimeChange\"\n\t\t\t\t\t@durationChange=\"onDurationChange\"\n\t\t\t\t\t@durationTypeChange=\"onDurationTypeChange\"\n\t\t\t\t\t@switchToEdit=\"switchToEdit\"\n\t\t\t\t/>\n\t\t\t\t<conference-field-password\n\t\t\t\t\t:mode=\"fieldsMode['password']\"\n\t\t\t\t\t:password=\"password.currentValue\"\n\t\t\t\t\t:passwordNeeded=\"passwordNeeded.currentValue\"\n\t\t\t\t\t@passwordChange=\"onPasswordChange\"\n\t\t\t\t\t@passwordNeededChange=\"onPasswordNeededChange\"\n\t\t\t\t\t@switchToEdit=\"switchToEdit\"\n\t\t\t\t/>\n<!--\t\t\t\t<div v-if=\"isFormCreateMode\" class=\"im-conference-create-delimiter im-conference-create-delimiter-small\"></div>-->\n\t\t\t\t<template v-if=\"broadcastingEnabled\">\n\t\t\t\t\t<conference-field-broadcast\n\t\t\t\t\t\t:mode=\"fieldsMode['broadcast']\"\n\t\t\t\t\t\t:broadcastMode=\"broadcastMode.currentValue\"\n\t\t\t\t\t\t:selectedPresenters=\"selectedPresenters.currentValue\"\n\t\t\t\t\t\t:chatHost=\"chatHost\"\n\t\t\t\t\t\t@broadcastModeChange=\"onBroadcastModeChange\"\n\t\t\t\t\t\t@switchToEdit=\"switchToEdit\"\n\t\t\t\t\t\t@presenterSelect=\"onPresenterSelect\"\n\t\t\t\t\t\t@presenterDeselect=\"onPresenterDeselect\"\n\t\t\t\t\t/>\n\t\t\t\t</template>\n\t\t\t\t<!-- Action buttons -->\n\t\t\t\t<template v-if=\"!isFormCreateMode\">\n\t\t\t\t\t<div class=\"im-conference-create-section im-conference-create-actions\">\n\t\t\t\t\t\t<a :href=\"publicLink\" target=\"_blank\" class=\"ui-btn ui-btn-sm ui-btn-primary ui-btn-icon-camera\">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_START'] }}</a>\n\t\t\t\t\t\t<button @click=\"copyInvitation\" class=\"ui-btn ui-btn-sm ui-btn-light-border ui-btn-icon-share\">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_INVITATION_COPY'] }}</button>\n\t\t\t\t\t\t<button @click=\"openChat\" class=\"ui-btn ui-btn-sm ui-btn-light-border ui-btn-icon-chat\">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_CHAT'] }}</button>\n\t\t\t\t\t\t<button @click=\"editAll\" class=\"ui-btn ui-btn-sm ui-btn-light\">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_EDIT'] }}</button>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<!-- Bottom button panel -->\n\t\t\t\t<div v-if=\"isEditing\" class=\"im-conference-create-button-panel-edit ui-button-panel-wrapper ui-pinner ui-pinner-bottom ui-pinner-full-width\">\n\t\t\t\t\t<div class=\"ui-button-panel ui-button-panel-align-center\">\n\t\t\t\t\t\t<button @click=\"submitForm\" id=\"ui-button-panel-save\" :class=\"submitFormButtonClasses\">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_SAVE'] }}</button>\n\t\t\t\t\t\t<a @click=\"discardChanges\" id=\"ui-button-panel-cancel\" class=\"ui-btn ui-btn-link\">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_CANCEL'] }}</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div v-else-if=\"isFormCreateMode\" class=\"im-conference-create-button-panel-add ui-button-panel-wrapper ui-pinner ui-pinner-bottom ui-pinner-full-width\">\n\t\t\t\t\t<div class=\"ui-button-panel ui-button-panel-align-center\">\n\t\t\t\t\t\t<button @click=\"submitForm\" id=\"ui-button-panel-save\" name=\"save\" value=\"Y\" :class=\"submitFormButtonClasses\">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_CREATE'] }}</button>\n\t\t\t\t\t\t<a @click=\"closeSlider\" id=\"ui-button-panel-cancel\" class=\"ui-btn ui-btn-link\">{{ localize['BX_IM_COMPONENT_CONFERENCE_BUTTON_CANCEL'] }}</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"im-conference-create-delimiter\"></div>\n\t\t\t\t<!-- Invitation -->\n\t\t\t\t<conference-field-invitation\n\t\t\t\t\t:invitation=\"invitation\"\n\t\t\t\t\t:chatHost=\"chatHost\"\n\t\t\t\t\t:title=\"title.currentValue\"\n\t\t\t\t\t:defaultTitle=\"title.defaultValue\"\n\t\t\t\t\t:publicLink=\"conferenceLink\"\n\t\t\t\t\t:formMode=\"mode\"\n\t\t\t\t\t@invitationUpdate=\"onInvitationUpdate\"\n\t\t\t\t/>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

}((this.BX.Messenger = this.BX.Messenger || {}),BX,BX.Messenger.Lib,BX.Messenger.Lib,BX,BX.Calendar,BX.Calendar,BX.Event,window,BX.Messenger.Const,BX.UI.EntitySelector));
//# sourceMappingURL=conference-edit.bundle.js.map
