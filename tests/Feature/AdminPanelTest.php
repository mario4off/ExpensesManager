<?php

it('exposes the admin panel and redirects guests to its login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('serves the admin login page', function () {
    $this->get('/admin/login')->assertOk();
});
