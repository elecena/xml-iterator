<?php

namespace Elecena\XmlIterator\Nodes;

class XMLNodeClose extends XMLNode
{
    public function __construct(
        string $name,
        ?string $parentName,
    ) {
        parent::__construct($name, attributes: [], content: null, parentName: $parentName);
    }
}
