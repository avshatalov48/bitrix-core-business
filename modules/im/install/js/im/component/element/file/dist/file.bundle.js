(function (exports,ui_vue_directives_lazyload,ui_icons,ui_vue,im_model) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * File element Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.Vue.component('bx-messenger-element-file', {
	  props: {
	    userId: {
	      default: 0
	    },
	    file: {
	      type: Object,
	      default: im_model.FilesModel.create().getElementStore
	    }
	  },
	  methods: {
	    download: function download(file, event) {
	      if (file.image && file.urlShow) {
	        window.open(file.urlShow, '_blank');
	      } else if (file.video && file.urlShow) {
	        window.open(file.urlShow, '_blank');
	      } else if (file.urlDownload) {
	        window.open(file.urlDownload, '_self');
	      } else {
	        window.open(file.urlShow, '_blank');
	      }
	    }
	  },
	  computed: {
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('IM_MESSENGER_ELEMENT_FILE_', this.$root.$bitrixMessages);
	    },
	    fileNameLeft: function fileNameLeft() {
	      var end = this.file.name.length - this.fileNameRight.length;
	      return this.file.name.substring(0, end);
	    },
	    fileNameRight: function fileNameRight() {
	      var cutLength = this.file.extension.length + 1;

	      if (this.file.name.length > 30) {
	        cutLength = cutLength + 5;
	      }

	      var start = this.file.name.length - 1 - cutLength;
	      return this.file.name.substring(start);
	    },
	    fileSize: function fileSize() {
	      var size = this.file.size;
	      var sizes = ["BYTE", "KB", "MB", "GB", "TB"];
	      var position = 0;

	      while (size >= 1024 && position < 4) {
	        size /= 1024;
	        position++;
	      }

	      return Math.round(size) + " " + this.localize['IM_MESSENGER_ELEMENT_FILE_SIZE_' + sizes[position]];
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-element-file\" @click=\"download(file, $event)\">\n\t\t\t<div class=\"bx-im-element-file-icon\">\n\t\t\t\t<div :class=\"['ui-icon', 'ui-icon-file-'+file.icon]\"><i></i></div>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-element-file-block\">\n\t\t\t\t<div class=\"bx-im-element-file-name\" :title=\"file.name\">\n\t\t\t\t\t<span class=\"bx-im-element-file-name-left\">{{fileNameLeft}}</span><span class=\"bx-im-element-file-name-right\">{{fileNameRight}}</span>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-element-file-size\">{{fileSize}}</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	});
	ui_vue.Vue.cloneComponent('bx-messenger-element-file-image', 'bx-messenger-element-file', {
	  methods: {
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
	    }
	  },
	  computed: {
	    styleFileSizes: function styleFileSizes() {
	      var sizes = this.getImageSize(this.file.image.width, this.file.image.height, 280);
	      return {
	        width: sizes.width + 'px',
	        height: sizes.height + 'px',
	        backgroundSize: sizes.width < 100 || sizes.height < 100 ? 'contain' : 'initial'
	      };
	    },
	    styleBoxSizes: function styleBoxSizes() {
	      if (parseInt(this.styleFileSizes.height) <= 280) {
	        return {};
	      }

	      return {
	        height: '280px'
	      };
	    },
	    fileSource: function fileSource() {
	      return this.file.urlPreview;
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-element-file-image\" @click=\"download(file, $event)\" :style=\"styleBoxSizes\">\n\t\t\t<img v-bx-lazyload\n\t\t\t\tclass=\"bx-im-element-file-image-source\"\n\t\t\t\t:data-lazyload-src=\"fileSource\"\n\t\t\t\t:title=\"localize.IM_MESSENGER_ELEMENT_FILE_SHOW_TITLE.replace('#NAME#', file.name).replace('#SIZE#', fileSize)\"\n\t\t\t\t:style=\"styleFileSizes\"\n\t\t\t/>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),window,BX,BX,BX.Messenger.Model));
//# sourceMappingURL=file.bundle.js.map
