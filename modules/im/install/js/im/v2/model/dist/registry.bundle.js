this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_lib_logger,ui_reactionsSelect,im_v2_application_core,im_v2_lib_utils,main_core,ui_vue3_vuex,im_v2_const) {
	'use strict';

	class SettingsModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      [im_v2_const.Settings.application.darkTheme]: false,
	      [im_v2_const.Settings.application.enableSound]: true,
	      [im_v2_const.Settings.dialog.bigSmiles]: true,
	      [im_v2_const.Settings.dialog.background]: 1,
	      [im_v2_const.Settings.recent.showBirthday]: true,
	      [im_v2_const.Settings.recent.showInvited]: true,
	      [im_v2_const.Settings.recent.showLastMessage]: true
	    };
	  }
	  getGetters() {
	    return {
	      get: state => key => {
	        return state[key];
	      }
	    };
	  }
	  getActions() {
	    return {
	      set: (store, payload) => {
	        store.commit('set', this.validate(payload));
	      }
	    };
	  }
	  getMutations() {
	    return {
	      set: (state, payload) => {
	        Object.entries(payload).forEach(([key, value]) => {
	          state[key] = value;
	        });
	      }
	    };
	  }
	  validate(fields) {
	    const result = {};
	    if (main_core.Type.isBoolean(fields[im_v2_const.Settings.application.darkTheme])) {
	      result[im_v2_const.Settings.application.darkTheme] = fields[im_v2_const.Settings.application.darkTheme];
	    }
	    if (main_core.Type.isBoolean(fields[im_v2_const.Settings.application.enableSound])) {
	      result[im_v2_const.Settings.application.enableSound] = fields[im_v2_const.Settings.application.enableSound];
	    }
	    if (main_core.Type.isBoolean(fields[im_v2_const.Settings.dialog.bigSmiles])) {
	      result[im_v2_const.Settings.dialog.bigSmiles] = fields[im_v2_const.Settings.dialog.bigSmiles];
	    }
	    if (main_core.Type.isStringFilled(fields[im_v2_const.Settings.dialog.background])) {
	      fields[im_v2_const.Settings.dialog.background] = Number.parseInt(fields[im_v2_const.Settings.dialog.background], 10);
	    }
	    if (main_core.Type.isNumber(fields[im_v2_const.Settings.dialog.background])) {
	      result[im_v2_const.Settings.dialog.background] = fields[im_v2_const.Settings.dialog.background];
	    }
	    if (main_core.Type.isBoolean(fields[im_v2_const.Settings.recent.showBirthday])) {
	      result[im_v2_const.Settings.recent.showBirthday] = fields[im_v2_const.Settings.recent.showBirthday];
	    }
	    if (main_core.Type.isBoolean(fields[im_v2_const.Settings.recent.showInvited])) {
	      result[im_v2_const.Settings.recent.showInvited] = fields[im_v2_const.Settings.recent.showInvited];
	    }
	    if (main_core.Type.isBoolean(fields[im_v2_const.Settings.recent.showLastMessage])) {
	      result[im_v2_const.Settings.recent.showLastMessage] = fields[im_v2_const.Settings.recent.showLastMessage];
	    }
	    return result;
	  }
	}

	class ApplicationModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'application';
	  }
	  getNestedModules() {
	    return {
	      settings: SettingsModel
	    };
	  }
	  getState() {
	    return {
	      layout: {
	        name: im_v2_const.Layout.chat.name,
	        entityId: '',
	        contextId: 0
	      }
	    };
	  }
	  getGetters() {
	    return {
	      getLayout: state => {
	        return state.layout;
	      },
	      isChatOpen: state => dialogId => {
	        if (!state.layout.name === im_v2_const.Layout.chat.name) {
	          return false;
	        }
	        return state.layout.entityId === dialogId.toString();
	      },
	      areNotificationsOpen: state => {
	        return state.layout.name === im_v2_const.Layout.notification.name;
	      }
	    };
	  }
	  getActions() {
	    return {
	      setLayout: (store, payload) => {
	        const {
	          layoutName,
	          entityId = '',
	          contextId = 0
	        } = payload;
	        if (!main_core.Type.isStringFilled(layoutName)) {
	          return false;
	        }
	        const previousLayout = {
	          ...store.state.layout
	        };
	        const newLayout = {
	          name: this.validateLayout(layoutName),
	          entityId: this.validateLayoutEntityId(layoutName, entityId),
	          contextId: contextId
	        };
	        store.commit('update', {
	          layout: newLayout
	        });
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.layout.onLayoutChange, {
	          from: previousLayout,
	          to: newLayout
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      update: (state, payload) => {
	        Object.keys(payload).forEach(group => {
	          Object.entries(payload[group]).forEach(([key, value]) => {
	            state[group][key] = value;
	          });
	        });
	      }
	    };
	  }
	  validateLayout(layoutName) {
	    if (!im_v2_const.Layout[layoutName]) {
	      return im_v2_const.Layout.chat.name;
	    }
	    return layoutName;
	  }
	  validateLayoutEntityId(layoutName, entityId) {
	    if (!im_v2_const.Layout[layoutName]) {
	      return '';
	    }

	    // TODO check `entityId` by layout name

	    return entityId;
	  }
	}

	class PinModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getGetters() {
	    return {
	      getPinned: state => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        return [...state.collection[chatId]].map(pinnedMessageId => {
	          return im_v2_application_core.Core.getStore().getters['messages/getById'](pinnedMessageId);
	        });
	      },
	      isPinned: state => payload => {
	        const {
	          chatId,
	          messageId
	        } = payload;
	        if (!state.collection[chatId]) {
	          return false;
	        }
	        return state.collection[chatId].has(messageId);
	      }
	    };
	  }
	  getActions() {
	    return {
	      setPinned: (store, payload) => {
	        const {
	          chatId,
	          pinnedMessages
	        } = payload;
	        if (pinnedMessages.length === 0) {
	          return;
	        }
	        store.commit('setPinned', {
	          chatId,
	          pinnedMessageIds: pinnedMessages
	        });
	      },
	      set: (store, payload) => {
	        store.commit('set', payload);
	      },
	      add: (store, payload) => {
	        store.commit('add', payload);
	      },
	      delete: (store, payload) => {
	        store.commit('delete', payload);
	      }
	    };
	  }
	  getMutations() {
	    return {
	      setPinned: (state, payload) => {
	        im_v2_lib_logger.Logger.warn('Messages/pin model: setPinned mutation', payload);
	        const {
	          chatId,
	          pinnedMessageIds
	        } = payload;
	        state.collection[chatId] = new Set(pinnedMessageIds.reverse());
	      },
	      add: (state, payload) => {
	        im_v2_lib_logger.Logger.warn('Messages/pin model: add pin mutation', payload);
	        const {
	          chatId,
	          messageId
	        } = payload;
	        if (!state.collection[chatId]) {
	          state.collection[chatId] = new Set();
	        }
	        state.collection[chatId].add(messageId);
	      },
	      delete: (state, payload) => {
	        im_v2_lib_logger.Logger.warn('Messages/pin model: delete pin mutation', payload);
	        const {
	          chatId,
	          messageId
	        } = payload;
	        if (!state.collection[chatId]) {
	          return;
	        }
	        state.collection[chatId].delete(messageId);
	      }
	    };
	  }
	}

	const USERS_TO_SHOW = 5;
	class ReactionsModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getElementState() {
	    return {
	      reactionCounters: {},
	      reactionUsers: {},
	      ownReactions: new Set()
	    };
	  }
	  getGetters() {
	    return {
	      getByMessageId: state => messageId => {
	        return state.collection[messageId];
	      }
	    };
	  }
	  getActions() {
	    return {
	      set: (store, payload) => {
	        store.commit('set', this.prepareSetPayload(payload));
	      },
	      setReaction: (store, payload) => {
	        if (!ui_reactionsSelect.reactionType[payload.reaction]) {
	          return;
	        }
	        if (!store.state.collection[payload.messageId]) {
	          store.state.collection[payload.messageId] = this.getElementState();
	        }
	        store.commit('setReaction', payload);
	      },
	      removeReaction: (store, payload) => {
	        if (!store.state.collection[payload.messageId] || !ui_reactionsSelect.reactionType[payload.reaction]) {
	          return;
	        }
	        store.commit('removeReaction', payload);
	      }
	    };
	  }
	  getMutations() {
	    return {
	      set: (state, payload) => {
	        payload.forEach(item => {
	          const newItem = {
	            reactionCounters: item.reactionCounters,
	            reactionUsers: item.reactionUsers
	          };
	          const currentItem = state.collection[item.messageId];
	          const newOwnReaction = !!item.ownReactions;
	          if (newOwnReaction) {
	            newItem.ownReactions = item.ownReactions;
	          } else {
	            newItem.ownReactions = currentItem ? currentItem.ownReactions : new Set();
	          }
	          state.collection[item.messageId] = newItem;
	        });
	      },
	      setReaction: (state, payload) => {
	        const {
	          messageId,
	          userId,
	          reaction
	        } = payload;
	        const reactions = state.collection[messageId];
	        if (im_v2_application_core.Core.getUserId() === userId) {
	          this.removeAllCurrentUserReactions(reactions);
	          reactions.ownReactions.add(reaction);
	        }
	        if (!reactions.reactionCounters[reaction]) {
	          reactions.reactionCounters[reaction] = 0;
	        }
	        const currentCounter = reactions.reactionCounters[reaction];
	        if (currentCounter + 1 <= USERS_TO_SHOW) {
	          if (!reactions.reactionUsers[reaction]) {
	            reactions.reactionUsers[reaction] = new Set();
	          }
	          reactions.reactionUsers[reaction].add(userId);
	        }
	        reactions.reactionCounters[reaction]++;
	      },
	      removeReaction: (state, payload) => {
	        var _reactions$reactionUs;
	        const {
	          messageId,
	          userId,
	          reaction
	        } = payload;
	        const reactions = state.collection[messageId];
	        if (im_v2_application_core.Core.getUserId() === userId) {
	          reactions.ownReactions.delete(reaction);
	        }
	        (_reactions$reactionUs = reactions.reactionUsers[reaction]) == null ? void 0 : _reactions$reactionUs.delete(userId);
	        reactions.reactionCounters[reaction]--;
	        if (reactions.reactionCounters[reaction] === 0) {
	          delete reactions.reactionCounters[reaction];
	        }
	      }
	    };
	  }
	  removeAllCurrentUserReactions(reactions) {
	    reactions.ownReactions.forEach(reaction => {
	      var _reactions$reactionUs2;
	      (_reactions$reactionUs2 = reactions.reactionUsers[reaction]) == null ? void 0 : _reactions$reactionUs2.delete(im_v2_application_core.Core.getUserId());
	      reactions.reactionCounters[reaction]--;
	      if (reactions.reactionCounters[reaction] === 0) {
	        delete reactions.reactionCounters[reaction];
	      }
	    });
	    reactions.ownReactions = new Set();
	  }
	  prepareSetPayload(payload) {
	    return payload.map(item => {
	      var _item$ownReactions;
	      const reactionUsers = {};
	      Object.entries(item.reactionUsers).forEach(([reaction, users]) => {
	        reactionUsers[reaction] = new Set(users);
	      });
	      const reactionCounters = {};
	      Object.entries(item.reactionCounters).forEach(([reaction, counter]) => {
	        reactionCounters[reaction] = counter;
	      });
	      const result = {
	        messageId: item.messageId,
	        reactionCounters: reactionCounters,
	        reactionUsers: reactionUsers
	      };
	      if (((_item$ownReactions = item.ownReactions) == null ? void 0 : _item$ownReactions.length) > 0) {
	        result.ownReactions = new Set(item.ownReactions);
	      }
	      return result;
	    });
	  }
	}

	var _getMaxMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMaxMessageId");
	var _findLowestMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("findLowestMessageId");
	var _findMaxMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("findMaxMessageId");
	var _findLastOwnMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("findLastOwnMessageId");
	var _findFirstUnread = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("findFirstUnread");
	class MessagesModel extends ui_vue3_vuex.BuilderModel {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _findFirstUnread, {
	      value: _findFirstUnread2
	    });
	    Object.defineProperty(this, _findLastOwnMessageId, {
	      value: _findLastOwnMessageId2
	    });
	    Object.defineProperty(this, _findMaxMessageId, {
	      value: _findMaxMessageId2
	    });
	    Object.defineProperty(this, _findLowestMessageId, {
	      value: _findLowestMessageId2
	    });
	    Object.defineProperty(this, _getMaxMessageId, {
	      value: _getMaxMessageId2
	    });
	  }
	  getName() {
	    return 'messages';
	  }
	  getNestedModules() {
	    return {
	      pin: PinModel,
	      reactions: ReactionsModel
	    };
	  }
	  getState() {
	    return {
	      collection: {},
	      chatCollection: {}
	    };
	  }
	  getElementState() {
	    return {
	      id: 0,
	      chatId: 0,
	      authorId: 0,
	      date: new Date(),
	      text: '',
	      replaces: [],
	      files: [],
	      attach: [],
	      unread: false,
	      viewed: true,
	      viewedByOthers: false,
	      sending: false,
	      error: false,
	      retry: false,
	      componentId: im_v2_const.MessageComponent.base,
	      isEdited: false,
	      isDeleted: false,
	      removeLinks: false
	    };
	  }
	  getGetters() {
	    return {
	      get: state => chatId => {
	        if (!state.chatCollection[chatId]) {
	          return [];
	        }
	        return [...state.chatCollection[chatId]].map(messageId => {
	          return state.collection[messageId];
	        }).sort((a, b) => {
	          return a.id - b.id;
	        });
	      },
	      getById: state => id => {
	        return state.collection[id];
	      },
	      getByIdList: state => idList => {
	        const result = [];
	        idList.forEach(id => {
	          if (state.collection[id]) {
	            result.push(state.collection[id]);
	          }
	        });
	        return result;
	      },
	      hasMessage: state => ({
	        chatId,
	        messageId
	      }) => {
	        if (!state.chatCollection[chatId]) {
	          return false;
	        }
	        return state.chatCollection[chatId].has(messageId);
	      },
	      isInChatCollection: state => payload => {
	        var _state$chatCollection;
	        const {
	          messageId
	        } = payload;
	        const message = state.collection[messageId];
	        if (!message) {
	          return false;
	        }
	        const {
	          chatId
	        } = message;
	        return (_state$chatCollection = state.chatCollection[chatId]) == null ? void 0 : _state$chatCollection.has(messageId);
	      },
	      getFirstId: state => chatId => {
	        if (!state.chatCollection[chatId]) {
	          return;
	        }
	        return babelHelpers.classPrivateFieldLooseBase(this, _findLowestMessageId)[_findLowestMessageId](state, chatId);
	      },
	      getLastId: state => chatId => {
	        if (!state.chatCollection[chatId]) {
	          return;
	        }
	        return babelHelpers.classPrivateFieldLooseBase(this, _findMaxMessageId)[_findMaxMessageId](state, chatId);
	      },
	      getLastOwnMessageId: state => chatId => {
	        if (!state.chatCollection[chatId]) {
	          return 0;
	        }
	        return babelHelpers.classPrivateFieldLooseBase(this, _findLastOwnMessageId)[_findLastOwnMessageId](state, chatId);
	      },
	      getFirstUnread: state => chatId => {
	        if (!state.chatCollection[chatId]) {
	          return 0;
	        }
	        return babelHelpers.classPrivateFieldLooseBase(this, _findFirstUnread)[_findFirstUnread](state, chatId);
	      },
	      getChatUnreadMessages: state => chatId => {
	        if (!state.chatCollection[chatId]) {
	          return [];
	        }
	        const messages = [...state.chatCollection[chatId]].map(messageId => {
	          return state.collection[messageId];
	        });
	        return messages.filter(message => {
	          return message.unread === true;
	        });
	      },
	      getMessageFiles: state => payload => {
	        const messageId = payload;
	        if (!state.collection[messageId]) {
	          return [];
	        }
	        return state.collection[messageId].files.map(fileId => {
	          return this.store.getters['files/get'](fileId, true);
	        });
	      },
	      getMessageType: state => payload => {
	        const message = state.collection[payload];
	        if (!message) {
	          return;
	        }
	        const currentUserId = im_v2_application_core.Core.getUserId();
	        if (message.authorId === 0) {
	          return im_v2_const.MessageType.system;
	        } else if (message.authorId === currentUserId) {
	          return im_v2_const.MessageType.self;
	        }
	        return im_v2_const.MessageType.opponent;
	      }
	    };
	  }
	  getActions() {
	    return {
	      setChatCollection: (store, payload) => {
	        var _clearCollection, _messages$;
	        let {
	          messages,
	          clearCollection
	        } = payload;
	        clearCollection = (_clearCollection = clearCollection) != null ? _clearCollection : false;
	        if (!Array.isArray(messages) && main_core.Type.isPlainObject(messages)) {
	          messages = [messages];
	        }
	        messages = messages.map(message => {
	          return {
	            ...this.getElementState(),
	            ...this.validate(message)
	          };
	        });
	        const chatId = (_messages$ = messages[0]) == null ? void 0 : _messages$.chatId;
	        if (chatId && clearCollection) {
	          store.commit('clearCollection', {
	            chatId
	          });
	        }
	        store.commit('store', {
	          messages
	        });
	        store.commit('setChatCollection', {
	          messages
	        });
	      },
	      store: (store, payload) => {
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        payload = payload.map(message => {
	          return {
	            ...this.getElementState(),
	            ...this.validate(message)
	          };
	        });
	        if (payload.length === 0) {
	          return;
	        }
	        store.commit('store', {
	          messages: payload
	        });
	      },
	      add: (store, payload) => {
	        const message = {
	          ...this.getElementState(),
	          ...this.validate(payload)
	        };
	        store.commit('store', {
	          messages: [message]
	        });
	        store.commit('setChatCollection', {
	          messages: [message]
	        });
	        return message.id;
	      },
	      updateWithId: (store, payload) => {
	        const {
	          id,
	          fields
	        } = payload;
	        if (!store.state.collection[id]) {
	          return;
	        }
	        store.commit('updateWithId', {
	          id,
	          fields: this.validate(fields)
	        });
	      },
	      update: (store, payload) => {
	        const {
	          id,
	          fields
	        } = payload;
	        const currentMessage = store.state.collection[id];
	        if (!currentMessage) {
	          return;
	        }
	        store.commit('update', {
	          id,
	          fields: {
	            ...currentMessage,
	            ...this.validate(fields)
	          }
	        });
	      },
	      readMessages: (store, payload) => {
	        const {
	          chatId,
	          messageIds
	        } = payload;
	        if (!store.state.chatCollection[chatId]) {
	          return 0;
	        }
	        const chatMessages = [...store.state.chatCollection[chatId]].map(messageId => {
	          return store.state.collection[messageId];
	        });
	        let messagesToReadCount = 0;
	        const maxMessageId = babelHelpers.classPrivateFieldLooseBase(this, _getMaxMessageId)[_getMaxMessageId](messageIds);
	        const messageIdsToView = messageIds;
	        const messageIdsToRead = [];
	        chatMessages.forEach(chatMessage => {
	          if (!chatMessage.unread) {
	            return;
	          }
	          if (chatMessage.id <= maxMessageId) {
	            messagesToReadCount++;
	            messageIdsToRead.push(chatMessage.id);
	          }
	        });
	        store.commit('readMessages', {
	          messageIdsToRead,
	          messageIdsToView
	        });
	        return messagesToReadCount;
	      },
	      setViewedByOthers: (store, payload) => {
	        const {
	          ids
	        } = payload;
	        store.commit('setViewedByOthers', {
	          ids
	        });
	      },
	      delete: (store, payload) => {
	        const {
	          id
	        } = payload;
	        if (!store.state.collection[id]) {
	          return;
	        }
	        store.commit('delete', {
	          id
	        });
	      },
	      clearChatCollection: (store, payload) => {
	        const {
	          chatId
	        } = payload;
	        store.commit('clearCollection', {
	          chatId
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      setChatCollection: (state, payload) => {
	        im_v2_lib_logger.Logger.warn('Messages model: setChatCollection mutation', payload);
	        payload.messages.forEach(message => {
	          if (!state.chatCollection[message.chatId]) {
	            state.chatCollection[message.chatId] = new Set();
	          }
	          state.chatCollection[message.chatId].add(message.id);
	        });
	      },
	      store: (state, payload) => {
	        im_v2_lib_logger.Logger.warn('Messages model: store mutation', payload);
	        payload.messages.forEach(message => {
	          state.collection[message.id] = message;
	        });
	      },
	      updateWithId: (state, payload) => {
	        im_v2_lib_logger.Logger.warn('Messages model: updateWithId mutation', payload);
	        const {
	          id,
	          fields
	        } = payload;
	        const currentMessage = {
	          ...state.collection[id]
	        };
	        delete state.collection[id];
	        state.collection[fields.id] = {
	          ...currentMessage,
	          ...fields,
	          sending: false
	        };
	        if (state.chatCollection[currentMessage.chatId].has(id)) {
	          state.chatCollection[currentMessage.chatId].delete(id);
	          state.chatCollection[currentMessage.chatId].add(fields.id);
	        }
	      },
	      update: (state, payload) => {
	        im_v2_lib_logger.Logger.warn('Messages model: update mutation', payload);
	        const {
	          id,
	          fields
	        } = payload;
	        state.collection[id] = {
	          ...state.collection[id],
	          ...fields
	        };
	      },
	      delete: (state, payload) => {
	        im_v2_lib_logger.Logger.warn('Messages model: delete mutation', payload);
	        const {
	          id
	        } = payload;
	        const {
	          chatId
	        } = state.collection[id];
	        state.chatCollection[chatId].delete(id);
	        delete state.collection[id];
	      },
	      clearCollection: (state, payload) => {
	        im_v2_lib_logger.Logger.warn('Messages model: clear collection mutation', payload.chatId);
	        state.chatCollection[payload.chatId] = new Set();
	      },
	      readMessages: (state, payload) => {
	        const {
	          messageIdsToRead,
	          messageIdsToView
	        } = payload;
	        messageIdsToRead.forEach(messageId => {
	          const message = state.collection[messageId];
	          if (!message) {
	            return;
	          }
	          message.unread = false;
	        });
	        messageIdsToView.forEach(messageId => {
	          const message = state.collection[messageId];
	          if (!message) {
	            return;
	          }
	          message.viewed = true;
	        });
	      },
	      setViewedByOthers: (state, payload) => {
	        const {
	          ids
	        } = payload;
	        ids.forEach(id => {
	          const message = state.collection[id];
	          if (!message) {
	            return;
	          }
	          const isOwnMessage = message.authorId === im_v2_application_core.Core.getUserId();
	          if (!isOwnMessage || message.viewedByOthers) {
	            return;
	          }
	          message.viewedByOthers = true;
	        });
	      }
	    };
	  }
	  validate(fields) {
	    let result = {};
	    if (main_core.Type.isNumber(fields.id)) {
	      result.id = fields.id;
	    } else if (im_v2_lib_utils.Utils.text.isUuidV4(fields.temporaryId)) {
	      result.id = fields.temporaryId;
	    }
	    if (!main_core.Type.isUndefined(fields.chat_id)) {
	      fields.chatId = fields.chat_id;
	    }
	    if (main_core.Type.isNumber(fields.chatId) || main_core.Type.isStringFilled(fields.chatId)) {
	      result.chatId = Number.parseInt(fields.chatId, 10);
	    }
	    if (main_core.Type.isStringFilled(fields.date)) {
	      result.date = im_v2_lib_utils.Utils.date.cast(fields.date);
	    }
	    if (main_core.Type.isNumber(fields.text) || main_core.Type.isString(fields.text)) {
	      result.text = fields.text.toString();
	    }
	    if (main_core.Type.isStringFilled(fields.system)) {
	      fields.isSystem = fields.system === 'Y';
	    }
	    if (!main_core.Type.isUndefined(fields.senderId)) {
	      fields.authorId = fields.senderId;
	    } else if (!main_core.Type.isUndefined(fields.author_id)) {
	      fields.authorId = fields.author_id;
	    }
	    if (main_core.Type.isNumber(fields.authorId) || main_core.Type.isStringFilled(fields.authorId)) {
	      result.authorId = Number.parseInt(fields.authorId, 10);
	    }
	    if (fields.isSystem === true) {
	      result.authorId = 0;
	    }
	    if (main_core.Type.isArray(fields.replaces)) {
	      result.replaces = fields.replaces;
	    }
	    if (main_core.Type.isBoolean(fields.sending)) {
	      result.sending = fields.sending;
	    }
	    if (main_core.Type.isBoolean(fields.unread)) {
	      result.unread = fields.unread;
	    }
	    if (main_core.Type.isBoolean(fields.viewed)) {
	      result.viewed = fields.viewed;
	    }
	    if (main_core.Type.isBoolean(fields.viewedByOthers)) {
	      result.viewedByOthers = fields.viewedByOthers;
	    }
	    if (main_core.Type.isBoolean(fields.error)) {
	      result.error = fields.error;
	    }
	    if (main_core.Type.isBoolean(fields.retry)) {
	      result.retry = fields.retry;
	    }
	    if (main_core.Type.isString(fields.componentId)) {
	      result.componentId = fields.componentId;
	    }
	    if (main_core.Type.isArray(fields.files)) {
	      result.files = fields.files;
	    }
	    if (main_core.Type.isArray(fields.attach)) {
	      result.attach = fields.attach;
	    }
	    if (main_core.Type.isBoolean(fields.isEdited)) {
	      result.isEdited = fields.isEdited;
	    }
	    if (main_core.Type.isBoolean(fields.isDeleted)) {
	      result.isDeleted = fields.isDeleted;
	    }
	    if (main_core.Type.isBoolean(fields.removeLinks)) {
	      result.removeLinks = fields.removeLinks;
	    }
	    if (main_core.Type.isPlainObject(fields.params)) {
	      const preparedParams = this.prepareParams(fields.params);
	      result = {
	        ...result,
	        ...preparedParams
	      };
	    }
	    return result;
	  }
	  prepareParams(rawParams) {
	    const result = {};
	    Object.entries(rawParams).forEach(([key, value]) => {
	      if (key === 'COMPONENT_ID' && main_core.Type.isStringFilled(value)) {
	        result.componentId = value;
	      } else if (key === 'FILE_ID' && main_core.Type.isArray(value)) {
	        result.files = value;
	      } else if (key === 'IS_EDITED' && main_core.Type.isStringFilled(value)) {
	        result.isEdited = value === 'Y';
	      } else if (key === 'IS_DELETED' && main_core.Type.isStringFilled(value)) {
	        result.isDeleted = value === 'Y';
	      } else if (key === 'ATTACH' && (main_core.Type.isArray(value) || main_core.Type.isBoolean(value) || main_core.Type.isString(value))) {
	        result.attach = value;
	      } else if (key === 'LINK_ACTIVE' && main_core.Type.isArrayFilled(value)) {
	        result.removeLinks = value.includes(im_v2_application_core.Core.getUserId());
	      }
	    });
	    return result;
	  }
	}
	function _getMaxMessageId2(messageIds) {
	  let maxMessageId = 0;
	  messageIds.forEach(messageId => {
	    if (maxMessageId < messageId) {
	      maxMessageId = messageId;
	    }
	  });
	  return maxMessageId;
	}
	function _findLowestMessageId2(state, chatId) {
	  let firstId = null;
	  const messages = [...state.chatCollection[chatId]];
	  for (const messageId of messages) {
	    const element = state.collection[messageId];
	    if (!firstId) {
	      firstId = element.id;
	    }
	    if (im_v2_lib_utils.Utils.text.isTempMessage(element.id)) {
	      continue;
	    }
	    if (element.id < firstId) {
	      firstId = element.id;
	    }
	  }
	  return firstId;
	}
	function _findMaxMessageId2(state, chatId) {
	  let lastId = 0;
	  const messages = [...state.chatCollection[chatId]];
	  for (const messageId of messages) {
	    const element = state.collection[messageId];
	    if (im_v2_lib_utils.Utils.text.isTempMessage(element.id)) {
	      continue;
	    }
	    if (element.id > lastId) {
	      lastId = element.id;
	    }
	  }
	  return lastId;
	}
	function _findLastOwnMessageId2(state, chatId) {
	  let lastOwnMessageId = 0;
	  const messages = [...state.chatCollection[chatId]].sort((a, z) => z - a);
	  for (const messageId of messages) {
	    const element = state.collection[messageId];
	    if (im_v2_lib_utils.Utils.text.isTempMessage(element.id)) {
	      continue;
	    }
	    if (element.authorId === im_v2_application_core.Core.getUserId()) {
	      lastOwnMessageId = element.id;
	      break;
	    }
	  }
	  return lastOwnMessageId;
	}
	function _findFirstUnread2(state, chatId) {
	  let resultId = 0;
	  for (const messageId of state.chatCollection[chatId]) {
	    const message = state.collection[messageId];
	    if (message.unread) {
	      resultId = messageId;
	      break;
	    }
	  }
	  return resultId;
	}

	const WRITING_STATUS_TIME = 35000;
	class DialoguesModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'dialogues';
	  }
	  getState() {
	    return {
	      collection: {},
	      writingStatusTimers: {},
	      chatOptions: {}
	    };
	  }
	  getElementState() {
	    return {
	      dialogId: '0',
	      chatId: 0,
	      type: im_v2_const.DialogType.chat,
	      name: '',
	      description: '',
	      avatar: '',
	      color: im_v2_const.Color.base,
	      extranet: false,
	      counter: 0,
	      userCounter: 0,
	      lastReadId: 0,
	      markedId: 0,
	      lastMessageId: 0,
	      lastMessageViews: {
	        countOfViewers: 0,
	        firstViewer: null,
	        messageId: 0
	      },
	      savedPositionMessageId: 0,
	      managerList: [],
	      writingList: [],
	      muteList: [],
	      textareaMessage: '',
	      quoteId: 0,
	      owner: 0,
	      entityType: '',
	      entityId: '',
	      dateCreate: null,
	      public: {
	        code: '',
	        link: ''
	      },
	      inited: false,
	      loading: false,
	      hasPrevPage: false,
	      hasNextPage: false,
	      diskFolderId: 0
	    };
	  }
	  getGetters() {
	    return {
	      get: state => (dialogId, getBlank = false) => {
	        if (!state.collection[dialogId] && getBlank) {
	          return this.getElementState();
	        } else if (!state.collection[dialogId] && !getBlank) {
	          return null;
	        }
	        return state.collection[dialogId];
	      },
	      getByChatId: state => chatId => {
	        chatId = Number.parseInt(chatId, 10);
	        return Object.values(state.collection).find(item => {
	          return item.chatId === chatId;
	        });
	      },
	      getBlank: () => {
	        return this.getElementState();
	      },
	      getChatOption: state => (chatType, option) => {
	        if (!state.chatOptions[chatType]) {
	          chatType = 'default';
	        }
	        return state.chatOptions[chatType][option];
	      },
	      getQuoteId: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return 0;
	        }
	        return state.collection[dialogId].quoteId;
	      },
	      isUser: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return false;
	        }
	        return state.collection[dialogId].type === im_v2_const.DialogType.user;
	      },
	      canLeave: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return false;
	        }
	        const dialog = state.collection[dialogId];
	        const isExternalTelephonyCall = dialog.type === im_v2_const.DialogType.call;
	        const isUser = dialog.type === im_v2_const.DialogType.user;
	        if (isExternalTelephonyCall || isUser) {
	          return false;
	        }
	        const currentUserId = im_v2_application_core.Core.getUserId();
	        const optionToCheck = dialog.owner === currentUserId ? im_v2_const.ChatOption.leaveOwner : im_v2_const.ChatOption.leave;
	        return this.store.getters['dialogues/getChatOption'](dialog.type, optionToCheck);
	      },
	      canMute: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return false;
	        }
	        const dialog = state.collection[dialogId];
	        const isUser = dialog.type === im_v2_const.DialogType.user;
	        const isAnnouncement = dialog.type === im_v2_const.DialogType.announcement;
	        if (isUser || isAnnouncement) {
	          return null;
	        }
	        return this.store.getters['dialogues/getChatOption'](dialog.type, im_v2_const.ChatOption.mute);
	      },
	      getLastReadId: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return 0;
	        }
	        const {
	          lastReadId,
	          lastMessageId
	        } = state.collection[dialogId];
	        return lastReadId === lastMessageId ? 0 : lastReadId;
	      },
	      getInitialMessageId: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return 0;
	        }
	        const {
	          lastReadId,
	          markedId
	        } = state.collection[dialogId];
	        if (markedId === 0) {
	          return lastReadId;
	        }
	        return Math.min(lastReadId, markedId);
	      }
	    };
	  }
	  getActions() {
	    return {
	      set: (store, payload) => {
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        payload.map(element => {
	          return this.validate(element);
	        }).forEach(element => {
	          const existingItem = store.state.collection[element.dialogId];
	          if (existingItem) {
	            store.commit('update', {
	              dialogId: element.dialogId,
	              fields: element
	            });
	          } else {
	            store.commit('add', {
	              dialogId: element.dialogId,
	              fields: {
	                ...this.getElementState(),
	                ...element
	              }
	            });
	          }
	        });
	      },
	      add: (store, payload) => {
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        payload.map(element => {
	          return this.validate(element);
	        }).forEach(element => {
	          const existingItem = store.state.collection[element.dialogId];
	          if (!existingItem) {
	            store.commit('add', {
	              dialogId: element.dialogId,
	              fields: {
	                ...this.getElementState(),
	                ...element
	              }
	            });
	          }
	        });
	      },
	      update: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return false;
	        }
	        store.commit('update', {
	          dialogId: payload.dialogId,
	          fields: this.validate(payload.fields)
	        });
	      },
	      delete: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return false;
	        }
	        store.commit('delete', {
	          dialogId: payload.dialogId
	        });
	      },
	      startWriting: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return false;
	        }
	        const timerId = `${payload.dialogId}|${payload.userId}`;
	        const alreadyWriting = existingItem.writingList.some(el => el.userId === payload.userId);
	        if (alreadyWriting) {
	          clearTimeout(store.state.writingStatusTimers[timerId]);
	          store.state.writingStatusTimers[timerId] = this.setWritingStatusTimeout(payload);
	          return true;
	        }
	        const newItem = {
	          userId: payload.userId,
	          userName: payload.userName
	        };
	        const newWritingList = [newItem, ...existingItem.writingList];
	        store.commit('update', {
	          actionName: 'startWriting',
	          dialogId: payload.dialogId,
	          fields: this.validate({
	            writingList: newWritingList
	          })
	        });
	        if (!store.state.writingStatusTimers[timerId]) {
	          store.state.writingStatusTimers[timerId] = this.setWritingStatusTimeout(payload);
	        }
	      },
	      stopWriting: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return false;
	        }
	        const alreadyWriting = existingItem.writingList.find(el => el.userId === payload.userId);
	        if (!alreadyWriting) {
	          return false;
	        }
	        const newWritingList = existingItem.writingList.filter(item => item.userId !== payload.userId);
	        store.commit('update', {
	          actionName: 'stopWriting',
	          dialogId: payload.dialogId,
	          fields: this.validate({
	            writingList: newWritingList
	          })
	        });
	        const timerId = `${payload.dialogId}|${payload.userId}`;
	        clearTimeout(store.state.writingStatusTimers[timerId]);
	        delete store.state.writingStatusTimers[timerId];
	      },
	      increaseCounter: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return false;
	        }
	        if (existingItem.counter === 100) {
	          return true;
	        }
	        let increasedCounter = existingItem.counter + payload.count;
	        if (increasedCounter > 100) {
	          increasedCounter = 100;
	        }
	        store.commit('update', {
	          actionName: 'increaseCounter',
	          dialogId: payload.dialogId,
	          fields: {
	            counter: increasedCounter,
	            previousCounter: existingItem.counter
	          }
	        });
	      },
	      decreaseCounter: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return false;
	        }
	        if (existingItem.counter === 100) {
	          return true;
	        }
	        let decreasedCounter = existingItem.counter - payload.count;
	        if (decreasedCounter < 0) {
	          decreasedCounter = 0;
	        }
	        store.commit('update', {
	          actionName: 'decreaseCounter',
	          dialogId: payload.dialogId,
	          fields: {
	            counter: decreasedCounter,
	            previousCounter: existingItem.counter
	          }
	        });
	      },
	      clearCounters: store => {
	        store.commit('clearCounters');
	      },
	      mute: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return false;
	        }
	        const currentUserId = im_v2_application_core.Core.getUserId();
	        if (existingItem.muteList.includes(currentUserId)) {
	          return false;
	        }
	        const muteList = [...existingItem.muteList, currentUserId];
	        store.commit('update', {
	          actionName: 'mute',
	          dialogId: payload.dialogId,
	          fields: this.validate({
	            muteList
	          })
	        });
	      },
	      unmute: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return false;
	        }
	        const currentUserId = im_v2_application_core.Core.getUserId();
	        const muteList = existingItem.muteList.filter(item => item !== currentUserId);
	        store.commit('update', {
	          actionName: 'unmute',
	          dialogId: payload.dialogId,
	          fields: this.validate({
	            muteList
	          })
	        });
	      },
	      setChatOptions: (store, payload) => {
	        store.commit('setChatOptions', this.validateChatOptions(payload));
	      },
	      setLastMessageViews: (store, payload) => {
	        const {
	          dialogId,
	          fields: {
	            userId,
	            userName,
	            date,
	            messageId
	          }
	        } = payload;
	        const existingItem = store.state.collection[dialogId];
	        if (!existingItem) {
	          return false;
	        }
	        const newLastMessageViews = {
	          countOfViewers: 1,
	          messageId,
	          firstViewer: {
	            userId,
	            userName,
	            date: im_v2_lib_utils.Utils.date.cast(date)
	          }
	        };
	        store.commit('update', {
	          actionName: 'setLastMessageViews',
	          dialogId: dialogId,
	          fields: {
	            lastMessageViews: newLastMessageViews
	          }
	        });
	      },
	      clearLastMessageViews: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return false;
	        }
	        const {
	          lastMessageViews: defaultLastMessageViews
	        } = this.getElementState();
	        store.commit('update', {
	          actionName: 'clearLastMessageViews',
	          dialogId: payload.dialogId,
	          fields: {
	            lastMessageViews: defaultLastMessageViews
	          }
	        });
	      },
	      incrementLastMessageViews: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return false;
	        }
	        const newCounter = existingItem.lastMessageViews.countOfViewers + 1;
	        store.commit('update', {
	          actionName: 'incrementLastMessageViews',
	          dialogId: payload.dialogId,
	          fields: {
	            lastMessageViews: {
	              ...existingItem.lastMessageViews,
	              countOfViewers: newCounter
	            }
	          }
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      add: (state, payload) => {
	        state.collection[payload.dialogId] = payload.fields;
	      },
	      update: (state, payload) => {
	        state.collection[payload.dialogId] = {
	          ...state.collection[payload.dialogId],
	          ...payload.fields
	        };
	      },
	      delete: (state, payload) => {
	        delete state.collection[payload.dialogId];
	      },
	      setChatOptions: (state, payload) => {
	        state.chatOptions = payload;
	      },
	      clearCounters: state => {
	        Object.keys(state.collection).forEach(key => {
	          state.collection[key].counter = 0;
	          state.collection[key].markedId = 0;
	        });
	      }
	    };
	  }
	  setWritingStatusTimeout(payload) {
	    return setTimeout(() => {
	      this.store.dispatch('dialogues/stopWriting', {
	        dialogId: payload.dialogId,
	        userId: payload.userId
	      });
	    }, WRITING_STATUS_TIME);
	  }
	  validate(fields) {
	    const result = {};
	    if (!main_core.Type.isUndefined(fields.dialog_id)) {
	      fields.dialogId = fields.dialog_id;
	    }
	    if (main_core.Type.isNumber(fields.dialogId) || main_core.Type.isStringFilled(fields.dialogId)) {
	      result.dialogId = fields.dialogId.toString();
	    }
	    if (!main_core.Type.isUndefined(fields.chat_id)) {
	      fields.chatId = fields.chat_id;
	    } else if (!main_core.Type.isUndefined(fields.id)) {
	      fields.chatId = fields.id;
	    }
	    if (main_core.Type.isNumber(fields.chatId) || main_core.Type.isStringFilled(fields.chatId)) {
	      result.chatId = Number.parseInt(fields.chatId, 10);
	    }
	    if (main_core.Type.isStringFilled(fields.type)) {
	      result.type = fields.type.toString();
	    }
	    if (main_core.Type.isNumber(fields.quoteId)) {
	      result.quoteId = Number.parseInt(fields.quoteId, 10);
	    }
	    if (main_core.Type.isNumber(fields.counter) || main_core.Type.isStringFilled(fields.counter)) {
	      result.counter = Number.parseInt(fields.counter, 10);
	    }
	    if (!main_core.Type.isUndefined(fields.user_counter)) {
	      result.userCounter = fields.user_counter;
	    }
	    if (main_core.Type.isNumber(fields.userCounter) || main_core.Type.isStringFilled(fields.userCounter)) {
	      result.userCounter = Number.parseInt(fields.userCounter, 10);
	    }
	    if (!main_core.Type.isUndefined(fields.last_id)) {
	      fields.lastId = fields.last_id;
	    }
	    if (main_core.Type.isNumber(fields.lastId)) {
	      result.lastReadId = fields.lastId;
	    }
	    if (!main_core.Type.isUndefined(fields.marked_id)) {
	      fields.markedId = fields.marked_id;
	    }
	    if (main_core.Type.isNumber(fields.markedId)) {
	      result.markedId = fields.markedId;
	    }
	    if (!main_core.Type.isUndefined(fields.last_message_id)) {
	      fields.lastMessageId = fields.last_message_id;
	    }
	    if (main_core.Type.isNumber(fields.lastMessageId) || main_core.Type.isStringFilled(fields.lastMessageId)) {
	      result.lastMessageId = Number.parseInt(fields.lastMessageId, 10);
	    }
	    if (main_core.Type.isPlainObject(fields.last_message_views)) {
	      fields.lastMessageViews = fields.last_message_views;
	    }
	    if (main_core.Type.isPlainObject(fields.lastMessageViews)) {
	      result.lastMessageViews = this.prepareLastMessageViews(fields.lastMessageViews);
	    }
	    if (main_core.Type.isBoolean(fields.hasPrevPage)) {
	      result.hasPrevPage = fields.hasPrevPage;
	    }
	    if (main_core.Type.isBoolean(fields.hasNextPage)) {
	      result.hasNextPage = fields.hasNextPage;
	    }
	    if (main_core.Type.isNumber(fields.savedPositionMessageId)) {
	      result.savedPositionMessageId = fields.savedPositionMessageId;
	    }
	    if (!main_core.Type.isUndefined(fields.textareaMessage)) {
	      result.textareaMessage = fields.textareaMessage.toString();
	    }
	    if (!main_core.Type.isUndefined(fields.title)) {
	      fields.name = fields.title;
	    }
	    if (main_core.Type.isNumber(fields.name) || main_core.Type.isStringFilled(fields.name)) {
	      result.name = main_core.Text.decode(fields.name.toString());
	    }
	    if (!main_core.Type.isUndefined(fields.owner)) {
	      fields.ownerId = fields.owner;
	    }
	    if (main_core.Type.isNumber(fields.ownerId) || main_core.Type.isStringFilled(fields.ownerId)) {
	      result.owner = Number.parseInt(fields.ownerId, 10);
	    }
	    if (main_core.Type.isString(fields.avatar)) {
	      result.avatar = this.prepareAvatar(fields.avatar);
	    }
	    if (main_core.Type.isStringFilled(fields.color)) {
	      result.color = fields.color;
	    }
	    if (main_core.Type.isBoolean(fields.extranet)) {
	      result.extranet = fields.extranet;
	    }
	    if (!main_core.Type.isUndefined(fields.entity_type)) {
	      fields.entityType = fields.entity_type;
	    }
	    if (main_core.Type.isStringFilled(fields.entityType)) {
	      result.entityType = fields.entityType;
	    }
	    if (!main_core.Type.isUndefined(fields.entity_id)) {
	      fields.entityId = fields.entity_id;
	    }
	    if (main_core.Type.isNumber(fields.entityId) || main_core.Type.isStringFilled(fields.entityId)) {
	      result.entityId = fields.entityId.toString();
	    }
	    if (!main_core.Type.isUndefined(fields.date_create)) {
	      fields.dateCreate = fields.date_create;
	    }
	    if (!main_core.Type.isUndefined(fields.dateCreate)) {
	      result.dateCreate = im_v2_lib_utils.Utils.date.cast(fields.dateCreate);
	    }
	    if (main_core.Type.isPlainObject(fields.public)) {
	      result.public = {};
	      if (main_core.Type.isStringFilled(fields.public.code)) {
	        result.public.code = fields.public.code;
	      }
	      if (main_core.Type.isStringFilled(fields.public.link)) {
	        result.public.link = fields.public.link;
	      }
	    }
	    if (!main_core.Type.isUndefined(fields.writing_list)) {
	      fields.writingList = fields.writing_list;
	    }
	    if (main_core.Type.isArray(fields.writingList)) {
	      result.writingList = this.prepareWritingList(fields.writingList);
	    }
	    if (!main_core.Type.isUndefined(fields.manager_list)) {
	      fields.managerList = fields.manager_list;
	    }
	    if (main_core.Type.isArray(fields.managerList)) {
	      result.managerList = [];
	      fields.managerList.forEach(userId => {
	        userId = Number.parseInt(userId, 10);
	        if (userId > 0) {
	          result.managerList.push(userId);
	        }
	      });
	    }
	    if (!main_core.Type.isUndefined(fields.mute_list)) {
	      fields.muteList = fields.mute_list;
	    }
	    if (main_core.Type.isArray(fields.muteList) || main_core.Type.isPlainObject(fields.muteList)) {
	      result.muteList = this.prepareMuteList(fields.muteList);
	    }
	    if (main_core.Type.isBoolean(fields.inited)) {
	      result.inited = fields.inited;
	    }
	    if (main_core.Type.isBoolean(fields.loading)) {
	      result.loading = fields.loading;
	    }
	    if (main_core.Type.isString(fields.description)) {
	      result.description = fields.description;
	    }
	    if (main_core.Type.isNumber(fields.disk_folder_id)) {
	      result.diskFolderId = fields.disk_folder_id;
	    }
	    return result;
	  }
	  prepareAvatar(avatar) {
	    let result = '';
	    if (!avatar || avatar.endsWith('/js/im/images/blank.gif')) {
	      result = '';
	    } else if (avatar.startsWith('http')) {
	      result = avatar;
	    } else {
	      result = im_v2_application_core.Core.getHost() + avatar;
	    }
	    if (result) {
	      result = encodeURI(result);
	    }
	    return result;
	  }
	  prepareWritingList(writingList) {
	    const result = [];
	    writingList.forEach(element => {
	      const item = {};
	      if (!element.userId) {
	        return false;
	      }
	      item.userId = Number.parseInt(element.userId, 10);
	      item.userName = im_v2_lib_utils.Utils.text.htmlspecialcharsback(element.userName);
	      result.push(item);
	    });
	    return result;
	  }
	  prepareMuteList(muteList) {
	    const result = [];
	    if (main_core.Type.isArray(muteList)) {
	      muteList.forEach(userId => {
	        userId = Number.parseInt(userId, 10);
	        if (userId > 0) {
	          result.push(userId);
	        }
	      });
	    } else if (main_core.Type.isPlainObject(muteList)) {
	      Object.entries(muteList).forEach(([key, value]) => {
	        if (!value) {
	          return;
	        }
	        const userId = Number.parseInt(key, 10);
	        if (userId > 0) {
	          result.push(userId);
	        }
	      });
	    }
	    return result;
	  }
	  prepareLastMessageViews(rawLastMessageViews) {
	    const {
	      count_of_viewers: countOfViewers,
	      first_viewers: rawFirstViewers,
	      message_id: messageId
	    } = rawLastMessageViews;
	    let firstViewer;
	    rawFirstViewers.forEach(rawFirstViewer => {
	      if (rawFirstViewer.user_id === im_v2_application_core.Core.getUserId()) {
	        return;
	      }
	      firstViewer = {
	        userId: rawFirstViewer.user_id,
	        userName: rawFirstViewer.user_name,
	        date: im_v2_lib_utils.Utils.date.cast(rawFirstViewer.date)
	      };
	    });
	    if (countOfViewers > 0 && !firstViewer) {
	      throw new Error('Dialogues model: no first viewer for message');
	    }
	    return {
	      countOfViewers,
	      firstViewer,
	      messageId
	    };
	  }
	  validateChatOptions(options) {
	    const result = {};
	    Object.entries(options).forEach(([type, typeOptions]) => {
	      const newType = im_v2_lib_utils.Utils.text.convertSnakeToCamelCase(type.toLowerCase());
	      result[newType] = {};
	      Object.entries(typeOptions).forEach(([key, value]) => {
	        const newKey = im_v2_lib_utils.Utils.text.convertSnakeToCamelCase(key.toLowerCase());
	        result[newType][newKey] = value;
	      });
	    });
	    return result;
	  }
	}

	class UsersModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'users';
	  }
	  getState() {
	    return {
	      collection: {},
	      onlineList: [],
	      mobileOnlineList: [],
	      absentList: [],
	      botList: {}
	    };
	  }
	  getElementState(params = {}) {
	    const {
	      id = 0
	    } = params;
	    return {
	      id,
	      name: '',
	      firstName: '',
	      lastName: '',
	      avatar: '',
	      color: im_v2_const.Color.base,
	      workPosition: '',
	      gender: 'M',
	      extranet: false,
	      network: false,
	      bot: false,
	      connector: false,
	      externalAuthId: 'default',
	      status: '',
	      idle: false,
	      lastActivityDate: false,
	      mobileLastDate: false,
	      isOnline: false,
	      isMobileOnline: false,
	      birthday: false,
	      isBirthday: false,
	      absent: false,
	      isAbsent: false,
	      departments: [],
	      phones: {
	        workPhone: '',
	        personalMobile: '',
	        personalPhone: '',
	        innerPhone: ''
	      }
	    };
	  }
	  getGetters() {
	    return {
	      get: state => (userId, getTemporary = false) => {
	        userId = Number.parseInt(userId, 10);
	        if (userId <= 0) {
	          if (getTemporary) {
	            userId = 0;
	          } else {
	            return null;
	          }
	        }
	        const user = state.collection[userId];
	        if (!getTemporary && !user) {
	          return null;
	        } else if (getTemporary && !user) {
	          return this.getElementState({
	            id: userId
	          });
	        }
	        return user;
	      },
	      getBlank: () => params => {
	        return this.getElementState(params);
	      },
	      getList: state => userList => {
	        const result = [];
	        if (!Array.isArray(userList)) {
	          return null;
	        }
	        userList.forEach(id => {
	          if (state.collection[id]) {
	            result.push(state.collection[id]);
	          } else {
	            result.push(this.getElementState({
	              id
	            }));
	          }
	        });
	        return result;
	      },
	      hasBirthday: state => userId => {
	        userId = Number.parseInt(userId, 10);
	        const user = state.collection[userId];
	        if (userId <= 0 || !user) {
	          return false;
	        }
	        return user.isBirthday;
	      },
	      hasVacation: state => userId => {
	        userId = Number.parseInt(userId, 10);
	        const user = state.collection[userId];
	        if (userId <= 0 || !user) {
	          return false;
	        }
	        return user.isAbsent;
	      },
	      getStatus: state => userId => {
	        userId = Number.parseInt(userId, 10);
	        const user = state.collection[userId];
	        if (userId <= 0 || !user) {
	          return false;
	        }
	        if (!user.isOnline) {
	          return '';
	        }
	        if (user.isMobileOnline) {
	          return im_v2_const.UserStatus.mobileOnline;
	        } else if (user.idle) {
	          // away by time
	          return im_v2_const.UserStatus.idle;
	        } else {
	          // manually selected status (online, away, dnd, break)
	          return user.status;
	        }
	      },
	      getLastOnline: state => userId => {
	        userId = Number.parseInt(userId, 10);
	        const user = state.collection[userId];
	        if (userId <= 0 || !user) {
	          return '';
	        }
	        return im_v2_lib_utils.Utils.user.getLastDateText(user);
	      },
	      getPosition: state => userId => {
	        userId = Number.parseInt(userId, 10);
	        const user = state.collection[userId];
	        if (userId <= 0 || !user) {
	          return '';
	        }
	        if (user.workPosition) {
	          return user.workPosition;
	        }
	        return main_core.Loc.getMessage('IM_MODEL_USERS_DEFAULT_NAME');
	      },
	      getBotType: state => userId => {
	        userId = Number.parseInt(userId, 10);
	        const user = state.collection[userId];
	        if (userId <= 0 || !user || !user.bot || !state.botList[userId]) {
	          return '';
	        }
	        const botType = state.botList[userId].type;
	        if (!im_v2_const.BotType[botType]) {
	          return im_v2_const.BotType.bot;
	        }
	        return botType;
	      }
	    };
	  }
	  getActions() {
	    return {
	      set: (store, payload) => {
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        payload.map(user => {
	          return this.validate(user);
	        }).forEach(user => {
	          const existingUser = store.state.collection[user.id];
	          if (existingUser) {
	            store.commit('update', {
	              id: user.id,
	              fields: user
	            });
	          } else {
	            store.commit('add', {
	              id: user.id,
	              fields: {
	                ...this.getElementState(),
	                ...user
	              }
	            });
	          }
	        });
	      },
	      add: (store, payload) => {
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        payload.map(user => {
	          return this.validate(user);
	        }).forEach(user => {
	          const existingUser = store.state.collection[user.id];
	          if (!existingUser) {
	            store.commit('add', {
	              id: user.id,
	              fields: {
	                ...this.getElementState(),
	                ...user
	              }
	            });
	          }
	        });
	      },
	      update: (store, payload) => {
	        payload.id = Number.parseInt(payload.id, 10);
	        const user = store.state.collection[payload.id];
	        if (!user) {
	          return false;
	        }
	        store.commit('update', {
	          id: payload.id,
	          fields: this.validate(payload.fields)
	        });
	      },
	      delete: (store, payload) => {
	        store.commit('delete', payload.id);
	      },
	      setBotList: (store, payload) => {
	        store.commit('setBotList', payload);
	      },
	      setStatus: (store, payload) => {
	        store.commit('update', {
	          id: im_v2_application_core.Core.getUserId(),
	          fields: this.validate(payload)
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      add: (state, payload) => {
	        state.collection[payload.id] = payload.fields;
	        const user = state.collection[payload.id];
	        if (im_v2_lib_utils.Utils.user.isOnline(user.lastActivityDate)) {
	          user.isOnline = true;
	          this.addToOnlineList(user.id);
	        }
	        if (im_v2_lib_utils.Utils.user.isMobileOnline(user.lastActivityDate, user.mobileLastDate)) {
	          user.isMobileOnline = true;
	          this.addToMobileOnlineList(user.id);
	        }
	        if (user.birthday && im_v2_lib_utils.Utils.user.isBirthdayToday(user.birthday)) {
	          user.isBirthday = true;
	          setTimeout(() => {
	            user.isBirthday = false;
	          }, im_v2_lib_utils.Utils.date.getTimeToNextMidnight());
	        }
	        if (user.absent) {
	          user.isAbsent = true;
	          this.addToAbsentList(user.id);
	        }
	        this.startOnlineCheckInterval();
	        this.startAbsentCheckInterval();
	      },
	      update: (state, payload) => {
	        const user = state.collection[payload.id];
	        if (im_v2_lib_utils.Utils.user.isOnline(payload.fields.lastActivityDate)) {
	          user.isOnline = true;
	          this.addToOnlineList(payload.fields.id);
	        }
	        if (im_v2_lib_utils.Utils.user.isMobileOnline(payload.fields.lastActivityDate, payload.fields.mobileLastDate)) {
	          user.isMobileOnline = true;
	          this.addToMobileOnlineList(payload.fields.id);
	        }
	        if (payload.fields.absent === false) {
	          state.absentList = state.absentList.filter(element => {
	            return element !== payload.id;
	          });
	          state.collection[payload.id].isAbsent = false;
	        } else if (main_core.Type.isDate(payload.fields.absent)) {
	          state.collection[payload.id].isAbsent = true;
	          this.addToAbsentList(payload.id);
	        }
	        state.collection[payload.id] = {
	          ...state.collection[payload.id],
	          ...payload.fields
	        };
	      },
	      delete: (state, payload) => {
	        delete state.collection[payload.id];
	      },
	      setBotList: (state, payload) => {
	        state.botList = payload;
	      }
	    };
	  }
	  validate(fields) {
	    const result = {};
	    if (main_core.Type.isNumber(fields.id) || main_core.Type.isString(fields.id)) {
	      result.id = Number.parseInt(fields.id, 10);
	    }
	    if (main_core.Type.isStringFilled(fields.first_name)) {
	      fields.firstName = fields.first_name;
	    }
	    if (main_core.Type.isStringFilled(fields.last_name)) {
	      fields.lastName = fields.last_name;
	    }
	    if (main_core.Type.isStringFilled(fields.firstName)) {
	      result.firstName = im_v2_lib_utils.Utils.text.htmlspecialcharsback(fields.firstName);
	    }
	    if (main_core.Type.isStringFilled(fields.lastName)) {
	      result.lastName = im_v2_lib_utils.Utils.text.htmlspecialcharsback(fields.lastName);
	    }
	    if (main_core.Type.isStringFilled(fields.name)) {
	      fields.name = im_v2_lib_utils.Utils.text.htmlspecialcharsback(fields.name);
	      result.name = fields.name;
	    }
	    if (main_core.Type.isStringFilled(fields.color)) {
	      result.color = fields.color;
	    }
	    if (main_core.Type.isStringFilled(fields.avatar)) {
	      result.avatar = this.prepareAvatar(fields.avatar);
	    }
	    if (main_core.Type.isStringFilled(fields.work_position)) {
	      fields.workPosition = fields.work_position;
	    }
	    if (main_core.Type.isStringFilled(fields.workPosition)) {
	      result.workPosition = im_v2_lib_utils.Utils.text.htmlspecialcharsback(fields.workPosition);
	    }
	    if (main_core.Type.isStringFilled(fields.gender)) {
	      result.gender = fields.gender === 'F' ? 'F' : 'M';
	    }
	    if (main_core.Type.isStringFilled(fields.birthday)) {
	      result.birthday = fields.birthday;
	    }
	    if (main_core.Type.isBoolean(fields.extranet)) {
	      result.extranet = fields.extranet;
	    }
	    if (main_core.Type.isBoolean(fields.network)) {
	      result.network = fields.network;
	    }
	    if (main_core.Type.isBoolean(fields.bot)) {
	      result.bot = fields.bot;
	    }
	    if (main_core.Type.isBoolean(fields.connector)) {
	      result.connector = fields.connector;
	    }
	    if (main_core.Type.isStringFilled(fields.external_auth_id)) {
	      fields.externalAuthId = fields.external_auth_id;
	    }
	    if (main_core.Type.isStringFilled(fields.externalAuthId)) {
	      result.externalAuthId = fields.externalAuthId;
	    }
	    if (main_core.Type.isStringFilled(fields.status)) {
	      result.status = fields.status;
	    }
	    if (!main_core.Type.isUndefined(fields.idle)) {
	      result.idle = im_v2_lib_utils.Utils.date.cast(fields.idle, false);
	    }
	    if (!main_core.Type.isUndefined(fields.last_activity_date)) {
	      fields.lastActivityDate = fields.last_activity_date;
	    }
	    if (!main_core.Type.isUndefined(fields.lastActivityDate)) {
	      result.lastActivityDate = im_v2_lib_utils.Utils.date.cast(fields.lastActivityDate, false);
	    }
	    if (!main_core.Type.isUndefined(fields.mobile_last_date)) {
	      fields.mobileLastDate = fields.mobile_last_date;
	    }
	    if (!main_core.Type.isUndefined(fields.mobileLastDate)) {
	      result.mobileLastDate = im_v2_lib_utils.Utils.date.cast(fields.mobileLastDate, false);
	    }
	    if (!main_core.Type.isUndefined(fields.absent)) {
	      result.absent = im_v2_lib_utils.Utils.date.cast(fields.absent, false);
	    }
	    if (Array.isArray(fields.departments)) {
	      result.departments = [];
	      fields.departments.forEach(departmentId => {
	        departmentId = Number.parseInt(departmentId, 10);
	        if (departmentId > 0) {
	          result.departments.push(departmentId);
	        }
	      });
	    }
	    if (main_core.Type.isPlainObject(fields.phones)) {
	      result.phones = this.preparePhones(fields.phones);
	    }
	    return result;
	  }
	  prepareAvatar(avatar) {
	    let result = '';
	    if (!avatar || avatar.endsWith('/js/im/images/blank.gif')) {
	      result = '';
	    } else if (avatar.startsWith('http')) {
	      result = avatar;
	    } else {
	      result = im_v2_application_core.Core.getHost() + avatar;
	    }
	    if (result) {
	      result = encodeURI(result);
	    }
	    return result;
	  }
	  preparePhones(phones) {
	    const result = {};
	    if (!main_core.Type.isUndefined(phones.work_phone)) {
	      phones.workPhone = phones.work_phone;
	    }
	    if (main_core.Type.isStringFilled(phones.workPhone) || main_core.Type.isNumber(phones.workPhone)) {
	      result.workPhone = phones.workPhone.toString();
	    }
	    if (!main_core.Type.isUndefined(phones.personal_mobile)) {
	      phones.personalMobile = phones.personal_mobile;
	    }
	    if (main_core.Type.isStringFilled(phones.personalMobile) || main_core.Type.isNumber(phones.personalMobile)) {
	      result.personalMobile = phones.personalMobile.toString();
	    }
	    if (!main_core.Type.isUndefined(phones.personal_phone)) {
	      phones.personalPhone = phones.personal_phone;
	    }
	    if (main_core.Type.isStringFilled(phones.personalPhone) || main_core.Type.isNumber(phones.personalPhone)) {
	      result.personalPhone = phones.personalPhone.toString();
	    }
	    if (!main_core.Type.isUndefined(phones.inner_phone)) {
	      phones.innerPhone = phones.inner_phone;
	    }
	    if (main_core.Type.isStringFilled(phones.innerPhone) || main_core.Type.isNumber(phones.innerPhone)) {
	      result.innerPhone = phones.innerPhone.toString();
	    }
	    return result;
	  }
	  addToOnlineList(id) {
	    const state = this.store.state.users;
	    if (!state.onlineList.includes(id)) {
	      state.onlineList.push(id);
	    }
	  }
	  addToMobileOnlineList(id) {
	    const state = this.store.state.users;
	    if (!state.mobileOnlineList.includes(id)) {
	      state.mobileOnlineList.push(id);
	    }
	  }
	  addToAbsentList(id) {
	    const state = this.store.state.users;
	    if (!state.absentList.includes(id)) {
	      state.absentList.push(id);
	    }
	  }
	  startAbsentCheckInterval() {
	    if (this.absentCheckInterval) {
	      return true;
	    }
	    const TIME_TO_NEXT_DAY = 1000 * 60 * 60 * 24;
	    this.absentCheckInterval = setTimeout(() => {
	      setInterval(() => {
	        const state = this.store.state.users;
	        state.absentList.forEach(userId => {
	          const user = state.collection[userId];
	          if (!user) {
	            return;
	          }
	          const currentTime = Date.now();
	          const absentEnd = new Date(user.absent).getTime();
	          if (absentEnd <= currentTime) {
	            state.absentList = state.absentList.filter(element => {
	              return element !== userId;
	            });
	            user.isAbsent = false;
	          }
	        });
	      }, TIME_TO_NEXT_DAY);
	    }, im_v2_lib_utils.Utils.date.getTimeToNextMidnight());
	  }
	  startOnlineCheckInterval() {
	    if (this.onlineCheckInterval) {
	      return true;
	    }
	    const ONE_MINUTE = 60000;
	    this.onlineCheckInterval = setInterval(() => {
	      const state = this.store.state.users;
	      state.onlineList.forEach(userId => {
	        const user = state.collection[userId];
	        if (!user) {
	          return;
	        }
	        if (im_v2_lib_utils.Utils.user.isOnline(user.lastActivityDate)) {
	          user.isOnline = true;
	        } else {
	          user.isOnline = false;
	          state.onlineList = state.onlineList.filter(element => element !== userId);
	        }
	      });
	      state.mobileOnlineList.forEach(userId => {
	        const user = state.collection[userId];
	        if (!user) {
	          return;
	        }
	        if (im_v2_lib_utils.Utils.user.isMobileOnline(user.lastActivityDate, user.mobileLastDate)) {
	          user.isMobileOnline = true;
	        } else {
	          user.isMobileOnline = false;
	          state.mobileOnlineList = state.mobileOnlineList.filter(element => element !== userId);
	        }
	      });
	    }, ONE_MINUTE);
	  }
	}

	class FilesModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'files';
	  }
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getElementState() {
	    return {
	      id: 0,
	      chatId: 0,
	      name: 'File is deleted',
	      date: new Date(),
	      type: 'file',
	      extension: '',
	      icon: 'empty',
	      size: 0,
	      image: false,
	      status: im_v2_const.FileStatus.done,
	      progress: 100,
	      authorId: 0,
	      authorName: '',
	      urlPreview: '',
	      urlShow: '',
	      urlDownload: '',
	      viewerAttrs: null
	    };
	  }
	  getGetters() {
	    return {
	      get: state => (fileId, getTemporary = false) => {
	        if (!fileId) {
	          return null;
	        }
	        if (!getTemporary && !state.collection[fileId]) {
	          return null;
	        }
	        return state.collection[fileId];
	      },
	      isInCollection: state => payload => {
	        const {
	          fileId
	        } = payload;
	        return !!state.collection[fileId];
	      }
	    };
	  }
	  getActions() {
	    return {
	      add: (store, payload) => {
	        const preparedFile = {
	          ...this.getElementState(),
	          ...this.validate(payload)
	        };
	        store.commit('add', {
	          files: [preparedFile]
	        });
	      },
	      set: (store, payload) => {
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        payload = payload.map(file => {
	          return {
	            ...this.getElementState(),
	            ...this.validate(file)
	          };
	        });
	        store.commit('add', {
	          files: payload
	        });
	      },
	      update: (store, payload) => {
	        const {
	          id,
	          fields
	        } = payload;
	        const existingItem = store.state.collection[id];
	        if (!existingItem) {
	          return false;
	        }
	        store.commit('update', {
	          id: id,
	          fields: this.validate(fields)
	        });
	        return true;
	      },
	      updateWithId: (store, payload) => {
	        const {
	          id,
	          fields
	        } = payload;
	        if (!store.state.collection[id]) {
	          return;
	        }
	        store.commit('updateWithId', {
	          id,
	          fields: this.validate(fields)
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      add: (state, payload) => {
	        payload.files.forEach(file => {
	          state.collection[file.id] = file;
	        });
	      },
	      update: (state, payload) => {
	        Object.entries(payload.fields).forEach(([key, value]) => {
	          state.collection[payload.id][key] = value;
	        });
	      },
	      updateWithId: (state, payload) => {
	        const {
	          id,
	          fields
	        } = payload;
	        const currentFile = {
	          ...state.collection[id]
	        };
	        delete state.collection[id];
	        state.collection[fields.id] = {
	          ...currentFile,
	          ...fields
	        };
	      }
	    };
	  }
	  validate(file) {
	    const result = {};
	    if (main_core.Type.isNumber(file.id) || main_core.Type.isStringFilled(file.id)) {
	      result.id = file.id;
	    }
	    if (main_core.Type.isNumber(file.chatId) || main_core.Type.isString(file.chatId)) {
	      result.chatId = Number.parseInt(file.chatId, 10);
	    }
	    if (!main_core.Type.isUndefined(file.date)) {
	      result.date = im_v2_lib_utils.Utils.date.cast(file.date);
	    }
	    if (main_core.Type.isString(file.type)) {
	      result.type = file.type;
	    }
	    if (main_core.Type.isString(file.extension)) {
	      result.extension = file.extension.toString();
	      if (result.type === 'image') {
	        result.icon = 'img';
	      } else if (result.type === 'video') {
	        result.icon = 'mov';
	      } else {
	        result.icon = im_v2_lib_utils.Utils.file.getIconTypeByExtension(result.extension);
	      }
	    }
	    if (main_core.Type.isString(file.name) || main_core.Type.isNumber(file.name)) {
	      result.name = file.name.toString();
	    }
	    if (main_core.Type.isNumber(file.size) || main_core.Type.isString(file.size)) {
	      result.size = Number.parseInt(file.size, 10);
	    }
	    if (main_core.Type.isBoolean(file.image)) {
	      result.image = false;
	    } else if (main_core.Type.isPlainObject(file.image)) {
	      result.image = {
	        width: 0,
	        height: 0
	      };
	      if (main_core.Type.isString(file.image.width) || main_core.Type.isNumber(file.image.width)) {
	        result.image.width = Number.parseInt(file.image.width, 10);
	      }
	      if (main_core.Type.isString(file.image.height) || main_core.Type.isNumber(file.image.height)) {
	        result.image.height = Number.parseInt(file.image.height, 10);
	      }
	      if (result.image.width <= 0 || result.image.height <= 0) {
	        result.image = false;
	      }
	    }
	    if (main_core.Type.isString(file.status) && !main_core.Type.isUndefined(im_v2_const.FileStatus[file.status])) {
	      result.status = file.status;
	    }
	    if (main_core.Type.isNumber(file.progress) || main_core.Type.isString(file.progress)) {
	      result.progress = Number.parseInt(file.progress, 10);
	    }
	    if (main_core.Type.isNumber(file.authorId) || main_core.Type.isString(file.authorId)) {
	      result.authorId = Number.parseInt(file.authorId, 10);
	    }
	    if (main_core.Type.isString(file.authorName) || main_core.Type.isNumber(file.authorName)) {
	      result.authorName = file.authorName.toString();
	    }
	    if (main_core.Type.isString(file.urlPreview)) {
	      if (!file.urlPreview || file.urlPreview.startsWith('http') || file.urlPreview.startsWith('bx') || file.urlPreview.startsWith('file') || file.urlPreview.startsWith('blob')) {
	        result.urlPreview = file.urlPreview;
	      } else {
	        result.urlPreview = im_v2_application_core.Core.getHost() + file.urlPreview;
	      }
	    }
	    if (main_core.Type.isString(file.urlDownload)) {
	      if (!file.urlDownload || file.urlDownload.startsWith('http') || file.urlDownload.startsWith('bx') || file.urlPreview.startsWith('file')) {
	        result.urlDownload = file.urlDownload;
	      } else {
	        result.urlDownload = im_v2_application_core.Core.getHost() + file.urlDownload;
	      }
	    }
	    if (main_core.Type.isString(file.urlShow)) {
	      if (!file.urlShow || file.urlShow.startsWith('http') || file.urlShow.startsWith('bx') || file.urlShow.startsWith('file')) {
	        result.urlShow = file.urlShow;
	      } else {
	        result.urlShow = im_v2_application_core.Core.getHost() + file.urlShow;
	      }
	    }
	    if (main_core.Type.isPlainObject(file.viewerAttrs)) {
	      result.viewerAttrs = this.validateViewerAttributes(file.viewerAttrs);
	    }
	    return result;
	  }
	  validateViewerAttributes(viewerAttrs) {
	    const result = {
	      viewer: true
	    };
	    if (main_core.Type.isString(viewerAttrs.actions)) {
	      result.actions = viewerAttrs.actions;
	    }
	    if (main_core.Type.isString(viewerAttrs.objectId)) {
	      result.objectId = viewerAttrs.objectId;
	    }
	    if (main_core.Type.isString(viewerAttrs.src)) {
	      result.src = viewerAttrs.src;
	    }
	    if (main_core.Type.isString(viewerAttrs.title)) {
	      result.title = viewerAttrs.title;
	    }
	    if (main_core.Type.isString(viewerAttrs.viewerGroupBy)) {
	      result.viewerGroupBy = viewerAttrs.viewerGroupBy;
	    }
	    if (main_core.Type.isString(viewerAttrs.viewerType)) {
	      result.viewerType = viewerAttrs.viewerType;
	    }
	    if (main_core.Type.isString(viewerAttrs.viewerTypeClass)) {
	      result.viewerTypeClass = viewerAttrs.viewerTypeClass;
	    }
	    if (main_core.Type.isBoolean(viewerAttrs.viewerSeparateItem)) {
	      result.viewerSeparateItem = viewerAttrs.viewerSeparateItem;
	    }
	    if (main_core.Type.isString(viewerAttrs.viewerExtension)) {
	      result.viewerExtension = viewerAttrs.viewerExtension;
	    }
	    if (main_core.Type.isNumber(viewerAttrs.imChatId)) {
	      result.imChatId = viewerAttrs.imChatId;
	    }
	    return result;
	  }
	}

	class CallsModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getElementState() {
	    return {
	      dialogId: 0,
	      name: '',
	      call: {},
	      state: im_v2_const.RecentCallStatus.waiting
	    };
	  }
	  getGetters() {
	    return {
	      get: state => {
	        return Object.values(state.collection);
	      }
	    };
	  }
	  getActions() {
	    return {
	      addActiveCall: (store, payload) => {
	        const existingCall = Object.values(store.state.collection).find(item => {
	          return item.dialogId === payload.dialogId || item.call.id === payload.call.id;
	        });
	        if (existingCall) {
	          store.commit('updateActiveCall', {
	            dialogId: existingCall.dialogId,
	            fields: this.validateActiveCall(payload)
	          });
	          return true;
	        }
	        store.commit('addActiveCall', this.prepareActiveCall(payload));
	      },
	      updateActiveCall: (store, payload) => {
	        const existingCall = store.state.collection[payload.dialogId];
	        if (!existingCall) {
	          return;
	        }
	        store.commit('updateActiveCall', {
	          dialogId: existingCall.dialogId,
	          fields: this.validateActiveCall(payload.fields)
	        });
	      },
	      deleteActiveCall: (store, payload) => {
	        const existingCall = store.state.collection[payload.dialogId];
	        if (!existingCall) {
	          return;
	        }
	        store.commit('deleteActiveCall', {
	          dialogId: existingCall.dialogId
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      addActiveCall: (state, payload) => {
	        state.collection[payload.dialogId] = payload;
	      },
	      updateActiveCall: (state, payload) => {
	        state.collection[payload.dialogId] = {
	          ...state.collection[payload.dialogId],
	          ...payload.fields
	        };
	      },
	      deleteActiveCall: (state, payload) => {
	        delete state.collection[payload.dialogId];
	      }
	    };
	  }
	  prepareActiveCall(call) {
	    return {
	      ...this.getElementState(),
	      ...this.validateActiveCall(call)
	    };
	  }
	  validateActiveCall(fields) {
	    const result = {};
	    if (main_core.Type.isStringFilled(fields.dialogId) || main_core.Type.isNumber(fields.dialogId)) {
	      result.dialogId = fields.dialogId;
	    }
	    if (main_core.Type.isStringFilled(fields.name)) {
	      result.name = fields.name;
	    }
	    if (main_core.Type.isObjectLike(fields.call)) {
	      var _fields$call, _fields$call$associat;
	      result.call = fields.call;
	      if (((_fields$call = fields.call) == null ? void 0 : (_fields$call$associat = _fields$call.associatedEntity) == null ? void 0 : _fields$call$associat.avatar) === '/bitrix/js/im/images/blank.gif') {
	        result.call.associatedEntity.avatar = '';
	      }
	    }
	    if (im_v2_const.RecentCallStatus[fields.state]) {
	      result.state = fields.state;
	    }
	    return result;
	  }
	}

	class RecentModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'recent';
	  }
	  getNestedModules() {
	    return {
	      calls: CallsModel
	    };
	  }
	  getState() {
	    return {
	      collection: {},
	      recentCollection: new Set(),
	      unreadCollection: new Set(),
	      unloadedChatCounters: {}
	    };
	  }
	  getElementState() {
	    return {
	      dialogId: '0',
	      message: {
	        id: 0,
	        senderId: 0,
	        date: new Date(),
	        status: im_v2_const.MessageStatus.received,
	        sending: false,
	        text: '',
	        params: {
	          withFile: false,
	          withAttach: false
	        }
	      },
	      draft: {
	        text: '',
	        date: null
	      },
	      unread: false,
	      pinned: false,
	      liked: false,
	      invitation: {
	        isActive: false,
	        originator: 0,
	        canResend: false
	      },
	      options: {}
	    };
	  }
	  getGetters() {
	    return {
	      getRecentCollection: state => {
	        return [...state.recentCollection].map(id => {
	          return state.collection[id];
	        });
	      },
	      getUnreadCollection: state => {
	        return [...state.unreadCollection].map(id => {
	          return state.collection[id];
	        });
	      },
	      getSortedCollection: state => {
	        const collectionAsArray = Object.values(state.collection).filter(item => {
	          const isBirthdayPlaceholder = item.options.birthdayPlaceholder;
	          const isInvitedUser = item.options.defaultUserRecord;
	          return !isBirthdayPlaceholder && !isInvitedUser && item.message.id;
	        });
	        return [...collectionAsArray].sort((a, b) => {
	          return b.message.date - a.message.date;
	        });
	      },
	      get: state => dialogId => {
	        if (main_core.Type.isNumber(dialogId)) {
	          dialogId = dialogId.toString();
	        }
	        if (state.collection[dialogId]) {
	          return state.collection[dialogId];
	        }
	        return null;
	      },
	      needsBirthdayPlaceholder: state => dialogId => {
	        const currentItem = state.collection[dialogId];
	        if (!currentItem) {
	          return false;
	        }
	        const dialog = this.store.getters['dialogues/get'](dialogId);
	        if (!dialog || dialog.type !== im_v2_const.DialogType.user) {
	          return false;
	        }
	        const hasBirthday = this.store.getters['users/hasBirthday'](dialogId);
	        if (!hasBirthday) {
	          return false;
	        }
	        const hasMessage = im_v2_lib_utils.Utils.text.isTempMessage(currentItem.message.id) || currentItem.message.id > 0;
	        const hasTodayMessage = hasMessage && im_v2_lib_utils.Utils.date.isToday(currentItem.message.date);
	        const showBirthday = this.store.getters['application/settings/get'](im_v2_const.Settings.recent.showBirthday);
	        return showBirthday && !hasTodayMessage && dialog.counter === 0;
	      },
	      needsVacationPlaceholder: state => dialogId => {
	        const currentItem = state.collection[dialogId];
	        if (!currentItem) {
	          return false;
	        }
	        const dialog = this.store.getters['dialogues/get'](dialogId);
	        if (!dialog || dialog.type !== im_v2_const.DialogType.user) {
	          return false;
	        }
	        const hasVacation = this.store.getters['users/hasVacation'](dialogId);
	        if (!hasVacation) {
	          return false;
	        }
	        const hasMessage = im_v2_lib_utils.Utils.text.isTempMessage(currentItem.message.id) || currentItem.message.id > 0;
	        const hasTodayMessage = hasMessage && im_v2_lib_utils.Utils.date.isToday(currentItem.message.date);
	        return !hasTodayMessage && dialog.counter === 0;
	      },
	      getMessageDate: state => dialogId => {
	        const currentItem = state.collection[dialogId];
	        if (!currentItem) {
	          return null;
	        }
	        if (main_core.Type.isDate(currentItem.draft.date) && currentItem.draft.date > currentItem.message.date) {
	          return currentItem.draft.date;
	        }
	        const needsBirthdayPlaceholder = this.store.getters['recent/needsBirthdayPlaceholder'](currentItem.dialogId);
	        if (needsBirthdayPlaceholder) {
	          return im_v2_lib_utils.Utils.date.getStartOfTheDay();
	        }
	        return currentItem.message.date;
	      },
	      getTotalCounter: state => {
	        let loadedChatsCounter = 0;
	        [...state.recentCollection].forEach(dialogId => {
	          const dialog = this.store.getters['dialogues/get'](dialogId, true);
	          const recentItem = state.collection[dialogId];
	          const isMuted = dialog.muteList.includes(im_v2_application_core.Core.getUserId());
	          if (isMuted) {
	            return;
	          }
	          const isMarked = recentItem.unread;
	          if (dialog.counter === 0 && isMarked) {
	            loadedChatsCounter++;
	            return;
	          }
	          loadedChatsCounter += dialog.counter;
	        });
	        let unloadedChatsCounter = 0;
	        Object.values(state.unloadedChatCounters).forEach(counter => {
	          unloadedChatsCounter += counter;
	        });
	        return loadedChatsCounter + unloadedChatsCounter;
	      }
	    };
	  }
	  getActions() {
	    return {
	      setRecent: (store, payload) => {
	        this.store.dispatch('recent/set', payload).then(itemIds => {
	          store.commit('setRecentCollection', itemIds);
	        });
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        const zeroedCountersForNewItems = {};
	        payload.forEach(item => {
	          zeroedCountersForNewItems[item.chat_id] = 0;
	        });
	        this.store.dispatch('recent/setUnloadedChatCounters', zeroedCountersForNewItems);
	      },
	      setUnread: (store, payload) => {
	        this.store.dispatch('recent/set', payload).then(itemIds => {
	          store.commit('setUnreadCollection', itemIds);
	        });
	      },
	      set: (store, payload) => {
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        const itemsToUpdate = [];
	        const itemsToAdd = [];
	        payload.map(element => {
	          return this.validate(element);
	        }).forEach(element => {
	          const existingItem = store.state.collection[element.dialogId];
	          if (existingItem) {
	            itemsToUpdate.push({
	              dialogId: existingItem.dialogId,
	              fields: {
	                ...element
	              }
	            });
	          } else {
	            itemsToAdd.push({
	              ...this.getElementState(),
	              ...element
	            });
	          }
	        });
	        if (itemsToAdd.length > 0) {
	          store.commit('add', itemsToAdd);
	        }
	        if (itemsToUpdate.length > 0) {
	          store.commit('update', itemsToUpdate);
	        }
	        return [...itemsToAdd, ...itemsToUpdate].map(item => item.dialogId);
	      },
	      update: (store, payload) => {
	        const {
	          id,
	          fields
	        } = payload;
	        const existingItem = store.state.collection[id];
	        if (!existingItem) {
	          return false;
	        }
	        if (fields.message) {
	          fields.message = {
	            ...existingItem.message,
	            ...fields.message
	          };
	        }
	        store.commit('update', {
	          dialogId: existingItem.dialogId,
	          fields: this.validate(fields)
	        });
	      },
	      unread: (store, payload) => {
	        const existingItem = store.state.collection[payload.id];
	        if (!existingItem) {
	          return false;
	        }
	        store.commit('update', {
	          dialogId: existingItem.dialogId,
	          fields: {
	            unread: payload.action
	          }
	        });
	      },
	      pin: (store, payload) => {
	        const existingItem = store.state.collection[payload.id];
	        if (!existingItem) {
	          return false;
	        }
	        store.commit('update', {
	          dialogId: existingItem.dialogId,
	          fields: {
	            pinned: payload.action
	          }
	        });
	      },
	      like: (store, payload) => {
	        const existingItem = store.state.collection[payload.id];
	        if (!existingItem) {
	          return false;
	        }
	        const isLastMessage = existingItem.message.id === Number.parseInt(payload.messageId, 10);
	        const isExactMessageLiked = !main_core.Type.isUndefined(payload.messageId) && payload.liked === true;
	        if (isExactMessageLiked && !isLastMessage) {
	          return false;
	        }
	        store.commit('update', {
	          dialogId: existingItem.dialogId,
	          fields: {
	            liked: payload.liked === true
	          }
	        });
	      },
	      draft: (store, payload) => {
	        const dialog = this.store.getters['dialogues/get'](payload.id);
	        if (!dialog) {
	          return false;
	        }
	        let existingItem = store.state.collection[payload.id];
	        if (!existingItem) {
	          if (payload.text === '') {
	            return false;
	          }
	          const newItem = {
	            dialogId: payload.id.toString()
	          };
	          store.commit('add', {
	            ...this.getElementState(),
	            ...newItem
	          });
	          store.commit('setRecentCollection', [newItem.dialogId]);
	          existingItem = store.state.collection[payload.id];
	        }
	        const fields = this.validate({
	          draft: {
	            text: payload.text.toString()
	          }
	        });
	        if (fields.draft.text === existingItem.draft.text) {
	          return false;
	        }
	        store.commit('update', {
	          dialogId: existingItem.dialogId,
	          fields
	        });
	      },
	      delete: (store, payload) => {
	        const existingItem = store.state.collection[payload.id];
	        if (!existingItem) {
	          return false;
	        }
	        store.commit('delete', {
	          id: existingItem.dialogId
	        });
	        store.commit('deleteFromRecentCollection', existingItem.dialogId);
	      },
	      clearUnread: store => {
	        store.commit('clearUnread');
	      },
	      setUnloadedChatCounters: (store, payload) => {
	        if (!main_core.Type.isPlainObject(payload)) {
	          return;
	        }
	        store.commit('setUnloadedChatCounters', payload);
	      }
	    };
	  }
	  getMutations() {
	    return {
	      setRecentCollection: (state, payload) => {
	        payload.forEach(dialogId => {
	          state.recentCollection.add(dialogId);
	        });
	      },
	      deleteFromRecentCollection: (state, payload) => {
	        state.recentCollection.delete(payload);
	      },
	      setUnreadCollection: (state, payload) => {
	        payload.forEach(dialogId => {
	          state.unreadCollection.add(dialogId);
	        });
	      },
	      add: (state, payload) => {
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        payload.forEach(item => {
	          state.collection[item.dialogId] = item;
	        });
	      },
	      update: (state, payload) => {
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        payload.forEach(({
	          dialogId,
	          fields
	        }) => {
	          // if we already got chat - we should not update it with default user chat (unless it's an accepted invitation)
	          const defaultUserElement = fields.options && fields.options.defaultUserRecord && !fields.invitation;
	          if (defaultUserElement) {
	            return false;
	          }
	          const currentElement = state.collection[dialogId];
	          fields.message = {
	            ...currentElement.message,
	            ...fields.message
	          };
	          fields.options = {
	            ...currentElement.options,
	            ...fields.options
	          };
	          state.collection[dialogId] = {
	            ...currentElement,
	            ...fields
	          };
	        });
	      },
	      delete: (state, payload) => {
	        delete state.collection[payload.id];
	      },
	      clearUnread: state => {
	        Object.keys(state.collection).forEach(key => {
	          state.collection[key].unread = false;
	        });
	      },
	      setUnloadedChatCounters: (state, payload) => {
	        Object.entries(payload).forEach(([chatId, counter]) => {
	          if (counter === 0) {
	            delete state.unloadedChatCounters[chatId];
	            return;
	          }
	          state.unloadedChatCounters[chatId] = counter;
	        });
	      }
	    };
	  }
	  validate(fields) {
	    const result = {
	      options: {}
	    };
	    if (main_core.Type.isNumber(fields.id)) {
	      result.dialogId = fields.id.toString();
	    }
	    if (main_core.Type.isStringFilled(fields.id)) {
	      result.dialogId = fields.id;
	    }
	    if (main_core.Type.isNumber(fields.dialogId)) {
	      result.dialogId = fields.dialogId.toString();
	    }
	    if (main_core.Type.isStringFilled(fields.dialogId)) {
	      result.dialogId = fields.dialogId;
	    }
	    if (main_core.Type.isPlainObject(fields.message)) {
	      result.message = this.prepareMessage(fields);
	    }
	    if (main_core.Type.isPlainObject(fields.draft)) {
	      result.draft = this.prepareDraft(fields);
	    }
	    if (main_core.Type.isBoolean(fields.unread)) {
	      result.unread = fields.unread;
	    }
	    if (main_core.Type.isBoolean(fields.pinned)) {
	      result.pinned = fields.pinned;
	    }
	    if (main_core.Type.isBoolean(fields.liked)) {
	      result.liked = fields.liked;
	    }
	    if (main_core.Type.isPlainObject(fields.invited)) {
	      result.invitation = {
	        isActive: true,
	        originator: fields.invited.originator_id,
	        canResend: fields.invited.can_resend
	      };
	      result.options.defaultUserRecord = true;
	    } else if (fields.invited === false) {
	      result.invitation = {
	        isActive: false,
	        originator: 0,
	        canResend: false
	      };
	      result.options.defaultUserRecord = true;
	    }
	    if (main_core.Type.isPlainObject(fields.options)) {
	      if (!result.options) {
	        result.options = {};
	      }
	      if (main_core.Type.isBoolean(fields.options.default_user_record)) {
	        fields.options.defaultUserRecord = fields.options.default_user_record;
	      }
	      if (main_core.Type.isBoolean(fields.options.defaultUserRecord)) {
	        result.options.defaultUserRecord = fields.options.defaultUserRecord;
	      }
	      if (main_core.Type.isBoolean(fields.options.birthdayPlaceholder)) {
	        result.options.birthdayPlaceholder = fields.options.birthdayPlaceholder;
	      }
	    }
	    return result;
	  }
	  prepareMessage(fields) {
	    var _fields$message$param, _fields$message$param2, _fields$message$param3, _fields$message$param4, _fields$message$param5;
	    const {
	      message
	    } = this.getElementState();
	    if (main_core.Type.isNumber(fields.message.id) || im_v2_lib_utils.Utils.text.isUuidV4(fields.message.id) || main_core.Type.isStringFilled(fields.message.id)) {
	      message.id = fields.message.id;
	    }
	    if (main_core.Type.isString(fields.message.text)) {
	      message.text = fields.message.text;
	    }
	    if (main_core.Type.isStringFilled(fields.message.attach) || main_core.Type.isBoolean(fields.message.attach) || main_core.Type.isArray(fields.message.attach)) {
	      message.params.withAttach = fields.message.attach;
	    } else if (main_core.Type.isStringFilled((_fields$message$param = fields.message.params) == null ? void 0 : _fields$message$param.withAttach) || main_core.Type.isBoolean((_fields$message$param2 = fields.message.params) == null ? void 0 : _fields$message$param2.withAttach) || main_core.Type.isArray((_fields$message$param3 = fields.message.params) == null ? void 0 : _fields$message$param3.withAttach)) {
	      message.params.withAttach = fields.message.params.withAttach;
	    }
	    if (main_core.Type.isBoolean(fields.message.file) || main_core.Type.isPlainObject(fields.message.file)) {
	      message.params.withFile = fields.message.file;
	    } else if (main_core.Type.isBoolean((_fields$message$param4 = fields.message.params) == null ? void 0 : _fields$message$param4.withFile) || main_core.Type.isPlainObject((_fields$message$param5 = fields.message.params) == null ? void 0 : _fields$message$param5.withFile)) {
	      message.params.withFile = fields.message.params.withFile;
	    }
	    if (main_core.Type.isDate(fields.message.date) || main_core.Type.isString(fields.message.date)) {
	      message.date = im_v2_lib_utils.Utils.date.cast(fields.message.date);
	    }
	    if (main_core.Type.isNumber(fields.message.author_id)) {
	      message.senderId = fields.message.author_id;
	    } else if (main_core.Type.isNumber(fields.message.authorId)) {
	      message.senderId = fields.message.authorId;
	    } else if (main_core.Type.isNumber(fields.message.senderId)) {
	      message.senderId = fields.message.senderId;
	    }
	    if (main_core.Type.isStringFilled(fields.message.status)) {
	      message.status = fields.message.status;
	    }
	    if (main_core.Type.isBoolean(fields.message.sending)) {
	      message.sending = fields.message.sending;
	    }
	    return message;
	  }
	  prepareDraft(fields) {
	    const {
	      draft
	    } = this.getElementState();
	    if (main_core.Type.isString(fields.draft.text)) {
	      draft.text = fields.draft.text;
	    }
	    if (main_core.Type.isStringFilled(draft.text)) {
	      draft.date = new Date();
	    } else {
	      draft.date = null;
	    }
	    return draft;
	  }
	}

	class NotificationsModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'notifications';
	  }
	  getState() {
	    return {
	      collection: new Map(),
	      searchCollection: new Map(),
	      unreadCounter: 0
	    };
	  }
	  getElementState() {
	    return {
	      id: 0,
	      authorId: 0,
	      date: new Date(),
	      title: '',
	      text: '',
	      params: {},
	      replaces: [],
	      notifyButtons: [],
	      sectionCode: im_v2_const.NotificationTypesCodes.simple,
	      read: false,
	      settingName: 'im|default'
	    };
	  }
	  getGetters() {
	    return {
	      getSortedCollection: state => {
	        return [...state.collection.values()].sort(this.sortByType);
	      },
	      getSearchResultCollection: state => {
	        return [...state.searchCollection.values()].sort(this.sortByType);
	      },
	      getConfirmsCount: state => {
	        return [...state.collection.values()].filter(notification => {
	          return notification.sectionCode === im_v2_const.NotificationTypesCodes.confirm;
	        }).length;
	      },
	      getById: state => notificationId => {
	        if (main_core.Type.isString(notificationId)) {
	          notificationId = Number.parseInt(notificationId, 10);
	        }
	        const existingItem = state.collection.get(notificationId);
	        if (!existingItem) {
	          return false;
	        }
	        return existingItem;
	      },
	      getCounter: state => {
	        return state.unreadCounter;
	      }
	    };
	  }
	  getActions() {
	    return {
	      initialSet: (store, payload) => {
	        if (main_core.Type.isNumber(payload.total_unread_count)) {
	          store.commit('setCounter', payload.total_unread_count);
	        }
	        if (!main_core.Type.isArrayFilled(payload.notifications)) {
	          return;
	        }
	        const itemsToUpdate = [];
	        const itemsToAdd = [];
	        const currentUserId = im_v2_application_core.Core.getUserId();
	        payload.notifications.map(element => {
	          return NotificationsModel.validate(element, currentUserId);
	        }).forEach(element => {
	          const existingItem = store.state.collection.get(element.id);
	          if (existingItem) {
	            itemsToUpdate.push({
	              id: existingItem.id,
	              fields: {
	                ...element
	              }
	            });
	          } else {
	            itemsToAdd.push({
	              ...this.getElementState(),
	              ...element
	            });
	          }
	        });
	        if (itemsToAdd.length > 0) {
	          store.commit('add', itemsToAdd);
	        }
	        if (itemsToUpdate.length > 0) {
	          store.commit('update', itemsToUpdate);
	        }
	      },
	      set: (store, payload) => {
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        const itemsToUpdate = [];
	        const itemsToAdd = [];
	        const currentUserId = im_v2_application_core.Core.getUserId();
	        payload.map(element => {
	          return NotificationsModel.validate(element, currentUserId);
	        }).forEach(element => {
	          const existingItem = store.state.collection.get(element.id);
	          if (existingItem) {
	            itemsToUpdate.push({
	              id: existingItem.id,
	              fields: {
	                ...element
	              }
	            });
	          } else {
	            itemsToAdd.push({
	              ...this.getElementState(),
	              ...element
	            });
	          }
	        });
	        if (itemsToAdd.length > 0) {
	          store.commit('add', itemsToAdd);
	          itemsToAdd.forEach(() => {
	            store.commit('increaseCounter');
	          });
	        }
	        if (itemsToUpdate.length > 0) {
	          store.commit('update', itemsToUpdate);
	        }
	      },
	      setSearchResult: (store, payload) => {
	        const itemsToUpdate = [];
	        const itemsToAdd = [];
	        let {
	          notifications
	        } = payload;
	        const skipValidation = !!payload.skipValidation;
	        if (!skipValidation) {
	          const currentUserId = im_v2_application_core.Core.getUserId();
	          notifications = notifications.map(element => {
	            return NotificationsModel.validate(element, currentUserId);
	          });
	        }
	        notifications.forEach(element => {
	          const existingItem = store.state.searchCollection.get(element.id);
	          if (existingItem) {
	            itemsToUpdate.push({
	              id: existingItem.id,
	              fields: {
	                ...element
	              }
	            });
	          } else {
	            itemsToAdd.push({
	              ...this.getElementState(),
	              ...element
	            });
	          }
	        });
	        if (itemsToAdd.length > 0) {
	          store.commit('addSearchResult', itemsToAdd);
	        }
	        if (itemsToUpdate.length > 0) {
	          store.commit('updateSearchResult', itemsToUpdate);
	        }
	      },
	      read: (store, payload) => {
	        payload.ids.forEach(notificationId => {
	          const existingItem = store.state.collection.get(notificationId);
	          if (!existingItem || existingItem.read === payload.read) {
	            return false;
	          }
	          if (payload.read) {
	            store.commit('decreaseCounter');
	          } else {
	            store.commit('increaseCounter');
	          }
	          store.commit('read', {
	            id: existingItem.id,
	            read: payload.read
	          });
	        });
	      },
	      readAll: store => {
	        store.commit('readAll');
	        store.commit('setCounter', 0);
	      },
	      delete: (store, payload) => {
	        const existingItem = store.state.collection.get(payload.id);
	        if (!existingItem) {
	          return;
	        }
	        if (existingItem.read === false) {
	          store.commit('decreaseCounter');
	        }
	        store.commit('delete', {
	          id: existingItem.id
	        });
	      },
	      clearSearchResult: store => {
	        store.commit('clearSearchResult');
	      },
	      setCounter: (store, payload) => {
	        store.commit('setCounter', payload);
	      }
	    };
	  }
	  getMutations() {
	    return {
	      add: (state, payload) => {
	        payload.forEach(item => {
	          state.collection.set(item.id, item);
	        });
	      },
	      addSearchResult: (state, payload) => {
	        payload.forEach(item => {
	          state.searchCollection.set(item.id, item);
	        });
	      },
	      update: (state, payload) => {
	        payload.forEach(item => {
	          state.collection.set(item.id, {
	            ...state.collection.get(item.id),
	            ...item.fields
	          });
	        });
	      },
	      updateSearchResult: (state, payload) => {
	        payload.forEach(item => {
	          state.searchCollection.set(item.id, {
	            ...state.searchCollection.get(item.id),
	            ...item.fields
	          });
	        });
	      },
	      delete: (state, payload) => {
	        state.collection.delete(payload.id);
	      },
	      read: (state, payload) => {
	        state.collection.set(payload.id, {
	          ...state.collection.get(payload.id),
	          read: payload.read
	        });
	      },
	      readAll: state => {
	        [...state.collection.values()].forEach(item => {
	          if (!item.read) {
	            item.read = true;
	          }
	        });
	      },
	      setCounter: (state, payload) => {
	        state.unreadCounter = Number.parseInt(payload, 10);
	      },
	      decreaseCounter: state => {
	        if (state.unreadCounter > 0) {
	          state.unreadCounter--;
	        }
	      },
	      increaseCounter: state => {
	        state.unreadCounter++;
	      },
	      clearSearchResult: state => {
	        state.searchCollection.clear();
	      }
	    };
	  }
	  static validate(fields) {
	    const result = {};
	    if (main_core.Type.isString(fields.id) || main_core.Type.isNumber(fields.id)) {
	      result.id = fields.id;
	    }
	    if (main_core.Type.isNumber(fields.author_id)) {
	      result.authorId = fields.author_id;
	    } else if (main_core.Type.isNumber(fields.userId)) {
	      result.authorId = fields.userId;
	    }
	    if (!main_core.Type.isNil(fields.date)) {
	      result.date = im_v2_lib_utils.Utils.date.cast(fields.date);
	    }
	    if (main_core.Type.isString(fields.notify_title)) {
	      result.title = fields.notify_title;
	    } else if (main_core.Type.isString(fields.title)) {
	      result.title = fields.title;
	    }
	    if (main_core.Type.isString(fields.text) || main_core.Type.isNumber(fields.text)) {
	      result.text = main_core.Text.decode(fields.text.toString());
	    }
	    if (main_core.Type.isObjectLike(fields.params)) {
	      result.params = fields.params;
	    }
	    if (main_core.Type.isArray(fields.replaces)) {
	      result.replaces = fields.replaces;
	    }
	    if (!main_core.Type.isNil(fields.notify_buttons)) {
	      result.notifyButtons = JSON.parse(fields.notify_buttons);
	    } else if (!main_core.Type.isNil(fields.buttons)) {
	      result.notifyButtons = fields.buttons.map(button => {
	        return {
	          COMMAND: 'notifyConfirm',
	          COMMAND_PARAMS: `${result.id}|${button.VALUE}`,
	          TEXT: `${button.TITLE}`,
	          TYPE: 'BUTTON',
	          DISPLAY: 'LINE',
	          BG_COLOR: button.VALUE === 'Y' ? '#8bc84b' : '#ef4b57',
	          TEXT_COLOR: '#fff'
	        };
	      });
	    }
	    if (fields.notify_type === im_v2_const.NotificationTypesCodes.confirm || fields.type === im_v2_const.NotificationTypesCodes.confirm) {
	      result.sectionCode = im_v2_const.NotificationTypesCodes.confirm;
	    } else {
	      result.sectionCode = im_v2_const.NotificationTypesCodes.simple;
	    }
	    if (!main_core.Type.isNil(fields.notify_read)) {
	      result.read = fields.notify_read === 'Y';
	    } else if (!main_core.Type.isNil(fields.read)) {
	      result.read = fields.read === 'Y';
	    }
	    if (main_core.Type.isString(fields.setting_name)) {
	      result.settingName = fields.setting_name;
	    } else if (main_core.Type.isString(fields.settingName)) {
	      result.settingName = fields.settingName;
	    }
	    return result;
	  }
	  sortByType(a, b) {
	    if (a.sectionCode === im_v2_const.NotificationTypesCodes.confirm && b.sectionCode !== im_v2_const.NotificationTypesCodes.confirm) {
	      return -1;
	    } else if (a.sectionCode !== im_v2_const.NotificationTypesCodes.confirm && b.sectionCode === im_v2_const.NotificationTypesCodes.confirm) {
	      return 1;
	    } else {
	      return b.id - a.id;
	    }
	  }
	}

	class LinksModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {},
	      counters: {}
	    };
	  }
	  getGetters() {
	    return {
	      get: state => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        return [...state.collection[chatId].values()].sort((a, b) => b.id - a.id);
	      },
	      getSize: state => chatId => {
	        if (!state.collection[chatId]) {
	          return 0;
	        }
	        return state.collection[chatId].size;
	      },
	      getCounter: state => chatId => {
	        if (!state.counters[chatId]) {
	          return 0;
	        }
	        return state.counters[chatId];
	      }
	    };
	  }
	  getElementState() {
	    return {
	      id: 0,
	      messageId: 0,
	      chatId: 0,
	      authorId: 0,
	      source: '',
	      date: new Date(),
	      richData: {
	        id: null,
	        description: null,
	        link: null,
	        name: null,
	        previewUrl: null,
	        type: null
	      }
	    };
	  }
	  getActions() {
	    return {
	      setCounter: (store, payload) => {
	        if (!main_core.Type.isNumber(payload.counter) || !main_core.Type.isNumber(payload.chatId)) {
	          return;
	        }
	        store.commit('setCounter', payload);
	      },
	      set: (store, payload) => {
	        const {
	          chatId,
	          links
	        } = payload;
	        if (!main_core.Type.isArrayFilled(links) || !main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collection[chatId]) {
	          store.state.collection[chatId] = new Map();
	        }
	        links.forEach(link => {
	          const preparedLink = {
	            ...this.getElementState(),
	            ...this.validate(link)
	          };
	          store.commit('add', {
	            chatId,
	            link: preparedLink
	          });
	        });
	      },
	      delete: (store, payload) => {
	        const {
	          chatId,
	          id
	        } = payload;
	        if (!main_core.Type.isNumber(id) || !main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collection[chatId] || !store.state.collection[chatId].has(id)) {
	          return;
	        }
	        store.commit('delete', {
	          chatId,
	          id
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      setCounter: (state, payload) => {
	        const {
	          chatId,
	          counter
	        } = payload;
	        state.counters[chatId] = counter;
	      },
	      add: (state, payload) => {
	        const {
	          chatId,
	          link
	        } = payload;
	        state.collection[chatId].set(link.id, link);
	      },
	      delete: (state, payload) => {
	        const {
	          chatId,
	          id
	        } = payload;
	        state.collection[chatId].delete(id);
	        state.counters[chatId]--;
	      }
	    };
	  }
	  validate(fields) {
	    const result = {
	      richData: {}
	    };
	    if (main_core.Type.isNumber(fields.id)) {
	      result.id = fields.id;
	    }
	    if (main_core.Type.isNumber(fields.messageId)) {
	      result.messageId = fields.messageId;
	    }
	    if (main_core.Type.isNumber(fields.chatId)) {
	      result.chatId = fields.chatId;
	    }
	    if (main_core.Type.isNumber(fields.authorId)) {
	      result.authorId = fields.authorId;
	    }
	    if (main_core.Type.isString(fields.url.source)) {
	      result.source = fields.url.source;
	    }
	    if (main_core.Type.isString(fields.dateCreate)) {
	      result.date = im_v2_lib_utils.Utils.date.cast(fields.dateCreate);
	    }
	    if (main_core.Type.isPlainObject(fields.url.richData)) {
	      result.richData = this.validateRichData(fields.url.richData);
	    }
	    return result;
	  }
	  validateRichData(richData) {
	    const result = {};
	    if (main_core.Type.isNumber(richData.id)) {
	      result.id = richData.id;
	    }
	    if (main_core.Type.isString(richData.description)) {
	      result.description = richData.description;
	    }
	    if (main_core.Type.isString(richData.link)) {
	      result.link = richData.link;
	    }
	    if (main_core.Type.isString(richData.name)) {
	      result.name = richData.name;
	    }
	    if (main_core.Type.isString(richData.previewUrl)) {
	      result.previewUrl = richData.previewUrl;
	    }
	    if (main_core.Type.isString(richData.type)) {
	      result.type = richData.type;
	    }
	    return result;
	  }
	}

	class FavoritesModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {},
	      counters: {}
	    };
	  }
	  getElementState() {
	    return {
	      id: 0,
	      messageId: 0,
	      chatId: 0,
	      authorId: 0,
	      date: new Date()
	    };
	  }
	  getGetters() {
	    return {
	      get: state => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        return [...state.collection[chatId].values()].sort((a, b) => b.id - a.id);
	      },
	      getSize: state => chatId => {
	        if (!state.collection[chatId]) {
	          return 0;
	        }
	        return state.collection[chatId].size;
	      },
	      getCounter: state => chatId => {
	        if (state.counters[chatId]) {
	          return state.counters[chatId];
	        }
	        return 0;
	      },
	      isFavoriteMessage: state => (chatId, messageId) => {
	        if (!state.collection[chatId]) {
	          return false;
	        }
	        const chatFavorites = Object.fromEntries(state.collection[chatId]);
	        const targetMessage = Object.values(chatFavorites).find(element => element.messageId === messageId);
	        return !!targetMessage;
	      }
	    };
	  }
	  getActions() {
	    return {
	      setCounter: (store, payload) => {
	        if (!main_core.Type.isNumber(payload.counter) || !main_core.Type.isNumber(payload.chatId)) {
	          return;
	        }
	        store.commit('setCounter', payload);
	      },
	      set: (store, payload) => {
	        if (main_core.Type.isNumber(payload.favorites)) {
	          payload.favorites = [payload.favorites];
	        }
	        const {
	          chatId,
	          favorites
	        } = payload;
	        if (!main_core.Type.isArrayFilled(favorites) || !main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collection[chatId]) {
	          store.state.collection[chatId] = new Map();
	        }
	        favorites.forEach(favorite => {
	          const preparedFavoriteMessage = {
	            ...this.getElementState(),
	            ...this.validate(favorite)
	          };
	          store.commit('add', {
	            chatId,
	            favorite: preparedFavoriteMessage
	          });
	        });
	      },
	      delete: (store, payload) => {
	        const {
	          chatId,
	          id
	        } = payload;
	        if (!main_core.Type.isNumber(id) || !main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collection[chatId] || !store.state.collection[chatId].has(id)) {
	          return;
	        }
	        store.commit('delete', {
	          chatId,
	          id
	        });
	      },
	      deleteByMessageId: (store, payload) => {
	        const {
	          chatId,
	          messageId
	        } = payload;
	        if (!store.state.collection[chatId]) {
	          return;
	        }
	        const chatCollection = store.state.collection[chatId];
	        let targetLinkId = null;
	        for (const [linkId, linkObject] of chatCollection) {
	          if (linkObject.messageId === messageId) {
	            targetLinkId = linkId;
	            break;
	          }
	        }
	        if (!targetLinkId) {
	          return;
	        }
	        store.commit('delete', {
	          chatId,
	          id: targetLinkId
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      setCounter: (state, payload) => {
	        const {
	          chatId,
	          counter
	        } = payload;
	        state.counters[chatId] = counter;
	      },
	      add: (state, payload) => {
	        const {
	          chatId,
	          favorite
	        } = payload;
	        state.collection[chatId].set(favorite.id, favorite);
	      },
	      delete: (state, payload) => {
	        const {
	          chatId,
	          id
	        } = payload;
	        state.collection[chatId].delete(id);
	        state.counters[chatId]--;
	      }
	    };
	  }
	  validate(fields) {
	    const result = {};
	    if (main_core.Type.isNumber(fields.id)) {
	      result.id = fields.id;
	    }
	    if (main_core.Type.isNumber(fields.messageId)) {
	      result.messageId = fields.messageId;
	    }
	    if (main_core.Type.isNumber(fields.chatId)) {
	      result.chatId = fields.chatId;
	    }
	    if (main_core.Type.isNumber(fields.authorId)) {
	      result.authorId = fields.authorId;
	    }
	    if (main_core.Type.isString(fields.dateCreate)) {
	      result.date = im_v2_lib_utils.Utils.date.cast(fields.dateCreate);
	    }
	    return result;
	  }
	}

	class MembersModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getGetters() {
	    return {
	      get: state => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        return [...state.collection[chatId]];
	      },
	      getSize: state => chatId => {
	        if (!state.collection[chatId]) {
	          return 0;
	        }
	        return state.collection[chatId].size;
	      }
	    };
	  }
	  getActions() {
	    return {
	      set: (store, payload) => {
	        const {
	          chatId,
	          users
	        } = payload;
	        if (!main_core.Type.isArray(users) || !main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (users.length > 0) {
	          store.commit('set', {
	            chatId,
	            users
	          });
	        }
	      },
	      delete: (store, payload) => {
	        const {
	          chatId,
	          userId
	        } = payload;
	        if (!main_core.Type.isNumber(chatId) || !main_core.Type.isNumber(userId)) {
	          return;
	        }
	        if (!store.state.collection[chatId]) {
	          return;
	        }
	        store.commit('delete', {
	          userId,
	          chatId
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      set: (state, payload) => {
	        if (!state.collection[payload.chatId]) {
	          state.collection[payload.chatId] = new Set(payload.users);
	        } else {
	          payload.users.forEach(id => {
	            state.collection[payload.chatId].add(id);
	          });
	        }
	      },
	      delete: (state, payload) => {
	        const {
	          chatId,
	          userId
	        } = payload;
	        state.collection[chatId].delete(userId);
	      }
	    };
	  }
	}

	class TasksModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getElementState() {
	    return {
	      id: 0,
	      messageId: 0,
	      chatId: 0,
	      authorId: 0,
	      date: new Date(),
	      task: {
	        id: 0,
	        title: '',
	        creatorId: 0,
	        responsibleId: 0,
	        status: 0,
	        statusTitle: '',
	        deadline: new Date(),
	        state: '',
	        color: '',
	        source: ''
	      }
	    };
	  }
	  getGetters() {
	    return {
	      get: state => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        return [...state.collection[chatId].values()].sort((a, b) => b.id - a.id);
	      },
	      getSize: state => chatId => {
	        if (!state.collection[chatId]) {
	          return 0;
	        }
	        return state.collection[chatId].size;
	      }
	    };
	  }
	  getActions() {
	    return {
	      set: (store, payload) => {
	        const {
	          chatId,
	          tasks
	        } = payload;
	        if (!main_core.Type.isArrayFilled(tasks) || !main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collection[chatId]) {
	          store.state.collection[chatId] = new Map();
	        }
	        tasks.forEach(task => {
	          const preparedTask = {
	            ...this.getElementState(),
	            ...this.validate(task)
	          };
	          store.commit('add', {
	            chatId,
	            task: preparedTask
	          });
	        });
	      },
	      delete: (store, payload) => {
	        const {
	          chatId,
	          id
	        } = payload;
	        if (!main_core.Type.isNumber(chatId) || !main_core.Type.isNumber(id)) {
	          return;
	        }
	        if (!store.state.collection[chatId]) {
	          return;
	        }
	        store.commit('delete', {
	          id,
	          chatId
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      add: (state, payload) => {
	        const {
	          chatId,
	          task
	        } = payload;
	        state.collection[chatId].set(task.id, task);
	      },
	      delete: (state, payload) => {
	        const {
	          id,
	          chatId
	        } = payload;
	        state.collection[chatId].delete(id);
	      }
	    };
	  }
	  validate(fields) {
	    const result = {
	      task: {}
	    };
	    if (main_core.Type.isNumber(fields.id)) {
	      result.id = fields.id;
	    }
	    if (main_core.Type.isNumber(fields.messageId)) {
	      result.messageId = fields.messageId;
	    }
	    if (main_core.Type.isNumber(fields.chatId)) {
	      result.chatId = fields.chatId;
	    }
	    if (main_core.Type.isNumber(fields.authorId)) {
	      result.authorId = fields.authorId;
	    }
	    if (main_core.Type.isString(fields.dateCreate)) {
	      result.date = im_v2_lib_utils.Utils.date.cast(fields.dateCreate);
	    }
	    if (main_core.Type.isPlainObject(fields.task)) {
	      result.task = this.validateTask(fields.task);
	    }
	    return result;
	  }
	  validateTask(task) {
	    const result = {};
	    if (main_core.Type.isNumber(task.id)) {
	      result.id = task.id;
	    }
	    if (main_core.Type.isString(task.title)) {
	      result.title = task.title;
	    }
	    if (main_core.Type.isNumber(task.creatorId)) {
	      result.creatorId = task.creatorId;
	    }
	    if (main_core.Type.isNumber(task.responsibleId)) {
	      result.responsibleId = task.responsibleId;
	    }
	    if (main_core.Type.isNumber(task.status)) {
	      result.status = task.status;
	    }
	    if (main_core.Type.isString(task.statusTitle)) {
	      result.statusTitle = task.statusTitle;
	    }
	    if (main_core.Type.isString(task.deadline)) {
	      result.deadline = im_v2_lib_utils.Utils.date.cast(task.deadline);
	    }
	    if (main_core.Type.isString(task.state)) {
	      result.state = task.state;
	    }
	    if (main_core.Type.isString(task.color)) {
	      result.color = task.color;
	    }
	    if (main_core.Type.isString(task.source)) {
	      result.source = task.source;
	    }
	    return result;
	  }
	}

	class MeetingsModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getElementState() {
	    return {
	      id: 0,
	      messageId: 0,
	      chatId: 0,
	      authorId: 0,
	      date: new Date(),
	      meeting: {
	        id: 0,
	        title: '',
	        dateFrom: new Date(),
	        dateTo: new Date(),
	        source: ''
	      }
	    };
	  }
	  getGetters() {
	    return {
	      get: state => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        return [...state.collection[chatId].values()].sort((a, b) => b.id - a.id);
	      },
	      getSize: state => chatId => {
	        if (!state.collection[chatId]) {
	          return 0;
	        }
	        return state.collection[chatId].size;
	      }
	    };
	  }
	  getActions() {
	    return {
	      set: (store, payload) => {
	        const {
	          chatId,
	          meetings
	        } = payload;
	        if (!main_core.Type.isArrayFilled(meetings) || !main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collection[chatId]) {
	          store.state.collection[chatId] = new Map();
	        }
	        meetings.forEach(meeting => {
	          const preparedMeeting = {
	            ...this.getElementState(),
	            ...this.validate(meeting)
	          };
	          store.commit('add', {
	            chatId,
	            meeting: preparedMeeting
	          });
	        });
	      },
	      delete: (store, payload) => {
	        const {
	          chatId,
	          id
	        } = payload;
	        if (!main_core.Type.isNumber(chatId) || !main_core.Type.isNumber(id)) {
	          return;
	        }
	        if (!store.state.collection[chatId]) {
	          return;
	        }
	        store.commit('delete', {
	          id,
	          chatId
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      add: (state, payload) => {
	        const {
	          chatId,
	          meeting
	        } = payload;
	        state.collection[chatId].set(meeting.id, meeting);
	      },
	      delete: (state, payload) => {
	        const {
	          id,
	          chatId
	        } = payload;
	        state.collection[chatId].delete(id);
	      }
	    };
	  }
	  validate(fields) {
	    const result = {
	      meeting: {}
	    };
	    if (main_core.Type.isNumber(fields.id)) {
	      result.id = fields.id;
	    }
	    if (main_core.Type.isNumber(fields.messageId)) {
	      result.messageId = fields.messageId;
	    }
	    if (main_core.Type.isNumber(fields.chatId)) {
	      result.chatId = fields.chatId;
	    }
	    if (main_core.Type.isNumber(fields.authorId)) {
	      result.authorId = fields.authorId;
	    }
	    if (main_core.Type.isString(fields.dateCreate)) {
	      result.date = im_v2_lib_utils.Utils.date.cast(fields.dateCreate);
	    }
	    if (main_core.Type.isPlainObject(fields.calendar)) {
	      result.meeting = this.validateMeeting(fields.calendar);
	    }
	    return result;
	  }
	  validateMeeting(meeting) {
	    const result = {};
	    if (main_core.Type.isNumber(meeting.id)) {
	      result.id = meeting.id;
	    }
	    if (main_core.Type.isString(meeting.title)) {
	      result.title = meeting.title;
	    }
	    if (main_core.Type.isString(meeting.dateFrom)) {
	      result.dateFrom = im_v2_lib_utils.Utils.date.cast(meeting.dateFrom);
	    }
	    if (main_core.Type.isString(meeting.dateTo)) {
	      result.dateTo = im_v2_lib_utils.Utils.date.cast(meeting.dateTo);
	    }
	    if (main_core.Type.isString(meeting.source)) {
	      result.source = meeting.source;
	    }
	    return result;
	  }
	}

	class FilesModel$1 extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getElementState() {
	    return {
	      id: 0,
	      messageId: 0,
	      chatId: 0,
	      authorId: 0,
	      date: new Date(),
	      fileId: 0
	    };
	  }
	  getGetters() {
	    return {
	      get: state => (chatId, subType) => {
	        if (!state.collection[chatId] || !state.collection[chatId][subType]) {
	          return [];
	        }
	        return [...state.collection[chatId][subType].values()].sort((a, b) => b.id - a.id);
	      },
	      getLatest: (state, getters, rootState, rootGetters) => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        let media = [];
	        let audio = [];
	        let documents = [];
	        let other = [];
	        if (state.collection[chatId][im_v2_const.SidebarFileTypes.media]) {
	          media = [...state.collection[chatId][im_v2_const.SidebarFileTypes.media].values()];
	        }
	        if (state.collection[chatId][im_v2_const.SidebarFileTypes.audio]) {
	          audio = [...state.collection[chatId][im_v2_const.SidebarFileTypes.audio].values()];
	        }
	        if (state.collection[chatId][im_v2_const.SidebarFileTypes.document]) {
	          documents = [...state.collection[chatId][im_v2_const.SidebarFileTypes.document].values()];
	        }
	        if (state.collection[chatId][im_v2_const.SidebarFileTypes.other]) {
	          other = [...state.collection[chatId][im_v2_const.SidebarFileTypes.other].values()];
	        }
	        const sortedFlatCollection = [media, audio, documents, other].flat().sort((a, b) => b.id - a.id);
	        return this.getTopThreeCompletedFiles(sortedFlatCollection, rootGetters);
	      },
	      getLatestUnsorted: (state, getters, rootState, rootGetters) => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        let unsorted = [];
	        if (state.collection[chatId][im_v2_const.SidebarFileTypes.fileUnsorted]) {
	          unsorted = [...state.collection[chatId][im_v2_const.SidebarFileTypes.fileUnsorted].values()];
	        }
	        const sortedCollection = unsorted.sort((a, b) => b.id - a.id);
	        return this.getTopThreeCompletedFiles(sortedCollection, rootGetters);
	      },
	      getSize: state => (chatId, subType) => {
	        if (!state.collection[chatId] || !state.collection[chatId][subType]) {
	          return 0;
	        }
	        return state.collection[chatId][subType].size;
	      }
	    };
	  }
	  getActions() {
	    return {
	      set: (store, payload) => {
	        const {
	          chatId,
	          files
	        } = payload;
	        if (!main_core.Type.isArrayFilled(files) || !main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collection[chatId]) {
	          store.state.collection[chatId] = {};
	        }
	        files.forEach(file => {
	          const preparedFile = {
	            ...this.getElementState(),
	            ...this.validate(file)
	          };
	          const {
	            subType
	          } = file;
	          store.commit('add', {
	            chatId,
	            subType,
	            file: preparedFile
	          });
	        });
	      },
	      delete: (store, payload) => {
	        const {
	          chatId,
	          id
	        } = payload;
	        if (!main_core.Type.isNumber(id) || !main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collection[chatId]) {
	          return;
	        }
	        store.commit('delete', {
	          chatId,
	          id
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      add: (state, payload) => {
	        const {
	          chatId,
	          file,
	          subType
	        } = payload;
	        if (!state.collection[chatId][subType]) {
	          state.collection[chatId][subType] = new Map();
	        }
	        state.collection[chatId][subType].set(file.id, file);
	      },
	      delete: (state, payload) => {
	        const {
	          chatId,
	          id
	        } = payload;
	        Object.values(im_v2_const.SidebarFileTypes).forEach(subType => {
	          if (state.collection[chatId][subType] && state.collection[chatId][subType].has(id)) {
	            state.collection[chatId][subType].delete(id);
	          }
	        });
	      }
	    };
	  }
	  validate(fields) {
	    const result = {};
	    if (main_core.Type.isNumber(fields.id)) {
	      result.id = fields.id;
	    }
	    if (main_core.Type.isNumber(fields.messageId)) {
	      result.messageId = fields.messageId;
	    }
	    if (main_core.Type.isNumber(fields.chatId)) {
	      result.chatId = fields.chatId;
	    }
	    if (main_core.Type.isNumber(fields.authorId)) {
	      result.authorId = fields.authorId;
	    }
	    if (main_core.Type.isString(fields.dateCreate)) {
	      result.date = im_v2_lib_utils.Utils.date.cast(fields.dateCreate);
	    } else if (main_core.Type.isString(fields.date)) {
	      result.date = im_v2_lib_utils.Utils.date.cast(fields.date);
	    }
	    result.fileId = main_core.Type.isNumber(fields.fileId) ? fields.fileId : result.id;
	    return result;
	  }
	  getTopThreeCompletedFiles(collection, rootGetters) {
	    return collection.filter(sidebarFile => {
	      const file = rootGetters['files/get'](sidebarFile.fileId, true);
	      return file.progress === 100;
	    }).slice(0, 3);
	  }
	}

	class SidebarModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'sidebar';
	  }
	  getNestedModules() {
	    return {
	      members: MembersModel,
	      links: LinksModel,
	      favorites: FavoritesModel,
	      tasks: TasksModel,
	      meetings: MeetingsModel,
	      files: FilesModel$1
	    };
	  }
	  getState() {
	    return {
	      initedList: new Set(),
	      isFilesMigrated: false,
	      isLinksMigrated: false
	    };
	  }
	  getGetters() {
	    return {
	      isInited: state => chatId => {
	        return state.initedList.has(chatId);
	      }
	    };
	  }
	  getActions() {
	    return {
	      setInited: (store, chatId) => {
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        store.commit('setInited', chatId);
	      },
	      setFilesMigrated: (store, value) => {
	        if (!main_core.Type.isBoolean(value)) {
	          return;
	        }
	        store.commit('setFilesMigrated', value);
	      },
	      setLinksMigrated: (store, value) => {
	        if (!main_core.Type.isBoolean(value)) {
	          return;
	        }
	        store.commit('setLinksMigrated', value);
	      }
	    };
	  }
	  getMutations() {
	    return {
	      setInited: (state, payload) => {
	        state.initedList.add(payload);
	      },
	      setFilesMigrated: (state, payload) => {
	        state.isFilesMigrated = payload;
	      },
	      setLinksMigrated: (state, payload) => {
	        state.isLinksMigrated = payload;
	      }
	    };
	  }
	}

	var _validate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("validate");
	var _validateOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("validateOptions");
	var _validateLoadConfiguration = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("validateLoadConfiguration");
	class MarketModel extends ui_vue3_vuex.BuilderModel {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _validateLoadConfiguration, {
	      value: _validateLoadConfiguration2
	    });
	    Object.defineProperty(this, _validateOptions, {
	      value: _validateOptions2
	    });
	    Object.defineProperty(this, _validate, {
	      value: _validate2
	    });
	  }
	  getName() {
	    return 'market';
	  }
	  getState() {
	    return {
	      collection: new Map(),
	      placementCollection: {
	        [im_v2_const.PlacementType.contextMenu]: new Set(),
	        [im_v2_const.PlacementType.navigation]: new Set(),
	        [im_v2_const.PlacementType.textarea]: new Set(),
	        [im_v2_const.PlacementType.sidebar]: new Set(),
	        [im_v2_const.PlacementType.smilesSelector]: new Set()
	      }
	    };
	  }
	  getElementState() {
	    return {
	      id: 0,
	      title: '',
	      options: {
	        role: '',
	        extranet: '',
	        context: null,
	        width: null,
	        height: null,
	        color: null,
	        iconName: null
	      },
	      placement: '',
	      order: 0,
	      loadConfiguration: {
	        ID: 0,
	        PLACEMENT: '',
	        PLACEMENT_ID: 0
	      }
	    };
	  }
	  getGetters() {
	    return {
	      getByPlacement: state => placement => {
	        const appIds = [...state.placementCollection[placement].values()];
	        return appIds.map(id => {
	          return state.collection.get(id);
	        });
	      },
	      getById: state => id => {
	        return state.collection.get(id);
	      }
	    };
	  }
	  getActions() {
	    return {
	      set: (store, payload) => {
	        const {
	          items
	        } = payload;
	        items.forEach(item => {
	          store.commit('setPlacementCollection', {
	            placement: item.placement,
	            id: item.id
	          });
	          store.commit('setCollection', item);
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      setPlacementCollection: (state, payload) => {
	        state.placementCollection[payload.placement].add(payload.id);
	      },
	      setCollection: (state, payload) => {
	        state.collection.set(payload.id, {
	          ...this.getElementState(),
	          ...babelHelpers.classPrivateFieldLooseBase(this, _validate)[_validate](payload)
	        });
	      }
	    };
	  }
	}
	function _validate2(app) {
	  const result = {};
	  if (main_core.Type.isNumber(app.id) || main_core.Type.isStringFilled(app.id)) {
	    result.id = app.id.toString();
	  }
	  if (main_core.Type.isString(app.title)) {
	    result.title = app.title;
	  }
	  result.options = babelHelpers.classPrivateFieldLooseBase(this, _validateOptions)[_validateOptions](app.options);
	  if (main_core.Type.isString(app.placement)) {
	    result.placement = app.placement;
	  }
	  if (main_core.Type.isNumber(app.order)) {
	    result.order = app.order;
	  }
	  result.loadConfiguration = babelHelpers.classPrivateFieldLooseBase(this, _validateLoadConfiguration)[_validateLoadConfiguration](app.loadConfiguration);
	  return result;
	}
	function _validateOptions2(options) {
	  const result = {
	    context: null,
	    width: null,
	    height: null,
	    color: null,
	    iconName: null
	  };
	  if (!main_core.Type.isPlainObject(options)) {
	    return result;
	  }
	  if (main_core.Type.isArrayFilled(options.context)) {
	    result.context = options.context;
	  }
	  if (main_core.Type.isNumber(options.width)) {
	    result.width = options.width;
	  }
	  if (main_core.Type.isNumber(options.height)) {
	    result.height = options.height;
	  }
	  if (main_core.Type.isStringFilled(options.color)) {
	    result.color = options.color;
	  }
	  if (main_core.Type.isStringFilled(options.iconName)) {
	    result.iconName = options.iconName;
	  }
	  return result;
	}
	function _validateLoadConfiguration2(configuration) {
	  const result = {
	    ID: 0,
	    PLACEMENT: '',
	    PLACEMENT_ID: 0
	  };
	  if (!main_core.Type.isPlainObject(configuration)) {
	    return result;
	  }
	  if (main_core.Type.isNumber(configuration.ID)) {
	    result.ID = configuration.ID;
	  }
	  if (main_core.Type.isStringFilled(configuration.PLACEMENT)) {
	    result.PLACEMENT = configuration.PLACEMENT;
	  }
	  if (main_core.Type.isNumber(configuration.PLACEMENT_ID)) {
	    result.PLACEMENT_ID = configuration.PLACEMENT_ID;
	  }
	  return result;
	}

	exports.ApplicationModel = ApplicationModel;
	exports.MessagesModel = MessagesModel;
	exports.DialoguesModel = DialoguesModel;
	exports.UsersModel = UsersModel;
	exports.FilesModel = FilesModel;
	exports.RecentModel = RecentModel;
	exports.NotificationsModel = NotificationsModel;
	exports.SidebarModel = SidebarModel;
	exports.MarketModel = MarketModel;

}((this.BX.Messenger.v2.Model = this.BX.Messenger.v2.Model || {}),BX.Event,BX.Messenger.v2.Lib,BX.Ui,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX,BX.Vue3.Vuex,BX.Messenger.v2.Const));
//# sourceMappingURL=registry.bundle.js.map
