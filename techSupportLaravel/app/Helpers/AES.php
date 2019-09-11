<?php namespace app\Helpers;

class AES
{
	private $mode, $key, $iv;
	
	function __construct($mode, $key, $iv) {
		// Ensure key size of 128, 192, or 256 bits
		$keylen = strlen(bin2hex($key)) * 4;
		if (!($keylen == 128 || $keylen == 192 || $keylen == 256)) {
			throw new Exception("Invalid Key length ($keylen) in AES constructor. Must be 128, 192, or 256 bits (16, 24, or 32 bytes).");
		}
		// Ensure IV size of 128 bits
		$this->iv = (isset($iv) ? $iv : $this->hex3bin("00000000000000000000000000000000"));
		$ivlen = strlen(bin2hex($this->iv)) * 4;
		if (!($ivlen == 128)) {
			throw new Exception("Invalid IV length ($ivlen) in AES constructor. Must be 128 bits (16 bytes).");
		}

		$this->key = $key;
	}

	public function encrypt($input) {
	  // $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
	  $encrypted = openssl_encrypt($input, 'aes-256-cbc', $this->key, 0, $this->iv);
	  return base64_encode($encrypted . '::' . $this->iv);
	}

	public function decrypt($input) {
	    list($encrypted_data, $this->iv) = 
	    explode('::', base64_decode($input), 2);
	    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $this->key, 0, $this->iv);
	}
	
	private function hex3bin($hexString) {
		if (strlen($hexString) % 2 != 0 || preg_match("/[^\da-fA-F]/",$hexString)) {
			throw new Exception("Invalid hexadecimal number ($hexString) in hex3bin()." . (strlen($hexString) % 2 != 0 ? ' Must have even number of characters.' : ''));
		}
		return pack("H*", $hexString);
	}
	
}