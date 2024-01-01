<?php

namespace SmurfWorks\ModelMeta\Tests\Setup;

class CustomClassTestCase extends DefaultTestCase
{
    public function getEnvironmentSetUp($app)
    {
        config()->set('model-meta.tables.keys', 'custom_class_keys');
        config()->set('model-meta.tables.values', 'custom_class_values');

        config()->set('model-meta.classes.keys', \SmurfWorks\ModelMeta\Tests\Models\CustomKey::class);
        config()->set('model-meta.classes.values', \SmurfWorks\ModelMeta\Tests\Models\CustomValue::class);

        parent::getEnvironmentSetUp($app);
    }
}
