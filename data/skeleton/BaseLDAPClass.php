<?php

class Base##CLASS## extends BaseLDAPObject
{
  public static function copyFrom($ldap_object, $exclude_attrs)
  {
    $new = new ##CLASS##();
    $new->attributes = $ldap_object->attributes;

    if (!empty($exclude_attrs))
    {
      foreach ($exclude_attrs as $exclude_attr)
      {
        unset($new->attributes[$exclude_attr]);
      }
    }

    return $new;
  }
}
