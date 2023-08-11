<?php

namespace Elecena\XmlIterator\Nodes;

class XMLNodeClose extends XMLNode
{
	function __construct(
		string $name
	)
	{
		parent::__construct($name, attributes: [], content: null);
	}
}