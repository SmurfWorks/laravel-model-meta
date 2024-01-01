<?php

use SmurfWorks\ModelMeta\Tests\Setup;

/* Note that running pest workflows on windows didn't seem to have a facade root set, and would fail with facades
 * in the tests. */

uses(Setup\DefaultTestCase::class)->in(__DIR__.'/Tests/Default');
uses(Setup\CustomTableTestCase::class)->in(__DIR__.'/Tests/CustomTable');
uses(Setup\CustomClassTestCase::class)->in(__DIR__.'/Tests/CustomClass');
uses(Setup\AvailableTypesTestCase::class)->in(__DIR__.'/Tests/AvailableTypes');
uses(Setup\InvalidNamespaceTestCase::class)->in(__DIR__.'/Tests/InvalidNamespace');
