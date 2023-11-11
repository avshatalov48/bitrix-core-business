import { Dom, Event, Loc, Tag, Text, Type } from 'main.core';
import { Popup } from 'main.popup';
import { Helper } from 'bizproc.automation';
import { Button } from 'ui.buttons';

const renderAfterPreviousImageBlock = () => {
	return Tag.render`
		<svg 
			class="bizproc-automation_execution-queue_in-turn"
			width="97"
			height="121"
			viewBox="0 0 97 121"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
			xmlns:xlink="http://www.w3.org/1999/xlink"
		>
			<rect width="97" height="121" fill="url(#pattern0)"/>
			<path
				fill-rule="evenodd"
				clip-rule="evenodd"
				d="M12.25 27C11.5596 27 11 26.4404 11 25.75V18.25C11 17.5596 11.5596 17 12.25 17H15.6875H16H79.75H81.3125H82.7275C83.2009 17 83.6338 17.2675 83.8455 17.691L86 22L83.8455 26.309C83.6338 26.7325 83.2009 27 82.7275 27H81.3125H79.75H16H15.6875H12.25Z"
				fill="#DFE0E3"
			/>
			<path
				fill-rule="evenodd"
				clip-rule="evenodd"
				d="M12.25 27C11.5596 27 11 26.4404 11 25.75V18.25C11 17.5596 11.5596 17 12.25 17H15.6875H16H79.75H81.3125H82.7275C83.2009 17 83.6338 17.2675 83.8455 17.691L86 22L83.8455 26.309C83.6338 26.7325 83.2009 27 82.7275 27H81.3125H79.75H16H15.6875H12.25Z"
				fill="#55D0E0"
			/>
			<g filter="url(#filter0_d_272_90944)" class="bizproc-automation_execution-queue_transform-element --one">
				<rect
					x="11"
					y="32"
					width="75"
					height="34"
					rx="4"
					fill="white"
					fill-opacity="0.9"
					shape-rendering="crispEdges"
				/>
				<path
					d="M11 56H86V62C86 64.2091 84.2091 66 82 66H15C12.7909 66 11 64.2091 11 62V56Z"
					fill="#C5F8FF"
				/>
				<rect x="22" y="37" width="21" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<rect x="15" y="45" width="54" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<rect x="72" y="45" width="8" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<rect x="55" y="59" width="28" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<g class="bizproc-automation_execution-queue_checked --one">
					<circle cx="15" cy="38" r="8" fill="#739F00"/>
					<path
						d="M11.7084 37.089L15.4796 40.8602L13.9711 42.3687L10.1999 38.5975L11.7084 37.089Z"
						fill="white"
					/>
					<path
						d="M20.0051 36.3347L13.9711 42.3687L12.4627 40.8602L18.4966 34.8262L20.0051 36.3347Z"
						fill="white"
					/>
				</g>
			</g>
			<g filter="url(#filter1_d_272_90944)" class="bizproc-automation_execution-queue_transform-element --two">
				<rect
					x="11"
					y="71"
					width="75"
					height="34"
					rx="4"
					fill="white"
					fill-opacity="0.9"
					shape-rendering="crispEdges"
				/>
				<path
					d="M11 95H86V101C86 103.209 84.2091 105 82 105H15C12.7909 105 11 103.209 11 101V95Z"
					fill="#C5F8FF"
				/>
				<rect x="15" y="84" width="54" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<rect x="72" y="84" width="8" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<rect x="55" y="98" width="28" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<rect x="22" y="76" width="21" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<g class="bizproc-automation_execution-queue_checked --two">
					<circle cx="15" cy="77" r="8" fill="#739F00"/>
					<path
						d="M11.7084 76.089L15.4796 79.8602L13.9711 81.3687L10.1999 77.5975L11.7084 76.089Z"
						fill="white"
					/>
					<path
						d="M20.0051 75.3347L13.9711 81.3687L12.4627 79.8602L18.4966 73.8262L20.0051 75.3347Z"
						fill="white"
					/>
				</g>
			</g>
		</svg>
	`;
};

const renderParallelImageBlock = () => {
	return Tag.render`
		<svg
			class="bizproc-automation_execution-queue_simultaneously"
			width="97"
			height="121"
			viewBox="0 0 97 121"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
			xmlns:xlink="http://www.w3.org/1999/xlink"
		>
			<rect width="97" height="121" fill="url(#pattern0)"/>
			<path
				fill-rule="evenodd"
				clip-rule="evenodd"
				d="M12.25 27C11.5596 27 11 26.4404 11 25.75V18.25C11 17.5596 11.5596 17 12.25 17H15.6875H16H79.75H81.3125H82.7275C83.2009 17 83.6338 17.2675 83.8455 17.691L86 22L83.8455 26.309C83.6338 26.7325 83.2009 27 82.7275 27H81.3125H79.75H16H15.6875H12.25Z"
				fill="#DFE0E3"
			/>
			<path
				fill-rule="evenodd"
				clip-rule="evenodd"
				d="M12.25 27C11.5596 27 11 26.4404 11 25.75V18.25C11 17.5596 11.5596 17 12.25 17H15.6875H16H79.75H81.3125H82.7275C83.2009 17 83.6338 17.2675 83.8455 17.691L86 22L83.8455 26.309C83.6338 26.7325 83.2009 27 82.7275 27H81.3125H79.75H16H15.6875H12.25Z"
				fill="#55D0E0"
			/>
			<g
				filter="url(#filter0_d_272_90944)"
				class="bizproc-automation_execution-queue_transform-element"
			>
				<rect
					x="11"
					y="32"
					width="75"
					height="34"
					rx="4"
					fill="white"
					fill-opacity="0.9"
					shape-rendering="crispEdges"
				/>
				<path
					d="M11 56H86V62C86 64.2091 84.2091 66 82 66H15C12.7909 66 11 64.2091 11 62V56Z"
					fill="#C5F8FF"
				/>
				<rect x="22" y="37" width="21" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<rect x="15" y="45" width="54" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<rect x="72" y="45" width="8" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<rect x="55" y="59" width="28" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<g class="bizproc-automation_execution-queue_checked">
					<circle cx="15" cy="38" r="8" fill="#739F00"/>
					<path
						d="M11.7084 37.089L15.4796 40.8602L13.9711 42.3687L10.1999 38.5975L11.7084 37.089Z"
						fill="white"
					/>
					<path
						d="M20.0051 36.3347L13.9711 42.3687L12.4627 40.8602L18.4966 34.8262L20.0051 36.3347Z"
						fill="white"
					/>
				</g>
			</g>
			<g
				filter="url(#filter1_d_272_90944)"
				class="bizproc-automation_execution-queue_transform-element"
			>
				<rect
					x="11"
					y="71"
					width="75"
					height="34"
					rx="4"
					fill="white"
					fill-opacity="0.9"
					shape-rendering="crispEdges"
				/>
				<path
					d="M11 95H86V101C86 103.209 84.2091 105 82 105H15C12.7909 105 11 103.209 11 101V95Z"
					fill="#C5F8FF"
				/>
				<rect x="15" y="84" width="54" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<rect x="72" y="84" width="8" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<rect x="55" y="98" width="28" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<rect x="22" y="76" width="21" height="4" rx="2" fill="#999999" fill-opacity="0.33"/>
				<g class="bizproc-automation_execution-queue_checked">
					<circle cx="15" cy="77" r="8" fill="#739F00"/>
					<path
						d="M11.7084 76.089L15.4796 79.8602L13.9711 81.3687L10.1999 77.5975L11.7084 76.089Z"
						fill="white"
					/>
					<path
						d="M20.0051 75.3347L13.9711 81.3687L12.4627 79.8602L18.4966 73.8262L20.0051 75.3347Z"
						fill="white"
					/>
				</g>
			</g>
		</svg>
	`;
};

const renderRow = (
	isActive: boolean,
	uid: string,
	content: {
		title: string,
		description: string,
		imageRenderFunction: () => Element,
		value: string,
	},
) => {
	const { root, radio } = Tag.render`
		<label
			class="bizproc-automation-popup-select__wrapper-flex ${isActive ? '--active' : ''} ui-ctl ui-ctl-radio ui-ctl-w100"
			for="${uid}"
			data-role="execution-queue-row"
		>
			<div class="bizproc-automation-popup-select__wrapper-info-block">
				<div class="bizproc-automation-popup-select__header-input">
					<input
						ref="radio"
						class="ui-ctl-element"
						id="${uid}"
						type="radio"
						value="${Text.encode(content.value)}"
						name="execution"
					/>
					<span class="bizproc-automation-popup-settings__input-title">${Text.encode(content.title)}</span>
				</div>
				<div class="bizproc-automation-popup-settings__description">${Text.encode(content.description)}</div>
			</div>
			<div class="bizproc-automation-popup-settings__image-block">
				${content.imageRenderFunction()}
			</div>
		</label>
	`;
	Event.bind(radio, 'change', () => {
		document.querySelectorAll('[data-role="execution-queue-row"]').forEach((node) => {
			Dom.removeClass(node, '--active');
		});
		Dom.addClass(root, '--active');
	});

	if (isActive)
	{
		Dom.attr(radio, 'checked', 'checked');
	}

	return root;
};

type ExecutionQueuePopupSettings = {
	bindElement: HTMLElement,
	currentValue: '1' | '0',
	onSubmitButtonClick?: (form: FormData) => void,
};

const showExecutionQueuePopup = (
	settings: ExecutionQueuePopupSettings,
) => {
	const afterPreviousContent = {
		title: Loc.getMessage('BIZPROC_JS_AUTOMATION_EXECUTION_QUEUE_AFTER_PREVIOUS_TITLE'),
		description: Loc.getMessage('BIZPROC_JS_AUTOMATION_EXECUTION_QUEUE_AFTER_PREVIOUS_DESCRIPTION'),
		imageRenderFunction: renderAfterPreviousImageBlock,
		value: 'afterPrevious',
	};
	const parallelContent = {
		title: Loc.getMessage('BIZPROC_JS_AUTOMATION_EXECUTION_QUEUE_PARALLEL_TITLE'),
		description: Loc.getMessage('BIZPROC_JS_AUTOMATION_EXECUTION_QUEUE_PARALLEL_DESCRIPTION'),
		imageRenderFunction: renderParallelImageBlock,
		value: 'parallel',
	};

	const content = Tag.render`
		<form class="bizproc-automation-popup-select-block">
			<div class="bizproc-automation-popup-select-item">
				${renderRow(settings.currentValue === '1', 'bizproc-automation-cmp1', afterPreviousContent)}
			</div>
			<div class="bizproc-automation-popup-select-item">
				${renderRow(settings.currentValue !== '1', 'bizproc-automation-cmp2', parallelContent)}
			</div>
		</form>
	`;

	const popup = new Popup({
		id: Helper.generateUniqueId(),
		bindElement: settings.bindElement,
		content,
		closeByEsc: true,
		buttons: [
			new Button({
				color: Button.Color.PRIMARY,
				text: Loc.getMessage('BIZPROC_JS_AUTOMATION_CHOOSE_BUTTON_CAPS'),
				onclick: () => {
					if (Type.isFunction(settings.onSubmitButtonClick))
					{
						settings.onSubmitButtonClick(new FormData(content));
					}

					popup.close();
				},
			}),
			new Button({
				color: Button.Color.LINK,
				text: Loc.getMessage('BIZPROC_JS_AUTOMATION_CANCEL_BUTTON_CAPS'),
				onclick: () => {
					popup.close();
				},
			}),
		],
		width: 482,
		padding: 20,
		closeIcon: false,
		autoHide: true,
		titleBar: false,
		angle: {
			offset: (settings.bindElement.clientWidth + 33) / 2,
		},
		overlay: { backgroundColor: 'transparent' },
		events: {
			onClose: () => {
				popup.destroy();
			},
		},
	});

	popup.show();
};

export default showExecutionQueuePopup;
