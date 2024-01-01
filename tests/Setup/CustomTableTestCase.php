<?php

namespace SmurfWorks\ModelMeta\Tests\Setup;

class CustomTableTestCase extends DefaultTestCase
{
    public function getEnvironmentSetUp($app)
    {
        config()->set('model-meta.tables.keys', 'custom_keys_table');
        config()->set('model-meta.tables.values', 'custom_values_table');

        parent::getEnvironmentSetUp($app);
    }
}
