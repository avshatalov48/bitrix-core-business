<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("sale.account.pay");
?>
	<link rel="stylesheet" href="style.css">

<!---->
	<div class="bx-sap">
		<h2>Пополнение счета</h2>

		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12 bx-sap-block">
					<h3 class="bx-sap-title">Фиксированный платеж</h3>
					<div class="bx-sap-fixedpay-container">
						<ul class="bx-sap-fixedpay-list">
							<li>100</li>
							<li>200</li>
							<li>500</li>
							<li>1000</li>
							<li>5000</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12 bx-sap-block form-horizontal">
					<h3 class="bx-sap-title">Сумма</h3>
					<div class="" style="max-width: 200px;">
						<div class="form-group" style="margin-bottom: 0;">
							<div class="col-sm-9">
								<input type="text" class="form-control input-lg" id="" placeholder="0.00">
							</div>
							<label class="control-label input-lg input-lg col-sm-3" for="">RUB</label>
						</div>

					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12 bx-sap-block">
					<h3 class="bx-sap-title">Метод оплаты</h3>
					<div>
						<div class="bx-soa-pp row">
							<div class="col-md-7 col-sm-8 col-xs-12 bx-soa-pp-item-container">
								<div class="bx-soa-pp-company col-lg-3 col-sm-4 col-xs-6">
									<div class="bx-soa-pp-company-graf-container">
										<input id=""
												 name="PAY_SYSTEM_ID"
												 type="checkbox"
												 class="bx-soa-pp-company-checkbox"
												 value="4">
										<div class="bx-soa-pp-company-image"
											 style="
											 		background-image: url(/upload/sale/paysystem/logotip/5b8/5b8538f5ec25775365aa346a10547adb.gif); /*Картинка по умолчанию 150px шириной*/
											 		background-image: -webkit-image-set(url(150px.jpg) 1x, url(230px.jpg) 2x); /*картинка для обычного 150px для ретины 300px*/

											 		"></div>
									</div>
									<div class="bx-soa-pp-company-smalltitle">Яндекс.Деньги</div>
								</div>
								<div class="bx-soa-pp-company col-lg-3 col-sm-4 col-xs-6">
									<div class="bx-soa-pp-company-graf-container">
										<input id=""
												 name="PAY_SYSTEM_ID"
												 type="checkbox"
												 class="bx-soa-pp-company-checkbox"
												 value="5">
										<div class="bx-soa-pp-company-image"
											 style="background-image: url(/upload/sale/paysystem/logotip/3fe/3fe185a7bd98ff1fc8d8a850e606c977.gif);"></div>
									</div>
									<div class="bx-soa-pp-company-smalltitle">Банковские карты</div>
								</div>
								<div class="bx-soa-pp-company col-lg-3 col-sm-4 col-xs-6 bx-selected">
									<div class="bx-soa-pp-company-graf-container">
										<input id="ID_PAY_SYSTEM_ID_6"
												 name="PAY_SYSTEM_ID"
												 type="checkbox"
												 class="bx-soa-pp-company-checkbox"
												 value="6">
										<div class="bx-soa-pp-company-image"
											 style="background-image: url(/upload/sale/paysystem/logotip/f58/f586e0454a76f17b222fa04231d3fe2e.gif);"></div>
									</div>
									<div class="bx-soa-pp-company-smalltitle">Терминалы</div>
								</div>
								<div class="bx-soa-pp-company col-lg-3 col-sm-4 col-xs-6">
									<div class="bx-soa-pp-company-graf-container">
										<input id="ID_PAY_SYSTEM_ID_1"
												 name="PAY_SYSTEM_ID"
												 type="checkbox"
												 class="bx-soa-pp-company-checkbox"
												 value="1">
										<div class="bx-soa-pp-company-image"
											 style="background-image: url(/upload/sale/paysystem/logotip/397/397f1ad6b92a31dda47824684e58ca51.gif);"></div>
									</div>
									<div class="bx-soa-pp-company-smalltitle">Наличные курьеру</div>
								</div>
								<div class="bx-soa-pp-company col-lg-3 col-sm-4 col-xs-6">
									<div class="bx-soa-pp-company-graf-container">
										<input id="ID_PAY_SYSTEM_ID_2"
												 name="PAY_SYSTEM_ID"
												 type="checkbox"
												 class="bx-soa-pp-company-checkbox"
												 value="2">
										<div class="bx-soa-pp-company-image"
											 style="background-image: url(/upload/sale/paysystem/logotip/25f/25ffaf55ef53096ad9a89d2c5e86be1d.png);"></div>
									</div>
									<div class="bx-soa-pp-company-smalltitle">Наложенный платеж</div>
								</div>
								<div class="bx-soa-pp-company col-lg-3 col-sm-4 col-xs-6">
									<div class="bx-soa-pp-company-graf-container">
										<input id="ID_PAY_SYSTEM_ID_8"
												 name="PAY_SYSTEM_ID"
												 type="checkbox"
												 class="bx-soa-pp-company-checkbox"
												 value="8">
										<div class="bx-soa-pp-company-image"
											 style="background-image: url(/upload/sale/paysystem/logotip/38c/38ca4261cc65971dd1bd732615707c20.gif);"></div>
									</div>
									<div class="bx-soa-pp-company-smalltitle">Сбербанк</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<a href="" class="btn btn-default btn-lg">Купить</a>
				</div>
			</div>
		</div>
	</div>
<!---->
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>