<?php

namespace PHPCodeCoverageVerifier\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PHPCodeCoverageVerifier\CodeCoverageVerifier;

class Verify extends Command
{
	protected function configure()
	{
		$this
			->setName('verify')
			->setDescription('Verify code coverage using a clover-xml file against a diff/patch file')
			->addArgument(
				'clover-xml',
				InputArgument::REQUIRED,
				'Path to the clover-xml file'
			)
			->addArgument(
				'diff-file',
				InputArgument::REQUIRED,
				'Path to the diff-file'
			)
			->addOption(
				'display-not-covered-range',
				null,
				InputOption::VALUE_OPTIONAL,
				'Will display which line aren\'t covered',
				false
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$clover_xml = $input->getArgument('clover-xml');
		$diff_file = $input->getArgument('diff-file');

		$output->writeln('Using clover-xml file: '.$clover_xml);
		$output->writeln('With diff file: '.$diff_file);

		$options = array(
						'display_not_covered_range' => $input->hasOption('display-not-covered-range')
					);

		$codeCoverageVerifier = new CodeCoverageVerifier($options);
		$coverage = $codeCoverageVerifier->execute_file($clover_xml, $diff_file);

		$output->writeln('');
		$output->writeln('Covered: ');
		$this->output_coverage_result($output, $coverage['covered']);
		$output->write(str_repeat(PHP_EOL, 2));
		$output->writeln('Not covered: ');
		$this->output_coverage_result($output, $coverage['not-covered']);
		$output->write(str_repeat(PHP_EOL, 2));
		$output->writeln('Ignored: ');
		$this->output_coverage_result($output, $coverage['ignored']);
		$output->write(str_repeat(PHP_EOL, 2));
		$total = $coverage['details']['covered'] + $coverage['details']['not-covered'];
		if ($total === 0) {
			$percentage_covered = 0;
			$percentage_not_covered = 0;
		} else {
			$percentage_covered = 100 * $coverage['details']['covered'] / $total;
			$percentage_not_covered = 100 * $coverage['details']['not-covered'] / $total;
		}
		$output->writeln('Coverage: '.$coverage['details']['covered'].' covered ('.round($percentage_covered, 3).'%), '.$coverage['details']['not-covered'].' not covered ('.round($percentage_not_covered, 3).'%)');
	}

	private function output_coverage_result(OutputInterface $output, $coverage_array)
	{
		if (count($coverage_array) > 0) {
			$output->write(implode(PHP_EOL, $coverage_array));
		} else {
			$output->write('None');
		}
	}
}