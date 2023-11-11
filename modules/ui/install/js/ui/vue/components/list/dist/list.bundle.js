/* eslint-disable */
(function (exports,ui_designTokens,ui_vue) {
	'use strict';

	/**
	 * Bitrix UI
	 * Base list element
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2020 Bitrix
	 */
	ui_vue.BitrixVue.component('bx-list-element', {
	  props: ['rawListItem', 'itemTypes'],
	  computed: {
	    imageStyle: function imageStyle() {
	      return {};
	    },
	    imageClass: function imageClass() {
	      return 'bx-vue-list-item-image';
	    },
	    avatarText: function avatarText() {
	      var words = this.listItem.title.value.split(' ');
	      if (words.length > 1) {
	        return words[0].charAt(0) + words[1].charAt(0);
	      } else if (words.length === 1) {
	        return words[0].charAt(0);
	      }
	    },
	    listItemStyle: function listItemStyle() {
	      return {};
	    },
	    listItem: function listItem() {
	      return this.rawListItem;
	    }
	  },
	  template: "\n\t\t<div class=\"bx-vue-list-item\" :style=\"listItemStyle\">\n\t\t\t<template v-if=\"listItem.template !== itemTypes.placeholder\">\n\t\t\t\t<div v-if=\"listItem.avatar\" class=\"bx-vue-list-item-image-wrap\">\n\t\t\t\t\t<img v-if=\"listItem.avatar.url\" :src=\"listItem.avatar.url\" :style=\"imageStyle\" :class=\"imageClass\" alt=\"\">\n\t\t\t\t\t<div v-else-if=\"!listItem.avatar.url\" :style=\"imageStyle\" class=\"bx-vue-list-item-image-text\">{{ avatarText }}</div>\t\n\t\t\t\t\t<div v-if=\"listItem.avatar.topLeftIcon\" :class=\"'bx-vue-list-icon-avatar-top-left bx-vue-list-avatar-top-left-' + listItem.avatar.topLeftIcon\"></div>\n\t\t\t\t\t<div v-if=\"listItem.avatar.bottomRightIcon\" :class=\"'bx-vue-list-icon-avatar-bottom-right bx-vue-list-avatar-bottom-right-' + listItem.avatar.bottomRightIcon\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-vue-list-item-content\">\n\t\t\t\t\t<div class=\"bx-vue-list-item-content-header\">\n\t\t\t\t\t\t<div v-if=\"listItem.title\" class=\"bx-vue-list-item-header-title\">\n\t\t\t\t\t\t\t<div v-if=\"listItem.title.leftIcon\" :class=\"'bx-vue-list-icon-title-left bx-vue-list-icon-title-left-' + listItem.title.leftIcon\"></div>\n\t\t\t\t\t\t\t<span class=\"bx-vue-list-item-header-title-text\">{{ listItem.title.value }}</span>\n\t\t\t\t\t\t\t<div v-if=\"listItem.title.rightIcon\" :class=\"'bx-vue-list-icon-title-right bx-vue-list-icon-title-right-' + listItem.title.rightIcon\"></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div v-if=\"listItem.date\" class=\"bx-vue-list-item-header-date\">\n\t\t\t\t\t\t\t<div v-if=\"listItem.date.leftIcon\" :class=\"'bx-vue-list-icon-date-left bx-vue-list-icon-date-left-' + listItem.date.leftIcon\"></div>\n\t\t\t\t\t\t\t{{ listItem.date.value }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"bx-vue-list-item-content-bottom\">\n\t\t\t\t\t\t<div v-if=\"listItem.subtitle\" class=\"bx-vue-list-item-bottom-subtitle\">\n\t\t\t\t\t\t\t<div v-if=\"listItem.subtitle.leftIcon\" :class=\"'bx-vue-list-icon-subtitle-left bx-vue-list-icon-subtitle-left-' + listItem.subtitle.leftIcon\"></div>\n\t\t\t\t\t\t\t<span class=\"bx-vue-list-item-bottom-subtitle-text\">{{ listItem.subtitle.value }}</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"bx-vue-list-item-bottom-counter\">\n\t\t\t\t\t\t\t<div v-if=\"listItem.counter.leftIcon\" :class=\"'bx-vue-list-icon-counter-left bx-vue-list-icon-counter-left-' + listItem.counter.leftIcon\"></div>\n\t\t\t\t\t\t\t<div v-if=\"listItem.counter.value > 0\" class=\"bx-vue-list-item-bottom-counter-value\">{{ listItem.counter.value }}</div>\n\t\t\t\t\t\t\t<div v-else-if=\"listItem.notification\" class=\"bx-vue-list-item-bottom-counter-notification\"></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-else-if=\"listItem.template === itemTypes.placeholder\">\n\t\t\t\t<div class=\"bx-vue-list-item-image-wrap\"><img src=\"https://www.ischool.berkeley.edu/sites/default/files/default_images/avatar.jpeg\" alt=\"\" class=\"bx-vue-list-item-image\"></div>\n\t\t\t\t<div class=\"bx-vue-list-item-content\">\n\t\t\t\t\t<div class=\"bx-vue-list-item-content-header\">\n\t\t\t\t\t\t<div class=\"bx-vue-list-item-placeholder-title\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"bx-vue-list-item-content-bottom\">\n\t\t\t\t\t\t<div class=\"bx-vue-list-item-bottom-subtitle\">\n\t\t\t\t\t\t\t<div class=\"bx-vue-list-item-placeholder-subtitle\"></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t</div>\n\t"
	});

	/**
	 * Bitrix UI
	 * Base list
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2020 Bitrix
	 */
	ui_vue.BitrixVue.component('bx-list', {
	  data: function data() {
	    return {
	      generalSectionName: 'general',
	      showSectionNames: true,
	      resultList: {},
	      itemTypes: {
	        "default": 'default',
	        placeholder: 'placeholder'
	      },
	      cssPrefix: '',
	      observer: null,
	      elementComponent: 'bx-list-element'
	    };
	  },
	  created: function created() {},
	  methods: {
	    /* region 01. Data validation */validateData: function validateData(listData) {
	      var _this = this;
	      var result = [];
	      listData.items.forEach(function (listItem) {
	        result.push(_this.validateItem(listItem));
	      });
	      this.list = result;
	      this.validateSections(listData.sections);
	    },
	    validateItem: function validateItem(listItem) {
	      var itemResult = {};
	      if (typeof listItem.id === "number" || typeof listItem.id === "string") {
	        itemResult.id = listItem.id.toString();
	      }
	      if (typeof listItem.type !== "undefined" && this.itemTypes[listItem.type]) {
	        itemResult.type = listItem.type;
	      } else {
	        itemResult.type = this.itemTypes["default"];
	      }
	      if (typeof listItem.title !== "undefined") {
	        itemResult.title = {};
	        if (babelHelpers["typeof"](listItem.title) === 'object' && listItem.title) {
	          if (typeof listItem.title.value === 'string') {
	            itemResult.title.value = listItem.title.value;
	          }
	          if (typeof listItem.title.leftIcon === 'string') {
	            itemResult.title.leftIcon = listItem.title.leftIcon;
	          }
	          if (typeof listItem.title.rightIcon === 'string') {
	            itemResult.title.rightIcon = listItem.title.rightIcon;
	          }
	        } else if (typeof listItem.title === 'string') {
	          itemResult.title.value = listItem.title;
	        }
	      }
	      if (typeof listItem.subtitle !== "undefined") {
	        itemResult.subtitle = {};
	        if (babelHelpers["typeof"](listItem.subtitle) === 'object' && listItem.subtitle) {
	          if (typeof listItem.subtitle.value === 'string') {
	            itemResult.subtitle.value = listItem.subtitle.value;
	          }
	          if (typeof listItem.subtitle.leftIcon === 'string') {
	            itemResult.subtitle.leftIcon = listItem.subtitle.leftIcon;
	          }
	        } else if (typeof listItem.subtitle === 'string') {
	          itemResult.subtitle.value = listItem.subtitle;
	        }
	      }
	      if (typeof listItem.avatar !== 'undefined') {
	        itemResult.avatar = {};
	        if (babelHelpers["typeof"](listItem.avatar) === 'object' && listItem.avatar) {
	          //TODO: avatar processing
	          if (typeof listItem.avatar.url === 'string') {
	            itemResult.avatar.url = listItem.avatar.url;
	          }
	          if (typeof listItem.avatar.topLeftIcon === 'string') {
	            itemResult.avatar.topLeftIcon = listItem.avatar.topLeftIcon;
	          }
	          if (typeof listItem.avatar.bottomRightIcon === 'string') {
	            itemResult.avatar.bottomRightIcon = listItem.avatar.bottomRightIcon;
	          }
	        } else if (typeof listItem.avatar === 'string') {
	          //TODO: avatar processing
	          itemResult.avatar.url = listItem.avatar;
	        }
	      }
	      if (typeof listItem.date !== 'undefined') {
	        itemResult.date = {};
	        if (babelHelpers["typeof"](listItem.date) === 'object' && listItem.date && !(listItem.date instanceof Date)) {
	          if (listItem.date.value instanceof Date) {
	            itemResult.date.value = this.formatDate(listItem.date.value);
	          }
	          if (typeof listItem.date.leftIcon === 'string') {
	            itemResult.date.leftIcon = listItem.date.leftIcon;
	          }
	        } else if (listItem.date instanceof Date) {
	          itemResult.date.value = this.formatDate(listItem.date);
	        }
	      }
	      if (typeof listItem.sectionCode === 'string') {
	        itemResult.sectionCode = listItem.sectionCode;
	      }
	      if (typeof listItem.counter === 'number') {
	        itemResult.counter = this.formatCounter(listItem.counter);
	      }
	      if (typeof listItem.notification === 'boolean') {
	        itemResult.notification = listItem.notification;
	      }
	      return itemResult;
	    },
	    validateSections: function validateSections(sections) {
	      var _this2 = this;
	      if (sections && sections.length > 0) {
	        sections.forEach(function (element) {
	          if (typeof element === 'string' && element.length > 0) {
	            _this2.sections.push(element);
	          }
	        });
	      }
	      if (this.sections.length === 0) {
	        this.sections = [this.generalSectionName];
	        this.list.map(function (element) {
	          element.sectionCode = _this2.generalSectionName;
	          return element;
	        });
	      }
	    },
	    formatCounter: function formatCounter(counter) {
	      if (counter > 999) {
	        counter = 999;
	      } else if (counter < 0) {
	        counter = 0;
	      }
	      return counter;
	    },
	    /* endregion 01. Data validation */
	    /* region 02. Events handling */
	    onScroll: function onScroll(event) {},
	    onClick: function onClick(event, id) {},
	    onDoubleClick: function onDoubleClick(event) {} /* endregion 02. Events handling */
	  },
	  computed: {
	    wrapperStyle: function wrapperStyle() {
	      return this.cssPrefix + ' bx-vue-list-wrap';
	    },
	    list: function list() {
	      return [];
	    },
	    sections: function sections() {
	      return [];
	    },
	    sectionedList: function sectionedList() {
	      var _this3 = this;
	      this.sections.forEach(function (section) {
	        ui_vue.BitrixVue.set(_this3.resultList, section, []);
	        var listForSection = _this3.list.filter(function (item) {
	          return item.sectionCode === section;
	        });
	        _this3.resultList[section] = babelHelpers.toConsumableArray(listForSection);
	      });
	      return this.resultList;
	    }
	  },
	  template: "\n\t\t<div :class=\"wrapperStyle\" @scroll=\"onScroll\">\n\t\t\t<template v-for=\"section in sections\">\n\t\t\t\t<div v-if=\"sections.length > 1 && sectionedList[section].length > 0 && showSectionNames\" class=\"bx-vue-list-section\">{{ section }}</div>\n\t\t\t\t<div\n\t\t\t\t\tv-for=\"listItem in sectionedList[section]\"\n\t\t\t\t\t:key=\"listItem.id\"\n\t\t\t\t\t@click=\"onClick($event, listItem.id)\"\n\t\t\t\t\t@click.right=\"onRightClick($event, listItem.id)\"\n\t\t\t\t\t:data-id=\"listItem.id\"\n\t\t\t\t>\n\t\t\t\t\t<component :is=\"elementComponent\" :rawListItem=\"listItem\" :itemTypes=\"itemTypes\" @dblclick=\"onDoubleClick\"/>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),BX,BX));
//# sourceMappingURL=list.bundle.js.map
