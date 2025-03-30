/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_lib_permission,im_v2_lib_createChat,im_v2_lib_helpdesk,socialnetwork_collab_accessRights,main_core_events,main_popup,im_v2_lib_confirm,ui_notification,main_core,im_v2_application_core,im_public,im_v2_lib_analytics,im_v2_provider_service,im_v2_component_elements,im_v2_const,im_v2_component_content_chatForms_elements) {
	'use strict';

	// @vue/component
	const GroupChatCreation = {
	  name: 'GroupChatCreation',
	  components: {
	    TitleInput: im_v2_component_content_chatForms_elements.TitleInput,
	    ChatAvatar: im_v2_component_content_chatForms_elements.ChatAvatar,
	    ChatMembersSelector: im_v2_component_content_chatForms_elements.ChatMembersSelector,
	    SettingsSection: im_v2_component_content_chatForms_elements.SettingsSection,
	    RightsSection: im_v2_component_content_chatForms_elements.RightsSection,
	    AppearanceSection: im_v2_component_content_chatForms_elements.AppearanceSection,
	    ButtonPanel: im_v2_component_content_chatForms_elements.ButtonPanel,
	    CreateChatHeading: im_v2_component_content_chatForms_elements.CreateChatHeading
	  },
	  data() {
	    return {
	      isCreating: false,
	      avatarFile: null,
	      chatTitle: '',
	      chatMembers: [['user', im_v2_application_core.Core.getUserId()]],
	      settings: {
	        isAvailableInSearch: false,
	        description: ''
	      },
	      rights: {
	        ownerId: 0,
	        managerIds: [],
	        manageUsersAdd: '',
	        manageUsersDelete: '',
	        manageUi: '',
	        manageMessages: ''
	      }
	    };
	  },
	  watch: {
	    chatTitle(newValue) {
	      im_v2_lib_createChat.CreateChatManager.getInstance().setChatTitle(newValue);
	    }
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.layout.onLayoutChange, this.onLayoutChange);
	    this.rights.ownerId = im_v2_application_core.Core.getUserId();
	    this.initDefaultRolesForRights();
	    this.restoreFields();
	    im_v2_lib_createChat.CreateChatManager.getInstance().setChatType(im_v2_const.ChatType.chat);
	    im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(true);
	    im_v2_lib_createChat.CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
	  },
	  beforeUnmount() {
	    if (this.exitByCancel || this.exitByChatTypeSwitch || this.exitByCreation) {
	      return;
	    }
	    this.saveFields();
	  },
	  methods: {
	    onMembersChange(currentTags) {
	      this.chatMembers = currentTags;
	    },
	    onOwnerChange(ownerId) {
	      this.rights.ownerId = ownerId;
	    },
	    onManagersChange(managerIds) {
	      this.rights.managerIds = managerIds;
	    },
	    onChatTypeChange(isAvailableInSearch) {
	      this.settings.isAvailableInSearch = isAvailableInSearch;
	    },
	    onDescriptionChange(description) {
	      this.settings.description = description;
	    },
	    onManageUsersAddChange(newValue) {
	      this.rights.manageUsersAdd = newValue;
	    },
	    onManageUsersDeleteChange(newValue) {
	      this.rights.manageUsersDelete = newValue;
	    },
	    onManageUiChange(newValue) {
	      this.rights.manageUi = newValue;
	    },
	    onManageMessagesChange(newValue) {
	      this.rights.manageMessages = newValue;
	    },
	    async onCreateClick() {
	      this.isCreating = true;
	      const {
	        newDialogId
	      } = await this.getChatService().createChat({
	        title: this.chatTitle,
	        avatar: this.avatarFile,
	        memberEntities: this.chatMembers,
	        ownerId: this.rights.ownerId,
	        managers: this.rights.managerIds,
	        isAvailableInSearch: this.settings.isAvailableInSearch,
	        description: this.settings.description,
	        manageUsersAdd: this.rights.manageUsersAdd,
	        manageUsersDelete: this.rights.manageUsersDelete,
	        manageUi: this.rights.manageUi,
	        manageMessages: this.rights.manageMessages
	      }).catch(() => {
	        this.isCreating = false;
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_CREATE_CHAT_ERROR')
	        });
	      });
	      this.isCreating = false;
	      this.exitByCreation = true;
	      im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(false);
	      void im_public.Messenger.openChat(newDialogId);
	    },
	    onCancelClick() {
	      this.exitByCancel = true;
	      im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(false);
	      im_public.Messenger.openChat();
	    },
	    onAvatarChange(newAvatarFile) {
	      this.avatarFile = newAvatarFile;
	      im_v2_lib_createChat.CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
	    },
	    onScroll() {
	      var _MenuManager$getMenuB, _MenuManager$getMenuB2, _MenuManager$getMenuB3, _MenuManager$getMenuB4;
	      (_MenuManager$getMenuB = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUsersAddMenu)) == null ? void 0 : _MenuManager$getMenuB.close();
	      (_MenuManager$getMenuB2 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUsersDeleteMenu)) == null ? void 0 : _MenuManager$getMenuB2.close();
	      (_MenuManager$getMenuB3 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUiMenu)) == null ? void 0 : _MenuManager$getMenuB3.close();
	      (_MenuManager$getMenuB4 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageMessagesMenu)) == null ? void 0 : _MenuManager$getMenuB4.close();
	    },
	    onLayoutChange(event) {
	      const {
	        to
	      } = event.getData();
	      if (to.name === im_v2_const.Layout.createChat.name && to.entityId !== im_v2_const.ChatType.chat) {
	        this.exitByChatTypeSwitch = true;
	      }
	    },
	    saveFields() {
	      im_v2_lib_createChat.CreateChatManager.getInstance().saveFields({
	        chatTitle: this.chatTitle,
	        avatarFile: this.avatarFile,
	        chatMembers: this.chatMembers,
	        settings: this.settings,
	        rights: this.rights
	      });
	    },
	    restoreFields() {
	      const savedFields = im_v2_lib_createChat.CreateChatManager.getInstance().getFields();
	      if (!savedFields) {
	        return;
	      }
	      const {
	        chatTitle,
	        avatarFile,
	        chatMembers,
	        settings,
	        rights
	      } = savedFields;
	      this.chatTitle = chatTitle;
	      this.avatarFile = avatarFile;
	      this.chatMembers = chatMembers;
	      this.settings = settings;
	      this.rights = rights;
	    },
	    initDefaultRolesForRights() {
	      const {
	        manageUsersAdd,
	        manageUsersDelete,
	        manageUi,
	        manageMessages
	      } = im_v2_lib_permission.PermissionManager.getInstance().getDefaultRolesForActionGroups();
	      this.rights.manageUsersAdd = manageUsersAdd;
	      this.rights.manageUsersDelete = manageUsersDelete;
	      this.rights.manageUi = manageUi;
	      this.rights.manageMessages = manageMessages;
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat-forms__content --chat" @scroll="onScroll">
			<div class="bx-im-content-chat-forms__header">
				<ChatAvatar :avatarFile="avatarFile" :chatTitle="chatTitle" @avatarChange="onAvatarChange" />
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_CHAT_TITLE_PLACEHOLDER')" />
			</div>
			<CreateChatHeading :text="loc('IM_CREATE_CHAT_MEMBERS_TITLE')" />
			<div class="bx-im-content-chat-forms__members_container">
				<ChatMembersSelector :chatMembers="chatMembers" @membersChange="onMembersChange" />
			</div>
			<SettingsSection
				:isAvailableInSearch="settings.isAvailableInSearch"
				:description="settings.description"
				@chatTypeChange="onChatTypeChange"
				@descriptionChange="onDescriptionChange"
			/>
			<RightsSection
				:ownerId="rights.ownerId"
				:managerIds="rights.managerIds"
				:manageUsersAdd="rights.manageUsersAdd"
				:manageUsersDelete="rights.manageUsersDelete"
				:manageUi="rights.manageUi"
				:manageMessages="rights.manageMessages"
				@ownerChange="onOwnerChange"
				@managersChange="onManagersChange"
				@manageUsersAddChange="onManageUsersAddChange"
				@manageUsersDeleteChange="onManageUsersDeleteChange"
				@manageUiChange="onManageUiChange"
				@manageMessagesChange="onManageMessagesChange"
			/> 
		</div>
		<ButtonPanel
			:isCreating="isCreating"
			:createButtonTitle="loc('IM_CREATE_CHAT_CONFIRM')"
			@create="onCreateClick"
			@cancel="onCancelClick"
		/>
	`
	};

	// @vue/component
	const ConferenceCreation = {
	  name: 'ConferenceCreation',
	  components: {
	    TitleInput: im_v2_component_content_chatForms_elements.TitleInput,
	    ChatAvatar: im_v2_component_content_chatForms_elements.ChatAvatar,
	    ChatMembersSelector: im_v2_component_content_chatForms_elements.ChatMembersSelector,
	    SettingsSection: im_v2_component_content_chatForms_elements.SettingsSection,
	    RightsSection: im_v2_component_content_chatForms_elements.RightsSection,
	    AppearanceSection: im_v2_component_content_chatForms_elements.AppearanceSection,
	    ConferenceSection: im_v2_component_content_chatForms_elements.ConferenceSection,
	    ButtonPanel: im_v2_component_content_chatForms_elements.ButtonPanel,
	    CreateChatHeading: im_v2_component_content_chatForms_elements.CreateChatHeading
	  },
	  data() {
	    return {
	      isCreating: false,
	      avatarFile: null,
	      chatTitle: '',
	      chatMembers: [['user', im_v2_application_core.Core.getUserId()]],
	      conference: {
	        passwordNeeded: false,
	        password: ''
	      },
	      settings: {
	        description: ''
	      },
	      rights: {
	        ownerId: 0,
	        managerIds: [],
	        manageUsersAdd: '',
	        manageUsersDelete: '',
	        manageUi: '',
	        manageMessages: ''
	      }
	    };
	  },
	  computed: {
	    EmptyAvatarType: () => im_v2_component_elements.EmptyAvatarType
	  },
	  watch: {
	    chatTitle(newValue) {
	      im_v2_lib_createChat.CreateChatManager.getInstance().setChatTitle(newValue);
	    }
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.layout.onLayoutChange, this.onLayoutChange);
	    this.rights.ownerId = im_v2_application_core.Core.getUserId();
	    this.initDefaultRolesForRights();
	    this.restoreFields();
	    im_v2_lib_createChat.CreateChatManager.getInstance().setChatType(im_v2_const.ChatType.videoconf);
	    im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(true);
	    im_v2_lib_createChat.CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
	  },
	  beforeUnmount() {
	    if (this.exitByCancel || this.exitByChatTypeSwitch) {
	      return;
	    }
	    this.saveFields();
	  },
	  methods: {
	    onMembersChange(currentTags) {
	      this.chatMembers = currentTags;
	    },
	    onOwnerChange(ownerId) {
	      this.rights.ownerId = ownerId;
	    },
	    onManagersChange(managerIds) {
	      this.rights.managerIds = managerIds;
	    },
	    onDescriptionChange(description) {
	      this.settings.description = description;
	    },
	    onManageUsersAddChange(newValue) {
	      this.rights.manageUsersAdd = newValue;
	    },
	    onManageUsersDeleteChange(newValue) {
	      this.rights.manageUsersDelete = newValue;
	    },
	    onManageUiChange(newValue) {
	      this.rights.manageUi = newValue;
	    },
	    onManageMessagesChange(newValue) {
	      this.rights.manageMessages = newValue;
	    },
	    onAvatarChange(newAvatarFile) {
	      this.avatarFile = newAvatarFile;
	      im_v2_lib_createChat.CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
	    },
	    onPasswordNeededChange(passwordNeeded) {
	      this.conference.passwordNeeded = passwordNeeded;
	    },
	    onPasswordChange(newPassword) {
	      this.conference.password = newPassword;
	    },
	    async onCreateClick() {
	      if (!this.checkPassword()) {
	        this.showPasswordError();
	        return;
	      }
	      this.isCreating = true;
	      const {
	        newDialogId
	      } = await this.getChatService().createChat({
	        entityType: im_v2_const.ChatType.videoconf,
	        title: this.chatTitle,
	        avatar: this.avatarFile,
	        memberEntities: this.chatMembers,
	        ownerId: this.rights.ownerId,
	        managers: this.rights.managerIds,
	        description: this.settings.description,
	        manageUsersAdd: this.rights.manageUsersAdd,
	        manageUsersDelete: this.rights.manageUsersDelete,
	        manageUi: this.rights.manageUi,
	        manageMessages: this.rights.manageMessages,
	        conferencePassword: this.conference.passwordNeeded ? this.conference.password : ''
	      }).catch(() => {
	        this.isCreating = false;
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_CREATE_CHAT_ERROR')
	        });
	      });
	      this.isCreating = false;
	      im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(false);
	      void im_public.Messenger.openChat(newDialogId);
	    },
	    onCancelClick() {
	      this.exitByCancel = true;
	      im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(false);
	      im_public.Messenger.openChat();
	    },
	    onScroll() {
	      var _MenuManager$getMenuB, _MenuManager$getMenuB2, _MenuManager$getMenuB3, _MenuManager$getMenuB4;
	      (_MenuManager$getMenuB = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUsersAddMenu)) == null ? void 0 : _MenuManager$getMenuB.close();
	      (_MenuManager$getMenuB2 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUsersDeleteMenu)) == null ? void 0 : _MenuManager$getMenuB2.close();
	      (_MenuManager$getMenuB3 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUiMenu)) == null ? void 0 : _MenuManager$getMenuB3.close();
	      (_MenuManager$getMenuB4 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageMessagesMenu)) == null ? void 0 : _MenuManager$getMenuB4.close();
	    },
	    onLayoutChange(event) {
	      const {
	        to
	      } = event.getData();
	      if (to.name === im_v2_const.Layout.createChat.name && to.entityId !== im_v2_const.ChatType.videoconf) {
	        this.exitByChatTypeSwitch = true;
	      }
	    },
	    saveFields() {
	      im_v2_lib_createChat.CreateChatManager.getInstance().saveFields({
	        chatTitle: this.chatTitle,
	        avatarFile: this.avatarFile,
	        chatMembers: this.chatMembers,
	        settings: this.settings,
	        rights: this.rights,
	        conference: this.conference
	      });
	    },
	    restoreFields() {
	      const savedFields = im_v2_lib_createChat.CreateChatManager.getInstance().getFields();
	      if (!savedFields) {
	        return;
	      }
	      const {
	        chatTitle,
	        avatarFile,
	        chatMembers,
	        conference,
	        settings,
	        rights
	      } = savedFields;
	      this.chatTitle = chatTitle;
	      this.avatarFile = avatarFile;
	      this.chatMembers = chatMembers;
	      this.conference = conference;
	      this.settings = settings;
	      this.rights = rights;
	    },
	    initDefaultRolesForRights() {
	      const {
	        manageUsersAdd,
	        manageUsersDelete,
	        manageUi,
	        manageMessages
	      } = im_v2_lib_permission.PermissionManager.getInstance().getDefaultRolesForActionGroups();
	      this.rights.manageUsersAdd = manageUsersAdd;
	      this.rights.manageUsersDelete = manageUsersDelete;
	      this.rights.manageUi = manageUi;
	      this.rights.manageMessages = manageMessages;
	    },
	    checkPassword() {
	      const PASSWORD_MIN_LENGTH = 3;
	      if (!this.conference.passwordNeeded) {
	        return true;
	      }
	      const password = this.conference.password.trim();
	      return password !== '' && password.length >= PASSWORD_MIN_LENGTH;
	    },
	    showPasswordError() {
	      BX.UI.Notification.Center.notify({
	        content: this.loc('IM_CREATE_CHAT_CONFERENCE_PASSWORD_ERROR')
	      });
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat-forms__content --conference" @scroll="onScroll">
			<div class="bx-im-content-chat-forms__header">
				<ChatAvatar 
					:avatarFile="avatarFile" 
					:chatTitle="chatTitle"
					:type="EmptyAvatarType.squared"	
					@avatarChange="onAvatarChange" 
				/>
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_CONFERENCE_TITLE_PLACEHOLDER')" />
			</div>
			<CreateChatHeading :text="loc('IM_CREATE_CHAT_MEMBERS_TITLE')" />
			<div class="bx-im-content-chat-forms__members_container">
				<ChatMembersSelector :chatMembers="chatMembers" @membersChange="onMembersChange" />
			</div>
			<ConferenceSection
				:passwordNeeded="conference.passwordNeeded"
				:password="conference.password"
				@passwordNeededChange="onPasswordNeededChange"
				@passwordChange="onPasswordChange"
			/>
			<SettingsSection
				:withSearchOption="false"
				:description="settings.description"
				@descriptionChange="onDescriptionChange"
			/>
			<RightsSection
				:ownerId="rights.ownerId"
				:managerIds="rights.managerIds"
				:manageUsersAdd="rights.manageUsersAdd"
				:manageUsersDelete="rights.manageUsersDelete"
				:manageUi="rights.manageUi"
				:manageMessages="rights.manageMessages"
				@ownerChange="onOwnerChange"
				@managersChange="onManagersChange"
				@manageUsersAddChange="onManageUsersAddChange"
				@manageUsersDeleteChange="onManageUsersDeleteChange"
				@manageUiChange="onManageUiChange"
				@manageMessagesChange="onManageMessagesChange"
			/>
		</div>
		<ButtonPanel
			:isCreating="isCreating"
			:createButtonTitle="loc('IM_CREATE_CONFERENCE_CONFIRM')"
			@create="onCreateClick"
			@cancel="onCancelClick"
		/>
	`
	};

	// @vue/component
	const ChannelCreation = {
	  name: 'ChannelCreation',
	  components: {
	    TitleInput: im_v2_component_content_chatForms_elements.TitleInput,
	    ChatAvatar: im_v2_component_content_chatForms_elements.ChatAvatar,
	    ChatMembersSelector: im_v2_component_content_chatForms_elements.ChatMembersSelector,
	    SettingsSection: im_v2_component_content_chatForms_elements.SettingsSection,
	    RightsSection: im_v2_component_content_chatForms_elements.RightsSection,
	    AppearanceSection: im_v2_component_content_chatForms_elements.AppearanceSection,
	    PrivacySection: im_v2_component_content_chatForms_elements.PrivacySection,
	    ButtonPanel: im_v2_component_content_chatForms_elements.ButtonPanel,
	    TextareaInput: im_v2_component_content_chatForms_elements.TextareaInput,
	    CreateChatHeading: im_v2_component_content_chatForms_elements.CreateChatHeading
	  },
	  data() {
	    return {
	      isCreating: false,
	      avatarFile: null,
	      chatTitle: '',
	      chatMembers: [['user', im_v2_application_core.Core.getUserId()]],
	      settings: {
	        isAvailableInSearch: false,
	        description: ''
	      },
	      rights: {
	        ownerId: 0,
	        managerIds: [],
	        manageUsersAdd: '',
	        manageUsersDelete: '',
	        manageUi: '',
	        manageMessages: ''
	      }
	    };
	  },
	  computed: {
	    ChatType: () => im_v2_const.ChatType,
	    EmptyAvatarType: () => im_v2_component_elements.EmptyAvatarType
	  },
	  watch: {
	    chatTitle(newValue) {
	      im_v2_lib_createChat.CreateChatManager.getInstance().setChatTitle(newValue);
	    }
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.layout.onLayoutChange, this.onLayoutChange);
	    this.rights.ownerId = im_v2_application_core.Core.getUserId();
	    this.initDefaultRolesForRights();
	    this.restoreFields();
	    im_v2_lib_createChat.CreateChatManager.getInstance().setChatType(im_v2_const.ChatType.channel);
	    im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(true);
	    im_v2_lib_createChat.CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.layout.onLayoutChange, this.onLayoutChange);
	    if (this.exitByCancel || this.exitByChatTypeSwitch || this.exitByCreation) {
	      return;
	    }
	    this.saveFields();
	  },
	  methods: {
	    onMembersChange(currentTags) {
	      this.chatMembers = currentTags;
	    },
	    onOwnerChange(ownerId) {
	      this.rights.ownerId = ownerId;
	    },
	    onManagersChange(managerIds) {
	      this.rights.managerIds = managerIds;
	    },
	    onChatTypeChange(isAvailableInSearch) {
	      this.settings.isAvailableInSearch = isAvailableInSearch;
	    },
	    onDescriptionChange(description) {
	      this.settings.description = description;
	    },
	    onManageUsersAddChange(newValue) {
	      this.rights.manageUsersAdd = newValue;
	    },
	    onManageUsersDeleteChange(newValue) {
	      this.rights.manageUsersDelete = newValue;
	    },
	    onManageUiChange(newValue) {
	      this.rights.manageUi = newValue;
	    },
	    onManageMessagesChange(newValue) {
	      this.rights.manageMessages = newValue;
	    },
	    async onCreateClick() {
	      this.isCreating = true;
	      const {
	        newDialogId
	      } = await this.getChatService().createChat({
	        type: im_v2_const.ChatType.channel,
	        title: this.chatTitle,
	        avatar: this.avatarFile,
	        memberEntities: this.chatMembers,
	        ownerId: this.rights.ownerId,
	        managers: this.rights.managerIds,
	        isAvailableInSearch: this.settings.isAvailableInSearch,
	        description: this.settings.description,
	        manageUsersAdd: this.rights.manageUsersAdd,
	        manageUsersDelete: this.rights.manageUsersDelete,
	        manageUi: this.rights.manageUi,
	        manageMessages: this.rights.manageMessages
	      }).catch(() => {
	        this.isCreating = false;
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_CREATE_CHAT_ERROR')
	        });
	      });
	      this.isCreating = false;
	      this.exitByCreation = true;
	      im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(false);
	      void im_public.Messenger.openChat(newDialogId);
	    },
	    onCancelClick() {
	      this.exitByCancel = true;
	      im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(false);
	      im_public.Messenger.openChat();
	    },
	    onAvatarChange(newAvatarFile) {
	      this.avatarFile = newAvatarFile;
	      im_v2_lib_createChat.CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
	    },
	    onScroll() {
	      var _MenuManager$getMenuB, _MenuManager$getMenuB2, _MenuManager$getMenuB3, _MenuManager$getMenuB4;
	      (_MenuManager$getMenuB = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUsersAddMenu)) == null ? void 0 : _MenuManager$getMenuB.close();
	      (_MenuManager$getMenuB2 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUsersDeleteMenu)) == null ? void 0 : _MenuManager$getMenuB2.close();
	      (_MenuManager$getMenuB3 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUiMenu)) == null ? void 0 : _MenuManager$getMenuB3.close();
	      (_MenuManager$getMenuB4 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageMessagesMenu)) == null ? void 0 : _MenuManager$getMenuB4.close();
	    },
	    onLayoutChange(event) {
	      const {
	        to
	      } = event.getData();
	      if (to.name === im_v2_const.Layout.createChat.name && to.entityId !== im_v2_const.ChatType.channel) {
	        this.exitByChatTypeSwitch = true;
	      }
	    },
	    saveFields() {
	      im_v2_lib_createChat.CreateChatManager.getInstance().saveFields({
	        chatTitle: this.chatTitle,
	        avatarFile: this.avatarFile,
	        chatMembers: this.chatMembers,
	        settings: this.settings,
	        rights: this.rights
	      });
	    },
	    restoreFields() {
	      const savedFields = im_v2_lib_createChat.CreateChatManager.getInstance().getFields();
	      if (!savedFields) {
	        return;
	      }
	      const {
	        chatTitle,
	        avatarFile,
	        chatMembers,
	        settings,
	        rights
	      } = savedFields;
	      this.chatTitle = chatTitle;
	      this.avatarFile = avatarFile;
	      this.chatMembers = chatMembers;
	      this.settings = settings;
	      this.rights = rights;
	    },
	    initDefaultRolesForRights() {
	      const {
	        manageUsersAdd,
	        manageUsersDelete,
	        manageUi,
	        manageMessages
	      } = im_v2_lib_permission.PermissionManager.getInstance().getDefaultRolesForActionGroups(im_v2_const.ChatType.channel);
	      this.rights.manageUsersAdd = manageUsersAdd;
	      this.rights.manageUsersDelete = manageUsersDelete;
	      this.rights.manageUi = manageUi;
	      this.rights.manageMessages = manageMessages;
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat-forms__content --channel" @scroll="onScroll">
			<div class="bx-im-content-chat-forms__header">
				<ChatAvatar 
					:avatarFile="avatarFile" 
					:chatTitle="chatTitle" 
					:type="EmptyAvatarType.squared"
					@avatarChange="onAvatarChange" 
				/>
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_CHANNEL_TITLE_PLACEHOLDER_V2')" />
			</div>
			<CreateChatHeading :text="loc('IM_CREATE_CHANNEL_DESCRIPTION_TITLE')" />
			<div class="bx-im-content-chat-forms__description_container">
				<TextareaInput
					:value="settings.description"
					:placeholder="loc('IM_CREATE_CHANNEL_DESCRIPTION_PLACEHOLDER_V3')"
					:border="false"
					@input="onDescriptionChange"
				/>
			</div>
			<PrivacySection
				:isAvailableInSearch="settings.isAvailableInSearch"
				@chatTypeChange="onChatTypeChange"
			/>
			<CreateChatHeading
				:text="loc('IM_CREATE_CHANNEL_MEMBERS_TITLE')"
				:hintText="loc('IM_CREATE_CHANNEL_MEMBERS_HINT')"
			/>
			<div class="bx-im-content-chat-forms__members_container">
				<ChatMembersSelector :chatMembers="chatMembers" @membersChange="onMembersChange" />
			</div>
			<RightsSection
				:chatType="ChatType.channel"
				:ownerId="rights.ownerId"
				:managerIds="rights.managerIds"
				:manageUsersAdd="rights.manageUsersAdd"
				:manageUsersDelete="rights.manageUsersDelete"
				:manageUi="rights.manageUi"
				:manageMessages="rights.manageMessages"
				@ownerChange="onOwnerChange"
				@managersChange="onManagersChange"
				@manageUsersAddChange="onManageUsersAddChange"
				@manageUsersDeleteChange="onManageUsersDeleteChange"
				@manageUiChange="onManageUiChange"
				@manageMessagesChange="onManageMessagesChange"
			/>
		</div>
		<ButtonPanel
			:isCreating="isCreating"
			:createButtonTitle="loc('IM_CREATE_CHANNEL_CONFIRM')"
			@create="onCreateClick"
			@cancel="onCancelClick"
		/>
	`
	};

	const LINK_CSS_CLASS = 'bx-im-create-collab-description-banner__link';
	const TITLE_LINK_MODIFIER = '--title';
	const INVITE_LINK_MODIFIER = '--invite';

	// @vue/component
	const DescriptionBanner = {
	  name: 'DescriptionBanner',
	  computed: {
	    preparedTitle() {
	      return main_core.Loc.getMessage('IM_CREATE_COLLAB_BANNER_TITLE', {
	        '[learn-more]': `<span class="${LINK_CSS_CLASS} ${TITLE_LINK_MODIFIER} --solid">`,
	        '[/learn-more]': '</span>'
	      });
	    },
	    preparedInviteText() {
	      return main_core.Loc.getMessage('IM_CREATE_COLLAB_BANNER_TEXT_2', {
	        '[learn-more]': `<span class="${LINK_CSS_CLASS} ${INVITE_LINK_MODIFIER} --dashed">`,
	        '[/learn-more]': '</span>'
	      });
	    }
	  },
	  methods: {
	    onTitleClick(event) {
	      if (!main_core.Dom.hasClass(event.target, TITLE_LINK_MODIFIER)) {
	        return;
	      }
	      const ARTICLE_CODE = '22706764';
	      im_v2_lib_helpdesk.openHelpdeskArticle(ARTICLE_CODE);
	    },
	    onInviteClick(event) {
	      if (!main_core.Dom.hasClass(event.target, INVITE_LINK_MODIFIER)) {
	        return;
	      }
	      const ARTICLE_CODE = '22706836';
	      im_v2_lib_helpdesk.openHelpdeskArticle(ARTICLE_CODE);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-create-collab-description-banner__container" ref="container">
			<div class="bx-im-create-collab-description-banner__icon"></div>
			<div class="bx-im-create-collab-description-banner__content">
				<div class="bx-im-create-collab-description-banner__title" v-html="preparedTitle" @click="onTitleClick"></div>
				<div class="bx-im-create-collab-description-banner__text">
					{{ loc('IM_CREATE_COLLAB_BANNER_TEXT_1') }}
				</div>
				<div class="bx-im-create-collab-description-banner__text" v-html="preparedInviteText" @click="onInviteClick"></div>
			</div>
		</div>
	`
	};

	// @vue/component
	const RightsSection = {
	  name: 'RightsSection',
	  components: {
	    CreateChatExternalSection: im_v2_component_content_chatForms_elements.CreateChatExternalSection
	  },
	  props: {
	    collabId: {
	      type: Number,
	      default: 0
	    }
	  },
	  emits: ['change'],
	  data() {
	    return {
	      formResult: null
	    };
	  },
	  methods: {
	    async onClick() {
	      const sliderParams = {};
	      if (this.collabId > 0) {
	        sliderParams.collabId = this.collabId;
	      }
	      if (this.formResult) {
	        sliderParams.formData = this.formResult;
	      }
	      this.form = await socialnetwork_collab_accessRights.AccessRights.openForm(sliderParams);
	      this.bindEvents();
	    },
	    bindEvents() {
	      main_core_events.EventEmitter.subscribe(this.form, 'save', this.onSave);
	      main_core_events.EventEmitter.subscribe(this.form, 'cancel', this.onCancel);
	    },
	    unbindEvents() {
	      main_core_events.EventEmitter.unsubscribe(this.form, 'save', this.onSave);
	      main_core_events.EventEmitter.unsubscribe(this.form, 'cancel', this.onCancel);
	    },
	    onSave(event) {
	      this.formResult = event.getData();
	      this.$emit('change', this.formResult);
	      this.unbindEvents();
	    },
	    onCancel() {
	      this.unbindEvents();
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<CreateChatExternalSection
			:title="loc('IM_CREATE_CHAT_RIGHTS_SECTION')"
			@click="onClick"
			name="rights"
		/>
	`
	};

	const CreateCollabErrorCode = {
	  emptyName: 'name',
	  duplicateName: 'ERROR_GROUP_NAME_EXISTS'
	};
	const NotificationTextByErrorCode = {
	  [CreateCollabErrorCode.emptyName]: main_core.Loc.getMessage('IM_CREATE_COLLAB_ERROR_EMPTY_NAME'),
	  [CreateCollabErrorCode.duplicateName]: main_core.Loc.getMessage('IM_CREATE_COLLAB_ERROR_DUPLICATE_NAME'),
	  default: main_core.Loc.getMessage('IM_CREATE_CHAT_ERROR')
	};
	const CollabCreation = {
	  name: 'CollabCreation',
	  components: {
	    TitleInput: im_v2_component_content_chatForms_elements.TitleInput,
	    ChatAvatar: im_v2_component_content_chatForms_elements.ChatAvatar,
	    DescriptionBanner,
	    CreateChatHeading: im_v2_component_content_chatForms_elements.CreateChatHeading,
	    TextareaInput: im_v2_component_content_chatForms_elements.TextareaInput,
	    ButtonPanel: im_v2_component_content_chatForms_elements.ButtonPanel,
	    RightsSection
	  },
	  data() {
	    return {
	      isCreating: false,
	      avatarFile: null,
	      chatTitle: '',
	      description: '',
	      groupSettings: {
	        ownerId: im_v2_application_core.Core.getUserId(),
	        moderatorMembers: [],
	        options: null,
	        permissions: null
	      }
	    };
	  },
	  watch: {
	    chatTitle(newValue) {
	      im_v2_lib_createChat.CreateChatManager.getInstance().setChatTitle(newValue);
	    }
	  },
	  computed: {
	    EmptyAvatarType: () => im_v2_component_elements.EmptyAvatarType,
	    createButtonColorScheme() {
	      return {
	        borderColor: im_v2_const.Color.transparent,
	        backgroundColor: im_v2_const.Color.collab60,
	        iconColor: im_v2_const.Color.white,
	        textColor: im_v2_const.Color.white,
	        hoverColor: im_v2_const.Color.collab50
	      };
	    }
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.layout.onLayoutChange, this.onLayoutChange);
	    this.restoreFields();
	    im_v2_lib_createChat.CreateChatManager.getInstance().setChatType(im_v2_const.ChatType.collab);
	    im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(true);
	    im_v2_lib_createChat.CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.layout.onLayoutChange, this.onLayoutChange);
	    if (this.exitByCancel || this.exitByChatTypeSwitch || this.exitByCreation) {
	      return;
	    }
	    this.saveFields();
	  },
	  methods: {
	    restoreFields() {
	      const savedFields = im_v2_lib_createChat.CreateChatManager.getInstance().getFields();
	      if (!savedFields) {
	        return;
	      }
	      const {
	        chatTitle,
	        avatarFile,
	        description
	      } = savedFields;
	      this.chatTitle = chatTitle;
	      this.avatarFile = avatarFile;
	      this.description = description;
	    },
	    saveFields() {
	      im_v2_lib_createChat.CreateChatManager.getInstance().saveFields({
	        chatTitle: this.chatTitle,
	        avatarFile: this.avatarFile,
	        description: this.description
	      });
	    },
	    onLayoutChange(event) {
	      const {
	        to
	      } = event.getData();
	      if (to.name === im_v2_const.Layout.createChat.name && to.entityId !== im_v2_const.ChatType.collab) {
	        this.exitByChatTypeSwitch = true;
	      }
	    },
	    async onCreateClick() {
	      this.isCreating = true;
	      try {
	        const {
	          newDialogId
	        } = await this.getChatService().createCollab({
	          title: this.chatTitle,
	          avatar: this.avatarFile,
	          description: this.description,
	          ownerId: this.groupSettings.ownerId,
	          moderatorMembers: this.groupSettings.moderatorMembers,
	          options: this.groupSettings.options,
	          permissions: this.groupSettings.permissions
	        });
	        this.isCreating = false;
	        this.exitByCreation = true;
	        im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(false);
	        await im_public.Messenger.openChat(newDialogId);
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.header.openAddToChatPopup);
	      } catch (error) {
	        this.handleCreationError(error);
	      }
	    },
	    onCancelClick() {
	      this.exitByCancel = true;
	      im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(false);
	      im_public.Messenger.openChat();
	    },
	    onAvatarChange(newAvatarFile) {
	      this.avatarFile = newAvatarFile;
	      im_v2_lib_createChat.CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
	    },
	    onDescriptionChange(description) {
	      this.description = description;
	    },
	    onRightsChange(rights) {
	      const {
	        ownerId,
	        moderators,
	        permissions,
	        options
	      } = rights;
	      this.groupSettings.ownerId = ownerId;
	      this.groupSettings.moderatorMembers = moderators;
	      this.groupSettings.permissions = permissions;
	      this.groupSettings.options = options;
	    },
	    handleCreationError(error) {
	      var _NotificationTextByEr;
	      const {
	        code
	      } = error;
	      const notificationText = (_NotificationTextByEr = NotificationTextByErrorCode[code]) != null ? _NotificationTextByEr : NotificationTextByErrorCode.default;
	      this.isCreating = false;
	      BX.UI.Notification.Center.notify({
	        content: notificationText
	      });
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat-forms__content --collab">
			<div class="bx-im-content-chat-forms__header">
				<ChatAvatar 
					:avatarFile="avatarFile" 
					:chatTitle="chatTitle" 
					:type="EmptyAvatarType.collab" 
					@avatarChange="onAvatarChange" 
				/>
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_COLLAB_TITLE_PLACEHOLDER')" />
			</div>
			<DescriptionBanner />
			<CreateChatHeading :text="loc('IM_CREATE_COLLAB_DESCRIPTION_TITLE')" />
			<div class="bx-im-content-chat-forms__description_container">
				<TextareaInput
					:value="description"
					:placeholder="loc('IM_CREATE_COLLAB_DESCRIPTION_PLACEHOLDER')"
					:border="false"
					@input="onDescriptionChange"
				/>
			</div>
			<RightsSection @change="onRightsChange" />
		</div>
		<ButtonPanel
			:isCreating="isCreating"
			:createButtonTitle="loc('IM_CREATE_COLLAB_CONFIRM')"
			:createButtonColorScheme="createButtonColorScheme"
			@create="onCreateClick"
			@cancel="onCancelClick"
		/>
	`
	};

	const CreatableChat = {
	  chat: 'chat',
	  videoconf: 'videoconf',
	  channel: 'channel',
	  collab: 'collab'
	};
	const CreationComponentByChatType = {
	  [im_v2_const.ChatType.chat]: GroupChatCreation,
	  [im_v2_const.ChatType.videoconf]: ConferenceCreation,
	  [im_v2_const.ChatType.channel]: ChannelCreation,
	  [im_v2_const.ChatType.collab]: CollabCreation,
	  default: GroupChatCreation
	};

	// @vue/component
	const CreateChatContent = {
	  name: 'CreateChatContent',
	  components: {
	    GroupChatCreation,
	    ConferenceCreation,
	    ChannelCreation,
	    CollabCreation
	  },
	  props: {
	    entityId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    ChatType: () => im_v2_const.ChatType,
	    chatType() {
	      return this.entityId;
	    },
	    creationComponent() {
	      var _CreationComponentByC;
	      return (_CreationComponentByC = CreationComponentByChatType[this.chatType]) != null ? _CreationComponentByC : CreationComponentByChatType.default;
	    }
	  },
	  template: `
		<div class="bx-im-content-chat-forms__container">
			<component :is="creationComponent" />
		</div>
	`
	};

	var _initialManagers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initialManagers");
	var _initialMembers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initialMembers");
	class ChatMemberDiffManager {
	  constructor() {
	    Object.defineProperty(this, _initialManagers, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _initialMembers, {
	      writable: true,
	      value: []
	    });
	  }
	  setInitialManagers(initialManagers) {
	    babelHelpers.classPrivateFieldLooseBase(this, _initialManagers)[_initialManagers] = initialManagers;
	  }
	  setInitialChatMembers(initialMembers) {
	    babelHelpers.classPrivateFieldLooseBase(this, _initialMembers)[_initialMembers] = initialMembers;
	  }
	  getAddedMemberEntities(modifiedEntities) {
	    const originalSet = new Set(babelHelpers.classPrivateFieldLooseBase(this, _initialMembers)[_initialMembers].map(elem => JSON.stringify(elem)));
	    return modifiedEntities.filter(elem => !originalSet.has(JSON.stringify(elem)));
	  }
	  getDeletedMemberEntities(modifiedEntities) {
	    const modifiedSet = new Set(modifiedEntities.map(elem => JSON.stringify(elem)));
	    return babelHelpers.classPrivateFieldLooseBase(this, _initialMembers)[_initialMembers].filter(elem => !modifiedSet.has(JSON.stringify(elem)));
	  }
	  getAddedManagers(modifiedArray) {
	    const originalSet = new Set(babelHelpers.classPrivateFieldLooseBase(this, _initialManagers)[_initialManagers]);
	    return modifiedArray.filter(elem => !originalSet.has(elem));
	  }
	  getDeletedManagers(modifiedArray) {
	    const modifiedSet = new Set(modifiedArray);
	    return babelHelpers.classPrivateFieldLooseBase(this, _initialManagers)[_initialManagers].filter(elem => !modifiedSet.has(elem));
	  }
	}

	const ENTITY_ID = 'imUpdateChatCollapsedUsers';
	const AVATAR_URL = '/bitrix/js/im/v2/component/content/chat-forms/src/css/images/collapsed-users-avatar.svg';
	function getCollapsedUsersElement({
	  title,
	  onclick
	}) {
	  const textNode = `
		<div class="bx-im-content-chat-forms__collapsed-users">
			${title}
			<div class="bx-im-content-chat-forms__collapsed-users-icon"></div>
		</div>
	`;
	  return {
	    id: 0,
	    entityId: ENTITY_ID,
	    avatar: AVATAR_URL,
	    title: {
	      text: textNode,
	      type: 'html'
	    },
	    deselectable: false,
	    tagOptions: {
	      onclick
	    }
	  };
	}

	// @vue/component
	const GroupChatUpdating = {
	  name: 'GroupChatUpdating',
	  components: {
	    TitleInput: im_v2_component_content_chatForms_elements.TitleInput,
	    ChatAvatar: im_v2_component_content_chatForms_elements.ChatAvatar,
	    ButtonPanel: im_v2_component_content_chatForms_elements.ButtonPanel,
	    CreateChatHeading: im_v2_component_content_chatForms_elements.CreateChatHeading,
	    ChatMembersSelector: im_v2_component_content_chatForms_elements.ChatMembersSelector,
	    SettingsSection: im_v2_component_content_chatForms_elements.SettingsSection,
	    RightsSection: im_v2_component_content_chatForms_elements.RightsSection
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isLoading: true,
	      isUpdating: false,
	      areUsersCollapsed: false,
	      collapsedUsersCount: 0,
	      avatarFile: null,
	      avatarUrl: '',
	      chatTitle: '',
	      chatMembers: [],
	      settings: {
	        isAvailableInSearch: false,
	        description: ''
	      },
	      rights: {
	        ownerId: 0,
	        managerIds: [],
	        manageUsersAdd: '',
	        manageUsersDelete: '',
	        manageUi: '',
	        manageMessages: ''
	      }
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    collapsedUsers() {
	      if (!this.areUsersCollapsed) {
	        return [];
	      }
	      const title = this.loc('IM_UPDATE_GROUP_CHAT_COLLAPSED_ELEMENT', {
	        '#COUNT#': this.collapsedUsersCount
	      });
	      const element = getCollapsedUsersElement({
	        title,
	        onclick: this.onCollapsedUsersClick
	      });
	      return [element];
	    },
	    canChangeSearchAvailability() {
	      return [im_v2_const.ChatType.open, im_v2_const.ChatType.chat].includes(this.dialog.type);
	    },
	    changedChatType() {
	      if (!this.canChangeSearchAvailability) {
	        return this.dialog.type;
	      }
	      return this.settings.isAvailableInSearch ? im_v2_const.ChatType.open : im_v2_const.ChatType.chat;
	    }
	  },
	  async created() {
	    await this.fillForm();
	    this.memberDiffManager = new ChatMemberDiffManager();
	    this.memberDiffManager.setInitialChatMembers(this.chatMembers);
	    this.memberDiffManager.setInitialManagers(this.rights.managerIds);
	    this.isLoading = false;
	  },
	  methods: {
	    onMembersChange(currentTags) {
	      this.chatMembers = currentTags;
	    },
	    onChatTypeChange(isAvailableInSearch) {
	      this.settings.isAvailableInSearch = isAvailableInSearch;
	    },
	    onOwnerChange(ownerId) {
	      this.rights.ownerId = ownerId;
	    },
	    onManagersChange(managerIds) {
	      this.rights.managerIds = managerIds;
	    },
	    onManageUsersAddChange(newValue) {
	      this.rights.manageUsersAdd = newValue;
	    },
	    onManageUsersDeleteChange(newValue) {
	      this.rights.manageUsersDelete = newValue;
	    },
	    onManageUiChange(newValue) {
	      this.rights.manageUi = newValue;
	    },
	    onManageMessagesChange(newValue) {
	      this.rights.manageMessages = newValue;
	    },
	    onDescriptionChange(description) {
	      this.settings.description = description;
	    },
	    async fillForm() {
	      this.chatTitle = this.dialog.name;
	      this.avatarUrl = this.dialog.avatar;
	      this.settings.description = this.dialog.description;
	      this.chatMembers = await this.getMemberEntities();
	      this.rights.ownerId = this.dialog.ownerId;
	      this.rights.manageMessages = this.dialog.permissions.manageMessages;
	      this.rights.managerIds = this.dialog.managerList;
	      this.rights.manageUi = this.dialog.permissions.manageUi;
	      this.rights.manageUsersAdd = this.dialog.permissions.manageUsersAdd;
	      this.rights.manageUsersDelete = this.dialog.permissions.manageUsersDelete;
	      this.settings.isAvailableInSearch = this.dialog.type === im_v2_const.ChatType.open;
	    },
	    async getMemberEntities() {
	      const {
	        memberEntities,
	        areUsersCollapsed,
	        userCount
	      } = await this.getChatService().getMemberEntities(this.chatId);
	      if (areUsersCollapsed) {
	        this.areUsersCollapsed = true;
	        this.collapsedUsersCount = userCount;
	        return memberEntities;
	      }
	      return memberEntities;
	    },
	    async onUpdateClick() {
	      im_v2_lib_analytics.Analytics.getInstance().chatEdit.onSubmitForm(this.dialogId);
	      im_v2_lib_analytics.Analytics.getInstance().ignoreNextChatOpen(this.dialogId);
	      this.isUpdating = true;
	      await this.getChatService().updateChat(this.chatId, {
	        title: this.chatTitle,
	        avatar: this.avatarFile,
	        type: this.changedChatType,
	        addedMemberEntities: this.memberDiffManager.getAddedMemberEntities(this.chatMembers),
	        deletedMemberEntities: this.memberDiffManager.getDeletedMemberEntities(this.chatMembers),
	        addedManagers: this.memberDiffManager.getAddedManagers(this.rights.managerIds),
	        deletedManagers: this.memberDiffManager.getDeletedManagers(this.rights.managerIds),
	        ownerId: this.rights.ownerId,
	        isAvailableInSearch: this.settings.isAvailableInSearch,
	        description: this.settings.description,
	        manageUsersAdd: this.rights.manageUsersAdd,
	        manageUsersDelete: this.rights.manageUsersDelete,
	        manageUi: this.rights.manageUi,
	        manageMessages: this.rights.manageMessages
	      }).catch(() => {
	        this.isUpdating = false;
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_UPDATE_CHAT_ERROR')
	        });
	      });
	      this.isUpdating = false;
	      return im_public.Messenger.openChat(this.dialogId);
	    },
	    onCancelClick() {
	      im_v2_lib_analytics.Analytics.getInstance().ignoreNextChatOpen(this.dialogId);
	      void im_public.Messenger.openChat(this.dialogId);
	    },
	    onAvatarChange(newAvatarFile) {
	      this.avatarFile = newAvatarFile;
	      this.avatarUrl = '';
	    },
	    onScroll() {
	      var _MenuManager$getMenuB, _MenuManager$getMenuB2, _MenuManager$getMenuB3, _MenuManager$getMenuB4;
	      (_MenuManager$getMenuB = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUsersAddMenu)) == null ? void 0 : _MenuManager$getMenuB.close();
	      (_MenuManager$getMenuB2 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUsersDeleteMenu)) == null ? void 0 : _MenuManager$getMenuB2.close();
	      (_MenuManager$getMenuB3 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUiMenu)) == null ? void 0 : _MenuManager$getMenuB3.close();
	      (_MenuManager$getMenuB4 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageMessagesMenu)) == null ? void 0 : _MenuManager$getMenuB4.close();
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    async onCollapsedUsersClick() {
	      const confirmResult = await im_v2_lib_confirm.showExitUpdateChatConfirm(this.dialogId);
	      if (!confirmResult) {
	        return;
	      }
	      await this.onUpdateClick();
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.members,
	        dialogId: this.dialogId
	      });
	    }
	  },
	  template: `
		<div v-if="isLoading" class="bx-im-content-chat-forms__skeleton"></div>
		<div v-else class="bx-im-content-chat-forms__content --chat" @scroll="onScroll">
			<div class="bx-im-content-chat-forms__header">
				<ChatAvatar
					:avatarFile="avatarFile" 
					:existingAvatarUrl="avatarUrl" 
					:chatTitle="chatTitle" 
					@avatarChange="onAvatarChange" 
				/>
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_CHAT_TITLE_PLACEHOLDER')" />
			</div>
			<CreateChatHeading :text="loc('IM_CREATE_CHAT_MEMBERS_TITLE')" />
			<div class="bx-im-content-chat-forms__members_container">
				<ChatMembersSelector 
					:customElements="collapsedUsers"
					:chatMembers="chatMembers" 
					@membersChange="onMembersChange" 
				/>
			</div>
			<SettingsSection
				:isAvailableInSearch="settings.isAvailableInSearch"
				:description="settings.description"
				:withSearchOption="canChangeSearchAvailability"
				@chatTypeChange="onChatTypeChange"
				@descriptionChange="onDescriptionChange"
			/>
			<RightsSection
				:ownerId="rights.ownerId"
				:managerIds="rights.managerIds"
				:manageUsersAdd="rights.manageUsersAdd"
				:manageUsersDelete="rights.manageUsersDelete"
				:manageUi="rights.manageUi"
				:manageMessages="rights.manageMessages"
				@ownerChange="onOwnerChange"
				@managersChange="onManagersChange"
				@manageUsersAddChange="onManageUsersAddChange"
				@manageUsersDeleteChange="onManageUsersDeleteChange"
				@manageUiChange="onManageUiChange"
				@manageMessagesChange="onManageMessagesChange"
			/>
		</div>
		<ButtonPanel
			:isCreating="isUpdating || isLoading"
			:createButtonTitle="loc('IM_UPDATE_CONFIRM')"
			@create="onUpdateClick"
			@cancel="onCancelClick"
		/>
	`
	};

	// @vue/component
	const ChannelUpdating = {
	  name: 'ChannelUpdating',
	  components: {
	    TitleInput: im_v2_component_content_chatForms_elements.TitleInput,
	    ChatAvatar: im_v2_component_content_chatForms_elements.ChatAvatar,
	    ChatMembersSelector: im_v2_component_content_chatForms_elements.ChatMembersSelector,
	    SettingsSection: im_v2_component_content_chatForms_elements.SettingsSection,
	    RightsSection: im_v2_component_content_chatForms_elements.RightsSection,
	    AppearanceSection: im_v2_component_content_chatForms_elements.AppearanceSection,
	    PrivacySection: im_v2_component_content_chatForms_elements.PrivacySection,
	    ButtonPanel: im_v2_component_content_chatForms_elements.ButtonPanel,
	    TextareaInput: im_v2_component_content_chatForms_elements.TextareaInput,
	    CreateChatHeading: im_v2_component_content_chatForms_elements.CreateChatHeading
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isLoading: true,
	      isUpdating: false,
	      areUsersCollapsed: false,
	      collapsedUsersCount: 0,
	      avatarFile: null,
	      avatarUrl: '',
	      chatTitle: '',
	      chatMembers: [],
	      settings: {
	        isAvailableInSearch: true,
	        description: ''
	      },
	      rights: {
	        ownerId: 0,
	        managerIds: [],
	        manageUsersAdd: '',
	        manageUsersDelete: '',
	        manageUi: '',
	        manageMessages: ''
	      }
	    };
	  },
	  computed: {
	    ChatType: () => im_v2_const.ChatType,
	    EmptyAvatarType: () => im_v2_component_elements.EmptyAvatarType,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    collapsedUsers() {
	      if (!this.areUsersCollapsed) {
	        return [];
	      }
	      const title = this.loc('IM_UPDATE_CHANNEL_COLLAPSED_ELEMENT', {
	        '#COUNT#': this.collapsedUsersCount
	      });
	      const element = getCollapsedUsersElement({
	        title,
	        onclick: this.onCollapsedUsersClick
	      });
	      return [element];
	    },
	    changedChatType() {
	      return this.settings.isAvailableInSearch ? im_v2_const.ChatType.openChannel : im_v2_const.ChatType.channel;
	    }
	  },
	  async created() {
	    await this.fillForm();
	    this.memberDiffManager = new ChatMemberDiffManager();
	    this.memberDiffManager.setInitialChatMembers(this.chatMembers);
	    this.memberDiffManager.setInitialManagers(this.rights.managerIds);
	    this.isLoading = false;
	  },
	  methods: {
	    onMembersChange(currentTags) {
	      this.chatMembers = currentTags;
	    },
	    onOwnerChange(ownerId) {
	      this.rights.ownerId = ownerId;
	    },
	    onManagersChange(managerIds) {
	      this.rights.managerIds = managerIds;
	    },
	    onDescriptionChange(description) {
	      this.settings.description = description;
	    },
	    onManageUsersAddChange(newValue) {
	      this.rights.manageUsersAdd = newValue;
	    },
	    onManageUsersDeleteChange(newValue) {
	      this.rights.manageUsersDelete = newValue;
	    },
	    onManageUiChange(newValue) {
	      this.rights.manageUi = newValue;
	    },
	    onManageMessagesChange(newValue) {
	      this.rights.manageMessages = newValue;
	    },
	    onChatTypeChange(isAvailableInSearch) {
	      this.settings.isAvailableInSearch = isAvailableInSearch;
	    },
	    async onUpdateClick() {
	      im_v2_lib_analytics.Analytics.getInstance().chatEdit.onSubmitForm(this.dialogId);
	      im_v2_lib_analytics.Analytics.getInstance().ignoreNextChatOpen(this.dialogId);
	      this.isUpdating = true;
	      await this.getChatService().updateChat(this.chatId, {
	        title: this.chatTitle,
	        avatar: this.avatarFile,
	        type: this.changedChatType,
	        addedMemberEntities: this.memberDiffManager.getAddedMemberEntities(this.chatMembers),
	        deletedMemberEntities: this.memberDiffManager.getDeletedMemberEntities(this.chatMembers),
	        addedManagers: this.memberDiffManager.getAddedManagers(this.rights.managerIds),
	        deletedManagers: this.memberDiffManager.getDeletedManagers(this.rights.managerIds),
	        ownerId: this.rights.ownerId,
	        isAvailableInSearch: this.settings.isAvailableInSearch,
	        description: this.settings.description,
	        manageUsersAdd: this.rights.manageUsersAdd,
	        manageUsersDelete: this.rights.manageUsersDelete,
	        manageUi: this.rights.manageUi,
	        manageMessages: this.rights.manageMessages
	      }).catch(() => {
	        this.isUpdating = false;
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_UPDATE_CHAT_ERROR')
	        });
	      });
	      this.isUpdating = false;
	      return im_public.Messenger.openChat(this.dialogId);
	    },
	    onCancelClick() {
	      im_v2_lib_analytics.Analytics.getInstance().ignoreNextChatOpen(this.dialogId);
	      void im_public.Messenger.openChat(this.dialogId);
	    },
	    onAvatarChange(newAvatarFile) {
	      this.avatarFile = newAvatarFile;
	      this.avatarUrl = '';
	    },
	    onScroll() {
	      var _MenuManager$getMenuB, _MenuManager$getMenuB2, _MenuManager$getMenuB3, _MenuManager$getMenuB4;
	      (_MenuManager$getMenuB = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUsersAddMenu)) == null ? void 0 : _MenuManager$getMenuB.close();
	      (_MenuManager$getMenuB2 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUsersDeleteMenu)) == null ? void 0 : _MenuManager$getMenuB2.close();
	      (_MenuManager$getMenuB3 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUiMenu)) == null ? void 0 : _MenuManager$getMenuB3.close();
	      (_MenuManager$getMenuB4 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageMessagesMenu)) == null ? void 0 : _MenuManager$getMenuB4.close();
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    async fillForm() {
	      this.chatTitle = this.dialog.name;
	      this.avatarUrl = this.dialog.avatar;
	      this.settings.description = this.dialog.description;
	      this.chatMembers = await this.getMemberEntities();
	      this.rights.ownerId = this.dialog.ownerId;
	      this.rights.manageMessages = this.dialog.permissions.manageMessages;
	      this.rights.managerIds = this.dialog.managerList;
	      this.rights.manageUi = this.dialog.permissions.manageUi;
	      this.rights.manageUsersAdd = this.dialog.permissions.manageUsersAdd;
	      this.rights.manageUsersDelete = this.dialog.permissions.manageUsersDelete;
	      this.settings.isAvailableInSearch = this.dialog.type === im_v2_const.ChatType.openChannel;
	    },
	    async getMemberEntities() {
	      const {
	        memberEntities,
	        areUsersCollapsed,
	        userCount
	      } = await this.getChatService().getMemberEntities(this.chatId);
	      if (areUsersCollapsed) {
	        this.areUsersCollapsed = true;
	        this.collapsedUsersCount = userCount;
	        return memberEntities;
	      }
	      return memberEntities;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    async onCollapsedUsersClick() {
	      const confirmResult = await im_v2_lib_confirm.showExitUpdateChatConfirm(this.dialogId);
	      if (!confirmResult) {
	        return;
	      }
	      await this.onUpdateClick();
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.members,
	        dialogId: this.dialogId
	      });
	    }
	  },
	  template: `
		<div v-if="isLoading" class="bx-im-content-chat-forms__skeleton"></div>
		<div v-else class="bx-im-content-chat-forms__content --channel" @scroll="onScroll">
			<div class="bx-im-content-chat-forms__header">
				<ChatAvatar
					:avatarFile="avatarFile"
					:chatTitle="chatTitle"
					:existingAvatarUrl="avatarUrl"
					:type="EmptyAvatarType.squared"
					@avatarChange="onAvatarChange"
				/>
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_CHANNEL_TITLE_PLACEHOLDER_V2')" />
			</div>
			<CreateChatHeading :text="loc('IM_CREATE_CHANNEL_DESCRIPTION_TITLE')" />
			<div class="bx-im-content-chat-forms__description_container">
				<TextareaInput
					:value="settings.description"
					:placeholder="loc('IM_CREATE_CHANNEL_DESCRIPTION_PLACEHOLDER_V3')"
					:border="false"
					@input="onDescriptionChange"
				/>
			</div>
			<PrivacySection
				:isAvailableInSearch="settings.isAvailableInSearch"
				@chatTypeChange="onChatTypeChange"
			/>
			<CreateChatHeading
				:text="loc('IM_CREATE_CHANNEL_MEMBERS_TITLE')"
				:hintText="loc('IM_CREATE_CHANNEL_MEMBERS_HINT')"
			/>
			<div class="bx-im-content-chat-forms__members_container">
				<ChatMembersSelector
					:customElements="collapsedUsers"
					:chatMembers="chatMembers" 
					@membersChange="onMembersChange" 
				/>
			</div>
			<RightsSection
				:chatType="ChatType.channel"
				:ownerId="rights.ownerId"
				:managerIds="rights.managerIds"
				:manageUsersAdd="rights.manageUsersAdd"
				:manageUsersDelete="rights.manageUsersDelete"
				:manageUi="rights.manageUi"
				:manageMessages="rights.manageMessages"
				@ownerChange="onOwnerChange"
				@managersChange="onManagersChange"
				@manageUsersAddChange="onManageUsersAddChange"
				@manageUsersDeleteChange="onManageUsersDeleteChange"
				@manageUiChange="onManageUiChange"
				@manageMessagesChange="onManageMessagesChange"
			/>
		</div>
		<ButtonPanel
			:isCreating="isLoading || isUpdating"
			:createButtonTitle="loc('IM_UPDATE_CONFIRM')"
			@create="onUpdateClick"
			@cancel="onCancelClick"
		/>
	`
	};

	const UpdateCollabErrorCode = {
	  emptyName: 'name',
	  duplicateName: 'ERROR_GROUP_NAME_EXISTS'
	};
	const NotificationTextByErrorCode$1 = {
	  [UpdateCollabErrorCode.emptyName]: main_core.Loc.getMessage('IM_CREATE_COLLAB_ERROR_EMPTY_NAME'),
	  [UpdateCollabErrorCode.duplicateName]: main_core.Loc.getMessage('IM_CREATE_COLLAB_ERROR_DUPLICATE_NAME'),
	  default: main_core.Loc.getMessage('IM_UPDATE_CHAT_ERROR')
	};

	// @vue/component
	const CollabUpdating = {
	  name: 'CollabUpdating',
	  components: {
	    TitleInput: im_v2_component_content_chatForms_elements.TitleInput,
	    ChatAvatar: im_v2_component_content_chatForms_elements.ChatAvatar,
	    TextareaInput: im_v2_component_content_chatForms_elements.TextareaInput,
	    ButtonPanel: im_v2_component_content_chatForms_elements.ButtonPanel,
	    CreateChatHeading: im_v2_component_content_chatForms_elements.CreateChatHeading,
	    RightsSection
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isUpdating: false,
	      avatarFile: null,
	      avatarUrl: '',
	      chatTitle: '',
	      description: '',
	      groupSettings: {
	        ownerId: im_v2_application_core.Core.getUserId(),
	        moderatorMembers: [],
	        options: null,
	        permissions: null,
	        isModified: false
	      }
	    };
	  },
	  computed: {
	    EmptyAvatarType: () => im_v2_component_elements.EmptyAvatarType,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    collabId() {
	      const {
	        collabId
	      } = this.$store.getters['chats/collabs/getByChatId'](this.chatId);
	      return collabId;
	    },
	    updateButtonColorScheme() {
	      return {
	        borderColor: im_v2_const.Color.transparent,
	        backgroundColor: im_v2_const.Color.collab60,
	        iconColor: im_v2_const.Color.white,
	        textColor: im_v2_const.Color.white,
	        hoverColor: im_v2_const.Color.collab50
	      };
	    }
	  },
	  created() {
	    this.fillForm();
	    this.initDiffManager();
	  },
	  methods: {
	    initDiffManager() {
	      this.memberDiffManager = new ChatMemberDiffManager();
	      const managersWithoutOwner = this.dialog.managerList.filter(managerId => {
	        return managerId !== im_v2_application_core.Core.getUserId();
	      });
	      this.memberDiffManager.setInitialManagers(managersWithoutOwner);
	    },
	    async fillForm() {
	      this.chatTitle = this.dialog.name;
	      this.avatarUrl = this.dialog.avatar;
	      this.description = this.dialog.description;
	    },
	    onAvatarChange(newAvatarFile) {
	      this.avatarFile = newAvatarFile;
	      this.avatarUrl = '';
	    },
	    onDescriptionChange(description) {
	      this.description = description;
	    },
	    onRightsChange(rights) {
	      const {
	        ownerId,
	        moderators,
	        permissions,
	        options
	      } = rights;
	      this.groupSettings.ownerId = ownerId;
	      this.groupSettings.moderatorMembers = moderators;
	      this.groupSettings.permissions = permissions;
	      this.groupSettings.options = options;
	      this.groupSettings.isModified = true;
	    },
	    async onUpdateClick() {
	      im_v2_lib_analytics.Analytics.getInstance().chatEdit.onSubmitForm(this.dialogId);
	      im_v2_lib_analytics.Analytics.getInstance().ignoreNextChatOpen(this.dialogId);
	      this.isUpdating = true;
	      let payload = {
	        title: this.chatTitle,
	        avatar: this.avatarFile,
	        description: this.description
	      };
	      if (this.groupSettings.isModified) {
	        const groupSettings = {
	          ownerId: this.groupSettings.ownerId,
	          options: this.groupSettings.options,
	          permissions: this.groupSettings.permissions,
	          addModeratorMembers: this.memberDiffManager.getAddedManagers(this.groupSettings.moderatorMembers),
	          deleteModeratorMembers: this.memberDiffManager.getDeletedManagers(this.groupSettings.moderatorMembers)
	        };
	        payload = {
	          ...payload,
	          groupSettings
	        };
	      }
	      try {
	        await this.getChatService().updateCollab(this.dialogId, payload);
	        this.isUpdating = false;
	        void im_public.Messenger.openChat(this.dialogId);
	      } catch (error) {
	        this.handleUpdateError(error);
	      }
	    },
	    onCancelClick() {
	      im_v2_lib_analytics.Analytics.getInstance().ignoreNextChatOpen(this.dialogId);
	      void im_public.Messenger.openChat(this.dialogId);
	    },
	    handleUpdateError(error) {
	      var _NotificationTextByEr;
	      console.error('1', error);
	      const {
	        code
	      } = error;
	      const notificationText = (_NotificationTextByEr = NotificationTextByErrorCode$1[code]) != null ? _NotificationTextByEr : NotificationTextByErrorCode$1.default;
	      this.isUpdating = false;
	      BX.UI.Notification.Center.notify({
	        content: notificationText
	      });
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat-forms__content --collab">
			<div class="bx-im-content-chat-forms__header">
				<ChatAvatar
					:avatarFile="avatarFile"
					:existingAvatarUrl="avatarUrl"
					:chatTitle="chatTitle"
					:type="EmptyAvatarType.collab"
					@avatarChange="onAvatarChange"
				/>
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_COLLAB_TITLE_PLACEHOLDER')" />
			</div>
			<CreateChatHeading :text="loc('IM_CREATE_COLLAB_DESCRIPTION_TITLE')" />
			<div class="bx-im-content-chat-forms__description_container">
				<TextareaInput
					:value="description"
					:placeholder="loc('IM_CREATE_COLLAB_DESCRIPTION_PLACEHOLDER')"
					:border="false"
					@input="onDescriptionChange"
				/>
			</div>
			<RightsSection :collabId="collabId" @change="onRightsChange" />
		</div>
		<ButtonPanel
			:isCreating="isUpdating"
			:createButtonTitle="loc('IM_UPDATE_CONFIRM')"
			:createButtonColorScheme="updateButtonColorScheme"
			@create="onUpdateClick"
			@cancel="onCancelClick"
		/>
	`
	};

	// @vue/component
	const UpdateChatContent = {
	  name: 'UpdateChatContent',
	  components: {
	    GroupChatUpdating,
	    ChannelUpdating,
	    CollabUpdating
	  },
	  props: {
	    entityId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    ChatType: () => im_v2_const.ChatType,
	    chatType() {
	      const chat = this.$store.getters['chats/get'](this.entityId, true);
	      return chat.type;
	    },
	    isChannel() {
	      const editableChannelTypes = new Set([im_v2_const.ChatType.channel, im_v2_const.ChatType.openChannel]);
	      return editableChannelTypes.has(this.chatType);
	    },
	    isChat() {
	      const editableChatTypes = new Set([im_v2_const.ChatType.chat, im_v2_const.ChatType.open, im_v2_const.ChatType.videoconf]);
	      return editableChatTypes.has(this.chatType);
	    },
	    isCollab() {
	      return this.chatType === im_v2_const.ChatType.collab;
	    }
	  },
	  template: `
		<div class="bx-im-content-chat-forms__container">
			<GroupChatUpdating v-if="isChat" :dialogId="this.entityId" />
			<ChannelUpdating v-else-if="isChannel" :dialogId="this.entityId" />
			<CollabUpdating v-else-if="isCollab" :dialogId="this.entityId" />
		</div>
	`
	};

	exports.CreateChatContent = CreateChatContent;
	exports.CreatableChat = CreatableChat;
	exports.UpdateChatContent = UpdateChatContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Socialnetwork.Collab,BX.Event,BX.Main,BX.Messenger.v2.Lib,BX,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Const,BX.Messenger.v2.Component.Content));
//# sourceMappingURL=registry.bundle.js.map
