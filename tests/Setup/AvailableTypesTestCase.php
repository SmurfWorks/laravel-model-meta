<?php

namespace SmurfWorks\ModelMeta\Tests\Setup;

use SmurfWorks\ModelMeta\ModelMeta;

class AvailableTypesTestCase extends DefaultTestCase
{
    public function getEnvironmentSetUp($app)
    {
        config()->set('model-meta.disable-value-types', [
            ModelMeta::TYPE_FULLTEXT,
            ModelMeta::TYPE_JSONB,
            ModelMeta::TYPE_INTEGER,
        ]);

        parent::getEnvironmentSetUp($app);
    }
}
