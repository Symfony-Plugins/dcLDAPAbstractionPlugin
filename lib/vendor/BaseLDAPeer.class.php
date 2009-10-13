<?php

class BaseLDAPeer
{
  const HOST = "www.example.com";

  const USERNAME = "username";

  const PASSWORD = "password";

  const USE_SSL = false;

  public static $exclude_attrs = array();

  public function __construct()
  {
  }

  private static function extractAttributes($ldap_entry, $conn)
  {
    $attributes = array();

    if ($ldap_entry !== false)
    {
      $attrs = ldap_get_attributes($conn, $ldap_entry);

      foreach (array_keys($attrs) as $attribute)
      {
        if (!is_int($attribute) && $attribute != "count")
        {
          $attributes[] = $attribute;
        }
      }
    }

    return $attributes;
  }

  private static function extractValues($ldap_entry, $attributes, $conn)
  {
    $values = array();
    foreach ($attributes as $attribute)
    {
      $vals = ldap_get_values($conn, $ldap_entry, $attribute);
      unset($vals["count"]);
      $values[$attribute] = $vals;
    }

    return $values;
  }

  private static function createLDAPObject($ldap_entry, $conn)
  {
    $attributes = self::extractAttributes($ldap_entry, $conn);
    $values = self::extractValues($ldap_entry, $attributes, $conn);
    $values['dn'] = ldap_get_dn($conn, $ldap_entry);

    $ldap_object = new BaseLDAPObject();
    return $ldap_object->__constructFrom($values);
  }

  private static function select(LDAPCriteria $ldap_criteria, $conn = null)
  {
    if ($conn === null)
    {
      $conn = LDAPConnection::getConnection($ldap_criteria);
    }

    if ($function = $ldap_criteria->getFunction())
    {
      $results = $function($conn,
                           $ldap_criteria->getBaseDn(),
                           $ldap_criteria->getFilter(),
                           $ldap_criteria->getAttributes(),
                           $ldap_criteria->getAttrsonly(),
                           $ldap_criteria->getSizelimit(),
                           $ldap_criteria->getTimelimit(),
                           $ldap_criteria->getDeref());

      if (!$results)
      {
        throw new Exception(ldap_error($conn));
      }

      if (!is_null($ldap_criteria->getSortfilter()))
      {
        ldap_sort($conn, $results, $ldap_criteria->getSortfilter());
      }

      return $results;
    }
    else
    {
      throw new Exception("Fatal error: method not implemented.");
    }
  }

  public static function doSelect(LDAPCriteria $ldap_criteria, $conn = null)
  {
    if ($conn === null)
    {
      $conn = LDAPConnection::getConnection($ldap_criteria);
    }

    $ldap_objects = array();
    $results = self::select($ldap_criteria, $conn);

    $ldap_entry = ldap_first_entry($conn, $results);
    if ($ldap_entry !== false)
    {
      $ldap_objects[] = self::createLDAPObject($ldap_entry, $conn);

      while ($ldap_entry = ldap_next_entry($conn, $ldap_entry))
      {
        $ldap_objects[] = self::createLDAPObject($ldap_entry, $conn);
      }
    }

    return $ldap_objects;
  }

  public static function doSelectOne(LDAPCriteria $ldap_criteria, $conn = null)
  {
    if ($conn === null)
    {
      $conn = LDAPConnection::getConnection($ldap_criteria);
    }

    $results = self::select($ldap_criteria, $conn);
    $first_entry = ldap_first_entry($conn, $results);

    return self::createLDAPObject($first_entry, $conn);
  }
}
