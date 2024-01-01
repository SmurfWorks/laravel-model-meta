<?php

namespace SmurfWorks\ModelMeta\Tests\Setup;

class InvalidNamespaceTestCase extends DefaultTestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config()->set('model-meta.model-namespace', ['Invalid\Namespace']);
        cache()->clear();
    }
}
