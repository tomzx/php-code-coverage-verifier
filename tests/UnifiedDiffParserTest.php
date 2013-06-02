<?php

use PHPCodeCoverageVerifier\UnifiedDiffParser;

class UnifiedDiffParserTest extends PHPUnit_Framework_TestCase
{
	private function fixture($path)
	{
		return __DIR__.'/fixtures/'.$path;
	}

	public function testParseInvalidDiff()
	{
		$this->setExpectedException('Exception', 'Could not find parser for line #1, text "1"');

		$diff_file = file_get_contents($this->fixture('invalid_diff.diff'));
		$unifiedDiffParser = new UnifiedDiffParser();
		$unifiedDiffParser->parse($diff_file);
	}

	public function testParseDiffWithComment()
	{
		$diff_file = file_get_contents($this->fixture('comment_diff.diff'));
		$unifiedDiffParser = new UnifiedDiffParser();
		$unifiedDiffParser->parse($diff_file);

		// Todo: Assert something...
	}

	public function testParseDiffWithLoggingEnabled()
	{
		$diff_file = file_get_contents($this->fixture('diff.diff'));
		$unifiedDiffParser = new UnifiedDiffParser();
		$unifiedDiffParser->setLogging(true);
		ob_start();
		$unifiedDiffParser->parse($diff_file);
		$content = ob_get_contents();
		ob_end_clean();

		$this->assertNotEmpty($content);
	}
}
