<?php

namespace Elecena\XmlIterator\Nodes;

/**
 * An abstract class for the items returned when iterating over the instance of the @see \Elecena\XmlIterator\XMLParser
 */
abstract class XMLNode
{
    public function __construct(
        public string  $name,
        public array   $attributes = [],
        public ?string $content = null,
    ) {
    }
}
