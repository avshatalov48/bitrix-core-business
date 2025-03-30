/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.AccessRights = this.BX.UI.AccessRights || {};
(function (exports,main_core_events,ui_dialogs_messagebox,ui_buttons,ui_vue3_components_popup,ui_entitySelector,ui_vue3,ui_vue3_directives_hint,ui_vue3_components_switcher,main_popup,ui_ears,ui_hint,ui_vue3_components_richMenu,ui_notification,ui_analytics,ui_vue3_vuex,main_core) {
	'use strict';

	/**
	 * @abstract
	 */
	class Base {
	  /*
	   * @abstract
	   */
	  getComponentName() {
	    throw new Error('not implemented');
	  }
	  getEmptyValue(item) {
	    var _item$emptyValue;
	    return (_item$emptyValue = item.emptyValue) != null ? _item$emptyValue : new Set();
	  }
	  getMinValue(item) {
	    if (!main_core.Type.isNil(item.minValue)) {
	      return item.minValue;
	    }
	    return null;
	  }
	  getMaxValue(item) {
	    if (!main_core.Type.isNil(item.maxValue)) {
	      return item.maxValue;
	    }
	    return null;
	  }
	  isRowValueConfigurable() {
	    return true;
	  }
	}

	class DependentVariables extends Base {
	  getComponentName() {
	    return 'DependentVariables';
	  }
	}

	class Multivariables extends Base {
	  getComponentName() {
	    return 'Multivariables';
	  }
	}

	class Toggler extends Base {
	  getComponentName() {
	    return 'Toggler';
	  }
	  getEmptyValue(item) {
	    const isFalsy = !item.emptyValue || !item.emptyValue[0];
	    if (isFalsy) {
	      // use explicit '0' for correctly identify modifications
	      return new Set(['0']);
	    }
	    return super.getEmptyValue(item);
	  }
	  getMinValue(item) {
	    const explicit = super.getMinValue(item);
	    if (!main_core.Type.isNull(explicit)) {
	      return explicit;
	    }
	    return new Set(['0']);
	  }
	  getMaxValue(item) {
	    const explicit = super.getMaxValue(item);
	    if (!main_core.Type.isNull(explicit)) {
	      return explicit;
	    }
	    return new Set(['1']);
	  }
	  isRowValueConfigurable() {
	    return false;
	  }
	}

	class Variables extends Base {
	  getComponentName() {
	    return 'Variables';
	  }
	}

	var _cache = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");
	class ServiceLocator {
	  /**
	   * `BX.UI.Hint.createInstance` takes up to 30% of CPU time when multiple hints are mounted on page
	   * (e.g. on a load, search), probably because of `Manager.initByClassName` call in `new Manager`.
	   * therefore, we share a Manager instance across all hints in the app
	   */
	  static getHint(appGuid) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember(`hint-${appGuid}`, () => {
	      return BX.UI.Hint.createInstance({
	        id: `ui-access-rights-v2-hint-${appGuid}`,
	        popupParameters: {
	          className: 'ui-access-rights-v2-popup-pointer-events ui-hint-popup',
	          autoHide: true,
	          darkMode: true,
	          maxWidth: 280,
	          offsetTop: 0,
	          offsetLeft: 8,
	          angle: true,
	          animation: 'fading-slide'
	        }
	      });
	    });
	  }
	  static getValueTypeByRight(right) {
	    return this.getValueType(right.type);
	  }
	  static getValueType(type) {
	    const stringType = String(type);
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember(stringType, () => {
	      if (stringType === 'dependent_variables') {
	        return new DependentVariables();
	      }
	      if (stringType === 'multivariables') {
	        return new Multivariables();
	      }
	      if (stringType === 'toggler') {
	        return new Toggler();
	      }
	      if (stringType === 'variables') {
	        return new Variables();
	      }
	      console.warn('ui.accessrights.v2: Unknown access right type', type);
	      return null;
	    });
	  }
	}
	Object.defineProperty(ServiceLocator, _cache, {
	  writable: true,
	  value: new main_core.Cache.MemoryCache()
	});

	const EntitySelectorContext = Object.freeze({
	  ROLE: 'ui.accessrights.v2~role-selector',
	  MEMBER: 'ui.accessrights.v2~member-selector',
	  VARIABLE: 'ui.accessrights.v2~variable-selector'
	});
	const EntitySelectorEntities = Object.freeze({
	  ROLE: 'ui.accessrights.v2~role',
	  VARIABLE: 'ui.accessrights.v2~variable'
	});

	const Selector = {
	  name: 'Selector',
	  emits: ['close'],
	  props: {
	    userGroup: {
	      /** @type UserGroup */
	      type: Object,
	      required: true
	    },
	    bindNode: {
	      type: HTMLElement,
	      required: true
	    }
	  },
	  computed: {
	    selectedItems() {
	      const result = [];
	      for (const accessCode of this.userGroup.members.keys()) {
	        result.push(this.getItemIdByAccessCode(accessCode));
	      }
	      return result;
	    }
	  },
	  mounted() {
	    new ui_entitySelector.Dialog({
	      enableSearch: true,
	      context: EntitySelectorContext.MEMBER,
	      alwaysShowLabels: true,
	      entities: [{
	        id: 'user',
	        options: {
	          intranetUsersOnly: true,
	          emailUsers: false,
	          inviteEmployeeLink: false,
	          inviteGuestLink: false
	        }
	      }, {
	        id: 'department',
	        options: {
	          selectMode: 'usersAndDepartments',
	          allowSelectRootDepartment: true,
	          allowFlatDepartments: true
	        }
	      }, {
	        id: 'project',
	        dynamicLoad: true,
	        options: {
	          addProjectMetaUsers: true
	        },
	        itemOptions: {
	          default: {
	            link: '',
	            linkTitle: ''
	          }
	        }
	      }, {
	        id: 'site-groups',
	        dynamicLoad: true,
	        dynamicSearch: true
	      }],
	      targetNode: this.bindNode,
	      preselectedItems: this.selectedItems,
	      cacheable: false,
	      events: {
	        'Item:onSelect': this.onMemberAdd,
	        'Item:onDeselect': this.onMemberRemove,
	        onHide: () => {
	          this.$emit('close');
	        }
	      }
	    }).show();
	  },
	  methods: {
	    // eslint-disable-next-line sonarjs/cognitive-complexity
	    getItemIdByAccessCode(accessCode) {
	      if (/^I?U(\d+)$/.test(accessCode)) {
	        const match = accessCode.match(/^I?U(\d+)$/) || null;
	        const userId = match ? match[1] : null;
	        return ['user', userId];
	      }
	      if (/^DR(\d+)$/.test(accessCode)) {
	        const match = accessCode.match(/^DR(\d+)$/) || null;
	        const departmentId = match ? match[1] : null;
	        return ['department', departmentId];
	      }
	      if (/^D(\d+)$/.test(accessCode)) {
	        const match = accessCode.match(/^D(\d+)$/) || null;
	        const departmentId = match ? match[1] : null;
	        return ['department', `${departmentId}:F`];
	      }
	      if (/^G(\d+)$/.test(accessCode)) {
	        const match = accessCode.match(/^G(\d+)$/) || null;
	        const groupId = match ? match[1] : null;
	        return ['site-groups', groupId];
	      }
	      if (/^SG(\d+)_([AEK])$/.test(accessCode)) {
	        const match = accessCode.match(/^SG(\d+)_([AEK])$/) || null;
	        const projectId = match ? match[1] : null;
	        const postfix = match ? match[2] : null;
	        return ['project', `${projectId}:${postfix}`];
	      }
	      return ['unknown', accessCode];
	    },
	    onMemberAdd(event) {
	      const member = this.getMemberFromEvent(event);
	      this.$store.dispatch('userGroups/addMember', {
	        userGroupId: this.userGroup.id,
	        accessCode: member.id,
	        member
	      });
	    },
	    onMemberRemove(event) {
	      const member = this.getMemberFromEvent(event);
	      this.$store.dispatch('userGroups/removeMember', {
	        userGroupId: this.userGroup.id,
	        accessCode: member.id
	      });
	    },
	    getMemberFromEvent(event) {
	      const {
	        item
	      } = event.getData();
	      return {
	        id: this.getAccessCodeByItem(item),
	        type: this.getMemberTypeByItem(item),
	        name: item.title.text,
	        avatar: main_core.Type.isStringFilled(item.avatar) ? item.avatar : null
	      };
	    },
	    // eslint-disable-next-line sonarjs/cognitive-complexity
	    getAccessCodeByItem(item) {
	      const entityId = item.entityId;
	      if (entityId === 'user') {
	        return `U${item.id}`;
	      }
	      if (entityId === 'department') {
	        if (main_core.Type.isString(item.id) && item.id.endsWith(':F')) {
	          const match = item.id.match(/^(\d+):F$/);
	          const originalId = match ? match[1] : null;

	          // only members of the department itself
	          return `D${originalId}`;
	        }

	        // whole department recursively
	        return `DR${item.id}`;
	      }
	      if (entityId === 'site-groups') {
	        return `G${item.id}`;
	      }
	      if (entityId === 'project') {
	        const subType = item.customData.get('metauser');
	        const originalId = item.customData.get('projectId');
	        if (subType === 'owner') {
	          return `SG${originalId}_A`;
	        }
	        if (subType === 'moderator') {
	          return `SG${originalId}_E`;
	        }
	        if (subType === 'all') {
	          return `SG${originalId}_K`;
	        }
	      }
	      return '';
	    },
	    getMemberTypeByItem(item) {
	      switch (item.entityId) {
	        case 'user':
	          return 'users';
	        case 'intranet':
	        case 'department':
	          return 'departments';
	        case 'socnetgroup':
	        case 'project':
	          return 'sonetgroups';
	        case 'group':
	          return 'groups';
	        case 'site-groups':
	          return 'usergroups';
	        default:
	          return '';
	      }
	    }
	  },
	  // just a template stub
	  template: '<div hidden></div>'
	};

	const SingleMember = {
	  name: 'SingleMember',
	  props: {
	    member: {
	      /** @type Member */
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    avatarBackgroundImage() {
	      return `url(${encodeURI(this.member.avatar)})`;
	    },
	    noAvatarClass() {
	      if (this.member.type === 'groups') {
	        return 'ui-icon-common-user-group';
	      }
	      if (this.member.type === 'sonetgroups' || this.member.type === 'departments') {
	        return 'ui-icon-common-company';
	      }
	      if (this.member.type === 'usergroups') {
	        return 'ui-icon-common-user-group';
	      }
	      return 'ui-icon-common-user';
	    }
	  },
	  template: `
		<div class='ui-access-rights-v2-members-item'>
			<a v-if="member.avatar" class='ui-access-rights-v2-members-item-avatar' :title="member.name" :style="{
				backgroundImage: avatarBackgroundImage,
				backgroundSize: 'cover',
			}"></a>
			<a v-else class='ui-icon ui-access-rights-v2-members-item-icon' :class="noAvatarClass" :title="member.name">
				<i></i>
			</a>
		</div>
	`
	};

	const MAX_SHOWN_MEMBERS = 5;
	const Members = {
	  name: 'Members',
	  components: {
	    SingleMember,
	    Selector
	  },
	  props: {
	    userGroup: {
	      /** @type UserGroup */
	      type: Object,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isSelectorShown: false,
	      isSelectedMembersPopupShown: false
	    };
	  },
	  popup: null,
	  computed: {
	    shownMembers() {
	      if (this.userGroup.members.size <= MAX_SHOWN_MEMBERS) {
	        return this.userGroup.members;
	      }
	      const shownKeyValuePairs = [...this.userGroup.members].slice(0, MAX_SHOWN_MEMBERS);
	      return new Map(shownKeyValuePairs);
	    },
	    notShownMembersCount() {
	      if (this.userGroup.members.size > MAX_SHOWN_MEMBERS) {
	        return this.userGroup.members.size - MAX_SHOWN_MEMBERS;
	      }
	      return 0;
	    },
	    bindNode() {
	      return this.$refs.container;
	    }
	  },
	  template: `
		<div ref="container" class="ui-access-rights-v2-members-container"  @click="isSelectorShown = true">
			<div v-if="userGroup.members.size > 0" class='ui-access-rights-v2-members'>
				<SingleMember v-for="[accessCode, member] in shownMembers" :key="accessCode" :member="member"/>
				<span v-if="notShownMembersCount > 0" class="ui-access-rights-v2-members-more">
					+ {{ notShownMembersCount }}
				</span>
			</div>
			<div
				class='ui-access-rights-v2-members-item ui-access-rights-v2-members-item-add'
				:class="{
					'--show-always': userGroup.members.size <= 0,
					'--has-siblings': userGroup.members.size > 0,
				}"
			>
				<div class="ui-icon-set --plus-30"></div>
			</div>
			<Selector
				v-if="isSelectorShown"
				:user-group="userGroup"
				:bind-node="bindNode"
				@close="isSelectorShown = false"
			/>
		</div>
	`
	};

	const RoleHeading = {
	  name: 'RoleHeading',
	  components: {
	    RichMenuPopup: ui_vue3_components_richMenu.RichMenuPopup,
	    RichMenuItem: ui_vue3_components_richMenu.RichMenuItem
	  },
	  props: {
	    userGroup: {
	      /** @type UserGroup */
	      type: Object,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isEdit: false,
	      isPopupShown: false
	    };
	  },
	  computed: {
	    RichMenuItemIcon: () => ui_vue3_components_richMenu.RichMenuItemIcon,
	    ...ui_vue3_vuex.mapState({
	      isSaving: state => state.application.isSaving,
	      guid: state => state.application.guid,
	      maxVisibleUserGroups: state => state.application.options.maxVisibleUserGroups
	    }),
	    ...ui_vue3_vuex.mapGetters({
	      isMaxVisibleUserGroupsReached: 'userGroups/isMaxVisibleUserGroupsReached',
	      isMaxValueSetForAny: 'accessRights/isMaxValueSetForAny',
	      isMinValueSetForAny: 'accessRights/isMinValueSetForAny'
	    }),
	    title: {
	      get() {
	        return this.userGroup.title;
	      },
	      set(title) {
	        this.$store.dispatch('userGroups/setRoleTitle', {
	          userGroupId: this.userGroup.id,
	          title
	        });
	      }
	    }
	  },
	  watch: {
	    isEdit(newValue) {
	      if (newValue === true) {
	        this.bindClickedOutsideHandler();
	        void this.$nextTick(() => {
	          this.$refs.input.scrollIntoView({
	            behavior: 'smooth',
	            block: 'nearest',
	            inline: 'nearest'
	          });
	          this.$refs.input.focus();
	          this.$refs.input.select();
	        });
	      } else {
	        this.unbindClickedOutsideHandler();
	      }
	    }
	  },
	  mounted() {
	    // todo fix hide/show new role
	    if (this.userGroup.isNew) {
	      // start editing a newly created role right away
	      this.isEdit = true;
	    }
	  },
	  beforeUnmount() {
	    this.unbindClickedOutsideHandler();
	  },
	  methods: {
	    bindClickedOutsideHandler() {
	      main_core.Event.bind(window, 'click', this.turnOffEditWhenClickedOutside, {
	        capture: true
	      });
	    },
	    unbindClickedOutsideHandler() {
	      main_core.Event.unbind(window, 'click', this.turnOffEditWhenClickedOutside, {
	        capture: true
	      });
	    },
	    turnOffEditWhenClickedOutside(event) {
	      if (event.target !== this.$refs.input) {
	        this.isEdit = false;
	      }
	    },
	    showDeleteConfirmation() {
	      const popup = new main_popup.Popup({
	        bindElement: this.$refs.container,
	        width: 250,
	        overlay: true,
	        contentPadding: 10,
	        content: this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_POPUP_REMOVE_ROLE'),
	        className: 'ui-access-rights-v2-text-center',
	        animation: 'fading-slide',
	        cacheable: false,
	        buttons: [new ui_buttons.Button({
	          text: this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_POPUP_REMOVE_ROLE_YES'),
	          size: ui_buttons.ButtonSize.SMALL,
	          color: ui_buttons.ButtonColor.PRIMARY,
	          events: {
	            click: () => {
	              popup.destroy();
	              this.$store.dispatch('userGroups/removeUserGroup', {
	                userGroupId: this.userGroup.id
	              });
	            }
	          }
	        }), new ui_buttons.CancelButton({
	          size: ui_buttons.ButtonSize.SMALL,
	          events: {
	            click: () => {
	              popup.destroy();
	            }
	          }
	        })]
	      });
	      popup.show();
	    },
	    showActionsMenu() {
	      if (!this.isSaving) {
	        this.isPopupShown = true;
	      }
	    },
	    onSetMaxValuesClick() {
	      this.isPopupShown = false;
	      this.$store.dispatch('userGroups/setMaxAccessRightValues', {
	        userGroupId: this.userGroup.id
	      });
	    },
	    onSetMinValuesClick() {
	      this.isPopupShown = false;
	      this.$store.dispatch('userGroups/setMinAccessRightValues', {
	        userGroupId: this.userGroup.id
	      });
	    },
	    onEnableEditClick() {
	      this.isPopupShown = false;
	      this.isEdit = true;
	    },
	    onCopyRoleClick() {
	      if (this.isMaxVisibleUserGroupsReached) {
	        return;
	      }
	      this.isPopupShown = false;
	      this.$store.dispatch('userGroups/copyUserGroup', {
	        userGroupId: this.userGroup.id
	      });
	    },
	    onDeleteRoleClick() {
	      this.isPopupShown = false;
	      this.showDeleteConfirmation();
	    }
	  },
	  template: `
		<div ref="container" class='ui-access-rights-v2-role'>
			<div class="ui-access-rights-v2-role-value-container">
				<input
					v-if="isEdit && !isSaving"
					ref="input"
					type='text'
					class='ui-access-rights-v2-role-input'
					v-model="title"
					:placeholder="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_NAME')"
					@keydown.enter="isEdit = false"
				/>
				<div v-else class='ui-access-rights-v2-role-value' :title="title">{{ title }}</div>
			</div>
			<div 
				ref="menu"
				class="ui-icon-set --more ui-access-rights-v2-icon-more" 
				@click="showActionsMenu"
			>
				<RichMenuPopup v-if="isPopupShown" @close="isPopupShown = false" :popup-options="{bindElement: $refs.menu}">
					<RichMenuItem
						v-if="isMaxValueSetForAny"
						:icon="RichMenuItemIcon.check"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MAX_ACCESS_RIGHTS')"
						:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MAX_ACCESS_RIGHTS_SUBTITLE')"
						@click="onSetMaxValuesClick"
					/>
					<RichMenuItem
						v-if="isMinValueSetForAny"
						:icon="RichMenuItemIcon['red-lock']"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MIN_ACCESS_RIGHTS')"
						:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MIN_ACCESS_RIGHTS_SUBTITLE')"
						@click="onSetMinValuesClick"
					/>
					<RichMenuItem
						:icon="RichMenuItemIcon.pencil"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_RENAME')"
						:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_RENAME_SUBTITLE')"
						@click="onEnableEditClick"
					/>
					<RichMenuItem
						:icon="RichMenuItemIcon.copy"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_COPY')"
						:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_COPY_ROLE_SUBTITLE')"
						:disabled="isMaxVisibleUserGroupsReached"
						:hint="
							isMaxVisibleUserGroupsReached
								? $Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_COPYING_DISABLED', {
									'#COUNT#': maxVisibleUserGroups,
								})
								: null
						"
						@click="onCopyRoleClick"
					/>
					<RichMenuItem
						:icon="RichMenuItemIcon['trash-bin']"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_REMOVE')"
						:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_REMOVE_SUBTITLE')"
						@click="onDeleteRoleClick"
					/>
				</RichMenuPopup>
			</div>
		</div>
	`
	};

	class ItemsMapper {
	  static mapUserGroups(userGroups) {
	    const result = [];
	    for (const userGroup of userGroups.values()) {
	      result.push({
	        id: userGroup.id,
	        entityId: EntitySelectorEntities.ROLE,
	        title: userGroup.title,
	        supertitle: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE'),
	        avatar: '/bitrix/js/ui/accessrights/v2/images/role-avatar.svg',
	        tabs: ['recents']
	      });
	    }
	    return result;
	  }
	  static mapVariables(variables) {
	    const items = [];
	    for (const variable of variables.values()) {
	      const item = main_core.Runtime.clone(variable);
	      item.entityId = item.entityId || EntitySelectorEntities.VARIABLE;
	      item.tabs = 'recents';
	      items.push(item);
	    }
	    return items;
	  }
	}

	const CellLayout = {
	  template: `
		<div class='ui-access-rights-v2-column-item'>
			<slot/>
		</div>
	`
	};

	const ColumnLayout = {
	  template: `
		<div class='ui-access-rights-v2-column'>
			<slot/>
		</div>
	`
	};

	const RolesControl = {
	  name: 'RolesControl',
	  components: {
	    CellLayout,
	    ColumnLayout,
	    RichMenuPopup: ui_vue3_components_richMenu.RichMenuPopup,
	    RichMenuItem: ui_vue3_components_richMenu.RichMenuItem
	  },
	  props: {
	    userGroups: {
	      type: Map,
	      required: true
	    }
	  },
	  viewDialog: null,
	  computed: {
	    RichMenuItemIcon: () => ui_vue3_components_richMenu.RichMenuItemIcon,
	    ...ui_vue3_vuex.mapState({
	      allUserGroups: state => state.userGroups.collection,
	      maxVisibleUserGroups: state => state.application.options.maxVisibleUserGroups,
	      guid: state => state.application.guid
	    }),
	    ...ui_vue3_vuex.mapGetters({
	      isMaxVisibleUserGroupsSet: 'application/isMaxVisibleUserGroupsSet',
	      isMaxVisibleUserGroupsReached: 'userGroups/isMaxVisibleUserGroupsReached'
	    }),
	    shownGroupsCounter() {
	      return this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_COUNTER', {
	        '#VISIBLE_ROLES#': this.userGroups.size,
	        '#ALL_ROLES#': this.allUserGroups.size,
	        '#GREY_START#': '<span style="opacity: var(--ui-opacity-30)">',
	        '#GREY_FINISH#': '</span>'
	      });
	    },
	    copyDialogItems() {
	      return ItemsMapper.mapUserGroups(this.allUserGroups);
	    },
	    viewDialogItems() {
	      const result = [];
	      for (const copyDialogItem of this.copyDialogItems) {
	        result.push({
	          ...copyDialogItem,
	          selected: this.userGroups.has(copyDialogItem.id)
	        });
	      }
	      return result;
	    }
	  },
	  data() {
	    return {
	      isPopupShown: false
	    };
	  },
	  methods: {
	    onCreateNewRoleClick() {
	      if (this.isMaxVisibleUserGroupsReached) {
	        return;
	      }
	      this.isPopupShown = false;
	      this.$store.dispatch('userGroups/addUserGroup');
	    },
	    onRoleViewClick() {
	      this.isPopupShown = false;
	      this.showViewDialog(this.$refs.configure);
	    },
	    onCopyRoleClick() {
	      if (this.isMaxVisibleUserGroupsReached) {
	        return;
	      }
	      this.isPopupShown = false;
	      this.showCopyDialog();
	    },
	    showCopyDialog() {
	      const copyDialog = new ui_entitySelector.Dialog({
	        context: EntitySelectorContext.ROLE,
	        targetNode: this.$refs.configure,
	        multiple: false,
	        dropdownMode: true,
	        enableSearch: true,
	        cacheable: false,
	        items: this.copyDialogItems,
	        events: {
	          'Item:onSelect': dialogEvent => {
	            const {
	              item
	            } = dialogEvent.getData();
	            this.$store.dispatch('userGroups/copyUserGroup', {
	              userGroupId: item.getId()
	            });
	          }
	        }
	      });
	      copyDialog.show();
	    },
	    showViewDialog(target) {
	      this.viewDialog = new ui_entitySelector.Dialog({
	        context: EntitySelectorContext.ROLE,
	        footer: this.isMaxVisibleUserGroupsSet ? this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_SELECTOR_MAX_VISIBLE_WARNING', {
	          '#COUNT#': this.maxVisibleUserGroups
	        }) : null,
	        targetNode: target,
	        multiple: true,
	        dropdownMode: true,
	        enableSearch: true,
	        cacheable: false,
	        items: this.viewDialogItems,
	        events: {
	          'Item:onBeforeSelect': dialogEvent => {
	            if (this.isMaxVisibleUserGroupsSet && this.viewDialog.getSelectedItems().length >= this.maxVisibleUserGroups) {
	              dialogEvent.preventDefault();
	            }
	          },
	          'Item:onSelect': dialogEvent => {
	            const {
	              item
	            } = dialogEvent.getData();
	            this.$store.dispatch('userGroups/showUserGroup', {
	              userGroupId: item.getId()
	            });
	          },
	          'Item:onDeselect': dialogEvent => {
	            const {
	              item
	            } = dialogEvent.getData();
	            this.$store.dispatch('userGroups/hideUserGroup', {
	              userGroupId: item.getId()
	            });
	          },
	          onHide: () => {
	            this.viewDialog = null;
	          }
	        }
	      });
	      this.viewDialog.show();
	    },
	    toggleViewDialog(target) {
	      if (this.viewDialog) {
	        this.viewDialog.hide();
	      } else {
	        this.showViewDialog(target);
	      }
	    }
	  },
	  template: `
		<ColumnLayout>
			<CellLayout class="ui-access-rights-v2-header-roles-control">
				<div class='ui-access-rights-v2-column-item-text ui-access-rights-v2-header-roles-control-header'>
					<div>{{ $Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLES') }}</div>
					<div
						ref="configure"
						class="ui-icon-set --more ui-access-rights-v2-icon-more"
						@click="isPopupShown = true"
					>
						<RichMenuPopup v-if="isPopupShown" @close="isPopupShown = false" :popup-options="{bindElement: $refs.configure}">
							<RichMenuItem
								:icon="RichMenuItemIcon.role"
								:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_NEW_ROLE')"
								:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_NEW_ROLE_SUBTITLE')"
								:disabled="isMaxVisibleUserGroupsReached"
								:hint="
									isMaxVisibleUserGroupsReached
										? $Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_ADDING_DISABLED', {
											'#COUNT#': maxVisibleUserGroups,
										})
										: null
								"
								@click="onCreateNewRoleClick"
							/>
							<RichMenuItem
								:icon="RichMenuItemIcon.copy"
								:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_COPY_ROLE')"
								:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_COPY_ROLE_SUBTITLE')"
								:disabled="isMaxVisibleUserGroupsReached"
								:hint="
									isMaxVisibleUserGroupsReached
										? $Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_COPYING_DISABLED', {
											'#COUNT#': maxVisibleUserGroups,
										})
										: null
								"
								@click="onCopyRoleClick"
							/>
							<RichMenuItem
								:icon="RichMenuItemIcon['opened-eye']"
								:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_VIEW')"
								:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_VIEW_SUBTITLE_MSGVER_1')"
								@click="onRoleViewClick"
							/>
						</RichMenuPopup>
					</div>
				</div>
				<div class="ui-access-rights-v2-header-roles-control-actions">
					<div
						ref="counter"
						class="ui-access-rights-v2-header-roles-control-counter"
						@click="toggleViewDialog($refs.counter)"
					>
						<div class="ui-icon-set --opened-eye" style="--ui-icon-set__icon-size: 15px;"></div>
						<span v-html="shownGroupsCounter"></span>
						<div class="ui-icon-set --chevron-down ui-access-rights-v2-header-roles-control-chevron"></div>
					</div>
					<div class="ui-access-rights-v2-header-roles-control-expander">
						<div
							class="ui-icon-set --collapse"
							:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_COLLAPSE_ALL_SECTIONS')"
							@click="$store.dispatch('accessRights/collapseAllSections')"
						></div>
						<div 
							class="ui-icon-set --expand-1"
							:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_EXPAND_ALL_SECTIONS')"
							@click="$store.dispatch('accessRights/expandAllSections')"
						></div>
					</div>
				</div>
			</CellLayout>
		</ColumnLayout>
	`
	};

	const isMaxListenersSet = new Map();
	const lastScrollLeft = new Map();

	/**
	 * A div without styling that synchronizes horizontal scroll of all elements wrapped in this component with other
	 * wrapped elements in this Vue application.
	 */
	const SyncHorizontalScroll = {
	  name: 'SyncHorizontalScroll',
	  data() {
	    return {
	      componentGuid: main_core.Text.getRandom(16)
	    };
	  },
	  computed: {
	    ...ui_vue3_vuex.mapState({
	      guid: state => state.application.guid
	    })
	  },
	  throttledEmitScrollEvent: null,
	  created() {
	    this.throttledEmitScrollEvent = requestAnimationFrameThrottle(this.emitScrollEvent);
	  },
	  mounted() {
	    if (!isMaxListenersSet.has(this.guid)) {
	      // + 1 for header
	      const sectionsNumber = this.$store.state.accessRights.collection.size + 1;

	      // correctly notify about memory leak
	      this.$Bitrix.eventEmitter.incrementMaxListeners('ui:accessrights:v2:syncScroll', sectionsNumber);
	      isMaxListenersSet.set(this.guid, true);
	    }
	    this.$Bitrix.eventEmitter.subscribe('ui:accessrights:v2:syncScroll', this.handleScrollEvent);
	    void this.$nextTick(() => {
	      if (lastScrollLeft.has(this.guid)) {
	        this.syncScroll(lastScrollLeft.get(this.guid));
	      }
	    });
	  },
	  beforeUnmount() {
	    this.$Bitrix.eventEmitter.unsubscribe('ui:accessrights:v2:syncScroll', this.handleScrollEvent);
	  },
	  methods: {
	    emitScrollEvent(event) {
	      // this component instance is being scrolled, we need to notify other instances
	      const {
	        scrollLeft
	      } = event.target;
	      lastScrollLeft.set(this.guid, scrollLeft);

	      // emit global application event so other SyncHorizontalScroll instances receive it
	      this.$Bitrix.eventEmitter.emit('ui:accessrights:v2:syncScroll', {
	        scrollLeft,
	        componentGuid: this.componentGuid
	      });
	    },
	    handleScrollEvent(event) {
	      const {
	        scrollLeft,
	        componentGuid
	      } = event.getData();
	      if (this.componentGuid === componentGuid) {
	        // this event was sent by this exact instance
	        return;
	      }
	      this.syncScroll(scrollLeft);
	    },
	    syncScroll(scrollLeft) {
	      // magic hack - don't update the element if value not changed.
	      // I'm not sure whether this works, but why not
	      if (this.$el.scrollLeft !== scrollLeft) {
	        this.$el.scrollLeft = scrollLeft;
	      }
	    }
	  },
	  template: `
		<div @scroll="throttledEmitScrollEvent">
			<slot/>
		</div>
	`
	};

	/**
	 * Same as `Runtime.throttle`, but uses `requestAnimationFrame` instead of setTimeout.
	 * Why? To sync wait time with display refresh rate for smother animations.
	 */
	function requestAnimationFrameThrottle(func) {
	  let callbackSet = false;
	  let invoke = false;
	  return function wrapper(...args) {
	    invoke = true;
	    if (!callbackSet) {
	      const q = function q() {
	        if (invoke) {
	          func(...args);
	          invoke = false;
	          requestAnimationFrame(q);
	          callbackSet = true;
	        } else {
	          callbackSet = false;
	        }
	      };
	      q();
	    }
	  };
	}

	/**
	 * A special case of Section
	 */
	const Header = {
	  name: 'Header',
	  components: {
	    RoleHeading,
	    SyncHorizontalScroll,
	    Members,
	    RolesControl,
	    ColumnLayout,
	    CellLayout
	  },
	  props: {
	    userGroups: {
	      type: Map,
	      required: true
	    }
	  },
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  computed: {
	    ...ui_vue3_vuex.mapState({
	      maxVisibleUserGroups: state => state.application.options.maxVisibleUserGroups
	    }),
	    ...ui_vue3_vuex.mapGetters({
	      isMaxVisibleUserGroupsReached: 'userGroups/isMaxVisibleUserGroupsReached'
	    })
	  },
	  methods: {
	    onCreateNewRoleClick() {
	      if (this.isMaxVisibleUserGroupsReached) {
	        return;
	      }
	      this.$store.dispatch('userGroups/addUserGroup');
	    }
	  },
	  // data attributes are needed for e2e automated tests
	  template: `
		<div class="ui-access-rights-v2-section ui-access-rights-v2--head-section">
			<div class='ui-access-rights-v2-section-container'>
				<div class='ui-access-rights-v2-section-head'>
					<RolesControl :user-groups="userGroups"/>
				</div>
				<div class='ui-access-rights-v2-section-content'>
					<SyncHorizontalScroll class='ui-access-rights-v2-section-wrapper'>
						<ColumnLayout
							v-for="[groupId, group] in userGroups" 
							:key="groupId"
							:data-accessrights-user-group-id="groupId"
						>
							<CellLayout class="ui-access-rights-v2-header-role-cell">
								<RoleHeading :user-group="group"/>
								<Members :user-group="group"/>
							</CellLayout>
						</ColumnLayout>
						<ColumnLayout>
							<div class="ui-access-rights-v2-header-role-add">
								<button class="ui-btn ui-btn-light-border ui-btn-round ui-btn-disabled"
										v-if="isMaxVisibleUserGroupsReached"
										v-hint="$Bitrix.Loc.getMessage(
									 'JS_UI_ACCESSRIGHTS_V2_ROLE_ADDING_DISABLED', 
									 {'#COUNT#': maxVisibleUserGroups,})"
								>
									<div class="ui-icon-set --plus-20"/>
								</button>
								<button class="ui-btn ui-btn-light-border ui-btn-round"
										v-else
										@click="onCreateNewRoleClick"
								>
									<div class="ui-icon-set --plus-20"/>
								</button>
							</div>
						</ColumnLayout>
					</SyncHorizontalScroll>
				</div>
			</div>
		</div>
	`
	};

	const SearchBox = {
	  name: 'SearchBox',
	  debouncedSetSearchQuery: null,
	  created() {
	    const setSearchQuery = query => {
	      this.$store.dispatch('accessRights/search', {
	        query
	      });
	    };
	    this.debouncedSetSearchQuery = main_core.Runtime.debounce(setSearchQuery, 200);
	  },
	  computed: {
	    searchQuery: {
	      get() {
	        return this.$store.state.accessRights.searchQuery;
	      },
	      set(query) {
	        this.debouncedSetSearchQuery(query);
	      }
	    }
	  },
	  template: `
		<div class="ui-ctl ui-ctl-after-icon ui-ctl-w100 ui-access-rights-v2-search">
			<input
				type="text"
				class="ui-ctl-element ui-ctl-textbox ui-access-rights-v2-search-input"
				:placeholder="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SEARCH_PLACEHOLDER')"
				v-model="searchQuery"
			>
			<a class="ui-ctl-after ui-ctl-icon-search ui-access-rights-v2-search-icon"></a>
		</div>
	`
	};

	function shouldRowBeRendered(accessRightItem) {
	  if (!accessRightItem.isShown) {
	    return false;
	  }
	  return !accessRightItem.group || accessRightItem.isGroupExpanded;
	}
	function getSelectedVariables(variables, selected, isAllSelected) {
	  if (isAllSelected) {
	    return variables;
	  }
	  const selectedVariables = new Map();
	  for (const [variableId, variable] of variables) {
	    if (selected.has(variableId)) {
	      selectedVariables.set(variableId, variable);
	    }
	  }
	  return selectedVariables;
	}
	function getMultipleSelectedVariablesTitle(selectedVariables) {
	  const lastVariable = [...selectedVariables.values()].pop();
	  if (selectedVariables.size === 1) {
	    return lastVariable.title;
	  }
	  return main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_HAS_SELECTED_ITEMS', {
	    '#FIRST_ITEM_NAME#': cutLongTitle(lastVariable.title),
	    '#COUNT_REST_ITEMS#': selectedVariables.size - 1
	  });
	}
	function cutLongTitle(title) {
	  const VARIABLE_TITLE_MAX_LENGTH = 15;
	  if (title.length > VARIABLE_TITLE_MAX_LENGTH) {
	    return `${title.slice(0, VARIABLE_TITLE_MAX_LENGTH)}...`;
	  }
	  return title;
	}
	function getMultipleSelectedVariablesHintHtml(selectedVariables, hintTitle, allVariables) {
	  if (selectedVariables.size < 2) {
	    return '';
	  }
	  let listItems = '';
	  for (const value of makeSortedVariablesArray(selectedVariables, allVariables)) {
	    listItems += `<li>${main_core.Text.encode(value.title)}</li>`;
	  }
	  return `
		<p>${main_core.Text.encode(hintTitle)}</p>
		<ul>${listItems}</ul>
	`;
	}
	function makeSortedVariablesArray(toSort, example) {
	  const orderMap = new Map();
	  let index = 0;
	  for (const [variableId] of example) {
	    orderMap.set(variableId, index);
	    index++;
	  }
	  return [...toSort.values()].sort((a, b) => {
	    const indexA = orderMap.get(a.id);
	    const indexB = orderMap.get(b.id);
	    if (main_core.Type.isNil(indexA)) {
	      return 1;
	    }
	    if (main_core.Type.isNil(indexB)) {
	      return -1;
	    }
	    return indexA - indexB;
	  });
	}
	const DEFAULT_ALIAS_SEPARATOR = '|';
	function parseAliasKey(key, separator = DEFAULT_ALIAS_SEPARATOR) {
	  const parts = key.split(separator);
	  return new Set(parts);
	}
	function compileAliasKey(parts, separator = DEFAULT_ALIAS_SEPARATOR) {
	  const sortedParts = [...parts].sort();
	  return sortedParts.join(separator);
	}
	function normalizeAliasKey(key, separator = DEFAULT_ALIAS_SEPARATOR) {
	  const parsed = parseAliasKey(key, separator);
	  return compileAliasKey(parsed, separator);
	}

	var _initialRights = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initialRights");
	var _searchAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchAction");
	class AccessRightsModel extends ui_vue3_vuex.BuilderModel {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _searchAction, {
	      value: _searchAction2
	    });
	    Object.defineProperty(this, _initialRights, {
	      writable: true,
	      value: new Map()
	    });
	  }
	  getName() {
	    return 'accessRights';
	  }
	  setInitialAccessRights(rights) {
	    babelHelpers.classPrivateFieldLooseBase(this, _initialRights)[_initialRights] = rights;
	    return this;
	  }
	  getState() {
	    return {
	      collection: main_core.Runtime.clone(babelHelpers.classPrivateFieldLooseBase(this, _initialRights)[_initialRights]),
	      searchQuery: ''
	    };
	  }
	  getElementState(params = {}) {
	    throw new Error('Cant create AccessRightSection. You are doing something wrong');
	  }
	  getGetters() {
	    return {
	      shown: state => {
	        const result = new Map();
	        for (const [sectionCode, section] of state.collection) {
	          if (section.isShown) {
	            result.set(sectionCode, section);
	          }
	        }
	        return result;
	      },
	      isMinValueSetForAny: (state, getters) => {
	        for (const section of state.collection.values()) {
	          for (const item of section.rights.values()) {
	            const isSet = getters.isMinValueSet(section.sectionCode, item.id);
	            if (isSet) {
	              return true;
	            }
	          }
	        }
	        return false;
	      },
	      isMinValueSet: state => (sectionCode, rightId) => {
	        var _state$collection$get;
	        const item = (_state$collection$get = state.collection.get(sectionCode)) == null ? void 0 : _state$collection$get.rights.get(rightId);
	        if (!item) {
	          console.warn('ui.accessrights.v2: attempt to check if min value set for unknown right', {
	            sectionCode,
	            rightId
	          });
	          return false;
	        }
	        return !main_core.Type.isNil(item.minValue);
	      },
	      isMaxValueSetForAny: (state, getters) => {
	        for (const section of state.collection.values()) {
	          for (const item of section.rights.values()) {
	            const isSet = getters.isMaxValueSet(section.sectionCode, item.id);
	            if (isSet) {
	              return true;
	            }
	          }
	        }
	        return false;
	      },
	      isMaxValueSet: state => (sectionCode, rightId) => {
	        var _state$collection$get2;
	        const item = (_state$collection$get2 = state.collection.get(sectionCode)) == null ? void 0 : _state$collection$get2.rights.get(rightId);
	        if (!item) {
	          console.warn('ui.accessrights.v2: attempt to check if max value set for unknown right', {
	            sectionCode,
	            rightId
	          });
	          return false;
	        }
	        return !main_core.Type.isNil(item.maxValue);
	      },
	      getEmptyValue: state => (sectionCode, valueId) => {
	        var _state$collection$get3, _ServiceLocator$getVa, _ServiceLocator$getVa2;
	        const item = (_state$collection$get3 = state.collection.get(sectionCode)) == null ? void 0 : _state$collection$get3.rights.get(valueId);
	        if (!item) {
	          return new Set();
	        }
	        return (_ServiceLocator$getVa = (_ServiceLocator$getVa2 = ServiceLocator.getValueTypeByRight(item)) == null ? void 0 : _ServiceLocator$getVa2.getEmptyValue(item)) != null ? _ServiceLocator$getVa : new Set();
	      },
	      getNothingSelectedValue: (state, getters) => (sectionCode, valueId) => {
	        var _state$collection$get4, _item$nothingSelected;
	        const item = (_state$collection$get4 = state.collection.get(sectionCode)) == null ? void 0 : _state$collection$get4.rights.get(valueId);
	        return (_item$nothingSelected = item == null ? void 0 : item.nothingSelectedValue) != null ? _item$nothingSelected : getters.getEmptyValue(sectionCode, valueId);
	      },
	      getSelectedVariablesAlias: state => (sectionCode, valueId, values) => {
	        var _state$collection$get5;
	        const item = (_state$collection$get5 = state.collection.get(sectionCode)) == null ? void 0 : _state$collection$get5.rights.get(valueId);
	        if (!item) {
	          return null;
	        }
	        const key = compileAliasKey(values, item.selectedVariablesAliasesSeparator);
	        return item.selectedVariablesAliases.get(key);
	      }
	    };
	  }
	  getActions() {
	    return {
	      toggleSection: (store, {
	        sectionCode
	      }) => {
	        if (!store.state.collection.has(sectionCode)) {
	          console.warn('ui.accessrights.v2: Attempt to toggle section that dont exists', {
	            sectionCode
	          });
	          return;
	        }
	        store.commit('toggleSection', {
	          sectionCode
	        });
	      },
	      expandAllSections: store => {
	        for (const sectionCode of store.state.collection.keys()) {
	          store.commit('expandSection', {
	            sectionCode
	          });
	        }
	      },
	      collapseAllSections: store => {
	        for (const sectionCode of store.state.collection.keys()) {
	          store.commit('collapseSection', {
	            sectionCode
	          });
	        }
	      },
	      toggleGroup: (store, {
	        sectionCode,
	        groupId
	      }) => {
	        var _store$state$collecti;
	        const item = (_store$state$collecti = store.state.collection.get(sectionCode)) == null ? void 0 : _store$state$collecti.rights.get(groupId);
	        if (!item) {
	          console.warn('ui.accessrights.v2: Attempt to toggle group that dont exists', {
	            groupId
	          });
	          return;
	        }
	        if (!item.groupHead) {
	          console.warn('ui.accessrights.v2: Attempt to toggle group that is not group head', {
	            groupId
	          });
	          return;
	        }
	        store.commit('toggleGroup', {
	          sectionCode,
	          groupId
	        });
	      },
	      search: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _searchAction)[_searchAction](store, payload);
	      }
	    };
	  }
	  getMutations() {
	    return {
	      toggleSection: (state, {
	        sectionCode
	      }) => {
	        const section = state.collection.get(sectionCode);
	        section.isExpanded = !section.isExpanded;
	      },
	      expandSection: (state, {
	        sectionCode
	      }) => {
	        const section = state.collection.get(sectionCode);
	        section.isExpanded = true;
	      },
	      collapseSection: (state, {
	        sectionCode
	      }) => {
	        const section = state.collection.get(sectionCode);
	        section.isExpanded = false;
	      },
	      toggleGroup: (state, {
	        sectionCode,
	        groupId
	      }) => {
	        const section = state.collection.get(sectionCode);
	        for (const item of section.rights.values()) {
	          if (item.id === groupId && item.groupHead || item.group === groupId) {
	            item.isGroupExpanded = !item.isGroupExpanded;
	          }
	        }
	      },
	      expandGroup: (state, {
	        sectionCode,
	        groupId
	      }) => {
	        const section = state.collection.get(sectionCode);
	        section.isExpanded = true;
	        for (const item of section.rights.values()) {
	          if (item.id === groupId && item.groupHead || item.group === groupId) {
	            item.isGroupExpanded = true;
	          }
	        }
	      },
	      showItem: (state, {
	        sectionCode,
	        itemId
	      }) => {
	        const section = state.collection.get(sectionCode);
	        section.isShown = true;
	        const item = section.rights.get(itemId);
	        item.isShown = true;
	        if (item.group) {
	          section.rights.get(item.group).isShown = true;
	        }
	      },
	      showGroup: (state, {
	        sectionCode,
	        groupId
	      }) => {
	        const section = state.collection.get(sectionCode);
	        section.isShown = true;
	        for (const item of section.rights.values()) {
	          if (item.id === groupId && item.groupHead || item.group === groupId) {
	            item.isShown = true;
	          }
	        }
	      },
	      showSection: (state, {
	        sectionCode
	      }) => {
	        const section = state.collection.get(sectionCode);
	        section.isShown = true;
	        for (const item of section.rights.values()) {
	          item.isShown = true;
	        }
	      },
	      showAll: state => {
	        for (const section of state.collection.values()) {
	          section.isShown = true;
	          for (const item of section.rights.values()) {
	            item.isShown = true;
	          }
	        }
	      },
	      hideAll: state => {
	        for (const section of state.collection.values()) {
	          section.isShown = false;
	          for (const item of section.rights.values()) {
	            item.isShown = false;
	          }
	        }
	      },
	      setSearchQuery: (state, {
	        query
	      }) => {
	        // eslint-disable-next-line no-param-reassign
	        state.searchQuery = String(query);
	      }
	    };
	  }
	}
	function _searchAction2(store, {
	  query
	}) {
	  if (!main_core.Type.isString(query)) {
	    console.warn('ui.accessrights.v2: attempt to search with non-string search query');
	    return;
	  }
	  store.commit('setSearchQuery', {
	    query
	  });
	  if (query === '') {
	    store.commit('showAll');
	    return;
	  }
	  store.commit('hideAll');
	  const lowerQuery = query.toLowerCase();
	  for (const section of store.state.collection.values()) {
	    var _section$sectionSubTi;
	    if (section.sectionTitle.toLowerCase().includes(lowerQuery) || (_section$sectionSubTi = section.sectionSubTitle) != null && _section$sectionSubTi.toLowerCase().includes(lowerQuery)) {
	      store.commit('showSection', {
	        sectionCode: section.sectionCode
	      });
	      continue;
	    }
	    for (const item of section.rights.values()) {
	      if (!item.title.toLowerCase().includes(lowerQuery)) {
	        continue;
	      }
	      if (item.groupHead) {
	        store.commit('showGroup', {
	          sectionCode: section.sectionCode,
	          groupId: item.id
	        });
	      } else {
	        store.commit('showItem', {
	          sectionCode: section.sectionCode,
	          itemId: item.id
	        });
	        if (item.group) {
	          store.commit('expandGroup', {
	            sectionCode: section.sectionCode,
	            groupId: item.group
	          });
	        }
	      }
	    }
	  }
	}

	const MenuCell = {
	  name: 'MenuCell',
	  components: {
	    CellLayout,
	    RichMenuPopup: ui_vue3_components_richMenu.RichMenuPopup,
	    RichMenuItem: ui_vue3_components_richMenu.RichMenuItem
	  },
	  inject: ['section', 'userGroup'],
	  data() {
	    return {
	      isMenuShown: false
	    };
	  },
	  computed: {
	    RichMenuItemIcon: () => ui_vue3_components_richMenu.RichMenuItemIcon,
	    ...ui_vue3_vuex.mapGetters({
	      isMaxValueSetForAny: 'accessRights/isMaxValueSetForAny',
	      isMinValueSetForAny: 'accessRights/isMinValueSetForAny'
	    }),
	    menuPopupOptions() {
	      const width = 290;
	      return {
	        bindElement: this.$refs.icon,
	        width,
	        // by default popup is positioned so that the left top angle is below the bind element.
	        // we need to position it in the center of the column
	        offsetLeft: -Math.floor(width / 2) + 9
	      };
	    },
	    shownUserGroupsWithoutCurrent() {
	      const shown = this.$store.getters['userGroups/shown'];
	      const shownWithoutCurrent = main_core.Runtime.clone(shown);
	      shownWithoutCurrent.delete(this.userGroup.id);
	      return shownWithoutCurrent;
	    },
	    applyDialogItems() {
	      return ItemsMapper.mapUserGroups(this.shownUserGroupsWithoutCurrent);
	    }
	  },
	  methods: {
	    toggleMenu() {
	      this.isMenuShown = !this.isMenuShown;
	    },
	    showApplyDialog() {
	      this.isMenuShown = false;
	      const applyDialog = new ui_entitySelector.Dialog({
	        context: EntitySelectorContext.ROLE,
	        targetNode: this.$refs.icon,
	        multiple: false,
	        dropdownMode: true,
	        enableSearch: true,
	        cacheable: false,
	        items: this.applyDialogItems,
	        events: {
	          'Item:onSelect': dialogEvent => {
	            const {
	              item
	            } = dialogEvent.getData();
	            this.$store.dispatch('userGroups/copySectionValues', {
	              srcUserGroupId: this.userGroup.id,
	              dstUserGroupId: item.getId(),
	              sectionCode: this.section.sectionCode
	            });
	          }
	        }
	      });
	      applyDialog.show();
	    },
	    setMaxValuesInSection() {
	      this.isMenuShown = false;
	      this.$store.dispatch('userGroups/setMaxAccessRightValuesInSection', {
	        userGroupId: this.userGroup.id,
	        sectionCode: this.section.sectionCode
	      });
	    },
	    setMinValuesInSection() {
	      this.isMenuShown = false;
	      this.$store.dispatch('userGroups/setMinAccessRightValuesInSection', {
	        userGroupId: this.userGroup.id,
	        sectionCode: this.section.sectionCode
	      });
	    }
	  },
	  template: `
		<CellLayout class="ui-access-rights-v2-menu-cell" style="cursor: pointer" @click="toggleMenu">
			<div
				ref="icon"
				class="ui-icon-set --more ui-access-rights-v2-icon-more"
			>
				<RichMenuPopup
					v-if="isMenuShown"
					@close="isMenuShown = false"
					:popup-options="menuPopupOptions"
				>
					<RichMenuItem
						v-if="isMaxValueSetForAny"
						:icon="RichMenuItemIcon.check"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MAX_ACCESS_RIGHTS')"
						:subtitle="$Bitrix.Loc.getMessage(
							'JS_UI_ACCESSRIGHTS_V2_SET_MAX_ACCESS_RIGHTS_SUBTITLE_SECTION',
							{
								'#SECTION#': section.sectionTitle + (section.sectionSubTitle ? (' ' + section.sectionSubTitle) : ''),
							}
						)"
						@click="setMaxValuesInSection"
					/>
					<RichMenuItem
						v-if="isMinValueSetForAny"
						:icon="RichMenuItemIcon['red-lock']"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MIN_ACCESS_RIGHTS')"
						:subtitle="$Bitrix.Loc.getMessage(
							'JS_UI_ACCESSRIGHTS_V2_SET_MIN_ACCESS_RIGHTS_SUBTITLE_SECTION',
							{
								'#SECTION#': section.sectionTitle + (section.sectionSubTitle ? (' ' + section.sectionSubTitle) : ''),
							}
						)"
						@click="setMinValuesInSection"
					/>
					<RichMenuItem
						:icon="RichMenuItemIcon.copy"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_APPLY_TO_ROLE')"
						:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_APPLY_TO_ROLE_SUBTITLE')"
						@click="showApplyDialog"
					/>
				</RichMenuPopup>
			</div>
		</CellLayout>
	`
	};

	const Icon = {
	  name: 'Icon',
	  inject: ['section'],
	  computed: {
	    iconBgColor() {
	      if (this.section.sectionIcon.bgColor.startsWith('--')) {
	        // css variable
	        return `var(${this.section.sectionIcon.bgColor})`;
	      }

	      // we assume its hex
	      return this.section.sectionIcon.bgColor;
	    }
	  },
	  template: `
		<div v-if="section.sectionIcon" class="ui-access-rights-v2-section-header-icon" :style="{
			backgroundColor: iconBgColor,
		}">
			<div class="ui-icon-set" :class="'--' + section.sectionIcon.type"></div>
		</div>
	`
	};

	const Locator = {
	  name: 'Locator',
	  components: {
	    SectionIcon: Icon
	  },
	  props: {
	    maxWidth: {
	      type: Number,
	      // same as value popup width
	      default: 430
	    }
	  },
	  inject: ['section', 'right'],
	  computed: {
	    rightOrGroupTitle() {
	      if (!this.right.group) {
	        return this.right.title;
	      }
	      const groupHead = this.section.rights.get(this.right.group);
	      return groupHead == null ? void 0 : groupHead.title;
	    }
	  },
	  template: `
		<div class="ui-access-rights-v2-cell-popup-header-locator" :style="{
			maxWidth: maxWidth + 'px',
		}">
			<SectionIcon/>
			<span
				class="ui-access-rights-v2-text-ellipsis"
				:title="section.sectionTitle"
			>{{ section.sectionTitle }}</span>
			<span
				v-if="section.sectionSubTitle" 
				class="ui-access-rights-v2-text-ellipsis"
				:title="section.sectionSubTitle"
				style="margin-left: 5px; color: var(--ui-color-palette-gray-70);"
			>{{ section.sectionSubTitle }}</span>
			<div class="ui-icon-set --chevron-right ui-access-rights-v2-cell-popup-header-chevron"></div>
			<template v-if="rightOrGroupTitle !== right.title">
				<span class="ui-access-rights-v2-text-ellipsis" :title="right.title">{{ right.title }}</span>
				<div class="ui-icon-set --chevron-right ui-access-rights-v2-cell-popup-header-chevron"></div>
			</template>
			<span class="ui-access-rights-v2-text-ellipsis" :title="rightOrGroupTitle">{{ rightOrGroupTitle }}</span>
		</div>
	`
	};

	const MasterSwitcher = {
	  name: 'MasterSwitcher',
	  components: {
	    Switcher: ui_vue3_components_switcher.Switcher
	  },
	  emits: ['check', 'uncheck'],
	  props: {
	    isChecked: {
	      type: Boolean,
	      required: true
	    }
	  },
	  inject: ['section', 'right'],
	  computed: {
	    switcherOptions() {
	      return {
	        size: 'small',
	        color: 'green'
	      };
	    }
	  },
	  template: `
		<div class="ui-access-rights-v2-cell-popup-header-master-switcher" :class="{
			'--checked': isChecked,
		}">
			<slot/>
			<div class="ui-access-rights-v2-cell-popup-header-toggle-container">
				<span class="ui-access-rights-v2-cell-popup-header-toggle-caption">{{
					$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ACCESS')
				}}</span>
				<Switcher
					:is-checked="isChecked"
					@check="$emit('check')"
					@uncheck="$emit('uncheck')"
					:options="switcherOptions"
					data-accessrights-min-max
				/>
			</div>
		</div>
	`
	};

	const SingleRoleTitle = {
	  name: 'SingleRoleTitle',
	  props: {
	    userGroupTitle: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<div class="ui-access-rights-v2-cell-popup-header-role-container">
			<div>
				<div class="ui-access-rights-v2-cell-popup-header-role-caption">
					{{ $Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE') }}
				</div>
				<div
					class="ui-access-rights-v2-cell-popup-header-role-title ui-access-rights-v2-text-ellipsis"
					:title="userGroupTitle"
				>
					{{ userGroupTitle }}
				</div>
			</div>
		</div>
	`
	};

	const PopupHeader = {
	  name: 'DependentVariablesPopupHeader',
	  components: {
	    Locator,
	    MasterSwitcher,
	    SingleRoleTitle
	  },
	  emits: ['setMax', 'setMin'],
	  props: {
	    values: {
	      /** @type Set<string> */
	      type: Set,
	      required: true
	    }
	  },
	  inject: ['right'],
	  computed: {
	    isChecked() {
	      if (!this.isMinMaxValuesSet) {
	        return this.values.size > 0;
	      }
	      return this.isSelectedAnythingBesidesMin;
	    },
	    isMinMaxValuesSet() {
	      return !main_core.Type.isNil(this.right.minValue) && !main_core.Type.isNil(this.right.maxValue);
	    },
	    isSelectedAnythingBesidesMin() {
	      if (this.values.size <= 0) {
	        return false;
	      }
	      for (const variableId of this.values) {
	        if (!this.right.minValue.has(variableId)) {
	          return true;
	        }
	      }
	      return false;
	    }
	  },
	  methods: {
	    setMin() {
	      if (this.isMinMaxValuesSet) {
	        this.$emit('setMin');
	      }
	    },
	    setMax() {
	      if (this.isMinMaxValuesSet) {
	        this.$emit('setMax');
	      }
	    }
	  },
	  template: `
		<div>
			<Locator/>
			<MasterSwitcher
				:is-checked="isChecked"
				@check="setMax"
				@uncheck="setMin"
			>
				<slot/>
			</MasterSwitcher>
		</div>
	`
	};

	const PopupContent = {
	  name: 'DependentVariablesPopupContent',
	  emits: ['apply'],
	  components: {
	    Switcher: ui_vue3_components_switcher.Switcher,
	    PopupHeader
	  },
	  props: {
	    // value for selector is id of a selected variable
	    initialValues: {
	      type: Set,
	      default: new Set()
	    }
	  },
	  data() {
	    return {
	      // values modified during popup lifetime and not yet dispatched to store
	      notSavedValues: this.getNotSavedValues()
	    };
	  },
	  inject: ['section', 'right', 'redefineApply'],
	  computed: {
	    isMinMaxValuesSet() {
	      return !main_core.Type.isNil(this.right.minValue) && !main_core.Type.isNil(this.right.maxValue);
	    },
	    variablesShownInList() {
	      if (!this.isMinMaxValuesSet) {
	        return this.right.variables;
	      }
	      const variablesWithoutMinAndSecondary = main_core.Runtime.clone(this.right.variables);
	      for (const variableId of this.right.minValue) {
	        variablesWithoutMinAndSecondary.delete(variableId);
	      }
	      for (const [variableId, variable] of variablesWithoutMinAndSecondary) {
	        if (variable.secondary) {
	          variablesWithoutMinAndSecondary.delete(variableId);
	        }
	      }
	      return variablesWithoutMinAndSecondary;
	    },
	    secondaryVariables() {
	      const result = new Map();
	      for (const [variableId, variable] of this.right.variables) {
	        if (variable.secondary) {
	          result.set(variableId, variable);
	        }
	      }
	      return result;
	    },
	    nothingSelectedValues() {
	      return this.$store.getters['accessRights/getNothingSelectedValue'](this.section.sectionCode, this.right.id);
	    },
	    switcherOptions() {
	      return {
	        size: 'small',
	        color: 'primary'
	      };
	    },
	    secondarySwitcherOptions() {
	      return {
	        size: 'extra-small',
	        color: 'green'
	      };
	    }
	  },
	  mounted() {
	    this.redefineApply(() => {
	      this.apply();
	    });
	  },
	  methods: {
	    addValue(variableId) {
	      const variable = this.right.variables.get(variableId);
	      if (!variable) {
	        return;
	      }
	      this.notSavedValues.add(variableId);
	      if (!main_core.Type.isNil(variable.requires)) {
	        for (const requiredId of variable.requires) {
	          this.notSavedValues.add(requiredId);
	        }
	      }
	      if (!main_core.Type.isNil(variable.conflictsWith)) {
	        // remove old variables that conflict with variable we want to add
	        for (const conflictId of variable.conflictsWith) {
	          this.notSavedValues.delete(conflictId);
	        }
	      }
	      for (const otherVariable of this.right.variables.values()) {
	        if (otherVariable.id === variableId) {
	          continue;
	        }

	        // if one of the current variables conflicts with newly added variables, we remove old variable
	        if (this.notSavedValues.has(otherVariable.id) && !main_core.Type.isNil(otherVariable.conflictsWith)) {
	          for (const conflictId of otherVariable.conflictsWith) {
	            if (this.notSavedValues.has(conflictId)) {
	              this.notSavedValues.delete(otherVariable.id);
	            }
	          }
	        }
	      }
	    },
	    removeValue(variableId) {
	      this.notSavedValues.delete(variableId);
	      for (const otherVariableId of this.notSavedValues) {
	        if (otherVariableId === variableId) {
	          continue;
	        }
	        const otherVariable = this.right.variables.get(otherVariableId);
	        if (!otherVariable) {
	          continue;
	        }
	        if (!main_core.Type.isNil(otherVariable.requires) && otherVariable.requires.has(variableId)) {
	          this.notSavedValues.delete(otherVariableId);
	        }
	      }
	    },
	    setMaxValue() {
	      for (const variableId of this.right.maxValue) {
	        this.addValue(variableId);
	      }
	    },
	    setMinValue() {
	      for (const variableId of this.right.minValue) {
	        this.addValue(variableId);
	      }
	    },
	    apply() {
	      let values = this.notSavedValues;
	      if (values.size <= 0) {
	        values = this.nothingSelectedValues;
	      }
	      this.$emit('apply', {
	        values
	      });
	    },
	    getNotSavedValues() {
	      const result = new Set();
	      this.initialValues.forEach(value => {
	        if (this.right.variables.has(value)) {
	          result.add(value);
	        }
	      });
	      return result;
	    }
	  },
	  // data attributes are needed for e2e automated tests
	  template: `
		<div>
			<PopupHeader
				:values="notSavedValues"
				@set-max="setMaxValue"
				@set-min="setMinValue"
			>
				<slot name="role-title"/>
			</PopupHeader>
			<div class="ui-access-rights-v2-dv-popup--line-container">
				<div
					v-for="[variableId, variable] in variablesShownInList"
					:key="variableId"
					class="ui-access-rights-v2-dv-popup--line"
				>
					<span class="ui-access-rights-v2-text-ellipsis" :title="variable.title">{{ variable.title }}</span>
					<Switcher
						:is-checked="notSavedValues.has(variable.id)"
						@check="addValue(variable.id)"
						@uncheck="removeValue(variable.id)"
						:options="switcherOptions"
						:data-accessrights-variable-id="variable.id"
					/>
				</div>
				<div
					v-for="[variableId, variable] in secondaryVariables"
					:key="variableId"
					class="ui-access-rights-v2-dv-popup--line --secondary"
				>
					<Switcher
						:is-checked="notSavedValues.has(variable.id)"
						@check="addValue(variable.id)"
						@uncheck="removeValue(variable.id)"
						:options="secondarySwitcherOptions"
						style="padding-right: 5px;"
						:data-accessrights-variable-id="variable.id"
					/>
					<span class="ui-access-rights-v2-text-ellipsis">{{ variable.title }}</span>
				</div>
			</div>
		</div>
	`
	};

	const AllRolesTitle = {
	  name: 'AllRolesTitle',
	  template: `
		<div class="ui-access-rights-v2-cell-popup-header-all-role-container">
			<div class="ui-icon-set --persons-3" style="margin-right: 4px;"></div>
			<div class="ui-access-rights-v2-cell-popup-header-all-roles-caption">{{ 
				$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ALL_ROLES')
			}}</div>
		</div>
	`
	};

	const ValuePopup = {
	  name: 'ValuePopup',
	  components: {
	    Popup: ui_vue3_components_popup.Popup
	  },
	  emits: ['close', 'apply'],
	  provide() {
	    return {
	      redefineApply: func => {
	        this.onApply = func;
	      }
	    };
	  },
	  data() {
	    return {
	      onApply: () => {
	        this.$emit('apply');
	      }
	    };
	  },
	  computed: {
	    popupOptions() {
	      return {
	        autoHide: true,
	        closeEsc: true,
	        cacheable: false,
	        minWidth: 466,
	        padding: 18
	      };
	    }
	  },
	  mounted() {
	    void this.$nextTick(() => {
	      const applyButton = new ui_buttons.ApplyButton({
	        color: ui_buttons.ButtonColor.PRIMARY,
	        onclick: () => {
	          this.apply();
	          this.$emit('close');
	        }
	      });
	      applyButton.renderTo(this.$refs['button-container']);
	      const cancelButton = new ui_buttons.CancelButton({
	        onclick: () => {
	          this.$emit('close');
	        }
	      });
	      cancelButton.renderTo(this.$refs['button-container']);
	    });
	  },
	  methods: {
	    apply() {
	      this.onApply();
	    }
	  },
	  template: `
		<Popup @close="$emit('close')" :options="popupOptions">
			<slot/>
			<div ref="button-container" class="ui-access-rights-v2-value-popup-buttons"></div>
		</Popup>
	`
	};

	const DependentVariables$1 = {
	  name: 'DependentVariables',
	  components: {
	    PopupContent,
	    AllRolesTitle,
	    ValuePopup
	  },
	  emits: ['close'],
	  inject: ['section', 'right'],
	  methods: {
	    apply({
	      values
	    }) {
	      this.$store.dispatch('userGroups/setAccessRightValuesForShown', {
	        sectionCode: this.section.sectionCode,
	        valueId: this.right.id,
	        values
	      });
	    }
	  },
	  template: `
		<ValuePopup @close="$emit('close')">
			<PopupContent @apply="apply">
				<template #role-title>
					<AllRolesTitle/>
				</template>
			</PopupContent>
		</ValuePopup>
	`
	};

	let _ = t => t,
	  _t,
	  _t2;
	var _toggleSelectButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("toggleSelectButtons");
	var _selectAll = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectAll");
	var _deselectAll = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deselectAll");
	var _onItemStatusChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onItemStatusChange");
	class Footer extends ui_entitySelector.DefaultFooter {
	  constructor(dialog, options) {
	    super(dialog, options);
	    Object.defineProperty(this, _onItemStatusChange, {
	      value: _onItemStatusChange2
	    });
	    Object.defineProperty(this, _deselectAll, {
	      value: _deselectAll2
	    });
	    Object.defineProperty(this, _selectAll, {
	      value: _selectAll2
	    });
	    Object.defineProperty(this, _toggleSelectButtons, {
	      value: _toggleSelectButtons2
	    });
	    this.selectAllButton = main_core.Tag.render(_t || (_t = _`<div class="ui-selector-footer-link">${0}</div>`), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ALL_SELECT_LABEL'));
	    main_core.Dom.hide(this.selectAllButton);
	    main_core.Event.bind(this.selectAllButton, 'click', babelHelpers.classPrivateFieldLooseBase(this, _selectAll)[_selectAll].bind(this));
	    this.deselectAllButton = main_core.Tag.render(_t2 || (_t2 = _`<div class="ui-selector-footer-link">${0}</div>`), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ALL_DESELECT_LABEL'));
	    main_core.Dom.hide(this.deselectAllButton);
	    main_core.Event.bind(this.deselectAllButton, 'click', babelHelpers.classPrivateFieldLooseBase(this, _deselectAll)[_deselectAll].bind(this));
	    this.getDialog().subscribe('Item:onSelect', babelHelpers.classPrivateFieldLooseBase(this, _onItemStatusChange)[_onItemStatusChange].bind(this));
	    this.getDialog().subscribe('Item:onDeselect', babelHelpers.classPrivateFieldLooseBase(this, _onItemStatusChange)[_onItemStatusChange].bind(this));
	  }
	  getContent() {
	    babelHelpers.classPrivateFieldLooseBase(this, _toggleSelectButtons)[_toggleSelectButtons]();
	    return [this.selectAllButton, this.deselectAllButton];
	  }
	}
	function _toggleSelectButtons2() {
	  if (this.getDialog().getSelectedItems().length === this.getDialog().getItems().length) {
	    main_core.Dom.hide(this.selectAllButton);
	    main_core.Dom.show(this.deselectAllButton);
	  } else {
	    main_core.Dom.show(this.selectAllButton);
	    main_core.Dom.hide(this.deselectAllButton);
	  }
	}
	function _selectAll2() {
	  this.getDialog().getItems().forEach(item => {
	    item.select();
	  });
	}
	function _deselectAll2() {
	  this.getDialog().getSelectedItems().forEach(item => {
	    item.deselect();
	  });
	}
	function _onItemStatusChange2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _toggleSelectButtons)[_toggleSelectButtons]();
	}

	let _$1 = t => t,
	  _t$1;
	var _renderVueApp = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderVueApp");
	class Header$1 extends ui_entitySelector.BaseHeader {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _renderVueApp, {
	      value: _renderVueApp2
	    });
	  }
	  render() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _renderVueApp)[_renderVueApp]();
	  }
	}
	function _renderVueApp2() {
	  const container = main_core.Tag.render(_t$1 || (_t$1 = _$1`<div style="padding: 20px 20px 0;"></div>`));
	  const app = ui_vue3.BitrixVue.createApp(Locator, {
	    maxWidth: this.getDialog().getWidth()
	  });
	  app.provide('section', this.getOption('section'));
	  app.provide('right', this.getOption('right'));
	  app.mount(container);
	  return container;
	}

	const Selector$1 = {
	  name: 'Selector',
	  emits: ['apply', 'close'],
	  props: {
	    // value for selector is id of a selected variable
	    initialValues: {
	      type: Set,
	      default: new Set()
	    }
	  },
	  inject: ['section', 'right'],
	  data() {
	    return {
	      // values modified during popup lifetime and not yet dispatched to store
	      values: this.initialValues
	    };
	  },
	  dialog: null,
	  computed: {
	    isAllSelected() {
	      return this.values.has(this.right.allSelectedCode);
	    },
	    selectedVariables() {
	      return getSelectedVariables(this.right.variables, this.values, this.isAllSelected);
	    },
	    dialogItems() {
	      return ItemsMapper.mapVariables(this.right.variables);
	    },
	    selectedDialogItems() {
	      return this.dialogItems.filter(item => this.selectedVariables.has(item.id));
	    }
	  },
	  mounted() {
	    this.showSelector();
	  },
	  beforeUnmount() {
	    var _this$dialog;
	    (_this$dialog = this.dialog) == null ? void 0 : _this$dialog.hide();
	  },
	  methods: {
	    showSelector() {
	      this.dialog = new ui_entitySelector.Dialog({
	        height: 400,
	        context: EntitySelectorContext.VARIABLE,
	        enableSearch: this.right.enableSearch,
	        multiple: true,
	        autoHide: true,
	        hideByEsc: true,
	        dropdownMode: true,
	        compactView: this.right.compactView,
	        showAvatars: this.right.showAvatars,
	        selectedItems: this.selectedDialogItems,
	        searchOptions: {
	          allowCreateItem: false
	        },
	        cacheable: false,
	        events: {
	          'Item:onSelect': this.onItemSelect,
	          'Item:onDeselect': this.onItemDeselect,
	          onHide: this.apply,
	          onDestroy: () => {
	            this.dialog = null;
	          }
	        },
	        entities: [{
	          id: EntitySelectorEntities.VARIABLE
	        }],
	        items: this.dialogItems,
	        header: Header$1,
	        headerOptions: {
	          section: this.section,
	          right: this.right
	        },
	        footer: Footer
	      });
	      this.dialog.show();
	    },
	    onItemSelect(event) {
	      const addedItem = event.getData().item;
	      this.addValue(String(addedItem.getId()));
	    },
	    onItemDeselect(event) {
	      const removedItem = event.getData().item;
	      this.removeValue(String(removedItem.getId()));
	    },
	    addValue(value) {
	      const newValues = main_core.Runtime.clone(this.values);
	      newValues.add(value);
	      if (newValues.length >= this.right.variables.size) {
	        this.setValues(new Set([this.right.allSelectedCode]));
	      } else {
	        this.setValues(newValues);
	      }
	    },
	    removeValue(value) {
	      if (this.values.has(this.right.allSelectedCode)) {
	        const allVariablesIds = [...this.right.variables.values()].map(variable => variable.id);
	        const allVariablesIdsWithoutRemoved = new Set(allVariablesIds);
	        allVariablesIdsWithoutRemoved.delete(value);
	        this.setValues(allVariablesIdsWithoutRemoved);
	      } else {
	        const newValues = [...this.values].filter(candidate => candidate !== value);
	        this.setValues(new Set(newValues));
	      }
	    },
	    setValues(newValues) {
	      this.values = newValues;
	    },
	    apply() {
	      this.setNothingSelectedValueIfNeeded();
	      this.$emit('apply', {
	        values: this.values
	      });
	      this.$emit('close');
	    },
	    setNothingSelectedValueIfNeeded() {
	      if (this.values.size <= 0) {
	        const nothingSelected = this.$store.getters['accessRights/getNothingSelectedValue'](this.section.sectionCode, this.right.id);
	        for (const nothing of nothingSelected) {
	          this.addValue(nothing);
	        }
	      }
	    }
	  },
	  template: `
		<div></div>
	`
	};

	const Multivariables$1 = {
	  name: 'Multivariables',
	  emits: ['close'],
	  components: {
	    Selector: Selector$1
	  },
	  inject: ['section', 'right'],
	  methods: {
	    apply({
	      values
	    }) {
	      this.$store.dispatch('userGroups/setAccessRightValuesForShown', {
	        sectionCode: this.section.sectionCode,
	        valueId: this.right.id,
	        values
	      });
	    },
	    close() {
	      this.$emit('close');
	    }
	  },
	  template: `
		<Selector @apply="apply" @close="close"/>
	`
	};

	const POPUP_ID = 'ui-access-rights-v2-row-value-variables';
	const Variables$1 = {
	  name: 'Variables',
	  emits: ['close'],
	  inject: ['section', 'right'],
	  mounted() {
	    this.showSelector();
	  },
	  beforeUnmount() {
	    this.closeSelector();
	  },
	  methods: {
	    showSelector() {
	      const menuItems = [];
	      for (const variable of this.right.variables.values()) {
	        menuItems.push({
	          id: variable.id,
	          text: variable.title,
	          onclick: (innerEvent, item) => {
	            var _item$getMenuWindow;
	            (_item$getMenuWindow = item.getMenuWindow()) == null ? void 0 : _item$getMenuWindow.close();
	            this.setValue(variable.id);
	          }
	        });
	      }
	      main_popup.MenuManager.show({
	        id: POPUP_ID,
	        bindElement: this.$el,
	        items: menuItems,
	        autoHide: true,
	        cacheable: false,
	        events: {
	          onClose: () => {
	            this.$emit('close');
	          }
	        }
	      });
	    },
	    setValue(value) {
	      this.$store.dispatch('userGroups/setAccessRightValuesForShown', {
	        sectionCode: this.section.sectionCode,
	        valueId: this.right.id,
	        values: new Set([value])
	      });
	    },
	    closeSelector() {
	      var _MenuManager$getMenuB;
	      (_MenuManager$getMenuB = main_popup.MenuManager.getMenuById(POPUP_ID)) == null ? void 0 : _MenuManager$getMenuB.close();
	    }
	  },
	  // invisible div for binding selector to it
	  template: `
		<div></div>
	`
	};

	/**
	 * A special case of Hint. We don't need interactivity here, but we do need to wrap slot with a hint.
	 * Combine these properties in a single vue hint wrapper is impossible.
	 */
	const SelectedHint = {
	  name: 'SelectedHint',
	  props: {
	    html: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isRendered: true
	    };
	  },
	  watch: {
	    html() {
	      // force hint directive to re-render
	      this.isRendered = false;
	      void this.$nextTick(() => {
	        this.isRendered = true;
	      });
	    }
	  },
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  // offsetTop is needed to fix infinite mouseenter/mouseleave loop in chromium. issue 204272
	  template: `
		<div v-if="isRendered" v-hint="{
			html,
			popupOptions: {
				offsetTop: 3,
			},
		}" data-hint-init="vue">
			<slot/>
		</div>
	`
	};

	const DependentVariables$2 = {
	  name: 'DependentVariables',
	  components: {
	    ValuePopup,
	    PopupContent,
	    SelectedHint,
	    SingleRoleTitle
	  },
	  props: {
	    // value for selector is id of a selected variable
	    value: {
	      /** @type AccessRightValue */
	      type: Object,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isPopupShown: false
	    };
	  },
	  inject: ['section', 'userGroup', 'right'],
	  computed: {
	    selectedVariables() {
	      return getSelectedVariables(this.right.variables, this.value.values, false);
	    },
	    currentAlias() {
	      return this.$store.getters['accessRights/getSelectedVariablesAlias'](this.section.sectionCode, this.value.id, this.value.values);
	    },
	    title() {
	      if (main_core.Type.isString(this.currentAlias)) {
	        return this.currentAlias;
	      }
	      if (this.selectedVariables.size <= 0) {
	        return this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ADD');
	      }
	      return getMultipleSelectedVariablesTitle(this.selectedVariables);
	    },
	    hintHtml() {
	      return getMultipleSelectedVariablesHintHtml(this.selectedVariables, this.hintTitle, this.right.variables);
	    },
	    hintTitle() {
	      if (main_core.Type.isString(this.right.hintTitle)) {
	        return this.right.hintTitle;
	      }
	      return this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SELECTED_ITEMS_TITLE');
	    }
	  },
	  methods: {
	    apply({
	      values
	    }) {
	      this.$store.dispatch('userGroups/setAccessRightValues', {
	        sectionCode: this.section.sectionCode,
	        userGroupId: this.userGroup.id,
	        valueId: this.value.id,
	        values
	      });
	    }
	  },
	  template: `
		<div class='ui-access-rights-v2-column-item-text-link' :class="{
			'ui-access-rights-v2-text-ellipsis': !hintHtml
		}" @click="isPopupShown = true">
			<SelectedHint v-if="hintHtml" :html="hintHtml">{{title}}</SelectedHint>
			<div v-else :title="title">{{title}}</div>
			<ValuePopup v-if="isPopupShown" @close="isPopupShown = false">
				<PopupContent
					@apply="apply"
					:initial-values="value.values"
				>
					<template #role-title>
						<SingleRoleTitle :user-group-title="userGroup.title"/>
					</template>
				</PopupContent>
			</ValuePopup>
		</div>
	`
	};

	const Multivariables$2 = {
	  name: 'Multivariables',
	  components: {
	    SelectedHint,
	    Selector: Selector$1
	  },
	  props: {
	    // value for selector is id of a selected variable
	    value: {
	      /** @type AccessRightValue */
	      type: Object,
	      required: true
	    }
	  },
	  inject: ['section', 'userGroup', 'right'],
	  data() {
	    return {
	      isSelectorShown: false
	    };
	  },
	  computed: {
	    isAllSelected() {
	      return this.value.values.has(this.right.allSelectedCode);
	    },
	    selectedVariables() {
	      return getSelectedVariables(this.right.variables, this.value.values, this.isAllSelected);
	    },
	    currentAlias() {
	      return this.$store.getters['accessRights/getSelectedVariablesAlias'](this.section.sectionCode, this.value.id, this.value.values);
	    },
	    title() {
	      if (main_core.Type.isString(this.currentAlias)) {
	        return this.currentAlias;
	      }
	      if (this.isAllSelected) {
	        return this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ALL_ACCEPTED');
	      }
	      if (this.selectedVariables.size <= 0) {
	        return this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ADD');
	      }
	      return getMultipleSelectedVariablesTitle(this.selectedVariables);
	    },
	    hintHtml() {
	      return getMultipleSelectedVariablesHintHtml(this.selectedVariables, this.hintTitle, this.right.variables);
	    },
	    hintTitle() {
	      if (main_core.Type.isString(this.right.hintTitle)) {
	        return this.right.hintTitle;
	      }
	      return this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SELECTED_ITEMS_TITLE');
	    }
	  },
	  methods: {
	    showSelector() {
	      this.isSelectorShown = true;
	    },
	    setValues({
	      values
	    }) {
	      this.$store.dispatch('userGroups/setAccessRightValues', {
	        sectionCode: this.section.sectionCode,
	        userGroupId: this.userGroup.id,
	        valueId: this.value.id,
	        values
	      });
	    }
	  },
	  template: `
		<SelectedHint 
			v-if="hintHtml"
			:html="hintHtml" 
			class='ui-access-rights-v2-column-item-text-link'
			@click="showSelector"
			v-bind="$attrs"
		>
			{{ title }}
		</SelectedHint>
		<div 
			v-else
			class='ui-access-rights-v2-column-item-text-link ui-access-rights-v2-text-ellipsis'
			@click="showSelector"
			:title="title"
			v-bind="$attrs"
		>
			{{ title }}
		</div>
		<Selector
			v-if="isSelectorShown" 
			:initial-values="value.values"
			@close="isSelectorShown = false"
			@apply="setValues"
		/>
	`
	};

	const Toggler$1 = {
	  name: 'Toggler',
	  components: {
	    Switcher: ui_vue3_components_switcher.Switcher
	  },
	  props: {
	    value: {
	      /** @type AccessRightValue */
	      type: Object,
	      required: true
	    }
	  },
	  inject: ['section', 'userGroup'],
	  computed: {
	    isChecked() {
	      return this.value.values.has('1');
	    }
	  },
	  methods: {
	    setValue(value) {
	      this.$store.dispatch('userGroups/setAccessRightValues', {
	        userGroupId: this.userGroup.id,
	        sectionCode: this.section.sectionCode,
	        valueId: this.value.id,
	        values: new Set([value])
	      });
	    }
	  },
	  // eslint-disable-next-line quotes
	  template: `
		<Switcher
			:is-checked="isChecked"
			@check="setValue('1')"
			@uncheck="setValue('0')"
			:options="{
				size: 'extra-small',
				color: 'green',
			}"
		/>
	`
	};

	const POPUP_ID$1 = 'ui-access-rights-v2-column-item-popup-variables';
	const Variables$2 = {
	  name: 'Variables',
	  props: {
	    // value for selector is id of a selected variable
	    value: {
	      /** @type AccessRightValue */
	      type: Object,
	      required: true
	    }
	  },
	  inject: ['section', 'userGroup', 'right'],
	  computed: {
	    emptyVariableId() {
	      const emptyValue = this.$store.getters['accessRights/getEmptyValue'](this.section.sectionCode, this.value.id);
	      return emptyValue[0];
	    },
	    currentVariableId() {
	      if (this.value.values.size <= 0) {
	        return this.emptyVariableId;
	      }
	      const [firstItem] = this.value.values;
	      return firstItem;
	    },
	    currentAlias() {
	      return this.$store.getters['accessRights/getSelectedVariablesAlias'](this.section.sectionCode, this.value.id, this.value.values);
	    },
	    currentVariableTitle() {
	      if (main_core.Type.isString(this.currentAlias)) {
	        return this.currentAlias;
	      }
	      const variable = this.right.variables.get(this.currentVariableId);
	      if (!variable) {
	        return this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ADD');
	      }
	      return variable.title;
	    }
	  },
	  methods: {
	    showSelector(event) {
	      const menuItems = [];
	      for (const variable of this.right.variables.values()) {
	        menuItems.push({
	          id: variable.id,
	          text: variable.title,
	          onclick: (innerEvent, item) => {
	            var _item$getMenuWindow;
	            (_item$getMenuWindow = item.getMenuWindow()) == null ? void 0 : _item$getMenuWindow.close();
	            this.setValue(variable.id);
	          }
	        });
	      }
	      main_popup.MenuManager.show({
	        id: POPUP_ID$1,
	        bindElement: event.target,
	        items: menuItems,
	        autoHide: true,
	        cacheable: false
	      });
	    },
	    setValue(value) {
	      this.$store.dispatch('userGroups/setAccessRightValues', {
	        sectionCode: this.section.sectionCode,
	        userGroupId: this.userGroup.id,
	        valueId: this.value.id,
	        values: new Set([value])
	      });
	    }
	  },
	  template: `
		<div
			class='ui-access-rights-v2-column-item-text-link ui-access-rights-v2-text-ellipsis'
			:title="currentVariableTitle"
			@click="showSelector"
		>
			{{ currentVariableTitle }}
		</div>
	`
	};

	const Cells = Object.freeze({
	  DependentVariables: DependentVariables$2,
	  Multivariables: Multivariables$2,
	  Toggler: Toggler$1,
	  Variables: Variables$2
	});
	const Rows = Object.freeze({
	  DependentVariables: DependentVariables$1,
	  Multivariables: Multivariables$1,
	  // no row value for toggler
	  Variables: Variables$1
	});
	function getValueComponent(accessRightItem) {
	  const type = ServiceLocator.getValueTypeByRight(accessRightItem);
	  if (!type) {
	    // vue will render empty cell
	    return '';
	  }
	  return type.getComponentName();
	}

	const ValueCell = {
	  name: 'ValueCell',
	  components: {
	    CellLayout,
	    ...Cells
	  },
	  props: {
	    right: {
	      /** @type AccessRightItem */
	      type: Object,
	      required: true
	    }
	  },
	  inject: ['section', 'userGroup'],
	  provide() {
	    return {
	      right: this.right
	    };
	  },
	  computed: {
	    value() {
	      const value = this.userGroup.accessRights.get(this.right.id);
	      return value || this.$store.getters['userGroups/getEmptyAccessRightValue'](this.userGroup.id, this.section.sectionCode, this.right.id);
	    },
	    cellComponent() {
	      return getValueComponent(this.right);
	    }
	  },
	  // data attributes are needed for e2e automated tests
	  template: `
		<CellLayout
			:class="{
				'ui-access-rights-v2-group-children': right.group,
				'--modified': value.isModified
			}"
			v-memo="[userGroup.id, value.values, value.isModified]"
		>
			<Component
				:is="cellComponent"
				:value="value"
				:data-accessrights-right-id="right.id"
			/>
		</CellLayout>
	`
	};

	const Column = {
	  name: 'Column',
	  components: {
	    ColumnLayout,
	    ValueCell,
	    MenuCell
	  },
	  props: {
	    userGroup: {
	      /** @type UserGroup */
	      type: Object,
	      required: true
	    },
	    rights: {
	      type: Map,
	      required: true
	    }
	  },
	  provide() {
	    return {
	      userGroup: ui_vue3.computed(() => this.userGroup)
	    };
	  },
	  computed: {
	    renderedRights() {
	      const result = new Map();
	      for (const [rightId, right] of this.rights) {
	        if (shouldRowBeRendered(right)) {
	          result.set(rightId, right);
	        }
	      }
	      return result;
	    }
	  },
	  template: `
		<ColumnLayout ref="column">
			<MenuCell/>
			<ValueCell
				v-for="[rightId, accessRightItem] in renderedRights"
				:key="rightId"
				:right="accessRightItem"
			/>
		</ColumnLayout>
	`
	};

	const ColumnList = {
	  name: 'ColumnList',
	  components: {
	    Column,
	    SyncHorizontalScroll,
	    ColumnLayout
	  },
	  props: {
	    userGroups: {
	      type: Map,
	      required: true
	    },
	    rights: {
	      type: Map,
	      required: true
	    }
	  },
	  throttledScrollHandler: null,
	  throttledResizeHandler: null,
	  ears: null,
	  isEarsInited: false,
	  data() {
	    return {
	      isLeftShadowShown: false,
	      isRightShadowShown: false
	    };
	  },
	  created() {
	    this.throttledScrollHandler = main_core.Runtime.throttle(() => {
	      this.adjustShadowsVisibility();
	    }, 200);
	    this.throttledResizeHandler = main_core.Runtime.throttle(() => {
	      this.adjustShadowsVisibility();
	      this.adjustEars();
	    }, 200);
	  },
	  mounted() {
	    main_core.Event.bind(window, 'resize', this.throttledResizeHandler);
	    this.adjustShadowsVisibility();
	    this.initEars();
	  },
	  beforeUnmount() {
	    this.destroyEars();
	    main_core.Event.unbind(window, 'resize', this.throttledResizeHandler);
	  },
	  watch: {
	    userGroups(newValue, oldValue) {
	      if (newValue.size !== oldValue.size) {
	        this.adjustShadowsVisibility();
	        this.adjustEars();
	      }
	    }
	  },
	  methods: {
	    calculateShadowsVisibility() {
	      if (!this.$refs['column-container']) {
	        // in case it's accidentally called before mount or after unmount
	        return {
	          isLeftShadowShown: false,
	          isRightShadowShown: false
	        };
	      }
	      const scrollLeft = this.$refs['column-container'].$el.scrollLeft;
	      const isLeftShadowShown = scrollLeft > 0;
	      const offsetWidth = this.$refs['column-container'].$el.offsetWidth;
	      return {
	        isLeftShadowShown,
	        isRightShadowShown: this.$refs['column-container'].$el.scrollWidth > Math.round(scrollLeft + offsetWidth)
	      };
	    },
	    adjustShadowsVisibility() {
	      // avoid "forced synchronous layout"
	      requestAnimationFrame(() => {
	        const {
	          isLeftShadowShown,
	          isRightShadowShown
	        } = this.calculateShadowsVisibility();
	        this.isLeftShadowShown = isLeftShadowShown;
	        this.isRightShadowShown = isRightShadowShown;
	      });
	    },
	    adjustEars() {
	      if (!this.isEarsInited) {
	        return;
	      }

	      // avoid "forced synchronous layout"
	      requestAnimationFrame(() => {
	        // force ears to recalculate its visibility
	        this.ears.toggleEars();
	      });
	    },
	    initEars() {
	      if (!this.$refs['column-container']) {
	        return;
	      }
	      if (this.ears) {
	        return;
	      }
	      this.ears = new ui_ears.Ears({
	        container: this.$refs['column-container'].$el,
	        immediateInit: true,
	        smallSize: true
	      });

	      // chrome is not happy when we query DOM values (scrollLeft, offsetWidth, ...) just after we've changed them
	      // avoid "forced synchronous layout"
	      requestAnimationFrame(() => {
	        if (!this.ears || !this.$refs['column-container']) {
	          this.ears = null;

	          // sometimes the callback is fired after the component is unmounted
	          return;
	        }
	        const scrollLeft = this.$refs['column-container'].$el.scrollLeft;
	        this.ears.init();

	        // Ears add wrapper around the container, and it breaks our markup a little. Fix it
	        main_core.Dom.style(this.ears.getWrapper(), 'flex', 1);
	        if (scrollLeft > 0) {
	          // ears.init resets scrollLeft to 0
	          this.$refs['column-container'].$el.scrollLeft = scrollLeft;
	        }
	        this.isEarsInited = true;
	      });
	    },
	    destroyEars() {
	      var _this$ears;
	      (_this$ears = this.ears) == null ? void 0 : _this$ears.destroy();
	      this.isEarsInited = false;
	      this.ears = null;
	    }
	  },
	  template: `
		<div
			class='ui-access-rights-v2-section-content'
			:class="{
				'ui-access-rights-v2-section-shadow-left-shown': isLeftShadowShown,
				'ui-access-rights-v2-section-shadow-right-shown': isRightShadowShown,
			}"
		>
			<SyncHorizontalScroll
				ref="column-container"
				class='ui-access-rights-v2-section-wrapper'
				@scroll="throttledScrollHandler"
			>
				<Column
					v-for="[groupId, group] in userGroups"
					:key="groupId"
					:user-group="group"
					:rights="rights"
					:data-accessrights-user-group-id="groupId"
				/>
				<ColumnLayout/>
			</SyncHorizontalScroll>
		</div>
	`
	};

	let _$2 = t => t,
	  _t$2;

	/**
	 * A special case of Hint that provides interactivity and reactivity.
	 */
	const Hint = {
	  name: 'Hint',
	  props: {
	    html: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    ...ui_vue3_vuex.mapState({
	      guid: state => state.application.guid
	    })
	  },
	  mounted() {
	    this.renderHint();
	  },
	  watch: {
	    html() {
	      // make ui.hint reactive :(
	      main_core.Dom.clean(this.$refs.container);
	      this.renderHint();
	    }
	  },
	  methods: {
	    renderHint() {
	      const hintIconWrapper = main_core.Tag.render(_t$2 || (_t$2 = _$2`<span data-hint-html="true" data-hint-interactivity="true"></span>`));
	      // Tag.render cant set prop value with HTML properly :(
	      hintIconWrapper.setAttribute('data-hint', this.html);
	      main_core.Dom.append(hintIconWrapper, this.$refs.container);
	      this.getHintManager().initNode(hintIconWrapper);
	    },
	    getHintManager() {
	      return ServiceLocator.getHint(this.guid);
	    }
	  },
	  template: '<span ref="container"></span>'
	};

	const Header$2 = {
	  name: 'Header',
	  components: {
	    Hint,
	    Icon
	  },
	  inject: ['section'],
	  methods: {
	    toggleSection() {
	      this.$store.dispatch('accessRights/toggleSection', {
	        sectionCode: this.section.sectionCode
	      });
	    }
	  },
	  template: `
		<div
			@click="toggleSection"
			class='ui-access-rights-v2-section-header'
			:class="{
				'--expanded': section.isExpanded,
			}" 
			v-memo="[section.isExpanded]"
		>
			<div class="ui-access-rights-v2-section-header-expander">
				<div class='ui-icon-set' :class="{
					'--chevron-up': section.isExpanded,
					'--chevron-down': !section.isExpanded,
				}"
				></div>
			</div>
			<Icon/>
			<span 
				class="ui-access-rights-v2-text-ellipsis ui-access-rights-v2-section-title"
				:title="section.sectionTitle"
			>{{ section.sectionTitle }}</span>
			<span
				v-if="section.sectionSubTitle"
				class="ui-access-rights-v2-text-ellipsis ui-access-rights-v2-section-subtitle"
				:title="section.sectionSubTitle"
			>
				{{ section.sectionSubTitle }}
			</span>
			<Hint v-if="section.sectionHint" :html="section.sectionHint"/>
		</div>
	`
	};

	const MenuCell$1 = {
	  name: 'MenuCell',
	  components: {
	    CellLayout
	  },
	  template: `
		<CellLayout class="ui-access-rights-v2-menu-cell"/>
	`
	};

	const RowValue = {
	  name: 'RowValue',
	  components: {
	    ...Rows
	  },
	  emits: ['close'],
	  inject: ['right'],
	  computed: {
	    component() {
	      return getValueComponent(this.right);
	    }
	  },
	  template: `
		<Component :is="component" @close="$emit('close')" />
	`
	};

	const TitleCell = {
	  name: 'TitleCell',
	  components: {
	    Hint,
	    RowValue,
	    RichMenuItem: ui_vue3_components_richMenu.RichMenuItem,
	    RichMenuPopup: ui_vue3_components_richMenu.RichMenuPopup
	  },
	  props: {
	    right: {
	      /** @type AccessRightItem */
	      type: Object,
	      required: true
	    }
	  },
	  inject: ['section'],
	  provide() {
	    return {
	      right: this.right
	    };
	  },
	  data() {
	    return {
	      isMenuShown: false,
	      isRowValueShown: false
	    };
	  },
	  computed: {
	    RichMenuItemIcon: () => ui_vue3_components_richMenu.RichMenuItemIcon,
	    isMinValueSet() {
	      return this.$store.getters['accessRights/isMinValueSet'](this.section.sectionCode, this.right.id);
	    },
	    isMaxValueSet() {
	      return this.$store.getters['accessRights/isMaxValueSet'](this.section.sectionCode, this.right.id);
	    },
	    isRowValueConfigurable() {
	      var _ServiceLocator$getVa, _ServiceLocator$getVa2;
	      return (_ServiceLocator$getVa = (_ServiceLocator$getVa2 = ServiceLocator.getValueTypeByRight(this.right)) == null ? void 0 : _ServiceLocator$getVa2.isRowValueConfigurable()) != null ? _ServiceLocator$getVa : false;
	    }
	  },
	  methods: {
	    toggleGroup() {
	      if (!this.right.groupHead) {
	        return;
	      }
	      this.$store.dispatch('accessRights/toggleGroup', {
	        sectionCode: this.section.sectionCode,
	        groupId: this.right.id
	      });
	    },
	    toggleMenu() {
	      this.isMenuShown = !this.isMenuShown;
	    },
	    setMaxValuesForRight() {
	      this.isRowValueShown = false;
	      this.isMenuShown = false;
	      this.$store.dispatch('userGroups/setMaxAccessRightValuesForRight', {
	        sectionCode: this.section.sectionCode,
	        rightId: this.right.id
	      });
	    },
	    setMinValuesForRight() {
	      this.isRowValueShown = false;
	      this.isMenuShown = false;
	      this.$store.dispatch('userGroups/setMinAccessRightValuesForRight', {
	        sectionCode: this.section.sectionCode,
	        rightId: this.right.id
	      });
	    },
	    openRowValue() {
	      this.isMenuShown = false;
	      this.isRowValueShown = true;
	    }
	  },
	  // data attributes are needed for e2e automated tests
	  template: `
		<div
			class='ui-access-rights-v2-column-item-text ui-access-rights-v2-column-item-title'
			@click="toggleGroup"
			:title="right.title"
			:style="{
				cursor: right.groupHead ? 'pointer' : null,
			}"
			v-memo="[right.isGroupExpanded]"
			:data-accessrights-right-id="right.id"
		>
			<span
				v-if="right.groupHead"
				class="ui-icon-set"
				:class="{
					'--minus-in-circle': right.isGroupExpanded,
					'--plus-in-circle': !right.isGroupExpanded,
				}"
			></span>
			<span class="ui-access-rights-v2-text-ellipsis" :style="{
				'margin-left': !right.groupHead && !right.group ? '23px' : null,
			}">{{ right.title }}</span>
			<Hint v-once v-if="right.hint" :html="right.hint" />
		</div>
		<div
			ref="icon" 
			class="ui-icon-set --more ui-access-rights-v2-icon-more ui-access-rights-v2-title-column-menu" 
			@click="toggleMenu"
		>
			<RichMenuPopup
				v-if="isMenuShown"
				@close="isMenuShown = false"
				:popup-options="{bindElement: $refs.icon, width: 300}"
			>
				<RichMenuItem
					v-if="isMaxValueSet"
					:icon="RichMenuItemIcon.check"
					:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MAX_ACCESS_RIGHTS_ROW')"
					:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MAX_ACCESS_RIGHTS_ROW_SUBTITLE')"
					@click="setMaxValuesForRight"
				/>
				<RichMenuItem
					v-if="isMinValueSet"
					:icon="RichMenuItemIcon['red-lock']"
					:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MIN_ACCESS_RIGHTS_ROW')"
					:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MIN_ACCESS_RIGHTS_ROW_SUBTITLE')"
					@click="setMinValuesForRight"
				/>
				<RichMenuItem
					v-if="isRowValueConfigurable"
					:icon="RichMenuItemIcon.settings"
					:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_OPEN_ROW_VALUE')"
					:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_OPEN_ROW_VALUE_SUBTITLE')"
					@click="openRowValue"
				/>
			</RichMenuPopup>
			<RowValue v-if="isRowValueShown" @close="isRowValueShown = false"/>
		</div>
	`
	};

	const TitleColumn = {
	  name: 'TitleColumn',
	  components: {
	    TitleCell,
	    ColumnLayout,
	    CellLayout,
	    MenuCell: MenuCell$1
	  },
	  props: {
	    rights: {
	      type: Map,
	      required: true
	    }
	  },
	  computed: {
	    renderedRights() {
	      const result = new Map();
	      for (const [rightId, right] of this.rights) {
	        if (shouldRowBeRendered(right)) {
	          result.set(rightId, right);
	        }
	      }
	      return result;
	    }
	  },
	  template: `
		<ColumnLayout>
			<MenuCell/>
			<CellLayout
				v-for="[rightId, accessRightItem] in renderedRights"
				:key="rightId"
				:class="{
					'ui-access-rights-v2-group-children': accessRightItem.group,
				}"
			>
				<TitleCell :right="accessRightItem" />
			</CellLayout>
		</ColumnLayout>
	`
	};

	const Section = {
	  name: 'Section',
	  components: {
	    Column,
	    SyncHorizontalScroll,
	    TitleColumn,
	    Header: Header$2,
	    ColumnList
	  },
	  props: {
	    userGroups: {
	      type: Map,
	      required: true
	    },
	    rights: {
	      type: Map,
	      required: true
	    },
	    code: {
	      type: String,
	      required: true
	    },
	    isExpanded: {
	      type: Boolean,
	      required: true
	    },
	    title: {
	      type: String,
	      required: true
	    },
	    subTitle: {
	      type: String
	    },
	    hint: {
	      type: String
	    },
	    icon: {
	      /** @type AccessRightSectionIcon */
	      type: Object
	    }
	  },
	  provide() {
	    return {
	      section: ui_vue3.computed(() => {
	        return {
	          sectionCode: this.code,
	          sectionTitle: this.title,
	          sectionSubTitle: this.subTitle,
	          sectionIcon: this.icon,
	          sectionHint: this.hint,
	          isExpanded: this.isExpanded,
	          rights: this.rights
	        };
	      })
	    };
	  },
	  // data attributes are needed for e2e automated tests
	  template: `
		<div class="ui-access-rights-v2-section" :data-accessrights-section-code="code">
			<Header/>
			<div v-if="isExpanded" class='ui-access-rights-v2-section-container'>
				<div class='ui-access-rights-v2-section-head'>
					<TitleColumn :rights="rights" />
				</div>
				<ColumnList :rights="rights" :user-groups="userGroups"/>
			</div>
		</div>
	`
	};

	const Grid = {
	  name: 'Grid',
	  components: {
	    Section,
	    Header,
	    SearchBox
	  },
	  loader: null,
	  computed: {
	    ...ui_vue3_vuex.mapState({
	      isSaving: state => state.application.isSaving,
	      guid: state => state.application.guid,
	      searchContainerSelector: state => state.application.options.searchContainerSelector
	    }),
	    ...ui_vue3_vuex.mapGetters({
	      shownSections: 'accessRights/shown',
	      shownUserGroups: 'userGroups/shown'
	    })
	  },
	  mounted() {
	    ServiceLocator.getHint(this.guid).initOwnerDocument(this.$refs.container);
	  },
	  methods: {
	    scrollToSection(sectionCode) {
	      const section = this.$refs.sections.find(item => item.code === sectionCode);
	      if (section) {
	        scrollTo({
	          top: main_core.Dom.getPosition(section.$el).top - 155,
	          behavior: 'smooth'
	        });
	      }
	    }
	  },
	  template: `
		<Teleport v-if="searchContainerSelector" :to="searchContainerSelector">
			<SearchBox/>
		</Teleport>
		<div ref="container" class='ui-access-rights-v2' :class="{
			'ui-access-rights-v2-block': isSaving,
		}">
			<Header :user-groups="shownUserGroups"/>
			<Section
				v-for="[sectionCode, accessRightSection] in shownSections"
				:key="sectionCode"
				:code="accessRightSection.sectionCode"
				:is-expanded="accessRightSection.isExpanded"
				:title="accessRightSection.sectionTitle"
				:sub-title="accessRightSection.sectionSubTitle"
				:hint="accessRightSection.sectionHint"
				:icon="accessRightSection.sectionIcon"
				:rights="accessRightSection.rights"
				:user-groups="shownUserGroups"
				ref="sections"
			/>
		</div>
	`
	};

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _data = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("data");
	var _isEnabled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isEnabled");
	var _isCancelAlreadyRegistered = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCancelAlreadyRegistered");
	var _analyzeRoles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("analyzeRoles");
	var _isUserGroupEdited = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isUserGroupEdited");
	var _getSaveErrorStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSaveErrorStatus");
	var _registerRoleCreateEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("registerRoleCreateEvent");
	var _registerRoleEditEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("registerRoleEditEvent");
	var _registerRoleDeleteEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("registerRoleDeleteEvent");
	var _appendRoleCountView = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appendRoleCountView");
	var _appendP = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appendP");
	class AnalyticsManager {
	  constructor(store, analyticsData) {
	    Object.defineProperty(this, _appendP, {
	      value: _appendP2
	    });
	    Object.defineProperty(this, _appendRoleCountView, {
	      value: _appendRoleCountView2
	    });
	    Object.defineProperty(this, _registerRoleDeleteEvent, {
	      value: _registerRoleDeleteEvent2
	    });
	    Object.defineProperty(this, _registerRoleEditEvent, {
	      value: _registerRoleEditEvent2
	    });
	    Object.defineProperty(this, _registerRoleCreateEvent, {
	      value: _registerRoleCreateEvent2
	    });
	    Object.defineProperty(this, _getSaveErrorStatus, {
	      value: _getSaveErrorStatus2
	    });
	    Object.defineProperty(this, _isUserGroupEdited, {
	      value: _isUserGroupEdited2
	    });
	    Object.defineProperty(this, _analyzeRoles, {
	      value: _analyzeRoles2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _data, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isEnabled, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isCancelAlreadyRegistered, {
	      writable: true,
	      value: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = store;
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = analyticsData;

	    // check 2 out of 3 required fields
	    // 'event' field is provided by AnalyticsManager
	    babelHelpers.classPrivateFieldLooseBase(this, _isEnabled)[_isEnabled] = Object.hasOwn(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data], 'tool') && Object.hasOwn(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data], 'category');
	  }
	  onSaveAttempt() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isEnabled)[_isEnabled]) {
	      return;
	    }
	    const {
	      createdRoles,
	      editedRoles,
	      deletedRoles
	    } = babelHelpers.classPrivateFieldLooseBase(this, _analyzeRoles)[_analyzeRoles]();
	    for (let i = 0; i < createdRoles; i++) {
	      babelHelpers.classPrivateFieldLooseBase(this, _registerRoleCreateEvent)[_registerRoleCreateEvent]('attempt');
	    }
	    for (let i = 0; i < editedRoles; i++) {
	      babelHelpers.classPrivateFieldLooseBase(this, _registerRoleEditEvent)[_registerRoleEditEvent]('attempt');
	    }
	    for (let i = 0; i < deletedRoles; i++) {
	      babelHelpers.classPrivateFieldLooseBase(this, _registerRoleDeleteEvent)[_registerRoleDeleteEvent]('attempt');
	    }
	  }
	  onSaveSuccess() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isEnabled)[_isEnabled]) {
	      return;
	    }
	    const {
	      createdRoles,
	      editedRoles,
	      deletedRoles
	    } = babelHelpers.classPrivateFieldLooseBase(this, _analyzeRoles)[_analyzeRoles]();
	    for (let i = 0; i < createdRoles; i++) {
	      babelHelpers.classPrivateFieldLooseBase(this, _registerRoleCreateEvent)[_registerRoleCreateEvent]('success');
	    }
	    for (let i = 0; i < editedRoles; i++) {
	      babelHelpers.classPrivateFieldLooseBase(this, _registerRoleEditEvent)[_registerRoleEditEvent]('success');
	    }
	    for (let i = 0; i < deletedRoles; i++) {
	      babelHelpers.classPrivateFieldLooseBase(this, _registerRoleDeleteEvent)[_registerRoleDeleteEvent]('success');
	    }
	  }
	  onSaveError(response) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isEnabled)[_isEnabled]) {
	      return;
	    }
	    const status = babelHelpers.classPrivateFieldLooseBase(this, _getSaveErrorStatus)[_getSaveErrorStatus](response);
	    const {
	      createdRoles,
	      editedRoles,
	      deletedRoles
	    } = babelHelpers.classPrivateFieldLooseBase(this, _analyzeRoles)[_analyzeRoles]();
	    for (let i = 0; i < createdRoles; i++) {
	      babelHelpers.classPrivateFieldLooseBase(this, _registerRoleCreateEvent)[_registerRoleCreateEvent](status);
	    }
	    for (let i = 0; i < editedRoles; i++) {
	      babelHelpers.classPrivateFieldLooseBase(this, _registerRoleEditEvent)[_registerRoleEditEvent](status);
	    }
	    for (let i = 0; i < deletedRoles; i++) {
	      babelHelpers.classPrivateFieldLooseBase(this, _registerRoleDeleteEvent)[_registerRoleDeleteEvent](status);
	    }
	  }
	  onCancelChanges() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isEnabled)[_isEnabled]) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isCancelAlreadyRegistered)[_isCancelAlreadyRegistered]) {
	      return;
	    }
	    ui_analytics.sendData({
	      ...babelHelpers.classPrivateFieldLooseBase(this, _data)[_data],
	      event: 'settings_cancel'
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _isCancelAlreadyRegistered)[_isCancelAlreadyRegistered] = true;
	  }
	  onCloseWithoutSave() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isEnabled)[_isEnabled]) {
	      return;
	    }
	    ui_analytics.sendData({
	      ...babelHelpers.classPrivateFieldLooseBase(this, _data)[_data],
	      event: 'settings_pop_cancel'
	    });
	  }
	}
	function _analyzeRoles2() {
	  const result = {
	    createdRoles: 0,
	    editedRoles: 0,
	    deletedRoles: babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].state.userGroups.deleted.size
	  };
	  for (const userGroup of babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].state.userGroups.collection.values()) {
	    if (userGroup.isNew) {
	      result.createdRoles++;
	    } else if (babelHelpers.classPrivateFieldLooseBase(this, _isUserGroupEdited)[_isUserGroupEdited](userGroup)) {
	      result.editedRoles++;
	    }
	  }
	  return result;
	}
	function _isUserGroupEdited2(userGroup) {
	  if (userGroup.isModified) {
	    return true;
	  }
	  for (const value of userGroup.accessRights.values()) {
	    if (value.isModified) {
	      return true;
	    }
	  }
	  return false;
	}
	function _getSaveErrorStatus2(response) {
	  if (!main_core.Type.isArrayFilled(response == null ? void 0 : response.errors)) {
	    return 'error';
	  }
	  for (const error of response.errors) {
	    if (main_core.Type.isStringFilled(error == null ? void 0 : error.code)) {
	      return `error_${main_core.Text.toCamelCase(error.code)}`;
	    }
	  }
	  return 'error';
	}
	function _registerRoleCreateEvent2(status) {
	  const data = {
	    ...babelHelpers.classPrivateFieldLooseBase(this, _data)[_data],
	    event: 'role_create',
	    status
	  };
	  babelHelpers.classPrivateFieldLooseBase(this, _appendRoleCountView)[_appendRoleCountView](data);
	  ui_analytics.sendData(data);
	}
	function _registerRoleEditEvent2(status) {
	  const data = {
	    ...babelHelpers.classPrivateFieldLooseBase(this, _data)[_data],
	    event: 'role_edit',
	    status
	  };
	  babelHelpers.classPrivateFieldLooseBase(this, _appendRoleCountView)[_appendRoleCountView](data);
	  ui_analytics.sendData(data);
	}
	function _registerRoleDeleteEvent2(status) {
	  const data = {
	    ...babelHelpers.classPrivateFieldLooseBase(this, _data)[_data],
	    event: 'role_delete',
	    status
	  };
	  babelHelpers.classPrivateFieldLooseBase(this, _appendRoleCountView)[_appendRoleCountView](data);
	  ui_analytics.sendData(data);
	}
	function _appendRoleCountView2(data) {
	  babelHelpers.classPrivateFieldLooseBase(this, _appendP)[_appendP](data, 'roleCountView', babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['userGroups/shown'].size);
	}
	function _appendP2(data, name, value) {
	  for (const pName of ['p1', 'p2', 'p3', 'p4', 'p5']) {
	    if (!Object.hasOwn(data, pName)) {
	      // eslint-disable-next-line no-param-reassign
	      data[pName] = `${name}_${value}`;
	      return;
	    }
	  }
	}

	const ACTION_SAVE = 'save';
	const MODE = 'ajax';
	const BODY_TYPE = 'data';
	var _guid = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("guid");
	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	class ApplicationModel extends ui_vue3_vuex.BuilderModel {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _guid, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: void 0
	    });
	  }
	  getName() {
	    return 'application';
	  }
	  setOptions(options) {
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = options;
	    return this;
	  }
	  setGuid(guid) {
	    babelHelpers.classPrivateFieldLooseBase(this, _guid)[_guid] = guid;
	    return this;
	  }
	  getState() {
	    return {
	      options: babelHelpers.classPrivateFieldLooseBase(this, _options)[_options],
	      guid: babelHelpers.classPrivateFieldLooseBase(this, _guid)[_guid],
	      isSaving: false
	    };
	  }
	  getGetters() {
	    return {
	      isMaxVisibleUserGroupsSet: state => {
	        return state.options.maxVisibleUserGroups > 0;
	      }
	    };
	  }
	  getMutations() {
	    return {
	      setSaving: (state, isSaving) => {
	        // eslint-disable-next-line no-param-reassign
	        state.isSaving = Boolean(isSaving);
	      }
	    };
	  }
	}

	const NEW_USER_GROUP_ID_PREFIX = 'new~~~';
	var _initialUserGroups = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initialUserGroups");
	var _setAccessRightValuesAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setAccessRightValuesAction");
	var _setAccessRightValuesForShownAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setAccessRightValuesForShownAction");
	var _setMinAccessRightValuesAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMinAccessRightValuesAction");
	var _setMinAccessRightValuesInSectionAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMinAccessRightValuesInSectionAction");
	var _setMinAccessRightValuesForRight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMinAccessRightValuesForRight");
	var _getMinValueForColumnAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMinValueForColumnAction");
	var _getMinValue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMinValue");
	var _setMaxAccessRightValuesAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMaxAccessRightValuesAction");
	var _setMaxAccessRightValuesInSectionAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMaxAccessRightValuesInSectionAction");
	var _setMaxAccessRightValuesForRight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMaxAccessRightValuesForRight");
	var _getMaxValueForColumnAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMaxValueForColumnAction");
	var _getMaxValue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMaxValue");
	var _copySectionValuesAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copySectionValuesAction");
	var _setRoleTitleAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setRoleTitleAction");
	var _addMemberAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMemberAction");
	var _removeMemberAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeMemberAction");
	var _copyUserGroupAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copyUserGroupAction");
	var _addUserGroupAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addUserGroupAction");
	var _removeUserGroupAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeUserGroupAction");
	var _showUserGroupAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showUserGroupAction");
	var _hideUserGroupAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideUserGroupAction");
	var _isUserGroupExists = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isUserGroupExists");
	var _getUserGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getUserGroup");
	var _isValueExistsInStructure = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isValueExistsInStructure");
	var _isValueModified = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isValueModified");
	var _isSetsEqual = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSetsEqual");
	var _isUserGroupModified = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isUserGroupModified");
	class UserGroupsModel extends ui_vue3_vuex.BuilderModel {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _isUserGroupModified, {
	      value: _isUserGroupModified2
	    });
	    Object.defineProperty(this, _isSetsEqual, {
	      value: _isSetsEqual2
	    });
	    Object.defineProperty(this, _isValueModified, {
	      value: _isValueModified2
	    });
	    Object.defineProperty(this, _isValueExistsInStructure, {
	      value: _isValueExistsInStructure2
	    });
	    Object.defineProperty(this, _getUserGroup, {
	      value: _getUserGroup2
	    });
	    Object.defineProperty(this, _isUserGroupExists, {
	      value: _isUserGroupExists2
	    });
	    Object.defineProperty(this, _hideUserGroupAction, {
	      value: _hideUserGroupAction2
	    });
	    Object.defineProperty(this, _showUserGroupAction, {
	      value: _showUserGroupAction2
	    });
	    Object.defineProperty(this, _removeUserGroupAction, {
	      value: _removeUserGroupAction2
	    });
	    Object.defineProperty(this, _addUserGroupAction, {
	      value: _addUserGroupAction2
	    });
	    Object.defineProperty(this, _copyUserGroupAction, {
	      value: _copyUserGroupAction2
	    });
	    Object.defineProperty(this, _removeMemberAction, {
	      value: _removeMemberAction2
	    });
	    Object.defineProperty(this, _addMemberAction, {
	      value: _addMemberAction2
	    });
	    Object.defineProperty(this, _setRoleTitleAction, {
	      value: _setRoleTitleAction2
	    });
	    Object.defineProperty(this, _copySectionValuesAction, {
	      value: _copySectionValuesAction2
	    });
	    Object.defineProperty(this, _getMaxValue, {
	      value: _getMaxValue2
	    });
	    Object.defineProperty(this, _getMaxValueForColumnAction, {
	      value: _getMaxValueForColumnAction2
	    });
	    Object.defineProperty(this, _setMaxAccessRightValuesForRight, {
	      value: _setMaxAccessRightValuesForRight2
	    });
	    Object.defineProperty(this, _setMaxAccessRightValuesInSectionAction, {
	      value: _setMaxAccessRightValuesInSectionAction2
	    });
	    Object.defineProperty(this, _setMaxAccessRightValuesAction, {
	      value: _setMaxAccessRightValuesAction2
	    });
	    Object.defineProperty(this, _getMinValue, {
	      value: _getMinValue2
	    });
	    Object.defineProperty(this, _getMinValueForColumnAction, {
	      value: _getMinValueForColumnAction2
	    });
	    Object.defineProperty(this, _setMinAccessRightValuesForRight, {
	      value: _setMinAccessRightValuesForRight2
	    });
	    Object.defineProperty(this, _setMinAccessRightValuesInSectionAction, {
	      value: _setMinAccessRightValuesInSectionAction2
	    });
	    Object.defineProperty(this, _setMinAccessRightValuesAction, {
	      value: _setMinAccessRightValuesAction2
	    });
	    Object.defineProperty(this, _setAccessRightValuesForShownAction, {
	      value: _setAccessRightValuesForShownAction2
	    });
	    Object.defineProperty(this, _setAccessRightValuesAction, {
	      value: _setAccessRightValuesAction2
	    });
	    Object.defineProperty(this, _initialUserGroups, {
	      writable: true,
	      value: new Map()
	    });
	  }
	  getName() {
	    return 'userGroups';
	  }
	  setInitialUserGroups(groups) {
	    babelHelpers.classPrivateFieldLooseBase(this, _initialUserGroups)[_initialUserGroups] = groups;
	    return this;
	  }
	  getState() {
	    return {
	      collection: main_core.Runtime.clone(babelHelpers.classPrivateFieldLooseBase(this, _initialUserGroups)[_initialUserGroups]),
	      deleted: new Set()
	    };
	  }
	  getElementState(params = {}) {
	    return {
	      id: `${NEW_USER_GROUP_ID_PREFIX}${main_core.Text.getRandom()}`,
	      isNew: true,
	      isModified: true,
	      isShown: true,
	      title: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_NAME'),
	      accessRights: new Map(),
	      members: new Map()
	    };
	  }
	  getGetters() {
	    return {
	      shown: state => {
	        const result = new Map();
	        for (const [userGroupId, userGroup] of state.collection) {
	          if (userGroup.isShown) {
	            result.set(userGroupId, userGroup);
	          }
	        }
	        return result;
	      },
	      getEmptyAccessRightValue: (state, getters, rootState, rootGetters) => (userGroupId, sectionCode, valueId) => {
	        const values = rootGetters['accessRights/getEmptyValue'](sectionCode, valueId);
	        return {
	          id: valueId,
	          values,
	          isModified: state.collection.get(userGroupId).isNew
	        };
	      },
	      defaultAccessRightValues: (state, getters, rootState) => {
	        const result = new Map();
	        for (const section of rootState.accessRights.collection.values()) {
	          for (const [rightId, right] of section.rights) {
	            if (main_core.Type.isNil(right.defaultValue)) {
	              continue;
	            }
	            result.set(rightId, {
	              id: rightId,
	              values: right.defaultValue,
	              isModified: true
	            });
	          }
	        }
	        return result;
	      },
	      isModified: state => {
	        if (state.deleted.size > 0) {
	          return true;
	        }
	        for (const userGroup of state.collection.values()) {
	          if (userGroup.isNew || userGroup.isModified) {
	            return true;
	          }
	          for (const value of userGroup.accessRights.values()) {
	            if (value.isModified) {
	              return true;
	            }
	          }
	        }
	        return false;
	      },
	      isMaxVisibleUserGroupsReached: (state, getters, rootState, rootGetters) => {
	        if (!rootGetters['application/isMaxVisibleUserGroupsSet']) {
	          return false;
	        }
	        return getters.shown.size >= rootState.application.options.maxVisibleUserGroups;
	      }
	    };
	  }
	  getActions() {
	    return {
	      setAccessRightValues: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setAccessRightValuesAction)[_setAccessRightValuesAction](store, payload);
	      },
	      setAccessRightValuesForShown: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setAccessRightValuesForShownAction)[_setAccessRightValuesForShownAction](store, payload);
	      },
	      setMinAccessRightValues: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setMinAccessRightValuesAction)[_setMinAccessRightValuesAction](store, payload);
	      },
	      setMaxAccessRightValues: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setMaxAccessRightValuesAction)[_setMaxAccessRightValuesAction](store, payload);
	      },
	      setMinAccessRightValuesInSection: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setMinAccessRightValuesInSectionAction)[_setMinAccessRightValuesInSectionAction](store, payload);
	      },
	      setMaxAccessRightValuesInSection: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setMaxAccessRightValuesInSectionAction)[_setMaxAccessRightValuesInSectionAction](store, payload);
	      },
	      setMinAccessRightValuesForRight: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setMinAccessRightValuesForRight)[_setMinAccessRightValuesForRight](store, payload);
	      },
	      setMaxAccessRightValuesForRight: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setMaxAccessRightValuesForRight)[_setMaxAccessRightValuesForRight](store, payload);
	      },
	      setRoleTitle: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setRoleTitleAction)[_setRoleTitleAction](store, payload);
	      },
	      addMember: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _addMemberAction)[_addMemberAction](store, payload);
	      },
	      removeMember: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _removeMemberAction)[_removeMemberAction](store, payload);
	      },
	      copyUserGroup: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _copyUserGroupAction)[_copyUserGroupAction](store, payload);
	      },
	      copySectionValues: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _copySectionValuesAction)[_copySectionValuesAction](store, payload);
	      },
	      addUserGroup: store => {
	        babelHelpers.classPrivateFieldLooseBase(this, _addUserGroupAction)[_addUserGroupAction](store);
	      },
	      removeUserGroup: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _removeUserGroupAction)[_removeUserGroupAction](store, payload);
	      },
	      showUserGroup: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _showUserGroupAction)[_showUserGroupAction](store, payload);
	      },
	      hideUserGroup: (store, payload) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _hideUserGroupAction)[_hideUserGroupAction](store, payload);
	      }
	    };
	  }
	  getMutations() {
	    return {
	      setAccessRightValues: (state, {
	        userGroupId,
	        valueId,
	        values,
	        isModified
	      }) => {
	        const userGroup = babelHelpers.classPrivateFieldLooseBase(this, _getUserGroup)[_getUserGroup](state, userGroupId);
	        const accessRightValue = userGroup.accessRights.get(valueId);
	        if (!accessRightValue) {
	          userGroup.accessRights.set(valueId, {
	            id: valueId,
	            values,
	            isModified
	          });
	          return;
	        }
	        accessRightValue.values = values;
	        accessRightValue.isModified = isModified;
	      },
	      setRoleTitle: (state, {
	        userGroupId,
	        title
	      }) => {
	        const userGroup = babelHelpers.classPrivateFieldLooseBase(this, _getUserGroup)[_getUserGroup](state, userGroupId);
	        userGroup.title = title;
	        userGroup.isModified = babelHelpers.classPrivateFieldLooseBase(this, _isUserGroupModified)[_isUserGroupModified](userGroup);
	      },
	      addMember: (state, {
	        userGroupId,
	        accessCode,
	        member
	      }) => {
	        const userGroup = babelHelpers.classPrivateFieldLooseBase(this, _getUserGroup)[_getUserGroup](state, userGroupId);
	        userGroup.members.set(accessCode, member);
	        userGroup.isModified = babelHelpers.classPrivateFieldLooseBase(this, _isUserGroupModified)[_isUserGroupModified](userGroup);
	      },
	      removeMember: (state, {
	        userGroupId,
	        accessCode
	      }) => {
	        const userGroup = babelHelpers.classPrivateFieldLooseBase(this, _getUserGroup)[_getUserGroup](state, userGroupId);
	        userGroup.members.delete(accessCode);
	        userGroup.isModified = babelHelpers.classPrivateFieldLooseBase(this, _isUserGroupModified)[_isUserGroupModified](userGroup);
	      },
	      addUserGroup: (state, {
	        userGroup
	      }) => {
	        state.collection.set(userGroup.id, userGroup);
	      },
	      removeUserGroup: (state, {
	        userGroupId
	      }) => {
	        state.collection.delete(userGroupId);
	      },
	      markUserGroupForDeletion: (state, {
	        userGroupId
	      }) => {
	        state.deleted.add(userGroupId);
	      },
	      showUserGroup: (state, {
	        userGroupId
	      }) => {
	        // eslint-disable-next-line no-param-reassign
	        state.collection.get(userGroupId).isShown = true;
	      },
	      hideUserGroup: (state, {
	        userGroupId
	      }) => {
	        // eslint-disable-next-line no-param-reassign
	        state.collection.get(userGroupId).isShown = false;
	      }
	    };
	  }
	}
	function _setAccessRightValuesAction2(store, payload) {
	  if (!main_core.Type.isSet(payload.values)) {
	    console.warn('ui.accessrights.v2: Attempt to set not-Set values', payload);
	    return;
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isUserGroupExists)[_isUserGroupExists](store, payload.userGroupId)) {
	    console.warn('ui.accessrights.v2: Attempt to set value to a user group that dont exists', payload);
	    return;
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isValueExistsInStructure)[_isValueExistsInStructure](store, payload.sectionCode, payload.valueId)) {
	    console.warn('ui.accessrights.v2: Attempt to set value to a right that dont exists in structure', payload);
	    return;
	  }
	  store.commit('setAccessRightValues', {
	    userGroupId: payload.userGroupId,
	    valueId: payload.valueId,
	    values: payload.values,
	    isModified: babelHelpers.classPrivateFieldLooseBase(this, _isValueModified)[_isValueModified](payload.userGroupId, payload.valueId, payload.values, store.rootGetters['accessRights/getEmptyValue'](payload.sectionCode, payload.valueId))
	  });
	}
	function _setAccessRightValuesForShownAction2(store, payload) {
	  for (const userGroupId of store.getters.shown.keys()) {
	    void store.dispatch('setAccessRightValues', {
	      ...payload,
	      userGroupId
	    });
	  }
	}
	function _setMinAccessRightValuesAction2(store, {
	  userGroupId
	}) {
	  for (const sectionCode of store.rootState.accessRights.collection.keys()) {
	    void store.dispatch('setMinAccessRightValuesInSection', {
	      userGroupId,
	      sectionCode
	    });
	  }
	  void store.dispatch('accessRights/expandAllSections', null, {
	    root: true
	  });
	}
	function _setMinAccessRightValuesInSectionAction2(store, {
	  userGroupId,
	  sectionCode
	}) {
	  const section = store.rootState.accessRights.collection.get(sectionCode);
	  if (!section) {
	    console.warn('ui.accessrights.v2: attempt to set min values in section that dont exists', {
	      sectionCode
	    });
	    return;
	  }
	  for (const item of section.rights.values()) {
	    const valueToSet = babelHelpers.classPrivateFieldLooseBase(this, _getMinValueForColumnAction)[_getMinValueForColumnAction](item, store.rootGetters['accessRights/getEmptyValue'](section.sectionCode, item.id));
	    if (main_core.Type.isNil(valueToSet)) {
	      continue;
	    }
	    void store.dispatch('setAccessRightValues', {
	      userGroupId,
	      sectionCode: section.sectionCode,
	      valueId: item.id,
	      values: valueToSet
	    });
	  }
	}
	function _setMinAccessRightValuesForRight2(store, {
	  sectionCode,
	  rightId
	}) {
	  var _store$rootState$acce;
	  const right = (_store$rootState$acce = store.rootState.accessRights.collection.get(sectionCode)) == null ? void 0 : _store$rootState$acce.rights.get(rightId);
	  if (!right) {
	    console.warn('ui.accessrights.v2: attempt to set min values for right that dont exists', {
	      sectionCode,
	      rightId
	    });
	    return;
	  }
	  const valueToSet = babelHelpers.classPrivateFieldLooseBase(this, _getMinValue)[_getMinValue](right);
	  if (main_core.Type.isNil(valueToSet)) {
	    console.warn('ui.accessrights.v2: attempt to set min values for right that dont have min value set', {
	      sectionCode,
	      rightId
	    });
	    return;
	  }
	  void store.dispatch('setAccessRightValuesForShown', {
	    sectionCode,
	    valueId: rightId,
	    values: valueToSet
	  });
	}
	function _getMinValueForColumnAction2(item, emptyValue) {
	  const setEmpty = main_core.Type.isBoolean(item.setEmptyOnSetMinMaxValueInColumn) && item.setEmptyOnSetMinMaxValueInColumn;
	  if (setEmpty) {
	    return emptyValue;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _getMinValue)[_getMinValue](item);
	}
	function _getMinValue2(item) {
	  var _ServiceLocator$getVa;
	  return (_ServiceLocator$getVa = ServiceLocator.getValueTypeByRight(item)) == null ? void 0 : _ServiceLocator$getVa.getMinValue(item);
	}
	function _setMaxAccessRightValuesAction2(store, {
	  userGroupId
	}) {
	  for (const sectionCode of store.rootState.accessRights.collection.keys()) {
	    void store.dispatch('setMaxAccessRightValuesInSection', {
	      userGroupId,
	      sectionCode
	    });
	  }
	  void store.dispatch('accessRights/expandAllSections', null, {
	    root: true
	  });
	}
	function _setMaxAccessRightValuesInSectionAction2(store, {
	  userGroupId,
	  sectionCode
	}) {
	  const section = store.rootState.accessRights.collection.get(sectionCode);
	  if (!section) {
	    console.warn('ui.accessrights.v2: attempt to set max values in section that dont exists', {
	      sectionCode
	    });
	    return;
	  }
	  for (const item of section.rights.values()) {
	    const valueToSet = babelHelpers.classPrivateFieldLooseBase(this, _getMaxValueForColumnAction)[_getMaxValueForColumnAction](item, store.rootGetters['accessRights/getEmptyValue'](section.sectionCode, item.id));
	    if (main_core.Type.isNil(valueToSet)) {
	      continue;
	    }
	    void store.dispatch('setAccessRightValues', {
	      userGroupId,
	      sectionCode: section.sectionCode,
	      valueId: item.id,
	      values: valueToSet
	    });
	  }
	}
	function _setMaxAccessRightValuesForRight2(store, {
	  sectionCode,
	  rightId
	}) {
	  var _store$rootState$acce2;
	  const right = (_store$rootState$acce2 = store.rootState.accessRights.collection.get(sectionCode)) == null ? void 0 : _store$rootState$acce2.rights.get(rightId);
	  if (!right) {
	    console.warn('ui.accessrights.v2: attempt to set max values for right that dont exists', {
	      sectionCode,
	      rightId
	    });
	    return;
	  }
	  const valueToSet = babelHelpers.classPrivateFieldLooseBase(this, _getMaxValue)[_getMaxValue](right);
	  if (main_core.Type.isNil(valueToSet)) {
	    console.warn('ui.accessrights.v2: attempt to set max values for right that dont have max value set', {
	      sectionCode,
	      rightId
	    });
	    return;
	  }
	  void store.dispatch('setAccessRightValuesForShown', {
	    sectionCode,
	    valueId: rightId,
	    values: valueToSet
	  });
	}
	function _getMaxValueForColumnAction2(item, emptyValue) {
	  const setEmpty = main_core.Type.isBoolean(item.setEmptyOnSetMinMaxValueInColumn) && item.setEmptyOnSetMinMaxValueInColumn;
	  if (setEmpty) {
	    return emptyValue;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _getMaxValue)[_getMaxValue](item);
	}
	function _getMaxValue2(item) {
	  var _ServiceLocator$getVa2;
	  return (_ServiceLocator$getVa2 = ServiceLocator.getValueTypeByRight(item)) == null ? void 0 : _ServiceLocator$getVa2.getMaxValue(item);
	}
	function _copySectionValuesAction2(store, payload) {
	  const src = babelHelpers.classPrivateFieldLooseBase(this, _getUserGroup)[_getUserGroup](store.state, payload.srcUserGroupId);
	  if (!src) {
	    console.warn('ui.accessrights.v2: Attempt to copy values from user group that dont exists', payload);
	    return;
	  }
	  const section = store.rootState.accessRights.collection.get(payload.sectionCode);
	  if (!section) {
	    console.warn('ui.accessrights.v2: Attempt to copy values for section that dont exists', payload);
	    return;
	  }
	  for (const rightId of section.rights.keys()) {
	    const value = src.accessRights.get(rightId);
	    if (value) {
	      void store.dispatch('setAccessRightValues', {
	        userGroupId: payload.dstUserGroupId,
	        sectionCode: section.sectionCode,
	        valueId: value.id,
	        values: value.values
	      });
	    } else {
	      const emptyValue = store.rootGetters['accessRights/getEmptyValue'](section.sectionCode, rightId);
	      void store.dispatch('setAccessRightValues', {
	        userGroupId: payload.dstUserGroupId,
	        sectionCode: section.sectionCode,
	        valueId: rightId,
	        values: emptyValue
	      });
	    }
	  }
	}
	function _setRoleTitleAction2(store, payload) {
	  if (!main_core.Type.isString(payload.title)) {
	    console.warn('ui.accessrights.v2: Attempt to set role title with something other than string', payload);
	    return;
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isUserGroupExists)[_isUserGroupExists](store, payload.userGroupId)) {
	    console.warn('ui.accessrights.v2: Attempt to update user group that dont exists', payload);
	    return;
	  }
	  store.commit('setRoleTitle', payload);
	}
	function _addMemberAction2(store, payload) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isUserGroupExists)[_isUserGroupExists](store, payload.userGroupId)) {
	    console.warn('ui.accessrights.v2: Attempt to add member to a user group that dont exists', payload);
	    return;
	  }
	  if (!main_core.Type.isStringFilled(payload.accessCode) || !main_core.Type.isStringFilled(payload.member.id) || !main_core.Type.isStringFilled(payload.member.type) || !main_core.Type.isStringFilled(payload.member.name) || !(main_core.Type.isNil(payload.member.avatar) || main_core.Type.isStringFilled(payload.member.avatar))) {
	    console.warn('ui.accessrights.v2: Attempt to add member with invalid payload', payload);
	    return;
	  }
	  store.commit('addMember', payload);
	}
	function _removeMemberAction2(store, payload) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isUserGroupExists)[_isUserGroupExists](store, payload.userGroupId)) {
	    console.warn('ui.accessrights.v2: Attempt to remove member from a user group that dont exists', payload);
	    return;
	  }
	  if (!main_core.Type.isStringFilled(payload.accessCode)) {
	    console.warn('ui.accessrights.v2: Attempt to remove member with invalid payload', payload);
	    return;
	  }
	  store.commit('removeMember', payload);
	}
	function _copyUserGroupAction2(store, {
	  userGroupId
	}) {
	  const sourceGroup = babelHelpers.classPrivateFieldLooseBase(this, _getUserGroup)[_getUserGroup](store.state, userGroupId);
	  if (!sourceGroup) {
	    console.warn('ui.accessrights.v2: Attempt to copy user group that dont exists', {
	      userGroupId
	    });
	    return;
	  }
	  const emptyGroup = this.getElementState();
	  const copy = {
	    ...main_core.Runtime.clone(sourceGroup),
	    id: emptyGroup.id,
	    title: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_COPIED_ROLE_NAME', {
	      '#ORIGINAL#': sourceGroup.title
	    }),
	    isNew: true,
	    isModified: true,
	    isShown: true
	  };
	  for (const value of copy.accessRights.values()) {
	    // is a new group all values are modified
	    value.isModified = true;
	  }
	  store.commit('addUserGroup', {
	    userGroup: copy
	  });
	}
	function _addUserGroupAction2(store) {
	  const newGroup = this.getElementState();
	  newGroup.accessRights = main_core.Runtime.clone(store.getters.defaultAccessRightValues);
	  store.commit('addUserGroup', {
	    userGroup: newGroup
	  });
	}
	function _removeUserGroupAction2(store, {
	  userGroupId
	}) {
	  const userGroup = babelHelpers.classPrivateFieldLooseBase(this, _getUserGroup)[_getUserGroup](store.state, userGroupId);
	  if (!userGroup) {
	    console.warn('ui.accessrights.v2: Attempt to remove user group that dont exists', {
	      userGroupId
	    });
	    return;
	  }
	  store.commit('removeUserGroup', {
	    userGroupId
	  });
	  if (!userGroup.isNew) {
	    store.commit('markUserGroupForDeletion', {
	      userGroupId
	    });
	  }
	}
	function _showUserGroupAction2(store, {
	  userGroupId
	}) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isUserGroupExists)[_isUserGroupExists](store, userGroupId)) {
	    console.warn('ui.accessrights.v2: Attempt to show user group that dont exists', {
	      userGroupId
	    });
	    return;
	  }
	  store.commit('showUserGroup', {
	    userGroupId
	  });
	}
	function _hideUserGroupAction2(store, {
	  userGroupId
	}) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isUserGroupExists)[_isUserGroupExists](store, userGroupId)) {
	    console.warn('ui.accessrights.v2: Attempt to shrink user group that dont exists', {
	      userGroupId
	    });
	    return;
	  }
	  store.commit('hideUserGroup', {
	    userGroupId
	  });
	}
	function _isUserGroupExists2(store, userGroupId) {
	  const group = babelHelpers.classPrivateFieldLooseBase(this, _getUserGroup)[_getUserGroup](store.state, userGroupId);
	  return Boolean(group);
	}
	function _getUserGroup2(state, userGroupId) {
	  return state.collection.get(userGroupId);
	}
	function _isValueExistsInStructure2(store, sectionCode, valueId) {
	  const section = store.rootState.accessRights.collection.get(sectionCode);
	  return section == null ? void 0 : section.rights.has(valueId);
	}
	function _isValueModified2(userGroupId, valueId, values, emptyValue) {
	  var _initialGroup$accessR, _initialGroup$accessR2;
	  const initialGroup = babelHelpers.classPrivateFieldLooseBase(this, _initialUserGroups)[_initialUserGroups].get(userGroupId);
	  if (!initialGroup) {
	    // its a newly created group, all values are modified

	    return true;
	  }
	  const initialValues = (_initialGroup$accessR = (_initialGroup$accessR2 = initialGroup.accessRights.get(valueId)) == null ? void 0 : _initialGroup$accessR2.values) != null ? _initialGroup$accessR : emptyValue;

	  // use native Sets instead of Vue-wrapped proxy-sets, they throw an error on `symmetricDifference`
	  return !babelHelpers.classPrivateFieldLooseBase(this, _isSetsEqual)[_isSetsEqual](new Set(initialValues), new Set(values));
	}
	function _isSetsEqual2(a, b) {
	  if (main_core.Type.isFunction(a.symmetricDifference)) {
	    // native way to compare sets for modern browsers
	    return a.symmetricDifference(b).size === 0;
	  }

	  // polyfill

	  if (a.size !== b.size) {
	    return false;
	  }
	  for (const value of a) {
	    if (!b.has(value)) {
	      return false;
	    }
	  }
	  for (const value of b) {
	    if (!a.has(value)) {
	      return false;
	    }
	  }
	  return true;
	}
	function _isUserGroupModified2(userGroup) {
	  if (userGroup.isNew) {
	    return true;
	  }
	  const initialGroup = babelHelpers.classPrivateFieldLooseBase(this, _initialUserGroups)[_initialUserGroups].get(userGroup.id);
	  if (!initialGroup) {
	    throw new Error('ui.accessrights.v2: initial user group not found');
	  }
	  if (userGroup.title !== initialGroup.title) {
	    return true;
	  }
	  const initialAccessCodes = new Set(initialGroup.members.keys());
	  const currentAccessCodes = new Set(userGroup.members.keys());
	  return !babelHelpers.classPrivateFieldLooseBase(this, _isSetsEqual)[_isSetsEqual](initialAccessCodes, currentAccessCodes);
	}

	function createStore(options, userGroups, accessRights, appGuid) {
	  const userGroupsModel = UserGroupsModel.create().setInitialUserGroups(userGroups);
	  const {
	    store
	  } = ui_vue3_vuex.Builder.init().addModel(ApplicationModel.create().setOptions(options).setGuid(appGuid)).addModel(AccessRightsModel.create().setInitialAccessRights(accessRights)).addModel(userGroupsModel).syncBuild();
	  return {
	    store,
	    resetState: () => userGroupsModel.clearState(),
	    userGroupsModel
	  };
	}

	var _transformAccessCodes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("transformAccessCodes");
	var _transformAccessRightValues = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("transformAccessRightValues");
	/**
	 * @abstract
	 */
	class BaseUserGroupsExporter {
	  constructor() {
	    Object.defineProperty(this, _transformAccessRightValues, {
	      value: _transformAccessRightValues2
	    });
	    Object.defineProperty(this, _transformAccessCodes, {
	      value: _transformAccessCodes2
	    });
	  }
	  transform(source) {
	    const result = [];
	    for (const userGroup of source.values()) {
	      result.push({
	        id: userGroup.id.startsWith(NEW_USER_GROUP_ID_PREFIX) ? '0' : userGroup.id,
	        title: userGroup.title,
	        accessCodes: babelHelpers.classPrivateFieldLooseBase(this, _transformAccessCodes)[_transformAccessCodes](userGroup.members),
	        accessRights: babelHelpers.classPrivateFieldLooseBase(this, _transformAccessRightValues)[_transformAccessRightValues](userGroup)
	      });
	    }
	    return result;
	  }
	  /**
	   * @abstract
	   * @protected
	   */
	  shouldBeIncludedInExport(userGroup, accessRightValue) {
	    throw new Error('Not implemented');
	  }
	}
	function _transformAccessCodes2(members) {
	  const result = {};
	  for (const [accessCode, member] of members) {
	    result[accessCode] = member.type;
	  }
	  return result;
	}
	function _transformAccessRightValues2(userGroup) {
	  const result = [];
	  for (const accessRightValue of userGroup.accessRights.values()) {
	    if (!this.shouldBeIncludedInExport(userGroup, accessRightValue)) {
	      continue;
	    }
	    for (const singleValue of accessRightValue.values) {
	      result.push({
	        id: accessRightValue.id,
	        value: singleValue
	      });
	    }
	  }
	  return result;
	}

	class AllUserGroupsExporter extends BaseUserGroupsExporter {
	  shouldBeIncludedInExport(userGroup, accessRightValue) {
	    return true;
	  }
	}

	class OnlyChangedUserGroupsExporter extends BaseUserGroupsExporter {
	  shouldBeIncludedInExport(userGroup, accessRightValue) {
	    return userGroup.isNew || accessRightValue.isModified;
	  }
	}

	var _internalizeExternalSection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("internalizeExternalSection");
	var _internalizeExternalIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("internalizeExternalIcon");
	var _internalizeExternalItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("internalizeExternalItem");
	var _internalizeSelectedVariablesAliases = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("internalizeSelectedVariablesAliases");
	var _internalizeValueSet = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("internalizeValueSet");
	var _internalizeSetEmptyOnSetMinMaxValueInColumn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("internalizeSetEmptyOnSetMinMaxValueInColumn");
	var _internalizeExternalVariable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("internalizeExternalVariable");
	class AccessRightsInternalizer {
	  constructor() {
	    Object.defineProperty(this, _internalizeExternalVariable, {
	      value: _internalizeExternalVariable2
	    });
	    Object.defineProperty(this, _internalizeSetEmptyOnSetMinMaxValueInColumn, {
	      value: _internalizeSetEmptyOnSetMinMaxValueInColumn2
	    });
	    Object.defineProperty(this, _internalizeValueSet, {
	      value: _internalizeValueSet2
	    });
	    Object.defineProperty(this, _internalizeSelectedVariablesAliases, {
	      value: _internalizeSelectedVariablesAliases2
	    });
	    Object.defineProperty(this, _internalizeExternalItem, {
	      value: _internalizeExternalItem2
	    });
	    Object.defineProperty(this, _internalizeExternalIcon, {
	      value: _internalizeExternalIcon2
	    });
	    Object.defineProperty(this, _internalizeExternalSection, {
	      value: _internalizeExternalSection2
	    });
	  }
	  transform(externalSource) {
	    const result = new Map();
	    for (const external of externalSource) {
	      const internalized = babelHelpers.classPrivateFieldLooseBase(this, _internalizeExternalSection)[_internalizeExternalSection](external);
	      result.set(internalized.sectionCode, internalized);
	    }
	    return result;
	  }
	}
	function _internalizeExternalSection2(externalSection) {
	  const internalizedSection = {
	    sectionCode: main_core.Type.isStringFilled(externalSection.sectionCode) ? externalSection.sectionCode : main_core.Text.getRandom(),
	    sectionTitle: String(externalSection.sectionTitle),
	    sectionSubTitle: main_core.Type.isStringFilled(externalSection.sectionSubTitle) ? externalSection.sectionSubTitle : null,
	    sectionHint: main_core.Type.isStringFilled(externalSection.sectionHint) ? externalSection.sectionHint : null,
	    sectionIcon: babelHelpers.classPrivateFieldLooseBase(this, _internalizeExternalIcon)[_internalizeExternalIcon](externalSection.sectionIcon),
	    rights: new Map(),
	    isExpanded: true,
	    isShown: true
	  };
	  for (const externalItem of externalSection.rights) {
	    const internalizedItem = babelHelpers.classPrivateFieldLooseBase(this, _internalizeExternalItem)[_internalizeExternalItem](externalItem);
	    internalizedSection.rights.set(internalizedItem.id, internalizedItem);
	  }
	  return internalizedSection;
	}
	function _internalizeExternalIcon2(externalIcon) {
	  if (main_core.Type.isStringFilled(externalIcon == null ? void 0 : externalIcon.type) && main_core.Type.isStringFilled(externalIcon == null ? void 0 : externalIcon.bgColor)) {
	    return {
	      type: externalIcon.type,
	      bgColor: externalIcon.bgColor
	    };
	  }
	  return null;
	}
	function _internalizeExternalItem2(externalItem) {
	  const [aliases, separator] = babelHelpers.classPrivateFieldLooseBase(this, _internalizeSelectedVariablesAliases)[_internalizeSelectedVariablesAliases](externalItem.selectedVariablesAliases);
	  const normalizedItem = {
	    id: String(externalItem.id),
	    type: String(externalItem.type),
	    title: String(externalItem.title),
	    hint: main_core.Type.isStringFilled(externalItem.hint) ? externalItem.hint : null,
	    group: main_core.Type.isNil(externalItem.group) ? null : String(externalItem.group),
	    groupHead: main_core.Type.isBoolean(externalItem.groupHead) ? externalItem.groupHead : false,
	    isShown: true,
	    minValue: babelHelpers.classPrivateFieldLooseBase(this, _internalizeValueSet)[_internalizeValueSet](externalItem.minValue),
	    maxValue: babelHelpers.classPrivateFieldLooseBase(this, _internalizeValueSet)[_internalizeValueSet](externalItem.maxValue),
	    defaultValue: babelHelpers.classPrivateFieldLooseBase(this, _internalizeValueSet)[_internalizeValueSet](externalItem.defaultValue),
	    emptyValue: babelHelpers.classPrivateFieldLooseBase(this, _internalizeValueSet)[_internalizeValueSet](externalItem.emptyValue),
	    nothingSelectedValue: babelHelpers.classPrivateFieldLooseBase(this, _internalizeValueSet)[_internalizeValueSet](externalItem.nothingSelectedValue),
	    setEmptyOnSetMinMaxValueInColumn: babelHelpers.classPrivateFieldLooseBase(this, _internalizeSetEmptyOnSetMinMaxValueInColumn)[_internalizeSetEmptyOnSetMinMaxValueInColumn](externalItem),
	    variables: main_core.Type.isArray(externalItem.variables) ? new Map() : null,
	    allSelectedCode: main_core.Type.isStringFilled(externalItem.allSelectedCode) ? externalItem.allSelectedCode : null,
	    selectedVariablesAliases: aliases,
	    selectedVariablesAliasesSeparator: separator,
	    enableSearch: main_core.Type.isBoolean(externalItem.enableSearch) ? externalItem.enableSearch : null,
	    showAvatars: main_core.Type.isBoolean(externalItem.showAvatars) ? externalItem.showAvatars : null,
	    compactView: main_core.Type.isBoolean(externalItem.compactView) ? externalItem.compactView : null,
	    hintTitle: main_core.Type.isStringFilled(externalItem.hintTitle) ? externalItem.hintTitle : null
	  };
	  if (normalizedItem.groupHead || normalizedItem.group) {
	    normalizedItem.isGroupExpanded = false;
	  }
	  if (main_core.Type.isArray(externalItem.variables)) {
	    for (const variable of externalItem.variables) {
	      const normalizedVariable = babelHelpers.classPrivateFieldLooseBase(this, _internalizeExternalVariable)[_internalizeExternalVariable](variable);
	      normalizedItem.variables.set(normalizedVariable.id, normalizedVariable);
	    }
	  }
	  return normalizedItem;
	}
	function _internalizeSelectedVariablesAliases2(externalAliases) {
	  if (!main_core.Type.isPlainObject(externalAliases)) {
	    return [new Map(), DEFAULT_ALIAS_SEPARATOR];
	  }
	  const separator = main_core.Type.isString(externalAliases.separator) ? externalAliases.separator : DEFAULT_ALIAS_SEPARATOR;
	  const result = new Map();
	  for (const [key, value] of Object.entries(externalAliases)) {
	    if (key === 'separator') {
	      continue;
	    }
	    result.set(normalizeAliasKey(key, separator), String(value));
	  }
	  return [result, separator];
	}
	function _internalizeValueSet2(value) {
	  if (main_core.Type.isNil(value)) {
	    return null;
	  }
	  if (main_core.Type.isArray(value)) {
	    return new Set(value.map(item => String(item)));
	  }
	  return new Set([String(value)]);
	}
	function _internalizeSetEmptyOnSetMinMaxValueInColumn2(externalItem) {
	  const boolOrNull = x => main_core.Type.isBoolean(x) ? x : null;
	  if (!main_core.Type.isUndefined(externalItem.setEmptyOnSetMinMaxValueInColumn)) {
	    return boolOrNull(externalItem.setEmptyOnSetMinMaxValueInColumn);
	  }

	  // todo compatibility, can be removed when crm update is out
	  return boolOrNull(externalItem.setEmptyOnGroupActions);
	}
	function _internalizeExternalVariable2(externalVariable) {
	  return {
	    id: String(externalVariable.id),
	    title: String(externalVariable.title),
	    entityId: main_core.Type.isStringFilled(externalVariable.entityId) ? externalVariable.entityId : null,
	    supertitle: main_core.Type.isStringFilled(externalVariable.supertitle) ? externalVariable.supertitle : null,
	    avatar: main_core.Type.isStringFilled(externalVariable.avatar) ? externalVariable.avatar : null,
	    avatarOptions: main_core.Type.isPlainObject(externalVariable.avatarOptions) ? externalVariable.avatarOptions : null,
	    conflictsWith: main_core.Type.isArray(externalVariable.conflictsWith) ? new Set(externalVariable.conflictsWith.map(x => String(x))) : null,
	    requires: main_core.Type.isArray(externalVariable.requires) ? new Set(externalVariable.requires.map(x => String(x))) : null,
	    secondary: main_core.Type.isBoolean(externalVariable.secondary) ? externalVariable.secondary : null
	  };
	}

	var _deepFreeze = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deepFreeze");
	class ApplicationInternalizer {
	  constructor() {
	    Object.defineProperty(this, _deepFreeze, {
	      value: _deepFreeze2
	    });
	  }
	  // noinspection OverlyComplexFunctionJS
	  transform(externalSource) {
	    // freeze tells vue that we don't need reactivity on this state
	    // and prevents accidental modification as well
	    return babelHelpers.classPrivateFieldLooseBase(this, _deepFreeze)[_deepFreeze]({
	      component: String(externalSource.component),
	      actionSave: main_core.Type.isStringFilled(externalSource.actionSave) ? externalSource.actionSave : ACTION_SAVE,
	      mode: main_core.Type.isStringFilled(externalSource.mode) ? externalSource.mode : MODE,
	      bodyType: main_core.Type.isStringFilled(externalSource.bodyType) ? externalSource.bodyType : BODY_TYPE,
	      additionalSaveParams: main_core.Type.isPlainObject(externalSource.additionalSaveParams) ? externalSource.additionalSaveParams : null,
	      isSaveOnlyChangedRights: main_core.Type.isBoolean(externalSource.isSaveOnlyChangedRights) ? externalSource.isSaveOnlyChangedRights : false,
	      maxVisibleUserGroups: main_core.Type.isInteger(externalSource.maxVisibleUserGroups) ? externalSource.maxVisibleUserGroups : null,
	      searchContainerSelector: main_core.Type.isStringFilled(externalSource.searchContainerSelector) ? externalSource.searchContainerSelector : null
	    });
	  }
	}
	function _deepFreeze2(target) {
	  if (main_core.Type.isObject(target)) {
	    Object.values(target).forEach(value => {
	      babelHelpers.classPrivateFieldLooseBase(this, _deepFreeze)[_deepFreeze](value);
	    });
	    return Object.freeze(target);
	  }
	  return target;
	}

	var _maxVisibleUserGroups = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("maxVisibleUserGroups");
	var _internalizeExternalGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("internalizeExternalGroup");
	var _internalizeExternalAccessRightsValue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("internalizeExternalAccessRightsValue");
	var _internalizeExternalAccessCode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("internalizeExternalAccessCode");
	var _internalizeExternalMember = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("internalizeExternalMember");
	class UserGroupsInternalizer {
	  constructor(maxVisibleUserGroups) {
	    Object.defineProperty(this, _internalizeExternalMember, {
	      value: _internalizeExternalMember2
	    });
	    Object.defineProperty(this, _internalizeExternalAccessCode, {
	      value: _internalizeExternalAccessCode2
	    });
	    Object.defineProperty(this, _internalizeExternalAccessRightsValue, {
	      value: _internalizeExternalAccessRightsValue2
	    });
	    Object.defineProperty(this, _internalizeExternalGroup, {
	      value: _internalizeExternalGroup2
	    });
	    Object.defineProperty(this, _maxVisibleUserGroups, {
	      writable: true,
	      value: null
	    });
	    if (main_core.Type.isInteger(maxVisibleUserGroups)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _maxVisibleUserGroups)[_maxVisibleUserGroups] = maxVisibleUserGroups;
	    }
	  }
	  transform(externalSource) {
	    const result = new Map();
	    for (const externalGroup of externalSource) {
	      const internalGroup = babelHelpers.classPrivateFieldLooseBase(this, _internalizeExternalGroup)[_internalizeExternalGroup](externalGroup);
	      if (babelHelpers.classPrivateFieldLooseBase(this, _maxVisibleUserGroups)[_maxVisibleUserGroups] > 0 && result.size >= babelHelpers.classPrivateFieldLooseBase(this, _maxVisibleUserGroups)[_maxVisibleUserGroups]) {
	        internalGroup.isShown = false;
	      }
	      result.set(internalGroup.id, internalGroup);
	    }
	    return result;
	  }
	}
	function _internalizeExternalGroup2(externalGroup) {
	  const internalizedGroup = {
	    id: String(externalGroup.id),
	    isNew: false,
	    isModified: false,
	    isShown: true,
	    title: String(externalGroup.title),
	    accessRights: new Map(),
	    members: new Map()
	  };
	  for (const externalValue of externalGroup.accessRights) {
	    const internalizedValue = babelHelpers.classPrivateFieldLooseBase(this, _internalizeExternalAccessRightsValue)[_internalizeExternalAccessRightsValue](externalValue);
	    if (internalizedGroup.accessRights.has(internalizedValue.id)) {
	      for (const previousValue of internalizedGroup.accessRights.get(internalizedValue.id).values) {
	        internalizedValue.values.add(previousValue);
	      }
	    }
	    internalizedGroup.accessRights.set(internalizedValue.id, internalizedValue);
	  }
	  for (const [accessCode, externalMember] of Object.entries(externalGroup.members)) {
	    const internalizedAccessCode = babelHelpers.classPrivateFieldLooseBase(this, _internalizeExternalAccessCode)[_internalizeExternalAccessCode](accessCode);
	    internalizedGroup.members.set(internalizedAccessCode, babelHelpers.classPrivateFieldLooseBase(this, _internalizeExternalMember)[_internalizeExternalMember](externalMember));
	  }
	  return internalizedGroup;
	}
	function _internalizeExternalAccessRightsValue2(externalAccessRightsValue) {
	  const valueId = String(externalAccessRightsValue.id);
	  const internalized = {
	    id: valueId,
	    isModified: false
	  };
	  const values = main_core.Type.isArray(externalAccessRightsValue.value) ? externalAccessRightsValue.value : [externalAccessRightsValue.value];
	  internalized.values = new Set(values.map(x => String(x)));
	  return internalized;
	}
	function _internalizeExternalAccessCode2(accessCode) {
	  let stringAccessCode = String(accessCode);
	  if (/^IU(\d+)$/.test(stringAccessCode)) {
	    // `IU` and `U` are basically the same in this extension. differentiation between them is not supported
	    // for data consistency, force `U`
	    stringAccessCode = stringAccessCode.replace('IU', 'U');
	  }
	  return stringAccessCode;
	}
	function _internalizeExternalMember2(externalMember) {
	  return {
	    type: String(externalMember.type),
	    id: String(externalMember.id),
	    name: String(externalMember.name),
	    avatar: main_core.Type.isStringFilled(externalMember.avatar) ? externalMember.avatar : null
	  };
	}

	var _srcUserGroups = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("srcUserGroups");
	var _maxVisibleUserGroups$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("maxVisibleUserGroups");
	var _ensureThatNoMoreUserGroupsThanMaxIsShown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ensureThatNoMoreUserGroupsThanMaxIsShown");
	class ShownUserGroupsCopier {
	  constructor(srcUserGroups, maxVisibleUserGroups) {
	    Object.defineProperty(this, _ensureThatNoMoreUserGroupsThanMaxIsShown, {
	      value: _ensureThatNoMoreUserGroupsThanMaxIsShown2
	    });
	    Object.defineProperty(this, _srcUserGroups, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _maxVisibleUserGroups$1, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _srcUserGroups)[_srcUserGroups] = srcUserGroups;
	    if (main_core.Type.isInteger(maxVisibleUserGroups)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _maxVisibleUserGroups$1)[_maxVisibleUserGroups$1] = maxVisibleUserGroups;
	    }
	  }

	  /**
	   * WARNING! Mutates `externalSource`. Src is not copied for perf reasons, since we don't need it functionally
	   */
	  transform(externalSource) {
	    for (const [userGroupId, userGroup] of externalSource) {
	      const srcUserGroup = babelHelpers.classPrivateFieldLooseBase(this, _srcUserGroups)[_srcUserGroups].get(userGroupId);
	      if (srcUserGroup) {
	        userGroup.isShown = srcUserGroup.isShown;
	      } else {
	        // likely it's a just created user group
	        userGroup.isShown = true;
	      }
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _maxVisibleUserGroups$1)[_maxVisibleUserGroups$1] > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _ensureThatNoMoreUserGroupsThanMaxIsShown)[_ensureThatNoMoreUserGroupsThanMaxIsShown](externalSource);
	    }
	    return externalSource;
	  }
	}
	function _ensureThatNoMoreUserGroupsThanMaxIsShown2(userGroups) {
	  let shownCount = 0;
	  for (const userGroup of userGroups.values()) {
	    if (!userGroup.isShown) {
	      continue;
	    }
	    shownCount++;
	    if (shownCount > babelHelpers.classPrivateFieldLooseBase(this, _maxVisibleUserGroups$1)[_maxVisibleUserGroups$1]) {
	      userGroup.isShown = false;
	    }
	  }
	}

	var _options$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _renderTo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTo");
	var _buttonPanel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buttonPanel");
	var _guid$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("guid");
	var _isUserConfirmedClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isUserConfirmedClose");
	var _handleSliderClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleSliderClose");
	var _app = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("app");
	var _rootComponent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rootComponent");
	var _store$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _resetState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resetState");
	var _unwatch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unwatch");
	var _userGroupsModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userGroupsModel");
	var _analyticsManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("analyticsManager");
	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _unbindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unbindEvents");
	var _tryShowFeaturePromoter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tryShowFeaturePromoter");
	var _showNotification = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showNotification");
	var _runSaveAjaxRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("runSaveAjaxRequest");
	var _confirmBeforeClosingModifiedSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("confirmBeforeClosingModifiedSlider");
	/**
	 * @memberOf BX.UI.AccessRights.V2
	 */
	class App {
	  constructor(options) {
	    Object.defineProperty(this, _confirmBeforeClosingModifiedSlider, {
	      value: _confirmBeforeClosingModifiedSlider2
	    });
	    Object.defineProperty(this, _runSaveAjaxRequest, {
	      value: _runSaveAjaxRequest2
	    });
	    Object.defineProperty(this, _showNotification, {
	      value: _showNotification2
	    });
	    Object.defineProperty(this, _tryShowFeaturePromoter, {
	      value: _tryShowFeaturePromoter2
	    });
	    Object.defineProperty(this, _unbindEvents, {
	      value: _unbindEvents2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    Object.defineProperty(this, _options$1, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _renderTo, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _buttonPanel, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _guid$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isUserConfirmedClose, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _handleSliderClose, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _app, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _rootComponent, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _resetState, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _unwatch, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userGroupsModel, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _analyticsManager, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1] = options || {};
	    babelHelpers.classPrivateFieldLooseBase(this, _renderTo)[_renderTo] = babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].renderTo;
	    babelHelpers.classPrivateFieldLooseBase(this, _buttonPanel)[_buttonPanel] = BX.UI.ButtonPanel || null;
	    babelHelpers.classPrivateFieldLooseBase(this, _guid$1)[_guid$1] = main_core.Text.getRandom(16);
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents)[_bindEvents]();
	  }
	  fireEventReset() {
	    const box = ui_dialogs_messagebox.MessageBox.create({
	      message: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CANCEL_WARNING'),
	      modal: true,
	      buttons: [new ui_buttons.Button({
	        color: ui_buttons.ButtonColor.PRIMARY,
	        size: ui_buttons.ButtonSize.SMALL,
	        text: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CANCEL_YES_CANCEL'),
	        onclick: () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _analyticsManager)[_analyticsManager].onCancelChanges();
	          babelHelpers.classPrivateFieldLooseBase(this, _resetState)[_resetState]();
	          box.close();
	        }
	      }), new ui_buttons.Button({
	        color: ui_buttons.ButtonColor.LINK,
	        size: ui_buttons.ButtonSize.SMALL,
	        text: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CANCEL_NO_CANCEL'),
	        onclick: () => {
	          box.close();
	        }
	      })]
	    });
	    box.show();
	  }
	  sendActionRequest() {
	    return new Promise((resolve, reject) => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].state.application.isSaving || !babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['userGroups/isModified']) {
	        resolve();
	        return;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].commit('application/setSaving', true);
	      babelHelpers.classPrivateFieldLooseBase(this, _analyticsManager)[_analyticsManager].onSaveAttempt();
	      babelHelpers.classPrivateFieldLooseBase(this, _runSaveAjaxRequest)[_runSaveAjaxRequest]().then(({
	        userGroups
	      }) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _analyticsManager)[_analyticsManager].onSaveSuccess();
	        babelHelpers.classPrivateFieldLooseBase(this, _userGroupsModel)[_userGroupsModel].setInitialUserGroups(userGroups);

	        // reset modification flags and stuff
	        babelHelpers.classPrivateFieldLooseBase(this, _resetState)[_resetState]();
	        babelHelpers.classPrivateFieldLooseBase(this, _showNotification)[_showNotification](main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SETTINGS_HAVE_BEEN_SAVED'));
	      }).catch(response => {
	        var _response$errors, _response$errors$;
	        babelHelpers.classPrivateFieldLooseBase(this, _analyticsManager)[_analyticsManager].onSaveError(response);
	        console.warn('ui.accessrights.v2: error during save', response);
	        if (babelHelpers.classPrivateFieldLooseBase(this, _tryShowFeaturePromoter)[_tryShowFeaturePromoter](response)) {
	          reject(response);
	          return;
	        }
	        babelHelpers.classPrivateFieldLooseBase(this, _showNotification)[_showNotification]((response == null ? void 0 : (_response$errors = response.errors) == null ? void 0 : (_response$errors$ = _response$errors[0]) == null ? void 0 : _response$errors$.message) || 'Something went wrong');
	        reject(response);
	      }).finally(() => {
	        var _babelHelpers$classPr;
	        const waitContainer = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _buttonPanel)[_buttonPanel]) == null ? void 0 : _babelHelpers$classPr.getContainer().querySelector('.ui-btn-wait');
	        main_core.Dom.removeClass(waitContainer, 'ui-btn-wait');
	        babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].commit('application/setSaving', false);
	        resolve();
	      });
	    });
	  }
	  draw() {
	    const applicationOptions = new ApplicationInternalizer().transform(babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1]);
	    const {
	      store,
	      resetState,
	      userGroupsModel
	    } = createStore(applicationOptions, new UserGroupsInternalizer(applicationOptions.maxVisibleUserGroups).transform(babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].userGroups), new AccessRightsInternalizer().transform(babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].accessRights), babelHelpers.classPrivateFieldLooseBase(this, _guid$1)[_guid$1]);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1] = store;
	    babelHelpers.classPrivateFieldLooseBase(this, _resetState)[_resetState] = resetState;
	    babelHelpers.classPrivateFieldLooseBase(this, _userGroupsModel)[_userGroupsModel] = userGroupsModel;
	    babelHelpers.classPrivateFieldLooseBase(this, _unwatch)[_unwatch] = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].watch((state, getters) => getters['userGroups/isModified'], newValue => {
	      if (newValue) {
	        var _babelHelpers$classPr2;
	        (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _buttonPanel)[_buttonPanel]) == null ? void 0 : _babelHelpers$classPr2.show();
	      } else {
	        var _babelHelpers$classPr3;
	        (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _buttonPanel)[_buttonPanel]) == null ? void 0 : _babelHelpers$classPr3.hide();
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _app)[_app] = ui_vue3.BitrixVue.createApp(Grid);
	    babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].use(babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1]);
	    main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _renderTo)[_renderTo]);
	    babelHelpers.classPrivateFieldLooseBase(this, _rootComponent)[_rootComponent] = babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].mount(babelHelpers.classPrivateFieldLooseBase(this, _renderTo)[_renderTo]);
	    babelHelpers.classPrivateFieldLooseBase(this, _analyticsManager)[_analyticsManager] = new AnalyticsManager(babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1], babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].analytics);
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _analyticsManager)[_analyticsManager] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].unmount();
	    babelHelpers.classPrivateFieldLooseBase(this, _app)[_app] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _unbindEvents)[_unbindEvents]();
	    babelHelpers.classPrivateFieldLooseBase(this, _unwatch)[_unwatch]();
	    babelHelpers.classPrivateFieldLooseBase(this, _unwatch)[_unwatch] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _resetState)[_resetState] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _userGroupsModel)[_userGroupsModel] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _buttonPanel)[_buttonPanel] = null;
	    main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _renderTo)[_renderTo]);
	    babelHelpers.classPrivateFieldLooseBase(this, _renderTo)[_renderTo] = null;
	  }
	  hasUnsavedChanges() {
	    return !(!babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['userGroups/isModified'] || babelHelpers.classPrivateFieldLooseBase(this, _isUserConfirmedClose)[_isUserConfirmedClose]);
	  }
	  scrollToSection(sectionCode) {
	    babelHelpers.classPrivateFieldLooseBase(this, _rootComponent)[_rootComponent].scrollToSection(sectionCode);
	  }
	}
	function _bindEvents2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _handleSliderClose)[_handleSliderClose] = event => {
	    var _BX$SidePanel, _BX$SidePanel$Instanc;
	    const [sliderEvent] = event.getData();
	    const isSliderBelongsToThisApp = ((_BX$SidePanel = BX.SidePanel) == null ? void 0 : (_BX$SidePanel$Instanc = _BX$SidePanel.Instance) == null ? void 0 : _BX$SidePanel$Instanc.getSliderByWindow(window)) === (sliderEvent == null ? void 0 : sliderEvent.getSlider());
	    if (!isSliderBelongsToThisApp) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _confirmBeforeClosingModifiedSlider)[_confirmBeforeClosingModifiedSlider](sliderEvent);
	  };
	  main_core_events.EventEmitter.subscribe('SidePanel.Slider:onClose', babelHelpers.classPrivateFieldLooseBase(this, _handleSliderClose)[_handleSliderClose]);
	}
	function _unbindEvents2() {
	  main_core_events.EventEmitter.unsubscribe('SidePanel.Slider:onClose', babelHelpers.classPrivateFieldLooseBase(this, _handleSliderClose)[_handleSliderClose]);
	  babelHelpers.classPrivateFieldLooseBase(this, _handleSliderClose)[_handleSliderClose] = null;
	}
	function _tryShowFeaturePromoter2(response) {
	  if (!main_core.Type.isArrayFilled(response == null ? void 0 : response.errors)) {
	    return false;
	  }
	  for (const error of response.errors) {
	    var _error$customData;
	    if (main_core.Type.isStringFilled(error == null ? void 0 : (_error$customData = error.customData) == null ? void 0 : _error$customData.sliderCode)) {
	      main_core.Runtime.loadExtension('ui.info-helper').then(({
	        FeaturePromotersRegistry
	      }) => {
	        /** @see BX.UI.FeaturePromotersRegistry */
	        FeaturePromotersRegistry.getPromoter({
	          code: error.customData.sliderCode
	        }).show();
	      }).catch(loadError => {
	        console.error('ui.accessrights.v2: could not load ui.info-helper', loadError);
	      });
	      return true;
	    }
	  }
	  return false;
	}
	function _showNotification2(title) {
	  BX.UI.Notification.Center.notify({
	    content: title,
	    position: 'top-right',
	    autoHideDelay: 3000
	  });
	}
	function _runSaveAjaxRequest2() {
	  const internalUserGroups = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].state.userGroups.collection;
	  let userGroups = null;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].state.application.options.isSaveOnlyChangedRights) {
	    userGroups = new OnlyChangedUserGroupsExporter().transform(internalUserGroups);
	  } else {
	    userGroups = new AllUserGroupsExporter().transform(internalUserGroups);
	  }
	  const bodyType = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].state.application.options.bodyType;

	  // wrap ajax in native promise
	  return new Promise((resolve, reject) => {
	    main_core.ajax.runComponentAction(babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].state.application.options.component, babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].state.application.options.actionSave, {
	      mode: babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].state.application.options.mode,
	      [bodyType]: {
	        userGroups,
	        deletedUserGroups: [...babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].state.userGroups.deleted.values()],
	        parameters: babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].state.application.options.additionalSaveParams
	      }
	    }).then(response => {
	      const maxVisibleUserGroups = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].state.application.options.maxVisibleUserGroups;
	      const newUserGroups = new UserGroupsInternalizer(maxVisibleUserGroups).transform(response.data.USER_GROUPS);
	      new ShownUserGroupsCopier(internalUserGroups, maxVisibleUserGroups).transform(newUserGroups);
	      resolve({
	        userGroups: newUserGroups
	      });
	    }).catch(reject);
	  });
	}
	function _confirmBeforeClosingModifiedSlider2(sliderEvent) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['userGroups/isModified'] || babelHelpers.classPrivateFieldLooseBase(this, _isUserConfirmedClose)[_isUserConfirmedClose]) {
	    return;
	  }
	  sliderEvent.denyAction();
	  const box = ui_dialogs_messagebox.MessageBox.create({
	    title: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CLOSE_WARNING_TITLE'),
	    message: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CLOSE_WARNING'),
	    modal: true,
	    buttons: [new ui_buttons.Button({
	      color: ui_buttons.ButtonColor.PRIMARY,
	      size: ui_buttons.ButtonSize.SMALL,
	      text: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CLOSE_YES_CLOSE'),
	      onclick: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _analyticsManager)[_analyticsManager].onCloseWithoutSave();
	        babelHelpers.classPrivateFieldLooseBase(this, _isUserConfirmedClose)[_isUserConfirmedClose] = true;
	        box.close();
	        setTimeout(() => {
	          sliderEvent.getSlider().close();
	        });
	      }
	    }), new ui_buttons.CancelButton({
	      size: ui_buttons.ButtonSize.SMALL,
	      onclick: () => {
	        box.close();
	      }
	    })]
	  });
	  box.show();
	}

	exports.App = App;

}((this.BX.UI.AccessRights.V2 = this.BX.UI.AccessRights.V2 || {}),BX.Event,BX.UI.Dialogs,BX.UI,BX.UI.Vue3.Components,BX.UI.EntitySelector,BX.Vue3,BX.Vue3.Directives,BX.UI.Vue3.Components,BX.Main,BX.UI,BX,BX.UI.Vue3.Components,BX,BX.UI.Analytics,BX.Vue3.Vuex,BX));
//# sourceMappingURL=v2.bundle.js.map
