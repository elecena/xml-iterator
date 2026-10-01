<?php

$finder = PhpCsFixer\Finder::create()
  ->in(__DIR__ . '/src/')
  ->in(__DIR__ . '/tests/')
;

$config = new PhpCsFixer\Config();

// https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/rules/index.rst
// https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/ruleSets/index.rst
return $config
  ->setUnsupportedPhpVersionAllowed(true)
  ->setRules([
      '@PSR2' => true,
  ])
  ->setFinder($finder)
;
