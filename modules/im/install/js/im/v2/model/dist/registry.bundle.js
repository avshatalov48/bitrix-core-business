/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_lib_user,im_v2_lib_userStatus,im_v2_lib_logger,im_v2_lib_channel,im_v2_lib_utils,im_v2_const,im_v2_application_core,ui_vue3_vuex,main_core,im_v2_model) {
	'use strict';

	const isNumberOrString = target => {
	  return main_core.Type.isNumber(target) || main_core.Type.isString(target);
	};
	const convertToString = target => {
	  return target.toString();
	};
	const convertToNumber = target => {
	  return Number.parseInt(target, 10);
	};
	const convertToDate = target => {
	  return im_v2_lib_utils.Utils.date.cast(target, false);
	};
	const SNAKE_CASE_REGEXP = /(_[\da-z])/gi;
	const convertObjectKeysToCamelCase = targetObject => {
	  const resultObject = {};
	  Object.entries(targetObject).forEach(([key, value]) => {
	    const newKey = prepareKey(key);
	    if (main_core.Type.isPlainObject(value)) {
	      resultObject[newKey] = convertObjectKeysToCamelCase(value);
	      return;
	    }
	    if (main_core.Type.isArray(value)) {
	      resultObject[newKey] = convertArrayItemsKeysToCamelCase(value);
	      return;
	    }
	    resultObject[newKey] = value;
	  });
	  return resultObject;
	};
	const prepareKey = rawKey => {
	  let key = rawKey;
	  if (key.search(SNAKE_CASE_REGEXP) !== -1) {
	    key = key.toLowerCase();
	  }
	  return main_core.Text.toCamelCase(key);
	};
	const convertArrayItemsKeysToCamelCase = targetArray => {
	  return targetArray.map(arrayItem => {
	    if (!main_core.Type.isPlainObject(arrayItem)) {
	      return arrayItem;
	    }
	    return convertObjectKeysToCamelCase(arrayItem);
	  });
	};

	const SortWeight = {
	  im: 10
	};
	const prepareNotificationSettings = target => {
	  const result = {};
	  const sortedTarget = sortNotificationSettingsBlock(target);
	  sortedTarget.forEach(block => {
	    const preparedItems = {};
	    block.notices.forEach(item => {
	      preparedItems[item.id] = item;
	    });
	    result[block.id] = {
	      id: block.id,
	      label: block.label,
	      items: preparedItems
	    };
	  });
	  return result;
	};
	const sortNotificationSettingsBlock = target => {
	  return [...target].sort((a, b) => {
	    var _SortWeight$a$id, _SortWeight$b$id;
	    const weightA = (_SortWeight$a$id = SortWeight[a.id]) != null ? _SortWeight$a$id : 0;
	    const weightB = (_SortWeight$b$id = SortWeight[b.id]) != null ? _SortWeight$b$id : 0;
	    return weightB - weightA;
	  });
	};

	const settingsFieldsConfig = [{
	  fieldName: im_v2_const.Settings.notification.enableSound,
	  targetFieldName: im_v2_const.Settings.notification.enableSound,
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: im_v2_const.Settings.notification.enableAutoRead,
	  targetFieldName: im_v2_const.Settings.notification.enableAutoRead,
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: im_v2_const.Settings.notification.mode,
	  targetFieldName: im_v2_const.Settings.notification.mode,
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: im_v2_const.Settings.notification.enableWeb,
	  targetFieldName: im_v2_const.Settings.notification.enableWeb,
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: im_v2_const.Settings.notification.enableMail,
	  targetFieldName: im_v2_const.Settings.notification.enableMail,
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: im_v2_const.Settings.notification.enablePush,
	  targetFieldName: im_v2_const.Settings.notification.enablePush,
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'notifications',
	  targetFieldName: 'notifications',
	  checkFunction: main_core.Type.isArray,
	  formatFunction: prepareNotificationSettings
	}, {
	  fieldName: im_v2_const.Settings.message.bigSmiles,
	  targetFieldName: im_v2_const.Settings.message.bigSmiles,
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: im_v2_const.Settings.appearance.background,
	  targetFieldName: im_v2_const.Settings.appearance.background,
	  checkFunction: isNumberOrString,
	  formatFunction: convertToNumber
	}, {
	  fieldName: im_v2_const.Settings.appearance.alignment,
	  targetFieldName: im_v2_const.Settings.appearance.alignment,
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: im_v2_const.Settings.recent.showBirthday,
	  targetFieldName: im_v2_const.Settings.recent.showBirthday,
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: im_v2_const.Settings.recent.showInvited,
	  targetFieldName: im_v2_const.Settings.recent.showInvited,
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: im_v2_const.Settings.recent.showLastMessage,
	  targetFieldName: im_v2_const.Settings.recent.showLastMessage,
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: im_v2_const.Settings.hotkey.sendByEnter,
	  targetFieldName: im_v2_const.Settings.hotkey.sendByEnter,
	  checkFunction: main_core.Type.isString,
	  formatFunction: target => {
	    return target === '1';
	  }
	}, {
	  fieldName: im_v2_const.Settings.hotkey.sendByEnter,
	  targetFieldName: im_v2_const.Settings.hotkey.sendByEnter,
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: im_v2_const.Settings.desktop.enableRedirect,
	  targetFieldName: im_v2_const.Settings.desktop.enableRedirect,
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: im_v2_const.Settings.user.status,
	  targetFieldName: im_v2_const.Settings.user.status,
	  checkFunction: main_core.Type.isString
	}];

	const formatFieldsWithConfig = (fields, config) => {
	  const resultObject = {};
	  const rawFields = convertObjectKeysToCamelCase(fields);
	  config.forEach(fieldConfig => {
	    const {
	      fieldName,
	      targetFieldName,
	      checkFunction,
	      formatFunction
	    } = fieldConfig;

	    // check if field exists
	    const foundFieldName = getValidFieldName(rawFields, fieldName);
	    if (!foundFieldName) {
	      return;
	    }

	    // validate value
	    if (!isFieldValueValid(rawFields[foundFieldName], checkFunction)) {
	      return;
	    }

	    // format value
	    resultObject[targetFieldName] = formatFieldValue({
	      fieldValue: rawFields[foundFieldName],
	      formatFunction,
	      currentResult: resultObject,
	      rawFields: fields
	    });
	  });
	  return resultObject;
	};
	const getValidFieldName = (fields, fieldName) => {
	  let fieldNameList = fieldName;
	  if (main_core.Type.isStringFilled(fieldNameList)) {
	    fieldNameList = [fieldNameList];
	  }
	  for (const singleField of fieldNameList) {
	    if (!main_core.Type.isUndefined(fields[singleField])) {
	      return singleField;
	    }
	  }
	  return null;
	};
	const isFieldValueValid = (field, checkFunction) => {
	  let checkFunctionList = checkFunction;
	  if (main_core.Type.isUndefined(checkFunctionList)) {
	    return true;
	  }
	  if (main_core.Type.isFunction(checkFunctionList)) {
	    checkFunctionList = [checkFunctionList];
	  }
	  return checkFunctionList.some(singleFunction => singleFunction(field));
	};
	const formatFieldValue = params => {
	  const {
	    fieldValue,
	    formatFunction,
	    currentResult,
	    rawFields
	  } = params;
	  if (main_core.Type.isUndefined(formatFunction)) {
	    return fieldValue;
	  }
	  return formatFunction(fieldValue, currentResult, rawFields);
	};

	/* eslint-disable no-param-reassign */
	class SettingsModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      [im_v2_const.Settings.appearance.background]: 1,
	      [im_v2_const.Settings.appearance.alignment]: im_v2_const.DialogAlignment.left,
	      [im_v2_const.Settings.notification.enableSound]: true,
	      [im_v2_const.Settings.notification.enableAutoRead]: true,
	      [im_v2_const.Settings.notification.mode]: im_v2_const.NotificationSettingsMode.simple,
	      [im_v2_const.Settings.notification.enableWeb]: true,
	      [im_v2_const.Settings.notification.enableMail]: true,
	      [im_v2_const.Settings.notification.enablePush]: true,
	      notifications: {},
	      [im_v2_const.Settings.message.bigSmiles]: true,
	      [im_v2_const.Settings.recent.showBirthday]: true,
	      [im_v2_const.Settings.recent.showInvited]: true,
	      [im_v2_const.Settings.recent.showLastMessage]: true,
	      [im_v2_const.Settings.desktop.enableRedirect]: true
	    };
	  }
	  getGetters() {
	    return {
	      /** @function application/settings/get */
	      get: state => key => {
	        return state[key];
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function application/settings/set */
	      set: (store, payload) => {
	        store.commit('set', this.formatFields(payload));
	      },
	      /** @function application/settings/setNotificationOption */
	      setNotificationOption: (store, payload) => {
	        store.commit('setNotificationOption', payload);
	      }
	    };
	  }
	  getMutations() {
	    return {
	      set: (state, payload) => {
	        Object.entries(payload).forEach(([key, value]) => {
	          state[key] = value;
	        });
	      },
	      setNotificationOption: (state, payload) => {
	        var _moduleOptions$items;
	        const {
	          moduleId,
	          optionName,
	          type,
	          value
	        } = payload;
	        const moduleOptions = state.notifications[moduleId];
	        if (!(moduleOptions != null && (_moduleOptions$items = moduleOptions.items) != null && _moduleOptions$items[optionName])) {
	          return;
	        }
	        moduleOptions.items[optionName][type] = value;
	      }
	    };
	  }
	  formatFields(fields) {
	    return formatFieldsWithConfig(fields, settingsFieldsConfig);
	  }
	}

	const tariffRestrictionsFieldsConfig = [{
	  fieldName: 'fullChatHistory',
	  targetFieldName: 'fullChatHistory',
	  checkFunction: main_core.Type.isPlainObject
	}];

	/* eslint-disable no-param-reassign */
	class TariffRestrictionsModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      fullChatHistory: {
	        isAvailable: true,
	        limitDays: null
	      }
	    };
	  }
	  getGetters() {
	    return {
	      /** @function application/tariffRestrictions/get */
	      get: state => {
	        return state;
	      },
	      /** @function application/tariffRestrictions/isHistoryAvailable */
	      isHistoryAvailable: state => {
	        var _state$fullChatHistor, _state$fullChatHistor2;
	        return (_state$fullChatHistor = (_state$fullChatHistor2 = state.fullChatHistory) == null ? void 0 : _state$fullChatHistor2.isAvailable) != null ? _state$fullChatHistor : false;
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function application/tariffRestrictions/set */
	      set: (store, payload) => {
	        store.commit('set', this.formatFields(payload));
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
	  formatFields(fields) {
	    return formatFieldsWithConfig(fields, tariffRestrictionsFieldsConfig);
	  }
	}

	class ApplicationModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'application';
	  }
	  getNestedModules() {
	    return {
	      settings: SettingsModel,
	      tariffRestrictions: TariffRestrictionsModel
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
	      /** @function application/getLayout */
	      getLayout: state => {
	        return state.layout;
	      },
	      /** @function application/isChatOpen */
	      isChatOpen: state => dialogId => {
	        const allowedLayouts = [im_v2_const.Layout.chat.name, im_v2_const.Layout.copilot.name, im_v2_const.Layout.channel.name, im_v2_const.Layout.collab.name];
	        if (!allowedLayouts.includes(state.layout.name)) {
	          return false;
	        }
	        return state.layout.entityId === dialogId.toString();
	      },
	      isLinesChatOpen: state => dialogId => {
	        if (state.layout.name !== im_v2_const.Layout.openlines.name && state.layout.name !== im_v2_const.Layout.openlinesV2.name) {
	          return false;
	        }
	        return state.layout.entityId === dialogId.toString();
	      },
	      /** @function application/areNotificationsOpen */
	      areNotificationsOpen: state => {
	        return state.layout.name === im_v2_const.Layout.notification.name;
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function application/setLayout */
	      setLayout: (store, payload) => {
	        const {
	          name,
	          entityId = '',
	          contextId = 0
	        } = payload;
	        if (!main_core.Type.isStringFilled(name)) {
	          return;
	        }
	        const previousLayout = {
	          ...store.state.layout
	        };
	        const newLayout = {
	          name: this.validateLayout(name),
	          entityId: this.validateLayoutEntityId(name, entityId),
	          contextId
	        };
	        if (previousLayout.name === newLayout.name && previousLayout.entityId === newLayout.entityId) {
	          return;
	        }
	        store.commit('updateLayout', {
	          layout: newLayout
	        });
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.layout.onLayoutChange, {
	          from: previousLayout,
	          to: newLayout
	        });
	      }
	    };
	  }

	  /* eslint-disable no-param-reassign */
	  getMutations() {
	    return {
	      updateLayout: (state, payload) => {
	        state.layout = {
	          ...state.layout,
	          ...payload.layout
	        };
	      }
	    };
	  }
	  validateLayout(name) {
	    if (!im_v2_const.Layout[name]) {
	      return im_v2_const.Layout.chat.name;
	    }
	    return name;
	  }
	  validateLayoutEntityId(name, entityId) {
	    if (!im_v2_const.Layout[name]) {
	      return '';
	    }

	    // TODO check `entityId` by layout name

	    return entityId;
	  }
	}

	const prepareComponentId = componentId => {
	  const supportedComponents = Object.values(im_v2_const.MessageComponent);
	  if (!supportedComponents.includes(componentId)) {
	    return im_v2_const.MessageComponent.unsupported;
	  }
	  return componentId;
	};
	const prepareAuthorId = (target, currentResult, rawFields) => {
	  if (main_core.Type.isString(rawFields.system) && rawFields.system === 'Y') {
	    return 0;
	  }
	  if (main_core.Type.isBoolean(rawFields.isSystem) && rawFields.isSystem === true) {
	    return 0;
	  }
	  return convertToNumber(target);
	};
	const prepareKeyboard = rawKeyboardButtons => {
	  return rawKeyboardButtons.map(rawButton => {
	    return {
	      ...rawButton,
	      block: rawButton.block === 'Y',
	      disabled: rawButton.disabled === 'Y',
	      vote: rawButton.vote === 'Y',
	      wait: rawButton.wait === 'Y'
	    };
	  });
	};

	const messageFieldsConfig = [{
	  fieldName: ['id', 'temporaryId'],
	  targetFieldName: 'id',
	  checkFunction: [main_core.Type.isNumber, im_v2_lib_utils.Utils.text.isTempMessage]
	}, {
	  fieldName: 'chatId',
	  targetFieldName: 'chatId',
	  checkFunction: isNumberOrString,
	  formatFunction: convertToNumber
	}, {
	  fieldName: 'date',
	  targetFieldName: 'date',
	  checkFunction: [main_core.Type.isString, main_core.Type.isDate],
	  formatFunction: im_v2_lib_utils.Utils.date.cast
	}, {
	  fieldName: 'text',
	  targetFieldName: 'text',
	  checkFunction: isNumberOrString,
	  formatFunction: convertToString
	}, {
	  fieldName: ['senderId', 'authorId'],
	  targetFieldName: 'authorId',
	  checkFunction: isNumberOrString,
	  formatFunction: prepareAuthorId
	}, {
	  fieldName: 'sending',
	  targetFieldName: 'sending',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'unread',
	  targetFieldName: 'unread',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'viewed',
	  targetFieldName: 'viewed',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'viewedByOthers',
	  targetFieldName: 'viewedByOthers',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'error',
	  targetFieldName: 'error',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'componentId',
	  targetFieldName: 'componentId',
	  checkFunction: target => {
	    return main_core.Type.isString(target) && target !== '';
	  },
	  formatFunction: prepareComponentId
	}, {
	  fieldName: 'componentParams',
	  targetFieldName: 'componentParams',
	  checkFunction: main_core.Type.isPlainObject
	}, {
	  fieldName: ['files', 'fileId'],
	  targetFieldName: 'files',
	  checkFunction: main_core.Type.isArray
	}, {
	  fieldName: 'attach',
	  targetFieldName: 'attach',
	  checkFunction: [main_core.Type.isArray, main_core.Type.isBoolean, main_core.Type.isString]
	}, {
	  fieldName: 'keyboard',
	  targetFieldName: 'keyboard',
	  checkFunction: main_core.Type.isArray,
	  formatFunction: prepareKeyboard
	}, {
	  fieldName: 'keyboard',
	  targetFieldName: 'keyboard',
	  checkFunction: target => target === 'N',
	  formatFunction: () => []
	}, {
	  fieldName: 'isEdited',
	  targetFieldName: 'isEdited',
	  checkFunction: main_core.Type.isString,
	  formatFunction: target => target === 'Y'
	}, {
	  fieldName: 'isEdited',
	  targetFieldName: 'isEdited',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'isDeleted',
	  targetFieldName: 'isDeleted',
	  checkFunction: main_core.Type.isString,
	  formatFunction: target => target === 'Y'
	}, {
	  fieldName: 'isDeleted',
	  targetFieldName: 'isDeleted',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'replyId',
	  targetFieldName: 'replyId',
	  checkFunction: isNumberOrString,
	  formatFunction: convertToNumber
	}, {
	  fieldName: 'forward',
	  targetFieldName: 'forward',
	  checkFunction: main_core.Type.isPlainObject
	}];

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

	const Reaction = Object.freeze({
	  like: 'like',
	  kiss: 'kiss',
	  laugh: 'laugh',
	  wonder: 'wonder',
	  cry: 'cry',
	  angry: 'angry',
	  facepalm: 'facepalm'
	});
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
	        if (!Reaction[payload.reaction]) {
	          return;
	        }
	        if (!store.state.collection[payload.messageId]) {
	          store.state.collection[payload.messageId] = this.getElementState();
	        }
	        store.commit('setReaction', payload);
	      },
	      removeReaction: (store, payload) => {
	        if (!store.state.collection[payload.messageId] || !Reaction[payload.reaction]) {
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

	const commentFieldsConfig = [{
	  fieldName: 'chatId',
	  targetFieldName: 'chatId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'lastUserIds',
	  targetFieldName: 'lastUserIds',
	  checkFunction: main_core.Type.isArray
	}, {
	  fieldName: 'messageCount',
	  targetFieldName: 'messageCount',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'messageId',
	  targetFieldName: 'messageId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'isUserSubscribed',
	  targetFieldName: 'isUserSubscribed',
	  checkFunction: main_core.Type.isBoolean
	}];

	const LAST_USERS_TO_SHOW = 3;
	var _formatFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("formatFields");
	var _isMessageAuthor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isMessageAuthor");
	class CommentsModel extends ui_vue3_vuex.BuilderModel {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _isMessageAuthor, {
	      value: _isMessageAuthor2
	    });
	    Object.defineProperty(this, _formatFields, {
	      value: _formatFields2
	    });
	  }
	  getState() {
	    return {
	      collection: {},
	      layout: {
	        opened: false,
	        channelDialogId: '',
	        postId: 0
	      }
	    };
	  }
	  getElementState() {
	    return {
	      chatId: 0,
	      lastUserIds: [],
	      messageCount: 0,
	      messageId: 0,
	      isUserSubscribed: false
	    };
	  }
	  getGetters() {
	    return {
	      /** @function messages/comments/getByMessageId */
	      getByMessageId: state => messageId => {
	        var _state$collection$mes;
	        return (_state$collection$mes = state.collection[messageId]) != null ? _state$collection$mes : this.getElementState();
	      },
	      /** @function messages/comments/getMessageIdByChatId */
	      getMessageIdByChatId: state => chatId => {
	        const collection = Object.values(state.collection);
	        const foundItem = collection.find(item => {
	          return item.chatId === chatId;
	        });
	        return foundItem == null ? void 0 : foundItem.messageId;
	      },
	      /** @function messages/comments/isUserSubscribed */
	      isUserSubscribed: state => messageId => {
	        var _element$isUserSubscr;
	        const element = state.collection[messageId];
	        if (!element && babelHelpers.classPrivateFieldLooseBase(this, _isMessageAuthor)[_isMessageAuthor](messageId)) {
	          return true;
	        }
	        return (_element$isUserSubscr = element == null ? void 0 : element.isUserSubscribed) != null ? _element$isUserSubscr : false;
	      },
	      /** @function messages/comments/areOpened */
	      areOpened: state => {
	        return state.layout.opened;
	      },
	      /** @function messages/comments/areOpenedForChannel */
	      areOpenedForChannel: state => channelDialogId => {
	        return state.layout.channelDialogId === channelDialogId;
	      },
	      /** @function messages/comments/areOpenedForChannelPost */
	      areOpenedForChannelPost: state => postId => {
	        return state.layout.postId === postId;
	      },
	      /** @function messages/comments/getOpenedChannelId */
	      getOpenedChannelId: state => {
	        var _state$layout$channel;
	        return (_state$layout$channel = state.layout.channelDialogId) != null ? _state$layout$channel : '';
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function messages/comments/set */
	      set: (store, rawPayload) => {
	        let payload = rawPayload;
	        if (!payload) {
	          return;
	        }
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        payload = payload.map(item => {
	          const currentItem = store.state.collection[item.messageId];
	          if (currentItem) {
	            return {
	              ...currentItem,
	              ...babelHelpers.classPrivateFieldLooseBase(this, _formatFields)[_formatFields](item)
	            };
	          }
	          return {
	            ...this.getElementState(),
	            isUserSubscribed: babelHelpers.classPrivateFieldLooseBase(this, _isMessageAuthor)[_isMessageAuthor](item.messageId),
	            ...babelHelpers.classPrivateFieldLooseBase(this, _formatFields)[_formatFields](item)
	          };
	        });
	        store.commit('set', payload);
	      },
	      /** @function messages/comments/setLastUser */
	      setLastUser: (store, payload) => {
	        const {
	          messageId,
	          newUserId
	        } = payload;
	        const currentItem = store.state.collection[messageId];
	        if (!currentItem || newUserId === 0) {
	          return;
	        }
	        store.commit('setLastUser', payload);
	      },
	      /** @function messages/comments/subscribe */
	      subscribe: (store, messageId) => {
	        im_v2_application_core.Core.getStore().dispatch('messages/comments/set', {
	          messageId,
	          isUserSubscribed: true
	        });
	      },
	      /** @function messages/comments/unsubscribe */
	      unsubscribe: (store, messageId) => {
	        im_v2_application_core.Core.getStore().dispatch('messages/comments/set', {
	          messageId,
	          isUserSubscribed: false
	        });
	      },
	      /** @function messages/comments/setOpened */
	      setOpened: (store, payload) => {
	        store.commit('setOpened', payload);
	      },
	      /** @function messages/comments/setClosed */
	      setClosed: store => {
	        store.commit('setClosed');
	      }
	    };
	  }

	  /* eslint-disable no-param-reassign */
	  getMutations() {
	    return {
	      set: (state, payload) => {
	        payload.forEach(item => {
	          state.collection[item.messageId] = item;
	        });
	      },
	      setLastUser: (state, payload) => {
	        const {
	          messageId,
	          newUserId
	        } = payload;
	        const {
	          lastUserIds: currentUsers
	        } = state.collection[messageId];
	        if (currentUsers.includes(newUserId)) {
	          return;
	        }
	        if (currentUsers.length < LAST_USERS_TO_SHOW) {
	          currentUsers.unshift(newUserId);
	          return;
	        }
	        currentUsers.pop();
	        currentUsers.unshift(newUserId);
	      },
	      setOpened: (state, payload) => {
	        const {
	          channelDialogId,
	          commentsPostId
	        } = payload;
	        state.layout = {
	          opened: true,
	          channelDialogId,
	          postId: commentsPostId
	        };
	      },
	      setClosed: state => {
	        state.layout = {
	          opened: false,
	          channelDialogId: '',
	          commentsPostId: 0
	        };
	      }
	    };
	  }
	}
	function _formatFields2(fields) {
	  return im_v2_model.formatFieldsWithConfig(fields, commentFieldsConfig);
	}
	function _isMessageAuthor2(messageId) {
	  const message = im_v2_application_core.Core.getStore().getters['messages/getById'](messageId);
	  return (message == null ? void 0 : message.authorId) === im_v2_application_core.Core.getUserId();
	}

	class SelectModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: new Set(),
	      isBulkActionsMode: false
	    };
	  }
	  getGetters() {
	    return {
	      /** @function messages/select/getCollection */
	      getCollection: state => {
	        return state.collection;
	      },
	      /** @function messages/select/getBulkActionsMode */
	      getBulkActionsMode: state => {
	        return state.isBulkActionsMode;
	      },
	      /** @function messages/select/isMessageSelected */
	      isMessageSelected: state => messageId => {
	        return state.collection.has(messageId);
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function messages/select/toggle */
	      toggle: (store, messageId) => {
	        if (store.state.collection.has(messageId)) {
	          store.commit('delete', messageId);
	        } else {
	          store.commit('add', messageId);
	        }
	      },
	      /** @function messages/select/deleteByMessageId */
	      deleteByMessageId: (store, messageId) => {
	        if (!store.state.collection.has(messageId)) {
	          return;
	        }
	        store.commit('delete', messageId);
	      },
	      /** @function messages/select/toggleBulkActionsMode */
	      toggleBulkActionsMode: (store, active) => {
	        store.commit('toggleBulkActionsMode', active);
	      },
	      /** @function messages/select/clear */
	      clear: store => {
	        store.commit('clear');
	      }
	    };
	  }
	  getMutations() {
	    return {
	      add: (state, messageId) => {
	        state.collection.add(messageId);
	      },
	      delete: (state, messageId) => {
	        state.collection.delete(messageId);
	      },
	      toggleBulkActionsMode: (state, active) => {
	        // eslint-disable-next-line no-param-reassign
	        state.isBulkActionsMode = active;
	      },
	      clear: state => {
	        state.collection.clear();
	      }
	    };
	  }
	}

	var _findNextLoadingMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("findNextLoadingMessages");
	var _formatFields$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("formatFields");
	var _needToSwapAuthorId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needToSwapAuthorId");
	var _prepareSwapAuthorId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareSwapAuthorId");
	var _getMaxMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMaxMessageId");
	var _findLowestMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("findLowestMessageId");
	var _findMaxMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("findMaxMessageId");
	var _findLastOwnMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("findLastOwnMessageId");
	var _findFirstUnread = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("findFirstUnread");
	var _sortCollection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sortCollection");
	var _makeFakePreviousSiblingId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("makeFakePreviousSiblingId");
	class MessagesModel extends ui_vue3_vuex.BuilderModel {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _makeFakePreviousSiblingId, {
	      value: _makeFakePreviousSiblingId2
	    });
	    Object.defineProperty(this, _sortCollection, {
	      value: _sortCollection2
	    });
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
	    Object.defineProperty(this, _prepareSwapAuthorId, {
	      value: _prepareSwapAuthorId2
	    });
	    Object.defineProperty(this, _needToSwapAuthorId, {
	      value: _needToSwapAuthorId2
	    });
	    Object.defineProperty(this, _formatFields$1, {
	      value: _formatFields2$1
	    });
	    Object.defineProperty(this, _findNextLoadingMessages, {
	      value: _findNextLoadingMessages2
	    });
	  }
	  getName() {
	    return 'messages';
	  }
	  getNestedModules() {
	    return {
	      pin: PinModel,
	      reactions: ReactionsModel,
	      comments: CommentsModel,
	      select: SelectModel
	    };
	  }
	  getState() {
	    return {
	      collection: {},
	      chatCollection: {},
	      loadingMessages: {}
	    };
	  }
	  getElementState() {
	    return {
	      id: 0,
	      chatId: 0,
	      authorId: 0,
	      replyId: 0,
	      date: new Date(),
	      text: '',
	      files: [],
	      attach: [],
	      keyboard: [],
	      unread: false,
	      viewed: true,
	      viewedByOthers: false,
	      sending: false,
	      error: false,
	      componentId: im_v2_const.MessageComponent.default,
	      componentParams: {},
	      forward: {
	        id: '',
	        userId: 0
	      },
	      isEdited: false,
	      isDeleted: false
	    };
	  }

	  // eslint-disable-next-line max-lines-per-function
	  getGetters() {
	    return {
	      /** @function messages/getByChatId */
	      getByChatId: (state, getters) => chatId => {
	        if (!state.chatCollection[chatId]) {
	          return [];
	        }
	        const fakeFirstMessage = {
	          id: babelHelpers.classPrivateFieldLooseBase(this, _makeFakePreviousSiblingId)[_makeFakePreviousSiblingId](chatId)
	        };
	        const firstLoadingMessages = babelHelpers.classPrivateFieldLooseBase(this, _findNextLoadingMessages)[_findNextLoadingMessages](fakeFirstMessage, getters);
	        return [...state.chatCollection[chatId]].map(messageId => {
	          return state.collection[messageId];
	        }).sort(babelHelpers.classPrivateFieldLooseBase(this, _sortCollection)[_sortCollection]).reduce((acc, message) => {
	          acc.push(message, ...babelHelpers.classPrivateFieldLooseBase(this, _findNextLoadingMessages)[_findNextLoadingMessages](message, getters));
	          return acc;
	        }, [...firstLoadingMessages]);
	      },
	      /** @function messages/getById */
	      getById: state => id => {
	        return state.collection[id];
	      },
	      /** @function messages/getByIdList */
	      getByIdList: state => idList => {
	        const result = [];
	        idList.forEach(id => {
	          if (state.collection[id]) {
	            result.push(state.collection[id]);
	          }
	        });
	        return result;
	      },
	      /** @function messages/hasMessage */
	      hasMessage: state => ({
	        chatId,
	        messageId
	      }) => {
	        if (!state.chatCollection[chatId]) {
	          return false;
	        }
	        return state.chatCollection[chatId].has(messageId);
	      },
	      /** @function messages/isForward */
	      isForward: state => id => {
	        const message = state.collection[id];
	        if (!message) {
	          return false;
	        }
	        return main_core.Type.isStringFilled(message.forward.id);
	      },
	      /** @function messages/isInChatCollection */
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
	      /** @function messages/getFirstId */
	      getFirstId: state => chatId => {
	        if (!state.chatCollection[chatId]) {
	          return 0;
	        }
	        return babelHelpers.classPrivateFieldLooseBase(this, _findLowestMessageId)[_findLowestMessageId](state, chatId);
	      },
	      /** @function messages/getLastId */
	      getLastId: state => chatId => {
	        if (!state.chatCollection[chatId]) {
	          return 0;
	        }
	        return babelHelpers.classPrivateFieldLooseBase(this, _findMaxMessageId)[_findMaxMessageId](state, chatId);
	      },
	      /** @function messages/getLastOwnMessageId */
	      getLastOwnMessageId: state => chatId => {
	        if (!state.chatCollection[chatId]) {
	          return 0;
	        }
	        return babelHelpers.classPrivateFieldLooseBase(this, _findLastOwnMessageId)[_findLastOwnMessageId](state, chatId);
	      },
	      /** @function messages/getFirstUnread */
	      getFirstUnread: state => chatId => {
	        if (!state.chatCollection[chatId]) {
	          return 0;
	        }
	        return babelHelpers.classPrivateFieldLooseBase(this, _findFirstUnread)[_findFirstUnread](state, chatId);
	      },
	      /** @function messages/getChatUnreadMessages */
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
	      /** @function messages/getMessageFiles */
	      getMessageFiles: state => payload => {
	        const messageId = payload;
	        if (!state.collection[messageId]) {
	          return [];
	        }
	        return state.collection[messageId].files.map(fileId => {
	          return this.store.getters['files/get'](fileId, true);
	        });
	      },
	      /** @function messages/getMessageType */
	      getMessageType: state => messageId => {
	        const message = state.collection[messageId];
	        if (!message) {
	          return null;
	        }
	        const currentUserId = im_v2_application_core.Core.getUserId();
	        if (message.authorId === 0) {
	          return im_v2_const.MessageType.system;
	        }
	        if (message.authorId === currentUserId) {
	          return im_v2_const.MessageType.self;
	        }
	        return im_v2_const.MessageType.opponent;
	      },
	      /** @function messages/getPreviousMessage */
	      getPreviousMessage: state => payload => {
	        const {
	          messageId,
	          chatId
	        } = payload;
	        const message = state.collection[messageId];
	        if (!message) {
	          return null;
	        }
	        const chatCollection = [...state.chatCollection[chatId]];
	        const initialMessageIndex = chatCollection.indexOf(messageId);
	        const desiredMessageId = chatCollection[initialMessageIndex - 1];
	        if (!desiredMessageId) {
	          return null;
	        }
	        return state.collection[desiredMessageId];
	      },
	      findPreviousMessageId: (state, getters) => payload => {
	        const chatCollection = getters.getByChatId(payload.chatId);
	        const currentMessageIndex = chatCollection.findIndex(message => {
	          return message.id === payload.messageId;
	        });
	        if (currentMessageIndex > 0) {
	          return chatCollection[currentMessageIndex - 1].id;
	        }
	        return -1;
	      },
	      findLastChatMessageId: (state, getters) => chatId => {
	        const lastMessage = getters.getByChatId(chatId).pop();
	        if (lastMessage) {
	          return lastMessage.id;
	        }
	        return -1;
	      },
	      hasLoadingMessageByPreviousSiblingId: state => messageId => {
	        return Boolean(state.loadingMessages[messageId]);
	      },
	      getLoadingMessageByPreviousSiblingId: state => messageId => {
	        var _state$loadingMessage;
	        return (_state$loadingMessage = state.loadingMessages[messageId]) != null ? _state$loadingMessage : null;
	      },
	      getLoadingMessageByMessageId: state => messageId => {
	        const message = Object.values(state.loadingMessages).find(currentMessage => {
	          return currentMessage.id === messageId;
	        });
	        if (message) {
	          return message;
	        }
	        return null;
	      },
	      hasLoadingMessageByMessageId: (state, getters) => messageId => {
	        return getters.getLoadingMessageByMessageId(messageId) !== null;
	      },
	      isRealMessage: () => messageId => {
	        return !im_v2_lib_utils.Utils.text.isTempMessage(messageId);
	      }
	    };
	  }

	  // eslint-disable-next-line max-lines-per-function
	  getActions() {
	    return {
	      /** @function messages/setChatCollection */
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
	            ...babelHelpers.classPrivateFieldLooseBase(this, _formatFields$1)[_formatFields$1](message)
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
	      /** @function messages/store */
	      store: (store, payload) => {
	        let preparedMessages = payload;
	        if (main_core.Type.isPlainObject(payload)) {
	          preparedMessages = [payload];
	        }
	        preparedMessages = preparedMessages.map(message => {
	          const currentMessage = store.state.collection[message.id];
	          if (currentMessage) {
	            return {
	              ...currentMessage,
	              ...babelHelpers.classPrivateFieldLooseBase(this, _formatFields$1)[_formatFields$1](message)
	            };
	          }
	          return {
	            ...this.getElementState(),
	            ...babelHelpers.classPrivateFieldLooseBase(this, _formatFields$1)[_formatFields$1](message)
	          };
	        });
	        if (preparedMessages.length === 0) {
	          return;
	        }
	        store.commit('store', {
	          messages: preparedMessages
	        });
	      },
	      /** @function messages/add */
	      add: (store, payload) => {
	        const message = {
	          ...this.getElementState(),
	          ...babelHelpers.classPrivateFieldLooseBase(this, _formatFields$1)[_formatFields$1](payload)
	        };
	        store.commit('store', {
	          messages: [message]
	        });
	        store.commit('setChatCollection', {
	          messages: [message]
	        });
	        return message.id;
	      },
	      /** @function messages/updateWithId */
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
	          fields: babelHelpers.classPrivateFieldLooseBase(this, _formatFields$1)[_formatFields$1](fields)
	        });
	      },
	      /** @function messages/update */
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
	            ...babelHelpers.classPrivateFieldLooseBase(this, _formatFields$1)[_formatFields$1](fields)
	          }
	        });
	      },
	      /** @function messages/readMessages */
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
	      /** @function messages/setViewedByOthers */
	      setViewedByOthers: (store, payload) => {
	        const {
	          ids
	        } = payload;
	        store.commit('setViewedByOthers', {
	          ids
	        });
	      },
	      /** @function messages/delete */
	      delete: (store, payload) => {
	        const {
	          id
	        } = payload;
	        if (!store.state.collection[id]) {
	          return;
	        }
	        if (store.getters.hasLoadingMessageByPreviousSiblingId(id)) {
	          const currentMessage = store.state.collection[id];
	          const newPreviousMessageId = store.getters.findPreviousMessageId({
	            messageId: currentMessage.id,
	            chatId: currentMessage.chatId
	          });
	          store.commit('updateLoadingMessagePreviousSiblingId', {
	            oldId: currentMessage.id,
	            newId: newPreviousMessageId
	          });
	        }
	        if (store.getters.hasLoadingMessageByMessageId(id)) {
	          store.commit('deleteLoadingMessageByMessageId', {
	            messageId: id
	          });
	        }
	        store.commit('delete', {
	          id
	        });
	      },
	      /** @function messages/clearChatCollection */
	      clearChatCollection: (store, payload) => {
	        const {
	          chatId
	        } = payload;
	        store.commit('clearCollection', {
	          chatId
	        });
	      },
	      /** @function messages/deleteAttach */
	      deleteAttach: (store, payload) => {
	        const {
	          messageId,
	          attachId
	        } = payload;
	        const message = store.state.collection[messageId];
	        if (!message || !main_core.Type.isArray(message.attach)) {
	          return;
	        }
	        const attach = message.attach.filter(attachItem => {
	          return attachId !== attachItem.id;
	        });
	        store.commit('update', {
	          id: messageId,
	          fields: {
	            ...message,
	            ...babelHelpers.classPrivateFieldLooseBase(this, _formatFields$1)[_formatFields$1]({
	              attach
	            })
	          }
	        });
	      },
	      /** @function messages/addLoadingMessage */
	      addLoadingMessage: (store, payload) => {
	        const message = {
	          ...this.getElementState(),
	          ...babelHelpers.classPrivateFieldLooseBase(this, _formatFields$1)[_formatFields$1](payload.message)
	        };
	        store.commit('store', {
	          messages: [message]
	        });
	        if (!store.state.chatCollection[message.chatId]) {
	          store.commit('initChatCollection', {
	            chatId: message.chatId
	          });
	        }
	        const previousSiblingId = (() => {
	          const id = store.getters.findLastChatMessageId(message.chatId);
	          if (id === -1) {
	            return babelHelpers.classPrivateFieldLooseBase(this, _makeFakePreviousSiblingId)[_makeFakePreviousSiblingId](message.chatId);
	          }
	          return id;
	        })();
	        store.commit('addLoadingMessage', {
	          message,
	          previousSiblingId
	        });
	      },
	      /** @function messages/deleteLoadingMessageByMessageId */
	      deleteLoadingMessageByMessageId: (store, payload) => {
	        store.commit('deleteLoadingMessageByMessageId', {
	          ...payload
	        });
	      }
	    };
	  }

	  /* eslint-disable no-param-reassign */
	  // eslint-disable-next-line max-lines-per-function
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
	      initChatCollection: (state, payload) => {
	        if (!state.chatCollection[payload.chatId]) {
	          state.chatCollection[payload.chatId] = new Set();
	        }
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
	        var _state$chatCollection2;
	        im_v2_lib_logger.Logger.warn('Messages model: delete mutation', payload);
	        const {
	          id
	        } = payload;
	        const {
	          chatId
	        } = state.collection[id];
	        (_state$chatCollection2 = state.chatCollection[chatId]) == null ? void 0 : _state$chatCollection2.delete(id);
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
	      },
	      addLoadingMessage: (state, payload) => {
	        const {
	          message,
	          previousSiblingId
	        } = payload;
	        state.loadingMessages[previousSiblingId] = message;
	      },
	      deleteLoadingMessageByMessageId: (state, payload) => {
	        const entries = Object.entries(state.loadingMessages);
	        const entry = entries.find(([, message]) => {
	          return message.id === payload.messageId;
	        });
	        if (entry) {
	          const [previousSiblingId] = entry;
	          delete state.loadingMessages[previousSiblingId];
	        }
	      },
	      updateLoadingMessagePreviousSiblingId: (state, payload) => {
	        const {
	          oldId,
	          newId
	        } = payload;
	        const loadingMessage = state.loadingMessages[oldId];
	        if (loadingMessage) {
	          delete state.loadingMessages[oldId];
	          state.loadingMessages[newId] = loadingMessage;
	        }
	      }
	    };
	  }
	}
	function _findNextLoadingMessages2(message, getters) {
	  if (getters.hasLoadingMessageByPreviousSiblingId(message.id)) {
	    const loadingMessage = getters.getLoadingMessageByPreviousSiblingId(message.id);
	    return [loadingMessage, ...babelHelpers.classPrivateFieldLooseBase(this, _findNextLoadingMessages)[_findNextLoadingMessages](loadingMessage, getters)];
	  }
	  return [];
	}
	function _formatFields2$1(rawFields) {
	  const messageParams = main_core.Type.isPlainObject(rawFields.params) ? rawFields.params : {};
	  const fields = {
	    ...rawFields,
	    ...messageParams
	  };
	  const formattedFields = im_v2_model.formatFieldsWithConfig(fields, messageFieldsConfig);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _needToSwapAuthorId)[_needToSwapAuthorId](formattedFields, messageParams)) {
	    formattedFields.authorId = babelHelpers.classPrivateFieldLooseBase(this, _prepareSwapAuthorId)[_prepareSwapAuthorId](formattedFields, messageParams);
	  }
	  return formattedFields;
	}
	function _needToSwapAuthorId2(formattedFields, messageParams) {
	  const {
	    NAME: name,
	    USER_ID: userId
	  } = messageParams;
	  return Boolean(name && userId && formattedFields.authorId);
	}
	function _prepareSwapAuthorId2(formattedFields, messageParams) {
	  const {
	    NAME: authorName,
	    USER_ID: userId,
	    AVATAR: avatar
	  } = messageParams;
	  const originalAuthorId = formattedFields.authorId;
	  const fakeAuthorId = convertToNumber(userId);
	  const userManager = new im_v2_lib_user.UserManager();
	  const networkId = `${im_v2_const.UserIdNetworkPrefix}-${originalAuthorId}-${fakeAuthorId}`;
	  void userManager.setUsersToModel({
	    networkId,
	    name: authorName,
	    avatar: avatar != null ? avatar : ''
	  });
	  return networkId;
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
	function _sortCollection2(a, b) {
	  if (im_v2_lib_utils.Utils.text.isUuidV4(a.id) && !im_v2_lib_utils.Utils.text.isUuidV4(b.id)) {
	    return 1;
	  }
	  if (!im_v2_lib_utils.Utils.text.isUuidV4(a.id) && im_v2_lib_utils.Utils.text.isUuidV4(b.id)) {
	    return -1;
	  }
	  if (im_v2_lib_utils.Utils.text.isUuidV4(a.id) && im_v2_lib_utils.Utils.text.isUuidV4(b.id)) {
	    return a.date.getTime() - b.date.getTime();
	  }
	  return a.id - b.id;
	}
	function _makeFakePreviousSiblingId2(chatId) {
	  return `${chatId}/-1`;
	}

	const prepareManagerList = managerList => {
	  const result = [];
	  managerList.forEach(rawUserId => {
	    const userId = Number.parseInt(rawUserId, 10);
	    if (userId > 0) {
	      result.push(userId);
	    }
	  });
	  return result;
	};
	const prepareChatName = chatName => {
	  return main_core.Text.decode(chatName.toString());
	};
	const prepareAvatar = avatar => {
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
	};
	const prepareWritingList = writingList => {
	  const result = [];
	  writingList.forEach(element => {
	    const item = {};
	    if (!element.userId) {
	      return;
	    }
	    item.userId = Number.parseInt(element.userId, 10);
	    item.userName = main_core.Text.decode(element.userName);
	    result.push(item);
	  });
	  return result;
	};
	const prepareMuteList = muteList => {
	  const result = [];
	  if (main_core.Type.isArray(muteList)) {
	    muteList.forEach(rawUserId => {
	      const userId = Number.parseInt(rawUserId, 10);
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
	};
	const prepareLastMessageViews = rawLastMessageViews => {
	  const {
	    countOfViewers,
	    firstViewers: rawFirstViewers,
	    messageId
	  } = rawLastMessageViews;
	  let firstViewer = null;
	  for (const rawFirstViewer of rawFirstViewers) {
	    if (rawFirstViewer.userId === im_v2_application_core.Core.getUserId()) {
	      continue;
	    }
	    firstViewer = {
	      userId: rawFirstViewer.userId,
	      userName: rawFirstViewer.userName,
	      date: im_v2_lib_utils.Utils.date.cast(rawFirstViewer.date)
	    };
	    break;
	  }
	  if (countOfViewers > 0 && !firstViewer) {
	    throw new Error('Chats model: no first viewer for message');
	  }
	  return {
	    countOfViewers,
	    firstViewer,
	    messageId
	  };
	};

	const chatFieldsConfig = [{
	  fieldName: 'dialogId',
	  targetFieldName: 'dialogId',
	  checkFunction: isNumberOrString,
	  formatFunction: convertToString
	}, {
	  fieldName: ['id', 'chatId'],
	  targetFieldName: 'chatId',
	  checkFunction: isNumberOrString,
	  formatFunction: convertToNumber
	}, {
	  fieldName: 'type',
	  targetFieldName: 'type',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'quoteId',
	  targetFieldName: 'quoteId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'counter',
	  targetFieldName: 'counter',
	  checkFunction: isNumberOrString,
	  formatFunction: convertToNumber
	}, {
	  fieldName: 'userCounter',
	  targetFieldName: 'userCounter',
	  checkFunction: isNumberOrString,
	  formatFunction: convertToNumber
	}, {
	  fieldName: 'lastId',
	  targetFieldName: 'lastReadId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'markedId',
	  targetFieldName: 'markedId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'lastMessageId',
	  targetFieldName: 'lastMessageId',
	  checkFunction: isNumberOrString,
	  formatFunction: convertToNumber
	}, {
	  fieldName: 'lastMessageViews',
	  targetFieldName: 'lastMessageViews',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: prepareLastMessageViews
	}, {
	  fieldName: 'hasPrevPage',
	  targetFieldName: 'hasPrevPage',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'hasNextPage',
	  targetFieldName: 'hasNextPage',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'savedPositionMessageId',
	  targetFieldName: 'savedPositionMessageId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: ['title', 'name'],
	  targetFieldName: 'name',
	  checkFunction: isNumberOrString,
	  formatFunction: prepareChatName
	}, {
	  fieldName: ['owner', 'ownerId'],
	  targetFieldName: 'ownerId',
	  checkFunction: isNumberOrString,
	  formatFunction: convertToNumber
	}, {
	  fieldName: 'avatar',
	  targetFieldName: 'avatar',
	  checkFunction: main_core.Type.isString,
	  formatFunction: prepareAvatar
	}, {
	  fieldName: 'color',
	  targetFieldName: 'color',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'extranet',
	  targetFieldName: 'extranet',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'entityLink',
	  targetFieldName: 'entityLink',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: target => {
	    return im_v2_model.formatFieldsWithConfig(target, chatEntityFieldsConfig);
	  }
	}, {
	  fieldName: 'dateCreate',
	  targetFieldName: 'dateCreate',
	  formatFunction: im_v2_lib_utils.Utils.date.cast
	}, {
	  fieldName: 'public',
	  targetFieldName: 'public',
	  checkFunction: main_core.Type.isPlainObject
	}, {
	  fieldName: 'writingList',
	  targetFieldName: 'writingList',
	  checkFunction: main_core.Type.isArray,
	  formatFunction: prepareWritingList
	}, {
	  fieldName: 'managerList',
	  targetFieldName: 'managerList',
	  checkFunction: main_core.Type.isArray,
	  formatFunction: prepareManagerList
	}, {
	  fieldName: 'muteList',
	  targetFieldName: 'muteList',
	  checkFunction: [main_core.Type.isArray, main_core.Type.isPlainObject],
	  formatFunction: prepareMuteList
	}, {
	  fieldName: 'inited',
	  targetFieldName: 'inited',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'loading',
	  targetFieldName: 'loading',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'description',
	  targetFieldName: 'description',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'diskFolderId',
	  targetFieldName: 'diskFolderId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'role',
	  targetFieldName: 'role',
	  checkFunction: main_core.Type.isString,
	  formatFunction: target => target.toLowerCase()
	}, {
	  fieldName: 'permissions',
	  targetFieldName: 'permissions',
	  checkFunction: main_core.Type.isPlainObject
	}, {
	  fieldName: 'tariffRestrictions',
	  targetFieldName: 'tariffRestrictions',
	  checkFunction: main_core.Type.isPlainObject
	}, {
	  fieldName: 'parentChatId',
	  targetFieldName: 'parentChatId',
	  checkFunction: main_core.Type.isNumber
	}];
	const chatEntityFieldsConfig = [{
	  fieldName: 'type',
	  targetFieldName: 'type',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'url',
	  targetFieldName: 'url',
	  checkFunction: main_core.Type.isString
	}];

	const collabFieldsConfig = [{
	  fieldName: 'collabId',
	  targetFieldName: 'collabId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'guestCount',
	  targetFieldName: 'guestCount',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'entities',
	  targetFieldName: 'entities',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: target => im_v2_model.formatFieldsWithConfig(target, collabEntitiesFieldConfig)
	}];
	const collabEntitiesFieldConfig = [{
	  fieldName: 'tasks',
	  targetFieldName: 'tasks',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: target => im_v2_model.formatFieldsWithConfig(target, collabEntityFieldConfig)
	}, {
	  fieldName: 'files',
	  targetFieldName: 'files',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: target => im_v2_model.formatFieldsWithConfig(target, collabEntityFieldConfig)
	}, {
	  fieldName: 'calendar',
	  targetFieldName: 'calendar',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: target => im_v2_model.formatFieldsWithConfig(target, collabEntityFieldConfig)
	}];
	const collabEntityFieldConfig = [{
	  fieldName: 'counter',
	  targetFieldName: 'counter',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'url',
	  targetFieldName: 'url',
	  checkFunction: main_core.Type.isStringFilled
	}];

	var _formatFields$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("formatFields");
	class CollabsModel extends ui_vue3_vuex.BuilderModel {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _formatFields$2, {
	      value: _formatFields2$2
	    });
	  }
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getElementState() {
	    return {
	      collabId: 0,
	      guestCount: 0,
	      entities: {
	        tasks: {
	          counter: 0,
	          url: ''
	        },
	        files: {
	          counter: 0,
	          url: ''
	        },
	        calendar: {
	          counter: 0,
	          url: ''
	        }
	      }
	    };
	  }
	  getGetters() {
	    return {
	      /** @function chats/collabs/getByChatId */
	      getByChatId: state => chatId => {
	        var _state$collection$cha;
	        return (_state$collection$cha = state.collection[chatId]) != null ? _state$collection$cha : this.getElementState();
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function chats/collabs/set */
	      set: (store, payload) => {
	        const {
	          chatId,
	          collabInfo
	        } = payload;
	        if (!main_core.Type.isPlainObject(collabInfo)) {
	          return;
	        }
	        store.commit('set', {
	          chatId,
	          collabInfo: babelHelpers.classPrivateFieldLooseBase(this, _formatFields$2)[_formatFields$2](collabInfo)
	        });
	      },
	      /** @function chats/collabs/setCounter */
	      setCounter: (store, payload) => {
	        const {
	          chatId,
	          entity,
	          counter
	        } = payload;
	        const state = store.state;
	        const currentRecord = state.collection[chatId];
	        if (!currentRecord || !currentRecord.entities[entity]) {
	          return;
	        }
	        store.commit('setCounter', {
	          chatId,
	          entity,
	          counter
	        });
	      },
	      /** @function chats/collabs/setGuestCount */
	      setGuestCount: (store, payload) => {
	        const {
	          chatId,
	          guestCount
	        } = payload;
	        const state = store.state;
	        const currentRecord = state.collection[chatId];
	        if (!currentRecord) {
	          return;
	        }
	        store.commit('setGuestCount', {
	          chatId,
	          guestCount
	        });
	      }
	    };
	  }

	  /* eslint-disable no-param-reassign */
	  getMutations() {
	    return {
	      set: (state, payload) => {
	        const {
	          chatId,
	          collabInfo
	        } = payload;
	        state.collection[chatId] = collabInfo;
	      },
	      setCounter: (state, payload) => {
	        const {
	          chatId,
	          entity,
	          counter
	        } = payload;
	        const currentRecord = state.collection[chatId];
	        currentRecord.entities[entity].counter = counter;
	      },
	      setGuestCount: (state, payload) => {
	        const {
	          chatId,
	          guestCount
	        } = payload;
	        const currentRecord = state.collection[chatId];
	        currentRecord.guestCount = guestCount;
	      }
	    };
	  }
	}
	function _formatFields2$2(fields) {
	  return im_v2_model.formatFieldsWithConfig(fields, collabFieldsConfig);
	}

	/* eslint-disable no-param-reassign */
	class ChatsModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'chats';
	  }
	  getNestedModules() {
	    return {
	      collabs: CollabsModel
	    };
	  }
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getElementState() {
	    return {
	      dialogId: '0',
	      chatId: 0,
	      type: im_v2_const.ChatType.chat,
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
	      quoteId: 0,
	      ownerId: 0,
	      entityLink: {},
	      dateCreate: null,
	      public: {
	        code: '',
	        link: ''
	      },
	      inited: false,
	      loading: false,
	      hasPrevPage: false,
	      hasNextPage: false,
	      diskFolderId: 0,
	      role: im_v2_const.UserRole.guest,
	      permissions: {
	        manageUi: im_v2_const.UserRole.none,
	        manageSettings: im_v2_const.UserRole.none,
	        manageUsersAdd: im_v2_const.UserRole.none,
	        manageUsersDelete: im_v2_const.UserRole.none,
	        manageMessages: im_v2_const.UserRole.none
	      },
	      tariffRestrictions: {
	        isHistoryLimitExceeded: false
	      },
	      parentChatId: 0
	    };
	  }

	  // eslint-disable-next-line max-lines-per-function
	  getGetters() {
	    return {
	      /** @function chats/get */
	      get: state => (dialogId, getBlank = false) => {
	        if (!state.collection[dialogId] && getBlank) {
	          return this.getElementState();
	        }
	        if (!state.collection[dialogId] && !getBlank) {
	          return null;
	        }
	        return state.collection[dialogId];
	      },
	      /** @function chats/getByChatId */
	      getByChatId: state => (chatId, getBlank = false) => {
	        const preparedChatId = Number.parseInt(chatId, 10);
	        const chat = Object.values(state.collection).find(item => {
	          return item.chatId === preparedChatId;
	        });
	        if (!chat && getBlank) {
	          return this.getElementState();
	        }
	        return chat;
	      },
	      /** @function chats/getQuoteId */
	      getQuoteId: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return 0;
	        }
	        return state.collection[dialogId].quoteId;
	      },
	      /** @function chats/isUser */
	      isUser: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return false;
	        }
	        return state.collection[dialogId].type === im_v2_const.ChatType.user;
	      },
	      /** @function chats/getLastReadId */
	      getLastReadId: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return 0;
	        }
	        const {
	          lastReadId
	        } = state.collection[dialogId];
	        const lastReadIdMessage = im_v2_application_core.Core.getStore().getters['messages/getById'](lastReadId);
	        if (!lastReadIdMessage) {
	          return 0;
	        }
	        return lastReadId;
	      },
	      /** @function chats/getInitialMessageId */
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
	      },
	      /** @function chats/isSupport */
	      isSupport: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return false;
	        }
	        return state.collection[dialogId].type === im_v2_const.ChatType.support24Question;
	      }
	    };
	  }

	  // eslint-disable-next-line max-lines-per-function
	  getActions() {
	    return {
	      /** @function chats/set */
	      set: (store, rawPayload) => {
	        let payload = rawPayload;
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        payload.map(element => {
	          return this.formatFields(element);
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
	      /** @function chats/add */
	      add: (store, rawPayload) => {
	        let payload = rawPayload;
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        payload.map(element => {
	          return this.formatFields(element);
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
	      /** @function chats/update */
	      update: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return;
	        }
	        store.commit('update', {
	          dialogId: payload.dialogId,
	          fields: this.formatFields(payload.fields)
	        });
	      },
	      /** @function chats/delete */
	      delete: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return;
	        }
	        store.commit('delete', {
	          dialogId: payload.dialogId
	        });
	      },
	      /** @function chats/clearCounters */
	      clearCounters: store => {
	        store.commit('clearCounters');
	      },
	      /** @function chats/mute */
	      mute: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return;
	        }
	        const currentUserId = im_v2_application_core.Core.getUserId();
	        if (existingItem.muteList.includes(currentUserId)) {
	          return;
	        }
	        const muteList = [...existingItem.muteList, currentUserId];
	        store.commit('update', {
	          actionName: 'mute',
	          dialogId: payload.dialogId,
	          fields: this.formatFields({
	            muteList
	          })
	        });
	      },
	      /** @function chats/unmute */
	      unmute: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return;
	        }
	        const currentUserId = im_v2_application_core.Core.getUserId();
	        const muteList = existingItem.muteList.filter(item => item !== currentUserId);
	        store.commit('update', {
	          actionName: 'unmute',
	          dialogId: payload.dialogId,
	          fields: this.formatFields({
	            muteList
	          })
	        });
	      },
	      /** @function chats/setLastMessageViews */
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
	          return;
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
	          dialogId,
	          fields: {
	            lastMessageViews: newLastMessageViews
	          }
	        });
	      },
	      /** @function chats/clearLastMessageViews */
	      clearLastMessageViews: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return;
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
	      /** @function chats/incrementLastMessageViews */
	      incrementLastMessageViews: (store, payload) => {
	        const existingItem = store.state.collection[payload.dialogId];
	        if (!existingItem) {
	          return;
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
	      clearCounters: state => {
	        Object.keys(state.collection).forEach(key => {
	          state.collection[key].counter = 0;
	          state.collection[key].markedId = 0;
	        });
	      }
	    };
	  }
	  formatFields(fields) {
	    return im_v2_model.formatFieldsWithConfig(fields, chatFieldsConfig);
	  }
	}

	class BotsModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getElementState() {
	    return {
	      code: '',
	      type: im_v2_const.BotType.bot,
	      appId: '',
	      isHidden: false,
	      isSupportOpenline: false,
	      isHuman: false
	    };
	  }
	  getGetters() {
	    return {
	      /** @function users/bots/getByUserId */
	      getByUserId: state => userId => {
	        return state.collection[userId];
	      },
	      /** @function users/bots/isNetwork */
	      isNetwork: state => userId => {
	        var _state$collection$use;
	        return ((_state$collection$use = state.collection[userId]) == null ? void 0 : _state$collection$use.type) === im_v2_const.BotType.network;
	      },
	      /** @function users/bots/isSupport */
	      isSupport: state => userId => {
	        var _state$collection$use2;
	        return ((_state$collection$use2 = state.collection[userId]) == null ? void 0 : _state$collection$use2.type) === im_v2_const.BotType.support24;
	      },
	      /** @function users/bots/getCopilotUserId */
	      getCopilotUserId: state => {
	        for (const [userId, bot] of Object.entries(state.collection)) {
	          if (bot.code === im_v2_const.BotCode.copilot) {
	            return Number.parseInt(userId, 10);
	          }
	        }
	        return null;
	      },
	      /** @function users/bots/isCopilot */
	      isCopilot: (state, getters) => userId => {
	        const copilotUserId = getters.getCopilotUserId;
	        return copilotUserId === Number.parseInt(userId, 10);
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function users/bots/set */
	      set: (store, payload) => {
	        const {
	          userId,
	          botData
	        } = payload;
	        if (!botData) {
	          return;
	        }
	        store.commit('set', {
	          userId,
	          botData: {
	            ...this.getElementState(),
	            ...this.formatFields(botData)
	          }
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      set: (state, payload) => {
	        const {
	          userId,
	          botData
	        } = payload;
	        // eslint-disable-next-line no-param-reassign
	        state.collection[userId] = botData;
	      }
	    };
	  }
	  formatFields(fields) {
	    const result = convertObjectKeysToCamelCase(fields);
	    if (result.type === im_v2_const.RawBotType.human) {
	      result.type = im_v2_const.BotType.bot;
	      result.isHuman = true;
	    }
	    const TYPES_MAPPED_TO_DEFAULT_BOT = [im_v2_const.RawBotType.openline, im_v2_const.RawBotType.supervisor];
	    if (TYPES_MAPPED_TO_DEFAULT_BOT.includes(result.type)) {
	      result.type = im_v2_const.BotType.bot;
	    }
	    return result;
	  }
	}

	const prepareAvatar$1 = avatar => {
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
	};
	const prepareDepartments = departments => {
	  const result = [];
	  departments.forEach(rawDepartmentId => {
	    const departmentId = Number.parseInt(rawDepartmentId, 10);
	    if (departmentId > 0) {
	      result.push(departmentId);
	    }
	  });
	  return result;
	};
	const preparePhones = phones => {
	  const result = {};
	  if (main_core.Type.isStringFilled(phones.workPhone) || main_core.Type.isNumber(phones.workPhone)) {
	    result.workPhone = phones.workPhone.toString();
	  }
	  if (main_core.Type.isStringFilled(phones.personalMobile) || main_core.Type.isNumber(phones.personalMobile)) {
	    result.personalMobile = phones.personalMobile.toString();
	  }
	  if (main_core.Type.isStringFilled(phones.personalPhone) || main_core.Type.isNumber(phones.personalPhone)) {
	    result.personalPhone = phones.personalPhone.toString();
	  }
	  if (main_core.Type.isStringFilled(phones.innerPhone) || main_core.Type.isNumber(phones.innerPhone)) {
	    result.innerPhone = phones.innerPhone.toString();
	  }
	  return result;
	};

	const userFieldsConfig = [{
	  fieldName: 'id',
	  targetFieldName: 'id',
	  checkFunction: isNumberOrString,
	  formatFunction: convertToNumber
	}, {
	  fieldName: 'networkId',
	  targetFieldName: 'id',
	  checkFunction: im_v2_lib_utils.Utils.user.isNetworkUserId
	}, {
	  fieldName: 'firstName',
	  targetFieldName: 'firstName',
	  checkFunction: main_core.Type.isString,
	  formatFunction: main_core.Text.decode
	}, {
	  fieldName: 'lastName',
	  targetFieldName: 'lastName',
	  checkFunction: main_core.Type.isString,
	  formatFunction: main_core.Text.decode
	}, {
	  fieldName: 'name',
	  targetFieldName: 'name',
	  checkFunction: main_core.Type.isString,
	  formatFunction: main_core.Text.decode
	}, {
	  fieldName: 'color',
	  targetFieldName: 'color',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'avatar',
	  targetFieldName: 'avatar',
	  checkFunction: main_core.Type.isString,
	  formatFunction: prepareAvatar$1
	}, {
	  fieldName: 'workPosition',
	  targetFieldName: 'workPosition',
	  checkFunction: main_core.Type.isString,
	  formatFunction: main_core.Text.decode
	}, {
	  fieldName: 'gender',
	  targetFieldName: 'gender',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'birthday',
	  targetFieldName: 'birthday',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'isBirthday',
	  targetFieldName: 'isBirthday',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'isAdmin',
	  targetFieldName: 'isAdmin',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'type',
	  targetFieldName: 'type',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'network',
	  targetFieldName: 'network',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'connector',
	  targetFieldName: 'connector',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'externalAuthId',
	  targetFieldName: 'externalAuthId',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'status',
	  targetFieldName: 'status',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'idle',
	  targetFieldName: 'idle',
	  formatFunction: convertToDate
	}, {
	  fieldName: 'lastActivityDate',
	  targetFieldName: 'lastActivityDate',
	  formatFunction: convertToDate
	}, {
	  fieldName: 'mobileLastDate',
	  targetFieldName: 'mobileLastDate',
	  formatFunction: convertToDate
	}, {
	  fieldName: 'absent',
	  targetFieldName: 'absent',
	  formatFunction: convertToDate
	}, {
	  fieldName: 'isAbsent',
	  targetFieldName: 'isAbsent',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'departments',
	  targetFieldName: 'departments',
	  checkFunction: main_core.Type.isArray,
	  formatFunction: prepareDepartments
	}, {
	  fieldName: 'phones',
	  targetFieldName: 'phones',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: preparePhones
	}];

	const UserPositionByType = {
	  [im_v2_const.UserType.bot]: main_core.Loc.getMessage('IM_MODEL_USERS_CHAT_BOT'),
	  [im_v2_const.UserType.collaber]: main_core.Loc.getMessage('IM_MODEL_USERS_COLLABER'),
	  default: main_core.Loc.getMessage('IM_MODEL_USERS_DEFAULT_NAME')
	};
	class UsersModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'users';
	  }
	  getNestedModules() {
	    return {
	      bots: BotsModel
	    };
	  }
	  getState() {
	    return {
	      collection: {},
	      absentList: [],
	      absentCheckInterval: null
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
	      isAdmin: false,
	      type: im_v2_const.UserType.user,
	      network: false,
	      connector: false,
	      externalAuthId: 'default',
	      status: '',
	      idle: false,
	      lastActivityDate: false,
	      mobileLastDate: false,
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

	  // eslint-disable-next-line max-lines-per-function
	  getGetters() {
	    return {
	      /** @function users/get */
	      get: state => (userId, getTemporary = false) => {
	        const user = state.collection[userId];
	        if (!getTemporary && !user) {
	          return null;
	        }
	        if (getTemporary && !user) {
	          return this.getElementState({
	            id: userId
	          });
	        }
	        return user;
	      },
	      /** @function users/getBlank */
	      getBlank: () => params => {
	        return this.getElementState(params);
	      },
	      /** @function users/getList */
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
	      /** @function users/hasBirthday */
	      hasBirthday: state => rawUserId => {
	        const userId = Number.parseInt(rawUserId, 10);
	        const user = state.collection[userId];
	        if (userId <= 0 || !user) {
	          return false;
	        }
	        return user.isBirthday;
	      },
	      /** @function users/hasVacation */
	      hasVacation: state => rawUserId => {
	        const userId = Number.parseInt(rawUserId, 10);
	        const user = state.collection[userId];
	        if (userId <= 0 || !user) {
	          return false;
	        }
	        return user.isAbsent;
	      },
	      /** @function users/getLastOnline */
	      getLastOnline: state => rawUserId => {
	        const userId = Number.parseInt(rawUserId, 10);
	        const user = state.collection[userId];
	        if (userId <= 0 || !user) {
	          return '';
	        }
	        return im_v2_lib_utils.Utils.user.getLastDateText(user);
	      },
	      /** @function users/getPosition */
	      getPosition: state => rawUserId => {
	        var _UserPositionByType$u;
	        const userId = Number.parseInt(rawUserId, 10);
	        const user = state.collection[userId];
	        const isSupportBot = im_v2_application_core.Core.getStore().getters['users/bots/isSupport'](userId);
	        if (userId <= 0 || !user || isSupportBot) {
	          return '';
	        }
	        if (user.workPosition) {
	          return user.workPosition;
	        }
	        return (_UserPositionByType$u = UserPositionByType[user.type]) != null ? _UserPositionByType$u : UserPositionByType.default;
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function users/set */
	      set: (store, rawPayload) => {
	        let payload = rawPayload;
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        payload.map(user => {
	          return this.formatFields(user);
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
	      /** @function users/add */
	      add: (store, rawPayload) => {
	        let payload = rawPayload;
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        payload.map(user => {
	          return this.formatFields(user);
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
	      /** @function users/update */
	      update: (store, rawPayload) => {
	        const payload = rawPayload;
	        payload.id = Number.parseInt(payload.id, 10);
	        const user = store.state.collection[payload.id];
	        if (!user) {
	          return;
	        }
	        const fields = {
	          ...payload.fields,
	          id: payload.id
	        };
	        store.commit('update', {
	          id: payload.id,
	          fields: this.formatFields(fields)
	        });
	      },
	      /** @function users/delete */
	      delete: (store, payload) => {
	        store.commit('delete', payload.id);
	      },
	      /** @function users/setStatus */
	      setStatus: (store, payload) => {
	        store.commit('update', {
	          id: im_v2_application_core.Core.getUserId(),
	          fields: this.formatFields(payload)
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      add: (state, payload) => {
	        // eslint-disable-next-line no-param-reassign
	        state.collection[payload.id] = payload.fields;
	        im_v2_lib_userStatus.UserStatusManager.getInstance().onUserUpdate(payload.fields);
	      },
	      update: (state, payload) => {
	        // eslint-disable-next-line no-param-reassign
	        state.collection[payload.id] = {
	          ...state.collection[payload.id],
	          ...payload.fields
	        };
	        im_v2_lib_userStatus.UserStatusManager.getInstance().onUserUpdate(payload.fields);
	      },
	      delete: (state, payload) => {
	        // eslint-disable-next-line no-param-reassign
	        delete state.collection[payload.id];
	      }
	    };
	  }
	  formatFields(fields) {
	    const preparedFields = im_v2_model.formatFieldsWithConfig(fields, userFieldsConfig);
	    const isBot = preparedFields.type === im_v2_const.UserType.bot;
	    if (isBot) {
	      im_v2_application_core.Core.getStore().dispatch('users/bots/set', {
	        userId: preparedFields.id,
	        botData: fields.botData || fields.bot_data
	      });
	    }
	    return preparedFields;
	  }
	  addToAbsentList(id) {
	    const state = this.store.state.users;
	    if (!state.absentList.includes(id)) {
	      state.absentList.push(id);
	    }
	  }
	  startAbsentCheckInterval() {
	    const state = this.store.state.users;
	    if (state.absentCheckInterval) {
	      return;
	    }
	    const TIME_TO_NEXT_DAY = 1000 * 60 * 60 * 24;
	    state.absentCheckInterval = setTimeout(() => {
	      setInterval(() => {
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
	}

	class FilesModel extends ui_vue3_vuex.BuilderModel {
	  getName() {
	    return 'files';
	  }
	  getState() {
	    return {
	      collection: {},
	      temporaryFilesMap: new Map()
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
	      /** @function files/get */
	      get: (state, getters) => (fileId, getTemporary = false) => {
	        if (!fileId) {
	          return null;
	        }
	        if (!getTemporary && !state.collection[fileId]) {
	          return null;
	        }
	        const file = state.collection[fileId];
	        const hasMappedTemporaryFile = getters.hasMappedTemporaryFile({
	          serverFileId: fileId
	        });
	        if (file && hasMappedTemporaryFile) {
	          const temporaryFile = getters.getMappedTemporaryFile({
	            serverFileId: fileId
	          });
	          return {
	            ...file,
	            urlPreview: temporaryFile.urlPreview,
	            urlShow: temporaryFile.urlShow
	          };
	        }
	        return file;
	      },
	      /** @function files/isInCollection */
	      isInCollection: state => payload => {
	        const {
	          fileId
	        } = payload;
	        return Boolean(state.collection[fileId]);
	      },
	      /** @function files/hasMappedTemporaryFile */
	      hasMappedTemporaryFile: state => payload => {
	        if (state.temporaryFilesMap.has(payload.serverFileId)) {
	          const temporaryFileId = state.temporaryFilesMap.get(payload.serverFileId);
	          return Object.hasOwn(state.collection, temporaryFileId);
	        }
	        return false;
	      },
	      /** @function files/getMappedTemporaryFile */
	      getMappedTemporaryFile: state => payload => {
	        const {
	          serverFileId
	        } = payload;
	        if (state.temporaryFilesMap.has(serverFileId)) {
	          const temporaryFileId = state.temporaryFilesMap.get(serverFileId);
	          return state.collection[temporaryFileId];
	        }
	        return null;
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function files/add */
	      add: (store, payload) => {
	        const preparedFile = {
	          ...this.getElementState(),
	          ...this.validate(payload)
	        };
	        store.commit('add', {
	          files: [preparedFile]
	        });
	      },
	      /** @function files/set */
	      set: (store, ...payload) => {
	        const files = payload.flat().map(file => {
	          return {
	            ...this.getElementState(),
	            ...this.validate(file)
	          };
	        });
	        store.commit('add', {
	          files
	        });
	      },
	      /** @function files/update */
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
	          id,
	          fields: this.validate(fields)
	        });
	        return true;
	      },
	      /** @function files/updateWithId */
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
	      /** @function files/delete */
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
	      /** @function files/setTemporaryFileMapping */
	      setTemporaryFileMapping: (store, payload) => {
	        store.commit('setTemporaryFileMapping', payload);
	      }
	    };
	  }

	  /* eslint-disable no-param-reassign */
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
	      },
	      delete: (state, payload) => {
	        im_v2_lib_logger.Logger.warn('Files model: delete mutation', payload);
	        const {
	          id
	        } = payload;
	        delete state.collection[id];
	      },
	      setTemporaryFileMapping: (state, payload) => {
	        state.temporaryFilesMap.set(payload.serverFileId, payload.temporaryFileId);
	      }
	    };
	  }

	  // eslint-disable-next-line max-lines-per-function,sonarjs/cognitive-complexity
	  validate(file, options = {}) {
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
	      if (!file.urlShow || file.urlShow.startsWith('http') || file.urlShow.startsWith('bx') || file.urlShow.startsWith('file') || file.urlShow.startsWith('blob')) {
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
	    if (main_core.Type.isString(viewerAttrs.viewerPreview)) {
	      result.viewerPreview = viewerAttrs.viewerPreview;
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

	const prepareDraft = draft => {
	  if (!draft.text || draft.text === '') {
	    return {
	      text: '',
	      date: null
	    };
	  }
	  return {
	    text: draft.text,
	    date: new Date()
	  };
	};
	const prepareInvitation = invited => {
	  if (main_core.Type.isPlainObject(invited)) {
	    return {
	      isActive: true,
	      originator: invited.originatorId,
	      canResend: invited.canResend
	    };
	  }
	  return {
	    isActive: false,
	    originator: 0,
	    canResend: false
	  };
	};

	const recentFieldsConfig = [{
	  fieldName: ['id', 'dialogId'],
	  targetFieldName: 'dialogId',
	  checkFunction: isNumberOrString,
	  formatFunction: convertToString
	}, {
	  fieldName: 'messageId',
	  targetFieldName: 'messageId',
	  checkFunction: isNumberOrString
	}, {
	  fieldName: 'draft',
	  targetFieldName: 'draft',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: prepareDraft
	}, {
	  fieldName: 'invited',
	  targetFieldName: 'invitation',
	  checkFunction: [main_core.Type.isPlainObject, main_core.Type.isBoolean],
	  formatFunction: prepareInvitation
	}, {
	  fieldName: 'unread',
	  targetFieldName: 'unread',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'pinned',
	  targetFieldName: 'pinned',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'liked',
	  targetFieldName: 'liked',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: ['defaultUserRecord', 'isFakeElement'],
	  targetFieldName: 'isFakeElement',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'isBirthdayPlaceholder',
	  targetFieldName: 'isBirthdayPlaceholder',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: ['dateLastActivity', 'lastActivityDate'],
	  targetFieldName: 'lastActivityDate',
	  checkFunction: [main_core.Type.isString, main_core.Type.isDate],
	  formatFunction: im_v2_lib_utils.Utils.date.cast
	}];

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
	      },
	      getCallByDialog: state => dialogId => {
	        return state.collection[dialogId];
	      },
	      hasActiveCall: state => dialogId => {
	        if (main_core.Type.isUndefined(dialogId)) {
	          const activeCall = Object.values(state.collection).find(item => {
	            return item.state === im_v2_const.RecentCallStatus.joined;
	          });
	          return Boolean(activeCall);
	        }
	        const existingCall = Object.values(state.collection).find(item => {
	          return item.dialogId === dialogId;
	        });
	        if (!existingCall) {
	          return false;
	        }
	        return existingCall.state === im_v2_const.RecentCallStatus.joined;
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

	var _formatFields$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("formatFields");
	var _updateUnloadedRecentCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateUnloadedRecentCounters");
	var _updateUnloadedCopilotCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateUnloadedCopilotCounters");
	var _updateUnloadedCollabCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateUnloadedCollabCounters");
	var _updateUnloadedCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateUnloadedCounters");
	var _getMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMessage");
	var _getDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	var _hasTodayMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasTodayMessage");
	var _canDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canDelete");
	var _prepareFakeItemWithDraft = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareFakeItemWithDraft");
	var _createFakeMessageForDraft = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createFakeMessageForDraft");
	var _shouldDeleteItemWithDraft = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shouldDeleteItemWithDraft");
	class RecentModel extends ui_vue3_vuex.BuilderModel {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _shouldDeleteItemWithDraft, {
	      value: _shouldDeleteItemWithDraft2
	    });
	    Object.defineProperty(this, _createFakeMessageForDraft, {
	      value: _createFakeMessageForDraft2
	    });
	    Object.defineProperty(this, _prepareFakeItemWithDraft, {
	      value: _prepareFakeItemWithDraft2
	    });
	    Object.defineProperty(this, _canDelete, {
	      value: _canDelete2
	    });
	    Object.defineProperty(this, _hasTodayMessage, {
	      value: _hasTodayMessage2
	    });
	    Object.defineProperty(this, _getDialog, {
	      value: _getDialog2
	    });
	    Object.defineProperty(this, _getMessage, {
	      value: _getMessage2
	    });
	    Object.defineProperty(this, _updateUnloadedCounters, {
	      value: _updateUnloadedCounters2
	    });
	    Object.defineProperty(this, _updateUnloadedCollabCounters, {
	      value: _updateUnloadedCollabCounters2
	    });
	    Object.defineProperty(this, _updateUnloadedCopilotCounters, {
	      value: _updateUnloadedCopilotCounters2
	    });
	    Object.defineProperty(this, _updateUnloadedRecentCounters, {
	      value: _updateUnloadedRecentCounters2
	    });
	    Object.defineProperty(this, _formatFields$3, {
	      value: _formatFields2$3
	    });
	  }
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
	      copilotCollection: new Set(),
	      channelCollection: new Set(),
	      collabCollection: new Set()
	    };
	  }
	  getElementState() {
	    return {
	      dialogId: '0',
	      messageId: 0,
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
	      isFakeElement: false,
	      isBirthdayPlaceholder: false,
	      lastActivityDate: null
	    };
	  }

	  // eslint-disable-next-line max-lines-per-function
	  getGetters() {
	    return {
	      /** @function recent/getRecentCollection */
	      getRecentCollection: state => {
	        return [...state.recentCollection].filter(dialogId => {
	          const dialog = this.store.getters['chats/get'](dialogId);
	          return Boolean(dialog);
	        }).map(id => {
	          return state.collection[id];
	        });
	      },
	      /** @function recent/getUnreadCollection */
	      getUnreadCollection: state => {
	        return [...state.unreadCollection].map(id => {
	          return state.collection[id];
	        });
	      },
	      /** @function recent/getCopilotCollection */
	      getCopilotCollection: state => {
	        return [...state.copilotCollection].filter(dialogId => {
	          const dialog = this.store.getters['chats/get'](dialogId);
	          return Boolean(dialog);
	        }).map(id => {
	          return state.collection[id];
	        });
	      },
	      /** @function recent/getChannelCollection */
	      getChannelCollection: state => {
	        return [...state.channelCollection].filter(dialogId => {
	          const dialog = this.store.getters['chats/get'](dialogId);
	          return Boolean(dialog);
	        }).map(id => {
	          return state.collection[id];
	        });
	      },
	      /** @function recent/getCollabCollection */
	      getCollabCollection: state => {
	        return [...state.collabCollection].filter(dialogId => {
	          const dialog = this.store.getters['chats/get'](dialogId);
	          return Boolean(dialog);
	        }).map(id => {
	          return state.collection[id];
	        });
	      },
	      /** @function recent/getSortedCollection */
	      getSortedCollection: state => {
	        const recentCollectionAsArray = [...state.recentCollection].map(dialogId => {
	          return state.collection[dialogId];
	        });
	        const filteredCollection = recentCollectionAsArray.filter(item => {
	          return !item.isBirthdayPlaceholder && !item.isFakeElement && item.messageId;
	        });
	        return [...filteredCollection].sort((a, b) => {
	          const messageA = babelHelpers.classPrivateFieldLooseBase(this, _getMessage)[_getMessage](a.messageId);
	          const messageB = babelHelpers.classPrivateFieldLooseBase(this, _getMessage)[_getMessage](b.messageId);
	          return messageB.date - messageA.date;
	        });
	      },
	      /** @function recent/get */
	      get: state => dialogId => {
	        if (!state.collection[dialogId]) {
	          return null;
	        }
	        return state.collection[dialogId];
	      },
	      /** @function recent/getMessage */
	      getMessage: state => dialogId => {
	        const element = state.collection[dialogId];
	        if (!element) {
	          return null;
	        }
	        return babelHelpers.classPrivateFieldLooseBase(this, _getMessage)[_getMessage](element.messageId);
	      },
	      /** @function recent/needsBirthdayPlaceholder */
	      needsBirthdayPlaceholder: state => dialogId => {
	        const currentItem = state.collection[dialogId];
	        if (!currentItem) {
	          return false;
	        }
	        const dialog = this.store.getters['chats/get'](dialogId);
	        if (!dialog || dialog.type !== im_v2_const.ChatType.user) {
	          return false;
	        }
	        const hasBirthday = this.store.getters['users/hasBirthday'](dialogId);
	        if (!hasBirthday) {
	          return false;
	        }
	        const showBirthday = this.store.getters['application/settings/get'](im_v2_const.Settings.recent.showBirthday);
	        const hasTodayMessage = babelHelpers.classPrivateFieldLooseBase(this, _hasTodayMessage)[_hasTodayMessage](currentItem.messageId);
	        return showBirthday && !hasTodayMessage && dialog.counter === 0;
	      },
	      /** @function recent/needsVacationPlaceholder */
	      needsVacationPlaceholder: state => dialogId => {
	        const currentItem = state.collection[dialogId];
	        if (!currentItem) {
	          return false;
	        }
	        const dialog = this.store.getters['chats/get'](dialogId);
	        if (!dialog || dialog.type !== im_v2_const.ChatType.user) {
	          return false;
	        }
	        const hasVacation = this.store.getters['users/hasVacation'](dialogId);
	        if (!hasVacation) {
	          return false;
	        }
	        const hasTodayMessage = babelHelpers.classPrivateFieldLooseBase(this, _hasTodayMessage)[_hasTodayMessage](currentItem.messageId);
	        return !hasTodayMessage && dialog.counter === 0;
	      },
	      /** @function recent/getSortDate */
	      getSortDate: state => dialogId => {
	        const currentItem = state.collection[dialogId];
	        if (!currentItem) {
	          return null;
	        }
	        const message = babelHelpers.classPrivateFieldLooseBase(this, _getMessage)[_getMessage](currentItem.messageId);
	        if (main_core.Type.isDate(currentItem.draft.date) && currentItem.draft.date > message.date) {
	          return currentItem.draft.date;
	        }
	        const needsBirthdayPlaceholder = this.store.getters['recent/needsBirthdayPlaceholder'](currentItem.dialogId);
	        if (needsBirthdayPlaceholder) {
	          return im_v2_lib_utils.Utils.date.getStartOfTheDay();
	        }
	        const lastActivity = currentItem.lastActivityDate;
	        const needToUseActivityDate = main_core.Type.isDate(lastActivity) && lastActivity > message.date;
	        if (im_v2_lib_channel.ChannelManager.isChannel(currentItem.dialogId) && needToUseActivityDate) {
	          return lastActivity;
	        }
	        return message.date;
	      }
	    };
	  }

	  /* eslint-disable no-param-reassign */
	  /* eslint-disable-next-line max-lines-per-function */
	  getActions() {
	    return {
	      /** @function recent/setRecent */
	      setRecent: async (store, payload) => {
	        const itemIds = await im_v2_application_core.Core.getStore().dispatch('recent/store', payload);
	        store.commit('setRecentCollection', itemIds);
	        babelHelpers.classPrivateFieldLooseBase(this, _updateUnloadedRecentCounters)[_updateUnloadedRecentCounters](payload);
	      },
	      /** @function recent/setUnread */
	      setUnread: async (store, payload) => {
	        const itemIds = await this.store.dispatch('recent/store', payload);
	        store.commit('setUnreadCollection', itemIds);
	      },
	      /** @function recent/setCopilot */
	      setCopilot: async (store, payload) => {
	        const itemIds = await this.store.dispatch('recent/store', payload);
	        store.commit('setCopilotCollection', itemIds);
	        babelHelpers.classPrivateFieldLooseBase(this, _updateUnloadedCopilotCounters)[_updateUnloadedCopilotCounters](payload);
	      },
	      /** @function recent/setChannel */
	      setChannel: async (store, payload) => {
	        const itemIds = await this.store.dispatch('recent/store', payload);
	        store.commit('setChannelCollection', itemIds);
	      },
	      /** @function recent/setCollab */
	      setCollab: async (store, payload) => {
	        const itemIds = await this.store.dispatch('recent/store', payload);
	        store.commit('setCollabCollection', itemIds);
	        babelHelpers.classPrivateFieldLooseBase(this, _updateUnloadedCollabCounters)[_updateUnloadedCollabCounters](payload);
	      },
	      /** @function recent/clearChannelCollection */
	      clearChannelCollection: store => {
	        store.commit('clearChannelCollection');
	      },
	      /** @function recent/store */
	      store: (store, payload) => {
	        if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	          payload = [payload];
	        }
	        const itemsToUpdate = [];
	        const itemsToAdd = [];
	        payload.map(element => {
	          return babelHelpers.classPrivateFieldLooseBase(this, _formatFields$3)[_formatFields$3](element);
	        }).forEach(element => {
	          const preparedElement = {
	            ...element
	          };
	          const existingItem = store.state.collection[element.dialogId];
	          if (existingItem) {
	            itemsToUpdate.push({
	              dialogId: existingItem.dialogId,
	              fields: preparedElement
	            });
	          } else {
	            itemsToAdd.push({
	              ...this.getElementState(),
	              ...preparedElement
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
	      /** @function recent/update */
	      update: (store, payload) => {
	        const {
	          id,
	          fields
	        } = payload;
	        const existingItem = store.state.collection[id];
	        if (!existingItem) {
	          return;
	        }
	        store.commit('update', {
	          dialogId: existingItem.dialogId,
	          fields: babelHelpers.classPrivateFieldLooseBase(this, _formatFields$3)[_formatFields$3](fields)
	        });
	      },
	      /** @function recent/unread */
	      unread: (store, payload) => {
	        const existingItem = store.state.collection[payload.id];
	        if (!existingItem) {
	          return;
	        }
	        store.commit('update', {
	          dialogId: existingItem.dialogId,
	          fields: {
	            unread: payload.action
	          }
	        });
	      },
	      /** @function recent/pin */
	      pin: (store, payload) => {
	        const existingItem = store.state.collection[payload.id];
	        if (!existingItem) {
	          return;
	        }
	        store.commit('update', {
	          dialogId: existingItem.dialogId,
	          fields: {
	            pinned: payload.action
	          }
	        });
	      },
	      /** @function recent/like */
	      like: (store, payload) => {
	        const existingItem = store.state.collection[payload.id];
	        if (!existingItem) {
	          return;
	        }
	        const isLastMessage = existingItem.messageId === Number.parseInt(payload.messageId, 10);
	        const isExactMessageLiked = !main_core.Type.isUndefined(payload.messageId) && payload.liked === true;
	        if (isExactMessageLiked && !isLastMessage) {
	          return;
	        }
	        store.commit('update', {
	          dialogId: existingItem.dialogId,
	          fields: {
	            liked: payload.liked === true
	          }
	        });
	      },
	      /** @function recent/setRecentDraft */
	      setRecentDraft: (store, payload) => {
	        im_v2_application_core.Core.getStore().dispatch('recent/setDraft', {
	          id: payload.id,
	          text: payload.text,
	          collectionName: 'recentCollection',
	          addMethodName: 'setRecentCollection'
	        });
	      },
	      /** @function recent/setCopilotDraft */
	      setCopilotDraft: (store, payload) => {
	        im_v2_application_core.Core.getStore().dispatch('recent/setDraft', {
	          id: payload.id,
	          text: payload.text,
	          collectionName: 'copilotCollection',
	          addMethodName: 'setCopilotCollection'
	        });
	      },
	      /** @function recent/setDraft */
	      setDraft: (store, payload) => {
	        const isRemovingDraft = !main_core.Type.isStringFilled(payload.text);
	        if (isRemovingDraft && babelHelpers.classPrivateFieldLooseBase(this, _shouldDeleteItemWithDraft)[_shouldDeleteItemWithDraft](payload)) {
	          void im_v2_application_core.Core.getStore().dispatch('recent/delete', {
	            id: payload.id
	          });
	          return;
	        }
	        let existingItem = store.state.collection[payload.id];
	        if (!existingItem && !isRemovingDraft) {
	          store.commit('add', {
	            ...this.getElementState(),
	            ...babelHelpers.classPrivateFieldLooseBase(this, _prepareFakeItemWithDraft)[_prepareFakeItemWithDraft](payload)
	          });
	          existingItem = store.state.collection[payload.id];
	        }
	        const existingCollectionItem = store.state[payload.collectionName].has(payload.id);
	        if (!existingCollectionItem) {
	          if (isRemovingDraft) {
	            return;
	          }
	          store.commit(payload.addMethodName, [payload.id.toString()]);
	        }
	        const fields = babelHelpers.classPrivateFieldLooseBase(this, _formatFields$3)[_formatFields$3]({
	          draft: {
	            text: payload.text.toString()
	          }
	        });
	        if (fields.draft.text === existingItem.draft.text) {
	          return;
	        }
	        store.commit('update', {
	          dialogId: existingItem.dialogId,
	          fields
	        });
	      },
	      /** @function recent/delete */
	      delete: (store, payload) => {
	        const existingItem = store.state.collection[payload.id];
	        if (!existingItem) {
	          return;
	        }
	        store.commit('deleteFromRecentCollection', existingItem.dialogId);
	        store.commit('deleteFromCopilotCollection', existingItem.dialogId);
	        store.commit('deleteFromChannelCollection', existingItem.dialogId);
	        store.commit('deleteFromCollabCollection', existingItem.dialogId);
	        const canDelete = babelHelpers.classPrivateFieldLooseBase(this, _canDelete)[_canDelete](existingItem.dialogId);
	        if (!canDelete) {
	          return;
	        }
	        store.commit('delete', {
	          id: existingItem.dialogId
	        });
	      },
	      /** @function recent/clearUnread */
	      clearUnread: store => {
	        store.commit('clearUnread');
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
	      setCopilotCollection: (state, payload) => {
	        payload.forEach(dialogId => {
	          state.copilotCollection.add(dialogId);
	        });
	      },
	      deleteFromCopilotCollection: (state, payload) => {
	        state.copilotCollection.delete(payload);
	      },
	      deleteFromChannelCollection: (state, payload) => {
	        state.channelCollection.delete(payload);
	      },
	      setChannelCollection: (state, payload) => {
	        payload.forEach(dialogId => {
	          state.channelCollection.add(dialogId);
	        });
	      },
	      clearChannelCollection: state => {
	        state.channelCollection = new Set();
	      },
	      setCollabCollection: (state, payload) => {
	        payload.forEach(dialogId => {
	          state.collabCollection.add(dialogId);
	        });
	      },
	      deleteFromCollabCollection: (state, payload) => {
	        state.collabCollection.delete(payload);
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
	          // if we already got chat - we should not update it with fake user chat
	          // (unless it's an accepted invitation or fake user with real message)
	          const elementIsInRecent = state.recentCollection.has(dialogId);
	          const isFakeElement = fields.isFakeElement && im_v2_lib_utils.Utils.text.isTempMessage(fields.messageId);
	          if (elementIsInRecent && isFakeElement && !fields.invitation) {
	            return;
	          }
	          const currentElement = state.collection[dialogId];
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
	      }
	    };
	  }
	}
	function _formatFields2$3(rawFields) {
	  const options = main_core.Type.isPlainObject(rawFields.options) ? rawFields.options : {};
	  const fields = {
	    ...rawFields,
	    ...options
	  };
	  return im_v2_model.formatFieldsWithConfig(fields, recentFieldsConfig);
	}
	function _updateUnloadedRecentCounters2(payload) {
	  babelHelpers.classPrivateFieldLooseBase(this, _updateUnloadedCounters)[_updateUnloadedCounters](payload, 'counters/setUnloadedChatCounters');
	}
	function _updateUnloadedCopilotCounters2(payload) {
	  babelHelpers.classPrivateFieldLooseBase(this, _updateUnloadedCounters)[_updateUnloadedCounters](payload, 'counters/setUnloadedCopilotCounters');
	}
	function _updateUnloadedCollabCounters2(payload) {
	  babelHelpers.classPrivateFieldLooseBase(this, _updateUnloadedCounters)[_updateUnloadedCounters](payload, 'counters/setUnloadedCollabCounters');
	}
	function _updateUnloadedCounters2(payload, updateMethod) {
	  if (!Array.isArray(payload) && main_core.Type.isPlainObject(payload)) {
	    payload = [payload];
	  }
	  const zeroedCountersForNewItems = {};
	  const preparedItems = payload.map(item => im_v2_model.convertObjectKeysToCamelCase(item));
	  preparedItems.forEach(item => {
	    zeroedCountersForNewItems[item.chatId] = 0;
	  });
	  void im_v2_application_core.Core.getStore().dispatch(updateMethod, zeroedCountersForNewItems);
	}
	function _getMessage2(messageId) {
	  return im_v2_application_core.Core.getStore().getters['messages/getById'](messageId);
	}
	function _getDialog2(dialogId) {
	  return im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	}
	function _hasTodayMessage2(messageId) {
	  const message = babelHelpers.classPrivateFieldLooseBase(this, _getMessage)[_getMessage](messageId);
	  const hasMessage = im_v2_lib_utils.Utils.text.isUuidV4(message.id) || message.id > 0;
	  return hasMessage && im_v2_lib_utils.Utils.date.isToday(message.date);
	}
	function _canDelete2(dialogId) {
	  const NOT_DELETABLE_TYPES = [im_v2_const.ChatType.openChannel];
	  const {
	    type
	  } = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](dialogId);
	  return !NOT_DELETABLE_TYPES.includes(type);
	}
	function _prepareFakeItemWithDraft2(payload) {
	  const messageId = babelHelpers.classPrivateFieldLooseBase(this, _createFakeMessageForDraft)[_createFakeMessageForDraft](payload.id);
	  return babelHelpers.classPrivateFieldLooseBase(this, _formatFields$3)[_formatFields$3]({
	    dialogId: payload.id.toString(),
	    draft: {
	      text: payload.text.toString()
	    },
	    messageId
	  });
	}
	function _createFakeMessageForDraft2(dialogId) {
	  const messageId = `${im_v2_const.FakeDraftMessagePrefix}-${dialogId}`;
	  void im_v2_application_core.Core.getStore().dispatch('messages/store', {
	    id: messageId,
	    date: new Date()
	  });
	  return messageId;
	}
	function _shouldDeleteItemWithDraft2(payload) {
	  const existingItem = im_v2_application_core.Core.getStore().state.recent.collection[payload.id];
	  return existingItem && !main_core.Type.isStringFilled(payload.text) && existingItem.messageId.toString().startsWith(im_v2_const.FakeDraftMessagePrefix);
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
	      deleteFromSearch: (store, payload) => {
	        const existingItem = store.state.searchCollection.get(payload.id);
	        if (!existingItem) {
	          return;
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
	        state.searchCollection.delete(payload.id);
	      },
	      read: (state, payload) => {
	        state.collection.set(payload.id, {
	          ...state.collection.get(payload.id),
	          read: payload.read
	        });
	        state.searchCollection.set(payload.id, {
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
	      result.params = convertObjectKeysToCamelCase(fields.params);
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

	const sidebarLinksFieldsConfig = [{
	  fieldName: 'id',
	  targetFieldName: 'id',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'messageId',
	  targetFieldName: 'messageId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'chatId',
	  targetFieldName: 'chatId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'authorId',
	  targetFieldName: 'authorId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'url',
	  targetFieldName: 'source',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: target => {
	    var _target$source;
	    return (_target$source = target.source) != null ? _target$source : '';
	  }
	}, {
	  fieldName: 'dateCreate',
	  targetFieldName: 'date',
	  checkFunction: main_core.Type.isString,
	  formatFunction: im_v2_lib_utils.Utils.date.cast
	}, {
	  fieldName: 'url',
	  targetFieldName: 'richData',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: target => {
	    return formatFieldsWithConfig(target.richData, richDataFieldsConfig);
	  }
	}];
	const richDataFieldsConfig = [{
	  fieldName: 'id',
	  targetFieldName: 'id',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'description',
	  targetFieldName: 'description',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'link',
	  targetFieldName: 'link',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'name',
	  targetFieldName: 'name',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'previewUrl',
	  targetFieldName: 'previewUrl',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'type',
	  targetFieldName: 'type',
	  checkFunction: main_core.Type.isString
	}];

	/* eslint-disable no-param-reassign */
	class LinksModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {},
	      collectionSearch: {},
	      counters: {},
	      historyLimitExceededCollection: {}
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
	  getChatState() {
	    return {
	      items: new Map(),
	      hasNextPage: true
	    };
	  }
	  getGetters() {
	    return {
	      /** @function sidebar/links/get */
	      get: state => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        return [...state.collection[chatId].items.values()].sort((a, b) => b.id - a.id);
	      },
	      /** @function sidebar/links/getSize */
	      getSize: state => chatId => {
	        if (!state.collection[chatId]) {
	          return 0;
	        }
	        return state.collection[chatId].items.size;
	      },
	      /** @function sidebar/links/getCounter */
	      getCounter: state => chatId => {
	        if (!state.counters[chatId]) {
	          return 0;
	        }
	        return state.counters[chatId];
	      },
	      /** @function sidebar/links/hasNextPage */
	      hasNextPage: state => chatId => {
	        if (!state.collection[chatId]) {
	          return false;
	        }
	        return state.collection[chatId].hasNextPage;
	      },
	      /** @function sidebar/links/hasNextPageSearch */
	      hasNextPageSearch: state => chatId => {
	        if (!state.collectionSearch[chatId]) {
	          return false;
	        }
	        return state.collectionSearch[chatId].hasNextPage;
	      },
	      /** @function sidebar/links/getSearchResultCollectionSize */
	      getSearchResultCollectionSize: state => chatId => {
	        if (!state.collectionSearch[chatId]) {
	          return 0;
	        }
	        return state.collectionSearch[chatId].items.size;
	      },
	      /** @function sidebar/links/getSearchResultCollection */
	      getSearchResultCollection: state => chatId => {
	        if (!state.collectionSearch[chatId]) {
	          return [];
	        }
	        return [...state.collectionSearch[chatId].items.values()].sort((a, b) => b.id - a.id);
	      },
	      /** @function sidebar/links/isHistoryLimitExceeded */
	      isHistoryLimitExceeded: state => chatId => {
	        var _state$historyLimitEx;
	        const isAvailable = im_v2_application_core.Core.getStore().getters['application/tariffRestrictions/isHistoryAvailable'];
	        if (isAvailable) {
	          return false;
	        }
	        return (_state$historyLimitEx = state.historyLimitExceededCollection[chatId]) != null ? _state$historyLimitEx : false;
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function sidebar/links/setCounter */
	      setCounter: (store, payload) => {
	        if (!main_core.Type.isNumber(payload.counter) || !main_core.Type.isNumber(payload.chatId)) {
	          return;
	        }
	        store.commit('setCounter', payload);
	      },
	      /** @function sidebar/links/set */
	      set: (store, payload) => {
	        const {
	          chatId,
	          links,
	          hasNextPage,
	          isHistoryLimitExceeded = false
	        } = payload;
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        store.commit('setHasNextPage', {
	          chatId,
	          hasNextPage
	        });
	        store.commit('setHistoryLimitExceeded', {
	          chatId,
	          isHistoryLimitExceeded
	        });
	        links.forEach(link => {
	          const preparedLink = {
	            ...this.getElementState(),
	            ...this.formatFields(link)
	          };
	          store.commit('add', {
	            chatId,
	            link: preparedLink
	          });
	        });
	      },
	      /** @function sidebar/links/setSearch */
	      setSearch: (store, payload) => {
	        const {
	          chatId,
	          links,
	          hasNextPage,
	          isHistoryLimitExceeded = false
	        } = payload;
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        store.commit('setHasNextPageSearch', {
	          chatId,
	          hasNextPage
	        });
	        store.commit('setHistoryLimitExceeded', {
	          chatId,
	          isHistoryLimitExceeded
	        });
	        links.forEach(link => {
	          const preparedLink = {
	            ...this.getElementState(),
	            ...this.formatFields(link)
	          };
	          store.commit('addSearch', {
	            chatId,
	            link: preparedLink
	          });
	        });
	      },
	      /** @function sidebar/links/clearSearch */
	      clearSearch: store => {
	        store.commit('clearSearch', {});
	      },
	      /** @function sidebar/links/delete */
	      delete: (store, payload) => {
	        const {
	          chatId,
	          id
	        } = payload;
	        if (!main_core.Type.isNumber(id) || !main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collection[chatId] || !store.state.collection[chatId].items.has(id)) {
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
	      setHasNextPage: (state, payload) => {
	        const {
	          chatId,
	          hasNextPage
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].hasNextPage = hasNextPage;
	      },
	      setHasNextPageSearch: (state, payload) => {
	        const {
	          chatId,
	          hasNextPage
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (!hasCollection) {
	          state.collectionSearch[chatId] = this.getChatState();
	        }
	        state.collectionSearch[chatId].hasNextPage = hasNextPage;
	      },
	      setHistoryLimitExceeded: (state, payload) => {
	        const {
	          chatId,
	          isHistoryLimitExceeded
	        } = payload;
	        if (state.historyLimitExceededCollection[chatId] && !isHistoryLimitExceeded) {
	          return;
	        }
	        state.historyLimitExceededCollection[chatId] = isHistoryLimitExceeded;
	      },
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
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].items.set(link.id, link);
	      },
	      addSearch: (state, payload) => {
	        const {
	          chatId,
	          link
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (!hasCollection) {
	          state.collectionSearch[chatId] = this.getChatState();
	        }
	        state.collectionSearch[chatId].items.set(link.id, link);
	      },
	      clearSearch: state => {
	        state.collectionSearch = {};
	      },
	      delete: (state, payload) => {
	        const {
	          chatId,
	          id
	        } = payload;
	        const hasCollectionSearch = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (hasCollectionSearch) {
	          state.collectionSearch[chatId].items.delete(id);
	        }
	        state.collection[chatId].items.delete(id);
	        state.counters[chatId]--;
	      }
	    };
	  }
	  formatFields(fields) {
	    return formatFieldsWithConfig(fields, sidebarLinksFieldsConfig);
	  }
	}

	const sidebarFavoritesFieldsConfig = [{
	  fieldName: 'id',
	  targetFieldName: 'id',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'messageId',
	  targetFieldName: 'messageId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'chatId',
	  targetFieldName: 'chatId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'authorId',
	  targetFieldName: 'authorId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'dateCreate',
	  targetFieldName: 'date',
	  checkFunction: main_core.Type.isString,
	  formatFunction: im_v2_lib_utils.Utils.date.cast
	}];

	/* eslint-disable no-param-reassign */
	class FavoritesModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {},
	      counters: {},
	      collectionSearch: {},
	      historyLimitExceededCollection: {}
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
	  getChatState() {
	    return {
	      items: new Map(),
	      hasNextPage: true,
	      lastId: 0
	    };
	  }
	  getGetters() {
	    return {
	      /** @function sidebar/favorites/get */
	      get: state => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        return [...state.collection[chatId].items.values()].sort((a, b) => b.id - a.id);
	      },
	      /** @function sidebar/favorites/getSize */
	      getSize: state => chatId => {
	        if (!state.collection[chatId]) {
	          return 0;
	        }
	        return state.collection[chatId].items.size;
	      },
	      /** @function sidebar/favorites/getCounter */
	      getCounter: state => chatId => {
	        if (state.counters[chatId]) {
	          return state.counters[chatId];
	        }
	        return 0;
	      },
	      /** @function sidebar/favorites/isFavoriteMessage */
	      isFavoriteMessage: state => (chatId, messageId) => {
	        if (!state.collection[chatId]) {
	          return false;
	        }
	        const chatFavorites = Object.fromEntries(state.collection[chatId].items);
	        const targetMessage = Object.values(chatFavorites).find(element => element.messageId === messageId);
	        return Boolean(targetMessage);
	      },
	      /** @function sidebar/favorites/hasNextPage */
	      hasNextPage: state => chatId => {
	        if (!state.collection[chatId]) {
	          return false;
	        }
	        return state.collection[chatId].hasNextPage;
	      },
	      /** @function sidebar/favorites/getLastId */
	      getLastId: state => chatId => {
	        if (!state.collection[chatId]) {
	          return false;
	        }
	        return state.collection[chatId].lastId;
	      },
	      /** @function sidebar/favorites/getSearchResultCollectionLastId */
	      getSearchResultCollectionLastId: state => chatId => {
	        if (!state.collectionSearch[chatId]) {
	          return 0;
	        }
	        return state.collectionSearch[chatId].lastId;
	      },
	      /** @function sidebar/favorites/hasNextPageSearch */
	      hasNextPageSearch: state => chatId => {
	        if (!state.collectionSearch[chatId]) {
	          return false;
	        }
	        return state.collectionSearch[chatId].hasNextPage;
	      },
	      /** @function sidebar/favorites/getSearchResultCollection */
	      getSearchResultCollection: state => chatId => {
	        if (!state.collectionSearch[chatId]) {
	          return [];
	        }
	        return [...state.collectionSearch[chatId].items.values()].sort((a, b) => b.id - a.id);
	      },
	      /** @function sidebar/favorites/isHistoryLimitExceeded */
	      isHistoryLimitExceeded: state => chatId => {
	        var _state$historyLimitEx;
	        const isAvailable = im_v2_application_core.Core.getStore().getters['application/tariffRestrictions/isHistoryAvailable'];
	        if (isAvailable) {
	          return false;
	        }
	        return (_state$historyLimitEx = state.historyLimitExceededCollection[chatId]) != null ? _state$historyLimitEx : false;
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function sidebar/favorites/setCounter */
	      setCounter: (store, payload) => {
	        if (!main_core.Type.isNumber(payload.counter) || !main_core.Type.isNumber(payload.chatId)) {
	          return;
	        }
	        store.commit('setCounter', payload);
	      },
	      /** @function sidebar/favorites/set */
	      set: (store, payload) => {
	        if (main_core.Type.isNumber(payload.favorites)) {
	          payload.favorites = [payload.favorites];
	        }
	        const {
	          chatId,
	          favorites,
	          hasNextPage,
	          lastId,
	          isHistoryLimitExceeded = false
	        } = payload;
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        store.commit('setHistoryLimitExceeded', {
	          chatId,
	          isHistoryLimitExceeded
	        });
	        store.commit('setHasNextPage', {
	          chatId,
	          hasNextPage
	        });
	        store.commit('setLastId', {
	          chatId,
	          lastId
	        });
	        favorites.forEach(favorite => {
	          const preparedFavoriteMessage = {
	            ...this.getElementState(),
	            ...this.formatFields(favorite)
	          };
	          store.commit('add', {
	            chatId,
	            favorite: preparedFavoriteMessage
	          });
	        });
	      },
	      /** @function sidebar/favorites/delete */
	      delete: (store, payload) => {
	        const {
	          chatId,
	          id
	        } = payload;
	        if (!main_core.Type.isNumber(id) || !main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collection[chatId] || !store.state.collection[chatId].items.has(id)) {
	          return;
	        }
	        store.commit('delete', {
	          chatId,
	          id
	        });
	      },
	      /** @function sidebar/favorites/deleteByMessageId */
	      deleteByMessageId: (store, payload) => {
	        const {
	          chatId,
	          messageId
	        } = payload;
	        if (!store.state.collection[chatId]) {
	          return;
	        }
	        const chatCollection = store.state.collection[chatId].items;
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
	      },
	      /** @function sidebar/favorites/setSearch */
	      setSearch: (store, payload) => {
	        const {
	          chatId,
	          favorites,
	          hasNextPage,
	          lastId,
	          isHistoryLimitExceeded = false
	        } = payload;
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        store.commit('setHistoryLimitExceeded', {
	          chatId,
	          isHistoryLimitExceeded
	        });
	        store.commit('setHasNextPageSearch', {
	          chatId,
	          hasNextPage
	        });
	        store.commit('setLastIdSearch', {
	          chatId,
	          lastId
	        });
	        favorites.forEach(favorite => {
	          const preparedFavoriteMessage = {
	            ...this.getElementState(),
	            ...this.formatFields(favorite)
	          };
	          store.commit('addSearch', {
	            chatId,
	            favorite: preparedFavoriteMessage
	          });
	        });
	      },
	      /** @function sidebar/favorites/clearSearch */
	      clearSearch: store => {
	        store.commit('clearSearch', {});
	      }
	    };
	  }
	  getMutations() {
	    return {
	      setHasNextPage: (state, payload) => {
	        const {
	          chatId,
	          hasNextPage
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].hasNextPage = hasNextPage;
	      },
	      setHasNextPageSearch: (state, payload) => {
	        const {
	          chatId,
	          hasNextPage
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (!hasCollection) {
	          state.collectionSearch[chatId] = this.getChatState();
	        }
	        state.collectionSearch[chatId].hasNextPage = hasNextPage;
	      },
	      setCounter: (state, payload) => {
	        const {
	          chatId,
	          counter
	        } = payload;
	        state.counters[chatId] = counter;
	      },
	      setLastId: (state, payload) => {
	        const {
	          chatId,
	          lastId
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].lastId = lastId;
	      },
	      add: (state, payload) => {
	        const {
	          chatId,
	          favorite
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].items.set(favorite.id, favorite);
	      },
	      addSearch: (state, payload) => {
	        const {
	          chatId,
	          favorite
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (!hasCollection) {
	          state.collectionSearch[chatId] = this.getChatState();
	        }
	        state.collectionSearch[chatId].items.set(favorite.id, favorite);
	      },
	      clearSearch: state => {
	        state.collectionSearch = {};
	      },
	      setLastIdSearch: (state, payload) => {
	        const {
	          chatId,
	          lastId
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (!hasCollection) {
	          state.collectionSearch[chatId] = this.getChatState();
	        }
	        state.collectionSearch[chatId].lastId = lastId;
	      },
	      delete: (state, payload) => {
	        const {
	          chatId,
	          id
	        } = payload;
	        const hasCollectionSearch = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (hasCollectionSearch) {
	          state.collectionSearch[chatId].items.delete(id);
	        }
	        state.collection[chatId].items.delete(id);
	        state.counters[chatId]--;
	      },
	      setHistoryLimitExceeded: (state, payload) => {
	        const {
	          chatId,
	          isHistoryLimitExceeded
	        } = payload;
	        if (state.historyLimitExceededCollection[chatId] && !isHistoryLimitExceeded) {
	          return;
	        }
	        state.historyLimitExceededCollection[chatId] = isHistoryLimitExceeded;
	      }
	    };
	  }
	  formatFields(fields) {
	    return formatFieldsWithConfig(fields, sidebarFavoritesFieldsConfig);
	  }
	}

	/* eslint-disable no-param-reassign */
	class MembersModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getChatState() {
	    return {
	      users: new Set(),
	      hasNextPage: true,
	      lastId: 0,
	      inited: false
	    };
	  }
	  getGetters() {
	    return {
	      /** @function sidebar/members/get */
	      get: state => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        return [...state.collection[chatId].users];
	      },
	      /** @function sidebar/members/getSize */
	      getSize: state => chatId => {
	        if (!state.collection[chatId]) {
	          return 0;
	        }
	        return state.collection[chatId].users.size;
	      },
	      /** @function sidebar/members/hasNextPage */
	      hasNextPage: state => chatId => {
	        if (!state.collection[chatId]) {
	          return false;
	        }
	        return state.collection[chatId].hasNextPage;
	      },
	      /** @function sidebar/members/getLastId */
	      getLastId: state => chatId => {
	        if (!state.collection[chatId]) {
	          return false;
	        }
	        return state.collection[chatId].lastId;
	      },
	      /** @function sidebar/members/getInited */
	      getInited: state => chatId => {
	        if (!state.collection[chatId]) {
	          return false;
	        }
	        return state.collection[chatId].inited;
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function sidebar/members/set */
	      set: (store, payload) => {
	        const {
	          chatId,
	          users,
	          hasNextPage,
	          lastId
	        } = payload;
	        if (!main_core.Type.isNil(hasNextPage)) {
	          store.commit('setHasNextPage', {
	            chatId,
	            hasNextPage
	          });
	        }
	        if (!main_core.Type.isNil(lastId)) {
	          store.commit('setLastId', {
	            chatId,
	            lastId
	          });
	        }
	        store.commit('setInited', {
	          chatId,
	          inited: true
	        });
	        if (users.length > 0) {
	          store.commit('set', {
	            chatId,
	            users
	          });
	        }
	      },
	      /** @function sidebar/members/delete */
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
	        const {
	          chatId,
	          users
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        users.forEach(id => {
	          state.collection[chatId].users.add(id);
	        });
	      },
	      setHasNextPage: (state, payload) => {
	        const {
	          chatId,
	          hasNextPage
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].hasNextPage = hasNextPage;
	      },
	      setLastId: (state, payload) => {
	        const {
	          chatId,
	          lastId
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].lastId = lastId;
	      },
	      setInited: (state, payload) => {
	        const {
	          chatId,
	          inited
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].inited = inited;
	      },
	      delete: (state, payload) => {
	        const {
	          chatId,
	          userId
	        } = payload;
	        state.collection[chatId].users.delete(userId);
	      }
	    };
	  }
	}

	/* eslint-disable no-param-reassign */
	class MessageSearchModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      historyLimitExceededCollection: {}
	    };
	  }
	  getGetters() {
	    return {
	      /** @function sidebar/messageSearch/isHistoryLimitExceeded */
	      isHistoryLimitExceeded: state => chatId => {
	        var _state$historyLimitEx;
	        const isAvailable = im_v2_application_core.Core.getStore().getters['application/tariffRestrictions/isHistoryAvailable'];
	        if (isAvailable) {
	          return false;
	        }
	        return (_state$historyLimitEx = state.historyLimitExceededCollection[chatId]) != null ? _state$historyLimitEx : false;
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function sidebar/messageSearch/setHistoryLimitExceeded */
	      setHistoryLimitExceeded: (store, payload) => {
	        const {
	          chatId,
	          isHistoryLimitExceeded = false
	        } = payload;
	        store.commit('setHistoryLimitExceeded', {
	          chatId,
	          isHistoryLimitExceeded
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      setHistoryLimitExceeded: (state, payload) => {
	        const {
	          chatId,
	          isHistoryLimitExceeded
	        } = payload;
	        if (state.historyLimitExceededCollection[chatId] && !isHistoryLimitExceeded) {
	          return;
	        }
	        state.historyLimitExceededCollection[chatId] = isHistoryLimitExceeded;
	      }
	    };
	  }
	}

	const sidebarTaskFieldsConfig = [{
	  fieldName: 'id',
	  targetFieldName: 'id',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'messageId',
	  targetFieldName: 'messageId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'chatId',
	  targetFieldName: 'chatId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'authorId',
	  targetFieldName: 'authorId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'dateCreate',
	  targetFieldName: 'date',
	  checkFunction: main_core.Type.isString,
	  formatFunction: im_v2_lib_utils.Utils.date.cast
	}, {
	  fieldName: 'task',
	  targetFieldName: 'task',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: target => {
	    return formatFieldsWithConfig(target, taskFieldsConfig);
	  }
	}];
	const taskFieldsConfig = [{
	  fieldName: 'id',
	  targetFieldName: 'id',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'title',
	  targetFieldName: 'title',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'creatorId',
	  targetFieldName: 'creatorId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'responsibleId',
	  targetFieldName: 'responsibleId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'statusTitle',
	  targetFieldName: 'statusTitle',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'deadline',
	  targetFieldName: 'deadline',
	  checkFunction: main_core.Type.isString,
	  formatFunction: im_v2_lib_utils.Utils.date.cast
	}, {
	  fieldName: 'state',
	  targetFieldName: 'state',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'color',
	  targetFieldName: 'color',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'source',
	  targetFieldName: 'source',
	  checkFunction: main_core.Type.isString
	}];

	/* eslint-disable no-param-reassign */
	class TasksModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {},
	      collectionSearch: {},
	      historyLimitExceededCollection: {}
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
	  getChatState() {
	    return {
	      items: new Map(),
	      hasNextPage: true,
	      lastId: 0
	    };
	  }
	  getGetters() {
	    return {
	      /** @function sidebar/tasks/get */
	      get: state => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        return [...state.collection[chatId].items.values()].sort((a, b) => b.id - a.id);
	      },
	      /** @function sidebar/tasks/getSearchResultCollection */
	      getSearchResultCollection: state => chatId => {
	        if (!state.collectionSearch[chatId]) {
	          return [];
	        }
	        return [...state.collectionSearch[chatId].items.values()].sort((a, b) => b.id - a.id);
	      },
	      /** @function sidebar/tasks/getSize */
	      getSize: state => chatId => {
	        if (!state.collection[chatId]) {
	          return 0;
	        }
	        return state.collection[chatId].items.size;
	      },
	      /** @function sidebar/tasks/hasNextPage */
	      hasNextPage: state => chatId => {
	        if (!state.collection[chatId]) {
	          return false;
	        }
	        return state.collection[chatId].hasNextPage;
	      },
	      /** @function sidebar/tasks/getLastId */
	      getLastId: state => chatId => {
	        if (!state.collection[chatId]) {
	          return false;
	        }
	        return state.collection[chatId].lastId;
	      },
	      /** @function sidebar/tasks/getSearchResultCollectionLastId */
	      getSearchResultCollectionLastId: state => chatId => {
	        if (!state.collectionSearch[chatId]) {
	          return false;
	        }
	        return state.collectionSearch[chatId].lastId;
	      },
	      /** @function sidebar/tasks/isHistoryLimitExceeded */
	      isHistoryLimitExceeded: state => chatId => {
	        var _state$historyLimitEx;
	        const isAvailable = im_v2_application_core.Core.getStore().getters['application/tariffRestrictions/isHistoryAvailable'];
	        if (isAvailable) {
	          return false;
	        }
	        return (_state$historyLimitEx = state.historyLimitExceededCollection[chatId]) != null ? _state$historyLimitEx : false;
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function sidebar/tasks/set */
	      set: (store, payload) => {
	        const {
	          chatId,
	          tasks,
	          hasNextPage,
	          lastId,
	          isHistoryLimitExceeded = false
	        } = payload;
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        store.commit('setHistoryLimitExceeded', {
	          chatId,
	          isHistoryLimitExceeded
	        });
	        if (!main_core.Type.isNil(hasNextPage)) {
	          store.commit('setHasNextPage', {
	            chatId,
	            hasNextPage
	          });
	        }
	        if (!main_core.Type.isNil(lastId)) {
	          store.commit('setLastId', {
	            chatId,
	            lastId
	          });
	        }
	        tasks.forEach(task => {
	          const preparedTask = {
	            ...this.getElementState(),
	            ...this.formatFields(task)
	          };
	          store.commit('add', {
	            chatId,
	            task: preparedTask
	          });
	        });
	      },
	      /** @function sidebar/tasks/clearSearch */
	      clearSearch: store => {
	        store.commit('clearSearch', {});
	      },
	      /** @function sidebar/tasks/setSearch */
	      setSearch: (store, payload) => {
	        const {
	          chatId,
	          tasks,
	          hasNextPage,
	          lastId,
	          isHistoryLimitExceeded = false
	        } = payload;
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        store.commit('setHistoryLimitExceeded', {
	          chatId,
	          isHistoryLimitExceeded
	        });
	        if (!main_core.Type.isNil(hasNextPage)) {
	          store.commit('setHasNextPageSearch', {
	            chatId,
	            hasNextPage
	          });
	        }
	        if (!main_core.Type.isNil(lastId)) {
	          store.commit('setLastIdSearch', {
	            chatId,
	            lastId
	          });
	        }
	        tasks.forEach(task => {
	          const preparedTask = {
	            ...this.getElementState(),
	            ...this.formatFields(task)
	          };
	          store.commit('addSearch', {
	            chatId,
	            task: preparedTask
	          });
	        });
	      },
	      /** @function sidebar/tasks/delete */
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
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].items.set(task.id, task);
	      },
	      addSearch: (state, payload) => {
	        const {
	          chatId,
	          task
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (!hasCollection) {
	          state.collectionSearch[chatId] = this.getChatState();
	        }
	        state.collectionSearch[chatId].items.set(task.id, task);
	      },
	      delete: (state, payload) => {
	        const {
	          id,
	          chatId
	        } = payload;
	        const hasCollectionSearch = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (hasCollectionSearch) {
	          state.collectionSearch[chatId].items.delete(id);
	        }
	        state.collection[chatId].items.delete(id);
	      },
	      setHasNextPage: (state, payload) => {
	        const {
	          chatId,
	          hasNextPage
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].hasNextPage = hasNextPage;
	      },
	      setHasNextPageSearch: (state, payload) => {
	        const {
	          chatId,
	          hasNextPage
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (!hasCollection) {
	          state.collectionSearch[chatId] = this.getChatState();
	        }
	        state.collectionSearch[chatId].hasNextPage = hasNextPage;
	      },
	      setLastId: (state, payload) => {
	        const {
	          chatId,
	          lastId
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].lastId = lastId;
	      },
	      setLastIdSearch: (state, payload) => {
	        const {
	          chatId,
	          lastId
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (!hasCollection) {
	          state.collectionSearch[chatId] = this.getChatState();
	        }
	        state.collectionSearch[chatId].lastId = lastId;
	      },
	      clearSearch: state => {
	        state.collectionSearch = {};
	      },
	      setHistoryLimitExceeded: (state, payload) => {
	        const {
	          chatId,
	          isHistoryLimitExceeded
	        } = payload;
	        if (state.historyLimitExceededCollection[chatId] && !isHistoryLimitExceeded) {
	          return;
	        }
	        state.historyLimitExceededCollection[chatId] = isHistoryLimitExceeded;
	      }
	    };
	  }
	  formatFields(fields) {
	    return formatFieldsWithConfig(fields, sidebarTaskFieldsConfig);
	  }
	}

	const sidebarMeetingFieldsConfig = [{
	  fieldName: 'id',
	  targetFieldName: 'id',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'messageId',
	  targetFieldName: 'messageId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'chatId',
	  targetFieldName: 'chatId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'authorId',
	  targetFieldName: 'authorId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'dateCreate',
	  targetFieldName: 'date',
	  checkFunction: main_core.Type.isString,
	  formatFunction: im_v2_lib_utils.Utils.date.cast
	}, {
	  fieldName: 'calendar',
	  targetFieldName: 'meeting',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: target => {
	    return formatFieldsWithConfig(target, meetingFieldsConfig);
	  }
	}];
	const meetingFieldsConfig = [{
	  fieldName: 'id',
	  targetFieldName: 'id',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'title',
	  targetFieldName: 'title',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'dateFrom',
	  targetFieldName: 'dateFrom',
	  checkFunction: main_core.Type.isString,
	  formatFunction: im_v2_lib_utils.Utils.date.cast
	}, {
	  fieldName: 'dateTo',
	  targetFieldName: 'dateTo',
	  checkFunction: main_core.Type.isString,
	  formatFunction: im_v2_lib_utils.Utils.date.cast
	}, {
	  fieldName: 'source',
	  targetFieldName: 'source',
	  checkFunction: main_core.Type.isString
	}];

	/* eslint-disable no-param-reassign */
	class MeetingsModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {},
	      collectionSearch: {},
	      historyLimitExceededCollection: {}
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
	  getChatState() {
	    return {
	      items: new Map(),
	      hasNextPage: true,
	      lastId: 0
	    };
	  }
	  getGetters() {
	    return {
	      /** @function sidebar/meetings/get */
	      get: state => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        return [...state.collection[chatId].items.values()].sort((a, b) => b.id - a.id);
	      },
	      /** @function sidebar/meetings/getSearchResultCollection */
	      getSearchResultCollection: state => chatId => {
	        if (!state.collectionSearch[chatId]) {
	          return [];
	        }
	        return [...state.collectionSearch[chatId].items.values()].sort((a, b) => b.id - a.id);
	      },
	      /** @function sidebar/meetings/getSize */
	      getSize: state => chatId => {
	        if (!state.collection[chatId]) {
	          return 0;
	        }
	        return state.collection[chatId].items.size;
	      },
	      /** @function sidebar/meetings/hasNextPage */
	      hasNextPage: state => chatId => {
	        if (!state.collection[chatId]) {
	          return false;
	        }
	        return state.collection[chatId].hasNextPage;
	      },
	      /** @function sidebar/meetings/getLastId */
	      getLastId: state => chatId => {
	        if (!state.collection[chatId]) {
	          return false;
	        }
	        return state.collection[chatId].lastId;
	      },
	      /** @function sidebar/meetings/getSearchResultCollectionLastId */
	      getSearchResultCollectionLastId: state => chatId => {
	        if (!state.collectionSearch[chatId]) {
	          return false;
	        }
	        return state.collectionSearch[chatId].lastId;
	      },
	      /** @function sidebar/meetings/isHistoryLimitExceeded */
	      isHistoryLimitExceeded: state => chatId => {
	        var _state$historyLimitEx;
	        const isAvailable = im_v2_application_core.Core.getStore().getters['application/tariffRestrictions/isHistoryAvailable'];
	        if (isAvailable) {
	          return false;
	        }
	        return (_state$historyLimitEx = state.historyLimitExceededCollection[chatId]) != null ? _state$historyLimitEx : false;
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function sidebar/meetings/set */
	      set: (store, payload) => {
	        const {
	          chatId,
	          meetings,
	          hasNextPage,
	          lastId,
	          isHistoryLimitExceeded = false
	        } = payload;
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        store.commit('setHistoryLimitExceeded', {
	          chatId,
	          isHistoryLimitExceeded
	        });
	        if (!main_core.Type.isNil(hasNextPage)) {
	          store.commit('setHasNextPage', {
	            chatId,
	            hasNextPage
	          });
	        }
	        if (!main_core.Type.isNil(lastId)) {
	          store.commit('setLastId', {
	            chatId,
	            lastId
	          });
	        }
	        meetings.forEach(meeting => {
	          const preparedMeeting = {
	            ...this.getElementState(),
	            ...this.formatFields(meeting)
	          };
	          store.commit('add', {
	            chatId,
	            meeting: preparedMeeting
	          });
	        });
	      },
	      /** @function sidebar/meetings/setSearch */
	      setSearch: (store, payload) => {
	        const {
	          chatId,
	          meetings,
	          hasNextPage,
	          lastId,
	          isHistoryLimitExceeded = false
	        } = payload;
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        store.commit('setHistoryLimitExceeded', {
	          chatId,
	          isHistoryLimitExceeded
	        });
	        if (!main_core.Type.isNil(hasNextPage)) {
	          store.commit('setHasNextPageSearch', {
	            chatId,
	            hasNextPage
	          });
	        }
	        if (!main_core.Type.isNil(lastId)) {
	          store.commit('setLastIdSearch', {
	            chatId,
	            lastId
	          });
	        }
	        meetings.forEach(meeting => {
	          const preparedMeeting = {
	            ...this.getElementState(),
	            ...this.formatFields(meeting)
	          };
	          store.commit('addSearch', {
	            chatId,
	            meeting: preparedMeeting
	          });
	        });
	      },
	      /** @function sidebar/meetings/delete */
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
	      },
	      /** @function sidebar/meetings/clearSearch */
	      clearSearch: store => {
	        store.commit('clearSearch', {});
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
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].items.set(meeting.id, meeting);
	      },
	      addSearch: (state, payload) => {
	        const {
	          chatId,
	          meeting
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (!hasCollection) {
	          state.collectionSearch[chatId] = this.getChatState();
	        }
	        state.collectionSearch[chatId].items.set(meeting.id, meeting);
	      },
	      delete: (state, payload) => {
	        const {
	          id,
	          chatId
	        } = payload;
	        const hasCollectionSearch = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (hasCollectionSearch) {
	          state.collectionSearch[chatId].items.delete(id);
	        }
	        state.collection[chatId].items.delete(id);
	      },
	      setHasNextPage: (state, payload) => {
	        const {
	          chatId,
	          hasNextPage
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].hasNextPage = hasNextPage;
	      },
	      setHasNextPageSearch: (state, payload) => {
	        const {
	          chatId,
	          hasNextPage
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (!hasCollection) {
	          state.collectionSearch[chatId] = this.getChatState();
	        }
	        state.collectionSearch[chatId].hasNextPage = hasNextPage;
	      },
	      setLastId: (state, payload) => {
	        const {
	          chatId,
	          lastId
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId]);
	        if (!hasCollection) {
	          state.collection[chatId] = this.getChatState();
	        }
	        state.collection[chatId].lastId = lastId;
	      },
	      setLastIdSearch: (state, payload) => {
	        const {
	          chatId,
	          lastId
	        } = payload;
	        const hasCollection = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        if (!hasCollection) {
	          state.collectionSearch[chatId] = this.getChatState();
	        }
	        state.collectionSearch[chatId].lastId = lastId;
	      },
	      clearSearch: state => {
	        state.collectionSearch = {};
	      },
	      setHistoryLimitExceeded: (state, payload) => {
	        const {
	          chatId,
	          isHistoryLimitExceeded
	        } = payload;
	        if (state.historyLimitExceededCollection[chatId] && !isHistoryLimitExceeded) {
	          return;
	        }
	        state.historyLimitExceededCollection[chatId] = isHistoryLimitExceeded;
	      }
	    };
	  }
	  formatFields(fields) {
	    return formatFieldsWithConfig(fields, sidebarMeetingFieldsConfig);
	  }
	}

	const sidebarFilesFieldsConfig = [{
	  fieldName: 'id',
	  targetFieldName: 'id',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'messageId',
	  targetFieldName: 'messageId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'chatId',
	  targetFieldName: 'chatId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'authorId',
	  targetFieldName: 'authorId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: ['dateCreate', 'date'],
	  targetFieldName: 'date',
	  checkFunction: main_core.Type.isString,
	  formatFunction: im_v2_lib_utils.Utils.date.cast
	}, {
	  fieldName: ['fileId', 'id'],
	  targetFieldName: 'fileId',
	  checkFunction: main_core.Type.isNumber
	}];

	/* eslint-disable no-param-reassign */
	class FilesModel$1 extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {},
	      collectionSearch: {},
	      historyLimitExceededCollection: {}
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
	  getChatState() {
	    return {
	      items: new Map(),
	      hasNextPage: true,
	      lastId: 0
	    };
	  }

	  // eslint-disable-next-line max-lines-per-function
	  getGetters() {
	    return {
	      /** @function sidebar/files/get */
	      get: state => (chatId, subType) => {
	        if (!state.collection[chatId] || !state.collection[chatId][subType]) {
	          return [];
	        }
	        return [...state.collection[chatId][subType].items.values()].sort((a, b) => b.id - a.id);
	      },
	      /** @function sidebar/files/getSearchResultCollection */
	      getSearchResultCollection: state => (chatId, subType) => {
	        if (!state.collectionSearch[chatId] || !state.collectionSearch[chatId][subType]) {
	          return [];
	        }
	        return [...state.collectionSearch[chatId][subType].items.values()].sort((a, b) => b.id - a.id);
	      },
	      /** @function sidebar/files/getLatest */
	      getLatest: (state, getters, rootState, rootGetters) => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        let media = [];
	        let audio = [];
	        let documents = [];
	        let other = [];
	        let briefs = [];
	        if (state.collection[chatId][im_v2_const.SidebarFileTypes.media]) {
	          media = [...state.collection[chatId][im_v2_const.SidebarFileTypes.media].items.values()];
	        }
	        if (state.collection[chatId][im_v2_const.SidebarFileTypes.audio]) {
	          audio = [...state.collection[chatId][im_v2_const.SidebarFileTypes.audio].items.values()];
	        }
	        if (state.collection[chatId][im_v2_const.SidebarFileTypes.document]) {
	          documents = [...state.collection[chatId][im_v2_const.SidebarFileTypes.document].items.values()];
	        }
	        if (state.collection[chatId][im_v2_const.SidebarFileTypes.brief]) {
	          briefs = [...state.collection[chatId][im_v2_const.SidebarFileTypes.brief].items.values()];
	        }
	        if (state.collection[chatId][im_v2_const.SidebarFileTypes.other]) {
	          other = [...state.collection[chatId][im_v2_const.SidebarFileTypes.other].items.values()];
	        }
	        const sortedFlatCollection = [media, audio, documents, briefs, other].flat().sort((a, b) => b.id - a.id);
	        return this.getTopThreeCompletedFiles(sortedFlatCollection, rootGetters);
	      },
	      /** @function sidebar/files/getLatestUnsorted */
	      getLatestUnsorted: (state, getters, rootState, rootGetters) => chatId => {
	        if (!state.collection[chatId]) {
	          return [];
	        }
	        let unsorted = [];
	        if (state.collection[chatId][im_v2_const.SidebarFileTypes.fileUnsorted]) {
	          unsorted = [...state.collection[chatId][im_v2_const.SidebarFileTypes.fileUnsorted].items.values()];
	        }
	        const sortedCollection = unsorted.sort((a, b) => b.id - a.id);
	        return this.getTopThreeCompletedFiles(sortedCollection, rootGetters);
	      },
	      /** @function sidebar/files/getSize */
	      getSize: state => (chatId, subType) => {
	        if (!state.collection[chatId] || !state.collection[chatId][subType]) {
	          return 0;
	        }
	        return state.collection[chatId][subType].items.size;
	      },
	      /** @function sidebar/files/hasNextPage */
	      hasNextPage: state => (chatId, subType) => {
	        if (!state.collection[chatId] || !state.collection[chatId][subType]) {
	          return false;
	        }
	        return state.collection[chatId][subType].hasNextPage;
	      },
	      /** @function sidebar/files/hasNextPageSearch */
	      hasNextPageSearch: state => (chatId, subType) => {
	        if (!state.collectionSearch[chatId] || !state.collectionSearch[chatId][subType]) {
	          return false;
	        }
	        return state.collectionSearch[chatId][subType].hasNextPage;
	      },
	      /** @function sidebar/files/getLastId */
	      getLastId: state => (chatId, subType) => {
	        if (!state.collection[chatId] || !state.collection[chatId][subType]) {
	          return false;
	        }
	        return state.collection[chatId][subType].lastId;
	      },
	      /** @function sidebar/files/getSearchResultCollectionLastId */
	      getSearchResultCollectionLastId: state => (chatId, subType) => {
	        if (!state.collectionSearch[chatId] || !state.collectionSearch[chatId][subType]) {
	          return false;
	        }
	        return state.collectionSearch[chatId][subType].lastId;
	      },
	      /** @function sidebar/files/isHistoryLimitExceeded */
	      isHistoryLimitExceeded: state => chatId => {
	        var _state$historyLimitEx;
	        const isAvailable = im_v2_application_core.Core.getStore().getters['application/tariffRestrictions/isHistoryAvailable'];
	        if (isAvailable) {
	          return false;
	        }
	        return (_state$historyLimitEx = state.historyLimitExceededCollection[chatId]) != null ? _state$historyLimitEx : false;
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function sidebar/files/set */
	      set: (store, payload) => {
	        const {
	          chatId,
	          files,
	          subType
	        } = payload;
	        if (!main_core.Type.isArrayFilled(files) || !main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        files.forEach(file => {
	          const preparedFile = {
	            ...this.getElementState(),
	            ...this.formatFields(file)
	          };
	          store.commit('add', {
	            chatId,
	            subType,
	            file: preparedFile
	          });
	        });
	      },
	      /** @function sidebar/files/setSearch */
	      setSearch: (store, payload) => {
	        const {
	          chatId,
	          files,
	          subType
	        } = payload;
	        if (!main_core.Type.isArrayFilled(files) || !main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        files.forEach(file => {
	          const preparedFile = {
	            ...this.getElementState(),
	            ...this.formatFields(file)
	          };
	          store.commit('addSearch', {
	            chatId,
	            subType,
	            file: preparedFile
	          });
	        });
	      },
	      /** @function sidebar/files/delete */
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
	      },
	      /** @function sidebar/files/setHasNextPage */
	      setHasNextPage: (store, payload) => {
	        const {
	          chatId,
	          subType,
	          hasNextPage
	        } = payload;
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collection[chatId]) {
	          return;
	        }
	        store.commit('setHasNextPage', {
	          chatId,
	          subType,
	          hasNextPage
	        });
	      },
	      /** @function sidebar/files/setHasNextPageSearch */
	      setHasNextPageSearch: (store, payload) => {
	        const {
	          chatId,
	          subType,
	          hasNextPage
	        } = payload;
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collectionSearch[chatId]) {
	          return;
	        }
	        store.commit('setHasNextPageSearch', {
	          chatId,
	          subType,
	          hasNextPage
	        });
	      },
	      /** @function sidebar/files/setLastId */
	      setLastId: (store, payload) => {
	        const {
	          chatId,
	          subType,
	          lastId
	        } = payload;
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collection[chatId]) {
	          return;
	        }
	        store.commit('setLastId', {
	          chatId,
	          subType,
	          lastId
	        });
	      },
	      /** @function sidebar/files/setLastIdSearch */
	      setLastIdSearch: (store, payload) => {
	        const {
	          chatId,
	          subType,
	          lastId
	        } = payload;
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        if (!store.state.collectionSearch[chatId]) {
	          return;
	        }
	        store.commit('setLastIdSearch', {
	          chatId,
	          subType,
	          lastId
	        });
	      },
	      /** @function sidebar/files/clearSearch */
	      clearSearch: store => {
	        store.commit('clearSearch', {});
	      },
	      /** @function sidebar/files/setHistoryLimitExceeded */
	      setHistoryLimitExceeded: (store, payload) => {
	        const {
	          chatId,
	          isHistoryLimitExceeded = false
	        } = payload;
	        store.commit('setHistoryLimitExceeded', {
	          chatId,
	          isHistoryLimitExceeded
	        });
	      }
	    };
	  }

	  // eslint-disable-next-line max-lines-per-function
	  getMutations() {
	    return {
	      add: (state, payload) => {
	        const {
	          chatId,
	          file,
	          subType
	        } = payload;
	        if (!state.collection[chatId]) {
	          state.collection[chatId] = {};
	        }
	        if (!state.collection[chatId][subType]) {
	          state.collection[chatId][subType] = this.getChatState();
	        }
	        state.collection[chatId][subType].items.set(file.id, file);
	      },
	      addSearch: (state, payload) => {
	        const {
	          chatId,
	          file,
	          subType
	        } = payload;
	        if (!state.collectionSearch[chatId]) {
	          state.collectionSearch[chatId] = {};
	        }
	        if (!state.collectionSearch[chatId][subType]) {
	          state.collectionSearch[chatId][subType] = this.getChatState();
	        }
	        state.collectionSearch[chatId][subType].items.set(file.id, file);
	      },
	      delete: (state, payload) => {
	        const {
	          chatId,
	          id
	        } = payload;
	        const hasCollectionSearch = !main_core.Type.isNil(state.collectionSearch[chatId]);
	        Object.values(im_v2_const.SidebarFileTypes).forEach(subType => {
	          if (state.collection[chatId][subType] && state.collection[chatId][subType].items.has(id)) {
	            state.collection[chatId][subType].items.delete(id);
	            if (hasCollectionSearch) {
	              state.collectionSearch[chatId][subType].items.delete(id);
	            }
	          }
	        });
	      },
	      setHasNextPage: (state, payload) => {
	        const {
	          chatId,
	          subType,
	          hasNextPage
	        } = payload;
	        if (!state.collection[chatId]) {
	          state.collection[chatId] = {};
	        }
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId][subType]);
	        if (!hasCollection) {
	          state.collection[chatId][subType] = this.getChatState();
	        }
	        state.collection[chatId][subType].hasNextPage = hasNextPage;
	      },
	      setHasNextPageSearch: (state, payload) => {
	        const {
	          chatId,
	          subType,
	          hasNextPage
	        } = payload;
	        if (!state.collectionSearch[chatId]) {
	          state.collectionSearch[chatId] = {};
	        }
	        const hasCollection = !main_core.Type.isNil(state.collectionSearch[chatId][subType]);
	        if (!hasCollection) {
	          state.collectionSearch[chatId][subType] = this.getChatState();
	        }
	        state.collectionSearch[chatId][subType].hasNextPage = hasNextPage;
	      },
	      setLastId: (state, payload) => {
	        const {
	          chatId,
	          subType,
	          lastId
	        } = payload;
	        if (!state.collection[chatId]) {
	          state.collection[chatId] = {};
	        }
	        const hasCollection = !main_core.Type.isNil(state.collection[chatId][subType]);
	        if (!hasCollection) {
	          state.collection[chatId][subType] = this.getChatState();
	        }
	        state.collection[chatId][subType].lastId = lastId;
	      },
	      setLastIdSearch: (state, payload) => {
	        const {
	          chatId,
	          subType,
	          lastId
	        } = payload;
	        if (!state.collectionSearch[chatId]) {
	          state.collectionSearch[chatId] = {};
	        }
	        const hasCollection = !main_core.Type.isNil(state.collectionSearch[chatId][subType]);
	        if (!hasCollection) {
	          state.collectionSearch[chatId][subType] = this.getChatState();
	        }
	        state.collectionSearch[chatId][subType].lastId = lastId;
	      },
	      clearSearch: state => {
	        state.collectionSearch = {};
	      },
	      setHistoryLimitExceeded: (state, payload) => {
	        const {
	          chatId,
	          isHistoryLimitExceeded
	        } = payload;
	        if (state.historyLimitExceededCollection[chatId] && !isHistoryLimitExceeded) {
	          return;
	        }
	        state.historyLimitExceededCollection[chatId] = isHistoryLimitExceeded;
	      }
	    };
	  }
	  formatFields(fields) {
	    return formatFieldsWithConfig(fields, sidebarFilesFieldsConfig);
	  }
	  getTopThreeCompletedFiles(collection, rootGetters) {
	    return collection.filter(sidebarFile => {
	      const file = rootGetters['files/get'](sidebarFile.fileId, true);
	      return file.progress === 100;
	    }).slice(0, 3);
	  }
	}

	const sidebarMultidialogFieldsConfig = [{
	  fieldName: 'dialogId',
	  targetFieldName: 'dialogId',
	  checkFunction: main_core.Type.String
	}, {
	  fieldName: 'chatId',
	  targetFieldName: 'chatId',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'openSessionsLimit',
	  targetFieldName: 'openSessionsLimit',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'status',
	  targetFieldName: 'status',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'dateMessage',
	  targetFieldName: 'date',
	  checkFunction: main_core.Type.isString,
	  formatFunction: im_v2_lib_utils.Utils.date.cast
	}];

	/* eslint-disable no-param-reassign */
	class MultidialogModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      isInited: false,
	      isInitedDetail: false,
	      chatsCount: 0,
	      unreadChats: new Set(),
	      openSessionsLimit: 0,
	      multidialogs: {}
	    };
	  }
	  getElementState() {
	    return {
	      dialogId: '',
	      chatId: 0,
	      status: '',
	      date: new Date()
	    };
	  }
	  getGetters() {
	    return {
	      /** @function sidebar/multidialog/isInited */
	      isInited: ({
	        isInited
	      }) => {
	        return isInited;
	      },
	      /** @function sidebar/multidialog/isInitedDetail */
	      isInitedDetail: ({
	        isInitedDetail
	      }) => {
	        return isInitedDetail;
	      },
	      /** @function sidebar/multidialog/getOpenSessionsLimit */
	      getOpenSessionsLimit: ({
	        openSessionsLimit
	      }) => {
	        return openSessionsLimit;
	      },
	      /** @function sidebar/multidialog/getChatsCount */
	      getChatsCount: ({
	        chatsCount
	      }) => {
	        return chatsCount;
	      },
	      /** @function sidebar/multidialog/getTotalChatCounter */
	      getTotalChatCounter: ({
	        unreadChats
	      }) => {
	        let count = 0;
	        unreadChats.forEach(chatId => {
	          count += im_v2_application_core.Core.getStore().getters['counters/getChatCounterByChatId'](chatId);
	        });
	        return count;
	      },
	      /** @function sidebar/multidialog/get */
	      get: ({
	        multidialogs
	      }) => chatId => {
	        return multidialogs[chatId];
	      },
	      /** @function sidebar/multidialog/isSupport */
	      isSupport: () => dialogId => {
	        const isSupportBot = im_v2_application_core.Core.getStore().getters['users/bots/isSupport'](dialogId);
	        const isSupportChat = im_v2_application_core.Core.getStore().getters['chats/isSupport'](dialogId);
	        return isSupportChat || isSupportBot;
	      },
	      /** @function sidebar/multidialog/hasNextPage */
	      hasNextPage: ({
	        chatsCount,
	        multidialogs
	      }) => {
	        return chatsCount > Object.keys(multidialogs).length;
	      },
	      /** @function sidebar/multidialog/getNumberMultidialogs */
	      getNumberMultidialogs: ({
	        multidialogs
	      }) => {
	        return Object.keys(multidialogs).length;
	      },
	      /** @function sidebar/multidialog/getMultidialogsByStatus */
	      getMultidialogsByStatus: ({
	        multidialogs
	      }) => status => {
	        return Object.values(multidialogs).filter(multidialog => status.includes(multidialog.status));
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function sidebar/multidialog/setInited */
	      setInited: (store, isInited) => {
	        store.commit('setInited', isInited);
	      },
	      /** @function sidebar/multidialog/setInitedDetail */
	      setInitedDetail: (store, isInitedDetail) => {
	        store.commit('setInitedDetail', isInitedDetail);
	      },
	      /** @function sidebar/multidialog/addMultidialogs */
	      addMultidialogs: (store, multidialogs) => {
	        if (!main_core.Type.isArray(multidialogs)) {
	          return;
	        }
	        multidialogs.forEach(multidialog => {
	          const preparedTicket = {
	            ...this.getElementState(),
	            ...this.formatFields(multidialog)
	          };
	          store.commit('addMultidialog', preparedTicket);
	        });
	      },
	      /** @function sidebar/multidialog/setOpenSessionsLimit */
	      setOpenSessionsLimit: (store, openSessionsLimit) => {
	        if (main_core.Type.isNumber(openSessionsLimit)) {
	          store.commit('setOpenSessionsLimit', openSessionsLimit);
	        }
	      },
	      /** @function sidebar/multidialog/setChatsCount */
	      setChatsCount: (store, chatsCount) => {
	        if (main_core.Type.isNumber(chatsCount)) {
	          store.commit('setChatsCount', chatsCount);
	        }
	      },
	      /** @function sidebar/multidialog/setUnreadChats */
	      setUnreadChats: (store, unreadChats) => {
	        if (main_core.Type.isArray(unreadChats)) {
	          store.commit('setUnreadChats', unreadChats);
	        }
	      },
	      /** @function sidebar/multidialog/set */
	      set: (store, payload) => {
	        const {
	          unreadChats,
	          multidialogs,
	          chatsCount,
	          openSessionsLimit
	        } = payload;
	        store.dispatch('setUnreadChats', unreadChats);
	        store.dispatch('setChatsCount', chatsCount);
	        store.dispatch('setOpenSessionsLimit', openSessionsLimit);
	        store.dispatch('addMultidialogs', multidialogs);
	      },
	      /** @function sidebar/multidialog/deleteUnreadChats */
	      deleteUnreadChats: (store, chatId) => {
	        store.commit('deleteUnreadChats', chatId);
	      }
	    };
	  }
	  getMutations() {
	    return {
	      /** @function sidebar/multidialog/setInited */
	      setInited: (state, isInited) => {
	        state.isInited = isInited;
	      },
	      /** @function sidebar/multidialog/setInitedDetail */
	      setInitedDetail: (state, isInitedDetail) => {
	        state.isInitedDetail = isInitedDetail;
	      },
	      addMultidialog: (state, multidialog) => {
	        state.multidialogs[multidialog.chatId] = multidialog;
	      },
	      setChatsCount: (state, chatsCount) => {
	        state.chatsCount = chatsCount;
	      },
	      setOpenSessionsLimit: (state, openSessionsLimit) => {
	        state.openSessionsLimit = openSessionsLimit;
	      },
	      setUnreadChats: (state, unreadChats) => {
	        unreadChats.forEach(chatId => {
	          state.unreadChats.add(chatId);
	        });
	      },
	      deleteUnreadChats: ({
	        unreadChats
	      }, chatId) => {
	        unreadChats.delete(chatId);
	      }
	    };
	  }
	  formatFields(fields) {
	    return formatFieldsWithConfig(fields, sidebarMultidialogFieldsConfig);
	  }
	}

	/* eslint-disable no-param-reassign */
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
	      files: FilesModel$1,
	      multidialog: MultidialogModel,
	      messageSearch: MessageSearchModel
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
	      /** @function sidebar/isInited */
	      isInited: state => chatId => {
	        return state.initedList.has(chatId);
	      },
	      /** @function sidebar/hasHistoryLimit */
	      hasHistoryLimit: () => chatId => {
	        const limitsByPanel = ['sidebar/links/isHistoryLimitExceeded', 'sidebar/files/isHistoryLimitExceeded', 'sidebar/favorites/isHistoryLimitExceeded', 'sidebar/meetings/isHistoryLimitExceeded', 'sidebar/tasks/isHistoryLimitExceeded', 'sidebar/messageSearch/isHistoryLimitExceeded'].map(getterName => im_v2_application_core.Core.getStore().getters[getterName](chatId));
	        return limitsByPanel.some(hasLimit => hasLimit);
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function sidebar/setInited */
	      setInited: (store, chatId) => {
	        if (!main_core.Type.isNumber(chatId)) {
	          return;
	        }
	        store.commit('setInited', chatId);
	      },
	      /** @function sidebar/setFilesMigrated */
	      setFilesMigrated: (store, value) => {
	        if (!main_core.Type.isBoolean(value)) {
	          return;
	        }
	        store.commit('setFilesMigrated', value);
	      },
	      /** @function sidebar/setLinksMigrated */
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
	      setInited: (state, chatId) => {
	        state.initedList.add(chatId);
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

	var _getChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChat");
	var _getChatByChatId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChatByChatId");
	var _isChatMuted = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isChatMuted");
	var _getRecentItemCounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRecentItemCounter");
	class CountersModel extends ui_vue3_vuex.BuilderModel {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _getRecentItemCounter, {
	      value: _getRecentItemCounter2
	    });
	    Object.defineProperty(this, _isChatMuted, {
	      value: _isChatMuted2
	    });
	    Object.defineProperty(this, _getChatByChatId, {
	      value: _getChatByChatId2
	    });
	    Object.defineProperty(this, _getChat, {
	      value: _getChat2
	    });
	  }
	  getName() {
	    return 'counters';
	  }
	  getState() {
	    return {
	      unloadedChatCounters: {},
	      unloadedLinesCounters: {},
	      unloadedCopilotCounters: {},
	      unloadedCollabCounters: {},
	      commentCounters: {}
	    };
	  }

	  // eslint-disable-next-line max-lines-per-function
	  getGetters() {
	    return {
	      /** @function counters/getUnloadedChatCounters */
	      getUnloadedChatCounters: state => {
	        return state.unloadedChatCounters;
	      },
	      /** @function counters/getTotalChatCounter */
	      getTotalChatCounter: state => {
	        let loadedChatsCounter = 0;
	        const recentCollection = im_v2_application_core.Core.getStore().getters['recent/getRecentCollection'];
	        recentCollection.forEach(recentItem => {
	          const recentItemCounter = babelHelpers.classPrivateFieldLooseBase(this, _getRecentItemCounter)[_getRecentItemCounter](recentItem);
	          loadedChatsCounter += recentItemCounter;
	        });
	        let unloadedChatsCounter = 0;
	        Object.values(state.unloadedChatCounters).forEach(counter => {
	          unloadedChatsCounter += counter;
	        });
	        const channelCommentsCounter = im_v2_application_core.Core.getStore().getters['counters/getTotalCommentsCounter'];
	        return loadedChatsCounter + unloadedChatsCounter + channelCommentsCounter;
	      },
	      /** @function counters/getTotalCopilotCounter */
	      getTotalCopilotCounter: state => {
	        let loadedChatsCounter = 0;
	        const recentCollection = im_v2_application_core.Core.getStore().getters['recent/getCopilotCollection'];
	        recentCollection.forEach(recentItem => {
	          const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat](recentItem.dialogId);
	          if (babelHelpers.classPrivateFieldLooseBase(this, _isChatMuted)[_isChatMuted](chat)) {
	            return;
	          }
	          loadedChatsCounter += chat.counter;
	        });
	        let unloadedChatsCounter = 0;
	        Object.values(state.unloadedCopilotCounters).forEach(counter => {
	          unloadedChatsCounter += counter;
	        });
	        return loadedChatsCounter + unloadedChatsCounter;
	      },
	      /** @function counters/getTotalCollabCounter */
	      getTotalCollabCounter: state => {
	        let loadedChatsCounter = 0;
	        const recentCollection = im_v2_application_core.Core.getStore().getters['recent/getCollabCollection'];
	        recentCollection.forEach(recentItem => {
	          const recentItemCounter = babelHelpers.classPrivateFieldLooseBase(this, _getRecentItemCounter)[_getRecentItemCounter](recentItem);
	          loadedChatsCounter += recentItemCounter;
	        });
	        let unloadedChatsCounter = 0;
	        Object.values(state.unloadedCollabCounters).forEach(counter => {
	          unloadedChatsCounter += counter;
	        });
	        return loadedChatsCounter + unloadedChatsCounter;
	      },
	      /** @function counters/getTotalLinesCounter */
	      getTotalLinesCounter: state => {
	        let unloadedLinesCounter = 0;
	        Object.values(state.unloadedLinesCounters).forEach(counter => {
	          unloadedLinesCounter += counter;
	        });
	        return unloadedLinesCounter;
	      },
	      /** @function counters/getSpecificLinesCounter */
	      getSpecificLinesCounter: state => chatId => {
	        if (!state.unloadedLinesCounters[chatId]) {
	          return 0;
	        }
	        return state.unloadedLinesCounters[chatId];
	      },
	      /** @function counters/getTotalCommentsCounter */
	      getTotalCommentsCounter: state => {
	        let totalCounter = 0;
	        Object.entries(state.commentCounters).forEach(([channelChatId, channelCounters]) => {
	          const channel = babelHelpers.classPrivateFieldLooseBase(this, _getChatByChatId)[_getChatByChatId](channelChatId);
	          if (babelHelpers.classPrivateFieldLooseBase(this, _isChatMuted)[_isChatMuted](channel)) {
	            return;
	          }
	          Object.values(channelCounters).forEach(commentCounter => {
	            totalCounter += commentCounter;
	          });
	        });
	        return totalCounter;
	      },
	      /** @function counters/getChannelComments */
	      getChannelComments: state => chatId => {
	        if (!state.commentCounters[chatId]) {
	          return [];
	        }
	        return state.commentCounters[chatId];
	      },
	      /** @function counters/getChannelCommentsCounter */
	      getChannelCommentsCounter: state => chatId => {
	        if (!state.commentCounters[chatId]) {
	          return 0;
	        }
	        let result = 0;
	        Object.values(state.commentCounters[chatId]).forEach(counter => {
	          result += counter;
	        });
	        return result;
	      },
	      /** @function counters/getChatCounterByChatId */
	      getChatCounterByChatId: state => chatId => {
	        const recentCollection = im_v2_application_core.Core.getStore().getters['recent/getRecentCollection'];
	        const recentItem = recentCollection.find(element => {
	          const chat = this.store.getters['chats/get'](element.dialogId, true);
	          return chat.chatId === chatId;
	        });
	        if (!recentItem) {
	          var _state$unloadedChatCo;
	          return (_state$unloadedChatCo = state.unloadedChatCounters[chatId]) != null ? _state$unloadedChatCo : 0;
	        }
	        return babelHelpers.classPrivateFieldLooseBase(this, _getRecentItemCounter)[_getRecentItemCounter](recentItem);
	      },
	      /** @function counters/getSpecificCommentsCounter */
	      getSpecificCommentsCounter: state => payload => {
	        var _state$commentCounter;
	        const {
	          channelId,
	          commentChatId
	        } = payload;
	        if (!state.commentCounters[channelId]) {
	          return 0;
	        }
	        return (_state$commentCounter = state.commentCounters[channelId][commentChatId]) != null ? _state$commentCounter : 0;
	      }
	    };
	  }

	  /* eslint-disable no-param-reassign */
	  /* eslint-disable-next-line max-lines-per-function */
	  getActions() {
	    return {
	      /** @function counters/setUnloadedChatCounters */
	      setUnloadedChatCounters: (store, payload) => {
	        if (!main_core.Type.isPlainObject(payload)) {
	          return;
	        }
	        store.commit('setUnloadedChatCounters', payload);
	      },
	      /** @function counters/setUnloadedLinesCounters */
	      setUnloadedLinesCounters: (store, payload) => {
	        if (!main_core.Type.isPlainObject(payload)) {
	          return;
	        }
	        store.commit('setUnloadedLinesCounters', payload);
	      },
	      /** @function counters/setUnloadedCopilotCounters */
	      setUnloadedCopilotCounters: (store, payload) => {
	        if (!main_core.Type.isPlainObject(payload)) {
	          return;
	        }
	        store.commit('setUnloadedCopilotCounters', payload);
	      },
	      /** @function counters/setUnloadedCollabCounters */
	      setUnloadedCollabCounters: (store, payload) => {
	        if (!main_core.Type.isPlainObject(payload)) {
	          return;
	        }
	        store.commit('setUnloadedCollabCounters', payload);
	      },
	      /** @function counters/setCommentCounters */
	      setCommentCounters: (store, payload) => {
	        if (!main_core.Type.isPlainObject(payload)) {
	          return;
	        }
	        store.commit('setCommentCounters', payload);
	      },
	      /** @function counters/readAllChannelComments */
	      readAllChannelComments: (store, channelChatId) => {
	        if (!main_core.Type.isNumber(channelChatId)) {
	          return;
	        }
	        store.commit('readAllChannelComments', channelChatId);
	      },
	      /** @function counters/deleteForChannel */
	      deleteForChannel: (store, payload) => {
	        if (!main_core.Type.isPlainObject(payload)) {
	          return;
	        }
	        store.commit('deleteForChannel', payload);
	      }
	    };
	  }
	  getMutations() {
	    return {
	      setUnloadedChatCounters: (state, payload) => {
	        Object.entries(payload).forEach(([chatId, counter]) => {
	          if (counter === 0) {
	            delete state.unloadedChatCounters[chatId];
	            return;
	          }
	          state.unloadedChatCounters[chatId] = counter;
	        });
	      },
	      setUnloadedLinesCounters: (state, payload) => {
	        Object.entries(payload).forEach(([chatId, counter]) => {
	          if (counter === 0) {
	            delete state.unloadedLinesCounters[chatId];
	            return;
	          }
	          state.unloadedLinesCounters[chatId] = counter;
	        });
	      },
	      setUnloadedCopilotCounters: (state, payload) => {
	        Object.entries(payload).forEach(([chatId, counter]) => {
	          if (counter === 0) {
	            delete state.unloadedCopilotCounters[chatId];
	            return;
	          }
	          state.unloadedCopilotCounters[chatId] = counter;
	        });
	      },
	      setUnloadedCollabCounters: (state, payload) => {
	        Object.entries(payload).forEach(([chatId, counter]) => {
	          if (counter === 0) {
	            delete state.unloadedCollabCounters[chatId];
	            return;
	          }
	          state.unloadedCollabCounters[chatId] = counter;
	        });
	      },
	      setCommentCounters: (state, payload) => {
	        Object.entries(payload).forEach(([channelChatId, countersMap]) => {
	          if (!state.commentCounters[channelChatId]) {
	            state.commentCounters[channelChatId] = {};
	          }
	          const channelMap = state.commentCounters[channelChatId];
	          Object.entries(countersMap).forEach(([commentChatId, counter]) => {
	            if (counter === 0) {
	              delete channelMap[commentChatId];
	              return;
	            }
	            channelMap[commentChatId] = counter;
	          });
	        });
	      },
	      readAllChannelComments: (state, channelChatId) => {
	        delete state.commentCounters[channelChatId];
	      },
	      deleteForChannel: (state, payload) => {
	        const {
	          channelChatId,
	          commentChatId
	        } = payload;
	        if (!state.commentCounters[channelChatId]) {
	          return;
	        }
	        if (!commentChatId) {
	          delete state.commentCounters[channelChatId];
	          return;
	        }
	        delete state.commentCounters[channelChatId][commentChatId];
	      }
	    };
	  }
	}
	function _getChat2(dialogId) {
	  return im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true);
	}
	function _getChatByChatId2(chatId) {
	  return im_v2_application_core.Core.getStore().getters['chats/getByChatId'](chatId, true);
	}
	function _isChatMuted2(chat) {
	  return chat.muteList.includes(im_v2_application_core.Core.getUserId());
	}
	function _getRecentItemCounter2(recentItem) {
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat](recentItem.dialogId);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isChatMuted)[_isChatMuted](chat)) {
	    return 0;
	  }
	  const isMarked = recentItem.unread;
	  if (chat.counter === 0 && isMarked) {
	    return 1;
	  }
	  return chat.counter;
	}

	const copilotFieldsConfig = [{
	  fieldName: 'recommendedRoles',
	  targetFieldName: 'recommendedRoles',
	  checkFunction: main_core.Type.isArray
	}, {
	  fieldName: 'roles',
	  targetFieldName: 'roles',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: target => {
	    return Object.values(target).map(role => {
	      return formatFieldsWithConfig(role, rolesFieldsConfig);
	    });
	  }
	}];

	const chatFieldsConfig$1 = [{
	  fieldName: 'dialogId',
	  targetFieldName: 'dialogId',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'role',
	  targetFieldName: 'role',
	  checkFunction: main_core.Type.isString
	}];

	/* eslint-disable no-param-reassign */
	class ChatsModel$1 extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getElementState() {
	    return {
	      dialogId: '',
	      role: ''
	    };
	  }
	  getGetters() {
	    return {
	      /** @function copilot/chats/getRole */
	      getRole: state => dialogId => {
	        const chat = state.collection[dialogId];
	        if (!chat) {
	          return null;
	        }
	        return im_v2_application_core.Core.getStore().getters['copilot/roles/getByCode'](chat.role);
	      },
	      /** @function copilot/chats/getRoleAvatar */
	      getRoleAvatar: (state, getters) => dialogId => {
	        const role = getters.getRole(dialogId);
	        if (!role) {
	          return '';
	        }
	        return im_v2_application_core.Core.getStore().getters['copilot/roles/getAvatar'](role.code);
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function copilot/chats/add */
	      add: (store, payload) => {
	        if (!payload) {
	          return;
	        }
	        const chatsToAdd = main_core.Type.isArrayFilled(payload) ? payload : [payload];
	        chatsToAdd.forEach(chat => {
	          const preparedChat = {
	            ...this.getElementState(),
	            ...this.formatFields(chat)
	          };
	          store.commit('add', preparedChat);
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      add: (state, payload) => {
	        const {
	          dialogId
	        } = payload;
	        state.collection[dialogId] = payload;
	      }
	    };
	  }
	  formatFields(fields) {
	    return formatFieldsWithConfig(fields, chatFieldsConfig$1);
	  }
	}

	const messagesFieldsConfig = [{
	  fieldName: 'id',
	  targetFieldName: 'id',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'role',
	  targetFieldName: 'roleCode',
	  checkFunction: main_core.Type.isString
	}];

	/* eslint-disable no-param-reassign */
	class MessagesModel$1 extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      collection: {}
	    };
	  }
	  getElementState() {
	    return {
	      id: 0,
	      roleCode: ''
	    };
	  }
	  getGetters() {
	    return {
	      /** @function copilot/messages/getRole */
	      getRole: state => messageId => {
	        const message = state.collection[messageId];
	        if (!message) {
	          return im_v2_application_core.Core.getStore().getters['copilot/roles/getDefault'];
	        }
	        return im_v2_application_core.Core.getStore().getters['copilot/roles/getByCode'](message.roleCode);
	      },
	      /** @function copilot/messages/getPrompts */
	      getPrompts: state => messageId => {
	        const message = state.collection[messageId];
	        if (!message) {
	          return [];
	        }
	        return im_v2_application_core.Core.getStore().getters['copilot/roles/getPrompts'](message.roleCode);
	      },
	      getAvatar: (state, getters) => messageId => {
	        const role = getters.getRole(messageId);
	        if (!role) {
	          return '';
	        }
	        return im_v2_application_core.Core.getStore().getters['copilot/roles/getAvatar'](role.code);
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function copilot/messages/add */
	      add: (store, payload) => {
	        if (!main_core.Type.isArrayFilled(payload)) {
	          return;
	        }
	        payload.forEach(message => {
	          const preparedMessage = {
	            ...this.getElementState(),
	            ...this.formatFields(message)
	          };
	          store.commit('add', preparedMessage);
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      add: (state, payload) => {
	        state.collection[payload.id] = payload;
	      }
	    };
	  }
	  formatFields(fields) {
	    return formatFieldsWithConfig(fields, messagesFieldsConfig);
	  }
	}

	const rolesFieldsConfig$1 = [{
	  fieldName: 'avatar',
	  targetFieldName: 'avatar',
	  checkFunction: main_core.Type.isPlainObject,
	  formatFunction: target => {
	    return im_v2_model.formatFieldsWithConfig(target, avatarFieldsConfig);
	  }
	}, {
	  fieldName: 'code',
	  targetFieldName: 'code',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: ['desc', 'description'],
	  targetFieldName: 'desc',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'name',
	  targetFieldName: 'name',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'default',
	  targetFieldName: 'default',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'prompts',
	  targetFieldName: 'prompts',
	  checkFunction: main_core.Type.isArray,
	  formatFunction: target => {
	    return target.map(prompt => {
	      return im_v2_model.formatFieldsWithConfig(prompt, promptsFieldsConfig);
	    });
	  }
	}];
	const promptsFieldsConfig = [{
	  fieldName: 'code',
	  targetFieldName: 'code',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'promptType',
	  targetFieldName: 'promptType',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'text',
	  targetFieldName: 'text',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'title',
	  targetFieldName: 'title',
	  checkFunction: main_core.Type.isString
	}];
	const avatarFieldsConfig = [{
	  fieldName: 'small',
	  targetFieldName: 'small',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'medium',
	  targetFieldName: 'medium',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'large',
	  targetFieldName: 'large',
	  checkFunction: main_core.Type.isString
	}];

	const AvatarSizes = Object.freeze({
	  S: 'small',
	  M: 'medium',
	  L: 'large'
	});

	/* eslint-disable no-param-reassign */
	class RolesModel extends ui_vue3_vuex.BuilderModel {
	  getState() {
	    return {
	      roles: {}
	    };
	  }
	  getElementState() {
	    return {
	      code: '',
	      name: '',
	      desc: '',
	      default: false,
	      avatar: {
	        small: '',
	        medium: '',
	        large: ''
	      },
	      prompts: []
	    };
	  }
	  getGetters() {
	    return {
	      /** @function copilot/roles/get */
	      get: state => () => {
	        return Object.values(state.roles);
	      },
	      /** @function copilot/roles/getByCode */
	      getByCode: (state, getters) => code => {
	        var _state$roles$code;
	        return (_state$roles$code = state.roles[code]) != null ? _state$roles$code : getters.getDefault;
	      },
	      /** @function copilot/roles/getPrompts */
	      getPrompts: (state, getters) => roleCode => {
	        if (!state.roles[roleCode]) {
	          var _getters$getDefault$p, _getters$getDefault;
	          return (_getters$getDefault$p = (_getters$getDefault = getters.getDefault) == null ? void 0 : _getters$getDefault.prompts) != null ? _getters$getDefault$p : [];
	        }
	        return state.roles[roleCode].prompts;
	      },
	      /** @function copilot/roles/getDefault */
	      getDefault: state => {
	        return Object.values(state.roles).find(role => role.default);
	      },
	      /** @function copilot/roles/getAvatar */
	      getAvatar: (state, getters) => (roleCode, size = 'M') => {
	        if (!state.roles[roleCode]) {
	          var _getters$getDefault$p2, _getters$getDefault2;
	          return (_getters$getDefault$p2 = (_getters$getDefault2 = getters.getDefault) == null ? void 0 : _getters$getDefault2.prompts) != null ? _getters$getDefault$p2 : [];
	        }
	        return state.roles[roleCode].avatar[AvatarSizes[size]];
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function copilot/roles/add */
	      add: (store, payload) => {
	        const roles = Object.values(payload);
	        if (!main_core.Type.isArrayFilled(roles)) {
	          return;
	        }
	        roles.forEach(role => {
	          const preparedRole = {
	            ...this.getElementState(),
	            ...this.formatFields(role)
	          };
	          store.commit('add', preparedRole);
	        });
	      }
	    };
	  }
	  getMutations() {
	    return {
	      add: (state, payload) => {
	        state.roles[payload.code] = payload;
	      }
	    };
	  }
	  formatFields(fields) {
	    return formatFieldsWithConfig(fields, rolesFieldsConfig$1);
	  }
	}

	const RECOMMENDED_ROLES_LIMIT = 4;

	/* eslint-disable no-param-reassign */
	class CopilotModel extends ui_vue3_vuex.BuilderModel {
	  getNestedModules() {
	    return {
	      roles: RolesModel,
	      messages: MessagesModel$1,
	      chats: ChatsModel$1
	    };
	  }
	  getName() {
	    return 'copilot';
	  }
	  getState() {
	    return {
	      recommendedRoles: [],
	      aiProvider: ''
	    };
	  }
	  getGetters() {
	    return {
	      /** @function copilot/getProvider */
	      getProvider: state => {
	        return state.aiProvider;
	      },
	      /** @function copilot/getRecommendedRoles */
	      getRecommendedRoles: state => () => {
	        const roles = state.recommendedRoles.map(roleCode => {
	          return im_v2_application_core.Core.getStore().getters['copilot/roles/getByCode'](roleCode);
	        });
	        return roles.slice(0, RECOMMENDED_ROLES_LIMIT);
	      }
	    };
	  }
	  getActions() {
	    return {
	      /** @function copilot/setRecommendedRoles */
	      setRecommendedRoles: (store, payload) => {
	        if (!main_core.Type.isArrayFilled(payload)) {
	          return;
	        }
	        store.commit('setRecommendedRoles', payload);
	      },
	      /** @function copilot/setProvider */
	      setProvider: (store, payload) => {
	        if (!main_core.Type.isStringFilled(payload)) {
	          return;
	        }
	        store.commit('setProvider', payload);
	      }
	    };
	  }
	  getMutations() {
	    return {
	      setRecommendedRoles: (state, payload) => {
	        state.recommendedRoles = payload;
	      },
	      setProvider: (state, payload) => {
	        state.aiProvider = payload;
	      }
	    };
	  }
	  formatFields(fields) {
	    return im_v2_model.formatFieldsWithConfig(fields, copilotFieldsConfig);
	  }
	}

	exports.ApplicationModel = ApplicationModel;
	exports.MessagesModel = MessagesModel;
	exports.ChatsModel = ChatsModel;
	exports.UsersModel = UsersModel;
	exports.FilesModel = FilesModel;
	exports.RecentModel = RecentModel;
	exports.NotificationsModel = NotificationsModel;
	exports.SidebarModel = SidebarModel;
	exports.MarketModel = MarketModel;
	exports.CountersModel = CountersModel;
	exports.CopilotModel = CopilotModel;
	exports.formatFieldsWithConfig = formatFieldsWithConfig;
	exports.convertToNumber = convertToNumber;
	exports.convertToString = convertToString;
	exports.isNumberOrString = isNumberOrString;
	exports.convertObjectKeysToCamelCase = convertObjectKeysToCamelCase;
	exports.prepareDraft = prepareDraft;
	exports.prepareInvitation = prepareInvitation;

}((this.BX.Messenger.v2.Model = this.BX.Messenger.v2.Model || {}),BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Application,BX.Vue3.Vuex,BX,BX.Messenger.v2.Model));
//# sourceMappingURL=registry.bundle.js.map
