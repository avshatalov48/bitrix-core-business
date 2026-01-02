import './zoom-bar.css';
import { ref, toValue, computed } from 'ui.vue3';
import { BIcon, Outline } from 'ui.icon-set.api.vue';
import { ZoomBtn } from '../zoom-btn/zoom-btn';
import { ZoomPercent } from '../zoom-percent/zoom-percent';
import { CanvasMap } from '../canvas-map/canvas-map';
import { CanvasMapBtn } from '../canvas-map-btn/canvas-map-btn';

type ZoomBarSetup = {
	iconSet: { [string]: string };
	mapPositionClasses: { [string]: boolean };
	isShowMap: boolean;
	onToggleMap: () => void,
};

const VERTICAL_MAP_POSITION: { [string]: string } = {
	left: 'left',
	right: 'right',
};

const GORIZONTAL_MAP_POSITION: { [string]: string } = {
	top: 'top',
	bottom: 'bottom',
};

const MAP_CLASSES: { [string]: string } = {
	base: 'ui-block-diagram-canvas-zoom-bar__map',
	top: '--top',
	bottom: '--bottom',
	left: '--left',
	right: '--right',
};

const POSITION_MAP_DEFAULT_VALUES: string = 'top right';

// @vue/component
export const ZoomBar = {
	name: 'zoom-bar',
	components: {
		BIcon,
		ZoomBtn,
		ZoomPercent,
		CanvasMap,
		CanvasMapBtn,
	},
	props: {
		stepZoom: {
			type: Number,
			default: 0.2,
		},
		positionMap: {
			type: String,
			default: POSITION_MAP_DEFAULT_VALUES,
		},
		disabled: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['update:modelValue'],
	setup(props): ZoomBarSetup
	{
		const isShowMap = ref(false);

		const mapPositionClasses = computed((): { [string]: boolean } => {
			const isTop = props.positionMap
				.toLowerCase()
				.includes(GORIZONTAL_MAP_POSITION.top);
			const isLeft = props.positionMap
				.toLowerCase()
				.includes(VERTICAL_MAP_POSITION.left);

			return {
				[MAP_CLASSES.base]: true,
				[MAP_CLASSES.top]: isTop,
				[MAP_CLASSES.bottom]: !isTop,
				[MAP_CLASSES.left]: isLeft,
				[MAP_CLASSES.right]: !isLeft,
			};
		});

		function onToggleMap(): void
		{
			if (props.disabled)
			{
				isShowMap.value = false;

				return;
			}

			isShowMap.value = !toValue(isShowMap);
		}

		return {
			iconSet: Outline,
			mapPositionClasses,
			isShowMap,
			onToggleMap,
		};
	},
	template: `
		<div class="ui-block-diagram-canvas-zoom-bar">
			<div class="ui-block-diagram-canvas-zoom-bar__locate">
				<CanvasMapBtn
					:data-test-id="$blockDiagramTestId('zoomOpenMapBtn')"
					@click="onToggleMap"
				/>
				<transition name="editor-large-map-fade" mode="in-out">
					<div
						v-if="isShowMap"
						class="ui-block-diagram-canvas-zoom-bar__map"
						:class="mapPositionClasses"
					>
						<div class="ui-block-diagram-canvas-zoom-bar__map-header">
							<BIcon
								:name="iconSet.CROSS_M"
								:size="24"
								:data-test-id="$blockDiagramTestId('zoomCloseMapBtn')"
								class="ui-block-diagram-canvas-zoom-bar__map-close-icon"
								color="#2FC6F6"
								@click="onToggleMap"
							/>
						</div>
						<CanvasMap
							:mapSize="310"
							:data-test-id="$blockDiagramTestId('zoomCanvasMap')"
						/>
					</div>
				</transition>
			</div>
			<div class="ui-block-diagram-canvas-zoom-bar__separator"/>
			<div class="ui-block-diagram-canvas-zoom-bar__zoom">
				<ZoomBtn
					:stepZoom="stepZoom"
					:disabled="disabled"
					:data-test-id="$blockDiagramTestId('zoomOutBtn')"
					typeZoom="out"
				/>
				<ZoomPercent/>
				<ZoomBtn
					:stepZoom="stepZoom"
					:disabled="disabled"
					:data-test-id="$blockDiagramTestId('zoomInBtn')"
					typeZoom="in"
				/>
			</div>
		</div>
	`,
};
