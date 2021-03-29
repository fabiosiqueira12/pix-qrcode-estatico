<?php

require "PayloadPix.php";

//EXEMPLO COM CPF
$payLoadQRCode = (new PayloadPix)
->setPixKey("88191879069")
->setMerchantName("Nome do dono da conta")
->setMerchantCity("Sao Paulo")
->setAmount(0)
->setTxid(\uniqid())->getPayLoad();

echo $payLoadQRCode;
//Resultado
//00020126330014br.gov.bcb.pix0111881918790695204000053039865802BR5921Nome do dono da conta6009Sao Paulo62170513606155b4bb99663044B9C

?>