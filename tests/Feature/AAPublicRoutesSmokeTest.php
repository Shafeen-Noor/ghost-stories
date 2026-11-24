<?php

it('redirects home to the stories', function () {
    $this->get('/')->assertRedirect('/stories');
});

it('opens the stories page', function () {
    $this->get('/stories')->assertOk();
});
