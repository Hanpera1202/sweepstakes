<?php namespace App\Libs;

use Carbon\Carbon;

class Crypt{

    static function mc_decrypt($decrypt) {
        $mc_key = 'yGfJrzEVfDmtbWZS';
        $decoded = base64_decode($decrypt);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $mc_key, $decoded, MCRYPT_MODE_ECB, $iv);
        return self::pkcs5_unpad($decrypted);
    }

    // PKCS5Padding
    // 埋められたバイト値を除く
    static function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text)) return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
        return substr($text, 0, -1 * $pad);
    }

}
