<?php

class LDAPConnection
{
  private $connection;

  private static function getFullHost($ldap_criteria)
  {
    return ($ldap_criteria->getUseSsl()?"ldaps://":"").$ldap_criteria->getHost();
  }

  public static function getConnection(LDAPCriteria $ldap_criteria)
  {
    $connection = ldap_connect(self::getFullHost($ldap_criteria));

    ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

    $bind = @ldap_bind($connection, $ldap_criteria->getUsername(), $ldap_criteria->getPassword());
    if (!$bind)
    {
      throw new Exception(ldap_error($connection));
    }

    return $connection;
  }
}
