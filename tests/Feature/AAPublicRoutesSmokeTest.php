<?php

it('opens the home page', function () {
    $this->get('/')->assertOk();
});
