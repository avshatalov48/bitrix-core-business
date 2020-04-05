<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:oas="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsc="http://WSCreatePaymentDocRUR11.ALBO.CS.ws.alfabank.ru" xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru">
	<soapenv:Header>
	<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"><wsse:BinarySecurityToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary" wsu:Id="Security-Token-1">#CERT#</wsse:BinarySecurityToken><ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">#SIGNED_INFO#<ds:SignatureValue>#SIGNATURE_VALUE#</ds:SignatureValue><ds:KeyInfo><wsse:SecurityTokenReference><wsse:Reference URI="#Security-Token-1"/></wsse:SecurityTokenReference></ds:KeyInfo></ds:Signature></wsse:Security></soapenv:Header>
	<soapenv:Body xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" wsu:Id="reqBody">
		<wsc:WSCreatePaymentDocRURAdd xmlns:wsc="http://WSCreatePaymentDocRUR11.ALBO.CS.ws.alfabank.ru">
			<inCommonParms>
				<externalSystemCode><?=$params['ALFABANK_EXTERNAL_SYSTEM_CODE'];?></externalSystemCode>
				<externalUserCode><?=$params['ALFABANK_EXTERNAL_USER_CODE'];?></externalUserCode>
			</inCommonParms>
			<inParms>
				<wsc1:docNumber xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['PAYMENT_ID'];?></wsc1:docNumber>
				<wsc1:docDate xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['PAYMENT_DATE_INSERT'];?></wsc1:docDate>
				<wsc1:docSum xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['PAYMENT_SHOULD_PAY'];?></wsc1:docSum>
				<wsc1:payerAccount xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['BUYER_PERSON_COMPANY_NAME_CONTACT'];?></wsc1:payerAccount>
				<wsc1:payerKpp xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['BUYER_PERSON_COMPANY_KPP'];?></wsc1:payerKpp>
				<wsc1:payerInn xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['BUYER_PERSON_COMPANY_INN'];?></wsc1:payerInn>
				<wsc1:recipientName xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['SELLER_COMPANY_NAME'];?></wsc1:recipientName>
				<wsc1:recipientInn xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['SELLER_COMPANY_INN'];?></wsc1:recipientInn>
				<wsc1:recipientKpp xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['SELLER_COMPANY_KPP'];?></wsc1:recipientKpp>
				<wsc1:recipientAccount xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['SELLER_COMPANY_BANK_ACCOUNT'];?></wsc1:recipientAccount>
				<wsc1:recipientBankBik xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['SELLER_COMPANY_BANK_BIC'];?></wsc1:recipientBankBik>
				<wsc1:priority xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['ALFABANK_PRIORITY'];?></wsc1:priority>
				<wsc1:paymentPurpose xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['ALFABANK_PAYMENT_SUBJECT'];?></wsc1:paymentPurpose>
				<wsc1:budgetPayerStatus xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['ALFABANK_BUDGET_PAYER_STATUS'];?></wsc1:budgetPayerStatus>
				<wsc1:budgetKbk xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['ALFABANK_BUDGET_KBK'];?></wsc1:budgetKbk>
				<wsc1:budgetOkato xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['ALFABANK_BUDGET_OKATO'];?></wsc1:budgetOkato>
				<wsc1:budgetOktmo xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['ALFABANK_BUDGET_OKTMO'];?></wsc1:budgetOktmo>
				<wsc1:budgetPaymentBase xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['ALFABANK_BUDGET_PAYMENT_BASE'];?></wsc1:budgetPaymentBase>
				<wsc1:budgetPeriod xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['ALFABANK_BUDGET_PERIOD'];?></wsc1:budgetPeriod>
				<wsc1:budgetDocNumber xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['ALFABANK_BUDGET_DOC_NUMBER'];?></wsc1:budgetDocNumber>
				<wsc1:budgetDocDate xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['ALFABANK_BUDGET_DOC_DATE'];?></wsc1:budgetDocDate>
				<wsc1:budgetPaymentType xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['ALFABANK_BUDGET_PAYMENT_TYPE'];?></wsc1:budgetPaymentType>
				<wsc1:code xmlns:wsc1="http://WSCreatePaymentDocRUR11Types.ALBO.CS.ws.alfabank.ru"><?=$params['CODE'];?></wsc1:code>
			</inParms>
		</wsc:WSCreatePaymentDocRURAdd>
	</soapenv:Body>
</soapenv:Envelope>
