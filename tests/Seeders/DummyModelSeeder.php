<?php

namespace SmurfWorks\ModelMeta\Tests\Seeders;

use Illuminate\Database\Seeder;
use SmurfWorks\ModelMeta\ModelMeta;
use SmurfWorks\ModelMeta\Tests\Models\DummyModel;
use SmurfWorks\ModelMeta\Traits\ModelMetaSeeder;

class DummyModelSeeder extends Seeder
{
    use ModelMetaSeeder;

    public function run()
    {
        $this->createMetaKey(DummyModel::class, 'seeded_string', ModelMeta::TYPE_STRING);
        $this->createMetaKey(DummyModel::class, 'seeded_boolean', ModelMeta::TYPE_BOOLEAN);
        $this->createMetaKey(DummyModel::class, 'seeded_integer', ModelMeta::TYPE_INTEGER);

        $model = DummyModel::create(['name' => 'Seeded Test']);
        $model->update([
            'seeded_string' => 'Test String',
            'seeded_boolean' => true,
            'seeded_integer' => 123,
        ]);
    }
}
