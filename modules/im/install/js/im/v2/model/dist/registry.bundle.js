this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_vue3,im_v2_lib_logger,main_core_events,main_core,ui_vue3_vuex,im_v2_const,im_v2_lib_utils) {
	'use strict';

	class ApplicationModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'application';
	  }

	  getState() {
	    return {
	      common: {
	        host: this.getVariable('common.host', location.protocol + '//' + location.host),
	        siteId: this.getVariable('common.siteId', 'default'),
	        userId: this.getVariable('common.userId', 0),
	        languageId: this.getVariable('common.languageId', 'en')
	      },
	      dialog: {
	        dialogId: this.getVariable('dialog.dialogId', '0'),
	        chatId: this.getVariable('dialog.chatId', 0),
	        diskFolderId: this.getVariable('dialog.diskFolderId', 0),
	        messageLimit: this.getVariable('dialog.messageLimit', 20),
	        enableReadMessages: this.getVariable('dialog.enableReadMessages', true),
	        messageExtraCount: 0
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
	        type: this.getVariable('device.type', im_v2_const.DeviceType.desktop),
	        orientation: this.getVariable('device.orientation', im_v2_const.DeviceOrientation.portrait)
	      },
	      options: {
	        quoteEnable: this.getVariable('options.quoteEnable', true),
	        quoteFromRight: this.getVariable('options.quoteFromRight', true),
	        autoplayVideo: this.getVariable('options.autoplayVideo', true),
	        darkTheme: this.getVariable('options.darkTheme', false),
	        showSmiles: false
	      },
	      error: {
	        active: false,
	        code: '',
	        description: ''
	      }
	    };
	  }

	  getStateSaveException() {
	    return Object.assign({
	      common: this.getVariable('saveException.common', null),
	      dialog: this.getVariable('saveException.dialog', null),
	      mobile: this.getVariable('saveException.mobile', null),
	      device: this.getVariable('saveException.device', null),
	      error: this.getVariable('saveException.error', null)
	    });
	  }

	  getGetters() {
	    return {
	      getOption: state => optionName => {
	        if (!im_v2_const.Settings[optionName]) {
	          return false;
	        }

	        return state.options[optionName];
	      }
	    };
	  }

	  getActions() {
	    return {
	      set: (store, payload) => {
	        store.commit('set', this.validate(payload));
	      },
	      showSmiles: (store, payload) => {
	        store.commit('showSmiles');
	      },
	      hideSmiles: (store, payload) => {
	        store.commit('hideSmiles');
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
	      }
	    };
	  }

	  getMutations() {
	    return {
	      set: (state, payload) => {
	        let hasChange = false;

	        for (let group in payload) {
	          if (!payload.hasOwnProperty(group)) {
	            continue;
	          }

	          for (let field in payload[group]) {
	            if (!payload[group].hasOwnProperty(field)) {
	              continue;
	            }

	            state[group][field] = payload[group][field];
	            hasChange = true;
	          }
	        }

	        if (hasChange && this.isSaveNeeded(payload)) {
	          this.saveState(state);
	        }
	      },

	      increaseDialogExtraCount(state, payload = {}) {
	        let {
	          count = 1
	        } = payload;
	        state.dialog.messageExtraCount += count;
	      },

	      decreaseDialogExtraCount(state, payload = {}) {
	        let {
	          count = 1
	        } = payload;
	        let newCounter = state.dialog.messageExtraCount - count;

	        if (newCounter <= 0) {
	          newCounter = 0;
	        }

	        state.dialog.messageExtraCount = newCounter;
	      },

	      clearDialogExtraCount(state) {
	        state.dialog.messageExtraCount = 0;
	      },

	      showSmiles(state) {
	        state.options.showSmiles = true;
	      },

	      hideSmiles(state) {
	        state.options.showSmiles = false;
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

	      if (typeof fields.dialog.messageExtraCount === 'number') {
	        result.dialog.messageExtraCount = fields.dialog.messageExtraCount;
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

	      if (typeof fields.device.type === 'string' && typeof im_v2_const.DeviceType[fields.device.type] !== 'undefined') {
	        result.device.type = fields.device.type;
	      }

	      if (typeof fields.device.orientation === 'string' && typeof im_v2_const.DeviceOrientation[fields.device.orientation] !== 'undefined') {
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

	const IntersectionType = {
	  empty: 'empty',
	  equal: 'equal',
	  none: 'none',
	  found: 'found',
	  foundReverse: 'foundReverse'
	};
	class MessagesModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'messages';
	  }

	  getState() {
	    return {
	      created: 0,
	      collection: {},
	      mutationType: {},
	      saveMessageList: {},
	      saveFileList: {},
	      saveUserList: {}
	    };
	  }

	  getElementState() {
	    return {
	      templateId: 0,
	      templateType: 'message',
	      placeholderType: 0,
	      id: 0,
	      chatId: 0,
	      authorId: 0,
	      date: new Date(),
	      text: '',
	      textConverted: '',
	      params: {
	        TYPE: 'default',
	        COMPONENT_ID: 'bx-im-view-message'
	      },
	      push: false,
	      unread: false,
	      sending: false,
	      error: false,
	      retry: false,
	      blink: false
	    };
	  }

	  getGetters() {
	    return {
	      getMutationType: state => chatId => {
	        if (!state.mutationType[chatId]) {
	          return {
	            initialType: im_v2_const.MutationType.none,
	            appliedType: im_v2_const.MutationType.none
	          };
	        }

	        return state.mutationType[chatId];
	      },
	      getLastId: state => chatId => {
	        if (!state.collection[chatId] || state.collection[chatId].length <= 0) {
	          return null;
	        }

	        let lastId = 0;

	        for (let i = 0; i < state.collection[chatId].length; i++) {
	          let element = state.collection[chatId][i];

	          if (element.push || element.sending || element.id.toString().startsWith('temporary')) {
	            continue;
	          }

	          if (lastId < element.id) {
	            lastId = element.id;
	          }
	        }

	        return lastId ? lastId : null;
	      },
	      getMessage: state => (chatId, messageId) => {
	        if (!state.collection[chatId] || state.collection[chatId].length <= 0) {
	          return null;
	        }

	        for (let index = state.collection[chatId].length - 1; index >= 0; index--) {
	          if (state.collection[chatId][index].id === messageId) {
	            return state.collection[chatId][index];
	          }
	        }

	        return null;
	      },
	      get: state => chatId => {
	        if (!state.collection[chatId] || state.collection[chatId].length <= 0) {
	          return [];
	        }

	        return state.collection[chatId];
	      },
	      getBlank: state => params => {
	        return this.getElementState();
	      },
	      getSaveFileList: state => params => {
	        return state.saveFileList;
	      },
	      getSaveUserList: state => params => {
	        return state.saveUserList;
	      }
	    };
	  }

	  getActions() {
	    return {
	      add: (store, payload) => {
	        let result = this.validate(Object.assign({}, payload));
	        result.params = Object.assign({}, this.getElementState().params, result.params);

	        if (payload.id) {
	          if (store.state.collection[payload.chatId]) {
	            const countMessages = store.state.collection[payload.chatId].length - 1;

	            for (let index = countMessages; index >= 0; index--) {
	              const message = store.state.collection[payload.chatId][index];

	              if (message.templateId === payload.id) {
	                return;
	              }
	            }
	          }

	          result.id = payload.id;
	        } else {
	          result.id = 'temporary' + new Date().getTime() + store.state.created;
	        }

	        result.templateId = result.id;
	        result.unread = false;
	        store.commit('add', Object.assign({}, this.getElementState(), result));

	        if (payload.sending !== false) {
	          store.dispatch('actionStart', {
	            id: result.id,
	            chatId: result.chatId
	          });
	        }

	        return result.id;
	      },
	      actionStart: (store, payload) => {
	        if (/^\d+$/.test(payload.id)) {
	          payload.id = parseInt(payload.id);
	        }

	        payload.chatId = parseInt(payload.chatId);
	        ui_vue3.nextTick(() => {
	          store.commit('update', {
	            id: payload.id,
	            chatId: payload.chatId,
	            fields: {
	              sending: true
	            }
	          });
	        });
	      },
	      actionError: (store, payload) => {
	        if (/^\d+$/.test(payload.id)) {
	          payload.id = parseInt(payload.id);
	        }

	        payload.chatId = parseInt(payload.chatId);
	        ui_vue3.nextTick(() => {
	          store.commit('update', {
	            id: payload.id,
	            chatId: payload.chatId,
	            fields: {
	              sending: false,
	              error: true,
	              retry: payload.retry !== false
	            }
	          });
	        });
	      },
	      actionFinish: (store, payload) => {
	        if (/^\d+$/.test(payload.id)) {
	          payload.id = parseInt(payload.id);
	        }

	        payload.chatId = parseInt(payload.chatId);
	        ui_vue3.nextTick(() => {
	          store.commit('update', {
	            id: payload.id,
	            chatId: payload.chatId,
	            fields: {
	              sending: false,
	              error: false,
	              retry: false
	            }
	          });
	        });
	      },
	      set: (store, payload) => {
	        if (payload instanceof Array) {
	          payload = payload.map(message => this.prepareMessage(message));
	        } else {
	          let result = this.prepareMessage(payload);
	          (payload = []).push(result);
	        }

	        store.commit('set', {
	          insertType: im_v2_const.MutationType.set,
	          data: payload
	        });
	        return 'set is done';
	      },
	      addPlaceholders: (store, payload) => {
	        if (payload.placeholders instanceof Array) {
	          payload.placeholders = payload.placeholders.map(message => this.prepareMessage(message));
	        } else {
	          return false;
	        }

	        const insertType = payload.requestMode === 'history' ? im_v2_const.MutationType.setBefore : im_v2_const.MutationType.setAfter;

	        if (insertType === im_v2_const.MutationType.setBefore) {
	          payload.placeholders = payload.placeholders.reverse();
	        }

	        store.commit('set', {
	          insertType,
	          data: payload.placeholders
	        });
	        return payload.placeholders[0].id;
	      },
	      clearPlaceholders: (store, payload) => {
	        store.commit('clearPlaceholders', payload);
	      },
	      updatePlaceholders: (store, payload) => {
	        if (payload.data instanceof Array) {
	          payload.data = payload.data.map(message => this.prepareMessage(message));
	        } else {
	          return false;
	        }

	        store.commit('updatePlaceholders', payload);
	        return true;
	      },
	      setAfter: (store, payload) => {
	        if (payload instanceof Array) {
	          payload = payload.map(message => this.prepareMessage(message));
	        } else {
	          let result = this.prepareMessage(payload);
	          (payload = []).push(result);
	        }

	        store.commit('set', {
	          insertType: im_v2_const.MutationType.setAfter,
	          data: payload
	        });
	      },
	      setBefore: (store, payload) => {
	        if (payload instanceof Array) {
	          payload = payload.map(message => this.prepareMessage(message));
	        } else {
	          let result = this.prepareMessage(payload);
	          (payload = []).push(result);
	        }

	        store.commit('set', {
	          insertType: im_v2_const.MutationType.setBefore,
	          data: payload
	        });
	      },
	      update: (store, payload) => {
	        if (/^\d+$/.test(payload.id)) {
	          payload.id = parseInt(payload.id);
	        }

	        if (/^\d+$/.test(payload.chatId)) {
	          payload.chatId = parseInt(payload.chatId);
	        }

	        store.commit('initCollection', {
	          chatId: payload.chatId
	        });

	        if (!store.state.collection[payload.chatId]) {
	          return false;
	        }

	        let index = store.state.collection[payload.chatId].findIndex(el => el.id === payload.id);

	        if (index < 0) {
	          return false;
	        }

	        let result = this.validate(Object.assign({}, payload.fields));

	        if (result.params) {
	          result.params = Object.assign({}, this.getElementState().params, store.state.collection[payload.chatId][index].params, result.params);
	        }

	        store.commit('update', {
	          id: payload.id,
	          chatId: payload.chatId,
	          index: index,
	          fields: result
	        });

	        if (payload.fields.blink) {
	          setTimeout(() => {
	            store.commit('update', {
	              id: payload.id,
	              chatId: payload.chatId,
	              fields: {
	                blink: false
	              }
	            });
	          }, 1000);
	        }

	        return true;
	      },
	      delete: (store, payload) => {
	        if (!(payload.id instanceof Array)) {
	          payload.id = [payload.id];
	        }

	        payload.id = payload.id.map(id => {
	          if (/^\d+$/.test(id)) {
	            id = parseInt(id);
	          }

	          return id;
	        });
	        store.commit('delete', {
	          chatId: payload.chatId,
	          elements: payload.id
	        });
	        return true;
	      },
	      clear: (store, payload) => {
	        payload.chatId = parseInt(payload.chatId);

	        if (payload.keepPlaceholders) {
	          store.commit('clearMessages', {
	            chatId: payload.chatId
	          });
	        } else {
	          store.commit('clear', {
	            chatId: payload.chatId
	          });
	        }

	        return true;
	      },
	      applyMutationType: (store, payload) => {
	        payload.chatId = parseInt(payload.chatId);
	        store.commit('applyMutationType', {
	          chatId: payload.chatId
	        });
	        return true;
	      },
	      readMessages: (store, payload) => {
	        payload.readId = parseInt(payload.readId) || 0;
	        payload.chatId = parseInt(payload.chatId);

	        if (typeof store.state.collection[payload.chatId] === 'undefined') {
	          return {
	            count: 0
	          };
	        }

	        let count = 0;

	        for (let index = store.state.collection[payload.chatId].length - 1; index >= 0; index--) {
	          let element = store.state.collection[payload.chatId][index];
	          if (!element.unread) continue;

	          if (payload.readId === 0 || element.id <= payload.readId) {
	            count++;
	          }
	        }

	        store.commit('readMessages', {
	          chatId: payload.chatId,
	          readId: payload.readId
	        });
	        return {
	          count
	        };
	      },
	      unreadMessages: (store, payload) => {
	        payload.unreadId = parseInt(payload.unreadId) || 0;
	        payload.chatId = parseInt(payload.chatId);

	        if (typeof store.state.collection[payload.chatId] === 'undefined' || !payload.unreadId) {
	          return {
	            count: 0
	          };
	        }

	        let count = 0;

	        for (let index = store.state.collection[payload.chatId].length - 1; index >= 0; index--) {
	          let element = store.state.collection[payload.chatId][index];
	          if (element.unread) continue;

	          if (element.id >= payload.unreadId) {
	            count++;
	          }
	        }

	        store.commit('unreadMessages', {
	          chatId: payload.chatId,
	          unreadId: payload.unreadId
	        });
	        return {
	          count
	        };
	      }
	    };
	  }

	  getMutations() {
	    return {
	      initCollection: (state, payload) => {
	        return this.initCollection(state, payload);
	      },
	      add: (state, payload) => {
	        this.initCollection(state, {
	          chatId: payload.chatId
	        });
	        state.collection[payload.chatId].push(payload);
	        state.saveMessageList[payload.chatId].push(payload.id);
	        state.created += 1;
	        state.collection[payload.chatId].sort((a, b) => a.id - b.id);
	        this.saveState(state, payload.chatId);
	        im_v2_lib_logger.Logger.warn('Messages model: saving state after add');
	      },
	      clearPlaceholders: (state, payload) => {
	        if (!state.collection[payload.chatId]) {
	          return false;
	        }

	        state.collection[payload.chatId] = state.collection[payload.chatId].filter(element => {
	          return !element.id.toString().startsWith('placeholder');
	        });
	      },
	      updatePlaceholders: (state, payload) => {
	        const firstPlaceholderId = `placeholder${payload.firstMessage}`;
	        const firstPlaceholderIndex = state.collection[payload.chatId].findIndex(message => {
	          return message.id === firstPlaceholderId;
	        }); // Logger.warn('firstPlaceholderIndex', firstPlaceholderIndex);

	        if (firstPlaceholderIndex >= 0) {
	          // Logger.warn('before delete', state.collection[payload.chatId].length, [...state.collection[payload.chatId]]);
	          state.collection[payload.chatId].splice(firstPlaceholderIndex, payload.amount); // Logger.warn('after delete', state.collection[payload.chatId].length, [...state.collection[payload.chatId]]);

	          state.collection[payload.chatId].splice(firstPlaceholderIndex, 0, ...payload.data); // Logger.warn('after add', state.collection[payload.chatId].length, [...state.collection[payload.chatId]]);
	        }

	        state.collection[payload.chatId].sort((a, b) => a.id - b.id);
	        im_v2_lib_logger.Logger.warn('Messages model: saving state after updating placeholders');
	        this.saveState(state, payload.chatId);
	      },
	      set: (state, payload) => {
	        im_v2_lib_logger.Logger.warn('Messages model: set mutation', payload);
	        let chats = [];
	        let chatsSave = [];
	        let isPush = false;
	        payload.data = MessagesModel.getPayloadWithTempMessages(state, payload);
	        const initialType = payload.insertType;

	        if (payload.insertType === im_v2_const.MutationType.set) {
	          payload.insertType = im_v2_const.MutationType.setAfter;
	          let elements = {};
	          payload.data.forEach(element => {
	            if (!elements[element.chatId]) {
	              elements[element.chatId] = [];
	            }

	            elements[element.chatId].push(element.id);
	          });

	          for (let chatId in elements) {
	            if (!elements.hasOwnProperty(chatId)) continue;
	            this.initCollection(state, {
	              chatId
	            });
	            im_v2_lib_logger.Logger.warn('Messages model: messages before adding from request - ', state.collection[chatId].length);

	            if (state.saveMessageList[chatId].length > elements[chatId].length || elements[chatId].length < im_v2_const.StorageLimit.messages) {
	              state.collection[chatId] = state.collection[chatId].filter(element => elements[chatId].includes(element.id));
	              state.saveMessageList[chatId] = state.saveMessageList[chatId].filter(id => elements[chatId].includes(id));
	            }

	            im_v2_lib_logger.Logger.warn('Messages model: cache length', state.saveMessageList[chatId].length);
	            let intersection = this.manageCacheBeforeSet([...state.saveMessageList[chatId].reverse()], elements[chatId]);
	            im_v2_lib_logger.Logger.warn('Messages model: set intersection with cache', intersection);

	            if (intersection.type === IntersectionType.none) {
	              if (intersection.foundElements.length > 0) {
	                state.collection[chatId] = state.collection[chatId].filter(element => !intersection.foundElements.includes(element.id));
	                state.saveMessageList[chatId] = state.saveMessageList[chatId].filter(id => !intersection.foundElements.includes(id));
	              }

	              im_v2_lib_logger.Logger.warn('Messages model: no intersection - removing cache');
	              this.removeIntersectionCacheElements = state.collection[chatId].map(element => element.id);
	              state.collection[chatId] = state.collection[chatId].filter(element => !this.removeIntersectionCacheElements.includes(element.id));
	              state.saveMessageList[chatId] = state.saveMessageList[chatId].filter(id => !this.removeIntersectionCacheElements.includes(id));
	              this.removeIntersectionCacheElements = [];
	            } else if (intersection.type === IntersectionType.foundReverse) {
	              im_v2_lib_logger.Logger.warn('Messages model: found reverse intersection');
	              payload.insertType = im_v2_const.MutationType.setBefore;
	              payload.data = payload.data.reverse();
	            }
	          }
	        }

	        im_v2_lib_logger.Logger.warn('Messages model: adding messages to model', payload.data);

	        for (let element of payload.data) {
	          this.initCollection(state, {
	            chatId: element.chatId
	          });
	          let index = state.collection[element.chatId].findIndex(localMessage => {
	            if (MessagesModel.isTemporaryMessage(localMessage)) {
	              return localMessage.templateId === element.templateId;
	            }

	            return localMessage.id === element.id;
	          });

	          if (index > -1) {
	            state.collection[element.chatId][index] = Object.assign(state.collection[element.chatId][index], element);
	          } else if (payload.insertType === im_v2_const.MutationType.setBefore) {
	            state.collection[element.chatId].unshift(element);
	          } else if (payload.insertType === im_v2_const.MutationType.setAfter) {
	            state.collection[element.chatId].push(element);
	          }

	          chats.push(element.chatId);

	          if (this.store.getters['dialogues/canSaveChat'] && this.store.getters['dialogues/canSaveChat'](element.chatId)) {
	            chatsSave.push(element.chatId);
	          }
	        }

	        chats = [...new Set(chats)];
	        chatsSave = [...new Set(chatsSave)];
	        isPush = payload.data.every(element => element.push === true);
	        im_v2_lib_logger.Logger.warn('Is it fake push message?', isPush);
	        chats.forEach(chatId => {
	          state.collection[chatId].sort((a, b) => a.id - b.id);

	          if (!isPush) {
	            //send event that messages are ready and we can start reading etc
	            im_v2_lib_logger.Logger.warn('setting messagesSet = true for chatId = ', chatId);
	            setTimeout(() => {
	              main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.messagesSet, {
	                chatId
	              });
	              main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.readVisibleMessages, {
	                chatId
	              });
	            }, 100);
	          }
	        });

	        if (initialType !== im_v2_const.MutationType.setBefore) {
	          chatsSave.forEach(chatId => {
	            im_v2_lib_logger.Logger.warn('Messages model: saving state after set');
	            this.saveState(state, chatId);
	          });
	        }
	      },
	      update: (state, payload) => {
	        this.initCollection(state, {
	          chatId: payload.chatId
	        });
	        let index = -1;

	        if (typeof payload.index !== 'undefined' && state.collection[payload.chatId][payload.index]) {
	          index = payload.index;
	        } else {
	          index = state.collection[payload.chatId].findIndex(el => el.id === payload.id);
	        }

	        if (index >= 0) {
	          let isSaveState = state.saveMessageList[payload.chatId].includes(state.collection[payload.chatId][index].id) || payload.fields.id && !payload.fields.id.toString().startsWith('temporary') && state.collection[payload.chatId][index].id.toString().startsWith('temporary');
	          state.collection[payload.chatId][index] = Object.assign(state.collection[payload.chatId][index], payload.fields);

	          if (isSaveState) {
	            im_v2_lib_logger.Logger.warn('Messages model: saving state after update');
	            this.saveState(state, payload.chatId);
	          }
	        }
	      },
	      delete: (state, payload) => {
	        this.initCollection(state, {
	          chatId: payload.chatId
	        });
	        state.collection[payload.chatId] = state.collection[payload.chatId].filter(element => !payload.elements.includes(element.id));

	        if (state.saveMessageList[payload.chatId].length > 0) {
	          for (let id of payload.elements) {
	            if (state.saveMessageList[payload.chatId].includes(id)) {
	              im_v2_lib_logger.Logger.warn('Messages model: saving state after delete');
	              this.saveState(state, payload.chatId);
	              break;
	            }
	          }
	        }
	      },
	      clear: (state, payload) => {
	        this.initCollection(state, {
	          chatId: payload.chatId
	        });
	        state.collection[payload.chatId] = [];
	        state.saveMessageList[payload.chatId] = [];
	      },
	      clearMessages: (state, payload) => {
	        this.initCollection(state, {
	          chatId: payload.chatId
	        });
	        state.collection[payload.chatId] = state.collection[payload.chatId].filter(element => {
	          return element.id.toString().startsWith('placeholder');
	        });
	        state.saveMessageList[payload.chatId] = [];
	      },
	      applyMutationType: (state, payload) => {
	        if (typeof state.mutationType[payload.chatId] === 'undefined') {
	          state.mutationType[payload.chatId] = {
	            applied: false,
	            initialType: im_v2_const.MutationType.none,
	            appliedType: im_v2_const.MutationType.none,
	            scrollStickToTop: 0,
	            scrollMessageId: 0
	          };
	        }

	        state.mutationType[payload.chatId].applied = true;
	      },
	      readMessages: (state, payload) => {
	        this.initCollection(state, {
	          chatId: payload.chatId
	        });
	        let saveNeeded = false;

	        for (let index = state.collection[payload.chatId].length - 1; index >= 0; index--) {
	          let element = state.collection[payload.chatId][index];
	          if (!element.unread) continue;

	          if (payload.readId === 0 || element.id <= payload.readId) {
	            state.collection[payload.chatId][index] = Object.assign(state.collection[payload.chatId][index], {
	              unread: false
	            });
	            saveNeeded = true;
	          }
	        }

	        if (saveNeeded) {
	          im_v2_lib_logger.Logger.warn('Messages model: saving state after reading');
	          this.saveState(state, payload.chatId);
	        }
	      },
	      unreadMessages: (state, payload) => {
	        this.initCollection(state, {
	          chatId: payload.chatId
	        });
	        let saveNeeded = false;

	        for (let index = state.collection[payload.chatId].length - 1; index >= 0; index--) {
	          let element = state.collection[payload.chatId][index];
	          if (element.unread) continue;

	          if (element.id >= payload.unreadId) {
	            state.collection[payload.chatId][index] = Object.assign(state.collection[payload.chatId][index], {
	              unread: true
	            });
	            saveNeeded = true;
	          }
	        }

	        if (saveNeeded) {
	          im_v2_lib_logger.Logger.warn('Messages model: saving state after unreading');
	          this.saveState(state, payload.chatId);
	          this.updateSubordinateStates();
	        }
	      }
	    };
	  }

	  initCollection(state, payload) {
	    if (typeof payload.chatId === 'undefined') {
	      return false;
	    }

	    if (typeof payload.chatId === 'undefined' || typeof state.collection[payload.chatId] !== 'undefined') {
	      return true;
	    }

	    state.collection[payload.chatId] = payload.messages ? [...payload.messages] : [];
	    state.saveMessageList[payload.chatId] = [];
	    state.saveFileList[payload.chatId] = [];
	    state.saveUserList[payload.chatId] = [];
	    return true;
	  }

	  prepareMessage(message, options = {}) {
	    let result = this.validate(Object.assign({}, message), options);
	    result.params = Object.assign({}, this.getElementState().params, result.params);

	    if (!result.templateId) {
	      result.templateId = result.id;
	    }

	    return Object.assign({}, this.getElementState(), result);
	  }

	  manageCacheBeforeSet(cache, elements, recursive = false) {
	    im_v2_lib_logger.Logger.warn('manageCacheBeforeSet', cache, elements);
	    let result = {
	      type: IntersectionType.empty,
	      foundElements: [],
	      noneElements: []
	    };

	    if (!cache || cache.length <= 0) {
	      return result;
	    }

	    for (let id of elements) {
	      if (cache.includes(id)) {
	        if (result.type === IntersectionType.empty) {
	          result.type = IntersectionType.found;
	        }

	        result.foundElements.push(id);
	      } else {
	        if (result.type === IntersectionType.empty) {
	          result.type = IntersectionType.none;
	        }

	        result.noneElements.push(id);
	      }
	    }

	    if (result.type === IntersectionType.found && cache.length === elements.length && result.foundElements.length === elements.length) {
	      result.type = IntersectionType.equal;
	    } else if (result.type === IntersectionType.none && !recursive && result.foundElements.length > 0) {
	      let reverseResult = this.manageCacheBeforeSet(cache.reverse(), elements.reverse(), true);

	      if (reverseResult.type === IntersectionType.found) {
	        reverseResult.type = IntersectionType.foundReverse;
	        return reverseResult;
	      }
	    }

	    return result;
	  }

	  updateSaveLists(state, chatId) {
	    if (!this.isSaveAvailable()) {
	      return true;
	    }

	    if (!chatId || !this.store.getters['dialogues/canSaveChat'] || !this.store.getters['dialogues/canSaveChat'](chatId)) {
	      return false;
	    }

	    this.initCollection(state, {
	      chatId: chatId
	    });
	    let count = 0;
	    let saveMessageList = [];
	    let saveFileList = [];
	    let saveUserList = [];
	    let dialog = this.store.getters['dialogues/getByChatId'](chatId);

	    if (dialog && dialog.type === 'private') {
	      saveUserList.push(parseInt(dialog.dialogId));
	    }

	    let readCounter = 0;

	    for (let index = state.collection[chatId].length - 1; index >= 0; index--) {
	      if (state.collection[chatId][index].id.toString().startsWith('temporary')) {
	        continue;
	      }

	      if (!state.collection[chatId][index].unread) {
	        readCounter++;
	      }

	      if (count >= im_v2_const.StorageLimit.messages && readCounter >= 50) {
	        break;
	      }

	      saveMessageList.unshift(state.collection[chatId][index].id);
	      count++;
	    }

	    saveMessageList = saveMessageList.slice(0, im_v2_const.StorageLimit.messages);
	    state.collection[chatId].filter(element => saveMessageList.includes(element.id)).forEach(element => {
	      if (element.authorId > 0) {
	        saveUserList.push(element.authorId);
	      }

	      if (element.params.FILE_ID instanceof Array) {
	        saveFileList = element.params.FILE_ID.concat(saveFileList);
	      }
	    });
	    state.saveMessageList[chatId] = saveMessageList;
	    state.saveFileList[chatId] = [...new Set(saveFileList)];
	    state.saveUserList[chatId] = [...new Set(saveUserList)];
	    return true;
	  }

	  getSaveTimeout() {
	    return 150;
	  }

	  saveState(state, chatId) {
	    if (!this.updateSaveLists(state, chatId)) {
	      return false;
	    }

	    super.saveState(() => {
	      let storedState = {
	        collection: {},
	        saveMessageList: {},
	        saveUserList: {},
	        saveFileList: {}
	      };

	      for (let chatId in state.saveMessageList) {
	        if (!state.saveMessageList.hasOwnProperty(chatId)) {
	          continue;
	        }

	        if (!state.collection[chatId]) {
	          continue;
	        }

	        if (!storedState.collection[chatId]) {
	          storedState.collection[chatId] = [];
	        }

	        state.collection[chatId].filter(element => state.saveMessageList[chatId].includes(element.id)).forEach(element => {
	          if (element.templateType !== 'placeholder') {
	            storedState.collection[chatId].push(element);
	          }
	        });
	        im_v2_lib_logger.Logger.warn('Cache after updating', storedState.collection[chatId]);
	        storedState.saveMessageList[chatId] = state.saveMessageList[chatId];
	        storedState.saveFileList[chatId] = state.saveFileList[chatId];
	        storedState.saveUserList[chatId] = state.saveUserList[chatId];
	      }

	      return storedState;
	    });
	  }

	  updateSubordinateStates() {
	    this.store.dispatch('users/saveState');
	    this.store.dispatch('files/saveState');
	  }

	  validate(fields, options) {
	    const result = {};

	    if (typeof fields.id === "number") {
	      result.id = fields.id;
	    } else if (typeof fields.id === "string") {
	      if (fields.id.startsWith('temporary') || fields.id.startsWith('placeholder') || im_v2_lib_utils.Utils.types.isUuidV4(fields.id)) {
	        result.id = fields.id;
	      } else {
	        result.id = parseInt(fields.id);
	      }
	    }

	    if (typeof fields.uuid === "string") {
	      result.templateId = fields.uuid;
	    } else if (typeof fields.templateId === "number") {
	      result.templateId = fields.templateId;
	    } else if (typeof fields.templateId === "string") {
	      if (fields.templateId.startsWith('temporary') || im_v2_lib_utils.Utils.types.isUuidV4(fields.templateId)) {
	        result.templateId = fields.templateId;
	      } else {
	        result.templateId = parseInt(fields.templateId);
	      }
	    }

	    if (typeof fields.templateType === "string") {
	      result.templateType = fields.templateType;
	    }

	    if (typeof fields.placeholderType === "number") {
	      result.placeholderType = fields.placeholderType;
	    }

	    if (typeof fields.chat_id !== 'undefined') {
	      fields.chatId = fields.chat_id;
	    }

	    if (typeof fields.chatId === "number" || typeof fields.chatId === "string") {
	      result.chatId = parseInt(fields.chatId);
	    }

	    if (typeof fields.date !== "undefined") {
	      result.date = im_v2_lib_utils.Utils.date.cast(fields.date);
	    } // previous P&P format


	    if (typeof fields.textOriginal === "string" || typeof fields.textOriginal === "number") {
	      result.text = fields.textOriginal.toString();

	      if (typeof fields.text === "string" || typeof fields.text === "number") {
	        result.textConverted = this.convertToHtml({
	          text: fields.text.toString(),
	          isConverted: true
	        });
	      }
	    } else // modern format
	      {
	        if (typeof fields.text_converted !== 'undefined') {
	          fields.textConverted = fields.text_converted;
	        }

	        if (typeof fields.textConverted === "string" || typeof fields.textConverted === "number") {
	          result.textConverted = fields.textConverted.toString();
	        }

	        if (typeof fields.text === "string" || typeof fields.text === "number") {
	          result.text = fields.text.toString();
	          let isConverted = typeof result.textConverted !== 'undefined';
	          result.textConverted = this.convertToHtml({
	            text: isConverted ? result.textConverted : result.text,
	            isConverted
	          });
	        }
	      }

	    if (typeof fields.senderId !== 'undefined') {
	      fields.authorId = fields.senderId;
	    } else if (typeof fields.author_id !== 'undefined') {
	      fields.authorId = fields.author_id;
	    }

	    if (typeof fields.authorId === "number" || typeof fields.authorId === "string") {
	      if (fields.system === true || fields.system === 'Y') {
	        result.authorId = 0;
	      } else {
	        result.authorId = parseInt(fields.authorId);
	      }
	    }

	    if (typeof fields.params === "object" && fields.params !== null) {
	      const params = this.validateParams(fields.params, options);

	      if (params) {
	        result.params = params;
	      }
	    }

	    if (typeof fields.push === "boolean") {
	      result.push = fields.push;
	    }

	    if (typeof fields.sending === "boolean") {
	      result.sending = fields.sending;
	    }

	    if (typeof fields.unread === "boolean") {
	      result.unread = fields.unread;
	    }

	    if (typeof fields.blink === "boolean") {
	      result.blink = fields.blink;
	    }

	    if (typeof fields.error === "boolean" || typeof fields.error === "string") {
	      result.error = fields.error;
	    }

	    if (typeof fields.retry === "boolean") {
	      result.retry = fields.retry;
	    }

	    return result;
	  }

	  validateParams(params, options) {
	    const result = {};

	    try {
	      for (let field in params) {
	        if (!params.hasOwnProperty(field)) {
	          continue;
	        }

	        if (field === 'COMPONENT_ID') {
	          if (typeof params[field] === "string" && BX.Vue.isComponent(params[field])) {
	            result[field] = params[field];
	          }
	        } else if (field === 'LIKE') {
	          if (params[field] instanceof Array) {
	            result['REACTION'] = {
	              like: params[field].map(element => parseInt(element))
	            };
	          }
	        } else if (field === 'CHAT_LAST_DATE') {
	          result[field] = im_v2_lib_utils.Utils.date.cast(params[field]);
	        } else if (field === 'AVATAR') {
	          if (params[field]) {
	            result[field] = params[field].startsWith('http') ? params[field] : options.host + params[field];
	          }
	        } else if (field === 'NAME') {
	          if (params[field]) {
	            result[field] = params[field];
	          }
	        } else if (field === 'LINK_ACTIVE') {
	          if (params[field]) {
	            result[field] = params[field].map(function (userId) {
	              return parseInt(userId);
	            });
	          }
	        } else if (field === 'ATTACH') {
	          result[field] = this.decodeAttach(params[field]);
	        } else {
	          result[field] = params[field];
	        }
	      }
	    } catch (e) {}

	    let hasResultElements = false;

	    for (let field in result) {
	      if (!result.hasOwnProperty(field)) {
	        continue;
	      }

	      hasResultElements = true;
	      break;
	    }

	    return hasResultElements ? result : null;
	  }

	  convertToHtml(params = {}) {
	    let {
	      quote = true,
	      image = true,
	      text = '',
	      isConverted = false,
	      enableBigSmile = true
	    } = params;
	    text = text.trim();

	    if (!isConverted) {
	      text = text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	    }

	    if (text.startsWith('/me')) {
	      text = `<i>${text.substr(4)}</i>`;
	    } else if (text.startsWith('/loud')) {
	      text = `<b>${text.substr(6)}</b>`;
	    }

	    const quoteSign = "&gt;&gt;";

	    if (quote && text.indexOf(quoteSign) >= 0) {
	      let textPrepare = text.split(isConverted ? "<br />" : "\n");

	      for (let i = 0; i < textPrepare.length; i++) {
	        if (textPrepare[i].startsWith(quoteSign)) {
	          textPrepare[i] = textPrepare[i].replace(quoteSign, '<div class="bx-im-message-content-quote"><div class="bx-im-message-content-quote-wrap">');

	          while (++i < textPrepare.length && textPrepare[i].startsWith(quoteSign)) {
	            textPrepare[i] = textPrepare[i].replace(quoteSign, '');
	          }

	          textPrepare[i - 1] += '</div></div><br>';
	        }
	      }

	      text = textPrepare.join("<br />");
	    }

	    text = text.replace(/\n/gi, '<br />');
	    text = text.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;&nbsp;');
	    text = this.decodeBbCode(text, false, enableBigSmile);

	    if (quote) {
	      text = text.replace(/------------------------------------------------------<br \/>(.*?)\[(.*?)\]<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function (whole, p1, p2, p3, p4, offset) {
	        return (offset > 0 ? '<br>' : '') + "<div class=\"bx-im-message-content-quote\"><div class=\"bx-im-message-content-quote-wrap\"><div class=\"bx-im-message-content-quote-name\"><span class=\"bx-im-message-content-quote-name-text\">" + p1 + "</span><span class=\"bx-im-message-content-quote-name-time\">" + p2 + "</span></div>" + p3 + "</div></div><br />";
	      });
	      text = text.replace(/------------------------------------------------------<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function (whole, p1, p2, p3, offset) {
	        return (offset > 0 ? '<br>' : '') + "<div class=\"bx-im-message-content-quote\"><div class=\"bx-im-message-content-quote-wrap\">" + p1 + "</div></div><br />";
	      });
	    }

	    if (image) {
	      let changed = false;
	      text = text.replace(/<a(.*?)>(http[s]{0,1}:\/\/.*?)<\/a>/ig, function (whole, aInner, text, offset) {
	        if (!text.match(/(\.(jpg|jpeg|png|gif|webp)\?|\.(jpg|jpeg|png|gif|webp)$)/i) || text.indexOf("/docs/pub/") > 0 || text.indexOf("logout=yes") > 0) {
	          return whole;
	        } else {
	          changed = true;
	          return (offset > 0 ? '<br />' : '') + '<a' + aInner + ' target="_blank" class="bx-im-element-file-image"><img src="' + text + '" class="bx-im-element-file-image-source-text" onerror="BX.Messenger.Model.MessagesModel.hideErrorImage(this)"></a></span>';
	        }
	      });

	      if (changed) {
	        text = text.replace(/<\/span>(\n?)<br(\s\/?)>/ig, '</span>').replace(/<br(\s\/?)>(\n?)<br(\s\/?)>(\n?)<span/ig, '<br /><span');
	      }
	    }

	    if (enableBigSmile) {
	      text = text.replace(/^(\s*<img\s+src=[^>]+?data-code=[^>]+?data-definition="UHD"[^>]+?style="width:)(\d+)(px[^>]+?height:)(\d+)(px[^>]+?class="bx-smile"\s*\/?>\s*)$/, function doubleSmileSize(match, start, width, middle, height, end) {
	        return start + parseInt(width, 10) * 1.7 + middle + parseInt(height, 10) * 1.7 + end;
	      });
	    }

	    if (text.substr(-6) == '<br />') {
	      text = text.substr(0, text.length - 6);
	    }

	    text = text.replace(/<br><br \/>/ig, '<br />');
	    text = text.replace(/<br \/><br>/ig, '<br />');
	    return text;
	  }

	  decodeBbCode(text, textOnly = false, enableBigSmile = true) {
	    return MessagesModel.decodeBbCode({
	      text,
	      textOnly,
	      enableBigSmile
	    });
	  }

	  decodeAttach(item) {
	    if (Array.isArray(item)) {
	      item.forEach(arrayElement => {
	        arrayElement = this.decodeAttach(arrayElement);
	      });
	    } else if (typeof item === 'object' && item !== null) {
	      for (const prop in item) {
	        if (item.hasOwnProperty(prop)) {
	          item[prop] = this.decodeAttach(item[prop]);
	        }
	      }
	    } else {
	      if (typeof item === 'string') {
	        item = im_v2_lib_utils.Utils.text.htmlspecialcharsback(item);
	      }
	    }

	    return item;
	  }

	  static decodeBbCode(params = {}) {
	    let {
	      text,
	      textOnly = false,
	      enableBigSmile = true
	    } = params;
	    let putReplacement = [];
	    text = text.replace(/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/ig, function (whole) {
	      var id = putReplacement.length;
	      putReplacement.push(whole);
	      return '####REPLACEMENT_PUT_' + id + '####';
	    });
	    let sendReplacement = [];
	    text = text.replace(/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/ig, function (whole) {
	      var id = sendReplacement.length;
	      sendReplacement.push(whole);
	      return '####REPLACEMENT_SEND_' + id + '####';
	    });
	    let codeReplacement = [];
	    text = text.replace(/\[CODE\]\n?(.*?)\[\/CODE\]/sig, function (whole, text) {
	      let id = codeReplacement.length;
	      codeReplacement.push(text);
	      return '####REPLACEMENT_CODE_' + id + '####';
	    });
	    text = text.replace(/\[url=([^\]]+)\](.*?)\[\/url\]/ig, function (whole, link, text) {
	      let tag = document.createElement('a');
	      tag.href = im_v2_lib_utils.Utils.text.htmlspecialcharsback(link);
	      tag.target = '_blank';
	      tag.text = im_v2_lib_utils.Utils.text.htmlspecialcharsback(text);
	      let allowList = ["http:", "https:", "ftp:", "file:", "tel:", "callto:", "mailto:", "skype:", "viber:"];

	      if (allowList.indexOf(tag.protocol) <= -1) {
	        return whole;
	      }

	      return tag.outerHTML;
	    });
	    text = text.replace(/\[url\]([^\]]+)\[\/url\]/ig, function (whole, link) {
	      link = im_v2_lib_utils.Utils.text.htmlspecialcharsback(link);
	      let tag = document.createElement('a');
	      tag.href = link;
	      tag.target = '_blank';
	      tag.text = link;
	      let allowList = ["http:", "https:", "ftp:", "file:", "tel:", "callto:", "mailto:", "skype:", "viber:"];

	      if (allowList.indexOf(tag.protocol) <= -1) {
	        return whole;
	      }

	      return tag.outerHTML;
	    });
	    text = text.replace(/\[LIKE\]/ig, '<span class="bx-smile bx-im-smile-like"></span>');
	    text = text.replace(/\[DISLIKE\]/ig, '<span class="bx-smile bx-im-smile-dislike"></span>');
	    text = text.replace(/\[BR\]/ig, '<br/>');
	    text = text.replace(/\[([buis])\](.*?)\[(\/[buis])\]/ig, (whole, open, inner, close) => '<' + open + '>' + inner + '<' + close + '>'); // TODO tag USER
	    // this code needs to be ported to im/install/js/im/view/message/body/src/body.js:229

	    text = text.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/ig, (whole, openlines, chatId, inner) => openlines ? inner : '<span class="bx-im-mention" data-type="CHAT" data-value="chat' + chatId + '">' + inner + '</span>'); // TODO tag CHAT

	    text = text.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/ig, (whole, number, text) => '<span class="bx-im-mention" data-type="CALL" data-value="' + im_v2_lib_utils.Utils.text.htmlspecialchars(number) + '">' + text + '</span>'); // TODO tag CHAT

	    text = text.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/ig, (whole, historyId, text) => text); // TODO tag PCH

	    let textElementSize = 0;

	    if (enableBigSmile) {
	      textElementSize = text.replace(/\[icon\=([^\]]*)\]/ig, '').trim().length;
	    }

	    text = text.replace(/\[icon\=([^\]]*)\]/ig, whole => {
	      let url = whole.match(/icon\=(\S+[^\s.,> )\];\'\"!?])/i);

	      if (url && url[1]) {
	        url = url[1];
	      } else {
	        return '';
	      }

	      let attrs = {
	        'src': url,
	        'border': 0
	      };
	      let size = whole.match(/size\=(\d+)/i);

	      if (size && size[1]) {
	        attrs['width'] = size[1];
	        attrs['height'] = size[1];
	      } else {
	        let width = whole.match(/width\=(\d+)/i);

	        if (width && width[1]) {
	          attrs['width'] = width[1];
	        }

	        let height = whole.match(/height\=(\d+)/i);

	        if (height && height[1]) {
	          attrs['height'] = height[1];
	        }

	        if (attrs['width'] && !attrs['height']) {
	          attrs['height'] = attrs['width'];
	        } else if (attrs['height'] && !attrs['width']) {
	          attrs['width'] = attrs['height'];
	        } else if (attrs['height'] && attrs['width']) ; else {
	          attrs['width'] = 20;
	          attrs['height'] = 20;
	        }
	      }

	      attrs['width'] = attrs['width'] > 100 ? 100 : attrs['width'];
	      attrs['height'] = attrs['height'] > 100 ? 100 : attrs['height'];

	      if (enableBigSmile && textElementSize === 0 && attrs['width'] === attrs['height'] && attrs['width'] === 20) {
	        attrs['width'] = 40;
	        attrs['height'] = 40;
	      }

	      let title = whole.match(/title\=(.*[^\s\]])/i);

	      if (title && title[1]) {
	        title = title[1];

	        if (title.indexOf('width=') > -1) {
	          title = title.substr(0, title.indexOf('width='));
	        }

	        if (title.indexOf('height=') > -1) {
	          title = title.substr(0, title.indexOf('height='));
	        }

	        if (title.indexOf('size=') > -1) {
	          title = title.substr(0, title.indexOf('size='));
	        }

	        if (title) {
	          attrs['title'] = im_v2_lib_utils.Utils.text.htmlspecialchars(title).trim();
	          attrs['alt'] = attrs['title'];
	        }
	      }

	      let attributes = '';

	      for (let name in attrs) {
	        if (attrs.hasOwnProperty(name)) {
	          attributes += name + '="' + attrs[name] + '" ';
	        }
	      }

	      return '<img class="bx-smile bx-icon" ' + attributes + '>';
	    });
	    sendReplacement.forEach((value, index) => {
	      text = text.replace('####REPLACEMENT_SEND_' + index + '####', value);
	    });
	    text = text.replace(/\[SEND(?:=(?:.+?))?\](?:.+?)?\[\/SEND]/ig, match => {
	      return match.replace(/\[SEND(?:=(.+))?\](.+?)?\[\/SEND]/ig, (whole, command, text) => {
	        let html = '';
	        text = text ? text : command;
	        command = (command ? command : text).replace('<br />', '\n');

	        if (!textOnly && text) {
	          text = text.replace(/<([\w]+)[^>]*>(.*?)<\\1>/i, "$2", text);
	          text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);
	          command = command.split('####REPLACEMENT_PUT_').join('####REPLACEMENT_SP_');
	          html = '<!--IM_COMMAND_START-->' + '<span class="bx-im-message-command-wrap">' + '<span class="bx-im-message-command" data-entity="send">' + text + '</span>' + '<span class="bx-im-message-command-data">' + command + '</span>' + '</span>' + '<!--IM_COMMAND_END-->';
	        } else {
	          html = text;
	        }

	        return html;
	      });
	    });
	    putReplacement.forEach((value, index) => {
	      text = text.replace('####REPLACEMENT_PUT_' + index + '####', value);
	    });
	    text = text.replace(/\[PUT(?:=(?:.+?))?\](?:.+?)?\[\/PUT]/ig, match => {
	      return match.replace(/\[PUT(?:=(.+))?\](.+?)?\[\/PUT]/ig, (whole, command, text) => {
	        let html = '';
	        text = text ? text : command;
	        command = (command ? command : text).replace('<br />', '\n');

	        if (!textOnly && text) {
	          text = text.replace(/<([\w]+)[^>]*>(.*?)<\/\1>/i, "$2", text);
	          text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);
	          html = '<!--IM_COMMAND_START-->' + '<span class="bx-im-message-command-wrap">' + '<span class="bx-im-message-command" data-entity="put">' + text + '</span>' + '<span class="bx-im-message-command-data">' + command + '</span>' + '</span>' + '<!--IM_COMMAND_END-->';
	        } else {
	          html = text;
	        }

	        return html;
	      });
	    });
	    codeReplacement.forEach((code, index) => {
	      text = text.replace('####REPLACEMENT_CODE_' + index + '####', !textOnly ? '<div class="bx-im-message-content-code">' + code + '</div>' : code);
	    });

	    if (sendReplacement.length > 0) {
	      do {
	        sendReplacement.forEach((value, index) => {
	          text = text.replace('####REPLACEMENT_SEND_' + index + '####', value);
	        });
	      } while (text.includes('####REPLACEMENT_SEND_'));
	    }

	    text = text.split('####REPLACEMENT_SP_').join('####REPLACEMENT_PUT_');

	    if (putReplacement.length > 0) {
	      do {
	        putReplacement.forEach((value, index) => {
	          text = text.replace('####REPLACEMENT_PUT_' + index + '####', value);
	        });
	      } while (text.includes('####REPLACEMENT_PUT_'));
	    }

	    return text;
	  }

	  static hideErrorImage(element) {
	    if (element.parentNode && element.parentNode) {
	      element.parentNode.innerHTML = '<a href="' + element.src + '" target="_blank">' + element.src + '</a>';
	    }

	    return true;
	  }

	  static isTemporaryMessage(element) {
	    return element.id && (im_v2_lib_utils.Utils.types.isUuidV4(element.id) || element.id.toString().startsWith('temporary'));
	  }

	  static getPayloadWithTempMessages(state, payload) {
	    const payloadData = [...payload.data];

	    if (!im_v2_lib_utils.Utils.platform.isBitrixMobile()) {
	      return payloadData;
	    }

	    if (!payload.data || payload.data.length <= 0) {
	      return payloadData;
	    } // consider that in the payload we have messages only for one chat, so we get the value from the first message.


	    const payloadChatId = payload.data[0].chatId;

	    if (!state.collection[payloadChatId]) {
	      return payloadData;
	    }

	    state.collection[payloadChatId].forEach(message => {
	      if (MessagesModel.isTemporaryMessage(message) && !MessagesModel.existsInPayload(payload, message.templateId) && MessagesModel.doesTaskExist(message)) {
	        payloadData.push(message);
	      }
	    });
	    return payloadData;
	  }

	  static existsInPayload(payload, templateId) {
	    return payload.data.find(payloadMessage => payloadMessage.templateId === templateId);
	  }

	  static doesTaskExist(message) {
	    if (Array.isArray(message.params.FILE_ID)) {
	      let foundUploadTasks = false;
	      message.params.FILE_ID.forEach(fileId => {
	        if (!foundUploadTasks) {
	          foundUploadTasks = window.imDialogUploadTasks.find(task => task.taskId.split('|')[1] === fileId);
	        }
	      });
	      return !!foundUploadTasks;
	    }

	    if (message.templateId) {
	      const foundMessageTask = window.imDialogMessagesTasks.find(task => task.taskId.split('|')[1] === message.templateId);
	      return !!foundMessageTask;
	    }

	    return false;
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
	      type: im_v2_const.ChatTypes.chat,
	      name: '',
	      avatar: '',
	      color: '#17A3EA',
	      extranet: false,
	      counter: 0,
	      userCounter: 0,
	      messageCounter: 0,
	      unreadId: 0,
	      lastMessageId: 0,
	      managerList: [],
	      readList: [],
	      writingList: [],
	      muteList: [],
	      textareaMessage: '',
	      quoteId: 0,
	      editId: 0,
	      owner: 0,
	      entityType: '',
	      entityId: '',
	      dateCreate: null,
	      public: {
	        code: '',
	        link: ''
	      }
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
	      getEditId: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return 0;
	        }

	        return state.collection[dialogId].editId;
	      },
	      areUnreadMessagesLoaded: state => dialogId => {
	        const dialog = state.collection[dialogId];

	        if (!dialog || dialog.lastMessageId === 0) {
	          return true;
	        }

	        const messagesCollection = this.store.getters['messages/get'](dialog.chatId);

	        if (messagesCollection.length === 0) {
	          return true;
	        }

	        let lastElementId = 0;

	        for (let index = messagesCollection.length - 1; index >= 0; index--) {
	          const lastElement = messagesCollection[index];

	          if (main_core.Type.isNumber(lastElement.id)) {
	            lastElementId = lastElement.id;
	            break;
	          }
	        }

	        return lastElementId >= dialog.lastMessageId;
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
	              fields: { ...this.getElementState(),
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
	              fields: { ...this.getElementState(),
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

	        store.commit('delete', payload.dialogId);
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
	      addToReadList: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];

	        if (!existingItem) {
	          return false;
	        }

	        const readList = existingItem.readList.filter(el => el.userId !== payload.userId);
	        readList.push({
	          userId: payload.userId,
	          userName: payload.userName || '',
	          messageId: payload.messageId,
	          date: payload.date || new Date()
	        });
	        store.commit('update', {
	          actionName: 'addToReadList',
	          dialogId: payload.dialogId,
	          fields: this.validate({
	            readList
	          })
	        });
	      },
	      removeFromReadList: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];

	        if (!existingItem) {
	          return false;
	        }

	        const readList = existingItem.readList.filter(el => el.userId !== payload.userId);
	        store.commit('update', {
	          actionName: 'removeFromReadList',
	          dialogId: payload.dialogId,
	          fields: this.validate({
	            readList
	          })
	        });
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
	      increaseMessageCounter: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];

	        if (!existingItem) {
	          return false;
	        }

	        store.commit('update', {
	          actionName: 'increaseMessageCount',
	          dialogId: payload.dialogId,
	          fields: {
	            messageCounter: existingItem.messageCounter + payload.count
	          }
	        });
	      },
	      mute: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];

	        if (!existingItem) {
	          return false;
	        }

	        const currentUserId = this.store.state.application.common.userId;

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

	        const currentUserId = this.store.state.application.common.userId;
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
	      }
	    };
	  }

	  getMutations() {
	    return {
	      add: (state, payload) => {
	        state.collection[payload.dialogId] = payload.fields;
	      },
	      update: (state, payload) => {
	        state.collection[payload.dialogId] = { ...state.collection[payload.dialogId],
	          ...payload.fields
	        };
	      },
	      delete: (state, payload) => {
	        delete state.collection[payload.dialogId];
	      },
	      setChatOptions: (state, payload) => {
	        state.chatOptions = payload;
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

	    if (main_core.Type.isNumber(fields.editId)) {
	      result.editId = Number.parseInt(fields.editId, 10);
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

	    if (!main_core.Type.isUndefined(fields.message_count)) {
	      result.messageCounter = fields.message_count;
	    }

	    if (main_core.Type.isNumber(fields.messageCounter) || main_core.Type.isStringFilled(fields.messageCounter)) {
	      result.messageCounter = Number.parseInt(fields.messageCounter, 10);
	    }

	    if (!main_core.Type.isUndefined(fields.unread_id)) {
	      fields.unreadId = fields.unread_id;
	    }

	    if (main_core.Type.isNumber(fields.unreadId) || main_core.Type.isStringFilled(fields.unreadId)) {
	      result.unreadId = Number.parseInt(fields.unreadId, 10);
	    }

	    if (!main_core.Type.isUndefined(fields.last_message_id)) {
	      fields.lastMessageId = fields.last_message_id;
	    }

	    if (main_core.Type.isNumber(fields.lastMessageId) || main_core.Type.isStringFilled(fields.lastMessageId)) {
	      result.lastMessageId = Number.parseInt(fields.lastMessageId, 10);
	    }

	    if (!main_core.Type.isUndefined(fields.textareaMessage)) {
	      result.textareaMessage = fields.textareaMessage.toString();
	    }

	    if (!main_core.Type.isUndefined(fields.title)) {
	      fields.name = fields.title;
	    }

	    if (main_core.Type.isNumber(fields.name) || main_core.Type.isStringFilled(fields.name)) {
	      result.name = im_v2_lib_utils.Utils.text.htmlspecialcharsback(fields.name.toString());
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

	    if (!main_core.Type.isUndefined(fields.readed_list)) {
	      fields.readList = fields.readed_list;
	    }

	    if (main_core.Type.isArray(fields.readList)) {
	      result.readList = this.prepareReadList(fields.readList);
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

	  prepareReadList(readList) {
	    const result = [];
	    readList.forEach(element => {
	      const item = {};

	      if (!main_core.Type.isUndefined(element.user_id)) {
	        element.userId = element.user_id;
	      }

	      if (!main_core.Type.isUndefined(element.user_name)) {
	        element.userName = element.user_name;
	      }

	      if (!main_core.Type.isUndefined(element.message_id)) {
	        element.messageId = element.message_id;
	      }

	      if (!element.userId || !element.userName || !element.messageId) {
	        return false;
	      }

	      item.userId = Number.parseInt(element.userId, 10);
	      item.userName = element.userName.toString();
	      item.messageId = Number.parseInt(element.messageId, 10);
	      item.date = im_v2_lib_utils.Utils.date.cast(element.date);
	      result.push(item);
	    });
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
	          return false;
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
	              fields: { ...this.getElementState(),
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
	              fields: { ...this.getElementState(),
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
	        }

	        state.collection[payload.id] = { ...state.collection[payload.id],
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
	      created: 0,
	      collection: {},
	      index: {}
	    };
	  }

	  getElementState(params = {}) {
	    const {
	      id = 0,
	      chatId = 0,
	      name = 'File is deleted'
	    } = params;
	    return {
	      id,
	      chatId,
	      name,
	      templateId: id,
	      date: new Date(),
	      type: 'file',
	      extension: "",
	      icon: "empty",
	      size: 0,
	      image: false,
	      status: im_v2_const.FileStatus.done,
	      progress: 100,
	      authorId: 0,
	      authorName: "",
	      urlPreview: "",
	      urlShow: "",
	      urlDownload: "",
	      init: false,
	      viewerAttrs: {}
	    };
	  }

	  getGetters() {
	    return {
	      get: state => (chatId, fileId, getTemporary = false) => {
	        if (!chatId || !fileId) {
	          return null;
	        }

	        if (!state.index[chatId] || !state.index[chatId][fileId]) {
	          return null;
	        }

	        if (!getTemporary && !state.index[chatId][fileId].init) {
	          return null;
	        }

	        return state.index[chatId][fileId];
	      },
	      getList: state => chatId => {
	        if (!state.index[chatId]) {
	          return null;
	        }

	        return state.index[chatId];
	      },
	      getBlank: state => params => {
	        return this.getElementState(params);
	      }
	    };
	  }

	  getActions() {
	    return {
	      add: (store, payload) => {
	        let result = this.validate(Object.assign({}, payload));

	        if (payload.id) {
	          result.id = payload.id;
	        } else {
	          result.id = 'temporary' + new Date().getTime() + store.state.created;
	        }

	        result.templateId = result.id;
	        result.init = true;
	        store.commit('add', Object.assign({}, this.getElementState(), result));
	        return result.id;
	      },
	      set: (store, payload) => {
	        if (payload instanceof Array) {
	          payload = payload.map(file => {
	            let result = this.validate(Object.assign({}, file));
	            result.templateId = result.id;
	            return Object.assign({}, this.getElementState(), result, {
	              init: true
	            });
	          });
	        } else {
	          let result = this.validate(Object.assign({}, payload));
	          result.templateId = result.id;
	          payload = [];
	          payload.push(Object.assign({}, this.getElementState(), result, {
	            init: true
	          }));
	        }

	        store.commit('set', {
	          insertType: im_v2_const.MutationType.setAfter,
	          data: payload
	        });
	      },
	      setBefore: (store, payload) => {
	        if (payload instanceof Array) {
	          payload = payload.map(file => {
	            let result = this.validate(Object.assign({}, file));
	            result.templateId = result.id;
	            return Object.assign({}, this.getElementState(), result, {
	              init: true
	            });
	          });
	        } else {
	          let result = this.validate(Object.assign({}, payload));
	          result.templateId = result.id;
	          payload = [];
	          payload.push(Object.assign({}, this.getElementState(), result, {
	            init: true
	          }));
	        }

	        store.commit('set', {
	          actionName: 'setBefore',
	          insertType: im_v2_const.MutationType.setBefore,
	          data: payload
	        });
	      },
	      update: (store, payload) => {
	        let result = this.validate(Object.assign({}, payload.fields));
	        store.commit('initCollection', {
	          chatId: payload.chatId
	        });
	        let index = store.state.collection[payload.chatId].findIndex(el => el.id === payload.id);

	        if (index < 0) {
	          return false;
	        }

	        store.commit('update', {
	          id: payload.id,
	          chatId: payload.chatId,
	          index: index,
	          fields: result
	        });

	        if (payload.fields.blink) {
	          setTimeout(() => {
	            store.commit('update', {
	              id: payload.id,
	              chatId: payload.chatId,
	              fields: {
	                blink: false
	              }
	            });
	          }, 1000);
	        }

	        return true;
	      },
	      delete: (store, payload) => {
	        store.commit('delete', {
	          id: payload.id,
	          chatId: payload.chatId
	        });
	        return true;
	      },
	      saveState: (store, payload) => {
	        store.commit('saveState', {});
	        return true;
	      }
	    };
	  }

	  getMutations() {
	    return {
	      initCollection: (state, payload) => {
	        this.initCollection(state, payload);
	      },
	      add: (state, payload) => {
	        this.initCollection(state, payload);
	        state.collection[payload.chatId].push(payload);
	        state.index[payload.chatId][payload.id] = payload;
	        state.created += 1;
	        this.saveState(state);
	      },
	      set: (state, payload) => {
	        for (let element of payload.data) {
	          this.initCollection(state, {
	            chatId: element.chatId
	          });
	          let index = state.collection[element.chatId].findIndex(el => el.id === element.id);

	          if (index > -1) {
	            delete element.templateId;
	            state.collection[element.chatId][index] = Object.assign(state.collection[element.chatId][index], element);
	          } else if (payload.insertType === im_v2_const.MutationType.setBefore) {
	            state.collection[element.chatId].unshift(element);
	          } else {
	            state.collection[element.chatId].push(element);
	          }

	          state.index[element.chatId][element.id] = element;
	          this.saveState(state);
	        }
	      },
	      update: (state, payload) => {
	        this.initCollection(state, payload);
	        let index = -1;

	        if (typeof payload.index !== 'undefined' && state.collection[payload.chatId][payload.index]) {
	          index = payload.index;
	        } else {
	          index = state.collection[payload.chatId].findIndex(el => el.id === payload.id);
	        }

	        if (index >= 0) {
	          delete payload.fields.templateId;
	          let element = Object.assign(state.collection[payload.chatId][index], payload.fields);
	          state.collection[payload.chatId][index] = element;
	          state.index[payload.chatId][element.id] = element;
	          this.saveState(state);
	        }
	      },
	      delete: (state, payload) => {
	        this.initCollection(state, payload);
	        state.collection[payload.chatId] = state.collection[payload.chatId].filter(element => element.id !== payload.id);
	        delete state.index[payload.chatId][payload.id];
	        this.saveState(state);
	      },
	      saveState: (state, payload) => {
	        this.saveState(state);
	      }
	    };
	  }

	  initCollection(state, payload) {
	    if (typeof state.collection[payload.chatId] !== 'undefined') {
	      return true;
	    }

	    state.collection[payload.chatId] = [];
	    state.index[payload.chatId] = [];
	    return true;
	  }

	  getLoadedState(state) {
	    if (!state || typeof state !== 'object') {
	      return state;
	    }

	    if (typeof state.collection !== 'object') {
	      return state;
	    }

	    state.index = {};

	    for (let chatId in state.collection) {
	      if (!state.collection.hasOwnProperty(chatId)) {
	        continue;
	      }

	      state.index[chatId] = {};
	      state.collection[chatId].filter(file => file != null).forEach(file => {
	        state.index[chatId][file.id] = file;
	      });
	    }

	    return state;
	  }

	  getSaveFileList() {
	    if (!this.db) {
	      return [];
	    }

	    if (!this.store.getters['messages/getSaveFileList']) {
	      return [];
	    }

	    let list = this.store.getters['messages/getSaveFileList']();

	    if (!list) {
	      return [];
	    }

	    return list;
	  }

	  getSaveTimeout() {
	    return 250;
	  }

	  saveState(state) {
	    if (!this.isSaveAvailable()) {
	      return false;
	    }

	    super.saveState(() => {
	      let list = this.getSaveFileList();

	      if (!list) {
	        return false;
	      }

	      let storedState = {
	        collection: {}
	      };

	      for (let chatId in list) {
	        if (!list.hasOwnProperty(chatId)) {
	          continue;
	        }

	        list[chatId].forEach(fileId => {
	          if (!state.index[chatId]) {
	            return false;
	          }

	          if (!state.index[chatId][fileId]) {
	            return false;
	          }

	          if (!storedState.collection[chatId]) {
	            storedState.collection[chatId] = [];
	          }

	          storedState.collection[chatId].push(state.index[chatId][fileId]);
	        });
	      }

	      return storedState;
	    });
	  }

	  validate(fields, options = {}) {
	    const result = {};

	    if (typeof fields.id === "number") {
	      result.id = fields.id;
	    } else if (typeof fields.id === "string") {
	      if (fields.id.startsWith('temporary')) {
	        result.id = fields.id;
	      } else {
	        result.id = parseInt(fields.id);
	      }
	    }

	    if (typeof fields.templateId === "number") {
	      result.templateId = fields.templateId;
	    } else if (typeof fields.templateId === "string") {
	      if (fields.templateId.startsWith('temporary')) {
	        result.templateId = fields.templateId;
	      } else {
	        result.templateId = parseInt(fields.templateId);
	      }
	    }

	    if (typeof fields.chatId === "number" || typeof fields.chatId === "string") {
	      result.chatId = parseInt(fields.chatId);
	    }

	    if (typeof fields.date !== "undefined") {
	      result.date = im_v2_lib_utils.Utils.date.cast(fields.date);
	    }

	    if (typeof fields.type === "string") {
	      result.type = fields.type;
	    }

	    if (typeof fields.extension === "string") {
	      result.extension = fields.extension.toString();

	      if (result.type === 'image') {
	        result.icon = 'img';
	      } else if (result.type === 'video') {
	        result.icon = 'mov';
	      } else {
	        result.icon = FilesModel.getIconType(result.extension);
	      }
	    }

	    if (typeof fields.name === "string" || typeof fields.name === "number") {
	      result.name = fields.name.toString();
	    }

	    if (typeof fields.size === "number" || typeof fields.size === "string") {
	      result.size = parseInt(fields.size);
	    }

	    if (typeof fields.image === 'boolean') {
	      result.image = false;
	    } else if (typeof fields.image === 'object' && fields.image) {
	      result.image = {
	        width: 0,
	        height: 0
	      };

	      if (typeof fields.image.width === "string" || typeof fields.image.width === "number") {
	        result.image.width = parseInt(fields.image.width);
	      }

	      if (typeof fields.image.height === "string" || typeof fields.image.height === "number") {
	        result.image.height = parseInt(fields.image.height);
	      }

	      if (result.image.width <= 0 || result.image.height <= 0) {
	        result.image = false;
	      }
	    }

	    if (typeof fields.status === "string" && typeof im_v2_const.FileStatus[fields.status] !== 'undefined') {
	      result.status = fields.status;
	    }

	    if (typeof fields.progress === "number" || typeof fields.progress === "string") {
	      result.progress = parseInt(fields.progress);
	    }

	    if (typeof fields.authorId === "number" || typeof fields.authorId === "string") {
	      result.authorId = parseInt(fields.authorId);
	    }

	    if (typeof fields.authorName === "string" || typeof fields.authorName === "number") {
	      result.authorName = fields.authorName.toString();
	    }

	    if (typeof fields.urlPreview === 'string') {
	      if (!fields.urlPreview || fields.urlPreview.startsWith('http') || fields.urlPreview.startsWith('bx') || fields.urlPreview.startsWith('file') || fields.urlPreview.startsWith('blob')) {
	        result.urlPreview = fields.urlPreview;
	      } else {
	        result.urlPreview = this.store.state.application.common.host + fields.urlPreview;
	      }
	    }

	    if (typeof fields.urlDownload === 'string') {
	      if (!fields.urlDownload || fields.urlDownload.startsWith('http') || fields.urlDownload.startsWith('bx') || fields.urlPreview.startsWith('file')) {
	        result.urlDownload = fields.urlDownload;
	      } else {
	        result.urlDownload = this.store.state.application.common.host + fields.urlDownload;
	      }
	    }

	    if (typeof fields.urlShow === 'string') {
	      if (!fields.urlShow || fields.urlShow.startsWith('http') || fields.urlShow.startsWith('bx') || fields.urlShow.startsWith('file')) {
	        result.urlShow = fields.urlShow;
	      } else {
	        result.urlShow = this.store.state.application.common.host + fields.urlShow;
	      }
	    }

	    if (typeof fields.viewerAttrs === 'object') {
	      if (result.type === 'image' && !im_v2_lib_utils.Utils.platform.isBitrixMobile()) {
	        result.viewerAttrs = fields.viewerAttrs;
	      }

	      if (result.type === 'video' && !im_v2_lib_utils.Utils.platform.isBitrixMobile() && result.size > FilesModel.maxDiskFileSize) {
	        result.viewerAttrs = fields.viewerAttrs;
	      }
	    }

	    return result;
	  }

	  static getType(type) {
	    type = type.toString().toLowerCase().split('.').splice(-1)[0];

	    switch (type) {
	      case 'png':
	      case 'jpe':
	      case 'jpg':
	      case 'jpeg':
	      case 'gif':
	      case 'heic':
	      case 'bmp':
	      case 'webp':
	        return im_v2_const.FileType.image;

	      case 'mp4':
	      case 'mkv':
	      case 'webm':
	      case 'mpeg':
	      case 'hevc':
	      case 'avi':
	      case '3gp':
	      case 'flv':
	      case 'm4v':
	      case 'ogg':
	      case 'wmv':
	      case 'mov':
	        return im_v2_const.FileType.video;

	      case 'mp3':
	        return im_v2_const.FileType.audio;
	    }

	    return im_v2_const.FileType.file;
	  }

	  static getIconType(extension) {
	    let icon = 'empty';

	    switch (extension.toString()) {
	      case 'png':
	      case 'jpe':
	      case 'jpg':
	      case 'jpeg':
	      case 'gif':
	      case 'heic':
	      case 'bmp':
	      case 'webp':
	        icon = 'img';
	        break;

	      case 'mp4':
	      case 'mkv':
	      case 'webm':
	      case 'mpeg':
	      case 'hevc':
	      case 'avi':
	      case '3gp':
	      case 'flv':
	      case 'm4v':
	      case 'ogg':
	      case 'wmv':
	      case 'mov':
	        icon = 'mov';
	        break;

	      case 'txt':
	        icon = 'txt';
	        break;

	      case 'doc':
	      case 'docx':
	        icon = 'doc';
	        break;

	      case 'xls':
	      case 'xlsx':
	        icon = 'xls';
	        break;

	      case 'php':
	        icon = 'php';
	        break;

	      case 'pdf':
	        icon = 'pdf';
	        break;

	      case 'ppt':
	      case 'pptx':
	        icon = 'ppt';
	        break;

	      case 'rar':
	        icon = 'rar';
	        break;

	      case 'zip':
	      case '7z':
	      case 'tar':
	      case 'gz':
	      case 'gzip':
	        icon = 'zip';
	        break;

	      case 'set':
	        icon = 'set';
	        break;

	      case 'conf':
	      case 'ini':
	      case 'plist':
	        icon = 'set';
	        break;
	    }

	    return icon;
	  }

	}
	FilesModel.maxDiskFileSize = 5242880;

	class RecentModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'recent';
	  }

	  getState() {
	    return {
	      collection: {},
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
	        text: '',
	        date: new Date(),
	        senderId: 0,
	        status: im_v2_const.MessageStatus.received
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
	      state: im_v2_const.RecentCallStatus.waiting
	    };
	  }

	  getGetters() {
	    return {
	      getCollection: state => {
	        return Object.values(state.collection);
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
	      getItemText: state => dialogId => {
	        const currentItem = state.collection[dialogId];

	        if (!currentItem) {
	          return '';
	        }

	        let result = currentItem.message.text; // system mention (get current name from model, otherwise - from code)

	        result = result.replace(/\[user=(\d+) replace](.*?)\[\/user]/gi, (match, userId, userName) => {
	          const user = this.store.getters['users/get'](userId);
	          return user ? user.name : userName;
	        });
	        result = result.replace(/\[user=(\d+)]\[\/user]/gi, (match, userId) => {
	          const user = this.store.getters['users/get'](userId);
	          return user ? user.name : match;
	        }); // custom mention (keep name as it is)

	        return result.replace(/\[user=(\d+)](.+?)\[\/user]/gi, '$2');
	      },
	      needsBirthdayPlaceholder: state => dialogId => {
	        const currentItem = state.collection[dialogId];

	        if (!currentItem) {
	          return false;
	        }

	        const dialog = this.store.getters['dialogues/get'](dialogId);

	        if (!dialog || dialog.type !== im_v2_const.ChatTypes.user) {
	          return false;
	        }

	        const hasBirthday = this.store.getters['users/hasBirthday'](dialogId);

	        if (!hasBirthday) {
	          return false;
	        }

	        const hasTodayMessage = currentItem.message.id > 0 && im_v2_lib_utils.Utils.date.isToday(currentItem.message.date);
	        return state.options.showBirthday && !hasTodayMessage && dialog.counter === 0;
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
	      hasActiveCall: state => {
	        return state.activeCalls.some(item => item.state === im_v2_const.RecentCallStatus.joined);
	      },
	      getOption: state => optionName => {
	        if (!im_v2_const.RecentSettings[optionName]) {
	          return false;
	        }

	        return state.options[optionName];
	      }
	    };
	  }

	  getActions() {
	    return {
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
	              id: existingItem.dialogId,
	              fields: { ...element
	              }
	            });
	          } else {
	            itemsToAdd.push({ ...this.getElementState(),
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
	      update: (store, payload) => {
	        const existingItem = store.state.collection[payload.id];

	        if (!existingItem) {
	          return false;
	        }

	        store.commit('update', {
	          id: existingItem.dialogId,
	          fields: this.validate(payload.fields)
	        });
	      },
	      unread: (store, payload) => {
	        const existingItem = store.state.collection[payload.id];

	        if (!existingItem) {
	          return false;
	        }

	        store.commit('update', {
	          id: existingItem.dialogId,
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
	          id: existingItem.dialogId,
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
	          id: existingItem.dialogId,
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
	          store.commit('add', { ...this.getElementState(),
	            ...newItem
	          });
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
	          id: existingItem.dialogId,
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
	      }
	    };
	  }

	  getMutations() {
	    return {
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
	          id,
	          fields
	        }) => {
	          // if we already got chat - we should not update it with default user chat (unless it's an accepted invitation)
	          const defaultUserElement = fields.options && fields.options.defaultUserRecord && !fields.invitation;

	          if (defaultUserElement) {
	            return false;
	          }

	          const currentElement = state.collection[id];
	          fields.message = { ...currentElement.message,
	            ...fields.message
	          };
	          fields.options = { ...currentElement.options,
	            ...fields.options
	          };
	          state.collection[id] = { ...currentElement,
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
	        state.activeCalls[payload.index] = { ...state.activeCalls[payload.index],
	          ...payload.fields
	        };
	      },
	      deleteActiveCall: (state, payload) => {
	        state.activeCalls.splice(payload.index, 1);
	      },
	      setOptions: (state, payload) => {
	        state.options[payload.option] = payload.value;
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

	  prepareChatType(fields) {
	    if (fields.type === im_v2_const.ChatTypes.user) {
	      return im_v2_const.ChatTypes.user;
	    }

	    if (fields.chat) {
	      return fields.chat.type;
	    }

	    return fields.type;
	  }

	  prepareMessage(fields) {
	    const {
	      message
	    } = this.getElementState();

	    if (main_core.Type.isNumber(fields.message.id)) {
	      message.id = fields.message.id;
	    }

	    if (main_core.Type.isString(fields.message.text)) {
	      const textOptions = {};

	      if (fields.message.withAttach || fields.message.attach) {
	        textOptions.WITH_ATTACH = true;
	      } else if (fields.message.withFile || fields.message.file) {
	        textOptions.WITH_FILE = true;
	      }

	      message.text = this.prepareText(fields.message.text, textOptions);
	    }

	    if (main_core.Type.isDate(fields.message.date) || main_core.Type.isString(fields.message.date)) {
	      message.date = im_v2_lib_utils.Utils.date.cast(fields.message.date);
	    }

	    if (main_core.Type.isNumber(fields.message.author_id)) {
	      message.senderId = fields.message.author_id;
	    }

	    if (main_core.Type.isNumber(fields.message.senderId)) {
	      message.senderId = fields.message.senderId;
	    }

	    if (main_core.Type.isStringFilled(fields.message.status)) {
	      message.status = fields.message.status;
	    }

	    return message;
	  }

	  prepareDraft(fields) {
	    const {
	      draft
	    } = this.getElementState();

	    if (main_core.Type.isString(fields.draft.text)) {
	      draft.text = this.prepareText(fields.draft.text, {});
	    }

	    if (main_core.Type.isStringFilled(draft.text)) {
	      draft.date = new Date();
	    } else {
	      draft.date = null;
	    }

	    return draft;
	  }

	  prepareText(text, options) {
	    let result = text.trim();

	    if (result.startsWith('/me')) {
	      result = result.slice(4);
	    } else if (result.startsWith('/loud')) {
	      result = result.slice(6);
	    }

	    result = result.replace(/<br><br \/>/gi, '<br />');
	    result = result.replace(/<br \/><br>/gi, '<br />');
	    const codeReplacement = [];
	    result = result.replace(/\[code]\n?([\0-\uFFFF]*?)\[\/code]/gi, (whole, group) => {
	      const id = codeReplacement.length;
	      codeReplacement.push(group);
	      return `####REPLACEMENT_CODE_${id}####`;
	    });
	    result = result.replace(/\[put(?:=.+?)?](?:.+?)?\[\/put]/gi, match => {
	      return match.replace(/\[put(?:=(.+))?](.+?)?\[\/put]/gi, (whole, command, textToPut) => {
	        return textToPut || command;
	      });
	    });
	    result = result.replace(/\[send(?:=.+?)?](?:.+?)?\[\/send]/gi, match => {
	      return match.replace(/\[send(?:=(.+))?](.+?)?\[\/send]/gi, (whole, command, textToSend) => {
	        return textToSend || command;
	      });
	    });
	    result = result.replace(/\[[bisu]](.*?)\[\/[bisu]]/gi, '$1');
	    result = result.replace(/\[url](.*?)\[\/url]/gi, '$1');
	    result = result.replace(/\[url=(.*?)](.*?)\[\/url]/gi, '$2');
	    result = result.replace(/\[rating=([1-5])]/gi, () => `[${main_core.Loc.getMessage('IM_UTILS_TEXT_RATING')}] `);
	    result = result.replace(/\[attach=(\d+)]/gi, () => `[${main_core.Loc.getMessage('IM_UTILS_TEXT_ATTACH')}] `);
	    result = result.replace(/\[chat=(\d+)](.*?)\[\/chat]/gi, '$2');
	    result = result.replace(/\[send(?:=.+?)?](.+?)?\[\/send]/gi, '$1');
	    result = result.replace(/\[put(?:=.+?)?](.+?)?\[\/put]/gi, '$1');
	    result = result.replace(/\[call(?:=.+?)?](.*?)\[\/call]/gi, '$1');
	    result = result.replace(/\[pch=(\d+)](.*?)\[\/pch]/gi, '$2');
	    result = result.replace(/<img.*?data-code="([^"]*)".*?>/gi, '$1');
	    result = result.replace(/<span.*?title="([^"]*)".*?>.*?<\/span>/gi, '($1)');
	    result = result.replace(/<img.*?title="([^"]*)".*?>/gi, '($1)');
	    result = result.replace(/<s>([^"]*)<\/s>/gi, ' ');
	    result = result.replace(/\[s]([^"]*)\[\/s]/gi, ' ');
	    result = result.replace(/\[icon=([^\]]*)]/gi, this.prepareIconCode);
	    codeReplacement.forEach((element, index) => {
	      result = result.replace(`####REPLACEMENT_CODE_${index}####`, element);
	    });
	    result = result.replace(/-{54}(.*?)-{54}/gims, `[${main_core.Loc.getMessage('IM_UTILS_TEXT_QUOTE')}] `);
	    result = result.replace(/^(>>(.*)(\n)?)/gim, `[${main_core.Loc.getMessage('IM_UTILS_TEXT_QUOTE')}] `);

	    if (options.WITH_ATTACH && result.length === 0) {
	      result = `[${main_core.Loc.getMessage('IM_UTILS_TEXT_ATTACH')}] ${result}`;
	    } else if (options.WITH_FILE && result.length === 0) {
	      result = `[${main_core.Loc.getMessage('IM_UTILS_TEXT_FILE')}] ${result}`;
	    }

	    result = result.replace(/\n/gi, ' ').trim();
	    const SPLIT_INDEX = 24;
	    const UNSEEN_SPACE = '\u200B';

	    if (result.length > SPLIT_INDEX) {
	      let firstPart = result.slice(0, SPLIT_INDEX + 1);
	      const secondPart = result.slice(SPLIT_INDEX + 1);
	      const hasWhitespace = /\s/.test(firstPart);
	      const hasUserCode = /\[user=(\d+)](.*?)\[\/user]/i.test(result);

	      if (firstPart.length === SPLIT_INDEX + 1 && !hasWhitespace && !hasUserCode) {
	        firstPart += UNSEEN_SPACE;
	      }

	      result = firstPart + secondPart;
	    }

	    return result;
	  }

	  prepareIconCode(wholeMatch) {
	    let title = wholeMatch.match(/title=(.*[^\s\]])/i);

	    if (title && title[1]) {
	      // eslint-disable-next-line prefer-destructuring
	      title = title[1];

	      if (title.includes('width=')) {
	        title = title.slice(0, Math.max(0, title.indexOf('width=')));
	      }

	      if (title.includes('height=')) {
	        title = title.slice(0, Math.max(0, title.indexOf('height=')));
	      }

	      if (title.includes('size=')) {
	        title = title.slice(0, Math.max(0, title.indexOf('size=')));
	      }

	      if (title) {
	        title = `(${title.trim()})`;
	      }
	    } else {
	      title = `(${main_core.Loc.getMessage('IM_UTILS_TEXT_ICON')})`;
	    }

	    return title;
	  }

	  prepareActiveCall(call) {
	    return { ...this.getActiveCallDefaultState(),
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
	exports.MessagesModel = MessagesModel;
	exports.DialoguesModel = DialoguesModel;
	exports.UsersModel = UsersModel;
	exports.FilesModel = FilesModel;
	exports.RecentModel = RecentModel;

}((this.BX.Messenger.v2.Model = this.BX.Messenger.v2.Model || {}),BX.Vue3,BX.Messenger.v2.Lib,BX.Event,BX,BX.Vue3.Vuex,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
