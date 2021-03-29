# Gerador de código estático de uma chave pix PHP
Classe para gerar código QR Code estático de uma chave pix, seguindo a documentação do Bacen:
https://www.bcb.gov.br/content/estabilidadefinanceira/pix/Regulamento_Pix/II-ManualdePadroesparaIniciacaodoPix-versao2-1.pdf

Basta baixar a classe PayloadPix e utilizar em seu projeto. :D

O Código QRCODE pode ser gerado de qualquer chave pix(E-mail,chave aleatória,cpf,cnpj,telefone), lembrando não deve utilizar nenhuma formatação seja de telefone ou CPF/CNPJ apenas os digítos pode ver um exemplo no arquivo example.php

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
