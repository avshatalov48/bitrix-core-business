<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:oas="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsg="http://WSGetAccountMovementList.ALBO.CS.ws.alfabank.ru" xmlns:wsg1="http://WSGetAccountMovementListTypes.ALBO.CS.ws.alfabank.ru" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
	<soapenv:Header><wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"><wsse:BinarySecurityToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary" wsu:Id="Security-Token-1">#CERT#</wsse:BinarySecurityToken><ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">#SIGNED_INFO#<ds:SignatureValue>#SIGNATURE_VALUE#</ds:SignatureValue><ds:KeyInfo><wsse:SecurityTokenReference><wsse:Reference URI="#Security-Token-1"/></wsse:SecurityTokenReference></ds:KeyInfo></ds:Signature></wsse:Security></soapenv:Header>
	<soapenv:Body xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" wsu:Id="reqBody">
		<wsg:WSGetAccountMovementListStatus xmlns:wsg="http://WSGetAccountMovementList.ALBO.CS.ws.alfabank.ru">
			<inCommonParms>
				<externalSystemCode><?=$params['ALFABANK_EXTERNAL_SYSTEM_CODE'];?></externalSystemCode>
				<externalUserCode><?=$params['ALFABANK_EXTERNAL_USER_CODE'];?></externalUserCode>
			</inCommonParms>
			<inParms>
				<wsg1:requestId xmlns:wsg1="http://WSGetAccountMovementListTypes.ALBO.CS.ws.alfabank.ru"><?=$params['REQUEST_ID'];?></wsg1:requestId>
			</inParms>
		</wsg:WSGetAccountMovementListStatus>
	</soapenv:Body>
</soapenv:Envelope>
