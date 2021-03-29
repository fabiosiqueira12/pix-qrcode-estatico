<?php

/**
 * Gerar código estático de uma chave pix para QR Code de pagamento
 */
class PayloadPix{

    /**
     * IDs do Payload do Pix
     * @var string
     */
    const ID_PAYLOAD_FORMAT_INDICATOR = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION = '26';
    const ID_MERCHANT_ACCOUNT_INFORMATION_GUI = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION_KEY = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION = '02';
    const ID_MERCHANT_CATEGORY_CODE = '52';
    const ID_TRANSACTION_CURRENCY = '53';
    const ID_TRANSACTION_AMOUNT = '54';
    const ID_COUNTRY_CODE = '58';
    const ID_MERCHANT_NAME = '59';
    const ID_MERCHANT_CITY = '60';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE = '62';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID = '05';
    const ID_CRC16 = '63';

    /**
     * Método responsável por calcular o valor da hash de validação do código pix
     * @return string
     */
    private function getCRC16($payload) {
        //ADICIONA DADOS GERAIS NO PAYLOAD
        $payload .= self::ID_CRC16.'04';

        //DADOS DEFINIDOS PELO BACEN
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        //CHECKSUM
        if (($length = strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) $resultado ^= $polinomio;
                    $resultado &= 0xFFFF;
                }
            }
        }

        //RETORNA CÓDIGO CRC16 DE 4 CARACTERES
        return self::ID_CRC16.'04'.strtoupper(dechex($resultado));
    }

    /**
     * Chave pix pode SER CPF(apenas dígitos),e-mail, telefone (apenas dígitos), chave aleatório válida
     *
     * @var string
     */
    private $pixKey;

    /**
     * Descrição do pagamento
     *
     * @var string
     */
    private $description;

    /**
     * Nome do títular da conta
     *
     * @var string
     */
    private $merchantName;

    /**
     * Cidade do titular da conta
     *
     * @var string
     */
    private $merchantCity;

    /**
     * ID da transação pix
     *
     * @var string
     */
    private $txid;

    /**
     * Valor da transação
     *
     * @var string
     */
    private $amount;


    /**
     * Get chave pix
     *
     * @return  string
     */ 
    public function getPixKey()
    {
        return $this->pixKey;
    }

    /**
     * Set chave pix
     *
     * @param  string  $pixKey  Chave pix
     * @return self
     */ 
    public function setPixKey(string $pixKey)
    {
        $this->pixKey = $pixKey;
        return $this;
    }

    /**
     * Get descrição do pagamento
     *
     * @return  string
     */ 
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set descrição do pagamento
     *
     * @param  string  $description  Descrição do pagamento
     *
     * @return self
     */ 
    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get nome do títular da conta
     *
     * @return  string
     */ 
    public function getMerchantName()
    {
        return $this->merchantName;
    }

    /**
     * Set nome do títular da conta
     *
     * @param  string  $merchantName  Nome do títular da conta
     *
     * @return  self
     */ 
    public function setMerchantName(string $merchantName)
    {
        $this->merchantName = substr($merchantName, 0, 25);
        return $this;
    }

    /**
     * Get cidade do titular da conta
     *
     * @return  string
     */ 
    public function getMerchantCity()
    {
        return $this->merchantCity;
    }

    /**
     * Set cidade do titular da conta
     *
     * @param  string  $merchantCity  Cidade do titular da conta
     *
     * @return  self
     */ 
    public function setMerchantCity(string $merchantCity)
    {
        $this->merchantCity = $merchantCity;
        return $this;
    }

    /**
     * Get iD da transação pix
     *
     * @return  string
     */ 
    public function getTxid()
    {
        return $this->txid;
    }

    /**
     * Set iD da transação pix
     *
     * @param  string  $txid  ID da transação pix
     *
     * @return  self
     */ 
    public function setTxid(string $txid)
    {
        $this->txid = $txid;
        return $this;
    }

    /**
     * Get valor da transação
     *
     * @return  string
     */ 
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set valor da transação
     *
     * @param  float $amount  Valor da transação
     *
     * @return  self
     */ 
    public function setAmount(string $amount)
    {
        $this->amount = (string) \number_format($amount,2,'.','');
        return $this;
    }

    /**
     * Responsável por gerar o código completo do payload pix
     *
     * @return string
     */
    public function getPayLoad()
    {

        $amount = "";
        if (!empty($this->getAmount()) && $this->getAmount() != '0.00'){
            $amount .= $this->getValue(self::ID_TRANSACTION_AMOUNT,$this->getAmount());
        }else{
            $amount = "";
        }

        $payload = $this->getValue(self::ID_PAYLOAD_FORMAT_INDICATOR,'01').
        $this->getMerchantAccountInformation().
        $this->getValue(self::ID_MERCHANT_CATEGORY_CODE,'0000').
        $this->getValue(self::ID_TRANSACTION_CURRENCY,'986').
        $amount.
        $this->getValue(self::ID_COUNTRY_CODE,'BR').
        $this->getValue(self::ID_MERCHANT_NAME,$this->getMerchantName()).
        $this->getValue(self::ID_MERCHANT_CITY,$this->getMerchantCity()).
        $this->getAddionatialDataFieldTemplate();

        //RETORNA O PAYLOAD MAIS CRC16
        return $payload . $this->getCRC16($payload);
    }

    /**
     * Retorna os valores completos da informação da conta
     *
     * @return string
     */
    private function getMerchantAccountInformation()
    {
        //Dominio do banco
        $gui = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_GUI,"br.gov.bcb.pix");

        //Chave Pix KEY
        $key = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_KEY,$this->getPixKey());

        //DESCRICÃO DO PAGAMENTO
        $desc = !empty($this->getDescription()) ? $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION,$this->getDescription()) : "";

        //VALOR COMPLETO DA CONTA           
        return $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION,$gui.$key.$desc);

    }

    /**
     * Retorna os valores completos do campo adiconal do PIX (TXID)
     *
     * @return string
     */
    private function getAddionatialDataFieldTemplate()
    {
        //TXID
        $txid = $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID,$this->getTxid());

        //RETORNA O VALOR COMPLETO
        return $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE,$txid);
        
    }

    /**
     * Responsável por retornar o valor completo de um objeto do payload Pix
     *
     * @param string $id
     * @param string $value
     * @return string $id.size.$value
     */
    private function getValue($id,$value)
    {
        $size = str_pad(\strlen($value),2,'0',STR_PAD_LEFT);
        return $id.$size.$value;
    }
    
}

?>