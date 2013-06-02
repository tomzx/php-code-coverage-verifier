<?php

namespace PHPCodeCoverageVerifier\Console;

class Application extends \Symfony\Component\Console\Application
{
	public function __construct($version)
	{
		parent::__construct('PHP Code Coverage Verifier by Tom Rochette', $version);

		$this->add(new Command\Verify());
	}
}