<?php

use PHPCodeCoverageVerifier\CodeCoverageVerifier;

class CodeCoverageVerifierTest extends PHPUnit_Framework_TestCase
{
	private function get_default_result()
	{
		return 	array(
					'covered' => array(),
					'not-covered' => array(),
					'ignored' => array(),
					'details' => array(
									'covered' => 0,
									'not-covered' => 0,
								),
				);
	}

	private function fixture($path)
	{
		return __DIR__.'/fixtures/'.$path;
	}

	/**
	 * @expectedException Exception
	 */
	public function testExecuteNullCloverXmlWithEmptyDiffString()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute(null, '');

		$this->assertEquals($this->get_default_result(), $coverage);
	}

	/**
	 * @expectedException Exception
	 */
	public function testExecuteNullCloverXmlWithDiffString()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute(null, file_get_contents($this->fixture('diff.diff')));

		$this->assertEquals($this->get_default_result(), $coverage);
	}

	/**
	 * @expectedException Exception
	 */
	public function testExecuteEmptyCloverXmlFileWithEmptyDiffFile()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('empty_clover_xml.xml'), $this->fixture('empty_diff.diff'));

		$this->assertEquals($this->get_default_result(), $coverage);
	}

	/**
	 * @expectedException Exception
	 */
	public function testExecuteEmptyCloverXmlFileWithDiffFile()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('empty_clover_xml.xml'), $this->fixture('diff.diff'));

		$this->assertEquals($this->get_default_result(), $coverage);
	}


	public function testExecuteCloverXmlFileWithEmptyDiffFile()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('clover_xml.xml'), $this->fixture('empty_diff.diff'));

		$this->assertEquals($this->get_default_result(), $coverage);
	}

	public function testExecuteCloverXmlFileWithUnrelatedDiffFile()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('clover_xml.xml'), $this->fixture('unrelated_diff.diff'));

		$expected = $this->get_default_result();
		$expected['ignored'][] = 'application/classes/controller/unrelated.php';
		$this->assertEquals($expected, $coverage);
	}

	public function testExecuteCloverXmlFileWithDiffFile()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('clover_xml.xml'), $this->fixture('diff.diff'));

		$expected = $this->get_default_result();
		$expected['covered'][] = 'application/classes/controller/a_nice_file.php line 78 - 84';
		$expected['covered'][] = 'application/classes/controller/a_nice_file.php line 114 - 120';
		$expected['not-covered'][] = 'application/classes/controller/a_nice_file.php line 58 - 65';
		$expected['details']['covered'] = 6;
		$expected['details']['not-covered'] = 2;
		$this->assertEquals($expected, $coverage);
	}

	public function testExecuteCloverXmlFileWithDiffFileWithOptionDisplayNotCoveredRange()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier(array('display_not_covered_range' => true));
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('clover_xml.xml'), $this->fixture('diff.diff'));

		$expected = $this->get_default_result();
		$expected['covered'][] = 'application/classes/controller/a_nice_file.php line 78 - 84';
		$expected['covered'][] = 'application/classes/controller/a_nice_file.php line 114 - 120';
		$expected['not-covered'][] = 'application/classes/controller/a_nice_file.php line 58 - 65 (58, 59)';
		$expected['details']['covered'] = 6;
		$expected['details']['not-covered'] = 2;
		$this->assertEquals($expected, $coverage);
	}
}