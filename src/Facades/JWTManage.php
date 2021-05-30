<?php
/**
 * 
 * @fileName JWTManage.php
 * @category PHP
 * @package void
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 29/05/2018
 * @version JWTManage.php 2018.05.29
 * */
namespace Lex\Facades;

use Lcobucci\JWT\Builder as JWTAuth;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;

class JWTManage {

  const SIGNER_SHA256 = \Lcobucci\JWT\Signer\Hmac\Sha256::class;

  const SIGNER_KEYCHAIN = \Lcobucci\JWT\Signer\Keychain::class;

  // Sha Rsa Alg
  const SIGNER_RS256 = \Lcobucci\JWT\Signer\Rsa\Sha256::class;
  const SIGNER_RS384 = \Lcobucci\JWT\Signer\Rsa\Sha384::class;
  const SIGNER_RS512 = \Lcobucci\JWT\Signer\Rsa\Sha512::class;

  protected $_parse = null;

  protected $_signers = [];

  public function __construct () {
    $this->_signers = [
      'sha256'   => self::SIGNER_SHA256,
      'keychain' => self::SIGNER_KEYCHAIN,
      'RS256'    => self::SIGNER_RS256,
      'RS384'    => self::SIGNER_RS384,
      'RS512'    => self::SIGNER_RS512,
    ];
  }

  public function Auth ($token = null) {
    if (null !== $token) {
      $this->_parse = (new Parser)->parse(trim($token));
      return $this;
    }
    else return new JWTAuth;
  }

  public function getSigners () {
    return $this->_signers;
  }

  public function getSigner ($key) {
    if (isset($this->_signers[$key])) return new $this->_signers[$key];
    return false;
  }

  /**
   * 获取校验数据
   *
   * @param {array}   $data     构造参数数据
   * 允许的键值
   * ['setId', 'setIssuer', 'setAudience', 'setSubject', 'setCurrentTime']
   * @param {int}     $expired  校验时间是否过期
   *
   * @return \Lcobucci\JWT\ValidationData
   * */
  public function getValidationData (array $data = null, $expired = null) {
    if (is_int($expired)) $vdata = new ValidationData($expired);
    else $vdata = new ValidationData;

    if (null === $data) return $vdata;

    foreach ($data as $k => $v) {
      if (method_exists($vdata, $k)) {
        if (!is_array($v)) $v = [$v];
        call_user_func_array([$vdata, $k], $v);
      }
    }
    return $vdata;
  }

  public function get ($key) {
    if (!$this->_parse) return null;
    return $this->_parse->getClaim($key);
  }

  /**
   * 验证JWT是否过期
   * 
   * @param {int}   $time   到期时间
   *
   * @return bool 已到期 true, 未到期 false
   * */
  public function expired ($timeout) {
    if (!$this->_parse) return true;
    if (intval($timeout) >= $this->get('exp')) return true;
    return false;
  }

  public function __call ($name, $args) {
    if ($this->_parse && method_exists($this->_parse, $name)) {
      return call_user_func_array([$this->_parse, $name], $args);
    }
    return null;
  }

}
