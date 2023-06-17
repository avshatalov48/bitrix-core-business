this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Embedding = this.BX.Messenger.Embedding || {};
this.BX.Messenger.Embedding.Provider = this.BX.Messenger.Embedding.Provider || {};
(function (exports,pull_client,main_core,im_oldChatEmbedding_application_core,im_oldChatEmbedding_lib_logger,im_oldChatEmbedding_lib_user,im_oldChatEmbedding_const) {
	'use strict';

	class BasePullHandler {
	  constructor() {
	    this.store = im_oldChatEmbedding_application_core.Core.getStore();
	    this.userManager = new im_oldChatEmbedding_lib_user.UserManager();
	  }
	  getModuleId() {
	    return 'im';
	  }
	  getSubscriptionType() {
	    return pull_client.PullClient.SubscriptionType.Server;
	  }
	  handleMessage(params, extra) {
	    this.handleMessageAdd(params, extra);
	  }
	  handleMessageChat(params, extra) {
	    this.handleMessageAdd(params, extra);
	  }
	  handleMessageAdd(params, extra) {
	    im_oldChatEmbedding_lib_logger.Logger.warn('handleMessageAdd', params);
	    if (params.lines) {
	      return false;
	    }
	    if (params != null && params.chat[params.chatId]) {
	      const chatToAdd = {
	        ...params.chat[params.chatId],
	        dialogId: params.dialogId
	      };
	      this.store.dispatch('dialogues/set', chatToAdd);
	    }

	    //set users
	    if (params.users) {
	      this.userManager.setUsersToModel(Object.values(params.users));
	    }

	    //stop writing event
	    this.store.dispatch('dialogues/stopWriting', {
	      dialogId: params.dialogId,
	      userId: params.message.senderId
	    });

	    // counters (TBD for own message)
	    if (params.message.senderId !== im_oldChatEmbedding_application_core.Core.getUserId()) {
	      this.store.dispatch('dialogues/update', {
	        dialogId: params.dialogId,
	        fields: {
	          counter: params.counter
	        }
	      });
	    }
	  }
	  handleMessageUpdate(params, extra, command) {
	    this.execMessageUpdateOrDelete(params, extra, command);
	  }
	  handleMessageDelete(params, extra, command) {
	    this.execMessageUpdateOrDelete(params, extra, command);
	  }
	  handleMessageDeleteComplete(params, extra) {
	    this.execMessageUpdateOrDelete(params, extra, command);
	  }
	  execMessageUpdateOrDelete(params, extra, command) {
	    this.store.dispatch('dialogues/stopWriting', {
	      dialogId: params.dialogId,
	      userId: params.senderId
	    });
	  }
	  handleChatOwner(params, extra) {
	    this.store.dispatch('dialogues/update', {
	      dialogId: params.dialogId,
	      fields: {
	        ownerId: params.userId
	      }
	    });
	  }
	  handleChatManagers(params, extra) {
	    this.store.dispatch('dialogues/update', {
	      dialogId: params.dialogId,
	      fields: {
	        managerList: params.list
	      }
	    });
	  }
	  handleChatUpdateParams(params, extra) {
	    this.store.dispatch('dialogues/update', {
	      dialogId: params.dialogId,
	      fields: params.params
	    });
	  }
	  handleChatUserAdd(params, extra) {
	    if (params.users) {
	      this.userManager.setUsersToModel(params.users);
	    }
	    this.store.dispatch('dialogues/update', {
	      dialogId: params.dialogId,
	      fields: {
	        userCounter: params.userCount
	      }
	    });
	  }
	  handleChatUserLeave(params, extra) {
	    this.handleChatUserAdd(params, extra);
	  }
	  handleStartWriting(params, extra) {
	    const {
	      dialogId,
	      userId,
	      userName
	    } = params;
	    this.store.dispatch('dialogues/startWriting', {
	      dialogId,
	      userId,
	      userName
	    });
	  }
	  handleReadMessage(params, extra) {
	    im_oldChatEmbedding_lib_logger.Logger.warn('handleReadMessage', params);
	    this.store.dispatch('dialogues/update', {
	      dialogId: params.dialogId,
	      fields: {
	        counter: params.counter
	      }
	    });
	  }
	  handleReadMessageChat(params, extra) {
	    this.handleReadMessage(params, extra);
	  }
	  handleUnreadMessage(params, extra) {
	    this.store.dispatch('dialogues/update', {
	      dialogId: params.dialogId,
	      fields: {
	        counter: params.counter
	      }
	    });
	  }
	  handleUnreadMessageChat(params, extra) {
	    this.handleUnreadMessage(params, extra);
	  }
	  handleUnreadMessageOpponent(params, extra) {
	    this.execUnreadMessageOpponent(params, extra);
	  }
	  handleUnreadMessageChatOpponent(params, extra) {
	    this.execUnreadMessageOpponent(params, extra);
	  }
	  execUnreadMessageOpponent(params, extra) {
	    this.store.dispatch('dialogues/removeFromReadList', {
	      dialogId: params.dialogId,
	      userId: params.userId
	    });
	  }
	  handleReadAllChats() {
	    im_oldChatEmbedding_lib_logger.Logger.warn('BasePullHandler: handleReadAllChats');
	    this.store.dispatch('dialogues/clearCounters');
	    this.store.dispatch('recent/clearUnread');
	  }
	  handleChatMuteNotify(params) {
	    if (params.muted) {
	      this.store.dispatch('dialogues/mute', {
	        dialogId: params.dialogId
	      });
	      return true;
	    }
	    this.store.dispatch('dialogues/unmute', {
	      dialogId: params.dialogId
	    });
	  }
	  handleUserInvite(params) {
	    if (!params.invited) {
	      this.store.dispatch('users/update', {
	        id: params.userId,
	        fields: params.user
	      });
	    }
	  }
	  handleChatRename(params) {
	    const dialog = this.store.getters['dialogues/getByChatId'](params.chatId);
	    if (!dialog) {
	      return false;
	    }
	    this.store.dispatch('dialogues/update', {
	      dialogId: dialog.dialogId,
	      fields: {
	        name: params.name
	      }
	    });
	  }
	  handleChatAvatar(params) {
	    const dialog = this.store.getters['dialogues/getByChatId'](params.chatId);
	    if (!dialog) {
	      return false;
	    }
	    this.store.dispatch('dialogues/update', {
	      dialogId: dialog.dialogId,
	      fields: {
	        avatar: params.avatar
	      }
	    });
	  }
	}

	class RecentPullHandler {
	  constructor() {
	    this.store = im_oldChatEmbedding_application_core.Core.getStore();
	    this.userManager = new im_oldChatEmbedding_lib_user.UserManager();
	  }
	  getModuleId() {
	    return 'im';
	  }
	  handleMessage(params) {
	    this.handleMessageAdd(params);
	  }
	  handleMessageChat(params) {
	    this.handleMessageAdd(params);
	  }
	  handleMessageAdd(params) {
	    if (params.lines) {
	      return false;
	    }
	    const currentUserId = this.store.state.application.common.userId;
	    if (currentUserId && params.userInChat[params.chatId] && !params.userInChat[params.chatId].includes(currentUserId)) {
	      return false;
	    }
	    let attach = false;
	    if (main_core.Type.isArray(params.message.params['ATTACH'])) {
	      attach = params.message.params['ATTACH'];
	    }
	    let file = false;
	    if (main_core.Type.isArray(params.message.params['FILE_ID'])) {
	      file = params.files[params.message.params['FILE_ID'][0]];
	    }
	    im_oldChatEmbedding_lib_logger.Logger.warn('RecentPullHandler: handleMessageAdd', params);
	    const newRecentItem = {
	      id: params.dialogId,
	      message: {
	        id: params.message.id,
	        text: params.message.text,
	        date: params.message.date,
	        senderId: params.message.senderId,
	        attach,
	        file
	      }
	    };
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (recentItem) {
	      newRecentItem.options = {
	        birthdayPlaceholder: false
	      };
	      this.store.dispatch('recent/like', {
	        id: params.dialogId,
	        liked: false
	      });
	    }
	    const {
	      senderId
	    } = params.message;
	    const usersModel = this.store.state.users;
	    if (usersModel != null && usersModel.botList[senderId] && usersModel.botList[senderId].type === 'human') {
	      const {
	        text
	      } = params.message;
	      setTimeout(() => {
	        this.store.dispatch('recent/setRecent', newRecentItem);
	      }, this.getWaitTimeForHumanBot(text));
	      return;
	    }
	    this.store.dispatch('recent/setRecent', newRecentItem);
	  }
	  handleMessageUpdate(params, extra, command) {
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem || recentItem.message.id !== params.id) {
	      return false;
	    }
	    im_oldChatEmbedding_lib_logger.Logger.warn('RecentPullHandler: handleMessageUpdate', params, command);
	    let text = params.text;
	    if (command === 'messageDelete') {
	      text = main_core.Loc.getMessage('IM_EMBED_PULL_RECENT_MESSAGE_DELETED');
	    }
	    this.store.dispatch('recent/update', {
	      id: params.dialogId,
	      fields: {
	        message: {
	          id: params.id,
	          text: text,
	          date: recentItem.message.date,
	          status: recentItem.message.status,
	          senderId: params.senderId,
	          params: {
	            withFile: false,
	            withAttach: false
	          }
	        }
	      }
	    });
	  }
	  handleMessageDelete(params, extra, command) {
	    this.handleMessageUpdate(params, extra, command);
	  }
	  handleReadMessageOpponent(params) {
	    im_oldChatEmbedding_lib_logger.Logger.warn('RecentPullHandler: handleReadMessageOpponent', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    const lastReadMessage = Number.parseInt(params.lastId, 10);
	    if (!recentItem || recentItem.message.id !== lastReadMessage) {
	      return false;
	    }
	    this.store.dispatch('recent/update', {
	      id: params.dialogId,
	      fields: {
	        message: {
	          ...recentItem.message,
	          status: im_oldChatEmbedding_const.MessageStatus.delivered
	        }
	      }
	    });
	  }
	  handleReadMessageChatOpponent(params) {
	    this.handleReadMessageOpponent(params);
	  }
	  handleUnreadMessageOpponent(params) {
	    im_oldChatEmbedding_lib_logger.Logger.warn('RecentPullHandler: handleUnreadMessageOpponent', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    this.store.dispatch('recent/update', {
	      id: params.dialogId,
	      fields: {
	        message: {
	          ...recentItem.message,
	          status: im_oldChatEmbedding_const.MessageStatus.received
	        }
	      }
	    });
	  }
	  handleUnreadMessageChatOpponent(params) {
	    im_oldChatEmbedding_lib_logger.Logger.warn('RecentPullHandler: handleUnreadMessageChatOpponent', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    this.store.dispatch('recent/update', {
	      id: params.dialogId,
	      fields: {
	        message: {
	          ...recentItem.message,
	          status: params.chatMessageStatus
	        }
	      }
	    });
	  }
	  handleMessageLike(params) {
	    im_oldChatEmbedding_lib_logger.Logger.warn('RecentPullHandler: handleMessageLike', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    if (main_core.Type.isUndefined(BX.MessengerProxy)) {
	      return;
	    }
	    const currentDialogId = BX.MessengerProxy.getCurrentDialogId();
	    if (currentDialogId === params.dialogId) {
	      return false;
	    }
	    const currentUserId = im_oldChatEmbedding_application_core.Core.getUserId();
	    const isOwnLike = currentUserId === params.senderId;
	    const isOwnLastMessage = recentItem.message.senderId === currentUserId;
	    if (isOwnLike || !isOwnLastMessage) {
	      return false;
	    }
	    this.store.dispatch('recent/like', {
	      id: params.dialogId,
	      messageId: params.id,
	      liked: params.set
	    });
	  }
	  handleChatPin(params) {
	    im_oldChatEmbedding_lib_logger.Logger.warn('RecentPullHandler: handleChatPin', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    this.store.dispatch('recent/pin', {
	      id: params.dialogId,
	      action: params.active
	    });
	  }
	  handleChatUnread(params) {
	    im_oldChatEmbedding_lib_logger.Logger.warn('RecentPullHandler: handleChatUnread', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    this.store.dispatch('recent/unread', {
	      id: params.dialogId,
	      action: params.active
	    });
	  }
	  handleChatHide(params) {
	    im_oldChatEmbedding_lib_logger.Logger.warn('RecentPullHandler: handleChatHide', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    this.store.dispatch('recent/delete', {
	      id: params.dialogId
	    });
	  }
	  handleChatUserLeave(params) {
	    im_oldChatEmbedding_lib_logger.Logger.warn('RecentPullHandler: handleChatUserLeave', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    const currentUserId = this.store.state.application.common.userId;
	    if (currentUserId !== params.userId) {
	      return false;
	    }
	    this.store.dispatch('recent/delete', {
	      id: params.dialogId
	    });
	  }
	  handleUserInvite(params) {
	    var _params$invited;
	    im_oldChatEmbedding_lib_logger.Logger.warn('RecentPullHandler: handleUserInvite', params);
	    this.store.dispatch('recent/setRecent', {
	      id: params.user.id,
	      invited: (_params$invited = params.invited) != null ? _params$invited : false
	    });
	    this.userManager.setUsersToModel([params.user]);
	  }
	  getWaitTimeForHumanBot(text) {
	    const INITIAL_WAIT = 1000;
	    const WAIT_PER_WORD = 300;
	    const WAIT_LIMIT = 5000;
	    let waitTime = text.split(' ').length * WAIT_PER_WORD + INITIAL_WAIT;
	    if (waitTime > WAIT_LIMIT) {
	      waitTime = WAIT_LIMIT;
	    }
	    return waitTime;
	  }
	}

	exports.BasePullHandler = BasePullHandler;
	exports.RecentPullHandler = RecentPullHandler;

}((this.BX.Messenger.Embedding.Provider.Pull = this.BX.Messenger.Embedding.Provider.Pull || {}),BX,BX,BX.Messenger.Embedding.Application,BX.Messenger.Embedding.Lib,BX.Messenger.Embedding.Lib,BX.Messenger.Embedding.Const));
//# sourceMappingURL=registry.bundle.js.map
