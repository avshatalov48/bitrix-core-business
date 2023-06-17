this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_designTokens,im_v2_provider_service,im_v2_lib_oldChatEmbedding_menu,main_date,im_v2_lib_parser,ui_vue3_vuex,main_popup,im_v2_component_oldChatEmbedding_elements,im_v2_lib_logger,im_v2_lib_utils,main_core,main_core_events,im_v2_const) {
	'use strict';

	// @vue/component
	const NewUserPopup = {
	  name: 'NewUserPopup',
	  props: {
	    title: {
	      type: String,
	      required: true
	    },
	    text: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['click', 'close'],
	  mounted() {
	    BX.MessengerProxy.playNewUserSound();
	    this.setCloseTimer(5000);
	    this.onClosePopupHandler = this.onClosePopup.bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.closePopup, this.onClosePopupHandler);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.closePopup, this.onClosePopupHandler);
	  },
	  methods: {
	    onClick() {
	      this.$emit('click');
	      this.$emit('close');
	    },
	    onMouseOver() {
	      clearTimeout(this.closeTimeout);
	    },
	    onMouseLeave() {
	      this.setCloseTimer(2000);
	    },
	    setCloseTimer(time) {
	      this.closeTimeout = setTimeout(() => {
	        this.$emit('close');
	      }, time);
	    },
	    onClosePopup() {
	      this.$emit('close');
	    }
	  },
	  // language=Vue
	  template: `
		<Transition name="bx-im-recent-new-user-popup">
			<div @click="onClick" @mouseover="onMouseOver" @mouseleave="onMouseLeave" class="bx-im-recent-new-user-popup">
				<div class="bx-im-recent-new-user-popup-title">{{ title }}</div>
				<div class="bx-im-recent-new-user-popup-text">{{ text }}</div>
			</div>
		</Transition>
	`
	};

	// @vue/component
	const RecentItem = {
	  name: 'RecentItem',
	  components: {
	    Avatar: im_v2_component_oldChatEmbedding_elements.Avatar,
	    ChatTitle: im_v2_component_oldChatEmbedding_elements.ChatTitle,
	    NewUserPopup
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    compactMode: {
	      type: Boolean,
	      default: false
	    },
	    isVisibleOnScreen: {
	      type: Boolean,
	      required: true
	    }
	  },
	  data() {
	    return {
	      showNewUserPopup: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_oldChatEmbedding_elements.AvatarSize,
	    formattedDate() {
	      if (this.needsBirthdayPlaceholder) {
	        return this.$Bitrix.Loc.getMessage('IM_RECENT_BIRTHDAY_DATE');
	      }
	      return this.formatDate(this.item.message.date);
	    },
	    messageText() {
	      const formattedText = im_v2_lib_parser.Parser.purifyRecent(this.item);
	      if (!formattedText) {
	        return this.isUser ? this.$store.getters['users/getPosition'](this.item.dialogId) : this.hiddenMessageText;
	      }
	      return formattedText;
	    },
	    formattedMessageText() {
	      const SPLIT_INDEX = 24;
	      return im_v2_lib_utils.Utils.text.insertUnseenWhitespace(this.messageText, SPLIT_INDEX);
	    },
	    hiddenMessageText() {
	      if (this.isUser) {
	        return this.$store.getters['users/getPosition'](this.item.dialogId);
	      }
	      if (this.dialog.type === im_v2_const.DialogType.open) {
	        return this.$Bitrix.Loc.getMessage('IM_RECENT_CHAT_TYPE_OPEN');
	      }
	      return this.$Bitrix.Loc.getMessage('IM_RECENT_CHAT_TYPE_GROUP');
	    },
	    statusIcon() {
	      if (!this.isLastMessageAuthor || this.isBot || this.needsBirthdayPlaceholder || !this.item.message) {
	        return '';
	      }
	      if (this.isSelfChat) {
	        return '';
	      }
	      if (this.item.message.status === im_v2_const.MessageStatus.error) {
	        return 'error';
	      }
	      if (this.item.liked) {
	        return 'like';
	      }
	      if (this.item.message.status === im_v2_const.MessageStatus.delivered) {
	        return 'read';
	      }
	      return 'unread';
	    },
	    formattedCounter() {
	      return this.dialog.counter > 99 ? '99+' : this.dialog.counter;
	    },
	    user() {
	      return this.$store.getters['users/get'](this.item.dialogId, true);
	    },
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.item.dialogId, true);
	    },
	    currentUserId() {
	      return this.$store.state.application.common.userId;
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.DialogType.user;
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    isSelfChat() {
	      return this.isUser && this.user.id === this.currentUserId;
	    },
	    isBot() {
	      if (this.isUser) {
	        return this.user.bot;
	      }
	      return false;
	    },
	    isLastMessageAuthor() {
	      if (!this.item.message) {
	        return false;
	      }
	      return this.currentUserId === this.item.message.senderId;
	    },
	    lastMessageAuthorAvatar() {
	      const authorDialog = this.$store.getters['dialogues/get'](this.item.message.senderId);
	      if (!authorDialog) {
	        return '';
	      }
	      return authorDialog.avatar;
	    },
	    lastMessageAuthorAvatarStyle() {
	      return {
	        backgroundImage: `url('${this.lastMessageAuthorAvatar}')`
	      };
	    },
	    isChatMuted() {
	      if (this.isUser) {
	        return false;
	      }
	      const isMuted = this.dialog.muteList.find(element => {
	        return element === this.currentUserId;
	      });
	      return !!isMuted;
	    },
	    needsBirthdayPlaceholder() {
	      if (!this.isUser) {
	        return false;
	      }
	      return this.$store.getters['recent/needsBirthdayPlaceholder'](this.item.dialogId);
	    },
	    showBirthdays() {
	      return this.$store.getters['recent/getOption'](im_v2_const.RecentSettings.showBirthday);
	    },
	    showLastMessage() {
	      return this.$store.getters['recent/getOption'](im_v2_const.RecentSettings.showLastMessage);
	    },
	    invitation() {
	      return this.item.invitation;
	    },
	    newUserPopupContainer() {
	      return `#popup-window-content-bx-im-recent-welcome-${this.item.dialogId}`;
	    }
	  },
	  watch: {
	    invitation(newValue, oldValue) {
	      if (!this.compactMode) {
	        return false;
	      }

	      // invitation accepted, user logged in
	      if (oldValue.isActive === true && newValue.isActive === false) {
	        this.openNewUserPopup();
	      }
	    }
	  },
	  methods: {
	    openNewUserPopup() {
	      if (!this.isVisibleOnScreen || BX.MessengerProxy.isSliderOpened()) {
	        return false;
	      }
	      this.newUserPopup = this.getNewUserPopup();
	      this.newUserPopup.show();
	      this.showNewUserPopup = true;
	      this.$nextTick(() => {
	        this.newUserPopup.setOffset({
	          offsetTop: -this.newUserPopup.popupContainer.offsetHeight + 1,
	          offsetLeft: -this.newUserPopup.popupContainer.offsetWidth + 13
	        });
	        this.newUserPopup.adjustPosition();
	      });
	    },
	    getNewUserPopup() {
	      return main_popup.PopupManager.create({
	        id: `bx-im-recent-welcome-${this.item.dialogId}`,
	        bindElement: this.$refs.container,
	        bindOptions: {
	          forceBindPosition: true
	        },
	        className: 'bx-im-recent-welcome',
	        cacheable: false,
	        animation: {
	          showClassName: 'bx-im-recent-new-user-popup-show',
	          closeClassName: 'bx-im-recent-new-user-popup-hide',
	          closeAnimationType: 'animation'
	        }
	      });
	    },
	    onNewUserPopupClick(event) {
	      const target = !this.compactMode || event.altKey ? im_v2_const.OpenTarget.current : im_v2_const.OpenTarget.auto;
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.open, {
	        ...this.item,
	        target
	      });
	    },
	    onNewUserPopupClose() {
	      this.newUserPopup.close();
	      this.newUserPopup = null;
	      this.showNewUserPopup = false;
	    },
	    formatDate(date) {
	      const format = [['today', 'H:i'], ['d7', 'D'], ['', 'd.m.Y']];
	      return BX.date.format(format, date);
	    }
	  },
	  // language=Vue
	  template: `
		<div :data-id="item.dialogId" class="bx-im-recent-item-wrap">
		<div v-if="!compactMode" :class="{'bx-im-recent-item-no-text': !showLastMessage, 'bx-im-recent-item-pinned': item.pinned}" class="bx-im-recent-item">
			<div class="bx-im-recent-avatar-wrap">
				<Avatar :dialogId="item.dialogId" :size="AvatarSize.L" :withTyping="true"/>
			</div>
			<div class="bx-im-recent-item-content">
				<div class="bx-im-recent-item-content-header">
					<ChatTitle :dialogId="item.dialogId" :withMute="true" />
					<div class="bx-im-recent-date">
						<div v-if="statusIcon" :class="'bx-im-recent-status-icon bx-im-recent-status-icon-' + statusIcon"></div>
						{{ formattedDate }}
					</div>
				</div>
				<div class="bx-im-recent-item-content-bottom">
					<div class="bx-im-recent-message-text-wrap">
						<!-- Message text -->
						<span class="bx-im-recent-message-text">
							<template v-if="item.draft.text && dialog.counter === 0">
								<span class="bx-im-recent-draft-prefix">{{ $Bitrix.Loc.getMessage('IM_RECENT_MESSAGE_DRAFT_2', {'#TEXT#': ''}) }}</span>
								<span>{{ item.draft.text }}</span>
							</template>
							<template v-else-if="item.invitation.isActive">
								<span class="bx-im-recent-message-text-invitation">{{ $Bitrix.Loc.getMessage('IM_RECENT_INVITATION_NOT_ACCEPTED') }}</span>
							</template>
							<template v-else-if="needsBirthdayPlaceholder">
								{{ $Bitrix.Loc.getMessage('IM_RECENT_BIRTHDAY') }}
							</template>
							<template v-else-if="!showLastMessage">
								{{ hiddenMessageText }}
							</template>
							<template v-else>
								<span v-if="isLastMessageAuthor" class="bx-im-recent-last-message-author-icon-self"></span>
								<template v-else-if="isChat && item.message.senderId">
									<span v-if="lastMessageAuthorAvatar" :style="lastMessageAuthorAvatarStyle" class="bx-im-recent-last-message-author-icon-user"></span>
									<span v-else class="bx-im-recent-last-message-author-icon-user bx-im-recent-last-message-author-icon-user-default"></span>
								</template>
								<span>{{ formattedMessageText }}</span>
							</template>
						</span>
						<!-- End message text -->
					</div>
					<div :class="{'bx-im-recent-counter-static-wrap-extended': dialog.counter > 99}" class="bx-im-recent-counter-static-wrap">
						<div v-if="item.unread || item.pinned || dialog.counter > 0" class="bx-im-recent-counter-wrap">
							<div v-if="item.pinned && dialog.counter === 0 && !item.unread" class="bx-im-recent-pinned-icon"></div>
							<div v-if="dialog.counter > 0 && !isSelfChat" :class="{'bx-im-recent-counter-muted': isChatMuted}" class="bx-im-recent-counter">
								{{ formattedCounter }}
							</div>
							<div v-else-if="item.unread" :class="{'bx-im-recent-counter-muted': isChatMuted}"  class="bx-im-recent-counter bx-im-recent-counter-unread"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div v-if="compactMode" class="bx-im-recent-item" :class="{'bx-im-recent-item-pinned': item.pinned, 'bx-im-recent-item-no-counter': dialog.counter === 0}" ref="container">
			<div class="bx-im-recent-avatar-wrap">
				<Avatar
					:dialogId="item.dialogId"
					:size="AvatarSize.M"
					:withStatus="false"
					:withCounter="true"
				/>
			</div>
			<template v-if="showNewUserPopup">
				<Teleport :to="newUserPopupContainer">
					<NewUserPopup :title="dialog.name" :text="$Bitrix.Loc.getMessage('IM_RECENT_NEW_USER_POPUP_TEXT')" @click="onNewUserPopupClick" @close="onNewUserPopupClose"/>
				</Teleport>
			</template>
		</div>
		</div>
	`
	};

	// @vue/component
	const ActiveCall = {
	  name: 'ActiveCall',
	  components: {
	    Avatar: im_v2_component_oldChatEmbedding_elements.Avatar
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    compactMode: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['click', 'contextmenu'],
	  computed: {
	    RecentCallStatus: () => im_v2_const.RecentCallStatus,
	    AvatarSize: () => im_v2_component_oldChatEmbedding_elements.AvatarSize,
	    chatData() {
	      return this.item.call.associatedEntity;
	    },
	    isUser() {
	      return this.chatData.advanced.chatType === im_v2_const.DialogType.private;
	    },
	    isTabWithActiveCall() {
	      return this.getCallController().hasActiveCall();
	    },
	    avatarStyle() {
	      return {
	        backgroundImage: `url(${this.chatData.avatar})`
	      };
	    },
	    avatarText() {
	      return im_v2_lib_utils.Utils.text.getFirstLetters(this.item.name);
	    },
	    isDarkTheme() {
	      return this.application.options.darkTheme;
	    },
	    formattedName() {
	      return im_v2_lib_utils.Utils.text.htmlspecialcharsback(this.item.name);
	    },
	    ...ui_vue3_vuex.mapState({
	      application: state => state.application
	    })
	  },
	  methods: {
	    onJoinClick(event) {
	      if (this.joinMenu) {
	        this.joinMenu.destroy();
	      }
	      this.joinMenu = this.getJoinMenu(event);
	      this.joinMenu.show();
	    },
	    onHangupClick() {
	      this.getCallController().leaveCurrentCall();
	    },
	    onClick(event) {
	      if (this.item.state === im_v2_const.RecentCallStatus.joined) {
	        this.getCallController().unfold();
	        return;
	      }
	      const item = this.$store.getters['recent/get'](this.item.dialogId);
	      if (!item) {
	        return;
	      }
	      this.$emit('click', {
	        item,
	        $event: event
	      });
	    },
	    onRightClick() {
	      const item = this.$store.getters['recent/get'](this.item.dialogId);
	      if (!item) {
	        return;
	      }
	      this.$emit('contextmenu', {
	        item,
	        $event: event
	      });
	    },
	    getJoinMenu(event) {
	      return main_popup.MenuManager.create({
	        id: 'im-recent-active-call-join-menu',
	        bindElement: event.target,
	        darkMode: this.isDarkTheme,
	        cacheable: false,
	        items: [{
	          text: main_core.Loc.getMessage('IM_RECENT_ACTIVE_CALL_JOIN_VIDEO'),
	          onclick: function () {
	            this.getCallController().joinCall(this.item.call.id, true);
	            this.joinMenu.close();
	          }.bind(this)
	        }, {
	          text: main_core.Loc.getMessage('IM_RECENT_ACTIVE_CALL_JOIN_AUDIO'),
	          onclick: function () {
	            this.getCallController().joinCall(this.item.call.id, false);
	            this.joinMenu.close();
	          }.bind(this)
	        }]
	      });
	    },
	    getCallController() {
	      return BX.MessengerProxy.getCallController();
	    }
	  },
	  template: `
		<div :data-id="item.dialogId" class="bx-im-recent-item-wrap">
		<div v-if="!compactMode" @click="onClick" @click.right.prevent="onRightClick" class="bx-im-recent-item bx-im-recent-active-call-item">
			<div class="bx-im-recent-avatar-wrap">
				<Avatar :dialogId="item.dialogId" :size="AvatarSize.L" />
			</div>
			<div class="bx-im-recent-item-content">
				<!-- Waiting status -->
				<template v-if="item.state === RecentCallStatus.waiting">
					<!-- 1-on-1 -->
					<div v-if="isUser"  class="bx-im-recent-active-call-waiting-content">
						<div class="bx-im-recent-active-call-icon bx-im-recent-active-call-waiting-icon"></div>
						<div class="bx-im-recent-active-call-waiting-title">
							{{ formattedName }}
						</div>
					</div>
					<!-- Chat -->
					<div v-else>
						<div class="bx-im-recent-item-content-header">
							<div class="bx-im-recent-active-call-icon bx-im-recent-active-call-waiting-icon"></div>
							<span class="bx-im-recent-active-call-waiting-title" :title="formattedName">
								{{ formattedName }}
							</span>
						</div>
						<div class="bx-im-recent-item-content-bottom">
							<div @click.stop="onJoinClick" class="bx-im-recent-active-call-button bx-im-recent-active-call-join-button">
								{{ $Bitrix.Loc.getMessage('IM_RECENT_ACTIVE_CALL_JOIN') }}
							</div>
						</div>
					</div>
				</template>
				<!-- Joined status -->
				<template v-else-if="item.state === RecentCallStatus.joined">
					<!-- 1-on-1 -->
					<div v-if="isUser || !isTabWithActiveCall" class="bx-im-recent-active-call-joined-content">
						<div class="bx-im-recent-active-call-icon bx-im-recent-active-call-joined-icon"></div>
						<div class="bx-im-recent-active-call-joined-title">
							{{ formattedName }}
						</div>
					</div>
					<!-- Chat -->
					<div v-else-if="!isUser && isTabWithActiveCall">
						<div class="bx-im-recent-item-content-header">
							<div class="bx-im-recent-active-call-icon bx-im-recent-active-call-joined-icon"></div>
							<span class="bx-im-recent-active-call-joined-title" :title="formattedName">
								{{ formattedName }}
							</span>
						</div>
						<div class="bx-im-recent-item-content-bottom">
							<div @click.stop="onHangupClick" class="bx-im-recent-active-call-button bx-im-recent-active-call-hangup-button">
								{{ $Bitrix.Loc.getMessage('IM_RECENT_ACTIVE_CALL_HANGUP') }}
							</div>
						</div>
					</div>
				</template>
			</div>
		</div>
		<div v-if="compactMode" @click="onClick" @click.right.prevent="onRightClick" class="bx-im-recent-item bx-im-recent-active-call-item">
			<div class="bx-im-recent-avatar-wrap">
				<Avatar :dialogId="item.dialogId" :size="AvatarSize.M" />
				<div class="bx-im-recent-active-call-compact-icon-container">
					<div v-if="item.state === RecentCallStatus.waiting" class="bx-im-recent-active-call-icon bx-im-recent-active-call-waiting-icon"></div>
					<div v-else-if="item.state === RecentCallStatus.joined" class="bx-im-recent-active-call-icon bx-im-recent-active-call-joined-icon"></div>
				</div>
			</div>
		</div>
		</div>
	`
	};

	class SettingsManager {
	  static init($Bitrix) {
	    if (this.instance) {
	      return;
	    }
	    this.instance = new this($Bitrix);
	  }
	  constructor($Bitrix) {
	    this.store = null;
	    this.store = $Bitrix.Data.get('controller').store;
	    this.initSettings();
	    this.onSettingsChangeHandler = this.onSettingsChange.bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.settingsChange, this.onSettingsChangeHandler);
	    if (im_v2_lib_utils.Utils.platform.isBitrixDesktop() && !main_core.Type.isUndefined(BX.desktop)) {
	      BX.desktop.addCustomEvent('bxSaveSettings', settings => {
	        this.onSettingsChangeHandler({
	          data: settings
	        });
	      });
	    }
	  }
	  initSettings() {
	    if (!BX.MessengerProxy) {
	      console.error('Im.RecentList: SettingsManager: BX.MessengerProxy is not available');
	      return false;
	    }
	    this.initGeneralSettings();
	    this.initRecentSettings();
	  }
	  initGeneralSettings() {
	    const initialSettings = {};
	    Object.entries(im_v2_const.SettingsMap).forEach(([oldName, name]) => {
	      initialSettings[name] = BX.MessengerProxy.getOption(oldName);
	    });
	    this.store.dispatch('application/setOptions', initialSettings);
	  }
	  initRecentSettings() {
	    const initialSettings = {};
	    Object.entries(im_v2_const.RecentSettingsMap).forEach(([oldName, name]) => {
	      initialSettings[name] = BX.MessengerProxy.getOption(oldName);
	    });
	    this.store.dispatch('recent/setOptions', initialSettings);
	  }
	  onSettingsChange({
	    data: event
	  }) {
	    im_v2_lib_logger.Logger.warn('Im.RecentList: SettingsChange', event);
	    const generalSettings = {};
	    const recentSettings = {};
	    Object.entries(event).forEach(([name, value]) => {
	      if (Object.keys(im_v2_const.RecentSettingsMap).includes(name)) {
	        recentSettings[im_v2_const.RecentSettingsMap[name]] = value;
	      }
	      if (Object.keys(im_v2_const.SettingsMap).includes(name)) {
	        generalSettings[im_v2_const.SettingsMap[name]] = value;
	      }
	    });
	    this.store.dispatch('application/setOptions', generalSettings);
	    this.store.dispatch('recent/setOptions', recentSettings);
	  }
	  destroy() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.settingsChange, this.onSettingsChangeHandler);
	  }
	}
	SettingsManager.instance = null;

	class BroadcastManager extends main_core_events.EventEmitter {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
	    super();
	    this.setEventNamespace(BroadcastManager.eventNamespace);
	    this.init();
	  }
	  isSupported() {
	    return !main_core.Type.isUndefined(window.BroadcastChannel) && !im_v2_lib_utils.Utils.platform.isBitrixDesktop();
	  }
	  init() {
	    if (!this.isSupported()) {
	      return;
	    }
	    this.channel = new BroadcastChannel(BroadcastManager.channelName);
	    this.channel.addEventListener('message', ({
	      data: {
	        type,
	        data
	      }
	    }) => {
	      this.emit(type, data);
	    });
	  }
	  sendRecentList(recentData) {
	    if (!this.isSupported()) {
	      return;
	    }
	    this.channel.postMessage({
	      type: BroadcastManager.events.recentListUpdate,
	      data: recentData
	    });
	  }
	}
	BroadcastManager.instance = null;
	BroadcastManager.channelName = 'im-recent';
	BroadcastManager.eventNamespace = 'BX.Messenger.v2.Recent.BroadcastManager';
	BroadcastManager.events = {
	  recentListUpdate: 'recentListUpdate'
	};

	class CallManager {
	  static init($Bitrix) {
	    if (this.instance) {
	      return;
	    }
	    this.instance = new this($Bitrix);
	  }
	  constructor($Bitrix) {
	    this.store = null;
	    this.store = $Bitrix.Data.get('controller').store;
	    this.onCallCreatedHandler = this.onCallCreated.bind(this);
	    main_core_events.EventEmitter.subscribe('CallEvents::callCreated', this.onCallCreatedHandler);
	  }
	  onCallCreated(event) {
	    const {
	      call
	    } = event.getData()[0];
	    call.addEventListener(BX.Call.Event.onJoin, this.onCallJoin.bind(this));
	    call.addEventListener(BX.Call.Event.onLeave, this.onCallLeave.bind(this));
	    call.addEventListener(BX.Call.Event.onDestroy, this.onCallDestroy.bind(this));
	    this.store.dispatch('recent/addActiveCall', {
	      dialogId: call.associatedEntity.id,
	      name: call.associatedEntity.name,
	      call: call,
	      state: im_v2_const.RecentCallStatus.waiting
	    });
	  }
	  onCallJoin(event) {
	    this.store.dispatch('recent/updateActiveCall', {
	      dialogId: event.call.associatedEntity.id,
	      fields: {
	        state: im_v2_const.RecentCallStatus.joined
	      }
	    });
	  }
	  onCallLeave(event) {
	    this.store.dispatch('recent/updateActiveCall', {
	      dialogId: event.call.associatedEntity.id,
	      fields: {
	        state: im_v2_const.RecentCallStatus.waiting
	      }
	    });
	  }
	  onCallDestroy(event) {
	    this.store.dispatch('recent/deleteActiveCall', {
	      dialogId: event.call.associatedEntity.id
	    });
	  }
	  destroy() {
	    main_core_events.EventEmitter.unsubscribe(window, 'CallEvents::callCreated', this.onCallCreatedHandler);
	  }
	}
	CallManager.instance = null;

	class DraftManager {
	  static init($Bitrix) {
	    if (this.instance) {
	      return;
	    }
	    this.instance = new this($Bitrix);
	  }
	  constructor($Bitrix) {
	    this.store = null;
	    this.store = $Bitrix.Data.get('controller').store;
	    this.initDraftHistory();
	    this.onSetDraftHandler = this.onSetDraft.bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.setDraftMessage, this.onSetDraftHandler);
	  }
	  initDraftHistory() {
	    if (!BX.MessengerProxy) {
	      return false;
	    }
	    const history = BX.MessengerProxy.getTextareaHistory();
	    Object.entries(history).forEach(([dialogId, text]) => {
	      this.setDraftMessage(dialogId, text);
	    });
	  }
	  onSetDraft({
	    data: {
	      dialogId,
	      text
	    }
	  }) {
	    this.setDraftMessage(dialogId, text);
	  }
	  setDraftMessage(dialogId, text) {
	    this.store.dispatch('recent/draft', {
	      id: dialogId,
	      text
	    });
	  }
	  destroy() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.recent.setDraftMessage, this.onSetDraftHandler);
	  }
	}
	DraftManager.instance = null;

	class EventHandler {
	  static init($Bitrix) {
	    if (this.instance) {
	      return;
	    }
	    this.instance = new this($Bitrix);
	  }
	  constructor($Bitrix) {
	    this.store = null;
	    this.store = $Bitrix.Data.get('controller').store;
	    this.subscribeToEvents();
	  }
	  subscribeToEvents() {
	    this.onSetCounterHandler = this.onSetCounter.bind(this);
	    this.onSetMessageHandler = this.onSetMessage.bind(this);
	    this.onHideChatHandler = this.onHideChat.bind(this);
	    this.onLeaveChatHandler = this.onLeaveChat.bind(this);
	    this.onClearLikeHandler = this.onClearLike.bind(this);
	    this.onClearHistoryHandler = this.onClearHistory.bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.setCounter, this.onSetCounterHandler);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.setMessage, this.onSetMessageHandler);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.hideChat, this.onHideChatHandler);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.leaveChat, this.onLeaveChatHandler);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.clearLike, this.onClearLikeHandler);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.clearHistory, this.onClearHistoryHandler);
	  }
	  onSetCounter({
	    data: {
	      dialogId,
	      counter
	    }
	  }) {
	    const recentItem = this.store.getters['recent/get'](dialogId);
	    const dialog = this.store.getters['dialogues/get'](dialogId);
	    if (!recentItem || !dialog) {
	      return false;
	    }
	    this.store.dispatch('dialogues/update', {
	      dialogId: dialogId,
	      fields: {
	        counter: counter
	      }
	    });
	  }
	  onSetMessage({
	    data: {
	      id,
	      dialogId,
	      text,
	      date
	    }
	  }) {
	    const recentItem = this.store.getters['recent/get'](dialogId);
	    const dialog = this.store.getters['dialogues/get'](dialogId);
	    if (!recentItem || !dialog) {
	      return false;
	    }
	    if (id && !id.toString().startsWith('temp') && id !== recentItem.message.id) {
	      return false;
	    }
	    this.store.dispatch('recent/update', {
	      id: dialogId,
	      fields: {
	        message: {
	          id: id || 0,
	          text: text,
	          senderId: this.getCurrentUserId(),
	          status: recentItem.message.status,
	          date: date || recentItem.message.date
	        }
	      }
	    });
	  }
	  onHideChat({
	    data: {
	      dialogId
	    }
	  }) {
	    const recentItem = this.store.getters['recent/get'](dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    this.store.dispatch('recent/delete', {
	      id: dialogId
	    });
	  }
	  onLeaveChat({
	    data: {
	      dialogId
	    }
	  }) {
	    this.onHideChat({
	      data: {
	        dialogId
	      }
	    });
	  }
	  onClearLike({
	    data: {
	      dialogId
	    }
	  }) {
	    const recentItem = this.store.getters['recent/get'](dialogId);
	    if (!recentItem || !recentItem.liked) {
	      return false;
	    }
	    this.store.dispatch('recent/like', {
	      id: dialogId,
	      liked: false
	    });
	  }
	  onClearHistory({
	    data: {
	      dialogId
	    }
	  }) {
	    const recentItem = this.store.getters['recent/get'](dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    this.store.dispatch('recent/update', {
	      id: dialogId,
	      fields: {
	        message: {
	          ...recentItem.message,
	          text: main_core.Loc.getMessage('IM_RECENT_DELETED_MESSAGE')
	        }
	      }
	    });
	  }
	  getCurrentUserId() {
	    return this.store.state.application.common.userId;
	  }
	  destroy() {
	    this.unsubscribeEvents();
	  }
	  unsubscribeEvents() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.recent.setCounter, this.onSetCounterHandler);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.recent.setMessage, this.onSetMessageHandler);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.recent.hideChat, this.onHideChatHandler);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.recent.leaveChat, this.onLeaveChatHandler);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.recent.clearLike, this.onClearLikeHandler);
	  }
	}
	EventHandler.instance = null;

	// @vue/component
	const RecentList = {
	  name: 'RecentList',
	  components: {
	    LoadingState: im_v2_component_oldChatEmbedding_elements.RecentLoadingState,
	    RecentItem,
	    ActiveCall
	  },
	  directives: {
	    'recent-list-observer': {
	      mounted(element, binding) {
	        binding.instance.observer.observe(element);
	      }
	    }
	  },
	  props: {
	    compactMode: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      isLoading: false,
	      visibleElements: new Set()
	    };
	  },
	  computed: {
	    collection() {
	      return this.$store.getters['recent/getRecentCollection'];
	    },
	    sections() {
	      return [this.pinnedItems, this.generalItems];
	    },
	    preparedItems() {
	      const filteredCollection = this.collection.filter(item => {
	        if (!this.showBirthdays && item.options.birthdayPlaceholder) {
	          return false;
	        }
	        const dialog = this.$store.getters['dialogues/get'](item.dialogId, true);
	        const isUser = dialog.type === im_v2_const.DialogType.user;
	        const hasBirthday = isUser && this.showBirthdays && this.$store.getters['users/hasBirthday'](item.dialogId);
	        if (!this.showInvited && item.options.defaultUserRecord && !hasBirthday) {
	          return false;
	        }
	        return true;
	      });
	      return [...filteredCollection].sort((a, b) => {
	        const firstDate = this.$store.getters['recent/getMessageDate'](a.dialogId);
	        const secondDate = this.$store.getters['recent/getMessageDate'](b.dialogId);
	        return secondDate - firstDate;
	      });
	    },
	    pinnedItems() {
	      return this.preparedItems.filter(item => {
	        return item.pinned === true;
	      });
	    },
	    generalItems() {
	      return this.preparedItems.filter(item => {
	        return item.pinned === false;
	      });
	    },
	    isDarkTheme() {
	      return this.application.options.darkTheme;
	    },
	    showBirthdays() {
	      return this.$store.getters['recent/getOption'](im_v2_const.RecentSettings.showBirthday);
	    },
	    showInvited() {
	      return this.$store.getters['recent/getOption'](im_v2_const.RecentSettings.showInvited);
	    },
	    transitionType() {
	      if (this.compactMode) {
	        return '';
	      }
	      if (this.isLoading) {
	        return '';
	      }
	      return 'bx-messenger-recent-transition';
	    },
	    ...ui_vue3_vuex.mapState({
	      activeCalls: state => state.recent.activeCalls,
	      application: state => state.application
	    })
	  },
	  created() {
	    this.recentService = im_v2_provider_service.RecentService.getInstance();
	    this.contextMenuManager = new im_v2_lib_oldChatEmbedding_menu.RecentMenu(this.$Bitrix);
	    CallManager.init(this.$Bitrix);
	    EventHandler.init(this.$Bitrix);
	    SettingsManager.init(this.$Bitrix);
	    this.initBroadcastManager();
	    this.initObserver();
	    this.managePreloadedList();
	    this.manageChatOptions();
	  },
	  mounted() {
	    this.isLoading = true;
	    this.recentService.loadFirstPage().then(() => {
	      this.isLoading = false;
	      DraftManager.init(this.$Bitrix);
	    });
	    this.initBirthdayCheck();
	  },
	  beforeUnmount() {
	    this.contextMenuManager.destroy();
	    this.clearBirthdayCheck();
	    this.destroyBroadcastManager();
	  },
	  methods: {
	    onScroll(event) {
	      this.contextMenuManager.close();
	      if (!this.oneScreenRemaining(event) || !this.recentService.hasMoreItemsToLoad) {
	        return false;
	      }
	      this.isLoading = true;
	      this.recentService.loadNextPage().then(() => {
	        this.isLoading = false;
	      });
	    },
	    onClick(item, event) {
	      const target = !this.compactMode || event.altKey ? im_v2_const.OpenTarget.current : im_v2_const.OpenTarget.auto;
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.open, {
	        ...item,
	        chat: this.$store.getters['dialogues/get'](item.dialogId, true),
	        user: this.$store.getters['users/get'](item.dialogId, true),
	        target
	      });
	    },
	    onRightClick(item, event) {
	      if (event.altKey && event.shiftKey) {
	        return;
	      }
	      const target = !this.compactMode || event.altKey ? im_v2_const.OpenTarget.current : im_v2_const.OpenTarget.auto;
	      const context = {
	        ...item,
	        compactMode: this.compactMode,
	        target
	      };
	      this.contextMenuManager.openMenu(context, event.currentTarget);
	      event.preventDefault();
	    },
	    onCallClick({
	      item,
	      $event
	    }) {
	      this.onClick(item, $event);
	    },
	    onCallRightClick({
	      item,
	      $event
	    }) {
	      this.onRightClick(item, $event);
	    },
	    oneScreenRemaining(event) {
	      return event.target.scrollTop + event.target.clientHeight >= event.target.scrollHeight - event.target.clientHeight;
	    },
	    initObserver() {
	      this.observer = new IntersectionObserver(entries => {
	        entries.forEach(entry => {
	          if (entry.isIntersecting && entry.intersectionRatio === 1) {
	            this.visibleElements.add(entry.target.dataset.id);
	          } else if (!entry.isIntersecting) {
	            this.visibleElements.delete(entry.target.dataset.id);
	          }
	        });
	      }, {
	        threshold: [0, 1]
	      });
	    },
	    initBroadcastManager() {
	      this.onRecentListUpdate = event => {
	        this.recentService.setPreloadedData(event.data);
	      };
	      this.broadcastManager = BroadcastManager.getInstance();
	      this.broadcastManager.subscribe(BroadcastManager.events.recentListUpdate, this.onRecentListUpdate);
	    },
	    destroyBroadcastManager() {
	      this.broadcastManager = BroadcastManager.getInstance();
	      this.broadcastManager.unsubscribe(BroadcastManager.events.recentListUpdate, this.onRecentListUpdate);
	    },
	    initBirthdayCheck() {
	      const fourHours = 60000 * 60 * 4;
	      const day = 60000 * 60 * 24;
	      this.birthdayCheckTimeout = setTimeout(() => {
	        this.recentService.loadFirstPage({
	          ignorePreloadedItems: true
	        });
	        this.birthdayCheckInterval = setInterval(() => {
	          this.recentService.loadFirstPage({
	            ignorePreloadedItems: true
	          });
	        }, day);
	      }, im_v2_lib_utils.Utils.date.getTimeToNextMidnight() + fourHours);
	    },
	    clearBirthdayCheck() {
	      clearTimeout(this.birthdayCheckTimeout);
	      clearInterval(this.birthdayCheckInterval);
	    },
	    managePreloadedList() {
	      const {
	        preloadedList
	      } = this.$Bitrix.Application.get().params;
	      if (!preloadedList) {
	        return false;
	      }
	      this.recentService.setPreloadedData(preloadedList);
	      this.broadcastManager.sendRecentList(preloadedList);
	    },
	    manageChatOptions() {
	      const {
	        chatOptions
	      } = this.$Bitrix.Application.get().params;
	      if (!chatOptions) {
	        return false;
	      }
	      this.$store.dispatch('dialogues/setChatOptions', chatOptions);
	    }
	  },
	  template: `
		<div @scroll="onScroll" class="bx-messenger-recent-list" :class="{'bx-messenger-recent-compact': compactMode}" >
			<transition-group :name="transitionType">
				<ActiveCall
					v-for="activeCall in activeCalls"
					:key="'call-' + activeCall.dialogId"
					:item="activeCall"
					:compactMode="compactMode"
					@click="onCallClick"
					@click.right="onCallRightClick"
				/>
				<template v-for="section in sections">
					<RecentItem
						v-for="item in section"
						:key="item.dialogId"
						:item="item"
						:compactMode="compactMode"
						:isVisibleOnScreen="visibleElements.has(item.dialogId)"
						v-recent-list-observer
						@click="onClick(item, $event)"
						@click.right="onRightClick(item, $event)"
					/>
				</template>
			</transition-group>
			<LoadingState v-if="isLoading" :compactMode="compactMode" />
			<template v-if="collection.length === 0">
				<div class="bx-im-recent-empty">{{ $Bitrix.Loc.getMessage('IM_RECENT_EMPTY') }}</div>
			</template>
		</div>
	`
	};

	exports.RecentList = RecentList;

}((this.BX.Messenger.v2.ComponentLegacy = this.BX.Messenger.v2.ComponentLegacy || {}),BX,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.LibLegacy,BX.Main,BX.Messenger.v2.Lib,BX.Vue3.Vuex,BX.Main,BX.Messenger.v2.ComponentLegacy,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Event,BX.Messenger.v2.Const));
//# sourceMappingURL=recent-list.bundle.js.map
