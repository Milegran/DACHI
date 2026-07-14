<?php

interface AdminControllerInterface
{
    public function handle(array $get, array $post, array $session): void;
}
