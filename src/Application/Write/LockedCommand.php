<?php


namespace Cafe\Application\Write;


interface LockedCommand
{
    public function lockName(): string;
}