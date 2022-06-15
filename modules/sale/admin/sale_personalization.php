<?php
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

Loader::includeModule('sale');

if (!\Bitrix\Sale\Configuration::isCanUsePersonalization())
{
	LocalRedirect('/bitrix/admin/');
}

IncludeModuleLangFile(__FILE__);

// Page header
$APPLICATION->SetTitle(GetMessage('BIGDATA_PERSONALIZATION'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>

<style type="text/css">
.adm-c-bigdata-container{
	border-radius:5px;
	background:#fff url(/bitrix/images/sale/bigdata/bg3.png) repeat-x top center;
}

.adm-c-bigdata-title-box{
	padding-top:140px;
	background:url(/bitrix/images/sale/bigdata/h2.png) no-repeat center 70px;
}
.adm-c-bigdata-title-box h2{
	font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;
	font-size:27px;
	font-weight:normal;
	margin:0;
	padding:0 0 20px 0;
	text-align:center;
	color:#fff;
}
.adm-c-bigdata-mac{
	width:389px;
	height:245px;
	margin:20px auto;
	padding:17px 61px 46px;
	background:url(/bitrix/images/sale/bigdata/mac.png) no-repeat center;
}

.adm-c-bigdata-content{
	width:880px;
	margin:50px auto 20px;
	padding:20px;
	background:#f3f3f3;
}
.adm-c-bigdata-blocks-content{
	width:720px;
	/*min-height:100px;*/
	padding:160px 80px 10px;
	background:url(/bitrix/images/sale/bigdata/widg.png) no-repeat center 15px;
}

.adm-c-bigdata-blocks-content-part1{
	float:left;
	-webkit-box-sizing:border-box;
	-moz-box-sizing:border-box;
	box-sizing:border-box;
	width:33%;
	padding-right:40px;
	font-family:'Open Sans','Helvetica Neue', Helvetica, Arial, sans-serif;
	text-align: center;
	font-size: 16px;
	padding-top: 22px;
}
.adm-c-bigdata-blocks-content-part2{
	float:left;
	-webkit-box-sizing:border-box;
	-moz-box-sizing:border-box;
	box-sizing:border-box;
	width:34%;
	padding:22px 20px 0;
	font-family:'Open Sans','Helvetica Neue', Helvetica, Arial, sans-serif;
	text-align: center;
	font-size: 16px;
	/*padding-top: 25px;*/
}
.adm-c-bigdata-blocks-content-part3{
	float:left;
	-webkit-box-sizing:border-box;
	-moz-box-sizing:border-box;
	box-sizing:border-box;
	width:33%;
	padding-left:40px;
	font-family:'Open Sans','Helvetica Neue', Helvetica, Arial, sans-serif;
	text-align: center;
	font-size: 16px;
	padding-top: 22px;
}


.adm-c-bigdata-activate{
	border: 2px solid #fed800;
	width:920px;
	margin:0 auto 20px;
}
.adm-c-bigdata-activate-title{
	color: #000;
	background: #fed800;
	height: 50px;
	line-height: 50px;
	vertical-align: middle;
	font-size: 24px;
	text-align: center;
	font-family: 'Open Sans', "Helvetica Neue", Helvetica, Arial, sans-serif;
}
.adm-c-bigdata-activate-content{
	font-family: 'Open Sans', "Helvetica Neue", Helvetica, Arial, sans-serif;
	padding: 30px;
	background: #fff;
}
.adm-c-bigdata-activate-content-task-list{
	list-style: none;
}
.adm-c-bigdata-activate-content-task-list li{
	font-size: 18px;
	font-family: 'Open Sans', "Helvetica Neue", Helvetica, Arial, sans-serif;
	font-weight: 300;
	margin-bottom: 14px;
	display: block;
	position: relative;
	font-size: 17px;
}
.adm-c-bigdata-activate-content-task-list li:before{
	position: absolute;
	content: " ";
	display: block;
	top: 3px;
	width: 14px;
	height:14px;
	left: -25px;
	border: 2px solid #afb9bb;
	border-radius: 50%;
}
.adm-c-bigdata-activate-content-task-list li.good:after{
	background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABEAAAAOCAYAAADJ7fe0AAAAmUlEQVQ4y2NgIBO0b5BfAMR+DBQYsAyIn0FxLDkGzEIyAIS3kGrABDQDNgIxGykGtKEZsJtUF1SjGXAUiNnRFcUD8VogZsFiQD6aAWeBmB9dUTqSgp3IBgHZaWgGXAFiUWxOXYWmcCdU3A1N/A4Qy+Pz81wsfn6KxH8MxBrkxD8yNiIlFmZiMcCKnBSJbJArJXljHhBnEaseANB4tKwLlzcDAAAAAElFTkSuQmCC') no-repeat center;
	width: 20px;
	height: 18px;
	position: absolute;
	display: block;
	content: " ";
	left: -24px;
	top: 1px;
}

.adm-c-bigdata-activate-content-task-list-warning{

}
.adm-c-bigdata-activate-content-task-list-warning span{
	background: #ffe5e5;
	display: inline-block;
	color: #000;
	font-size: 12px;
	padding: 2px 10px;
}

.adm-c-bigdata-getStart-btn-container{
	text-align: center;
}
.adm-c-bigdata-getStart-btn{
	margin-top: 20px;
	display: inline-block;
	color: #fff;
	background: #87b01f;
	height:40px;
	border-radius: 3px;
	padding: 0 15px;
	line-height: 40px;
	vertical-align: middle;
	font-size: 18px;
	text-decoration: none;
	font-weight: bold;
	font-family: "Helvetica Neue", Arial, Helvetica, sans-serif;
	text-shadow:0 1px 1px #789d1c;
}
.adm-c-bigdata-getStart-btn:hover{
	text-decoration: none;
	background: #789d1c;
}

.adm-c-bigdata-desc{
	width:850px;
	margin:0 auto;
	padding-bottom: 20px;
}
.adm-c-bigdata-desc p{
	font-size: 12px;
	color: #343434;
}

.clb{clear: both;}

</style>

<div class="adm-c-bigdata-container">

	<div class="adm-c-bigdata-title-box">
		<h2><?=GetMessage('BIGDATA_CONVERT')?></h2>
		<?if (\Bitrix\Main\Application::getInstance()->getLicense()->getRegion() !== 'ua'):?>
			<div class="adm-c-bigdata-mac"><iframe width="389" height="245" src="//www.youtube.com/embed/AtNZQGbkjHI?rel=0&amp;controls=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe></div>
		<?endif;?>
	</div>

	<div class="adm-c-bigdata-content">
		<div class="adm-c-bigdata-blocks-content">
			<div class="adm-c-bigdata-blocks-content-part1">
				<?=GetMessage('BIGDATA_NUM_ONE')?>
			</div>
			<div class="adm-c-bigdata-blocks-content-part2">
				<?=GetMessage('BIGDATA_CONNECT')?>
			</div>
			<div class="adm-c-bigdata-blocks-content-part3">
				<?=GetMessage('BIGDATA_PLATFORM')?>
			</div>
			<div class="clb"></div>
		</div>
	</div>

	<div class="adm-c-bigdata-activate">
		<div class="adm-c-bigdata-activate-title"><?=GetMessage('BIGDATA_HOWTO_ENABLE')?></div>
		<div class="adm-c-bigdata-activate-content">
			<ul class="adm-c-bigdata-activate-content-task-list">

				<? $available = \Bitrix\Main\Analytics\Catalog::isOn(); ?>
				<li <?=$available?'class="good"':''?>>
					<?=GetMessage('BIGDATA_ENABLED')?>
					<? if (!$available): ?>
						<div class="adm-c-bigdata-activate-content-task-list-warning"><span><?=GetMessage('BIGDATA_DISABLED')?></span></div>
					<? endif; ?>
				</li>

				<? $installed = (time()-Bitrix\Main\Config\Option::get('main', 'rcm_component_usage', 0)<3600*24);?>
				<li <?=$installed?'class="good"':''?>>
					<?=GetMessage('BIGDATA_INSTALLED')?>
					<? if (!$installed): ?>
						<div class="adm-c-bigdata-activate-content-task-list-warning"><span><?=GetMessage('BIGDATA_UNINSTALLED')?></span></div>
					<? endif; ?>
				</li>

				<li <?=($available && $installed)?'class="good"':''?>><?=GetMessage('BIGDATA_OBSERVE')?></li>
			</ul>

			<?
				$goUrl = '';

				if ($available && $installed)
				{
					$goUrl = 'sale_order.php?lang='.LANGUAGE_ID;
				}
				elseif (!$available)
				{
					$goUrl = 'settings.php?mid=main&mid_menu=1&lang='.LANGUAGE_ID;
				}
				elseif (!$installed)
				{
					$goUrl = 'https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=42&CHAPTER_ID=05367';
				}
			?>

			<div class="adm-c-bigdata-getStart-btn-container">
				<a href="<?=htmlspecialcharsbx($goUrl)?>" class="adm-c-bigdata-getStart-btn"><?=($available && $installed)?GetMessage('BIGDATA_ANALYZE'):GetMessage('BIGDATA_GO')?></a>
			</div>
		</div>
	</div>

	<div class="adm-c-bigdata-desc">
		<p><?=GetMessage('BIGDATA_DESC_1')?></p>

		<p><?=GetMessage('BIGDATA_DESC_2')?></p>

		<p><?=GetMessage('BIGDATA_DESC_3')?></p>
	</div>
</div>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>