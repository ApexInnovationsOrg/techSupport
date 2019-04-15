<?php namespace app\Helpers;

class AES
{
	private $mode, $key, $iv;
	
	function __construct($mode, $key, $iv = null) {
		// Ensure key size of 128, 192, or 256 bits
		$keylen = strlen(bin2hex($key)) * 4;
		if (!($keylen == 128 || $keylen == 192 || $keylen == 256)) {
			throw new \Exception("Invalid Key length ($keylen) in AES constructor. Must be 128, 192, or 256 bits (16, 24, or 32 bytes).");
		}

		// Ensure IV size of 128 bits
		if($iv == null)
		{
			$iv = $this->hex3bin("00000000000000000000000000000000");
		}

		$this->iv = $iv;

		$ivlen = strlen(bin2hex($this->iv)) * 4;
		if (!($ivlen == 128)) {
			error_log($ivlen);
			throw new \Exception("Invalid IV length ($ivlen) in AES constructor. Must be 128 bits (16 bytes).");
		}
		
		// Initialize all object properties
		$this->mode = $mode;
		if (!($mode ==  MCRYPT_MODE_ECB || $mode == MCRYPT_MODE_CBC)) {
			throw new \Exception("Invalid Mode in AES constructor. Must be MCRYPT_MODE_ECB or MCRYPT_MODE_CBC.");
		}
		$this->key = $key;
	}

	private function hex3bin($hexString)
	{
		if (strlen($hexString) % 2 != 0 || preg_match("/[^\da-fA-F]/",$hexString)) {
			throw new Exception("Invalid hexadecimal number ($hexString) in hex3bin()." . (strlen($hexString) % 2 != 0 ? " Must have even number of characters." : ""));
		}
		return pack("H*", $hexString);
	}
	// Replace \0 padding of mcrypt with more standard PKCS7 padding
	function pad($data, $mode) {
		$block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, $this->mode);
		$datalen = strlen($data);
		switch ($mode) {
			case "PKCS7":
			case "PKCS5":
				$value = $block - ($datalen % $block);	// Both # of bytes and byte value
				$padding = str_repeat(chr($value), $value);
				break;
			default:	// "Zeros" - default 
				$padding = "";
				break;
		}	
		$data .= $padding;
		return $data;
	}
	function unpad($data, $mode) {
		$block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, $this->mode);
		$datalen = strlen($data);
		switch ($mode) {
			case "PKCS7":
			case "PKCS5":
				$padding = ord($data[$datalen - 1]);	// Both # of bytes and byte value
				if($padding && ($padding < $block)){
					for($i = $datalen - 1; $i >= $datalen - $padding; $i--){
						if(ord($data[$i]) != $padding){
							$padding = 0;	// will break loop also
						}
					}
				}
				break;
			default:	// "Zeros" - default
				$padding = 0;
				for($i = 1; $i <= $block; $i++){
					if(ord($data[$datalen - $i]) == 0){
						$padding++;
					}
				}
				if ($padding == $block) $padding = 0;	// So test-vectors don't fail
				break;
		}
		$data = substr($data, 0, strlen($data) - $padding);
		return $data;
	}
	function encrypt($input, $padMode = "Zeros") { return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $this->pad($input, $padMode), $this->mode, $this->iv); }
	function decrypt($input, $padMode = "Zeros") { return $this->unpad(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $input, $this->mode, $this->iv), $padMode); }
}