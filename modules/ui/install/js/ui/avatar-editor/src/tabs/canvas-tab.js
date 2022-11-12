import {Tag, Loc} from 'main.core';
import DefaultTab from './default-tab';

export default class CanvasTab extends DefaultTab
{
	static priority = 1;

	getHeader(): ?String
	{
		return Loc.getMessage('JS_AVATAR_EDITOR_PHOTO');
	}

	getBody(): Element
	{
		return this.cache.remember('body', () => {
			const res = Tag.render`
				<div class="ui-avatar-editor__content-block" data-bx-role="tab-canvas-body">
					<div class="ui-avatar-editor__control" data-bx-role="canvas-zooming">
						<div class="ui-avatar-editor__control-controller" data-bx-role="zoom-minus-button">
							<span class="ui-avatar-editor__control-minus"></span>
						</div>
						<div class="ui-avatar-editor__control-inner" data-bx-role="zoom-scale">
							<div class="ui-avatar-editor__control-slide-container ui-avatar-editor__control-slide-drag-state">
								<div class="ui-avatar-editor__control-slide" data-bx-role="zoom-knob"></div>
							</div>
						</div>
						<div class="ui-avatar-editor__control-controller" data-bx-role="zoom-plus-button">
							<span class="ui-avatar-editor__control-plus"></span>
						</div>
					</div>
					<div class="ui-avatar-editor__camera-block-image">
						<div class="ui-avatar-editor__user-loader-item" data-bx-role="canvas-loader">
							<div class="ui-avatar-editor__loader">
								<svg class="ui-avatar-editor__circular" viewBox="25 25 50 50">
									<circle class="ui-avatar-editor__path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"></circle>
								</svg>
							</div>
						</div>
						<div class="ui-avatar-editor__error" data-bx-role="canvas-error">
							<span>${Loc.getMessage('JS_AVATAR_EDITOR_ERROR')}</span>
							<span data-bx-role="tab-canvas-error"></span>
						</div>
						<div class="ui-avatar-editor__user-avatar-item" data-bx-role="canvas-holder">
							<span class="ui-avatar-editor__tab-avatar-image-item"></span>
						</div>

						<div data-editor-role="canvas-holder">
							<canvas data-bx-canvas="canvas" height="330" width="330"></canvas>
						</div>
					</div>
					<div class="ui-avatar-editor__button-layout">
						<div class="ui-avatar-editor__button" data-bx-role="button-add-picture" data-bx-id="upload-file">
							<span class="ui-avatar-editor__button-name">${Loc.getMessage('JS_AVATAR_EDITOR_UPLOAD')}</span>
						</div>
						<div class="ui-avatar-editor__button"  data-bx-role="button-add-picture" data-bx-id="snap-picture">
							<span class="ui-avatar-editor__button-name">${Loc.getMessage('JS_AVATAR_EDITOR_SNAP')}</span>
						</div>
					</div>
				</div>`;
			return res;
		})
	}

	static get code()
	{
		return 'canvas';
	}
}