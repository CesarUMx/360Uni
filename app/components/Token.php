<?php
use \Phalcon\Di\Injectable;

class Token extends Injectable {
    


    public function encrypt($string, $key) {
        $iv = "3132333435363738";
        $encrypted = openssl_encrypt($string, 'AES-256-CBC', $key, $options=0, $iv);
        return base64_encode($encrypted);
    }

    public function arrToString($arr) {
        return base64_encode(serialize($arr));
    }

    public function stringToArr($decrypted) {
        return unserialize(base64_decode($decrypted));
    }

    public function decrypt($encrypted, $key) {
        $data= base64_decode($encrypted);
        $iv = "3132333435363738";
        $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, $options=0, $iv);

        return $decrypted;
    }

}
