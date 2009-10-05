<?php

class Base##CLASS## extends BaseLDAPObject
{
  public static function copyFrom($ldap_object)
  {
    $new = new ##CLASS##();
    $new->attributes = $ldap_object->attributes;

    return $new;
  }
}
