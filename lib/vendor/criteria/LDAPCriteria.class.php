<?php

class LDAPCriteria
{
  /*
   * Search scopes
   */
  const BASE = 0;
  const ONE = 1;
  const SUB = 2;
  const CHILDREN = 3;

  /*
   * Likes
   */
  const BEGINS = 1;
  const ENDS = 2;
  const CONTAINS = 3;

  /*
   * Comparators
   */
  const LESS = "<=";
  const GREATER = ">=";
  const EQUAL = "=";
  const APPROX = "~=";

  /*
   * Default filter
   */
  const DEFAULT_FILTER = "(objectClass=*)";

  private static $functions = array(
    self::BASE => "ldap_read",
    self::ONE => "ldap_list",
    self::SUB => "ldap_search",
    self::CHILDREN => null
  );

  private $host,
          $username,
          $password,
          $use_ssl,
          $base_dn,
          $search_scope,
          $filter,
          $attributes,
          $attrsonly,
          $sizelimit,
          $timelimit,
          $deref,
          $sortfilter;

  public function __construct()
  {
    $this->buildDefaultSearchCriteria();
  }

  public function setHost($v)
  {
    $this->host = $v;
    return $this;
  }

  public function getHost()
  {
    return $this->host;
  }

  public function setUsername($v)
  {
    $this->username = $v;
    return $this;
  }

  public function getUsername()
  {
    return $this->username;
  }

  public function setPassword($v)
  {
    $this->password = $v;
    return $this;
  }

  public function getPassword()
  {
    return $this->password;
  }

  public function setUseSsl($v)
  {
    $this->use_ssl = $v;
    return $this;
  }

  public function getUseSsl()
  {
    return $this->use_ssl;
  }

  public function setBaseDn($v)
  {
    $this->base_dn = $v;
    return $this;
  }

  public function getBaseDn()
  {
    return $this->base_dn;
  }

  public function setSearchScope($v)
  {
    $this->search_scope = $v;
    return $this;
  }

  public function getSearchScope()
  {
    return $this->search_scope;
  }

  public function setFilter($v)
  {
    $this->filter = $v;
    return $this;
  }

  public function getFilter()
  {
    return $this->filter;
  }

  public function setAttributes($v)
  {
    $this->attributes = $v;
    return $this;
  }

  public function getAttributes()
  {
    return $this->attributes;
  }

  public function setAttrsonly($v)
  {
    $this->attrsonly = $v;
    return $this;
  }

  public function getAttrsonly()
  {
    return $this->attrsonly;
  }

  public function setSizelimit($v)
  {
    $this->sizelimit = $v;
    return $this;
  }

  public function getSizelimit()
  {
    return $this->sizelimit;
  }

  public function setTimelimit($v)
  {
    $this->timelimit = $v;
    return $this;
  }

  public function getTimelimit()
  {
    return $this->timelimit;
  }

  public function setDeref($v)
  {
    $this->deref = $v;
    return $this;
  }

  public function getDeref()
  {
    return $this->deref;
  }

  public function setSortfilter($v)
  {
    $this->sortfilter = $v;
    return $this;
  }

  public function getSortfilter()
  {
    return $this->sortfilter;
  }

  public function buildDefaultSearchCriteria()
  {
    $this->setSearchScope(self::SUB);
    $this->setFilter(self::DEFAULT_FILTER);
    $this->setAttributes(array());
    $this->setAttrsonly(0);
    $this->setSizelimit(0);
    $this->setTimelimit(0);
    $this->setDeref(LDAP_DEREF_NEVER);
    $this->setSortfilter(null);
  }

  public function getFunction()
  {
    $function = null;
    if (isset(self::$functions[$this->getSearchScope()]) && !is_null(self::$functions[$this->getSearchScope()]))
    {
      $function = self::$functions[$this->getSearchScope()];
    }
    return $function;
  }

  private function getCondition($attribute, $value, $comparator, $like)
  {
    switch ($like)
    {
      case self::BEGINS:
        $value = $value."*";
        break;
      case self::ENDS:
        $value = "*".$value;
        break;
      case self::CONTAINS:
        $value = "*".$value."*";
        break;
      default:
        break;
    }
    return "($attribute$comparator$value)";
  }

  public function add($attribute, $value, $comparator = self::EQUAL, $like = null)
  {
    $new_filter = $this->getCondition($attribute, $value, $comparator, $like);
    if ($this->getFilter() != self::DEFAULT_FILTER)
    {
      $new_filter = "(&".$this->getFilter().$new_filter.")";
    }
    $this->setFilter($new_filter);
    return $this;
  }

  public function addOr($attribute, $value, $comparator = self::EQUAL, $like = null)
  {
    $new_filter = $this->getCondition($attribute, $value, $comparator, $like);
    if ($this->getFilter() != self::DEFAULT_FILTER)
    {
      $new_filter = "(|".$this->getFilter().$new_filter.")";
    }
    $this->setFilter($new_filter);
    return $this;
  }
}
