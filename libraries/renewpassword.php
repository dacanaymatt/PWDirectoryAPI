<?php

/**
 * Secret Key Generator
 *
 * Secret key generator for email reset
 * http://self-issued.info/docs/draft-jones-json-web-token-01.html.
 *
 * @author Vince Urag <vinceuragvfx@gmail.com>
 * @author Rook
 * http://stackoverflow.com/users/183528/rook
 */

class Renewpassword {
  function crypto_rand_secure($min, $max)
  {
      $range = $max - $min;
      if ($range < 0) return $min; // not so random...
      $log = log($range, 2);
      $bytes = (int) ($log / 8) + 1; // length in bytes
      $bits = (int) $log + 1; // length in bits
      $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
      do {
          $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
          $rnd = $rnd & $filter; // discard irrelevant bits
      } while ($rnd >= $range);
      return $min + $rnd;
  }

  function getSecretKey($length=8)
  {
      $secretKey = "";
      $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
      $codeAlphabet.= "0123456789";
      for($i=0;$i<$length;$i++){
          $secretKey .= $codeAlphabet[$this->crypto_rand_secure(0,strlen($codeAlphabet))];
      }
      return $secretKey;
  }
}
