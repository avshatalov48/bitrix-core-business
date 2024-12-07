/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_lib_feature,im_v2_component_animation,ui_entitySelector,main_core,im_v2_component_elements,im_v2_application_core,im_v2_lib_permission,ui_forms,im_v2_lib_createChat,ui_notification,main_core_events,main_popup,im_public,im_v2_lib_analytics,im_v2_provider_service,im_v2_const,im_v2_lib_confirm) {
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
	  mounted() {
	    this.$refs.titleInput.focus();
	  },
	  methods: {
	    onInput(event) {
	      this.$emit('update:modelValue', event.target.value);
	    }
	  },
	  template: `
		<div class="bx-im-chat-forms-title-input__container">
			<input
				:value="modelValue"
				:placeholder="placeholder"
				@input="onInput"
				class="bx-im-chat-forms-title-input__input"
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
	    existingAvatarUrl: {
	      type: String,
	      default: ''
	    },
	    chatTitle: {
	      type: String,
	      required: true
	    },
	    squared: {
	      type: Boolean,
	      required: false,
	      default: false
	    }
	  },
	  emits: ['avatarChange'],
	  computed: {
	    preparedAvatar() {
	      if (!this.avatarFile) {
	        return null;
	      }
	      return URL.createObjectURL(this.avatarFile);
	    },
	    avatarToShow() {
	      return this.preparedAvatar || this.existingAvatarUrl;
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
		<div 
			class="bx-im-chat-forms-chat-avatar__container" 
			:class="{'--squared': squared}" 
			@click="onAvatarChangeClick"
		>
			<img 
				v-if="avatarToShow" 
				:src="avatarToShow" 
				:alt="chatTitle" 
				class="bx-im-chat-forms-chat-avatar__image" 
			/>
		</div>
		<input 
			type="file" 
			@change="onAvatarSelect" 
			accept="image/*" 
			class="bx-im-chat-forms-chat-avatar__input" 
			ref="avatarInput"
		>
	`
	};

	// @vue/component
	const ChatMembersSelector = {
	  props: {
	    chatMembers: {
	      type: Array,
	      required: true
	    },
	    customElements: {
	      type: Array,
	      default: () => []
	    }
	  },
	  emits: ['membersChange'],
	  created() {
	    const addButtonCaption = this.loc('IM_CREATE_CHAT_USER_SELECTOR_ADD_MEMBERS_V2');
	    this.membersSelector = new ui_entitySelector.TagSelector({
	      maxHeight: 99,
	      placeholder: '',
	      addButtonCaption,
	      addButtonCaptionMore: addButtonCaption,
	      showCreateButton: false,
	      items: this.customElements,
	      dialogOptions: {
	        enableSearch: true,
	        alwaysShowLabels: true,
	        context: 'IM_CHAT_CREATE',
	        entities: this.getEntitiesConfig(),
	        preselectedItems: this.chatMembers,
	        undeselectedItems: [['user', im_v2_application_core.Core.getUserId()]],
	        events: {
	          'Item:onSelect': this.onItemsChange,
	          'Item:onDeselect': this.onItemsChange
	        }
	      }
	    });
	  },
	  mounted() {
	    this.membersSelector.renderTo(this.$refs.members);
	  },
	  methods: {
	    getEntitiesConfig() {
	      const entitiesConfig = [{
	        id: 'user'
	      }];
	      const allowDepartments = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.chatDepartments);
	      if (allowDepartments) {
	        entitiesConfig.push({
	          id: 'department',
	          options: {
	            selectMode: 'usersAndDepartments',
	            allowFlatDepartments: true,
	            allowSelectRootDepartment: true
	          }
	        });
	      } else {
	        entitiesConfig.push({
	          id: 'department'
	        });
	      }
	      return entitiesConfig;
	    },
	    onItemsChange(event) {
	      const dialog = event.getTarget();
	      const selectedItems = dialog.getSelectedItems();
	      this.$emit('membersChange', selectedItems.map(item => this.prepareTag(item)));
	    },
	    prepareTag(tag) {
	      return [tag.getEntityId(), tag.getId()];
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-chat-forms-chat-members__container" ref="members"></div>
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
		<div class="bx-im-chat-forms-button-panel__container">
			<MessengerButton
				:size="ButtonSize.XL"
				:color="ButtonColor.Success"
				:text="createButtonTitle"
				:isLoading="isCreating"
				:isDisabled="isCreating"
				@click="$emit('create')"
				class="bx-im-chat-forms-button-panel__create-button"
			/>
			<MessengerButton
				:size="ButtonSize.XL"
				:color="ButtonColor.Link"
				:text="loc('IM_CREATE_CHAT_CANCEL')"
				:isDisabled="isCreating"
				@click="$emit('cancel')"
			/>
		</div>
	`
	};

	// @vue/component
	const CreateChatHeading = {
	  name: 'CreateChatHeading',
	  components: {
	    ChatHint: im_v2_component_elements.ChatHint
	  },
	  props: {
	    text: {
	      type: String,
	      required: true
	    },
	    hintText: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  computed: {
	    preparedText() {
	      return this.text.replace('#SUBTITLE_START#', '<span class="bx-im-content-create-chat__subheading">').replace('#SUBTITLE_END#', '</span>');
	    }
	  },
	  template: `
		<div class="bx-im-content-create-chat__heading_container">
			<div class="bx-im-content-create-chat__heading" v-html="preparedText"></div>
			<ChatHint v-if="hintText" :text="hintText" />
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
	    },
	    alwaysOpened: {
	      type: Boolean,
	      required: false,
	      default: false
	    }
	  },
	  data() {
	    return {
	      isOpened: false
	    };
	  },
	  computed: {
	    containerClasses() {
	      return [`--${this.name}`, {
	        '--active': this.isOpened
	      }];
	    }
	  },
	  created() {
	    if (this.alwaysOpened) {
	      this.isOpened = true;
	    }
	  },
	  methods: {
	    onHeaderClick() {
	      if (this.alwaysOpened) {
	        return;
	      }
	      this.isOpened = !this.isOpened;
	    }
	  },
	  template: `
		<div :class="containerClasses" class="bx-im-content-create-chat__section bx-im-content-create-chat__section_scope">
			<div @click="onHeaderClick" class="bx-im-content-create-chat__section_header">
				<div class="bx-im-content-create-chat__section_left">
					<div class="bx-im-content-create-chat__section_icon"></div>
					<div class="bx-im-content-create-chat__section_text">{{ title }}</div>
				</div>
				<div v-if="!alwaysOpened" class="bx-im-content-create-chat__section_right"></div>	
			</div>
			<ExpandAnimation>
				<div v-if="isOpened" class="bx-im-content-create-chat__section_content_container">
					<div class="bx-im-content-create-chat__section_content">
						<slot></slot>
					</div>
				</div>
			</ExpandAnimation>
		</div>
	`
	};

	// @vue/component
	const TextareaInput = {
	  name: 'TextareaInput',
	  props: {
	    value: {
	      type: String,
	      default: ''
	    },
	    placeholder: {
	      type: String,
	      required: true
	    },
	    border: {
	      type: Boolean,
	      required: false,
	      default: true
	    },
	    rounded: {
	      type: Boolean,
	      required: false,
	      default: true
	    }
	  },
	  emits: ['input'],
	  computed: {
	    containerClasses() {
	      return {
	        '--no-border': !this.border,
	        '--rounded': this.rounded
	      };
	    }
	  },
	  methods: {
	    onInput(event) {
	      this.$emit('input', event.target.value);
	    }
	  },
	  template: `
		<div class="bx-im-content-create-chat__textarea_container" :class="containerClasses">
			<div class="ui-ctl ui-ctl-textarea ui-ctl-w100 ui-ctl-no-resize">
				<textarea
					:value="value"
					:placeholder="placeholder"
					@input="onInput"
					class="bx-im-content-create-chat__textarea ui-ctl-element"
				></textarea>
			</div>
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
		<div class="bx-im-content-create-chat-radio__container">
			<label v-for="option in options" class="bx-im-content-create-chat-radio__item ui-ctl ui-ctl-radio">
				<input type="radio" class="ui-ctl-element" :name="groupName" :checked="option.selected" @input="onInput(option)">
				<div class="ui-ctl-label-text">
					<!-- Text -->
					<div v-if="option.html" class="bx-im-content-create-chat-radio__label_title" v-html="option.text"></div>
					<div v-else class="bx-im-content-create-chat-radio__label_title">{{ option.text }}</div>
					<!-- Subtext -->
					<template v-if="option.subtext">
						<div v-if="option.html" class="bx-im-content-create-chat-radio__label_subtitle" v-html="option.subtext"></div>
						<div v-else class="bx-im-content-create-chat-radio__label_subtitle">{{ option.subtext }}</div>
					</template>
				</div>
			</label>
		</div>
	`
	};

	// @vue/component
	const SettingsSection = {
	  components: {
	    CreateChatSection,
	    CreateChatHeading,
	    Dropdown: im_v2_component_elements.Dropdown,
	    TextareaInput,
	    RadioOption
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
	  computed: {
	    privacyOptions() {
	      return [{
	        value: false,
	        text: this.loc('IM_CREATE_CHAT_SETTINGS_SECTION_PRIVATE_TITLE'),
	        subtext: this.loc('IM_CREATE_CHAT_SETTINGS_SECTION_PRIVATE_SUBTITLE'),
	        selected: !this.isAvailableInSearch
	      }, {
	        value: true,
	        text: this.loc('IM_CREATE_CHAT_SETTINGS_SECTION_OPEN_TITLE'),
	        subtext: this.loc('IM_CREATE_CHAT_SETTINGS_SECTION_OPEN_SUBTITLE'),
	        selected: this.isAvailableInSearch
	      }];
	    },
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
	    onDescriptionChange(description) {
	      this.$emit('descriptionChange', description);
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<CreateChatSection name="settings" :title="loc('IM_CREATE_CHAT_SETTINGS_SECTION')">
			<div v-if="withSearchOption" class="bx-im-content-create-chat__section_block">
				<CreateChatHeading :text="loc('IM_CREATE_CHAT_SETTINGS_SECTION_PRIVACY')" />
				<RadioOption :items="privacyOptions" @change="onTypeChange" />
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<CreateChatHeading :text="loc('IM_CREATE_CHAT_SETTINGS_SECTION_DESCRIPTION')" />
				<div class="bx-im-chat-forms-settings__description_container">
					<TextareaInput
						:value="description"
						:placeholder="descriptionPlaceholderText"
						:rounded="false"
						@input="onDescriptionChange"
					/>
				</div>
			</div>
		</CreateChatSection>
	`
	};

	// @vue/component
	const RoleSelector = {
	  name: 'RoleSelector',
	  components: {
	    Dropdown: im_v2_component_elements.Dropdown,
	    ChatHint: im_v2_component_elements.ChatHint
	  },
	  props: {
	    title: {
	      type: String,
	      required: true
	    },
	    dropdownItems: {
	      type: Array,
	      required: true
	    },
	    dropdownId: {
	      type: String,
	      required: true
	    },
	    hintText: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  emits: ['itemChange'],
	  template: `
		<div class="bx-im-content-create-chat__section_block">
			<div class="bx-im-content-create-chat__section-header">
				<div class="bx-im-content-create-chat__section-heading">
					{{ title }}
				</div>
				<ChatHint v-if="hintText" :text="hintText" />
			</div>
			<div class="bx-im-chat-forms-chat-settings__manage-select">
				<Dropdown
					:items="dropdownItems"
					:id="dropdownId" 
					@itemChange="$emit('itemChange', $event)"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const UserSelector = {
	  name: 'UserSelector',
	  components: {
	    ChatHint: im_v2_component_elements.ChatHint
	  },
	  props: {
	    title: {
	      type: String,
	      required: true
	    },
	    hintText: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  template: `
		<div class="bx-im-content-create-chat__section_block">
			<div class="bx-im-content-create-chat__section-header">
				<div class="bx-im-content-create-chat__section-heading">
					{{ title }}
				</div>
				<ChatHint v-if="hintText" :text="hintText" />
			</div>
			<slot></slot>
		</div>
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
		<div class="bx-im-chat-forms-sections-owner__container" ref="owner"></div>
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
		<div class="bx-im-chat-forms-sections-managers__container" ref="managers"></div>
	`
	};

	const rightsDropdownItems = [{
	  value: im_v2_const.UserRole.member,
	  text: main_core.Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_ROLE_MEMBER')
	}, {
	  value: im_v2_const.UserRole.manager,
	  text: main_core.Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_ROLE_MANAGER_V2')
	}, {
	  value: im_v2_const.UserRole.owner,
	  text: main_core.Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_ROLE_OWNER'),
	  default: true
	}];

	const BlocksByChatType = {
	  [im_v2_const.ChatType.channel]: new Set([im_v2_const.ChatActionGroup.manageUsersAdd, im_v2_const.ChatActionGroup.manageUsersDelete, im_v2_const.ChatActionGroup.manageMessages]),
	  default: new Set([im_v2_const.ChatActionGroup.manageUsersAdd, im_v2_const.ChatActionGroup.manageUsersDelete, im_v2_const.ChatActionGroup.manageUi, im_v2_const.ChatActionGroup.manageMessages])
	};
	const CanAddUsersCaptionByChatType = {
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_CREATE_CHANNEL_RIGHTS_SECTION_MANAGE_USERS_ADD'),
	  default: main_core.Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_USERS_ADD')
	};
	const CanKickUsersCaptionByChatType = {
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_CREATE_CHANNEL_RIGHTS_SECTION_MANAGE_USERS_DELETE'),
	  default: main_core.Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_USERS_DELETE')
	};
	const CanSendMessageCaptionByChatType = {
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_CREATE_CHANNEL_RIGHTS_SECTION_MANAGE_SENDING'),
	  default: main_core.Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_SENDING_MSGVER_1')
	};
	const OwnerHintByChatType = {
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_CREATE_CHANNEL_OWNER_HINT'),
	  default: main_core.Loc.getMessage('IM_CREATE_CHAT_OWNER_HINT')
	};
	const ManagerHintByChatType = {
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_CREATE_CHANNEL_MANAGER_HINT'),
	  default: main_core.Loc.getMessage('IM_CREATE_CHAT_MANAGER_HINT')
	};
	const AddUsersHintByChatType = {
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_CREATE_CHANNEL_MANAGE_USERS_ADD_HINT'),
	  default: main_core.Loc.getMessage('IM_CREATE_CHAT_MANAGE_USERS_ADD_HINT')
	};
	const DeleteUsersHintByChatType = {
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_CREATE_CHANNEL_MANAGE_USERS_DELETE_HINT'),
	  default: main_core.Loc.getMessage('IM_CREATE_CHAT_MANAGE_USERS_DELETE_HINT')
	};
	const ManageUiHintByChatType = {
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_CREATE_CHANNEL_MANAGE_UI_HINT'),
	  default: main_core.Loc.getMessage('IM_CREATE_CHAT_MANAGE_UI_HINT')
	};
	const SendMessagesHintByChatType = {
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_CREATE_CHANNEL_MANAGE_SENDING_HINT'),
	  default: main_core.Loc.getMessage('IM_CREATE_CHAT_MANAGE_SENDING_HINT')
	};

	// @vue/component
	const RightsSection = {
	  name: 'RightsSection',
	  components: {
	    CreateChatSection,
	    RoleSelector,
	    UserSelector,
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
	    manageUsersAdd: {
	      type: String,
	      required: true
	    },
	    manageUsersDelete: {
	      type: String,
	      required: true
	    },
	    manageUi: {
	      type: String,
	      required: true
	    },
	    manageMessages: {
	      type: String,
	      required: true
	    },
	    chatType: {
	      type: String,
	      default: im_v2_const.ChatType.chat
	    }
	  },
	  emits: ['ownerChange', 'managersChange', 'manageUsersAddChange', 'manageUsersDeleteChange', 'manageUiChange', 'manageMessagesChange'],
	  computed: {
	    PopupType: () => im_v2_const.PopupType,
	    manageUsersAddItems() {
	      return this.prepareDropdownItems(this.manageUsersAdd);
	    },
	    manageUsersDeleteItems() {
	      return this.prepareDropdownItems(this.manageUsersDelete);
	    },
	    manageUiItems() {
	      return this.prepareDropdownItems(this.manageUi);
	    },
	    manageMessagesItems() {
	      return this.prepareDropdownItems(this.manageMessages);
	    },
	    showManageUiBlock() {
	      var _BlocksByChatType$thi;
	      const blocksByType = (_BlocksByChatType$thi = BlocksByChatType[this.chatType]) != null ? _BlocksByChatType$thi : BlocksByChatType.default;
	      return blocksByType.has(im_v2_const.ChatActionGroup.manageUi);
	    },
	    canAddUsersCaption() {
	      var _CanAddUsersCaptionBy;
	      return (_CanAddUsersCaptionBy = CanAddUsersCaptionByChatType[this.chatType]) != null ? _CanAddUsersCaptionBy : CanAddUsersCaptionByChatType.default;
	    },
	    canKickUsersCaption() {
	      var _CanKickUsersCaptionB;
	      return (_CanKickUsersCaptionB = CanKickUsersCaptionByChatType[this.chatType]) != null ? _CanKickUsersCaptionB : CanKickUsersCaptionByChatType.default;
	    },
	    canSendCaption() {
	      var _CanSendMessageCaptio;
	      return (_CanSendMessageCaptio = CanSendMessageCaptionByChatType[this.chatType]) != null ? _CanSendMessageCaptio : CanSendMessageCaptionByChatType.default;
	    },
	    ownerHint() {
	      var _OwnerHintByChatType$;
	      return (_OwnerHintByChatType$ = OwnerHintByChatType[this.chatType]) != null ? _OwnerHintByChatType$ : OwnerHintByChatType.default;
	    },
	    managerHint() {
	      var _ManagerHintByChatTyp;
	      return (_ManagerHintByChatTyp = ManagerHintByChatType[this.chatType]) != null ? _ManagerHintByChatTyp : ManagerHintByChatType.default;
	    },
	    addUsersHint() {
	      var _AddUsersHintByChatTy;
	      return (_AddUsersHintByChatTy = AddUsersHintByChatType[this.chatType]) != null ? _AddUsersHintByChatTy : AddUsersHintByChatType.default;
	    },
	    deleteUsersHint() {
	      var _DeleteUsersHintByCha;
	      return (_DeleteUsersHintByCha = DeleteUsersHintByChatType[this.chatType]) != null ? _DeleteUsersHintByCha : DeleteUsersHintByChatType.default;
	    },
	    manageUiHint() {
	      var _ManageUiHintByChatTy;
	      return (_ManageUiHintByChatTy = ManageUiHintByChatType[this.chatType]) != null ? _ManageUiHintByChatTy : ManageUiHintByChatType.default;
	    },
	    sendMessagesHint() {
	      var _SendMessagesHintByCh;
	      return (_SendMessagesHintByCh = SendMessagesHintByChatType[this.chatType]) != null ? _SendMessagesHintByCh : SendMessagesHintByChatType.default;
	    }
	  },
	  methods: {
	    prepareDropdownItems(defaultValue) {
	      return rightsDropdownItems.map(item => {
	        return {
	          ...item,
	          default: item.value === defaultValue
	        };
	      });
	    },
	    onOwnerChange(ownerId) {
	      this.$emit('ownerChange', ownerId);
	    },
	    onManagersChange(managerIds) {
	      this.$emit('managersChange', managerIds);
	    },
	    onManageUsersAddChange(newValue) {
	      this.$emit('manageUsersAddChange', newValue);
	    },
	    onManageUsersDeleteChange(newValue) {
	      this.$emit('manageUsersDeleteChange', newValue);
	    },
	    onManageUiChange(newValue) {
	      this.$emit('manageUiChange', newValue);
	    },
	    onManageMessagesChange(newValue) {
	      this.$emit('manageMessagesChange', newValue);
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<CreateChatSection name="rights" :title="loc('IM_CREATE_CHAT_RIGHTS_SECTION')">
			<UserSelector :title="loc('IM_CREATE_CHAT_SETTINGS_SECTION_OWNER')" :hintText="ownerHint">
				<OwnerSelector :ownerId="ownerId" @ownerChange="onOwnerChange" />
			</UserSelector>
			<UserSelector :title="loc('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGERS')" :hintText="managerHint">
				<ManagersSelector :managerIds="managerIds" @managersChange="onManagersChange" />
			</UserSelector>
			<RoleSelector
				:title="canAddUsersCaption"
				:hintText="addUsersHint"
				:dropdownId="PopupType.createChatManageUsersAddMenu"
				:dropdownItems="manageUsersAddItems"
				@itemChange="onManageUsersAddChange"
			/>
			<RoleSelector
				:title="canKickUsersCaption"
				:hintText="deleteUsersHint"
				:dropdownId="PopupType.createChatManageUsersDeleteMenu"
				:dropdownItems="manageUsersDeleteItems"
				@itemChange="onManageUsersDeleteChange"
			/>
			<RoleSelector
				v-if="showManageUiBlock"
				:title="loc('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_UI_MSGVER_2')"
				:hintText="manageUiHint"
				:dropdownId="PopupType.createChatManageUiMenu"
				:dropdownItems="manageUiItems"
				@itemChange="onManageUiChange"
			/>
			<RoleSelector
				:title="canSendCaption"
				:hintText="sendMessagesHint"
				:dropdownId="PopupType.createChatManageMessagesMenu"
				:dropdownItems="manageMessagesItems"
				@itemChange="onManageMessagesChange"
			/>
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
	    ButtonPanel,
	    CreateChatHeading
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
	const ConferenceSection = {
	  components: {
	    CreateChatSection,
	    CreateChatHeading,
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
	      this.onPasswordNeededChange(!this.passwordNeeded);
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<CreateChatSection name="conference" :title="loc('IM_CREATE_CHAT_CONFERENCE_SECTION')">
			<div class="bx-im-content-create-chat__section_block">
				<CreateChatHeading :text="loc('IM_CREATE_CHAT_CONFERENCE_SECTION_PRIVACY')" />
				<div @click="onToggleLabelClick" class="bx-im-chat-forms-chat-settings__type-select">
					<Toggle :size="ToggleSize.M" :isEnabled="passwordNeeded" />
					<div class="bx-im-chat-forms-chat-settings__type-select_label">
						{{ loc('IM_CREATE_CHAT_CONFERENCE_SECTION_USE_PASSWORD') }}
					</div>
				</div>
				<div v-if="passwordNeeded" class="bx-im-chat-forms-chat-settings__password-container ui-ctl ui-ctl-textbox">
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
	    ButtonPanel,
	    CreateChatHeading
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
				<ChatAvatar :avatarFile="avatarFile" :chatTitle="chatTitle" @avatarChange="onAvatarChange" :squared="true" />
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
	const PrivacySection = {
	  components: {
	    CreateChatSection,
	    RadioOption
	  },
	  props: {
	    withSearchOption: {
	      type: Boolean,
	      default: true
	    },
	    isAvailableInSearch: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['chatTypeChange'],
	  data() {
	    return {};
	  },
	  computed: {
	    privacyOptions() {
	      return [{
	        value: true,
	        text: this.loc('IM_CREATE_CHAT_PRIVACY_SECTION_OPEN_TITLE'),
	        subtext: this.loc('IM_CREATE_CHAT_PRIVACY_SECTION_OPEN_SUBTITLE_V2'),
	        selected: this.isAvailableInSearch
	      }, {
	        value: false,
	        text: this.loc('IM_CREATE_CHAT_PRIVACY_SECTION_PRIVATE_TITLE'),
	        subtext: this.loc('IM_CREATE_CHAT_PRIVACY_SECTION_PRIVATE_SUBTITLE_V2'),
	        selected: !this.isAvailableInSearch
	      }];
	    }
	  },
	  methods: {
	    onTypeChange(isAvailableInSearch) {
	      this.$emit('chatTypeChange', isAvailableInSearch);
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<CreateChatSection
			name="privacy"
			:title="loc('IM_CREATE_CHAT_PRIVACY_SECTION_V2')"
			:alwaysOpened="true"
		>
			<div class="bx-im-content-create-chat__section_block">
				<RadioOption :items="privacyOptions" @change="onTypeChange" />
			</div>
		</CreateChatSection>
	`
	};

	// @vue/component
	const ChannelCreation = {
	  name: 'ChannelCreation',
	  components: {
	    TitleInput,
	    ChatAvatar,
	    ChatMembersSelector,
	    SettingsSection,
	    RightsSection,
	    AppearanceSection,
	    PrivacySection,
	    ButtonPanel,
	    TextareaInput,
	    CreateChatHeading
	  },
	  data() {
	    return {
	      isCreating: false,
	      avatarFile: null,
	      chatTitle: '',
	      chatMembers: [['user', im_v2_application_core.Core.getUserId()]],
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
	    ChatType: () => im_v2_const.ChatType
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
				<ChatAvatar :avatarFile="avatarFile" :chatTitle="chatTitle" @avatarChange="onAvatarChange" :squared="true" />
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

	const CollabCreation = {
	  name: 'CollabCreation',
	  components: {
	    TitleInput,
	    ChatAvatar,
	    CreateChatHeading,
	    ChatMembersSelector,
	    ButtonPanel
	  },
	  data() {
	    return {
	      isCreating: false,
	      avatarFile: null,
	      chatTitle: '',
	      chatMembers: []
	    };
	  },
	  watch: {
	    chatTitle(newValue) {
	      im_v2_lib_createChat.CreateChatManager.getInstance().setChatTitle(newValue);
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
	        chatMembers
	      } = savedFields;
	      this.chatTitle = chatTitle;
	      this.avatarFile = avatarFile;
	      this.chatMembers = chatMembers;
	    },
	    saveFields() {
	      im_v2_lib_createChat.CreateChatManager.getInstance().saveFields({
	        chatTitle: this.chatTitle,
	        avatarFile: this.avatarFile,
	        chatMembers: this.chatMembers
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
	    onMembersChange(currentTags) {
	      this.chatMembers = currentTags;
	    },
	    async onCreateClick() {
	      this.isCreating = true;
	      const {
	        newDialogId
	      } = await this.getChatService().createCollab({
	        title: this.chatTitle,
	        avatar: this.avatarFile,
	        memberEntities: this.chatMembers
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
		<div class="bx-im-content-chat-forms__content --collab" @scroll="onScroll">
			<div class="bx-im-content-chat-forms__header">
				<ChatAvatar :avatarFile="avatarFile" :chatTitle="chatTitle" @avatarChange="onAvatarChange" :squared="true" />
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_COLLAB_TITLE_PLACEHOLDER')" />
			</div>
			<CreateChatHeading :text="loc('IM_CREATE_COLLAB_MEMBERS_TITLE')" />
			<div class="bx-im-content-chat-forms__members_container">
				<ChatMembersSelector :chatMembers="chatMembers" @membersChange="onMembersChange" />
			</div>
		</div>
		<ButtonPanel
			:isCreating="isCreating"
			:createButtonTitle="loc('IM_CREATE_COLLAB_CONFIRM')"
			@create="onCreateClick"
			@cancel="onCancelClick"
		/>
	`
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
	    TitleInput,
	    ChatAvatar,
	    ButtonPanel,
	    CreateChatHeading,
	    ChatMembersSelector,
	    SettingsSection,
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
	      im_v2_lib_analytics.Analytics.getInstance().onSubmitChatEditForm(this.dialogId);
	      this.isUpdating = true;
	      await this.getChatService().updateChat(this.chatId, {
	        title: this.chatTitle,
	        avatar: this.avatarFile,
	        managers: this.dialog.managerList,
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
	      im_v2_lib_analytics.Analytics.getInstance().onCancelChatEditForm(this.dialogId);
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
	      const confirmResult = await im_v2_lib_confirm.showExitUpdateGroupChatConfirm();
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
	    TitleInput,
	    ChatAvatar,
	    ChatMembersSelector,
	    SettingsSection,
	    RightsSection,
	    AppearanceSection,
	    PrivacySection,
	    ButtonPanel,
	    TextareaInput,
	    CreateChatHeading
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
	      im_v2_lib_analytics.Analytics.getInstance().onSubmitChatEditForm(this.dialogId);
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
	        managers: this.dialog.managerList,
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
	      im_v2_lib_analytics.Analytics.getInstance().onCancelChatEditForm(this.dialogId);
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
	      const confirmResult = await im_v2_lib_confirm.showExitUpdateChannelConfirm();
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
					@avatarChange="onAvatarChange" 
					:squared="true" 
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

	// @vue/component
	const UpdateChatContent = {
	  name: 'UpdateChatContent',
	  components: {
	    GroupChatUpdating,
	    ChannelUpdating
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
	    }
	  },
	  template: `
		<div class="bx-im-content-chat-forms__container">
			<GroupChatUpdating v-if="isChat" :dialogId="this.entityId" />
			<ChannelUpdating v-else-if="isChannel" :dialogId="this.entityId" />
		</div>
	`
	};

	exports.CreateChatContent = CreateChatContent;
	exports.UpdateChatContent = UpdateChatContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Animation,BX.UI.EntitySelector,BX,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX,BX.Event,BX.Main,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
