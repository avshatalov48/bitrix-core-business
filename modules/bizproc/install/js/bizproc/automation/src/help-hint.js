import { Event, Text, Type, Dom, Loc } from 'main.core';

export class HelpHint
{
	static popupHint;
	static timeout;

	static bindAll(node)
	{
		node.querySelectorAll('[data-text]').forEach((element) => HelpHint.bindToNode(element));
	}

	static bindToNode(node)
	{
		Event.bind(node, 'mouseover', this.showHint.bind(this, node));
		Event.bind(node, 'mouseout', this.hideHint.bind(this));
	}

	static isBindedToNode(node): boolean
	{
		return !!this.popupHint?.bindElement?.isSameNode(node);
	}

	static showHint(node)
	{
		const rawText = node.getAttribute('data-text');
		if (!rawText)
		{
			return;
		}
		let text = Text.encode(rawText);
		text = BX.util.nl2br(text);

		if (!Type.isStringFilled(text))
		{
			return;
		}
		this.hideHint();

		this.popupHint = new BX.PopupWindow('bizproc-automation-help-tip', node, {
			lightShadow: true,
			autoHide: false,
			darkMode: true,
			offsetLeft: 0,
			offsetTop: 2,
			bindOptions: {position: "top"},
			events : {
				onPopupClose()
				{
					this.destroy();
				},
			},
			content : Dom.create('div', {
				attrs : {
					style : 'padding-right: 5px; width: 250px;'
				},
				html: text
			}),
		});
		this.popupHint.setAngle({offset: 32, position: 'bottom'});
		this.popupHint.show();

		return true;
	}

	static showNoPermissionsHint(node)
	{
		this.showAngleHint(node, Loc.getMessage('BIZPROC_AUTOMATION_RIGHTS_ERROR'));
	}

	static showAngleHint(node, text)
	{
		if (this.timeout)
		{
			clearTimeout(this.timeout);
		}

		this.popupHint = BX.UI.Hint.createInstance({
			popupParameters: {
				width: 334,
				height: 104,
				closeByEsc: true,
				autoHide: true,
				angle: {offset: Dom.getPosition(node).width / 2},
				bindOptions: {position: 'top'},
			}
		});

		this.popupHint.close = function ()
		{
			this.hide();
		};
		this.popupHint.show(node, text);
		this.timeout = setTimeout(this.hideHint.bind(this), 5000);
	}

	static hideHint()
	{
		if (this.popupHint)
		{
			this.popupHint.close();
		}
		this.popupHint = null;
	}
}