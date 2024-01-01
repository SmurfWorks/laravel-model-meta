<?php

it('wont work without models', function () {

    $this->artisan('model-meta:update-key')
        ->assertFailed();
});
