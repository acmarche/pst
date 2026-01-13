<?php

declare(strict_types=1);

pest()->extend(Tests\TestCase::class)
    // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    // ->use(Illuminate\Foundation\Testing\DatabaseTruncation::class)
    ->in('Feature');
