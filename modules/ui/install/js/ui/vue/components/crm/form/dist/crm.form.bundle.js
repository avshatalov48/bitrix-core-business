this.BX = this.BX || {};
this.BX.Ui = this.BX.Ui || {};
this.BX.Ui.Vue = this.BX.Ui.Vue || {};
this.BX.Ui.Vue.Components = this.BX.Ui.Vue.Components || {};
(function (exports,ui_vue) {
	'use strict';

	var loadAppPromise = null;
	ui_vue.Vue.component('bx-crm-form', {
	  props: {
	    id: {
	      type: String,
	      required: true
	    },
	    sec: {
	      type: String,
	      required: true
	    },
	    lang: {
	      type: String,
	      required: true,
	      default: 'en'
	    },
	    address: {
	      type: String,
	      required: true,
	      default: function _default() {
	        return window.location.origin;
	      }
	    },
	    design: {
	      type: Object,
	      required: false,
	      default: function _default() {
	        return {
	          compact: true
	        };
	      }
	    }
	  },
	  data: function data() {
	    return {
	      message: '',
	      obj: {}
	    };
	  },
	  beforeDestroy: function beforeDestroy() {
	    if (this.obj.instance) {
	      this.obj.instance.destroy();
	    }
	  },
	  mounted: function mounted() {
	    var _this = this;

	    var loadForm = function loadForm() {
	      _this.message = '';
	      _this.obj.config.data.node = _this.$el;
	      _this.obj.config.data.design = babelHelpers.objectSpread({}, _this.obj.config.data.design, _this.design);
	      _this.obj.instance = window.b24form.App.createForm24(_this.obj.config, _this.obj.config.data);

	      _this.obj.instance.subscribeAll(function (data, instance, type) {
	        _this.$emit('form:' + type, data);
	      });
	    };

	    this.message = '...Loading';
	    var promise = null;

	    if (window.fetch) {
	      var formData = new FormData();
	      formData.append('id', this.id);
	      formData.append('sec', this.sec);
	      promise = fetch(this.address + "/bitrix/services/main/ajax.php?action=crm.site.form.get", {
	        method: 'POST',
	        body: formData,
	        mode: "cors"
	      });
	    } else {
	      this.message = 'error';
	      return;
	    }

	    promise.then(function (response) {
	      return response.json();
	    }).then(function (data) {
	      if (data.error) {
	        throw new Error(data.error_description);
	      }

	      _this.obj.config = data.result.config;

	      if (window.b24form && window.b24form.App) {
	        loadForm();
	        return;
	      }

	      if (!loadAppPromise) {
	        loadAppPromise = new Promise(function (resolve, reject) {
	          var node = document.createElement('script');
	          node.src = data.result.loader.app.link;
	          node.onload = resolve;
	          node.onerror = reject;
	          document.head.appendChild(node);
	        });
	      }

	      loadAppPromise.then(loadForm).catch(function (e) {
	        _this.message = 'App load failed:' + e;
	      });
	    }).catch(function (error) {
	      _this.message = error;
	    });
	  },
	  template: "\n\t\t<div>\n\t\t\t<div v-if=\"message\">{{ message }}</div>\n\t\t</div>\n\t"
	});

}((this.BX.Ui.Vue.Components.Crm = this.BX.Ui.Vue.Components.Crm || {}),BX));
//# sourceMappingURL=crm.form.bundle.js.map
