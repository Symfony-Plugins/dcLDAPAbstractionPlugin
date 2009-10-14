<?php

class Base##CLASS##Peer extends BaseLDAPeer
{
  const HOST = "##HOST##";

  const USERNAME = "##USERNAME##";

  const PASSWORD = "##PASSWORD##";

  const USE_SSL = ##USE_SSL##;

  const BASE_DN = "##BASE_DN##";

  public static $exclude_attrs = ##EXCLUDE_ATTRS##;

  public static function configureCriteria(LDAPCriteria $ldap_criteria)
  {
    $ldap_criteria->setHost(self::HOST);
    $ldap_criteria->setUsername(self::USERNAME);
    $ldap_criteria->setPassword(self::PASSWORD);
    $ldap_criteria->setUseSsl(self::USE_SSL);
    $ldap_criteria->setBaseDn(self::BASE_DN);

    return $ldap_criteria;
  }

  public static function doSelect(LDAPCriteria $ldap_criteria, $conn = null)
  {
    $objects = array();

    $ldap_criteria = self::configureCriteria($ldap_criteria);
    $ldap_objects = parent::doSelect($ldap_criteria, $conn);
    foreach ($ldap_objects as $ldap_object)
    {
      $objects[] = ##CLASS##::copyFrom($ldap_object, self::$exclude_attrs);
    }
    return $objects;
  }

  public static function doSelectOne(LDAPCriteria $ldap_criteria, $conn = null)
  {
    $ldap_criteria = self::configureCriteria($ldap_criteria);
    $ldap_object = parent::doSelectOne($ldap_criteria, $conn);
    return ##CLASS##::copyFrom($ldap_object, self::$exclude_attrs);
  }

  public static function retrieveBy($attribute, $value, $conn = null)
  {
    $ldap_criteria = self::configureCriteria(new LDAPCriteria());
    $ldap_criteria->add($attribute, $value);
    return self::doSelectOne($ldap_criteria, $conn);
  }
}
