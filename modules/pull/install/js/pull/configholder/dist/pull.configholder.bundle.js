/* eslint-disable */
this.BX = this.BX || {};
(function (exports) {
	'use strict';

	/* eslint-disable @bitrix24/bitrix24-rules/no-typeof */
	function getTimestamp() {
	  return Date.now();
	}
	function isNumber(item) {
	  return typeof item === 'number' && Number.isFinite(item);
	}
	function isPlainObject(item) {
	  return Boolean(item) && typeof item === 'object' && item.constructor === Object;
	}

	const REVISION = 19; // api revision - check module/pull/include.php

	/* eslint-disable @bitrix24/bitrix24-rules/no-native-events-binding */
	const CONFIG_CHECK_INTERVAL = 60000;
	const ConfigHolderEvents = {
	  ConfigExpired: 'configExpired',
	  RevisionChanged: 'revisionChanged'
	};
	class ConfigHolder extends EventTarget {
	  constructor(options = {}) {
	    super();
	    this.configGetMethod = 'pull.config.get';
	    if (options.configGetMethod) {
	      this.configGetMethod = options.configGetMethod;
	    }
	    this.restClient = options.restClient;
	    for (const eventName of Object.keys(options.events || {})) {
	      this.addEventListener(eventName, options.events[eventName]);
	    }
	  }
	  loadConfig(logTag) {
	    this.stopCheckConfig();
	    return new Promise((resolve, reject) => {
	      this.restClient.callMethod(this.configGetMethod, {
	        CACHE: 'N'
	      }, undefined, undefined, logTag).then(response => {
	        const data = response.data();
	        const timeShift = Math.floor((getTimestamp() - new Date(data.serverTime).getTime()) / 1000);
	        delete data.serverTime;
	        this.config = {
	          ...data
	        };
	        this.config.server.timeShift = timeShift;
	        this.startCheckConfig();
	        resolve(this.config);
	      }).catch(response => {
	        this.config = undefined;
	        const error = response.error();
	        if (error.getError().error === 'AUTHORIZE_ERROR' || error.getError().error === 'WRONG_AUTH_TYPE') {
	          error.status = 403;
	        }
	        reject(error);
	      });
	    });
	  }
	  startCheckConfig() {
	    if (this.checkInterval) {
	      clearInterval(this.checkInterval);
	    }
	    this.checkInterval = setInterval(() => this.checkConfig(), CONFIG_CHECK_INTERVAL);
	  }
	  stopCheckConfig() {
	    if (this.checkInterval) {
	      clearInterval(this.checkInterval);
	    }
	    this.checkInterval = null;
	  }
	  checkConfig() {
	    if (!this.isConfigActual(this.config)) {
	      this.dispatchEvent(new CustomEvent(ConfigHolderEvents.ConfigExpired));
	    } else if (this.config.api.revision_web !== REVISION) {
	      this.dispatchEvent(new CustomEvent(ConfigHolderEvents.RevisionChanged, {
	        detail: {
	          revision: this.config.api.revision_web
	        }
	      }));
	    }
	  }
	  isConfigActual(config) {
	    if (!isPlainObject(config)) {
	      return false;
	    }
	    if (config.server.config_timestamp < this.configTimestamp) {
	      return false;
	    }
	    const now = new Date();
	    if (isNumber(config.exp) && config.exp > 0 && config.exp < now.getTime() / 1000) {
	      return false;
	    }
	    const channelTypes = Object.keys(config.channels || {});
	    if (channelTypes.length === 0) {
	      return false;
	    }
	    for (const channelType of channelTypes) {
	      const channel = config.channels[channelType];
	      const channelEnd = new Date(channel.end);
	      if (channelEnd < now) {
	        return false;
	      }
	    }
	    return true;
	  }
	  dispose() {
	    this.stopCheckConfig();
	  }
	}

	exports.ConfigHolderEvents = ConfigHolderEvents;
	exports.ConfigHolder = ConfigHolder;

}((this.BX.Pull = this.BX.Pull || {})));
//# sourceMappingURL=pull.configholder.bundle.js.map
