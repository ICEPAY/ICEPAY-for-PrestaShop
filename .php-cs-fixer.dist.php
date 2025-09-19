<?php
use PhpCsFixer\Finder;
use PrestaShop\CodingStandards\CsFixer\Config as PsConfig;

$header = trim(file_get_contents(__DIR__ . '/LICENSE'));

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor','node_modules','var'])
    ->name('*.php');

$config = new PsConfig();
$config->setUsingCache(true);

return $config
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules(array_merge(
        $config->getRules(),
        [
            'blank_line_after_opening_tag' => true,
            'nullable_type_declaration_for_default_null_value' => true,
            'header_comment' => [
                'header' => $header,
                'comment_type' => 'PHPDoc',
                'location' => 'after_open',
                'separate' => 'none',
            ],
        ]
    ));