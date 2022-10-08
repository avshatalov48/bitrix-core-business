import { Tag, Type } from 'main.core';

export class TextCrop
{
	constructor(options)
	{
		this.target = options.target || null;
		this.rows = options.rows || 2;
		this.resize = options.resize || false;
		this.text = null;
		this.rowHeight = null;

		this.layout = {
			wrapper: null,
			basicBlock: null
		}
	}

	getText()
	{
		if(!this.text)
		{
			this.text = this.target
				? this.target.innerText
				: null;
		}

		return this.text;
	}

	getWrapper()
	{
		if(!this.layout.wrapper)
		{
			this.layout.wrapper = Tag.render`
				<div>${this.getText()}</div>
			`;
		}

		return this.layout.wrapper;
	}

	getBasicBlock()
	{
		if(!this.layout.basicBlock)
		{
			this.layout.basicBlock = Tag.render`
				<div>a</div>
			`;
		}

		return this.layout.basicBlock;
	}

	getRowHeight()
	{
		if(!this.rowHeight)
		{
			let styleAtt = getComputedStyle(this.getWrapper());

			if (styleAtt.lineHeight  === 'normal')
			{
				let firstHeight = this.getWrapper().offsetHeight;
				this.layout.wrapper.appendChild(this.getBasicBlock());
				let secondHeight = this.getWrapper().offsetHeight;
				this.getBasicBlock().remove();

				this.rowHeight = secondHeight - firstHeight;
			}
			else
			{
				this.rowHeight = styleAtt.lineHeight;
			}
		}

		return this.rowHeight;
	}

	cropResize()
	{
		if(this.resize)
		{
			let timer;
			window.addEventListener('resize', () => {
				if (!timer)
				{
					timer = setTimeout(() => {
						this.init();
						clearTimeout(timer);
					}, 100);
				}
			});
		}
	}

	crop()
	{
		this.init();
	}

	init()
	{
		if(!Type.isDomNode(this.target))
		{
			return;
		}

		this.getText();
		this.target.innerText = '';
		this.layout.wrapper = '';
		this.target.appendChild(this.getWrapper());

		let rowHeight = this.getRowHeight();
		let cropText = '';
		let numberRows = this.getWrapper().offsetHeight / parseInt(rowHeight);

		if (numberRows > this.rows)
		{
			this.target.setAttribute('title', this.getText());

			while (this.getWrapper().offsetHeight / parseInt(rowHeight) > this.rows)
			{
				cropText = this.layout.wrapper.textContent.substring(0, this.layout.wrapper.textContent.length - 4);
				this.layout.wrapper.innerHTML = cropText + '...';
			}
		}

		this.cropResize();
	}
}

