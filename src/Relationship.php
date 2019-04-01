<?php
declare(strict_types = 1);

namespace MyaZaki\LaravelSchemaspyMeta;

class Relationship
{
    public $related_table;
    public $foreign_key;
    public $parent_table;
    public $local_key;
    
    public function __construct($related_table, $foreign_key, $parent_table, $local_key)
    {
        $this->related_table = $related_table;
        $this->foreign_key = $foreign_key;
        $this->parent_table = $parent_table;
        $this->local_key = $local_key;
    }
}
