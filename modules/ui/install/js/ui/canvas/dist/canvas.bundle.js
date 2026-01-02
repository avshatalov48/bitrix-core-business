/* eslint-disable */
this.BX = this.BX || {};
(function (exports) {
	'use strict';

	// @vue/component
	const TransformCanvas = {
	  name: 'transform-canvas',
	  props: {
	    modelValue: {
	      type: Object,
	      required: false
	    },
	    zoomSensitivity: {
	      type: Number,
	      default: 0.01
	    },
	    zoomSensitivityMouse: {
	      type: Number,
	      default: 0.04
	    },
	    showGrid: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['update:modelValue'],
	  data() {
	    return {
	      transform: {
	        x: 0,
	        y: 0,
	        zoom: 1
	      },
	      dragging: false,
	      zooming: false,
	      lastWheelEventTime: null,
	      dragDirection: null
	    };
	  },
	  computed: {
	    canvasClass() {
	      return {
	        '--dragging': this.dragging,
	        '--zooming': this.zooming
	      };
	    },
	    canvasStyles() {
	      if (this.showGrid) {
	        const {
	          x,
	          y,
	          zoom
	        } = this.transform;
	        const size = Math.round(zoom * 100);
	        return {
	          'background-image': `
						linear-gradient(0deg, rgba(0, 0, 0, 0.1) 0px, rgba(0, 0, 0, 0.1) 1px, transparent 1px, transparent 100px),
						linear-gradient(90deg, rgba(0, 0, 0, 0.1) 0px, rgba(0, 0, 0, 0.1) 1px, transparent 1px, transparent 100px)
					`,
	          'background-position': `${x}px ${y}px`,
	          'background-size': `${size}px ${size}px`
	        };
	      }
	      return {};
	    },
	    transformStyles() {
	      this.transform = this.modelValue ? {
	        ...this.modelValue
	      } : this.transform;
	      const {
	        x,
	        y,
	        zoom
	      } = this.transform;
	      return {
	        transform: `translate(${x}px, ${y}px) scale(${zoom})`
	      };
	    }
	  },
	  methods: {
	    onMouseDown(event) {
	      this.dragOn = true;
	      this.dragDirection = null;
	    },
	    onMouseMove(event) {
	      if (!this.dragOn) {
	        return;
	      }
	      if (event.buttons !== 1) {
	        this.dragOn = false;
	        this.dragging = false;
	        this.dragDirection = null;
	        return;
	      }
	      this.dragging = true;
	      if (!this.dragDirection) {
	        if (Math.abs(event.movementX) > Math.abs(event.movementY)) {
	          this.dragDirection = 'horizontal';
	        } else {
	          this.dragDirection = 'vertical';
	        }
	      }
	      window.requestAnimationFrame(() => {
	        this.transform.x += event.movementX;
	        this.transform.y += event.movementY;
	        this.$emit('update:modelValue', this.transform);
	      });
	    },
	    onMouseUp() {
	      this.dragOn = false;
	      this.dragging = false;
	      this.dragDirection = null;
	    },
	    onContextMenu(event) {
	      event.preventDefault();
	    },
	    isTrackpad(event) {
	      return event.wheelDeltaY ? event.wheelDeltaY === -3 * event.deltaY : event.deltaMode === 0;
	    },
	    onWheel(event) {
	      const isTrackpad = this.isTrackpad(event);
	      if (event.ctrlKey) {
	        const zoomChange = isTrackpad ? -event.deltaY * this.zoomSensitivity : -Math.sign(event.deltaY) * this.zoomSensitivityMouse;
	        const mouseX = event.clientX;
	        const mouseY = event.clientY;
	        const newZoom = Math.min(Math.max(0.2, this.transform.zoom + zoomChange), 3);
	        const oldCenterX = (mouseX - this.transform.x) / this.transform.zoom;
	        const oldCenterY = (mouseY - this.transform.y) / this.transform.zoom;
	        this.transform.zoom = newZoom;
	        this.transform.x = mouseX - oldCenterX * newZoom;
	        this.transform.y = mouseY - oldCenterY * newZoom;
	        this.zooming = true;
	        this.$emit('update:modelValue', this.transform);
	        event.preventDefault();
	        setTimeout(() => {
	          this.zooming = false;
	        }, 200);
	      } else {
	        const dx = event.deltaX;
	        const dy = event.deltaY;
	        if (event.shiftKey) {
	          this.transform.x -= dy;
	          this.transform.y -= dx;
	        } else {
	          this.transform.x -= dx;
	          this.transform.y -= dy;
	        }
	        this.$emit('update:modelValue', this.transform);
	        event.preventDefault();
	      }
	    }
	  },
	  template: `
		<div
			class="ui-transform-canvas"
			:class="canvasClass"
			:style="canvasStyles"
			@mousedown="onMouseDown"
			@mousemove="onMouseMove"
			@mouseup="onMouseUp"
			@wheel="onWheel"
			@contextmenu="onContextMenu"
		>
			<div
				class="ui-transform-canvas__transform"
				:style="transformStyles"
			>
				<slot :transform="transform"/>
			</div>
		</div>
	`
	};

	exports.TransformCanvas = TransformCanvas;

}((this.BX.UI = this.BX.UI || {})));
//# sourceMappingURL=canvas.bundle.js.map
