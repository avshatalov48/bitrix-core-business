/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_unsupported,ui_vue3_directives_lazyload,im_v2_model,main_core_events,im_v2_lib_progressbar,im_v2_provider_service,im_v2_lib_menu,ui_icons_disk,im_v2_lib_utils,main_core,im_v2_component_elements,im_v2_component_message_elements,im_v2_component_message_base,im_v2_const) {
	'use strict';

	function getGalleryElementsConfig(filesCount, index) {
	  const spanValues = {
	    10: ['1-4', '1-1', '1-2', '1-1', '1-1', '1-2', '1-1', '1-1', '1-2', '1-1'],
	    9: ['1-4', '1-1', '1-2', '1-1', '1-2', '1-2', '1-1', '1-2', '1-1'],
	    8: ['1-4', '1-2', '1-2', '1-1', '1-2', '1-1', '1-2', '1-2'],
	    7: ['1-4', '1-2', '1-2', '1-2', '1-2', '1-2', '1-2'],
	    6: ['1-4', '1-2', '1-2', '1-1', '1-2', '1-1'],
	    5: ['1-4', '1-2', '1-2', '1-2', '1-2'],
	    4: ['2-4', '1-1', '1-2', '1-1'],
	    3: ['2-4', '1-2', '1-2'],
	    2: ['2-2', '2-2']
	  };
	  const spanValue = spanValues[filesCount] && spanValues[filesCount][index];
	  if (!spanValue) {
	    return {
	      'grid-row-end': 'span 1',
	      'grid-column-end': 'span 1'
	    };
	  }
	  const [rowSpan, colSpan] = spanValue.split('-');
	  return {
	    'grid-row-end': `span ${rowSpan}`,
	    'grid-column-end': `span ${colSpan}`
	  };
	}

	function getGalleryGridRowsConfig(filesCount) {
	  let rowsTemplate = '140px 80px';
	  if (filesCount >= 7) {
	    rowsTemplate = '140px 80px 80px 58px';
	  } else if (filesCount >= 3) {
	    rowsTemplate = '140px 80px 80px';
	  }
	  return {
	    gridTemplateRows: rowsTemplate
	  };
	}

	// @vue/component
	const ProgressBar = {
	  name: 'ProgressBar',
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    messageId: {
	      type: [String, Number],
	      required: true
	    },
	    withLabels: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    file() {
	      return this.item;
	    }
	  },
	  watch: {
	    'file.status': function () {
	      this.getProgressBarManager().update();
	    },
	    'file.progress': function () {
	      this.getProgressBarManager().update();
	    }
	  },
	  mounted() {
	    this.initProgressBar();
	  },
	  beforeUnmount() {
	    this.removeProgressBar();
	  },
	  methods: {
	    initProgressBar() {
	      if (this.file.progress === 100) {
	        return;
	      }
	      let blurElement;
	      if (this.file.progress < 0 || !this.isImage && !this.isVideo) {
	        blurElement = false;
	      }
	      const customConfig = {
	        blurElement,
	        hasTitle: false
	      };
	      if (!this.withLabels) {
	        customConfig.labels = {};
	      }
	      this.progressBarManager = new im_v2_lib_progressbar.ProgressBarManager({
	        container: this.$refs['progress-bar'],
	        uploadState: this.file,
	        customConfig
	      });
	      this.progressBarManager.subscribe(im_v2_lib_progressbar.ProgressBarManager.event.cancel, () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.uploader.cancel, {
	          tempFileId: this.file.id,
	          tempMessageId: this.messageId
	        });
	      });
	      this.progressBarManager.subscribe(im_v2_lib_progressbar.ProgressBarManager.event.destroy, () => {
	        if (this.progressBar) {
	          this.progressBar = null;
	        }
	      });
	      this.progressBarManager.start();
	    },
	    removeProgressBar() {
	      if (!this.getProgressBarManager()) {
	        return;
	      }
	      this.getProgressBarManager().destroy();
	    },
	    getProgressBarManager() {
	      return this.progressBarManager;
	    }
	  },
	  template: `
		<div class="bx-im-progress-bar__container" ref="progress-bar"></div>
	`
	};

	const MAX_WIDTH = 488;
	const MAX_HEIGHT = 340;
	const MIN_WIDTH = 200;
	const MIN_HEIGHT = 100;

	// @vue/component
	const GalleryItem = {
	  name: 'GalleryItem',
	  directives: {
	    lazyload: ui_vue3_directives_lazyload.lazyload
	  },
	  components: {
	    ProgressBar
	  },
	  props: {
	    id: {
	      type: [String, Number],
	      required: true
	    },
	    message: {
	      type: Object,
	      required: true
	    },
	    isGallery: {
	      type: Boolean,
	      default: false
	    },
	    handleLoading: {
	      type: Boolean,
	      default: true
	    },
	    removable: {
	      type: Boolean,
	      default: false
	    },
	    previewMode: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['onRemoveClick'],
	  computed: {
	    messageItem() {
	      return this.message;
	    },
	    file() {
	      return this.$store.getters['files/get'](this.id, true);
	    },
	    imageSize() {
	      if (this.isGallery) {
	        return {};
	      }
	      let newWidth = this.file.image.width;
	      let newHeight = this.file.image.height;
	      if (this.file.image.width > MAX_WIDTH || this.file.image.height > MAX_HEIGHT) {
	        const aspectRatio = this.file.image.width / this.file.image.height;
	        if (this.file.image.width > MAX_WIDTH) {
	          newWidth = MAX_WIDTH;
	          newHeight = Math.round(MAX_WIDTH / aspectRatio);
	        }
	        if (newHeight > MAX_HEIGHT) {
	          newWidth = Math.round(MAX_HEIGHT * aspectRatio);
	          newHeight = MAX_HEIGHT;
	        }
	      }
	      const sizes = {
	        width: Math.max(newWidth, MIN_WIDTH),
	        height: Math.max(newHeight, MIN_HEIGHT)
	      };
	      if (this.previewMode && sizes.width > sizes.height) {
	        return {
	          width: `${sizes.width}px`,
	          'object-fit': sizes.width < 100 || sizes.height < 100 ? 'cover' : 'contain'
	        };
	      }
	      return {
	        width: `${sizes.width}px`,
	        height: `${sizes.height}px`,
	        'object-fit': sizes.width < 100 || sizes.height < 100 ? 'cover' : 'contain'
	      };
	    },
	    viewerAttributes() {
	      return im_v2_lib_utils.Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
	    },
	    canBeOpenedWithViewer() {
	      var _BX$UI;
	      return this.file.viewerAttrs && ((_BX$UI = BX.UI) == null ? void 0 : _BX$UI.Viewer);
	    },
	    imageTitle() {
	      const size = im_v2_lib_utils.Utils.file.formatFileSize(this.file.size);
	      return this.loc('IM_ELEMENTS_MEDIA_IMAGE_TITLE', {
	        '#NAME#': this.file.name,
	        '#SIZE#': size
	      });
	    },
	    isLoaded() {
	      return this.file.progress === 100;
	    },
	    isForward() {
	      return main_core.Type.isStringFilled(this.messageItem.forward.id);
	    },
	    isVideo() {
	      return this.file.type === im_v2_const.FileType.video;
	    },
	    previewSourceLink() {
	      // for a video, we use "urlPreview", because there is an image preview.
	      // for an image, we use "urlShow", because for large gif files in "urlPreview" we have
	      // a static image (w/o animation) .
	      return this.isVideo ? this.file.urlPreview : this.file.urlShow;
	    },
	    allowLazyLoad() {
	      return !this.previewSourceLink.startsWith('blob:');
	    }
	  },
	  methods: {
	    download() {
	      var _this$file$urlDownloa;
	      if (this.file.progress !== 100 || this.canBeOpenedWithViewer) {
	        return;
	      }
	      const url = (_this$file$urlDownloa = this.file.urlDownload) != null ? _this$file$urlDownloa : this.file.urlShow;
	      window.open(url, '_blank');
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    onRemoveClick() {
	      this.$emit('onRemoveClick', {
	        file: this.file
	      });
	    }
	  },
	  template: `
		<div 
			v-bind="viewerAttributes" 
			class="bx-im-gallery-item__container" 
			:class="{'--with-forward': isForward}"
			@click="download"
			:style="imageSize"
		>
			<img
				v-if="allowLazyLoad"
				v-lazyload
				data-lazyload-dont-hide
				:data-lazyload-src="previewSourceLink"
				:title="imageTitle"
				:alt="file.name"
				class="bx-im-gallery-item__source"
			/>
			<img
				v-else
				:src="previewSourceLink"
				:title="imageTitle"
				:alt="file.name"
				class="bx-im-gallery-item__source"
			/>
			<ProgressBar v-if="handleLoading && !isLoaded" :item="file" :messageId="messageItem.id" :withLabels="!isGallery" />
			<div v-if="isVideo" class="bx-im-gallery-item__play-icon-container">
				<div class="bx-im-gallery-item__play-icon"></div>
			</div>
			<div v-if="removable" class="bx-im-gallery-item__remove" @click="onRemoveClick">
				<div class="bx-im-gallery-item__remove-icon"></div>
			</div>
		</div>
	`
	};

	const VIDEO_SIZE_TO_AUTOPLAY = 5000000;
	const MAX_WIDTH$1 = 420;
	const MAX_HEIGHT$1 = 340;
	const MIN_WIDTH$1 = 200;
	const MIN_HEIGHT$1 = 100;
	const DEFAULT_WIDTH = 320;
	const DEFAULT_HEIGHT = 180;

	// @vue/component
	const VideoItem = {
	  name: 'VideoItem',
	  components: {
	    VideoPlayer: im_v2_component_elements.VideoPlayer,
	    ProgressBar
	  },
	  props: {
	    id: {
	      type: [String, Number],
	      required: true
	    },
	    message: {
	      type: Object,
	      required: true
	    },
	    handleLoading: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    messageItem() {
	      return this.message;
	    },
	    file() {
	      return this.$store.getters['files/get'](this.id, true);
	    },
	    autoplay() {
	      return this.file.size < VIDEO_SIZE_TO_AUTOPLAY;
	    },
	    canBeOpenedWithViewer() {
	      var _BX$UI;
	      return this.file.viewerAttrs && ((_BX$UI = BX.UI) == null ? void 0 : _BX$UI.Viewer);
	    },
	    viewerAttributes() {
	      return im_v2_lib_utils.Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
	    },
	    imageSize() {
	      let newWidth = this.file.image.width;
	      let newHeight = this.file.image.height;
	      if (!newHeight || !newWidth) {
	        return {
	          width: `${DEFAULT_WIDTH}px`,
	          height: `${DEFAULT_HEIGHT}px`
	        };
	      }
	      if (this.file.image.width > MAX_WIDTH$1 || this.file.image.height > MAX_HEIGHT$1) {
	        const aspectRatio = this.file.image.width / this.file.image.height;
	        if (this.file.image.width > MAX_WIDTH$1) {
	          newWidth = MAX_WIDTH$1;
	          newHeight = Math.round(MAX_WIDTH$1 / aspectRatio);
	        }
	        if (newHeight > MAX_HEIGHT$1) {
	          newWidth = Math.round(MAX_HEIGHT$1 * aspectRatio);
	          newHeight = MAX_HEIGHT$1;
	        }
	      }
	      const sizes = {
	        width: Math.max(newWidth, MIN_WIDTH$1),
	        height: Math.max(newHeight, MIN_HEIGHT$1)
	      };
	      return {
	        width: `${sizes.width}px`,
	        height: `${sizes.height}px`,
	        'object-fit': sizes.width < 100 || sizes.height < 100 ? 'cover' : 'contain'
	      };
	    },
	    isLoaded() {
	      return this.file.progress === 100;
	    },
	    isForward() {
	      return main_core.Type.isStringFilled(this.messageItem.forward.id);
	    }
	  },
	  methods: {
	    download() {
	      var _this$file$urlDownloa;
	      if (this.file.progress !== 100 || this.canBeOpenedWithViewer) {
	        return;
	      }
	      const url = (_this$file$urlDownloa = this.file.urlDownload) != null ? _this$file$urlDownloa : this.file.urlShow;
	      window.open(url, '_blank');
	    }
	  },
	  template: `
		<div
			class="bx-im-video-item__container bx-im-video-item__scope"
			:class="{'--with-forward': isForward}"
			@click="download"
		>
			<ProgressBar v-if="!isLoaded && handleLoading" :item="file" :messageId="messageItem.id" />
			<VideoPlayer
				:fileId="file.id"
				:src="file.urlShow"
				:previewImageUrl="file.urlPreview"
				:elementStyle="imageSize"
				:withAutoplay="autoplay"
				:withPlayerControls="isLoaded"
				:viewerAttributes="viewerAttributes"
			/>
		</div>
	`
	};

	const FILES_LIMIT = 10;

	// @vue/component
	const MediaContent = {
	  name: 'MediaContent',
	  components: {
	    GalleryItem,
	    VideoItem,
	    MessageStatus: im_v2_component_message_elements.MessageStatus
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    previewMode: {
	      type: Boolean,
	      default: false
	    },
	    removable: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['onRemoveItem'],
	  computed: {
	    message() {
	      return this.item;
	    },
	    fileIds() {
	      return this.message.files.slice(0, FILES_LIMIT);
	    },
	    firstFileId() {
	      return this.message.files[0];
	    },
	    isGallery() {
	      return this.message.files.length > 1;
	    },
	    galleryRowConfig() {
	      return getGalleryGridRowsConfig(this.fileIds.length);
	    },
	    galleryColumnsConfig() {
	      if (this.previewMode) {
	        return {
	          gridTemplateColumns: '119px 67px 67px 119px'
	        };
	      }
	      return {};
	    },
	    galleryStyle() {
	      return {
	        ...this.galleryRowConfig,
	        ...this.galleryColumnsConfig
	      };
	    },
	    hasText() {
	      return this.message.text.length > 0;
	    },
	    hasAttach() {
	      return this.message.attach.length > 0;
	    },
	    onlyMedia() {
	      return !this.previewMode && !this.hasText && !this.hasAttach;
	    },
	    isSingleVideo() {
	      if (this.isGallery) {
	        return false;
	      }
	      return this.$store.getters['files/get'](this.firstFileId, true).type === im_v2_const.FileType.video;
	    }
	  },
	  methods: {
	    getGalleryElementStyles(index) {
	      return getGalleryElementsConfig(this.fileIds.length, index);
	    },
	    onRemoveItem(event) {
	      this.$emit('onRemoveItem', event);
	    }
	  },
	  template: `
		<div class="bx-im-message-media-content__container">
			<div v-if="isGallery" class="bx-im-message-media-content__gallery" :style="galleryStyle">
				<GalleryItem
					v-for="(fileId, index) in fileIds"
					:key="fileId"
					:id="fileId"
					:isGallery="true"
					:message="message"
					:style="getGalleryElementStyles(index)"
					:handleLoading="!previewMode"
					:removable="removable"
					@onRemoveClick="onRemoveItem"
				/>
			</div>
			<div v-else-if="isSingleVideo" class="bx-im-message-media-content__single-video">
				<VideoItem
					:id="firstFileId"
					:message="message"
					:handleLoading="!previewMode"
				/>
			</div>
			<div v-else class="bx-im-message-media-content__single-image">
				<GalleryItem
					:id="firstFileId"
					:message="message"
					:handleLoading="!previewMode"
					:previewMode="previewMode"
				/>
			</div>
			<div v-if="onlyMedia" class="bx-im-message-media-content__status-container">
				<MessageStatus :item="message" :isOverlay="true" />
			</div>
		</div>
	`
	};

	const MAX_GALLERY_WIDTH = 305;
	const MAX_SINGLE_MEDIA_WIDTH = 488;

	// @vue/component
	const MediaMessage = {
	  name: 'MediaMessage',
	  components: {
	    ReactionList: im_v2_component_message_elements.ReactionList,
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    MessageStatus: im_v2_component_message_elements.MessageStatus,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent,
	    MessageHeader: im_v2_component_message_elements.MessageHeader,
	    MessageFooter: im_v2_component_message_elements.MessageFooter,
	    MediaContent
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    withTitle: {
	      type: Boolean,
	      default: true
	    },
	    menuIsActiveForId: {
	      type: [String, Number],
	      default: 0
	    }
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    fileIds() {
	      return this.message.files;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId);
	    },
	    hasText() {
	      return this.message.text.length > 0;
	    },
	    hasAttach() {
	      return this.message.attach.length > 0;
	    },
	    hasReply() {
	      return this.message.replyId !== 0;
	    },
	    showContextMenu() {
	      return this.onlyImage;
	    },
	    showBottomContainer() {
	      return this.hasText || this.hasAttach || this.hasReply;
	    },
	    isForward() {
	      return main_core.Type.isStringFilled(this.message.forward.id);
	    },
	    needBackground() {
	      return this.showBottomContainer || this.isChannelPost || this.isForward;
	    },
	    isChannelPost() {
	      return [im_v2_const.ChatType.channel, im_v2_const.ChatType.openChannel].includes(this.dialog.type);
	    },
	    imageContainerStyles() {
	      let maxWidth = MAX_SINGLE_MEDIA_WIDTH;
	      if (this.fileIds.length > 1) {
	        maxWidth = MAX_GALLERY_WIDTH;
	      }
	      return {
	        'max-width': `${maxWidth}px`
	      };
	    }
	  },
	  template: `
		<BaseMessage :item="item" :dialogId="dialogId" :withBackground="needBackground">
			<div class="bx-im-message-image__container" :style="imageContainerStyles">
				<MessageHeader :withTitle="false" :item="item" class="bx-im-message-image__header" />
				<MediaContent :item="message" />
				<div v-if="showBottomContainer" class="bx-im-message-image__bottom-container">
					<DefaultMessageContent
						:item="item"
						:dialogId="dialogId"
						:withText="hasText"
						:withAttach="hasAttach"
					/>
				</div>
				<MessageFooter :item="item" :dialogId="dialogId" />
			</div>
			<template #after-message>
				<div v-if="!showBottomContainer" class="bx-im-message-image__reaction-list-container">
					<ReactionList :messageId="message.id" :contextDialogId="dialogId" />
				</div>
			</template>
		</BaseMessage>
	`
	};

	var _getMessageFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMessageFile");
	class BaseFileContextMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _getMessageFile, {
	      value: _getMessageFile2
	    });
	    this.id = im_v2_const.PopupType.messageBaseFileMenu;
	    this.id = 'bx-im-message-file-context-menu';
	    this.diskService = new im_v2_provider_service.DiskService();
	  }
	  getMenuItems() {
	    return [this.getDownloadFileItem(), this.getSaveToDiskItem()];
	  }
	  getDownloadFileItem() {
	    const file = babelHelpers.classPrivateFieldLooseBase(this, _getMessageFile)[_getMessageFile]();
	    if (!file) {
	      return null;
	    }
	    return {
	      html: im_v2_lib_utils.Utils.file.createDownloadLink(main_core.Loc.getMessage('IM_MESSAGE_FILE_MENU_DOWNLOAD_FILE'), file.urlDownload, file.name),
	      onclick: function () {
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getSaveToDiskItem() {
	    const file = babelHelpers.classPrivateFieldLooseBase(this, _getMessageFile)[_getMessageFile]();
	    if (!file) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_MESSAGE_FILE_MENU_SAVE_ON_DISK_MSGVER_1'),
	      onclick: function () {
	        void this.diskService.save(this.context.files).then(() => {
	          BX.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('IM_MESSAGE_FILE_MENU_SAVE_ON_DISK_SUCCESS_MSGVER_1')
	          });
	        });
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	}
	function _getMessageFile2() {
	  if (!this.context.fileId) {
	    return null;
	  }
	  return this.store.getters['files/get'](this.context.fileId);
	}

	// @vue/component
	const BaseFileItem = {
	  name: 'BaseFileItem',
	  components: {
	    ProgressBar
	  },
	  props: {
	    id: {
	      type: [String, Number],
	      required: true
	    },
	    messageId: {
	      type: [String, Number],
	      required: true
	    }
	  },
	  computed: {
	    file() {
	      return this.$store.getters['files/get'](this.id, true);
	    },
	    fileShortName() {
	      const NAME_MAX_LENGTH = 20;
	      return im_v2_lib_utils.Utils.file.getShortFileName(this.file.name, NAME_MAX_LENGTH);
	    },
	    fileSize() {
	      return im_v2_lib_utils.Utils.file.formatFileSize(this.file.size);
	    },
	    iconClass() {
	      const iconType = im_v2_lib_utils.Utils.file.getIconTypeByFilename(this.file.name);
	      return `ui-icon-file-${iconType}`;
	    },
	    canBeOpenedWithViewer() {
	      var _BX$UI;
	      return this.file.viewerAttrs && ((_BX$UI = BX.UI) == null ? void 0 : _BX$UI.Viewer);
	    },
	    viewerAttributes() {
	      return im_v2_lib_utils.Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
	    },
	    isLoaded() {
	      return this.file.progress === 100;
	    },
	    imageStyles() {
	      return {
	        backgroundImage: `url(${this.file.urlPreview})`
	      };
	    },
	    hasPreview() {
	      return main_core.Type.isStringFilled(this.file.urlPreview);
	    }
	  },
	  created() {
	    this.contextMenu = new BaseFileContextMenu();
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	  },
	  methods: {
	    download() {
	      var _this$file$urlDownloa;
	      if (this.file.progress !== 100 || this.canBeOpenedWithViewer) {
	        return;
	      }
	      const url = (_this$file$urlDownloa = this.file.urlDownload) != null ? _this$file$urlDownloa : this.file.urlShow;
	      window.open(url, '_blank');
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    openContextMenu(event) {
	      this.$emit('openContextMenu', {
	        event,
	        fileId: this.id
	      });
	    }
	  },
	  template: `
		<div class="bx-im-base-file-item__container">
			<div class="bx-im-base-file-item__icon-container" ref="loader-icon" v-bind="viewerAttributes" @click="download">
				<ProgressBar v-if="!isLoaded" :item="file" :messageId="messageId" :withLabels="false" />
				<div v-if="hasPreview" :style="imageStyles" class="bx-im-base-file-item__image"></div>
				<div v-else :class="iconClass" class="bx-im-base-file-item__type-icon ui-icon"><i></i></div>
			</div>
			<div class="bx-im-base-file-item__content" v-bind="viewerAttributes" @click="download">
				<span :title="file.name" class="bx-im-base-file-item__title">
					{{ fileShortName }}
				</span>
				<div class="bx-im-base-file-item__size">{{ fileSize }}</div>
			</div>
			<div 
				class="bx-im-base-file-item__download-icon"
				:class="{'--not-active': !isLoaded}"
				@click="openContextMenu"
			></div>
		</div>
	`
	};

	// @vue/component
	const BaseFileMessage = {
	  name: 'BaseFileMessage',
	  components: {
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent,
	    BaseFileItem,
	    MessageHeader: im_v2_component_message_elements.MessageHeader,
	    MessageFooter: im_v2_component_message_elements.MessageFooter
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    withTitle: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    FileType: () => im_v2_const.FileType,
	    message() {
	      return this.item;
	    },
	    messageFile() {
	      const firstFileId = this.message.files[0];
	      return this.$store.getters['files/get'](firstFileId, true);
	    }
	  },
	  created() {
	    this.contextMenu = new BaseFileContextMenu();
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	  },
	  methods: {
	    onOpenContextMenu({
	      event,
	      fileId
	    }) {
	      const context = {
	        dialogId: this.dialogId,
	        fileId,
	        ...this.message
	      };
	      this.contextMenu.openMenu(context, event.target);
	    }
	  },
	  template: `
		<BaseMessage :item="item" :dialogId="dialogId">
			<div class="bx-im-message-base-file__container">
				<MessageHeader :withTitle="withTitle" :item="item" class="bx-im-message-base-file__author-title" />
				<BaseFileItem
					:key="messageFile.id"
					:id="messageFile.id"
					:messageId="message.id"
					@openContextMenu="onOpenContextMenu"
				/>
				<DefaultMessageContent :item="item" :dialogId="dialogId" />
			</div>
			<MessageFooter :item="item" :dialogId="dialogId" />
		</BaseMessage>
	`
	};

	// @vue/component
	const AudioItem = {
	  name: 'AudioItem',
	  components: {
	    AudioPlayer: im_v2_component_elements.AudioPlayer,
	    ProgressBar
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    messageType: {
	      type: String,
	      required: true
	    },
	    messageId: {
	      type: [String, Number],
	      required: true
	    }
	  },
	  computed: {
	    file() {
	      return this.item;
	    },
	    isLoaded() {
	      return this.file.progress === 100;
	    }
	  },
	  template: `
		<div class="bx-im-media-audio__container">
			<ProgressBar v-if="!isLoaded" :item="file" :messageId="messageId" />
			<AudioPlayer
				:id="file.id"
				:messageId="messageId"
				:src="file.urlShow"
				:file="file"
				:timelineType="Math.floor(Math.random() * 5)"
				:authorId="file.authorId"
				:withContextMenu="false"
				:withAvatar="false"
			/>
		</div>
	`
	};

	// @vue/component
	const AudioMessage = {
	  name: 'AudioMessage',
	  components: {
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    MessageHeader: im_v2_component_message_elements.MessageHeader,
	    MessageFooter: im_v2_component_message_elements.MessageFooter,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent,
	    AudioItem
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    withTitle: {
	      type: Boolean,
	      default: false
	    }
	  },
	  computed: {
	    FileType: () => im_v2_const.FileType,
	    message() {
	      return this.item;
	    },
	    messageFile() {
	      const firstFileId = this.message.files[0];
	      return this.$store.getters['files/get'](firstFileId, true);
	    },
	    canSetReactions() {
	      return main_core.Type.isNumber(this.message.id);
	    },
	    messageType() {
	      return this.$store.getters['messages/getMessageType'](this.message.id);
	    }
	  },
	  template: `
		<BaseMessage :item="item" :dialogId="dialogId">
			<div class="bx-im-message-audio__container">
				<MessageHeader :withTitle="withTitle" :item="item" class="bx-im-message-audio__header"/>
				<AudioItem
					:key="messageFile.id"
					:item="messageFile"
					:messageId="message.id"
					:messageType="messageType"
				/>
			</div>
			<div class="bx-im-message-audio__default-message-container">
				<DefaultMessageContent :item="item" :dialogId="dialogId" />
			</div>
			<MessageFooter :item="item" :dialogId="dialogId" />
		</BaseMessage>
	`
	};

	const FILES_LIMIT$1 = 10;

	// @vue/component
	const FileCollectionMessage = {
	  name: 'FileCollectionMessage',
	  components: {
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent,
	    BaseFileItem,
	    MessageHeader: im_v2_component_message_elements.MessageHeader,
	    MessageFooter: im_v2_component_message_elements.MessageFooter
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    withTitle: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    FileType: () => im_v2_const.FileType,
	    message() {
	      return this.item;
	    },
	    messageId() {
	      return this.message.id;
	    },
	    fileIds() {
	      return this.message.files.slice(0, FILES_LIMIT$1);
	    }
	  },
	  created() {
	    this.contextMenu = new BaseFileContextMenu();
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	  },
	  methods: {
	    onOpenContextMenu({
	      event,
	      fileId
	    }) {
	      const context = {
	        dialogId: this.dialogId,
	        fileId,
	        ...this.message
	      };
	      this.contextMenu.openMenu(context, event.target);
	    }
	  },
	  template: `
		<BaseMessage :item="item" :dialogId="dialogId">
			<div class="bx-im-message-file-collection__container">
				<MessageHeader :withTitle="withTitle" :item="item" class="bx-im-message-file-collection__author-title" />
				<div class="bx-im-message-file-collection__items">
					<BaseFileItem
						v-for="fileId in fileIds"
						:key="fileId"
						:id="fileId"
						:messageId="messageId"
						@openContextMenu="onOpenContextMenu"
					/>
				</div>
				<DefaultMessageContent 
					:item="item" 
					:dialogId="dialogId"
					class="bx-im-message-file-collection__default-content" 
				/>
			</div>
			<MessageFooter :item="item" :dialogId="dialogId" />
		</BaseMessage>
	`
	};

	const FileMessageType = Object.freeze({
	  media: 'MediaMessage',
	  audio: 'AudioMessage',
	  base: 'BaseFileMessage',
	  collection: 'FileCollectionMessage'
	});

	// @vue/component
	const FileMessage = {
	  name: 'FileMessage',
	  components: {
	    BaseFileMessage,
	    MediaMessage,
	    AudioMessage,
	    UnsupportedMessage: im_v2_component_message_unsupported.UnsupportedMessage,
	    FileCollectionMessage
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    withTitle: {
	      type: Boolean,
	      default: true
	    },
	    menuIsActiveForId: {
	      type: [String, Number],
	      default: 0
	    }
	  },
	  computed: {
	    FileType: () => im_v2_const.FileType,
	    message() {
	      return this.item;
	    },
	    messageFiles() {
	      const files = [];
	      if (this.message.files.length === 0) {
	        return files;
	      }
	      this.message.files.forEach(fileId => {
	        const file = this.$store.getters['files/get'](fileId, true);
	        files.push(file);
	      });
	      return files;
	    },
	    isGallery() {
	      const allowedGalleryTypes = new Set([im_v2_const.FileType.image, im_v2_const.FileType.video]);
	      const isMediaOnly = this.messageFiles.every(file => {
	        return allowedGalleryTypes.has(file.type);
	      });
	      const hasImageProp = this.messageFiles.some(file => {
	        return file.image !== false;
	      });
	      return isMediaOnly && hasImageProp;
	    },
	    componentName() {
	      if (this.messageFiles.length > 1) {
	        return this.isGallery ? FileMessageType.media : FileMessageType.collection;
	      }
	      const file = this.messageFiles[0];
	      const hasPreview = Boolean(file.image);
	      if (file.type === im_v2_const.FileType.image && hasPreview) {
	        return FileMessageType.media;
	      }
	      if (file.type === im_v2_const.FileType.audio) {
	        return FileMessageType.audio;
	      }

	      // file.type value is empty for mkv files
	      const isVideo = file.type === im_v2_const.FileType.video || im_v2_lib_utils.Utils.file.getFileExtension(file.name) === 'mkv';
	      if (isVideo && hasPreview) {
	        return FileMessageType.media;
	      }
	      return FileMessageType.base;
	    },
	    isRealMessage() {
	      return this.$store.getters['messages/isRealMessage'](this.message.id);
	    }
	  },
	  template: `
		<component 
			:is="componentName" 
			:item="message" 
			:dialogId="dialogId"
			:withTitle="withTitle" 
			:menuIsActiveForId="menuIsActiveForId"
			:withRetryButton="false"
			:withContextMenu="isRealMessage"
		/>
	`
	};

	exports.FileMessage = FileMessage;
	exports.MediaContent = MediaContent;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message,BX.Vue3.Directives,BX.Messenger.v2.Model,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Const));
//# sourceMappingURL=file-message.bundle.js.map
