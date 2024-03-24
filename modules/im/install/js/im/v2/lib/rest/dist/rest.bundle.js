/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_core_events,im_v2_const,im_v2_application_core) {
	'use strict';

	const INVALID_AUTH_ERROR_CODE = 'invalid_authentication';
	let retryAllowed = true;
	const runAction = (action, config = {}) => {
	  const preparedConfig = {
	    ...config,
	    data: prepareRequestData(config.data)
	  };
	  return new Promise((resolve, reject) => {
	    main_core.ajax.runAction(action, preparedConfig).then(response => {
	      retryAllowed = true;
	      return resolve(response.data);
	    }).catch(response => {
	      if (needRetryRequest(response.errors)) {
	        retryAllowed = false;
	        return handleErrors(action, preparedConfig, response);
	      }
	      return reject(response.errors);
	    });
	  });
	};
	const handleErrors = async (action, config, response) => {
	  await main_core_events.EventEmitter.emitAsync(im_v2_const.EventType.request.onAuthError, {
	    errors: response.errors
	  });
	  return runAction(action, config);
	};
	const needRetryRequest = responseErrors => {
	  if (!retryAllowed) {
	    return false;
	  }
	  return responseErrors.some(error => error.code === INVALID_AUTH_ERROR_CODE);
	};
	const callBatch = query => {
	  const preparedQuery = {};
	  const methodsToCall = new Set();
	  Object.entries(query).forEach(([method, params]) => {
	    methodsToCall.add(method);
	    preparedQuery[method] = [method, params];
	  });
	  return new Promise((resolve, reject) => {
	    im_v2_application_core.Core.getRestClient().callBatch(preparedQuery, result => {
	      const data = {};
	      for (const method of methodsToCall) {
	        const methodResult = result[method];
	        if (methodResult.error()) {
	          const {
	            error: code,
	            error_description: description
	          } = methodResult.error().ex;
	          reject({
	            method,
	            code,
	            description
	          });
	          break;
	        }
	        data[method] = methodResult.data();
	      }
	      return resolve(data);
	    });
	  });
	};
	const prepareRequestData = data => {
	  if (data instanceof FormData) {
	    return data;
	  }
	  if (!main_core.Type.isObjectLike(data)) {
	    return {};
	  }
	  const preparedData = {};
	  for (const [key, value] of Object.entries(data)) {
	    let preparedValue = value;
	    if (main_core.Type.isBoolean(value)) {
	      preparedValue = value === true ? 'Y' : 'N';
	    }
	    preparedData[key] = preparedValue;
	  }
	  return preparedData;
	};

	exports.runAction = runAction;
	exports.callBatch = callBatch;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Application));
//# sourceMappingURL=rest.bundle.js.map
