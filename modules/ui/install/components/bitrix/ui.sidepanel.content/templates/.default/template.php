<?php
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-background');
\Bitrix\Main\UI\Extension::load(['ui', 'ui.sidepanel-content']);
?>
<div class="ui-slider-section">
	<div class="ui-slider-content-box">
		<div class="ui-slider-heading-2">Заголовок первой секции слайдера. Heading 3</div>
		<p class="ui-slider-paragraph">Paragraph 1. Текст слайдера. Теперь при загрузке картинки автоматически попадают в графический редактор. Вы можете обрезать изображение до нужного размера, настроить параметры, добавить текст и стикеры</p>
	</div>
	<div class="ui-slider-content-box">
		<div class="ui-slider-heading-4">Заголовок раздела слайдера. Heading 4</div>
		<p class="ui-slider-paragraph-2">Paragraph 2. Текст слайдера. Теперь при загрузке картинки автоматически попадают в графический редактор. Вы можете обрезать изображение до нужного размера, настроить параметры, добавить текст и стикеры</p>
	</div>
</div>
<div class="ui-slider-section">
	<div class="ui-slider-content-box">
		<div class="ui-slider-heading-4">Заголовок. Heading 4</div>
		<p class="ui-slider-paragraph-2">Paragraph 2. Текст блока. Теперь при загрузке картинки автоматически попадают в графический редактор. Текст слайдера. Теперь при загрузке картинки автоматически попадают в графический редактор. Вы можете обрезать изображение до нужного размера, настроить параметры, добавить текст и стикеры Теперь при загрузке картинки автоматически попадают в графический редактор.</p>
		<p class="ui-slider-paragraph-2">Текст слайдера. Теперь при загрузке картинки автоматически попадают в графический редактор. Вы можете обрезать изображение до нужного размера, настроить параметры, добавить текст и стикеры Теперь при загрузке картинки автоматически попадают в графический редактор. Текст слайдера. Теперь при загрузке картинки автоматически попадают в графический редактор. Вы можете обрезать изображение до нужного размера, настроить параметры, добавить текст и стикеры</p>
	</div>
</div>
<div class="ui-slider-section ui-slider-section-icon">
	<span class="ui-icon ui-slider-icon"><i></i></span>
	<div class="ui-slider-text-box">
		<div class="ui-slider-heading-3">Подключите бота своей компании</div>
		<div class="ui-slider-inner-box">
			<p class="ui-slider-paragraph-2">Для подключения необходимо создать публичный аккаунт в Viber или подключить уже существующий. Если у вас еще нет публичного аккаунта, мы поможем создать его в несколько шагов и подключить к вашему Битрикс24</p>
			<a href="#" class="ui-slider-link">Подробнее о подключении</a>
		</div>
	</div>
</div>
<div class="ui-slider-section">
	<div class="ui-slider-heading-4">Заголовок. Heading 4</div>
	<ul class="ui-slider-list">
		<li class="ui-slider-list-item">
			<span class="ui-slider-list-number">1</span>
			<span class="ui-slider-list-text">Клиент звонит или пишет в чат</span>
		</li>
		<li class="ui-slider-list-item">
			<span class="ui-slider-list-number">2</span>
			<span class="ui-slider-list-text">Вам приходит уведомление о том, что заказ оплачен</span>
		</li>
	</ul>
</div>

<div class="ui-slider-no-access">
	<div class="ui-slider-no-access-inner">
		<div class="ui-slider-no-access-title">Задача не найдена или доступ запрещен</div>
		<div class="ui-slider-no-access-subtitle">Обратитесь к участникам задачи или администратору портала</div>
		<div class="ui-slider-no-access-img">
			<div class="ui-slider-no-access-img-inner"></div>
		</div>
	</div>
</div>

<form action="/company/detail/1/">
	<?$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
		'BUTTONS' => ['save', 'cancel' => '/company/list/']
	]);?>
</form>
