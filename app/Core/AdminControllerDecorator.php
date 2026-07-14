<?php

abstract class AdminControllerDecorator implements AdminControllerInterface
{
    protected AdminControllerInterface $wrapped;

    public function __construct(AdminControllerInterface $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    abstract public function handle(array $get, array $post, array $session): void;
}
