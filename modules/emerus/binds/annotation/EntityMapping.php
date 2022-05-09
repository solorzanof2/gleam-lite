<?php

namespace emerus\binds\annotation;

class EntityMapping
{

    private $valid = true;

    private $tablename;

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid( bool $valid ): EntityMapping
    {
        $this->valid = $valid;
        return $this;
    }

    public function getTablename(): string
    {
        return $this->tablename;
    }

    public function setTablename( string $tablename ): EntityMapping
    {
        $this->tablename = $tablename;
        return $this;
    }
    
}