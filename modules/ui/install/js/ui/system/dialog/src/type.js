import { DialogAnglePositions } from './const';
import { BaseEvent } from 'main.core.events';

export type DialogAnglePositon = DialogAnglePositions.topLeft
	| DialogAnglePositions.topCenter
	| DialogAnglePositions.topRight
	| DialogAnglePositions.rightTop
	| DialogAnglePositions.rightCenter
	| DialogAnglePositions.rightBottom
	| DialogAnglePositions.bottomLeft
	| DialogAnglePositions.bottomCenter
	| DialogAnglePositions.bottomRight
	| DialogAnglePositions.leftTop
	| DialogAnglePositions.leftCenter
	| DialogAnglePositions.leftBottom
;

export type DialogStickPosition = 'top' | 'left' | 'bottom' | 'right';

export type DialogEventHandler = (event: BaseEvent) => void;
export type DialogEvents = {
	onHide: ?DialogEventHandler;
	onAfterHide: ?DialogEventHandler;
	onShow: ?DialogEventHandler;
	onAfterShow: ?DialogEventHandler;
	onBeforeShow?: DialogEventHandler;
}
