<?php

class ldapBuildmodelTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    // // add your own options here
    // $this->addOptions(array(
    //   new sfCommandOption('my_option', null, sfCommandOption::PARAMETER_REQUIRED, 'My option'),
    // ));

    $this->namespace        = 'ldap';
    $this->name             = 'build-model';
    $this->briefDescription = 'The ldap:build-model task creates model classes from the ldap configuration';
    $this->detailedDescription = <<<EOF
The [ldap:build-model|INFO] task creates model classes from the ldap configuration.
Call it with:

  [php symfony ldap:build-model|INFO]

The task read the schema information in [config/ldap.yml|COMMENT] from the project.

The model classes files are created in [lib/ldap|COMMENT].

This task never overrides custom classes in [lib/ldap|COMMENT].
It only replaces files in [lib/ldap/base|COMMENT].
EOF;
  }

  private function checkConfigurationFiles()
  {
    $finder = sfFinder::type('file')->name('ldap.yml');

    $schemas = array_unique($finder->follow_link()->in(sfConfig::get('sf_config_dir')));
    if (!count($schemas))
    {
      throw new sfCommandException('You must create a ldap.yml file.');
    }

    $finder = sfFinder::type('file')->follow_link()->name('ldap_connections.yml');

    $schemas = array_unique($finder->in(sfConfig::get('sf_config_dir')));
    if (!count($schemas))
    {
      throw new sfCommandException('You must create a ldap_connections.yml file.');
    }
  }

  public function getArrayString($array)
  {
    $str = 'array(';
    if (!is_null($array))
    {
      foreach ($array as $value)
      {
        $str .= "'".$value."', ";
      }
      $str = substr($str, 0, -2).')';
    }
    else
    {
      $str .= ')';
    }
    return $str;
  }

  public function createClasses()
  {
    $sffs = new sfFilesystem();

    $lib_path = sfConfig::get("sf_lib_dir")."/ldap";
    $bases_path = sfConfig::get("sf_lib_dir")."/ldap/base";
    if (!file_exists($lib_path) || !file_exists($bases_path))
    {
      $this->logSection('dir+', $lib_path);
      $this->logSection('dir+', $bases_path);
      $sffs->mkdirs($bases_path);
    }

    $ldap_connections = sfYamlConfigHandler::parseYaml(sfConfig::get("sf_config_dir")."/ldap_connections.yml");
    $ldap_definitions = sfYamlConfigHandler::parseYaml(sfConfig::get("sf_config_dir")."/ldap.yml");

    foreach ($ldap_definitions as $connection => $classes)
    {
      foreach ($classes as $class_name => $attrs)
      {
        $class = sfInflector::camelize($class_name);
        $base_peer_path = $bases_path."/Base".$class."Peer.php";
        $base_object_path = $bases_path."/Base".$class.".php";
        $peer_path = $lib_path."/".$class."Peer.php";
        $object_path = $lib_path."/".$class.".php";

        if (file_exists($base_peer_path))
        {
          unlink($base_peer_path);
        }
        $this->logSection('file+', $base_peer_path);
        $sffs->copy(dirname(__FILE__)."/../../data/skeleton/BaseLDAPClassPeer.php", $base_peer_path);

        if (file_exists($base_object_path))
        {
          unlink($base_object_path);
        }
        $this->logSection('file+', $base_object_path);
        $sffs->copy(dirname(__FILE__)."/../../data/skeleton/BaseLDAPClass.php", $base_object_path);

        $sffs->replaceTokens(array($base_peer_path,
                                   $base_object_path),
                             "##",
                             "##",
                             array("CLASS" => $class,
                                   "HOST" => $ldap_connections[$connection]["host"],
                                   "USERNAME" => $ldap_connections[$connection]["username"],
                                   "PASSWORD" => $ldap_connections[$connection]["password"],
                                   "USE_SSL" => $ldap_connections[$connection]["use_ssl"]?"true":"false",
                                   "BASE_DN" => $attrs["base_dn"],
                                   "EXCLUDE_ATTRS" => $this->getArrayString(isset($attrs["exclude_attrs"])?$attrs["exclude_attrs"]:null)));

        if (!file_exists($peer_path))
        {
          $this->logSection('file+', $peer_path);
          $sffs->copy(dirname(__FILE__)."/../../data/skeleton/LDAPCommonClass.php", $peer_path);

          $sffs->replaceTokens(array($peer_path),
                               "##",
                               "##",
                               array("CLASS" => $class."Peer",
                                     "SUPERCLASS" => "Base".$class."Peer"));
        }

        if (!file_exists($object_path))
        {
          $this->logSection('file+', $object_path);
          $sffs->copy(dirname(__FILE__)."/../../data/skeleton/LDAPCommonClass.php", $object_path);

          $sffs->replaceTokens(array($object_path),
                               "##",
                               "##",
                               array("CLASS" => $class,
                                     "SUPERCLASS" => "Base".$class));
        }
      }
    }
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->checkConfigurationFiles();

    $this->logSection('ldap', 'generating ldap classes');

    $this->createClasses();
  }
}
