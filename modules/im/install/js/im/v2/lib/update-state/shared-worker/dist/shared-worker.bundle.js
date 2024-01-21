/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports) {
	'use strict';

	let updateRequestStarted = false;
	let csrfToken = '';
	let sessionTime = 0;
	let setIntervalId = null;
	const browserInstances = [];
	const BASE_ENDPOINT = '/bitrix/services/main/ajax.php?action=';
	const UPDATE_STATE_ACTION = 'im.v2.UpdateState.getStateData';
	const requestDebounced = debounce(requestUpdate, 5000);

	// eslint-disable-next-line no-undef
	onconnect = event => {
	  const port = event.ports[0];
	  browserInstances.push(port);
	  port.onmessage = onMessage;
	  port.onmessageerror = onError;
	};
	function startUpdateInterval(data) {
	  updateRequestStarted = true;
	  setCsrfToken(data.csrfToken);
	  setSessionTime(data.sessionTime);
	  if (data.force) {
	    requestDebounced();
	  }
	  setIntervalId = setInterval(async () => {
	    await requestUpdate();
	  }, sessionTime);
	}
	async function requestUpdate() {
	  try {
	    const response = await fetch(getUpdateUrl(), getRequestOptions());
	    const handledResponse = await handleResponse(response);
	    if (handledResponse) {
	      browserInstances.forEach(instance => {
	        instance.postMessage({
	          response: handledResponse.data,
	          csrfToken: getCsrfToken()
	        });
	      });
	    }
	  } catch (error) {
	    console.error('updateState: request error', error);
	  }
	  return null;
	}
	async function handleResponse(response) {
	  try {
	    const responseJson = await response.json();
	    if (responseJson.errors && responseJson.errors.length > 0) {
	      const csrfTokenFromError = getCsrfTokenFromError(responseJson);
	      setCsrfToken(csrfTokenFromError || getCsrfToken());
	    }
	    return responseJson;
	  } catch (jsonError) {
	    console.error('updateState: json error', jsonError);
	  }
	  return null;
	}
	function getCsrfTokenFromError(responseJson) {
	  let newCsrfToken = null;
	  responseJson.errors.forEach(error => {
	    var _error$customData, _error$customData2;
	    if (!((_error$customData = error.customData) != null && _error$customData.csrf) || ((_error$customData2 = error.customData) == null ? void 0 : _error$customData2.csrf.length) === 0) {
	      return;
	    }
	    newCsrfToken = error.customData.csrf;
	  });
	  return newCsrfToken;
	}
	function getUpdateUrl() {
	  return `${BASE_ENDPOINT}${UPDATE_STATE_ACTION}`;
	}
	function getRequestOptions() {
	  return {
	    method: 'POST',
	    credentials: 'same-origin',
	    headers: {
	      'X-Bitrix-Csrf-Token': getCsrfToken()
	    }
	  };
	}
	function setCsrfToken(token) {
	  csrfToken = token;
	}
	function getCsrfToken() {
	  return csrfToken;
	}
	function setSessionTime(time) {
	  sessionTime = time;
	}
	function debounce(func, wait = 0) {
	  let timeoutId = null;
	  return function debounced(...args) {
	    // eslint-disable-next-line @bitrix24/bitrix24-rules/no-typeof
	    if (typeof timeoutId !== 'undefined' && timeoutId !== null) {
	      clearTimeout(timeoutId);
	    }
	    timeoutId = setTimeout(() => {
	      func(...args);
	    }, wait);
	  };
	}
	function onMessage({
	  data
	}) {
	  if (updateRequestStarted) {
	    clearInterval(setIntervalId);
	  }
	  startUpdateInterval(data);
	}
	function onError(error) {
	  console.error('shared worker: messageerror', error);
	}

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {})));
//# sourceMappingURL=shared-worker.bundle.js.map
