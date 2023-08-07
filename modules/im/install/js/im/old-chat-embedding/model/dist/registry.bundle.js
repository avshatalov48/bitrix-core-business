/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Embedding = this.BX.Messenger.Embedding || {};
(function (exports,main_core,ui_vue3_vuex,im_oldChatEmbedding_application_core,im_oldChatEmbedding_const,im_oldChatEmbedding_lib_utils) {
	'use strict';

	class ApplicationModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'application';
	  }
	  getState() {
	    return {
	      common: {
	        host: this.getVariable('common.host', `${location.protocol}//${location.host}`),
	        siteId: this.getVariable('common.siteId', 'default'),
	        userId: this.getVariable('common.userId', 0),
	        languageId: this.getVariable('common.languageId', 'en')
	      },
	      dialog: {
	        dialogId: this.getVariable('dialog.dialogId', '0'),
	        chatId: this.getVariable('dialog.chatId', 0),
	        diskFolderId: this.getVariable('dialog.diskFolderId', 0),
	        messageLimit: this.getVariable('dialog.messageLimit', 20),
	        enableReadMessages: this.getVariable('dialog.enableReadMessages', true)
	      },
	      disk: {
	        enabled: false,
	        maxFileSize: 5242880
	      },
	      call: {
	        serverEnabled: false,
	        maxParticipants: 24
	      },
	      mobile: {
	        keyboardShow: false
	      },
	      device: {
	        type: this.getVariable('device.type', im_oldChatEmbedding_const.DeviceType.desktop),
	        orientation: this.getVariable('device.orientation', im_oldChatEmbedding_const.DeviceOrientation.portrait)
	      },
	      options: {
	        quoteEnable: this.getVariable('options.quoteEnable', true),
	        quoteFromRight: this.getVariable('options.quoteFromRight', true),
	        autoplayVideo: this.getVariable('options.autoplayVideo', true),
	        darkTheme: this.getVariable('options.darkTheme', false),
	        bigSmileEnable: this.getVariable('options.bigSmileEnable', true)
	      },
	      error: {
	        active: false,
	        code: '',
	        description: ''
	      }
	    };
	  }
	  getGetters() {
	    return {
	      getOption: state => optionName => {
	        if (!im_oldChatEmbedding_const.Settings[optionName]) {
	          return false;
	        }
	        return state.options[optionName];
	      }
	    };
	  }
	  getActions() {
	    return {
	      setOptions: (store, payload) => {
	        if (!main_core.Type.isPlainObject(payload)) {
	          return false;
	        }
	        payload = this.validateOptions(payload);
	        Object.entries(payload).forEach(([option, value]) => {
	          store.commit('setOptions', {
	            option,
	            value
	          });
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
	      },
	      setOptions: (state, payload) => {
	        state.options[payload.option] = payload.value;
	      }
	    };
	  }
	  validate(fields) {
	    const result = {};
	    if (typeof fields.common === 'object' && fields.common) {
	      result.common = {};
	      if (typeof fields.common.userId === 'number') {
	        result.common.userId = fields.common.userId;
	      }
	      if (typeof fields.common.languageId === 'string') {
	        result.common.languageId = fields.common.languageId;
	      }
	    }
	    if (typeof fields.dialog === 'object' && fields.dialog) {
	      result.dialog = {};
	      if (typeof fields.dialog.dialogId === 'number') {
	        result.dialog.dialogId = fields.dialog.dialogId.toString();
	        result.dialog.chatId = 0;
	      } else if (typeof fields.dialog.dialogId === 'string') {
	        result.dialog.dialogId = fields.dialog.dialogId;
	        if (typeof fields.dialog.chatId !== 'number') {
	          let chatId = fields.dialog.dialogId;
	          if (chatId.startsWith('chat')) {
	            chatId = fields.dialog.dialogId.substr(4);
	          }
	          chatId = parseInt(chatId);
	          result.dialog.chatId = !isNaN(chatId) ? chatId : 0;
	          fields.dialog.chatId = result.dialog.chatId;
	        }
	      }
	      if (typeof fields.dialog.chatId === 'number') {
	        result.dialog.chatId = fields.dialog.chatId;
	      }
	      if (typeof fields.dialog.diskFolderId === 'number') {
	        result.dialog.diskFolderId = fields.dialog.diskFolderId;
	      }
	      if (typeof fields.dialog.messageLimit === 'number') {
	        result.dialog.messageLimit = fields.dialog.messageLimit;
	      }
	      if (typeof fields.dialog.enableReadMessages === 'boolean') {
	        result.dialog.enableReadMessages = fields.dialog.enableReadMessages;
	      }
	    }
	    if (typeof fields.disk === 'object' && fields.disk) {
	      result.disk = {};
	      if (typeof fields.disk.enabled === 'boolean') {
	        result.disk.enabled = fields.disk.enabled;
	      }
	      if (typeof fields.disk.maxFileSize === 'number') {
	        result.disk.maxFileSize = fields.disk.maxFileSize;
	      }
	    }
	    if (typeof fields.call === 'object' && fields.call) {
	      result.call = {};
	      if (typeof fields.call.serverEnabled === 'boolean') {
	        result.call.serverEnabled = fields.call.serverEnabled;
	      }
	      if (typeof fields.call.maxParticipants === 'number') {
	        result.call.maxParticipants = fields.call.maxParticipants;
	      }
	    }
	    if (typeof fields.mobile === 'object' && fields.mobile) {
	      result.mobile = {};
	      if (typeof fields.mobile.keyboardShow === 'boolean') {
	        result.mobile.keyboardShow = fields.mobile.keyboardShow;
	      }
	    }
	    if (typeof fields.device === 'object' && fields.device) {
	      result.device = {};
	      if (typeof fields.device.type === 'string' && typeof im_oldChatEmbedding_const.DeviceType[fields.device.type] !== 'undefined') {
	        result.device.type = fields.device.type;
	      }
	      if (typeof fields.device.orientation === 'string' && typeof im_oldChatEmbedding_const.DeviceOrientation[fields.device.orientation] !== 'undefined') {
	        result.device.orientation = fields.device.orientation;
	      }
	    }
	    if (typeof fields.error === 'object' && fields.error) {
	      if (typeof fields.error.active === 'boolean') {
	        result.error = {
	          active: fields.error.active,
	          code: fields.error.code.toString() || '',
	          description: fields.error.description.toString() || ''
	        };
	      }
	    }
	    return result;
	  }
	  validateOptions(fields) {
	    const result = {};
	    if (!main_core.Type.isUndefined(fields.darkTheme) && main_core.Type.isStringFilled(fields.darkTheme)) {
	      if (fields.darkTheme === 'auto' && BX.MessengerProxy) {
	        result.darkTheme = BX.MessengerProxy.isDarkTheme();
	      } else {
	        result.darkTheme = fields.darkTheme === 'dark';
	      }
	    }
	    return result;
	  }
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
	      type: im_oldChatEmbedding_const.DialogType.chat,
	      name: '',
	      description: '',
	      avatar: '',
	      color: im_oldChatEmbedding_const.Color.base,
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
	        return state.collection[dialogId].type === im_oldChatEmbedding_const.DialogType.user;
	      },
	      canLeave: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return false;
	        }
	        const dialog = state.collection[dialogId];
	        const isExternalTelephonyCall = dialog.type === im_oldChatEmbedding_const.DialogType.call;
	        const isUser = dialog.type === im_oldChatEmbedding_const.DialogType.user;
	        if (isExternalTelephonyCall || isUser) {
	          return false;
	        }
	        const currentUserId = im_oldChatEmbedding_application_core.Core.getUserId();
	        const optionToCheck = dialog.owner === currentUserId ? im_oldChatEmbedding_const.ChatOption.leaveOwner : im_oldChatEmbedding_const.ChatOption.leave;
	        return this.store.getters['dialogues/getChatOption'](dialog.type, optionToCheck);
	      },
	      canMute: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return false;
	        }
	        const dialog = state.collection[dialogId];
	        const isUser = dialog.type === im_oldChatEmbedding_const.DialogType.user;
	        const isAnnouncement = dialog.type === im_oldChatEmbedding_const.DialogType.announcement;
	        if (isUser || isAnnouncement) {
	          return null;
	        }
	        return this.store.getters['dialogues/getChatOption'](dialog.type, im_oldChatEmbedding_const.ChatOption.mute);
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
	        const currentUserId = im_oldChatEmbedding_application_core.Core.getUserId();
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
	        const currentUserId = im_oldChatEmbedding_application_core.Core.getUserId();
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
	            date: im_oldChatEmbedding_lib_utils.Utils.date.cast(date)
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
	      result.dateCreate = im_oldChatEmbedding_lib_utils.Utils.date.cast(fields.dateCreate);
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
	      result = this.store.state.application.common.host + avatar;
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
	      item.userName = im_oldChatEmbedding_lib_utils.Utils.text.htmlspecialcharsback(element.userName);
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
	      if (rawFirstViewer.user_id === im_oldChatEmbedding_application_core.Core.getUserId()) {
	        return;
	      }
	      firstViewer = {
	        userId: rawFirstViewer.user_id,
	        userName: rawFirstViewer.user_name,
	        date: im_oldChatEmbedding_lib_utils.Utils.date.cast(rawFirstViewer.date)
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
	      const newType = im_oldChatEmbedding_lib_utils.Utils.text.convertSnakeToCamelCase(type.toLowerCase());
	      result[newType] = {};
	      Object.entries(typeOptions).forEach(([key, value]) => {
	        const newKey = im_oldChatEmbedding_lib_utils.Utils.text.convertSnakeToCamelCase(key.toLowerCase());
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
	      color: im_oldChatEmbedding_const.Color.base,
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
	          return im_oldChatEmbedding_const.UserStatus.mobileOnline;
	        } else if (user.idle) {
	          // away by time
	          return im_oldChatEmbedding_const.UserStatus.idle;
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
	        return im_oldChatEmbedding_lib_utils.Utils.user.getLastDateText(user);
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
	        return main_core.Loc.getMessage('IM_EMBED_MODEL_USERS_DEFAULT_NAME');
	      },
	      getBotType: state => userId => {
	        userId = Number.parseInt(userId, 10);
	        const user = state.collection[userId];
	        if (userId <= 0 || !user || !user.bot || !state.botList[userId]) {
	          return '';
	        }
	        const botType = state.botList[userId].type;
	        if (!im_oldChatEmbedding_const.BotType[botType]) {
	          return im_oldChatEmbedding_const.BotType.bot;
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
	        const currentUserId = this.store.state.application.common.userId;
	        store.commit('update', {
	          id: currentUserId,
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
	        if (im_oldChatEmbedding_lib_utils.Utils.user.isOnline(user.lastActivityDate)) {
	          user.isOnline = true;
	          this.addToOnlineList(user.id);
	        }
	        if (im_oldChatEmbedding_lib_utils.Utils.user.isMobileOnline(user.lastActivityDate, user.mobileLastDate)) {
	          user.isMobileOnline = true;
	          this.addToMobileOnlineList(user.id);
	        }
	        if (user.birthday && im_oldChatEmbedding_lib_utils.Utils.user.isBirthdayToday(user.birthday)) {
	          user.isBirthday = true;
	          setTimeout(() => {
	            user.isBirthday = false;
	          }, im_oldChatEmbedding_lib_utils.Utils.date.getTimeToNextMidnight());
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
	        if (im_oldChatEmbedding_lib_utils.Utils.user.isOnline(payload.fields.lastActivityDate)) {
	          user.isOnline = true;
	          this.addToOnlineList(payload.fields.id);
	        }
	        if (im_oldChatEmbedding_lib_utils.Utils.user.isMobileOnline(payload.fields.lastActivityDate, payload.fields.mobileLastDate)) {
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
	      result.firstName = im_oldChatEmbedding_lib_utils.Utils.text.htmlspecialcharsback(fields.firstName);
	    }
	    if (main_core.Type.isStringFilled(fields.lastName)) {
	      result.lastName = im_oldChatEmbedding_lib_utils.Utils.text.htmlspecialcharsback(fields.lastName);
	    }
	    if (main_core.Type.isStringFilled(fields.name)) {
	      fields.name = im_oldChatEmbedding_lib_utils.Utils.text.htmlspecialcharsback(fields.name);
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
	      result.workPosition = im_oldChatEmbedding_lib_utils.Utils.text.htmlspecialcharsback(fields.workPosition);
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
	      result.idle = im_oldChatEmbedding_lib_utils.Utils.date.cast(fields.idle, false);
	    }
	    if (!main_core.Type.isUndefined(fields.last_activity_date)) {
	      fields.lastActivityDate = fields.last_activity_date;
	    }
	    if (!main_core.Type.isUndefined(fields.lastActivityDate)) {
	      result.lastActivityDate = im_oldChatEmbedding_lib_utils.Utils.date.cast(fields.lastActivityDate, false);
	    }
	    if (!main_core.Type.isUndefined(fields.mobile_last_date)) {
	      fields.mobileLastDate = fields.mobile_last_date;
	    }
	    if (!main_core.Type.isUndefined(fields.mobileLastDate)) {
	      result.mobileLastDate = im_oldChatEmbedding_lib_utils.Utils.date.cast(fields.mobileLastDate, false);
	    }
	    if (!main_core.Type.isUndefined(fields.absent)) {
	      result.absent = im_oldChatEmbedding_lib_utils.Utils.date.cast(fields.absent, false);
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
	      result = this.store.state.application.common.host + avatar;
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
	    }, im_oldChatEmbedding_lib_utils.Utils.date.getTimeToNextMidnight());
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
	        if (im_oldChatEmbedding_lib_utils.Utils.user.isOnline(user.lastActivityDate)) {
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
	        if (im_oldChatEmbedding_lib_utils.Utils.user.isMobileOnline(user.lastActivityDate, user.mobileLastDate)) {
	          user.isMobileOnline = true;
	        } else {
	          user.isMobileOnline = false;
	          state.mobileOnlineList = state.mobileOnlineList.filter(element => element !== userId);
	        }
	      });
	    }, ONE_MINUTE);
	  }
	}

	class RecentModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'recent';
	  }
	  getState() {
	    return {
	      collection: {},
	      recentCollection: new Set(),
	      unreadCollection: new Set(),
	      unloadedChatCounters: {},
	      activeCalls: [],
	      options: {
	        showBirthday: true,
	        showInvited: true,
	        showLastMessage: true
	      }
	    };
	  }
	  getElementState() {
	    return {
	      dialogId: '0',
	      message: {
	        id: 0,
	        senderId: 0,
	        date: new Date(),
	        status: im_oldChatEmbedding_const.MessageStatus.received,
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
	  getActiveCallDefaultState() {
	    return {
	      dialogId: 0,
	      name: '',
	      call: {},
	      state: im_oldChatEmbedding_const.RecentCallStatus.waiting
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
	        if (!dialog || dialog.type !== im_oldChatEmbedding_const.DialogType.user) {
	          return false;
	        }
	        const hasBirthday = this.store.getters['users/hasBirthday'](dialogId);
	        if (!hasBirthday) {
	          return false;
	        }
	        const hasMessage = im_oldChatEmbedding_lib_utils.Utils.text.isTempMessage(currentItem.message.id) || currentItem.message.id > 0;
	        const hasTodayMessage = hasMessage && im_oldChatEmbedding_lib_utils.Utils.date.isToday(currentItem.message.date);
	        return state.options.showBirthday && !hasTodayMessage && dialog.counter === 0;
	      },
	      needsVacationPlaceholder: state => dialogId => {
	        const currentItem = state.collection[dialogId];
	        if (!currentItem) {
	          return false;
	        }
	        const dialog = this.store.getters['dialogues/get'](dialogId);
	        if (!dialog || dialog.type !== im_oldChatEmbedding_const.DialogType.user) {
	          return false;
	        }
	        const hasVacation = this.store.getters['users/hasVacation'](dialogId);
	        if (!hasVacation) {
	          return false;
	        }
	        const hasMessage = im_oldChatEmbedding_lib_utils.Utils.text.isTempMessage(currentItem.message.id) || currentItem.message.id > 0;
	        const hasTodayMessage = hasMessage && im_oldChatEmbedding_lib_utils.Utils.date.isToday(currentItem.message.date);
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
	          return im_oldChatEmbedding_lib_utils.Utils.date.getStartOfTheDay();
	        }
	        return currentItem.message.date;
	      },
	      hasActiveCall: state => {
	        return state.activeCalls.some(item => item.state === im_oldChatEmbedding_const.RecentCallStatus.joined);
	      },
	      getOption: state => optionName => {
	        if (!im_oldChatEmbedding_const.RecentSettings[optionName]) {
	          return false;
	        }
	        return state.options[optionName];
	      },
	      getTotalCounter: state => {
	        let loadedChatsCounter = 0;
	        [...state.recentCollection].forEach(dialogId => {
	          const dialog = this.store.getters['dialogues/get'](dialogId, true);
	          const recentItem = state.collection[dialogId];
	          const isMuted = dialog.muteList.includes(im_oldChatEmbedding_application_core.Core.getUserId());
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
	      addActiveCall: (store, payload) => {
	        const existingIndex = store.state.activeCalls.findIndex(item => {
	          return item.dialogId === payload.dialogId || item.call.id === payload.call.id;
	        });
	        if (existingIndex > -1) {
	          store.commit('updateActiveCall', {
	            index: existingIndex,
	            fields: this.validateActiveCall(payload)
	          });
	          return true;
	        }
	        store.commit('addActiveCall', this.prepareActiveCall(payload));
	      },
	      updateActiveCall: (store, payload) => {
	        const existingIndex = store.state.activeCalls.findIndex(item => {
	          return item.dialogId === payload.dialogId;
	        });
	        store.commit('updateActiveCall', {
	          index: existingIndex,
	          fields: this.validateActiveCall(payload.fields)
	        });
	      },
	      deleteActiveCall: (store, payload) => {
	        const existingIndex = store.state.activeCalls.findIndex(item => {
	          return item.dialogId === payload.dialogId;
	        });
	        if (existingIndex === -1) {
	          return false;
	        }
	        store.commit('deleteActiveCall', {
	          index: existingIndex
	        });
	      },
	      setOptions: (store, payload) => {
	        if (!main_core.Type.isPlainObject(payload)) {
	          return false;
	        }
	        payload = this.validateOptions(payload);
	        Object.entries(payload).forEach(([option, value]) => {
	          store.commit('setOptions', {
	            option,
	            value
	          });
	        });
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
	      addActiveCall: (state, payload) => {
	        state.activeCalls.push(payload);
	      },
	      updateActiveCall: (state, payload) => {
	        state.activeCalls[payload.index] = {
	          ...state.activeCalls[payload.index],
	          ...payload.fields
	        };
	      },
	      deleteActiveCall: (state, payload) => {
	        state.activeCalls.splice(payload.index, 1);
	      },
	      setOptions: (state, payload) => {
	        state.options[payload.option] = payload.value;
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
	    if (main_core.Type.isNumber(fields.message.id) || im_oldChatEmbedding_lib_utils.Utils.text.isUuidV4(fields.message.id) || main_core.Type.isStringFilled(fields.message.id)) {
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
	      message.date = im_oldChatEmbedding_lib_utils.Utils.date.cast(fields.message.date);
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
	  prepareActiveCall(call) {
	    return {
	      ...this.getActiveCallDefaultState(),
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
	    if (im_oldChatEmbedding_const.RecentCallStatus[fields.state]) {
	      result.state = fields.state;
	    }
	    return result;
	  }
	  validateOptions(fields) {
	    const result = {};
	    if (main_core.Type.isBoolean(fields.showBirthday)) {
	      result.showBirthday = fields.showBirthday;
	    }
	    if (main_core.Type.isBoolean(fields.showInvited)) {
	      result.showInvited = fields.showInvited;
	    }
	    if (main_core.Type.isBoolean(fields.showLastMessage)) {
	      result.showLastMessage = fields.showLastMessage;
	    }
	    return result;
	  }
	}

	exports.ApplicationModel = ApplicationModel;
	exports.DialoguesModel = DialoguesModel;
	exports.UsersModel = UsersModel;
	exports.RecentModel = RecentModel;

}((this.BX.Messenger.Embedding.Model = this.BX.Messenger.Embedding.Model || {}),BX,BX.Vue3.Vuex,BX.Messenger.Embedding.Application,BX.Messenger.Embedding.Const,BX.Messenger.Embedding.Lib));
//# sourceMappingURL=registry.bundle.js.map
