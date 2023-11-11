/* eslint-disable */
(function (exports,ui_designTokens,ui_vue3,pull_client,main_core) {
	'use strict';

	var HIDE_TIMEOUT = 4000;
	var STATUS_CHANGE_DEFAULT_TIMEOUT = 500;
	var STATUS_CHANGE_CONNECTING_TIMEOUT = 5000;
	var STATUS_CHANGE_OFFLINE_TIMEOUT = 1000;

	// @vue/component
	var PullStatus = {
	  name: 'PullStatus',
	  props: {
	    canReconnect: {
	      type: Boolean,
	      "default": false
	    }
	  },
	  emits: ['reconnect'],
	  data: function data() {
	    return {
	      status: pull_client.PullClient.PullStatus.Online,
	      showed: null
	    };
	  },
	  computed: {
	    containerClass: function containerClass() {
	      var result = [];
	      var visibilityClass = '';
	      if (this.showed === true) {
	        visibilityClass = '--show';
	      } else if (this.showed === false) {
	        visibilityClass = '--hide';
	      }
	      var statusClass = "--".concat(this.status);
	      result.push(visibilityClass, statusClass);
	      return result;
	    },
	    connectionText: function connectionText() {
	      var result = '';
	      if (this.status === pull_client.PullClient.PullStatus.Online) {
	        result = this.$Bitrix.Loc.getMessage('BX_PULL_STATUS_ONLINE');
	      } else if (this.status === pull_client.PullClient.PullStatus.Offline) {
	        result = this.$Bitrix.Loc.getMessage('BX_PULL_STATUS_OFFLINE');
	      } else if (this.status === pull_client.PullClient.PullStatus.Connecting) {
	        result = this.$Bitrix.Loc.getMessage('BX_PULL_STATUS_CONNECTING');
	      }
	      return result;
	    },
	    button: function button() {
	      if (this.status === pull_client.PullClient.PullStatus.Online) {
	        return null;
	      }
	      var hotkey = '';
	      var name = '';
	      if (this.canReconnect) {
	        name = this.$Bitrix.Loc.getMessage('BX_PULL_STATUS_BUTTON_RECONNECT');
	      } else {
	        hotkey = main_core.Browser.isMac() ? '&#8984;+R' : 'Ctrl+R';
	        name = this.$Bitrix.Loc.getMessage('BX_PULL_STATUS_BUTTON_RELOAD');
	      }
	      return {
	        title: name,
	        key: hotkey
	      };
	    }
	  },
	  watch: {
	    status: function status() {
	      var _this = this;
	      clearTimeout(this.hideTimeout);
	      if (this.status !== pull_client.PullClient.PullStatus.Online) {
	        return;
	      }
	      this.hideTimeout = setTimeout(function () {
	        _this.showed = false;
	      }, HIDE_TIMEOUT);
	    }
	  },
	  created: function created() {
	    this.unsubscribeFunction = function () {};
	    this.initEvents();
	  },
	  beforeUnmount: function beforeUnmount() {
	    this.destroyEvents();
	  },
	  methods: {
	    initEvents: function initEvents() {
	      if (this.$Bitrix.PullClient.get()) {
	        this.subscribeToPullStatus();
	      }
	      this.$Bitrix.eventEmitter.subscribe(ui_vue3.BitrixVue.events.pullClientChange, this.subscribeToPullStatus);
	    },
	    destroyEvents: function destroyEvents() {
	      this.unsubscribeFunction();
	      this.$Bitrix.eventEmitter.unsubscribe(ui_vue3.BitrixVue.events.pullClientChange, this.subscribeToPullStatus);
	    },
	    subscribeToPullStatus: function subscribeToPullStatus() {
	      var _this2 = this;
	      this.unsubscribeFunction();
	      this.unsubscribeFunction = this.$Bitrix.PullClient.get().subscribe({
	        type: pull_client.PullClient.SubscriptionType.Status,
	        callback: function callback(event) {
	          return _this2.onStatusChange(event.status);
	        }
	      });
	    },
	    reconnect: function reconnect() {
	      if (this.canReconnect) {
	        this.$emit('reconnect');
	      } else {
	        location.reload();
	      }
	    },
	    onStatusChange: function onStatusChange(status) {
	      var _this3 = this;
	      clearTimeout(this.setStatusTimeout);
	      if (this.status === status) {
	        return;
	      }
	      var validStatuses = [pull_client.PullClient.PullStatus.Online, pull_client.PullClient.PullStatus.Offline, pull_client.PullClient.PullStatus.Connecting];
	      if (!validStatuses.includes(status)) {
	        return;
	      }
	      var timeout = STATUS_CHANGE_DEFAULT_TIMEOUT;
	      if (status === pull_client.PullClient.PullStatus.Connecting) {
	        timeout = STATUS_CHANGE_CONNECTING_TIMEOUT;
	      } else if (status === pull_client.PullClient.PullStatus.Offline) {
	        timeout = STATUS_CHANGE_OFFLINE_TIMEOUT;
	      }
	      this.setStatusTimeout = setTimeout(function () {
	        _this3.status = status;
	        _this3.showed = true;
	      }, timeout);
	    }
	  },
	  template: "\n\t\t<div class=\"bx-pull-vue3-status\" :class=\"containerClass\">\n\t\t\t<div class=\"bx-pull-vue3-status-wrap\">\n\t\t\t\t<span class=\"bx-pull-vue3-status-text\">{{ connectionText }}</span>\n\t\t\t\t<span v-if=\"button\" class=\"bx-pull-vue3-status-button\" @click=\"reconnect\">\n\t\t\t\t\t<span class=\"bx-pull-vue3-status-button-title\">{{ button.title }}</span>\n\t\t\t\t\t<span class=\"bx-pull-vue3-status-button-key\" v-html=\"button.key\"></span>\n\t\t\t\t</span>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	exports.PullStatus = PullStatus;

}((this.window = this.window || {}),BX,BX.Vue3,BX,BX));
//# sourceMappingURL=status.bundle.js.map
