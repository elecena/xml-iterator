<?php

namespace Elecena\XmlIterator\Nodes;

/**
 * An abstract class for the items returned when iterating over the instance of the @see \Elecena\XmlIterator\XMLParser
 */
abstract class XMLNode {
	function __construct(
		public string $tagName,
		public array $tagAttributes = [],
		public ?string $tagContent = null,
	) {}
}
