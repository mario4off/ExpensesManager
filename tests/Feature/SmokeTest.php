<?php

it('boots the application in the testing environment', function () {
    expect(app()->environment())->toBe('testing');
});
