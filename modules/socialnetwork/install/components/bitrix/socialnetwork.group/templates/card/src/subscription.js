import {ajax, Loc, Tag, Type} from 'main.core';
import {Popup} from 'main.popup';

import {WorkgroupCardUtil} from './util';

class WorkgroupCardSubscription
{
	constructor(params)
	{
		this.groupId = parseInt(params.groupId);
		this.buttonNode = params.buttonNode;
		this.notifyHintTimeout = null;
		this.notifyHintPopup = null;
		this.notifyHintTime = 3000;

		if (this.buttonNode)
		{
			this.buttonNode.addEventListener('click', () => {
				this.set();
			}, true);
		}
	}

	set()
	{
		const action = (!this.buttonNode.classList.contains('ui-btn-active') ? 'set' : 'unset');
		this.switch(this.buttonNode, (action === 'set'));

		ajax.runAction('socialnetwork.api.workgroup.setSubscription', {
			data: {
				params: {
					groupId: this.groupId,
					value: (action === 'set' ? 'Y' : 'N'),
				}
			},
		}).then((data) => {
			const eventData = {
				code: 'afterSetSubscribe',
				data: {
					groupId: this.groupId,
					value: (data.RESULT == 'Y'),
				}
			};
			window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', eventData);
		}).catch((response) => {
			this.switch(this.buttonNode, !(action === 'set'));
			WorkgroupCardUtil.processAJAXError(response.errors[0].message);
		});
	}

	switch(node, active)
	{
		if (!Type.isDomNode(node))
		{
			return;
		}

		if (!!active)
		{
			node.classList.add('ui-btn-active');
			node.classList.remove('ui-btn-icon-unfollow');
			node.classList.add('ui-btn-icon-follow');

			this.showNotifyHint(node, Loc.getMessage('SGCSSubscribeButtonHintOn'));
		}
		else
		{
			node.classList.remove('ui-btn-active');
			node.classList.add('ui-btn-icon-unfollow');
			node.classList.remove('ui-btn-icon-follow');

			this.showNotifyHint(node, Loc.getMessage('SGCSSubscribeButtonHintOff'));
		}
	}

	showNotifyHint(node, hintText)
	{
		if (this.notifyHintTimeout)
		{
			clearTimeout(this.notifyHintTimeout);
			this.notifyHintTimeout = null;
		}

		if (Type.isNull(this.notifyHintPopup))
		{
			this.notifyHintPopup = new Popup('sgm_notify_hint', node, {
				autoHide: true,
				lightShadow: true,
				zIndex: 2,
				content: Tag.render`<div class="sonet-sgm-notify-hint-content" style="display: none;"><span id="sgm_notify_hint_text">${hintText}</span></div>`,
				closeByEsc: true,
				closeIcon: false,
				offsetLeft: 21,
				offsetTop: 2,
			});

			this.notifyHintPopup.TEXT = document.getElementById('sgm_notify_hint_text');
			this.notifyHintPopup.setBindElement(node);
		}
		else
		{
			this.notifyHintPopup.TEXT.innerHTML = hintText;
			this.notifyHintPopup.setBindElement(node);
		}

		this.notifyHintPopup.setAngle({});
		this.notifyHintPopup.show();

		this.notifyHintTimeout = setTimeout(() => {
			this.notifyHintPopup.close();
		}, this.notifyHintTime);
	}
}

export {
	WorkgroupCardSubscription,
}
