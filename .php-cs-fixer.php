<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/app')  // Target directories
    ->in(__DIR__ . '/routes')
    ->in(__DIR__ . '/database')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)  // Allows risky rules
    ->setRules([
        '@PSR12' => true,  // Follows PSR-12 standard
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_quote' => true,
        'trailing_comma_in_multiline' => true,
        'phpdoc_align' => true,
        'no_extra_blank_lines' => true,
    ])
    ->setFinder($finder);
