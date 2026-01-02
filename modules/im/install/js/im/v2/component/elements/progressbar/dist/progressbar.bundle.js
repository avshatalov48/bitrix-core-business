/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_iconSet_api_vue,im_v2_const,main_core) {
	'use strict';

	const SIZE_LOWER_THRESHOLD = 1024 * 1024 * 2;
	const formatProgressLabel = (progress, totalBytes) => {
	  if (totalBytes < SIZE_LOWER_THRESHOLD) {
	    return main_core.Loc.getMessage('IM_ELEMENTS_PROGRESSBAR_UPLOAD_LOADING');
	  }
	  const bytesSent = totalBytes / 100 * progress;
	  const megaBytesSent = `${convertBytesToMegaBytes(bytesSent)} ${main_core.Loc.getMessage('IM_ELEMENTS_PROGRESSBAR_SIZE_MB')}`;
	  const megaBytesTotal = `${convertBytesToMegaBytes(totalBytes)} ${main_core.Loc.getMessage('IM_ELEMENTS_PROGRESSBAR_SIZE_MB')}`;
	  return `${megaBytesSent} / ${megaBytesTotal}`;
	};
	const convertBytesToMegaBytes = bytes => {
	  return (bytes / 1024 / 1024).toFixed(2);
	};

	const ProgressBarSize = Object.freeze({
	  S: 'S',
	  L: 'L'
	});
	const MIN_PROGRESS = 1; // Minimum progress to show the circle animation

	// @vue/component
	const ProgressBar = {
	  name: 'ProgressBar',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    size: {
	      type: String,
	      default: ProgressBarSize.L
	    },
	    handleStatus: {
	      type: Array,
	      default: () => {
	        return [im_v2_const.FileStatus.progress, im_v2_const.FileStatus.upload];
	      }
	    },
	    /**
	     * @value {[status: string]: { iconClass: string, labelText: string }}
	     */
	    statusMap: {
	      type: Object,
	      default: () => {
	        return {};
	      }
	    }
	  },
	  emits: ['cancelClick'],
	  computed: {
	    Color: () => im_v2_const.Color,
	    OutlineIcons: () => ui_iconSet_api_vue.Outline,
	    file() {
	      return this.item;
	    },
	    needProgressBar() {
	      return this.handleStatus.includes(this.file.status);
	    },
	    progressStyles() {
	      const radius = 23;
	      const circumference = 2 * Math.PI * radius;
	      const adjustedProgress = Math.max(this.file.progress, MIN_PROGRESS);
	      const offset = circumference - adjustedProgress / 100 * circumference;
	      return {
	        strokeDasharray: circumference,
	        strokeDashoffset: offset
	      };
	    },
	    labelText() {
	      var _this$statusMap, _this$statusMap$this$;
	      if ((_this$statusMap = this.statusMap) != null && (_this$statusMap$this$ = _this$statusMap[this.file.status]) != null && _this$statusMap$this$.labelText) {
	        var _this$statusMap2, _this$statusMap2$this;
	        return (_this$statusMap2 = this.statusMap) == null ? void 0 : (_this$statusMap2$this = _this$statusMap2[this.file.status]) == null ? void 0 : _this$statusMap2$this.labelText;
	      }
	      return formatProgressLabel(this.file.progress, this.file.size);
	    },
	    iconClass() {
	      var _this$statusMap3, _this$statusMap3$this;
	      if ((_this$statusMap3 = this.statusMap) != null && (_this$statusMap3$this = _this$statusMap3[this.file.status]) != null && _this$statusMap3$this.iconClass) {
	        return this.statusMap[this.file.status].iconClass;
	      }
	      return ui_iconSet_api_vue.Outline.CROSS_L;
	    },
	    containerClass() {
	      return `--size-${this.size.toLowerCase()}`;
	    },
	    needLabel() {
	      return this.size === ProgressBarSize.L;
	    },
	    iconSize() {
	      return this.size === ProgressBarSize.L ? 28 : 24;
	    }
	  },
	  methods: {
	    onLoaderClick() {
	      if (!this.handleStatus.includes(this.file.status)) {
	        return;
	      }
	      this.$emit('cancelClick', {
	        file: this.file
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div 
			v-if="needProgressBar"
			:class="containerClass"
			class="bx-im-progress-bar__container" 
		>
			<div class="bx-im-progress-bar__loader" @click="onLoaderClick">
				<svg viewBox="0 0 48 48">
					<circle
						:style="progressStyles"
						class="bx-im-progress-bar__loader-progress" 
						cx="24" 
						cy="24" 
						r="23"
					></circle>
				</svg>
				<BIcon
					:name="iconClass"
					:color="Color.white"
					:size="iconSize"
					class="bx-im-progress-bar__loader-icon"
				/>
			</div>
			<div v-if="needLabel" class="bx-im-progress-bar__label">
				{{ labelText }}
			</div>
		</div>
	`
	};

	exports.ProgressBarSize = ProgressBarSize;
	exports.ProgressBar = ProgressBar;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX.UI.IconSet,BX.Messenger.v2.Const,BX));
//# sourceMappingURL=progressbar.bundle.js.map
