/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_lib_logger,im_v2_lib_helpdesk,ui_feedback_form,im_v2_component_dialog_chat,im_v2_lib_theme,im_v2_lib_user,ui_forms,main_core,im_v2_application_core,im_v2_lib_rest,im_v2_lib_utils,im_v2_lib_desktopApi,im_v2_lib_confirm,im_v2_const,im_v2_provider_service) {
	'use strict';

	const SectionMetaData = {
	  appearance: {
	    name: main_core.Loc.getMessage('IM_CONTENT_SETTINGS_SECTION_LIST_APPEARANCE'),
	    icon: 'fa-solid fa-brush'
	  },
	  notification: {
	    name: main_core.Loc.getMessage('IM_CONTENT_SETTINGS_SECTION_LIST_NOTIFICATION'),
	    icon: 'fa-solid fa-bell'
	  },
	  hotkey: {
	    name: main_core.Loc.getMessage('IM_CONTENT_SETTINGS_SECTION_LIST_HOTKEY'),
	    icon: 'fa-solid fa-keyboard'
	  },
	  message: {
	    name: main_core.Loc.getMessage('IM_CONTENT_SETTINGS_SECTION_LIST_MESSAGE'),
	    icon: 'fa-solid fa-message'
	  },
	  recent: {
	    name: main_core.Loc.getMessage('IM_CONTENT_SETTINGS_SECTION_LIST_RECENT'),
	    icon: 'fa-solid fa-list-ul'
	  },
	  desktop: {
	    name: main_core.Loc.getMessage('IM_CONTENT_SETTINGS_SECTION_LIST_DESKTOP'),
	    icon: 'fa-solid fa-desktop'
	  },
	  help: {
	    name: main_core.Loc.getMessage('IM_CONTENT_SETTINGS_SECTION_LIST_HELP'),
	    icon: 'fa-solid fa-question'
	  },
	  feedback: {
	    name: main_core.Loc.getMessage('IM_CONTENT_SETTINGS_SECTION_LIST_FEEDBACK'),
	    icon: 'fa-solid fa-comment'
	  }
	};

	const AdditionalSections = {
	  help: 'help',
	  feedback: 'feedback'
	};

	// @vue/component
	const SectionList = {
	  name: 'SectionList',
	  props: {
	    activeSection: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['sectionClick'],
	  data() {
	    return {};
	  },
	  computed: {
	    AdditionalSections: () => AdditionalSections,
	    disabledSections() {
	      const disabledSections = new Set([im_v2_const.SettingsSection.message]);
	      if (!im_v2_lib_desktopApi.DesktopApi.isDesktop()) {
	        disabledSections.add(im_v2_const.SettingsSection.desktop);
	      }
	      return disabledSections;
	    },
	    sections() {
	      return Object.keys(im_v2_const.SettingsSection).filter(section => {
	        return !this.disabledSections.has(section);
	      });
	    }
	  },
	  methods: {
	    getSectionName(section) {
	      return SectionMetaData[section].name;
	    },
	    getSectionIconClass(section) {
	      return SectionMetaData[section].icon;
	    },
	    onHelpClick() {
	      const ARTICLE_CODE = '17373696';
	      im_v2_lib_helpdesk.openHelpdeskArticle(ARTICLE_CODE);
	    },
	    onFeedbackClick() {
	      BX.UI.Feedback.Form.open({
	        id: 'im-v2-feedback',
	        forms: [{
	          zones: ['ru'],
	          id: 550,
	          sec: '50my2x',
	          lang: 'ru'
	        }, {
	          zones: ['en'],
	          id: 560,
	          sec: '621lbr',
	          lang: 'ru'
	        }],
	        presets: {
	          sender_page: 'profile'
	        }
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-settings-section-list__container">
			<div class="bx-im-settings-section-list__title">
				{{ loc('IM_CONTENT_SETTINGS_SECTION_LIST_TITLE') }}
			</div>
			<div
				v-for="section in sections"
				:key="section"
				class="bx-im-settings-section-list__item"
				:class="{'--active': section === activeSection}"
				@click="$emit('sectionClick', section)"
			>
				<div class="bx-im-settings-section-list__item_icon">
					<i :class="getSectionIconClass(section)"></i>
				</div>
				<div class="bx-im-settings-section-list__item_title">{{ getSectionName(section) }}</div>
			</div>
			<!-- Help -->
			<div
				class="bx-im-settings-section-list__item"
				@click="onHelpClick"
			>
				<div class="bx-im-settings-section-list__item_icon">
					<i :class="getSectionIconClass(AdditionalSections.help)"></i>
				</div>
				<div class="bx-im-settings-section-list__item_title">{{ getSectionName(AdditionalSections.help) }}</div>
			</div>
			<!-- Feedback -->
			<div
				class="bx-im-settings-section-list__item"
				@click="onFeedbackClick"
			>
				<div class="bx-im-settings-section-list__item_icon">
					<i :class="getSectionIconClass(AdditionalSections.feedback)"></i>
				</div>
				<div class="bx-im-settings-section-list__item_title">{{ getSectionName(AdditionalSections.feedback) }}</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const ChatBackground = {
	  name: 'ChatBackground',
	  emits: ['close'],
	  computed: {
	    currentBackgroundId() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.appearance.background).toString();
	    },
	    backgroundIdList() {
	      return Object.keys(im_v2_lib_theme.SelectableBackground);
	    }
	  },
	  methods: {
	    getBackgroundStyleById(backgroundId) {
	      return im_v2_lib_theme.ThemeManager.getBackgroundStyleById(backgroundId);
	    },
	    onBackgroundClick(backgroundId) {
	      const preparedBackgroundId = Number.parseInt(backgroundId, 10);
	      const settingsService = new im_v2_provider_service.SettingsService();
	      settingsService.changeSetting(im_v2_const.Settings.appearance.background, preparedBackgroundId);
	    }
	  },
	  template: `
		<div class="bx-im-settings-background__container">
			<div class="bx-im-settings-background__list">
				<div
					v-for="id in backgroundIdList"
					:key="id"
					:style="getBackgroundStyleById(id)"
					class="bx-im-background-select-popup__item bx-im-settings-background__item"
					:class="{'--active': id === currentBackgroundId}"
					@click="onBackgroundClick(id)"
				></div>
			</div>
		</div>
	`
	};

	// @vue/component
	const ChatAlignment = {
	  name: 'ChatAlignment',
	  emits: ['close'],
	  computed: {
	    DialogAlignment: () => im_v2_const.DialogAlignment,
	    currentOptionId() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.appearance.alignment);
	    },
	    backgroundStyle() {
	      return im_v2_lib_theme.ThemeManager.getCurrentBackgroundStyle();
	    }
	  },
	  methods: {
	    onOptionClick(optionId) {
	      this.getSettingsService().changeSetting(im_v2_const.Settings.appearance.alignment, optionId);
	    },
	    getSettingsService() {
	      if (!this.settingsService) {
	        this.settingsService = new im_v2_provider_service.SettingsService();
	      }
	      return this.settingsService;
	    }
	  },
	  template: `
		<div class="bx-im-settings-alignment__container">
			<div class="bx-im-settings-alignment__list">
				<div
					class="bx-im-settings-alignment__item --left"
					:class="{'--active': currentOptionId === DialogAlignment.left}"
					:style="backgroundStyle"
					@click="onOptionClick(DialogAlignment.left)"
				>
					<div class="bx-im-settings-alignment__item_content"></div>
					<div v-if="currentOptionId === DialogAlignment.left" class="bx-im-settings-alignment__item_checkmark"></div>
				</div>
				<div
					class="bx-im-settings-alignment__item --center"
					:class="{'--active': currentOptionId === DialogAlignment.center}"
					:style="backgroundStyle"
					@click="onOptionClick(DialogAlignment.center)"
				>
					<div class="bx-im-settings-alignment__item_content"></div>
					<div v-if="currentOptionId === DialogAlignment.center" class="bx-im-settings-alignment__item_checkmark"></div>
				</div>
			</div>
		</div>
	`
	};

	const DEMO_DIALOG_ID = 'settings';
	const demoChat = {
	  dialogId: DEMO_DIALOG_ID,
	  chatId: -1,
	  type: im_v2_const.ChatType.chat,
	  inited: true,
	  role: im_v2_const.UserRole.guest
	};
	const demoUser = {
	  id: -1,
	  name: main_core.Loc.getMessage('IM_CONTENT_SETTINGS_DEMO_CHAT_USER_NAME')
	};
	const demoMessage1 = {
	  id: -3,
	  chatId: demoChat.chatId,
	  authorId: demoUser.id,
	  text: main_core.Loc.getMessage('IM_CONTENT_SETTINGS_DEMO_CHAT_MESSAGE_1'),
	  viewedByOthers: true
	};
	const demoMessage2 = {
	  id: -2,
	  chatId: demoChat.chatId,
	  authorId: im_v2_application_core.Core.getUserId(),
	  replyId: demoMessage1.id,
	  text: main_core.Loc.getMessage('IM_CONTENT_SETTINGS_DEMO_CHAT_MESSAGE_2'),
	  viewedByOthers: true
	};
	const demoMessage3 = {
	  id: -1,
	  chatId: demoChat.chatId,
	  authorId: demoUser.id,
	  text: main_core.Loc.getMessage('IM_CONTENT_SETTINGS_DEMO_CHAT_MESSAGE_3'),
	  viewedByOthers: true
	};
	const DemoManager = {
	  initModels() {
	    im_v2_application_core.Core.getStore().dispatch('chats/set', demoChat);
	    const userManager = new im_v2_lib_user.UserManager();
	    userManager.addUsersToModel([demoUser]);
	    const messages = [demoMessage1, demoMessage2, demoMessage3];
	    im_v2_application_core.Core.getStore().dispatch('messages/setChatCollection', {
	      messages
	    });
	  }
	};

	// @vue/component
	const AppearanceSection = {
	  name: 'AppearanceSection',
	  components: {
	    ChatDialog: im_v2_component_dialog_chat.ChatDialog,
	    ChatBackground,
	    ChatAlignment
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    containerClasses() {
	      const alignment = this.$store.getters['application/settings/get'](im_v2_const.Settings.appearance.alignment);
	      return [`--${alignment}-align`];
	    },
	    backgroundStyle() {
	      return im_v2_lib_theme.ThemeManager.getCurrentBackgroundStyle();
	    }
	  },
	  created() {
	    DemoManager.initModels();
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-settings-section-content__body">
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-content-chat__container bx-im-settings-appearance__demo-chat_container" :class="containerClasses" :style="backgroundStyle">
					<ChatDialog :dialogId="'settings'" />
				</div>
			</div>
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_APPEARANCE_BACKGROUND') }}
				</div>
				<ChatBackground />
			</div>
			<div class="bx-im-settings-section-content__separator"></div>
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_APPEARANCE_ALIGNMENT') }}
				</div>
				<ChatAlignment />
			</div>
		</div>
	`
	};

	// @vue/component
	const CheckboxOption = {
	  name: 'CheckboxOption',
	  props: {
	    value: {
	      type: Boolean,
	      required: true
	    },
	    text: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    disabled: {
	      type: Boolean,
	      required: false,
	      default: false
	    }
	  },
	  emits: ['change'],
	  data() {
	    return {};
	  },
	  methods: {
	    onInput(event) {
	      this.$emit('change', event.target.checked);
	    }
	  },
	  template: `
		<div class="bx-im-settings-checkbox__container bx-im-settings-section-content__block_option" :class="{ '--no-text': text === '' }">
			<label class="ui-ctl ui-ctl-checkbox">
				<input type="checkbox" :checked="value" :disabled="disabled" @input="onInput" class="ui-ctl-element">
				<div v-if="text" class="ui-ctl-label-text">{{ text }}</div>
			</label>
		</div>
	`
	};

	// @vue/component
	const RadioOption = {
	  name: 'RadioOption',
	  props: {
	    items: {
	      type: Array,
	      required: true
	    }
	  },
	  emits: ['change'],
	  data() {
	    return {
	      groupName: main_core.Text.getRandom()
	    };
	  },
	  computed: {
	    options() {
	      return this.items;
	    },
	    selectedValue() {
	      return this.options.find(option => {
	        return option.selected === true;
	      });
	    }
	  },
	  methods: {
	    onInput(option) {
	      this.$emit('change', option.value);
	    }
	  },
	  template: `
		<div class="bx-im-settings-radio__container bx-im-settings-section-content__block_option">
			<label v-for="option in options" class="ui-ctl ui-ctl-radio">
				<input type="radio" class="ui-ctl-element" :name="groupName" :checked="option.selected" @input="onInput(option)">
				<div class="bx-im-settings-radio__label ui-ctl-label-text">
					<!-- Text -->
					<div v-if="option.html" class="bx-im-settings-radio__label_title" v-html="option.text"></div>
					<div v-else class="bx-im-settings-radio__label_title">{{ option.text }}</div>
					<!-- Subtext -->
					<template v-if="option.subtext">
						<div v-if="option.html" class="bx-im-settings-radio__label_subtitle" v-html="option.subtext"></div>
						<div v-else class="bx-im-settings-radio__label_subtitle">{{ option.subtext }}</div>
					</template>
				</div>
			</label>
		</div>
	`
	};

	// @vue/component
	const SimpleNotificationList = {
	  name: 'SimpleNotificationList',
	  components: {
	    CheckboxOption
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    enableWeb() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.notification.enableWeb);
	    },
	    enableMail() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.notification.enableMail);
	    },
	    enablePush() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.notification.enablePush);
	    },
	    enableMailText() {
	      return this.loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_ENABLE_MAIL', {
	        '#MAIL#': this.userEmail
	      });
	    },
	    userEmail() {
	      const {
	        currentUser: {
	          email
	        }
	      } = im_v2_application_core.Core.getApplicationData();
	      return email;
	    }
	  },
	  methods: {
	    onEnableWebChange(newValue) {
	      this.getSettingsService().changeSetting(im_v2_const.Settings.notification.enableWeb, newValue);
	    },
	    onEnableMailChange(newValue) {
	      this.getSettingsService().changeSetting(im_v2_const.Settings.notification.enableMail, newValue);
	    },
	    onEnablePushChange(newValue) {
	      this.getSettingsService().changeSetting(im_v2_const.Settings.notification.enablePush, newValue);
	    },
	    getSettingsService() {
	      if (!this.settingsService) {
	        this.settingsService = new im_v2_provider_service.SettingsService();
	      }
	      return this.settingsService;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-settings-section-content__body">
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_BLOCK_SIMPLE_MODE_TITLE') }}
				</div>
				<CheckboxOption
					:value="enableWeb"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_ENABLE_WEB')"
					@change="onEnableWebChange"
				/>
				<CheckboxOption
					:value="enableMail"
					:text="enableMailText"
					@change="onEnableMailChange"
				/>
				<CheckboxOption
					:value="enablePush"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_ENABLE_PUSH_V1')"
					@change="onEnablePushChange"
				/>
			</div>
		</div>
	`
	};

	const NotificationService = {
	  async switchScheme(newScheme) {
	    void im_v2_application_core.Core.getStore().dispatch('application/settings/set', {
	      [im_v2_const.Settings.notification.mode]: newScheme
	    });
	    const newNotificationsSettings = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2SettingsNotifySwitchScheme, {
	      data: {
	        userId: im_v2_application_core.Core.getUserId(),
	        scheme: newScheme
	      }
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('NotificationService: switchScheme error', error);
	    });
	    return im_v2_application_core.Core.getStore().dispatch('application/settings/set', {
	      notifications: newNotificationsSettings
	    });
	  },
	  changeExpertOption(payload) {
	    const {
	      moduleId,
	      optionName,
	      type,
	      value
	    } = payload;
	    im_v2_application_core.Core.getStore().dispatch('application/settings/setNotificationOption', {
	      moduleId,
	      optionName,
	      type,
	      value
	    });
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2SettingsNotifyUpdate, {
	      data: {
	        userId: im_v2_application_core.Core.getUserId(),
	        moduleId,
	        name: optionName,
	        type,
	        value
	      }
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('NotificationService: changeExpertOption error', error);
	    });
	  }
	};

	// @vue/component
	const NotificationItem = {
	  name: 'NotificationItem',
	  components: {
	    CheckboxOption
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    blockId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    NotificationSettingsType: () => im_v2_const.NotificationSettingsType,
	    notification() {
	      return this.item;
	    },
	    disabledForWeb() {
	      return this.notification.disabled.includes(im_v2_const.NotificationSettingsType.web);
	    },
	    disabledForMail() {
	      return this.notification.disabled.includes(im_v2_const.NotificationSettingsType.mail);
	    },
	    disabledForPush() {
	      return this.notification.disabled.includes(im_v2_const.NotificationSettingsType.push);
	    }
	  },
	  methods: {
	    onItemChange(newValue, type) {
	      NotificationService.changeExpertOption({
	        moduleId: this.blockId,
	        optionName: this.notification.id,
	        type,
	        value: newValue
	      });
	    }
	  },
	  template: `
		<div class="bx-im-settings-expert-notifications-item__container">
			<div class="bx-im-settings-expert-notifications-item__title">
				{{ notification.label }}
			</div>
			<div class="bx-im-settings-expert-notifications-item__type --web">
				<CheckboxOption :value="notification.site" :disabled="disabledForWeb" @change="onItemChange($event, NotificationSettingsType.web)" />
			</div>
			<div class="bx-im-settings-expert-notifications-item__type --mail">
				<CheckboxOption :value="notification.mail" :disabled="disabledForMail" @change="onItemChange($event, NotificationSettingsType.mail)" />
			</div>
			<div class="bx-im-settings-expert-notifications-item__type --push">
				<CheckboxOption :value="notification.push" :disabled="disabledForPush" @change="onItemChange($event, NotificationSettingsType.push)" />
			</div>
		</div>
	`
	};

	// @vue/component
	const NotificationBlock = {
	  name: 'NotificationBlock',
	  components: {
	    NotificationItem
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    block() {
	      return this.item;
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-settings-section-content__body">
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ block.label }}
				</div>
				<div class="bx-im-settings-expert-notifications__header">
					<div class="bx-im-settings-expert-notifications__header_title"></div>
					<div class="bx-im-settings-expert-notifications__header_type">
						{{ loc('IM_CONTENT_SETTINGS_EXPERT_NOTIFICATIONS_TYPE_WEB') }}
					</div>
					<div class="bx-im-settings-expert-notifications__header_type">
						{{ loc('IM_CONTENT_SETTINGS_EXPERT_NOTIFICATIONS_TYPE_MAIL') }}
					</div>
					<div class="bx-im-settings-expert-notifications__header_type">
						{{ loc('IM_CONTENT_SETTINGS_EXPERT_NOTIFICATIONS_TYPE_PUSH') }}
					</div>
				</div>
				<NotificationItem v-for="item in block.items" :item="item" :blockId="block.id" :key="item.id" />
			</div>
		</div>
	`
	};

	// @vue/component
	const ExpertNotificationList = {
	  name: 'ExpertNotificationList',
	  components: {
	    NotificationBlock
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    notificationSettings() {
	      const settings = this.$store.getters['application/settings/get']('notifications');
	      return Object.values(settings);
	    }
	  },
	  template: `
		<NotificationBlock
			v-for="block in notificationSettings"
			:item="block"
			:key="block.id"
			class="bx-im-settings-expert-notifications__container"
		/>
	`
	};

	// @vue/component
	const NotificationSection = {
	  name: 'NotificationSection',
	  components: {
	    CheckboxOption,
	    RadioOption,
	    SimpleNotificationList,
	    ExpertNotificationList
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    enableSound() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.notification.enableSound);
	    },
	    enableAutoRead() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.notification.enableAutoRead);
	    },
	    notificationMode() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.notification.mode);
	    },
	    notificationModeOptions() {
	      return [{
	        value: im_v2_const.NotificationSettingsMode.simple,
	        text: this.loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_SIMPLE_MODE'),
	        selected: this.notificationMode === im_v2_const.NotificationSettingsMode.simple
	      }, {
	        value: im_v2_const.NotificationSettingsMode.expert,
	        text: this.loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_EXPERT_MODE'),
	        selected: this.notificationMode === im_v2_const.NotificationSettingsMode.expert
	      }];
	    },
	    notificationListComponent() {
	      return this.notificationMode === 'simple' ? SimpleNotificationList : ExpertNotificationList;
	    }
	  },
	  methods: {
	    onEnableSoundChange(newValue) {
	      this.getSettingsService().changeSetting(im_v2_const.Settings.notification.enableSound, newValue);
	    },
	    onEnableAutoReadChange(newValue) {
	      this.getSettingsService().changeSetting(im_v2_const.Settings.notification.enableAutoRead, newValue);
	    },
	    async onNotificationModeChange(newValue) {
	      const isChangingToSimple = newValue === im_v2_const.NotificationSettingsMode.simple;
	      if (isChangingToSimple) {
	        this.changeLocalNotificationMode(im_v2_const.NotificationSettingsMode.simple);
	        const confirmResult = await im_v2_lib_confirm.showNotificationsModeSwitchConfirm();
	        if (!confirmResult) {
	          this.changeLocalNotificationMode(im_v2_const.NotificationSettingsMode.expert);
	          return;
	        }
	      }
	      void NotificationService.switchScheme(newValue);
	    },
	    async changeLocalNotificationMode(newValue) {
	      this.$store.dispatch('application/settings/set', {
	        [im_v2_const.Settings.notification.mode]: newValue
	      });
	    },
	    getSettingsService() {
	      if (!this.settingsService) {
	        this.settingsService = new im_v2_provider_service.SettingsService();
	      }
	      return this.settingsService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-settings-section-content__body">
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_BLOCK_FOCUS') }}
				</div>
				<CheckboxOption
					:value="enableSound"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_ENABLE_SOUND')"
					@change="onEnableSoundChange"
				/>
				<CheckboxOption
					:value="enableAutoRead"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_AUTO_READ')"
					@change="onEnableAutoReadChange"
				/>
			</div>
		</div>
		<div class="bx-im-settings-section-content__body">
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_BLOCK_MODE') }}
				</div>
				<RadioOption :items="notificationModeOptions" @change="onNotificationModeChange" />
			</div>
		</div>
		<component :is="notificationListComponent" />
	`
	};

	// @vue/component
	const HotkeySection = {
	  name: 'HotkeySection',
	  components: {
	    RadioOption
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    sendByEnter() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.hotkey.sendByEnter);
	    },
	    sendCombinationItems() {
	      const ctrlKey = im_v2_lib_utils.Utils.platform.isMac() ? '&#8984;' : 'Ctrl';
	      const enterSubtext = this.loc('IM_CONTENT_SETTINGS_OPTION_HOTKEY_NEW_LINE', {
	        '#HOTKEY#': 'Shift + Enter'
	      });
	      const ctrlEnterSubtext = this.loc('IM_CONTENT_SETTINGS_OPTION_HOTKEY_NEW_LINE', {
	        '#HOTKEY#': 'Enter'
	      });
	      return [{
	        value: true,
	        text: 'Enter',
	        subtext: enterSubtext,
	        selected: this.sendByEnter === true
	      }, {
	        value: false,
	        text: `${ctrlKey} + Enter`,
	        subtext: ctrlEnterSubtext,
	        html: true,
	        selected: this.sendByEnter === false
	      }];
	    },
	    isMac() {
	      return im_v2_lib_utils.Utils.platform.isMac();
	    }
	  },
	  methods: {
	    onSendByEnterChange(newValue) {
	      this.getSettingsService().changeSetting(im_v2_const.Settings.hotkey.sendByEnter, newValue);
	    },
	    getSettingsService() {
	      if (!this.settingsService) {
	        this.settingsService = new im_v2_provider_service.SettingsService();
	      }
	      return this.settingsService;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-settings-section-content__body">
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_HOTKEY_SEND_COMBINATION') }}
				</div>
				<RadioOption
					:items="sendCombinationItems"
					@change="onSendByEnterChange"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const RecentSection = {
	  name: 'RecentSection',
	  components: {
	    CheckboxOption
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    showBirthday() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.recent.showBirthday);
	    },
	    showInvited() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.recent.showInvited);
	    },
	    showLastMessage() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.recent.showLastMessage);
	    }
	  },
	  methods: {
	    onShowBirthdayChange(newValue) {
	      this.getSettingsService().changeSetting(im_v2_const.Settings.recent.showBirthday, newValue);
	    },
	    onShowInvitedChange(newValue) {
	      this.getSettingsService().changeSetting(im_v2_const.Settings.recent.showInvited, newValue);
	    },
	    onShowLastMessageChange(newValue) {
	      this.getSettingsService().changeSetting(im_v2_const.Settings.recent.showLastMessage, newValue);
	    },
	    getSettingsService() {
	      if (!this.settingsService) {
	        this.settingsService = new im_v2_provider_service.SettingsService();
	      }
	      return this.settingsService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-settings-section-content__body">
			<div class="bx-im-settings-section-content__block">
				<CheckboxOption
					:value="showBirthday"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_RECENT_SHOW_BIRTHDAY')"
					@change="onShowBirthdayChange"
				/>
				<CheckboxOption
					:value="showInvited"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_RECENT_SHOW_INVITED')"
					@change="onShowInvitedChange"
				/>
				<CheckboxOption
					:value="showLastMessage"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_RECENT_SHOW_TEXT')"
					@change="onShowLastMessageChange"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const DesktopSection = {
	  name: 'DesktopSection',
	  components: {
	    CheckboxOption
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    twoWindowMode() {
	      return im_v2_lib_desktopApi.DesktopApi.isTwoWindowMode();
	    },
	    autoStartDesktop() {
	      return im_v2_lib_desktopApi.DesktopApi.getAutostartStatus();
	    },
	    openPortalLinkInDesktop() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.desktop.enableRedirect);
	    },
	    openPortalLinkInDesktopPhrase() {
	      if (!im_v2_lib_desktopApi.DesktopApi.isFeatureSupported(im_v2_lib_desktopApi.DesktopFeature.openPage.id)) {
	        return this.loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_ALWAYS_OPEN_CHAT');
	      }
	      return this.loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_ALWAYS_OPEN_PORTAL_LINK_V2');
	    },
	    openLinksInSlider() {
	      const sliderBindingStatus = im_v2_lib_desktopApi.DesktopApi.getCustomSetting(im_v2_lib_desktopApi.DesktopSettingsKey.sliderBindingsStatus, '1');
	      return sliderBindingStatus === '1';
	    },
	    sendTelemetry() {
	      return im_v2_lib_desktopApi.DesktopApi.getTelemetryStatus();
	    }
	  },
	  methods: {
	    async onTwoWindowModeChange(newValue) {
	      im_v2_lib_desktopApi.DesktopApi.setTwoWindowMode(newValue);
	      if (!im_v2_lib_desktopApi.DesktopApi.isFeatureSupported(im_v2_lib_desktopApi.DesktopFeature.restart.id)) {
	        void im_v2_lib_confirm.showDesktopConfirm();
	        return;
	      }
	      const userChoice = await im_v2_lib_confirm.showDesktopRestartConfirm();
	      if (userChoice === true) {
	        im_v2_lib_desktopApi.DesktopApi.restart();
	      }
	    },
	    onAutoStartDesktopChange(newValue) {
	      im_v2_lib_desktopApi.DesktopApi.setAutostartStatus(newValue);
	    },
	    onOpenPortalLinkInDesktopChange(newValue) {
	      this.getSettingsService().changeSetting(im_v2_const.Settings.desktop.enableRedirect, newValue);
	    },
	    onOpenLinksInSliderChange(newValue) {
	      this.setSliderBindingStatus(newValue);
	      im_v2_lib_desktopApi.DesktopApi.setCustomSetting(im_v2_lib_desktopApi.DesktopSettingsKey.sliderBindingsStatus, newValue ? '1' : '0');
	    },
	    onSendTelemetryChange(newValue) {
	      im_v2_lib_desktopApi.DesktopApi.setTelemetryStatus(newValue);
	    },
	    setSliderBindingStatus(flag) {
	      if (flag === true) {
	        BX.SidePanel.Instance.enableAnchorBinding();
	        return;
	      }
	      BX.SidePanel.Instance.disableAnchorBinding();
	    },
	    getSettingsService() {
	      if (!this.settingsService) {
	        this.settingsService = new im_v2_provider_service.SettingsService();
	      }
	      return this.settingsService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-settings-section-content__body">
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_BLOCK_STARTUP') }}
				</div>
				<CheckboxOption
					:value="twoWindowMode"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_TWO_WINDOW_MODE_V2')"
					@change="onTwoWindowModeChange"
				/>
				<CheckboxOption
					:value="autoStartDesktop"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_AUTO_START')"
					@change="onAutoStartDesktopChange"
				/>
			</div>
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_BLOCK_LINKS') }}
				</div>
				<CheckboxOption
					:value="openPortalLinkInDesktop"
					:text="openPortalLinkInDesktopPhrase"
					@change="onOpenPortalLinkInDesktopChange"
				/>
				<CheckboxOption
					:value="openLinksInSlider"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_OPEN_LINKS_IN_SLIDER_V2')"
					@change="onOpenLinksInSliderChange"
				/>
			</div>
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_BLOCK_ADDITIONAL') }}
				</div>
				<CheckboxOption
					:value="sendTelemetry"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_SEND_TELEMETRY')"
					@change="onSendTelemetryChange"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const SectionContent = {
	  name: 'SectionContent',
	  components: {
	    AppearanceSection,
	    NotificationSection,
	    HotkeySection,
	    RecentSection,
	    DesktopSection
	  },
	  props: {
	    activeSection: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    sectionComponentName() {
	      const uppercaseSection = this.activeSection[0].toUpperCase() + this.activeSection.slice(1);
	      const COMPONENT_POSTFIX = 'Section';
	      return `${uppercaseSection}${COMPONENT_POSTFIX}`;
	    },
	    sectionName() {
	      return SectionMetaData[this.activeSection].name;
	    },
	    sectionIconClass() {
	      return SectionMetaData[this.activeSection].icon;
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-settings-section-content__container">
			<div class="bx-im-settings-section-content__header">
				<div class="bx-im-settings-section-content__header_icon">
					<i :class="sectionIconClass"></i>
				</div>
				<div class="bx-im-settings-section-content__header_title">{{ sectionName }}</div>
			</div>
			<div class="bx-im-settings-section-content__background">
				<component :is="sectionComponentName" />
			</div>
		</div>
	`
	};

	// @vue/component
	const SettingsContent = {
	  name: 'SettingsContent',
	  components: {
	    SectionList,
	    SectionContent
	  },
	  props: {
	    entityId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      activeSection: ''
	    };
	  },
	  computed: {
	    sections() {
	      return Object.keys(im_v2_const.Settings);
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('Content: Openlines created');
	    this.setInitialSection();
	  },
	  methods: {
	    setInitialSection() {
	      if (this.entityId && im_v2_const.SettingsSection[this.entityId]) {
	        this.activeSection = this.entityId;
	        return;
	      }
	      this.activeSection = this.sections[0];
	    },
	    onSectionClick(sectionId) {
	      this.activeSection = sectionId;
	    }
	  },
	  template: `
		<div class="bx-im-content-settings__container">
			<SectionList :activeSection="activeSection" @sectionClick="onSectionClick" />
			<SectionContent :activeSection="activeSection" />
		</div>
	`
	};

	exports.SettingsContent = SettingsContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.UI.Feedback,BX.Messenger.v2.Component.Dialog,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Service));
//# sourceMappingURL=settings-content.bundle.js.map
