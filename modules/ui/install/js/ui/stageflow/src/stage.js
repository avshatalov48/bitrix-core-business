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
	backgroundImage = 'url(data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2216px%22%20height%3D%2232px%22%20viewBox%3D%220%200%2016%2032%22%20version%3D%221.1%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20xmlns%3Axlink%3D%22http%3A//www.w3.org/1999/xlink%22%3E%3Cdefs%3E%3Cpath%20d%3D%22M0%2C2.99610022%20C0%2C1.34139976%201.3355407%2C0%202.99805158%2C0%20L6.90478569%2C0%20C8.56056385%2C0%2010.3661199%2C1.25756457%2010.9371378%2C2.80757311%20L16%2C16.5505376%20L11.0069874%2C29.2022189%20C10.3971821%2C30.7473907%208.56729657%2C32%206.90478569%2C32%20L2.99805158%2C32%20C1.34227341%2C32%200%2C30.6657405%200%2C29.0038998%20L0%2C2.99610022%20Z%22%20id%3D%22Bg%22/%3E%3C/defs%3E%3Cg%20id%3D%22Bar%22%20stroke%3D%22none%22%20fill-rule%3D%22evenodd%22%3E%3Cmask%20fill%3D%22white%22%20id%3D%22mask%22%3E%3Cuse%20xlink%3Ahref%3D%22%23Bg%22/%3E%3C/mask%3E%3Cuse%20fill%3D%22#COLOR2#%22%20xlink%3Ahref%3D%22%23Bg%22/%3E%3Cpolygon%20id%3D%22Ln%22%20fill%3D%22#COLOR1#%22%20mask%3D%22url%28%23mask%29%22%20points%3D%220%2030%2016%2030%2016%2032%200%2032%22/%3E%3C/g%3E%3C/svg%3E) 3 10 3 3 fill repeat';

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

		let image = this.backgroundImage.replace('#COLOR1#', encodeURIComponent('#' + color));

		if(isFilled)
		{
			image = image.replace('#COLOR2#', encodeURIComponent('#' + color));
		}
		else
		{
			image = image.replace('#COLOR2#', encodeURIComponent('#' + this.backgroundColor));
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
		if ( baseColor.length > 7 )
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