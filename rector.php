<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/database',
    ])
    ->withSkip([
        __DIR__ . '/app/Modules/*/Data',
        __DIR__ . '/app/Modules/*/Requests',
        __DIR__ . '/app/Modules/*/Resources',
    ])
    ->withSets([
        LaravelLevelSetList::UP_TO_LARAVEL_120,
        LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,
        LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
    ])
    ->withRules([
        // Add strict type declarations
        \Rector\PhpStrict\Rector\ClassMethod\StrictArrayParamDimFetchRector::class,
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        naming: true,
        privatization: false,
        earlyReturn: true,
    )
    ->withPhpSets(
        php83: true,
    )
    ->withImportNames(
        importShortClasses: true,
        removeUnusedImports: true,
    );
