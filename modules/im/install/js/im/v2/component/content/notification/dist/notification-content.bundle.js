this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core_events,main_polyfill_intersectionobserver,ui_dialogs_messagebox,im_v2_provider_service,im_v2_lib_utils,im_v2_lib_parser,im_public,im_v2_component_elements,im_v2_lib_dateFormatter,ui_forms,ui_vue3_vuex,im_v2_lib_user,main_core,im_v2_application_core,im_v2_const,im_v2_lib_logger) {
	'use strict';

	// @vue/component
	const NotificationItemAvatar = {
	  name: 'NotificationItemAvatar',
	  components: {
	    Avatar: im_v2_component_elements.Avatar
	  },
	  props: {
	    userId: {
	      type: Number,
	      required: true
	    }
	  },
	  computed: {
	    isSystem() {
	      return this.userId === 0;
	    },
	    dialogId() {
	      return this.userId.toString();
	    },
	    user() {
	      // For now, we don't have a user if it is an OL user.
	      return this.$store.getters['users/get'](this.userId);
	    }
	  },
	  template: `
		<div class="bx-im-content-notification-item-avatar__container">
			<template v-if="isSystem || !user">
				<div class="bx-im-content-notification-item-avatar__system-icon"></div>
			</template>
			<template v-else>
				<Avatar :dialogId="dialogId" size="L" :withStatus="false"></Avatar>
			</template>
		</div>
	`
	};

	// @vue/component
	const NotificationQuickAnswer = {
	  name: 'NotificationQuickAnswer',
	  components: {
	    MessengerButton: im_v2_component_elements.Button
	  },
	  props: {
	    notification: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['sendQuickAnswer'],
	  data() {
	    return {
	      quickAnswerText: '',
	      quickAnswerResultMessage: '',
	      showQuickAnswer: false,
	      isSending: false,
	      successSentQuickAnswer: false
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor
	  },
	  methods: {
	    toggleQuickAnswer() {
	      if (this.successSentQuickAnswer) {
	        this.showQuickAnswer = true;
	        this.successSentQuickAnswer = false;
	        this.quickAnswerResultMessage = '';
	      } else {
	        this.showQuickAnswer = !this.showQuickAnswer;
	      }
	      if (this.showQuickAnswer) {
	        this.$nextTick(() => {
	          this.$refs['textarea'].focus();
	        });
	      }
	    },
	    sendQuickAnswer() {
	      if (this.isSending || this.quickAnswerText.trim() === '') {
	        return;
	      }
	      this.isSending = true;
	      this.$emit('sendQuickAnswer', {
	        id: this.notification.id,
	        text: this.quickAnswerText.trim(),
	        callbackSuccess: response => {
	          const {
	            result_message: resultMessage
	          } = response.data();
	          const [message] = resultMessage;
	          this.quickAnswerResultMessage = message;
	          this.successSentQuickAnswer = true;
	          this.quickAnswerText = '';
	          this.isSending = false;
	        },
	        callbackError: () => {
	          this.isSending = false;
	        }
	      });
	    }
	  },
	  template: `
		<div class="bx-im-content-notification-quick-answer__container">
			<button 
				v-if="!showQuickAnswer"
				class="bx-im-content-notification-quick-answer__reply-link" 
				@click="toggleQuickAnswer" 
				@dblclick.stop
			>
				{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_QUICK_ANSWER_BUTTON') }}
			</button>
			<transition name="quick-answer-slide">
				<div 
					v-if="showQuickAnswer && !successSentQuickAnswer" 
					class="bx-im-content-notification-quick-answer__textarea-container"
				>
					<textarea
						ref="textarea"
						autofocus
						class="bx-im-content-notification-quick-answer__textarea"
						v-model="quickAnswerText"
						:disabled="isSending"
						@keydown.enter.prevent
						@keyup.enter.prevent="sendQuickAnswer"
					/>
					<div 
						v-if="!successSentQuickAnswer" 
						class="bx-im-content-notification-quick-answer__buttons-container"
					>
						<MessengerButton
							:color="ButtonColor.Primary"
							:size="ButtonSize.M"
							:isRounded="true"
							:isUppercase="false"
							:text="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_QUICK_ANSWER_SEND')"
							:isLoading="isSending"
							@click="sendQuickAnswer"
						/>
						<MessengerButton
							:color="ButtonColor.LightBorder"
							:size="ButtonSize.M"
							:isRounded="true"
							:isUppercase="false"
							:text="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_QUICK_ANSWER_CANCEL')"
							:isDisabled="isSending"
							@click="toggleQuickAnswer"
						/>
					</div>
				</div>
			</transition>
			<div v-if="successSentQuickAnswer" class="bx-im-content-notification-quick-answer__result">
				<div class="bx-im-content-notification-quick-answer__success-icon"></div>
				<div class="bx-im-content-notification-quick-answer__success-text">{{ quickAnswerResultMessage }}</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const NotificationItemConfirmButtons = {
	  name: 'NotificationItemConfirmButtons',
	  components: {
	    Button: im_v2_component_elements.Button
	  },
	  props: {
	    buttons: {
	      type: Array,
	      required: true
	    }
	  },
	  emits: ['confirmButtonsClick'],
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    preparedButtons() {
	      return this.buttons.map(button => {
	        const [id, value] = button.COMMAND_PARAMS.split('|');
	        return {
	          id: id,
	          value: value,
	          text: button.TEXT
	        };
	      });
	    }
	  },
	  methods: {
	    click(button) {
	      this.$emit('confirmButtonsClick', button);
	    },
	    getButtonColor(button) {
	      return button.value === 'Y' ? im_v2_component_elements.ButtonColor.Primary : im_v2_component_elements.ButtonColor.LightBorder;
	    }
	  },
	  template: `
		<div class="bx-im-content-notification-item-confirm-buttons__container">
			<Button
				v-for="(button, index) in preparedButtons" :key="index"
				:text="button.text"
				:color="getButtonColor(button)"
				:size="ButtonSize.M"
				:isRounded="true"
				:isUppercase="false"
				@click="click(button)"
			></Button>
		</div>
	`
	};

	// @vue/component
	const NotificationItemContent = {
	  name: 'NotificationItemContent',
	  components: {
	    NotificationQuickAnswer,
	    Attach: im_v2_component_elements.Attach,
	    NotificationItemConfirmButtons
	  },
	  props: {
	    notification: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['confirmButtonsClick', 'sendQuickAnswer'],
	  computed: {
	    notificationItem() {
	      return this.notification;
	    },
	    hasQuickAnswer() {
	      var _this$notification$pa;
	      return !!((_this$notification$pa = this.notification.params) != null && _this$notification$pa.CAN_ANSWER) && this.notification.params.CAN_ANSWER === 'Y';
	    },
	    content() {
	      return im_v2_lib_parser.Parser.decodeNotification(this.notification);
	    },
	    attachList() {
	      var _this$notification$pa2;
	      return (_this$notification$pa2 = this.notification.params) == null ? void 0 : _this$notification$pa2.ATTACH;
	    }
	  },
	  methods: {
	    onContentClick(event) {
	      im_v2_lib_parser.Parser.executeClickEvent(event);
	    },
	    onConfirmButtonsClick(event) {
	      this.$emit('confirmButtonsClick', event);
	    },
	    onSendQuickAnswer(event) {
	      this.$emit('sendQuickAnswer', event);
	    }
	  },
	  template: `
		<div class="bx-im-content-notification-item-content__container" @click="onContentClick">
			<div 
				v-if="content.length > 0" 
				class="bx-im-content-notification-item-content__content-text"
				v-html="content"
			></div>
			<NotificationQuickAnswer 
				v-if="hasQuickAnswer" 
				:notification="notificationItem" 
				@sendQuickAnswer="onSendQuickAnswer"
			/>
			<template v-if="attachList">
				<template v-for="attachItem in attachList">
					<Attach :config="attachItem"/>
				</template>
			</template>
			<NotificationItemConfirmButtons 
				v-if="notificationItem.notifyButtons.length > 0" 
				@confirmButtonsClick="onConfirmButtonsClick" 
				:buttons="notificationItem.notifyButtons"
			/>
		</div>
	`
	};

	// @vue/component
	const NotificationItemHeader = {
	  name: 'NotificationItemHeader',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    AvatarSize: im_v2_component_elements.AvatarSize,
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    notification: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    notificationItem() {
	      return this.notification;
	    },
	    date() {
	      return this.notificationItem.date;
	    },
	    type() {
	      return this.notificationItem.sectionCode;
	    },
	    user() {
	      return this.$store.getters['users/get'](this.notificationItem.authorId, true);
	    },
	    hasName() {
	      return this.notificationItem.authorId > 0 && this.user.name.length > 0;
	    },
	    title() {
	      if (this.notificationItem.title.length > 0) {
	        return this.notificationItem.title;
	      }
	      return this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_ITEM_SYSTEM');
	    },
	    isSystem() {
	      return this.notification.authorId === 0;
	    },
	    userDialogId() {
	      return this.notification.authorId.toString();
	    },
	    titleClasses() {
	      return {
	        'bx-im-content-notification-item-header__title-text': true,
	        'bx-im-content-notification-item-header__title-user-text': !this.isSystem,
	        '--extranet': this.user.extranet,
	        '--short': !this.hasMoreUsers
	      };
	    },
	    hasMoreUsers() {
	      var _this$notificationIte;
	      if (this.isSystem) {
	        return false;
	      }
	      return !!((_this$notificationIte = this.notificationItem.params) != null && _this$notificationIte.USERS) && this.notificationItem.params.USERS.length > 0;
	    },
	    moreUsers() {
	      const phrase = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_MORE_USERS').split('#COUNT#');
	      return {
	        start: phrase[0],
	        end: this.notificationItem.params.USERS.length + phrase[1]
	      };
	    },
	    canDelete() {
	      return this.type === im_v2_const.NotificationTypesCodes.simple;
	    },
	    itemDate() {
	      return im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(this.date, im_v2_lib_dateFormatter.DateTemplate.notification);
	    }
	  },
	  methods: {
	    onUserTitleClick() {
	      if (this.isSystem) {
	        return;
	      }
	      im_public.Messenger.openChat(this.userDialogId);
	    },
	    onMoreUsersClick(event) {
	      if (event.users) {
	        this.$emit('moreUsersClick', {
	          event: event.event,
	          users: event.users
	        });
	      }
	    },
	    onDeleteClick() {
	      this.$emit('deleteClick', this.notificationItem.id);
	    }
	  },
	  template: `
		<div class="bx-im-content-notification-item-header__container">
			<div class="bx-im-content-notification-item-header__title-container">
				<ChatTitle
					v-if="hasName"
					:dialogId="userDialogId"
					:showItsYou="false"
					:class="titleClasses"
					@click.prevent="onUserTitleClick"
				/>
				<span v-else @click.prevent="onUserTitleClick" :class="titleClasses">{{ title }}</span>
				<span v-if="hasMoreUsers" class="bx-im-content-notification-item-header__more-users">
					<span class="bx-im-content-notification-item-header__more-users-start">{{ moreUsers.start }}</span>
					<span
						class="bx-im-content-notification-item-header__more-users-dropdown"
						@click="onMoreUsersClick({users: notificationItem.params.USERS, event: $event})"
					>
						{{ moreUsers.end }}
					</span>
				</span>
			</div>
			<div class="bx-im-content-notification-item-header__date-container">
				<div class="bx-im-content-notification-item-header__date">{{ itemDate }}</div>
				<div
					v-if="canDelete"
					class="bx-im-content-notification-item-header__delete-button"
					@click="onDeleteClick()"
				>
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const NotificationItem = {
	  components: {
	    NotificationItemAvatar,
	    NotificationItemContent,
	    NotificationItemHeader
	  },
	  props: {
	    notification: {
	      type: Object,
	      required: true
	    },
	    searchMode: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['dblclick', 'buttonsClick', 'confirmButtonsClick', 'deleteClick', 'sendQuickAnswer', 'moreUsersClick'],
	  computed: {
	    NotificationTypesCodes: () => im_v2_const.NotificationTypesCodes,
	    notificationItem() {
	      return this.notification;
	    },
	    type() {
	      return this.notification.sectionCode;
	    },
	    isUnread() {
	      return !this.notificationItem.read && !this.searchMode;
	    },
	    userData() {
	      return this.$store.getters['users/get'](this.notificationItem.authorId, true);
	    }
	  },
	  methods: {
	    onDoubleClick() {
	      if (this.searchMode) {
	        return;
	      }
	      this.$emit('dblclick', this.notificationItem.id);
	    },
	    onConfirmButtonsClick(event) {
	      this.$emit('confirmButtonsClick', event);
	    },
	    onMoreUsersClick(event) {
	      this.$emit('moreUsersClick', event);
	    },
	    onSendQuickAnswer(event) {
	      this.$emit('sendQuickAnswer', event);
	    },
	    onDeleteClick(event) {
	      this.$emit('deleteClick', event);
	    }
	  },
	  template: `
		<div
			class="bx-im-content-notification-item__container"
			:class="{'--unread': isUnread}"
			@dblclick="onDoubleClick"
		>
			<NotificationItemAvatar :userId="notificationItem.authorId" />
			<div class="bx-im-content-notification-item__content-container">
				<NotificationItemHeader 
					:notification="notificationItem"
					@deleteClick="onDeleteClick"
					@moreUsersClick="onMoreUsersClick"
				/>
				<NotificationItemContent 
					:notification="notificationItem" 
					@confirmButtonsClick="onConfirmButtonsClick"
					@sendQuickAnswer="onSendQuickAnswer"
				/>
			</div>
		</div>
	`
	};

	const NotificationPlaceholder = {
	  name: 'NotificationPlaceholder',
	  props: {
	    itemsToShow: {
	      type: Number,
	      default: 50
	    }
	  },
	  template: `
		<div class="bx-im-content-notification-placeholder__container" v-for="index in itemsToShow">
			<div class="bx-im-content-notification-placeholder__element">
				<div class="bx-im-content-notification-placeholder__avatar-container">
					<div class="bx-im-content-notification-placeholder__avatar"></div>
				</div>
				<div class="bx-im-content-notification-placeholder__content-container">
					<div class="bx-im-content-notification-placeholder__content-inner">
						<div class="bx-im-content-notification-placeholder__content --top"></div>
						<div class="bx-im-content-notification-placeholder__content --short"></div>
					</div>
					<div class="bx-im-content-notification-placeholder__content --full"></div>
					<div class="bx-im-content-notification-placeholder__content --middle"></div>
					<div class="bx-im-content-notification-placeholder__content --bottom"></div>
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const NotificationSearchPanel = {
	  name: 'NotificationSearchPanel',
	  props: {
	    schema: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['search'],
	  data: function () {
	    return {
	      searchQuery: '',
	      searchType: '',
	      searchDate: ''
	    };
	  },
	  computed: {
	    filterTypes() {
	      const originalSchema = {
	        ...this.schema
	      };

	      // get rid of some subcategories
	      const modulesToRemove = ['timeman', 'mail', 'disk', 'bizproc', 'voximplant', 'sender', 'blog', 'vote', 'socialnetwork', 'imopenlines', 'photogallery', 'intranet', 'forum'];
	      modulesToRemove.forEach(moduleId => {
	        if (originalSchema[moduleId]) {
	          delete originalSchema[moduleId].LIST;
	        }
	      });

	      // rename some groups
	      if (originalSchema.calendar) {
	        originalSchema.calendar.NAME = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_CALENDAR');
	      }
	      if (originalSchema.sender) {
	        originalSchema['sender'].NAME = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_SENDER');
	      }
	      if (originalSchema.blog) {
	        originalSchema.blog.NAME = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_BLOG');
	      }
	      if (originalSchema.socialnetwork) {
	        originalSchema['socialnetwork'].NAME = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_SOCIALNETWORK');
	      }
	      if (originalSchema.intranet) {
	        originalSchema['intranet'].NAME = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_INTRANET');
	      }

	      // we need only this modules in this order!
	      const modulesToShowInFilter = ['tasks', 'calendar', 'crm', 'timeman', 'mail', 'disk', 'bizproc', 'voximplant', 'sender', 'blog', 'vote', 'socialnetwork', 'imopenlines', 'photogallery', 'intranet', 'forum'];
	      const notificationFilterTypes = [];
	      modulesToShowInFilter.forEach(moduleId => {
	        if (originalSchema[moduleId]) {
	          notificationFilterTypes.push(originalSchema[moduleId]);
	        }
	      });
	      return notificationFilterTypes;
	    }
	  },
	  watch: {
	    searchQuery() {
	      this.search();
	    },
	    searchType() {
	      this.search();
	    },
	    searchDate() {
	      this.search();
	    }
	  },
	  methods: {
	    search() {
	      this.$emit('search', {
	        searchQuery: this.searchQuery,
	        searchType: this.searchType,
	        searchDate: this.searchDate
	      });
	    },
	    onDateFilterClick(event) {
	      if (BX && BX.calendar && BX.calendar.get().popup) {
	        BX.calendar.get().popup.close();
	      }

	      // eslint-disable-next-line bitrix-rules/no-bx
	      BX.calendar({
	        node: event.target,
	        field: event.target,
	        bTime: false,
	        callback_after: () => {
	          this.searchDate = event.target.value;
	        }
	      });
	      return false;
	    }
	  },
	  template: `
		<div class="bx-im-notifications-header-filter-box">
			<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-sm ui-ctl-w25">
				<div class="ui-ctl-after ui-ctl-icon-angle"></div>
				<select class="ui-ctl-element" v-model="searchType">
					<option value="">
						{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_PLACEHOLDER') }}
					</option>
					<template v-for="group in filterTypes">
						<template v-if="group.LIST">
							<optgroup :label="group.NAME">
								<option v-for="option in group.LIST" :value="option.ID">
									{{ option.NAME }}
								</option>
							</optgroup>
						</template>
						<template v-else>
							<option :value="group.MODULE_ID">
								{{ group.NAME }}
							</option>
						</template>
					</template>
				</select>
			</div>
			<div class="ui-ctl ui-ctl-textbox ui-ctl-after-icon ui-ctl-sm ui-ctl-w50">
				<button class="ui-ctl-after ui-ctl-icon-clear" @click.prevent="searchQuery=''"></button>
				<input
					autofocus
					type="text"
					class="ui-ctl-element"
					v-model="searchQuery"
					:placeholder="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TEXT_PLACEHOLDER')"
				>
			</div>
			<div class="ui-ctl ui-ctl-after-icon ui-ctl-before-icon ui-ctl-sm ui-ctl-w25">
				<div class="ui-ctl-before ui-ctl-icon-calendar"></div>
				<input
					type="text"
					class="ui-ctl-element ui-ctl-textbox"
					v-model="searchDate"
					@focus.prevent.stop="onDateFilterClick"
					@click.prevent.stop="onDateFilterClick"
					:placeholder="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_DATE_PLACEHOLDER')"
					readonly
				>
				<button class="ui-ctl-after ui-ctl-icon-clear" @click.prevent="searchDate=''"></button>
			</div>
		</div>
	`
	};

	const NotificationScrollButton = {
	  name: 'NotificationScrollButton',
	  props: {
	    unreadCounter: {
	      type: Number,
	      default: 0
	    },
	    notificationsOnScreen: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['scrollButtonClick'],
	  computed: {
	    notificationCollection() {
	      return this.$store.getters['notifications/getSortedCollection'];
	    },
	    hasUnreadOnScreen() {
	      return [...this.notificationsOnScreen].some(id => {
	        var _this$notificationMap;
	        return !((_this$notificationMap = this.notificationMapCollection.get(id)) != null && _this$notificationMap.read);
	      });
	    },
	    firstUnreadId() {
	      const item = this.notificationCollection.find(notification => !notification.read);
	      if (!item) {
	        return;
	      }
	      return item.id;
	    },
	    firstUnreadBelowVisible() {
	      const minIdOnScreen = Math.min(...this.notificationsOnScreen);
	      const item = this.notificationCollection.find(notification => {
	        return !notification.read && notification.sectionCode === im_v2_const.NotificationTypesCodes.simple && minIdOnScreen > notification.id;
	      });
	      if (!item) {
	        return;
	      }
	      return item.id;
	    },
	    hasUnreadBelowVisible() {
	      let unreadCounterBeforeVisible = 0;
	      for (let i = 0; i <= this.notificationCollection.length - 1; i++) {
	        if (!this.notificationCollection[i].read) {
	          ++unreadCounterBeforeVisible;
	        }

	        // In this case we decide that there is no more unread notifications below visible notifications,
	        // so we show arrow up on scroll button.
	        if (this.notificationsOnScreen.has(this.notificationCollection[i].id) && this.unreadCounter === unreadCounterBeforeVisible) {
	          return false;
	        }
	      }
	      return true;
	    },
	    showScrollButton() {
	      // todo: check BXIM.settings.notifyAutoRead
	      if (this.unreadCounter === 0 || this.hasUnreadOnScreen) {
	        return false;
	      }
	      return true;
	    },
	    arrowButtonClass() {
	      const arrowDown = this.hasUnreadBelowVisible;
	      return {
	        'bx-im-notifications-scroll-button-arrow-down': arrowDown,
	        'bx-im-notifications-scroll-button-arrow-up': !arrowDown
	      };
	    },
	    formattedCounter() {
	      if (this.unreadCounter > 99) {
	        return '99+';
	      }
	      return `${this.unreadCounter}`;
	    },
	    ...ui_vue3_vuex.mapState({
	      notificationMapCollection: state => state.notifications.collection
	    })
	  },
	  methods: {
	    onScrollButtonClick() {
	      let idToScroll = null;
	      if (this.firstUnreadBelowVisible) {
	        idToScroll = this.firstUnreadBelowVisible;
	      } else if (!this.hasUnreadBelowVisible) {
	        idToScroll = this.firstUnreadId;
	      }
	      let firstUnreadNode = null;
	      if (idToScroll !== null) {
	        const selector = `.bx-im-content-notification-item__container[data-id="${idToScroll}"]`;
	        firstUnreadNode = document.querySelector(selector);
	      }
	      if (firstUnreadNode) {
	        this.$emit('scrollButtonClick', firstUnreadNode.offsetTop);
	      } else {
	        const latestNotification = this.notificationCollection[this.notificationCollection.length - 1];
	        const selector = `.bx-im-content-notification-item__container[data-id="${latestNotification.id}"]`;
	        const latestNotificationNode = document.querySelector(selector);
	        this.$emit('scrollButtonClick', latestNotificationNode.offsetTop);
	      }
	    }
	  },
	  template: `
		<transition name="bx-im-notifications-scroll-button">
			<div 
				v-show="showScrollButton" 
				class="bx-im-content-notification-scroll-button__container" 
				@click="onScrollButtonClick"
			>
				<div class="bx-im-content-notification-scroll-button__button">
					<div class="bx-im-notifications-scroll-button-counter">
						{{ formattedCounter }}
					</div>
					<div :class="arrowButtonClass"></div>
				</div>
			</div>
		</transition>
	`
	};

	const LIMIT_PER_PAGE = 50;
	class NotificationSearchService {
	  constructor() {
	    this.searchQuery = '';
	    this.searchType = '';
	    this.searchDate = null;
	    this.store = null;
	    this.restClient = null;
	    this.userManager = null;
	    this.isLoading = false;
	    this.lastId = 0;
	    this.hasMoreItemsToLoad = true;
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  loadFirstPage({
	    searchQuery,
	    searchType,
	    searchDate
	  }) {
	    this.isLoading = true;
	    this.searchQuery = searchQuery;
	    this.searchType = searchType;
	    this.searchDate = searchDate;
	    return this.requestItems({
	      firstPage: true
	    });
	  }
	  loadNextPage() {
	    if (this.isLoading || !this.hasMoreItemsToLoad) {
	      return Promise.resolve();
	    }
	    this.isLoading = true;
	    return this.requestItems();
	  }
	  searchInModel({
	    searchQuery,
	    searchType,
	    searchDate
	  }) {
	    this.searchQuery = searchQuery;
	    this.searchType = searchType;
	    this.searchDate = searchDate;
	    return this.store.getters['notifications/getSortedCollection'].filter(item => {
	      let result = false;
	      if (this.searchQuery.length >= 3) {
	        result = item.text.toLowerCase().includes(this.searchQuery.toLowerCase());
	        if (!result) {
	          return result;
	        }
	      }
	      if (this.searchType !== '') {
	        result = item.settingName === this.searchType; // todo: ???
	        if (!result) {
	          return result;
	        }
	      }
	      if (this.searchDate !== '') {
	        const date = BX.parseDate(this.searchDate);
	        if (date instanceof Date) {
	          // compare dates excluding time.
	          const itemDateForCompare = new Date(item.date.getTime()).setHours(0, 0, 0, 0);
	          const dateFromInput = date.setHours(0, 0, 0, 0);
	          result = itemDateForCompare === dateFromInput;
	        }
	      }
	      return result;
	    });
	  }
	  requestItems({
	    firstPage = false
	  } = {}) {
	    const queryParams = this.getSearchRequestParams(firstPage);
	    return this.restClient.callMethod(im_v2_const.RestMethod.imNotifyHistorySearch, queryParams).then(response => {
	      const responseData = response.data();
	      im_v2_lib_logger.Logger.warn('im.notify.history.search: first page results', responseData);
	      this.hasMoreItemsToLoad = !this.isLastPage(responseData.notifications);
	      if (!responseData || responseData.notifications.length === 0) {
	        im_v2_lib_logger.Logger.warn('im.notify.get: no notifications', responseData);
	        return [];
	      }
	      this.lastId = this.getLastItemId(responseData.notifications);
	      this.userManager.setUsersToModel(responseData.users);
	      this.isLoading = false;
	      return responseData.notifications;
	    }).catch(error => {
	      im_v2_lib_logger.Logger.warn('History request error', error);
	    });
	  }
	  getSearchRequestParams(firstPage) {
	    const requestParams = {
	      'SEARCH_TEXT': this.searchQuery,
	      'SEARCH_TYPE': this.searchType,
	      'LIMIT': LIMIT_PER_PAGE,
	      'CONVERT_TEXT': 'Y'
	    };
	    if (BX.parseDate(this.searchDate) instanceof Date) {
	      requestParams['SEARCH_DATE'] = BX.parseDate(this.searchDate).toISOString();
	    }
	    if (!firstPage) {
	      requestParams['LAST_ID'] = this.lastId;
	    }
	    return requestParams;
	  }
	  getLastItemId(collection) {
	    return collection[collection.length - 1].id;
	  }
	  isLastPage(notifications) {
	    if (!main_core.Type.isArrayFilled(notifications) || notifications.length < LIMIT_PER_PAGE) {
	      return true;
	    }
	    return false;
	  }
	  destroy() {
	    im_v2_lib_logger.Logger.warn('Notification search service destroyed');
	  }
	}

	class NotificationReadService {
	  constructor() {
	    this.itemsToRead = new Set();
	    this.changeReadStatusBlockTimeout = {};
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.readOnClientWithDebounce = main_core.Runtime.debounce(this.readOnClient, 50, this);
	    this.readRequestWithDebounce = main_core.Runtime.debounce(this.readRequest, 500, this);
	  }
	  addToReadQueue(notificationIds) {
	    if (!main_core.Type.isArrayFilled(notificationIds)) {
	      return;
	    }
	    notificationIds.forEach(id => {
	      if (!main_core.Type.isNumber(id)) {
	        return;
	      }
	      const notification = this.store.getters['notifications/getById'](id);
	      if (notification.read) {
	        return;
	      }
	      this.itemsToRead.add(id);
	    });
	  }
	  read() {
	    this.readOnClientWithDebounce();
	    this.readRequestWithDebounce();
	  }
	  readRequest() {
	    if (this.itemsToRead.size === 0) {
	      return;
	    }
	    const idToReadFrom = Math.min(...this.itemsToRead);
	    this.restClient.callMethod(im_v2_const.RestMethod.imNotifyRead, {
	      id: idToReadFrom
	    }).then(response => {
	      im_v2_lib_logger.Logger.warn(`I have read all the notifications from id ${idToReadFrom}`, response);
	    }).catch(() => {
	      // revert?
	    });
	    this.itemsToRead.clear();
	  }
	  readOnClient() {
	    this.store.dispatch('notifications/read', {
	      ids: [...this.itemsToRead],
	      read: true
	    });
	  }
	  readAll() {
	    this.store.dispatch('notifications/readAll');
	    this.restClient.callMethod(im_v2_const.RestMethod.imNotifyRead, {
	      id: 0
	    }).then(response => {
	      im_v2_lib_logger.Logger.warn('I have read ALL the notifications', response);
	    }).catch(error => {
	      console.error(error);
	    });
	  }
	  changeReadStatus(notificationId) {
	    const notification = this.store.getters['notifications/getById'](notificationId);
	    this.store.dispatch('notifications/read', {
	      ids: [notification.id],
	      read: !notification.read
	    });
	    clearTimeout(this.changeReadStatusBlockTimeout[notification.id]);
	    this.changeReadStatusBlockTimeout[notification.id] = setTimeout(() => {
	      this.restClient.callMethod(im_v2_const.RestMethod.imNotifyRead, {
	        id: notification.id,
	        action: notification.read ? 'N' : 'Y',
	        only_current: 'Y'
	      }).then(() => {
	        im_v2_lib_logger.Logger.warn(`Notification ${notification.id} unread status set to ${!notification.read}`);
	      }).catch(error => {
	        console.error(error);
	        //revert?
	      });
	    }, 1500);
	  }
	  destroy() {
	    im_v2_lib_logger.Logger.warn('Notification read service destroyed');
	  }
	}

	// @vue/component
	const NotificationContent = {
	  name: 'NotificationContent',
	  components: {
	    NotificationItem,
	    NotificationSearchPanel,
	    NotificationPlaceholder,
	    NotificationScrollButton,
	    ChatInfoPopup: im_v2_component_elements.ChatInfoPopup,
	    UserListPopup: im_v2_component_elements.UserListPopup,
	    Loader: im_v2_component_elements.Loader
	  },
	  directives: {
	    'notifications-item-observer': {
	      mounted(element, binding) {
	        binding.instance.observer.observe(element);
	      },
	      beforeUnmount(element, binding) {
	        binding.instance.observer.unobserve(element);
	      }
	    }
	  },
	  data: function () {
	    return {
	      isInitialLoading: false,
	      isNextPageLoading: false,
	      notificationsOnScreen: new Set(),
	      windowFocused: false,
	      showSearchPanel: false,
	      showSearchResult: false,
	      popupBindElement: null,
	      showChatInfoPopup: false,
	      chatInfoDialogId: null,
	      showUserListPopup: false,
	      userListIds: null,
	      schema: {}
	    };
	  },
	  computed: {
	    NotificationTypesCodes: () => im_v2_const.NotificationTypesCodes,
	    notificationCollection() {
	      return this.$store.getters['notifications/getSortedCollection'];
	    },
	    searchResultCollection() {
	      return this.$store.getters['notifications/getSearchResultCollection'];
	    },
	    notifications() {
	      if (this.showSearchResult) {
	        return this.searchResultCollection;
	      }
	      return this.notificationCollection;
	    },
	    isReadAllAvailable() {
	      if (this.showSearchResult) {
	        return false;
	      }
	      return this.unreadCounter > 0;
	    },
	    isEmptyState() {
	      return this.notifications.length === 0 && !this.isInitialLoading && !this.isNextPageLoading;
	    },
	    emptyStateIcon() {
	      return this.showSearchResult ? 'bx-im-content-notification__not-found-icon' : 'bx-im-content-notification__empty-state-icon';
	    },
	    emptyStateTitle() {
	      return this.showSearchResult ? this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_RESULTS_NOT_FOUND') : this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_NO_ITEMS');
	    },
	    ...ui_vue3_vuex.mapState({
	      unreadCounter: state => state.notifications.unreadCounter
	    })
	  },
	  watch: {
	    showSearchPanel(newValue, oldValue) {
	      if (newValue === false && oldValue === true) {
	        this.showSearchResult = false;
	        this.$store.dispatch('notifications/clearSearchResult');
	      }
	    }
	  },
	  created() {
	    this.notificationService = new im_v2_provider_service.NotificationService();
	    this.notificationSearchService = new NotificationSearchService();
	    this.notificationReadService = new NotificationReadService();
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 1500, this);
	    main_core.Event.bind(window, 'focus', this.onWindowFocus);
	    main_core.Event.bind(window, 'blur', this.onWindowBlur);
	    this.initObserver();
	  },
	  mounted() {
	    this.isInitialLoading = true;
	    this.windowFocused = document.hasFocus();
	    this.notificationService.loadFirstPage().then(response => {
	      this.schema = response;
	      this.isInitialLoading = false;
	    });
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.mention.openChatInfo, this.onOpenChatInfo);
	  },
	  beforeUnmount() {
	    this.notificationService.destroy();
	    this.notificationSearchService.destroy();
	    this.notificationReadService.destroy();
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.mention.openChatInfo, this.onOpenChatInfo);
	    main_core.Event.unbind(window, 'focus', this.onWindowFocus);
	    main_core.Event.unbind(window, 'blur', this.onWindowBlur);
	  },
	  methods: {
	    initObserver() {
	      this.observer = new IntersectionObserver(entries => {
	        entries.forEach(entry => {
	          const notificationId = Number.parseInt(entry.target.dataset.id, 10);
	          if (!entry.isIntersecting) {
	            this.notificationsOnScreen.delete(notificationId);
	            return;
	          }
	          if (entry.intersectionRatio >= 0.7 || entry.intersectionRatio > 0 && entry.intersectionRect.height > entry.rootBounds.height / 2) {
	            this.read(notificationId);
	            this.notificationsOnScreen.add(notificationId);
	          } else {
	            this.notificationsOnScreen.delete(notificationId);
	          }
	        });
	      }, {
	        root: this.$refs['listNotifications'],
	        threshold: Array.from({
	          length: 101
	        }).fill(0).map((zero, index) => index * 0.01)
	      });
	    },
	    read(notificationIds) {
	      if (!this.windowFocused) {
	        return;
	      }
	      if (main_core.Type.isNumber(notificationIds)) {
	        notificationIds = [notificationIds];
	      }
	      this.notificationReadService.addToReadQueue(notificationIds);
	      this.notificationReadService.read();
	    },
	    oneScreenRemaining(event) {
	      return event.target.scrollTop + event.target.clientHeight >= event.target.scrollHeight - event.target.clientHeight;
	    },
	    searchOnServer(event) {
	      this.notificationSearchService.loadFirstPage(event).then(result => {
	        this.isNextPageLoading = false;
	        this.setSearchResult(result);
	      });
	    },
	    setSearchResult(items) {
	      this.$store.dispatch('notifications/setSearchResult', {
	        notifications: items
	      });
	    },
	    //events
	    onScrollButtonClick(offset) {
	      this.$refs['listNotifications'].scroll({
	        top: offset,
	        behavior: 'smooth'
	      });
	    },
	    onScroll(event) {
	      this.showChatInfoPopup = false;
	      this.showUserListPopup = false;
	      if (this.showSearchResult) {
	        this.onScrollSearchResult(event);
	      } else {
	        this.onScrollNotifications(event);
	      }
	    },
	    onClickReadAll() {
	      const messageBox = new ui_dialogs_messagebox.MessageBox({
	        message: this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_READ_ALL_WARNING_POPUP'),
	        buttons: ui_dialogs_messagebox.MessageBoxButtons.YES_CANCEL,
	        onYes: () => {
	          this.notificationReadService.readAll();
	          messageBox.close();
	        },
	        onCancel: () => {
	          messageBox.close();
	        }
	      });
	      messageBox.show();
	    },
	    onScrollNotifications(event) {
	      if (!this.oneScreenRemaining(event) || !this.notificationService.hasMoreItemsToLoad || this.isInitialLoading || this.isNextPageLoading) {
	        return;
	      }
	      this.isNextPageLoading = true;
	      this.notificationService.loadNextPage().then(() => {
	        this.isNextPageLoading = false;
	      });
	    },
	    onScrollSearchResult(event) {
	      if (!this.oneScreenRemaining(event) || !this.notificationSearchService.hasMoreItemsToLoad || this.isInitialLoading || this.isNextPageLoading) {
	        return;
	      }
	      this.isNextPageLoading = true;
	      this.notificationSearchService.loadNextPage().then(result => {
	        this.isNextPageLoading = false;
	        this.setSearchResult(result);
	      });
	    },
	    onDoubleClick(notificationId) {
	      if (this.showSearchResult) {
	        return;
	      }
	      this.notificationReadService.changeReadStatus(notificationId);
	    },
	    onConfirmButtonsClick(button) {
	      const {
	        id,
	        value
	      } = button;
	      const notificationId = Number.parseInt(id, 10);
	      this.notificationsOnScreen.delete(notificationId);
	      this.notificationService.sendConfirmAction(notificationId, value);
	    },
	    onDeleteClick(notificationId) {
	      this.notificationsOnScreen.delete(notificationId);
	      this.notificationService.delete(notificationId);
	    },
	    onOpenChatInfo(event) {
	      const {
	        dialogId,
	        event: $event
	      } = event.getData();
	      this.popupBindElement = $event.target;
	      this.chatInfoDialogId = dialogId;
	      this.showChatInfoPopup = true;
	    },
	    onMoreUsersClick(event) {
	      im_v2_lib_logger.Logger.warn('onMoreUsersClick', event);
	      this.popupBindElement = event.event.target;
	      this.userListIds = event.users;
	      this.showUserListPopup = true;
	    },
	    onSearch(event) {
	      if (event.searchQuery.length < 3 && event.searchType === '' && event.searchDate === '') {
	        this.showSearchResult = false;
	        return;
	      }
	      this.showSearchResult = true;
	      const localResult = this.notificationSearchService.searchInModel(event);
	      this.$store.dispatch('notifications/clearSearchResult');
	      this.$store.dispatch('notifications/setSearchResult', {
	        notifications: localResult,
	        skipValidation: true
	      });
	      this.isNextPageLoading = true;
	      this.searchOnServerDelayed(event);
	    },
	    onSendQuickAnswer(event) {
	      this.notificationService.sendQuickAnswer(event);
	    },
	    onWindowFocus() {
	      this.windowFocused = true;
	      this.read([...this.notificationsOnScreen]);
	    },
	    onWindowBlur() {
	      this.windowFocused = false;
	    }
	  },
	  template: `
	<div class="bx-im-content-notification__container">
		<div class="bx-im-content-notification__header-container">
			<div class="bx-im-content-notification__header">
				<div class="bx-im-content-notification__header-panel-container">
					<div class="bx-im-content-notification__panel-title_icon"></div>
					<div class="bx-im-content-notification__panel_text">
						{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_HEADER') }}
					</div>
				</div>
				<div v-if="notificationCollection.length > 0" class="bx-im-content-notification__header-buttons-container">
					<transition name="notifications-read-all-fade">
						<div
							v-if="isReadAllAvailable"
							class="bx-im-content-notification__header_button bx-im-content-notification__header_read-all-button"
							@click="onClickReadAll"
							:title="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_READ_ALL_BUTTON')"
						></div>
					</transition>
					<div
						class="bx-im-content-notification__header_button bx-im-content-notification__header_filter-button"
						:class="[showSearchPanel ? '--active' : '']"
						@click="showSearchPanel = !showSearchPanel"
						:title="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_OPEN_BUTTON')"
					></div>
				</div>
			</div>
			<NotificationSearchPanel v-if="showSearchPanel" :schema="schema" @search="onSearch" />
		</div>
		<div class="bx-im-content-notification__elements-container">
			<div class="bx-im-content-notification__elements" @scroll.passive="onScroll" ref="listNotifications">
				<NotificationItem
					v-for="notification in notifications"
					:key="notification.id"
					:data-id="notification.id"
					:notification="notification"
					@dblclick="onDoubleClick"
					@confirmButtonsClick="onConfirmButtonsClick"
					@deleteClick="onDeleteClick"
					@moreUsersClick="onMoreUsersClick"
					@sendQuickAnswer="onSendQuickAnswer"
					v-notifications-item-observer
				/>
				<div v-if="isEmptyState" class="bx-im-content-notification__empty-state-container">
					<div :class="emptyStateIcon"></div>
					<span class="bx-im-content-notification__empty-state-title">
						{{ emptyStateTitle }}
					</span>
				</div>
				<NotificationPlaceholder v-if="isInitialLoading" />
				<div v-if="isNextPageLoading" class="bx-im-content-notification__loader-container">
					<Loader />
				</div>
			</div>
			<NotificationScrollButton
				v-if="!isInitialLoading || !isNextPageLoading"
				:unreadCounter="unreadCounter"
				:notificationsOnScreen="notificationsOnScreen"
				@scrollButtonClick="onScrollButtonClick"
			/>
			<ChatInfoPopup
				v-if="showChatInfoPopup"
				:dialogId="chatInfoDialogId"
				:bindElement="popupBindElement"
				:showPopup="showChatInfoPopup"
				@close="showChatInfoPopup = false"
			/>
			<UserListPopup
				v-if="showUserListPopup"
				:userIds="userListIds"
				:bindElement="popupBindElement"
				:showPopup="showUserListPopup"
				@close="showUserListPopup = false"
			/>
		</div>
	</div>
`
	};

	exports.NotificationContent = NotificationContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.Event,BX,BX.UI.Dialogs,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Im.V2.Lib,BX,BX.Vue3.Vuex,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=notification-content.bundle.js.map
