<?php

namespace Elecena\XmlIterator\Nodes;

class XMLNodeOpen extends XMLNode
{
    public function __construct(
        string  $name,
        array   $attributes,
        ?string $parentName,
    ) {
        parent::__construct($name, $attributes, content: null, parentName: $parentName);
    }
}
