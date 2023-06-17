this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,im_v2_application_core) {
	'use strict';

	const runAction = (action, config = {}) => {
	  return new Promise((resolve, reject) => {
	    main_core.ajax.runAction(action, config).then(response => {
	      return resolve(response.data);
	    }).catch(response => {
	      return reject(response.errors);
	    });
	  });
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

	exports.runAction = runAction;
	exports.callBatch = callBatch;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Messenger.v2.Application));
//# sourceMappingURL=rest.bundle.js.map
