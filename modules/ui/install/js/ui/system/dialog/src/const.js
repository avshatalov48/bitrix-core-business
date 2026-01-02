export const DialogAnglePositions = Object.freeze({
	topLeft: 'topLeft',
	topCenter: 'topCenter',
	topRight: 'topRight',
	rightTop: 'rightTop',
	rightCenter: 'rightCenter',
	rightBottom: 'rightBottom',
	bottomLeft: 'bottomLeft',
	bottomCenter: 'bottomCenter',
	bottomRight: 'bottomRight',
	leftTop: 'leftTop',
	leftCenter: 'leftCenter',
	leftBottom: 'leftBottom',
});

export const DialogBackground = Object.freeze({
	default: 'default',
	vibrant: 'vibrant',
});

export const aliases = {
	onShow: { namespace: 'BX.UI.System.Dialog', eventName: 'onShow' },
	onAfterShow: { namespace: 'BX.UI.System.Dialog', eventName: 'onAfterShow' },
	onHide: { namespace: 'BX.UI.System.Dialog', eventName: 'onHide' },
	onAfterHide: { namespace: 'BX.UI.System.Dialog', eventName: 'onAfterHide' },
};
