<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
\Bitrix\Main\UI\Extension::load(['ui.fonts.opensans']);
?>

<style>
	.rest-app-container {
		display: flex;
		justify-content: center;
		align-items: center;
		flex-direction: column;
		padding: 20px;
		height: 100%;
		box-sizing: border-box;
	}

	.rest-app-title {
		margin: 0 0 38px 0;
		padding: 0;
		font: 37px/37px "OpenSans-Light", "Helvetica Neue", Helvetica, Arial, sans-serif;
		color: #525C69;
	}

	.rest-app-description {
		font: 14px "Helvetica Neue", Helvetica, Arial, sans-serif;
		color: rgba(82,92,105,.7);
	}

	.rest-app-description p {
		display: flex;
		align-items: center;
		justify-content: center;
		margin: 0 0 5px 0;
	}

	.rest-app-warning .rest-app-description {
		margin-bottom: 32px;
	}

	.rest-app-icon {
		position: relative;
		margin: 0 auto;
		width: 100%;
		max-width: 439px;
	}

	.rest-app-icon-cloud {
		position: absolute;
		bottom: 16px;
		left: -20px;
		display: block;
		width: 99px;
		height: 69px;
		background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20viewBox%3D%220%200%20101%2071%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M77.514%2069.964H24.122c-.457%200-.91-.017-1.358-.05C10.697%2069.637%201%2059.678%201%2047.434a22.608%2022.608%200%200%201%206.54-15.908%2022.275%2022.275%200%200%201%207.438-4.957%2023.855%2023.855%200%200%201%206.83-18.625A23.415%2023.415%200%200%201%2038.449%201c7.991.01%2015.047%204.044%2019.288%2010.2a19.057%2019.057%200%200%201%206.44-1.11c9.976.012%2018.17%207.688%2019.12%2017.503%209.559%202.104%2016.712%2010.699%2016.704%2020.975C99.99%2060.411%2090.473%2070.005%2078.74%2070c-.412%200-.82-.012-1.226-.036z%22%20stroke%3D%22%232FC6F6%22%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22%20opacity%3D%22.302%22/%3E%3C/svg%3E');
		background-repeat: no-repeat;
		opacity: 0;
	}

	.rest-app-icon-cloud.rest-app-icon-cloud-blue {
		position: absolute;
		top: 63px;
		left: 32px;
		display: block;
		width: 43px;
		height: 30px;
		background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20viewBox%3D%220%200%2043%2030%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M33.233%2029.985h-23.19c-.199%200-.395-.008-.59-.022C4.212%2029.842%200%2025.512%200%2020.188a9.835%209.835%200%200%201%202.84-6.916%209.674%209.674%200%200%201%203.231-2.155%2010.377%2010.377%200%200%201%202.967-8.098A10.165%2010.165%200%200%201%2016.265%200c3.471.004%206.536%201.758%208.378%204.435a8.27%208.27%200%200%201%202.797-.483c4.333.005%207.892%203.343%208.305%207.61%204.152.915%207.259%204.652%207.255%209.12-.004%205.149-4.138%209.32-9.234%209.318-.18%200-.357-.005-.533-.015z%22%20fill%3D%22%232FC6F6%22%20fill-rule%3D%22evenodd%22%20opacity%3D%22.117%22/%3E%3C/svg%3E');
		background-repeat: no-repeat;
		opacity: 0;
	}

	.rest-app-icon-cloud.rest-app-icon-cloud-blue.rest-app-icon-cloud-blue-right {
		position: absolute;
		top: auto;
		left: auto;
		bottom: 55px;
		right: 12px;
		width: 37px;
		height: 26px;
	}

	.rest-app-icon-cloud.rest-app-icon-cloud-right {
		position: absolute;
		top: 53px;
		left: auto;
		bottom: auto;
		right: 63px;
		width: 30px;
		height: 21px;
		background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20viewBox%3D%220%200%2032%2023%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M24.186%2021.99H8.006c-.138%200-.275-.006-.41-.016C3.938%2021.889%201%2018.858%201%2015.132a6.896%206.896%200%200%201%201.982-4.842%206.748%206.748%200%200%201%202.254-1.508%207.276%207.276%200%200%201%202.07-5.668A7.08%207.08%200%200%201%2012.348%201c2.421.003%204.56%201.23%205.845%203.104a5.752%205.752%200%200%201%201.951-.338c3.023.004%205.506%202.34%205.794%205.328%202.897.64%205.065%203.256%205.062%206.383-.003%203.605-2.887%206.524-6.443%206.523-.124%200-.248-.004-.371-.01z%22%20stroke%3D%22%232FC6F6%22%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22%20opacity%3D%22.302%22/%3E%3C/svg%3E');
		background-repeat: no-repeat;
	}

	.rest-app-icon-cloud-left-top {
		animation: rest-app-icon-cloud-left-top 3s cubic-bezier(0.215, 0.61, 0.355, 1) .4s forwards;
	}

	.rest-app-icon-cloud-left-bottom {
		animation: rest-app-icon-cloud-left-bottom 3s cubic-bezier(0.215, 0.61, 0.355, 1) .4s forwards;
	}

	.rest-app-icon-cloud-right-top {
		animation: rest-app-icon-cloud-right-top 3s cubic-bezier(0.215, 0.61, 0.355, 1) .4s forwards;
	}

	.rest-app-icon-cloud-right-bottom {
		animation: rest-app-icon-cloud-right-bottom 3s cubic-bezier(0.215, 0.61, 0.355, 1) .4s forwards;
	}

	.rest-app-icon-main {
		position: relative;
		display: block;
		margin: 0 auto 50px auto;
		width: 247px;
		height: 246px;
	}

	.rest-app-icon-refresh-noarrows {
		position: absolute;
		top: 19px;
		left: 20px;
		display: inline-block;
		width: 210px;
		height: 210px;
		background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20viewBox%3D%220%200%20207%20207%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M44.27%20181.734c30.611%2023.007%2073.223%2026.925%20108.48%206.57%2035.6-20.554%2053.515-59.973%2048.41-98.338a2.407%202.407%200%200%201%204.457-.631c5.354%2040.129-13.38%2081.367-50.617%20102.866-36.738%2021.21-81.113%2017.217-113.098-6.617l1.979%2011.223a2.063%202.063%200%201%201-4.064.717l-2.853-16.181a2.058%202.058%200%200%201%20.007-.756%202.056%202.056%200%200%201%201.699-2.04l16.269-2.87a2.056%202.056%200%201%201%20.714%204.05l-11.384%202.007zM160.13%2022.377C129.735%201.058%2088.52-2.089%2054.25%2017.697c-34.9%2020.15-52.805%2058.43-48.684%2096.075a2.407%202.407%200%200%201-4.428.902C-3.348%2075.178%2015.378%2034.943%2052%2013.799c35.901-20.727%2079.093-17.386%20110.892%205.018l-2.254-11.592a2.063%202.063%200%201%201%204.05-.787l3.136%2016.128c.05.258.05.513.006.756a2.056%202.056%200%200%201-1.663%202.07l-16.216%203.152a2.056%202.056%200%200%201-.785-4.036l10.963-2.13z%22%20fill%3D%22%23A6EAFE%22%20fill-rule%3D%22evenodd%22/%3E%3C/svg%3E');
		background-repeat: no-repeat;
		animation: rest-app-icon-refresh-animation 2s cubic-bezier(0.215, 0.61, 0.355, 1) forwards;
		opacity: 0;
	}
	
	.rest-app-icon-refresh-repeat-animation .rest-app-icon-refresh-noarrows {
		animation: rest-app-icon-refresh-repeat-animation 2s linear infinite;
	}

	.rest-app-icon-refresh-noarrows {
		width: 207px;
		height: 207px;
		background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20viewBox%3D%220%200%20207%20207%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cg%20fill%3D%22%232FC6F6%22%20fill-rule%3D%22nonzero%22%3E%3Cpath%20d%3D%22M64.139%20198.696a2.407%202.407%200%200%201%201.986-4.049c35.71%2014.792%2078.357%207.671%20107.39-21.362%2029.066-29.066%2036.17-71.78%2021.308-107.515a2.407%202.407%200%200%201%204.142-1.763c15.558%2037.375%208.135%2082.057-22.268%20112.46-30.43%2030.43-75.163%2037.84-112.558%2022.23zM141.552%207.75a2.407%202.407%200%200%201-1.295%204.328C104.826-1.985%2062.883%205.318%2034.215%2033.985%205.719%2062.481-1.668%20104.092%2012.055%20139.388a2.407%202.407%200%200%201-4.044%202.017c-14.554-36.99-6.88-80.7%2023.022-110.602C60.91.925%20104.58-6.76%20141.552%207.75z%22%20opacity%3D%22.129%22/%3E%3Cpath%20d%3D%22M126.068%2052.964l-2.163%202.164c-19.324-7.801-42.278-3.87-57.943%2011.795s-19.596%2038.619-11.795%2057.943l-1.639%201.638c-8.735-20.42-4.77-44.978%2011.896-61.644%2016.666-16.666%2041.223-20.631%2061.644-11.896zm29.074%2028.593c9.218%2020.587%205.378%2045.6-11.522%2062.499-16.899%2016.9-41.912%2020.74-62.499%2011.522l1.568-1.567c19.54%208.386%2043.055%204.602%2059.01-11.352%2015.953-15.954%2019.737-39.469%2011.35-59.009l2.093-2.093z%22%20opacity%3D%22.046%22/%3E%3C/g%3E%3C/svg%3E');
		background-repeat: no-repeat;
	}

	.rest-app-icon-refresh-noarrows-yellow {
		background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20viewBox%3D%220%200%20207%20207%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M64.139%20198.696a2.407%202.407%200%200%201%201.986-4.049c35.71%2014.792%2078.357%207.671%20107.39-21.362%2029.066-29.066%2036.17-71.78%2021.308-107.515a2.407%202.407%200%200%201%204.142-1.763c15.558%2037.375%208.135%2082.057-22.268%20112.46-30.43%2030.43-75.163%2037.84-112.558%2022.23zM141.552%207.75a2.407%202.407%200%200%201-1.295%204.328C104.826-1.985%2062.883%205.318%2034.215%2033.985%205.719%2062.481-1.668%20104.092%2012.055%20139.388a2.407%202.407%200%200%201-4.044%202.017c-14.554-36.99-6.88-80.7%2023.022-110.602C60.91.925%20104.58-6.76%20141.552%207.75z%22%20fill%3D%22%23FFA900%22%20fill-rule%3D%22nonzero%22%20opacity%3D%22.25%22/%3E%3C/svg%3E');
	}

	.rest-app-icon-alert {
		position: absolute;
		top: 65px;
		left: 63px;
		display: inline-block;
		width: 122px;
		height: 121px;
		background: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20viewBox%3D%220%200%20122%20121%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20xmlns%3Axlink%3D%22http%3A//www.w3.org/1999/xlink%22%3E%3Cdefs%3E%3Cfilter%20x%3D%22-9.1%25%22%20y%3D%22-7.3%25%22%20width%3D%22118.2%25%22%20height%3D%22118.3%25%22%20filterUnits%3D%22objectBoundingBox%22%20id%3D%22a%22%3E%3CfeOffset%20dy%3D%222%22%20in%3D%22SourceAlpha%22%20result%3D%22shadowOffsetOuter1%22/%3E%3CfeGaussianBlur%20stdDeviation%3D%223%22%20in%3D%22shadowOffsetOuter1%22%20result%3D%22shadowBlurOuter1%22/%3E%3CfeColorMatrix%20values%3D%220%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200.0741641171%200%22%20in%3D%22shadowBlurOuter1%22/%3E%3C/filter%3E%3Cpath%20d%3D%22M55%20109C24.624%20109%200%2084.6%200%2054.5S24.624%200%2055%200s55%2024.4%2055%2054.5S85.376%20109%2055%20109z%22%20id%3D%22b%22/%3E%3C/defs%3E%3Cg%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22%3E%3Cg%20opacity%3D%22.866%22%20fill-rule%3D%22nonzero%22%20transform%3D%22translate%286%204%29%22%3E%3Cuse%20fill%3D%22%23000%22%20filter%3D%22url%28%23a%29%22%20xlink%3Ahref%3D%22%23b%22/%3E%3Cuse%20fill%3D%22%23FF5752%22%20xlink%3Ahref%3D%22%23b%22/%3E%3C/g%3E%3Cpath%20d%3D%22M94.049%2074.5L66.115%2027.867c-2.152-3.58-7.28-3.58-9.387%200L28.794%2074.501c-2.198%203.672.458%208.308%204.717%208.308h55.867c4.213%200%206.869-4.636%204.67-8.308zM57.415%2044.116a3.698%203.698%200%20013.709-3.718h.504c2.06%200%203.709%201.652%203.709%203.718v13.908a3.698%203.698%200%2001-3.71%203.718h-.503a3.698%203.698%200%2001-3.71-3.718V44.115zm8.654%2026.99c0%202.57-2.106%204.681-4.67%204.681-2.565%200-4.671-2.111-4.671-4.682%200-2.57%202.106-4.682%204.67-4.682%202.565%200%204.671%202.112%204.671%204.682z%22%20fill%3D%22%23FFF%22/%3E%3C/g%3E%3C/svg%3E') no-repeat;
		animation: rest-app-icon-icon-animation .9s cubic-bezier(0.215, 0.61, 0.355, 1) forwards;
		opacity: 0;
		z-index: 3;
	}

	.rest-app-icon-circle {
		display: inline-block;
		width: 247px;
		height: 246px;
		background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20viewBox%3D%220%200%20247%20246%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M123.5%20246C55.293%20246%200%20190.932%200%20123%200%2055.069%2055.292%200%20123.5%200%20191.707%200%20247%2055.069%20247%20123c0%2067.932-55.293%20123-123.5%20123zm0-43c44.459%200%2080.5-35.817%2080.5-80s-36.041-80-80.5-80C79.04%2043%2043%2078.817%2043%20123s36.041%2080%2080.5%2080z%22%20fill%3D%22%239CE7FF%22%20fill-rule%3D%22nonzero%22%20opacity%3D%22.212%22/%3E%3C/svg%3E');
		background-repeat: no-repeat;
		animation: rest-app-icon-circle-animation .5s forwards .4s;
		opacity: 0;
	}

	.rest-app-icon-circle-yellow {
		background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20viewBox%3D%220%200%20248%20247%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M124%20246.5C55.793%20246.5.5%20191.432.5%20123.5.5%2055.569%2055.792.5%20124%20.5c68.207%200%20123.5%2055.069%20123.5%20123%200%2067.932-55.293%20123-123.5%20123zm0-44.5c43.625%200%2078.99-35.145%2078.99-78.5%200-43.354-35.365-78.5-78.99-78.5-43.626%200-78.99%2035.146-78.99%2078.5%200%2043.355%2035.365%2078.5%2078.99%2078.5z%22%20fill%3D%22%23F9E900%22%20fill-rule%3D%22nonzero%22%20opacity%3D%22.246%22/%3E%3C/svg%3E');
	}

	@keyframes rest-app-icon-icon-animation {
		0% {
			transform: scale(1.3);
			opacity: 0;
		}
		20% {
			opacity: 0;
		}
		30% {
			opacity: 0;
		}
		40% {
			opacity: .4;
		}
		100% {
			transform: scale(1);
			opacity: 1;
		}
	}

	@keyframes rest-app-icon-refresh-animation {
		0% {
			opacity: 0;
			transform: rotate(0deg);
		}
		100% {
			opacity: 1;
			transform: rotate(180deg);
		}
	}

	@keyframes rest-app-icon-refresh-animation {
		0% {
			opacity: 0;
			transform: rotate(0deg);
		}
		100% {
			opacity: 1;
			transform: rotate(180deg);
		}
	}

	@keyframes rest-app-icon-refresh-repeat-animation {
		0% {
			opacity: 1;
			transform: rotate(0deg);
		}
		100% {
			opacity: 1;
			transform: rotate(360deg);
		}
	}

	@keyframes rest-app-icon-circle-animation {
		0% {
			opacity: 0;
		}
		100% {
			opacity: 1;
		}
	}

	@keyframes rest-app-icon-cloud-left-top {
		0% {
			left: 59px;
			opacity: 0;
		}
		100% {
			left: 32px;
			opacity: 1;
		}
	}

	@keyframes rest-app-icon-cloud-left-bottom {
		0% {
			left: -50px;
			opacity: 0;
		}
		100% {
			left: -20px;
			opacity: 1;
		}
	}

	@keyframes rest-app-icon-cloud-right-top {
		0% {
			right: 78px;
			opacity: 0;
		}
		100% {
			right: 63px;
			opacity: 1;
		}
	}

	@keyframes rest-app-icon-cloud-right-bottom {
		0% {
			right: 0;
			opacity: 0;
		}
		100% {
			right: 12px;
			opacity: 1;
		}
	}
</style>

<div class="rest-app-container rest-app-warning">
	<h1 class="rest-app-title rest-app-title-dark-text"><?=htmlspecialchars($_REQUEST['title'])?></h1>
	<div class="rest-app-description">
		<?=htmlspecialchars(urldecode($_REQUEST['message']))?>
	</div>
	<div class="rest-app-icon">
		<div class="rest-app-icon-cloud rest-app-icon-cloud-blue rest-app-icon-cloud-left-top"></div>
		<div class="rest-app-icon-cloud rest-app-icon-cloud-left-bottom"></div>
		<div class="rest-app-icon-cloud rest-app-icon-cloud-blue rest-app-icon-cloud-blue-right rest-app-icon-cloud-right-bottom"></div>
		<div class="rest-app-icon-cloud rest-app-icon-cloud-right rest-app-icon-cloud-right-top"></div>
		<div class="rest-app-icon-main">
			<div class="rest-app-icon-refresh-noarrows rest-app-icon-refresh-noarrows-yellow"></div>
			<div class="rest-app-icon-alert"></div>
			<div class="rest-app-icon-circle rest-app-icon-circle-yellow"></div>
		</div>
	</div>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
