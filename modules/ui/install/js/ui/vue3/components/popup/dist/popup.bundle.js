/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.Vue3 = this.BX.UI.Vue3 || {};
(function (exports,main_core,main_popup) {
	'use strict';

	let _ = t => t,
	  _t;
	const Popup = {
	  name: 'Popup',
	  emits: ['close'],
	  props: {
	    options: {
	      /** @type PopupOptions */
	      type: Object,
	      default: {}
	    }
	  },
	  data() {
	    return {
	      isPopupShown: false,
	      popupContentId: `ui-vue3-popup-${main_core.Text.getRandom()}`
	    };
	  },
	  popup: null,
	  mounted() {
	    var _this$options$events;
	    const eventsFromOptions = (_this$options$events = this.options.events) != null ? _this$options$events : {};
	    this.popup = new main_popup.Popup({
	      ...this.options,
	      cacheable: false,
	      content: main_core.Tag.render(_t || (_t = _`<div id="${0}"></div>`), this.popupContentId),
	      events: {
	        ...eventsFromOptions,
	        onPopupShow: (...args) => {
	          // WARNING! Teleport should always be mounted AFTER the target node is rendered in DOM
	          this.isPopupShown = true;

	          // adjust position on page after vue has rendered popup content
	          void this.$nextTick(() => {
	            this.popup.adjustPosition();
	          });
	          if (main_core.Type.isFunction(eventsFromOptions.onPopupShow)) {
	            eventsFromOptions.onPopupShow(...args);
	          }
	        },
	        onPopupAfterClose: (...args) => {
	          this.isPopupShown = false;
	          this.$emit('close');
	          if (main_core.Type.isFunction(eventsFromOptions.onPopupAfterClose)) {
	            eventsFromOptions.onPopupAfterClose(...args);
	          }
	        }
	      }
	    });
	    this.popup.show();
	  },
	  beforeUnmount() {
	    var _this$popup;
	    (_this$popup = this.popup) == null ? void 0 : _this$popup.close();
	  },
	  template: `
		<Teleport v-if="isPopupShown" :to="'#' + popupContentId">
			<slot/>
		</Teleport>
	`
	};

	exports.Popup = Popup;

}((this.BX.UI.Vue3.Components = this.BX.UI.Vue3.Components || {}),BX,BX.Main));
//# sourceMappingURL=popup.bundle.js.map
