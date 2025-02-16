/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_uploader_tileWidget,main_core,main_core_events,ui_lexical_core,ui_textEditor,ui_uploader_vue,ui_uploader_core,ui_vue3) {
	'use strict';

	const {
	  DRAG_END_COMMAND,
	  DRAG_START_COMMAND
	} = ui_textEditor.Commands;
	var _textEditor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("textEditor");
	var _uploaderAdapter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploaderAdapter");
	var _uploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploader");
	var _allowDropFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("allowDropFiles");
	var _syncHighlightsDebounced = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("syncHighlightsDebounced");
	var _lastInserted = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastInserted");
	var _createTextEditor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createTextEditor");
	var _createUploaderAdapter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createUploaderAdapter");
	var _registerCommands = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("registerCommands");
	var _syncHighlights = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("syncHighlights");
	var _isInsertedChanged = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isInsertedChanged");
	class RichTextArea extends main_core_events.EventEmitter {
	  constructor(richTextAreaOptions) {
	    super();
	    Object.defineProperty(this, _isInsertedChanged, {
	      value: _isInsertedChanged2
	    });
	    Object.defineProperty(this, _syncHighlights, {
	      value: _syncHighlights2
	    });
	    Object.defineProperty(this, _registerCommands, {
	      value: _registerCommands2
	    });
	    Object.defineProperty(this, _createUploaderAdapter, {
	      value: _createUploaderAdapter2
	    });
	    Object.defineProperty(this, _createTextEditor, {
	      value: _createTextEditor2
	    });
	    Object.defineProperty(this, _textEditor, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _uploaderAdapter, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _uploader, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _allowDropFiles, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _syncHighlightsDebounced, {
	      writable: true,
	      value: main_core.Runtime.debounce(babelHelpers.classPrivateFieldLooseBase(this, _syncHighlights)[_syncHighlights], 500)
	    });
	    Object.defineProperty(this, _lastInserted, {
	      writable: true,
	      value: new Set()
	    });
	    this.setEventNamespace('BX.UI.RichTextArea');
	    const _options = main_core.Type.isPlainObject(richTextAreaOptions) ? richTextAreaOptions : {};
	    this.subscribeFromOptions(_options.widgetOptions.events);
	    babelHelpers.classPrivateFieldLooseBase(this, _createTextEditor)[_createTextEditor](_options.editorOptions, _options.editorInstance);
	    babelHelpers.classPrivateFieldLooseBase(this, _createUploaderAdapter)[_createUploaderAdapter](_options.uploaderOptions, _options.uploaderInstance, _options.files);
	    const fileInfos = babelHelpers.classPrivateFieldLooseBase(this, _uploaderAdapter)[_uploaderAdapter].getUploader().getFiles().map(file => {
	      return file.toJSON();
	    });
	    this.getEditor().dispatchCommand(ui_textEditor.Plugins.File.ADD_FILES_COMMAND, fileInfos);
	    babelHelpers.classPrivateFieldLooseBase(this, _registerCommands)[_registerCommands]();
	  }
	  getUploaderAdapter() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploaderAdapter)[_uploaderAdapter];
	  }
	  getUploader() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploaderAdapter)[_uploaderAdapter].getUploader();
	  }
	  getFileCount() {
	    return this.getUploader().getFiles().length;
	  }
	  getEditor() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _textEditor)[_textEditor];
	  }
	  isFilePluginEnabled() {
	    const filePlugin = this.getEditor().getPlugin('File');
	    return (filePlugin == null ? void 0 : filePlugin.isEnabled()) === true;
	  }
	  canDropFiles() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _allowDropFiles)[_allowDropFiles];
	  }
	  insertFile(fileInfo) {
	    this.getEditor().dispatchCommand(ui_textEditor.Plugins.File.INSERT_FILE_COMMAND, {
	      serverFileId: fileInfo.serverFileId,
	      width: 600,
	      // half size of imagePreviewWidth
	      height: 600,
	      // half size of imagePreviewHeight
	      info: fileInfo
	    });
	  }
	  removeFile(serverFileId) {
	    this.getEditor().dispatchCommand(ui_textEditor.Plugins.File.REMOVE_FILE_COMMAND, {
	      serverFileId,
	      skipHistoryStack: true
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _syncHighlights)[_syncHighlights](); // onChange doesn't emit due to history-merge
	  }

	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _textEditor)[_textEditor].destroy();
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].destroy();
	    babelHelpers.classPrivateFieldLooseBase(this, _textEditor)[_textEditor] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader] = null;
	  }
	}
	function _createTextEditor2(editorOptions, editorInstance) {
	  if (editorInstance) {
	    babelHelpers.classPrivateFieldLooseBase(this, _textEditor)[_textEditor] = editorInstance;
	  } else {
	    const options = main_core.Type.isPlainObject(editorOptions) ? {
	      ...editorOptions
	    } : {};
	    babelHelpers.classPrivateFieldLooseBase(this, _textEditor)[_textEditor] = new ui_textEditor.TextEditor(options);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _textEditor)[_textEditor].subscribeFromOptions({
	    onChange: event => {
	      const {
	        tags,
	        isInitialChange
	      } = event.getData();
	      if (tags.has('historic')) {
	        // Undo/Redo case uses setEditorState that's why we need a new update circle
	        this.getEditor().update(() => {
	          babelHelpers.classPrivateFieldLooseBase(this, _syncHighlights)[_syncHighlights]();
	        });
	      } else if (isInitialChange) {
	        babelHelpers.classPrivateFieldLooseBase(this, _syncHighlights)[_syncHighlights](true);
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _syncHighlightsDebounced)[_syncHighlightsDebounced]();
	      }
	    }
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _textEditor)[_textEditor];
	}
	function _createUploaderAdapter2(uploaderOptions, uploader, files) {
	  if (uploader instanceof ui_uploader_core.Uploader) {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderAdapter)[_uploaderAdapter] = new ui_uploader_vue.VueUploaderAdapter(uploader);
	  } else {
	    const options = main_core.Type.isPlainObject(uploaderOptions) ? uploaderOptions : {};
	    const defaultOptions = {
	      imagePreviewHeight: 1200,
	      // double size (see DiskUploaderController)
	      imagePreviewWidth: 1200,
	      imagePreviewQuality: 0.85,
	      treatOversizeImageAsFile: true,
	      ignoreUnknownImageTypes: true,
	      multiple: true
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderAdapter)[_uploaderAdapter] = new ui_uploader_vue.VueUploaderAdapter({
	      ...defaultOptions,
	      ...options
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderAdapter)[_uploaderAdapter].subscribeFromOptions({
	    'Item:onAdd': event => {
	      const item = event.getData().item;
	      const fileCount = this.getFileCount();
	      this.emit('Item:onAdd', {
	        item,
	        fileCount
	      });
	    },
	    'Item:onComplete': event => {
	      const item = event.getData().item;
	      const fileCount = this.getFileCount();
	      this.getEditor().dispatchCommand(ui_textEditor.Plugins.File.ADD_FILE_COMMAND, item);
	      this.emit('Item:onComplete', {
	        item,
	        fileCount
	      });
	    },
	    'Item:onRemove': event => {
	      const item = event.getData().item;
	      this.removeFile(event.getData().item.serverFileId);
	      const fileCount = this.getFileCount();
	      this.emit('Item:onRemove', {
	        item,
	        fileCount
	      });
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderAdapter)[_uploaderAdapter].getUploader().addFiles(files);
	}
	function _registerCommands2() {
	  this.getEditor().registerCommand(ui_lexical_core.PASTE_COMMAND, clipboardEvent => {
	    const clipboardData = clipboardEvent.clipboardData;
	    if (!clipboardData || !ui_uploader_core.isFilePasted(clipboardData)) {
	      return false;
	    }
	    clipboardEvent.preventDefault();
	    ui_uploader_core.getFilesFromDataTransfer(clipboardData).then(files => {
	      if (files.length > 0) {
	        this.emit('onBeforeFilePaste');
	      }
	      files.forEach(file => {
	        this.getUploader().addFile(file, {
	          events: {
	            [ui_uploader_core.FileEvent.LOAD_ERROR]: () => {},
	            [ui_uploader_core.FileEvent.UPLOAD_ERROR]: () => {},
	            [ui_uploader_core.FileEvent.UPLOAD_COMPLETE]: event => {
	              const uploaderFile = event.getTarget();
	              this.emit('onFilePaste', {
	                file: uploaderFile
	              });
	              this.insertFile(uploaderFile.toJSON());
	            }
	          }
	        });
	      });
	    }).catch(() => {
	      console.error('RichTextArea: clipboard pasting error.');
	    });
	    return true;
	  }, ui_lexical_core.COMMAND_PRIORITY_NORMAL);
	  this.getEditor().registerCommand(DRAG_START_COMMAND, () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _allowDropFiles)[_allowDropFiles] = false;
	  }, ui_lexical_core.COMMAND_PRIORITY_LOW);
	  this.getEditor().registerCommand(DRAG_END_COMMAND, () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _allowDropFiles)[_allowDropFiles] = true;
	  }, ui_lexical_core.COMMAND_PRIORITY_LOW);
	}
	function _syncHighlights2(initialSync = false) {
	  this.getEditor().dispatchCommand(ui_textEditor.Plugins.File.GET_INSERTED_FILES_COMMAND, nodes => {
	    const inserted = new Set();
	    for (const node of nodes) {
	      const {
	        serverFileId
	      } = node.getInfo();
	      if (main_core.Type.isStringFilled(serverFileId) || main_core.Type.isNumber(serverFileId)) {
	        inserted.add(serverFileId);
	      }
	    }
	    const isInsertedChanged = babelHelpers.classPrivateFieldLooseBase(this, _isInsertedChanged)[_isInsertedChanged](inserted);
	    babelHelpers.classPrivateFieldLooseBase(this, _lastInserted)[_lastInserted] = new Set(inserted);
	    let hasInsertedItems = false;
	    this.getUploader().getFiles().forEach(file => {
	      if (inserted.has(file.getServerFileId())) {
	        hasInsertedItems = true;
	        file.setCustomData('tileSelected', true);
	        inserted.delete(file.getServerFileId());
	      } else {
	        file.setCustomData('tileSelected', false);
	      }
	    });

	    // Redo/Undo history can have files that were removed from uploader
	    for (const serverFileId of inserted) {
	      this.richTextArea.removeFile(serverFileId);
	    }
	    if (!initialSync && isInsertedChanged) {
	      this.emit('Item:onInsertChange', {
	        hasInsertedItems
	      });
	    }
	  });
	}
	function _isInsertedChanged2(inserted) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _lastInserted)[_lastInserted].size !== inserted.size) {
	    return true;
	  }
	  for (const serverFileId of babelHelpers.classPrivateFieldLooseBase(this, _lastInserted)[_lastInserted]) {
	    if (!inserted.has(serverFileId)) {
	      return true;
	    }
	  }
	  return false;
	}

	// @vue/component
	const DropArea = {
	  name: 'DropArea',
	  props: {
	    show: {
	      type: Boolean,
	      required: true
	    }
	  },
	  template: `
		<Transition name="ui-rich-text-area-fade">
			<div v-if="show" class="ui-rich-text-area-drop-area">
				<div class="ui-rich-text-area-drop-area-box">
					<label class="ui-rich-text-area-drop-area-text">
						{{ $Bitrix.Loc.getMessage('UI_RICH_TEXT_AREA_DROP_AREA') }}
					</label>
				</div>
			</div>
		</Transition>
	`
	};

	const ActionButton = {
	  name: 'ActionButton',
	  props: {
	    icon: {
	      type: String
	    },
	    title: {
	      type: String
	    },
	    counter: {
	      type: Number,
	      default: 0
	    },
	    selected: {
	      type: Boolean,
	      default: false
	    },
	    buttonRef: {
	      default: null
	    }
	  },
	  // language=Vue
	  template: `
		<button class="ui-rich-text-area-action" :class="{ '--selected': selected }" :ref="buttonRef">
			<span class="ui-rich-text-area-action-icon"><span
				:class="icon"
				class="ui-icon-set"
				style="--ui-icon-set__icon-color: var(--ui-color-base-90)"
			></span></span>
			<span class="ui-rich-text-area-action-title">{{ title }}</span>
			<span class="ui-rich-text-area-action-counter" v-show="counter > 0">
				<span class="ui-counter ui-counter-sm ui-counter-gray"><span class="ui-counter-inner">{{ counter }}</span></span>
			</span>
		</button>
	`
	};

	const FileButton = {
	  name: 'FileButton',
	  components: {
	    ActionButton
	  },
	  // language=Vue
	  template: `
		<ActionButton icon="--attach" :title="$Bitrix.Loc.getMessage('UI_RICH_TEXT_AREA_UPLOAD_FILE')" />
	`
	};

	const CreateDocumentButton = {
	  name: 'CreateDocumentButton',
	  components: {
	    ActionButton
	  },
	  // language=Vue
	  template: `
		<ActionButton icon="--file" :title="$Bitrix.Loc.getMessage('UI_RICH_TEXT_AREA_CREATE_DOCUMENT')" />
	`
	};

	const RecordVideoButton = {
	  name: 'RecordVideoButton',
	  components: {
	    ActionButton
	  },
	  // language=Vue
	  template: `
		<ActionButton icon="--video-3" :title="$Bitrix.Loc.getMessage('UI_RICH_TEXT_AREA_RECORD_VIDEO')" />
	`
	};

	/**
	 * @memberof BX.UI.RichTextArea
	 */
	const RichTextAreaComponent = {
	  name: 'RichTextAreaComponent',
	  components: {
	    TextEditorComponent: ui_textEditor.TextEditorComponent,
	    TileWidgetComponent: ui_uploader_tileWidget.TileWidgetComponent,
	    DropArea,
	    FileButton,
	    ActionButton,
	    CreateDocumentButton,
	    RecordVideoButton
	  },
	  props: {
	    editorOptions: {
	      type: Object
	    },
	    editorInstance: {
	      type: ui_textEditor.TextEditor
	    },
	    uploaderOptions: {
	      type: Object
	    },
	    uploaderInstance: {
	      type: ui_uploader_core.Uploader
	    },
	    widgetOptions: {
	      type: Object,
	      default: {}
	    },
	    files: {
	      type: Array
	    }
	  },
	  data() {
	    return {
	      showDropArea: false,
	      uploaderVisibility: false
	    };
	  },
	  beforeCreate() {
	    this.richTextArea = new RichTextArea({
	      editorOptions: this.editorOptions,
	      editorInstance: this.editorInstance,
	      uploaderOptions: this.uploaderOptions,
	      uploaderInstance: this.uploaderInstance,
	      widgetOptions: this.widgetOptions,
	      files: this.files
	    });
	    this.richTextArea.subscribe('Item:onAdd', () => {
	      this.uploaderVisibility = true;
	    });
	    this.fileButtonRef = ui_vue3.ref(null);
	  },
	  created() {
	    this.uploaderVisibility = this.richTextArea.getFileCount() > 0;
	  },
	  methods: {
	    getRichTextArea() {
	      return this.richTextArea;
	    },
	    getEditor() {
	      return this.richTextArea.getEditor();
	    },
	    getUploader() {
	      return this.richTextArea.getUploader();
	    },
	    getUploaderAdapter() {
	      return this.richTextArea.getUploaderAdapter();
	    },
	    onDragOver(event) {
	      if (this.richTextArea.canDropFiles()) {
	        event.preventDefault();
	      }
	    },
	    onDragEnter(event) {
	      if (!this.richTextArea.canDropFiles()) {
	        return;
	      }
	      event.preventDefault();
	      event.stopPropagation();
	      void ui_uploader_core.hasDataTransferOnlyFiles(event.dataTransfer, false).then(success => {
	        if (!success) {
	          return;
	        }
	        this.lastDropAreaEnterTarget = event.target;
	        this.showDropArea = true;
	      });
	    },
	    onDragLeave(event) {
	      if (!this.richTextArea.canDropFiles()) {
	        return;
	      }
	      event.preventDefault();
	      event.stopPropagation();
	      if (this.lastDropAreaEnterTarget === event.target) {
	        this.showDropArea = false;
	      }
	    },
	    onDrop(event) {
	      if (!this.richTextArea.canDropFiles()) {
	        return;
	      }
	      event.preventDefault();
	      void ui_uploader_core.getFilesFromDataTransfer(event.dataTransfer).then(files => {
	        this.getUploader().addFiles(files);
	        this.getEditor().expand();
	      });
	      this.showDropArea = false;
	    }
	  },
	  computed: {
	    tileWidgetOptions() {
	      const options = this.widgetOptions;
	      const tileWidgetOptions = {
	        insertIntoText: main_core.Type.isBoolean(options.insertIntoText) ? options.insertIntoText : true,
	        ...(main_core.Type.isPlainObject(options.tileWidgetOptions) ? options.tileWidgetOptions : {})
	      };
	      tileWidgetOptions.enableDropzone = false;
	      if (tileWidgetOptions.insertIntoText) {
	        tileWidgetOptions.events = tileWidgetOptions.events || {};
	        tileWidgetOptions.events.onInsertIntoText = event => {
	          this.richTextArea.insertFile(event.getData().item);
	        };
	      }
	      return tileWidgetOptions;
	    },
	    isUploadEnabled() {
	      return this.getRichTextArea().isFilePluginEnabled();
	    }
	  },
	  mounted() {
	    if (this.isUploadEnabled) {
	      this.getUploader().assignBrowse(this.fileButtonRef.value);
	    }
	  },
	  unmounted() {
	    this.richTextArea.destroy();
	    this.richTextArea = null;
	  },
	  // language=Vue
	  template: `
		<div 
			class="ui-rich-text-area"
			v-on="
				isUploadEnabled
				? { drop: onDrop, dragleave: onDragLeave, dragenter: onDragEnter, dragover: onDragOver }
				: {}
			"
		>
			<TextEditorComponent :editor-instance="getEditor()">
				<template #footer>
					<div class="ui-rich-text-area-actions">
						<slot name="before-buttons" :richTextArea="getRichTextArea()"></slot>
						<slot name="file-button" :richTextArea="getRichTextArea()">
							<FileButton v-if="isUploadEnabled" ref="fileButton" :buttonRef="fileButtonRef" />
						</slot>
						<slot name="after-buttons" :richTextArea="getRichTextArea()"></slot>
					</div>
					<slot name="uploader" :adapter="getUploaderAdapter()" :richTextArea="getRichTextArea()">
						<div class="ui-rich-text-area-uploader" :class="{ '--visible': uploaderVisibility }">
							<TileWidgetComponent
								:widgetOptions="tileWidgetOptions"
								:uploader-adapter="getUploaderAdapter()"
								ref="tileWidget"
							/>
						</div>
					</slot>
				</template>
			</TextEditorComponent>
			<DropArea :show="showDropArea" />
		</div>
	`
	};

	exports.RichTextAreaComponent = RichTextAreaComponent;
	exports.FileButton = FileButton;
	exports.ActionButton = ActionButton;
	exports.CreateDocumentButton = CreateDocumentButton;
	exports.RecordVideoButton = RecordVideoButton;

}((this.BX.UI.RichTextArea = this.BX.UI.RichTextArea || {}),BX.UI.Uploader,BX,BX.Event,BX.UI.Lexical.Core,BX.UI.TextEditor,BX.UI.Uploader,BX.UI.Uploader,BX.Vue3));
//# sourceMappingURL=rich-text-area.bundle.js.map
