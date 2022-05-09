<?php

namespace emerus\core;

use emerus\utils\StringUtils;

class Collector
{
    const ID = 'id';
    
    private $inverseJoinField;

    private $joinField;

    private $joinValue = 0;

    private $rawData = [];

    private $data = [];

    private $collecteds = [];

    private $insert = [];

    public function __construct(string $joinField, string $inverseField, array $collection = [])
    {
        $this->rawData = $collection;
        foreach ($collection as $data) {
            if (StringUtils::isNull($this->joinField)) {
                $this->joinField = $joinField;
                $this->joinValue = $data[$joinField];
                $this->inverseJoinField = $inverseField;
            }
            if (isset($data[$inverseField])) {
                $this->data[] = $data[$inverseField];
            }
        }
    }

    public function addData(array $data): void
    {
        $this->data = $data;
    }

    public function add($id): void
    {
        if (in_array($id, $this->data)) {
            $this->collecteds[] = $id;
        } else {
            $this->insert[] = $id;
        }
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getCollecteds(): array
    {
        return $this->collecteds;
    }

    public function getToInsert(): array
    {
        return $this->insert;
    }

    public function getDroppeds(bool $inString = FALSE)
    {
        if ($inString) {
            return implode(',', $this->getDroppeds());
        } else {
            return array_diff($this->data, $this->collecteds);
        }
    }

    public function get(): array
    {
        $toRemove = $this->getDroppeds();
        $collection = [];

        # filter at first place all the removed childs
        foreach ($this->rawData as $row) {
            if (!in_array($row[$this->inverseJoinField], $toRemove)) {
                $collection[] = $row;
            }
        }

        # adding new childs collection
        foreach ($this->insert as $newChild) {
            $collection[] = $this->getRow($newChild);
        }

        # returning new collection to insert/update
        return $collection;
    }

    private function getRow(int $inverseFieldValue): array
    {
        return [
            self::ID => null,
            $this->joinField => $this->joinValue,
            $this->inverseJoinField => $inverseFieldValue
        ];
    }
}

?>