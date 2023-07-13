<?php

namespace IceCream;

class Field {
    public string $name;
    public string $title;
    public string $type = '';
    public function __construct(string $name, string $title)
    {
        $this->name = $name;
        $this->title = $title;
    }
}