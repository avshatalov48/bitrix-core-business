import {Tag, Text, Type} from 'main.core';

export class Stage
{
	id;
	name;
	success;
	fail;
	color;
	fillingColor;
	backgroundColor;
	isFilled;
	node;
	textNode;
	backgroundImage = "url('data:image/svg+xml;charset=UTF-8,%3csvg width=%27295%27 height=%2732%27 viewBox=%270 0 295 32%27 fill=%27none%27 xmlns=%27http://www.w3.org/2000/svg%27%3e%3cmask id=%27mask0_2_11%27 style=%27mask-type:alpha%27 maskUnits=%27userSpaceOnUse%27 x=%270%27 y=%270%27 width=%27295%27 height=%2732%27%3e%3cpath fill=%27#COLOR2#%27 d=%27M0 2.9961C0 1.3414 1.33554 0 2.99805 0L285.905 7.15256e-07C287.561 7.15256e-07 289.366 1.25757 289.937 2.80757L295 16.5505L290.007 29.2022C289.397 30.7474 287.567 32 285.905 32H2.99805C1.34227 32 0 30.6657 0 29.0039V2.9961Z%27/%3e%3c/mask%3e%3cg mask=%27url(%23mask0_2_11)%27%3e%3cpath fill=%27#COLOR2#%27 d=%27M0 2.9961C0 1.3414 1.33554 0 2.99805 0L285.905 7.15256e-07C287.561 7.15256e-07 289.366 1.25757 289.937 2.80757L295 16.5505L290.007 29.2022C289.397 30.7474 287.567 32 285.905 32H2.99805C1.34227 32 0 30.6657 0 29.0039V2.9961Z%27/%3e%3cpath d=%27M0 30H295V32H0V30Z%27 fill=%27#COLOR1#%27/%3e%3c/g%3e%3c/svg%3e') 3 10 3 3 fill repeat";

	constructor({id, name, color, backgroundColor, isFilled, events, isSuccess, isFail, fillingColor})
	{
		this.id = id;
		this.name = name;
		this.color = color;
		this.backgroundColor = backgroundColor;
		this.isFilled = isFilled;
		this.events = events;
		this.success = isSuccess;
		this.fail = isFail;
		this.fillingColor = fillingColor;
	}

	static create(data: {
		id: number,
		name: string,
		isSuccess: boolean,
		isFail: boolean,
		color: string,
		backgroundColor: string,
		fillingColor: string,
		isFilled: boolean,
		events: {
				onMouseEnter: ?Function,
				onMouseLeave: ?Function,
				onClick: ?Function,
		}
	}): ?Stage
	{
		if(Type.isPlainObject(data) && data.id && data.name && data.color && data.backgroundColor)
		{
			data.id = Text.toInteger(data.id);
			data.name = data.name.toString();
			data.color = data.color.toString();
			data.backgroundColor = data.backgroundColor.toString();
			if(!Type.isPlainObject(data.events))
			{
				data.events = {};
			}
			if(!Type.isBoolean(data.isFilled))
			{
				data.isFilled = false;
			}

			if(data.id > 0)
			{
				return new Stage(data);
			}
		}

		return null;
	}

	getId(): number
	{
		return this.id;
	}

	getName(): string
	{
		return this.name;
	}

	setName(name: string): Stage
	{
		this.name = name;
		if(this.textNode)
		{
			this.textNode.innerText = this.name;
		}

		return this;
	}

	isSuccess(): boolean
	{
		return (this.success === true);
	}

	isFail(): boolean
	{
		return (this.fail === true);
	}

	isFinal(): boolean
	{
		return (this.isFail() || this.isSuccess());
	}

	getColor(): string
	{
		return this.color;
	}

	setColor(color: string): Stage
	{
		this.color = color;

		return this;
	}

	render(): Element
	{
		if(this.node)
		{
			this.textNode.style.backgroundImage = this.getBackgroundImage();
		}
		else
		{
			this.textNode = Tag.render`<div style="border-image: ${this.getBackgroundImage()};" class="ui-stageflow-stage-item-text">${Text.encode(this.getName())}</div>`;
			this.node = Tag.render`<div 
					class="ui-stageflow-stage" 
					data-stage-id="${this.getId()}" 
					onmouseenter="${this.onMouseEnter.bind(this)}" 
					onmouseleave="${this.onMouseLeave.bind(this)}"
					onclick="${this.onClick.bind(this)}"
				>
				<div class="ui-stageflow-stage-item">
					${this.textNode}
				</div>
			</div>`;
		}

		this.textNode.style.color = Stage.calculateTextColor('#' + (this.isFilled ? this.color : this.backgroundColor));

		return this.node;
	}

	getBackgroundImage(color: string = null, isFilled: boolean = null): string
	{
		if(!color)
		{
			if(this.isFilled && this.fillingColor)
			{
				color = this.fillingColor;
			}
			else
			{
				color = this.getColor();
			}
		}
		if(Type.isNull(isFilled))
		{
			isFilled = this.isFilled;
		}

		let image = this.backgroundImage.replaceAll('#COLOR1#', encodeURIComponent('#' + color));

		if(isFilled)
		{
			image = image.replaceAll('#COLOR2#', encodeURIComponent('#' + color));
		}
		else
		{
			image = image.replaceAll('#COLOR2#', encodeURIComponent('#' + this.backgroundColor));
		}

		return image;
	}

	onMouseEnter()
	{
		if(Type.isFunction(this.events.onMouseEnter))
		{
			this.events.onMouseEnter(this);
		}
	}

	onMouseLeave()
	{
		if(Type.isFunction(this.events.onMouseLeave))
		{
			this.events.onMouseLeave(this);
		}
	}

	onClick()
	{
		if(Type.isFunction(this.events.onClick))
		{
			this.events.onClick(this);
		}
	}

	addBackLight(color)
	{
		if(this.textNode)
		{
			this.textNode.style.borderImage = this.getBackgroundImage(color, true);
			this.textNode.style.color = Stage.calculateTextColor('#' + color);
		}
	}

	removeBackLight()
	{
		if(this.textNode)
		{
			this.textNode.style.borderImage = this.getBackgroundImage();
			this.textNode.style.color = Stage.calculateTextColor('#' + (this.isFilled ? this.fillingColor : this.backgroundColor));
		}
	}

	static calculateTextColor(baseColor)
	{
		var r, g, b;
		if ( baseColor.length > 7 && baseColor.indexOf('(') >= 0 && baseColor.indexOf(')') >= 0)
		{
			var hexComponent = baseColor.split("(")[1].split(")")[0];
			hexComponent = hexComponent.split(",");
			r = parseInt(hexComponent[0]);
			g = parseInt(hexComponent[1]);
			b = parseInt(hexComponent[2]);
		}
		else
		{
			if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(baseColor))
			{
				var c = baseColor.substring(1).split('');
				if(c.length === 3)
				{
					c= [c[0], c[0], c[1], c[1], c[2], c[2]];
				}
				c = '0x'+c.join('');
				r = ( c >> 16 ) & 255;
				g = ( c >> 8 ) & 255;
				b =  c & 255;
			}
		}

		var y = 0.21 * r + 0.72 * g + 0.07 * b;
		return ( y < 145 ) ? "#fff" : "#333";
	}
}