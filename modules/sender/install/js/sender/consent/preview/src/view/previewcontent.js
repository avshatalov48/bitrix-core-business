import {Tag, Event} from "main.core";

export default class PreviewContent
{
	constructor()
	{
		this.isActive= false;
		this.text =  '';
		this.approveBtn =  '';
		this.rejectBtn = '';
		this.activeTab = 'desktop';
		this.activeClass = 'sender-message-editor--slider-desktop';

		return this;
	}

	changeActiveTab(activeTab)
	{
		this.activeTab = activeTab;
		this.activeClass = 'sender-message-editor--slider-' + activeTab;
		this.reDraw();
	}

	setActive(active)
	{
		this.isActive = active;
		this.reDraw();
	}

	setText(text)
	{
		this.text = text;
		this.reDraw();
	}

	setApproveBtn(accept)
	{
		this.approveBtn = accept;
		this.reDraw();
	}

	setRejectBtn(reject)
	{
		this.rejectBtn = reject;
		this.reDraw();
	}

	getTemplate()
	{
		const tabletActive = this.activeTab === 'tablet' ? 'active' : '';
		const mobileActive = this.activeTab === 'mobile' ? 'active' : '';
		const desktopActive = this.activeTab === 'desktop' ? 'active' : '';
		return Tag.render`
			<div class="sender-js-slider-contents">
				<div class="ui-slider-section sender-message-editor--slider-modifier ${this.activeClass}">
					<div class="sender-ui-panel-top-devices">
						<div class="sender-ui-panel-top-devices-inner">
						<button 
						class="sender-ui-button sender-ui-button-desktop sender-js-slider-modifier ${desktopActive}" 
						data-id="desktop"></button>
						<button 
						class="sender-ui-button sender-ui-button-tablet sender-js-slider-modifier  ${tabletActive}"
						data-id="tablet"></button>
						<button class="sender-ui-button sender-ui-button-mobile sender-js-slider-modifier  ${mobileActive}"
						data-id="mobile"></button>
					</div>
				</div>
				<div class="ui-slider-content-box">
					<div class="sender-message-mailing-icon"></div>
					${this.text}
					<div class="ui-btn-container ui-btn-container-center">
						<button class="ui-btn ui-btn-success">${this.approveBtn}</button>
						<button class="ui-btn ui-btn-light-border">${this.rejectBtn}</button>
					</div>
				</div>
			</div>
			`;
	}

	bindEvent()
	{
		const buttons = window.top.document.querySelectorAll('.sender-js-slider-modifier');

		buttons.forEach((element) => {
			const type = element.dataset.id || 'desktop';
			Event.bind(element, 'click', this.changeActiveTab.bind(this, type));
		});
	}

	reDraw()
	{
		// BX.SidePanel.Slider.top
		const content = window.top.document.querySelector('div.sender-js-slider-contents');

		if (!content)
		{
			return;
		}
		const parentNode = content.parentNode;

		parentNode.removeChild(content);

		parentNode.append(this.getTemplate());
		this.bindEvent();
	}
};
