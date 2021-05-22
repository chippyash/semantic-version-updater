<?php
declare(strict_types=1);
/**
 * Semantic Version Updater
 *
 * @author Ashley Kitson
 * @copyright Ashley Kitson 2017, 2021
 * @license GPLV3.0 See LICENSE.md
 */

namespace Chippyash\Vupdate\Command;

use Herrera\Version\Validator as VersionValidator;
use Herrera\Version\Dumper;
use Herrera\Version\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run the update
 */
class RunCommand extends Command
{
    protected const VFILENAME = 'VERSION';

    protected static $defaultName = 'run';

    protected function configure(): void
    {
        $this->setDescription('Semantic Version Updater')
            ->setHelp('Update a VERSION file according to semantic versioning rules')
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'Name of version file to use', self::VFILENAME)
            ->addOption('part', 'p', InputOption::VALUE_OPTIONAL, 'Which part of the version to update [patch|feature|bcbreak]', 'patch')
            ->addOption('force', 'o', InputOption::VALUE_OPTIONAL, 'Force version number')
            ->addOption('init', 'i', InputOption::VALUE_NONE, 'Initialise version file to 0.0.0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileToUpdate = $input->getOption('file');
        if ($input->getOption('init')) {
            $this->updateVersion('0.0.0', $fileToUpdate, $output);
            $output->writeln("version file initialised to 0.0.0");
            return Command::SUCCESS;
        }

        [$version, $ok] = $this->validateVersionFile($fileToUpdate, $output);
        if ($ok == Command::FAILURE) {
            return Command::FAILURE;
        }
        $output->writeln("current version is {$version}");

        if (!is_null($input->getOption('force'))) {
            $forceVersion = $input->getOption('force');
            $this->updateVersion($forceVersion, $fileToUpdate, $output);
            $output->writeln("version forced to {$forceVersion}");
            return Command::SUCCESS;
        }


        $partToUpdate = $input->getOption('part');
        $builder = Parser::toBuilder($version);
        switch ($partToUpdate) {
            case 'bcbreak':
                $this->updateVersion(Dumper::toString($builder->incrementMajor()), $fileToUpdate, $output);
                break;
            case 'feature':
                $this->updateVersion(Dumper::toString($builder->incrementMinor()), $fileToUpdate, $output);
                break;
            case 'patch':
                $this->updateVersion(Dumper::toString($builder->incrementPatch()), $fileToUpdate, $output);
                break;
            default:
                $output->writeln("invalid update part: {$partToUpdate}");
                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function updateVersion(string $version, string $fileName, OutputInterface $output)
    {
        file_put_contents($fileName, $version);
        $output->writeln("version updated to {$version}");
    }

    private function validateVersionFile($fileName, OutputInterface $output): array
    {
        if (!file_exists($fileName)) {
            $output->writeln('Not a valid version file name');
            return ['', Command::FAILURE];
        }

        $version = file_get_contents($fileName);
        if (!VersionValidator::isVersion($version)) {
            $output->writeln('Not a valid version string');
            return ['', Command::FAILURE];
        }

        return [$version, Command::SUCCESS];
    }
}
