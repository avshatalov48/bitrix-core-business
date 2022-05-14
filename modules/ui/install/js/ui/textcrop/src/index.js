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

		this.$wrapper = null;
		this.$basicBlock = null;
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
		if(!this.$wrapper)
		{
			this.$wrapper = Tag.render`
				<div>${this.getText()}</div>
			`;
		}

		return this.$wrapper;
	}

	getBasicBlock()
	{
		if(!this.$basicBlock)
		{
			this.$basicBlock = Tag.render`
				<div>a</div>
			`;
		}

		return this.$basicBlock;
	}

	getRowHeight()
	{
		if(!this.rowHeight)
		{
			let styleAtt = getComputedStyle(this.getWrapper());

			if (styleAtt.lineHeight  === 'normal')
			{
				let firstHeight = this.getWrapper().offsetHeight;
				this.$wrapper.appendChild(this.getBasicBlock());
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
			window.addEventListener('resize', BX.delegate(this.init, this));
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
		this.$wrapper = '';
		this.target.appendChild(this.getWrapper());

		let rowHeight = this.getRowHeight();
		let cropText = '';
		let numberRows = this.getWrapper().offsetHeight / parseInt(rowHeight);

		if (numberRows > this.rows)
		{
			this.target.setAttribute('title', this.getText());

			while (this.getWrapper().offsetHeight / parseInt(rowHeight) > this.rows)
			{
				cropText = this.$wrapper.textContent.substring(0, this.$wrapper.textContent.length - 4);
				this.$wrapper.innerHTML = cropText + '...';
			}
		}

		this.cropResize();
	}
}

