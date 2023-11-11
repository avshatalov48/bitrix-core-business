/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_animation,ui_entitySelector,main_core,main_core_events,main_popup,im_public,im_v2_application_core,im_v2_lib_createChat,im_v2_lib_permission,im_v2_provider_service,im_v2_const,ui_forms,im_v2_component_elements) {
	'use strict';

	// @vue/component
	const TitleInput = {
	  name: 'TitleInput',
	  props: {
	    modelValue: {
	      type: String,
	      default: ''
	    },
	    placeholder: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['update:modelValue'],
	  data() {
	    return {};
	  },
	  mounted() {
	    this.$refs.titleInput.focus();
	  },
	  methods: {
	    onInput(event) {
	      this.$emit('update:modelValue', event.target.value);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-create-chat__title_container">
			<input
				:value="modelValue"
				:placeholder="placeholder"
				@input="onInput"
				class="bx-im-content-create-chat__title_input"
				ref="titleInput"
			/>
		</div>
	`
	};

	// @vue/component
	const ChatAvatar = {
	  name: 'ChatAvatar',
	  props: {
	    avatarFile: {
	      required: true,
	      validator(value) {
	        return value instanceof File || main_core.Type.isNull(value);
	      }
	    },
	    chatTitle: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['avatarChange'],
	  data() {
	    return {};
	  },
	  computed: {
	    preparedAvatar() {
	      if (!this.avatarFile) {
	        return null;
	      }
	      return URL.createObjectURL(this.avatarFile);
	    }
	  },
	  methods: {
	    onAvatarChangeClick() {
	      this.$refs.avatarInput.click();
	    },
	    async onAvatarSelect(event) {
	      const input = event.target;
	      const file = input.files[0];
	      if (!file) {
	        return;
	      }
	      const preparedAvatar = await this.getChatService().prepareAvatar(file);
	      if (!preparedAvatar) {
	        return;
	      }
	      this.$emit('avatarChange', preparedAvatar);
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    }
	  },
	  template: `
		<div class="bx-im-content-create-chat__avatar_container" @click="onAvatarChangeClick">
			<img v-if="preparedAvatar" class="bx-im-content-create-chat__avatar_image" :src="preparedAvatar" :alt="chatTitle" />
		</div>
		<input type="file" @change="onAvatarSelect" accept="image/*" class="bx-im-content-create-chat__avatar_input" ref="avatarInput">
	`
	};

	// @vue/component
	const ChatMembersSelector = {
	  props: {
	    chatMembers: {
	      type: Array,
	      required: true
	    }
	  },
	  emits: ['membersChange'],
	  data() {
	    return {};
	  },
	  computed: {
	    currentUserId() {
	      return im_v2_application_core.Core.getUserId();
	    }
	  },
	  created() {
	    const members = this.chatMembers.map(userId => {
	      return ['user', userId];
	    });
	    const preselectedItems = [['user', this.currentUserId], ...members];
	    this.membersSelector = new ui_entitySelector.TagSelector({
	      maxHeight: 99,
	      placeholder: '',
	      addButtonCaption: this.loc('IM_CREATE_CHAT_USER_SELECTOR_ADD_MEMBERS'),
	      addButtonCaptionMore: this.loc('IM_CREATE_CHAT_USER_SELECTOR_ADD_MEMBERS'),
	      showCreateButton: false,
	      dialogOptions: {
	        enableSearch: false,
	        context: 'IM_CHAT_CREATE',
	        entities: [{
	          id: 'user'
	        }, {
	          id: 'department'
	        }],
	        preselectedItems,
	        undeselectedItems: [['user', this.currentUserId]]
	      },
	      events: {
	        onAfterTagAdd: event => {
	          const selector = event.getTarget();
	          this.$emit('membersChange', selector.getTags().map(tag => tag.id));
	        },
	        onAfterTagRemove: event => {
	          const selector = event.getTarget();
	          this.$emit('membersChange', selector.getTags().map(tag => tag.id));
	        }
	      }
	    });
	  },
	  mounted() {
	    this.membersSelector.renderTo(this.$refs.members);
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-create-chat__members" ref="members"></div>
	`
	};

	// @vue/component
	const ButtonPanel = {
	  components: {
	    MessengerButton: im_v2_component_elements.Button
	  },
	  props: {
	    isCreating: {
	      type: Boolean,
	      required: true
	    },
	    createButtonTitle: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['create', 'cancel'],
	  data() {
	    return {};
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-create-chat__buttons">
			<div class="bx-im-content-create-chat__buttons_create">
				<MessengerButton
					:size="ButtonSize.XL"
					:color="ButtonColor.Success"
					:text="createButtonTitle"
					:isLoading="isCreating"
					:isDisabled="isCreating"
					@click="$emit('create')"
				/>
			</div>
			<div class="bx-im-content-create-chat__buttons_cancel">
				<MessengerButton
					:size="ButtonSize.XL"
					:color="ButtonColor.Link"
					:text="loc('IM_CREATE_CHAT_CANCEL')"
					:isDisabled="isCreating"
					@click="$emit('cancel')"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const CreateChatSection = {
	  components: {
	    ExpandAnimation: im_v2_component_animation.ExpandAnimation
	  },
	  props: {
	    name: {
	      type: String,
	      required: true
	    },
	    title: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isFolded: true
	    };
	  },
	  computed: {
	    containerClasses() {
	      return [`--${this.name}`, {
	        '--active': !this.isFolded
	      }];
	    }
	  },
	  methods: {
	    onContainerClick() {
	      if (this.isFolded) {
	        this.isFolded = false;
	      }
	    },
	    onHeaderClick() {
	      if (!this.isFolded) {
	        this.isFolded = true;
	      }
	    }
	  },
	  template: `
		<div :class="containerClasses" class="bx-im-content-create-chat__section bx-im-content-create-chat__section_scope">
			<div @click="isFolded = !isFolded" class="bx-im-content-create-chat__section_header">
				<div class="bx-im-content-create-chat__section_left">
					<div class="bx-im-content-create-chat__section_icon"></div>
					<div class="bx-im-content-create-chat__section_text">{{ title }}</div>
				</div>
				<div class="bx-im-content-create-chat__section_right"></div>	
			</div>
			<ExpandAnimation>
				<div v-if="!isFolded" class="bx-im-content-create-chat__section_content_container">
					<div class="bx-im-content-create-chat__section_content">
						<slot></slot>
					</div>
				</div>
			</ExpandAnimation>
		</div>
	`
	};

	// @vue/component
	const SettingsSection = {
	  components: {
	    CreateChatSection,
	    Dropdown: im_v2_component_elements.Dropdown,
	    Toggle: im_v2_component_elements.Toggle
	  },
	  props: {
	    description: {
	      type: String,
	      required: true
	    },
	    withSearchOption: {
	      type: Boolean,
	      default: true
	    },
	    isAvailableInSearch: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['chatTypeChange', 'descriptionChange'],
	  data() {
	    return {};
	  },
	  computed: {
	    ToggleSize: () => im_v2_component_elements.ToggleSize,
	    descriptionPlaceholderText() {
	      return this.loc('IM_CREATE_CHAT_SETTINGS_SECTION_DESCRIPTION_PLACEHOLDER', {
	        '#BR#': '\n',
	        '#QUOTE_START#': '"',
	        '#QUOTE_END#': '"'
	      });
	    }
	  },
	  methods: {
	    onTypeChange(isAvailableInSearch) {
	      this.$emit('chatTypeChange', isAvailableInSearch);
	    },
	    onDescriptionChange(event) {
	      this.$emit('descriptionChange', event.target.value);
	    },
	    onToggleLabelClick() {
	      this.$refs.toggle.toggle();
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<CreateChatSection name="settings" :title="loc('IM_CREATE_CHAT_SETTINGS_SECTION')">
			<div v-if="withSearchOption" class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_PRIVACY') }}
				</div>
				<div class="bx-im-content-create-chat-settings__type-select">
					<Toggle :size="ToggleSize.M" :isEnabled="isAvailableInSearch" @change="onTypeChange" ref="toggle" />
					<div @click="onToggleLabelClick" class="bx-im-content-create-chat-settings__type-select_label">
						{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_AVAILABLE_FOR_SEARCH') }}
					</div>
				</div>	
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_DESCRIPTION') }}</div>
				<div class="bx-im-content-create-chat-settings__description_container">
					<div class="ui-ctl ui-ctl-textarea ui-ctl-w100 ui-ctl-no-resize">
						<textarea
							@input="onDescriptionChange"
							:value="description"
							:placeholder="descriptionPlaceholderText"
							class="bx-im-content-create-chat-settings__description ui-ctl-element"
						></textarea>
					</div>
				</div>
			</div>
		</CreateChatSection>
	`
	};

	// @vue/component
	const OwnerSelector = {
	  name: 'OwnerSelector',
	  props: {
	    ownerId: {
	      type: Number,
	      required: true
	    }
	  },
	  emits: ['ownerChange'],
	  data() {
	    return {};
	  },
	  created() {
	    this.membersSelector = new ui_entitySelector.TagSelector({
	      multiple: false,
	      maxHeight: 33,
	      placeholder: '',
	      addButtonCaption: this.loc('IM_CREATE_CHAT_USER_SELECTOR_CHANGE_OWNER'),
	      addButtonCaptionMore: this.loc('IM_CREATE_CHAT_USER_SELECTOR_CHANGE_OWNER'),
	      showCreateButton: false,
	      dialogOptions: {
	        enableSearch: false,
	        context: 'IM_CHAT_CREATE',
	        entities: [{
	          id: 'user'
	        }, {
	          id: 'department'
	        }],
	        preselectedItems: [['user', this.ownerId]]
	      },
	      events: {
	        onBeforeTagAdd: event => {
	          const {
	            tag
	          } = event.getData();
	          tag.setDeselectable(false);
	        },
	        onAfterTagAdd: event => {
	          const {
	            tag
	          } = event.getData();
	          this.$emit('ownerChange', tag.id);
	        }
	      }
	    });
	  },
	  mounted() {
	    this.membersSelector.renderTo(this.$refs.owner);
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-create-chat__owner" ref="owner"></div>
	`
	};

	// @vue/component
	const ManagersSelector = {
	  props: {
	    managerIds: {
	      type: Array,
	      required: true
	    }
	  },
	  emits: ['managersChange'],
	  data() {
	    return {};
	  },
	  computed: {
	    currentUserId() {
	      return im_v2_application_core.Core.getUserId();
	    }
	  },
	  created() {
	    const preselectedItems = this.managerIds.map(userId => {
	      return ['user', userId];
	    });
	    this.membersSelector = new ui_entitySelector.TagSelector({
	      maxHeight: 99,
	      placeholder: '',
	      addButtonCaption: this.loc('IM_CREATE_CHAT_RIGHTS_SECTION_ADD_MANAGERS'),
	      addButtonCaptionMore: this.loc('IM_CREATE_CHAT_RIGHTS_SECTION_ADD_MANAGERS'),
	      showCreateButton: false,
	      dialogOptions: {
	        enableSearch: false,
	        context: 'IM_CHAT_CREATE',
	        entities: [{
	          id: 'user'
	        }, {
	          id: 'department'
	        }],
	        preselectedItems
	      },
	      events: {
	        onAfterTagAdd: event => {
	          const selector = event.getTarget();
	          this.$emit('managersChange', selector.getTags().map(tag => tag.id));
	        },
	        onAfterTagRemove: event => {
	          const selector = event.getTarget();
	          this.$emit('managersChange', selector.getTags().map(tag => tag.id));
	        }
	      }
	    });
	  },
	  mounted() {
	    this.membersSelector.renderTo(this.$refs.managers);
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-create-chat__managers" ref="managers"></div>
	`
	};

	const rightsDropdownItems = [{
	  value: im_v2_const.UserRole.member,
	  text: main_core.Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_ROLE_MEMBER')
	}, {
	  value: im_v2_const.UserRole.manager,
	  text: main_core.Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_ROLE_MANAGER')
	}, {
	  value: im_v2_const.UserRole.owner,
	  text: main_core.Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_ROLE_OWNER'),
	  default: true
	}];

	// @vue/component
	const RightsSection = {
	  components: {
	    CreateChatSection,
	    Dropdown: im_v2_component_elements.Dropdown,
	    OwnerSelector,
	    ManagersSelector
	  },
	  props: {
	    ownerId: {
	      type: Number,
	      required: true
	    },
	    managerIds: {
	      type: Array,
	      required: true
	    },
	    manageUsers: {
	      type: String,
	      required: true
	    },
	    manageSettings: {
	      type: String,
	      required: true
	    },
	    manageUi: {
	      type: String,
	      required: true
	    },
	    canPost: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['ownerChange', 'managersChange', 'manageUsersChange', 'manageUiChange', 'canPostChange'],
	  data() {
	    return {};
	  },
	  computed: {
	    PopupType: () => im_v2_const.PopupType,
	    manageUsersItems() {
	      return rightsDropdownItems.map(item => {
	        if (item.value === this.manageUsers) {
	          return {
	            ...item,
	            default: true
	          };
	        }
	        return {
	          ...item
	        };
	      });
	    },
	    manageUiItems() {
	      return rightsDropdownItems.map(item => {
	        if (item.value === this.manageUi) {
	          return {
	            ...item,
	            default: true
	          };
	        }
	        return {
	          ...item
	        };
	      });
	    },
	    canPostItems() {
	      return rightsDropdownItems.map(item => {
	        if (item.value === this.canPost) {
	          return {
	            ...item,
	            default: true
	          };
	        }
	        return {
	          ...item
	        };
	      });
	    }
	  },
	  methods: {
	    onOwnerChange(ownerId) {
	      this.$emit('ownerChange', ownerId);
	    },
	    onManagersChange(managerIds) {
	      this.$emit('managersChange', managerIds);
	    },
	    onManageUsersChange(newValue) {
	      this.$emit('manageUsersChange', newValue);
	    },
	    onManageUiChange(newValue) {
	      this.$emit('manageUiChange', newValue);
	    },
	    onCanPostChange(newValue) {
	      this.$emit('canPostChange', newValue);
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<CreateChatSection name="rights" :title="loc('IM_CREATE_CHAT_RIGHTS_SECTION')">
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_OWNER') }}
				</div>
				<OwnerSelector :ownerId="ownerId" @ownerChange="onOwnerChange" />
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGERS') }}
				</div>
				<ManagersSelector :managerIds="managerIds" @managersChange="onManagersChange" />
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_USERS') }}
				</div>
				<div class="bx-im-content-create-chat-settings__manage-select">
					<Dropdown :items="manageUsersItems" :id="PopupType.createChatManageUsersMenu" @itemChange="onManageUsersChange" />
				</div>
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_UI') }}
				</div>
				<div class="bx-im-content-create-chat-settings__manage-select">
					<Dropdown :items="manageUiItems" :id="PopupType.createChatManageUiMenu" @itemChange="onManageUiChange" />
				</div>
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_SENDING') }}
				</div>
				<div class="bx-im-content-create-chat-settings__manage-select">
					<Dropdown :items="canPostItems" :id="PopupType.createChatCanPostMenu" @itemChange="onCanPostChange" />
				</div>
			</div>
		</CreateChatSection>
	`
	};

	// @vue/component
	const AppearanceSection = {
	  components: {
	    CreateChatSection
	  },
	  data() {
	    return {};
	  },
	  template: `
		<Section name="appearance" title="Appearance" />
	`
	};

	// @vue/component
	const GroupChatCreation = {
	  name: 'GroupChatCreation',
	  components: {
	    TitleInput,
	    ChatAvatar,
	    ChatMembersSelector,
	    SettingsSection,
	    RightsSection,
	    AppearanceSection,
	    ButtonPanel
	  },
	  data() {
	    return {
	      isCreating: false,
	      avatarFile: null,
	      chatTitle: '',
	      chatMembers: [],
	      settings: {
	        isAvailableInSearch: false,
	        description: ''
	      },
	      rights: {
	        ownerId: 0,
	        managerIds: [],
	        manageUsers: '',
	        manageSettings: '',
	        manageUi: '',
	        canPost: ''
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
	    im_v2_lib_createChat.CreateChatManager.getInstance().setChatType(im_v2_const.DialogType.chat);
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
	    onChatTypeChange(isAvailableInSearch) {
	      this.settings.isAvailableInSearch = isAvailableInSearch;
	    },
	    onDescriptionChange(description) {
	      this.settings.description = description;
	    },
	    onManageUsersChange(newValue) {
	      this.rights.manageUsers = newValue;
	    },
	    onManageUiChange(newValue) {
	      this.rights.manageUi = newValue;
	    },
	    onCanPostChange(newValue) {
	      this.rights.canPost = newValue;
	    },
	    async onCreateClick() {
	      this.isCreating = true;
	      const newDialogId = await this.getChatService().createChat({
	        title: this.chatTitle,
	        avatar: this.avatarFile,
	        members: this.chatMembers,
	        ownerId: this.rights.ownerId,
	        managers: this.rights.managerIds,
	        isAvailableInSearch: this.settings.isAvailableInSearch,
	        description: this.settings.description,
	        manageUsers: this.rights.manageUsers,
	        manageUi: this.rights.manageUi,
	        manageSettings: this.rights.manageSettings,
	        canPost: this.rights.canPost
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
	    onAvatarChange(newAvatarFile) {
	      this.avatarFile = newAvatarFile;
	      im_v2_lib_createChat.CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
	    },
	    onScroll() {
	      var _MenuManager$getMenuB, _MenuManager$getMenuB2, _MenuManager$getMenuB3;
	      (_MenuManager$getMenuB = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUsersMenu)) == null ? void 0 : _MenuManager$getMenuB.close();
	      (_MenuManager$getMenuB2 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUiMenu)) == null ? void 0 : _MenuManager$getMenuB2.close();
	      (_MenuManager$getMenuB3 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatCanPostMenu)) == null ? void 0 : _MenuManager$getMenuB3.close();
	    },
	    onLayoutChange(event) {
	      const {
	        to
	      } = event.getData();
	      if (to.name === im_v2_const.Layout.createChat.name && to.entityId !== im_v2_const.DialogType.chat) {
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
	        manageUsers,
	        manageUi,
	        manageSettings
	      } = im_v2_lib_permission.PermissionManager.getInstance().getDefaultRolesForActionGroups();
	      this.rights.manageUsers = manageUsers;
	      this.rights.manageUi = manageUi;
	      this.rights.manageSettings = manageSettings;
	      this.rights.canPost = im_v2_const.UserRole.member;
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
		<div class="bx-im-content-create-chat__content" @scroll="onScroll">
			<div class="bx-im-content-create-chat__header">
				<ChatAvatar :avatarFile="avatarFile" :chatTitle="chatTitle" @avatarChange="onAvatarChange" />
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_CHAT_TITLE_PLACEHOLDER')" />
			</div>
			<ChatMembersSelector :chatMembers="chatMembers" @membersChange="onMembersChange" />
			<SettingsSection
				:isAvailableInSearch="settings.isAvailableInSearch"
				:description="settings.description"
				@chatTypeChange="onChatTypeChange"
				@descriptionChange="onDescriptionChange"
			/>
			<RightsSection
				:ownerId="rights.ownerId"
				:managerIds="rights.managerIds"
				:manageUsers="rights.manageUsers"
				:manageUi="rights.manageUi"
				:manageSettings="rights.manageSettings"
				:canPost="rights.canPost"
				@ownerChange="onOwnerChange"
				@managersChange="onManagersChange"
				@manageUsersChange="onManageUsersChange"
				@manageUiChange="onManageUiChange"
				@canPostChange="onCanPostChange"
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
	const ConferenceSection = {
	  components: {
	    CreateChatSection,
	    Dropdown: im_v2_component_elements.Dropdown,
	    Toggle: im_v2_component_elements.Toggle
	  },
	  props: {
	    passwordNeeded: {
	      type: Boolean,
	      required: true
	    },
	    password: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['passwordNeededChange', 'passwordChange'],
	  data() {
	    return {};
	  },
	  computed: {
	    ToggleSize: () => im_v2_component_elements.ToggleSize
	  },
	  methods: {
	    onPasswordNeededChange(passwordNeeded) {
	      this.$emit('passwordNeededChange', passwordNeeded);
	    },
	    onPasswordChange(event) {
	      this.$emit('passwordChange', event.target.value);
	    },
	    onToggleLabelClick() {
	      this.$refs.toggle.toggle();
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<CreateChatSection name="conference" :title="loc('IM_CREATE_CHAT_CONFERENCE_SECTION')">
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_CONFERENCE_SECTION_PRIVACY') }}
				</div>
				<div class="bx-im-content-create-chat-settings__type-select">
					<Toggle :size="ToggleSize.M" :isEnabled="passwordNeeded" @change="onPasswordNeededChange" ref="toggle" />
					<div @click="onToggleLabelClick" class="bx-im-content-create-chat-settings__type-select_label">
						{{ loc('IM_CREATE_CHAT_CONFERENCE_SECTION_USE_PASSWORD') }}
					</div>
				</div>
				<div v-if="passwordNeeded" class="bx-im-content-create-chat-conference__password-container ui-ctl ui-ctl-textbox">
					<input
						type="text"
						class="bx-im-content-create-chat-conference__password-input ui-ctl-element"
						:value="password"
						:placeholder="loc('IM_CREATE_CHAT_CONFERENCE_SECTION_PASSWORD_PLACEHOLDER')"
						@input="onPasswordChange"
					/>
				</div>
			</div>
		</CreateChatSection>
	`
	};

	// @vue/component
	const ConferenceCreation = {
	  name: 'ConferenceCreation',
	  components: {
	    TitleInput,
	    ChatAvatar,
	    ChatMembersSelector,
	    SettingsSection,
	    RightsSection,
	    AppearanceSection,
	    ConferenceSection,
	    ButtonPanel
	  },
	  data() {
	    return {
	      isCreating: false,
	      avatarFile: null,
	      chatTitle: '',
	      chatMembers: [],
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
	        manageUsers: '',
	        manageSettings: '',
	        manageUi: '',
	        canPost: ''
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
	    im_v2_lib_createChat.CreateChatManager.getInstance().setChatType(im_v2_const.DialogType.videoconf);
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
	    onManageUsersChange(newValue) {
	      this.rights.manageUsers = newValue;
	    },
	    onManageUiChange(newValue) {
	      this.rights.manageUi = newValue;
	    },
	    onCanPostChange(newValue) {
	      this.rights.canPost = newValue;
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
	      const newDialogId = await this.getChatService().createChat({
	        type: im_v2_const.DialogType.videoconf,
	        title: this.chatTitle,
	        avatar: this.avatarFile,
	        members: this.chatMembers,
	        ownerId: this.rights.ownerId,
	        managers: this.rights.managerIds,
	        description: this.settings.description,
	        manageUsers: this.rights.manageUsers,
	        manageUi: this.rights.manageUi,
	        manageSettings: this.rights.manageSettings,
	        canPost: this.rights.canPost,
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
	      var _MenuManager$getMenuB, _MenuManager$getMenuB2, _MenuManager$getMenuB3;
	      (_MenuManager$getMenuB = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUsersMenu)) == null ? void 0 : _MenuManager$getMenuB.close();
	      (_MenuManager$getMenuB2 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatManageUiMenu)) == null ? void 0 : _MenuManager$getMenuB2.close();
	      (_MenuManager$getMenuB3 = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.createChatCanPostMenu)) == null ? void 0 : _MenuManager$getMenuB3.close();
	    },
	    onLayoutChange(event) {
	      const {
	        to
	      } = event.getData();
	      if (to.name === im_v2_const.Layout.createChat.name && to.entityId !== im_v2_const.DialogType.videoconf) {
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
	        manageUsers,
	        manageUi,
	        manageSettings
	      } = im_v2_lib_permission.PermissionManager.getInstance().getDefaultRolesForActionGroups();
	      this.rights.manageUsers = manageUsers;
	      this.rights.manageUi = manageUi;
	      this.rights.manageSettings = manageSettings;
	      this.rights.canPost = im_v2_const.UserRole.member;
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
		<div class="bx-im-content-create-chat__content" @scroll="onScroll">
			<div class="bx-im-content-create-chat__header">
				<ChatAvatar :avatarFile="avatarFile" :chatTitle="chatTitle" @avatarChange="onAvatarChange" />
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_CONFERENCE_TITLE_PLACEHOLDER')" />
			</div>
			<ChatMembersSelector :chatMembers="chatMembers" @membersChange="onMembersChange" />
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
				:manageUsers="rights.manageUsers"
				:manageUi="rights.manageUi"
				:manageSettings="rights.manageSettings"
				:canPost="rights.canPost"
				@ownerChange="onOwnerChange"
				@managersChange="onManagersChange"
				@manageUsersChange="onManageUsersChange"
				@manageUiChange="onManageUiChange"
				@canPostChange="onCanPostChange"
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
	const CreateChatContent = {
	  name: 'CreateChatContent',
	  components: {
	    GroupChatCreation,
	    ConferenceCreation
	  },
	  props: {
	    entityId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    DialogType: () => im_v2_const.DialogType,
	    chatType() {
	      return this.entityId;
	    }
	  },
	  template: `
		<div class="bx-im-content-create-chat__container bx-im-content-create-chat__scope">
			<GroupChatCreation v-if="chatType === DialogType.chat" />
			<ConferenceCreation v-else-if="chatType === DialogType.videoconf" />
		</div>
	`
	};

	exports.CreateChatContent = CreateChatContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.Messenger.v2.Component.Animation,BX.UI.EntitySelector,BX,BX.Event,BX.Main,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Const,BX,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=create-chat-content.bundle.js.map
