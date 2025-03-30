/* eslint-disable */
this.BX = this.BX || {};
(function (exports) {
	'use strict';

	/* eslint-disable @bitrix24/bitrix24-rules/no-typeof */
	function isString(item) {
	  return item === '' ? true : item ? typeof item === 'string' || item instanceof String : false;
	}
	function isArray(item) {
	  return item && Object.prototype.toString.call(item) === '[object Array]';
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

	exports.RpcError = RpcError;
	exports.JsonRpc = JsonRpc;

}((this.BX.Pull = this.BX.Pull || {})));
//# sourceMappingURL=pull.jsonrpc.bundle.js.map
