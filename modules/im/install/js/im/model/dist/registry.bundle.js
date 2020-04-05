this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,ui_vue,ui_vue_vuex,im_const) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Application model (Vuex Builder model)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */

	var ApplicationModel =
	/*#__PURE__*/
	function (_VuexBuilderModel) {
	  babelHelpers.inherits(ApplicationModel, _VuexBuilderModel);

	  function ApplicationModel() {
	    babelHelpers.classCallCheck(this, ApplicationModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ApplicationModel).apply(this, arguments));
	  }

	  babelHelpers.createClass(ApplicationModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'application';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
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
	          messageLimit: this.getVariable('dialog.messageLimit', 20)
	        },
	        disk: {
	          enabled: false,
	          maxFileSize: 5242880
	        },
	        device: {
	          type: this.getVariable('device.type', im_const.DeviceType.desktop),
	          orientation: this.getVariable('device.orientation', im_const.DeviceOrientation.portrait)
	        },
	        error: {
	          active: false,
	          code: '',
	          description: ''
	        }
	      };
	    }
	  }, {
	    key: "getStateSaveException",
	    value: function getStateSaveException() {
	      return {
	        common: null,
	        dialog: null,
	        device: null,
	        error: null
	      };
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      var _this = this;

	      return {
	        set: function set(store, payload) {
	          store.commit('set', _this.validate(payload));
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      var _this2 = this;

	      return {
	        set: function set(state, payload) {
	          var hasChange = false;

	          for (var group in payload) {
	            if (!payload.hasOwnProperty(group)) {
	              continue;
	            }

	            for (var field in payload[group]) {
	              if (!payload[group].hasOwnProperty(field)) {
	                continue;
	              }

	              state[group][field] = payload[group][field];
	              hasChange = true;
	            }
	          }

	          if (hasChange && _this2.isSaveNeeded(payload)) {
	            _this2.saveState(state);
	          }
	        }
	      };
	    }
	  }, {
	    key: "validate",
	    value: function validate(fields) {
	      var result = {};

	      if (babelHelpers.typeof(fields.common) === 'object' && fields.common) {
	        result.common = {};

	        if (typeof fields.common.userId === 'number') {
	          result.common.userId = fields.common.userId;
	        }

	        if (typeof fields.common.languageId === 'string') {
	          result.common.languageId = fields.common.languageId;
	        }
	      }

	      if (babelHelpers.typeof(fields.dialog) === 'object' && fields.dialog) {
	        result.dialog = {};

	        if (typeof fields.dialog.dialogId === 'number') {
	          result.dialog.dialogId = fields.dialog.dialogId.toString();
	          result.dialog.chatId = 0;
	        } else if (typeof fields.dialog.dialogId === 'string') {
	          result.dialog.dialogId = fields.dialog.dialogId;

	          if (typeof fields.dialog.chatId !== 'number') {
	            var chatId = fields.dialog.dialogId;

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
	      }

	      if (babelHelpers.typeof(fields.disk) === 'object' && fields.disk) {
	        result.disk = {};

	        if (typeof fields.disk.enabled === 'boolean') {
	          result.disk.enabled = fields.disk.enabled;
	        }

	        if (typeof fields.disk.maxFileSize === 'number') {
	          result.disk.maxFileSize = fields.disk.maxFileSize;
	        }
	      }

	      if (babelHelpers.typeof(fields.device) === 'object' && fields.device) {
	        result.device = {};

	        if (typeof fields.device.type === 'string' && typeof im_const.DeviceType[fields.device.type] !== 'undefined') {
	          result.device.type = fields.device.type;
	        }

	        if (typeof fields.device.orientation === 'string' && typeof im_const.DeviceOrientation[fields.device.orientation] !== 'undefined') {
	          result.device.orientation = fields.device.orientation;
	        }
	      }

	      if (babelHelpers.typeof(fields.error) === 'object' && fields.error) {
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
	  }]);
	  return ApplicationModel;
	}(ui_vue_vuex.VuexBuilderModel);

	/**
	 * Bitrix Messenger
	 * Message model (Vuex Builder model)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */

	var MessagesModel =
	/*#__PURE__*/
	function (_VuexBuilderModel) {
	  babelHelpers.inherits(MessagesModel, _VuexBuilderModel);

	  function MessagesModel() {
	    babelHelpers.classCallCheck(this, MessagesModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MessagesModel).apply(this, arguments));
	  }

	  babelHelpers.createClass(MessagesModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'messages';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return {
	        created: 0,
	        collection: {}
	      };
	    }
	  }, {
	    key: "getElementState",
	    value: function getElementState() {
	      return {
	        templateId: 0,
	        templateType: 'message',
	        id: 0,
	        chatId: 0,
	        authorId: 0,
	        date: new Date(),
	        text: "",
	        textConverted: "",
	        params: {
	          TYPE: 'default',
	          COMPONENT_ID: 'bx-messenger-message'
	        },
	        unread: false,
	        sending: false,
	        error: false,
	        blink: false
	      };
	    }
	  }, {
	    key: "getGetters",
	    value: function getGetters() {
	      var _this = this;

	      return {
	        getLastId: function getLastId(state) {
	          return function (chatId) {
	            if (!state.collection[chatId] || state.collection[chatId].length <= 0) {
	              return null;
	            }

	            for (var index = state.collection[chatId].length - 1; index >= 0; index--) {
	              var element = state.collection[chatId][index];
	              if (element.sending) continue;
	              return element.id;
	            }

	            return null;
	          };
	        },
	        get: function get(state) {
	          return function (chatId) {
	            if (!state.collection[chatId] || state.collection[chatId].length <= 0) {
	              return [];
	            }

	            return state.collection[chatId];
	          };
	        },
	        getBlank: function getBlank(state) {
	          return function (params) {
	            return _this.getElementState();
	          };
	        }
	      };
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      var _this2 = this;

	      return {
	        add: function add(store, payload) {
	          var result = _this2.validate(Object.assign({}, payload));

	          result.params = Object.assign({}, _this2.getElementState().params, result.params);
	          result.id = 'temporary' + store.state.created;
	          result.templateId = result.id;
	          result.unread = false;
	          store.commit('add', Object.assign({}, _this2.getElementState(), result));
	          store.dispatch('actionStart', {
	            id: result.id,
	            chatId: result.chatId
	          });
	          return result.id;
	        },
	        actionStart: function actionStart(store, payload) {
	          ui_vue.Vue.nextTick(function () {
	            store.commit('update', {
	              id: payload.id,
	              chatId: payload.chatId,
	              fields: {
	                sending: true
	              }
	            });
	          });
	        },
	        actionError: function actionError(store, payload) {
	          ui_vue.Vue.nextTick(function () {
	            store.commit('update', {
	              id: payload.id,
	              chatId: payload.chatId,
	              fields: {
	                sending: false,
	                error: true
	              }
	            });
	          });
	        },
	        actionFinish: function actionFinish(store, payload) {
	          ui_vue.Vue.nextTick(function () {
	            store.commit('update', {
	              id: payload.id,
	              chatId: payload.chatId,
	              fields: {
	                sending: false,
	                error: false
	              }
	            });
	          });
	        },
	        set: function set(store, payload) {
	          if (payload instanceof Array) {
	            payload = payload.map(function (message) {
	              var result = _this2.validate(Object.assign({}, message));

	              result.params = Object.assign({}, _this2.getElementState().params, result.params);
	              result.templateId = result.id;
	              return Object.assign({}, _this2.getElementState(), result);
	            });
	          } else {
	            var result = _this2.validate(Object.assign({}, payload));

	            result.params = Object.assign({}, _this2.getElementState().params, result.params);
	            result.templateId = result.id;
	            payload = [];
	            payload.push(Object.assign({}, _this2.getElementState(), result));
	          }

	          store.commit('set', {
	            insertType: im_const.InsertType.after,
	            data: payload
	          });
	        },
	        setBefore: function setBefore(store, payload) {
	          if (payload instanceof Array) {
	            payload = payload.map(function (message) {
	              var result = _this2.validate(Object.assign({}, message));

	              result.params = Object.assign({}, _this2.getElementState().params, result.params);
	              result.templateId = result.id;
	              return Object.assign({}, _this2.getElementState(), result);
	            });
	          } else {
	            var result = _this2.validate(Object.assign({}, payload));

	            result.params = Object.assign({}, _this2.getElementState().params, result.params);
	            result.templateId = result.id;
	            payload = [];
	            payload.push(Object.assign({}, _this2.getElementState(), result));
	          }

	          store.commit('set', {
	            actionName: 'setBefore',
	            insertType: im_const.InsertType.before,
	            data: payload
	          });
	        },
	        update: function update(store, payload) {
	          var result = _this2.validate(Object.assign({}, payload.fields));

	          if (typeof store.state.collection[payload.chatId] === 'undefined') {
	            ui_vue.Vue.set(store.state.collection, payload.chatId, []);
	          }

	          var index = store.state.collection[payload.chatId].findIndex(function (el) {
	            return el.id == payload.id;
	          });

	          if (index < 0) {
	            return false;
	          }

	          if (payload.fields.params) {
	            result.params = Object.assign({}, _this2.getElementState().params, store.state.collection[payload.chatId][index].params, payload.fields.params);
	          }

	          store.commit('update', {
	            id: payload.id,
	            chatId: payload.chatId,
	            index: index,
	            fields: result
	          });

	          if (payload.fields.blink) {
	            setTimeout(function () {
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
	        delete: function _delete(store, payload) {
	          store.commit('delete', {
	            id: payload.id,
	            chatId: payload.chatId
	          });
	          return true;
	        },
	        readMessages: function readMessages(store, payload) {
	          payload.readId = payload.readId || 0;

	          if (typeof store.state.collection[payload.chatId] === 'undefined') {
	            return {
	              count: 0
	            };
	          }

	          var count = 0;

	          for (var index = store.state.collection[payload.chatId].length - 1; index >= 0; index--) {
	            var element = store.state.collection[payload.chatId][index];
	            if (!element.unread) continue;

	            if (payload.readId === 0 || element.id <= payload.readId) {
	              count++;
	            }
	          }

	          var result = store.commit('readMessages', {
	            chatId: payload.chatId,
	            readId: payload.readId
	          });
	          return {
	            count: count
	          };
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      return {
	        initCollection: function initCollection(state, payload) {
	          if (typeof state.collection[payload.chatId] === 'undefined') {
	            ui_vue.Vue.set(state.collection, payload.chatId, payload.messages ? [].concat(payload.messages) : []);
	          }
	        },
	        add: function add(state, payload) {
	          if (typeof state.collection[payload.chatId] === 'undefined') {
	            ui_vue.Vue.set(state.collection, payload.chatId, []);
	          }

	          state.collection[payload.chatId].push(payload);
	          state.created += 1;
	        },
	        set: function set(state, payload) {
	          if (payload.insertType == im_const.InsertType.after) {
	            var _iteratorNormalCompletion = true;
	            var _didIteratorError = false;
	            var _iteratorError = undefined;

	            try {
	              var _loop = function _loop() {
	                var element = _step.value;

	                if (typeof state.collection[element.chatId] === 'undefined') {
	                  ui_vue.Vue.set(state.collection, element.chatId, []);
	                }

	                var index = state.collection[element.chatId].findIndex(function (el) {
	                  return el.id === element.id;
	                });

	                if (index > -1) {
	                  state.collection[element.chatId][index] = Object.assign(state.collection[element.chatId][index], element);
	                } else {
	                  state.collection[element.chatId].push(element);
	                }
	              };

	              for (var _iterator = payload.data[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
	                _loop();
	              }
	            } catch (err) {
	              _didIteratorError = true;
	              _iteratorError = err;
	            } finally {
	              try {
	                if (!_iteratorNormalCompletion && _iterator.return != null) {
	                  _iterator.return();
	                }
	              } finally {
	                if (_didIteratorError) {
	                  throw _iteratorError;
	                }
	              }
	            }
	          } else {
	            var _iteratorNormalCompletion2 = true;
	            var _didIteratorError2 = false;
	            var _iteratorError2 = undefined;

	            try {
	              var _loop2 = function _loop2() {
	                var element = _step2.value;

	                if (typeof state.collection[element.chatId] === 'undefined') {
	                  ui_vue.Vue.set(state.collection, element.chatId, []);
	                }

	                var index = state.collection[element.chatId].findIndex(function (el) {
	                  return el.id === element.id;
	                });

	                if (index > -1) {
	                  state.collection[element.chatId][index] = Object.assign(state.collection[element.chatId][index], element);
	                } else {
	                  state.collection[element.chatId].unshift(element);
	                }
	              };

	              for (var _iterator2 = payload.data[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
	                _loop2();
	              }
	            } catch (err) {
	              _didIteratorError2 = true;
	              _iteratorError2 = err;
	            } finally {
	              try {
	                if (!_iteratorNormalCompletion2 && _iterator2.return != null) {
	                  _iterator2.return();
	                }
	              } finally {
	                if (_didIteratorError2) {
	                  throw _iteratorError2;
	                }
	              }
	            }
	          }
	        },
	        update: function update(state, payload) {
	          if (typeof state.collection[payload.chatId] === 'undefined') {
	            ui_vue.Vue.set(state.collection, payload.chatId, []);
	          }

	          var index = -1;

	          if (typeof payload.index !== 'undefined' && state.collection[payload.chatId][payload.index]) {
	            index = payload.index;
	          } else {
	            index = state.collection[payload.chatId].findIndex(function (el) {
	              return el.id == payload.id;
	            });
	          }

	          if (index >= 0) {
	            state.collection[payload.chatId][index] = Object.assign(state.collection[payload.chatId][index], payload.fields);
	          }
	        },
	        delete: function _delete(state, payload) {
	          if (typeof state.collection[payload.chatId] === 'undefined') {
	            ui_vue.Vue.set(state.collection, payload.chatId, []);
	          }

	          state.collection[payload.chatId] = state.collection[payload.chatId].filter(function (element) {
	            return element.id != payload.id;
	          });
	        },
	        readMessages: function readMessages(state, payload) {
	          if (typeof state.collection[payload.chatId] === 'undefined') {
	            ui_vue.Vue.set(state.collection, payload.chatId, []);
	          }

	          for (var index = state.collection[payload.chatId].length - 1; index >= 0; index--) {
	            var element = state.collection[payload.chatId][index];
	            if (!element.unread) continue;

	            if (payload.readId === 0 || element.id <= payload.readId) {
	              state.collection[payload.chatId][index] = Object.assign(state.collection[payload.chatId][index], {
	                unread: false
	              });
	            }
	          }
	        }
	      };
	    }
	  }, {
	    key: "validate",
	    value: function validate(fields) {
	      var result = {};

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

	      if (typeof fields.chat_id !== 'undefined') {
	        fields.chatId = fields.chat_id;
	      }

	      if (typeof fields.chatId === "number" || typeof fields.chatId === "string") {
	        result.chatId = parseInt(fields.chatId);
	      }

	      if (fields.date instanceof Date) {
	        result.date = fields.date;
	      } else if (typeof fields.date === "string") {
	        result.date = new Date(fields.date);
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
	            var isConverted = typeof result.textConverted !== 'undefined';
	            result.textConverted = this.convertToHtml({
	              text: isConverted ? result.textConverted : result.text,
	              isConverted: isConverted
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

	      if (babelHelpers.typeof(fields.params) === "object" && fields.params !== null) {
	        var params = this.validateParams(fields.params);

	        if (params) {
	          result.params = params;
	        }
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

	      return result;
	    }
	  }, {
	    key: "validateParams",
	    value: function validateParams(params) {
	      var result = {};

	      try {
	        for (var field in params) {
	          if (!params.hasOwnProperty(field)) {
	            continue;
	          }

	          if (field === 'COMPONENT_ID') {
	            if (typeof params[field] === "string" && BX.Vue.isComponent(params[field])) {
	              result[field] = params[field];
	            }
	          } else {
	            result[field] = params[field];
	          }
	        }
	      } catch (e) {}

	      var hasResultElements = false;

	      for (var _field in result) {
	        if (!result.hasOwnProperty(_field)) {
	          continue;
	        }

	        hasResultElements = true;
	        break;
	      }

	      return hasResultElements ? result : null;
	    }
	  }, {
	    key: "convertToHtml",
	    value: function convertToHtml() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var _params$quote = params.quote,
	          quote = _params$quote === void 0 ? true : _params$quote,
	          _params$image = params.image,
	          image = _params$image === void 0 ? true : _params$image,
	          _params$text = params.text,
	          text = _params$text === void 0 ? '' : _params$text,
	          _params$highlightText = params.highlightText,
	          highlightText = _params$highlightText === void 0 ? '' : _params$highlightText,
	          _params$isConverted = params.isConverted,
	          isConverted = _params$isConverted === void 0 ? false : _params$isConverted,
	          _params$enableBigSmil = params.enableBigSmile,
	          enableBigSmile = _params$enableBigSmil === void 0 ? true : _params$enableBigSmil;
	      text = text.trim();

	      if (!isConverted) {
	        text = text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	      }

	      if (text.startsWith('/me')) {
	        text = "<i>".concat(text.substr(4), "</i>");
	      } else if (text.startsWith('/loud')) {
	        text = "<b>".concat(text.substr(6), "</b>");
	      }

	      var quoteSign = "&gt;&gt;";

	      if (quote && text.indexOf(quoteSign) >= 0) {
	        var textPrepare = text.split(isConverted ? "<br />" : "\n");

	        for (var i = 0; i < textPrepare.length; i++) {
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

	      text = this.decodeBbCode(text, false, enableBigSmile);
	      text = text.replace(/\n/gi, '<br />');
	      text = text.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;&nbsp;');

	      if (quote) {
	        text = text.replace(/------------------------------------------------------<br \/>(.*?)\[(.*?)\]<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function (whole, p1, p2, p3, p4, offset) {
	          return (offset > 0 ? '<br>' : '') + "<div class=\"bx-im-message-content-quote\"><div class=\"bx-im-message-content-quote-wrap\"><div class=\"bx-im-message-content-quote-name\">" + p1 + " <span class=\"bx-im-message-content-quote-time\">" + p2 + "</span></div>" + p3 + "</div></div><br />";
	        });
	        text = text.replace(/------------------------------------------------------<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function (whole, p1, p2, p3, offset) {
	          return (offset > 0 ? '<br>' : '') + "<div class=\"bx-im-message-content-quote\"><div class=\"bx-im-message-content-quote-wrap\">" + p1 + "</div></div><br />";
	        });
	      }

	      if (image) {
	        var changed = false;
	        text = text.replace(/<a(.*?)>(http[s]{0,1}:\/\/.*?)<\/a>/ig, function (whole, aInner, text, offset) {
	          if (!text.match(/(\.(jpg|jpeg|png|gif)\?|\.(jpg|jpeg|png|gif)$)/i) || text.indexOf("/docs/pub/") > 0 || text.indexOf("logout=yes") > 0) {
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

	      if (highlightText) {
	        text = text.replace(new RegExp("(" + highlightText.replace(/[\-\[\]\/{}()*+?.\\^$|]/g, "\\$&") + ")", 'ig'), '<span class="bx-messenger-highlight">$1</span>');
	      }

	      if (enableBigSmile) {
	        text = text.replace(/^(\s*<img\s+src=[^>]+?data-code=[^>]+?data-definition="UHD"[^>]+?style="width:)(\d+)(px[^>]+?height:)(\d+)(px[^>]+?class="bx-smile"\s*\/?>\s*)$/, function doubleSmileSize(match, start, width, middle, height, end) {
	          return start + parseInt(width, 10) * 2 + middle + parseInt(height, 10) * 2 + end;
	        });
	      }

	      if (text.substr(-6) == '<br />') {
	        text = text.substr(0, text.length - 6);
	      }

	      text = text.replace(/<br><br \/>/ig, '<br />');
	      text = text.replace(/<br \/><br>/ig, '<br />');
	      return text;
	    }
	  }, {
	    key: "decodeBbCode",
	    value: function decodeBbCode(textElement) {
	      var textOnly = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var enableBigSmile = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
	      var codeReplacement = [];
	      textElement = textElement.replace(/\[CODE\]\n?([\0-\uFFFF]*?)\[\/CODE\]/ig, function (whole, text) {
	        var id = codeReplacement.length;
	        codeReplacement.push(text);
	        return '####REPLACEMENT_MARK_' + id + '####';
	      });
	      textElement = textElement.replace(/\[LIKE\]/ig, '<span class="bx-smile bx-im-smile-like"></span>');
	      textElement = textElement.replace(/\[DISLIKE\]/ig, '<span class="bx-smile bx-im-smile-dislike"></span>');
	      textElement = textElement.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/ig, function (whole, userId, text) {
	        return text;
	      });
	      textElement = textElement.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/ig, function (whole, openlines, chatId, text) {
	        return text;
	      });
	      textElement = textElement.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/ig, function (whole, historyId, text) {
	        return text;
	      });
	      textElement = textElement.replace(/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/ig, function (whole, command, text) {
	        var html = '';
	        text = text ? text : command;
	        command = command ? command : text;

	        if (!textOnly && text) {
	          text = text.replace(/<([\w]+)[^>]*>(.*?)<\\1>/i, "$2", text);
	          text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);
	          html = '<span class="bx-im-message-command" data-entity="send">' + text + '</span>';
	          html += '<span class="bx-im-message-command-data">' + command + '</span>';
	        } else {
	          html = text;
	        }

	        return html;
	      });
	      textElement = textElement.replace(/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/ig, function (whole, command, text) {
	        var html = '';
	        text = text ? text : command;
	        command = command ? command : text;

	        if (!textOnly && text) {
	          text = text.replace(/<([\w]+)[^>]*>(.*?)<\/\1>/i, "$2", text);
	          text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);
	          html = '<span class="bx-im-message-command" data-entity="put" v-on:click="alert(1)">' + text + '</span>';
	          html += '<span class="bx-im-message-command-data">' + command + '</span>';
	        } else {
	          html = text;
	        }

	        return html;
	      });
	      textElement = textElement.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/ig, function (whole, command, text) {
	        return text;
	      });
	      var textElementSize = 0;

	      if (enableBigSmile) {
	        textElementSize = textElement.replace(/\[icon\=([^\]]*)\]/ig, '').trim().length;
	      }

	      textElement = textElement.replace(/\[icon\=([^\]]*)\]/ig, function (whole) {
	        var url = whole.match(/icon\=(\S+[^\s.,> )\];\'\"!?])/i);

	        if (url && url[1]) {
	          url = url[1];
	        } else {
	          return '';
	        }

	        var attrs = {
	          'src': url,
	          'border': 0
	        };
	        var size = whole.match(/size\=(\d+)/i);

	        if (size && size[1]) {
	          attrs['width'] = size[1];
	          attrs['height'] = size[1];
	        } else {
	          var width = whole.match(/width\=(\d+)/i);

	          if (width && width[1]) {
	            attrs['width'] = width[1];
	          }

	          var height = whole.match(/height\=(\d+)/i);

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

	        if (enableBigSmile && textElementSize == 0 && attrs['width'] == attrs['height'] && attrs['width'] == 20) {
	          attrs['width'] = 40;
	          attrs['height'] = 40;
	        }

	        var title = whole.match(/title\=(.*[^\s\]])/i);

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
	            attrs['title'] = BX.Messenger.Utils.htmlspecialchars(title).trim();
	            attrs['alt'] = BX.Messenger.Utils.htmlspecialchars(title).trim();
	          }
	        }

	        var attributes = '';

	        for (var name in attrs) {
	          if (attrs.hasOwnProperty(name)) {
	            attributes += name + '="' + attrs[name] + '" ';
	          }
	        }

	        return '<img class="bx-smile bx-icon" ' + attributes + '>';
	      });
	      codeReplacement.forEach(function (code, index) {
	        textElement = textElement.replace('####REPLACEMENT_MARK_' + index + '####', !textOnly ? '<div class="bx-im-message-content-code">' + code + '</div>' : code);
	      });
	      return textElement;
	    }
	  }], [{
	    key: "hideErrorImage",
	    value: function hideErrorImage(element) {
	      if (element.parentNode && element.parentNode) {
	        element.parentNode.innerHTML = '<a href="' + element.src + '" target="_blank">' + element.src + '</a>';
	      }

	      return true;
	    }
	  }]);
	  return MessagesModel;
	}(ui_vue_vuex.VuexBuilderModel);

	/**
	 * Bitrix Messenger
	 * Message model (Vuex Builder model)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */

	var DialoguesModel =
	/*#__PURE__*/
	function (_VuexBuilderModel) {
	  babelHelpers.inherits(DialoguesModel, _VuexBuilderModel);

	  function DialoguesModel() {
	    babelHelpers.classCallCheck(this, DialoguesModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DialoguesModel).apply(this, arguments));
	  }

	  babelHelpers.createClass(DialoguesModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'dialogues';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return {
	        host: this.getVariable('host', location.protocol + '//' + location.host),
	        collection: {}
	      };
	    }
	  }, {
	    key: "getElementState",
	    value: function getElementState() {
	      return {
	        dialogId: 0,
	        chatId: 0,
	        counter: 0,
	        unreadId: 0,
	        unreadLastId: 0,
	        readedList: [],
	        writingList: [],
	        init: false,
	        name: "",
	        owner: 0,
	        extranet: false,
	        avatar: "",
	        color: "#17A3EA",
	        type: "chat",
	        entityType: "",
	        entityId: "",
	        entityData1: "",
	        entityData2: "",
	        entityData3: "",
	        dateCreate: new Date()
	      };
	    }
	  }, {
	    key: "getGetters",
	    value: function getGetters() {
	      var _this = this;

	      return {
	        get: function get(state) {
	          return function (dialogId) {
	            if (!state.collection[dialogId] || state.collection[dialogId].length <= 0) {
	              return null;
	            }

	            return state.collection[dialogId];
	          };
	        },
	        getBlank: function getBlank(state) {
	          return function (params) {
	            return _this.getElementState();
	          };
	        }
	      };
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      var _this2 = this;

	      return {
	        set: function set(store, payload) {
	          if (payload instanceof Array) {
	            payload = payload.map(function (dialog) {
	              return Object.assign({}, _this2.getElementState(), _this2.validate(Object.assign({}, dialog), {
	                host: store.state.host
	              }), {
	                init: true
	              });
	            });
	          } else {
	            var result = [];
	            result.push(Object.assign({}, _this2.getElementState(), _this2.validate(Object.assign({}, payload), {
	              host: store.state.host
	            }), {
	              init: true
	            }));
	            payload = result;
	          }

	          store.commit('set', payload);
	        },
	        update: function update(store, payload) {
	          if (typeof store.state.collection[payload.dialogId] === 'undefined' || store.state.collection[payload.dialogId].init === false) {
	            return true;
	          }

	          store.commit('update', {
	            dialogId: payload.dialogId,
	            fields: _this2.validate(Object.assign({}, payload.fields), {
	              host: store.state.host
	            })
	          });
	          return true;
	        },
	        delete: function _delete(store, payload) {
	          store.commit('delete', payload.dialogId);
	          return true;
	        },
	        updateWriting: function updateWriting(store, payload) {
	          if (typeof store.state.collection[payload.dialogId] === 'undefined' || store.state.collection[payload.dialogId].init === false) {
	            return true;
	          }

	          var index = store.state.collection[payload.dialogId].writingList.findIndex(function (el) {
	            return el.userId == payload.userId;
	          });

	          if (payload.action) {
	            if (index >= 0) {
	              return true;
	            } else {
	              var writingList = [].concat(store.state.collection[payload.dialogId].writingList);
	              writingList.unshift({
	                userId: payload.userId,
	                userName: payload.userName
	              });
	              store.commit('update', {
	                actionName: 'updateWriting/1',
	                dialogId: payload.dialogId,
	                fields: {
	                  writingList: writingList
	                }
	              });
	            }
	          } else {
	            if (index >= 0) {
	              var _writingList = store.state.collection[payload.dialogId].writingList.filter(function (el) {
	                return el.userId != payload.userId;
	              });

	              store.commit('update', {
	                actionName: 'updateWriting/2',
	                dialogId: payload.dialogId,
	                fields: {
	                  writingList: _writingList
	                }
	              });
	              return true;
	            } else {
	              return true;
	            }
	          }

	          return false;
	        },
	        increaseCounter: function increaseCounter(store, payload) {
	          if (typeof store.state.collection[payload.dialogId] === 'undefined' || store.state.collection[payload.dialogId].init === false) {
	            return true;
	          }

	          var counter = store.state.collection[payload.dialogId].counter;
	          var increasedCounter = counter + payload.count;
	          var fields = {
	            counter: increasedCounter
	          };

	          if (typeof payload.unreadLastId !== 'undefined') {
	            fields.unreadLastId = payload.unreadLastId;
	          }

	          store.commit('update', {
	            actionName: 'increaseCounter',
	            dialogId: payload.dialogId,
	            fields: fields
	          });
	          return false;
	        },
	        decreaseCounter: function decreaseCounter(store, payload) {
	          if (typeof store.state.collection[payload.dialogId] === 'undefined' || store.state.collection[payload.dialogId].init === false) {
	            return true;
	          }

	          var counter = store.state.collection[payload.dialogId].counter;
	          var decreasedCounter = counter - payload.count;

	          if (decreasedCounter < 0) {
	            decreasedCounter = 0;
	          }

	          var unreadId = payload.unreadId > store.state.collection[payload.dialogId].unreadId ? payload.unreadId : store.state.collection[payload.dialogId].unreadId;
	          store.commit('update', {
	            actionName: 'decreaseCounter',
	            dialogId: payload.dialogId,
	            fields: {
	              counter: decreasedCounter,
	              unreadId: unreadId
	            }
	          });
	          return false;
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      var _this3 = this;

	      return {
	        initCollection: function initCollection(state, payload) {
	          if (typeof state.collection[payload.dialogId] === 'undefined') {
	            ui_vue.Vue.set(state.collection, payload.dialogId, _this3.getElementState());

	            if (payload.fields) {
	              state.collection[payload.dialogId] = Object.assign(state.collection[payload.dialogId], _this3.validate(Object.assign({}, payload.fields), {
	                host: state.host
	              }));
	            }
	          }
	        },
	        set: function set(state, payload) {
	          var _iteratorNormalCompletion = true;
	          var _didIteratorError = false;
	          var _iteratorError = undefined;

	          try {
	            for (var _iterator = payload[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
	              var element = _step.value;

	              if (typeof state.collection[element.dialogId] === 'undefined') {
	                ui_vue.Vue.set(state.collection, element.dialogId, element);
	              }

	              state.collection[element.dialogId] = element;
	            }
	          } catch (err) {
	            _didIteratorError = true;
	            _iteratorError = err;
	          } finally {
	            try {
	              if (!_iteratorNormalCompletion && _iterator.return != null) {
	                _iterator.return();
	              }
	            } finally {
	              if (_didIteratorError) {
	                throw _iteratorError;
	              }
	            }
	          }
	        },
	        update: function update(state, payload) {
	          if (typeof state.collection[payload.dialogId] === 'undefined') {
	            ui_vue.Vue.set(state.collection, payload.dialogId, _this3.getElementState());
	          }

	          state.collection[payload.dialogId] = Object.assign(state.collection[payload.dialogId], payload.fields);
	        },
	        delete: function _delete(state, payload) {
	          delete state.collection[payload.dialogId];
	        }
	      };
	    }
	  }, {
	    key: "validate",
	    value: function validate(fields) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var result = {};
	      options.host = options.host || this.getState().host;

	      if (typeof fields.dialog_id !== 'undefined') {
	        fields.dialogId = fields.dialog_id;
	      }

	      if (typeof fields.dialogId === "number" || typeof fields.dialogId === "string") {
	        result.dialogId = fields.dialogId.toString();
	      }

	      if (typeof fields.chat_id !== 'undefined') {
	        fields.chatId = fields.chat_id;
	      } else if (typeof fields.id !== 'undefined') {
	        fields.chatId = fields.id;
	      }

	      if (typeof fields.chatId === "number" || typeof fields.chatId === "string") {
	        result.chatId = parseInt(fields.chatId);
	      }

	      if (typeof fields.counter === "number" || typeof fields.counter === "string") {
	        result.counter = parseInt(fields.counter);
	      }

	      if (typeof fields.unread_id !== 'undefined') {
	        fields.unreadId = fields.unread_id;
	      }

	      if (typeof fields.unreadId === "number" || typeof fields.unreadId === "string") {
	        result.unreadId = parseInt(fields.unreadId);
	      }

	      if (typeof fields.unread_last_id !== 'undefined') {
	        fields.unreadLastId = fields.unread_last_id;
	      }

	      if (typeof fields.unreadLastId === "number" || typeof fields.unreadLastId === "string") {
	        result.unreadLastId = parseInt(fields.unreadLastId);
	      }

	      if (typeof fields.readed_list !== 'undefined') {
	        fields.readedList = fields.readed_list;
	      }

	      if (typeof fields.readedList !== 'undefined') {
	        result.readedList = [];

	        if (fields.readedList instanceof Array) {
	          fields.readedList.forEach(function (element) {
	            var record = {};

	            if (typeof element.user_id !== 'undefined') {
	              element.userId = element.user_id;
	            }

	            if (typeof element.user_name !== 'undefined') {
	              element.userName = element.user_name;
	            }

	            if (typeof element.message_id !== 'undefined') {
	              element.messageId = element.message_id;
	            }

	            if (!element.userId || !element.userName || !element.messageId) {
	              return false;
	            }

	            record.userId = parseInt(element.userId);
	            record.userName = element.userName.toString();
	            record.messageId = parseInt(element.messageId);

	            if (fields.date instanceof Date) {
	              record.date = fields.date;
	            } else if (typeof fields.date === "string") {
	              record.date = new Date(fields.date);
	            } else {
	              record.date = new Date();
	            }

	            result.readedList.push(record);
	          });
	        }
	      }

	      if (typeof fields.writing_list !== 'undefined') {
	        fields.writingList = fields.writing_list;
	      }

	      if (typeof fields.writingList !== 'undefined') {
	        result.writingList = [];

	        if (fields.writingList instanceof Array) {
	          fields.writingList.forEach(function (element) {
	            var record = {};

	            if (!element.userId) {
	              return false;
	            }

	            record.userId = parseInt(element.userId);
	            record.userName = element.userName;
	            result.writingList.push(record);
	          });
	        }
	      }

	      if (typeof fields.mute_list !== 'undefined') {
	        fields.muteList = fields.mute_list;
	      }

	      if (typeof fields.muteList !== 'undefined') {
	        result.muteList = [];

	        if (fields.muteList instanceof Array) {
	          fields.muteList.forEach(function (userId) {
	            userId = parseInt(userId);

	            if (userId > 0) {
	              result.muteList.push(userId);
	            }
	          });
	        }
	      }

	      if (typeof fields.title !== 'undefined') {
	        fields.name = fields.title;
	      }

	      if (typeof fields.name === "string" || typeof fields.name === "number") {
	        result.name = fields.name.toString();
	      }

	      if (typeof fields.owner !== 'undefined') {
	        fields.ownerId = fields.owner;
	      }

	      if (typeof fields.ownerId === "number" || typeof fields.ownerId === "string") {
	        result.ownerId = parseInt(fields.ownerId);
	      }

	      if (typeof fields.extranet === "boolean") {
	        result.extranet = fields.extranet;
	      }

	      if (typeof fields.avatar === 'string') {
	        if (!fields.avatar || fields.avatar.startsWith('http')) {
	          result.avatar = fields.avatar;
	        } else {
	          result.avatar = options.host + fields.avatar;
	        }
	      }

	      if (typeof fields.color === "string") {
	        result.color = fields.color.toString();
	      }

	      if (typeof fields.type === "string") {
	        result.type = fields.type.toString();
	      }

	      if (typeof fields.entity_type !== 'undefined') {
	        fields.entityType = fields.entity_type;
	      }

	      if (typeof fields.entityType === "string") {
	        result.entityType = fields.entityType.toString();
	      }

	      if (typeof fields.entity_id !== 'undefined') {
	        fields.entityId = fields.entity_id;
	      }

	      if (typeof fields.entityId === "string" || typeof fields.entityId === "number") {
	        result.entityId = fields.entityId.toString();
	      }

	      if (typeof fields.entity_data_1 !== 'undefined') {
	        fields.entityData1 = fields.entity_data_1;
	      }

	      if (typeof fields.entityData1 === "string") {
	        result.entityData1 = fields.entityData1.toString();
	      }

	      if (typeof fields.entity_data_2 !== 'undefined') {
	        fields.entityData2 = fields.entity_data_2;
	      }

	      if (typeof fields.entityData2 === "string") {
	        result.entityData2 = fields.entityData2.toString();
	      }

	      if (typeof fields.entity_data_3 !== 'undefined') {
	        fields.entityData3 = fields.entity_data_3;
	      }

	      if (typeof fields.entityData3 === "string") {
	        result.entityData3 = fields.entityData3.toString();
	      }

	      if (typeof fields.date_create !== 'undefined') {
	        fields.dateCreate = fields.date_create;
	      }

	      if (fields.dateCreate instanceof Date) {
	        result.dateCreate = fields.dateCreate;
	      } else if (typeof fields.dateCreate === "string") {
	        result.dateCreate = new Date(fields.dateCreate);
	      }

	      return result;
	    }
	  }]);
	  return DialoguesModel;
	}(ui_vue_vuex.VuexBuilderModel);

	/**
	 * Bitrix Messenger
	 * User model (Vuex Builder model)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */

	var UsersModel =
	/*#__PURE__*/
	function (_VuexBuilderModel) {
	  babelHelpers.inherits(UsersModel, _VuexBuilderModel);

	  function UsersModel() {
	    babelHelpers.classCallCheck(this, UsersModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UsersModel).apply(this, arguments));
	  }

	  babelHelpers.createClass(UsersModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'users';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return {
	        host: this.getVariable('host', location.protocol + '//' + location.host),
	        collection: {},
	        index: {}
	      };
	    }
	  }, {
	    key: "getElementState",
	    value: function getElementState() {
	      return {
	        id: 0,
	        name: this.getVariable('defaultName', ''),
	        firstName: this.getVariable('defaultName', ''),
	        lastName: "",
	        workPosition: "",
	        color: "#048bd0",
	        avatar: "",
	        gender: "M",
	        birthday: false,
	        extranet: false,
	        network: false,
	        bot: false,
	        connector: false,
	        externalAuthId: "default",
	        status: "online",
	        idle: false,
	        lastActivityDate: false,
	        mobileLastDate: false,
	        departments: [],
	        absent: false,
	        phones: {
	          workPhone: "",
	          personalMobile: "",
	          personalPhone: ""
	        },
	        init: false
	      };
	    }
	  }, {
	    key: "getGetters",
	    value: function getGetters() {
	      var _this = this;

	      return {
	        get: function get(state) {
	          return function (userId) {
	            if (!state.collection[userId]) {
	              return null;
	            }

	            return state.collection[userId];
	          };
	        },
	        getBlank: function getBlank(state) {
	          return function (params) {
	            return _this.getElementState();
	          };
	        }
	      };
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      var _this2 = this;

	      return {
	        set: function set(store, payload) {
	          if (payload instanceof Array) {
	            payload = payload.map(function (user) {
	              return Object.assign({}, _this2.getElementState(), _this2.validate(Object.assign({}, user), {
	                host: store.state.host
	              }), {
	                init: true
	              });
	            });
	          } else {
	            var result = [];
	            result.push(Object.assign({}, _this2.getElementState(), _this2.validate(Object.assign({}, payload), {
	              host: store.state.host
	            }), {
	              init: true
	            }));
	            payload = result;
	          }

	          store.commit('set', payload);
	        },
	        update: function update(store, payload) {
	          if (typeof store.state.collection[payload.id] === 'undefined' || store.state.collection[payload.id].init === false) {
	            return true;
	          }

	          store.commit('update', {
	            userId: payload.id,
	            fields: _this2.validate(Object.assign({}, payload.fields), {
	              host: store.state.host
	            })
	          });
	          return true;
	        },
	        delete: function _delete(store, payload) {
	          store.commit('delete', payload.id);
	          return true;
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      var _this3 = this;

	      return {
	        set: function set(state, payload) {
	          var _iteratorNormalCompletion = true;
	          var _didIteratorError = false;
	          var _iteratorError = undefined;

	          try {
	            for (var _iterator = payload[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
	              var element = _step.value;

	              if (typeof state.collection[element.id] === 'undefined') {
	                ui_vue.Vue.set(state.collection, element.id, element);
	              }

	              state.collection[element.id] = element;
	            }
	          } catch (err) {
	            _didIteratorError = true;
	            _iteratorError = err;
	          } finally {
	            try {
	              if (!_iteratorNormalCompletion && _iterator.return != null) {
	                _iterator.return();
	              }
	            } finally {
	              if (_didIteratorError) {
	                throw _iteratorError;
	              }
	            }
	          }
	        },
	        update: function update(state, payload) {
	          if (typeof state.collection[payload.id] === 'undefined') {
	            ui_vue.Vue.set(state.collection, payload.id, _this3.getElementState());
	          }

	          state.collection[payload.id] = Object.assign(state.collection[payload.id], payload.fields);
	        },
	        delete: function _delete(state, payload) {
	          delete state.collection[payload.id];
	        }
	      };
	    }
	  }, {
	    key: "validate",
	    value: function validate(fields) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var result = {};
	      options.host = options.host || this.getState().host;

	      if (typeof fields.id === "number" || typeof fields.id === "string") {
	        result.id = parseInt(fields.id);
	      }

	      if (typeof fields.first_name !== "undefined") {
	        fields.firstName = fields.first_name;
	      }

	      if (typeof fields.last_name !== "undefined") {
	        fields.lastName = fields.last_name;
	      }

	      if (typeof fields.name === "string" || typeof fields.name === "number") {
	        result.name = fields.name.toString();

	        if (typeof fields.firstName !== "undefined" && !fields.firstName) {
	          var elementsOfName = fields.name.split(' ');

	          if (elementsOfName.length > 1) {
	            delete elementsOfName[elementsOfName.length - 1];
	            fields.firstName = elementsOfName.join(' ').trim();
	          } else {
	            fields.firstName = result.name;
	          }
	        }

	        if (typeof fields.lastName !== "undefined" && !fields.lastName) {
	          var _elementsOfName = fields.name.split(' ');

	          if (_elementsOfName.length > 1) {
	            fields.lastName = _elementsOfName[_elementsOfName.length - 1];
	          } else {
	            fields.lastName = '';
	          }
	        }
	      }

	      if (typeof fields.firstName === "string" || typeof fields.name === "number") {
	        result.firstName = fields.firstName.toString();
	      }

	      if (typeof fields.lastName === "string" || typeof fields.name === "number") {
	        result.lastName = fields.lastName.toString();
	      }

	      if (typeof fields.work_position !== "undefined") {
	        fields.workPosition = fields.work_position;
	      }

	      if (typeof fields.workPosition === "string" || typeof fields.workPosition === "number") {
	        result.workPosition = fields.workPosition.toString();
	      }

	      if (typeof fields.color === "string") {
	        result.color = fields.color;
	      }

	      if (typeof fields.avatar === 'string') {
	        if (!fields.avatar || fields.avatar.startsWith('http')) {
	          result.avatar = fields.avatar;
	        } else {
	          result.avatar = options.host + fields.avatar;
	        }
	      }

	      if (typeof fields.gender !== 'undefined') {
	        result.gender = fields.gender === 'F' ? 'F' : 'M';
	      }

	      if (typeof fields.birthday === "string") {
	        result.birthday = fields.birthday;
	      }

	      if (typeof fields.extranet === "boolean") {
	        result.extranet = fields.extranet;
	      }

	      if (typeof fields.network === "boolean") {
	        result.network = fields.network;
	      }

	      if (typeof fields.bot === "boolean") {
	        result.bot = fields.bot;
	      }

	      if (typeof fields.connector === "boolean") {
	        result.connector = fields.connector;
	      }

	      if (typeof fields.external_auth_id !== "undefined") {
	        fields.externalAuthId = fields.external_auth_id;
	      }

	      if (typeof fields.externalAuthId === "string" && fields.externalAuthId) {
	        result.externalAuthId = fields.externalAuthId;
	      }

	      if (typeof fields.status === "string") {
	        result.status = fields.status;
	      }

	      if (typeof fields.idle !== "undefined") {
	        if (fields.idle instanceof Date) {
	          result.idle = fields.idle;
	        } else if (typeof fields.idle === "string") {
	          result.idle = new Date(fields.idle);
	        } else {
	          result.idle = false;
	        }
	      }

	      if (typeof fields.last_activity_date !== "undefined") {
	        fields.lastActivityDate = fields.last_activity_date;
	      }

	      if (typeof fields.lastActivityDate !== "undefined") {
	        if (fields.lastActivityDate instanceof Date) {
	          result.lastActivityDate = fields.lastActivityDate;
	        } else if (typeof fields.lastActivityDate === "string") {
	          result.lastActivityDate = new Date(fields.lastActivityDate);
	        } else {
	          result.lastActivityDate = false;
	        }
	      }

	      if (typeof fields.mobile_last_date !== "undefined") {
	        fields.mobileLastDate = fields.mobile_last_date;
	      }

	      if (typeof fields.mobileLastDate !== "undefined") {
	        if (fields.mobileLastDate instanceof Date) {
	          result.mobileLastDate = fields.mobileLastDate;
	        } else if (typeof fields.mobileLastDate === "string") {
	          result.mobileLastDate = new Date(fields.mobileLastDate);
	        } else {
	          result.mobileLastDate = false;
	        }
	      }

	      if (typeof fields.departments !== 'undefined') {
	        result.departments = [];

	        if (fields.departments instanceof Array) {
	          fields.departments.forEach(function (departmentId) {
	            departmentId = parseInt(departmentId);

	            if (departmentId > 0) {
	              result.departments.push(departmentId);
	            }
	          });
	        }
	      }

	      if (typeof fields.absent !== "undefined") {
	        if (fields.absent instanceof Date) {
	          result.absent = fields.absent;
	        } else if (typeof fields.absent === "string") {
	          result.absent = new Date(fields.absent);
	        } else {
	          result.absent = false;
	        }
	      }

	      if (babelHelpers.typeof(fields.phones) === 'object' && !fields.phones) {
	        if (typeof fields.phones.work_phone !== "undefined") {
	          fields.phones.workPhone = fields.phones.work_phone;
	        }

	        if (typeof fields.phones.workPhone === 'string' || typeof fields.phones.workPhone === 'number') {
	          result.phones.workPhone = fields.phones.workPhone.toString();
	        }

	        if (typeof fields.phones.personal_mobile !== "undefined") {
	          fields.phones.personalMobile = fields.phones.personal_mobile;
	        }

	        if (typeof fields.phones.personalMobile === 'string' || typeof fields.phones.personalMobile === 'number') {
	          result.phones.personalMobile = fields.phones.personalMobile.toString();
	        }

	        if (typeof fields.phones.personal_phone !== "undefined") {
	          fields.phones.personalPhone = fields.phones.personal_phone;
	        }

	        if (typeof fields.phones.personalPhone === 'string' || typeof fields.phones.personalPhone === 'number') {
	          result.phones.personalPhone = fields.phones.personalPhone.toString();
	        }
	      }

	      return result;
	    }
	  }]);
	  return UsersModel;
	}(ui_vue_vuex.VuexBuilderModel);

	/**
	 * Bitrix Messenger
	 * File model (Vuex Builder model)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */

	var FilesModel =
	/*#__PURE__*/
	function (_VuexBuilderModel) {
	  babelHelpers.inherits(FilesModel, _VuexBuilderModel);

	  function FilesModel() {
	    babelHelpers.classCallCheck(this, FilesModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FilesModel).apply(this, arguments));
	  }

	  babelHelpers.createClass(FilesModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'files';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return {
	        host: this.getVariable('host', location.protocol + '//' + location.host),
	        created: 0,
	        collection: {},
	        index: {}
	      };
	    }
	  }, {
	    key: "getElementState",
	    value: function getElementState() {
	      return {
	        id: 0,
	        templateId: 0,
	        chatId: 0,
	        date: new Date(),
	        type: 'file',
	        name: "",
	        extension: "",
	        icon: "empty",
	        size: 0,
	        image: false,
	        status: 'done',
	        progress: 100,
	        authorId: 0,
	        authorName: "",
	        urlPreview: "",
	        urlShow: "",
	        urlDownload: ""
	      };
	    }
	  }, {
	    key: "getGetters",
	    value: function getGetters() {
	      var _this = this;

	      return {
	        get: function get(state) {
	          return function (chatId) {
	            if (!state.collection[chatId]) {
	              return null;
	            }

	            return state.collection[chatId];
	          };
	        },
	        getObject: function getObject(state) {
	          return function (chatId) {
	            if (!state.index[chatId]) {
	              return null;
	            }

	            return state.index[chatId];
	          };
	        },
	        getBlank: function getBlank(state) {
	          return function (params) {
	            return _this.getElementState();
	          };
	        }
	      };
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      var _this2 = this;

	      return {
	        set: function set(store, payload) {
	          if (payload instanceof Array) {
	            payload = payload.map(function (file) {
	              var result = _this2.validate(Object.assign({}, file), {
	                host: store.state.host
	              });

	              result.templateId = result.id;
	              return Object.assign({}, _this2.getElementState(), result);
	            });
	          } else {
	            var result = _this2.validate(Object.assign({}, payload), {
	              host: store.state.host
	            });

	            result.templateId = result.id;
	            payload = [];
	            payload.push(Object.assign({}, _this2.getElementState(), result));
	          }

	          store.commit('set', {
	            insertType: im_const.InsertType.after,
	            data: payload
	          });
	        },
	        setBefore: function setBefore(store, payload) {
	          if (payload instanceof Array) {
	            payload = payload.map(function (message) {
	              var result = _this2.validate(Object.assign({}, message), {
	                host: store.state.host
	              });

	              result.templateId = result.id;
	              return Object.assign({}, _this2.getElementState(), result);
	            });
	          } else {
	            var result = _this2.validate(Object.assign({}, payload), {
	              host: store.state.host
	            });

	            result.templateId = result.id;
	            payload = [];
	            payload.push(Object.assign({}, _this2.getElementState(), result));
	          }

	          store.commit('set', {
	            actionName: 'setBefore',
	            insertType: im_const.InsertType.before,
	            data: payload
	          });
	        },
	        update: function update(store, payload) {
	          var result = _this2.validate(Object.assign({}, payload.fields), {
	            host: store.state.host
	          });

	          if (typeof store.state.collection[payload.chatId] === 'undefined') {
	            ui_vue.Vue.set(store.state.collection, payload.chatId, []);
	            ui_vue.Vue.set(store.state.index, payload.chatId, {});
	          }

	          var index = store.state.collection[payload.chatId].findIndex(function (el) {
	            return el.id == payload.id;
	          });

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
	            setTimeout(function () {
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
	        delete: function _delete(store, payload) {
	          store.commit('delete', {
	            id: payload.id,
	            chatId: payload.chatId
	          });
	          return true;
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      return {
	        initCollection: function initCollection(state, payload) {
	          if (typeof state.collection[payload.chatId] === 'undefined') {
	            ui_vue.Vue.set(state.collection, payload.chatId, []);
	            ui_vue.Vue.set(state.index, payload.chatId, {});
	          }
	        },
	        set: function set(state, payload) {
	          if (payload.insertType == im_const.InsertType.after) {
	            var _iteratorNormalCompletion = true;
	            var _didIteratorError = false;
	            var _iteratorError = undefined;

	            try {
	              var _loop = function _loop() {
	                var element = _step.value;

	                if (typeof state.collection[element.chatId] === 'undefined') {
	                  ui_vue.Vue.set(state.collection, element.chatId, []);
	                  ui_vue.Vue.set(state.index, element.chatId, {});
	                }

	                var index = state.collection[element.chatId].findIndex(function (el) {
	                  return el.id === element.id;
	                });

	                if (index > -1) {
	                  state.collection[element.chatId][index] = element;
	                } else {
	                  state.collection[element.chatId].push(element);
	                }

	                state.index[element.chatId][element.id] = element;
	              };

	              for (var _iterator = payload.data[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
	                _loop();
	              }
	            } catch (err) {
	              _didIteratorError = true;
	              _iteratorError = err;
	            } finally {
	              try {
	                if (!_iteratorNormalCompletion && _iterator.return != null) {
	                  _iterator.return();
	                }
	              } finally {
	                if (_didIteratorError) {
	                  throw _iteratorError;
	                }
	              }
	            }
	          } else {
	            var _iteratorNormalCompletion2 = true;
	            var _didIteratorError2 = false;
	            var _iteratorError2 = undefined;

	            try {
	              var _loop2 = function _loop2() {
	                var element = _step2.value;

	                if (typeof state.collection[element.chatId] === 'undefined') {
	                  ui_vue.Vue.set(state.collection, element.chatId, []);
	                  ui_vue.Vue.set(state.index, element.chatId, {});
	                }

	                var index = state.collection[element.chatId].findIndex(function (el) {
	                  return el.id === element.id;
	                });

	                if (index > -1) {
	                  state.collection[element.chatId][index] = element;
	                } else {
	                  state.collection[element.chatId].unshift(element);
	                }

	                state.index[element.chatId][element.id] = element;
	              };

	              for (var _iterator2 = payload.data[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
	                _loop2();
	              }
	            } catch (err) {
	              _didIteratorError2 = true;
	              _iteratorError2 = err;
	            } finally {
	              try {
	                if (!_iteratorNormalCompletion2 && _iterator2.return != null) {
	                  _iterator2.return();
	                }
	              } finally {
	                if (_didIteratorError2) {
	                  throw _iteratorError2;
	                }
	              }
	            }
	          }
	        },
	        update: function update(state, payload) {
	          if (typeof state.collection[payload.chatId] === 'undefined') {
	            ui_vue.Vue.set(state.collection, payload.chatId, []);
	            ui_vue.Vue.set(state.index, payload.chatId, {});
	          }

	          var index = -1;

	          if (typeof payload.index !== 'undefined' && state.collection[payload.chatId][payload.index]) {
	            index = payload.index;
	          } else {
	            index = state.collection[payload.chatId].findIndex(function (el) {
	              return el.id == payload.id;
	            });
	          }

	          if (index >= 0) {
	            var element = Object.assign(state.collection[payload.chatId][index], payload.fields);
	            state.collection[payload.chatId][index] = element;
	            state.index[payload.chatId][element.id] = element;
	          }
	        },
	        delete: function _delete(state, payload) {
	          if (typeof state.collection[payload.chatId] === 'undefined') {
	            ui_vue.Vue.set(state.collection, payload.chatId, []);
	            ui_vue.Vue.set(state.index, payload.chatId, {});
	          }

	          state.collection[payload.chatId] = state.collection[payload.chatId].filter(function (element) {
	            return element.id != payload.id;
	          });
	          delete state.index[payload.chatId][payload.id];
	        }
	      };
	    }
	  }, {
	    key: "validate",
	    value: function validate(fields) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var result = {};
	      options.host = options.host || this.getState().host;

	      if (typeof fields.id === "number" || typeof fields.id === "string") {
	        result.id = parseInt(fields.id);
	      }

	      if (typeof fields.chatId === "number" || typeof fields.chatId === "string") {
	        result.chatId = parseInt(fields.chatId);
	      }

	      if (fields.date instanceof Date) {
	        result.date = fields.date;
	      } else if (typeof fields.date === "string") {
	        result.date = new Date(fields.date);
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
	        } else if (result.extension === 'docx' || result.extension === 'doc') {
	          result.icon = 'doc';
	        } else if (result.extension === 'xlsx' || result.extension === 'xls') {
	          result.icon = 'xls';
	        } else if (result.extension === 'pptx' || result.extension === 'ppt') {
	          result.icon = 'ppt';
	        } else if (result.extension === 'rar') {
	          result.icon = 'rar';
	        } else if (result.extension === 'zip') {
	          result.icon = 'zip';
	        } else if (result.extension === 'pdf') {
	          result.icon = 'pdf';
	        } else if (result.extension === 'txt') {
	          result.icon = 'txt';
	        } else if (result.extension === 'php') {
	          result.icon = 'php';
	        } else if (result.extension === 'conf' || result.extension === 'ini' || result.extension === 'plist') {
	          result.icon = 'set';
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
	      } else if (babelHelpers.typeof(fields.image) === 'object' && fields.image) {
	        result.image = {
	          width: 0,
	          height: 0
	        };

	        if (typeof fields.image.width === "number") {
	          result.image.width = fields.image.width;
	        }

	        if (typeof fields.image.height === "number") {
	          result.image.height = fields.image.height;
	        }
	      }

	      if (typeof fields.status === "string") {
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
	        if (!fields.urlPreview || fields.urlPreview.startsWith('http')) {
	          result.urlPreview = fields.urlPreview;
	        } else {
	          result.urlPreview = options.host + fields.urlPreview;
	        }
	      }

	      if (typeof fields.urlDownload === 'string') {
	        if (!fields.urlDownload || fields.urlDownload.startsWith('http')) {
	          result.urlDownload = fields.urlDownload;
	        } else {
	          result.urlDownload = options.host + fields.urlDownload;
	        }
	      }

	      if (typeof fields.urlShow === 'string') {
	        if (!fields.urlShow || fields.urlShow.startsWith('http')) {
	          result.urlShow = fields.urlShow;
	        } else {
	          result.urlShow = options.host + fields.urlShow;
	        }
	      }

	      return result;
	    }
	  }]);
	  return FilesModel;
	}(ui_vue_vuex.VuexBuilderModel);

	exports.ApplicationModel = ApplicationModel;
	exports.MessagesModel = MessagesModel;
	exports.DialoguesModel = DialoguesModel;
	exports.UsersModel = UsersModel;
	exports.FilesModel = FilesModel;

}((this.BX.Messenger.Model = this.BX.Messenger.Model || {}),BX,BX,BX.Messenger.Const));
//# sourceMappingURL=registry.bundle.js.map
