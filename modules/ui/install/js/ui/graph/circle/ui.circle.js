(function() {
	'use strict';

	BX.namespace('BX.UI.Graph');

	BX.UI.Graph.Circle = function(domNode, perimetr, progressBar, settings) {
		this.domNode = domNode;
		this.perimetr = perimetr;
		this.radius = perimetr / 2;
		this.progressBar = Number(progressBar) > 100 ? 100 : progressBar;
		this.progressBg = null;
		this.number = null;
		this.waves = null;
		this.leftWave = null;
		this.rightWave = null;
		this.fixCounter = settings.fixCounter ? settings.fixCounter : null;
		this.color1 = settings.color1;
		this.color2 = settings.color2;
		this.x = 0;
		this.flat = 400;
		this.speed = 3;
		this.rate = 0;
		this.wave = 35;
		this.distance = 200;
	};

	BX.UI.Graph.Circle.prototype =
		{
			getCircumFerence: function() {
				return (this.radius - 10) * 2 * 3.14;
			},

			getCircumProgress: function() {
				return this.getCircumFerence() - (this.getCircumFerence() / 100 * this.progressBar);
			},

			createCircle: function() {
				this.svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
				this.svg.setAttributeNS(null, 'class', 'ui-graph-circle-bar');
				this.svg.setAttributeNS(null, 'viewport', '0 0 ' + this.radius + ' ' + this.radius);
				this.svg.setAttributeNS(null, 'width', this.perimetr);
				this.svg.setAttributeNS(null, 'height', this.perimetr);

				this.progressBg = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
				this.progressBg.setAttributeNS(null, 'r', this.radius - 10);
				this.progressBg.setAttributeNS(null, 'cx', this.radius);
				this.progressBg.setAttributeNS(null, 'cy', this.radius);
				this.progressBg.setAttributeNS(null, 'class', 'ui-graph-circle-bar-bg');

				this.progressMove = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
				this.progressMove.setAttributeNS(null, 'r', this.radius - 10);
				this.progressMove.setAttributeNS(null, 'cx', this.radius);
				this.progressMove.setAttributeNS(null, 'cy', this.radius);
				this.progressMove.setAttributeNS(null, 'stroke-dasharray', this.getCircumFerence());
				this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircumFerence());
				this.progressMove.setAttributeNS(null, 'class', 'ui-graph-circle-bar-progress');

				this.svg.appendChild(this.progressBg);
				this.svg.appendChild(this.progressMove);

				return this.svg;
			},

			animateFixedBar: function() {
				this.svg.setAttributeNS(null, 'class', 'ui-graph-circle-bar ui-graph-circle-bar-animate ui-graph-circle-bar-without-animate');
				this.progressMove.setAttributeNS(null, 'stroke-dashoffset', 0);
			},

			animateProgressBar: function() {
				this.svg.setAttributeNS(null, 'class', 'ui-graph-circle-bar ui-graph-circle-bar-animate');
				this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircumProgress());
			},

			createCanvas: function()
			{
				this.canvas = BX.create('canvas', {
					attrs: {
						className: 'ui-graph-circle-canvas',
						// 'data-progress': this.progressBar
					}
				});

				return this.canvas;
			},

			createNumberBlock: function() {
				this.number = BX.create('div', {
					attrs: {
						className: 'ui-graph-circle-number',
						'data-progress': this.progressBar
					}
				});

				return this.number;
			},

			drawCircle: function(ctx, mW, color)
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
			},

			drawSin: function(ctx, mW, color1, color2, wav, dY)
			{
				ctx.mozImageSmoothingEnabled = false;
				ctx.imageSmoothingEnabled = false;
				ctx.save();
				ctx.beginPath();
				ctx.moveTo(0, mW);
				ctx.lineTo(0, dY);
				ctx.quadraticCurveTo(mW / 4, dY - (mW * (wav / 200)), mW / 2, dY);
				// ctx.quadraticCurveTo(mW / 4, dY - wav, mW / 2, dY)
				ctx.lineTo(mW / 2, dY);
				ctx.quadraticCurveTo((mW * 3) / 4, dY + (mW * (wav / 200)), mW, dY);
				// ctx.quadraticCurveTo((mW * 3) / 4, dY + wav, mW, dY);
				//  ctx.stroke();
				ctx.lineTo(mW, mW);
				ctx.lineTo(0, mW);
				ctx.fillStyle = color1;
				ctx.fill();
				ctx.restore();
			},

			init: function(mW) {
				var canvas1 = document.querySelector('.ui-graph-circle-canvas');
				canvas1.style.height = mW;
				canvas1.width = canvas1.height = mW;

				var canvas2 = document.createElement('canvas'),
				ctx2 = canvas2.getContext('2d');
				canvas2.width = mW;
				canvas2.height = mW;

				var canvas3 = document.createElement('canvas'),
				ctx3 = canvas3.getContext('2d');
				canvas3.width = mW;
				canvas3.height = mW;

				var x = this.x;
				var flat = this.flat;
				var speed = this.speed;
				var rate = this.rate;
				var wave = this.wave;
				var distance = this.distance;

				var ctx1 = canvas1.getContext('2d');

				// this.drawCircle(ctx1, mW, '#fff');
				// 'rgba(49,205,255,.41)'
				// 'rgba(85, 208, 224,.32)'

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
			},

			createWavesBlock: function() {
				return BX.create('div', {
					attrs: {
						className: 'ui-graph-circle-waves-wrapper'
					},
					children: [
						this.waves = BX.create('div', {
							attrs: {
								className: 'ui-graph-circle-waves'
							},
							children: [
								this.leftWave = BX.create('div', {
									attrs: {
										className: 'ui-graph-circle-waves-left'
									}
								}),
								this.rightWave = BX.create('div', {
									attrs: {
										className: 'ui-graph-circle-waves-right'
									}
								})
							]
						})
					]
				})
			},

			animateWavesBlock: function(fixCounter) {
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

					// this.progressBar <= 25 ? progress = 25 : null;
					// this.waves.style.transform = 'translateY(-' + progress + '%)';
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
					console.log('animateWavesBlock', this.progress);
				}

				this.progressBar <= 25 ? this.progress = 25 : null;
				// this.waves.style.transform = 'translateY(-' + progress + '%)';
			},

			animateBothWaves: function() {
				var currentPosWaveLeft = 0;
				var currentPosWaveRight = 50;
				var fps = 15;
				var now;
				var then = Date.now();
				var interval = 1000/fps;
				var delta;

				function draw() {
					requestAnimationFrame(draw);
					now = Date.now();
					delta = now - then;

					if (delta > interval)
					{
						then = now - (delta % interval);

						var leftWave = document.querySelector('.ui-graph-circle-waves-left');
						var rightWave = document.querySelector('.ui-graph-circle-waves-right');

						currentPosWaveLeft += 1;
						currentPosWaveRight -= 1;

						leftWave.style.transform = 'translateX('+ currentPosWaveLeft + '%)';
						rightWave.style.transform = 'translateX('+ currentPosWaveRight + '%)';

						if (parseInt(currentPosWaveLeft, 10) >= 50)
						{
							currentPosWaveLeft = 0;
						}

						if (parseInt(currentPosWaveRight, 10) <= 0)
						{
							currentPosWaveRight = 50;
						}
					}
				}
				draw();
			},

			createWrapper: function() {
				this.graph = BX.create('div', {
					attrs: {
						className: 'ui-graph-circle-wrapper'
					}
				});

				// this.graph.appendChild(this.createCircle());
				this.graph.appendChild(this.createCanvas());
				this.graph.appendChild(this.createNumberBlock());
				// this.graph.appendChild(this.createWavesBlock());

				return this.graph;
			},

			addWrapperClass: function() {
				this.graph.classList.add('ui-graph-circle-wrapper-animate');

				if (this.fixCounter)
				{
					this.graph.classList.add('ui-graph-circle-counter');
				}
			},

			animateNumber: function(fixCounter) {
				var progress = this.progressBar;

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
					this.number.innerHTML.length >= 4 ? this.graph.classList.add('ui-graph-font-sm') : null;
				}
				else
				{
					var i = 0;
					var time = 1000 / progress;
					var interval = setInterval(function()
					{
						i++;
						this.number.innerHTML = i + ' <span>%</span>';
						i === Number(progress) ? clearInterval(interval) : null;
					}.bind(this), time);
				}
			},

			updateCounter: function(counter, fixCounter)
			{
				this.progressBar = counter;

				// if (fixCounter)
				// {
				// 	this.progressMove.setAttributeNS(null, 'stroke-dashoffset', 0);
				// }
				// else
				// {
				// 	this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircumProgress());
				// }
				if (fixCounter) {
					this.animateNumber(fixCounter);
					this.animateWavesBlock(fixCounter);
				}
				else
				{
					this.animateNumber();
					this.animateWavesBlock();
				}
			},

			show: function() {
				this.domNode.appendChild(this.createWrapper());

				setTimeout(function() {
					this.addWrapperClass();
					this.animateNumber(this.fixCounter);
					if (this.fixCounter)
					{
						// this.animateFixedBar();
					}
					else
					{
						// this.animateProgressBar();
					}
					// this.animateBothWaves();
					this.animateWavesBlock(this.fixCounter);
					this.init(200);
				}.bind(this), 500);
			}
		};

})();
