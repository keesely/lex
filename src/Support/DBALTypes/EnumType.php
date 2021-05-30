<?php

namespace Lex\Support\DBALTypes;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class EnumType extends Type {

  const ENUM = "enum";

  public function getName() {
    return self::ENUM;
  }

  public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
    $length = array_get($fieldDeclaration, 'length');
    return sprintf('enum(%s)', "'".implode("','", $length)."'");
    //return $platform->getIntegerTypeDeclarationSQL($fieldDeclaration);
  }
  
  public function convertToPHPValue ($value, AbstractPlatform $platform) {
    return (null === $value) ? null : (string) $value;
  }

  public function getBindingType () {
    return self::ENUM;
  }

}
