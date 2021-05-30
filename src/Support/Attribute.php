<?php
/**
 * 
 * @fileName Attribute.php
 * @category PHP
 * @package void
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 16/06/2020
 * @version Attribute.php 2020.06.16
 * */
namespace Lex\Support;

trait Attribute {

  private $__attributes = [];

  private function getAttribute ($key) {
    return array_get($this->__attributes, $key, null);
  }

  private function setAttribute ($key, $val) {
    return array_set($this->__attributes, $key, $val);
  }

  /**
   * Dynamically retrieve attributes on the model.
   *
   * @param  string  $key
   * @return mixed
   */
  public function __get($key)
  {
    return $this->getAttribute($key);
  }

  /**
   * Dynamically set attributes on the model.
   *
   * @param  string  $key
   * @param  mixed  $value
   * @return void
   */
  public function __set($key, $value)
  {
    $this->setAttribute($key, $value);
  }

  /**
   * Determine if the given attribute exists.
   *
   * @param  mixed  $offset
   * @return bool
   */
  public function offsetExists($offset)
  {
    return ! is_null($this->getAttribute($offset));
  }

  /**
   * Get the value for a given offset.
   *
   * @param  mixed  $offset
   * @return mixed
   */
  public function offsetGet($offset)
  {
    return $this->getAttribute($offset);
  }

  /**
   * Set the value for a given offset.
   *
   * @param  mixed  $offset
   * @param  mixed  $value
   * @return void
   */
  public function offsetSet($offset, $value)
  {
    $this->setAttribute($offset, $value);
  }

  /**
   * Unset the value for a given offset.
   *
   * @param  mixed  $offset
   * @return void
   */
  public function offsetUnset($offset)
  {
    unset($this->__attributes[$offset]);
  }

  /**
   * Determine if an attribute or relation exists on the model.
   *
   * @param  string  $key
   * @return bool
   */
  public function __isset($key)
  {
    return $this->offsetExists($key);
  }

  /**
   * Unset an attribute on the model.
   *
   * @param  string  $key
   * @return void
   */
  public function __unset($key)
  {
    $this->offsetUnset($key);
  }

  /**
   * Handle dynamic method calls into the model.
   *
   * @param  string  $method
   * @param  array  $parameters
   * @return mixed
   */
  public function __call($method, $parameters) {
      return $this->$method(...$parameters);
  }

  /**
   * Handle dynamic static method calls into the method.
   *
   * @param  string  $method
   * @param  array  $parameters
   * @return mixed
   */
  public static function __callStatic($method, $parameters)
  {
    return (new static)->$method(...$parameters);
  }

  /**
   * Convert the model to its string representation.
   *
   * @return string
   */
  public function __toString()
  {
    return $this->toJson();
  }

  public function toJson () {
    return json_encode($this->__attributes);
  }

  /**
   * When a model is being unserialized, check if it needs to be booted.
   *
   * @return void
   */
  public function __wakeup() {
    $this->__attributes = json_decode(base64_decode($this->__attributes), true);
  }

  public function __sleep () {
    $this->__attributes = base64_encode(json_encode($this->__attributes));
    return array('__attributes');
  }

}
