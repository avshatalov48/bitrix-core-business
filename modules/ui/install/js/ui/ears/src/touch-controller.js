export class TouchController
{
	constructor({ target })
	{
		this.target = target ? target : null;
		this.pos = { top: 0, left: 0, x: 0, y: 0 };
		this.touchInit = false;
		this.init();
	}

	init()
	{
		if (!this.target)
		{
			console.warn('BX.UI.Ears: TouchController not initialized')
			return;
		}

		this.target.addEventListener('mousedown', this.mouseDownHandler.bind(this));
		this.target.addEventListener('mousemove', this.mouseMoveHandler.bind(this));
		this.target.addEventListener('mouseup', this.mouseUpHandler.bind(this));
		this.target.addEventListener('mouseleave', this.mouseUpHandler.bind(this));
	}

	mouseDownHandler(ev)
	{
		this.touchInit = true;
		this.target.style.cursor = 'grabbing';
		this.target.style.userSelect = 'none';
		this.target.parentNode.classList.add('--grabbing');

		this.pos = {
			left: this.target.scrollLeft,
			top: this.target.scrollTop,
			x: ev.clientX,
			y: ev.clientY,
		};
	}

	mouseMoveHandler(ev)
	{
		if (!this.touchInit)
		{
			return;
		}

		const dx = ev.clientX - this.pos.x;
		const dy = ev.clientY - this.pos.y;

		this.target.scrollLeft = this.pos.left - dx;
		this.target.scrollTop = this.pos.top - dy;
	}

	mouseUpHandler()
	{
		this.touchInit = false;
		this.target.style.cursor = 'grab';
		this.target.style.removeProperty('user-select');
		this.target.parentNode.classList.remove('--grabbing');
	}
}