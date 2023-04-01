this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_popup,ui_buttons,ui_uploader_core,ui_uploader_tileWidget,main_core,ui_uploader_stackWidget) {
	'use strict';

	const StackWidgetSize = {
	  LARGE: 'large',
	  MEDIUM: 'medium',
	  SMALL: 'small',
	  TINY: 'tiny'
	};

	const progressSizes = {
	  [StackWidgetSize.LARGE]: {
	    width: 46,
	    lineSize: 5
	  },
	  [StackWidgetSize.MEDIUM]: {
	    width: 34,
	    lineSize: 4
	  },
	  [StackWidgetSize.SMALL]: {
	    width: 20,
	    lineSize: 3
	  },
	  [StackWidgetSize.TINY]: {
	    width: 14,
	    lineSize: 2
	  }
	};
	const StackUpload = {
	  name: 'StackUpload',
	  inject: ['widgetOptions'],
	  components: {
	    UploadLoader: ui_uploader_tileWidget.UploadLoader
	  },
	  props: {
	    items: {
	      type: Array,
	      required: true
	    },
	    queueItems: {
	      type: Array,
	      required: true
	    }
	  },
	  emits: ['showPopup', 'abortUpload'],
	  computed: {
	    StackWidgetSize: () => StackWidgetSize,

	    uploadFileTitle() {
	      if (this.queueItems.length > 1) {
	        return main_core.Loc.getMessage('STACK_WIDGET_FILES_UPLOADING');
	      } else {
	        return main_core.Loc.getMessage('STACK_WIDGET_FILE_UPLOADING');
	      }
	    },

	    progress() {
	      if (this.queueItems.length === 0) {
	        return 0;
	      }

	      const progress = this.queueItems.reduce((total, item) => {
	        return total + item.progress;
	      }, 0);
	      return Math.floor(progress / this.queueItems.length);
	    },

	    progressOptions() {
	      const {
	        width,
	        lineSize
	      } = progressSizes[this.widgetOptions.size];
	      return {
	        width,
	        lineSize,
	        progress: Math.max(this.progress, 10)
	      };
	    }

	  },
	  // language=Vue
	  template: `
		<div class="ui-uploader-stack-upload" @click="$emit('showPopup')">
			<div class="ui-uploader-stack-upload-box">
				<div 
					class="ui-uploader-stack-upload-abort" 
					:title="$Bitrix.Loc.getMessage('STACK_WIDGET_ABORT_UPLOAD')"
					@click.stop="$emit('abortUpload')"
				>
				</div>
				<div class="ui-uploader-stack-upload-content">
					<div class="ui-uploader-stack-upload-loader">
						<UploadLoader v-bind="progressOptions" />
					</div>
					<div class="ui-uploader-stack-upload-progress">
						<div
							v-if="widgetOptions.size === StackWidgetSize.LARGE"
							class="ui-uploader-stack-upload-title"
						>{{ uploadFileTitle }}</div>
						<div class="ui-uploader-stack-upload-percent">{{ progress }}%</div>
						<div
							v-if="queueItems.length === 1 && widgetOptions.size === StackWidgetSize.LARGE"
							class="ui-uploader-stack-upload-stats"
						>
							<span class="ui-uploader-stack-upload-total">{{
								queueItems.length ? queueItems[0].sizeFormatted : ''
							}}</span>
						</div>
					</div>
				</div>
				<div
					class="ui-uploader-stack-upload-menu"
					:title="$Bitrix.Loc.getMessage('STACK_WIDGET_OPEN_FILE_GALLERY')"
				></div>
			</div>
		</div>
	`
	};

	const StackLoad = {
	  name: 'StackLoad',
	  emits: ['showPopup'],
	  // language=Vue
	  template: `
		<div class="ui-uploader-stack-load" @click="$emit('showPopup')">
			<div class="ui-uploader-stack-load-icon"></div>
		</div>
	`
	};

	const fileIconSizes = {
	  [StackWidgetSize.LARGE]: 36,
	  [StackWidgetSize.MEDIUM]: 27,
	  [StackWidgetSize.SMALL]: 19,
	  [StackWidgetSize.TINY]: 15
	};
	const StackPreview = {
	  name: 'StackPreview',
	  inject: ['widgetOptions'],
	  components: {
	    FileIcon: ui_uploader_tileWidget.FileIcon
	  },
	  props: {
	    items: {
	      type: Array,
	      required: true
	    }
	  },
	  emits: ['showPopup'],
	  computed: {
	    FileStatus: () => ui_uploader_core.FileStatus,
	    Sizes: () => StackWidgetSize,

	    item() {
	      const item = this.items.find(item => {
	        return item.status !== ui_uploader_core.FileStatus.LOAD_FAILED || item.status !== ui_uploader_core.FileStatus.UPLOAD_FAILED;
	      });
	      return item || {};
	    },

	    fileIconSize() {
	      return fileIconSizes[this.widgetOptions.size];
	    },

	    errorsCount() {
	      return this.items.reduce((errors, item) => {
	        if (item.status === ui_uploader_core.FileStatus.LOAD_FAILED || item.status === ui_uploader_core.FileStatus.UPLOAD_FAILED) {
	          return errors + 1;
	        } else {
	          return errors;
	        }
	      }, 0);
	    }

	  },
	  // language=Vue
	  template: `
		<div class="ui-uploader-stack-preview" :class="{'--image': item.isImage}" @click="$emit('showPopup')">
			<div class="ui-uploader-stack-preview-box">
				<template v-if="item.failed">
					<div class="ui-uploader-stack-preview-error"></div>
				</template>
				<template v-else-if="item.previewUrl">
					<div
						class="ui-uploader-stack-preview-image"
						:class="{ '--default': item.previewUrl === null }"
						:style="{ backgroundImage: item.previewUrl !== null ? 'url(' + item.previewUrl + ')' : '' }">
					</div>
					<div v-if="items.length > 1" class="ui-uploader-stack-preview-stats">
						<span class="ui-uploader-stack-preview-total">{{ items.length }}</span>
					</div>
				</template>
				<template v-else>
					<template v-if="item.name && item.status !== FileStatus.LOADING">
						<div class="ui-uploader-stack-preview-file-icon">
							<FileIcon :name="item.extension" :size="fileIconSize"/>
						</div>
						<div
							v-if="[Sizes.LARGE, Sizes.MEDIUM].includes(widgetOptions.size)"
							:title="item.originalName"
							class="ui-uploader-stack-preview-file-name"
						>{{
							items.length > 1
							? this.$Bitrix.Loc.getMessage('STACK_WIDGET_FILE_COUNT', { '#count#': items.length })
							: item.originalName
						}}</div>
						<div 
							v-if="items.length > 1 && [Sizes.SMALL, Sizes.TINY].includes(widgetOptions.size)"
							class="ui-uploader-stack-preview-stats">
							<span class="ui-uploader-stack-preview-total">{{ items.length }}</span>
						</div>
					</template>
					<template v-else>
						<div class="ui-uploader-stack-preview-file-default"></div>
					</template>
				</template>
			</div>
			<div
				class="ui-uploader-stack-upload-menu"
				:title="$Bitrix.Loc.getMessage('STACK_WIDGET_OPEN_FILE_GALLERY')"
			></div>
		</div>
	`
	};

	const StackDropArea = {
	  name: 'StackDropArea',
	  inject: ['uploader', 'widgetOptions'],

	  data() {
	    return {
	      isHovering: false
	    };
	  },

	  computed: {
	    StackWidgetSize: () => ui_uploader_stackWidget.StackWidgetSize,

	    uploadFileTitle() {
	      if (this.uploader.shouldAcceptOnlyImages()) {
	        if (this.uploader.isMultiple()) {
	          return main_core.Loc.getMessage('STACK_WIDGET_UPLOAD_IMAGES');
	        } else {
	          return main_core.Loc.getMessage('STACK_WIDGET_UPLOAD_IMAGE');
	        }
	      } else {
	        if (this.uploader.isMultiple()) {
	          return main_core.Loc.getMessage('STACK_WIDGET_UPLOAD_FILES');
	        } else {
	          return main_core.Loc.getMessage('STACK_WIDGET_UPLOAD_FILE');
	        }
	      }
	    },

	    dragFileHint() {
	      if (this.uploader.isMultiple()) {
	        return main_core.Loc.getMessage('STACK_WIDGET_DRAG_FILES_HINT');
	      } else {
	        return main_core.Loc.getMessage('STACK_WIDGET_DRAG_FILE_HINT');
	      }
	    }

	  },

	  mounted() {
	    this.uploader.assignDropzone(this.$refs.container);
	    this.uploader.assignBrowse(this.$refs.container);
	  },

	  // language=Vue
	  template: `
		<div
			class="ui-uploader-stack-drop-area"
			ref="container"
			:class="{ '--hover': isHovering }"
			@mouseenter="isHovering = true"
			@mouseleave="isHovering = false"
			@dragleave="isHovering = false"
		>
			<div class="ui-uploader-stack-drop-area-content">
				<div class="ui-uploader-stack-drop-area-icon"></div>
				<div
					v-if="[StackWidgetSize.LARGE, StackWidgetSize.MEDIUM].includes(widgetOptions.size)"
					class="ui-uploader-stack-drop-area-title"
				>{{ uploadFileTitle }}</div>
				<div
					v-if="widgetOptions.size === StackWidgetSize.LARGE"
					class="ui-uploader-stack-drop-area-hint"
				>{{ dragFileHint }}</div>
			</div>
		</div>
	`
	};

	const isItemLoading = item => item.status === ui_uploader_core.FileStatus.LOADING;
	/**
	 * @memberof BX.UI.Uploader
	 */


	const StackWidgetComponent = {
	  name: 'StackWidget',
	  extends: ui_uploader_core.VueUploaderComponent,
	  components: {
	    TileList: ui_uploader_tileWidget.TileList,
	    ErrorPopup: ui_uploader_tileWidget.ErrorPopup,
	    StackUpload,
	    StackLoad,
	    StackPreview,
	    StackDropArea
	  },
	  mixins: [ui_uploader_tileWidget.DragOverMixin],
	  data: () => ({
	    popupContentId: null,
	    queueItems: [],
	    enableAnimation: true,
	    dragMode: false,
	    isMounted: false
	  }),
	  computed: {
	    containerClasses() {
	      return [{
	        '--multiple': this.uploader.isMultiple(),
	        '--only-images': this.uploader.shouldAcceptOnlyImages(),
	        '--many-items': this.items.length > 1
	      }, `--${this.widgetOptions.size}`];
	    },

	    currentComponent() {
	      if (this.items.length === 0 || this.dragOver) {
	        if (this.dragOver) {
	          this.dragMode = true;
	        }

	        return StackDropArea;
	      }

	      if (this.queueItems.length > 0) {
	        return StackUpload;
	      }

	      if (this.items.some(isItemLoading)) {
	        return StackLoad;
	      }

	      return StackPreview;
	    },

	    currentComponentProps() {
	      if (this.currentComponent === StackDropArea || this.currentComponent === StackLoad) {
	        return {};
	      } else if (this.currentComponent === StackUpload) {
	        return {
	          items: this.items,
	          queueItems: this.queueItems
	        };
	      } else if (this.currentComponent === StackPreview) {
	        return {
	          items: this.items
	        };
	      }
	    },

	    error() {
	      if (this.uploaderError) {
	        return this.uploaderError;
	      } else if (this.errorsCount > 0) {
	        return main_core.Loc.getMessage('STACK_WIDGET_FILE_UPLOAD_ERROR');
	      }

	      return null;
	    },

	    errorsCount() {
	      return this.items.reduce((errors, item) => {
	        if (item.status === ui_uploader_core.FileStatus.LOAD_FAILED || item.status === ui_uploader_core.FileStatus.UPLOAD_FAILED) {
	          return errors + 1;
	        } else {
	          return errors;
	        }
	      }, 0);
	    },

	    errorPopupOptions() {
	      return {
	        bindElement: this.$refs.item,
	        bindOptions: {
	          position: 'top'
	        },
	        darkMode: true,
	        offsetTop: 3,
	        background: '#d2000d',
	        contentBackground: 'transparent',
	        contentColor: 'white',
	        padding: this.uploaderError === null ? 10 : 20,
	        closeIcon: this.uploaderError !== null
	      };
	    }

	  },
	  watch: {
	    currentComponent(newValue, oldValue) {
	      if (this.dragOver) {
	        this.enableAnimation = false;
	      } else if (oldValue === StackDropArea && this.dragMode) {
	        this.enableAnimation = false;
	      } else if (oldValue === StackPreview) {
	        this.enableAnimation = false;
	      } else {
	        this.dragMode = false;
	        this.enableAnimation = true;
	      }
	    },

	    items: {
	      handler() {
	        if (this.items.length === 0 && this.popup) {
	          this.popup.close();
	        }
	      },

	      deep: true
	    }
	  },

	  created() {
	    this.popup = null;
	    this.adapter.subscribe('Uploader:onUploadStart', () => {
	      this.items.forEach(item => {
	        if (item.origin === ui_uploader_core.FileOrigin.CLIENT && item.queued !== true) {
	          item.queued = true;
	          this.queueItems.push(item);
	        }
	      });
	    });
	    this.adapter.subscribe('Uploader:onUploadComplete', () => {
	      this.queueItems = [];
	    });
	    this.adapter.subscribe('Item:onAdd', event => {
	      this.uploaderError = null;

	      if (this.uploader.getStatus() === ui_uploader_core.UploaderStatus.STARTED) {
	        const item = event.getData().item;
	        item.queued = true;
	        this.queueItems.push(event.getData().item);
	      }
	    });
	    this.adapter.subscribe('Item:onRemove', event => {
	      this.uploaderError = null;
	      const item = event.getData().item;
	      const position = this.queueItems.indexOf(item);

	      if (position >= 0) {
	        this.queueItems.splice(position, 1);
	      }
	    });
	  },

	  mounted() {
	    this.uploader.assignBrowse(this.$refs['add-button']);
	    this.isMounted = true;
	  },

	  methods: {
	    showPopup() {
	      if (!this.popup) {
	        const id = 'stack-uploader-' + main_core.Text.getRandom().toLowerCase();
	        this.popup = new main_popup.Popup({
	          width: 750,
	          height: 400,
	          draggable: true,
	          titleBar: main_core.Loc.getMessage('STACK_WIDGET_POPUP_TITLE'),
	          content: `<div id="${id}"></div>`,
	          cacheable: false,
	          closeIcon: true,
	          closeByEsc: true,
	          resizable: true,
	          minWidth: 450,
	          minHeight: 300,
	          events: {
	            onDestroy: () => {
	              this.popup = null;
	              this.popupContentId = null;
	            }
	          },
	          buttons: [new ui_buttons.CloseButton({
	            onclick: () => this.popup.close()
	          })]
	        });
	        this.popupContentId = `#${id}`;
	      }

	      this.popup.show();
	    },

	    abortUpload() {
	      const items = Array.from(this.queueItems);
	      this.queueItems = [];
	      items.forEach(item => {
	        this.uploader.removeFile(item.id);
	      });
	    },

	    handlePopupDestroy(error) {
	      if (this.uploaderError === error) {
	        this.uploaderError = null;
	      }
	    }

	  },
	  // language=Vue
	  template: `
		<div class="ui-uploader-stack-widget" :class="containerClasses" v-drop>
			<Teleport v-if="popupContentId !== null" :to="popupContentId">
				<TileList :items="items" />
			</Teleport>
			<div class="ui-uploader-stack-item" ref="item">
				<transition
					:leave-active-class="enableAnimation ? 'ui-uploader-stack-item-leave-active' : ''" 
					:leave-to-class="enableAnimation ? 'ui-uploader-stack-item-leave-to' : ''" 
					mode="out-in"
				>
					<keep-alive>
						<component
							:is="currentComponent"
							v-bind="currentComponentProps"
							@showPopup="showPopup"
							@abortUpload="abortUpload"
						/>
					</keep-alive>
				</transition>
			</div>
			<div v-if="uploader.isMultiple()" ref="add-button" class="ui-uploader-stack-add-btn"></div>
		</div>
		<ErrorPopup
			v-if="error !== null && isMounted"
			:error="error"
			:popup-options="errorPopupOptions"
			@onDestroy="handlePopupDestroy"
		/>
	`
	};

	/**
	 * @memberof BX.UI.Uploader
	 */
	class StackWidget extends ui_uploader_core.VueUploaderWidget {
	  constructor(uploaderOptions, stackWidgetOptions) {
	    const widgetOptions = main_core.Type.isPlainObject(stackWidgetOptions) ? Object.assign({}, stackWidgetOptions) : {};
	    super(uploaderOptions, widgetOptions);
	  }

	  getRootComponent() {
	    return StackWidgetComponent;
	  }

	}

	exports.StackWidget = StackWidget;
	exports.StackWidgetComponent = StackWidgetComponent;
	exports.StackWidgetSize = StackWidgetSize;

}((this.BX.UI.Uploader = this.BX.UI.Uploader || {}),BX.Main,BX.UI,BX.UI.Uploader,BX.UI.Uploader,BX,BX.UI.Uploader));
//# sourceMappingURL=ui.uploader.stack-widget.bundle.js.map
