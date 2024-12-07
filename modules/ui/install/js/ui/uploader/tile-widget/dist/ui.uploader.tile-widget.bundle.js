this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_events,ui_uploader_vue,ui_icons_generator,main_popup,ui_progressround,ui_uploader_tileWidget,main_core,ui_uploader_core) {
	'use strict';

	const SettingsButton = {
	  inject: ['widgetOptions', 'emitter'],
	  data: () => ({
	    selected: false
	  }),
	  methods: {
	    handleSettingsClick() {
	      this.emitter.emit('SettingsButton:onClick', {
	        container: this.$refs['container'],
	        button: this
	      });
	    },
	    getContainer() {
	      return this.$refs['container'];
	    },
	    select() {
	      this.selected = true;
	    },
	    deselect() {
	      this.selected = false;
	    }
	  },
	  // language=Vue
	  template: `
		<div 
			class="ui-tile-uploader-settings" 
			:class="{ '--selected': this.selected }" 
			@click="handleSettingsClick" 
			ref="container"
		></div>
	`
	};

	const DropArea = {
	  inject: ['uploader', 'widgetOptions', 'emitter'],
	  components: {
	    SettingsButton
	  },
	  mounted() {
	    this.uploader.assignBrowse(this.$refs.dropArea);
	  },
	  computed: {
	    dropLabel() {
	      return main_core.Loc.getMessage('TILE_UPLOADER_DROP_FILES_HERE');
	    }
	  },
	  methods: {
	    handleSettingsClick() {
	      this.emitter.emit('onSettingsButtonClick', {
	        button: this.$refs['ui-tile-uploader-settings']
	      });
	    }
	  },
	  // language=Vue
	  template: `
		<div class="ui-tile-uploader-drop-area">
			<div class="ui-tile-uploader-drop-box">
				<label class="ui-tile-uploader-drop-label" ref="dropArea">{{dropLabel}}</label>
				<SettingsButton v-if="widgetOptions.showSettingsButton" />
			</div>
		</div>
	`
	};

	/**
	 * @memberof BX.UI.Uploader
	 */
	const ErrorPopup = {
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
	  emits: ['onDestroy'],
	  watch: {
	    error(newValue) {
	      if (this.errorPopup) {
	        this.errorPopup.destroy();
	      }
	      this.errorPopup = this.createPopup(newValue);
	      this.errorPopup.show();
	    }
	  },
	  created() {
	    this.errorPopup = null;
	  },
	  mounted() {
	    if (this.error) {
	      this.errorPopup = this.createPopup(this.error);
	      this.errorPopup.show();
	    }
	  },
	  beforeUnmount() {
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
	        return error.message + '<br>' + error.description;
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
	};

	/**
	 * @memberof BX.UI.Uploader
	 */
	const FileIconComponent = {
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
	};

	const InsertIntoTextButton = {
	  name: 'InsertIntoTextButton',
	  inject: ['emitter'],
	  props: {
	    item: {
	      type: Object,
	      default: {}
	    }
	  },
	  computed: {
	    isInserted() {
	      var _this$item$customData;
	      return ((_this$item$customData = this.item.customData) == null ? void 0 : _this$item$customData.tileSelected) === true;
	    }
	  },
	  methods: {
	    click() {
	      this.emitter.emit('onInsertIntoText', {
	        item: this.item
	      });
	    },
	    handleMouseEnter(event) {
	      if (this.hintPopup) {
	        return;
	      }
	      const targetNode = event.currentTarget;
	      const targetNodeWidth = targetNode.offsetWidth;
	      this.hintPopup = new main_popup.Popup({
	        content: main_core.Loc.getMessage('TILE_UPLOADER_INSERT_INTO_THE_TEXT'),
	        cacheable: false,
	        animation: 'fading-slide',
	        bindElement: targetNode,
	        offsetTop: 0,
	        bindOptions: {
	          position: 'top'
	        },
	        darkMode: true,
	        events: {
	          onClose: () => {
	            this.hintPopup.destroy();
	            this.hintPopup = null;
	          },
	          onShow: event => {
	            const popup = event.getTarget();
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
	      });
	      this.hintPopup.show();
	    },
	    handleMouseLeave(event) {
	      if (this.hintPopup) {
	        this.hintPopup.close();
	        this.hintPopup = null;
	      }
	    }
	  },
	  // language=Vue
	  template: `
		<div 
			class="ui-tile-uploader-insert-into-text-button"
			:class="[{ '--inserted': isInserted }]"
			@mouseenter="handleMouseEnter" 
			@mouseleave="handleMouseLeave" 
			@click="click"
		></div>
	`
	};

	/**
	 * @memberof BX.UI.Uploader
	 */
	const UploadLoader = {
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
	  template: '<span ref="container"></span>'
	};

	const TileItem = {
	  components: {
	    UploadLoader,
	    ErrorPopup,
	    FileIconComponent
	  },
	  inject: ['uploader', 'widgetOptions', 'emitter'],
	  props: {
	    item: {
	      type: Object,
	      default: {}
	    }
	  },
	  data() {
	    return {
	      tileId: `tile-uploader-${main_core.Text.getRandom().toLowerCase()}`,
	      showError: false
	    };
	  },
	  computed: {
	    FileStatus: () => ui_uploader_core.FileStatus,
	    status() {
	      if (this.item.status === ui_uploader_core.FileStatus.UPLOADING) {
	        return `${this.item.progress}%`;
	      }
	      if (this.item.status === ui_uploader_core.FileStatus.LOAD_FAILED || this.item.status === ui_uploader_core.FileStatus.UPLOAD_FAILED) {
	        return main_core.Loc.getMessage('TILE_UPLOADER_ERROR_STATUS');
	      }
	      return main_core.Loc.getMessage('TILE_UPLOADER_WAITING_STATUS');
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
	      const nameParts = this.item.name.split('.');
	      if (nameParts.length > 1) {
	        nameParts.pop();
	      }
	      const nameWithoutExtension = nameParts.join('.');
	      if (nameWithoutExtension.length > 27) {
	        return nameWithoutExtension.substr(0, 17) + '...' + nameWithoutExtension.substr(-5);
	      }
	      return nameWithoutExtension;
	    },
	    showItemMenuButton() {
	      if (main_core.Type.isBoolean(this.widgetOptions.showItemMenuButton)) {
	        return this.widgetOptions.showItemMenuButton;
	      } else {
	        return this.menuItems.length > 0;
	      }
	    },
	    menuItems() {
	      const items = [];
	      items.push({
	        id: 'filesize',
	        text: main_core.Loc.getMessage('TILE_UPLOADER_FILE_SIZE', {
	          '#filesize#': this.item.sizeFormatted
	        }),
	        disabled: true
	      }, {
	        delimiter: true
	      });
	      if (this.widgetOptions.insertIntoText === true) {
	        items.push({
	          id: 'insert-into-text',
	          text: main_core.Loc.getMessage('TILE_UPLOADER_INSERT_INTO_THE_TEXT'),
	          onclick: () => {
	            if (this.menu) {
	              this.menu.close();
	            }
	            this.emitter.emit('onInsertIntoText', {
	              item: this.item
	            });
	          }
	        });
	      }
	      if (main_core.Type.isStringFilled(this.item.downloadUrl)) {
	        items.push({
	          id: 'download',
	          text: main_core.Loc.getMessage('TILE_UPLOADER_MENU_DOWNLOAD'),
	          href: this.item.downloadUrl,
	          onclick: () => {
	            if (this.menu) {
	              this.menu.close();
	            }
	          }
	        }, {
	          id: 'remove',
	          text: main_core.Loc.getMessage('TILE_UPLOADER_MENU_REMOVE'),
	          onclick: () => {
	            this.remove();
	          }
	        });
	      }
	      return items;
	    },
	    extraAction() {
	      return this.widgetOptions.slots && this.widgetOptions.slots[ui_uploader_tileWidget.TileWidgetSlot.ITEM_EXTRA_ACTION] ? this.widgetOptions.slots[ui_uploader_tileWidget.TileWidgetSlot.ITEM_EXTRA_ACTION] : this.widgetOptions.insertIntoText === true ? InsertIntoTextButton : null;
	    },
	    isSelected() {
	      return this.item.customData.tileSelected === true;
	    }
	  },
	  created() {
	    this.menu = null;
	  },
	  beforeUnmount() {
	    if (this.menu) {
	      this.menu.destroy();
	      this.menu = null;
	    }
	  },
	  methods: {
	    remove() {
	      this.uploader.removeFile(this.item.id);
	    },
	    handleMouseEnter(item) {
	      if (item.error) {
	        this.showError = true;
	      }
	    },
	    handleMouseLeave() {
	      this.showError = false;
	    },
	    toggleMenu() {
	      setTimeout(() => {
	        if (this.menu) {
	          if (this.menu.getPopupWindow().isShown()) {
	            this.menu.close();
	            return;
	          }
	          this.menu.destroy();
	        }
	        this.menu = main_popup.MenuManager.create({
	          id: this.tileId,
	          bindElement: this.$refs.menu,
	          angle: true,
	          offsetLeft: 13,
	          minWidth: 100,
	          cacheable: false,
	          items: this.menuItems,
	          events: {
	            onDestroy: () => {
	              this.menu = null;
	            }
	          }
	        });
	        this.emitter.emit('TileItem:onMenuCreate', {
	          menu: this.menu,
	          item: this.item
	        });
	        this.menu.show();
	      });
	    }
	  },
	  // language=Vue
	  template: `
	<div
		class="ui-tile-uploader-item"
		:class="['ui-tile-uploader-item--' + item.status, { '--image': item.isImage, '--selected': isSelected } ]"
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
					<div class="ui-tile-uploader-item-actions-pad">
						<div v-if="extraAction" class="ui-tile-uploader-item-extra-actions">
							<component :is="extraAction" :item="this.item"></component>
						</div>
						<div v-if="showItemMenuButton" class="ui-tile-uploader-item-menu" @click="toggleMenu" ref="menu"></div>
					</div>
				</div>
			</template>
			<div class="ui-tile-uploader-item-preview">
				<div
					v-if="item.previewUrl"
					class="ui-tile-uploader-item-image"
					:class="{ 'ui-tile-uploader-item-image-default': item.previewUrl === null }"
					:style="{ backgroundImage: item.previewUrl !== null ? 'url(' + item.previewUrl + ')' : '' }">
				</div>
				<div 
					v-else-if="item.name" 
					class="ui-tile-uploader-item-file-icon"
				>
					<FileIconComponent :name="item.extension ? item.extension : '...'" />
				</div>
				<div 
					v-else 
					class="ui-tile-uploader-item-file-default"
				>
					<FileIconComponent :name="item.extension ? item.extension : '...'" :size="36" />
				</div>
			</div>
			<div v-if="item.name" class="ui-tile-uploader-item-name-box" :title="item.name">
				<div class="ui-tile-uploader-item-name">
					<span class="ui-tile-uploader-item-name-title">{{clampedFileName}}</span><!--
					--><span v-if="item.extension" class="ui-tile-uploader-item-name-extension">.{{item.extension}}</span>
				</div>
			</div>
		</div>
	</div>
	`
	};

	const TileMoreItem = {
	  components: {
	    UploadLoader: ui_uploader_tileWidget.UploadLoader,
	    ErrorPopup: ui_uploader_tileWidget.ErrorPopup,
	    FileIconComponent
	  },
	  emit: ['onClick'],
	  props: {
	    hiddenFilesCount: {
	      type: Number,
	      default: 0
	    }
	  },
	  computed: {
	    moreButtonCaption() {
	      return main_core.Loc.getMessage('TILE_UPLOADER_MORE_BUTTON_CAPTION', {
	        '#COUNT#': `<span class="ui-tile-uploader-item-more-count">${this.hiddenFilesCount}</span>`
	      });
	    }
	  },
	  // language=Vue
	  template: `
		<div class="ui-tile-uploader-item" @click="$emit('onClick')">
			<div class="ui-tile-uploader-item-more">
				<div class="ui-tile-uploader-item-more-icon"></div>
				<div class="ui-tile-uploader-item-more-label" v-html="moreButtonCaption"></div>
			</div>
		</div>
	`
	};

	/**
	 * @memberof BX.UI.Uploader
	 */
	const TileList = {
	  components: {
	    TileItem,
	    TileMoreItem
	  },
	  emits: ['onUnmount'],
	  props: {
	    autoCollapse: {
	      type: Boolean,
	      default: false
	    },
	    items: {
	      type: Array,
	      default: []
	    }
	  },
	  data: () => ({
	    pageSize: 5,
	    firstHiddenItem: null,
	    lastHiddenItem: null
	  }),
	  created() {
	    this.moreItemBlocked = false;
	    if (!this.autoCollapse) {
	      return;
	    }
	    if (this.items.length > this.pageSize) {
	      this.firstHiddenItem = this.items[this.pageSize];
	      this.lastHiddenItem = this.items[this.items.length - 1];
	    }
	  },
	  unmounted() {
	    this.$emit('onUnmount');
	  },
	  computed: {
	    visibleItems() {
	      if (this.firstHiddenItem === null) {
	        return this.items;
	      }
	      const index = this.items.indexOf(this.firstHiddenItem);
	      if (index === -1) {
	        this.resetMoreItem();
	        return this.items;
	      }
	      return this.items.slice(0, index);
	    },
	    realtimeItems() {
	      if (this.lastHiddenItem === null) {
	        return [];
	      }
	      const index = this.items.indexOf(this.lastHiddenItem);
	      if (index === -1) {
	        this.resetMoreItem();
	        return [];
	      }
	      return this.items.slice(index + 1);
	    },
	    hiddenFilesCount() {
	      if (this.lastHiddenItem === null) {
	        return 0;
	      }
	      const firstIndex = this.items.indexOf(this.firstHiddenItem);
	      const lastIndex = this.items.indexOf(this.lastHiddenItem);
	      if (firstIndex === -1 || lastIndex === -1) {
	        this.resetMoreItem();
	        return 0;
	      }
	      return lastIndex - firstIndex + 1;
	    }
	  },
	  methods: {
	    getMore() {
	      if (this.moreItemBlocked) {
	        return;
	      }
	      this.pageSize = Math.min(this.pageSize + 5, 30);
	      const currentFirstIndex = this.items.indexOf(this.firstHiddenItem);
	      const lastIndex = this.items.indexOf(this.lastHiddenItem);
	      const newFirstIndex = currentFirstIndex + this.pageSize;
	      const nextFirstIndex = newFirstIndex > lastIndex ? lastIndex + 1 : newFirstIndex;
	      let itemsToShow = nextFirstIndex - currentFirstIndex;
	      for (let i = currentFirstIndex, delay = 0; i < nextFirstIndex; i++, delay++) {
	        this.moreItemBlocked = true;
	        setTimeout(() => {
	          if (i === lastIndex) {
	            this.resetMoreItem();
	          } else {
	            this.firstHiddenItem = this.items[i + 1];
	          }
	          itemsToShow--;
	          if (itemsToShow === 0) {
	            this.moreItemBlocked = false;
	          }
	        }, 100 * delay);
	      }
	    },
	    resetMoreItem() {
	      this.firstHiddenItem = null;
	      this.lastHiddenItem = null;
	    }
	  },
	  // language=Vue
	  template: `
		<div class="ui-tile-uploader-items">
			<transition-group name="ui-tile-uploader-item" type="animation">
				<TileItem v-for="item in visibleItems" :key="item.id" :item="item" />
			</transition-group>
			<transition name="ui-tile-uploader-item" type="animation">
				<TileMoreItem v-if="hiddenFilesCount > 0" :hidden-files-count="hiddenFilesCount" @onClick="getMore"/>
			</transition>
			<transition-group name="ui-tile-uploader-item" type="animation">
				<TileItem v-for="item in realtimeItems" :key="item.id" :item="item" />
			</transition-group>
		</div>
	`
	};

	/**
	 * @memberof BX.UI.Uploader
	 */
	const DragOverMixin = {
	  directives: {
	    drop: {
	      beforeMount(el, binding, vnode) {
	        if (binding.value === false) {
	          return;
	        }
	        function addClass() {
	          binding.instance.dragOver = true;
	          el.classList.add('--drag-over');
	        }
	        function removeClass() {
	          binding.instance.dragOver = false;
	          el.classList.remove('--drag-over');
	        }
	        let lastEnterTarget = null;
	        main_core.Event.bind(el, 'dragenter', event => {
	          ui_uploader_core.hasDataTransferOnlyFiles(event.dataTransfer, false).then(success => {
	            if (success) {
	              event.preventDefault();
	              event.stopPropagation();
	              lastEnterTarget = event.target;
	              addClass();
	            }
	          }).catch(() => {
	            // no-op
	          });
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
	      unmounted(el, binding, vnode) {
	        if (binding.value === false) {
	          return;
	        }
	        binding.instance.dragOver = false;
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

	/**
	 * @memberof BX.UI.Uploader
	 */
	const TileWidgetComponent = {
	  name: 'TileWidget',
	  extends: ui_uploader_vue.VueUploaderComponent,
	  components: {
	    DropArea,
	    TileList,
	    ErrorPopup
	  },
	  mixins: [DragOverMixin],
	  data() {
	    return {
	      isMounted: false,
	      autoCollapse: false
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
	    },
	    TileWidgetSlot: () => ui_uploader_tileWidget.TileWidgetSlot,
	    slots() {
	      const slots = main_core.Type.isPlainObject(this.widgetOptions.slots) ? this.widgetOptions.slots : {};
	      return {
	        [ui_uploader_tileWidget.TileWidgetSlot.BEFORE_TILE_LIST]: slots[ui_uploader_tileWidget.TileWidgetSlot.BEFORE_TILE_LIST],
	        [ui_uploader_tileWidget.TileWidgetSlot.AFTER_TILE_LIST]: slots[ui_uploader_tileWidget.TileWidgetSlot.AFTER_TILE_LIST],
	        [ui_uploader_tileWidget.TileWidgetSlot.BEFORE_DROP_AREA]: slots[ui_uploader_tileWidget.TileWidgetSlot.BEFORE_DROP_AREA],
	        [ui_uploader_tileWidget.TileWidgetSlot.AFTER_DROP_AREA]: slots[ui_uploader_tileWidget.TileWidgetSlot.AFTER_DROP_AREA]
	      };
	    },
	    enableDropzone() {
	      return this.widgetOptions.enableDropzone !== false;
	    }
	  },
	  created() {
	    this.autoCollapse = main_core.Type.isBoolean(this.widgetOptions.autoCollapse) ? this.widgetOptions.autoCollapse : this.items.length > 0;
	    this.adapter.subscribe('Item:onAdd', event => {
	      this.uploaderError = null;
	    });
	    this.adapter.subscribe('Item:onRemove', event => {
	      this.uploaderError = null;
	    });
	  },
	  mounted() {
	    if (this.enableDropzone) {
	      this.uploader.assignDropzone(this.$refs.container);
	    }
	    this.isMounted = true;
	  },
	  methods: {
	    enableAutoCollapse() {
	      this.autoCollapse = true;
	    },
	    disableAutoCollapse() {
	      this.autoCollapse = false;
	    },
	    handlePopupDestroy(error) {
	      if (this.uploaderError === error) {
	        this.uploaderError = null;
	      }
	    }
	  },
	  // language=Vue
	  template: `
		<div class="ui-tile-uploader" ref="container" v-drop="enableDropzone">
			<component :is="slots[TileWidgetSlot.BEFORE_TILE_LIST]"></component>
			<TileList 
				v-if="items.length !== 0" 
				:items="items" 
				:auto-collapse="autoCollapse" 
				@onUnmount="this.autoCollapse = false"
			/>
			<component :is="slots[TileWidgetSlot.AFTER_TILE_LIST]"></component>
			<component :is="slots[TileWidgetSlot.BEFORE_DROP_AREA]"></component>
			<DropArea />
			<component :is="slots[TileWidgetSlot.AFTER_DROP_AREA]"></component>
		</div>
		<ErrorPopup
			v-if="uploaderError && isMounted"
			:alignArrow="false"
			:error="uploaderError"
			:popup-options="errorPopupOptions"
			@onDestroy="handlePopupDestroy"
		/>
	`
	};

	/**
	 * @memberof BX.UI.Uploader
	 */
	class TileWidget extends ui_uploader_vue.VueUploaderWidget {
	  constructor(uploaderOptions, tileWidgetOptions) {
	    const widgetOptions = main_core.Type.isPlainObject(tileWidgetOptions) ? Object.assign({}, tileWidgetOptions) : {};
	    super(uploaderOptions, widgetOptions);
	  }
	  defineComponent() {
	    return TileWidgetComponent;
	  }
	}

	const TileWidgetSlot = {
	  BEFORE_TILE_LIST: 'beforeTileList',
	  AFTER_TILE_LIST: 'afterTileList',
	  BEFORE_DROP_AREA: 'beforeDropArea',
	  AFTER_DROP_AREA: 'afterDropArea',
	  ITEM_EXTRA_ACTION: 'Item:extraAction'
	};

	exports.TileWidget = TileWidget;
	exports.TileWidgetComponent = TileWidgetComponent;
	exports.TileWidgetSlot = TileWidgetSlot;
	exports.TileList = TileList;
	exports.FileIcon = FileIconComponent;
	exports.ErrorPopup = ErrorPopup;
	exports.UploadLoader = UploadLoader;
	exports.DragOverMixin = DragOverMixin;

}((this.BX.UI.Uploader = this.BX.UI.Uploader || {}),BX.Event,BX.UI.Uploader,BX.UI.Icons.Generator,BX.Main,BX.UI,BX.UI.Uploader,BX,BX.UI.Uploader));
//# sourceMappingURL=ui.uploader.tile-widget.bundle.js.map
