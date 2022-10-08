this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_progressround,main_popup,ui_vue,ui_icons_generator,main_core,main_core_events,ui_uploader_core) {
	'use strict';

	const DropArea = ui_vue.BitrixVue.localComponent('drop-area', {
	  mounted() {
	    this.$root.getUploader().assignBrowse(this.$refs.dropArea);
	  },

	  // language=Vue
	  template: `
		<div class="ui-tile-uploader-drop-area" ref="dropArea">
			<div class="ui-tile-uploader-drop-box">
				<label class="ui-tile-uploader-drop-label">{{
					$Bitrix.Loc.getMessage('TILE_UPLOADER_DROP_FILES_HERE')
				}}</label>
				<!--<div class="ui-tile-uploader-settings"></div>-->
			</div>
		</div>
	`
	});

	const UploadLoader = ui_vue.BitrixVue.localComponent('tile-uploader.uploader-loader', {
	  props: {
	    progress: {
	      type: Number,
	      default: 0
	    },
	    width: {
	      type: Number,
	      default: 45
	    },
	    lineSize: {
	      type: Number,
	      default: 3
	    },
	    colorTrack: {
	      type: String,
	      default: '#eeeff0'
	    },
	    colorBar: {
	      type: String,
	      default: '#2fc6f6'
	    },
	    rotation: {
	      type: Boolean,
	      default: true
	    }
	  },

	  data() {
	    return {
	      loader: null
	    };
	  },

	  mounted() {
	    this.createProgressbar();
	  },

	  watch: {
	    progress() {
	      this.updateProgressbar();
	    }

	  },
	  methods: {
	    createProgressbar() {
	      this.loader = new ui_progressround.ProgressRound({
	        width: this.width,
	        lineSize: this.lineSize,
	        colorBar: this.colorBar,
	        colorTrack: this.colorTrack,
	        rotation: this.rotation,
	        value: this.progress,
	        color: ui_progressround.ProgressRound.Color.SUCCESS
	      });
	      this.loader.renderTo(this.$refs.container);
	    },

	    updateProgressbar() {
	      if (!this.loader) {
	        this.createProgressbar();
	      }

	      this.loader.update(this.progress);
	    }

	  },
	  template: `<span ref="container"></span>`
	});

	const ErrorPopup = ui_vue.BitrixVue.localComponent('ui.uploader.error-popup', {
	  props: {
	    error: {
	      type: [Object, String]
	    },
	    alignArrow: {
	      type: Boolean,
	      default: true
	    },
	    popupOptions: {
	      type: Object,

	      default() {
	        return {};
	      }

	    }
	  },

	  data() {
	    return {
	      errorPopup: null
	    };
	  },

	  watch: {
	    error(newValue) {
	      if (this.errorPopup) {
	        this.errorPopup.destroy();
	      }

	      this.errorPopup = this.createPopup(newValue);
	      this.errorPopup.show();
	    }

	  },

	  mounted() {
	    if (this.error) {
	      this.errorPopup = this.createPopup(this.error);
	      this.errorPopup.show();
	    }
	  },

	  beforeDestroy() {
	    if (this.errorPopup) {
	      this.errorPopup.destroy();
	      this.errorPopup = null;
	    }
	  },

	  methods: {
	    createContent(error) {
	      if (main_core.Type.isStringFilled(error)) {
	        return error;
	      } else if (main_core.Type.isObject(error)) {
	        return error.getMessage() + '<br>' + error.getDescription();
	      }

	      return '';
	    },

	    createPopup(error) {
	      const content = this.createContent(error);
	      let defaultOptions;

	      if (this.alignArrow && main_core.Type.isElementNode(this.popupOptions.bindElement)) {
	        const targetNode = this.popupOptions.bindElement;
	        const targetNodeWidth = targetNode.offsetWidth;
	        defaultOptions = {
	          cacheable: false,
	          animation: 'fading-slide',
	          content,
	          // minWidth: 300,
	          events: {
	            onDestroy: () => {
	              this.$emit('onDestroy', error);
	              this.errorPopup = null;
	            },
	            onShow: function (event) {
	              const popup = event.getTarget();
	              popup.getPopupContainer().style.display = 'block';
	              const popupWidth = popup.getPopupContainer().offsetWidth;
	              const offsetLeft = targetNodeWidth / 2 - popupWidth / 2;
	              const angleShift = main_popup.Popup.getOption('angleLeftOffset') - main_popup.Popup.getOption('angleMinTop');
	              popup.setAngle({
	                offset: popupWidth / 2 - angleShift
	              });
	              popup.setOffset({
	                offsetLeft: offsetLeft + main_popup.Popup.getOption('angleLeftOffset')
	              });
	            }
	          }
	        };
	      } else {
	        defaultOptions = {
	          cacheable: false,
	          animation: 'fading-slide',
	          content,
	          events: {
	            onDestroy: () => {
	              this.$emit('onDestroy', error);
	              this.errorPopup = null;
	            }
	          }
	        };
	      }

	      const options = Object.assign({}, defaultOptions, this.popupOptions);
	      return new main_popup.Popup(options);
	    }

	  },
	  template: '<span></span>'
	});

	const FileIconComponent = ui_vue.BitrixVue.localComponent('ui.uploader.file-icon', {
	  props: {
	    name: {
	      type: String
	    },
	    type: {
	      type: String
	    },
	    color: {
	      type: String
	    },
	    size: {
	      type: Number,
	      default: 36
	    }
	  },

	  mounted() {
	    const icon = new ui_icons_generator.FileIcon({
	      name: this.name,
	      fileType: this.type,
	      color: this.color,
	      size: this.size
	    });
	    icon.renderTo(this.$el);
	  },

	  template: '<span></span>'
	});

	const TileItem = ui_vue.BitrixVue.localComponent('tile', {
	  components: {
	    UploadLoader,
	    ErrorPopup,
	    FileIconComponent
	  },
	  props: {
	    item: {
	      type: Object,
	      default: {}
	    }
	  },

	  data() {
	    return {
	      tileId: 'tile-uploader-' + main_core.Text.getRandom().toLowerCase(),
	      menu: null,
	      showError: false
	    };
	  },

	  computed: {
	    FileStatus: () => ui_uploader_core.FileStatus,

	    status() {
	      if (this.item.status === ui_uploader_core.FileStatus.UPLOADING) {
	        return this.item.progress + '%';
	      } else if (this.item.status === ui_uploader_core.FileStatus.LOAD_FAILED || this.item.status === ui_uploader_core.FileStatus.UPLOAD_FAILED) {
	        return main_core.Loc.getMessage('TILE_UPLOADER_ERROR_STATUS');
	      } else {
	        return main_core.Loc.getMessage('TILE_UPLOADER_WAITING_STATUS');
	      }
	    },

	    fileSize() {
	      if ([ui_uploader_core.FileStatus.LOADING, ui_uploader_core.FileStatus.LOAD_FAILED].includes(this.item.status) && this.item.origin === ui_uploader_core.FileOrigin.SERVER) {
	        return '';
	      }

	      return this.item.sizeFormatted;
	    },

	    errorPopupOptions() {
	      const targetNode = this.$refs.container;
	      const targetNodeWidth = targetNode.offsetWidth;
	      return {
	        bindElement: targetNode,
	        darkMode: true,
	        offsetTop: 6,
	        minWidth: targetNodeWidth,
	        maxWidth: 500
	      };
	    },

	    clampedFileName() {
	      const nameParts = this.item.originalName.split('.');

	      if (nameParts.length > 1) {
	        nameParts.pop();
	      }

	      const nameWithoutExtension = nameParts.join('.');

	      if (nameWithoutExtension.length > 27) {
	        return nameWithoutExtension.substr(0, 20) + '...' + nameWithoutExtension.substr(-5);
	      }

	      return nameWithoutExtension;
	    }

	  },

	  beforeDestroy() {
	    if (this.menu) {
	      this.menu.destroy();
	      this.menu = null;
	    }
	  },

	  methods: {
	    remove() {
	      const widget = this.$root.getWidget();
	      widget.remove(this.item.id);
	    },

	    handleMouseEnter(item) {
	      if (item.error) {
	        this.showError = true;
	      }
	    },

	    handleMouseLeave() {
	      this.showError = false;
	    },

	    showMenu() {
	      if (this.menu) {
	        this.menu.destroy();
	      }

	      this.menu = main_popup.MenuManager.create({
	        id: this.tileId,
	        bindElement: this.$refs.menu,
	        angle: true,
	        offsetLeft: 13,
	        minWidth: 100,
	        cacheable: false,
	        items: [{
	          text: main_core.Loc.getMessage('TILE_UPLOADER_MENU_DOWNLOAD'),
	          href: this.item.downloadUrl
	        }, {
	          text: main_core.Loc.getMessage('TILE_UPLOADER_MENU_REMOVE'),
	          onclick: () => {
	            this.remove();
	          }
	        }],
	        events: {
	          onDestroy: () => this.menu = null
	        }
	      });
	      this.menu.show();
	    }

	  },
	  // language=Vue
	  template: `
	<transition name="ui-tile-uploader-item">
		<div
			class="ui-tile-uploader-item"
			:class="['ui-tile-uploader-item--' + item.status, { '--image': item.isImage } ]"
			ref="container"
		>
			<ErrorPopup v-if="item.error && showError" :error="item.error" :popup-options="errorPopupOptions"/>
			<div 
				class="ui-tile-uploader-item-content"
				@mouseenter="handleMouseEnter(item)" 
				@mouseleave="handleMouseLeave(item)"
			>
				<div v-if="item.status !== FileStatus.COMPLETE" class="ui-tile-uploader-item-state">
					<div class="ui-tile-uploader-item-loader" v-if="item.status === FileStatus.UPLOADING">
						<UploadLoader :progress="item.progress" :width="20" colorTrack="#73d8f8" colorBar="#fff" />
					</div>
					<div v-else class="ui-tile-uploader-item-state-icon"></div>
					<div class="ui-tile-uploader-item-status">
						<div class="ui-tile-uploader-item-status-name">{{status}}</div>
						<div v-if="fileSize" class="ui-tile-uploader-item-state-desc">{{fileSize}}</div>
					</div>
					<div class="ui-tile-uploader-item-state-remove" @click="remove" key="aaa"></div>
				</div>
				<template v-else>
					<div class="ui-tile-uploader-item-remove" @click="remove" key="remove"></div>
					<div class="ui-tile-uploader-item-actions" key="actions">
						<div class="ui-tile-uploader-item-menu" @click="showMenu" ref="menu"></div>
					</div>
				</template>
				<div class="ui-tile-uploader-item-preview">
					<div
						v-if="item.isImage"
						class="ui-tile-uploader-item-image"
						:class="{ 'ui-tile-uploader-item-image-default': item.previewUrl === null }"
						:style="{ backgroundImage: item.previewUrl !== null ? 'url(' + item.previewUrl + ')' : '' }">
					</div>
					<div 
						v-else-if="item.name && item.status !== FileStatus.LOADING" 
						class="ui-tile-uploader-item-file-icon"
					>
						<FileIconComponent :name="item.extension" />
					</div>
					<div 
						v-else 
						class="ui-tile-uploader-item-file-default"
					>
						<FileIconComponent :name="item.extension ? item.extension : '...'" :size="36" />
					</div>
				</div>
				<div v-if="item.originalName" class="ui-tile-uploader-item-name-box" :title="item.originalName">
					<div class="ui-tile-uploader-item-name">
						<span class="ui-tile-uploader-item-name-title">{{clampedFileName}}</span><!--
						--><span v-if="item.extension" class="ui-tile-uploader-item-name-extension">.{{item.extension}}</span>
					</div>
				</div>
			</div>
		</div>
	</transition>
	`
	});

	const TileList = ui_vue.BitrixVue.localComponent('tile-list', {
	  components: {
	    TileItem
	  },
	  props: {
	    items: {
	      type: Array,
	      default: []
	    }
	  },
	  // language=Vue
	  template: `
		<div class="ui-tile-uploader-items">
			<TileItem v-for="item in items" :key="item.id" :item="item" />
		</div>
	`
	});

	const DragOverMixin = {
	  directives: {
	    drop: {
	      bind(el, binding, vnode) {
	        function addClass() {
	          vnode.context.dragOver = true;
	          el.classList.add('--drag-over');
	        }

	        function removeClass() {
	          vnode.context.dragOver = false;
	          el.classList.remove('--drag-over');
	        }

	        let lastEnterTarget = null;
	        main_core.Event.bind(el, 'dragenter', event => {
	          event.preventDefault();
	          event.stopPropagation();
	          lastEnterTarget = event.target;
	          addClass();
	        });
	        main_core.Event.bind(el, 'dragleave', event => {
	          event.preventDefault();
	          event.stopPropagation();

	          if (lastEnterTarget === event.target) {
	            removeClass();
	          }
	        });
	        main_core.Event.bind(el, 'drop', event => {
	          removeClass();
	        });
	      },

	      unbind(el, binding, vnode) {
	        vnode.context.dragOver = false;
	        main_core.Event.unbindAll(el, 'dragenter');
	        main_core.Event.unbindAll(el, 'dragleave');
	        main_core.Event.unbindAll(el, 'drop');
	      }

	    }
	  },

	  data() {
	    return {
	      dragOver: false
	    };
	  }

	};

	const TileWidgetComponent = ui_vue.BitrixVue.localComponent('tile-uploader', {
	  components: {
	    DropArea,
	    TileList,
	    ErrorPopup
	  },
	  mixins: [DragOverMixin],
	  props: {
	    items: {
	      type: Array,
	      default: []
	    }
	  },

	  data() {
	    return {
	      uploaderError: null
	    };
	  },

	  computed: {
	    errorPopupOptions() {
	      return {
	        bindElement: this.$refs.container,
	        closeIcon: true,
	        padding: 20,
	        offsetLeft: 45,
	        angle: true,
	        darkMode: true,
	        bindOptions: {
	          position: 'top',
	          forceTop: true
	        }
	      };
	    }

	  },

	  created() {
	    this.$Bitrix.eventEmitter.subscribe('Uploader:onError', event => {
	      this.uploaderError = event.getData().error;
	    });
	    this.$Bitrix.eventEmitter.subscribe('Item:onAdd', event => {
	      this.uploaderError = null;
	    });
	    this.$Bitrix.eventEmitter.subscribe('Item:onRemove', event => {
	      this.uploaderError = null;
	    });
	  },

	  mounted() {
	    this.$root.getUploader().assignDropzone(this.$refs.container);
	  },

	  // language=Vue
	  template: `
		<div class="ui-tile-uploader" ref="container" v-drop>
			<ErrorPopup 
				v-if="uploaderError"
				:alignArrow="false"
				:error="uploaderError"
				:popup-options="errorPopupOptions" 
				@onDestroy="uploaderError = null" 
			/>
			<template v-if="items.length === 0">
				<DropArea />
			</template>
			<template v-else>
				<TileList :items="items"></TileList>
				<DropArea />
			</template>
		</div>
	`
	});

	/**
	 * @memberof BX.UI.Uploader
	 */

	class TileWidget extends ui_uploader_core.VueUploader {
	  constructor(uploaderOptions, tileWidgetOptions) {
	    super(uploaderOptions);
	    const widgetOptions = main_core.Type.isPlainObject(tileWidgetOptions) ? Object.assign({}, tileWidgetOptions) : {};
	  }

	  getRootComponentId() {
	    return TileWidgetComponent;
	  }

	}

	exports.TileWidget = TileWidget;
	exports.TileList = TileList;
	exports.FileIcon = FileIconComponent;
	exports.ErrorPopup = ErrorPopup;
	exports.UploadLoader = UploadLoader;
	exports.DragOverMixin = DragOverMixin;

}((this.BX.UI.Uploader = this.BX.UI.Uploader || {}),BX.UI,BX.Main,BX,BX.UI.Icons.Generator,BX,BX.Event,BX.UI.Uploader));
//# sourceMappingURL=ui.uploader.tile-widget.bundle.js.map
