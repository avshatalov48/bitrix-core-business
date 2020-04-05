(function (exports,ui_vue_directives_lazyload,ui_dexie,ui_vue,rest_client) {
	'use strict';

	var SmileManager =
	/*#__PURE__*/
	function () {
	  function SmileManager(restClient) {
	    babelHelpers.classCallCheck(this, SmileManager);

	    if (typeof restClient !== 'undefined') {
	      this.restClient = restClient;
	    } else {
	      this.restClient = new BX.RestClient();
	    }

	    this.db = new ui_dexie.Dexie('bx-ui-smiles');
	    this.db.version(1).stores({
	      sets: "id, parentId, name, type, image",
	      smiles: "id, setId, name, image, typing, width, height, originalWidth, originalHeight, definition"
	    });
	  }

	  babelHelpers.createClass(SmileManager, [{
	    key: "loadFromCache",
	    value: function loadFromCache() {
	      var _this = this;

	      var promise = new BX.Promise();
	      var sets = [];
	      var smiles = [];
	      this.db.transaction('r', this.db.sets, this.db.smiles, function () {
	        _this.db.sets.each(function (set) {
	          return _this.db.smiles.where('setId').equals(set.id).first().then(function (smile) {
	            sets.push(babelHelpers.objectSpread({}, set, {
	              image: smile.image
	            }));
	          }).catch(function (error) {
	            return promise.reject(error);
	          });
	        }).then(function () {
	          return _this.db.smiles.where('setId').equals(sets[0].id).each(function (smile) {
	            smiles.push(smile);
	          });
	        }).then(function () {
	          var promiseResult = {
	            sets: sets,
	            smiles: smiles
	          };
	          promise.resolve(promiseResult);
	        }).catch(function (error) {
	          return promise.reject(error);
	        });
	      });
	      return promise;
	    }
	  }, {
	    key: "loadFromServer",
	    value: function loadFromServer() {
	      var _this2 = this;

	      var promise = new BX.Promise();
	      this.restClient.callMethod('smile.get').then(function (result) {
	        var sets = [];
	        var smiles = [];
	        var answer = result.data();
	        var setImage = {};
	        answer.smiles = answer.smiles.map(function (smile) {
	          if (!setImage[smile.setId]) {
	            setImage[smile.setId] = smile.image;
	          }

	          var originalWidth = smile.width;

	          if (smile.definition == 'HD') {
	            originalWidth = originalWidth * 2;
	          } else if (smile.definition == 'UHD') {
	            originalWidth = originalWidth * 4;
	          }

	          var originalHeight = smile.height;

	          if (smile.definition == 'HD') {
	            originalHeight = originalHeight * 2;
	          } else if (smile.definition == 'UHD') {
	            originalHeight = originalHeight * 4;
	          }

	          return babelHelpers.objectSpread({}, smile, {
	            originalWidth: originalWidth,
	            originalHeight: originalHeight
	          });
	        });
	        answer.sets.forEach(function (set) {
	          sets.push(babelHelpers.objectSpread({}, set, {
	            image: setImage[set.id]
	          }));
	        });
	        answer.smiles.forEach(function (smile) {
	          if (smile.setId == sets[0].id) {
	            smiles.push(smile);
	          }
	        });
	        var promiseResult = {
	          sets: sets,
	          smiles: smiles
	        };
	        promise.resolve(promiseResult);

	        _this2.db.smiles.clear().then(function () {
	          return _this2.db.sets.clear().then(function () {
	            _this2.db.sets.bulkAdd(sets);

	            _this2.db.smiles.bulkAdd(answer.smiles);
	          }).catch(function (error) {
	            return promise.reject(error);
	          });
	        }).catch(function (error) {
	          return promise.reject(error);
	        });
	      }).catch(function (error) {
	        return promise.reject(error);
	      });
	      return promise;
	    }
	  }, {
	    key: "changeSet",
	    value: function changeSet(setId) {
	      var promise = new BX.Promise();
	      this.db.smiles.where('setId').equals(setId).toArray(function (smiles) {
	        promise.resolve(smiles);
	      }).catch(function (error) {
	        return promise.reject(error);
	      });
	      return promise;
	    }
	  }]);
	  return SmileManager;
	}();

	/**
	 * Bitrix UI
	 * Smiles Vue component
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.Vue.component('bx-smiles', {
	  /**
	   * @emits 'selectSmile' {text: string}
	   * @emits 'selectSet' {setId: number}
	   */
	  data: function data() {
	    return {
	      smiles: [],
	      sets: []
	    };
	  },
	  created: function created() {
	    var _this = this;

	    this.setSelected = 0;
	    this.serverLoad = false;
	    var restClient = this.$root.$bitrixRestClient || rest_client.rest;
	    this.smilesController = new SmileManager(restClient);
	    this.smilesController.loadFromCache().then(function (result) {
	      if (_this.serverLoad) return true;
	      _this.smiles = result.smiles;
	      _this.sets = result.sets.map(function (element, index) {
	        element.selected = _this.setSelected === index;
	        return element;
	      });
	    });
	    this.smilesController.loadFromServer().then(function (result) {
	      _this.smiles = result.smiles;
	      _this.sets = result.sets.map(function (element, index) {
	        element.selected = _this.setSelected === index;
	        return element;
	      });
	    });
	  },
	  methods: {
	    selectSet: function selectSet(setId) {
	      var _this2 = this;

	      this.$emit('selectSet', {
	        setId: setId
	      });
	      this.smilesController.changeSet(setId).then(function (result) {
	        _this2.smiles = result;

	        _this2.sets.map(function (set) {
	          set.selected = set.id === setId;

	          if (set.selected) {
	            _this2.setSelected = setId;
	          }

	          return set;
	        });

	        _this2.$refs.elements.scrollTop = 0;
	      });
	    },
	    selectSmile: function selectSmile(text) {
	      this.$emit('selectSmile', {
	        text: ' ' + text + ' '
	      });
	    }
	  },
	  template: "\n\t\t<div class=\"bx-ui-smiles-box\">\n\t\t\t<div class=\"bx-ui-smiles-elements-wrap\" ref=\"elements\">\n\t\t\t\t<template v-if=\"!smiles.length\">\n\t\t\t\t\t<svg class=\"bx-ui-smiles-loading-circular\" viewBox=\"25 25 50 50\">\n\t\t\t\t\t\t<circle class=\"bx-ui-smiles-loading-path\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-miterlimit=\"10\"/>\n\t\t\t\t\t\t<circle class=\"bx-ui-smiles-loading-inner-path\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-miterlimit=\"10\"/>\n\t\t\t\t\t</svg>\n\t\t\t\t</template>\n\t\t\t\t<template v-else v-for=\"smile in smiles\">\n\t\t\t\t\t<div class=\"bx-ui-smiles-smile\">\n\t\t\t\t\t\t<img v-bx-lazyload :key=\"smile.id\"\n\t\t\t\t\t\t\tclass=\"bx-ui-smiles-smile-icon\"\n\t\t\t\t\t\t\t:data-lazyload-src=\"smile.image\"\n\t\t\t\t\t\t\tdata-lazyload-error-class=\"bx-ui-smiles-smile-icon-error\"\n\t\t\t\t\t\t\t:title=\"smile.name\"\n\t\t\t\t\t\t\t:style=\"{height: (smile.originalHeight*0.5)+'px', width: (smile.originalWidth*0.5)+'px'}\"\n\t\t\t\t\t\t\t@click=\"selectSmile(smile.typing)\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t\t<template v-if=\"sets.length > 1\">\n\t\t\t\t<div class=\"bx-ui-smiles-sets\">\n\t\t\t\t\t<template v-for=\"set in sets\">\n\t\t\t\t\t\t<div :class=\"['bx-ui-smiles-set', {'bx-ui-smiles-set-selected': set.selected}]\">\n\t\t\t\t\t\t\t<img v-bx-lazyload :key=\"set.id\"\n\t\t\t\t\t\t\t\tclass=\"bx-ui-smiles-set-icon\"\n\t\t\t\t\t\t\t\t:data-lazyload-src=\"set.image\"\n\t\t\t\t\t\t\t\tdata-lazyload-error-class=\"bx-ui-smiles-set-icon-error\"\n\t\t\t\t\t\t\t\t:title=\"set.name\"\n\t\t\t\t\t\t\t\t@click=\"selectSet(set.id)\"\n\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</template>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),window,BX,BX,BX));
//# sourceMappingURL=smiles.bundle.js.map
