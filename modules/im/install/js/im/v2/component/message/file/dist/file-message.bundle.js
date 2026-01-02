/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_unsupported,im_v2_provider_service_uploading,ui_uploader_core,im_v2_component_elements_mediaGallery,im_v2_component_elements_videoplayer,im_v2_provider_service_disk,im_v2_lib_menu,im_v2_lib_notifier,ui_icons_disk,im_v2_lib_utils,main_core,im_v2_component_elements_audioplayer,im_v2_component_elements_progressbar,im_v2_component_animation,ui_iconSet_api_vue,im_v2_component_elements_loader,im_v2_provider_service_message,im_v2_lib_feature,im_v2_lib_analytics,im_v2_component_message_elements,im_v2_component_message_base,im_v2_const) {
	'use strict';

	const VIDEO_SIZE_TO_AUTOPLAY = 5000000;
	const MAX_WIDTH = 460;
	const MAX_HEIGHT = 380;
	const MIN_WIDTH = 200;
	const MIN_HEIGHT = 100;
	const DEFAULT_WIDTH = 320;
	const DEFAULT_HEIGHT = 180;

	// @vue/component
	const VideoItem = {
	  name: 'VideoItem',
	  components: {
	    VideoPlayer: im_v2_component_elements_videoplayer.VideoPlayer,
	    ProgressBar: im_v2_component_elements_progressbar.ProgressBar
	  },
	  props: {
	    id: {
	      type: [String, Number],
	      required: true
	    },
	    message: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['cancelClick'],
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
	      return im_v2_lib_utils.Utils.file.getViewerDataAttributes({
	        viewerAttributes: this.file.viewerAttrs,
	        previewImageSrc: this.file.urlPreview,
	        context: im_v2_const.FileViewerContext.dialog
	      });
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
	      if (this.file.progress !== 100 || this.canBeOpenedWithViewer) {
	        return;
	      }
	      window.open(this.file.urlDownload, '_blank');
	    },
	    onCancelClick(event) {
	      this.$emit('cancelClick', event);
	    },
	    getHandleStatus() {
	      return [im_v2_const.FileStatus.preparing, im_v2_const.FileStatus.progress, im_v2_const.FileStatus.upload];
	    },
	    getStatusMap() {
	      return {
	        [im_v2_const.FileStatus.preparing]: {
	          iconClass: ui_iconSet_api_vue.Outline.CLOUD,
	          labelText: this.$Bitrix.Loc.getMessage('IM_MESSAGE_FILE_PREPARING_PROGRESS_LABEL')
	        }
	      };
	    }
	  },
	  template: `
		<div
			class="bx-im-video-item__container bx-im-video-item__scope"
			:class="{'--with-forward': isForward}"
			@click="download"
		>
			<ProgressBar 
				:item="file"
				:handleStatus="getHandleStatus()"
				:statusMap="getStatusMap()"
				@cancelClick="onCancelClick"
			/>
			<VideoPlayer
				:fileId="file.id"
				:src="file.urlDownload"
				:previewImageUrl="file.urlPreview"
				:elementStyle="imageSize"
				:withAutoplay="autoplay"
				:withPlayerControls="isLoaded"
				:viewerAttributes="viewerAttributes"
			/>
		</div>
	`
	};

	const SINGLE_IMAGE_CONTAINER_MAX_SIZE = {
	  maxWidth: 460,
	  maxHeight: 590
	};
	const SINGLE_IMAGE_CONTAINER_MIN_SIZE = {
	  minWidth: 120,
	  minHeight: 120
	};
	const SINGLE_IMAGE_WITH_TEXT_CONTAINER_MAX_SIZE = {
	  maxWidth: 460,
	  maxHeight: 590
	};
	const SINGLE_IMAGE_WITH_TEXT_CONTAINER_MIN_SIZE = {
	  minWidth: 460,
	  minHeight: 260
	};
	const GALLERY_MAX_WIDTH = 460;

	// @vue/component
	const MediaContent = {
	  name: 'MediaContent',
	  components: {
	    VideoItem,
	    MessageStatus: im_v2_component_message_elements.MessageStatus,
	    MediaGallery: im_v2_component_elements_mediaGallery.MediaGallery,
	    MediaGalleryItem: im_v2_component_elements_mediaGallery.MediaGalleryItem
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    containerHeight: {
	      type: [Number, null],
	      default: null
	    }
	  },
	  emits: ['cancelClick'],
	  computed: {
	    message() {
	      return this.item;
	    },
	    files() {
	      return this.message.files.map(fileId => {
	        return this.$store.getters['files/get'](fileId);
	      });
	    },
	    filesCount() {
	      return this.files.length;
	    },
	    firstFile() {
	      return this.files[0];
	    },
	    firstFileId() {
	      var _this$firstFile;
	      return (_this$firstFile = this.firstFile) == null ? void 0 : _this$firstFile.id;
	    },
	    hasText() {
	      return this.message.text.length > 0;
	    },
	    hasAttach() {
	      return this.message.attach.length > 0;
	    },
	    onlyMedia() {
	      return !this.hasText && !this.hasAttach;
	    },
	    isSingleVideo() {
	      return this.filesCount === 1 && this.firstFile.type === im_v2_const.FileType.video;
	    },
	    isSingleImage() {
	      return this.filesCount === 1 && this.firstFile.type === im_v2_const.FileType.image;
	    },
	    singleImageMaxSize() {
	      if (main_core.Type.isNumber(this.containerHeight)) {
	        const {
	          maxWidth,
	          maxHeight
	        } = SINGLE_IMAGE_CONTAINER_MAX_SIZE;
	        const maxAllowedPreviewHeight = Math.min(maxHeight, this.containerHeight * 0.9);
	        return {
	          maxWidth,
	          maxHeight: maxAllowedPreviewHeight
	        };
	      }
	      return SINGLE_IMAGE_CONTAINER_MAX_SIZE;
	    },
	    imageWithTextMaxSize() {
	      if (main_core.Type.isNumber(this.containerHeight)) {
	        const {
	          maxWidth,
	          maxHeight
	        } = SINGLE_IMAGE_WITH_TEXT_CONTAINER_MAX_SIZE;
	        const maxAllowedPreviewHeight = Math.min(maxHeight, this.containerHeight * 0.9);
	        return {
	          maxWidth,
	          maxHeight: maxAllowedPreviewHeight
	        };
	      }
	      return SINGLE_IMAGE_WITH_TEXT_CONTAINER_MAX_SIZE;
	    },
	    singleImageSize() {
	      if (this.onlyMedia) {
	        return this.calcPreviewSize({
	          ...this.firstFile.image,
	          ...SINGLE_IMAGE_CONTAINER_MIN_SIZE,
	          ...this.singleImageMaxSize
	        });
	      }
	      return this.calcPreviewSize({
	        ...this.firstFile.image,
	        ...SINGLE_IMAGE_WITH_TEXT_CONTAINER_MIN_SIZE,
	        ...this.imageWithTextMaxSize
	      });
	    }
	  },
	  methods: {
	    onCancel(event) {
	      this.$emit('cancelClick', event);
	    },
	    calcPreviewSize(options) {
	      const {
	        width,
	        height,
	        minWidth,
	        minHeight,
	        maxWidth,
	        maxHeight
	      } = options;
	      const adjustedWidth = Math.min(Math.max(width, minWidth), maxWidth);
	      const adjustedHeight = Math.min(Math.max(height, minHeight), maxHeight);
	      if (adjustedWidth === width && adjustedHeight === height) {
	        return {
	          width,
	          height
	        };
	      }
	      const scale = Math.min(maxWidth / width, maxHeight / height, adjustedWidth / width, adjustedHeight / height);
	      return {
	        width: Math.max(Math.min(width * scale, maxWidth), minWidth),
	        height: Math.max(Math.min(height * scale, maxHeight), minHeight)
	      };
	    },
	    getGalleryMaxWidth() {
	      return GALLERY_MAX_WIDTH;
	    }
	  },
	  template: `
		<div class="bx-im-message-media-content__container">
			<div v-if="isSingleImage" class="bx-im-message-media-content__single-image">
				<MediaGalleryItem
					:file="firstFile"
					:size="singleImageSize"
					:handleLoading="true"
					@cancelClick="onCancel"
				/>
			</div>
			<div v-else-if="isSingleVideo" class="bx-im-message-media-content__single-video">
				<VideoItem
					:id="firstFileId"
					:message="message"
					@cancelClick="onCancel"
				/>
			</div>
			<div v-else class="bx-im-message-media-content__gallery">
				<MediaGallery
					:files="files"
					:width="getGalleryMaxWidth()"
					:handleLoading="true"
					@cancelClick="onCancel"
				/>
			</div>
			<div v-if="onlyMedia" class="bx-im-message-media-content__status-container">
				<MessageStatus :item="message" :isOverlay="true" />
			</div>
		</div>
	`
	};

	const MAX_GALLERY_WIDTH = 460;
	const MAX_SINGLE_MEDIA_WIDTH = 460;

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
	    containerHeight: {
	      type: [Number, null],
	      default: 0
	    }
	  },
	  emits: ['cancelClick'],
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
	    hasError() {
	      return this.message.error;
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
	      if (this.fileIds.length > 1 || this.hasText) {
	        maxWidth = MAX_GALLERY_WIDTH;
	      }
	      return {
	        'max-width': `${maxWidth}px`
	      };
	    }
	  },
	  methods: {
	    onCancel(event) {
	      this.$emit('cancelClick', event);
	    }
	  },
	  template: `
		<BaseMessage 
			:item="item" 
			:dialogId="dialogId" 
			:withBackground="needBackground"
		>
			<div 
				class="bx-im-message-image__container"
				:class="{
					'--has-text': hasText,
				}"
				:style="imageContainerStyles"
			>
				<MessageHeader :withTitle="false" :item="item" class="bx-im-message-image__header" />
				<MediaContent 
					:item="message"
					:containerHeight="containerHeight"
					@cancelClick="onCancel"
				/>
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
	    this.diskService = new im_v2_provider_service_disk.DiskService();
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
	      title: main_core.Loc.getMessage('IM_MESSAGE_FILE_MENU_DOWNLOAD_FILE'),
	      onClick: function () {
	        im_v2_lib_utils.Utils.file.downloadFiles([file]);
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
	      title: main_core.Loc.getMessage('IM_MESSAGE_FILE_MENU_SAVE_ON_DISK_MSGVER_1'),
	      onClick: async function () {
	        this.menuInstance.close();
	        await this.diskService.save(this.context.files);
	        im_v2_lib_notifier.Notifier.file.onDiskSaveComplete();
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
	    ProgressBar: im_v2_component_elements_progressbar.ProgressBar
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
	  emits: ['cancelClick'],
	  computed: {
	    ProgressBarSize: () => im_v2_component_elements_progressbar.ProgressBarSize,
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
	      return im_v2_lib_utils.Utils.file.getViewerDataAttributes({
	        viewerAttributes: this.file.viewerAttrs,
	        previewImageSrc: this.file.urlPreview,
	        context: im_v2_const.FileViewerContext.dialog
	      });
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
	  methods: {
	    download() {
	      if (this.file.progress !== 100 || this.canBeOpenedWithViewer) {
	        return;
	      }
	      window.open(this.file.urlDownload, '_blank');
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    openContextMenu(event) {
	      this.$emit('openContextMenu', {
	        event,
	        fileId: this.id
	      });
	    },
	    onCancelClick(event) {
	      this.$emit('cancelClick', event);
	    }
	  },
	  template: `
		<div class="bx-im-base-file-item__container">
			<div class="bx-im-base-file-item__viewer-container" v-bind="viewerAttributes" @click="download">
				<div class="bx-im-base-file-item__icon-container" ref="loader-icon">
					<ProgressBar 
						v-if="!isLoaded" 
						:item="file"
						:size="ProgressBarSize.S"
						@cancelClick="onCancelClick"
					/>
				<div v-if="hasPreview" :style="imageStyles" class="bx-im-base-file-item__image"></div>
					<div v-else :class="iconClass" class="bx-im-base-file-item__type-icon ui-icon"><i></i></div>
				</div>
				<div class="bx-im-base-file-item__content">
					<span :title="file.name" class="bx-im-base-file-item__title">
						{{ fileShortName }}
					</span>
					<div class="bx-im-base-file-item__size">{{ fileSize }}</div>
				</div>
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
	  emits: ['cancelClick'],
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
	    },
	    onCancel(event) {
	      this.$emit('cancelClick', event);
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
					@cancelClick="onCancel"
				/>
				<DefaultMessageContent :item="item" :dialogId="dialogId" />
			</div>
			<MessageFooter :item="item" :dialogId="dialogId" />
		</BaseMessage>
	`
	};

	// @vue/component
	const TranscriptionItem = {
	  name: 'TranscriptionItem',
	  components: {
	    ExpandAnimation: im_v2_component_animation.ExpandAnimation
	  },
	  props: {
	    file: {
	      type: Object,
	      required: true
	    },
	    messageId: {
	      type: [String, Number],
	      required: true
	    },
	    isOpened: {
	      type: Boolean,
	      required: true
	    }
	  },
	  computed: {
	    transcription() {
	      return this.$store.getters['files/getTranscription'](this.file.id);
	    },
	    text() {
	      return this.isError ? this.loc('IM_MESSAGE_FILE_AUDIO_TRANSCRIPTION_ERROR') : this.transcription.transcriptText;
	    },
	    isError() {
	      return this.transcription && this.transcription.status === im_v2_const.TranscriptionStatus.ERROR;
	    },
	    showDivider() {
	      return this.isOpened && Boolean(this.message.text);
	    },
	    message() {
	      return this.$store.getters['messages/getById'](this.messageId);
	    }
	  },
	  methods: {
	    loc(code) {
	      return this.$Bitrix.Loc.getMessage(code);
	    }
	  },
	  template: `
		<div class="bx-im-audio-player__transcription-container">
			<ExpandAnimation>
				<div
					v-if="isOpened"
					:class="{'--error': isError}"
				>
					<div class="bx-im-audio-player__transcription-content">
						{{ text }}
					</div>
				</div>
			</ExpandAnimation>
			<div v-if="showDivider" class="bx-im-audio-player__transcription-divider"></div>
		</div>
	`
	};

	// @vue/component
	const TranscriptionButtonItem = {
	  name: 'TranscriptionButtonItem',
	  components: {
	    Spinner: im_v2_component_elements_loader.Spinner,
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    file: {
	      type: Object,
	      required: true
	    },
	    isOpened: {
	      type: Boolean,
	      required: true
	    }
	  },
	  emits: ['transcriptionToggle'],
	  computed: {
	    SpinnerSize: () => im_v2_component_elements_loader.SpinnerSize,
	    OutlineIcons: () => ui_iconSet_api_vue.Outline,
	    Color: () => im_v2_const.Color,
	    fileId() {
	      return this.file.id;
	    },
	    chatId() {
	      return this.file.chatId;
	    },
	    transcription() {
	      return this.$store.getters['files/getTranscription'](this.fileId);
	    },
	    status() {
	      return this.transcription ? this.transcription.status : null;
	    },
	    isPending() {
	      return this.status === im_v2_const.TranscriptionStatus.PENDING;
	    },
	    isSuccess() {
	      return this.status === im_v2_const.TranscriptionStatus.SUCCESS;
	    },
	    buttonIcon() {
	      return this.isOpened ? ui_iconSet_api_vue.Outline.CHEVRON_TOP_M : ui_iconSet_api_vue.Outline.TRANSCRIPTION;
	    },
	    withTranscription() {
	      return this.file.isTranscribable && im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.aiFileTranscriptionAvailable) && im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.copilotAvailable);
	    }
	  },
	  watch: {
	    status(newValue, oldValue) {
	      if (oldValue === im_v2_const.TranscriptionStatus.PENDING) {
	        im_v2_lib_analytics.Analytics.getInstance().audioMessage.onViewTranscription(this.chatId, newValue);
	        this.open();
	      }
	    }
	  },
	  methods: {
	    async onButtonClick() {
	      if (this.isPending) {
	        return;
	      }
	      if (this.isOpened) {
	        this.close();
	        return;
	      }
	      if (this.isSuccess) {
	        im_v2_lib_analytics.Analytics.getInstance().audioMessage.onViewTranscription(this.chatId, im_v2_const.TranscriptionStatus.SUCCESS);
	        this.open();
	        return;
	      }
	      const result = await this.transcribe();
	      if (result) {
	        this.open();
	      }
	    },
	    async transcribe() {
	      const messageService = new im_v2_provider_service_message.MessageService({
	        chatId: this.chatId
	      });
	      return messageService.transcribe(this.fileId);
	    },
	    open() {
	      this.$emit('transcriptionToggle', true);
	    },
	    close() {
	      this.$emit('transcriptionToggle', false);
	    }
	  },
	  template: `
		<div
			v-if="withTranscription"
			class="bx-im-audio-player__transcription-button-container"
		>
			<button @click="onButtonClick">
				<Spinner v-if="isPending" :size="SpinnerSize.XXS" />
				<BIcon
					v-else
					:name="buttonIcon"
					:color="Color.accentBlue"
					:size="20"
				/>
			</button>
		</div>
	`
	};

	// @vue/component
	const AudioItem = {
	  name: 'AudioItem',
	  components: {
	    AudioPlayer: im_v2_component_elements_audioplayer.AudioPlayer,
	    ProgressBar: im_v2_component_elements_progressbar.ProgressBar,
	    TranscriptionItem,
	    TranscriptionButtonItem
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    messageId: {
	      type: [String, Number],
	      required: true
	    }
	  },
	  emits: ['cancelClick'],
	  data() {
	    return {
	      isTranscriptionOpened: false
	    };
	  },
	  computed: {
	    ProgressBarSize: () => im_v2_component_elements_progressbar.ProgressBarSize,
	    file() {
	      return this.item;
	    },
	    timelineType() {
	      return Math.floor(Math.random() * 5);
	    }
	  },
	  methods: {
	    onCancelClick(event) {
	      this.$emit('cancelClick', event);
	    },
	    transcriptionToggle(status) {
	      this.isTranscriptionOpened = status;
	    }
	  },
	  template: `
		<div class="bx-im-media-audio__container">
			<ProgressBar 
				:item="file"
				:size="ProgressBarSize.S"
				@cancelClick="onCancelClick"
			/>
			<AudioPlayer
				:id="file.id"
				:messageId="messageId"
				:src="file.urlDownload"
				:file="file"
				:timelineType="timelineType"
				:authorId="file.authorId"
				:withContextMenu="false"
				:withAvatar="false"
			>
				<template #transcription-control>
					<TranscriptionButtonItem
						:file="file"
						:isOpened="isTranscriptionOpened"
						@transcriptionToggle="transcriptionToggle"
					/>
				</template>
			</AudioPlayer>
			<TranscriptionItem
				:file="file"
				:isOpened="isTranscriptionOpened"
				:messageId="messageId"
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
	  emits: ['cancelClick'],
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
	    }
	  },
	  methods: {
	    onCancel(event) {
	      this.$emit('cancelClick', event);
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
					@cancelClick="onCancel"
				/>
			</div>
			<div class="bx-im-message-audio__default-message-container">
				<DefaultMessageContent :item="item" :dialogId="dialogId" />
			</div>
			<MessageFooter :item="item" :dialogId="dialogId" />
		</BaseMessage>
	`
	};

	const FILES_LIMIT = 10;

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
	  emits: ['cancelClick'],
	  computed: {
	    FileType: () => im_v2_const.FileType,
	    message() {
	      return this.item;
	    },
	    messageId() {
	      return this.message.id;
	    },
	    fileIds() {
	      return this.message.files.slice(0, FILES_LIMIT);
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
	    },
	    onCancel(event) {
	      this.$emit('cancelClick', event);
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
						@cancelClick="onCancel"
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
	    containerHeight: {
	      type: [Number, null],
	      default: null
	    }
	  },
	  computed: {
	    FileType: () => im_v2_const.FileType,
	    message() {
	      return this.$store.getters['messages/getById'](this.item.id);
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
	      if (file.type === im_v2_const.FileType.video && hasPreview) {
	        return FileMessageType.media;
	      }
	      return FileMessageType.base;
	    },
	    isRealMessage() {
	      return this.$store.getters['messages/isRealMessage'](this.message.id);
	    }
	  },
	  methods: {
	    onCancel(event) {
	      const canceledFileId = event.file.id;
	      const uploadingService = im_v2_provider_service_uploading.UploadingService.getInstance();
	      const uploaderId = uploadingService.getUploaderIdByFileId(canceledFileId);
	      uploadingService.removeFileFromUploader({
	        uploaderId,
	        filesIds: [canceledFileId],
	        restartUploading: true
	      });
	      const uploaderFiles = uploadingService.getFiles(uploaderId);
	      if (main_core.Type.isArrayFilled(uploaderFiles)) {
	        const actualMessageFiles = uploaderFiles.map(file => {
	          return file.getId();
	        });
	        void this.$store.dispatch('messages/update', {
	          id: this.message.id,
	          fields: {
	            files: actualMessageFiles
	          }
	        });
	      } else {
	        const chatId = this.message.chatId;
	        void this.$store.dispatch('messages/delete', {
	          id: this.message.id
	        });
	        const chat = this.$store.getters['chats/getByChatId'](chatId);
	        const lastMessageId = this.$store.getters['messages/findLastChatMessageId'](chatId);
	        if (main_core.Type.isString(lastMessageId) || main_core.Type.isNumber(lastMessageId)) {
	          void this.$store.dispatch('recent/update', {
	            id: chat.dialogId,
	            fields: {
	              messageId: lastMessageId
	            }
	          });
	        } else {
	          void this.$store.dispatch('recent/delete', {
	            id: chat.dialogId
	          });
	        }
	      }
	    }
	  },
	  template: `
		<component 
			:is="componentName" 
			:item="message" 
			:dialogId="dialogId"
			:withTitle="withTitle" 
			:containerHeight="containerHeight"
			:withContextMenu="isRealMessage"
			@cancelClick="onCancel"
		/>
	`
	};

	exports.FileMessage = FileMessage;
	exports.MediaContent = MediaContent;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message,BX.Messenger.v2.Service,BX.UI.Uploader,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Animation,BX.UI.IconSet,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Const));
//# sourceMappingURL=file-message.bundle.js.map
