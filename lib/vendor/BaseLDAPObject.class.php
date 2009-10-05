<?php

class BaseLDAPObject
{
  protected $attributes;

  public function __constructFrom($ldap_fields)
  {
    $this->attributes = array();
    foreach ($ldap_fields as $attribute => $value)
    {
      if (is_array($value) && count($value) == 1)
      {
        $this->attributes[$attribute] = $value[0];
      }
      else
      {
        $this->attributes[$attribute] = $value;
      }
    }
    return $this;
  }

  public function get($attribute)
  {
    if (isset($this->attributes[$attribute]))
    {
      return $this->attributes[$attribute];
    }
    else
    {
      return null;
    }
  }

  public function has($attribute, $value)
  {
    $has = false;
    if (isset($this->attributes[$attribute]))
    {
      if (count($this->attributes[$attribute]) == 1)
      {
        $has = $this->attributes[$attribute] == $value;
      }
      else
      {
        $has = in_array($value, $this->attributes[$attribute]);
      }
    }
    return $has;
  }
}
