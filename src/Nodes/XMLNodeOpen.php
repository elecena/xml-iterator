<?php

namespace Elecena\XmlIterator\Nodes;

class XMLNodeOpen extends XMLNode
{
	function __construct(
		string $name,
		array  $attributes,
	)
	{
		parent::__construct($name, $attributes, content: null);
	}
}