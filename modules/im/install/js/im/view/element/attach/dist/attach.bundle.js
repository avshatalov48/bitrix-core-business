(function (exports,im_model,im_lib_utils,ui_vue) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Vue component
	 *
	 * Delimiter (attach type)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var AttachTypeDelimiter = {
	  property: 'DELIMITER',
	  name: 'bx-im-view-element-attach-delimiter',
	  component: {
	    props: {
	      config: {
	        type: Object,
	        default: {}
	      },
	      color: {
	        type: String,
	        default: 'transparent'
	      }
	    },
	    computed: {
	      styles: function styles() {
	        return {
	          width: this.config.DELIMITER.SIZE ? this.config.DELIMITER.SIZE + 'px' : '',
	          backgroundColor: this.config.DELIMITER.COLOR ? this.config.DELIMITER.COLOR : this.color
	        };
	      }
	    },
	    template: "<div class=\"bx-im-element-attach-type-delimiter\" :style=\"styles\">Delimiter</div>"
	  }
	};

	/**
	 * Bitrix Messenger
	 * Vue component
	 *
	 * File (attach type)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var AttachTypeFile = {
	  property: 'FILE',
	  name: 'bx-im-element-attach-file',
	  component: {
	    props: {
	      config: {
	        type: Object,
	        default: {}
	      },
	      color: {
	        type: String,
	        default: 'transparent'
	      }
	    },
	    methods: {
	      openLink: function openLink(element) {
	        im_lib_utils.Utils.platform.openNewPage(element.LINK);
	      },
	      file: function file() {
	        return {
	          name: this.config.FILE.NAME,
	          extension: this.config.FILE.NAME.split('.').splice(-1)[0],
	          size: this.config.FILE.SIZE
	        };
	      },
	      fileName: function fileName(element) {
	        var maxLength = 70;

	        if (!element.NAME || element.NAME.length < maxLength) {
	          return element.NAME;
	        }

	        var endWordLength = 10;
	        var extension = element.NAME.split('.').splice(-1)[0];
	        var secondPart = element.NAME.substring(element.NAME.length - 1 - (extension.length + 1 + endWordLength));
	        var firstPart = element.NAME.substring(0, maxLength - secondPart.length - 3);
	        return firstPart.trim() + '...' + secondPart.trim();
	      },
	      fileNameFull: function fileNameFull(element) {
	        return im_lib_utils.Utils.text.htmlspecialcharsback(element.NAME);
	      },
	      fileSize: function fileSize(element) {
	        var size = element.SIZE;

	        if (size <= 0) {
	          return '';
	        }

	        var sizes = ["BYTE", "KB", "MB", "GB", "TB"];
	        var position = 0;

	        while (size >= 1024 && position < 4) {
	          size /= 1024;
	          position++;
	        }

	        return Math.round(size) + " " + this.localize['IM_MESSENGER_ATTACH_FILE_SIZE_' + sizes[position]];
	      },
	      fileIcon: function fileIcon(element) {
	        return im_model.FilesModel.getIconType(element.NAME.split('.').splice(-1)[0]);
	      }
	    },
	    computed: {
	      localize: function localize() {
	        return ui_vue.Vue.getFilteredPhrases('IM_MESSENGER_ATTACH_FILE_', this.$root.$bitrixMessages);
	      }
	    },
	    template: "\n\t\t\t<div class=\"bx-im-element-attach-type-file-element\">\n\t\t\t\t<template v-for=\"(element, index) in config.FILE\">\n\t\t\t\t\t<div class=\"bx-im-element-attach-type-file\" @click=\"openLink(element)\">\n\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-file-icon\">\n\t\t\t\t\t\t\t<div :class=\"['ui-icon', 'ui-icon-file-'+fileIcon(element)]\"><i></i></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-file-block\">\n\t\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-file-name\" :title=\"fileNameFull(element)\">\n\t\t\t\t\t\t\t\t{{fileName(element)}}\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-file-size\">{{fileSize(element)}}</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t"
	  }
	};

	/**
	 * Bitrix Messenger
	 * Vue component
	 *
	 * Grid (attach type)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var AttachTypeGrid = {
	  property: 'GRID',
	  name: 'bx-im-view-element-attach-grid',
	  component: {
	    props: {
	      config: {
	        type: Object,
	        default: {}
	      },
	      color: {
	        type: String,
	        default: 'transparent'
	      }
	    },
	    created: function created() {
	      if (im_lib_utils.Utils.platform.isBitrixMobile()) {
	        this.maxCellWith = Math.floor(Math.min(screen.availWidth, screen.availHeight) / 4);
	      } else {
	        this.maxCellWith = null;
	      }
	    },
	    methods: {
	      openLink: function openLink(element) {
	        if (element.LINK) {
	          im_lib_utils.Utils.platform.openNewPage(element.LINK);
	        }
	      },
	      getWidth: function getWidth(element) {
	        if (this.type !== 'row') {
	          return element.WIDTH ? element.WIDTH + 'px' : '';
	        }

	        if (!element.VALUE) {
	          return false;
	        }

	        if (this.maxCellWith && element.WIDTH > this.maxCellWith) {
	          return this.maxCellWith + 'px';
	        }

	        return element.WIDTH ? element.WIDTH + 'px' : '';
	      },
	      getValue: function getValue(element) {
	        if (!element.VALUE) {
	          return '';
	        }

	        return im_model.MessagesModel.decodeBbCode({
	          text: element.VALUE
	        });
	      }
	    },
	    computed: {
	      type: function type() {
	        return this.config.GRID[0].DISPLAY.toLowerCase();
	      }
	    },
	    template: "\n\t\t\t<div class=\"bx-im-element-attach-type-grid\">\n\t\t\t\t<template v-if=\"type === 'block'\">\n\t\t\t\t\t<template v-for=\"(element, index) in config.GRID\">\n\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-grid-display bx-im-element-attach-type-display-block\" :style=\"{width: getWidth(element)}\">\n\t\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-grid-element-name\" v-html=\"element.NAME\"></div>\n\t\t\t\t\t\t\t<template v-if=\"element.LINK\">\n\t\t\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-grid-element-value bx-im-element-attach-type-grid-element-value-link\" @click=\"openLink(element)\" v-html=\"getValue(element)\"></div>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-grid-element-value\" v-html=\"getValue(element)\"></div>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t</div>\t\n\t\t\t\t\t</template>\n\t\t\t\t</template>\n\t\t\t\t<template v-else-if=\"type === 'line'\">\n\t\t\t\t\t<template v-for=\"(element, index) in config.GRID\">\n\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-grid-display bx-im-element-attach-type-display-card\" :style=\"{width: getWidth(element)}\">\n\t\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-grid-element-name\" v-html=\"element.NAME\"></div>\n\t\t\t\t\t\t\t<template v-if=\"element.LINK\">\n\t\t\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-grid-element-value bx-im-element-attach-type-grid-element-value-link\" @click=\"openLink(element)\" v-html=\"getValue(element)\" :style=\"{color: element.COLOR? element.COLOR: ''}\"></div>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-grid-element-value\" v-html=\"getValue(element)\" :style=\"{color: element.COLOR? element.COLOR: ''}\"></div>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</template>\n\t\t\t\t</template>\n\t\t\t\t<template v-else-if=\"type === 'row'\">\n\t\t\t\t\t<div class=\"bx-im-element-attach-type-grid-display bx-im-element-attach-type-display-column\">\n\t\t\t\t\t\t<table class=\"bx-im-element-attach-type-display-column-table\">\n\t\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t\t<template v-for=\"(element, index) in config.GRID\">\n\t\t\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t\t\t<template v-if=\"element.NAME\">\n\t\t\t\t\t\t\t\t\t\t\t<td class=\"bx-im-element-attach-type-grid-element-name\" :colspan=\"element.VALUE? 1: 2\" v-html=\"element.NAME\" :style=\"{width: getWidth(element)}\"></td>\n\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t<template v-if=\"element.VALUE\">\n\t\t\t\t\t\t\t\t\t\t\t<template v-if=\"element.LINK\">\n\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"bx-im-element-attach-type-grid-element-value bx-im-element-attach-type-grid-element-value-link\" @click=\"openLink(element)\" v-html=\"getValue(element)\" :colspan=\"element.NAME? 1: 2\" :style=\"{color: element.COLOR? element.COLOR: ''}\"></td>\n\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"bx-im-element-attach-type-grid-element-value\" v-html=\"getValue(element)\" :colspan=\"element.NAME? 1: 2\" :style=\"{color: element.COLOR? element.COLOR: ''}\"></td>\n\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t</tbody>\n\t\t\t\t\t\t</table>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t"
	  }
	};

	/**
	 * Bitrix Messenger
	 * Vue component
	 *
	 * Rich Attach type
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var AttachTypeHtml = {
	  property: 'HTML',
	  name: 'bx-im-view-element-attach-html',
	  component: {
	    props: {
	      config: {
	        type: Object,
	        default: {}
	      },
	      color: {
	        type: String,
	        default: 'transparent'
	      }
	    },
	    template: "<div class=\"bx-im-element-attach-type-html\" v-html=\"config.HTML\"></div>"
	  }
	};

	/**
	 * Bitrix Messenger
	 * Vue component
	 *
	 * Image (attach type)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var AttachTypeImage = {
	  property: 'IMAGE',
	  name: 'bx-im-view-element-attach-image',
	  component: {
	    props: {
	      config: {
	        type: Object,
	        default: {}
	      },
	      color: {
	        type: String,
	        default: 'transparent'
	      }
	    },
	    methods: {
	      open: function open(file) {
	        if (!file) {
	          return false;
	        }

	        if (im_lib_utils.Utils.platform.isBitrixMobile()) {
	          // TODO add multiply
	          BXMobileApp.UI.Photo.show({
	            photos: [{
	              url: file
	            }],
	            default_photo: file
	          });
	        } else {
	          window.open(file, '_blank');
	        }
	      },
	      getImageSize: function getImageSize(width, height, maxWidth) {
	        var aspectRatio;

	        if (width > maxWidth) {
	          aspectRatio = maxWidth / width;
	        } else {
	          aspectRatio = 1;
	        }

	        return {
	          width: width * aspectRatio,
	          height: height * aspectRatio
	        };
	      },
	      getElementSource: function getElementSource(element) {
	        return element.PREVIEW ? element.PREVIEW : element.LINK;
	      },
	      getElementTitle: function getElementTitle(element) {
	        return im_lib_utils.Utils.text.htmlspecialcharsback(element.NAME);
	      },
	      lazyLoadCallback: function lazyLoadCallback(event) {
	        if (!event.element.style.width) {
	          event.element.style.width = event.element.offsetWidth + 'px';
	        }

	        if (!event.element.style.height) {
	          event.element.style.height = event.element.offsetHeight + 'px';
	        }
	      },
	      styleFileSizes: function styleFileSizes(image) {
	        if (!(image.WIDTH && image.HEIGHT)) {
	          return {
	            maxHeight: '100%',
	            backgroundSize: 'contain'
	          };
	        }

	        var sizes = this.getImageSize(image.WIDTH, image.HEIGHT, 250);
	        return {
	          width: sizes.width + 'px',
	          height: sizes.height + 'px',
	          backgroundSize: sizes.width < 100 || sizes.height < 100 ? 'contain' : 'initial'
	        };
	      },
	      styleBoxSizes: function styleBoxSizes(image) {
	        if (!(image.WIDTH && image.HEIGHT)) {
	          return {
	            height: '150px'
	          };
	        }

	        if (parseInt(this.styleFileSizes(image).height) <= 250) {
	          return {};
	        }

	        return {
	          height: '280px'
	        };
	      }
	    },
	    template: "\n\t\t\t<div class=\"bx-im-element-attach-type-image\">\n\t\t\t\t<template v-for=\"(image, index) in config.IMAGE\">\n\t\t\t\t\t<div class=\"bx-im-element-attach-type-image-block\" @click=\"open(image.LINK)\" :style=\"styleBoxSizes(image)\" :key=\"index\">\n\t\t\t\t\t\t<img v-bx-lazyload=\"{callback: lazyLoadCallback}\"\n\t\t\t\t\t\t\tclass=\"bx-im-element-attach-type-image-source\"\n\t\t\t\t\t\t\t:data-lazyload-src=\"getElementSource(image)\"\n\t\t\t\t\t\t\t:style=\"styleFileSizes(image)\"\n\t\t\t\t\t\t\t:title=\"getElementTitle(image)\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t"
	  }
	};

	/**
	 * Bitrix Messenger
	 * Vue component
	 *
	 * Link (attach type)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var AttachTypeLink = {
	  property: 'LINK',
	  name: 'bx-im-view-element-attach-link',
	  component: {
	    props: {
	      config: {
	        type: Object,
	        default: {}
	      },
	      color: {
	        type: String,
	        default: 'transparent'
	      }
	    },
	    methods: {
	      getImageConfig: function getImageConfig(element) {
	        return {
	          IMAGE: [{
	            NAME: element.NAME,
	            PREVIEW: element.PREVIEW,
	            WIDTH: element.WIDTH,
	            HEIGHT: element.HEIGHT
	          }]
	        };
	      },
	      getLinkName: function getLinkName(element) {
	        return element.NAME ? element.NAME : element.LINK;
	      },
	      openLink: function openLink(element) {
	        if (element.LINK) {
	          im_lib_utils.Utils.platform.openNewPage(element.LINK);
	        }
	      }
	    },
	    computed: {
	      imageComponentName: function imageComponentName() {
	        return AttachTypeImage.name;
	      }
	    },
	    components: babelHelpers.defineProperty({}, AttachTypeImage.name, AttachTypeImage.component),
	    template: "\n\t\t\t<div class=\"bx-im-element-attach-type-link\">\n\t\t\t\t<template v-for=\"(element, index) in config.LINK\">\n\t\t\t\t\t<div class=\"bx-im-element-attach-type-link-element\" :key=\"index\">\n\t\t\t\t\t\t<div v-if=\"element.PREVIEW\" class=\"bx-im-element-attach-type-link-image\" @click=\"openLink(element)\">\n\t\t\t\t\t\t\t<component :is=\"imageComponentName\" :config=\"getImageConfig(element)\" :color=\"color\"/>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-link-name\" @click=\"openLink(element)\" v-html=\"getLinkName(element)\"></div>\n\t\t\t\t\t\t<div v-if=\"element.DESC\" class=\"bx-im-element-attach-type-link-desc\" v-html=\"element.DESC\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t"
	  }
	};

	/**
	 * Bitrix Messenger
	 * Vue component
	 *
	 * Message (attach type)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var AttachTypeMessage = {
	  property: 'MESSAGE',
	  name: 'bx-im-view-element-attach-message',
	  component: {
	    props: {
	      config: {
	        type: Object,
	        default: {}
	      },
	      color: {
	        type: String,
	        default: 'transparent'
	      }
	    },
	    computed: {
	      message: function message() {
	        return im_model.MessagesModel.decodeBbCode({
	          text: this.config.MESSAGE
	        });
	      }
	    },
	    template: "<div class=\"bx-im-element-attach-type-message\" v-html=\"message\"></div>"
	  }
	};

	/**
	 * Bitrix Messenger
	 * Vue component
	 *
	 * Rich (attach type)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var AttachTypeRich = {
	  property: 'RICH_LINK',
	  name: 'bx-im-view-element-attach-rich',
	  component: {
	    props: {
	      config: {
	        type: Object,
	        default: {}
	      },
	      color: {
	        type: String,
	        default: 'transparent'
	      }
	    },
	    methods: {
	      getImageConfig: function getImageConfig(element) {
	        return {
	          IMAGE: [{
	            NAME: element.NAME,
	            PREVIEW: element.PREVIEW,
	            WIDTH: element.WIDTH,
	            HEIGHT: element.HEIGHT
	          }]
	        };
	      },
	      openLink: function openLink(element) {
	        if (element.LINK) {
	          im_lib_utils.Utils.platform.openNewPage(element.LINK);
	        }
	      }
	    },
	    computed: {
	      imageComponentName: function imageComponentName() {
	        return AttachTypeImage.name;
	      }
	    },
	    components: babelHelpers.defineProperty({}, AttachTypeImage.name, AttachTypeImage.component),
	    template: "\n\t\t\t<div class=\"bx-im-element-attach-type-rich\">\n\t\t\t\t<template v-for=\"(element, index) in config.RICH_LINK\">\n\t\t\t\t\t<div class=\"bx-im-element-attach-type-rich-element\" :key=\"index\">\n\t\t\t\t\t\t<div v-if=\"element.PREVIEW\" class=\"bx-im-element-attach-type-rich-image\" @click=\"openLink(element)\">\n\t\t\t\t\t\t\t<component :is=\"imageComponentName\" :config=\"getImageConfig(element)\" :color=\"color\"/>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-rich-name\" @click=\"openLink(element)\" v-html=\"element.NAME\"></div>\n\t\t\t\t\t\t<div v-if=\"element.DESC\" class=\"bx-im-element-attach-type-rich-desc\" v-html=\"element.DESC\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t"
	  }
	};

	/**
	 * Bitrix Messenger
	 * Vue component
	 *
	 * User (Attach type)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var AttachTypeUser = {
	  property: 'USER',
	  name: 'bx-im-view-element-attach-user',
	  component: {
	    props: {
	      config: {
	        type: Object,
	        default: {}
	      },
	      color: {
	        type: String,
	        default: 'transparent'
	      }
	    },
	    methods: {
	      openLink: function openLink(element) {
	        if (element.LINK) {
	          im_lib_utils.Utils.platform.openNewPage(element.LINK);
	        }
	      },
	      getAvatarType: function getAvatarType(element) {
	        if (element.AVATAR) {
	          return '';
	        }

	        var avatarType = 'user';

	        if (element.AVATAR_TYPE === 'CHAT') {
	          avatarType = 'chat';
	        } else if (element.AVATAR_TYPE === 'BOT') {
	          avatarType = 'bot';
	        }

	        return 'bx-im-element-attach-type-user-avatar-type-' + avatarType;
	      }
	    },
	    template: "\n\t\t\t<div class=\"bx-im-element-attach-type-user\">\n\t\t\t\t<template v-for=\"(element, index) in config.USER\">\n\t\t\t\t\t<div class=\"bx-im-element-attach-type-user-body\" @click=\"openLink(element)\">\n\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-user-avatar\">\n\t\t\t\t\t\t\t<div :class=\"['bx-im-element-attach-type-user-avatar-type', getAvatarType(element)]\" :style=\"{backgroundColor: element.AVATAR? '': color}\">\n\t\t\t\t\t\t\t\t<img v-if=\"element.AVATAR\" \n\t\t\t\t\t\t\t\t\tv-bx-lazyload\n\t\t\t\t\t\t\t\t\tclass=\"bx-im-element-attach-type-user-avatar-source\"\n\t\t\t\t\t\t\t\t\t:data-lazyload-src=\"element.AVATAR\"\n\t\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"bx-im-element-attach-type-user-name\" v-html=\"element.NAME\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t"
	  }
	};

	/**
	 * Bitrix Messenger
	 * Attach element Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var AttachTypes = [AttachTypeDelimiter, AttachTypeFile, AttachTypeGrid, AttachTypeHtml, AttachTypeImage, AttachTypeLink, AttachTypeMessage, AttachTypeRich, AttachTypeUser];
	var AttachComponents = {};
	AttachTypes.forEach(function (attachType) {
	  AttachComponents[attachType.name] = attachType.component;
	});
	ui_vue.Vue.component('bx-im-view-element-attach', {
	  props: {
	    config: {
	      type: Object,
	      default: {}
	    },
	    baseColor: {
	      type: String,
	      default: '#17a3ea'
	    }
	  },
	  methods: {
	    getComponentForBlock: function getComponentForBlock(block) {
	      var _iteratorNormalCompletion = true;
	      var _didIteratorError = false;
	      var _iteratorError = undefined;

	      try {
	        for (var _iterator = AttachTypes[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
	          var attachType = _step.value;

	          if (typeof block[attachType.property] !== 'undefined') {
	            return attachType.name;
	          }
	        }
	      } catch (err) {
	        _didIteratorError = true;
	        _iteratorError = err;
	      } finally {
	        try {
	          if (!_iteratorNormalCompletion && _iterator.return != null) {
	            _iterator.return();
	          }
	        } finally {
	          if (_didIteratorError) {
	            throw _iteratorError;
	          }
	        }
	      }

	      return '';
	    }
	  },
	  computed: {
	    color: function color() {
	      if (typeof this.config.COLOR === 'undefined' || !this.config.COLOR) {
	        return this.baseColor;
	      }

	      if (this.config.COLOR === 'transparent') {
	        return '';
	      }

	      return this.config.COLOR;
	    }
	  },
	  components: AttachComponents,
	  template: "\n\t\t<div class=\"bx-im-element-attach\">\n\t\t\t<div v-if=\"color\" class=\"bx-im-element-attach-border\" :style=\"{borderColor: color}\"></div>\n\t\t\t<div class=\"bx-im-element-attach-content\">\n\t\t\t\t<template v-for=\"(block, index) in config.BLOCKS\">\n\t\t\t\t\t<component :is=\"getComponentForBlock(block)\" :config=\"block\" :color=\"color\" :key=\"index\" />\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),BX.Messenger.Model,BX.Messenger.Lib,BX));
//# sourceMappingURL=attach.bundle.js.map
