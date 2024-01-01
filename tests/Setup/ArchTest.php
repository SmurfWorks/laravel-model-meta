<?php

/* This didn't seem to run in workflows with prefer-oldest */
arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();
