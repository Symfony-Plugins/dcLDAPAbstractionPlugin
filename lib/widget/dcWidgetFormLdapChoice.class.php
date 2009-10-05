<?php

class dcWidgetFormLdapChoice extends sfWidgetFormChoice
{
  public function __construct($options = array(), $attributes = array())
  {
    $options['choices'] = new sfCallable(array($this, 'getChoices'));

    parent::__construct($options, $attributes);
  }

  public function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('model');
    $this->addRequiredOption('key_attribute');
    $this->addOption('add_empty', false);
    $this->addOption('method', '__toString');
    $this->addOption('sortfilter', null);
    $this->addOption('ldap_criteria', null);
    $this->addOption('connection', null);
    $this->addOption('peer_method', 'doSelect');

    parent::configure($options, $attributes);
  }

  public function getChoices()
  {
    $choices = array();

    if (false !== $this->getOption('add_empty'))
    {
      $choices[''] = true === $this->getOption('add_empty') ? '' : $this->getOption('add_empty');
    }

    $peer = $this->getOption('model').'Peer';
    $peer_method = $this->getOption('peer_method');

    $ldap_criteria = $this->getOption('ldap_criteria')?$this->getOption('ldap_criteria'):new LDAPCriteria();
    $conn = $this->getOption('connection')?$this->getOption('connection'):null;
    $ldap_criteria->setSortfilter($this->getOption('sortfilter')?$this->getOption('sortfileter'):null);

    $objects = call_user_func("$peer::$peer_method", $ldap_criteria, $conn);

    $key_attribute = $this->getOption('key_attribute');
    $method = $this->getOption('method');

    foreach ($objects as $object)
    {
      $choices[$object->get($key_attribute)] = $object->$method();
    }

    return $choices;
  }
}
