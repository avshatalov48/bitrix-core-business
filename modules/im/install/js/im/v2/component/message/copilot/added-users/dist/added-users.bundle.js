/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_public,im_v2_component_message_base,im_v2_component_elements) {
	'use strict';

	// @vue/component
	const ChatCopilotAddedUsersMessage = {
	  name: 'ChatCopilotAddedUsersMessage',
	  components: {
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    UserListPopup: im_v2_component_elements.UserListPopup
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      showMoreUsers: false
	    };
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    addedUsers() {
	      const addedUsers = this.message.componentParams.addedUsers;
	      const firstAddedUser = addedUsers.shift();
	      return {
	        first: firstAddedUser,
	        restUsers: addedUsers
	      };
	    },
	    firstAddedUserName() {
	      return this.$store.getters['users/get'](this.addedUsers.first).name;
	    },
	    andMoreAddedUsers() {
	      if (this.addedUsers.restUsers.length === 0) {
	        return '';
	      }
	      return this.loc('IM_MESSAGE_COPILOT_ADDED_USERS_DESCRIPTION_MORE', {
	        '#NAME#': '',
	        '#COUNT#': this.addedUsers.restUsers.length
	      });
	    },
	    preparedDescription() {
	      return this.loc('IM_MESSAGE_COPILOT_ADDED_USERS_DESCRIPTION_MENTION_MSGVER_1', {
	        '#BR#': '\n'
	      });
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    onFirstAddedUserClick() {
	      im_public.Messenger.openChat(this.addedUsers.first.toString());
	    },
	    onMoreUsersClick() {
	      this.showMoreUsers = true;
	    }
	  },
	  template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withReactions="false"
			:withBackground="false"
		>
			<div class="bx-im-message-copilot-added-users__container">
				<div class="bx-im-message-copilot-added-users__image"></div>
				<div class="bx-im-message-copilot-added-users__content">
					<div class="bx-im-message-copilot-added-users__title">
						{{ loc('IM_MESSAGE_COPILOT_ADDED_USERS_TITLE') }}
					</div>
					<div
						class="bx-im-message-copilot-added-users__description"
						:title="preparedDescription"
					>
						{{ preparedDescription }}
					</div>
					<div class="bx-im-message-copilot-added-users__users"> 
						<span class="bx-im-message-copilot-added-users__user" @click="onFirstAddedUserClick">
							{{ firstAddedUserName }}
						</span>
						<span
							v-if="andMoreAddedUsers"
							class="bx-im-message-copilot-added-users__user"
							@click="onMoreUsersClick"
							ref="addedUsersLink"
						>
							{{ andMoreAddedUsers }}
						</span>
					</div>
				</div>
			</div>
			<UserListPopup
				:showPopup="showMoreUsers"
				:userIds="addedUsers.restUsers"
				:contextDialogId="dialogId"
				:bindElement="$refs.addedUsersLink || {}"
				:withAngle="false"
				:forceTop="true"
				@close="showMoreUsers = false"
			/>
		</BaseMessage>
	`
	};

	exports.ChatCopilotAddedUsersMessage = ChatCopilotAddedUsersMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=added-users.bundle.js.map
