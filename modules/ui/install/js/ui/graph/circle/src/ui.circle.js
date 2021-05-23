import {Event, Dom} from "main.core";

export class Circle {
	constructor(domNode, perimetr, progressBar, settings)
	{
		this.domNode = domNode;
		this.progressBar = Number(progressBar) > 100 ? 100 : progressBar;
		this.progressBg = null;
		this.number = null;
		this.fixCounter = settings.fixCounter ? settings.fixCounter : null;
		this.color1 = settings.color1;
		this.color2 = settings.color2;
		this.x = 0;
		this.flat = 400;
		this.speed = 3;
		this.rate = 0;
		this.wave = 35;
		this.distance = 200;
	}

	createCanvas()
	{
		this.canvas = Dom.create('canvas', {
			attrs: {
				className: 'ui-graph-circle-canvas',
				// 'data-progress': this.progressBar
			}
		});

		return this.canvas;
	}

	createNumberBlock()
	{
		this.number = Dom.create('div', {
			attrs: {
				className: 'ui-graph-circle-number',
				'data-progress': this.progressBar
			}
		});

		return this.number;
	}

	drawCircle(ctx, mW, color)
	{
		ctx.mozImageSmoothingEnabled = false;
		ctx.imageSmoothingEnabled = false;
		ctx.beginPath();
		ctx.arc(mW / 2, mW / 2, mW / 2 - 1, 0, 2 * Math.PI);
		ctx.strokeStyle = color;
		ctx.stroke();
		ctx.beginPath();
		ctx.arc(mW / 2, mW / 2, mW / 2 - 2, 0, 2 * Math.PI);
		ctx.clip();
	}

	drawSin(ctx, mW, color1, color2, wav, dY)
	{
		ctx.mozImageSmoothingEnabled = false;
		ctx.imageSmoothingEnabled = false;
		ctx.save();
		ctx.beginPath();
		ctx.moveTo(0, mW);
		ctx.lineTo(0, dY);
		ctx.quadraticCurveTo(mW / 4, dY - (mW * (wav / 200)), mW / 2, dY);
		ctx.lineTo(mW / 2, dY);
		ctx.quadraticCurveTo((mW * 3) / 4, dY + (mW * (wav / 200)), mW, dY);
		ctx.lineTo(mW, mW);
		ctx.lineTo(0, mW);
		ctx.fillStyle = color1;
		ctx.fill();
		ctx.restore();
	}

	init(mW)
	{
		const canvas1 = this.canvas;
		canvas1.style.height = mW;
		canvas1.width = canvas1.height = mW;

		const canvas2 = document.createElement('canvas'),
		ctx2 = canvas2.getContext('2d');
		canvas2.width = mW;
		canvas2.height = mW;

		const canvas3 = document.createElement('canvas'),
		ctx3 = canvas3.getContext('2d');
		canvas3.width = mW;
		canvas3.height = mW;

		let x = this.x;
		let flat = this.flat;
		let speed = this.speed;
		let rate = this.rate;
		let wave = this.wave;
		let distance = this.distance;

		const ctx1 = canvas1.getContext('2d');

		if (!this.color1 && !this.color2)
		{
			this.drawSin(ctx2, mW, 'rgba(183, 235, 129, .45)', 'rgba(183, 235, 129, .45)', wave, mW - mW * rate);
			this.drawSin(ctx3, mW, 'rgba(178, 232, 0, .29)', 'rgba(178, 232, 0, .29)', wave, mW - mW * rate);
		}
		else
		{
			this.drawSin(ctx2, mW, this.color1, this.color1, wave, mW - mW * rate);
			this.drawSin(ctx3, mW, this.color2, this.color2, wave, mW - mW * rate);
		}

		function animation()
		{
			ctx1.clearRect(0, 0, mW, mW);
			ctx1.drawImage(canvas2, x, 0, mW + flat, mW);
			ctx1.drawImage(canvas2, x - mW - flat, 0, mW + flat, mW);
			ctx1.drawImage(canvas3, x - distance, 0, mW + flat, mW);
			ctx1.drawImage(canvas3, x - mW - distance - flat, 0, mW + flat, mW);
			x >= (mW - speed + flat) ? x = 0 : x += speed;
			requestAnimationFrame(animation);
		}
		animation();
	}

	animateWavesBlock(fixCounter)
	{
		this.progress = this.progressBar;

		if(fixCounter)
		{
			if (this.progress <= 50)
			{
				this.rate = 0.45;
			}

			if (this.progress > 50)
			{
				this.rate = 0.75;
			}

			this.progressBar <= 25 ? this.rate = 0.25 : null;
		}
		else
		{
			if (this.progress <= 50)
			{
				this.rate = 0.50;
			}

			if (this.progress > 50)
			{
				this.rate = 0.85;
			}
		}

		this.progressBar <= 25 ? this.progress = 25 : null;
	}

	createWrapper()
	{
		this.graph = Dom.create('div', {
			attrs: {
				className: 'ui-graph-circle-wrapper'
			}
		});
		Dom.append(this.createCanvas(), this.graph);
		Dom.append(this.createNumberBlock(), this.graph);

		return this.graph;
	}

	addWrapperClass()
	{
		Dom.addClass(this.graph, 'ui-graph-circle-wrapper-animate');

		if (this.fixCounter)
		{
			Dom.addClass(this.graph, 'ui-graph-circle-counter');
		}
	}

	animateNumber(fixCounter)
	{
		let progress = this.progressBar;

		if(fixCounter)
		{
			progress = fixCounter;
		}

		if (Number(progress) <= 0)
		{
			if(fixCounter)
			{
				this.number.innerHTML = '0';
			}
		else
		{
			this.number.innerHTML = '0' + ' <span>%</span>';
		}

			return;
		}

		if(fixCounter)
		{
			this.number.innerHTML = fixCounter;
			this.number.innerHTML.length >= 4 ? Dom.addClass(this.graph, 'ui-graph-font-sm') : null;
		}
		else
		{
			let i = 0;
			let time = 1000 / progress;
			let interval = setInterval(function()
			{
				i++;
				this.number.innerHTML = i + ' <span>%</span>';
				i === Number(progress) ? clearInterval(interval) : null;
			}.bind(this), time);
		}
	}

	updateCounter(counter, fixCounter)
	{
		this.progressBar = counter;

		if (fixCounter) {
			this.animateNumber(fixCounter);
			this.animateWavesBlock(fixCounter);
		}
		else
		{
			this.animateNumber();
			this.animateWavesBlock();
		}
	}

	show()
	{
		Dom.append(this.createWrapper(), this.domNode);

		setTimeout(function() {
			this.addWrapperClass();
			this.animateNumber(this.fixCounter);
			this.animateWavesBlock(this.fixCounter);
			this.init(200);
		}.bind(this), 500);
	}
}
