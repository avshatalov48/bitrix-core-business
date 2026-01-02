/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_icons_disk,main_core,im_v2_lib_parser,im_v2_lib_utils,rest_client,im_v2_application_core,im_v2_const,ui_vue3_directives_lazyload) {
	'use strict';

	// @vue/component
	const AttachDelimiter = {
	  name: 'AttachDelimiter',
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    styles() {
	      const result = {};
	      if (this.internalConfig.delimiter.color) {
	        result.backgroundColor = this.internalConfig.delimiter.color;
	      }
	      if (this.internalConfig.delimiter.size > 0) {
	        result.width = `${this.internalConfig.delimiter.size}px`;
	      }
	      return result;
	    }
	  },
	  template: `
		<div class="bx-im-attach-delimiter__container" :style="styles"></div>
	`
	};

	const AttachFileItem = {
	  name: 'AttachFileItem',
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    fileName() {
	      return this.internalConfig.name;
	    },
	    fileSize() {
	      return this.internalConfig.size;
	    },
	    link() {
	      return this.internalConfig.link;
	    },
	    fileShortName() {
	      const NAME_MAX_LENGTH = 70;
	      const fileName = main_core.Type.isStringFilled(this.fileName) ? this.fileName : this.$Bitrix.Loc.getMessage('IM_ELEMENTS_ATTACH_RICH_FILE_NO_NAME');
	      return im_v2_lib_utils.Utils.file.getShortFileName(fileName, NAME_MAX_LENGTH);
	    },
	    formattedFileSize() {
	      if (!this.fileSize) {
	        return '';
	      }
	      return im_v2_lib_utils.Utils.file.formatFileSize(this.fileSize);
	    },
	    iconClasses() {
	      return ['ui-icon', `ui-icon-file-${this.fileIcon}`];
	    },
	    fileIcon() {
	      return im_v2_lib_utils.Utils.file.getIconTypeByFilename(this.fileName);
	    }
	  },
	  methods: {
	    openLink() {
	      if (!this.link) {
	        return;
	      }
	      window.open(this.link, '_blank');
	    }
	  },
	  template: `
		<div @click="openLink" class="bx-im-attach-file__container">
			<div class="bx-im-attach-file__item">
				<div class="bx-im-attach-file__icon">
					<div :class="iconClasses"><i></i></div>
				</div>
				<div class="bx-im-attach-file__block">
					<div class="bx-im-attach-file__name" :title="fileName">
						{{ fileShortName }}
					</div>
					<div class="bx-im-attach-file__size">
						{{ formattedFileSize }}
					</div>
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const AttachFile = {
	  name: 'AttachFile',
	  components: {
	    AttachFileItem
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    }
	  },
	  template: `
		<div class="bx-im-attach-file__container">
			<AttachFileItem
				v-for="(fileItem, index) in internalConfig.file"
				:config="fileItem"
				:key="index"
			/>
		</div>
	`
	};

	const AttachGridItemDisplayType = {
	  block: 'block',
	  line: 'line',
	  row: 'row'
	};
	const DisplayType = AttachGridItemDisplayType;

	// @vue/component
	const AttachGridItem = {
	  name: 'AttachGridItem',
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    DisplayType: () => DisplayType,
	    internalConfig() {
	      return this.config;
	    },
	    display() {
	      return this.internalConfig.display.toLowerCase();
	    },
	    width() {
	      if (!this.value || !this.internalConfig.width) {
	        return '';
	      }
	      return `${this.internalConfig.width}px`;
	    },
	    value() {
	      if (!this.internalConfig.value) {
	        return '';
	      }
	      return im_v2_lib_parser.Parser.decodeText(this.internalConfig.value);
	    },
	    colorToken() {
	      return this.internalConfig.colorToken || 'base';
	    },
	    name() {
	      return this.internalConfig.name;
	    },
	    link() {
	      return this.internalConfig.link;
	    }
	  },
	  template: `
		<div v-if="display === DisplayType.block" :style="{width}" class="bx-im-attach-grid__item --block">
			<div class="bx-im-attach-grid__name">{{ name }}</div>
			<div v-if="link" class="bx-im-attach-grid__value --link">
				<a :href="link" target="_blank" :class="colorToken" v-html="value"></a>
			</div>
			<div v-else v-html="value" :class="colorToken" class="bx-im-attach-grid__value"></div>
		</div>
		<div v-if="display === DisplayType.line" :style="{width}" class="bx-im-attach-grid__item --line">
			<div class="bx-im-attach-grid__name">{{ name }}</div>
			<div v-if="link" :class="colorToken" class="bx-im-attach-grid__value --link">
				<a :href="link" target="_blank" v-html="value"></a>
			</div>
			<div v-else class="bx-im-attach-grid__value" :class="colorToken" v-html="value"></div>
		</div>
		<div v-if="display === DisplayType.row" class="bx-im-attach-grid__item --row">
			<table>
				<tbody>
					<tr>
						<td v-if="name" :colspan="value? 1: 2" :style="{width}" class="bx-im-attach-grid__name">
							{{ name }}
						</td>
						<td
							v-if="value && link"
							:colspan="name? 1: 2"
							:class="colorToken"
							class="bx-im-attach-grid__value --link"
						>
							<a :href="link" target="_blank" v-html="value"></a>
						</td>
						<td
							v-if="value && !link"
							:colspan="name? 1: 2"
							:class="colorToken"
							v-html="value"
							class="bx-im-attach-grid__value"
						>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	`
	};

	// @vue/component
	const AttachGrid = {
	  name: 'AttachGrid',
	  components: {
	    AttachGridItem
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    }
	  },
	  template: `
		<div class="bx-im-attach-grid__container">
			<AttachGridItem
				v-for="(gridItem, index) in internalConfig.grid"
				:config="gridItem"
				:key="index"
			/>
		</div>
	`
	};

	const AttachHtml = {
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    html() {
	      return im_v2_lib_parser.Parser.decodeHtml(this.internalConfig.html);
	    }
	  },
	  template: `
		<div class="bx-im-element-attach-type-html" v-html="html"></div>
	`
	};

	const MAX_IMAGE_SIZE = 272;

	// @vue/component
	const AttachImageItem = {
	  name: 'AttachImageItem',
	  directives: {
	    lazyload: ui_vue3_directives_lazyload.lazyload
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    width() {
	      return this.internalConfig.width || 0;
	    },
	    height() {
	      return this.internalConfig.height || 0;
	    },
	    link() {
	      return this.internalConfig.link;
	    },
	    name() {
	      return this.internalConfig.name;
	    },
	    preview() {
	      return this.internalConfig.preview;
	    },
	    source() {
	      var _this$preview;
	      return (_this$preview = this.preview) != null ? _this$preview : this.link;
	    },
	    imageSize() {
	      if (this.width === 0 || this.height === 0) {
	        return {};
	      }
	      const sizes = im_v2_lib_utils.Utils.file.resizeToFitMaxSize(this.width, this.height, MAX_IMAGE_SIZE);
	      return {
	        width: `${sizes.width}px`,
	        height: `${sizes.height}px`,
	        'object-fit': sizes.width < 100 || sizes.height < 100 ? 'cover' : 'contain'
	      };
	    },
	    hasWidth() {
	      return Boolean(this.imageSize.width);
	    }
	  },
	  methods: {
	    open() {
	      if (!this.link) {
	        return;
	      }
	      window.open(this.link, '_blank');
	    },
	    lazyLoadCallback(event) {
	      const {
	        element
	      } = event;
	      if (!main_core.Dom.style(element, 'width')) {
	        main_core.Dom.style(element, 'width', `${element.offsetWidth}px`);
	      }
	      if (!main_core.Dom.style(element, 'height')) {
	        main_core.Dom.style(element, 'height', `${element.offsetHeight}px`);
	      }
	    }
	  },
	  template: `
		<div class="bx-im-attach-image__item" :class="{'--with-width': hasWidth }" @click="open">
			<img
				v-lazyload="{callback: lazyLoadCallback}"
				:data-lazyload-src="source"
				:style="imageSize"
				:title="name"
				:alt="name"
				class="bx-im-attach-image__source"
			/>
		</div>
	`
	};

	const AttachImage = {
	  name: 'AttachImage',
	  components: {
	    AttachImageItem
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    }
	  },
	  template: `
		<div class="bx-im-attach-image__container bx-im-attach-image__scope">
			<AttachImageItem v-for="(image, index) in internalConfig.image" :config="image" :key="index" />
		</div>
	`
	};

	// @vue/component
	const AttachLinkItem = {
	  name: 'AttachLinkItem',
	  components: {
	    AttachImage
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    link() {
	      return this.internalConfig.link;
	    },
	    name() {
	      var _this$internalConfig$;
	      return (_this$internalConfig$ = this.internalConfig.name) != null ? _this$internalConfig$ : this.link;
	    },
	    description() {
	      return this.internalConfig.desc;
	    },
	    html() {
	      const content = this.internalConfig.html || this.description;
	      return im_v2_lib_parser.Parser.decodeText(content);
	    },
	    preview() {
	      return this.internalConfig.preview;
	    },
	    imageConfig() {
	      return {
	        image: [{
	          name: this.internalConfig.name,
	          preview: this.internalConfig.preview,
	          width: this.internalConfig.width,
	          height: this.internalConfig.height
	        }]
	      };
	    }
	  },
	  template: `
		<div class="bx-im-attach-link__item">
			<a v-if="link" :href="link" target="_blank" class="bx-im-attach-link__link">
				{{ name }}
			</a>
			<span v-else class="bx-im-attach-link__name">
				{{ name }}
			</span>
			<div v-if="internalConfig.html || description" class="bx-im-attach-link__desc" v-html="html"></div>
			<div v-if="preview" class="bx-im-attach-link__image">
				<AttachImage :config="imageConfig" />
			</div>
		</div>
	`
	};

	// @vue/component
	const AttachLink = {
	  name: 'AttachLink',
	  components: {
	    AttachLinkItem
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    }
	  },
	  template: `
		<div class="bx-im-attach-link__container">
			<AttachLinkItem v-for="(link, index) in internalConfig.link" :config="link" :key="index" />
		</div>
	`
	};

	// @vue/component
	const AttachMessage = {
	  name: 'AttachMessage',
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    message() {
	      return im_v2_lib_parser.Parser.decodeText(this.internalConfig.message);
	    }
	  },
	  template: `
		<div class="bx-im-attach-message__container" v-html="message"></div>
	`
	};

	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _message = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("message");
	class RichService {
	  constructor(message) {
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _message, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _message)[_message] = message;
	  }
	  deleteRichLink(attachId) {
	    void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/deleteAttach', {
	      messageId: babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].id,
	      attachId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imV2ChatMessageDeleteRichUrl, {
	      messageId: babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].id
	    }).catch(result => {
	      console.error('RichService: error deleting rich link', result.error);
	    });
	  }
	}

	// @vue/component
	const AttachRichItem = {
	  name: 'AttachRichItem',
	  components: {
	    AttachImage
	  },
	  inject: ['message'],
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    attachId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    link() {
	      return this.internalConfig.link;
	    },
	    name() {
	      return im_v2_lib_utils.Utils.text.convertHtmlEntities(this.internalConfig.name);
	    },
	    description() {
	      return im_v2_lib_utils.Utils.text.convertHtmlEntities(this.internalConfig.desc);
	    },
	    html() {
	      return this.internalConfig.html;
	    },
	    preview() {
	      return this.internalConfig.preview;
	    },
	    previewSize() {
	      var _this$internalConfig$, _this$internalConfig$2, _this$internalConfig$3, _this$internalConfig$4;
	      return {
	        width: (_this$internalConfig$ = (_this$internalConfig$2 = this.internalConfig.previewSize) == null ? void 0 : _this$internalConfig$2.width) != null ? _this$internalConfig$ : 0,
	        height: (_this$internalConfig$3 = (_this$internalConfig$4 = this.internalConfig.previewSize) == null ? void 0 : _this$internalConfig$4.height) != null ? _this$internalConfig$3 : 0
	      };
	    },
	    imageConfig() {
	      return {
	        image: [{
	          name: this.name,
	          preview: this.preview,
	          width: this.previewSize.width,
	          height: this.previewSize.height
	        }]
	      };
	    },
	    canShowDeleteIcon() {
	      if (!this.message) {
	        return false;
	      }
	      return this.message.authorId === im_v2_application_core.Core.getUserId();
	    },
	    deleteRichLinkTitle() {
	      return this.$Bitrix.Loc.getMessage('IM_ELEMENTS_ATTACH_RICH_LINK_DELETE');
	    },
	    imageStyles() {
	      if (this.previewSize.width === 0 || this.previewSize.height === 0) {
	        return {
	          width: '272px',
	          height: '272px'
	        };
	      }
	      return {};
	    }
	  },
	  methods: {
	    openLink() {
	      if (!this.link) {
	        return;
	      }
	      window.open(this.link, '_blank');
	    },
	    deleteRichLink() {
	      if (!this.message) {
	        return;
	      }
	      new RichService(this.message).deleteRichLink(this.attachId);
	    }
	  },
	  template: `
		<div class="bx-im-attach-rich__scope bx-im-attach-rich__container">
			<div class="bx-im-attach-rich__block">
				<div class="bx-im-attach-rich__name" @click="openLink">{{ name }}</div>
				<div v-if="html || description" class="bx-im-attach-rich__desc">{{ html || description }}</div>
				<button 
					v-if="canShowDeleteIcon" 
					class="bx-im-attach-rich__hide-icon"
					@click="deleteRichLink"
					:title="deleteRichLinkTitle"
				></button>
			</div>
			<div v-if="preview" class="bx-im-attach-rich__image" @click="openLink" :style="imageStyles">
				<AttachImage :config="imageConfig" />
			</div>
		</div>
	`
	};

	// @vue/component
	const AttachRich = {
	  components: {
	    AttachRichItem
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    attachId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    }
	  },
	  template: `
		<div class="bx-im-attach-rich__container">
			<AttachRichItem 
				v-for="(rich, index) in internalConfig.richLink" 
				:config="rich"
				:key="index" 
				:attachId="attachId" 
			/>
		</div>
	`
	};

	const AVATAR_TYPE = {
	  user: 'user',
	  chat: 'chat',
	  bot: 'bot'
	};

	// @vue/component
	const AttachUserItem = {
	  name: 'AttachUserItem',
	  directives: {
	    lazyload: ui_vue3_directives_lazyload.lazyload
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    name() {
	      return this.internalConfig.name;
	    },
	    avatar() {
	      return this.internalConfig.avatar;
	    },
	    avatarType() {
	      return this.internalConfig.avatarType;
	    },
	    link() {
	      return this.internalConfig.link;
	    },
	    avatarTypeClass() {
	      if (this.avatar) {
	        return '';
	      }
	      let avatarType = AVATAR_TYPE.user;
	      if (this.avatarType === AVATAR_TYPE.chat) {
	        avatarType = AVATAR_TYPE.chat;
	      } else if (this.avatarType === AVATAR_TYPE.bot) {
	        avatarType = AVATAR_TYPE.bot;
	      }
	      return [`--${avatarType}`, 'base'];
	    }
	  },
	  template: `
		<div class="bx-im-attach-user__item">
			<div class="bx-im-attach-user__avatar" :class="avatarTypeClass">
				<img v-if="avatar" v-lazyload :data-lazyload-src="avatar" class="bx-im-attach-user__source" alt="name" />
			</div>
			<a v-if="link" :href="link" class="bx-im-attach-user__name" target="_blank">
				{{ name }}
			</a>
			<span class="bx-im-attach-user__name" v-else>
				{{ name }}
			</span>
		</div>
	`
	};

	// @vue/component
	const AttachUser = {
	  name: 'AttachUser',
	  components: {
	    AttachUserItem
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    }
	  },
	  template: `
		<div class="bx-im-attach-user__container">
			<AttachUserItem v-for="(user, index) in internalConfig.user" :config="user" :key="index" />
		</div>
	`
	};

	const PropertyToComponentMap = {
	  [im_v2_const.AttachType.Delimiter]: AttachDelimiter,
	  [im_v2_const.AttachType.File]: AttachFile,
	  [im_v2_const.AttachType.Grid]: AttachGrid,
	  [im_v2_const.AttachType.Html]: AttachHtml,
	  [im_v2_const.AttachType.Image]: AttachImage,
	  [im_v2_const.AttachType.Link]: AttachLink,
	  [im_v2_const.AttachType.Message]: AttachMessage,
	  [im_v2_const.AttachType.Rich]: AttachRich,
	  [im_v2_const.AttachType.User]: AttachUser
	};

	// @vue/component
	const Attach = {
	  name: 'MessengerAttach',
	  components: {
	    AttachDelimiter,
	    AttachFile,
	    AttachGrid,
	    AttachHtml,
	    AttachImage,
	    AttachLink,
	    AttachMessage,
	    AttachRich,
	    AttachUser
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    blocks() {
	      return this.internalConfig.blocks;
	    },
	    colorToken() {
	      const {
	        colorToken = im_v2_const.ColorToken.base
	      } = this.internalConfig;
	      return colorToken;
	    }
	  },
	  methods: {
	    getComponentForBlock(block) {
	      const [blockType] = Object.keys(block);
	      if (!PropertyToComponentMap[blockType]) {
	        return '';
	      }
	      return PropertyToComponentMap[blockType];
	    }
	  },
	  template: `
		<div class="bx-im-attach__container bx-im-attach__scope">
			<div class="bx-im-attach__border" :class="colorToken"></div>
			<div class="bx-im-attach__content">
				<component
					v-for="(block, index) in blocks"
					:is="getComponentForBlock(block)"
					:config="block"
					:colorToken="colorToken"
					:key="index"
					:attachId="internalConfig.id.toString()"
				/>
			</div>
		</div>
	`
	};

	exports.Attach = Attach;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Vue3.Directives));
//# sourceMappingURL=attach.bundle.js.map
