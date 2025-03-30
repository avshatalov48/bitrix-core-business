/* eslint-disable */
(function (exports,pull_util,pull_connector,pull_configholder) {
	'use strict';

	const REVISION = 19; // api revision - check module/pull/include.php

	const ConnectionType = {
	  WebSocket: 'webSocket',
	  LongPolling: 'longPolling'
	};
	const PullStatus = {
	  Online: 'online',
	  Offline: 'offline',
	  Connecting: 'connect'
	};
	const SenderType = {
	  Unknown: 0,
	  Client: 1,
	  Backend: 2
	};
	const SubscriptionType = {
	  Server: 'server',
	  Client: 'client',
	  Online: 'online',
	  Status: 'status',
	  Revision: 'revision'
	};
	const CloseReasons = {
	  NORMAL_CLOSURE: 1000,
	  SERVER_DIE: 1001,
	  CONFIG_REPLACED: 3000,
	  CHANNEL_EXPIRED: 3001,
	  SERVER_RESTARTED: 3002,
	  CONFIG_EXPIRED: 3003,
	  MANUAL: 3004,
	  STUCK: 3005,
	  BACKEND_ERROR: 3006,
	  WRONG_CHANNEL_ID: 4010
	};
	const ServerMode = {
	  Shared: 'shared',
	  Personal: 'personal'
	};

	/* eslint-disable @bitrix24/bitrix24-rules/no-typeof */
	function isString(item) {
	  return item === '' ? true : item ? typeof item === 'string' || item instanceof String : false;
	}
	function isArray(item) {
	  return item && Object.prototype.toString.call(item) === '[object Array]';
	}
	function isDomNode(item) {
	  return item && typeof item === 'object' && 'nodeType' in item;
	}
	function isDate(item) {
	  return item && Object.prototype.toString.call(item) === '[object Date]';
	}
	function isPlainObject(item) {
	  return Boolean(item) && typeof item === 'object' && item.constructor === Object;
	}
	function isNotEmptyString(item) {
	  return isString(item) ? item.length > 0 : false;
	}
	function isJsonRpcRequest(item) {
	  return typeof item === 'object' && item && 'jsonrpc' in item && isNotEmptyString(item.jsonrpc) && 'method' in item && isNotEmptyString(item.method);
	}
	function isJsonRpcResponse(item) {
	  return typeof item === 'object' && item && 'jsonrpc' in item && isNotEmptyString(item.jsonrpc) && 'id' in item && ('result' in item || 'error' in item);
	}
	function buildQueryString(params) {
	  let result = '';
	  for (const key of Object.keys(params)) {
	    const value = params[key];
	    if (isArray(value)) {
	      for (const [index, valueElement] of value.entries()) {
	        const left = encodeURIComponent(`${key}[${index}]`);
	        const right = `${encodeURIComponent(valueElement)}&`;
	        result += `${left}=${right}`;
	      }
	    } else {
	      result += `${encodeURIComponent(key)}=${encodeURIComponent(value)}&`;
	    }
	  }
	  if (result.length > 0) {
	    result = result.slice(0, Math.max(0, result.length - 1));
	  }
	  return result;
	}
	function clone(obj, bCopyObj = true) {
	  let _obj, i, l;
	  if (obj === null) {
	    return null;
	  }
	  if (isDomNode(obj)) {
	    _obj = obj.cloneNode(bCopyObj);
	  } else if (typeof obj === 'object') {
	    if (isArray(obj)) {
	      _obj = [];
	      for (i = 0, l = obj.length; i < l; i++) {
	        if (typeof obj[i] === 'object' && bCopyObj) {
	          _obj[i] = clone(obj[i], bCopyObj);
	        } else {
	          _obj[i] = obj[i];
	        }
	      }
	    } else {
	      _obj = {};
	      if (obj.constructor) {
	        if (isDate(obj)) {
	          _obj = new Date(obj);
	        } else {
	          _obj = new obj.constructor();
	        }
	      }
	      for (i in obj) {
	        if (!obj.hasOwnProperty(i)) {
	          continue;
	        }
	        if (typeof obj[i] === 'object' && bCopyObj) {
	          _obj[i] = clone(obj[i], bCopyObj);
	        } else {
	          _obj[i] = obj[i];
	        }
	      }
	    }
	  } else {
	    _obj = obj;
	  }
	  return _obj;
	}
	function getDateForLog() {
	  const d = new Date();
	  return `${d.getFullYear()}-${lpad(d.getMonth(), 2, '0')}-${lpad(d.getDate(), 2, '0')} ${lpad(d.getHours(), 2, '0')}:${lpad(d.getMinutes(), 2, '0')}`;
	}
	function lpad(str, length, chr = ' ') {
	  if (str.length > length) {
	    return str;
	  }
	  let result = '';
	  for (let i = 0; i < length - result.length; i++) {
	    result += chr;
	  }
	  return result + str;
	}

	/* eslint-disable @bitrix24/bitrix24-rules/no-typeof */
	var _subscribers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribers");
	var _logger = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("logger");
	class Emitter {
	  // [userId] => array of callbacks

	  constructor(options = {}) {
	    Object.defineProperty(this, _subscribers, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _logger, {
	      writable: true,
	      value: void 0
	    });
	    this.debug = false;
	    this.userStatusCallbacks = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _logger)[_logger] = options.logger;
	  }

	  /**
	   * Creates a subscription to incoming messages.
	   *
	   * @returns {Function} - Unsubscribe callback function
	   */
	  subscribe(params) {
	    /**
	     * After modify this method, copy to follow scripts:
	     * mobile/install/mobileapp/mobile/extensions/bitrix/pull/client/events/extension.js
	     * mobile/install/js/mobile/pull/client/src/client.js
	     */

	    if (!pull_util.isObject(params)) {
	      throw new TypeError('params must be an object');
	    }
	    if (!pull_util.isPlainObject(params)) {
	      return this.attachCommandHandler(params);
	    }
	    const {
	      command,
	      moduleId,
	      callback,
	      type = SubscriptionType.Server
	    } = params;
	    if (type === SubscriptionType.Server || type === SubscriptionType.Client) {
	      if (typeof babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type] === 'undefined') {
	        babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type] = {};
	      }
	      if (typeof babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type][moduleId] === 'undefined') {
	        babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type][moduleId] = {
	          callbacks: [],
	          commands: {}
	        };
	      }
	      if (command) {
	        if (!pull_util.isArray(babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type][moduleId].commands[command])) {
	          babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type][moduleId].commands[command] = [];
	        }
	        babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type][moduleId].commands[command].push(callback);
	        return () => {
	          // eslint-disable-next-line max-len
	          babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type][moduleId].commands[command] = babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type][moduleId].commands[command].filter(element => {
	            return element !== callback;
	          });
	        };
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type][moduleId].callbacks.push(callback);
	      return () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type][moduleId].callbacks = babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type][moduleId].callbacks.filter(element => {
	          return element !== callback;
	        });
	      };
	    }
	    if (typeof babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type] === 'undefined') {
	      babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type] = [];
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type].push(callback);
	    return () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type] = babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][type].filter(element => {
	        return element !== callback;
	      });
	    };
	  }

	  /*
	   Subscribes provided handler to pull events.
	   @return {() => void} Returns function, that can be called to unsubscribe the handler.
	   */
	  attachCommandHandler(handler) {
	    /**
	     * After modify this method, copy to follow scripts:
	     * mobile/install/mobileapp/mobile/extensions/bitrix/pull/client/events/extension.js
	     */
	    const moduleId = pull_util.isFunction(handler.getModuleId) ? handler.getModuleId() : '';
	    if (!pull_util.isNotEmptyString(moduleId)) {
	      throw new TypeError('handler.getModuleId() must return a string');
	    }
	    let type = SubscriptionType.Server;
	    if (pull_util.isFunction(handler.getSubscriptionType)) {
	      type = handler.getSubscriptionType();
	      if (!Object.values(SubscriptionType).includes(type)) {
	        throw new Error('result of handler.getSubscriptionType() must return valid SubscriptionType element');
	      }
	    }
	    return this.subscribe({
	      type,
	      moduleId,
	      callback: data => {
	        const method = findHandlerMethod(handler, data.command);
	        if (method) {
	          var _babelHelpers$classPr;
	          let loggableData = '';
	          try {
	            loggableData = JSON.stringify(data);
	          } catch {
	            loggableData = '(contains circular references)';
	          }
	          (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _logger)[_logger]) == null ? void 0 : _babelHelpers$classPr.log(`Pull.attachCommandHandler: receive command ${loggableData}`);
	          method(data.params, data.extra, data.command);
	        }
	      }
	    });
	  }

	  /**
	   *
	   * @param params {Object}
	   * @returns {boolean}
	   */
	  emit(params = {}) {
	    /**
	     * After modify this method, copy to follow scripts:
	     * mobile/install/mobileapp/mobile/extensions/bitrix/pull/client/events/extension.js
	     * mobile/install/js/mobile/pull/client/src/client.js
	     */
	    if (params.type === SubscriptionType.Server || params.type === SubscriptionType.Client) {
	      if (typeof babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][params.type] === 'undefined') {
	        babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][params.type] = {};
	      }
	      if (typeof babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][params.type][params.moduleId] === 'undefined') {
	        babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][params.type][params.moduleId] = {
	          callbacks: [],
	          commands: {}
	        };
	      }
	      if (babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][params.type][params.moduleId].callbacks.length > 0) {
	        babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][params.type][params.moduleId].callbacks.forEach(callback => {
	          callback(params.data, {
	            type: params.type,
	            moduleId: params.moduleId
	          });
	        });
	      }
	      if (babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][params.type][params.moduleId].commands[params.data.command] && babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][params.type][params.moduleId].commands[params.data.command].length > 0) {
	        babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][params.type][params.moduleId].commands[params.data.command].forEach(callback => {
	          callback(params.data.params, params.data.extra, params.data.command, {
	            type: params.type,
	            moduleId: params.moduleId
	          });
	        });
	      }
	      return true;
	    }
	    if (typeof babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][params.type] === 'undefined') {
	      babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][params.type] = [];
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][params.type].length <= 0) {
	      return true;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribers)[_subscribers][params.type].forEach(callback => {
	      callback(params.data, {
	        type: params.type
	      });
	    });
	    return true;
	  }
	  broadcastMessage(message) {
	    var _message$extra;
	    const moduleId = message.module_id.toLowerCase();
	    const command = message.command;
	    const params = message.params;
	    const extra = (_message$extra = message.extra) != null ? _message$extra : {};
	    this.logMessage(message);
	    try {
	      if (extra.sender && extra.sender.type === SenderType.Client) {
	        this.emitClientEvent(moduleId, command, pull_util.clone(params), pull_util.clone(extra));
	      } else if (moduleId === 'online') {
	        if (extra.server_time_ago < 240) {
	          this.emitOnlineEvent(moduleId, command, pull_util.clone(params), pull_util.clone(extra));
	        }
	        if (command === 'userStatusChange') {
	          this.emitUserStatusChange(params.user_id, params.online);
	        }
	      } else {
	        this.emitServerEvent(moduleId, command, pull_util.clone(params), pull_util.clone(extra));
	      }
	    } catch (e) {
	      if (typeof console === 'object') {
	        console.error('\n========= PULL ERROR ===========\n' + 'Error type: broadcastMessages execute error\n' + 'Error event: ', e, '\n' + 'Message: ', message, '\n' + '================================\n');
	        if (pull_util.isFunction(BX.debug)) {
	          BX.debug(e);
	        }
	      }
	    }
	  }
	  emitServerEvent(moduleId, command, params, extra) {
	    if ('BX' in globalThis && pull_util.isFunction(BX.onCustomEvent)) {
	      BX.onCustomEvent(window, `onPullEvent-${moduleId}`, [command, params, extra], true);
	      BX.onCustomEvent(window, 'onPullEvent', [moduleId, command, params, extra], true);
	    }
	    this.emit({
	      type: SubscriptionType.Server,
	      moduleId,
	      data: {
	        command,
	        params,
	        extra
	      }
	    });
	  }
	  emitClientEvent(moduleId, command, params, extra) {
	    if (pull_util.isFunction(BX.onCustomEvent)) {
	      BX.onCustomEvent(window, `onPullClientEvent-${moduleId}`, [command, params, extra], true);
	      BX.onCustomEvent(window, 'onPullClientEvent', [moduleId, command, params, extra], true);
	    }
	    this.emit({
	      type: SubscriptionType.Client,
	      moduleId,
	      data: {
	        command,
	        params,
	        extra
	      }
	    });
	  }
	  emitOnlineEvent(moduleId, command, params, extra) {
	    if (pull_util.isFunction(BX.onCustomEvent)) {
	      BX.onCustomEvent(window, 'onPullOnlineEvent', [command, params, extra], true);
	    }
	    this.emit({
	      type: SubscriptionType.Online,
	      data: {
	        command,
	        params,
	        extra
	      }
	    });
	  }
	  addUserStatusCallback(userId, callback) {
	    if (!this.userStatusCallbacks[userId]) {
	      this.userStatusCallbacks[userId] = [];
	    }
	    if (pull_util.isFunction(callback)) {
	      this.userStatusCallbacks[userId].push(callback);
	    }
	  }
	  removeUserStatusCallback(userId, callback) {
	    if (this.userStatusCallbacks[userId]) {
	      this.userStatusCallbacks[userId] = this.userStatusCallbacks[userId].filter(cb => cb !== callback);
	    }
	  }
	  hasUserStatusCallbacks(userId) {
	    return this.userStatusCallbacks[userId].length > 0;
	  }
	  emitUserStatusChange(userId, isOnline) {
	    if (this.userStatusCallbacks[userId]) {
	      this.userStatusCallbacks[userId].forEach(cb => cb({
	        userId,
	        isOnline
	      }));
	    }
	  }
	  getSubscribedUsersList() {
	    const result = [];
	    for (const userId of Object.keys(this.userStatusCallbacks)) {
	      if (this.userStatusCallbacks[userId].length > 0) {
	        result.push(Number(userId));
	      }
	    }
	    return result;
	  }
	  capturePullEvent(debugFlag = true) {
	    this.debug = debugFlag;
	  }
	  logMessage(message) {
	    if (!this.debug) {
	      return;
	    }
	    if (message.extra.sender && message.extra.sender.type === SenderType.Client) {
	      console.info(`onPullClientEvent-${message.module_id}`, message.command, message.params, message.extra);
	    } else if (message.module_id === 'online') {
	      console.info('onPullOnlineEvent', message.command, message.params, message.extra);
	    } else {
	      console.info('onPullEvent', message.module_id, message.command, message.params, message.extra);
	    }
	  }
	}
	function findHandlerMethod(handler, command) {
	  let method = null;
	  if (pull_util.isFunction(handler.getMap)) {
	    const mapping = handler.getMap();
	    if (pull_util.isPlainObject(mapping)) {
	      if (pull_util.isFunction(mapping[command])) {
	        method = mapping[command].bind(handler);
	      } else if (typeof mapping[command] === 'string' && pull_util.isFunction(handler[mapping[command]])) {
	        method = handler[mapping[command]].bind(handler);
	      }
	    }
	  }
	  if (!method) {
	    const methodName = getDefaultHandlerMethodName(command);
	    if (pull_util.isFunction(handler[methodName])) {
	      method = handler[methodName].bind(handler);
	    }
	  }
	  return method;
	}
	function getDefaultHandlerMethodName(command) {
	  return `handle${command.charAt(0).toUpperCase()}${command.slice(1)}`;
	}

	class ErrorNotConnected extends Error {
	  constructor(message) {
	    super(message);
	    this.name = 'ErrorNotConnected';
	  }
	}

	class ErrorTimeout extends Error {
	  constructor(message) {
	    super(message);
	    this.name = 'ErrorTimeout';
	  }
	}

	const JSON_RPC_VERSION = '2.0';
	const RpcError = {
	  Parse: {
	    code: -32700,
	    message: 'Parse error'
	  },
	  InvalidRequest: {
	    code: -32600,
	    message: 'Invalid Request'
	  },
	  MethodNotFound: {
	    code: -32601,
	    message: 'Method not found'
	  },
	  InvalidParams: {
	    code: -32602,
	    message: 'Invalid params'
	  },
	  Internal: {
	    code: -32603,
	    message: 'Internal error'
	  }
	};
	class JsonRpc extends EventTarget {
	  constructor(options) {
	    super();
	    this.idCounter = 0;
	    this.handlers = {};
	    this.rpcResponseAwaiters = new Map();
	    this.sender = options.sender;
	    for (const method of Object.keys(options.handlers || {})) {
	      this.handle(method, options.handlers[method]);
	    }
	    for (const eventType of Object.keys(options.events || {})) {
	      // eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-events-binding
	      this.addEventListener(eventType, options.events[eventType]);
	    }
	  }

	  /**
	   * @param {string} method
	   * @param {function} handler
	   */
	  handle(method, handler) {
	    this.handlers[method] = handler;
	  }

	  /**
	   * Sends RPC command to the server.
	   *
	   * @param {string} method Method name
	   * @param {object} params
	   * @param {int} timeout
	   * @returns {Promise}
	   */
	  executeOutgoingRpcCommand(method, params, timeout = 5) {
	    return new Promise((resolve, reject) => {
	      const request = this.createRequest(method, params);
	      if (this.sender.send(JSON.stringify(request)) === false) {
	        reject(new ErrorNotConnected('send failed'));
	      }
	      if (timeout > 0) {
	        const t = setTimeout(() => {
	          this.rpcResponseAwaiters.delete(request.id);
	          reject(new ErrorTimeout('no response'));
	        }, timeout * 1000);
	        this.rpcResponseAwaiters.set(request.id, {
	          resolve,
	          reject,
	          timeout: t
	        });
	      } else {
	        resolve();
	      }
	    });
	  }

	  /**
	   * Executes array or rpc commands. Returns array of promises, each promise will be resolved individually.
	   *
	   * @param {JsonRpcRequest[]} batch
	   * @returns {Promise[]}
	   */
	  executeOutgoingRpcBatch(batch) {
	    const requests = [];
	    const promises = [];
	    batch.forEach(({
	      method,
	      params,
	      id
	    }) => {
	      const request = this.createRequest(method, params, id);
	      requests.push(request);
	      promises.push(new Promise((resolve, reject) => {
	        this.rpcResponseAwaiters.set(request.id, {
	          resolve,
	          reject
	        });
	      }));
	    });
	    this.sender.send(JSON.stringify(requests));
	    return promises;
	  }
	  processRpcResponse(response) {
	    if ('id' in response && this.rpcResponseAwaiters.has(response.id)) {
	      const awaiter = this.rpcResponseAwaiters.get(response.id);
	      if ('result' in response) {
	        awaiter.resolve(response.result);
	      } else if ('error' in response) {
	        awaiter.reject(response.error);
	      } else {
	        awaiter.reject(new Error('wrong response structure'));
	      }
	      clearTimeout(awaiter.timeout);
	      this.rpcResponseAwaiters.delete(response.id);
	    } else {
	      this.dispatchEvent(new CustomEvent('error', {
	        error: new Error(`received rpc response with unknown id ${response}`)
	      }));
	    }
	  }
	  async handleIncomingMessage(message) {
	    let decoded = {};
	    try {
	      decoded = JSON.parse(message);
	    } catch (e) {
	      throw new Error(`could not decode json rpc message: ${e}`);
	    }
	    if (isArray(decoded)) {
	      this.executeIncomingRpcBatch(decoded);
	    } else if (isJsonRpcRequest(decoded)) {
	      const commandResult = await this.executeIncomingRpcCommand(decoded);
	      if (commandResult !== null && commandResult !== undefined) {
	        const response = commandResult.error ? this.createErrorResponse(decoded.id, commandResult.error) : this.createResponse(decoded.id, commandResult);
	        this.sender.send(JSON.stringify(response));
	      } else {
	        this.sender.send(JSON.stringify(this.createResponse(decoded.id, null)));
	      }
	    } else if (isJsonRpcResponse(decoded)) {
	      this.processRpcResponse(decoded);
	    } else {
	      throw new Error(`unknown rpc packet: ${decoded}`);
	    }
	  }

	  /**
	   * Executes RPC command, received from the server
	   *
	   * @param {string} method
	   * @param {object} params
	   * @returns {object}
	   */
	  async executeIncomingRpcCommand({
	    method,
	    params
	  }) {
	    if (method in this.handlers) {
	      try {
	        return this.handlers[method].call(this, params);
	      } catch (e) {
	        return {
	          jsonrpc: '2.0',
	          error: e.toString()
	        };
	      }
	    }
	    return {
	      error: RpcError.MethodNotFound
	    };
	  }
	  async executeIncomingRpcBatch(batch) {
	    const result = [];
	    for (const command of batch) {
	      if ('jsonrpc' in command) {
	        if ('method' in command) {
	          const commandResult = this.executeIncomingRpcCommand(command);
	          if (commandResult) {
	            commandResult.jsonrpc = JSON_RPC_VERSION;
	            commandResult.id = command.id;
	            result.push(commandResult);
	          }
	        } else {
	          this.processRpcResponse(command);
	        }
	      } else {
	        this.dispatchEvent(new CustomEvent('error', {
	          error: new Error(`unknown rpc command in batch: ${command}`)
	        }));
	        result.push({
	          jsonrpc: '2.0',
	          error: RpcError.InvalidRequest
	        });
	      }
	    }
	    return result;
	  }
	  nextId() {
	    this.idCounter++;
	    return this.idCounter;
	  }
	  createPublishRequest(messageBatch) {
	    const result = messageBatch.map(message => this.createRequest('publish', message));
	    if (result.length === 0) {
	      return result[0];
	    }
	    return result;
	  }
	  createRequest(method, params, id) {
	    return {
	      jsonrpc: JSON_RPC_VERSION,
	      method,
	      params,
	      id: id != null ? id : this.nextId()
	    };
	  }
	  createResponse(id, result) {
	    return {
	      jsonrpc: JSON_RPC_VERSION,
	      id,
	      result
	    };
	  }
	  createErrorResponse(id, error) {
	    return {
	      jsonrpc: JSON_RPC_VERSION,
	      id,
	      error
	    };
	  }
	}

	/* eslint-disable @bitrix24/bitrix24-rules/no-typeof */
	const WorkerConnectorEvents = {
	  Message: 'message',
	  RevisionChanged: 'revisionChanged',
	  ConnectionStatus: 'connectionStatus'
	};
	const WORKER_PATH = '/bitrix/js/pull/worker/dist/pull.worker.bundle.js';
	const WORKER_NAME = 'Bitrix24 Push&Pull';
	class WorkerConnector extends EventTarget {
	  static isSharedWorkerSupported() {
	    return 'SharedWorker' in window;
	  }
	  constructor(options) {
	    super();
	    this.connectionType = ConnectionType.WebSocket;
	    this.connectionStatus = PullStatus.Offline;
	    this.isJsonRpcConnection = false;
	    this.bundleTimestamp = options.bundleTimestamp;
	    this.configTimestamp = options.configTimestamp;
	    for (const eventName of Object.keys(options.events || {})) {
	      this.addEventListener(eventName, options.events[eventName]);
	    }
	    this.worker = new SharedWorker(`${WORKER_PATH}?${this.bundleTimestamp}`, WORKER_NAME);
	    this.rpcAdapter = this.createRpcAdapter();
	    this.worker.port.start();
	    this.worker.port.addEventListener('message', this.onPortMessage.bind(this));
	    window.addEventListener('offline', this.onOffline.bind(this));
	    window.addEventListener('online', this.onOnline.bind(this));
	    window.addEventListener('pagehide', this.onPageHide.bind(this));
	  }
	  createRpcAdapter() {
	    return new JsonRpc({
	      sender: {
	        send: m => this.worker.port.postMessage(m)
	      },
	      handlers: {
	        ready: this.handleReady.bind(this),
	        incomingMessage: this.handleIncomingMessage.bind(this),
	        revisionChanged: this.handleRevisionChanged.bind(this),
	        connectionStatusChanged: this.handleConnectionStatusChanged.bind(this)
	      },
	      events: {
	        error: error => console.error('rpc error', error)
	      }
	    });
	  }
	  setPublicIds(publicIds) {
	    return this.rpcAdapter.executeOutgoingRpcCommand('setPublicIds', {
	      publicIds
	    });
	  }
	  sendMessage(users, moduleId, command, params, expiry) {
	    return this.rpcAdapter.executeOutgoingRpcCommand('sendMessage', {
	      users,
	      moduleId,
	      command,
	      params,
	      expiry
	    });
	  }
	  sendMessageBatch(messageBatch) {
	    return this.rpcAdapter.executeOutgoingRpcCommand('sendMessageBatch', {
	      messageBatch
	    });
	  }
	  sendMessageToChannels(publicChannels, moduleId, command, params, expiry) {
	    return this.rpcAdapter.executeOutgoingRpcCommand('sendMessageToChannels', {
	      publicChannels,
	      moduleId,
	      command,
	      params,
	      expiry
	    });
	  }
	  connect() {
	    return Promise.resolve();
	  }
	  getUsersLastSeen(userList) {
	    return this.rpcAdapter.executeOutgoingRpcCommand('getUsersLastSeen', {
	      userList
	    });
	  }
	  listChannels() {
	    return this.rpcAdapter.executeOutgoingRpcCommand('listChannels');
	  }
	  isJsonRpc() {
	    return this.isJsonRpcConnection;
	  }
	  subscribeUserStatusChange(userId) {
	    return this.rpcAdapter.executeOutgoingRpcCommand('subscribeUserStatusChange', {
	      userId
	    });
	  }
	  unsubscribeUserStatusChange(userId) {
	    return this.rpcAdapter.executeOutgoingRpcCommand('unsubscribeUserStatusChange', {
	      userId
	    });
	  }
	  isWebSocketConnected() {
	    return this.connectionType === ConnectionType.WebSocket && this.connectionStatus === PullStatus.Online;
	  }
	  getConnectionPath() {
	    return 'not available in SharedWorker mode';
	  }
	  getServerMode() {
	    return 'n/a';
	  }
	  onLoginSuccess() {
	    this.rpcAdapter.executeOutgoingRpcCommand('notifyLogin');
	  }
	  handleReady() {
	    this.rpcAdapter.executeOutgoingRpcCommand('notifyConfigTimestamp', {
	      configTimestamp: this.configTimestamp
	    });
	  }
	  handleIncomingMessage({
	    payload
	  }) {
	    this.dispatchEvent(new CustomEvent(WorkerConnectorEvents.Message, {
	      detail: payload
	    }));
	  }
	  handleRevisionChanged({
	    revision
	  }) {
	    this.dispatchEvent(new CustomEvent(WorkerConnectorEvents.RevisionChanged, {
	      detail: {
	        revision
	      }
	    }));
	  }
	  handleConnectionStatusChanged({
	    status,
	    connectionType,
	    isJsonRpc
	  }) {
	    this.dispatchEvent(new CustomEvent(WorkerConnectorEvents.ConnectionStatus, {
	      detail: {
	        status
	      }
	    }));
	    this.connectionType = connectionType;
	    this.connectionStatus = status;
	    this.isJsonRpcConnection = isJsonRpc;
	  }
	  onPortMessage(e) {
	    const message = e.data;
	    this.rpcAdapter.handleIncomingMessage(message);
	  }
	  onOffline() {
	    this.rpcAdapter.executeOutgoingRpcCommand('notifyOffline');
	  }
	  onOnline() {
	    this.rpcAdapter.executeOutgoingRpcCommand('notifyOnline');
	  }
	  onPageHide() {
	    this.rpcAdapter.executeOutgoingRpcCommand('bye');
	  }
	  isConnected() {
	    return this.connectionStatus === PullStatus.Online;
	  }
	  async pingWorker() {
	    return this.rpcAdapter.executeOutgoingRpcCommand('bye');
	  }
	  async getWorkerLog() {
	    return this.rpcAdapter.executeOutgoingRpcCommand('getLog');
	  }
	  async getWorkerConfig() {
	    return this.rpcAdapter.executeOutgoingRpcCommand('getConfig');
	  }
	  disconnect() {
	    console.warn('Pull: SharedWorker mode: disconnection request ignored');
	  }
	  scheduleReconnect() {
	    // nothing
	  }
	  resetSession() {
	    // nothing
	  }
	}

	/* eslint-disable max-classes-per-file */
	class MiniRest {
	  constructor(options = {}) {
	    this.sessid = '';
	    this.queryParams = {};
	    if (isNotEmptyString(options.sessid)) {
	      this.sessid = options.sessid;
	    }
	    if (isPlainObject(options.queryParams)) {
	      this.queryParams = options.queryParams;
	    }
	  }
	  async callMethod(method, params = {}, _ = null, __ = null, logTag = '') {
	    const lt = logTag ? `?logTag=${logTag}` : '';
	    const url = `/rest/${method}.json${lt}`;
	    let decoded = null;
	    let responseStatus = 0;
	    try {
	      const response = await fetch(url, this.getFetchOptions({
	        ...this.queryParams,
	        ...params
	      }));
	      responseStatus = response.status;
	      decoded = await response.json();
	    } catch {
	      throw new RestCompatResult(0, {
	        error: 'NETWORK_ERROR',
	        error_description: 'Network error'
	      });
	    }
	    if (decoded && 'error' in decoded && decoded.error === 'session_failed' && 'sessid' in decoded && isNotEmptyString(decoded.sessid)) {
	      this.sessid = decoded.sessid;
	      // after setting sessid fetch options should differ
	      try {
	        const fallbackResponse = await fetch(url, this.getFetchOptions({
	          ...this.queryParams,
	          ...params
	        }));
	        responseStatus = fallbackResponse.status;
	        decoded = await fallbackResponse.json();
	      } catch {
	        throw new RestCompatResult(0, {
	          error: 'NETWORK_ERROR',
	          error_description: 'Network error'
	        });
	      }
	    }
	    const result = new RestCompatResult(responseStatus, decoded);
	    if (result.isError) {
	      throw result;
	    }
	    return result;
	  }
	  getFetchOptions(params = {}) {
	    const query = buildQueryString({
	      sessid: this.getSessid(),
	      ...params
	    });
	    return {
	      method: 'POST',
	      headers: {
	        'Content-Type': 'application/x-www-form-urlencoded',
	        'X-Bitrix-Csrf-Token': this.getSessid()
	      },
	      credentials: 'same-origin',
	      body: query
	    };
	  }
	  getSessid() {
	    if (this.sessid !== '') {
	      return this.sessid;
	    }

	    // eslint-disable-next-line @bitrix24/bitrix24-rules/no-typeof
	    if (typeof BX !== 'undefined' && BX.bitrix_sessid) {
	      // eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
	      return BX.bitrix_sessid();
	    }
	    return '';
	  }
	}
	class RestCompatResult {
	  constructor(status, answer) {
	    this.isError = false;
	    this.status = status;
	    this.answer = answer;
	    if (typeof this.answer.error !== 'undefined') {
	      this.isError = true;
	      this.answer.ex = new RestCompatError(this.status, typeof this.answer.error === 'string' ? this.answer : this.answer.error);
	    }
	  }
	  data() {
	    return this.answer.result;
	  }
	  time() {
	    return this.answer.time;
	  }
	  error() {
	    return this.answer.ex;
	  }
	}
	class RestCompatError {
	  constructor(status, ex) {
	    this.status = status;
	    this.ex = ex;
	  }
	  getError() {
	    return this.ex;
	  }
	  getStatus() {
	    return this.status;
	  }
	  toString() {
	    const description = this.ex.error_description ? `: ${this.ex.error_description}` : '';
	    return `${this.ex.error}${description} (${this.status})`;
	  }
	}

	class TagWatcher {
	  constructor(options) {
	    this.queue = {};
	    this.watchUpdateInterval = 1740000;
	    this.watchForceUpdateInterval = 5000;
	    this.restClient = options.restClient;
	  }
	  extend(tag, force) {
	    if (!tag || this.queue[tag]) {
	      return;
	    }
	    this.queue[tag] = true;
	    if (force) {
	      this.scheduleUpdate(true);
	    }
	  }
	  clear(tagId) {
	    delete this.queue[tagId];
	  }
	  scheduleUpdate(force) {
	    clearTimeout(this.watchUpdateTimeout);
	    this.watchUpdateTimeout = setTimeout(() => {
	      this.update();
	    }, force ? this.watchForceUpdateInterval : this.watchUpdateInterval);
	  }
	  update() {
	    const watchTags = Object.keys(this.queue);
	    if (watchTags.length > 0) {
	      this.restClient.callMethod('pull.watch.extend', {
	        tags: watchTags
	      }, result => {
	        if (result.error()) {
	          this.scheduleUpdate();
	          return;
	        }
	        const updatedTags = result.data();
	        for (const tagId of Object.keys(updatedTags)) {
	          if (!updatedTags[tagId]) {
	            this.clear(tagId);
	          }
	        }
	        this.scheduleUpdate();
	      });
	    } else {
	      this.scheduleUpdate();
	    }
	  }
	}

	/* eslint-disable @bitrix24/bitrix24-rules/no-bx-message */
	class StorageManager {
	  constructor(params = {}) {
	    var _params$userId, _params$siteId;
	    this.userId = (_params$userId = params.userId) != null ? _params$userId : BX.message && BX.message.USER_ID ? BX.message.USER_ID : 0;
	    this.siteId = (_params$siteId = params.siteId) != null ? _params$siteId : BX.message && BX.message.SITE_ID ? BX.message.SITE_ID : 'none';
	  }
	  set(name, value) {
	    if (!window.localStorage) {
	      return false;
	    }
	    let encoded = value;
	    if (isNotEmptyString(value)) {
	      encoded = JSON.stringify(value);
	    }
	    return window.localStorage.setItem(this.getKey(name), encoded);
	  }
	  get(name, defaultValue = null) {
	    if (!window.localStorage) {
	      return defaultValue;
	    }
	    const result = window.localStorage.getItem(this.getKey(name));
	    if (result === null) {
	      return defaultValue;
	    }
	    return JSON.parse(result);
	  }
	  remove(name) {
	    if (!window.localStorage) {
	      return;
	    }
	    window.localStorage.removeItem(this.getKey(name));
	  }
	  getKey(name) {
	    return `bx-pull-${this.userId}-${this.siteId}-${name}`;
	  }
	  compareKey(eventKey, userKey) {
	    return eventKey === this.getKey(userKey);
	  }
	}

	/* eslint-disable @bitrix24/bitrix24-rules/no-typeof */
	const OFFLINE_STATUS_DELAY = 5000;
	const LS_SESSION = 'bx-pull-session';
	const LS_SESSION_CACHE_TIME = 20;
	var _status = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("status");
	var _emitter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("emitter");
	var _connector = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("connector");
	class PullClient {
	  /* eslint-disable no-param-reassign */
	  constructor(params = {}) {
	    var _params$guestMode, _params$guestUserId, _params$siteId, _params$restClient, _params$configTimesta;
	    Object.defineProperty(this, _status, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _emitter, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _connector, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter] = new Emitter({
	      logger: this.getLogger()
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector] = null;
	    if (params.restApplication) {
	      if (typeof params.configGetMethod === 'undefined') {
	        params.configGetMethod = 'pull.application.config.get';
	      }
	      if (typeof params.skipCheckRevision === 'undefined') {
	        params.skipCheckRevision = true;
	      }
	      if (typeof params.restApplication === 'string') {
	        params.siteId = params.restApplication;
	      }
	      params.serverEnabled = true;
	    }
	    this.context = 'master';
	    this.guestMode = (_params$guestMode = params.guestMode) != null ? _params$guestMode : getGlobalParam('pull_guest_mode', 'N') === 'Y';
	    this.guestUserId = (_params$guestUserId = params.guestUserId) != null ? _params$guestUserId : getGlobalParamInt('pull_guest_user_id', 0);
	    if (this.guestMode && this.guestUserId) {
	      this.userId = this.guestUserId;
	    } else {
	      var _params$userId;
	      this.userId = (_params$userId = params.userId) != null ? _params$userId : getGlobalParamInt('USER_ID', 0);
	    }
	    this.siteId = (_params$siteId = params.siteId) != null ? _params$siteId : getGlobalParam('SITE_ID', 'none');
	    this.restClient = (_params$restClient = params.restClient) != null ? _params$restClient : this.createRestClint();
	    this.customRestClient = Boolean(params.restClient);
	    this.enabled = typeof params.serverEnabled === 'undefined' ? typeof BX.message !== 'undefined' && BX.message.pull_server_enabled === 'Y' : params.serverEnabled === 'Y' || params.serverEnabled === true;
	    this.unloading = false;
	    this.starting = false;
	    this.connectionAttempt = 0;
	    this.connectionType = ConnectionType.WebSocket;
	    this.restartTimeout = null;
	    this.restoreWebSocketTimeout = null;
	    this.configGetMethod = typeof params.configGetMethod === 'string' ? params.configGetMethod : 'pull.config.get';
	    this.getPublicListMethod = typeof params.getPublicListMethod === 'string' ? params.getPublicListMethod : 'pull.channel.public.list';
	    this.skipStorageInit = params.skipStorageInit === true;
	    this.skipCheckRevision = params.skipCheckRevision === true;
	    this.tagWatcher = new TagWatcher({
	      restClient: this.restClient
	    });
	    this.configTimestamp = (_params$configTimesta = params.configTimestamp) != null ? _params$configTimesta : getGlobalParamInt('pull_config_timestamp', 0);
	    this.config = null;
	    this.storage = null;
	    if (this.userId && !this.skipStorageInit) {
	      this.storage = new StorageManager({
	        userId: this.userId,
	        siteId: this.siteId
	      });
	    }
	    this.notificationPopup = null;

	    // timers
	    this.checkInterval = null;
	    this.offlineTimeout = null;

	    // manual stop workaround
	    this.isManualDisconnect = false;
	    this.loggingEnabled = false;
	    this.status = PullStatus.Offline;
	  }
	  get connector() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector];
	  }
	  get session() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].session;
	  }
	  get status() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _status)[_status];
	  }
	  set status(status) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] === status) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] = status;
	    if (!this.enabled) {
	      return;
	    }
	    if (this.offlineTimeout) {
	      clearTimeout(this.offlineTimeout);
	      this.offlineTimeout = null;
	    }
	    if (status === PullStatus.Offline) {
	      this.sendPullStatusDelayed(status, OFFLINE_STATUS_DELAY);
	    } else {
	      this.sendPullStatus(status);
	    }
	  }
	  subscribe(params) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter].subscribe(params);
	  }
	  attachCommandHandler(handler) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter].attachCommandHandler(handler);
	  }
	  async start(startConfig) {
	    if (!this.enabled) {
	      throw new Error('Push & Pull server is disabled');
	    }
	    if (this.isConnected()) {
	      return true;
	    }
	    const sharedWorkerAllowed = getGlobalParamBool('shared_worker_allowed') && WorkerConnector.isSharedWorkerSupported();

	    /* if config exists - initialize PullConnector with this config, otherwise start SharedWorker */
	    if (startConfig) {
	      let restoreSession = true;
	      if (typeof startConfig.skipReconnectToLastSession !== 'undefined') {
	        restoreSession = !startConfig.skipReconnectToLastSession;
	        delete startConfig.skipReconnectToLastSession;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector] = this.createConnector(startConfig, restoreSession);
	    } else if (!this.guestMode && !this.customRestClient && sharedWorkerAllowed) {
	      babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector] = this.createWorkerConnector();
	    } else {
	      window.addEventListener('beforeunload', this.onBeforeUnload.bind(this));
	      window.addEventListener('offline', this.onOffline.bind(this));
	      window.addEventListener('online', this.onOnline.bind(this));
	      this.configHolder = this.createConfigHolder(this.restClient);
	      let config = null;
	      try {
	        config = await this.configHolder.loadConfig('client_start');
	        babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector] = this.createConnector(config, true);
	      } catch (e) {
	        console.error(`${getDateForLog()} Pull: load config`, e);
	        babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector] = this.createConnector(null, true);
	        this.scheduleRestart(CloseReasons.BACKEND_ERROR, 'backend error');
	        return false;
	      }
	    }
	    await babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].connect();
	    this.init();
	    this.tagWatcher.scheduleUpdate();
	    return true;
	  }
	  createConnector(config, restoreSession) {
	    return new pull_connector.Connector({
	      config,
	      restoreSession,
	      restClient: this.restClient,
	      getPublicListMethod: this.getPublicListMethod,
	      logger: this.getLogger(),
	      events: {
	        [pull_connector.ConnectorEvents.Message]: this.onMessage.bind(this),
	        [pull_connector.ConnectorEvents.ChannelReplaced]: this.onChannelReplaced.bind(this),
	        [pull_connector.ConnectorEvents.ConfigExpired]: this.onConfigExpired.bind(this),
	        [pull_connector.ConnectorEvents.ConnectionStatus]: this.onConnectionStatus.bind(this),
	        [pull_connector.ConnectorEvents.ConnectionError]: this.onConnectionError.bind(this),
	        [pull_connector.ConnectorEvents.RevisionChanged]: this.onRevisionChanged.bind(this)
	      }
	    });
	  }
	  createWorkerConnector() {
	    return new WorkerConnector({
	      bundleTimestamp: getGlobalParamInt('pull_worker_mtime', 0),
	      configTimestamp: this.configTimestamp,
	      events: {
	        [WorkerConnectorEvents.Message]: this.onMessage.bind(this),
	        [WorkerConnectorEvents.RevisionChanged]: this.onRevisionChanged.bind(this),
	        [WorkerConnectorEvents.ConnectionStatus]: this.onConnectionStatus.bind(this)
	      }
	    });
	  }
	  init() {
	    if (BX && BX.desktop) {
	      BX.addCustomEvent('BXLinkOpened', this.connect.bind(this));
	      BX.addCustomEvent('onDesktopReload', () => {
	        var _babelHelpers$classPr;
	        return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector]) == null ? void 0 : _babelHelpers$classPr.resetSession();
	      });
	      BX.desktop.addCustomEvent('BXLoginSuccess', this.onLoginSuccess.bind(this));
	    }
	  }
	  onLoginSuccess() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector] instanceof WorkerConnector) {
	      babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].onLoginSuccess();
	    } else {
	      this.restart(1000, 'desktop login');
	    }
	  }
	  createConfigHolder(restClient) {
	    return new pull_configholder.ConfigHolder({
	      restClient,
	      configGetMethod: this.configGetMethod,
	      events: {
	        [pull_configholder.ConfigHolderEvents.ConfigExpired]: e => {
	          this.logToConsole('Stale config detected. Restarting');
	          this.restart(CloseReasons.CONFIG_EXPIRED, 'config expired');
	        },
	        [pull_configholder.ConfigHolderEvents.RevisionChanged]: this.onRevisionChanged.bind(this)
	      }
	    });
	  }
	  createRestClint() {
	    const options = {};
	    if (this.guestMode && this.guestUserId !== 0) {
	      options.queryParams = {
	        pull_guest_id: this.guestUserId
	      };
	    }
	    return new MiniRest(options);
	  }
	  setLastMessageId(lastMessageId) {
	    this.session.mid = lastMessageId;
	  }
	  setPublicIds(publicIds) {
	    babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].setPublicIds(publicIds);
	  }

	  /**
	   * Send single message to the specified users.
	   *
	   * @param {integer[]} users User ids of the message receivers.
	   * @param {string} moduleId Name of the module to receive message,
	   * @param {string} command Command name.
	   * @param {object} params Command parameters.
	   * @param {integer} [expiry] Message expiry time in seconds.
	   * @return {Promise}
	   */
	  sendMessage(users, moduleId, command, params, expiry) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].sendMessage(users, moduleId, command, params, expiry);
	  }

	  /**
	   * Send single message to the specified public channels.
	   *
	   * @param {string[]} publicChannels Public ids of the channels to receive message.
	   * @param {string} moduleId Name of the module to receive message,
	   * @param {string} command Command name.
	   * @param {object} params Command parameters.
	   * @param {integer} [expiry] Message expiry time in seconds.
	   * @return {Promise}
	   */
	  sendMessageToChannels(publicChannels, moduleId, command, params, expiry) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].sendMessageToChannels(publicChannels, moduleId, command, params, expiry);
	  }

	  /**
	   * Sends batch of messages to the multiple public channels.
	   *
	   * @param {object[]} messageBatch Array of messages to send.
	   * @param  {int[]} messageBatch.userList User ids the message receivers.
	   * @param  {string[]|object[]} messageBatch.channelList Public ids of the channels to send messages.
	   * @param {string} messageBatch.moduleId Name of the module to receive message,
	   * @param {string} messageBatch.command Command name.
	   * @param {object} messageBatch.params Command parameters.
	   * @param {integer} [messageBatch.expiry] Message expiry time in seconds.
	   * @return void
	   */
	  async sendMessageBatch(messageBatch) {
	    try {
	      await babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].sendMessageBatch(messageBatch);
	    } catch (e) {
	      console.error(e);
	    }
	  }

	  /**
	   * @param userId {number}
	   * @param callback {UserStatusCallback}
	   * @returns {Promise}
	   */
	  async subscribeUserStatusChange(userId, callback) {
	    if (typeof userId !== 'number') {
	      throw new TypeError('userId must be a number');
	    }
	    await babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].subscribeUserStatusChange(userId);
	    babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter].addUserStatusCallback(userId, callback);
	  }

	  /**
	   * @param userId {number}
	   * @param callback {UserStatusCallback}
	   * @returns {Promise}
	   */
	  async unsubscribeUserStatusChange(userId, callback) {
	    if (typeof userId !== 'number') {
	      throw new TypeError('userId must be a number');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter].removeUserStatusCallback(userId, callback);
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter].hasUserStatusCallbacks(userId)) {
	      await babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].unsubscribeUserStatusChange(userId);
	    }
	  }
	  restoreUserStatusSubscription() {
	    for (const userId of babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter].getSubscribedUsersList()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].subscribeUserStatusChange(userId);
	    }
	  }
	  emitAuthError() {
	    if (BX && BX.onCustomEvent) {
	      BX.onCustomEvent(window, 'onPullError', ['AUTHORIZE_ERROR']);
	    }
	  }
	  isJsonRpc() {
	    return this.connector ? this.connector.isJsonRpc() : false;
	  }

	  /**
	   * Returns "last seen" time in seconds for the users. Result format: Object{userId: int}
	   * If the user is currently connected - will return 0.
	   * If the user if offline - will return diff between current timestamp and last seen timestamp in seconds.
	   * If the user was never online - the record for user will be missing from the result object.
	   *
	   * @param {integer[]} userList List of user ids.
	   * @returns {Promise}
	   */
	  getUsersLastSeen(userList) {
	    if (!isArray(userList) || !userList.every(item => typeof item === 'number')) {
	      throw new Error('userList must be an array of numbers');
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].getUsersLastSeen(userList);
	  }

	  /**
	   * Pings server. In case of success promise will be resolved, otherwise - rejected.
	   *
	   * @param {int} timeout Request timeout in seconds
	   * @returns {Promise}
	   */
	  ping(timeout) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].ping(timeout);
	  }

	  /**
	   * Returns list channels that the connection is subscribed to.
	   *
	   * @returns {Promise}
	   */
	  listChannels() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].listChannels();
	  }
	  scheduleRestart(disconnectCode, disconnectReason, restartDelay) {
	    clearTimeout(this.restartTimeout);
	    let delay = restartDelay;
	    if (!delay || delay < 1) {
	      delay = Math.ceil(Math.random() * 30) + 5;
	    }
	    this.restartTimeout = setTimeout(() => this.restart(disconnectCode, disconnectReason), delay * 1000);
	  }
	  async restart(disconnectCode = CloseReasons.NORMAL_CLOSURE, disconnectReason = 'manual restart') {
	    if (this.configHolder && babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector] instanceof pull_connector.Connector) {
	      this.logToConsole(`Pull: restarting with code ${disconnectCode}`);
	      this.disconnect(disconnectCode, disconnectReason);
	      const loadConfigReason = `${disconnectCode}_${disconnectReason.replaceAll(' ', '_')}`;
	      try {
	        const config = await this.configHolder.loadConfig(loadConfigReason);
	        babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].setConfig(config);
	      } catch (error) {
	        if ('status' in error && (error.status === 401 || error.status === 403)) {
	          this.emitAuthError();
	        }
	        this.scheduleRestart(CloseReasons.BACKEND_ERROR, 'backend error');
	        return;
	      }
	      try {
	        await babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].connect();
	      } catch {
	        babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].scheduleReconnect();
	      }
	      this.tagWatcher.scheduleUpdate();
	    } else {
	      this.logToConsole('Pull: restart request ignored in shared worker mode');
	    }
	  }
	  disconnect(disconnectCode, disconnectReason) {
	    var _babelHelpers$classPr2;
	    (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector]) == null ? void 0 : _babelHelpers$classPr2.disconnect(disconnectCode, disconnectReason);
	  }
	  stop(disconnectCode, disconnectReason) {
	    this.disconnect(disconnectCode, disconnectReason);
	  }

	  /**
	   * @returns {Promise}
	   */
	  connect() {
	    if (!this.enabled) {
	      return Promise.reject();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].connect();
	  }
	  logToConsole(message, ...params) {
	    if (this.loggingEnabled) {
	      // eslint-disable-next-line no-console
	      console.log(`${getDateForLog()}: ${message}`, ...params);
	    }
	  }
	  getLogger() {
	    return {
	      log: this.logToConsole.bind(this),
	      logForce: (message, ...params) => {
	        console.log(`${getDateForLog()}: ${message}`, ...params);
	      }
	    };
	  }
	  isConnected() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector] ? babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].isConnected() : false;
	  }

	  // can't be disabled anymore, now when we dropped support for nginx servers
	  isPublishingSupported() {
	    return true;
	  }

	  // can't be disabled anymore
	  isPublishingEnabled() {
	    return true;
	  }
	  onMessage(e) {
	    babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter].broadcastMessage(e.detail);
	  }
	  onChannelReplaced(e) {
	    this.logToConsole(`Pull: new config for ${e.detail.type} channel set\n`);
	  }
	  onConfigExpired() {
	    this.restart(CloseReasons.CONFIG_EXPIRED, 'config expired');
	  }
	  onConnectionStatus(e) {
	    this.status = e.detail.status;
	    if (this.status === PullStatus.Online && e.detail.connectionType === ConnectionType.WebSocket) {
	      this.restoreUserStatusSubscription();
	    }
	  }
	  onConnectionError(e) {
	    if (e.detail.code === CloseReasons.WRONG_CHANNEL_ID) {
	      this.scheduleRestart(CloseReasons.WRONG_CHANNEL_ID, 'wrong channel signature');
	    } else {
	      this.restart(e.detail.code, e.detail.reason);
	    }
	  }
	  onRevisionChanged(e) {
	    this.checkRevision(e.detail.revision);
	  }
	  onBeforeUnload() {
	    this.unloading = true;
	    const session = clone(this.session);
	    session.ttl = Date.now() + LS_SESSION_CACHE_TIME * 1000;
	    if (this.storage) {
	      try {
	        this.storage.set(LS_SESSION, JSON.stringify(session), LS_SESSION_CACHE_TIME);
	      } catch (e) {
	        console.error(`${getDateForLog()} Pull: Could not save session info in local storage. Error:`, e);
	      }
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].scheduleReconnect(15);
	  }
	  onOffline() {
	    this.disconnect('1000', 'offline');
	  }
	  onOnline() {
	    this.connect();
	  }
	  checkRevision(serverRevision) {
	    if (this.skipCheckRevision) {
	      return true;
	    }
	    if (serverRevision > 0 && serverRevision !== REVISION) {
	      this.enabled = false;
	      if (typeof BX.message !== 'undefined') {
	        this.showNotification(BX.message('PULL_OLD_REVISION'));
	      }
	      this.disconnect(CloseReasons.NORMAL_CLOSURE, 'check_revision');
	      if (typeof BX.onCustomEvent !== 'undefined') {
	        BX.onCustomEvent(window, 'onPullRevisionUp', [serverRevision, REVISION]);
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter].emit({
	        type: SubscriptionType.Revision,
	        data: {
	          server: serverRevision,
	          client: REVISION
	        }
	      });
	      this.logToConsole(`Pull revision changed from ${REVISION} to ${serverRevision}. Reload required`);
	      return false;
	    }
	    return true;
	  }
	  showNotification(text) {
	    if (this.notificationPopup || typeof BX.PopupWindow === 'undefined') {
	      return;
	    }
	    this.notificationPopup = new BX.PopupWindow('bx-notifier-popup-confirm', null, {
	      zIndex: 200,
	      autoHide: false,
	      closeByEsc: false,
	      overlay: true,
	      content: BX.create('div', {
	        props: {
	          className: 'bx-messenger-confirm'
	        },
	        html: text
	      }),
	      buttons: [new BX.PopupWindowButton({
	        text: BX.message('JS_CORE_WINDOW_CLOSE'),
	        className: 'popup-window-button-decline',
	        events: {
	          click: () => this.notificationPopup.close()
	        }
	      })],
	      events: {
	        onPopupClose: () => this.notificationPopup.destroy(),
	        onPopupDestroy: () => {
	          this.notificationPopup = null;
	        }
	      }
	    });
	    this.notificationPopup.show();
	  }
	  getServerMode() {
	    switch (babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].getServerMode()) {
	      case ServerMode.Shared:
	        return 'cloud';
	      case ServerMode.Personal:
	        return 'local';
	      default:
	        return 'n/a';
	    }
	  }
	  getDebugInfo() {
	    var _babelHelpers$classPr3, _babelHelpers$classPr4, _this$session, _this$session2, _this$session$history, _this$session3;
	    if (!JSON || !JSON.stringify) {
	      return false;
	    }
	    let configDump = {
	      'Config error': 'config is not loaded'
	    };
	    if (this.config && this.config.channels) {
	      configDump = {
	        ChannelID: this.config.channels.private ? this.config.channels.private.id : 'n/a',
	        ChannelDie: this.config.channels.private ? this.config.channels.private.end : 'n/a',
	        ChannelDieShared: 'shared' in this.config.channels ? this.config.channels.shared.end : 'n/a'
	      };
	    }
	    let websocketMode = '-';
	    if (babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector] instanceof pull_connector.Connector && babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].isWebSocketConnected()) {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].isJsonRpc()) {
	        websocketMode = 'json-rpc';
	      } else {
	        websocketMode = babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].isProtobufSupported() ? 'protobuf' : 'text';
	      }
	    }
	    return {
	      UserId: this.userId + (this.userId > 0 ? '' : '(guest)'),
	      'Guest userId': this.guestMode && this.guestUserId !== 0 ? this.guestUserId : '-',
	      'Browser online': navigator.onLine ? 'Y' : 'N',
	      Connect: this.isConnected() ? 'Y' : 'N',
	      'Server type': this.getServerMode(),
	      'WebSocket supported': 'Y',
	      'WebSocket connected': (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector]) != null && _babelHelpers$classPr3.isWebSocketConnected() ? 'Y' : 'N',
	      'WebSocket mode': websocketMode,
	      'Try connect': (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector]) != null && _babelHelpers$classPr4.reconnectTimeout ? 'Y' : 'N',
	      'Try number': this.connectionAttempt,
	      Path: babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector] ? babelHelpers.classPrivateFieldLooseBase(this, _connector)[_connector].getConnectionPath() : '-',
	      ...configDump,
	      'Last message': ((_this$session = this.session) == null ? void 0 : _this$session.mid) > 0 ? (_this$session2 = this.session) == null ? void 0 : _this$session2.mid : '-',
	      'Session history': (_this$session$history = (_this$session3 = this.session) == null ? void 0 : _this$session3.history) != null ? _this$session$history : null,
	      'Watch tags': this.tagWatcher.queue
	    };
	  }
	  enableLogging(loggingFlag = true) {
	    this.loggingEnabled = loggingFlag;
	  }
	  capturePullEvent(debugFlag = true) {
	    babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter].capturePullEvent(debugFlag);
	  }
	  sendPullStatusDelayed(status, delay) {
	    if (this.offlineTimeout) {
	      clearTimeout(this.offlineTimeout);
	    }
	    this.offlineTimeout = setTimeout(() => {
	      this.offlineTimeout = null;
	      this.sendPullStatus(status);
	    }, delay);
	  }
	  sendPullStatus(status) {
	    if (this.unloading) {
	      return;
	    }
	    if (typeof BX.onCustomEvent !== 'undefined') {
	      BX.onCustomEvent(window, 'onPullStatus', [status]);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter].emit({
	      type: SubscriptionType.Status,
	      data: {
	        status
	      }
	    });
	  }
	  extendWatch(tag, force) {
	    this.tagWatcher.extend(tag, force);
	  }
	  clearWatch(tagId) {
	    this.tagWatcher.clear(tagId);
	  }

	  // old functions, not used anymore.
	  setPrivateVar() {}
	  returnPrivateVar() {}
	  expireConfig() {}
	  updateChannelID() {}
	  tryConnect() {}
	  tryConnectDelay() {}
	  tryConnectSet() {}
	  updateState() {}
	  setUpdateStateStepCount() {}
	  supportWebSocket() {
	    return this.isWebSocketSupported();
	  }
	  isWebSoketConnected() {
	    return this.isConnected() && this.connectionType === ConnectionType.WebSocket;
	  }
	  getPullServerStatus() {
	    return this.isConnected();
	  }
	  closeConfirm() {
	    if (this.notificationPopup) {
	      this.notificationPopup.destroy();
	    }
	  }
	}
	PullClient.PullStatus = PullStatus;
	PullClient.SubscriptionType = SubscriptionType;
	PullClient.CloseReasons = CloseReasons;
	PullClient.StorageManager = StorageManager;
	function getGlobalParam(name, defaultValue) {
	  if (typeof BX.message !== 'undefined' && name in BX.message) {
	    return BX.message[name];
	  }
	  return defaultValue;
	}
	function getGlobalParamInt(name, defaultValue) {
	  if (typeof BX.message !== 'undefined' && name in BX.message) {
	    return parseInt(BX.message[name], 10);
	  }
	  return defaultValue;
	}
	function getGlobalParamBool(name, defaultValue) {
	  if (typeof BX.message !== 'undefined' && name in BX.message) {
	    return BX.message[name] === 'Y';
	  }
	  return defaultValue;
	}

	/**
	 * Bitrix Push & Pull
	 * Pull client
	 *
	 * @package bitrix
	 * @subpackage pull
	 * @copyright 2001-2019 Bitrix
	 */
	if (!globalThis.BX) {
	  globalThis.BX = {};
	}
	if (!BX.PULL) {
	  BX.PULL = new PullClient();
	}
	BX.PullClient = PullClient;

	exports.PullClient = PullClient;

}((this.BX = this.BX || {}),BX.Pull.Util,BX.Pull,BX.Pull));
//# sourceMappingURL=pull.client.js.map
