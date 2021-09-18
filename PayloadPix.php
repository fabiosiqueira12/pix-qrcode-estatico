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
        

        function charCodeAt($payload, $i) {
            return ord(substr($payload, $i, 1));
        }
      
        $crc = 0xFFFF;
        $strlen = strlen($payload);
        for($c = 0; $c < $strlen; $c++) {
        $crc ^= charCodeAt($payload, $c) << 8;
            for($i = 0; $i < 8; $i++) {
                  if($crc & 0x8000) {
                     $crc = ($crc << 1) ^ 0x1021;
                  } else {
                     $crc = $crc << 1;
                  }
            }
         }
         $hex = $crc & 0xFFFF;
         $hex = dechex($hex);
         $hex = strtoupper($hex);
         $hex = str_pad($hex, 4, '0', STR_PAD_LEFT);
      
        return $hex;
    }

    /**
     * Remove os caracteres especiais
     *
     * @param string $txt
     * @return string 
     */
    private function removeCharEspeciais($txt){
        return preg_replace('/\W /','',$this->removeAcentos($txt));
    }
     
     /**
      * Remove os acentos do texto
      *
      * @param string $texto
      * @return string
      */
    private function removeAcentos($texto){
        $search = explode(",","à,á,â,ä,æ,ã,å,ā,ç,ć,č,è,é,ê,ë,ē,ė,ę,î,ï,í,ī,į,ì,ł,ñ,ń,ô,ö,ò,ó,œ,ø,ō,õ,ß,ś,š,û,ü,ù,ú,ū,ÿ,ž,ź,ż,À,Á,Â,Ä,Æ,Ã,Å,Ā,Ç,Ć,Č,È,É,Ê,Ë,Ē,Ė,Ę,Î,Ï,Í,Ī,Į,Ì,Ł,Ñ,Ń,Ô,Ö,Ò,Ó,Œ,Ø,Ō,Õ,Ś,Š,Û,Ü,Ù,Ú,Ū,Ÿ,Ž,Ź,Ż");
        $replace =explode(",","a,a,a,a,a,a,a,a,c,c,c,e,e,e,e,e,e,e,i,i,i,i,i,i,l,n,n,o,o,o,o,o,o,o,o,s,s,s,u,u,u,u,u,y,z,z,z,A,A,A,A,A,A,A,A,C,C,C,E,E,E,E,E,E,E,I,I,I,I,I,I,L,N,N,O,O,O,O,O,O,O,O,S,S,U,U,U,U,U,Y,Z,Z,Z");
        return $this->removeEmoji(str_replace($search, $replace, $texto));
     }
     
    /**
     * Remove emojis dos textos
     *
     * @param string $string
     * @return string
     */
    private function removeEmoji($string){
        return preg_replace('%(?:
            \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
        )%xs', '  ', $string);      
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
        $pixKey = ltrim($pixKey);
        //Verifica se a chave é um número de telefone para adicionar o +55
        if (is_numeric($pixKey) && !$this->validateCpf($pixKey) && !$this->validateCNPJ($pixKey)){
            $this->pixKey = "+55{$pixKey}";
        }else{
            $this->pixKey = $pixKey;
        }
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
        $this->description = $this->removeCharEspeciais($description);
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
        $this->merchantName = substr($this->removeCharEspeciais($merchantName), 0, 25);
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
        $this->merchantCity = substr($this->removeCharEspeciais($merchantCity), 0, 15);
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

        //ADICIONA DADOS GERAIS NO PAYLOAD
        $payload .= self::ID_CRC16.'04';

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
        $gui = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_GUI,"BR.GOV.BCB.PIX");

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

    /**
     * Faz Validação de CNPJ
     *
     * @param string $str
     * @return boolean
     */
    private function validateCNPJ($str)
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $str);
	
        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;

        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj))
            return false;	

        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
            return false;

        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
    
    /**
     * Verifica se a chave pix é um CPF
     *
     * @param string $str
     * @return boolean
     */
    private function validateCpf($str)
    {

        // Elimina possivel mascara
        $cpf = preg_replace("/[^0-9]/", "", $str);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

        // Verifica se o numero de digitos informados é igual a 11 
        if (strlen($cpf) != 11) {
            return false;
        }
        // Verifica se nenhuma das sequências invalidas abaixo 
        // foi digitada. Caso afirmativo, retorna falso
        else if (
            $cpf == '00000000000' ||
            $cpf == '11111111111' ||
            $cpf == '22222222222' ||
            $cpf == '33333333333' ||
            $cpf == '44444444444' ||
            $cpf == '55555555555' ||
            $cpf == '66666666666' ||
            $cpf == '77777777777' ||
            $cpf == '88888888888' ||
            $cpf == '99999999999'
        ) {
            return false;
            // Calcula os digitos verificadores para verificar se o
            // CPF é válido
        } else {

            for ($t = 9; $t < 11; $t++) {

                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf[$c] * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf[$c] != $d) {
                    return false;
                }
            }
            return true;
        }
    }
    
}

?>